<?php

namespace yura\Http\Controllers\Postco;

use DB;
use Illuminate\Http\Request;
use yura\Http\Controllers\Controller;
use yura\Modelos\Planta;
use yura\Modelos\Submenu;
use yura\Modelos\Variedad;

class KardexController extends Controller
{
    public function inicio(Request $request)
    {
        $finca = getFincaActiva();
        $plantas = Planta::where('estado', 1)
            ->where('id_empresa', $finca)
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.postco.kardex.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'plantas' => $plantas,
        ]);
    }

    public function listar_reporte(Request $request)
    {
        $finca = getFincaActiva();
        $variedad = Variedad::find($request->variedad);
        $desde = $request->desde >= '2026-09-11' ? $request->desde : '2026-09-11';
        $hasta = $request->hasta;
        // Calcular saldo inicial
        $ingreso_inicial = DB::table('ingreso_recepcion')
            ->select(DB::raw('sum(tallos) as cantidad'))
            ->where('id_variedad', $request->variedad)
            ->where('id_empresa', $finca)
            ->where('bodega', $request->bodega)
            ->where('fecha', '<', $desde)
            ->where('fecha', '>=', '2026-09-11')
            ->get()[0]->cantidad;
        $salida_inicial = DB::table('salidas_recepcion as s')
            ->join('inventario_recepcion as i', 'i.id_inventario_recepcion', '=', 's.id_inventario_recepcion')
            ->select(DB::raw('sum(s.cantidad + s.basura) as cantidad'))
            ->where('s.id_variedad', $request->variedad)
            ->where('i.id_empresa', $finca)
            ->where('i.bodega', $request->bodega)
            ->where('s.fecha', '<', $desde)
            ->where('s.fecha', '>=', '2026-09-11')
            ->where('s.fecha_registro', '>=', '2026-09-11')
            ->get()[0]->cantidad;
        $saldo_inicial = $ingreso_inicial - $salida_inicial;
        // INGRESOS
        $ingresos_documento = DB::table('ingreso_recepcion as i')
            ->join('api_store_cajas as api', 'api.id_api_store_cajas', '=', 'i.id_api_store_cajas')
            ->select(
                DB::raw("'INGRESO' as tipo"),
                'api.documento as documento',
                'i.fecha',
                DB::raw("DATE_FORMAT(i.fecha_registro, '%Y-%m-%d %H:%i') as fecha_registro"),
                DB::raw('sum(i.tallos) as cantidad'),
                DB::raw("'INTERNO' as concepto"),
                DB::raw("NULL as detalle")
            )
            ->where('i.id_empresa', $finca)
            ->where('i.fecha', '>=', $desde)
            ->where('i.fecha', '<=', $hasta)
            ->where('i.bodega', $request->bodega)
            ->where('i.id_variedad', $request->variedad)
            ->groupBy(
                'documento',
                'fecha',
                DB::raw("DATE_FORMAT(i.fecha_registro, '%Y-%m-%d %H:%i')")
            )
            ->orderBy('i.fecha')
            ->orderBy('i.fecha_registro')
            ->get();

        $ingresos_compra = DB::table('ingreso_recepcion as i')
            ->select(
                DB::raw("'INGRESO' as tipo"),
                'i.packing as documento',
                'i.fecha',
                DB::raw("DATE_FORMAT(i.fecha_registro, '%Y-%m-%d %H:%i') as fecha_registro"),
                DB::raw('sum(i.tallos) as cantidad'),
                DB::raw("'COMPRA' as concepto"),
                DB::raw("NULL as detalle")
            )
            ->whereNotNull('i.packing')
            ->where('i.id_empresa', $finca)
            ->where('i.fecha', '>=', $desde)
            ->where('i.fecha', '<=', $hasta)
            ->where('i.bodega', $request->bodega)
            ->where('i.id_variedad', $request->variedad)
            ->groupBy(
                'documento',
                'fecha',
                DB::raw("DATE_FORMAT(i.fecha_registro, '%Y-%m-%d %H:%i')")
            )
            ->orderBy('i.fecha')
            ->orderBy('i.fecha_registro')
            ->get();

        $ingresos_movimiento = DB::table('ingreso_recepcion as i')
            ->select(
                DB::raw("'INGRESO' as tipo"),
                'i.id_ingreso_recepcion as documento',
                'i.fecha',
                DB::raw("DATE_FORMAT(i.fecha_registro, '%Y-%m-%d %H:%i') as fecha_registro"),
                DB::raw('sum(i.tallos) as cantidad'),
                DB::raw("'MOVIMIENTO' as concepto"),
                DB::raw("NULL as detalle")
            )
            ->whereNotNull('i.cambio_bodega')
            ->where('i.id_empresa', $finca)
            ->where('i.fecha', '>=', $desde)
            ->where('i.fecha', '<=', $hasta)
            ->where('i.bodega', $request->bodega)
            ->where('i.id_variedad', $request->variedad)
            ->groupBy(
                'documento',
                'fecha',
                DB::raw("DATE_FORMAT(i.fecha_registro, '%Y-%m-%d %H:%i')")
            )
            ->orderBy('i.fecha')
            ->orderBy('i.fecha_registro')
            ->get();

        $ingresos_correccion = DB::table('ingreso_recepcion as i')
            ->join('correccion_recepcion as c', 'c.id_correccion_recepcion', '=', 'i.id_correccion_recepcion')
            ->select(
                DB::raw("'INGRESO' as tipo"),
                'c.orden as documento',
                'i.fecha',
                DB::raw("DATE_FORMAT(i.fecha_registro, '%Y-%m-%d %H:%i') as fecha_registro"),
                DB::raw('sum(i.tallos) as cantidad'),
                DB::raw("'CORRECCION' as concepto"),
                DB::raw("NULL as detalle")
            )
            ->whereNotNull('i.id_correccion_recepcion')
            ->where('i.id_empresa', $finca)
            ->where('i.fecha', '>=', $desde)
            ->where('i.fecha', '<=', $hasta)
            ->where('i.bodega', $request->bodega)
            ->where('i.id_variedad', $request->variedad)
            ->groupBy(
                'documento',
                'fecha',
                DB::raw("DATE_FORMAT(i.fecha_registro, '%Y-%m-%d %H:%i')")
            )
            ->orderBy('i.fecha')
            ->orderBy('i.fecha_registro')
            ->get();

        // SALIDAS
        $salidas_ot = DB::table('salidas_recepcion as s')
            ->join('inventario_recepcion as i', 'i.id_inventario_recepcion', '=', 's.id_inventario_recepcion')
            ->join('orden_trabajo as ot', 'ot.id_orden_trabajo', '=', 's.id_orden_trabajo')
            ->join('detalle_caja_proyecto as dc', 'dc.id_detalle_caja_proyecto', '=', 'ot.id_detalle_caja_proyecto')
            ->join('variedad as bqt', 'bqt.id_variedad', '=', 'dc.id_variedad')
            ->join('detalle_cliente as cli', 'cli.id_cliente', '=', 'ot.id_cliente')
            ->select(
                DB::raw("'SALIDA' as tipo"),
                's.id_orden_trabajo as documento',
                's.fecha',
                DB::raw("DATE_FORMAT(s.fecha_registro, '%Y-%m-%d %H:%i') as fecha_registro"),
                DB::raw('sum(s.cantidad) as cantidad'),
                DB::raw("'OT' as concepto"),
                DB::raw("CONCAT(bqt.nombre, ': ', cli.nombre) as detalle"),
            )
            ->whereNotNull('s.id_orden_trabajo')
            ->where('cli.estado', 1)
            ->where('s.cantidad', '>', 0)
            ->where('i.id_empresa', $finca)
            ->where('i.bodega', $request->bodega)
            ->where('s.fecha_registro', '>=', $desde)
            ->where('s.fecha', '>=', $desde)
            ->where('s.fecha', '<=', $hasta)
            ->where('s.id_variedad', $request->variedad)
            ->groupBy(
                'documento',
                'fecha',
                DB::raw("DATE_FORMAT(s.fecha_registro, '%Y-%m-%d %H:%i')"),
                DB::raw("CONCAT(bqt.nombre, ': ', cli.nombre)")
            )
            ->orderBy('s.fecha')
            ->orderBy('s.fecha_registro')
            ->get();
        $salidas_ot_nacional = DB::table('salidas_recepcion as s')
            ->join('inventario_recepcion as i', 'i.id_inventario_recepcion', '=', 's.id_inventario_recepcion')
            ->join('ot_nacional as ot', 'ot.id_ot_nacional', '=', 's.id_ot_nacional')
            ->join('detalle_caja_proyecto as dc', 'dc.id_detalle_caja_proyecto', '=', 'ot.id_detalle_caja_proyecto')
            ->join('variedad as bqt', 'bqt.id_variedad', '=', 'dc.id_variedad')
            ->join('caja_proyecto as caja', 'caja.id_caja_proyecto', '=', 'dc.id_caja_proyecto')
            ->join('proyecto as proy', 'proy.id_proyecto', '=', 'caja.id_proyecto')
            ->join('detalle_cliente as cli', 'cli.id_cliente', '=', 'proy.id_cliente')
            ->select(
                DB::raw("'SALIDA' as tipo"),
                'ot.numero as documento',
                's.fecha',
                DB::raw("DATE_FORMAT(s.fecha_registro, '%Y-%m-%d %H:%i') as fecha_registro"),
                DB::raw('sum(s.cantidad) as cantidad'),
                DB::raw("'OT_NACIONAL' as concepto"),
                DB::raw("CONCAT(bqt.nombre, ': ', cli.nombre) as detalle")
            )
            ->whereNotNull('s.id_ot_nacional')
            ->where('cli.estado', 1)
            ->where('s.cantidad', '>', 0)
            ->where('i.id_empresa', $finca)
            ->where('i.bodega', $request->bodega)
            ->where('s.fecha_registro', '>=', $desde)
            ->where('s.fecha', '>=', $desde)
            ->where('s.fecha', '<=', $hasta)
            ->where('s.id_variedad', $request->variedad)
            ->groupBy(
                'documento',
                'fecha',
                DB::raw("DATE_FORMAT(s.fecha_registro, '%Y-%m-%d %H:%i')"),
                DB::raw("CONCAT(bqt.nombre, ': ', cli.nombre)")
            )
            ->orderBy('s.fecha')
            ->orderBy('s.fecha_registro')
            ->get();
        $salidas_solido = DB::table('salidas_recepcion as s')
            ->join('inventario_recepcion as i', 'i.id_inventario_recepcion', '=', 's.id_inventario_recepcion')
            ->join('detalle_caja_proyecto as dc', 'dc.id_detalle_caja_proyecto', '=', 's.id_detalle_caja_proyecto')
            ->join('caja_proyecto as caja', 'caja.id_caja_proyecto', '=', 'dc.id_caja_proyecto')
            ->join('proyecto as proy', 'proy.id_proyecto', '=', 'caja.id_proyecto')
            ->join('detalle_cliente as cli', 'cli.id_cliente', '=', 'proy.id_cliente')
            ->select(
                DB::raw("'SALIDA' as tipo"),
                's.id_salidas_recepcion as documento',
                's.fecha',
                DB::raw("DATE_FORMAT(s.fecha_registro, '%Y-%m-%d %H:%i') as fecha_registro"),
                DB::raw('sum(s.cantidad) as cantidad'),
                DB::raw("'FLOR SOLIDA' as concepto"),
                'cli.nombre as detalle'
            )
            ->whereNotNull('s.id_detalle_caja_proyecto')
            ->whereNull('s.id_ot_nacional')
            ->where('cli.estado', 1)
            ->where('s.cantidad', '>', 0)
            ->where('i.id_empresa', $finca)
            ->where('i.bodega', $request->bodega)
            ->where('s.fecha_registro', '>=', $desde)
            ->where('s.fecha', '>=', $desde)
            ->where('s.fecha', '<=', $hasta)
            ->where('s.id_variedad', $request->variedad)
            ->groupBy(
                'documento',
                'fecha',
                DB::raw("DATE_FORMAT(s.fecha_registro, '%Y-%m-%d %H:%i')"),
                'detalle'
            )
            ->orderBy('s.fecha')
            ->orderBy('s.fecha_registro')
            ->get();
        $salidas_movimiento = DB::table('salidas_recepcion as s')
            ->join('inventario_recepcion as i', 'i.id_inventario_recepcion', '=', 's.id_inventario_recepcion')
            ->select(
                DB::raw("'SALIDA' as tipo"),
                's.id_salidas_recepcion as documento',
                's.fecha',
                DB::raw("DATE_FORMAT(s.fecha_registro, '%Y-%m-%d %H:%i') as fecha_registro"),
                DB::raw('sum(s.cantidad) as cantidad'),
                DB::raw("'MOVIMIENTO' as concepto"),
                DB::raw("NULL as detalle")
            )
            ->whereNotNull('s.cambio_bodega')
            ->where('s.cantidad', '>', 0)
            ->where('i.id_empresa', $finca)
            ->where('i.bodega', $request->bodega)
            ->where('s.fecha_registro', '>=', $desde)
            ->where('s.fecha', '>=', $desde)
            ->where('s.fecha', '<=', $hasta)
            ->where('s.id_variedad', $request->variedad)
            ->groupBy(
                'documento',
                'fecha',
                DB::raw("DATE_FORMAT(s.fecha_registro, '%Y-%m-%d %H:%i')"),
            )
            ->orderBy('s.fecha')
            ->orderBy('s.fecha_registro')
            ->get();
        $salidas_basura = DB::table('salidas_recepcion as s')
            ->join('inventario_recepcion as i', 'i.id_inventario_recepcion', '=', 's.id_inventario_recepcion')
            ->select(
                DB::raw("'SALIDA' as tipo"),
                's.orden_basura as documento',
                's.fecha',
                DB::raw("DATE_FORMAT(s.fecha_registro, '%Y-%m-%d %H:%i') as fecha_registro"),
                DB::raw('sum(s.basura) as cantidad'),
                DB::raw("'FLOR BAJA' as concepto"),
                DB::raw("NULL as detalle")
            )
            ->whereNotNull('s.orden_basura')
            ->where('s.basura', '>', 0)
            ->where('s.estado_orden_basura', 1)
            ->where('i.id_empresa', $finca)
            ->where('i.bodega', $request->bodega)
            ->where('s.fecha_registro', '>=', $desde)
            ->where('s.fecha', '>=', $desde)
            ->where('s.fecha', '<=', $hasta)
            ->where('s.id_variedad', $request->variedad)
            ->groupBy(
                'documento',
                'fecha',
                DB::raw("DATE_FORMAT(s.fecha_registro, '%Y-%m-%d %H:%i')"),
            )
            ->orderBy('s.fecha')
            ->orderBy('s.fecha_registro')
            ->get();
        $salidas_correccion = DB::table('salidas_recepcion as s')
            ->join('inventario_recepcion as i', 'i.id_inventario_recepcion', '=', 's.id_inventario_recepcion')
            ->join('correccion_recepcion as c', 'c.id_correccion_recepcion', '=', 's.id_correccion_recepcion')
            ->select(
                DB::raw("'SALIDA' as tipo"),
                'c.orden as documento',
                's.fecha',
                DB::raw("DATE_FORMAT(s.fecha_registro, '%Y-%m-%d %H:%i') as fecha_registro"),
                DB::raw('sum(s.cantidad) as cantidad'),
                DB::raw("'CORRECCION' as concepto"),
                DB::raw("NULL as detalle")
            )
            ->whereNotNull('s.id_correccion_recepcion')
            ->where('s.cantidad', '>', 0)
            ->where('i.id_empresa', $finca)
            ->where('i.bodega', $request->bodega)
            ->where('s.fecha_registro', '>=', $desde)
            ->where('s.fecha', '>=', $desde)
            ->where('s.fecha', '<=', $hasta)
            ->where('s.id_variedad', $request->variedad)
            ->groupBy(
                'documento',
                'fecha',
                DB::raw("DATE_FORMAT(s.fecha_registro, '%Y-%m-%d %H:%i')"),
            )
            ->orderBy('s.fecha')
            ->orderBy('s.fecha_registro')
            ->get();

        // UNIFICAR TODO EL KARDEX 
        $kardex = $ingresos_documento->merge($ingresos_compra)
            ->merge($ingresos_movimiento)
            ->merge($ingresos_correccion)
            ->merge($salidas_ot)
            ->merge($salidas_ot_nacional)
            ->merge($salidas_solido)
            ->merge($salidas_movimiento)
            ->merge($salidas_basura)
            ->merge($salidas_correccion)
            ->sortBy('fecha')
            ->sortBy('fecha_registro');

        return view('adminlte.gestion.postco.kardex.partials.listado', [
            'listado' => $kardex,
            'saldo' => $saldo_inicial,
            'variedad' => $variedad,
            'desde' => $desde,
            'hasta' => $hasta,
            'bodega' => $request->bodega == 'V' ? 'Ventas' : 'Produccion',
        ]);
    }
}
