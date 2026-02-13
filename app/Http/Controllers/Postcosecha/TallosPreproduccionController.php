<?php

namespace yura\Http\Controllers\Postcosecha;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use yura\Http\Controllers\Controller;
use yura\Modelos\Submenu;
use yura\Modelos\Variedad;

class TallosPreproduccionController extends Controller
{
    public function inicio(Request $request)
    {
        $variedades = Variedad::where('estado', 1)
            ->where('receta', 1)
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.postcocecha.tallos_preproduccion.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'variedades' => $variedades,
        ]);
    }

    public function listar_reporte(Request $request)
    {
        $fecha = $request->fecha;
        $combinaciones = DB::table('detalle_import_pedido as d')
            ->join('import_pedido as p', 'p.id_import_pedido', '=', 'd.id_import_pedido')
            ->join('variedad as v', 'v.id_variedad', '=', 'd.id_variedad')
            ->select('d.id_variedad', 'd.longitud', 'v.nombre', 'v.siglas')->distinct()
            ->where('p.estado', 1)
            ->where('p.fecha', $fecha);
        if ($request->variedad != 'T')
            $combinaciones = $combinaciones->where('d.id_variedad', $request->variedad);
        $combinaciones = $combinaciones->get();

        $listado = [];
        $detalles = [];
        foreach ($combinaciones as $item) {
            $venta = DB::table('import_pedido as p')
                ->join('detalle_import_pedido as d', 'd.id_import_pedido', '=', 'p.id_import_pedido')
                ->join('distribucion_receta as r', 'r.id_detalle_import_pedido', '=', 'd.id_detalle_import_pedido')
                ->select(DB::raw('sum(d.ramos * d.caja * r.unidades) as cantidad'))
                ->where('p.fecha', $fecha)
                ->where('d.id_variedad', $item->id_variedad)
                ->where('d.longitud', $item->longitud)
                ->where('p.estado', 1)
                ->get()[0]->cantidad;

            $pedidos = DB::table('detalle_import_pedido as d')
                ->join('import_pedido as p', 'p.id_import_pedido', '=', 'd.id_import_pedido')
                ->join('detalle_cliente as dc', 'dc.id_cliente', '=', 'p.id_cliente')
                ->select(
                    'p.id_import_pedido',
                    'd.id_detalle_import_pedido',
                    'd.bloquear_distribucion',
                    'd.ramos_armados',
                    'd.ejecutado',
                    'p.codigo',
                    'p.codigo_ref',
                    'p.id_cliente',
                    'dc.nombre as nombre_cliente'
                )->distinct()
                ->where('p.fecha', $fecha)
                ->where('d.id_variedad', $item->id_variedad)
                ->where('d.longitud', $item->longitud)
                ->where('dc.estado', 1)
                ->where('p.estado', 1)
                ->orderBy('dc.nombre')
                ->get();
            $ramos_armados = 0;
            foreach ($pedidos as $p) {
                $distribucion = DB::table('detalle_import_pedido as d')
                    ->join('import_pedido as p', 'p.id_import_pedido', '=', 'd.id_import_pedido')
                    ->join('distribucion_receta as r', 'r.id_detalle_import_pedido', '=', 'd.id_detalle_import_pedido')
                    ->join('variedad as v', 'v.id_variedad', '=', 'r.id_variedad')
                    ->join('planta as pta', 'pta.id_planta', '=', 'v.id_planta')
                    ->select(
                        'r.id_variedad',
                        'v.nombre as nombre_variedad',
                        'r.unidades',
                        'v.id_planta',
                        'pta.nombre as nombre_planta'
                    )->distinct()
                    ->where('d.id_import_pedido', $p->id_import_pedido)
                    ->where('d.id_detalle_import_pedido', $p->id_detalle_import_pedido)
                    ->where('d.id_variedad', $item->id_variedad)
                    ->where('d.longitud', $item->longitud)
                    ->orderBy('pta.nombre')
                    ->orderBy('v.nombre')
                    ->get();
                $tallos_x_ramo = 0;
                foreach ($distribucion as $pos_d => $dist) {
                    $tallos_x_ramo += $dist->unidades;
                }

                $ramos_orden = DB::table('orden_trabajo')
                    ->select(
                        DB::raw('sum(ramos) as ramos'),
                        DB::raw('sum(ramos_armados) as ramos_armados'),
                    )
                    ->where('id_detalle_import_pedido', $p->id_detalle_import_pedido)
                    ->where('longitud', $item->longitud)
                    ->get()[0];
                $ramos_armados += ($ramos_orden->ramos_armados + $p->ramos_armados) * $tallos_x_ramo;

                /* DETALLES */
                $ramos_venta = DB::table('import_pedido as p')
                    ->join('detalle_import_pedido as d', 'd.id_import_pedido', '=', 'p.id_import_pedido')
                    ->select(DB::raw('sum(d.ramos * d.caja) as cantidad'))
                    ->where('d.id_import_pedido', $p->id_import_pedido)
                    ->where('d.id_detalle_import_pedido', $p->id_detalle_import_pedido)
                    ->where('d.id_variedad', $item->id_variedad)
                    ->where('d.longitud', $item->longitud)
                    ->get()[0]->cantidad;
                $resumen_orden = DB::table('orden_trabajo')
                    ->select(
                        DB::raw('sum(ramos) as ramos'),
                        DB::raw('sum(ramos_armados) as ramos_armados'),
                    )
                    ->where('id_detalle_import_pedido', $p->id_detalle_import_pedido)
                    ->where('longitud', $item->longitud)
                    ->get()[0];
                $ramos_orden = $resumen_orden->ramos;
                $ramos_armados_orden = $resumen_orden->ramos_armados;
                $ramos_pre_ot = DB::table('pre_orden_trabajo')
                    ->select(
                        DB::raw('sum(ramos) as ramos')
                    )
                    ->where('id_detalle_import_pedido', $p->id_detalle_import_pedido)
                    ->where('longitud', $item->longitud)
                    ->where('estado', 1)
                    ->get()[0];
                $distribucion = DB::table('detalle_import_pedido as d')
                    ->join('import_pedido as p', 'p.id_import_pedido', '=', 'd.id_import_pedido')
                    ->join('distribucion_receta as r', 'r.id_detalle_import_pedido', '=', 'd.id_detalle_import_pedido')
                    ->join('variedad as v', 'v.id_variedad', '=', 'r.id_variedad')
                    ->join('planta as pta', 'pta.id_planta', '=', 'v.id_planta')
                    ->select(
                        'r.id_variedad',
                        'v.nombre as nombre_variedad',
                        'r.unidades',
                        'v.id_planta',
                        'pta.nombre as nombre_planta'
                    )->distinct()
                    ->where('d.id_import_pedido', $p->id_import_pedido)
                    ->where('d.id_detalle_import_pedido', $p->id_detalle_import_pedido)
                    ->where('d.id_variedad', $item->id_variedad)
                    ->where('d.longitud', $item->longitud)
                    ->orderBy('pta.nombre')
                    ->orderBy('v.nombre')
                    ->get();
                $detalles[] = [
                    'pedido' => $p,
                    'ramos_venta' => $ramos_venta,
                    'ramos_orden' => $ramos_orden,
                    'ramos_armados_orden' => $ramos_armados_orden,
                    'ramos_pre_ot' => $ramos_pre_ot->ramos,
                    'distribucion' => $distribucion
                ];
            }
            $listado[] = [
                'item' => $item,
                'venta' => $venta,
                'ramos_armados' => $ramos_armados,
            ];
        }
        $resumen_variedades = [];
        foreach ($detalles as $pos_p => $item) {
            foreach ($item['distribucion'] as $pos_d => $dist) {
                if ($pos_d == 0) {
                    $armados = $item['ramos_armados_orden'] + $item['pedido']->ramos_armados;
                    $faltantes = $item['ramos_venta'] - $armados;
                }
                if ($faltantes > 0) {
                    $pos_en_resumen = -1;
                    foreach ($resumen_variedades as $pos => $r) {
                        if ($r['variedad']->id_variedad == $dist->id_variedad) {
                            $pos_en_resumen = $pos;
                        }
                    }
                    if ($pos_en_resumen != -1) {
                        $resumen_variedades[$pos_en_resumen]['faltantes'] += $faltantes * $dist->unidades;
                    } else {
                        $resumen_variedades[] = [
                            'variedad' => $dist,
                            'faltantes' => $faltantes * $dist->unidades,
                        ];
                    }
                }
            }
        }

        return view('adminlte.gestion.postcocecha.tallos_preproduccion.partials.listado', [
            'listado' => $listado,
            'fecha' => $fecha,
            'resumen_variedades' => $resumen_variedades,
        ]);
    }

    public function modal_combinacion(Request $request)
    {
        $finca = getFincaActiva();
        $pedidos = DB::table('detalle_import_pedido as d')
            ->join('import_pedido as p', 'p.id_import_pedido', '=', 'd.id_import_pedido')
            ->join('detalle_cliente as dc', 'dc.id_cliente', '=', 'p.id_cliente')
            ->select(
                'p.id_import_pedido',
                'd.id_detalle_import_pedido',
                'd.bloquear_distribucion',
                'd.ramos_armados',
                'd.ejecutado',
                'p.codigo',
                'p.codigo_ref',
                'p.id_cliente',
                'dc.nombre as nombre_cliente'
            )->distinct()
            ->where('p.fecha', $request->fecha)
            ->where('d.id_variedad', $request->variedad)
            ->where('d.longitud', $request->longitud)
            ->where('p.id_empresa', $finca)
            ->where('dc.estado', 1)
            ->where('p.estado', 1)
            ->orderBy('dc.nombre')
            ->get();

        $listado = [];
        foreach ($pedidos as $p) {
            $ramos_venta = DB::table('import_pedido as p')
                ->join('detalle_import_pedido as d', 'd.id_import_pedido', '=', 'p.id_import_pedido')
                ->select(DB::raw('sum(d.ramos * d.caja) as cantidad'))
                ->where('d.id_import_pedido', $p->id_import_pedido)
                ->where('d.id_detalle_import_pedido', $p->id_detalle_import_pedido)
                ->where('d.id_variedad', $request->variedad)
                ->where('d.longitud', $request->longitud)
                ->get()[0]->cantidad;
            $resumen_orden = DB::table('orden_trabajo')
                ->select(
                    DB::raw('sum(ramos) as ramos'),
                    DB::raw('sum(ramos_armados) as ramos_armados'),
                )
                ->where('id_detalle_import_pedido', $p->id_detalle_import_pedido)
                ->where('longitud', $request->longitud)
                ->get()[0];
            $ramos_orden = $resumen_orden->ramos;
            $ramos_armados_orden = $resumen_orden->ramos_armados;
            $ramos_pre_ot = DB::table('pre_orden_trabajo')
                ->select(
                    DB::raw('sum(ramos) as ramos')
                )
                ->where('id_detalle_import_pedido', $p->id_detalle_import_pedido)
                ->where('longitud', $request->longitud)
                ->where('estado', 1)
                ->get()[0];
            $distribucion = DB::table('detalle_import_pedido as d')
                ->join('import_pedido as p', 'p.id_import_pedido', '=', 'd.id_import_pedido')
                ->join('distribucion_receta as r', 'r.id_detalle_import_pedido', '=', 'd.id_detalle_import_pedido')
                ->join('variedad as v', 'v.id_variedad', '=', 'r.id_variedad')
                ->join('planta as pta', 'pta.id_planta', '=', 'v.id_planta')
                ->select(
                    'r.id_variedad',
                    'v.nombre as nombre_variedad',
                    'r.unidades',
                    'v.id_planta',
                    'pta.nombre as nombre_planta'
                )->distinct()
                ->where('d.id_import_pedido', $p->id_import_pedido)
                ->where('d.id_detalle_import_pedido', $p->id_detalle_import_pedido)
                ->where('d.id_variedad', $request->variedad)
                ->where('d.longitud', $request->longitud)
                ->orderBy('pta.nombre')
                ->orderBy('v.nombre')
                ->get();
            $listado[] = [
                'pedido' => $p,
                'ramos_venta' => $ramos_venta,
                'ramos_orden' => $ramos_orden,
                'ramos_armados_orden' => $ramos_armados_orden,
                'ramos_pre_ot' => $ramos_pre_ot->ramos,
                'distribucion' => $distribucion
            ];
        }

        $usuarios = DB::table('permiso_accion')
            ->where('accion', 'CONFIRMAR_PREPRODUCCION')
            ->get()
            ->pluck('id_usuario')
            ->toArray();
        return view('adminlte.gestion.postcocecha.tallos_preproduccion.partials.modal_combinacion', [
            'listado' => $listado,
            'variedad' => Variedad::find($request->variedad),
            'fecha' => $request->fecha,
            'fecha_trabajo' => $request->fecha_trabajo,
            'longitud' => $request->longitud,
            'usuarios' => $usuarios,
        ]);
    }
}
