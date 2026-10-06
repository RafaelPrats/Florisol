<?php

namespace yura\Http\Controllers\Postco;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use yura\Http\Controllers\Controller;
use yura\Modelos\Planta;
use yura\Modelos\RegistroMovimientos;
use yura\Modelos\Submenu;
use yura\Modelos\Variedad;

class BitacoraController extends Controller
{
    public function inicio(Request $request)
    {
        $finca = getFincaActiva();
        $plantas = Planta::where('estado', 1)
            ->where('id_empresa', $finca)
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.postco.bitacora_recepcion.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'plantas' => $plantas,
        ]);
    }

    public function listar_reporte(Request $request)
    {
        $finca = getFincaActiva();
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

        $variedad = Variedad::find($request->variedad);
        $listado = RegistroMovimientos::where('fecha', '>=', $request->desde)
            ->where('fecha', '<=', $request->hasta)
            ->where('id_empresa', $finca)
            ->where('bodega', $request->bodega)
            ->where('id_variedad', $request->variedad)
            ->orderBy('fecha_registro')
            ->get();
        return view('adminlte.gestion.postco.bitacora_recepcion.partials.listado', [
            'listado' => $listado,
            'variedad' => $variedad,
            'saldo' => $saldo_inicial,
            'desde' => $desde,
            'hasta' => $hasta,
            'bodega' => $request->bodega == 'V' ? 'Ventas' : 'Produccion',
        ]);
    }

    public function exportar_reporte(Request $request)
    {
        $spread = new Spreadsheet();
        $this->excel_reporte($spread, $request);
        $fileName = "Bitacora.xlsx";
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
        $bodega = $request->bodega == 'V' ? 'Ventas' : 'Produccion';
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
        $saldo = $ingreso_inicial - $salida_inicial;

        $variedad = Variedad::find($request->variedad);
        $listado = RegistroMovimientos::where('fecha', '>=', $request->desde)
            ->where('fecha', '<=', $request->hasta)
            ->where('id_empresa', $finca)
            ->where('bodega', $request->bodega)
            ->where('id_variedad', $request->variedad)
            ->orderBy('fecha_registro')
            ->get();

        $columnas = getColumnasExcel();
        $sheet = $spread->getActiveSheet();
        $sheet->setTitle('BITACORA');

        $saldo_inicial = $saldo;
        $total_ingresos = 0;
        $total_salidas = 0;
        foreach ($listado as $item) {
            if ($item->tipo == 'I') {
                $total_ingresos += $item->cantidad;
            } else {
                $total_salidas += $item->cantidad;
            }
        }
        $saldo_final = $saldo_inicial + $total_ingresos - $total_salidas;

        $sheet->mergeCells('A1:I1');
        setValueToCeldaExcel($sheet, 'A1', 'BITÁCORA DE RECEPCIÓN · ' . mb_strtoupper($variedad->nombre));
        $sheet->getRowDimension(1)->setRowHeight(30);
        $sheet->getStyle('A1:I1')->applyFromArray([
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

        $sheet->mergeCells('A2:I2');
        setValueToCeldaExcel(
            $sheet,
            'A2',
            'Variedad: ' . $variedad->nombre .
                '    |    Periodo: ' . explode(' del ', convertDateToText($desde))[0] .
                ' al ' . explode(' del ', convertDateToText($hasta))[0] .
                '    |    Bodega: ' . $bodega
        );
        $sheet->getRowDimension(2)->setRowHeight(22);
        $sheet->getStyle('A2:I2')->applyFromArray([
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

        $sheet->mergeCells('A3:I3');
        setValueToCeldaExcel(
            $sheet,
            'A3',
            'SALDO INICIAL: ' . number_format($saldo_inicial) .
                '     |     ENTRADAS: +' . number_format($total_ingresos) .
                '     |     SALIDAS: -' . number_format($total_salidas) .
                '     |     SALDO AL CIERRE: ' . number_format($saldo_final)
        );
        $sheet->getRowDimension(3)->setRowHeight(24);
        $sheet->getStyle('A3:I3')->applyFromArray([
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

        $headers = ['Fecha registro', 'Tipo', 'Fecha', 'Documento', 'Usuario', 'Detalle', 'Entrada', 'Salida', 'Saldo'];
        foreach ($headers as $col => $header) {
            setValueToCeldaExcel($sheet, $columnas[$col] . '5', $header);
        }
        $sheet->getRowDimension(5)->setRowHeight(23);
        $sheet->getStyle('A5:I5')->applyFromArray([
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
        $sheet->getStyle('G5')->getFill()->getStartColor()->setRGB('00B388');
        $sheet->getStyle('H5')->getFill()->getStartColor()->setRGB('D01C62');

        $row = 5;
        $fecha_anterior = null;
        $grupo_fecha = 0;
        foreach ($listado as $item) {
            if ($item->tipo == 'I') {
                $saldo += $item->cantidad;
            } else {
                $saldo -= $item->cantidad;
            }

            $fecha_registro = substr($item->fecha_registro, 0, 10);
            $fecha_inicio = $fecha_anterior != $fecha_registro;
            if ($fecha_inicio && $fecha_anterior !== null) {
                $grupo_fecha = !$grupo_fecha;
            }
            $fecha_anterior = $fecha_registro;

            $row++;
            setValueToCeldaExcel($sheet, 'A' . $row, $item->fecha_registro);
            setValueToCeldaExcel($sheet, 'B' . $row, $item->tipo == 'I' ? 'INGRESO' : 'SALIDA');
            setValueToCeldaExcel($sheet, 'C' . $row, explode(' del ', convertDateToText($item->fecha))[0]);
            setValueToCeldaExcel($sheet, 'D' . $row, $item->concepto . '-' . $item->numero);
            setValueToCeldaExcel($sheet, 'E' . $row, $item->usuario->username);
            setValueToCeldaExcel($sheet, 'F' . $row, $item->descripcion ?: '—');
            setValueToCeldaExcel($sheet, 'G' . $row, $item->tipo == 'I' ? $item->cantidad : '');
            setValueToCeldaExcel($sheet, 'H' . $row, $item->tipo == 'S' ? $item->cantidad : '');
            setValueToCeldaExcel($sheet, 'I' . $row, $saldo);

            $sheet->getStyle('A' . $row . ':I' . $row)->applyFromArray([
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
                $sheet->getStyle('A' . $row . ':I' . $row)->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('F0F7F4');
            }
            if ($fecha_inicio) {
                $sheet->getStyle('A' . $row . ':I' . $row)->getBorders()->getTop()
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
                    'startColor' => ['rgb' => $item->tipo == 'I' ? '00B388' : 'D01C62'],
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
            ]);
            $sheet->getStyle('G' . $row)->applyFromArray([
                'font' => [
                    'bold' => $item->tipo == 'I',
                    'color' => ['rgb' => '009874'],
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                ],
                'numberFormat' => [
                    'formatCode' => '#,##0;[Red]-#,##0',
                ],
            ]);
            $sheet->getStyle('H' . $row)->applyFromArray([
                'font' => [
                    'bold' => $item->tipo == 'S',
                    'color' => ['rgb' => 'D01C62'],
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                ],
                'numberFormat' => [
                    'formatCode' => '#,##0;[Red]-#,##0',
                ],
            ]);
            $sheet->getStyle('I' . $row)->applyFromArray([
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
        $sheet->getStyle('A5:I' . $last_row)->getBorders()->getVertical()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR)
            ->getColor()->setRGB('E6ECE9');
        $sheet->setAutoFilter('A5:I' . $last_row);
        $sheet->freezePane('A6');
        $sheet->setShowGridlines(false);
        $sheet->getPageSetup()
            ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE)
            ->setFitToPage(true)
            ->setFitToWidth(1)
            ->setFitToHeight(0);
        $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 5);

        $anchos = [
            'A' => 20,
            'B' => 14,
            'C' => 16,
            'D' => 22,
            'E' => 20,
            'F' => 48,
            'G' => 15,
            'H' => 15,
            'I' => 16,
        ];
        foreach ($anchos as $columna => $ancho) {
            $sheet->getColumnDimension($columna)->setWidth($ancho);
        }
        if ($last_row > 5) {
            $sheet->getStyle('G6:I' . $last_row)->getNumberFormat()
                ->setFormatCode('#,##0;[Red]-#,##0');
        }
    }
}
