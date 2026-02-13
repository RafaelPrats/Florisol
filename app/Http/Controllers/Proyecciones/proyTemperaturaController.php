<?php

namespace yura\Http\Controllers\Proyecciones;

use Illuminate\Http\Request;
use yura\Http\Controllers\Controller;
use yura\Modelos\Ciclo;
use yura\Modelos\GrupoMenu;
use yura\Modelos\Sector;
use yura\Modelos\Submenu;
use yura\Modelos\Temperatura;
use Validator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class proyTemperaturaController extends Controller
{
    public function inicio(Request $request)
    {
        return view('adminlte.gestion.proyecciones.temperaturas.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
        ]);
    }

    public function kh_temperaturas(Request $request)
    {
        return view('adminlte.gestion.proyecciones.temperaturas.know_how', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'grupos_menu' => GrupoMenu::All(),
            'sectores' => Sector::All()->where('estado', 1)->where('interno', 1)->sortBy('nombre'),
        ]);
    }

    public function listar_ciclos(Request $request)
    {
        $finca = getFincaActiva();
        $query = Ciclo::where('estado', 1)
            ->where('activo', 1)
            ->where('id_variedad', $request->variedad)
            ->where('poda_siembra', $request->poda_siembra)
            ->where('id_empresa', $finca)
            ->orderBy('fecha_inicio', 'desc')
            ->get();    // ciclos activos
        $ciclos = [];
        $max_semana = 0;
        foreach ($query as $c) {
            $ini_curva = '';
            if ($c->getTallosCosechados(15) > 0)
                $ini_curva = $c->semana_poda_siembra;
            $temperaturas = $c->temperaturas;
            array_push($ciclos, [
                'ciclo' => $c,
                'ini_curva' => $ini_curva,
                'temperaturas' => $temperaturas,
            ]);
            $semana_fen = intval(difFechas($c->fecha_inicio, date('Y-m-d'))->days / 7) + 1;
            if ($max_semana < $semana_fen)
                $max_semana = $semana_fen;
        }
        return view('adminlte.gestion.proyecciones.temperaturas.partials.listado', [
            'ciclos' => $ciclos,
            'max_semana' => $max_semana,
            'sector' => $request->sector,
        ]);
    }

    public function add_temperatura(Request $request)
    {
        $finca = getFincaActiva();
        $temperatura = Temperatura::All()
            ->where('id_empresa', $finca)
            ->where('estado', 1)
            ->where('fecha', date('Y-m-d'))
            ->first();
        return view('adminlte.gestion.proyecciones.temperaturas.forms.add_temperatura', [
            'temperatura' => $temperatura,
        ]);
    }

    public function listar_temperaturas(Request $request)
    {
        $finca = getFincaActiva();
        $desde = $request->desde != '' ? $request->desde : opDiasFecha('-', 30, date('Y-m-d'));
        $hasta = $request->hasta != '' ? $request->hasta : date('Y-m-d');
        $query = Temperatura::where('estado', 1)
            ->where('fecha', '>=', $desde)
            ->where('fecha', '<=', $hasta)
            ->where('id_empresa', $finca)
            ->orderBy('fecha', 'desc')
            ->get();
        return view('adminlte.gestion.proyecciones.temperaturas.partials.table', [
            'desde' => $desde,
            'hasta' => $hasta,
            'listado' => $query,
        ]);
    }

    public function store_temperatura(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'fecha' => 'required',
            'maxima' => 'required',
            'minima' => 'required',
            'lluvia' => 'required',
        ], [
            'fecha.required' => 'La fecha es obligatoria',
            'maxima.required' => 'La máxima es obligatoria',
            'minima.required' => 'La mínima es obligatoria',
            'lluvia.required' => 'La lluvia es obligatoria',
        ]);
        if (!$valida->fails()) {
            $finca = getFincaActiva();
            $model = Temperatura::All()
                ->where('estado', 1)
                ->where('fecha', $request->fecha)
                ->where('id_empresa', $finca)
                ->first();
            if ($model == '') {
                $model = new Temperatura();
                $model->fecha = $request->fecha;
                $model->id_empresa = $finca;
                $id_model = '';
            } else
                $id_model = $model->id_temperatura;
            $model->maxima = $request->maxima;
            $model->minima = $request->minima;
            $model->lluvia = $request->lluvia;

            if ($model->save()) {
                $model = $id_model != '' ? Temperatura::find($id_model) : Temperatura::All()->last();
                $success = true;
                $msg = '<div class="alert alert-success text-center">' .
                    '<p> Se ha guardado una nueva temperatura satisfactoriamente</p>'
                    . '</div>';
                bitacora('temperatura', $model->id_temperatura, $id_model != '' ? 'U' : 'I', 'Inserción satisfactoria de una nueva temperatura');
            } else {
                $success = false;
                $msg = '<div class="alert alert-warning text-center">' .
                    '<p> Ha ocurrido un problema al guardar la información al sistema</p>'
                    . '</div>';
            }
        } else {
            $success = false;
            $errores = '';
            foreach ($valida->errors()->all() as $mi_error) {
                if ($errores == '') {
                    $errores = '<li>' . $mi_error . '</li>';
                } else {
                    $errores .= '<li>' . $mi_error . '</li>';
                }
            }
            $msg = '<div class="alert alert-danger">' .
                '<p class="text-center">¡Por favor corrija los siguientes errores!</p>' .
                '<ul>' .
                $errores .
                '</ul>' .
                '</div>';
        }
        return [
            'mensaje' => $msg,
            'success' => $success
        ];
    }

    public function buscar_temperatura(Request $request)
    {
        $finca = getFincaActiva();
        $temperatura = Temperatura::All()
            ->where('estado', 1)
            ->where('fecha', $request->fecha)
            ->where('id_empresa', $finca)
            ->first();
        return [
            'temperatura' => $temperatura
        ];
    }

    public function store_all_temperatura(Request $request)
    {
        $finca = getFincaActiva();
        $success = true;
        $msg = '';
        foreach ($request->data as $data) {
            if ($data['fecha'] != '' && $data['minima'] != '' && $data['maxima'] != '' && $data['lluvia'] != '') {
                $model = Temperatura::All()
                    ->where('estado', 1)
                    ->where('fecha', $data['fecha'])
                    ->where('id_empresa', $finca)
                    ->first();
                if ($model == '') {
                    $model = new Temperatura();
                    $model->fecha = $data['fecha'];
                    $model->id_empresa = $finca;
                    $id_model = '';
                } else
                    $id_model = $model->id_temperatura;
                $model->maxima = $data['maxima'];
                $model->minima = $data['minima'];
                $model->lluvia = $data['lluvia'];

                if ($model->save()) {
                    $model = $id_model != '' ? Temperatura::find($id_model) : Temperatura::All()->last();
                    bitacora('temperatura', $model->id_temperatura, $id_model != '' ? 'U' : 'I', 'Inserción satisfactoria de una temperatura');
                } else {
                    $success = false;
                    $msg = '<div class="alert alert-warning text-center">' .
                        '<p> Ha ocurrido un problema al guardar la información del día "' . $data['fecha'] . '"</p>'
                        . '</div>';
                    break;
                }
            }
        }
        return [
            'mensaje' => $msg,
            'success' => $success
        ];
    }

    public function exportar_reporte_temperatura(Request $request)
    {
        $spread = new Spreadsheet();
        $this->excel_reporte_temperatura($spread, $request);
        $spread->getProperties()
            ->setCreator("Benchflow")
            ->setTitle('Temperaturas')
            ->setSubject('Temperaturas');

        $fileName = "Temperaturas.xlsx";
        $writer = new Xlsx($spread);

        //--------------------------- GUARDAR EL EXCEL -----------------------

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        $writer->save('php://output');
    }

    public function excel_reporte_temperatura($spread, $request)
    {
        $finca = getFincaActiva();
        $desde = $request->desde != '' ? $request->desde : opDiasFecha('-', 30, date('Y-m-d'));
        $hasta = $request->hasta != '' ? $request->hasta : date('Y-m-d');
        $listado = Temperatura::where('estado', 1)
            ->where('fecha', '>=', $desde)
            ->where('fecha', '<=', $hasta)
            ->where('id_empresa', $finca)
            ->orderBy('fecha', 'desc')
            ->get();

        /* ----------------------- CREAR HOJA DE EXCEL ------------------------ */
        $objSheet = $spread->getActiveSheet()->setTitle('Proyecciones');

        $row = 1;
        setValueToCeldaExcel($objSheet, 'A' . $row, 'Fecha');
        setValueToCeldaExcel($objSheet, 'B' . $row, 'Máxima');
        setValueToCeldaExcel($objSheet, 'C' . $row, 'Mínima');
        setValueToCeldaExcel($objSheet, 'D' . $row, 'Delta');
        setValueToCeldaExcel($objSheet, 'E' . $row, 'Lluvia');

        setColorTextToCeldaExcel($objSheet, 'A' . $row . ':E' . $row, 'FFFFFF');    // blanco
        setBgToCeldaExcel($objSheet, 'A' . $row . ':E' . $row, '00b388');    // verde

        $prom_minima = 0;
        $prom_maxima = 0;
        $prom_delta = 0;
        $total_lluvia = 0;
        foreach ($listado as $item) {
            $row++;
            $prom_minima += $item->minima;
            $prom_maxima += $item->maxima;
            $prom_delta += ($item->maxima - $item->minima);
            $total_lluvia += $item->lluvia;

            setValueToCeldaExcel($objSheet, 'A' . $row, $item->fecha);
            setValueToCeldaExcel($objSheet, 'B' . $row, $item->maxima);
            setValueToCeldaExcel($objSheet, 'C' . $row, $item->minima);
            setValueToCeldaExcel($objSheet, 'D' . $row, $item->maxima - $item->minima);
            setValueToCeldaExcel($objSheet, 'E' . $row, $item->lluvia);
        }
        $row++;
        setValueToCeldaExcel($objSheet, 'A' . $row, 'TOTALES');
        setValueToCeldaExcel($objSheet, 'B' . $row, round($prom_maxima / count($listado), 2));
        setValueToCeldaExcel($objSheet, 'C' . $row, round($prom_minima / count($listado), 2));
        setValueToCeldaExcel($objSheet, 'D' . $row, round($prom_delta / count($listado), 2));
        setValueToCeldaExcel($objSheet, 'E' . $row, $total_lluvia);
        setColorTextToCeldaExcel($objSheet, 'A' . $row . ':E' . $row, 'FFFFFF');    // blanco
        setBgToCeldaExcel($objSheet, 'A' . $row . ':E' . $row, '00b388');    // verde

        setBorderToCeldaExcel($objSheet, 'A1:E' . $row);
        $objSheet->getColumnDimension('A')->setAutoSize(true);
        $objSheet->getColumnDimension('B')->setAutoSize(true);
        $objSheet->getColumnDimension('C')->setAutoSize(true);
        $objSheet->getColumnDimension('D')->setAutoSize(true);
        $objSheet->getColumnDimension('E')->setAutoSize(true);
    }
}