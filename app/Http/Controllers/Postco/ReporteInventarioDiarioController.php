<?php

namespace yura\Http\Controllers\Postco;

use DB;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use yura\Http\Controllers\Controller;
use yura\Modelos\Planta;
use yura\Modelos\Submenu;

class ReporteInventarioDiarioController extends Controller
{
    public function inicio(Request $request)
    {
        $finca = getFincaActiva();
        $plantas = Planta::where('estado', 1)
            ->where('id_empresa', $finca)
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.postco.inventario_diario.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'plantas' => $plantas,
        ]);
    }

    public function listar_reporte(Request $request)
    {
        list($variedades, $fechas) = $this->obtener_reporte($request);

        return view('adminlte.gestion.postco.inventario_diario.partials.listado', [
            'listado' => $variedades,
            'fechas' => $fechas,
        ]);
    }

    public function exportar_reporte(Request $request)
    {
        $spread = new Spreadsheet();
        $this->excel_reporte($spread, $request);
        $fileName = "Inventario_Historico.xlsx";
        $writer = new Xlsx($spread);

        //--------------------------- GUARDAR EL EXCEL -----------------------

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        $writer->save('php://output');

        //$writer->save('/var/www/html/Dasalflor/storage/storage/excel/excel_prueba.xlsx');
    }

    public function excel_reporte($spread, $request)
    {
        list($listado, $fechas) = $this->obtener_reporte($request);

        $columnas = getColumnasExcel();
        $sheet = $spread->getActiveSheet();
        $sheet->setTitle('Reporte');

        $row = 1;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Planta');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Variedad');
        foreach ($fechas as $f) {
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, explode(' del ', convertDateToText($f))[0]);
        }
        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        foreach ($listado as $pos => $item) {
            if ($item->total_saldo > 0) {
                $row++;
                $col = 0;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->pta_nombre);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->var_nombre);
                foreach ($item->valores as $val) {
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $val['saldo']);
                }
            }
        }

        $lastColumn = $columnas[$col];
        $lastRow = max($row, 1);
        $range = 'A1:' . $lastColumn . $lastRow;
        $sheet->setShowGridlines(false);
        $sheet->getStyle($range)->applyFromArray([
            'alignment' => [
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR,
                    'color' => ['rgb' => 'DCE9E4'],
                ],
            ],
        ]);
        $sheet->getStyle('A1:' . $lastColumn . '1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '003729'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(34);

        for ($excelRow = 2; $excelRow <= $lastRow; $excelRow++) {
            if ($excelRow % 2 == 0) {
                $sheet->getStyle('A' . $excelRow . ':' . $lastColumn . $excelRow)
                    ->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
                $sheet->getStyle('A' . $excelRow . ':' . $lastColumn . $excelRow)
                    ->getFill()
                    ->getStartColor()
                    ->setRGB('F4FAF7');
            }
            $sheet->getStyle('C' . $excelRow . ':' . $lastColumn . $excelRow)
                ->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        }
        $sheet->getColumnDimension('A')->setWidth(24);
        $sheet->getColumnDimension('B')->setWidth(28);
        for ($i = 2; $i <= $col; $i++) {
            $sheet->getColumnDimension($columnas[$i])->setWidth(16);
            $sheet->getStyle($columnas[$i] . '1:' . $columnas[$i] . $lastRow)
                ->getBorders()
                ->getLeft()
                ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)
                ->getColor()
                ->setRGB('B8D8CC');
        }
        $sheet->freezePane('C2');
        if ($lastRow > 1) {
            $sheet->setAutoFilter($range);
        }
        $sheet->getPageSetup()
            ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE)
            ->setFitToPage(true)
            ->setFitToWidth(1)
            ->setFitToHeight(0);
        $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 1);
        $sheet->getPageSetup()->setPrintArea($range);
    }

    private function obtener_reporte(Request $request)
    {
        $finca = getFincaActiva();
        $fecha_minima = '2026-09-17';
        $variedades = DB::table('inventario_recepcion as i')
            ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->select('i.id_variedad', 'v.nombre as var_nombre', 'p.nombre as pta_nombre')
            ->where('i.id_empresa', $finca)
            ->where('i.bodega', $request->bodega)
            ->when($request->planta != '', function ($q) use ($request) {
                $q->where('v.id_planta', $request->planta);
            })
            ->when($request->variedad != '', function ($q) use ($request) {
                $q->where('i.id_variedad', $request->variedad);
            })
            ->groupBy('i.id_variedad', 'v.nombre', 'p.nombre')
            ->orderBy('p.nombre')
            ->orderBy('v.nombre')
            ->get();

        $desde = $request->desde >= $fecha_minima ? $request->desde : $fecha_minima;
        $hasta = $request->hasta;
        $fechas = [];
        for ($fecha = $desde; $fecha <= $hasta; $fecha = opDiasFecha('+', 1, $fecha)) {
            $fechas[] = $fecha;
        }

        if ($variedades->isEmpty() || empty($fechas)) {
            foreach ($variedades as $variedad) {
                $variedad->valores = [];
                $variedad->total_saldo = 0;
            }
            return [$variedades, $fechas];
        }

        $ids_variedades = $variedades->pluck('id_variedad')->all();
        $ingresos_diarios = DB::table('ingreso_recepcion')
            ->select('id_variedad', 'fecha')
            ->selectRaw('SUM(tallos) as cantidad')
            ->whereIn('id_variedad', $ids_variedades)
            ->where('id_empresa', $finca)
            ->where('bodega', $request->bodega)
            ->where('fecha', '>=', $fecha_minima)
            ->where('fecha', '<=', $hasta)
            ->groupBy('id_variedad', 'fecha')
            ->get()
            ->keyBy(function ($item) {
                return $item->id_variedad . '|' . $item->fecha;
            });

        $salidas_diarias = DB::table('salidas_recepcion as s')
            ->join('inventario_recepcion as i', 'i.id_inventario_recepcion', '=', 's.id_inventario_recepcion')
            ->select('s.id_variedad', 's.fecha')
            ->selectRaw("
                SUM(
                    s.cantidad +
                    CASE
                        WHEN s.orden_basura IS NOT NULL AND s.estado_orden_basura = 1
                        THEN s.basura
                        ELSE 0
                    END
                ) as cantidad
            ")
            ->whereIn('s.id_variedad', $ids_variedades)
            ->where('i.id_empresa', $finca)
            ->where('i.bodega', $request->bodega)
            ->where('s.fecha', '>=', $fecha_minima)
            ->where('s.fecha', '<=', $hasta)
            ->where('s.fecha_registro', '>=', $fecha_minima)
            ->groupBy('s.id_variedad', 's.fecha')
            ->get()
            ->keyBy(function ($item) {
                return $item->id_variedad . '|' . $item->fecha;
            });

        foreach ($variedades as $variedad) {
            $valores = [];
            $total_saldo = 0;
            $ingresos_acumulados = 0;
            $salidas_acumuladas = 0;

            for ($fecha = $fecha_minima; $fecha <= $hasta; $fecha = opDiasFecha('+', 1, $fecha)) {
                $clave = $variedad->id_variedad . '|' . $fecha;
                $ingreso_dia = $ingresos_diarios->get($clave);
                $salida_dia = $salidas_diarias->get($clave);
                $ingresos_acumulados += $ingreso_dia ? (float) $ingreso_dia->cantidad : 0;
                $salidas_acumuladas += $salida_dia ? (float) $salida_dia->cantidad : 0;
                if ($fecha >= $desde) {
                    $saldo = $ingresos_acumulados - $salidas_acumuladas;
                    $total_saldo += $saldo;
                    $valores[] = [
                        'saldo' => $saldo,
                        'ingresos' => $ingresos_acumulados,
                        'salidas' => $salidas_acumuladas,
                    ];
                }
            }

            $variedad->valores = $valores;
            $variedad->total_saldo = $total_saldo;
        }

        return [$variedades, $fechas];
    }
}
