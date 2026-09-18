<?php

namespace yura\Http\Controllers\Postco;

use DB;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
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

    public function exportar_reporte(Request $request)
    {
        $spread = new Spreadsheet();
        $this->excel_reporte($spread, $request);
        $fileName = "Consumo_Diario.xlsx";
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
        $listado = $variedades;

        $columnas = getColumnasExcel();
        $sheet = $spread->getActiveSheet();
        $sheet->setTitle('Reporte');

        $row = 1;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Planta');
        $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + 1));
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Variedad');
        $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + 1));
        $total_fechas = [];
        foreach ($fechas as $f) {
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, explode(' del ', convertDateToText($f))[0]);
            $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[($col + 1)] . $row);
            $col++;
            $total_fechas[] = [
                'salidas' => 0,
                'ventas' => 0,
            ];
        }
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Totales');
        $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[($col + 1)] . $row);
        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        $row++;
        $col = 1;
        foreach ($fechas as $f) {
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Despacho');
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Venta');
        }
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Despacho');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Venta');
        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '5a7177');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        foreach ($listado as $pos => $item) {
            $row++;
            $col = 0;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->pta_nombre);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->var_nombre);
            $ventas_var = 0;
            $salidas_var = 0;
            foreach ($fechas as $pos_f => $f) {
                $ventas = 0;
                $salidas = 0;
                foreach ($item->ventas as $val) {
                    if ($val->fecha == $f) {
                        $ventas += $val->monto;
                    }
                }
                foreach ($item->salidas_ot as $val) {
                    if ($val->fecha == $f) {
                        $salidas += $val->cantidad;
                    }
                }
                foreach ($item->salidas_ot_nacional as $val) {
                    if ($val->fecha == $f) {
                        $salidas += $val->cantidad;
                    }
                }
                foreach ($item->salidas_solido as $val) {
                    if ($val->fecha == $f) {
                        $salidas += $val->cantidad;
                    }
                }
                $ventas_var += $ventas;
                $salidas_var += $salidas;
                $total_fechas[$pos_f]['ventas'] += $ventas;
                $total_fechas[$pos_f]['salidas'] += $salidas;

                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $salidas > 0 ? round($salidas) : '');
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $ventas > 0 ? '$' . round($ventas, 2) : '');
            }
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, round($salidas_var));
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, '$' . round($ventas_var, 2));
        }
        $row++;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TOTALES');
        $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[($col + 1)] . $row);
        $col++;
        $total_ventas = 0;
        $total_salidas = 0;
        foreach ($total_fechas as $val) {
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, round($val['salidas']));
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, '$' . round($val['ventas'], 2));
            $total_ventas += $val['salidas'];
            $total_salidas += $val['ventas'];
        }
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, round($total_ventas));
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, '$' . round($total_salidas, 2));
        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '5a7177');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        setTextCenterToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);
        setBorderToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);

        for ($i = 0; $i <= $col; $i++)
            $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);
    }
}
