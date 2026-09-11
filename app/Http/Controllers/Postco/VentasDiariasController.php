<?php

namespace yura\Http\Controllers\Postco;

use DB;
use Illuminate\Http\Request;
use yura\Http\Controllers\Controller;
use yura\Modelos\Planta;
use yura\Modelos\Submenu;

class VentasDiariasController extends Controller
{
    public function inicio(Request $request)
    {
        $finca = getFincaActiva();
        $plantas = Planta::where('estado', 1)
            ->where('id_empresa', $finca)
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.postco.ventas_diarias.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'plantas' => $plantas,
        ]);
    }

    public function listar_reporte(Request $request)
    {
        $finca = getFincaActiva();
        $fechas = [];
        $fecha = $request->desde;
        while ($fecha <= $request->hasta) {
            $fechas[] = $fecha;
            $fecha = opDiasFecha('+', 1, $fecha);
        }
        $variedades_pedidos = DB::table('proyecto as p')
            ->join('caja_proyecto as cp', 'cp.id_proyecto', '=', 'p.id_proyecto')
            ->join('detalle_caja_proyecto as dc', 'dc.id_caja_proyecto', '=', 'cp.id_caja_proyecto')
            ->join('variedad as v', 'v.id_variedad', '=', 'dc.id_variedad')
            ->join('planta as pta', 'pta.id_planta', '=', 'v.id_planta')
            ->join('segmento as seg', 'seg.nombre', '=', 'p.segmento')
            ->select(
                'v.id_planta',
                'dc.id_variedad',
                'pta.nombre as pta_nombre',
                'v.nombre as var_nombre'
            )->distinct()
            ->where('p.estado', 1)
            ->where('p.id_empresa', $finca)
            ->whereIn('p.fecha', $fechas);
        if ($request->bodega != '')
            $variedades_pedidos = $variedades_pedidos->where('seg.bodega', $request->bodega);
        if ($request->planta != '')
            $variedades_pedidos = $variedades_pedidos->where('v.id_planta', $request->planta);
        if ($request->variedad != '')
            $variedades_pedidos = $variedades_pedidos->where('dc.id_variedad', $request->variedad);
        $variedades_pedidos = $variedades_pedidos->get();

        $variedades_ot = DB::table('salidas_recepcion as s')
            ->join('variedad as v', 'v.id_variedad', '=', 's.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->join('inventario_recepcion as i', 'i.id_inventario_recepcion', '=', 's.id_inventario_recepcion')
            ->select(
                'v.id_planta',
                's.id_variedad',
                'p.nombre as pta_nombre',
                'v.nombre as var_nombre'
            )->distinct()
            ->whereNotNull('s.id_orden_trabajo')
            ->where('s.cantidad', '>', 0)
            ->where('i.id_empresa', $finca)
            ->whereIn('s.fecha', $fechas)
            ->whereNotIn('s.id_variedad', $variedades_pedidos->pluck('id_variedad')->toArray());
        if ($request->bodega != '')
            $variedades_ot = $variedades_ot->where('i.bodega', $request->bodega);
        if ($request->planta != '')
            $variedades_ot = $variedades_ot->where('v.id_planta', $request->planta);
        if ($request->variedad != '')
            $variedades_ot = $variedades_ot->where('s.id_variedad', $request->variedad);
        $variedades_ot = $variedades_ot->get();

        $variedades_ot_nacional = DB::table('salidas_recepcion as s')
            ->join('variedad as v', 'v.id_variedad', '=', 's.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->join('inventario_recepcion as i', 'i.id_inventario_recepcion', '=', 's.id_inventario_recepcion')
            ->select(
                'v.id_planta',
                's.id_variedad',
                'p.nombre as pta_nombre',
                'v.nombre as var_nombre'
            )->distinct()
            ->whereNotNull('s.id_ot_nacional')
            ->where('s.cantidad', '>', 0)
            ->where('i.id_empresa', $finca)
            ->whereIn('s.fecha', $fechas)
            ->whereNotIn('s.id_variedad', $variedades_pedidos->pluck('id_variedad')->toArray())
            ->whereNotIn('s.id_variedad', $variedades_ot->pluck('id_variedad')->toArray());
        if ($request->bodega != '')
            $variedades_ot_nacional = $variedades_ot_nacional->where('i.bodega', $request->bodega);
        if ($request->planta != '')
            $variedades_ot_nacional = $variedades_ot_nacional->where('v.id_planta', $request->planta);
        if ($request->variedad != '')
            $variedades_ot_nacional = $variedades_ot_nacional->where('s.id_variedad', $request->variedad);
        $variedades_ot_nacional = $variedades_ot_nacional->get();

        $variedades_solido = DB::table('salidas_recepcion as s')
            ->join('variedad as v', 'v.id_variedad', '=', 's.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->join('inventario_recepcion as i', 'i.id_inventario_recepcion', '=', 's.id_inventario_recepcion')
            ->select(
                'v.id_planta',
                's.id_variedad',
                'p.nombre as pta_nombre',
                'v.nombre as var_nombre'
            )->distinct()
            ->whereNotNull('s.id_detalle_caja_proyecto')
            ->whereNull('s.id_ot_nacional')
            ->where('s.cantidad', '>', 0)
            ->where('i.id_empresa', $finca)
            ->whereIn('s.fecha', $fechas)
            ->whereNotIn('s.id_variedad', $variedades_pedidos->pluck('id_variedad')->toArray())
            ->whereNotIn('s.id_variedad', $variedades_ot->pluck('id_variedad')->toArray())
            ->whereNotIn('s.id_variedad', $variedades_ot_nacional->pluck('id_variedad')->toArray());
        if ($request->bodega != '')
            $variedades_solido = $variedades_solido->where('i.bodega', $request->bodega);
        if ($request->planta != '')
            $variedades_solido = $variedades_solido->where('v.id_planta', $request->planta);
        if ($request->variedad != '')
            $variedades_solido = $variedades_solido->where('s.id_variedad', $request->variedad);
        $variedades_solido = $variedades_solido
            ->get();

        $variedades = $variedades_pedidos
            ->merge($variedades_ot)
            ->merge($variedades_ot_nacional)
            ->merge($variedades_solido)
            ->sortBy('pta_nombre')
            ->sortBy('var_nombre');

        foreach ($variedades as $var) {
            $ventas = DB::table('proyecto as p')
                ->join('caja_proyecto as cp', 'cp.id_proyecto', '=', 'p.id_proyecto')
                ->join('detalle_caja_proyecto as dc', 'dc.id_caja_proyecto', '=', 'cp.id_caja_proyecto')
                ->join('segmento as seg', 'seg.nombre', '=', 'p.segmento')
                ->select(
                    'p.fecha',
                    DB::raw('sum(cp.cantidad * dc.ramos_x_caja * dc.precio) as monto')
                )
                ->where('p.estado', 1)
                ->where('p.id_empresa', $finca)
                ->whereIn('p.fecha', $fechas)
                ->where('dc.id_variedad', $var->id_variedad);
            if ($request->bodega != '')
                $ventas = $ventas->where('seg.bodega', $request->bodega);
            $ventas = $ventas->groupBy('fecha')
                ->orderBy('fecha')
                ->get();

            $salidas_ot = DB::table('salidas_recepcion as s')
                ->join('variedad as v', 'v.id_variedad', '=', 's.id_variedad')
                ->join('inventario_recepcion as i', 'i.id_inventario_recepcion', '=', 's.id_inventario_recepcion')
                ->select(
                    's.fecha',
                    DB::raw('sum(s.cantidad) as cantidad')
                )
                ->whereNotNull('s.id_orden_trabajo')
                ->where('s.cantidad', '>', 0)
                ->where('i.id_empresa', $finca)
                ->whereIn('s.fecha', $fechas)
                ->where('s.id_variedad', $var->id_variedad);
            if ($request->bodega != '')
                $salidas_ot = $salidas_ot->where('i.bodega', $request->bodega);
            $salidas_ot = $salidas_ot->groupBy('fecha')
                ->orderBy('fecha')
                ->get();

            $salidas_ot_nacional = DB::table('salidas_recepcion as s')
                ->join('variedad as v', 'v.id_variedad', '=', 's.id_variedad')
                ->join('inventario_recepcion as i', 'i.id_inventario_recepcion', '=', 's.id_inventario_recepcion')
                ->select(
                    's.fecha',
                    DB::raw('sum(s.cantidad) as cantidad')
                )
                ->whereNotNull('s.id_ot_nacional')
                ->where('s.cantidad', '>', 0)
                ->where('i.id_empresa', $finca)
                ->whereIn('s.fecha', $fechas)
                ->where('s.id_variedad', $var->id_variedad);
            if ($request->bodega != '')
                $salidas_ot_nacional = $salidas_ot_nacional->where('i.bodega', $request->bodega);
            $salidas_ot_nacional = $salidas_ot_nacional->groupBy('fecha')
                ->orderBy('fecha')
                ->get();

            $salidas_solido = DB::table('salidas_recepcion as s')
                ->join('variedad as v', 'v.id_variedad', '=', 's.id_variedad')
                ->join('inventario_recepcion as i', 'i.id_inventario_recepcion', '=', 's.id_inventario_recepcion')
                ->select(
                    's.fecha',
                    DB::raw('sum(s.cantidad) as cantidad')
                )
                ->whereNotNull('s.id_detalle_caja_proyecto')
                ->whereNull('s.id_ot_nacional')
                ->where('s.cantidad', '>', 0)
                ->where('i.id_empresa', $finca)
                ->whereIn('s.fecha', $fechas)
                ->where('s.id_variedad', $var->id_variedad);
            if ($request->bodega != '')
                $salidas_solido = $salidas_solido->where('i.bodega', $request->bodega);
            $salidas_solido = $salidas_solido->groupBy('fecha')
                ->orderBy('fecha')
                ->get();

            $var->ventas = $ventas;
            $var->salidas_ot = $salidas_ot;
            $var->salidas_ot_nacional = $salidas_ot_nacional;
            $var->salidas_solido = $salidas_solido;
        }
        return view('adminlte.gestion.postco.ventas_diarias.partials.listado', [
            'fechas' => $fechas,
            'listado' => $variedades,
        ]);
    }
}
