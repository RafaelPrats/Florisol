<?php

namespace yura\Http\Controllers\Postco;

use DB;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
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
        $desde = $request->desde >= '2026-09-17' ? $request->desde : '2026-09-17';
        $hasta = $request->hasta;
        // Calcular saldo inicial
        $ingreso_inicial = DB::table('ingreso_recepcion')
            ->select(DB::raw('sum(tallos) as cantidad'))
            ->where('id_variedad', $request->variedad)
            ->where('id_empresa', $finca)
            ->where('bodega', $request->bodega)
            ->where('fecha', '<', $desde)
            ->where('fecha', '>=', '2026-09-17')
            ->where('fecha_registro', '>=', '2026-09-17 00:00:00')
            ->get()[0]->cantidad;
        $salida_inicial = DB::table('salidas_recepcion as s')
            ->join('inventario_recepcion as i', 'i.id_inventario_recepcion', '=', 's.id_inventario_recepcion')
            ->select(DB::raw('sum(s.cantidad + s.basura) as cantidad'))
            ->where('s.id_variedad', $request->variedad)
            ->where('i.id_empresa', $finca)
            ->where('i.bodega', $request->bodega)
            ->where('s.fecha', '<', $desde)
            ->where('s.fecha', '>=', '2026-09-17')
            ->where('s.fecha_registro', '>=', '2026-09-17 00:00:00')
            ->get()[0]->cantidad;
        $saldo_inicial = $ingreso_inicial - $salida_inicial;
        // INGRESOS
        $ingresos_documento = DB::table('ingreso_recepcion as i')
            ->join('api_store_cajas as api', 'api.id_api_store_cajas', '=', 'i.id_api_store_cajas')
            ->select(
                DB::raw("'INGRESO' as tipo"),
                'i.id_api_store_cajas as documento',
                'i.fecha',
                DB::raw("DATE_FORMAT(i.fecha_registro, '%Y-%m-%d %H:%i') as fecha_registro"),
                DB::raw('sum(i.tallos) as cantidad'),
                DB::raw("'INTERNO' as concepto"),
                DB::raw("CONCAT('Documento: ', api.documento) as detalle"),
            )
            ->where('i.id_empresa', $finca)
            ->where('i.fecha', '>=', $desde)
            ->where('i.fecha', '<=', $hasta)
            ->where('i.bodega', $request->bodega)
            ->where('i.id_variedad', $request->variedad)
            ->groupBy(
                'i.id_api_store_cajas',
                'fecha',
                DB::raw("DATE_FORMAT(i.fecha_registro, '%Y-%m-%d %H:%i')"),
                'detalle'
            )
            ->orderBy('i.fecha')
            ->orderBy('i.fecha_registro')
            ->get();

        $ingresos_compra = DB::table('ingreso_recepcion as i')
            ->join('configuracion_empresa as p', 'p.id_configuracion_empresa', '=', 'i.id_proveedor')
            ->select(
                DB::raw("'INGRESO' as tipo"),
                'i.packing as documento',
                'i.fecha',
                DB::raw("DATE_FORMAT(i.fecha_registro, '%Y-%m-%d %H:%i') as fecha_registro"),
                DB::raw('sum(i.tallos) as cantidad'),
                DB::raw("'COMPRA' as concepto"),
                DB::raw("CONCAT('Proveedor: ', p.nombre) as detalle"),
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
                DB::raw("DATE_FORMAT(i.fecha_registro, '%Y-%m-%d %H:%i')"),
                'detalle'
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
            ->join('usuario as u', 'u.id_usuario', '=', 'c.id_usuario')
            ->select(
                DB::raw("'INGRESO' as tipo"),
                'c.orden as documento',
                'i.fecha',
                DB::raw("DATE_FORMAT(i.fecha_registro, '%Y-%m-%d %H:%i') as fecha_registro"),
                DB::raw('sum(i.tallos) as cantidad'),
                DB::raw("'CORRECCION' as concepto"),
                DB::raw("CONCAT('Usuario: ', u.nombre_completo) as detalle"),
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
                DB::raw("DATE_FORMAT(i.fecha_registro, '%Y-%m-%d %H:%i')"),
                'detalle'
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
                's.orden_flor_solida as documento',
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
            ->join('usuario as u', 'u.id_usuario', '=', 'c.id_usuario')
            ->select(
                DB::raw("'SALIDA' as tipo"),
                'c.orden as documento',
                's.fecha',
                DB::raw("DATE_FORMAT(s.fecha_registro, '%Y-%m-%d %H:%i') as fecha_registro"),
                DB::raw('sum(s.cantidad) as cantidad'),
                DB::raw("'CORRECCION' as concepto"),
                DB::raw("CONCAT('usuario: ', u.nombre_completo) as detalle"),
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
                'detalle'
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

    public function exportar_reporte(Request $request)
    {
        $spread = new Spreadsheet();
        $this->excel_reporte($spread, $request);
        $fileName = "Kardex.xlsx";
        $writer = new Xlsx($spread);

        //--------------------------- GUARDAR EL EXCEL -----------------------

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        $writer->save('php://output');

        //$writer->save('/var/www/html/Dasalflor/storage/storage/excel/excel_prueba.xlsx');
    }

    public function excel_reporte($spread, $request)
    {
        $finca = getFincaActiva();
        $variedad = Variedad::find($request->variedad);
        $desde = $request->desde >= '2026-09-17' ? $request->desde : '2026-09-17';
        $hasta = $request->hasta;
        $bodega = $request->bodega == 'V' ? 'Ventas' : 'Produccion';
        // Calcular saldo inicial
        $ingreso_inicial = DB::table('ingreso_recepcion')
            ->select(DB::raw('sum(tallos) as cantidad'))
            ->where('id_variedad', $request->variedad)
            ->where('id_empresa', $finca)
            ->where('bodega', $request->bodega)
            ->where('fecha', '<', $desde)
            ->where('fecha', '>=', '2026-09-17')
            ->where('fecha_registro', '>=', '2026-09-17 00:00:00')
            ->get()[0]->cantidad;
        $salida_inicial = DB::table('salidas_recepcion as s')
            ->join('inventario_recepcion as i', 'i.id_inventario_recepcion', '=', 's.id_inventario_recepcion')
            ->select(DB::raw('sum(s.cantidad + s.basura) as cantidad'))
            ->where('s.id_variedad', $request->variedad)
            ->where('i.id_empresa', $finca)
            ->where('i.bodega', $request->bodega)
            ->where('s.fecha', '<', $desde)
            ->where('s.fecha', '>=', '2026-09-17')
            ->where('s.fecha_registro', '>=', '2026-09-17 00:00:00')
            ->get()[0]->cantidad;
        $saldo = $ingreso_inicial - $salida_inicial;
        // INGRESOS
        $ingresos_documento = DB::table('ingreso_recepcion as i')
            ->join('api_store_cajas as api', 'api.id_api_store_cajas', '=', 'i.id_api_store_cajas')
            ->select(
                DB::raw("'INGRESO' as tipo"),
                'i.id_api_store_cajas as documento',
                'i.fecha',
                DB::raw("DATE_FORMAT(i.fecha_registro, '%Y-%m-%d %H:%i') as fecha_registro"),
                DB::raw('sum(i.tallos) as cantidad'),
                DB::raw("'INTERNO' as concepto"),
                DB::raw("CONCAT('Documento: ', api.documento) as detalle"),
            )
            ->where('i.id_empresa', $finca)
            ->where('i.fecha', '>=', $desde)
            ->where('i.fecha', '<=', $hasta)
            ->where('i.bodega', $request->bodega)
            ->where('i.id_variedad', $request->variedad)
            ->groupBy(
                'i.id_api_store_cajas',
                'fecha',
                DB::raw("DATE_FORMAT(i.fecha_registro, '%Y-%m-%d %H:%i')"),
                'detalle'
            )
            ->orderBy('i.fecha')
            ->orderBy('i.fecha_registro')
            ->get();

        $ingresos_compra = DB::table('ingreso_recepcion as i')
            ->join('configuracion_empresa as p', 'p.id_configuracion_empresa', '=', 'i.id_proveedor')
            ->select(
                DB::raw("'INGRESO' as tipo"),
                'i.packing as documento',
                'i.fecha',
                DB::raw("DATE_FORMAT(i.fecha_registro, '%Y-%m-%d %H:%i') as fecha_registro"),
                DB::raw('sum(i.tallos) as cantidad'),
                DB::raw("'COMPRA' as concepto"),
                DB::raw("CONCAT('Proveedor: ', p.nombre) as detalle"),
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
                DB::raw("DATE_FORMAT(i.fecha_registro, '%Y-%m-%d %H:%i')"),
                'detalle'
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
            ->join('usuario as u', 'u.id_usuario', '=', 'c.id_usuario')
            ->select(
                DB::raw("'INGRESO' as tipo"),
                'c.orden as documento',
                'i.fecha',
                DB::raw("DATE_FORMAT(i.fecha_registro, '%Y-%m-%d %H:%i') as fecha_registro"),
                DB::raw('sum(i.tallos) as cantidad'),
                DB::raw("'CORRECCION' as concepto"),
                DB::raw("CONCAT('Usuario: ', u.nombre_completo) as detalle"),
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
                DB::raw("DATE_FORMAT(i.fecha_registro, '%Y-%m-%d %H:%i')"),
                'detalle'
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
                's.orden_flor_solida as documento',
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
            ->join('usuario as u', 'u.id_usuario', '=', 'c.id_usuario')
            ->select(
                DB::raw("'SALIDA' as tipo"),
                'c.orden as documento',
                's.fecha',
                DB::raw("DATE_FORMAT(s.fecha_registro, '%Y-%m-%d %H:%i') as fecha_registro"),
                DB::raw('sum(s.cantidad) as cantidad'),
                DB::raw("'CORRECCION' as concepto"),
                DB::raw("CONCAT('usuario: ', u.nombre_completo) as detalle"),
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
                'detalle'
            )
            ->orderBy('s.fecha')
            ->orderBy('s.fecha_registro')
            ->get();

        // UNIFICAR TODO EL KARDEX 
        $listado = $ingresos_documento->merge($ingresos_compra)
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

        $columnas = getColumnasExcel();
        $sheet = $spread->getActiveSheet();
        $sheet->setTitle('KARDEX');

        $saldo_inicial = $saldo;
        $total_ingresos = 0;
        $total_salidas = 0;
        foreach ($listado as $item) {
            if ($item->tipo == 'INGRESO') {
                $total_ingresos += $item->cantidad;
            } else {
                $total_salidas += $item->cantidad;
            }
        }
        $saldo_final = $saldo_inicial + $total_ingresos - $total_salidas;

        $sheet->mergeCells('A1:G1');
        setValueToCeldaExcel($sheet, 'A1', 'KARDEX DE ' . mb_strtoupper($variedad->nombre));
        $sheet->getRowDimension(1)->setRowHeight(30);
        $sheet->getStyle('A1:G1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '003729'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'indent' => 1,
            ],
        ]);

        $sheet->mergeCells('A2:G2');
        setValueToCeldaExcel(
            $sheet,
            'A2',
            'Variedad: ' . $variedad->nombre .
                '    |    Periodo: ' . explode(' del ', convertDateToText($desde))[0] .
                ' al ' . explode(' del ', convertDateToText($hasta))[0] .
                '    |    Bodega: ' . $bodega
        );
        $sheet->getRowDimension(2)->setRowHeight(22);
        $sheet->getStyle('A2:G2')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
                'color' => ['rgb' => '003729'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E8F6F1'],
            ],
            'alignment' => [
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'indent' => 1,
            ],
        ]);

        $sheet->mergeCells('A3:G3');
        setValueToCeldaExcel(
            $sheet,
            'A3',
            'SALDO INICIAL: ' . number_format($saldo_inicial) .
                '     |     ENTRADAS: +' . number_format($total_ingresos) .
                '     |     SALIDAS: -' . number_format($total_salidas) .
                '     |     SALDO AL CIERRE: ' . number_format($saldo_final)
        );
        $sheet->getRowDimension(3)->setRowHeight(24);
        $sheet->getStyle('A3:G3')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
                'color' => ['rgb' => '003729'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F2F7F5'],
            ],
            'alignment' => [
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'indent' => 1,
            ],
        ]);

        $headers = ['Fecha', 'Tipo', 'Documento', 'Detalle', 'Entrada', 'Salida', 'Saldo'];
        foreach ($headers as $col => $header) {
            setValueToCeldaExcel($sheet, $columnas[$col] . '5', $header);
        }
        $sheet->getRowDimension(5)->setRowHeight(23);
        $sheet->getStyle('A5:G5')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '5A7177'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getStyle('E5')->getFill()->getStartColor()->setRGB('00B388');
        $sheet->getStyle('F5')->getFill()->getStartColor()->setRGB('D01C62');

        $row = 5;
        $fecha_anterior = null;
        $grupo_fecha = 0;
        foreach ($listado as $item) {
            if ($item->tipo == 'INGRESO') {
                $saldo += $item->cantidad;
            } else {
                $saldo -= $item->cantidad;
            }

            $fecha_inicio = $fecha_anterior != $item->fecha;
            if ($fecha_inicio && $fecha_anterior !== null) {
                $grupo_fecha = !$grupo_fecha;
            }
            $fecha_anterior = $item->fecha;

            $row++;
            setValueToCeldaExcel($sheet, 'A' . $row, explode(' del ', convertDateToText($item->fecha))[0]);
            setValueToCeldaExcel($sheet, 'B' . $row, $item->tipo);
            setValueToCeldaExcel($sheet, 'C' . $row, $item->concepto . '-' . $item->documento);
            setValueToCeldaExcel($sheet, 'D' . $row, $item->detalle ?: '—');
            setValueToCeldaExcel($sheet, 'E' . $row, $item->tipo == 'INGRESO' ? $item->cantidad : '');
            setValueToCeldaExcel($sheet, 'F' . $row, $item->tipo == 'SALIDA' ? $item->cantidad : '');
            setValueToCeldaExcel($sheet, 'G' . $row, $saldo);

            $sheet->getStyle('A' . $row . ':G' . $row)->applyFromArray([
                'alignment' => [
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
                'borders' => [
                    'bottom' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR,
                        'color' => ['rgb' => 'DCE6E2'],
                    ],
                ],
            ]);
            if ($grupo_fecha) {
                $sheet->getStyle('A' . $row . ':G' . $row)->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('F0F7F4');
            }
            if ($fecha_inicio) {
                $sheet->getStyle('A' . $row . ':G' . $row)->getBorders()->getTop()
                    ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM)
                    ->getColor()->setRGB('00B388');
            }

            $sheet->getStyle('B' . $row)->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => $item->tipo == 'INGRESO' ? '00B388' : 'D01C62'],
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
            ]);
            $sheet->getStyle('E' . $row)->applyFromArray([
                'font' => [
                    'bold' => $item->tipo == 'INGRESO',
                    'color' => ['rgb' => '009874'],
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                ],
                'numberFormat' => [
                    'formatCode' => '#,##0;[Red]-#,##0',
                ],
            ]);
            $sheet->getStyle('F' . $row)->applyFromArray([
                'font' => [
                    'bold' => $item->tipo == 'SALIDA',
                    'color' => ['rgb' => 'D01C62'],
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                ],
                'numberFormat' => [
                    'formatCode' => '#,##0;[Red]-#,##0',
                ],
            ]);
            $sheet->getStyle('G' . $row)->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => '003729'],
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E8F6F1'],
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                ],
                'numberFormat' => [
                    'formatCode' => '#,##0;[Red]-#,##0',
                ],
            ]);
            $sheet->getRowDimension($row)->setRowHeight(20);
        }

        $last_row = $row;
        $sheet->getStyle('A5:G' . $last_row)->getBorders()->getVertical()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR)
            ->getColor()->setRGB('E6ECE9');
        $sheet->setAutoFilter('A5:G' . $last_row);
        $sheet->freezePane('A6');
        $sheet->setShowGridlines(false);
        $sheet->getPageSetup()
            ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE)
            ->setFitToPage(true)
            ->setFitToWidth(1)
            ->setFitToHeight(0);
        $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 5);

        $anchos = [
            'A' => 16,
            'B' => 14,
            'C' => 24,
            'D' => 52,
            'E' => 15,
            'F' => 15,
            'G' => 16,
        ];
        foreach ($anchos as $columna => $ancho) {
            $sheet->getColumnDimension($columna)->setWidth($ancho);
        }
        if ($last_row > 5) {
            $sheet->getStyle('E6:G' . $last_row)->getNumberFormat()
                ->setFormatCode('#,##0;[Red]-#,##0');
        }
    }
}
