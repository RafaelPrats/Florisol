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
        $sheet->setTitle('KARDEX');

        $row = 1;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Bitacora de ' . $variedad->nombre . ' desde ' . explode(' del ', convertDateToText($desde))[0] . ' al ' . explode(' del ', convertDateToText($hasta))[0] . ' en ' . $bodega);
        $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col + 6] . $row);

        $row++;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Fecha Registro');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Tipo');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Fecha');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Documento');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Usuario');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Detalle');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Entrada');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Salida');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Saldo: ' . $saldo);
        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        foreach ($listado as $pos => $item) {
            if ($item->tipo == 'I') {
                $saldo += $item->cantidad;
            }
            if ($item->tipo == 'S') {
                $saldo -= $item->cantidad;
            }
            $row++;
            $col = 0;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->fecha_registro);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->tipo == 'I' ? 'INGRESO' : 'SALIDA');
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, explode(' del ', convertDateToText($item->fecha))[0]);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->concepto . '-' . $item->numero);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->usuario->username);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->descripcion);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->tipo == 'I' ? $item->cantidad : '');
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->tipo == 'S' ? $item->cantidad : '');
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $saldo);
        }

        setTextCenterToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);
        setBorderToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);

        for ($i = 0; $i <= $col; $i++)
            $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);
    }
}
