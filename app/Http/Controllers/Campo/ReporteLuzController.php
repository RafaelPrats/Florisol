<?php

namespace yura\Http\Controllers\Campo;

use Illuminate\Http\Request;
use yura\Http\Controllers\Controller;
use yura\Modelos\Ciclo;
use yura\Modelos\CicloLuz;
use yura\Modelos\Planta;
use yura\Modelos\Submenu;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use yura\Modelos\Sector;

class ReporteLuzController extends Controller
{
    public function inicio(Request $request)
    {
        $semana_actual = getSemanaByDate(hoy());
        $sectores = Sector::where('estado', 1)
            ->where('id_empresa', getFincaActiva())
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.campo.reporte_luz.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'semana_actual' => $semana_actual,
            'sectores' => $sectores,
        ]);
    }

    public function listar_reporte_luz(Request $request)
    {
        $finca = getFincaActiva();
        $semana = getObjSemana($request->semana);
        $ciclos = Ciclo::join('variedad as v', 'v.id_variedad', '=', 'ciclo.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->join('modulo as m', 'm.id_modulo', '=', 'ciclo.id_modulo')
            ->select('ciclo.*')->distinct()
            ->where('ciclo.estado', 1)
            ->where('v.estado', 1)
            ->where('p.estado', 1)
            ->where('ciclo.activo', 1)
            ->where('p.tiene_ciclos', 1)
            ->where('ciclo.id_empresa', $finca)
            ->where('m.id_sector', $request->sector)
            ->orderBy('v.nombre')
            ->orderBy('ciclo.fecha_inicio')
            ->get();
        $entradas = [];
        $salidas = [];
        $activos = [];

        foreach ($ciclos as $c) {
            if ($semana->codigo == getSemanaByDate(hoy())->codigo)  // semana actual
                $luz = $c->getLuzBySemana($semana);
            else
                $luz = CicloLuz::where('id_ciclo', $c->id_ciclo)
                    ->where('fecha', hoy())
                    ->first();
            if ($luz != '') {
                $dias_ciclo = difFechas(hoy(), $c->fecha_inicio)->days;
                $dias_luz = 0;
                if ($luz->inicio_luz <= $dias_ciclo)
                    if (($luz->dias_proy + $luz->dias_adicional) >= $dias_ciclo - $luz->inicio_luz)
                        $dias_luz = $dias_ciclo - $luz->inicio_luz;
                    else
                        $dias_luz = ($luz->dias_proy + $luz->dias_adicional);
                $inicio_luz = opDiasFecha('+', $luz->inicio_luz, $c->fecha_inicio);
                $semana_inicio_luz = getSemanaByDate($inicio_luz);
                $fin_luz = opDiasFecha('+', $luz->inicio_luz + $luz->dias_proy + $luz->dias_adicional - 1, $c->fecha_inicio);
                if ($semana_inicio_luz->codigo == $semana->codigo)
                    array_push($entradas, $luz);
                else if (/*$dias_luz > 0 && */getSemanaByDate($fin_luz)->codigo == $semana->codigo)
                    array_push($salidas, $luz);
                else if (/*$dias_luz > 0 && */$fin_luz >= $semana->fecha_inicial && $semana->codigo >= $semana_inicio_luz->codigo && !in_array($luz, $salidas))
                    array_push($activos, $luz);
            }
        }

        /* order by fecha_inicio */
        if (count($entradas) > 0) {
            for ($i = 0; $i < count($entradas) - 1; $i++) {
                for ($y = $i + 1; $y < count($entradas); $y++) {
                    $ciclo_i = $entradas[$i]->ciclo;
                    $inicio_luz_i = opDiasFecha('+', $entradas[$i]->inicio_luz, $ciclo_i->fecha_inicio);
                    $ciclo_y = $entradas[$y]->ciclo;
                    $inicio_luz_y = opDiasFecha('+', $entradas[$y]->inicio_luz, $ciclo_y->fecha_inicio);
                    if ($inicio_luz_i > $inicio_luz_y) {
                        $temp = $entradas[$i];
                        $entradas[$i] = $entradas[$y];
                        $entradas[$y] = $temp;
                    }
                }
            }
        }
        if (count($activos) > 0) {
            for ($i = 0; $i < count($activos) - 1; $i++) {
                for ($y = $i + 1; $y < count($activos); $y++) {
                    $ciclo_i = $activos[$i]->ciclo;
                    $fin_luz_i = opDiasFecha('+', $activos[$i]->inicio_luz + $activos[$i]->dias_proy + $activos[$i]->dias_adicional - 1, $ciclo_i->fecha_inicio);
                    $ciclo_y = $activos[$y]->ciclo;
                    $fin_luz_y = opDiasFecha('+', $activos[$y]->inicio_luz + $activos[$y]->dias_proy + $activos[$y]->dias_adicional - 1, $ciclo_y->fecha_inicio);
                    if ($fin_luz_i > $fin_luz_y) {
                        $temp = $activos[$i];
                        $activos[$i] = $activos[$y];
                        $activos[$y] = $temp;
                    }
                }
            }
        }
        if (count($salidas) > 0) {
            for ($i = 0; $i < count($salidas) - 1; $i++) {
                for ($y = $i + 1; $y < count($salidas); $y++) {
                    $ciclo_i = $salidas[$i]->ciclo;
                    $fin_luz_i = opDiasFecha('+', $salidas[$i]->inicio_luz + $salidas[$i]->dias_proy + $salidas[$i]->dias_adicional - 1, $ciclo_i->fecha_inicio);
                    $ciclo_y = $salidas[$y]->ciclo;
                    $fin_luz_y = opDiasFecha('+', $salidas[$y]->inicio_luz + $salidas[$y]->dias_proy + $salidas[$y]->dias_adicional - 1, $ciclo_y->fecha_inicio);
                    if ($fin_luz_i > $fin_luz_y) {
                        $temp = $salidas[$i];
                        $salidas[$i] = $salidas[$y];
                        $salidas[$y] = $temp;
                    }
                }
            }
        }
        return view('adminlte.gestion.campo.reporte_luz.partials.listado', [
            'entradas' => $entradas,
            'salidas' => $salidas,
            'activos' => $activos,
            'semana' => $semana,
        ]);
    }

    public function listar_row_luz(Request $request)
    {
        $luz = CicloLuz::find($request->id);
        $ciclo = $luz->ciclo;
        //$modulo = $ciclo->modulo;
        $dias_ciclo = difFechas(hoy(), $ciclo->fecha_inicio)->days;
        $inicio_luz = opDiasFecha('+', $luz->inicio_luz, $ciclo->fecha_inicio);
        $fin_luz = opDiasFecha('+', $luz->inicio_luz + $luz->dias_proy + $luz->dias_adicional - 1, $ciclo->fecha_inicio);
        $dias_luz = 0;
        if ($luz->inicio_luz <= $dias_ciclo)
            if (($luz->dias_proy + $luz->dias_adicional) >= $dias_ciclo - $luz->inicio_luz)
                $dias_luz = $dias_ciclo - $luz->inicio_luz;
            else
                $dias_luz = ($luz->dias_proy + $luz->dias_adicional);
        $horas_dia = isset($luz) ? $luz->getHorasDia() : 0;
        // calcular horas luz
        $horas_luz = $dias_luz * $horas_dia;
        $costo_luz = 0;
        $costo_x_tipo = $luz->tipo_luz / 1000;
        $costo_x_lampara = $costo_x_tipo * $luz->lamparas;
        $costo_x_lampara = $costo_x_lampara * $horas_luz;
        $costo_luz = $costo_x_lampara * 0.10;
        $costo_m2 = round($costo_luz / $ciclo->area, 4) * 100;

        return [
            'ini_luz' => convertDateToText($inicio_luz),
            'sem_ini_luz' => getSemanaByDate($inicio_luz)->codigo,
            'fin_luz' => convertDateToText($fin_luz),
            'sem_fin_luz' => getSemanaByDate($fin_luz)->codigo,
            'horas_luz' => $horas_luz,
            'costo_luz' => $costo_luz,
            'costo_m2' => $costo_m2,
        ];
    }

    public function exportar_reporte(Request $request)
    {
        $spread = new Spreadsheet();
        $this->excel_reporte($spread, $request);
        $spread->getProperties()
            ->setTitle('Reporte_Luz');

        $fileName = "Reporte_Luz.xlsx";
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
        $semana = getObjSemana($request->semana);
        $ciclos = Ciclo::join('variedad as v', 'v.id_variedad', '=', 'ciclo.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->join('modulo as m', 'm.id_modulo', '=', 'ciclo.id_modulo')
            ->select('ciclo.*')->distinct()
            ->where('ciclo.estado', 1)
            ->where('v.estado', 1)
            ->where('p.estado', 1)
            ->where('ciclo.activo', 1)
            ->where('p.tiene_ciclos', 1)
            ->where('ciclo.id_empresa', $finca)
            ->where('m.id_sector', $request->sector)
            ->orderBy('v.nombre')
            ->orderBy('ciclo.fecha_inicio')
            ->get();
        $entradas = [];
        $salidas = [];
        foreach ($ciclos as $c) {
            $luz = $c->getLuzBySemana($semana);
            if ($luz != '') {
                $dias_ciclo = difFechas(hoy(), $c->fecha_inicio)->days;
                $dias_luz = 0;
                if ($luz->inicio_luz <= $dias_ciclo)
                    if (($luz->dias_proy + $luz->dias_adicional) >= $dias_ciclo - $luz->inicio_luz)
                        $dias_luz = $dias_ciclo - $luz->inicio_luz;
                    else
                        $dias_luz = ($luz->dias_proy + $luz->dias_adicional);
                $inicio_luz = opDiasFecha('+', $luz->inicio_luz, $c->fecha_inicio);
                $fin_luz = opDiasFecha('+', $luz->inicio_luz + $luz->dias_proy + $luz->dias_adicional - 1, $c->fecha_inicio);
                if (getSemanaByDate($inicio_luz)->codigo == $semana->codigo)
                    array_push($entradas, $luz);
                else if (/*$dias_luz > 0 && */getSemanaByDate($fin_luz)->codigo == $semana->codigo)
                    array_push($salidas, $luz);
            }
        }

        /* order by fecha_inicio */
        if (count($entradas) > 0) {
            for ($i = 0; $i < count($entradas) - 1; $i++) {
                for ($y = $i + 1; $y < count($entradas); $y++) {
                    $ciclo_i = $entradas[$i]->ciclo;
                    $inicio_luz_i = opDiasFecha('+', $entradas[$i]->inicio_luz, $ciclo_i->fecha_inicio);
                    $ciclo_y = $entradas[$y]->ciclo;
                    $inicio_luz_y = opDiasFecha('+', $entradas[$y]->inicio_luz, $ciclo_y->fecha_inicio);
                    if ($inicio_luz_i > $inicio_luz_y) {
                        $temp = $entradas[$i];
                        $entradas[$i] = $entradas[$y];
                        $entradas[$y] = $temp;
                    }
                }
            }
        }
        if (count($salidas) > 0) {
            for ($i = 0; $i < count($salidas) - 1; $i++) {
                for ($y = $i + 1; $y < count($salidas); $y++) {
                    $ciclo_i = $salidas[$i]->ciclo;
                    $fin_luz_i = opDiasFecha('+', $salidas[$i]->inicio_luz + $salidas[$i]->dias_proy + $salidas[$i]->dias_adicional - 1, $ciclo_i->fecha_inicio);
                    $ciclo_y = $salidas[$y]->ciclo;
                    $fin_luz_y = opDiasFecha('+', $salidas[$y]->inicio_luz + $salidas[$y]->dias_proy + $salidas[$y]->dias_adicional - 1, $ciclo_y->fecha_inicio);
                    if ($fin_luz_i > $fin_luz_y) {
                        $temp = $salidas[$i];
                        $salidas[$i] = $salidas[$y];
                        $salidas[$y] = $temp;
                    }
                }
            }
        }

        /* -------------------- CREAR HOJA EXCEL -------------------- */
        $objSheet = $spread->getActiveSheet()->setTitle('Reporte_Luz Semana ' . $request->semana);
        /* CICLOS ENTRANTES */

        setValueToCeldaExcel($objSheet, 'A1', 'Ciclos ENTRANTES');
        setBgToCeldaExcel($objSheet, 'A1', '5a7177');  // dark
        setColorTextToCeldaExcel($objSheet, 'A1', 'FFFFFF');  // blanco
        $objSheet->mergeCells('A1:P1');
        $row = 2;
        setValueToCeldaExcel($objSheet, 'A' . $row, 'Variedad');
        setValueToCeldaExcel($objSheet, 'B' . $row, 'Tipo');
        setValueToCeldaExcel($objSheet, 'C' . $row, 'Módulo');
        setValueToCeldaExcel($objSheet, 'D' . $row, 'Poda');
        setValueToCeldaExcel($objSheet, 'E' . $row, 'Fecha Poda');
        setValueToCeldaExcel($objSheet, 'F' . $row, 'Días');
        setValueToCeldaExcel($objSheet, 'G' . $row, 'Tipo Luz');
        setValueToCeldaExcel($objSheet, 'H' . $row, '# Lamp.');
        setValueToCeldaExcel($objSheet, 'I' . $row, 'Día Ini. Luz');
        setValueToCeldaExcel($objSheet, 'J' . $row, 'Ini. Luz');
        setValueToCeldaExcel($objSheet, 'K' . $row, 'Días Proy.');
        setValueToCeldaExcel($objSheet, 'L' . $row, 'Días Adic. Luz');
        setValueToCeldaExcel($objSheet, 'M' . $row, 'Fin Luz');
        setValueToCeldaExcel($objSheet, 'N' . $row, 'Sem. Fin');
        setValueToCeldaExcel($objSheet, 'O' . $row, 'Hrs. Luz');
        setValueToCeldaExcel($objSheet, 'P' . $row, 'Horario');
        setBgToCeldaExcel($objSheet, 'A' . $row . ':' . 'P' . $row, '00b388');  // verde
        setColorTextToCeldaExcel($objSheet, 'A' . $row . ':' . 'P' . $row, 'FFFFFF');  // blanco

        foreach ($entradas as $luz) {
            $row++;
            $ciclo = $luz->ciclo;
            $modulo = $ciclo->modulo;
            $dias_ciclo = difFechas(hoy(), $ciclo->fecha_inicio)->days;
            $inicio_luz = opDiasFecha('+', $luz->inicio_luz, $ciclo->fecha_inicio);
            $fin_luz = opDiasFecha('+', $luz->inicio_luz + $luz->dias_proy + $luz->dias_adicional - 1, $ciclo->fecha_inicio);
            $dias_luz = 0;
            if (isset($luz) && $luz->inicio_luz <= $dias_ciclo)
                if (($luz->dias_proy + $luz->dias_adicional) >= $dias_ciclo - $luz->inicio_luz)
                    $dias_luz = $dias_ciclo - $luz->inicio_luz;
                else
                    $dias_luz = ($luz->dias_proy + $luz->dias_adicional);
            $horas_dia = isset($luz) ? $luz->getHorasDia() : 0;
            $horas_luz = $dias_luz * $horas_dia;
            $variedad = $ciclo->variedad;

            setValueToCeldaExcel($objSheet, 'A' . $row, $variedad->planta->nombre);
            setValueToCeldaExcel($objSheet, 'B' . $row, $variedad->nombre);
            setValueToCeldaExcel($objSheet, 'C' . $row, $modulo->nombre);
            setValueToCeldaExcel($objSheet, 'D' . $row, $ciclo->poda_siembra);
            setValueToCeldaExcel($objSheet, 'E' . $row, convertDateToText($ciclo->fecha_inicio));
            setValueToCeldaExcel($objSheet, 'F' . $row, $dias_ciclo);
            setValueToCeldaExcel($objSheet, 'G' . $row, $luz->tipo_luz);
            setValueToCeldaExcel($objSheet, 'H' . $row, $luz->lamparas);
            setValueToCeldaExcel($objSheet, 'I' . $row, $luz->inicio_luz);
            setValueToCeldaExcel($objSheet, 'J' . $row, convertDateToText($inicio_luz));
            setValueToCeldaExcel($objSheet, 'K' . $row, $luz->dias_proy);
            setValueToCeldaExcel($objSheet, 'L' . $row, $luz->dias_adicional);
            setValueToCeldaExcel($objSheet, 'M' . $row, convertDateToText($fin_luz));
            setValueToCeldaExcel($objSheet, 'N' . $row, getSemanaByDate($fin_luz)->codigo);
            setValueToCeldaExcel($objSheet, 'O' . $row, $horas_luz);
            setValueToCeldaExcel($objSheet, 'P' . $row, $luz->hora_ini . ' - ' . $luz->hora_fin);
        }

        /* CICLOS SALIENTES */
        $row++;
        setValueToCeldaExcel($objSheet, 'A' . $row, 'Ciclos SALIENTES');
        setBgToCeldaExcel($objSheet, 'A' . $row, '5a7177');  // dark
        setColorTextToCeldaExcel($objSheet, 'A' . $row, 'FFFFFF');  // blanco
        $objSheet->mergeCells('A' . $row . ':Q' . $row);
        $row++;
        setValueToCeldaExcel($objSheet, 'A' . $row, 'Variedad');
        setValueToCeldaExcel($objSheet, 'B' . $row, 'Tipo');
        setValueToCeldaExcel($objSheet, 'C' . $row, 'Módulo');
        setValueToCeldaExcel($objSheet, 'D' . $row, 'Poda');
        setValueToCeldaExcel($objSheet, 'E' . $row, 'Fecha Poda');
        setValueToCeldaExcel($objSheet, 'F' . $row, 'Días');
        setValueToCeldaExcel($objSheet, 'G' . $row, 'Tipo Luz');
        setValueToCeldaExcel($objSheet, 'H' . $row, '# Lamp.');
        setValueToCeldaExcel($objSheet, 'I' . $row, 'Día Ini. Luz');
        setValueToCeldaExcel($objSheet, 'J' . $row, 'Ini. Luz');
        setValueToCeldaExcel($objSheet, 'K' . $row, 'Sem. Ini.');
        setValueToCeldaExcel($objSheet, 'L' . $row, 'Días Luz');
        setValueToCeldaExcel($objSheet, 'M' . $row, 'Días Proy.');
        setValueToCeldaExcel($objSheet, 'N' . $row, 'Días Adic. Luz');
        setValueToCeldaExcel($objSheet, 'O' . $row, 'Fin Luz');
        setValueToCeldaExcel($objSheet, 'P' . $row, 'Hrs. Luz');
        setValueToCeldaExcel($objSheet, 'Q' . $row, 'Horario');
        setBgToCeldaExcel($objSheet, 'A' . $row . ':' . 'Q' . $row, '00b388');  // verde
        setColorTextToCeldaExcel($objSheet, 'A' . $row . ':' . 'Q' . $row, 'FFFFFF');  // blanco

        foreach ($salidas as $luz) {
            $row++;
            $ciclo = $luz->ciclo;
            $modulo = $ciclo->modulo;
            $dias_ciclo = difFechas(hoy(), $ciclo->fecha_inicio)->days;
            $inicio_luz = opDiasFecha('+', $luz->inicio_luz, $ciclo->fecha_inicio);
            $fin_luz = opDiasFecha('+', $luz->inicio_luz + $luz->dias_proy + $luz->dias_adicional - 1, $ciclo->fecha_inicio);
            $dias_luz = 0;
            if (isset($luz) && $luz->inicio_luz <= $dias_ciclo)
                if (($luz->dias_proy + $luz->dias_adicional) >= $dias_ciclo - $luz->inicio_luz)
                    $dias_luz = $dias_ciclo - $luz->inicio_luz;
                else
                    $dias_luz = ($luz->dias_proy + $luz->dias_adicional);
            $horas_dia = isset($luz) ? $luz->getHorasDia() : 0;
            $horas_luz = $dias_luz * $horas_dia;
            $variedad = $ciclo->variedad;

            setValueToCeldaExcel($objSheet, 'A' . $row, $variedad->planta->nombre);
            setValueToCeldaExcel($objSheet, 'B' . $row, $variedad->nombre);
            setValueToCeldaExcel($objSheet, 'C' . $row, $modulo->nombre);
            setValueToCeldaExcel($objSheet, 'D' . $row, $modulo->getPodaSiembraByCiclo($ciclo->id_ciclo));
            setValueToCeldaExcel($objSheet, 'E' . $row, convertDateToText($ciclo->fecha_inicio));
            setValueToCeldaExcel($objSheet, 'F' . $row, $dias_ciclo);
            setValueToCeldaExcel($objSheet, 'G' . $row, $luz->tipo_luz);
            setValueToCeldaExcel($objSheet, 'H' . $row, $luz->lamparas);
            setValueToCeldaExcel($objSheet, 'I' . $row, $luz->inicio_luz);
            setValueToCeldaExcel($objSheet, 'J' . $row, convertDateToText($inicio_luz));
            setValueToCeldaExcel($objSheet, 'K' . $row, getSemanaByDate($inicio_luz)->codigo);
            setValueToCeldaExcel($objSheet, 'L' . $row, $dias_luz);
            setValueToCeldaExcel($objSheet, 'M' . $row, $luz->dias_proy);
            setValueToCeldaExcel($objSheet, 'N' . $row, $luz->dias_adicional);
            setValueToCeldaExcel($objSheet, 'O' . $row, convertDateToText($fin_luz));
            setValueToCeldaExcel($objSheet, 'P' . $row, $horas_luz);
            setValueToCeldaExcel($objSheet, 'Q' . $row, $luz->hora_ini . ' - ' . $luz->hora_fin);
        }

        setTextCenterToCeldaExcel($objSheet, 'A1:Q' . $row);
        setBorderToCeldaExcel($objSheet, 'A1:Q' . $row);
        $objSheet->getColumnDimension('A')->setAutoSize(true);
        $objSheet->getColumnDimension('B')->setAutoSize(true);
        $objSheet->getColumnDimension('C')->setAutoSize(true);
        $objSheet->getColumnDimension('D')->setAutoSize(true);
        $objSheet->getColumnDimension('E')->setAutoSize(true);
        $objSheet->getColumnDimension('F')->setAutoSize(true);
        $objSheet->getColumnDimension('G')->setAutoSize(true);
        $objSheet->getColumnDimension('H')->setAutoSize(true);
        $objSheet->getColumnDimension('I')->setAutoSize(true);
        $objSheet->getColumnDimension('J')->setAutoSize(true);
        $objSheet->getColumnDimension('K')->setAutoSize(true);
        $objSheet->getColumnDimension('L')->setAutoSize(true);
        $objSheet->getColumnDimension('M')->setAutoSize(true);
        $objSheet->getColumnDimension('N')->setAutoSize(true);
        $objSheet->getColumnDimension('O')->setAutoSize(true);
        $objSheet->getColumnDimension('P')->setAutoSize(true);
        $objSheet->getColumnDimension('Q')->setAutoSize(true);
    }
}
