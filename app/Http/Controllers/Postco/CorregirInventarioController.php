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
            if ($codigo != '' && $codigo->valor == $request->codigo) {
                $finca = getFincaActiva();
                foreach (json_decode($request->data) as $data) {
                    $variedad = Variedad::find($data->id_variedad);

                    $correccion = new CorreccionRecepcion();
                    $correccion->id_empresa = $finca;
                    $correccion->fecha = $request->fecha;
                    $correccion->orden = $request->orden;
                    $correccion->id_variedad = $data->id_variedad;
                    $correccion->bodega = $request->bodega;
                    $correccion->id_usuario = session('id_usuario');
                    $correccion->anterior = $data->anterior;
                    $correccion->actual = $data->actual;
                    $correccion->diferencia = $data->diferencia;
                    $correccion->fecha_registro = date('Y-m-d H:i:s');
                    $correccion->save();
                    $correccion->id_correccion_recepcion = DB::table('correccion_recepcion')
                        ->select(DB::raw('max(id_correccion_recepcion) as id'))
                        ->get()[0]->id;

                    $inventario = InventarioRecepcion::where('id_empresa', $finca)
                        ->where('disponibles', '>', 0)
                        ->where('id_variedad', $data->id_variedad)
                        ->where('bodega', $request->bodega)
                        ->orderBy('fecha', 'asc')
                        ->first();
                    if ($data->diferencia > 0) {    // ingreso
                        $ingreso = new IngresoRecepcion();
                        $ingreso->id_variedad = $data->id_variedad;
                        $ingreso->fecha_registro = date('Y-m-d H:i:s');
                        //$ingreso->fecha = $inventario != '' ? $inventario->fecha : $request->fecha;
                        $ingreso->fecha = $request->fecha;
                        $ingreso->tallos_x_ramo = 1;
                        $ingreso->ramos = $data->diferencia;
                        $ingreso->bodega = $request->bodega;
                        $ingreso->longitud = $inventario != '' ? $inventario->longitud : 60;
                        $ingreso->id_empresa = $finca;
                        $ingreso->tallos = $data->diferencia;
                        $ingreso->id_correccion_recepcion = $correccion->id_correccion_recepcion;
                        $ingreso->save();

                        if ($inventario != '') {
                            $inventario->disponibles += $data->diferencia;
                            $inventario->save();
                            $id_inventario = $inventario->id_inventario_recepcion;
                        } else {
                            $model_inventario = InventarioRecepcion::where('id_variedad', $data->id_variedad)
                                ->where('fecha', $request->fecha)
                                ->where('tallos_x_ramo', 1)
                                ->where('bodega', $request->bodega)
                                ->where('longitud', 60)
                                ->where('id_empresa', $finca)
                                ->first();
                            if ($model_inventario == '') {
                                $model_inventario = new InventarioRecepcion();
                                $model_inventario->id_variedad = $data->id_variedad;
                                $model_inventario->fecha = $request->fecha;
                                $model_inventario->tallos_x_ramo = 1;
                                $model_inventario->ramos = $data->diferencia;
                                $model_inventario->bodega = $request->bodega;
                                $model_inventario->longitud = 60;
                                $model_inventario->disponibles = $data->diferencia;
                                $model_inventario->id_empresa = $finca;
                                $model_inventario->save();
                                $id_inventario = DB::table('inventario_recepcion')
                                    ->select(DB::raw('max(id_inventario_recepcion) as id'))
                                    ->get()[0]->id;
                            } else {
                                $model_inventario->disponibles += $data->diferencia;
                                $model_inventario->save();
                                $id_inventario = $model_inventario->id_inventario_recepcion;
                            }
                        }

                        $registro = new RegistroMovimientos();
                        $registro->id_variedad = $data->id_variedad;
                        $registro->id_empresa = $finca;
                        $registro->bodega = $request->bodega;
                        $registro->fecha = $request->fecha;
                        $registro->tipo = 'I';
                        $registro->concepto = 'CORRECCION';
                        $registro->numero = $request->orden;
                        $registro->cantidad = $data->diferencia;
                        $registro->id_usuario = session('id_usuario');
                        $registro->descripcion = 'Ingreso a traves de la Correccion de inventario';
                        // campos de relacion
                        $registro->id_inventario_recepcion = $id_inventario;
                        $registro->id_correccion_recepcion = $correccion->id_correccion_recepcion;
                        $registro->save();
                    } else {    // salida
                        $salidas = new SalidasRecepcion();
                        $salidas->id_inventario_recepcion = $inventario->id_inventario_recepcion;
                        $salidas->id_variedad = $data->id_variedad;
                        $salidas->cantidad = abs($data->diferencia);
                        $salidas->basura = 0;
                        $salidas->fecha = $request->fecha;
                        $salidas->id_correccion_recepcion = $correccion->id_correccion_recepcion;
                        $salidas->save();

                        if ($inventario != '' && $inventario->disponibles >= abs($data->diferencia)) {
                            $inventario->disponibles -= abs($data->diferencia);
                            $inventario->save();

                            $registro = new RegistroMovimientos();
                            $registro->id_variedad = $data->id_variedad;
                            $registro->id_empresa = $finca;
                            $registro->bodega = $request->bodega;
                            $registro->fecha = $request->fecha;
                            $registro->tipo = 'S';
                            $registro->concepto = 'CORRECCION';
                            $registro->numero = $request->orden;
                            $registro->cantidad = abs($data->diferencia);
                            $registro->id_usuario = session('id_usuario');
                            $registro->descripcion = 'Salida a traves de la Correccion de inventario';
                            // campos de relacion
                            $registro->id_inventario_recepcion = $inventario->id_inventario_recepcion;
                            $registro->id_correccion_recepcion = $correccion->id_correccion_recepcion;
                            $registro->save();
                        } else {
                            DB::rollBack();
                            $success = false;
                            $msg = '<div class="alert alert-danger text-center">' .
                                '<h3>Ya no hay flor disponible de ' . $variedad->nombre . '. Refresque el inventario e intentelo de nuevo</h3>' .
                                '</div>';
                        }
                    }
                }

                DB::commit();
                $success = true;
                $msg = 'Se ha <strong>CORREGIDO</strong> el inventario correctamente';
            } else {
                DB::rollBack();
                $success = false;
                $msg = '<div class="alert alert-danger text-center">' .
                    '<h3>El codigo de autorizacion es incorrecto</h3>' .
                    '</div>';
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $success = false;
            $msg = '<div class="alert alert-danger text-center">' .
                '<p> Ha ocurrido un problema al guardar la informacion al sistema</p>' .
                '<p>' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine() . '</p>'
                . '</div>';
        }

        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }
}
