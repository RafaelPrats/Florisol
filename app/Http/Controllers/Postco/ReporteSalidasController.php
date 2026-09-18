<?php

namespace yura\Http\Controllers\Postco;

use DB;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
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
            ->join('detalle_caja_proyecto as dc', 'dc.id_detalle_caja_proyecto', '=', 'ot.id_detalle_caja_proyecto')
            ->join('variedad as bqt', 'bqt.id_variedad', '=', 'dc.id_variedad')
            ->join('detalle_cliente as cli', 'cli.id_cliente', '=', 'ot.id_cliente')
            ->select(
                's.*',
                'bqt.nombre as bqt_nombre',
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
                    'bqt_nombre' => $items->first()->bqt_nombre,
                    'cli_nombre' => $items->first()->cli_nombre,
                    'ramos' => $items->first()->ramos,
                    'detalles' => $items->values(),
                ];
            })
            ->values();

        $listado_ot_nacional = DB::table('salidas_recepcion as s')
            ->join('ot_nacional as ot', 'ot.id_ot_nacional', '=', 's.id_ot_nacional')
            ->join('variedad as v', 'v.id_variedad', '=', 's.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->join('inventario_recepcion as i', 'i.id_inventario_recepcion', '=', 's.id_inventario_recepcion')
            ->join('detalle_caja_proyecto as dc', 'dc.id_detalle_caja_proyecto', '=', 'ot.id_detalle_caja_proyecto')
            ->join('variedad as bqt', 'bqt.id_variedad', '=', 'dc.id_variedad')
            ->join('caja_proyecto as caja', 'caja.id_caja_proyecto', '=', 'dc.id_caja_proyecto')
            ->join('proyecto as proy', 'proy.id_proyecto', '=', 'caja.id_proyecto')
            ->join('detalle_cliente as cli', 'cli.id_cliente', '=', 'proy.id_cliente')
            ->select(
                's.*',
                'v.nombre as var_nombre',
                'p.nombre as pta_nombre',
                'ot.numero',
                'bqt.nombre as bqt_nombre',
                'cli.nombre as cli_nombre',
                'i.fecha as fecha_inventario',
                'i.tallos_x_ramo',
                'i.longitud',
                'i.bodega',
                'dc.ramos_x_caja',
                'caja.cantidad as cajas',
            )->distinct()
            ->whereNotNull('s.id_ot_nacional')
            ->where('cli.estado', 1)
            ->where('s.cantidad', '>', 0)
            ->where('i.id_empresa', $finca)
            ->where('s.fecha', '>=', $request->desde)
            ->where('s.fecha', '<=', $request->hasta);
        if ($request->bodega != 'T')
            $listado_ot_nacional = $listado_ot_nacional->where('i.bodega', $request->bodega);
        if ($request->planta != '')
            $listado_ot_nacional = $listado_ot_nacional->where('v.id_planta', $request->planta);
        if ($request->variedad != '')
            $listado_ot_nacional = $listado_ot_nacional->where('i.id_variedad', $request->variedad);
        $listado_ot_nacional = $listado_ot_nacional->orderBy('s.fecha')
            ->orderBy('ot.numero')
            ->get()
            ->groupBy('numero')
            ->map(function ($items, $numero) {
                return [
                    'numero' => $numero,
                    'fecha' => $items->first()->fecha,
                    'bqt_nombre' => $items->first()->bqt_nombre,
                    'cli_nombre' => $items->first()->cli_nombre,
                    'id_detalle_caja_proyecto' => $items->first()->id_detalle_caja_proyecto,
                    'ramos_x_caja' => $items->first()->ramos_x_caja,
                    'cajas' => $items->first()->cajas,
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
            ->whereNull('s.id_ot_nacional')
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

        $listado_corregir = DB::table('salidas_recepcion as s')
            ->join('variedad as v', 'v.id_variedad', '=', 's.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->join('inventario_recepcion as i', 'i.id_inventario_recepcion', '=', 's.id_inventario_recepcion')
            ->join('correccion_recepcion as c', 'c.id_correccion_recepcion', '=', 's.id_correccion_recepcion')
            ->select(
                's.*',
                'v.nombre as var_nombre',
                'p.nombre as pta_nombre',
                'c.orden',
                'c.anterior',
                'c.actual',
                'i.fecha as fecha_inventario',
                'i.tallos_x_ramo',
                'i.longitud',
                'i.bodega',
            )->distinct()
            ->whereNotNull('s.id_correccion_recepcion')
            ->where('i.id_empresa', $finca)
            ->where('s.fecha', '>=', $request->desde)
            ->where('s.fecha', '<=', $request->hasta);
        if ($request->bodega != 'T')
            $listado_corregir = $listado_corregir->where('i.bodega', $request->bodega);
        if ($request->planta != '')
            $listado_corregir = $listado_corregir->where('v.id_planta', $request->planta);
        if ($request->variedad != '')
            $listado_corregir = $listado_corregir->where('i.id_variedad', $request->variedad);
        $listado_corregir = $listado_corregir->orderBy('s.fecha')
            ->orderBy('c.orden')
            ->get()
            ->groupBy('orden')
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
            'listado_ot_nacional' => $listado_ot_nacional,
            'listado_corregir' => $listado_corregir,
        ]);
    }

    public function exportar_reporte(Request $request)
    {
        $spread = new Spreadsheet();
        $this->excel_reporte($spread, $request);
        $fileName = "Salidas.xlsx";
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
        $listado_ot = DB::table('salidas_recepcion as s')
            ->join('variedad as v', 'v.id_variedad', '=', 's.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->join('inventario_recepcion as i', 'i.id_inventario_recepcion', '=', 's.id_inventario_recepcion')
            ->join('orden_trabajo as ot', 'ot.id_orden_trabajo', '=', 's.id_orden_trabajo')
            ->join('detalle_caja_proyecto as dc', 'dc.id_detalle_caja_proyecto', '=', 'ot.id_detalle_caja_proyecto')
            ->join('variedad as bqt', 'bqt.id_variedad', '=', 'dc.id_variedad')
            ->join('detalle_cliente as cli', 'cli.id_cliente', '=', 'ot.id_cliente')
            ->select(
                's.*',
                'bqt.nombre as bqt_nombre',
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
                    'bqt_nombre' => $items->first()->bqt_nombre,
                    'cli_nombre' => $items->first()->cli_nombre,
                    'ramos' => $items->first()->ramos,
                    'detalles' => $items->values(),
                ];
            })
            ->values();

        $listado_ot_nacional = DB::table('salidas_recepcion as s')
            ->join('ot_nacional as ot', 'ot.id_ot_nacional', '=', 's.id_ot_nacional')
            ->join('variedad as v', 'v.id_variedad', '=', 's.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->join('inventario_recepcion as i', 'i.id_inventario_recepcion', '=', 's.id_inventario_recepcion')
            ->join('detalle_caja_proyecto as dc', 'dc.id_detalle_caja_proyecto', '=', 'ot.id_detalle_caja_proyecto')
            ->join('variedad as bqt', 'bqt.id_variedad', '=', 'dc.id_variedad')
            ->join('caja_proyecto as caja', 'caja.id_caja_proyecto', '=', 'dc.id_caja_proyecto')
            ->join('proyecto as proy', 'proy.id_proyecto', '=', 'caja.id_proyecto')
            ->join('detalle_cliente as cli', 'cli.id_cliente', '=', 'proy.id_cliente')
            ->select(
                's.*',
                'v.nombre as var_nombre',
                'p.nombre as pta_nombre',
                'ot.numero',
                'bqt.nombre as bqt_nombre',
                'cli.nombre as cli_nombre',
                'i.fecha as fecha_inventario',
                'i.tallos_x_ramo',
                'i.longitud',
                'i.bodega',
                'dc.ramos_x_caja',
                'caja.cantidad as cajas',
            )->distinct()
            ->whereNotNull('s.id_ot_nacional')
            ->where('cli.estado', 1)
            ->where('s.cantidad', '>', 0)
            ->where('i.id_empresa', $finca)
            ->where('s.fecha', '>=', $request->desde)
            ->where('s.fecha', '<=', $request->hasta);
        if ($request->bodega != 'T')
            $listado_ot_nacional = $listado_ot_nacional->where('i.bodega', $request->bodega);
        if ($request->planta != '')
            $listado_ot_nacional = $listado_ot_nacional->where('v.id_planta', $request->planta);
        if ($request->variedad != '')
            $listado_ot_nacional = $listado_ot_nacional->where('i.id_variedad', $request->variedad);
        $listado_ot_nacional = $listado_ot_nacional->orderBy('s.fecha')
            ->orderBy('ot.numero')
            ->get()
            ->groupBy('numero')
            ->map(function ($items, $numero) {
                return [
                    'numero' => $numero,
                    'fecha' => $items->first()->fecha,
                    'bqt_nombre' => $items->first()->bqt_nombre,
                    'cli_nombre' => $items->first()->cli_nombre,
                    'id_detalle_caja_proyecto' => $items->first()->id_detalle_caja_proyecto,
                    'ramos_x_caja' => $items->first()->ramos_x_caja,
                    'cajas' => $items->first()->cajas,
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
            ->whereNull('s.id_ot_nacional')
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

        $listado_corregir = DB::table('salidas_recepcion as s')
            ->join('variedad as v', 'v.id_variedad', '=', 's.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->join('inventario_recepcion as i', 'i.id_inventario_recepcion', '=', 's.id_inventario_recepcion')
            ->join('correccion_recepcion as c', 'c.id_correccion_recepcion', '=', 's.id_correccion_recepcion')
            ->select(
                's.*',
                'v.nombre as var_nombre',
                'p.nombre as pta_nombre',
                'c.orden',
                'c.anterior',
                'c.actual',
                'i.fecha as fecha_inventario',
                'i.tallos_x_ramo',
                'i.longitud',
                'i.bodega',
            )->distinct()
            ->whereNotNull('s.id_correccion_recepcion')
            ->where('i.id_empresa', $finca)
            ->where('s.fecha', '>=', $request->desde)
            ->where('s.fecha', '<=', $request->hasta);
        if ($request->bodega != 'T')
            $listado_corregir = $listado_corregir->where('i.bodega', $request->bodega);
        if ($request->planta != '')
            $listado_corregir = $listado_corregir->where('v.id_planta', $request->planta);
        if ($request->variedad != '')
            $listado_corregir = $listado_corregir->where('i.id_variedad', $request->variedad);
        $listado_corregir = $listado_corregir->orderBy('s.fecha')
            ->orderBy('c.orden')
            ->get()
            ->groupBy('orden')
            ->map(function ($items, $orden) {
                return [
                    'orden' => $orden,
                    'fecha' => $items->first()->fecha,
                    'detalles' => $items->values(),
                ];
            })
            ->values();

        // OT
        $columnas = getColumnasExcel();
        $sheet = $spread->getActiveSheet();
        $sheet->setTitle('OT');

        $row = 1;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'OT');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Fecha Salida');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Cliente');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Ramos');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Fecha Inventario');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Bodega');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Planta');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Variedad');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Longitud');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TxR');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Tallos');
        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        foreach ($listado_ot as $ot) {
            foreach ($ot['detalles'] as $pos_i => $item) {
                $row++;
                if ($pos_i == 0) {
                    $col = 0;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, '#' . $ot['ot']);
                    $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($ot['detalles']) - 1));
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $ot['fecha']);
                    $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($ot['detalles']) - 1));
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $ot['cli_nombre'] . ' - ' . $ot['bqt_nombre']);
                    $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($ot['detalles']) - 1));
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $ot['ramos']);
                    $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($ot['detalles']) - 1));
                }
                $col = 4;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->fecha_inventario);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->bodega == 'V' ? 'Ventas' : 'Produccion');
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->pta_nombre);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->var_nombre);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->longitud);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->tallos_x_ramo);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->cantidad);
            }
        }

        setTextCenterToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);
        setBorderToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);

        for ($i = 0; $i <= $col; $i++)
            $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);

        // NACIONAL
        $sheet = $spread->createSheet()->setTitle('Nacional');

        $row = 1;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'OT');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Fecha Salida');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Cliente');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Ramos');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Fecha Inventario');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Bodega');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Planta');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Variedad');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Longitud');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TxR');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Tallos');
        $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[($col + 1)] . $row);
        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        foreach ($listado_ot_nacional as $ot) {
            $total_tallos_ot = 0;
            foreach ($ot['detalles'] as $pos_i => $item) {
                $total_tallos_ot += $item->cantidad;
            }
            foreach ($ot['detalles'] as $pos_i => $item) {
                $row++;
                if ($pos_i == 0) {
                    $col = 0;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, '#' . $ot['numero']);
                    $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($ot['detalles']) - 1));
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $ot['fecha']);
                    $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($ot['detalles']) - 1));
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $ot['cli_nombre'] . ' - ' . $ot['bqt_nombre']);
                    $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($ot['detalles']) - 1));
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $ot['ramos_x_caja'] * $ot['cajas']);
                    $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($ot['detalles']) - 1));
                }
                $col = 4;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->fecha_inventario);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->bodega == 'V' ? 'Ventas' : 'Produccion');
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->pta_nombre);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->var_nombre);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->longitud);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->tallos_x_ramo);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->cantidad);
                $col++;
                if ($pos_i == 0) {
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_tallos_ot);
                    $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($ot['detalles']) - 1));
                }
            }
        }

        setTextCenterToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);
        setBorderToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);

        for ($i = 0; $i <= $col; $i++)
            $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);

        // FLOR SOLIDA
        $sheet = $spread->createSheet()->setTitle('Flor Solida');

        $row = 1;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Fecha Despacho');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Cliente');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Piezas');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Tipo Caja');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Ramos x Caja');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Planta');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Variedad');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Fecha Inventario');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Bodega');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Longitud');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TxR');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Tallos');
        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        foreach ($listado_solido as $det_caja) {
            foreach ($det_caja['detalles'] as $pos_i => $item) {
                $row++;
                if ($pos_i == 0) {
                    $col = 0;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $det_caja['fecha']);
                    $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($det_caja['detalles']) - 1));
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $det_caja['cli_nombre']);
                    $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($det_caja['detalles']) - 1));
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $det_caja['piezas']);
                    $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($det_caja['detalles']) - 1));
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $det_caja['tipo_caja']);
                    $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($det_caja['detalles']) - 1));
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $det_caja['ramos_x_caja']);
                    $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($det_caja['detalles']) - 1));
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $det_caja['pta_nombre']);
                    $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($det_caja['detalles']) - 1));
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $det_caja['var_nombre']);
                    $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($det_caja['detalles']) - 1));
                }
                $col = 7;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->fecha_inventario);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->bodega == 'V' ? 'Ventas' : 'Produccion');
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->longitud);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->tallos_x_ramo);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->cantidad);
            }
        }

        setTextCenterToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);
        setBorderToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);

        for ($i = 0; $i <= $col; $i++)
            $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);

        // MOVIMIENTOS
        $sheet = $spread->createSheet()->setTitle('Movimientos');

        $row = 1;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Fecha');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'De');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'A');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Fecha Inventario');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Planta');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Variedad');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Longitud');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TxR');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Tallos');
        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        foreach ($listado_movimientos as $item) {
            $row++;
            $col = 0;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->fecha);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->bodega == 'V' ? 'Ventas' : 'Produccion');
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->cambio_bodega == 'V' ? 'Ventas' : 'Produccion');
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->fecha_inventario);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->pta_nombre);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->var_nombre);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->longitud);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->tallos_x_ramo);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->cantidad);
        }

        setTextCenterToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);
        setBorderToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);

        for ($i = 0; $i <= $col; $i++)
            $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);

        // FLOR BAJA
        $sheet = $spread->createSheet()->setTitle('Flor Baja');

        $row = 1;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'N°');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Fecha Salida');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Fecha Inventario');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Bodega');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Planta');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Variedad');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Longitud');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TxR');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Tallos');
        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        foreach ($listado_orden_basura as $orden) {
            foreach ($orden['detalles'] as $pos_i => $item) {
                $row++;
                if ($pos_i == 0) {
                    $col = 0;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, '#' . $orden['orden']);
                    $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($orden['detalles']) - 1));
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $orden['fecha']);
                    $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($orden['detalles']) - 1));
                }
                $col = 2;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->fecha_inventario);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->bodega == 'V' ? 'Ventas' : 'Produccion');
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->pta_nombre);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->var_nombre);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->longitud);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->tallos_x_ramo);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->basura);
            }
        }

        setTextCenterToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);
        setBorderToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);

        for ($i = 0; $i <= $col; $i++)
            $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);

        // CORRECCION
        $sheet = $spread->createSheet()->setTitle('Correccion');

        $row = 1;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Fecha Salida');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'N°');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Fecha Inventario');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Bodega');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Planta');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Variedad');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Longitud');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Tallos Anteriores');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Tallos Corregidos');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Tallos Sacados');
        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        foreach ($listado_corregir as $correccion) {
            foreach ($correccion['detalles'] as $pos_i => $item) {
                $row++;
                if ($pos_i == 0) {
                    $col = 0;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $correccion['fecha']);
                    $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($correccion['detalles']) - 1));
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, '#' . $correccion['orden']);
                    $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($correccion['detalles']) - 1));
                }
                $col = 2;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->fecha_inventario);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->bodega == 'V' ? 'Ventas' : 'Produccion');
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->pta_nombre);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->var_nombre);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->longitud);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->anterior);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->actual);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->cantidad);
            }
        }

        setTextCenterToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);
        setBorderToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);

        for ($i = 0; $i <= $col; $i++)
            $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);
    }
}
