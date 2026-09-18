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
        $finca = getFincaActiva();
        $variedades = DB::table('inventario_recepcion as i')
            ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->select(
                'i.id_variedad',
                'v.nombre as var_nombre',
                'p.nombre as pta_nombre'
            )->distinct()
            ->where('i.id_empresa', $finca)
            ->where('i.bodega', $request->bodega)
            ->when($request->planta != '', function ($q) use ($request) {
                $q->where('v.id_planta', $request->planta);
            })
            ->when($request->variedad != '', function ($q) use ($request) {
                $q->where('i.id_variedad', $request->variedad);
            })
            ->groupBy(
                'i.id_variedad',
                'v.nombre',
                'p.nombre'
            )
            ->orderBy('p.nombre')
            ->orderBy('v.nombre')
            ->get();
        $desde = $request->desde >= '2026-09-17' ? $request->desde : '2026-09-17';
        $hasta = $request->hasta;
        $fechas = [];
        $f = $desde;
        while ($f <= $hasta) {
            $fechas[] = $f;
            $f = opDiasFecha('+', 1, $f);
        }

        foreach ($variedades as $var) {
            $valores = [];
            $total_saldo = 0;
            foreach ($fechas as $f) {
                $ingresos = DB::table('ingreso_recepcion')
                    ->select(
                        DB::raw('sum(tallos) as cantidad')
                    )
                    ->where('id_variedad', $var->id_variedad)
                    ->where('id_empresa', $finca)
                    ->where('bodega', $request->bodega)
                    ->where('fecha', '<=', $f)
                    ->where('fecha', '>=', '2026-09-17')
                    ->get()[0]->cantidad;
                $salidas = DB::table('salidas_recepcion as s')
                    ->join('inventario_recepcion as i', 'i.id_inventario_recepcion', '=', 's.id_inventario_recepcion')
                    ->select(
                        DB::raw("
        SUM(
            s.cantidad +
            CASE
                WHEN s.orden_basura IS NOT NULL
                     AND s.estado_orden_basura = 1
                THEN s.basura
                ELSE 0
            END
        ) as cantidad
    ")
                    )
                    ->where('s.id_variedad', $var->id_variedad)
                    ->where('i.id_empresa', $finca)
                    ->where('i.bodega', $request->bodega)
                    ->where('s.fecha', '<=', $f)
                    ->where('s.fecha', '>=', '2026-09-17')
                    ->where('s.fecha_registro', '>=', '2026-09-17')
                    ->get()[0]->cantidad;
                $saldo = $ingresos - $salidas;
                $total_saldo += $saldo;
                $valores[] = [
                    'saldo' => $saldo,
                    'ingresos' => $ingresos,
                    'salidas' => $salidas,
                ];
            }

            $var->valores = $valores;
            $var->total_saldo = $total_saldo;
        }
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
        $finca = getFincaActiva();
        $variedades = DB::table('inventario_recepcion as i')
            ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->select(
                'i.id_variedad',
                'v.nombre as var_nombre',
                'p.nombre as pta_nombre'
            )
            ->where('i.id_empresa', $finca)
            ->where('i.bodega', $request->bodega)
            ->when($request->planta != '', function ($q) use ($request) {
                $q->where('v.id_planta', $request->planta);
            })
            ->when($request->variedad != '', function ($q) use ($request) {
                $q->where('i.id_variedad', $request->variedad);
            })
            ->groupBy(
                'i.id_variedad',
                'v.nombre',
                'p.nombre'
            )
            ->orderBy('p.nombre')
            ->orderBy('v.nombre')
            ->get();
        $desde = $request->desde >= '2026-09-17' ? $request->desde : '2026-09-17';
        $hasta = $request->hasta;
        $fechas = [];
        $f = $desde;
        while ($f <= $hasta) {
            $fechas[] = $f;
            $f = opDiasFecha('+', 1, $f);
        }

        foreach ($variedades as $var) {
            $valores = [];
            $total_saldo = 0;
            foreach ($fechas as $f) {
                $ingresos = DB::table('ingreso_recepcion')
                    ->select(
                        DB::raw('sum(tallos) as cantidad')
                    )
                    ->where('id_variedad', $var->id_variedad)
                    ->where('id_empresa', $finca)
                    ->where('bodega', $request->bodega)
                    ->where('fecha', '<=', $f)
                    ->where('fecha', '>=', '2026-09-17')
                    ->get()[0]->cantidad;
                $salidas = DB::table('salidas_recepcion as s')
                    ->join('inventario_recepcion as i', 'i.id_inventario_recepcion', '=', 's.id_inventario_recepcion')
                    ->select(
                        DB::raw("
        SUM(
            s.cantidad +
            CASE
                WHEN s.orden_basura IS NOT NULL
                     AND s.estado_orden_basura = 1
                THEN s.basura
                ELSE 0
            END
        ) as cantidad
    ")
                    )
                    ->where('s.id_variedad', $var->id_variedad)
                    ->where('i.id_empresa', $finca)
                    ->where('i.bodega', $request->bodega)
                    ->where('s.fecha', '<=', $f)
                    ->where('s.fecha', '>=', '2026-09-17')
                    ->where('s.fecha_registro', '>=', '2026-09-17')
                    ->get()[0]->cantidad;
                $saldo = $ingresos - $salidas;
                $total_saldo += $saldo;
                $valores[] = [
                    'saldo' => $saldo,
                    'ingresos' => $ingresos,
                    'salidas' => $salidas,
                ];
            }

            $var->valores = $valores;
            $var->total_saldo = $total_saldo;
        }
        $listado = $variedades;

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

        setTextCenterToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);
        setBorderToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);

        for ($i = 0; $i <= $col; $i++)
            $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);
    }
}
