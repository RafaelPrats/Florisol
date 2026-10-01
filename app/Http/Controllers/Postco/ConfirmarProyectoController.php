<?php

namespace yura\Http\Controllers\Postco;

use DB;
use Illuminate\Http\Request;
use yura\Http\Controllers\Controller;
use yura\Modelos\DetalleProyectoConfirmacion;
use yura\Modelos\FincaProveedor;
use yura\Modelos\Proyecto;
use yura\Modelos\ProyectoConfirmacion;
use yura\Modelos\Submenu;

class ConfirmarProyectoController extends Controller
{
    public function inicio(Request $request)
    {
        $proyecto = isset($request->p) ? $request->p : '';

        $fincas = DB::table('usuario_proveedor as up')
            ->join('finca_proveedor as f', 'f.id_finca_proveedor', '=', 'up.id_finca_proveedor')
            ->select('up.id_finca_proveedor', 'f.nombre')->distinct()
            ->where('f.estado', 1)
            ->where('up.id_usuario', session('id_usuario'));
        if (isset($request->f))
            $fincas = $fincas->where('up.id_finca_proveedor', $request->f);
        $fincas = $fincas->orderBy('f.nombre')
            ->get();
        return view('adminlte.gestion.postco.confirmar_proyecto.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::where('url', '=', explode('/', $request->getRequestUri())[1])->first(),
            'proyecto' => $proyecto,
            'fincas' => $fincas,
        ]);
    }

    public function listar_reporte(Request $request)
    {
        $finca_proveedor = FincaProveedor::find($request->id_finca);
        $proyecto = isset($request->id_proyecto) ? Proyecto::find($request->id_proyecto) : '';
        $mis_plantas = DB::table('planta_finca')
            ->where('id_finca_proveedor', $request->id_finca)
            ->pluck('id_planta')
            ->toArray();
        $query = DB::table('caja_proyecto as cp')
            ->join('detalle_caja_proyecto as dc', 'dc.id_caja_proyecto', '=', 'cp.id_caja_proyecto')
            ->join('variedad as v', 'v.id_variedad', '=', 'dc.id_variedad')
            // Planta de la variedad original
            ->leftJoin('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->leftJoin('distribucion_receta as dr', function ($join) {
                $join->on(
                    'dr.id_detalle_caja_proyecto',
                    '=',
                    'dc.id_detalle_caja_proyecto'
                );
            })
            // Variedad contenida en la receta
            ->leftJoin('variedad as vr', 'vr.id_variedad', '=', 'dr.id_variedad')
            // Planta de la variedad contenida en la receta
            ->leftJoin('planta as pr', 'pr.id_planta', '=', 'vr.id_planta')
            ->where('cp.id_proyecto', $request->id_proyecto)
            // Solo variedades cuya planta pertenece a la finca
            ->whereIn(
                DB::raw("
            CASE
                WHEN v.receta = 1 THEN vr.id_planta
                ELSE v.id_planta
            END
        "),
                $mis_plantas
            )
            ->select(
                DB::raw("
            CASE
                WHEN v.receta = 1 THEN dr.id_variedad
                ELSE dc.id_variedad
            END as id_variedad
        "),
                DB::raw("
            CASE
                WHEN v.receta = 1 THEN vr.nombre
                ELSE v.nombre
            END as var_nombre
        "),
                DB::raw("
            CASE
                WHEN v.receta = 1 THEN pr.nombre
                ELSE p.nombre
            END as pta_nombre
        "),
                DB::raw("
            SUM(
                cp.cantidad
                * dc.ramos_x_caja
                * CASE
                    WHEN v.receta = 1 THEN dr.unidades
                    ELSE dc.tallos_x_ramo
                  END
            ) as tallos
        ")
            )
            ->groupBy(
                DB::raw("
            CASE
                WHEN v.receta = 1 THEN dr.id_variedad
                ELSE dc.id_variedad
            END
        "),
                DB::raw("
            CASE
                WHEN v.receta = 1 THEN vr.nombre
                ELSE v.nombre
            END
        "),
                DB::raw("
            CASE
                WHEN v.receta = 1 THEN pr.nombre
                ELSE p.nombre
            END
        ")
            )
            ->orderBy('pta_nombre')
            ->orderBy('var_nombre')
            ->get();
        $confirmacion = ProyectoConfirmacion::where('id_finca_proveedor', $request->id_finca)
            ->where('id_proyecto', $request->id_proyecto)
            ->first();
        if ($confirmacion != '') {
            $det_confirmacion = DB::table('detalle_proyecto_confirmacion as det')
                ->join('variedad as v', 'v.id_variedad', '=', 'det.id_variedad')
                ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                ->select(
                    'det.id_variedad',
                    'v.id_variedad',
                    'v.nombre as var_nombre',
                    'p.nombre as pta_nombre',
                    'det.confirmados',
                    'det.tallos_pedido',
                )->distinct()
                ->where('id_proyecto_confirmacion', $confirmacion->id_proyecto_confirmacion)
                ->orderBy('v.nombre')
                ->get();
        } else {
            $det_confirmacion = [];
        }

        $listado = collect($query)->map(function ($item) use ($det_confirmacion) {

            $confirmacion = collect($det_confirmacion)
                ->firstWhere('id_variedad', $item->id_variedad);

            $item->confirmados = $confirmacion ? $confirmacion->confirmados : 0;

            $item->tallos_pedido = $confirmacion
                ? $confirmacion->tallos_pedido
                : $item->tallos;

            // Identificar el origen de la variedad
            $item->tipo_variedad = 'PEDIDO';

            return $item;
        });

        // Agregar variedades confirmadas que no están en el pedido
        $variedades_query = collect($query)->pluck('id_variedad')->toArray();

        $adicionales = collect($det_confirmacion)
            ->whereNotIn('id_variedad', $variedades_query)
            ->map(function ($item) {

                return (object) [
                    'id_variedad' => $item->id_variedad,
                    'var_nombre' => $item->var_nombre,
                    'pta_nombre' => $item->pta_nombre,
                    'tallos' => $item->confirmados,
                    'confirmados' => $item->confirmados,
                    'tallos_pedido' => $item->tallos_pedido,

                    // Identificar el origen de la variedad
                    'tipo_variedad' => 'CONFIRMACION',
                ];
            });

        // Unificar y ordenar
        $listado = $listado
            ->concat($adicionales)
            ->sortBy(function ($item) {
                return strtolower($item->pta_nombre . '|' . $item->var_nombre);
            })
            ->values();
        return view('adminlte.gestion.postco.confirmar_proyecto.partials.listado', [
            'proyecto' => $proyecto,
            'listado' => $listado,
            'finca_proveedor' => $finca_proveedor,
            'confirmacion' => $confirmacion,
            'det_confirmacion' => $det_confirmacion,
        ]);
    }

    public function store_confirmar_pedido(Request $request)
    {
        DB::beginTransaction();
        try {
            $confirmacion = ProyectoConfirmacion::where('id_proyecto', $request->proy)
                ->where('id_finca_proveedor', $request->finca)
                ->first();
            if ($confirmacion == '') {
                $confirmacion = new ProyectoConfirmacion();
                $confirmacion->id_proyecto = $request->proy;
                $confirmacion->id_finca_proveedor = $request->finca;
                $confirmacion->estado = 'P';
                $confirmacion->fecha = hoy();
                $confirmacion->id_usuario = session('id_usuario');
                $confirmacion->save();
                $confirmacion->id_proyecto_confirmacion = DB::table('proyecto_confirmacion')
                    ->select(DB::raw('max(id_proyecto_confirmacion) as id'))
                    ->get()[0]->id;

                foreach (json_decode($request->data) as $data) {
                    $detalle = new DetalleProyectoConfirmacion();
                    $detalle->id_proyecto_confirmacion = $confirmacion->id_proyecto_confirmacion;
                    $detalle->id_variedad = $data->variedad;
                    $detalle->tallos_pedido = $data->tallos;
                    $detalle->confirmados = $data->confirmar;
                    $detalle->save();
                }

                DB::commit();
                $success = true;
                $msg = 'Se ha <strong>CONFIRMADO</strong> el pedido correctamente';
            } elseif ($confirmacion->estado == 'P') {
                DetalleProyectoConfirmacion::where('id_proyecto_confirmacion', $confirmacion->id_proyecto_confirmacion)
                    ->delete();

                foreach (json_decode($request->data) as $data) {
                    $detalle = new DetalleProyectoConfirmacion();
                    $detalle->id_proyecto_confirmacion = $confirmacion->id_proyecto_confirmacion;
                    $detalle->id_variedad = $data->variedad;
                    $detalle->tallos_pedido = $data->tallos;
                    $detalle->confirmados = $data->confirmar;
                    $detalle->save();
                }

                DB::commit();
                $success = true;
                $msg = 'Se han <strong>ACTUALIZADO</strong> las cantidades correctamente';
            } else {
                DB::rollBack();
                $success = false;
                $msg = '<div class="alert alert-danger text-center">' .
                    '<h3>Ya se encuentra confirmado el pedido por esta finca</h3>' .
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
