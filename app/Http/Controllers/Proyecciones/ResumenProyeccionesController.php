<?php

namespace yura\Http\Controllers\Proyecciones;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use yura\Http\Controllers\Controller;
use yura\Modelos\Planta;
use yura\Modelos\Submenu;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ResumenProyeccionesController extends Controller
{
    public function inicio(Request $request)
    {

        return view('adminlte.gestion.proyecciones.resumen_proyecciones.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'text' => ['titulo' => 'Proyecciones', 'subtitulo' => 'resumen total'],
            'desde' => getSemanaByDate(opDiasFecha('-', 14, date('Y-m-d'))),
            'hasta' => getSemanaByDate(opDiasFecha('+', 70, date('Y-m-d')))
        ]);
    }

    public function listar_resumen_semanal(Request $request)
    {
        $semanas = getSemanasByCodigos($request->desde, $request->hasta);

        $datos = '';
        if (count($semanas) > 0) {
            $finca = getFincaActiva();
            $desde = $semanas[0];
            $hasta = $semanas[count($semanas) - 1];

            $plantas_cosechadas = DB::table('desglose_recepcion as dr')
                ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
                ->join('variedad as v', 'v.id_variedad', '=', 'dr.id_variedad')
                ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                ->select('v.id_planta')->distinct()
                ->where('dr.estado', 1)
                ->where('r.estado', 1)
                ->where('dr.id_empresa', $finca)
                ->where('r.fecha_ingreso', '>=', $desde->fecha_inicial . ' 00:00:00')
                ->where('r.fecha_ingreso', '<=', $hasta->fecha_final . ' 23:59:59')
                ->get();

            $plantas_proyectadas_N_1 = DB::table('proyeccion_modulo_semana as p')
                ->join('modulo as m', 'm.id_modulo', '=', 'p.id_modulo')
                ->join('variedad as v', 'v.id_variedad', '=', 'p.id_variedad')
                ->join('planta as pta', 'pta.id_planta', '=', 'v.id_planta')
                ->select('v.id_planta')->distinct()
                ->where('m.estado', 1)
                ->where('p.estado', 1)
                ->where('pta.tipo', 'N')
                ->where('pta.tiene_ciclos', 1)
                ->where('p.proyectados', '>', 0)
                ->where('m.id_empresa', $finca)
                ->where('p.semana', '>=', $desde->codigo)
                ->where('p.semana', '<=', $hasta->codigo)
                ->get();
            $plantas_proyectadas_N_0 = DB::table('proy_no_perennes as p')
                ->join('semana as s', 's.id_semana', '=', 'p.id_semana')
                ->join('variedad as v', 'v.id_variedad', '=', 's.id_variedad')
                ->join('planta as pta', 'pta.id_planta', '=', 'v.id_planta')
                ->select('v.id_planta')->distinct()
                ->where('s.estado', 1)
                ->where('pta.tipo', 'N')
                ->where('pta.tiene_ciclos', 0)
                ->where('p.proyectados', '>', 0)
                ->where('p.id_empresa', $finca)
                ->where('s.codigo', '>=', $desde->codigo)
                ->where('s.codigo', '<=', $hasta->codigo)
                ->get();
            $plantas_proyectadas_P = DB::table('semana_proy_perenne as p')
                ->join('semana as s', 's.id_semana', '=', 'p.id_semana')
                ->join('variedad as v', 'v.id_variedad', '=', 's.id_variedad')
                ->join('planta as pta', 'pta.id_planta', '=', 'v.id_planta')
                ->select('v.id_planta')->distinct()
                ->where('s.estado', 1)
                ->where('pta.tipo', 'P')
                ->where('p.proyectados', '>', 0)
                ->where('p.id_empresa', $finca)
                ->where('s.codigo', '>=', $desde->codigo)
                ->where('s.codigo', '<=', $hasta->codigo)
                ->get();

            $plantas_query = [];
            foreach ($plantas_cosechadas as $v)
                array_push($plantas_query, $v->id_planta);
            foreach ($plantas_proyectadas_N_1 as $v)
                if (!in_array($v->id_planta, $plantas_query))
                    array_push($plantas_query, $v->id_planta);
            foreach ($plantas_proyectadas_N_0 as $v)
                if (!in_array($v->id_planta, $plantas_query))
                    array_push($plantas_query, $v->id_planta);
            foreach ($plantas_proyectadas_P as $v)
                if (!in_array($v->id_planta, $plantas_query))
                    array_push($plantas_query, $v->id_planta);
            $plantas = DB::table('planta')
                ->select('nombre', 'id_planta', 'tipo', 'tiene_ciclos')->distinct()
                ->where('estado', 1)
                ->whereIn('id_planta', $plantas_query)
                ->orderBy('nombre', 'asc')
                ->get();

            $data = [];
            foreach ($plantas as $pos => $pta) {
                $cosechados = [];
                $proyectados = [];
                foreach ($semanas as $sem) {
                    $cos = DB::table('cosecha_diaria')
                        ->select(DB::raw('sum(cosechados) as cantidad'))
                        ->where('id_empresa', $finca)
                        ->where('id_planta', $pta->id_planta)
                        ->where('fecha', '>=', $sem->fecha_inicial)
                        ->where('fecha', '<=', $sem->fecha_final)
                        ->get()[0]->cantidad;

                    if ($pta->tipo == 'N')  // es una planta Normal
                        if ($pta->tiene_ciclos == 1) {  // Tiene Ciclos (Gyp)
                            $mod_ciclos = DB::table('ciclo as c')
                                ->join('variedad as v', 'v.id_variedad', '=', 'c.id_variedad')
                                ->select('c.id_modulo')->distinct()
                                ->where('v.id_planta', $pta->id_planta)
                                ->where('c.id_empresa', $finca)
                                ->where('c.estado', 1)
                                ->Where(function ($q) use ($sem) {
                                    $q->where('c.fecha_fin', '>=', $sem->fecha_inicial)
                                        ->where('c.fecha_fin', '<=', $sem->fecha_final)
                                        ->orWhere(function ($q) use ($sem) {
                                            $q->where('c.fecha_inicio', '>=', $sem->fecha_inicial)
                                                ->where('c.fecha_inicio', '<=', $sem->fecha_final);
                                        })
                                        ->orWhere(function ($q) use ($sem) {
                                            $q->where('c.fecha_inicio', '<', $sem->fecha_inicial)
                                                ->where('c.fecha_fin', '>', $sem->fecha_final);
                                        })
                                        ->orWhere('c.activo', 1);
                                })
                                ->get();
                            $ids_modulos = [];
                            foreach ($mod_ciclos as $m)
                                $ids_modulos[] = $m->id_modulo;

                            $proy = DB::table('proyeccion_modulo_semana as p')
                                ->join('modulo as m', 'm.id_modulo', '=', 'p.id_modulo')
                                ->join('variedad as v', 'v.id_variedad', '=', 'p.id_variedad')
                                ->select(DB::raw('sum(p.proyectados) as cantidad'))
                                ->where('m.estado', 1)
                                ->where('p.estado', 1)
                                ->where('m.id_empresa', $finca)
                                ->where('v.id_planta', $pta->id_planta)
                                ->where('p.semana', $sem->codigo)
                                ->whereIn('p.id_modulo', $ids_modulos)
                                ->get()[0]->cantidad;
                        } else {    // No Tiene CICLOS
                            $proy = DB::table('proy_no_perennes as p')
                                ->join('semana as s', 's.id_semana', '=', 'p.id_semana')
                                ->join('variedad as v', 'v.id_variedad', '=', 's.id_variedad')
                                ->select(DB::raw('sum(p.proyectados) as cantidad'))
                                ->where('s.estado', 1)
                                ->where('p.id_empresa', $finca)
                                ->where('v.id_planta', $pta->id_planta)
                                ->where('s.codigo', $sem->codigo)
                                ->get()[0]->cantidad;
                        }
                    else {  // es una planta Perenne
                        $proy = DB::table('semana_proy_perenne as p')
                            ->join('semana as s', 's.id_semana', '=', 'p.id_semana')
                            ->join('variedad as v', 'v.id_variedad', '=', 's.id_variedad')
                            ->select(DB::raw('sum(p.proyectados) as cantidad'))
                            ->where('s.estado', 1)
                            ->where('p.id_empresa', $finca)
                            ->where('v.id_planta', $pta->id_planta)
                            ->where('s.codigo', $sem->codigo)
                            ->get()[0]->cantidad;
                    }
                    $cosechados[] = $cos;
                    $proyectados[] = $proy;
                }

                array_push($data, [
                    'planta' => $pta,
                    'cosechados' => $cosechados,
                    'proyectados' => $proyectados,
                ]);
            }

            $datos = [
                'semanas' => $semanas,
                'data' => $data,
            ];
        }
        return view('adminlte.gestion.proyecciones.resumen_proyecciones.partials.resumen_semanal', $datos);
    }

    public function select_desglose_planta(Request $request)
    {
        $semanas = DB::table('semana')
            ->select('codigo', 'fecha_inicial', 'fecha_final')->distinct()
            ->where('codigo', '>=', $request->desde)
            ->where('codigo', '<=', $request->hasta)
            ->orderBy('codigo')
            ->get();

        $datos = '';
        if (count($semanas) > 0) {
            $finca = getFincaActiva();
            $desde = $semanas[0];
            $hasta = $semanas[count($semanas) - 1];
            $planta = Planta::find($request->id_pta);

            $variedades_cosechadas = DB::table('desglose_recepcion as dr')
                ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
                ->join('variedad as v', 'v.id_variedad', '=', 'dr.id_variedad')
                ->select('dr.id_variedad')->distinct()
                ->where('dr.estado', 1)
                ->where('r.estado', 1)
                ->where('v.id_planta', $planta->id_planta)
                ->where('dr.id_empresa', $finca)
                ->where('r.fecha_ingreso', '>=', $desde->fecha_inicial . ' 00:00:00')
                ->where('r.fecha_ingreso', '<=', $hasta->fecha_final . ' 23:59:59')
                ->get();
            if ($planta->tipo == 'N')
                if ($planta->tiene_ciclos == 1)
                    $variedades_proyectadas = DB::table('proyeccion_modulo_semana as p')
                        ->join('modulo as m', 'm.id_modulo', '=', 'p.id_modulo')
                        ->join('variedad as v', 'v.id_variedad', '=', 'p.id_variedad')
                        ->select('p.id_variedad')->distinct()
                        ->where('m.estado', 1)
                        ->where('p.estado', 1)
                        ->where('v.id_planta', $planta->id_planta)
                        ->where('p.proyectados', '>', 0)
                        ->where('m.id_empresa', $finca)
                        ->where('p.semana', '>=', $desde->codigo)
                        ->where('p.semana', '<=', $hasta->codigo)
                        ->get();
                else
                    $variedades_proyectadas = DB::table('proy_no_perennes as p')
                        ->join('semana as s', 's.id_semana', '=', 'p.id_semana')
                        ->join('variedad as v', 'v.id_variedad', '=', 's.id_variedad')
                        ->select('s.id_variedad')->distinct()
                        ->where('s.estado', 1)
                        ->where('v.id_planta', $planta->id_planta)
                        ->where('p.proyectados', '>', 0)
                        ->where('p.id_empresa', $finca)
                        ->where('s.codigo', '>=', $desde->codigo)
                        ->where('s.codigo', '<=', $hasta->codigo)
                        ->get();
            else
                $variedades_proyectadas = DB::table('proyeccion_modulo_semana as p')
                    ->join('modulo as m', 'm.id_modulo', '=', 'p.id_modulo')
                    ->join('variedad as v', 'v.id_variedad', '=', 'p.id_variedad')
                    ->select('p.id_variedad')->distinct()
                    ->where('m.estado', 1)
                    ->where('p.estado', 1)
                    ->where('v.id_planta', $planta->id_planta)
                    ->where('p.proyectados', '>', 0)
                    ->where('m.id_empresa', $finca)
                    ->where('p.semana', '>=', $desde->codigo)
                    ->where('p.semana', '<=', $hasta->codigo)
                    ->get();

            $variedades_query = [];
            foreach ($variedades_cosechadas as $v)
                array_push($variedades_query, $v->id_variedad);
            foreach ($variedades_proyectadas as $v)
                if (!in_array($v->id_variedad, $variedades_query))
                    array_push($variedades_query, $v->id_variedad);

            $variedades = DB::table('variedad')
                ->select('nombre', 'id_variedad')->distinct()
                ->where('estado', 1)
                ->whereIn('id_variedad', $variedades_query)
                ->orderBy('nombre', 'asc')
                ->get();

            $data = [];
            foreach ($variedades as $pos => $var) {
                $cosechados = [];
                $proyectados = [];
                foreach ($semanas as $sem) {
                    $cos = DB::table('cosecha_diaria')
                        ->select(DB::raw('sum(cosechados) as cantidad'))
                        ->where('id_empresa', $finca)
                        ->where('id_variedad', $var->id_variedad)
                        ->where('fecha', '>=', $sem->fecha_inicial)
                        ->where('fecha', '<=', $sem->fecha_final)
                        ->get()[0]->cantidad;

                    if ($planta->tipo == 'N') {
                        if ($planta->tiene_ciclos == 1) {
                            $mod_ciclos = DB::table('ciclo as c')
                                ->select('c.id_modulo')->distinct()
                                ->where('c.id_variedad', $var->id_variedad)
                                ->where('c.id_empresa', $finca)
                                ->where('c.estado', 1)
                                ->Where(function ($q) use ($sem) {
                                    $q->where('c.fecha_fin', '>=', $sem->fecha_inicial)
                                        ->where('c.fecha_fin', '<=', $sem->fecha_final)
                                        ->orWhere(function ($q) use ($sem) {
                                            $q->where('c.fecha_inicio', '>=', $sem->fecha_inicial)
                                                ->where('c.fecha_inicio', '<=', $sem->fecha_final);
                                        })
                                        ->orWhere(function ($q) use ($sem) {
                                            $q->where('c.fecha_inicio', '<', $sem->fecha_inicial)
                                                ->where('c.fecha_fin', '>', $sem->fecha_final);
                                        })
                                        ->orWhere('c.activo', 1);
                                })
                                ->get();
                            $ids_modulos = [];
                            foreach ($mod_ciclos as $m)
                                $ids_modulos[] = $m->id_modulo;

                            $proy = DB::table('proyeccion_modulo_semana as p')
                                ->join('modulo as m', 'm.id_modulo', '=', 'p.id_modulo')
                                ->select(DB::raw('sum(p.proyectados) as cantidad'))
                                ->where('m.estado', 1)
                                ->where('p.estado', 1)
                                ->where('m.id_empresa', $finca)
                                ->where('p.id_variedad', $var->id_variedad)
                                ->where('p.semana', $sem->codigo)
                                ->whereIn('p.id_modulo', $ids_modulos)
                                ->get()[0]->cantidad;
                        } else {
                            $proy = DB::table('proy_no_perennes as p')
                                ->join('semana as s', 's.id_semana', '=', 'p.id_semana')
                                ->join('variedad as v', 'v.id_variedad', '=', 's.id_variedad')
                                ->select(DB::raw('sum(p.proyectados) as cantidad'))
                                ->where('s.estado', 1)
                                ->where('p.id_empresa', $finca)
                                ->where('v.id_variedad', $var->id_variedad)
                                ->where('s.codigo', $sem->codigo)
                                ->get()[0]->cantidad;
                        }
                    } else {
                        $proy = DB::table('semana_proy_perenne as p')
                            ->join('semana as s', 's.id_semana', '=', 'p.id_semana')
                            ->join('variedad as v', 'v.id_variedad', '=', 's.id_variedad')
                            ->select(DB::raw('sum(p.proyectados) as cantidad'))
                            ->where('s.estado', 1)
                            ->where('p.id_empresa', $finca)
                            ->where('v.id_variedad', $var->id_variedad)
                            ->where('s.codigo', $sem->codigo)
                            ->get()[0]->cantidad;
                    }

                    $cosechados[] = $cos;
                    $proyectados[] = $proy;
                }

                array_push($data, [
                    'variedad' => $var,
                    'cosechados' => $cosechados,
                    'proyectados' => $proyectados,
                ]);
            }

            $datos = [
                'semanas' => $semanas,
                'data' => $data,
            ];
        }
        return $datos;
    }

    /* -------------------------------------------------------------------- */
    public function exportar_reporte(Request $request)
    {
        $spread = new Spreadsheet();
        $semanas = DB::table('semana')
            ->select('codigo', 'fecha_inicial', 'fecha_final')->distinct()
            ->where('codigo', '>=', $request->desde)
            ->where('codigo', '<=', $request->hasta)
            ->orderBy('codigo')
            ->get();

        if ($request->reporte == 'N')
            $this->excel_reporte_plantas_normales($spread, $semanas);
        if ($request->reporte == 'P')
            $this->excel_reporte_plantas_normales($spread, $semanas);

        $spread->getProperties()
            ->setCreator("Benchflow")
            ->setTitle('Resumen de Proyección')
            ->setSubject('Resumen de Proyección');

        $fileName = "Resumen_proyeccion.xlsx";
        $writer = new Xlsx($spread);

        //--------------------------- GUARDAR EL EXCEL -----------------------

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        $writer->save('php://output');
    }

    public function excel_reporte_plantas_normales($spread, $semanas)
    {
        $finca = getFincaActiva();
        $desde = $semanas[0];
        $hasta = $semanas[count($semanas) - 1];
        $plantas_cosechadas = DB::table('desglose_recepcion as dr')
            ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
            ->join('variedad as v', 'v.id_variedad', '=', 'dr.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->select('v.id_planta')->distinct()
            ->where('dr.estado', 1)
            ->where('r.estado', 1)
            ->where('p.tipo', 'N')
            ->where('dr.id_empresa', $finca)
            ->where('r.fecha_ingreso', '>=', $desde->fecha_inicial . ' 00:00:00')
            ->where('r.fecha_ingreso', '<=', $hasta->fecha_final . ' 23:59:59')
            ->get();
        $plantas_proyectadas = DB::table('proyeccion_modulo_semana as p')
            ->join('modulo as m', 'm.id_modulo', '=', 'p.id_modulo')
            ->join('variedad as v', 'v.id_variedad', '=', 'p.id_variedad')
            ->join('planta as pta', 'pta.id_planta', '=', 'v.id_planta')
            ->select('v.id_planta')->distinct()
            ->where('m.estado', 1)
            ->where('p.estado', 1)
            ->where('pta.tipo', 'N')
            ->where('p.proyectados', '>', 0)
            ->where('m.id_empresa', $finca)
            ->where('p.semana', '>=', $desde->codigo)
            ->where('p.semana', '<=', $hasta->codigo)
            ->get();
        $plantas_query = [];
        foreach ($plantas_cosechadas as $v)
            array_push($plantas_query, $v->id_planta);
        foreach ($plantas_proyectadas as $v)
            if (!in_array($v->id_planta, $plantas_query))
                array_push($plantas_query, $v->id_planta);

        $plantas = DB::table('planta')
            ->select('nombre', 'id_planta')->distinct()
            ->where('estado', 1)
            ->whereIn('id_planta', $plantas_query)
            ->orderBy('nombre', 'asc')
            ->get();

        $data = [];
        foreach ($plantas as $pos => $pta) {
            $cosechados = [];
            $proyectados = [];
            foreach ($semanas as $sem) {
                $cos = DB::table('cosecha_diaria')
                    ->select(DB::raw('sum(cosechados) as cantidad'))
                    ->where('id_empresa', $finca)
                    ->where('id_planta', $pta->id_planta)
                    ->where('fecha', '>=', $sem->fecha_inicial)
                    ->where('fecha', '<=', $sem->fecha_final)
                    ->get()[0]->cantidad;

                $mod_ciclos = DB::table('ciclo as c')
                    ->join('variedad as v', 'v.id_variedad', '=', 'c.id_variedad')
                    ->select('c.id_modulo')->distinct()
                    ->where('v.id_planta', $pta->id_planta)
                    ->where('c.id_empresa', $finca)
                    ->where('c.estado', 1)
                    ->Where(function ($q) use ($sem) {
                        $q->where('c.fecha_fin', '>=', $sem->fecha_inicial)
                            ->where('c.fecha_fin', '<=', $sem->fecha_final)
                            ->orWhere(function ($q) use ($sem) {
                                $q->where('c.fecha_inicio', '>=', $sem->fecha_inicial)
                                    ->where('c.fecha_inicio', '<=', $sem->fecha_final);
                            })
                            ->orWhere(function ($q) use ($sem) {
                                $q->where('c.fecha_inicio', '<', $sem->fecha_inicial)
                                    ->where('c.fecha_fin', '>', $sem->fecha_final);
                            })
                            ->orWhere('c.activo', 1);
                    })
                    ->get();
                $ids_modulos = [];
                foreach ($mod_ciclos as $m)
                    $ids_modulos[] = $m->id_modulo;

                $proy = DB::table('proyeccion_modulo_semana as p')
                    ->join('modulo as m', 'm.id_modulo', '=', 'p.id_modulo')
                    ->join('variedad as v', 'v.id_variedad', '=', 'p.id_variedad')
                    ->select(DB::raw('sum(p.proyectados) as cantidad'))
                    ->where('m.estado', 1)
                    ->where('p.estado', 1)
                    ->where('m.id_empresa', $finca)
                    ->where('v.id_planta', $pta->id_planta)
                    ->where('p.semana', $sem->codigo)
                    ->whereIn('p.id_modulo', $ids_modulos)
                    ->get()[0]->cantidad;
                $cosechados[] = $cos;
                $proyectados[] = $proy;
            }

            array_push($data, [
                'planta' => $pta,
                'cosechados' => $cosechados,
                'proyectados' => $proyectados,
            ]);
        }

        /* ----------------------- CREAR HOJA DE EXCEL ------------------------ */
        $objSheet = $spread->getActiveSheet()->setTitle('Plantas normales');
        $columnas = getColumnasExcel();

        /* --------------- SEMANAS ------------------ */
        setValueToCeldaExcel($objSheet, 'A1', 'Semanas');
        setBgToCeldaExcel($objSheet, 'A1', '00b388');   // verde
        setColorTextToCeldaExcel($objSheet, 'A1', 'FFFFFF');   // blanco
        $objSheet->mergeCells('A1:A2');

        $totales_cosechados = [];
        $totales_proyectados = [];
        $col = 1;
        foreach ($semanas as $pos => $sem) {
            setValueToCeldaExcel($objSheet, $columnas[$col] . '1', $sem->codigo);
            setBgToCeldaExcel($objSheet, $columnas[$col] . '1', '5a7177');    // dark
            setColorTextToCeldaExcel($objSheet, $columnas[$col] . '1', 'FFFFFF');   // blanco
            $objSheet->mergeCells($columnas[$col] . '1:' . $columnas[$col + 1] . '1');
            setValueToCeldaExcel($objSheet, $columnas[$col] . '2', 'Cosechados');
            setValueToCeldaExcel($objSheet, $columnas[$col + 1] . '2', 'Proyectados');
            setBgToCeldaExcel($objSheet, $columnas[$col] . '2:' . $columnas[$col + 1] . '2', '5a7177');    // dark
            setColorTextToCeldaExcel($objSheet, $columnas[$col] . '2:' . $columnas[$col + 1] . '2', 'FFFFFF');   // blanco
            $col += 2;

            array_push($totales_cosechados, 0);
            array_push($totales_proyectados, 0);
        }

        $row = 3;
        foreach ($data as $item) {
            setValueToCeldaExcel($objSheet, 'A' . $row, $item['planta']->nombre);
            setBgToCeldaExcel($objSheet, 'A' . $row, '8fdbc9'); // verde claro
            $pos = 1;
            foreach ($item['cosechados'] as $pos_c => $cos) {
                setValueToCeldaExcel($objSheet, $columnas[$pos] . $row, $cos);
                $proy = $item['proyectados'][$pos_c] != '' ? $item['proyectados'][$pos_c] : 0;
                setValueToCeldaExcel($objSheet, $columnas[$pos + 1] . $row, round($proy, 2));
                $pos += 2;

                $totales_cosechados[$pos_c] += $cos;
                $totales_proyectados[$pos_c] += $item['proyectados'][$pos_c];
            }
            $row++;
        }

        setValueToCeldaExcel($objSheet, 'A' . $row, 'TOTALES');
        setBgToCeldaExcel($objSheet, 'A' . $row, '00b388');   // verde
        setColorTextToCeldaExcel($objSheet, 'A' . $row, 'FFFFFF');   // blanco
        $t = 1;
        foreach ($totales_cosechados as $pos_t => $cos) {
            setValueToCeldaExcel($objSheet, $columnas[$t] . $row, $cos);
            setValueToCeldaExcel($objSheet, $columnas[$t + 1] . $row, round($totales_proyectados[$pos_t], 2));
            setBgToCeldaExcel($objSheet, $columnas[$t] . $row . ':' . $columnas[$t + 1] . $row, '5a7177');    // dark
            setColorTextToCeldaExcel($objSheet, $columnas[$t] . $row . ':' . $columnas[$t + 1] . $row, 'FFFFFF');   // blanco
            $t += 2;
        }

        setBorderToCeldaExcel($objSheet, 'A1:' . $columnas[$col - 1] . $row);
        setTextCenterToCeldaExcel($objSheet, 'A1:' . $columnas[$col - 1] . $row);
        for ($i = 0; $i <= $col - 1; $i++)
            $objSheet->getColumnDimension($columnas[$i])->setAutoSize(true);
    }
}
