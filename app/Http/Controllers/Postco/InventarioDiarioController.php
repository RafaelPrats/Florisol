<?php

namespace yura\Http\Controllers\Postco;

use DB;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
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
        $finca = getFincaActiva();

        $variedades = DB::table('inventario_recepcion as i')
            ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->select(
                'i.id_variedad',
                'v.nombre as var_nombre',
                'p.nombre as pta_nombre'
            )
            ->where('i.disponibles', '>', 0)
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

        $idsVariedad = $variedades->pluck('id_variedad')->toArray();

        $valores = DB::table('inventario_recepcion')
            ->select(
                'id_variedad',
                'fecha',
                DB::raw('SUM(disponibles) as tallos')
            )
            ->where('disponibles', '>', 0)
            ->where('id_empresa', $finca)
            ->where('bodega', $request->bodega)
            ->whereIn('id_variedad', $idsVariedad)
            ->groupBy('id_variedad', 'fecha')
            ->orderBy('fecha')
            ->get()
            ->groupBy('id_variedad');

        foreach ($variedades as $var) {
            $var->valores = $valores[$var->id_variedad] ?? collect();
        }

        return view('adminlte.gestion.postco.inventario_recepcion.partials.listado', [
            'variedades' => $variedades
        ]);
    }

    public function exportar_reporte(Request $request)
    {
        $spread = new Spreadsheet();
        $this->excel_reporte($spread, $request);
        $fileName = "Inventario.xlsx";
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
            ->where('i.disponibles', '>', 0)
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

        $idsVariedad = $variedades->pluck('id_variedad')->toArray();

        $valores = DB::table('inventario_recepcion')
            ->select(
                'id_variedad',
                'fecha',
                DB::raw('SUM(disponibles) as tallos')
            )
            ->where('disponibles', '>', 0)
            ->where('id_empresa', $finca)
            ->where('bodega', $request->bodega)
            ->whereIn('id_variedad', $idsVariedad)
            ->groupBy('id_variedad', 'fecha')
            ->orderBy('fecha')
            ->get()
            ->groupBy('id_variedad');

        foreach ($variedades as $var) {
            $var->valores = $valores[$var->id_variedad] ?? collect();
        }

        $columnas = getColumnasExcel();
        $sheet = $spread->getActiveSheet();
        $sheet->setTitle('INVENTARIO');

        $row = 1;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Planta');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Variedad');
        $total_fechas = [];
        $total_tallos = 0;
        for ($i = 0; $i <= 9; $i++) {
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, opDiasFecha('-', $i, hoy()) . ' ' . ($i == 9 ? '...' : ''));
            $total_fechas[] = 0;
        }
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Total');
        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        foreach ($variedades as $var) {
            $total_variedad = 0;
            $row++;
            $col = 0;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $var->pta_nombre);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $var->var_nombre);
            for ($i = 0; $i <= 9; $i++) {
                $fecha = opDiasFecha('-', $i, hoy());
                $valor = 0;
                foreach ($var->valores as $val) {
                    if ($i < 9) {
                        if ($val->fecha == $fecha) {
                            $valor += $val->tallos;
                        }
                    } else {
                        if ($val->fecha <= $fecha) {
                            $valor += $val->tallos;
                        }
                    }
                }
                $total_variedad += $valor;
                $total_tallos += $valor;
                $total_fechas[$i] += $valor;

                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $valor > 0 ? $valor : '');
            }
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_variedad);
        }
        $row++;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Totales');
        setBgToCeldaExcel($sheet, $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
        $col++;
        setBgToCeldaExcel($sheet, $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
        foreach ($total_fechas as $pos => $val) {
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $val);
            setBgToCeldaExcel($sheet, $columnas[$col] . $row, '5a7177');
            setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
        }
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_tallos);
        setBgToCeldaExcel($sheet, $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');

        setTextCenterToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);
        setBorderToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);

        for ($i = 0; $i <= $col; $i++)
            $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);
    }
}
