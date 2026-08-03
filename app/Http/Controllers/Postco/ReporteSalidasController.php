<?php

namespace yura\Http\Controllers\Postco;

use DB;
use Illuminate\Http\Request;
use yura\Http\Controllers\Controller;
use yura\Modelos\Planta;
use yura\Modelos\Submenu;

class ReporteSalidasController extends Controller
{
    public function inicio(Request $request)
    {
        $finca = getFincaActiva();
        $plantas = Planta::where('estado', 1)
            ->where('id_empresa', $finca)
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.postco.reporte_salidas.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'plantas' => $plantas,
        ]);
    }

    public function listar_reporte(Request $request)
    {
        $finca = getFincaActiva();
        $listado_ot = DB::table('salidas_recepcion as s')
            ->join('variedad as v', 'v.id_variedad', '=', 's.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->join('inventario_recepcion as i', 'i.id_inventario_recepcion', '=', 's.id_inventario_recepcion')
            ->join('orden_trabajo as ot', 'ot.id_orden_trabajo', '=', 's.id_orden_trabajo')
            ->join('detalle_cliente as cli', 'cli.id_cliente', '=', 'ot.id_cliente')
            ->select(
                's.*',
                'v.nombre as var_nombre',
                'p.nombre as pta_nombre',
                'ot.ramos',
                'cli.nombre as cli_nombre',
                'i.fecha as fecha_inventario',
                'i.tallos_x_ramo',
                'i.longitud',
                'i.bodega',
            )->distinct()
            ->whereNotNull('s.id_orden_trabajo')
            ->where('s.cantidad', '>', 0)
            ->where('cli.estado', 1)
            ->where('i.id_empresa', $finca)
            ->where('s.fecha', '>=', $request->desde)
            ->where('s.fecha', '<=', $request->hasta);
        if ($request->bodega != 'T')
            $listado_ot = $listado_ot->where('i.bodega', $request->bodega);
        if ($request->planta != '')
            $listado_ot = $listado_ot->where('v.id_planta', $request->planta);
        if ($request->variedad != '')
            $listado_ot = $listado_ot->where('i.id_variedad', $request->variedad);
        $listado_ot = $listado_ot->orderBy('s.fecha')
            ->orderBy('s.id_orden_trabajo')
            ->get()
            ->groupBy('id_orden_trabajo')
            ->map(function ($items, $ot) {
                return [
                    'ot' => $ot,
                    'fecha' => $items->first()->fecha,
                    'cli_nombre' => $items->first()->cli_nombre,
                    'ramos' => $items->first()->ramos,
                    'detalles' => $items->values(),
                ];
            })
            ->values();

        $listado_solido = DB::table('salidas_recepcion as s')
            ->join('variedad as v', 'v.id_variedad', '=', 's.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->join('inventario_recepcion as i', 'i.id_inventario_recepcion', '=', 's.id_inventario_recepcion')
            ->join('detalle_caja_proyecto as d', 'd.id_detalle_caja_proyecto', '=', 's.id_detalle_caja_proyecto')
            ->join('caja_proyecto as c', 'c.id_caja_proyecto', '=', 'd.id_caja_proyecto')
            ->join('proyecto as pr', 'pr.id_proyecto', '=', 'c.id_proyecto')
            ->join('detalle_cliente as cli', 'cli.id_cliente', '=', 'pr.id_cliente')
            ->select(
                's.*',
                'v.nombre as var_nombre',
                'p.nombre as pta_nombre',
                'd.ramos_x_caja',
                'c.cantidad as piezas',
                'c.tipo_caja',
                'cli.nombre as cli_nombre',
                'i.fecha as fecha_inventario',
                'i.tallos_x_ramo',
                'i.longitud',
                'i.bodega',
            )->distinct()
            ->whereNotNull('s.id_detalle_caja_proyecto')
            ->where('s.cantidad', '>', 0)
            ->where('cli.estado', 1)
            ->where('i.id_empresa', $finca)
            ->where('s.fecha', '>=', $request->desde)
            ->where('s.fecha', '<=', $request->hasta);
        if ($request->bodega != 'T')
            $listado_solido = $listado_solido->where('i.bodega', $request->bodega);
        if ($request->planta != '')
            $listado_solido = $listado_solido->where('v.id_planta', $request->planta);
        if ($request->variedad != '')
            $listado_solido = $listado_solido->where('i.id_variedad', $request->variedad);
        $listado_solido = $listado_solido->orderBy('s.fecha')
            ->orderBy('p.nombre')
            ->orderBy('v.nombre')
            ->get()
            ->groupBy('id_detalle_caja_proyecto')
            ->map(function ($items, $det_caja) {
                return [
                    'det_caja' => $det_caja,
                    'pta_nombre' => $items->first()->pta_nombre,
                    'var_nombre' => $items->first()->var_nombre,
                    'fecha' => $items->first()->fecha,
                    'cli_nombre' => $items->first()->cli_nombre,
                    'ramos_x_caja' => $items->first()->ramos_x_caja,
                    'piezas' => $items->first()->piezas,
                    'tipo_caja' => $items->first()->tipo_caja,
                    'detalles' => $items->values(),
                ];
            })
            ->values();

        $listado_movimientos = DB::table('salidas_recepcion as s')
            ->join('variedad as v', 'v.id_variedad', '=', 's.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->join('inventario_recepcion as i', 'i.id_inventario_recepcion', '=', 's.id_inventario_recepcion')
            ->select(
                's.*',
                'v.nombre as var_nombre',
                'p.nombre as pta_nombre',
                'i.fecha as fecha_inventario',
                'i.tallos_x_ramo',
                'i.longitud',
                'i.bodega',
            )->distinct()
            ->whereNotNull('s.cambio_bodega')
            ->where('s.cantidad', '>', 0)
            ->where('i.id_empresa', $finca)
            ->where('s.fecha', '>=', $request->desde)
            ->where('s.fecha', '<=', $request->hasta);
        if ($request->bodega != 'T')
            $listado_movimientos = $listado_movimientos->where('i.bodega', $request->bodega);
        if ($request->planta != '')
            $listado_movimientos = $listado_movimientos->where('v.id_planta', $request->planta);
        if ($request->variedad != '')
            $listado_movimientos = $listado_movimientos->where('i.id_variedad', $request->variedad);
        $listado_movimientos = $listado_movimientos->orderBy('s.fecha')
            ->orderBy('p.nombre')
            ->orderBy('v.nombre')
            ->get();

        $listado_orden_basura = DB::table('salidas_recepcion as s')
            ->join('variedad as v', 'v.id_variedad', '=', 's.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->join('inventario_recepcion as i', 'i.id_inventario_recepcion', '=', 's.id_inventario_recepcion')
            ->join('motivo_baja as m', 'm.id_motivo_baja', '=', 's.id_motivo_baja')
            ->select(
                's.*',
                'v.nombre as var_nombre',
                'p.nombre as pta_nombre',
                'm.nombre as motivo_nombre',
                'i.fecha as fecha_inventario',
                'i.tallos_x_ramo',
                'i.longitud',
                'i.bodega',
            )->distinct()
            ->whereNotNull('s.orden_basura')
            ->where('s.basura', '>', 0)
            ->where('s.estado_orden_basura', 1)
            ->where('i.id_empresa', $finca)
            ->where('s.fecha', '>=', $request->desde)
            ->where('s.fecha', '<=', $request->hasta);
        if ($request->bodega != 'T')
            $listado_orden_basura = $listado_orden_basura->where('i.bodega', $request->bodega);
        if ($request->planta != '')
            $listado_orden_basura = $listado_orden_basura->where('v.id_planta', $request->planta);
        if ($request->variedad != '')
            $listado_orden_basura = $listado_orden_basura->where('i.id_variedad', $request->variedad);
        $listado_orden_basura = $listado_orden_basura->orderBy('s.orden_basura')
            ->get()
            ->groupBy('orden_basura')
            ->map(function ($items, $orden) {
                return [
                    'orden' => $orden,
                    'fecha' => $items->first()->fecha,
                    'detalles' => $items->values(),
                ];
            })
            ->values();


        return view('adminlte.gestion.postco.reporte_salidas.partials.listado', [
            'listado_ot' => $listado_ot,
            'listado_solido' => $listado_solido,
            'listado_movimientos' => $listado_movimientos,
            'listado_orden_basura' => $listado_orden_basura,
        ]);
    }
}
