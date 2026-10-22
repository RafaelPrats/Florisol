<?php

namespace yura\Http\Controllers\Postco;

use DB;
use Illuminate\Http\Request;
use yura\Http\Controllers\Controller;
use yura\Modelos\CodigoAutorizacion;
use yura\Modelos\CorreccionRecepcion;
use yura\Modelos\IngresoRecepcion;
use yura\Modelos\InventarioRecepcion;
use yura\Modelos\Planta;
use yura\Modelos\RegistroMovimientos;
use yura\Modelos\SalidasRecepcion;
use yura\Modelos\Submenu;
use yura\Modelos\Variedad;

class CorregirInventarioController extends Controller
{
    public function inicio(Request $request)
    {
        $finca = getFincaActiva();
        $plantas = Planta::where('estado', 1)
            ->where('id_empresa', $finca)
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.postco.corregir_inventario.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'plantas' => $plantas,
        ]);
    }

    public function listar_reporte(Request $request)
    {
        $finca = getFincaActiva();
        $listado = DB::table('inventario_recepcion as i')
            ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->select(
                'i.id_variedad',
                'v.nombre as var_nombre',
                'v.id_planta',
                'p.nombre as pta_nombre',
                DB::raw('sum(i.disponibles) as disponibles')
            )
            ->where('i.disponibles', '>=', 0)
            ->where('i.id_empresa', $finca)
            ->where('i.bodega', $request->bodega);
        if ($request->planta != '')
            $listado = $listado->where('v.id_planta', $request->planta);
        if ($request->variedad != '')
            $listado = $listado->where('i.id_variedad', $request->variedad);
        $listado = $listado->groupBy(
            'i.id_variedad',
            'v.nombre',
            'v.id_planta',
            'p.nombre'
        )
            ->orderBy('p.nombre')
            ->orderBy('v.nombre')
            ->get();
        $last_orden = DB::table('correccion_recepcion')
            ->select(DB::raw('max(orden) as orden'))
            ->get()[0]->orden + 1;
        foreach ($listado as $item) {
            $ingreso = DB::table('ingreso_recepcion')
                ->select(DB::raw('sum(tallos) as cantidad'))
                ->where('id_variedad', $item->id_variedad)
                ->where('id_empresa', $finca)
                ->where('bodega', $request->bodega)
                ->where('fecha', '<=', hoy())
                ->where('fecha', '>=', '2026-09-17')
                ->get()[0]->cantidad;
            $salida = DB::table('salidas_recepcion as s')
                ->join('inventario_recepcion as i', 'i.id_inventario_recepcion', '=', 's.id_inventario_recepcion')
                ->select(DB::raw("
        SUM(
            s.cantidad +
            CASE
                WHEN s.orden_basura IS NOT NULL
                     AND s.estado_orden_basura = 1
                THEN s.basura
                ELSE 0
            END
        ) as cantidad
    "))

                ->where('s.id_variedad', $item->id_variedad)
                ->where('i.id_empresa', $finca)
                ->where('i.bodega', $request->bodega)
                ->where('s.fecha', '<=', hoy())
                ->where('s.fecha', '>=', '2026-09-17')
                ->where('s.fecha_registro', '>=', '2026-09-17')

                ->get()[0]->cantidad;
            $saldo = $ingreso - $salida;
            $item->saldo = $saldo;
        }

        return view('adminlte.gestion.postco.corregir_inventario.partials.listado', [
            'last_orden' => $last_orden,
            'listado' => $listado,
        ]);
    }

    public function store_correccion(Request $request)
    {
        DB::beginTransaction();
        try {
            $codigo = CodigoAutorizacion::where('nombre', 'corregir_inventario')
                ->first();
            if ($codigo == '' || $codigo->valor != $request->codigo) {
                DB::rollBack();
                return [
                    'success' => false,
                    'mensaje' => '<div class="alert alert-danger text-center"><h3>El codigo de autorizacion es incorrecto</h3></div>',
                ];
            }

            $finca = getFincaActiva();
            $datos = json_decode($request->data);
            if (!is_array($datos) || count($datos) == 0) {
                DB::rollBack();
                return [
                    'success' => false,
                    'mensaje' => '<div class="alert alert-danger text-center"><h3>No hay variedades para corregir.</h3></div>',
                ];
            }

            $ids_variedades = [];
            $salidas_requeridas = [];
            foreach ($datos as $data) {
                if (
                    !isset($data->id_variedad, $data->anterior, $data->actual, $data->diferencia) ||
                    !is_numeric($data->id_variedad) ||
                    !is_numeric($data->anterior) ||
                    !is_numeric($data->actual) ||
                    !is_numeric($data->diferencia) ||
                    (int)$data->diferencia == 0 ||
                    (int)$data->actual - (int)$data->anterior != (int)$data->diferencia
                ) {
                    DB::rollBack();
                    return [
                        'success' => false,
                        'mensaje' => '<div class="alert alert-danger text-center"><h3>Los datos de la correccion no son validos. Refresque el inventario e intentelo de nuevo.</h3></div>',
                    ];
                }

                $id_variedad = (int)$data->id_variedad;
                $ids_variedades[$id_variedad] = $id_variedad;
                if ((int)$data->diferencia < 0) {
                    $salidas_requeridas[$id_variedad] = ($salidas_requeridas[$id_variedad] ?? 0) + abs((int)$data->diferencia);
                }
            }

            $inventarios_por_variedad = InventarioRecepcion::where('id_empresa', $finca)
                ->where('bodega', $request->bodega)
                ->whereIn('id_variedad', array_values($ids_variedades))
                ->where('disponibles', '>', 0)
                ->orderBy('id_variedad')
                ->orderBy('fecha', 'asc')
                ->orderBy('id_inventario_recepcion', 'asc')
                ->lockForUpdate()
                ->get()
                ->groupBy('id_variedad');

            foreach ($salidas_requeridas as $id_variedad => $cantidad_requerida) {
                $lotes = $inventarios_por_variedad->get($id_variedad, collect());
                $disponibles = $lotes->sum('disponibles');
                if ($disponibles < $cantidad_requerida) {
                    $variedad = Variedad::find($id_variedad);
                    DB::rollBack();
                    return [
                        'success' => false,
                        'mensaje' => '<div class="alert alert-danger text-center"><h3>No hay suficientes tallos disponibles de ' .
                            ($variedad != '' ? $variedad->nombre : 'la variedad seleccionada') .
                            '. Solicitados: ' . number_format($cantidad_requerida) .
                            ', disponibles: ' . number_format($disponibles) .
                            '. No se aplico ninguna correccion. Refresque el inventario e intentelo de nuevo.</h3></div>',
                    ];
                }
            }

            foreach ($datos as $data) {
                $id_variedad = (int)$data->id_variedad;
                $diferencia = (int)$data->diferencia;
                $variedad = Variedad::find($id_variedad);

                $id_correccion = DB::table('correccion_recepcion')->insertGetId([
                    'id_empresa' => $finca,
                    'fecha' => $request->fecha,
                    'orden' => $request->orden,
                    'id_variedad' => $id_variedad,
                    'bodega' => $request->bodega,
                    'id_usuario' => session('id_usuario'),
                    'anterior' => $data->anterior,
                    'actual' => $data->actual,
                    'diferencia' => $diferencia,
                    'fecha_registro' => date('Y-m-d H:i:s'),
                ]);

                $inventario = $inventarios_por_variedad->get($id_variedad, collect())->first();
                if ($diferencia > 0) {
                    $ingreso = new IngresoRecepcion();
                    $ingreso->id_variedad = $id_variedad;
                    $ingreso->fecha_registro = date('Y-m-d H:i:s');
                    $ingreso->fecha = $request->fecha;
                    $ingreso->tallos_x_ramo = 1;
                    $ingreso->ramos = $diferencia;
                    $ingreso->bodega = $request->bodega;
                    $ingreso->longitud = $inventario != '' ? $inventario->longitud : 60;
                    $ingreso->id_empresa = $finca;
                    $ingreso->tallos = $diferencia;
                    $ingreso->id_correccion_recepcion = $id_correccion;
                    $ingreso->save();

                    if ($inventario != '') {
                        $inventario->disponibles += $diferencia;
                        $inventario->save();
                        $id_inventario = $inventario->id_inventario_recepcion;
                    } else {
                        $model_inventario = InventarioRecepcion::where('id_variedad', $id_variedad)
                            ->where('fecha', $request->fecha)
                            ->where('tallos_x_ramo', 1)
                            ->where('bodega', $request->bodega)
                            ->where('longitud', 60)
                            ->where('id_empresa', $finca)
                            ->lockForUpdate()
                            ->first();
                        if ($model_inventario == '') {
                            $id_inventario = DB::table('inventario_recepcion')->insertGetId([
                                'id_variedad' => $id_variedad,
                                'fecha' => $request->fecha,
                                'tallos_x_ramo' => 1,
                                'ramos' => $diferencia,
                                'bodega' => $request->bodega,
                                'longitud' => 60,
                                'disponibles' => $diferencia,
                                'id_empresa' => $finca,
                            ]);
                        } else {
                            $model_inventario->disponibles += $diferencia;
                            $model_inventario->save();
                            $id_inventario = $model_inventario->id_inventario_recepcion;
                        }
                    }

                    $registro = new RegistroMovimientos();
                    $registro->id_variedad = $id_variedad;
                    $registro->id_empresa = $finca;
                    $registro->bodega = $request->bodega;
                    $registro->fecha = $request->fecha;
                    $registro->tipo = 'I';
                    $registro->concepto = 'CORRECCION';
                    $registro->numero = $request->orden;
                    $registro->cantidad = $diferencia;
                    $registro->id_usuario = session('id_usuario');
                    $registro->descripcion = 'Ingreso a traves de la Correccion de inventario';
                    $registro->id_inventario_recepcion = $id_inventario;
                    $registro->id_correccion_recepcion = $id_correccion;
                    $registro->save();
                } elseif ($diferencia < 0) {
                    $pendiente = abs($diferencia);
                    foreach ($inventarios_por_variedad->get($id_variedad, collect()) as $lote) {
                        if ($pendiente == 0) {
                            break;
                        }

                        $cantidad_lote = min((int)$lote->disponibles, $pendiente);
                        if ($cantidad_lote == 0) {
                            continue;
                        }

                        $salida = new SalidasRecepcion();
                        $salida->id_inventario_recepcion = $lote->id_inventario_recepcion;
                        $salida->id_variedad = $id_variedad;
                        $salida->cantidad = $cantidad_lote;
                        $salida->basura = 0;
                        $salida->fecha = $request->fecha;
                        $salida->id_correccion_recepcion = $id_correccion;
                        $salida->save();

                        $lote->disponibles -= $cantidad_lote;
                        $lote->save();

                        $registro = new RegistroMovimientos();
                        $registro->id_variedad = $id_variedad;
                        $registro->id_empresa = $finca;
                        $registro->bodega = $request->bodega;
                        $registro->fecha = $request->fecha;
                        $registro->tipo = 'S';
                        $registro->concepto = 'CORRECCION';
                        $registro->numero = $request->orden;
                        $registro->cantidad = $cantidad_lote;
                        $registro->id_usuario = session('id_usuario');
                        $registro->descripcion = 'Salida a traves de la Correccion de inventario';
                        $registro->id_inventario_recepcion = $lote->id_inventario_recepcion;
                        $registro->id_correccion_recepcion = $id_correccion;
                        $registro->save();

                        $pendiente -= $cantidad_lote;
                    }
                }
            }

            DB::commit();
            return [
                'success' => true,
                'mensaje' => 'Se ha <strong>CORREGIDO</strong> el inventario correctamente',
            ];
        } catch (\Throwable $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            return [
                'success' => false,
                'mensaje' => '<div class="alert alert-danger text-center">' .
                '<p> Ha ocurrido un problema al guardar la informacion al sistema</p>' .
                '<p>' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine() . '</p>'
                . '</div>',
            ];
        }
    }
}
