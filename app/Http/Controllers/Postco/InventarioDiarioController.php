<?php

namespace yura\Http\Controllers\Postco;

use DB;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use yura\Http\Controllers\Controller;
use yura\Modelos\Planta;
use yura\Modelos\Submenu;

class InventarioDiarioController extends Controller
{
    public function inicio(Request $request)
    {
        $finca = getFincaActiva();
        $plantas = Planta::where('estado', 1)
            ->where('id_empresa', $finca)
            ->orderBy('nombre')
            ->get();

        return view('adminlte.gestion.postco.inventario_recepcion.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'plantas' => $plantas,
        ]);
    }

    public function listar_reporte(Request $request)
    {
        $reporte = $this->obtener_reporte($request);

        return view('adminlte.gestion.postco.inventario_recepcion.partials.listado', $reporte);
    }

    public function exportar_reporte(Request $request)
    {
        $spread = new Spreadsheet();
        $this->excel_reporte($spread, $request);
        $fileName = 'Inventario.xlsx';
        $writer = new Xlsx($spread);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        $writer->save('php://output');
    }

    public function excel_reporte($spread, $request)
    {
        $reporte = $this->obtener_reporte($request);
        $variedades = $reporte['variedades'];
        $totales = $reporte['totales'];
        $columnas = getColumnasExcel();
        $sheet = $spread->getActiveSheet();
        $sheet->setTitle('INVENTARIO');

        $headers = ['Planta', 'Variedad'];
        foreach ($reporte['periodos'] as $periodo) {
            $headers[] = $periodo['excel_label'];
        }
        $headers[] = 'Total';

        foreach ($headers as $index => $header) {
            setValueToCeldaExcel($sheet, $columnas[$index] . '1', $header);
        }

        $lastColumn = $columnas[count($headers) - 1];
        $row = 2;
        foreach ($variedades as $variedad) {
            setValueToCeldaExcel($sheet, 'A' . $row, $variedad->pta_nombre);
            setValueToCeldaExcel($sheet, 'B' . $row, $variedad->var_nombre);
            foreach ($variedad->valores as $index => $valor) {
                setValueToCeldaExcel($sheet, $columnas[$index + 2] . $row, $valor > 0 ? $valor : '');
            }
            setValueToCeldaExcel($sheet, $lastColumn . $row, $variedad->total_tallos);
            $row++;
        }

        $totalRow = $row;
        setValueToCeldaExcel($sheet, 'A' . $totalRow, 'Totales');
        foreach ($totales as $index => $total) {
            setValueToCeldaExcel($sheet, $columnas[$index + 2] . $totalRow, $total);
        }
        setValueToCeldaExcel($sheet, $lastColumn . $totalRow, array_sum($totales));

        $lastRow = max($totalRow, 1);
        $range = 'A1:' . $lastColumn . $lastRow;
        $sheet->setShowGridlines(false);
        $sheet->getStyle($range)->applyFromArray([
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_HAIR,
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
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '003729'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(36);

        for ($dataRow = 2; $dataRow < $totalRow; $dataRow++) {
            if ($dataRow % 2 == 0) {
                $sheet->getStyle('A' . $dataRow . ':' . $lastColumn . $dataRow)
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID);
                $sheet->getStyle('A' . $dataRow . ':' . $lastColumn . $dataRow)
                    ->getFill()
                    ->getStartColor()
                    ->setRGB('F4FAF7');
            }
            $sheet->getStyle('C' . $dataRow . ':' . $lastColumn . $dataRow)
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }

        $sheet->getStyle('A' . $totalRow . ':' . $lastColumn . $totalRow)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '5A7177'],
            ],
        ]);
        $sheet->getColumnDimension('A')->setWidth(24);
        $sheet->getColumnDimension('B')->setWidth(28);
        for ($index = 2; $index < count($headers) - 1; $index++) {
            $sheet->getColumnDimension($columnas[$index])->setWidth(15);
            $sheet->getStyle($columnas[$index] . '1:' . $columnas[$index] . $lastRow)
                ->getBorders()
                ->getLeft()
                ->setBorderStyle(Border::BORDER_THIN)
                ->getColor()
                ->setRGB('B8D8CC');
        }
        $sheet->getColumnDimension($lastColumn)->setWidth(16);
        $sheet->freezePane('C2');
        if ($variedades->isNotEmpty()) {
            $sheet->setAutoFilter('A1:' . $lastColumn . ($totalRow - 1));
        }
        $sheet->getPageSetup()
            ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
            ->setFitToPage(true)
            ->setFitToWidth(1)
            ->setFitToHeight(0);
        $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 1);
        $sheet->getPageSetup()->setPrintArea($range);
    }

    private function obtener_reporte(Request $request)
    {
        $finca = getFincaActiva();
        $hoy = hoy();
        $periodos = [];
        $indices_fecha = [];

        for ($i = 0; $i < 9; $i++) {
            $fecha = opDiasFecha('-', $i, $hoy);
            $periodos[] = [
                'fecha' => $fecha,
                'label' => ($i == 0 ? 'Hoy ' : '') . date('d/m', strtotime($fecha)),
                'excel_label' => $fecha,
                'title' => convertDateToText($fecha),
            ];
            $indices_fecha[$fecha] = $i;
        }
        $fecha_limite_acumulado = opDiasFecha('-', 9, $hoy);
        $periodos[] = [
            'fecha' => $fecha_limite_acumulado,
            'label' => '9+ días',
            'excel_label' => 'Hasta ' . $fecha_limite_acumulado,
            'title' => 'Inventario con fecha hasta ' . convertDateToText($fecha_limite_acumulado),
        ];

        $variedades = DB::table('inventario_recepcion as i')
            ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->select('i.id_variedad', 'v.nombre as var_nombre', 'p.nombre as pta_nombre')
            ->where('i.disponibles', '>', 0)
            ->where('i.id_empresa', $finca)
            ->where('i.bodega', $request->bodega)
            ->when($request->planta != '', function ($query) use ($request) {
                $query->where('v.id_planta', $request->planta);
            })
            ->when($request->variedad != '', function ($query) use ($request) {
                $query->where('i.id_variedad', $request->variedad);
            })
            ->groupBy('i.id_variedad', 'v.nombre', 'p.nombre')
            ->orderBy('p.nombre')
            ->orderBy('v.nombre')
            ->get();

        $totales = array_fill(0, 10, 0);
        foreach ($variedades as $variedad) {
            $variedad->valores = array_fill(0, 10, 0);
            $variedad->total_tallos = 0;
        }

        if ($variedades->isNotEmpty()) {
            $ids_variedades = $variedades->pluck('id_variedad')->all();
            $inventario_diario = DB::table('inventario_recepcion')
                ->select('id_variedad', 'fecha')
                ->selectRaw('SUM(disponibles) as tallos')
                ->where('disponibles', '>', 0)
                ->where('id_empresa', $finca)
                ->where('bodega', $request->bodega)
                ->whereIn('id_variedad', $ids_variedades)
                ->where('fecha', '<=', $hoy)
                ->groupBy('id_variedad', 'fecha')
                ->orderBy('fecha')
                ->get();

            $variedades_por_id = $variedades->keyBy('id_variedad');
            foreach ($inventario_diario as $registro) {
                if (isset($indices_fecha[$registro->fecha])) {
                    $indice = $indices_fecha[$registro->fecha];
                } elseif ($registro->fecha <= $fecha_limite_acumulado) {
                    $indice = 9;
                } else {
                    continue;
                }

                $cantidad = (float) $registro->tallos;
                $variedad = $variedades_por_id->get($registro->id_variedad);
                $variedad->valores[$indice] += $cantidad;
                $variedad->total_tallos += $cantidad;
                $totales[$indice] += $cantidad;
            }
        }

        return [
            'variedades' => $variedades,
            'periodos' => $periodos,
            'totales' => $totales,
            'total_tallos' => array_sum($totales),
        ];
    }
}
