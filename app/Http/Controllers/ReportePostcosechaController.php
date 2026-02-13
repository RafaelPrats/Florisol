<?php

namespace yura\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use yura\Modelos\Planta;
use yura\Modelos\Submenu;
use yura\Modelos\Variedad;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportePostcosechaController extends Controller
{
    public function inicio(Request $request)
    {
        return view('adminlte.gestion.reporte_postcosecha.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'plantas' => Planta::where('estado', 1)->orderBy('nombre')->get()
        ]);
    }

    public function listar_reporte(Request $request)
    {
        $finca = getFincaActiva();
        $plantas = Planta::where('estado', 1);
        if ($request->planta != '')
            $plantas = $plantas->where('id_planta', $request->planta);
        $plantas = $plantas->orderBy('nombre')->get();
        $longitudes = [100, 90, 80, 70, 60, 50, 40];
        $listado = [];
        foreach ($plantas as $p) {
            $variedades = Variedad::where('estado', 1)
                ->where('id_planta', $p->id_planta);
            if ($request->variedad != '')
                $variedades = $variedades->where('id_variedad', $request->variedad);
            $variedades = $variedades->orderBy('nombre')
                ->get();
            $valores_var = [];
            foreach ($variedades as $v) {
                $valores_long = [];
                $tiene = false;
                foreach ($longitudes as $l) {
                    $cantidad = DB::table('inventario_frio as i')
                        ->join('clasificacion_ramo as l', 'l.id_clasificacion_ramo', '=', 'i.id_clasificacion_ramo')
                        ->select(DB::raw('sum(i.cantidad) as cantidad'))
                        //->where('i.disponibilidad', 1)
                        ->where('i.basura', 0)
                        ->where('i.estado', 1)
                        ->where('i.id_variedad', $v->id_variedad)
                        ->where('l.nombre', $l)
                        ->where('i.fecha', '>=', $request->desde)
                        ->where('i.fecha', '<=', $request->hasta)
                        ->where('i.id_empresa', $finca)
                        ->get()[0]->cantidad;
                    $valores_long[] = $cantidad;
                    if ($cantidad > 0)
                        $tiene = true;
                }
                if ($tiene)
                    $valores_var[] = [
                        'variedad' => $v,
                        'valores_long' => $valores_long,
                    ];
            }
            if (count($valores_var) > 0)
                $listado[] = [
                    'planta' => $p,
                    'valores_var' => $valores_var,
                ];
        }
        return view('adminlte.gestion.reporte_postcosecha.partials.listado', [
            'listado' => $listado,
            'longitudes' => $longitudes,
        ]);
    }

    public function exportar_reporte(Request $request)
    {
        $spread = new Spreadsheet();
        $this->excel_reporte($spread, $request);

        $fileName = "Postcosecha_" . $request->desde . "-" . $request->hasta . ".xlsx";
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
        $plantas = Planta::where('estado', 1);
        if ($request->planta != '')
            $plantas = $plantas->where('id_planta', $request->planta);
        $plantas = $plantas->orderBy('nombre')->get();
        $longitudes = [100, 90, 80, 70, 60, 50, 40];
        $listado = [];
        foreach ($plantas as $p) {
            $variedades = Variedad::where('estado', 1)
                ->where('id_planta', $p->id_planta);
            if ($request->variedad != '')
                $variedades = $variedades->where('id_variedad', $request->variedad);
            $variedades = $variedades->orderBy('nombre')
                ->get();
            $valores_var = [];
            foreach ($variedades as $v) {
                $valores_long = [];
                $tiene = false;
                foreach ($longitudes as $l) {
                    $cantidad = DB::table('inventario_frio as i')
                        ->join('clasificacion_ramo as l', 'l.id_clasificacion_ramo', '=', 'i.id_clasificacion_ramo')
                        ->select(DB::raw('sum(i.cantidad) as cantidad'))
                        //->where('i.disponibilidad', 1)
                        ->where('i.basura', 0)
                        ->where('i.estado', 1)
                        ->where('i.id_variedad', $v->id_variedad)
                        ->where('l.nombre', $l)
                        ->where('i.fecha', '>=', $request->desde)
                        ->where('i.fecha', '<=', $request->hasta)
                        ->where('i.id_empresa', $finca)
                        ->get()[0]->cantidad;
                    $valores_long[] = $cantidad;
                    if ($cantidad > 0)
                        $tiene = true;
                }
                if ($tiene)
                    $valores_var[] = [
                        'variedad' => $v,
                        'valores_long' => $valores_long,
                    ];
            }
            if (count($valores_var) > 0)
                $listado[] = [
                    'planta' => $p,
                    'valores_var' => $valores_var,
                ];
        }

        $columnas = getColumnasExcel();
        $sheet = $spread->getActiveSheet();
        $sheet->setTitle('Cuarto Frío');

        $row = 1;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Variedad/Medida');
        setBgToCeldaExcel($sheet, $columnas[$col] . $row, '00b388');
        $totales_longitudes = [];
        foreach ($longitudes as $l) {
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $l);
            setBgToCeldaExcel($sheet, $columnas[$col] . $row, '5a7177');
            $totales_longitudes[] = 0;
        }
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Total');
        setBgToCeldaExcel($sheet, $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row, 'ffffff');

        foreach ($listado as $item) {
            foreach ($item['valores_var'] as $var) {
                $row++;
                $col = 0;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $var['variedad']->nombre);
                setBgToCeldaExcel($sheet, $columnas[$col] . $row, '5a7177');
                setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
                $total_var = 0;
                foreach ($var['valores_long'] as $pos => $val) {
                    $total_var += $val;
                    $totales_longitudes[$pos] += $val;
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $val);
                }
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_var);
                setBgToCeldaExcel($sheet, $columnas[$col] . $row, '5a7177');
                setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
            }
        }
        $row++;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TOTALES');
        setBgToCeldaExcel($sheet, $columnas[$col] . $row, '00b388');
        $total = 0;
        foreach ($totales_longitudes as $v) {
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $v);
            setBgToCeldaExcel($sheet, $columnas[$col] . $row, '5a7177');
            $total += $v;
        }
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total);
        setBgToCeldaExcel($sheet, $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, 'A' . $row . ':' . $columnas[$col] . $row, 'ffffff');

        setTextCenterToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);

        setBorderToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);

        for ($i = 0; $i <= $col; $i++)
            $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);
    }
}
