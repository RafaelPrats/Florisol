<?php

namespace yura\Http\Controllers\Proyecciones;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use yura\Http\Controllers\Controller;
use yura\Modelos\Planta;
use yura\Modelos\Semana;
use yura\Modelos\Submenu;
use yura\Modelos\Variedad;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class proyNoPerennesController extends Controller
{
    public function inicio(Request $request)
    {
        $finca = getFincaActiva();
        $plantas = DB::table('ciclo as c')
            ->join('variedad as v', 'v.id_variedad', '=', 'c.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->select('v.id_planta', 'p.nombre', 'p.tipo')->distinct()
            ->where('c.estado', 1)
            ->where('v.estado', 1)
            ->where('p.estado', 1)
            ->where('c.activo', 1)
            ->where('c.id_empresa', $finca)
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.proyecciones.no_perennes.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'plantas' => $plantas,
            'annos' => DB::table('semana as s')
                ->select('s.anno')->distinct()
                ->where('s.estado', '=', 1)
                ->orderBy('s.anno', 'desc')
                ->get()
        ]);
    }

    public function listar_proyecciones(Request $request)
    {
        $finca = getFincaActiva();
        if ($request->tipo_planta == 'N') { // No perennes
            if ($request->variedad != 'T') {    // una variedad
                $semanas = Semana::where('anno', $request->anno)
                    ->where('id_variedad', $request->variedad)
                    ->orderBy('codigo')
                    ->get();
                $listado = [];
                foreach ($semanas as $sem) {
                    $proy = DB::table('proy_no_perennes as proy')
                        ->join('semana as s', 's.id_semana', '=', 'proy.id_semana')
                        ->select(DB::raw('sum(proy.area_produccion) as area_produccion'),
                            DB::raw('sum(proy.area_semana) as area_semana'),
                            DB::raw('sum(proy.proyectados) as proyectados'))
                        ->where('s.codigo', $sem->codigo)
                        ->where('proy.id_empresa', $finca)
                        ->where('s.id_variedad', $request->variedad)
                        ->get()[0];
                    $cos = DB::table('resumen_total_semanal_exportcalas as p')
                        ->select(DB::raw('sum(p.tallos_cosechados) as tallos_cosechados'))
                        ->where('p.id_variedad', $request->variedad)
                        ->where('p.id_empresa', $finca)
                        ->where('p.semana', $sem->codigo)
                        ->get()[0];
                    array_push($listado, [
                        'codigo' => $sem->codigo,
                        'fecha_inicial' => $sem->fecha_inicial,
                        'fecha_final' => $sem->fecha_final,
                        'area_produccion' => $proy->area_produccion,
                        'area_semana' => $proy->area_semana,
                        'proyectados' => $proy->proyectados,
                        'tallos_cosechados' => $cos->tallos_cosechados,
                    ]);
                }
                $view = 'listado';
                $datos = [
                    'semanas' => $semanas,
                    'listado' => $listado,
                    'finca' => $finca,
                ];
            } else {    // todas las variedades
                $semanas = DB::table('semana')
                    ->select('codigo', 'fecha_inicial', 'fecha_final')->distinct()
                    ->where('estado', 1)
                    ->where('anno', $request->anno)
                    ->orderBy('codigo')
                    ->get();
                $listado = [];
                foreach ($semanas as $sem) {
                    $proy = DB::table('proy_no_perennes as proy')
                        ->join('semana as s', 's.id_semana', '=', 'proy.id_semana')
                        ->join('variedad as v', 'v.id_variedad', '=', 's.id_variedad')
                        ->select(DB::raw('sum(proy.area_produccion) as area_produccion'),
                            DB::raw('sum(proy.area_semana) as area_semana'),
                            DB::raw('sum(proy.proyectados) as proyectados'))
                        ->where('s.codigo', $sem->codigo)
                        ->where('proy.id_empresa', $finca)
                        ->where('v.id_planta', $request->planta)
                        ->get()[0];
                    $cos = DB::table('resumen_total_semanal_exportcalas as p')
                        ->join('variedad as v', 'v.id_variedad', '=', 'p.id_variedad')
                        ->select(DB::raw('sum(p.tallos_cosechados) as tallos_cosechados'))
                        ->where('v.id_planta', $request->planta)
                        ->where('p.id_empresa', $finca)
                        ->where('p.semana', $sem->codigo)
                        ->get()[0];
                    array_push($listado, [
                        'codigo' => $sem->codigo,
                        'fecha_inicial' => $sem->fecha_inicial,
                        'fecha_final' => $sem->fecha_final,
                        'area_produccion' => $proy->area_produccion,
                        'area_semana' => $proy->area_semana,
                        'proyectados' => $proy->proyectados,
                        'tallos_cosechados' => $cos->tallos_cosechados,
                    ]);
                }
                $view = 'listado_acumulado';
                $datos = [
                    'listado' => $listado,
                    'finca' => $finca,
                ];
            }
            return view('adminlte.gestion.proyecciones.no_perennes.partials.' . $view, $datos);
        } else {    // Perennes
            if ($request->variedad != 'T') {    // una variedad
                $tallos_m2_anno = DB::table('semana as sem')
                    ->join('semana_proy_perenne as p', 'p.id_semana', '=', 'sem.id_semana')
                    ->select(DB::raw('sum(p.curva) as cantidad'))
                    ->where('sem.estado', 1)
                    ->where('sem.id_variedad', $request->variedad)
                    ->where('sem.anno', $request->anno)
                    ->where('p.id_empresa', $finca)
                    ->get()[0]->cantidad;

                $tallos_m2_anno = $tallos_m2_anno > 0 ? $tallos_m2_anno : 0;

                $area = 0;
                $listado = [];
                if ($tallos_m2_anno > 0) {
                    $semanas = Semana::where('anno', $request->anno)
                        ->where('id_variedad', $request->variedad)
                        ->orderBy('codigo')
                        ->get();
                    $area = DB::table('ciclo as c')
                        ->select(DB::raw('sum(c.area) as area'))
                        ->where('c.estado', 1)
                        ->where('c.activo', 1)
                        ->where('c.id_variedad', $request->variedad)
                        ->where('c.id_empresa', $finca)
                        ->get()[0]->area;
                    $area = $area > 0 ? $area : 0;
                    foreach ($semanas as $sem) {
                        $valores = DB::table('semana as sem')
                            ->join('semana_proy_perenne as p', 'p.id_semana', '=', 'sem.id_semana')
                            ->select('sem.id_semana', 'sem.codigo', 'sem.fecha_inicial', 'sem.fecha_final',
                                'p.curva as curva_perenne', 'p.proyectados', 'p.cosechados', 'p.porcentaje_cumplimiento', 'p.tallos_m2_ejecutado',
                                'p.sum_ejec_4_sem', 'p.sum_ejec_13_sem', 'p.sum_ejec_52_sem', 'p.plantas_iniciales',
                                'p.proyectados_acum', 'p.cosechados_acum', 'p.porcentaje_cumplimiento_acum', 'p.tallos_m2_ejecutado_acum')
                            ->where('sem.estado', 1)
                            ->where('sem.id_variedad', $request->variedad)
                            ->where('sem.codigo', $sem->codigo)
                            ->where('p.id_empresa', $finca)
                            ->orderBy('sem.codigo')
                            ->get();
                        $valores = count($valores) == 1 ? $valores : '';

                        array_push($listado, [
                            'semana' => $sem,
                            'valores' => $valores[0],
                        ]);
                    }
                }

                $view = 'listado';
                $datos = [
                    'listado' => $listado,
                    'finca' => $finca,
                    'tallos_m2_anno' => $tallos_m2_anno,
                    'area' => $area,
                ];
            } else {    // todas las variedades
                $pta = Planta::find($request->planta);
                $variedades = $pta->variedades->where('estado', 1);
                $ids_variedad = [];
                $prom_tallos_m2_anno = 0;
                $cantidad = 0;

                foreach ($variedades as $var) {
                    $tallos_m2_anno = DB::table('semana as sem')
                        ->join('semana_proy_perenne as p', 'p.id_semana', '=', 'sem.id_semana')
                        ->select(DB::raw('sum(p.curva) as cantidad'))
                        ->where('sem.estado', 1)
                        ->where('sem.id_variedad', $var->id_variedad)
                        ->where('sem.anno', $request->anno)
                        ->where('p.id_empresa', $finca)
                        ->get()[0]->cantidad;
                    $tallos_m2_anno = $tallos_m2_anno > 0 ? $tallos_m2_anno : 0;

                    if ($tallos_m2_anno > 0) {
                        array_push($ids_variedad, $var->id_variedad);
                        $prom_tallos_m2_anno += $tallos_m2_anno;
                        $cantidad++;
                    }
                }

                $listado = [];
                if ($prom_tallos_m2_anno > 0) {
                    $semanas = DB::table('semana')
                        ->select('codigo', 'fecha_inicial', 'fecha_final')->distinct()
                        ->where('estado', 1)
                        ->where('anno', $request->anno)
                        ->orderBy('codigo')
                        ->get();
                    foreach ($semanas as $sem) {
                        $valores = DB::table('semana as sem')
                            ->join('semana_proy_perenne as p', 'p.id_semana', '=', 'sem.id_semana')
                            ->select(DB::raw('sum(p.curva) as curva_perenne'), DB::raw('sum(p.proyectados) as proyectados'),
                                DB::raw('sum(p.cosechados) as cosechados'),
                                DB::raw('sum(p.porcentaje_cumplimiento) as porcentaje_cumplimiento'),
                                DB::raw('sum(p.tallos_m2_ejecutado) as tallos_m2_ejecutado'),
                                DB::raw('sum(p.sum_ejec_4_sem) as sum_ejec_4_sem'),
                                DB::raw('sum(p.sum_ejec_13_sem) as sum_ejec_13_sem'),
                                DB::raw('sum(p.sum_ejec_52_sem) as sum_ejec_52_sem'),
                                DB::raw('sum(p.plantas_iniciales) as plantas_iniciales'),
                                DB::raw('sum(p.proyectados_acum) as proyectados_acum'),
                                DB::raw('sum(p.cosechados_acum) as cosechados_acum'),
                                DB::raw('sum(p.porcentaje_cumplimiento_acum) as porcentaje_cumplimiento_acum'),
                                DB::raw('sum(p.tallos_m2_ejecutado_acum) as tallos_m2_ejecutado_acum'))
                            ->where('sem.estado', 1)
                            ->whereIn('sem.id_variedad', $ids_variedad)
                            ->where('sem.codigo', $sem->codigo)
                            ->where('p.id_empresa', $finca)
                            ->get()[0];
                        $area = DB::table('ciclo as c')
                            ->select(DB::raw('sum(c.area) as area'))
                            ->where('c.estado', 1)
                            ->where('c.activo', 1)
                            ->whereIn('c.id_variedad', $ids_variedad)
                            ->where('c.id_empresa', $finca)
                            ->get()[0]->area;
                        $area = $area > 0 ? $area : 0;
                        array_push($listado, [
                            'semana' => $sem,
                            'valores' => $valores,
                            'area' => $area,
                            'tallos_m2_anno' => $cantidad > 0 ? round($prom_tallos_m2_anno / $cantidad, 2) : 0,
                        ]);
                    }
                }

                $view = 'listado_acumulado';
                $datos = [
                    'listado' => $listado,
                    'finca' => $finca,
                ];
            }
            return view('adminlte.gestion.proyecciones.reporte_perennes.partials.' . $view, $datos);
        }
    }

    public function exportar_reporte_proyecciones(Request $request)
    {
        $spread = new Spreadsheet();
        if ($request->tipo_planta == 'N')   // No Perennes
            $this->excel_reporte_proyecciones_no_perennes($spread, $request);
        else    // Perennes
            $this->excel_reporte_proyecciones_perennes($spread, $request);
        $spread->getProperties()
            ->setCreator("Benchflow")
            ->setTitle('Proyecciones')
            ->setSubject('Proyecciones');

        $fileName = "Proyecciones.xlsx";
        $writer = new Xlsx($spread);

        //--------------------------- GUARDAR EL EXCEL -----------------------

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        $writer->save('php://output');
    }

    public function excel_reporte_proyecciones_no_perennes($spread, $request)
    {
        $finca = getFincaActiva();
        if ($request->variedad != 'T') {    // una variedad
            $semanas = Semana::where('anno', $request->anno)
                ->where('id_variedad', $request->variedad)
                ->orderBy('codigo')
                ->get();
            $listado = [];
            foreach ($semanas as $sem) {
                $proy = DB::table('proy_no_perennes as proy')
                    ->join('semana as s', 's.id_semana', '=', 'proy.id_semana')
                    ->select(DB::raw('sum(proy.area_produccion) as area_produccion'),
                        DB::raw('sum(proy.area_semana) as area_semana'),
                        DB::raw('sum(proy.proyectados) as proyectados'))
                    ->where('s.codigo', $sem->codigo)
                    ->where('proy.id_empresa', $finca)
                    ->where('s.id_variedad', $request->variedad)
                    ->get()[0];
                $cos = DB::table('resumen_total_semanal_exportcalas as p')
                    ->select(DB::raw('sum(p.tallos_cosechados) as tallos_cosechados'))
                    ->where('p.id_variedad', $request->variedad)
                    ->where('p.id_empresa', $finca)
                    ->where('p.semana', $sem->codigo)
                    ->get()[0];
                array_push($listado, [
                    'codigo' => $sem->codigo,
                    'fecha_inicial' => $sem->fecha_inicial,
                    'fecha_final' => $sem->fecha_final,
                    'area_produccion' => $proy->area_produccion,
                    'area_semana' => $proy->area_semana,
                    'proyectados' => $proy->proyectados,
                    'tallos_cosechados' => $cos->tallos_cosechados,
                ]);
            }

            /* ----------------------- CREAR HOJA DE EXCEL ------------------------ */
            $objSheet = $spread->getActiveSheet()->setTitle('Proyecciones');

            $row = 1;
            setValueToCeldaExcel($objSheet, 'A' . $row, 'Semana');
            setValueToCeldaExcel($objSheet, 'B' . $row, 'Área Prod.');
            setValueToCeldaExcel($objSheet, 'C' . $row, 'Área Cult.');
            setValueToCeldaExcel($objSheet, 'D' . $row, 'Tallos Proy.');
            setValueToCeldaExcel($objSheet, 'E' . $row, 'Tallos Proy. Acum. Año');
            setValueToCeldaExcel($objSheet, 'F' . $row, 'Tallos Cos.');
            setValueToCeldaExcel($objSheet, 'G' . $row, 'Tallos Cos. Acum. Año');
            setValueToCeldaExcel($objSheet, 'H' . $row, '% Cump.');
            setValueToCeldaExcel($objSheet, 'I' . $row, '% Cump. Acum. Año');
            setValueToCeldaExcel($objSheet, 'J' . $row, 'Tallos/m2 Ejec.');
            setValueToCeldaExcel($objSheet, 'K' . $row, 'Tallos/m2 Ejec. Acum.');
            setValueToCeldaExcel($objSheet, 'L' . $row, 'Tallos/m2/año (52 sem.)');

            setColorTextToCeldaExcel($objSheet, 'A' . $row . ':L' . $row, 'FFFFFF');    // blanco
            setBgToCeldaExcel($objSheet, 'A' . $row . ':L' . $row, '00b388');    // verde
            setBgToCeldaExcel($objSheet, 'B' . $row . ':C' . $row, '0b3248');    // azul oscuro

            $area_total = 0;
            $proy_total = 0;
            $cos_total = 0;
            $prom_tallos_m2_ejec = 0;
            $positivo_tallos_m2_ejec = 0;
            $prom_flor_m2_anno_52 = 0;
            $positivo_flor_m2_anno_52 = 0;
            foreach ($listado as $pos => $s) {
                $row++;
                $area_produccion = $s['area_produccion'];
                $area_semana = $s['area_semana'];
                $area_total += $area_produccion;
                $proyectados = $s['proyectados'];
                $proy_total += $proyectados;
                $cosechados = $s['tallos_cosechados'];
                $cos_total += $cosechados;

                $tallos_m2_ejec = $area_semana > 0 ? round($cosechados / $area_semana, 2) : 0;
                $prom_tallos_m2_ejec += $tallos_m2_ejec;
                $flor_m2_anno_52 = $area_semana > 0 && ($pos + 1) > 0 ? round((($cos_total / $area_semana) / ($pos + 1)) * 52, 2) : 0;
                $prom_flor_m2_anno_52 += $flor_m2_anno_52;
                if ($tallos_m2_ejec > 0)
                    $positivo_tallos_m2_ejec++;
                if ($flor_m2_anno_52 > 0)
                    $positivo_flor_m2_anno_52++;

                setValueToCeldaExcel($objSheet, 'A' . $row, $s['codigo']);
                setValueToCeldaExcel($objSheet, 'B' . $row, round($area_produccion, 2));
                setValueToCeldaExcel($objSheet, 'C' . $row, round($area_semana, 2));
                setValueToCeldaExcel($objSheet, 'D' . $row, round($proyectados, 2));
                setValueToCeldaExcel($objSheet, 'E' . $row, round($proy_total, 2));
                setValueToCeldaExcel($objSheet, 'F' . $row, $cosechados);
                setValueToCeldaExcel($objSheet, 'G' . $row, $cos_total);
                setValueToCeldaExcel($objSheet, 'H' . $row, porcentaje($cosechados, $proyectados, 1) . '%');
                setValueToCeldaExcel($objSheet, 'I' . $row, porcentaje($cos_total, $proy_total, 1) . '%');
                setValueToCeldaExcel($objSheet, 'J' . $row, $tallos_m2_ejec);
                setValueToCeldaExcel($objSheet, 'K' . $row, $prom_tallos_m2_ejec);
                setValueToCeldaExcel($objSheet, 'L' . $row, $flor_m2_anno_52);
            }
            $row++;
            setValueToCeldaExcel($objSheet, 'A' . $row, 'TOTALES');
            setValueToCeldaExcel($objSheet, 'B' . $row, round($area_total / count($semanas), 2));
            setValueToCeldaExcel($objSheet, 'D' . $row, round($proy_total, 2));
            setValueToCeldaExcel($objSheet, 'F' . $row, $cos_total);
            setValueToCeldaExcel($objSheet, 'H' . $row, porcentaje($cos_total, $proy_total, 1) . '%');
            setValueToCeldaExcel($objSheet, 'J' . $row, round($prom_tallos_m2_ejec / $positivo_tallos_m2_ejec, 2));
            setValueToCeldaExcel($objSheet, 'L' . $row, round($prom_flor_m2_anno_52 / $positivo_flor_m2_anno_52, 2));
            setColorTextToCeldaExcel($objSheet, 'A' . $row . ':L' . $row, 'FFFFFF');    // blanco
            setBgToCeldaExcel($objSheet, 'A' . $row . ':L' . $row, '00b388');    // verde

            setBorderToCeldaExcel($objSheet, 'A1:L' . $row);
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
        } else {    // todas las variedades
            $semanas = DB::table('semana')
                ->select('codigo', 'fecha_inicial', 'fecha_final')->distinct()
                ->where('estado', 1)
                ->where('anno', $request->anno)
                ->orderBy('codigo')
                ->get();
            $listado = [];
            foreach ($semanas as $sem) {
                $proy = DB::table('proy_no_perennes as proy')
                    ->join('semana as s', 's.id_semana', '=', 'proy.id_semana')
                    ->join('variedad as v', 'v.id_variedad', '=', 's.id_variedad')
                    ->select(DB::raw('sum(proy.area_produccion) as area_produccion'),
                        DB::raw('sum(proy.area_semana) as area_semana'),
                        DB::raw('sum(proy.proyectados) as proyectados'))
                    ->where('s.codigo', $sem->codigo)
                    ->where('proy.id_empresa', $finca)
                    ->where('v.id_planta', $request->planta)
                    ->get()[0];
                $cos = DB::table('resumen_total_semanal_exportcalas as p')
                    ->join('variedad as v', 'v.id_variedad', '=', 'p.id_variedad')
                    ->select(DB::raw('sum(p.tallos_cosechados) as tallos_cosechados'))
                    ->where('v.id_planta', $request->planta)
                    ->where('p.id_empresa', $finca)
                    ->where('p.semana', $sem->codigo)
                    ->get()[0];
                array_push($listado, [
                    'codigo' => $sem->codigo,
                    'fecha_inicial' => $sem->fecha_inicial,
                    'fecha_final' => $sem->fecha_final,
                    'area_produccion' => $proy->area_produccion,
                    'area_semana' => $proy->area_semana,
                    'proyectados' => $proy->proyectados,
                    'tallos_cosechados' => $cos->tallos_cosechados,
                ]);
            }

            /* ----------------------- CREAR HOJA DE EXCEL ------------------------ */
            $objSheet = $spread->getActiveSheet()->setTitle('Proyecciones');

            $row = 1;
            setValueToCeldaExcel($objSheet, 'A' . $row, 'Semana');
            setValueToCeldaExcel($objSheet, 'B' . $row, 'Área Prod.');
            setValueToCeldaExcel($objSheet, 'C' . $row, 'Área Cult.');
            setValueToCeldaExcel($objSheet, 'D' . $row, 'Tallos Proy.');
            setValueToCeldaExcel($objSheet, 'E' . $row, 'Tallos Proy. Acum. Año');
            setValueToCeldaExcel($objSheet, 'F' . $row, 'Tallos Cos.');
            setValueToCeldaExcel($objSheet, 'G' . $row, 'Tallos Cos. Acum. Año');
            setValueToCeldaExcel($objSheet, 'H' . $row, '% Cump.');
            setValueToCeldaExcel($objSheet, 'I' . $row, '% Cump. Acum. Año');
            setValueToCeldaExcel($objSheet, 'J' . $row, 'Tallos/m2 Ejec.');
            setValueToCeldaExcel($objSheet, 'K' . $row, 'Tallos/m2 Ejec. Acum.');
            setValueToCeldaExcel($objSheet, 'L' . $row, 'Tallos/m2/año (52 sem.)');

            setColorTextToCeldaExcel($objSheet, 'A' . $row . ':L' . $row, 'FFFFFF');    // blanco
            setBgToCeldaExcel($objSheet, 'A' . $row . ':L' . $row, '00b388');    // verde
            setBgToCeldaExcel($objSheet, 'B' . $row . ':C' . $row, '0b3248');    // azul oscuro

            $area_total = 0;
            $proy_total = 0;
            $cos_total = 0;
            $prom_tallos_m2_ejec = 0;
            $positivo_tallos_m2_ejec = 0;
            $prom_flor_m2_anno_52 = 0;
            $positivo_flor_m2_anno_52 = 0;
            foreach ($listado as $pos => $s) {
                $row++;
                $area_produccion = $s['area_produccion'];
                $area_semana = $s['area_semana'];
                $area_total += $area_produccion;
                $proyectados = $s['proyectados'];
                $proy_total += $proyectados;
                $cosechados = $s['tallos_cosechados'];
                $cos_total += $cosechados;

                $tallos_m2_ejec = $area_semana > 0 ? round($cosechados / $area_semana, 2) : 0;
                $prom_tallos_m2_ejec += $tallos_m2_ejec;
                $flor_m2_anno_52 = $area_semana > 0 && ($pos + 1) > 0 ? round((($cos_total / $area_semana) / ($pos + 1)) * 52, 2) : 0;
                $prom_flor_m2_anno_52 += $flor_m2_anno_52;
                if ($tallos_m2_ejec > 0)
                    $positivo_tallos_m2_ejec++;
                if ($flor_m2_anno_52 > 0)
                    $positivo_flor_m2_anno_52++;

                setValueToCeldaExcel($objSheet, 'A' . $row, $s['codigo']);
                setValueToCeldaExcel($objSheet, 'B' . $row, round($area_produccion, 2));
                setValueToCeldaExcel($objSheet, 'C' . $row, round($area_semana, 2));
                setValueToCeldaExcel($objSheet, 'D' . $row, round($proyectados, 2));
                setValueToCeldaExcel($objSheet, 'E' . $row, round($proy_total, 2));
                setValueToCeldaExcel($objSheet, 'F' . $row, $cosechados);
                setValueToCeldaExcel($objSheet, 'G' . $row, $cos_total);
                setValueToCeldaExcel($objSheet, 'H' . $row, porcentaje($cosechados, $proyectados, 1) . '%');
                setValueToCeldaExcel($objSheet, 'I' . $row, porcentaje($cos_total, $proy_total, 1) . '%');
                setValueToCeldaExcel($objSheet, 'J' . $row, $tallos_m2_ejec);
                setValueToCeldaExcel($objSheet, 'K' . $row, $prom_tallos_m2_ejec);
                setValueToCeldaExcel($objSheet, 'L' . $row, $flor_m2_anno_52);
            }
            $row++;
            setValueToCeldaExcel($objSheet, 'A' . $row, 'TOTALES');
            setValueToCeldaExcel($objSheet, 'B' . $row, round($area_total / count($semanas), 2));
            setValueToCeldaExcel($objSheet, 'D' . $row, round($proy_total, 2));
            setValueToCeldaExcel($objSheet, 'F' . $row, $cos_total);
            setValueToCeldaExcel($objSheet, 'H' . $row, porcentaje($cos_total, $proy_total, 1) . '%');
            setValueToCeldaExcel($objSheet, 'J' . $row, round($prom_tallos_m2_ejec / $positivo_tallos_m2_ejec, 2));
            setValueToCeldaExcel($objSheet, 'L' . $row, round($prom_flor_m2_anno_52 / $positivo_flor_m2_anno_52, 2));
            setColorTextToCeldaExcel($objSheet, 'A' . $row . ':L' . $row, 'FFFFFF');    // blanco
            setBgToCeldaExcel($objSheet, 'A' . $row . ':L' . $row, '00b388');    // verde

            setBorderToCeldaExcel($objSheet, 'A1:L' . $row);
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
        }
    }

    public function excel_reporte_proyecciones_perennes($spread, $request)
    {
        $finca = getFincaActiva();
        if ($request->variedad != 'T') {    // una variedad
            $tallos_m2_anno = DB::table('semana as sem')
                ->join('semana_proy_perenne as p', 'p.id_semana', '=', 'sem.id_semana')
                ->select(DB::raw('sum(p.curva) as cantidad'))
                ->where('sem.estado', 1)
                ->where('sem.id_variedad', $request->variedad)
                ->where('sem.anno', $request->anno)
                ->where('p.id_empresa', $finca)
                ->get()[0]->cantidad;

            $tallos_m2_anno = $tallos_m2_anno > 0 ? $tallos_m2_anno : 0;

            $area = 0;
            $listado = [];
            if ($tallos_m2_anno > 0) {
                $semanas = Semana::where('anno', $request->anno)
                    ->where('id_variedad', $request->variedad)
                    ->orderBy('codigo')
                    ->get();
                $area = DB::table('ciclo as c')
                    ->select(DB::raw('sum(c.area) as area'))
                    ->where('c.estado', 1)
                    ->where('c.activo', 1)
                    ->where('c.id_variedad', $request->variedad)
                    ->where('c.id_empresa', $finca)
                    ->get()[0]->area;
                $area = $area > 0 ? $area : 0;
                foreach ($semanas as $sem) {
                    $valores = DB::table('semana as sem')
                        ->join('semana_proy_perenne as p', 'p.id_semana', '=', 'sem.id_semana')
                        ->select('sem.id_semana', 'sem.codigo', 'sem.fecha_inicial', 'sem.fecha_final',
                            'p.curva as curva_perenne', 'p.proyectados', 'p.cosechados', 'p.porcentaje_cumplimiento', 'p.tallos_m2_ejecutado',
                            'p.sum_ejec_4_sem', 'p.sum_ejec_13_sem', 'p.sum_ejec_52_sem', 'p.plantas_iniciales',
                            'p.proyectados_acum', 'p.cosechados_acum', 'p.porcentaje_cumplimiento_acum', 'p.tallos_m2_ejecutado_acum')
                        ->where('sem.estado', 1)
                        ->where('sem.id_variedad', $request->variedad)
                        ->where('sem.codigo', $sem->codigo)
                        ->where('p.id_empresa', $finca)
                        ->orderBy('sem.codigo')
                        ->get();
                    $valores = count($valores) == 1 ? $valores : '';

                    array_push($listado, [
                        'semana' => $sem,
                        'valores' => $valores[0],
                    ]);
                }
            }

            /* ----------------------- CREAR HOJA DE EXCEL ------------------------ */
            $objSheet = $spread->getActiveSheet()->setTitle('Proyecciones');

            $row = 1;
            setValueToCeldaExcel($objSheet, 'A' . $row, 'SEMANA');
            setValueToCeldaExcel($objSheet, 'B' . $row, 'Área m2');
            setValueToCeldaExcel($objSheet, 'C' . $row, 'Ptas. Iniciales');
            setValueToCeldaExcel($objSheet, 'D' . $row, 'Densidad');
            setValueToCeldaExcel($objSheet, 'E' . $row, 'Tallos/m2/año');
            setValueToCeldaExcel($objSheet, 'F' . $row, 'Tallos/m2/sem.');
            setValueToCeldaExcel($objSheet, 'G' . $row, 'Tallos Proy.');
            setValueToCeldaExcel($objSheet, 'H' . $row, 'Tallos Proy. Acum. Año');
            setValueToCeldaExcel($objSheet, 'I' . $row, 'Tallos Proy. Acum. 52');
            setValueToCeldaExcel($objSheet, 'J' . $row, 'Tallos Cos.');
            setValueToCeldaExcel($objSheet, 'K' . $row, 'Tallos Cos. Acum. Año');
            setValueToCeldaExcel($objSheet, 'L' . $row, 'Tallos Cos. Acum. 52');
            setValueToCeldaExcel($objSheet, 'M' . $row, '% Cump. Sem.');
            setValueToCeldaExcel($objSheet, 'N' . $row, '% Cump. Sem. Acum.');
            setValueToCeldaExcel($objSheet, 'O' . $row, 'Tallos/m2 Ejec.');
            setValueToCeldaExcel($objSheet, 'P' . $row, 'Tallos/m2 Ejec. Acum.');
            setValueToCeldaExcel($objSheet, 'Q' . $row, 'Tallos/m2/año (52 sem.)');

            setColorTextToCeldaExcel($objSheet, 'A' . $row . ':Q' . $row, 'FFFFFF');    // blanco
            setBgToCeldaExcel($objSheet, 'A' . $row . ':Q' . $row, '00b388');    // verde

            $proy_acum_anno = 0;
            $cos_acum = 0;
            $prom_tallos_m2_ejec = 0;
            $positivo_tallos_m2_ejec = 0;
            $prom_flor_m2_anno_52 = 0;
            $positivo_flor_m2_anno_52 = 0;
            foreach($listado as $pos => $item) {
                $row++;
                $densidad = $area > 0 ? $item['valores']->plantas_iniciales / $area : 0;
                $proy_acum_anno += $item['valores']->proyectados;
                $cos_acum += $item['valores']->cosechados;
                $prom_tallos_m2_ejec += $item['valores']->tallos_m2_ejecutado;
                $flor_m2_anno_52 = $area > 0 && ($pos + 1) > 0 ? round((($cos_acum / $area) / ($pos + 1)) * 52, 2) : 0;
                $prom_flor_m2_anno_52 += $flor_m2_anno_52;
                if ($item['valores']->tallos_m2_ejecutado > 0)
                    $positivo_tallos_m2_ejec++;
                if ($flor_m2_anno_52 > 0)
                    $positivo_flor_m2_anno_52++;

                setValueToCeldaExcel($objSheet, 'A' . $row, $item['semana']->codigo);
                setValueToCeldaExcel($objSheet, 'B' . $row, round($area, 2));
                setValueToCeldaExcel($objSheet, 'C' . $row, round($item['valores']->plantas_iniciales));
                setValueToCeldaExcel($objSheet, 'D' . $row, round($densidad, 2));
                setValueToCeldaExcel($objSheet, 'E' . $row, round($tallos_m2_anno, 2));
                setValueToCeldaExcel($objSheet, 'F' . $row, $item['valores']->curva_perenne);
                setValueToCeldaExcel($objSheet, 'G' . $row, round($item['valores']->proyectados, 2));
                setValueToCeldaExcel($objSheet, 'H' . $row, round($proy_acum_anno, 2));
                setValueToCeldaExcel($objSheet, 'I' . $row, round($item['valores']->proyectados_acum, 2));
                setValueToCeldaExcel($objSheet, 'J' . $row, round($item['valores']->cosechados));
                setValueToCeldaExcel($objSheet, 'K' . $row, round($cos_acum));
                setValueToCeldaExcel($objSheet, 'L' . $row, round($item['valores']->cosechados_acum));
                setValueToCeldaExcel($objSheet, 'M' . $row, round($item['valores']->porcentaje_cumplimiento, 2).'%');
                setValueToCeldaExcel($objSheet, 'N' . $row, round($item['valores']->porcentaje_cumplimiento_acum, 2).'%');
                setValueToCeldaExcel($objSheet, 'O' . $row, round($item['valores']->tallos_m2_ejecutado, 2));
                setValueToCeldaExcel($objSheet, 'P' . $row, round($prom_tallos_m2_ejec, 2));
                setValueToCeldaExcel($objSheet, 'Q' . $row, round($flor_m2_anno_52, 2));
            }
            $row++;
            setValueToCeldaExcel($objSheet, 'A' . $row, 'TOTALES');
            setValueToCeldaExcel($objSheet, 'G' . $row, round($proy_acum_anno, 2));
            setValueToCeldaExcel($objSheet, 'J' . $row, round($cos_acum));
            setValueToCeldaExcel($objSheet, 'N' . $row, $proy_acum_anno > 0 ? round(($cos_acum * 100) / $proy_acum_anno, 2) : 0);
            setValueToCeldaExcel($objSheet, 'O' . $row, round($prom_tallos_m2_ejec / $positivo_tallos_m2_ejec, 2));
            setValueToCeldaExcel($objSheet, 'Q' . $row, round($prom_flor_m2_anno_52 / $positivo_flor_m2_anno_52, 2));

            setColorTextToCeldaExcel($objSheet, 'A' . $row . ':Q' . $row, 'FFFFFF');    // blanco
            setBgToCeldaExcel($objSheet, 'A' . $row . ':Q' . $row, '00b388');    // verde

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
        } else {    // todas las variedades
            $pta = Planta::find($request->planta);
            $variedades = $pta->variedades->where('estado', 1);
            $ids_variedad = [];
            $prom_tallos_m2_anno = 0;
            $cantidad = 0;

            foreach ($variedades as $var) {
                $tallos_m2_anno = DB::table('semana as sem')
                    ->join('semana_proy_perenne as p', 'p.id_semana', '=', 'sem.id_semana')
                    ->select(DB::raw('sum(p.curva) as cantidad'))
                    ->where('sem.estado', 1)
                    ->where('sem.id_variedad', $var->id_variedad)
                    ->where('sem.anno', $request->anno)
                    ->where('p.id_empresa', $finca)
                    ->get()[0]->cantidad;
                $tallos_m2_anno = $tallos_m2_anno > 0 ? $tallos_m2_anno : 0;

                if ($tallos_m2_anno > 0) {
                    array_push($ids_variedad, $var->id_variedad);
                    $prom_tallos_m2_anno += $tallos_m2_anno;
                    $cantidad++;
                }
            }

            $listado = [];
            if ($prom_tallos_m2_anno > 0) {
                $semanas = DB::table('semana')
                    ->select('codigo', 'fecha_inicial', 'fecha_final')->distinct()
                    ->where('estado', 1)
                    ->where('anno', $request->anno)
                    ->orderBy('codigo')
                    ->get();
                foreach ($semanas as $sem) {
                    $valores = DB::table('semana as sem')
                        ->join('semana_proy_perenne as p', 'p.id_semana', '=', 'sem.id_semana')
                        ->select(DB::raw('sum(p.curva) as curva_perenne'), DB::raw('sum(p.proyectados) as proyectados'),
                            DB::raw('sum(p.cosechados) as cosechados'),
                            DB::raw('sum(p.porcentaje_cumplimiento) as porcentaje_cumplimiento'),
                            DB::raw('sum(p.tallos_m2_ejecutado) as tallos_m2_ejecutado'),
                            DB::raw('sum(p.sum_ejec_4_sem) as sum_ejec_4_sem'),
                            DB::raw('sum(p.sum_ejec_13_sem) as sum_ejec_13_sem'),
                            DB::raw('sum(p.sum_ejec_52_sem) as sum_ejec_52_sem'),
                            DB::raw('sum(p.plantas_iniciales) as plantas_iniciales'),
                            DB::raw('sum(p.proyectados_acum) as proyectados_acum'),
                            DB::raw('sum(p.cosechados_acum) as cosechados_acum'),
                            DB::raw('sum(p.porcentaje_cumplimiento_acum) as porcentaje_cumplimiento_acum'),
                            DB::raw('sum(p.tallos_m2_ejecutado_acum) as tallos_m2_ejecutado_acum'))
                        ->where('sem.estado', 1)
                        ->whereIn('sem.id_variedad', $ids_variedad)
                        ->where('sem.codigo', $sem->codigo)
                        ->where('p.id_empresa', $finca)
                        ->get()[0];
                    $area = DB::table('ciclo as c')
                        ->select(DB::raw('sum(c.area) as area'))
                        ->where('c.estado', 1)
                        ->where('c.activo', 1)
                        ->whereIn('c.id_variedad', $ids_variedad)
                        ->where('c.id_empresa', $finca)
                        ->get()[0]->area;
                    $area = $area > 0 ? $area : 0;
                    array_push($listado, [
                        'semana' => $sem,
                        'valores' => $valores,
                        'area' => $area,
                        'tallos_m2_anno' => $cantidad > 0 ? round($prom_tallos_m2_anno / $cantidad, 2) : 0,
                    ]);
                }
            }

            /* ----------------------- CREAR HOJA DE EXCEL ------------------------ */
            $objSheet = $spread->getActiveSheet()->setTitle('Proyecciones');

            $row = 1;
            setValueToCeldaExcel($objSheet, 'A' . $row, 'SEMANA');
            setValueToCeldaExcel($objSheet, 'B' . $row, 'Área m2');
            setValueToCeldaExcel($objSheet, 'C' . $row, 'Ptas. Iniciales');
            setValueToCeldaExcel($objSheet, 'D' . $row, 'Densidad');
            setValueToCeldaExcel($objSheet, 'E' . $row, 'Tallos proy/m2/año');
            setValueToCeldaExcel($objSheet, 'F' . $row, 'Tallos Proy.');
            setValueToCeldaExcel($objSheet, 'G' . $row, 'Tallos Proy. Acum. Año');
            setValueToCeldaExcel($objSheet, 'H' . $row, 'Tallos Proy. Acum. 52');
            setValueToCeldaExcel($objSheet, 'I' . $row, 'Tallos Cos.');
            setValueToCeldaExcel($objSheet, 'J' . $row, 'Tallos Cos. Acum. Año');
            setValueToCeldaExcel($objSheet, 'K' . $row, 'Tallos Cos. Acum. 52');
            setValueToCeldaExcel($objSheet, 'L' . $row, '% Cump. Sem.');
            setValueToCeldaExcel($objSheet, 'M' . $row, '% Cump. Sem. Acum.');
            setValueToCeldaExcel($objSheet, 'N' . $row, 'Tallos/m2 Ejec.');
            setValueToCeldaExcel($objSheet, 'O' . $row, 'Tallos/m2 Ejec. Acum.');
            setValueToCeldaExcel($objSheet, 'P' . $row, 'Tallos/m2/año (52 sem)');

            setColorTextToCeldaExcel($objSheet, 'A' . $row . ':P' . $row, 'FFFFFF');    // blanco
            setBgToCeldaExcel($objSheet, 'A' . $row . ':P' . $row, '00b388');    // verde

            $proy_acum_anno = 0;
            $cos_acum = 0;
            $prom_tallos_m2_ejec = 0;
            $positivo_tallos_m2_ejec = 0;
            $prom_flor_m2_anno_52 = 0;
            $positivo_flor_m2_anno_52 = 0;
            foreach($listado as $pos => $item) {
                $row++;
                $densidad = $item['area'] > 0 ? $item['valores']->plantas_iniciales / $item['area'] : 0;
                $proy_acum_anno += $item['valores']->proyectados;
                $cos_acum += $item['valores']->cosechados;
                $tallos_m2_ejecutado = $item['area'] > 0 ? $item['valores']->cosechados / $item['area'] : 0;
                $prom_tallos_m2_ejec += $tallos_m2_ejecutado;
                $flor_m2_anno_52 = $item['area'] > 0 && ($pos + 1) > 0 ? round((($cos_acum / $item['area']) / ($pos + 1)) * 52, 2) : 0;
                $prom_flor_m2_anno_52 += $flor_m2_anno_52;
                if ($tallos_m2_ejecutado > 0)
                    $positivo_tallos_m2_ejec++;
                if ($flor_m2_anno_52 > 0)
                    $positivo_flor_m2_anno_52++;

                setValueToCeldaExcel($objSheet, 'A' . $row, $item['semana']->codigo);
                setValueToCeldaExcel($objSheet, 'B' . $row, round($item['area'], 2));
                setValueToCeldaExcel($objSheet, 'C' . $row, round($item['valores']->plantas_iniciales));
                setValueToCeldaExcel($objSheet, 'D' . $row, round($densidad, 2));
                setValueToCeldaExcel($objSheet, 'E' . $row, round($item['area'] > 0 ? round($item['valores']->proyectados / $item['area'], 2) : 0));
                setValueToCeldaExcel($objSheet, 'F' . $row, round($item['valores']->proyectados, 2));
                setValueToCeldaExcel($objSheet, 'G' . $row, round($proy_acum_anno, 2));
                setValueToCeldaExcel($objSheet, 'H' . $row, round($item['valores']->proyectados_acum, 2));
                setValueToCeldaExcel($objSheet, 'I' . $row, round($item['valores']->cosechados));
                setValueToCeldaExcel($objSheet, 'J' . $row, round($cos_acum));
                setValueToCeldaExcel($objSheet, 'K' . $row, round($item['valores']->cosechados_acum));
                setValueToCeldaExcel($objSheet, 'L' . $row, porcentaje($item['valores']->cosechados, $item['valores']->proyectados, 1).'%');
                setValueToCeldaExcel($objSheet, 'M' . $row, porcentaje($item['valores']->cosechados_acum, $item['valores']->proyectados_acum, 1).'%');
                setValueToCeldaExcel($objSheet, 'N' . $row, $item['area'] > 0 ? round($item['valores']->cosechados / $item['area'], 2) : 0);
                setValueToCeldaExcel($objSheet, 'O' . $row, round($prom_tallos_m2_ejec, 2));
                setValueToCeldaExcel($objSheet, 'P' . $row, round($flor_m2_anno_52, 2));
            }
            $row++;
            setValueToCeldaExcel($objSheet, 'A' . $row, 'TOTALES');
            setValueToCeldaExcel($objSheet, 'F' . $row, round($proy_acum_anno, 2));
            setValueToCeldaExcel($objSheet, 'I' . $row, round($cos_acum));
            setValueToCeldaExcel($objSheet, 'M' . $row, porcentaje($cos_acum, $proy_acum_anno, 1).'%');
            setValueToCeldaExcel($objSheet, 'N' . $row, round($prom_tallos_m2_ejec / $positivo_tallos_m2_ejec, 2));
            setValueToCeldaExcel($objSheet, 'P' . $row, round($prom_flor_m2_anno_52 / $positivo_flor_m2_anno_52, 2));

            setColorTextToCeldaExcel($objSheet, 'A' . $row . ':P' . $row, 'FFFFFF');    // blanco
            setBgToCeldaExcel($objSheet, 'A' . $row . ':P' . $row, '00b388');    // verde

            setBorderToCeldaExcel($objSheet, 'A1:P' . $row);
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
        }
    }
}
