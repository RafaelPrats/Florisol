<?php

namespace yura\Http\Controllers\Costos;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use yura\Http\Controllers\Controller;
use yura\Modelos\Submenu;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use yura\Modelos\Planta;

class TablaOperacionesController extends Controller
{
    public function inicio(Request $request)
    {
        $semana_hasta = getSemanaByDate(opDiasFecha('-', 7, date('Y-m-d')))->codigo;
        $semana_desde = substr($semana_hasta, 0, 2) . '01';
        $plantas = Planta::where('estado', 1)
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.costos.tabla_operaciones.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'hasta' => $semana_hasta,
            'desde' => $semana_desde,
            'plantas' => $plantas,
        ]);
    }

    public function listado_operaciones(Request $request)
    {
        $finca = getFincaActiva();
        $planta = $request->planta == 'T' ? '' : Planta::find($request->planta);
        $semanas = DB::table('semana')
            ->select('codigo', 'fecha_inicial', 'fecha_final')->distinct()
            ->where('codigo', '>=', $request->desde)
            ->where('codigo', '<=', $request->hasta)
            ->orderBy('codigo')
            ->get();

        $fincas = [$finca];
        $finca_comprada = [];
        $otras_fincas = [];
        if ($finca == 2) {
            array_push($fincas, -1);
            array_push($finca_comprada, -1);
            $otras_fincas = [1, 3];
        }

        $listado = [];
        foreach ($semanas as $sem) {
            /* ------------------ AREA ------------------- */
            $area_a = $area_b = $area_a_finca = $area_b_finca = 0;
            if ($planta == '' || $planta->tiene_ciclos == 1 || $planta->tipo == 'P') {
                $q_area_a = DB::table('ciclo as c')
                    ->join('variedad as v', 'v.id_variedad', '=', 'c.id_variedad')
                    ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                    ->select(DB::raw('sum(c.area) as area'))
                    ->where('v.estado', 1)
                    ->where('p.estado', 1)
                    ->where('c.estado', '=', 1)
                    ->where('c.id_empresa', $finca)
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
                            });
                    })
                    ->Where(function ($q) use ($sem) {
                        $q->where('p.tipo', 'P')
                            ->orWhere('p.tiene_ciclos', 1);
                    });
                $area_a_finca = $q_area_a->get()[0]->area;

                if ($planta != '')
                    $q_area_a = $q_area_a->where('v.id_planta', $planta->id_planta);
                $area_a = $q_area_a->get()[0]->area;
                if ($planta != '') {
                    $q_area_b = DB::table('proy_no_perennes as proy')
                        ->join('semana as s', 's.id_semana', '=', 'proy.id_semana')
                        ->join('variedad as v', 'v.id_variedad', '=', 's.id_variedad')
                        ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                        ->select(
                            DB::raw('sum(proy.area_produccion) as area_produccion'),
                            DB::raw('sum(proy.area_semana) as area_semana')
                        )
                        ->where('s.codigo', $sem->codigo)
                        ->where('p.estado', 1)
                        ->where('v.estado', 1)
                        ->where('p.tiene_ciclos', 0)
                        ->where('p.tipo', 'N')
                        ->where('proy.id_empresa', $finca);
                    $area_b_finca = $q_area_b->get()[0]->area_produccion;
                }
            }
            if ($planta == '' || ($planta->tiene_ciclos == 0 && $planta->tipo == 'N')) {
                $q_area_b = DB::table('proy_no_perennes as proy')
                    ->join('semana as s', 's.id_semana', '=', 'proy.id_semana')
                    ->join('variedad as v', 'v.id_variedad', '=', 's.id_variedad')
                    ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                    ->select(
                        DB::raw('sum(proy.area_produccion) as area_produccion'),
                        DB::raw('sum(proy.area_semana) as area_semana')
                    )
                    ->where('s.codigo', $sem->codigo)
                    ->where('p.estado', 1)
                    ->where('v.estado', 1)
                    ->where('p.tiene_ciclos', 0)
                    ->where('p.tipo', 'N')
                    ->where('proy.id_empresa', $finca);
                $area_b_finca = $q_area_b->get()[0]->area_produccion;
                if ($planta != '')
                    $q_area_b = $q_area_b->where('v.id_planta', $planta->id_planta);
                $area_b = $q_area_b->get()[0]->area_produccion;

                if ($planta != '') {
                    $q_area_a = DB::table('ciclo as c')
                        ->join('variedad as v', 'v.id_variedad', '=', 'c.id_variedad')
                        ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                        ->select(DB::raw('sum(c.area) as area'))
                        ->where('v.estado', 1)
                        ->where('p.estado', 1)
                        ->where('c.estado', '=', 1)
                        ->where('c.id_empresa', $finca)
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
                                });
                        })
                        ->Where(function ($q) use ($sem) {
                            $q->where('p.tipo', 'P')
                                ->orWhere('p.tiene_ciclos', 1);
                        });
                    $area_a_finca = $q_area_a->get()[0]->area;
                }
            }
            $area = $area_a + $area_b;
            $area_finca = $area_a_finca + $area_b_finca;

            /* COMPRA_FLOR */
            $compra_flor = DB::table('bouquetera as b')
                ->join('variedad as v', 'v.id_variedad', '=', 'b.id_variedad')
                ->select(
                    DB::raw('sum(b.precio * (tallos)) as tallos'),
                    DB::raw('sum(b.precio * (exportada)) as exportada'),
                    DB::raw('sum(b.tallos) as tallos_bqt'),
                    DB::raw('sum(b.exportada) as tallos_exportada')
                )
                ->where('b.fecha', '>=', $sem->fecha_inicial)
                ->where('b.fecha', '<=', $sem->fecha_final)
                ->whereIn('b.id_empresa', $fincas);
            if ($planta != '')
                $compra_flor = $compra_flor->where('v.id_planta', $planta->id_planta);
            $compra_flor = $compra_flor->get()[0];

            $compra_flor_finca = DB::table('bouquetera as b')
                ->join('variedad as v', 'v.id_variedad', '=', 'b.id_variedad')
                ->select(
                    DB::raw('sum(b.precio * (tallos)) as tallos'),
                    DB::raw('sum(b.precio * (exportada)) as exportada'),
                    DB::raw('sum(b.tallos) as tallos_bqt'),
                    DB::raw('sum(b.exportada) as tallos_exportada')
                )
                ->where('b.fecha', '>=', $sem->fecha_inicial)
                ->where('b.fecha', '<=', $sem->fecha_final)
                ->where('b.id_empresa', $finca);
            if ($planta != '')
                $compra_flor_finca = $compra_flor_finca->where('v.id_planta', $planta->id_planta);
            $compra_flor_finca = $compra_flor_finca->get()[0];

            $compra_flor_otras_fincas = DB::table('bouquetera as b')
                ->join('variedad as v', 'v.id_variedad', '=', 'b.id_variedad')
                ->select(
                    DB::raw('sum(b.precio * (tallos)) as tallos'),
                    DB::raw('sum(b.precio * (exportada)) as exportada'),
                    DB::raw('sum(b.tallos) as tallos_bqt'),
                    DB::raw('sum(b.exportada) as tallos_exportada')
                )
                ->where('b.fecha', '>=', $sem->fecha_inicial)
                ->where('b.fecha', '<=', $sem->fecha_final)
                ->whereIn('b.id_empresa', $otras_fincas);
            if ($planta != '')
                $compra_flor_otras_fincas = $compra_flor_otras_fincas->where('v.id_planta', $planta->id_planta);
            $compra_flor_otras_fincas = $compra_flor_otras_fincas->get()[0];

            $flor_comprada_exp = DB::table('bouquetera as b')
                ->join('variedad as v', 'v.id_variedad', '=', 'b.id_variedad')
                ->select(
                    DB::raw('sum(b.precio * (tallos)) as tallos'),
                    DB::raw('sum(b.precio * (exportada)) as exportada'),
                    DB::raw('sum(b.tallos) as tallos_bqt'),
                    DB::raw('sum(b.exportada) as tallos_exportada')
                )
                ->where('b.fecha', '>=', $sem->fecha_inicial)
                ->where('b.fecha', '<=', $sem->fecha_final)
                ->whereIn('b.id_empresa', $fincas);
            if ($planta != '')
                $flor_comprada_exp = $flor_comprada_exp->where('v.id_planta', $planta->id_planta);
            $flor_comprada_exp = $flor_comprada_exp->get()[0];

            $flor_comprada_bqt = DB::table('bouquetera as b')
                ->join('variedad as v', 'v.id_variedad', '=', 'b.id_variedad')
                ->select(
                    DB::raw('sum(b.precio * (tallos)) as tallos'),
                    DB::raw('sum(b.precio * (exportada)) as exportada'),
                    DB::raw('sum(b.tallos) as tallos_bqt'),
                    DB::raw('sum(b.exportada) as tallos_exportada')
                )
                ->where('b.fecha', '>=', $sem->fecha_inicial)
                ->where('b.fecha', '<=', $sem->fecha_final)
                ->whereIn('b.id_empresa', $finca_comprada);
            if ($planta != '')
                $flor_comprada_bqt = $flor_comprada_bqt->where('v.id_planta', $planta->id_planta);
            $flor_comprada_bqt = $flor_comprada_bqt->get()[0];

            array_push($listado, [
                'semana' => $sem,
                'area' => $area,
                'area_finca' => $area_finca,
                'compra_flor' => $compra_flor,
                'compra_flor_finca' => $compra_flor_finca,
                'compra_flor_otras_fincas' => $compra_flor_otras_fincas,
                'flor_comprada_exp' => $flor_comprada_exp,
                'flor_comprada_bqt' => $flor_comprada_bqt,
            ]);
        }

        /* RESUMEN_SEMANAL */
        $resumen_semanal = DB::table('resumen_total_semanal_exportcalas as r')
            ->join('variedad as v', 'v.id_variedad', '=', 'r.id_variedad')
            ->select(
                'semana',
                DB::raw('sum(r.tallos_cosechados) as tallos_cosechados'),
                DB::raw('sum(r.tallos_exportables) as tallos_exportables'),
                DB::raw('sum(r.bouquetera) as bouquetera'),
                DB::raw('sum(r.venta) as venta'),
                DB::raw('sum(r.nacional) as nacionales'),
                DB::raw('sum(r.bajas) as bajas'),
                DB::raw('sum(r.tallos_vendidos) as tallos_vendidos'),
                DB::raw('sum(r.tallos_bqt_4_sem) as tallos_bqt_4_sem'),
                DB::raw('sum(r.ventas_bqt_4_sem) as ventas_bqt_4_sem'),
                DB::raw('sum(r.venta_bouquetera) as venta_bouquetera')
            )
            ->where('r.id_empresa', $finca)
            ->where('r.semana', '>=', $request->desde)
            ->where('r.semana', '<=', $request->hasta);
        if ($planta != '')
            $resumen_semanal = $resumen_semanal->where('v.id_planta', $planta->id_planta);
        $resumen_semanal = $resumen_semanal->groupBy('r.semana')
            ->orderBy('r.semana')
            ->get();

        /* RESUMEN_SEMANAL FINCA */
        $resumen_semanal_finca = DB::table('resumen_total_semanal_exportcalas as r')
            ->select(
                'semana',
                /*DB::raw('sum(r.tallos_cosechados) as tallos_cosechados'),
                DB::raw('sum(r.tallos_exportables) as tallos_exportables'),
                DB::raw('sum(r.bouquetera) as bouquetera'),
                DB::raw('sum(r.venta) as venta'),
                DB::raw('sum(r.nacional) as nacionales'),
                DB::raw('sum(r.bajas) as bajas'),
                DB::raw('sum(r.tallos_vendidos) as tallos_vendidos'),*/
                DB::raw('sum(r.tallos_bqt_4_sem) as tallos_bqt_4_sem'),
                DB::raw('sum(r.ventas_bqt_4_sem) as ventas_bqt_4_sem'),
                //DB::raw('sum(r.venta_bouquetera) as venta_bouquetera')
            )
            ->where('r.id_empresa', 2)
            ->where('r.semana', '>=', $request->desde)
            ->where('r.semana', '<=', $request->hasta)
            ->groupBy('r.semana')
            ->orderBy('r.semana')
            ->get();

        /* RESUMEN_COSTOS */
        $resumen_costos = DB::table('resumen_costos_semanal')
            ->where('id_empresa', $finca)
            ->where('codigo_semana', '>=', $request->desde)
            ->where('codigo_semana', '<=', $request->hasta)
            ->orderBy('codigo_semana')
            ->get();

        return view('adminlte.gestion.costos.tabla_operaciones.partials.listado', [
            'finca' => $finca,
            'planta' => $planta,
            'listado' => $listado,
            'resumen_semanal' => $resumen_semanal,
            'resumen_semanal_finca' => $resumen_semanal_finca,
            'resumen_costos' => $resumen_costos,
            'columnas' => $request->columnas,
        ]);
    }

    public function exportar_listado_operaciones(Request $request)
    {
        $spread = new Spreadsheet();
        $this->excel_listado_operaciones($spread, $request);
        $spread->getProperties()
            ->setCreator("Benchflow")
            ->setTitle('Tabla_Operaciones')
            ->setSubject('Tabla_Operaciones');

        $fileName = "Tabla_Operaciones.xlsx";
        $writer = new Xlsx($spread);

        //--------------------------- GUARDAR EL EXCEL -----------------------

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        $writer->save('php://output');
    }

    public function excel_listado_operaciones($spread, $request)
    {
        $finca = getFincaActiva();
        $show_columnas = explode(',', $request->columnas);
        $semanas = DB::table('semana')
            ->select('codigo', 'fecha_inicial', 'fecha_final')->distinct()
            ->where('codigo', '>=', $request->desde)
            ->where('codigo', '<=', $request->hasta)
            ->orderBy('codigo')
            ->get();

        $fincas = [$finca];
        if ($finca == 2)
            array_push($fincas, -1);

        $listado = [];
        foreach ($semanas as $sem) {
            /* ------------------ AREA ------------------- */
            $area_a = DB::table('ciclo as c')
                ->join('variedad as v', 'v.id_variedad', '=', 'c.id_variedad')
                ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                ->select(DB::raw('sum(c.area) as area'))
                ->where('v.estado', 1)
                ->where('p.estado', 1)
                ->where('c.estado', '=', 1)
                ->where('c.id_empresa', $finca)
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
                        });
                })
                ->Where(function ($q) use ($sem) {
                    $q->where('p.tipo', 'P')
                        ->orWhere('p.tiene_ciclos', 1);
                })
                ->get()[0]->area;
            $area_b = DB::table('proy_no_perennes as proy')
                ->join('semana as s', 's.id_semana', '=', 'proy.id_semana')
                ->join('variedad as v', 'v.id_variedad', '=', 's.id_variedad')
                ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                ->select(
                    DB::raw('sum(proy.area_produccion) as area_produccion'),
                    DB::raw('sum(proy.area_semana) as area_semana')
                )
                ->where('s.codigo', $sem->codigo)
                ->where('p.estado', 1)
                ->where('v.estado', 1)
                ->where('p.tiene_ciclos', 0)
                ->where('p.tipo', 'N')
                ->where('proy.id_empresa', $finca)
                ->get()[0]->area_produccion;
            $area = $area_a + $area_b;

            /* COMPRA_FLOR */
            $compra_flor = DB::table('bouquetera')
                ->select(
                    DB::raw('sum(precio * (tallos)) as tallos'),
                    DB::raw('sum(precio * (exportada)) as exportada'),
                    DB::raw('sum(tallos) as tallos_bqt'),
                    DB::raw('sum(exportada) as tallos_exportada')
                )
                ->where('fecha', '>=', $sem->fecha_inicial)
                ->where('fecha', '<=', $sem->fecha_final)
                ->whereIn('id_empresa', $fincas)
                ->get()[0];

            array_push($listado, [
                'semana' => $sem,
                'area' => $area,
                'compra_flor' => $compra_flor,
            ]);
        }

        /* RESUMEN_SEMANAL */
        $resumen_semanal = DB::table('resumen_total_semanal_exportcalas')
            ->select(
                'semana',
                DB::raw('sum(tallos_cosechados) as tallos_cosechados'),
                DB::raw('sum(tallos_exportables) as tallos_exportables'),
                DB::raw('sum(bouquetera) as bouquetera'),
                DB::raw('sum(venta) as venta'),
                DB::raw('sum(nacional) as nacionales'),
                DB::raw('sum(bajas) as bajas'),
                DB::raw('sum(tallos_vendidos) as tallos_vendidos'),
                DB::raw('sum(venta_bouquetera) as venta_bouquetera')
            )
            ->where('id_empresa', $finca)
            ->where('semana', '>=', $request->desde)
            ->where('semana', '<=', $request->hasta)
            ->groupBy('semana')
            ->orderBy('semana')
            ->get();

        /* RESUMEN_COSTOS */
        $resumen_costos = DB::table('resumen_costos_semanal')
            ->where('id_empresa', $finca)
            ->where('codigo_semana', '>=', $request->desde)
            ->where('codigo_semana', '<=', $request->hasta)
            ->orderBy('codigo_semana')
            ->get();

        /* ----------------------- CREAR HOJA DE EXCEL ------------------------ */
        $objSheet = $spread->getActiveSheet()->setTitle('Tabla_Operaciones');
        $columnas = getColumnasExcel();
        $row = 1;
        $col = 1;
        setValueToCeldaExcel($objSheet, 'A' . $row, 'Semana');
        if (in_array('r-area', $show_columnas)) {
            setValueToCeldaExcel($objSheet, $columnas[$col] . $row, 'Área m2');
            $col++;
        }
        if (in_array('r-area_promedio', $show_columnas)) {
            setValueToCeldaExcel($objSheet, $columnas[$col] . $row, 'Área prom.');
            $col++;
        }
        if (in_array('r-tallos_cosechados', $show_columnas)) {
            setValueToCeldaExcel($objSheet, $columnas[$col] . $row, 'Tallos Cosechados');
            $col++;
        }
        if (in_array('r-tallos_cosechados_acum', $show_columnas)) {
            setValueToCeldaExcel($objSheet, $columnas[$col] . $row, 'Tallos Cos. Acum.');
            $col++;
        }
        if (in_array('r-tallos_m2', $show_columnas)) {
            setValueToCeldaExcel($objSheet, $columnas[$col] . $row, 'Tallos m2');
            $col++;
        }
        if (in_array('r-tallos_m2_52_sem', $show_columnas)) {
            setValueToCeldaExcel($objSheet, $columnas[$col] . $row, 'Tallos m2 52 Sem.');
            $col++;
        }
        if (in_array('r-tallos_producidos', $show_columnas)) {
            setValueToCeldaExcel($objSheet, $columnas[$col] . $row, 'Tallos Producidos');
            $col++;
        }
        if (in_array('r-tallos_producidos_acum', $show_columnas)) {
            setValueToCeldaExcel($objSheet, $columnas[$col] . $row, 'Tallos Prod. Acum.');
            $col++;
        }
        if (in_array('r-tallos_exportables', $show_columnas)) {
            setValueToCeldaExcel($objSheet, $columnas[$col] . $row, 'Expotables');
            $col++;
        }
        if (in_array('r-tallos_exportables_acum', $show_columnas)) {
            setValueToCeldaExcel($objSheet, $columnas[$col] . $row, 'Exp. Acum.');
            $col++;
        }
        if (in_array('r-porcent_exportables', $show_columnas)) {
            setValueToCeldaExcel($objSheet, $columnas[$col] . $row, '%Exp.');
            $col++;
        }
        if (in_array('r-tallos_bqt', $show_columnas)) {
            setValueToCeldaExcel($objSheet, $columnas[$col] . $row, 'Bqt');
            $col++;
        }
        if (in_array('r-tallos_bqt_acum', $show_columnas)) {
            setValueToCeldaExcel($objSheet, $columnas[$col] . $row, 'Bqt Acum.');
            $col++;
        }
        if (in_array('r-porcent_bqt', $show_columnas)) {
            setValueToCeldaExcel($objSheet, $columnas[$col] . $row, '%Bqt');
            $col++;
        }
        if (in_array('r-venta_total', $show_columnas)) {
            setValueToCeldaExcel($objSheet, $columnas[$col] . $row, 'Venta Total');
            $col++;
        }
        if (in_array('r-venta_total_acum', $show_columnas)) {
            setValueToCeldaExcel($objSheet, $columnas[$col] . $row, 'Venta Total Acum.');
            $col++;
        }
        if (in_array('r-venta_normal', $show_columnas)) {
            setValueToCeldaExcel($objSheet, $columnas[$col] . $row, 'Venta');
            $col++;
        }
        if (in_array('r-venta_normal_acum', $show_columnas)) {
            setValueToCeldaExcel($objSheet, $columnas[$col] . $row, 'Venta Acum.');
            $col++;
        }
        if (in_array('r-porcent_venta_normal', $show_columnas)) {
            setValueToCeldaExcel($objSheet, $columnas[$col] . $row, '%Venta');
            $col++;
        }
        if (in_array('r-venta_bqt', $show_columnas)) {
            setValueToCeldaExcel($objSheet, $columnas[$col] . $row, 'Venta Bqt');
            $col++;
        }
        if (in_array('r-venta_bqt_acum', $show_columnas)) {
            setValueToCeldaExcel($objSheet, $columnas[$col] . $row, 'Venta Bqt Acum.');
            $col++;
        }
        if (in_array('r-porcent_venta_bqt', $show_columnas)) {
            setValueToCeldaExcel($objSheet, $columnas[$col] . $row, '%Venta Bqt');
            $col++;
        }
        if (in_array('r-precio_tallo_total', $show_columnas)) {
            setValueToCeldaExcel($objSheet, $columnas[$col] . $row, 'Precio/Tallo');
            $col++;
        }
        if (in_array('r-precio_tallo_normal', $show_columnas)) {
            setValueToCeldaExcel($objSheet, $columnas[$col] . $row, 'Precio/Tallo Venta');
            $col++;
        }
        if (in_array('r-precio_tallo_bqt', $show_columnas)) {
            setValueToCeldaExcel($objSheet, $columnas[$col] . $row, 'Precio/Tallo Bqt');
            $col++;
        }
        if (in_array('r-venta_m2', $show_columnas)) {
            setValueToCeldaExcel($objSheet, $columnas[$col] . $row, 'Venta/m2');
            $col++;
        }
        if (in_array('r-venta_m2_25_sem', $show_columnas)) {
            setValueToCeldaExcel($objSheet, $columnas[$col] . $row, 'Venta/m2 52 Sem.');
            $col++;
        }
        if (in_array('r-costos_total', $show_columnas)) {
            setValueToCeldaExcel($objSheet, $columnas[$col] . $row, 'Costos Total');
            $col++;
        }
        if (in_array('r-costos_total_acum', $show_columnas)) {
            setValueToCeldaExcel($objSheet, $columnas[$col] . $row, 'Costos Total Acum.');
            $col++;
        }
        if (in_array('r-mo', $show_columnas)) {
            setValueToCeldaExcel($objSheet, $columnas[$col] . $row, 'MO');
            $col++;
        }
        if (in_array('r-mo_acum', $show_columnas)) {
            setValueToCeldaExcel($objSheet, $columnas[$col] . $row, 'MO Acum.');
            $col++;
        }
        if (in_array('r-porcent_mo', $show_columnas)) {
            setValueToCeldaExcel($objSheet, $columnas[$col] . $row, '%MO');
            $col++;
        }
        if (in_array('r-insumos', $show_columnas)) {
            setValueToCeldaExcel($objSheet, $columnas[$col] . $row, 'Insumos');
            $col++;
        }
        if (in_array('r-insumos_acum', $show_columnas)) {
            setValueToCeldaExcel($objSheet, $columnas[$col] . $row, 'Insumos Acum.');
            $col++;
        }
        if (in_array('r-porcent_insumos', $show_columnas)) {
            setValueToCeldaExcel($objSheet, $columnas[$col] . $row, '%Insumos');
            $col++;
        }
        if (in_array('r-fijos', $show_columnas)) {
            setValueToCeldaExcel($objSheet, $columnas[$col] . $row, 'Fijos');
            $col++;
        }
        if (in_array('r-fijos_acum', $show_columnas)) {
            setValueToCeldaExcel($objSheet, $columnas[$col] . $row, 'Fijos Acum.');
            $col++;
        }
        if (in_array('r-porcent_fijos', $show_columnas)) {
            setValueToCeldaExcel($objSheet, $columnas[$col] . $row, '%Fijos');
            $col++;
        }
        if (in_array('r-regalias', $show_columnas)) {
            setValueToCeldaExcel($objSheet, $columnas[$col] . $row, 'Regalías');
            $col++;
        }
        if (in_array('r-regalias_acum', $show_columnas)) {
            setValueToCeldaExcel($objSheet, $columnas[$col] . $row, 'Regalías Acum.');
            $col++;
        }
        if (in_array('r-porcent_regalias', $show_columnas)) {
            setValueToCeldaExcel($objSheet, $columnas[$col] . $row, '%Regalías');
            $col++;
        }
        if (in_array('r-compra_flor', $show_columnas)) {
            setValueToCeldaExcel($objSheet, $columnas[$col] . $row, 'Compra de Flor');
            $col++;
        }
        if (in_array('r-compra_flor_acum', $show_columnas)) {
            setValueToCeldaExcel($objSheet, $columnas[$col] . $row, 'Compra Flor Acum.');
            $col++;
        }
        if (in_array('r-porcent_compra_flor', $show_columnas)) {
            setValueToCeldaExcel($objSheet, $columnas[$col] . $row, '%Compra Flor');
            $col++;
        }
        if (in_array('r-costos_m2', $show_columnas)) {
            setValueToCeldaExcel($objSheet, $columnas[$col] . $row, 'Costos/m2');
            $col++;
        }
        if (in_array('r-costos_m2_52_sem', $show_columnas)) {
            setValueToCeldaExcel($objSheet, $columnas[$col] . $row, 'Costos/m2 52 Sem.');
            $col++;
        }
        if (in_array('r-ebitda', $show_columnas)) {
            setValueToCeldaExcel($objSheet, $columnas[$col] . $row, 'EBITDA');
            $col++;
        }
        if (in_array('r-ebitda_acum', $show_columnas)) {
            setValueToCeldaExcel($objSheet, $columnas[$col] . $row, 'EBITDA Acum.');
            $col++;
        }
        if (in_array('r-ebitda_m2', $show_columnas)) {
            setValueToCeldaExcel($objSheet, $columnas[$col] . $row, 'EBITDA/m2');
            $col++;
        }
        if (in_array('r-ebitda_m2_52_sem', $show_columnas)) {
            setValueToCeldaExcel($objSheet, $columnas[$col] . $row, 'EBITDA/m2 52 Sem.');
        }

        setColorTextToCeldaExcel($objSheet, 'A' . $row . ':' . $columnas[$col] . $row, 'FFFFFF');   // blanco
        setBgToCeldaExcel($objSheet, 'A' . $row . ':' . $columnas[$col] . $row, '00b388');  // verde


        $area_acum = 0;
        $venta_acum = 0;
        $ebitda_acum = 0;
        $cos_acum = 0;
        $producidos_acum = 0;
        $exp_acum = 0;
        $bqt_acum = 0;
        $venta_normal_acum = 0;
        $venta_bqt_acum = 0;
        $costos_acum = 0;
        $mo_acum = 0;
        $insumos_acum = 0;
        $fijos_acum = 0;
        $regalias_acum = 0;
        $compra_flor_acum = 0;

        foreach ($listado as $pos => $item) {
            $ventas = $resumen_semanal[$pos]->venta + $resumen_semanal[$pos]->venta_bouquetera;
            $venta_acum += $ventas;
            $costos_operativos = $resumen_costos[$pos]->mano_obra + $resumen_costos[$pos]->insumos + $resumen_costos[$pos]->fijos +
                $resumen_costos[$pos]->regalias + ($item['compra_flor']->tallos + $item['compra_flor']->exportada);
            $costos_acum += $costos_operativos;
            $ebitda = $ventas - $costos_operativos;
            $ebitda_acum += $ebitda;
            $cos_acum += $resumen_semanal[$pos]->tallos_cosechados;
            $area_acum += $item['area'];
            $prom_area = $area_acum / ($pos + 1);
            $producidos_acum += $resumen_semanal[$pos]->tallos_exportables + $item['compra_flor']->tallos_bqt;
            $exp_acum += $resumen_semanal[$pos]->tallos_exportables;
            $bqt_acum += $item['compra_flor']->tallos_bqt;
            $venta_normal_acum += $resumen_semanal[$pos]->venta;
            $venta_bqt_acum += $resumen_semanal[$pos]->venta_bouquetera;
            $mo_acum += $resumen_costos[$pos]->mano_obra;
            $insumos_acum += $resumen_costos[$pos]->insumos;
            $fijos_acum += $resumen_costos[$pos]->fijos;
            $regalias_acum += $resumen_costos[$pos]->regalias;
            $compra_flor_acum += $item['compra_flor']->tallos + $item['compra_flor']->exportada;
            $costos_m2 = $costos_acum / $prom_area;
            $ebitda_m2 = $ebitda_acum / $prom_area;
            $tallos_m2 = $cos_acum / $prom_area;
            $tallos_m2_52_sem = ($tallos_m2 / ($pos + 1)) * 52;

            $row++;
            $col = 1;
            setValueToCeldaExcel($objSheet, 'A' . $row, $item['semana']->codigo);
            if (in_array('r-area', $show_columnas)) {
                setValueToCeldaExcel($objSheet, $columnas[$col] . $row, round($item['area'], 2));
                $col++;
            }
            if (in_array('r-area_promedio', $show_columnas)) {
                setValueToCeldaExcel($objSheet, $columnas[$col] . $row, number_format($prom_area, 2));
                $col++;
            }
            if (in_array('r-tallos_cosechados', $show_columnas)) {
                setValueToCeldaExcel($objSheet, $columnas[$col] . $row, $resumen_semanal[$pos]->tallos_cosechados);
                $col++;
            }
            if (in_array('r-tallos_cosechados_acum', $show_columnas)) {
                setValueToCeldaExcel($objSheet, $columnas[$col] . $row, $cos_acum);
                $col++;
            }
            if (in_array('r-tallos_m2', $show_columnas)) {
                setValueToCeldaExcel($objSheet, $columnas[$col] . $row, number_format($tallos_m2, 2));
                $col++;
            }
            if (in_array('r-tallos_m2_52_sem', $show_columnas)) {
                setValueToCeldaExcel($objSheet, $columnas[$col] . $row, $resumen_semanal[$pos]->tallos_exportables + $item['compra_flor']->tallos_bqt);
                $col++;
            }
            if (in_array('r-tallos_producidos', $show_columnas)) {
                setValueToCeldaExcel($objSheet, $columnas[$col] . $row, $resumen_semanal[$pos]->tallos_exportables + $item['compra_flor']->tallos_bqt);
                $col++;
            }
            if (in_array('r-tallos_producidos_acum', $show_columnas)) {
                setValueToCeldaExcel($objSheet, $columnas[$col] . $row, $producidos_acum);
                $col++;
            }
            if (in_array('r-tallos_exportables', $show_columnas)) {
                setValueToCeldaExcel($objSheet, $columnas[$col] . $row, $resumen_semanal[$pos]->tallos_exportables);
                $col++;
            }
            if (in_array('r-tallos_exportables_acum', $show_columnas)) {
                setValueToCeldaExcel($objSheet, $columnas[$col] . $row, $exp_acum);
                $col++;
            }
            if (in_array('r-porcent_exportables', $show_columnas)) {
                setValueToCeldaExcel($objSheet, $columnas[$col] . $row, porcentaje($resumen_semanal[$pos]->tallos_exportables, $resumen_semanal[$pos]->tallos_exportables + $item['compra_flor']->tallos_bqt, 1) . '%');
                $col++;
            }
            if (in_array('r-tallos_bqt', $show_columnas)) {
                setValueToCeldaExcel($objSheet, $columnas[$col] . $row, $item['compra_flor']->tallos_bqt);
                $col++;
            }
            if (in_array('r-tallos_bqt_acum', $show_columnas)) {
                setValueToCeldaExcel($objSheet, $columnas[$col] . $row, $bqt_acum);
                $col++;
            }
            if (in_array('r-porcent_bqt', $show_columnas)) {
                setValueToCeldaExcel($objSheet, $columnas[$col] . $row, porcentaje($item['compra_flor']->tallos_bqt, $resumen_semanal[$pos]->tallos_exportables + $item['compra_flor']->tallos_bqt, 1) . '%');
                $col++;
            }
            if (in_array('r-venta_total', $show_columnas)) {
                setValueToCeldaExcel($objSheet, $columnas[$col] . $row, '$' . number_format($resumen_semanal[$pos]->venta + $resumen_semanal[$pos]->venta_bouquetera, 2));
                $col++;
            }
            if (in_array('r-venta_total_acum', $show_columnas)) {
                setValueToCeldaExcel($objSheet, $columnas[$col] . $row, '$' . number_format($venta_acum, 2));
                $col++;
            }
            if (in_array('r-venta_normal', $show_columnas)) {
                setValueToCeldaExcel($objSheet, $columnas[$col] . $row, '$' . number_format($resumen_semanal[$pos]->venta, 2));
                $col++;
            }
            if (in_array('r-venta_normal_acum', $show_columnas)) {
                setValueToCeldaExcel($objSheet, $columnas[$col] . $row, '$' . number_format($venta_normal_acum, 2));
                $col++;
            }
            if (in_array('r-porcent_venta_normal', $show_columnas)) {
                setValueToCeldaExcel($objSheet, $columnas[$col] . $row, porcentaje($resumen_semanal[$pos]->venta, $resumen_semanal[$pos]->venta + $resumen_semanal[$pos]->venta_bouquetera, 1) . '%');
                $col++;
            }
            if (in_array('r-venta_bqt', $show_columnas)) {
                setValueToCeldaExcel($objSheet, $columnas[$col] . $row, '$' . number_format($resumen_semanal[$pos]->venta_bouquetera, 2));
                $col++;
            }
            if (in_array('r-venta_bqt_acum', $show_columnas)) {
                setValueToCeldaExcel($objSheet, $columnas[$col] . $row, '$' . $venta_bqt_acum);
                $col++;
            }
            if (in_array('r-porcent_venta_bqt', $show_columnas)) {
                setValueToCeldaExcel($objSheet, $columnas[$col] . $row, porcentaje($resumen_semanal[$pos]->venta_bouquetera, $resumen_semanal[$pos]->venta + $resumen_semanal[$pos]->venta_bouquetera, 1) . '%');
                $col++;
            }
            if (in_array('r-precio_tallo_total', $show_columnas)) {
                setValueToCeldaExcel($objSheet, $columnas[$col] . $row, '$' . number_format($venta_acum / $producidos_acum, 2));
                $col++;
            }
            if (in_array('r-precio_tallo_normal', $show_columnas)) {
                setValueToCeldaExcel($objSheet, $columnas[$col] . $row, '$' . $resumen_semanal[$pos]->tallos_exportables > 0 ? number_format($resumen_semanal[$pos]->venta / $resumen_semanal[$pos]->tallos_exportables, 2) : 0);
                $col++;
            }
            if (in_array('r-precio_tallo_bqt', $show_columnas)) {
                setValueToCeldaExcel($objSheet, $columnas[$col] . $row, '$' . $item['compra_flor']->tallos_bqt > 0 ? number_format($resumen_semanal[$pos]->venta_bouquetera / $item['compra_flor']->tallos_bqt, 2) : 0);
                $col++;
            }
            if (in_array('r-venta_m2', $show_columnas)) {
                setValueToCeldaExcel($objSheet, $columnas[$col] . $row, '$' . $prom_area > 0 ? number_format($venta_acum / $prom_area, 2) : 0);
                $col++;
            }
            if (in_array('r-venta_m2_25_sem', $show_columnas)) {
                setValueToCeldaExcel($objSheet, $columnas[$col] . $row, '$' . $prom_area > 0 ? number_format((($venta_acum / $prom_area) / ($pos + 1)) * 52, 2) : 0);
                $col++;
            }
            if (in_array('r-costos_total', $show_columnas)) {
                setValueToCeldaExcel($objSheet, $columnas[$col] . $row, '$' . number_format($costos_operativos, 2));
                $col++;
            }
            if (in_array('r-costos_total_acum', $show_columnas)) {
                setValueToCeldaExcel($objSheet, $columnas[$col] . $row, '$' . number_format($costos_acum, 2));
                $col++;
            }
            if (in_array('r-mo', $show_columnas)) {
                setValueToCeldaExcel($objSheet, $columnas[$col] . $row, '$' . number_format($resumen_costos[$pos]->mano_obra, 2));
                $col++;
            }
            if (in_array('r-mo_acum', $show_columnas)) {
                setValueToCeldaExcel($objSheet, $columnas[$col] . $row, '$' . number_format($mo_acum, 2));
                $col++;
            }
            if (in_array('r-porcent_mo', $show_columnas)) {
                setValueToCeldaExcel($objSheet, $columnas[$col] . $row, porcentaje($resumen_costos[$pos]->mano_obra, $costos_operativos, 1) . '%');
                $col++;
            }
            if (in_array('r-insumos', $show_columnas)) {
                setValueToCeldaExcel($objSheet, $columnas[$col] . $row, '$' . number_format($resumen_costos[$pos]->insumos, 2));
                $col++;
            }
            if (in_array('r-insumos_acum', $show_columnas)) {
                setValueToCeldaExcel($objSheet, $columnas[$col] . $row, '$' . number_format($insumos_acum, 2));
                $col++;
            }
            if (in_array('r-porcent_insumos', $show_columnas)) {
                setValueToCeldaExcel($objSheet, $columnas[$col] . $row, porcentaje($resumen_costos[$pos]->insumos, $costos_operativos, 1) . '%');
                $col++;
            }
            if (in_array('r-fijos', $show_columnas)) {
                setValueToCeldaExcel($objSheet, $columnas[$col] . $row, '$' . number_format($resumen_costos[$pos]->fijos, 2));
                $col++;
            }
            if (in_array('r-fijos_acum', $show_columnas)) {
                setValueToCeldaExcel($objSheet, $columnas[$col] . $row, '$' . number_format($fijos_acum, 2));
                $col++;
            }
            if (in_array('r-porcent_fijos', $show_columnas)) {
                setValueToCeldaExcel($objSheet, $columnas[$col] . $row, porcentaje($resumen_costos[$pos]->fijos, $costos_operativos, 1) . '%');
                $col++;
            }
            if (in_array('r-regalias', $show_columnas)) {
                setValueToCeldaExcel($objSheet, $columnas[$col] . $row, '$' . number_format($resumen_costos[$pos]->regalias, 2));
                $col++;
            }
            if (in_array('r-regalias_acum', $show_columnas)) {
                setValueToCeldaExcel($objSheet, $columnas[$col] . $row, '$' . number_format($regalias_acum, 2));
                $col++;
            }
            if (in_array('r-porcent_regalias', $show_columnas)) {
                setValueToCeldaExcel($objSheet, $columnas[$col] . $row, porcentaje($resumen_costos[$pos]->regalias, $costos_operativos, 1) . '%');
                $col++;
            }
            if (in_array('r-compra_flor', $show_columnas)) {
                setValueToCeldaExcel($objSheet, $columnas[$col] . $row, '$' . number_format($item['compra_flor']->tallos + $item['compra_flor']->exportada, 2));
                $col++;
            }
            if (in_array('r-compra_flor_acum', $show_columnas)) {
                setValueToCeldaExcel($objSheet, $columnas[$col] . $row, '$' . number_format($compra_flor_acum, 2));
                $col++;
            }
            if (in_array('r-porcent_compra_flor', $show_columnas)) {
                setValueToCeldaExcel($objSheet, $columnas[$col] . $row, porcentaje($item['compra_flor']->tallos + $item['compra_flor']->exportada, $costos_operativos, 1) . '%');
                $col++;
            }
            if (in_array('r-costos_m2', $show_columnas)) {
                setValueToCeldaExcel($objSheet, $columnas[$col] . $row, '$' . number_format($costos_m2, 2));
                $col++;
            }
            if (in_array('r-costos_m2_52_sem', $show_columnas)) {
                setValueToCeldaExcel($objSheet, $columnas[$col] . $row, '$' . number_format(($costos_m2 / ($pos + 1)) * 52, 2));
                $col++;
            }
            if (in_array('r-ebitda', $show_columnas)) {
                setValueToCeldaExcel($objSheet, $columnas[$col] . $row, '$' . number_format($ebitda, 2));
                $col++;
            }
            if (in_array('r-ebitda_acum', $show_columnas)) {
                setValueToCeldaExcel($objSheet, $columnas[$col] . $row, '$' . number_format($ebitda_acum, 2));
                $col++;
            }
            if (in_array('r-ebitda_m2', $show_columnas)) {
                setValueToCeldaExcel($objSheet, $columnas[$col] . $row, '$' . number_format($ebitda_m2, 2));
                $col++;
            }
            if (in_array('r-ebitda_m2_52_sem', $show_columnas)) {
                setValueToCeldaExcel($objSheet, $columnas[$col] . $row, '$' . number_format(($ebitda_m2 / ($pos + 1)) * 52, 2));
            }

            if ($pos % 2 == 0)
                setBgToCeldaExcel($objSheet, 'A' . $row . ':' . $columnas[$col] . $row, 'e9ecef');  // gris claro
        }
        setBorderToCeldaExcel($objSheet, 'A1:' . $columnas[$col] . $row);
        for ($i = 0; $i <= $col; $i++)
            $objSheet->getColumnDimension($columnas[$i])->setAutoSize(true);
    }
}
