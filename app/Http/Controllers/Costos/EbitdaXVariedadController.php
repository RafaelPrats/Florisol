<?php

namespace yura\Http\Controllers\Costos;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use yura\Http\Controllers\Controller;
use yura\Modelos\AcumuladosAnno;
use yura\Modelos\Planta;
use yura\Modelos\Submenu;

class EbitdaXVariedadController extends Controller
{
    public function inicio(Request $request)
    {
        $semana = getSemanaByDate(opDiasFecha('-', 7, date('Y-m-d')));
        $plantas = Planta::where('estado', 1)
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.costos.ebitda_x_variedad.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'semana' => $semana,
            'plantas' => $plantas,
        ]);
    }

    public function listado_ebitda_x_variedad(Request $request)
    {
        $finca = getFincaActiva();
        $semana = getObjSemana($request->semana);
        $semana_desde = getObjSemana(substr($semana->codigo, 0, 2) . '01');
        $num_semanas = (difFechas($semana->fecha_inicial, $semana_desde->fecha_inicial)->days / 7) + 1;
        $plantas = Planta::where('estado', 1);
        if ($request->planta != 'T')
            $plantas = $plantas->where('id_planta', $request->planta);
        if ($request->tipo_planta != 'T')
            $plantas = $plantas->where('tipo', $request->tipo_planta);
        $plantas = $plantas->orderBy('nombre')
            ->get();

        $fincas = [$finca];
        $finca_comprada = [];
        $otras_fincas = [];
        if ($finca == 2) {
            array_push($fincas, -1);
            array_push($finca_comprada, -1);
            $otras_fincas = [1, 3];
        }

        $area_finca_n = DB::table('proy_no_perennes as proy')
            ->join('semana as s', 's.id_semana', '=', 'proy.id_semana')
            ->join('variedad as v', 'v.id_variedad', '=', 's.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->select(
                DB::raw('sum(proy.area_produccion) as area_produccion'),
                DB::raw('sum(proy.area_semana) as area_semana')
            )
            ->where('s.codigo', $semana->codigo)
            ->where('p.estado', 1)
            ->where('v.estado', 1)
            ->where('p.tiene_ciclos', 0)
            ->where('p.tipo', 'N')
            ->where('proy.id_empresa', $finca)
            ->get()[0]->area_produccion;

        $area_finca_p = DB::table('ciclo as c')
            ->join('variedad as v', 'v.id_variedad', '=', 'c.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->select(DB::raw('sum(c.area) as area'))
            ->where('v.estado', 1)
            ->where('p.estado', 1)
            ->where('c.estado', '=', 1)
            ->where('c.id_empresa', $finca)
            ->Where(function ($q) use ($semana) {
                $q->where('c.fecha_fin', '>=', $semana->fecha_inicial)
                    ->where('c.fecha_fin', '<=', $semana->fecha_final)
                    ->orWhere(function ($q) use ($semana) {
                        $q->where('c.fecha_inicio', '>=', $semana->fecha_inicial)
                            ->where('c.fecha_inicio', '<=', $semana->fecha_final);
                    })
                    ->orWhere(function ($q) use ($semana) {
                        $q->where('c.fecha_inicio', '<', $semana->fecha_inicial)
                            ->where('c.fecha_fin', '>', $semana->fecha_final);
                    });
            })
            ->Where(function ($q) use ($semana) {
                $q->where('p.tipo', 'P')
                    ->orWhere('p.tiene_ciclos', 1);
            })
            ->get()[0]->area;
        $area_finca = $area_finca_n + $area_finca_p;

        $array_semanas = getSemanasByCodigos($semana_desde->codigo, $semana->codigo);
        $area_finca_acum = 0;
        foreach ($array_semanas as $s) {
            $area_finca_acum_n = DB::table('proy_no_perennes as proy')
                ->join('semana as s', 's.id_semana', '=', 'proy.id_semana')
                ->join('variedad as v', 'v.id_variedad', '=', 's.id_variedad')
                ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                ->select(
                    DB::raw('sum(proy.area_produccion) as area_produccion'),
                    DB::raw('sum(proy.area_semana) as area_semana')
                )
                ->where('s.codigo', $s->codigo)
                ->where('p.estado', 1)
                ->where('v.estado', 1)
                ->where('p.tiene_ciclos', 0)
                ->where('p.tipo', 'N')
                ->where('proy.id_empresa', $finca)
                ->get()[0]->area_produccion;

            $area_finca_acum_p = DB::table('ciclo as c')
                ->join('variedad as v', 'v.id_variedad', '=', 'c.id_variedad')
                ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                ->select(DB::raw('sum(c.area) as area'))
                ->where('v.estado', 1)
                ->where('p.estado', 1)
                ->where('c.estado', '=', 1)
                ->where('c.id_empresa', $finca)
                ->Where(function ($q) use ($s) {
                    $q->where('c.fecha_fin', '>=', $s->fecha_inicial)
                        ->where('c.fecha_fin', '<=', $s->fecha_final)
                        ->orWhere(function ($q) use ($s) {
                            $q->where('c.fecha_inicio', '>=', $s->fecha_inicial)
                                ->where('c.fecha_inicio', '<=', $s->fecha_final);
                        })
                        ->orWhere(function ($q) use ($s) {
                            $q->where('c.fecha_inicio', '<', $s->fecha_inicial)
                                ->where('c.fecha_fin', '>', $s->fecha_final);
                        });
                })
                ->Where(function ($q) use ($s) {
                    $q->where('p.tipo', 'P')
                        ->orWhere('p.tiene_ciclos', 1);
                })
                ->get()[0]->area;
            $area_finca_acum += $area_finca_acum_n + $area_finca_acum_p;
        }
        $prom_area_finca = round($area_finca_acum / $num_semanas, 2);

        $resumen_semanal_finca = DB::table('resumen_total_semanal_exportcalas as r')
            ->select(
                DB::raw('sum(r.tallos_bqt_4_sem) as tallos_bqt_4_sem'),
                DB::raw('sum(r.ventas_bqt_4_sem) as ventas_bqt_4_sem'),
            )
            ->where('r.id_empresa', 2)
            ->where('r.semana', $semana->codigo)
            ->get()[0];
        $precio_tallo_bqt_4_sem = $resumen_semanal_finca->tallos_bqt_4_sem > 0 ? number_format($resumen_semanal_finca->ventas_bqt_4_sem / $resumen_semanal_finca->tallos_bqt_4_sem, 2) : 0;

        /* RESUMEN_COSTOS */
        $resumen_costos_acum = DB::table('resumen_costos_semanal')
            ->select(
                DB::raw('sum(mano_obra) as mano_obra'),
                DB::raw('sum(insumos) as insumos'),
                DB::raw('sum(fijos) as fijos'),
                DB::raw('sum(regalias) as regalias')
            )
            ->where('id_empresa', $finca)
            ->where('codigo_semana', '>=', $semana_desde->codigo)
            ->where('codigo_semana', '<=', $semana->codigo)
            ->get()[0];
        $costos_acum = $resumen_costos_acum->mano_obra + $resumen_costos_acum->insumos + $resumen_costos_acum->fijos + $resumen_costos_acum->regalias;
        $costos_m2 = $prom_area_finca > 0 ? $costos_acum / $prom_area_finca : 0;
        $costos_m2_52_sem = ($costos_m2 / $num_semanas) * 52;

        $listado = [];
        foreach ($plantas as $planta) {
            $acumulados = AcumuladosAnno::All()
                ->where('id_planta', $planta->id_planta)
                ->where('id_empresa', $finca)
                ->where('semana', $semana->codigo)
                ->first();
            /* ------------------ AREA ------------------- */
            $area_a = $area_b = 0;
            if ($planta->tiene_ciclos == 0 && $planta->tipo == 'N') {   // Normales y Sin ciclos
                $area_b = DB::table('proy_no_perennes as proy')
                    ->join('semana as s', 's.id_semana', '=', 'proy.id_semana')
                    ->join('variedad as v', 'v.id_variedad', '=', 's.id_variedad')
                    ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                    ->select(
                        DB::raw('sum(proy.area_produccion) as area_produccion'),
                        DB::raw('sum(proy.area_semana) as area_semana')
                    )
                    ->where('s.codigo', $semana->codigo)
                    ->where('p.estado', 1)
                    ->where('v.estado', 1)
                    ->where('p.tiene_ciclos', 0)
                    ->where('p.tipo', 'N')
                    ->where('proy.id_empresa', $finca)
                    ->where('v.id_planta', $planta->id_planta)
                    ->get()[0]->area_produccion;
            } else {    // Perennes o Con ciclos
                $area_a = DB::table('ciclo as c')
                    ->join('variedad as v', 'v.id_variedad', '=', 'c.id_variedad')
                    ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                    ->select(DB::raw('sum(c.area) as area'))
                    ->where('v.estado', 1)
                    ->where('p.estado', 1)
                    ->where('c.estado', '=', 1)
                    ->where('c.id_empresa', $finca)
                    ->Where(function ($q) use ($semana) {
                        $q->where('c.fecha_fin', '>=', $semana->fecha_inicial)
                            ->where('c.fecha_fin', '<=', $semana->fecha_final)
                            ->orWhere(function ($q) use ($semana) {
                                $q->where('c.fecha_inicio', '>=', $semana->fecha_inicial)
                                    ->where('c.fecha_inicio', '<=', $semana->fecha_final);
                            })
                            ->orWhere(function ($q) use ($semana) {
                                $q->where('c.fecha_inicio', '<', $semana->fecha_inicial)
                                    ->where('c.fecha_fin', '>', $semana->fecha_final);
                            });
                    })
                    ->Where(function ($q) use ($semana) {
                        $q->where('p.tipo', 'P')
                            ->orWhere('p.tiene_ciclos', 1);
                    })
                    ->where('v.id_planta', $planta->id_planta)
                    ->get()[0]->area;
            }
            $area = $area_a + $area_b;
            $prom_area = $acumulados != '' ? $acumulados->area : 0;

            if ($prom_area > 0) {
                /* RESUMEN_SEMANAL */
                $resumen_semanal = DB::table('resumen_total_semanal_exportcalas as r')
                    ->join('variedad as v', 'v.id_variedad', '=', 'r.id_variedad')
                    ->select(
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
                    ->where('r.semana', $semana->codigo)
                    ->where('v.id_planta', $planta->id_planta)
                    ->get()[0];
                $tallos_m2 = $prom_area > 0 ? $acumulados->cosechados / $prom_area : 0;
                $tallos_m2_52_sem = ($tallos_m2 / $num_semanas) * 52;

                $compra_flor_finca = DB::table('bouquetera as b')
                    ->join('variedad as v', 'v.id_variedad', '=', 'b.id_variedad')
                    ->select(
                        DB::raw('sum(b.precio * (tallos)) as tallos'),
                        DB::raw('sum(b.precio * (exportada)) as exportada'),
                        DB::raw('sum(b.tallos) as tallos_bqt'),
                        DB::raw('sum(b.exportada) as tallos_exportada')
                    )
                    ->where('b.fecha', '>=', $semana->fecha_inicial)
                    ->where('b.fecha', '<=', $semana->fecha_final)
                    ->where('b.id_empresa', $finca)
                    ->where('v.id_planta', $planta->id_planta)
                    ->get()[0];
                $producidos_acum = $acumulados->prod_exp + $acumulados->prod_bqt;
                $exp_acum = $acumulados->prod_exp;
                $bqt_acum = $acumulados->prod_bqt;
                $bqt_total = $compra_flor_finca->tallos_bqt;
                $bqt_total_acum = $acumulados->bqt_total;

                $compra_flor_otras_fincas = DB::table('bouquetera as b')
                    ->join('variedad as v', 'v.id_variedad', '=', 'b.id_variedad')
                    ->select(
                        DB::raw('sum(b.precio * (tallos)) as tallos'),
                        DB::raw('sum(b.precio * (exportada)) as exportada'),
                        DB::raw('sum(b.tallos) as tallos_bqt'),
                        DB::raw('sum(b.exportada) as tallos_exportada')
                    )
                    ->where('b.fecha', '>=', $semana->fecha_inicial)
                    ->where('b.fecha', '<=', $semana->fecha_final)
                    ->whereIn('b.id_empresa', $otras_fincas)
                    ->where('v.id_planta', $planta->id_planta)
                    ->get()[0];
                $tallos_prod_bqt_otras_fincas_acum = $acumulados->tallos_prod_bqt_otras_fincas;

                $flor_comprada_exp = DB::table('bouquetera as b')
                    ->join('variedad as v', 'v.id_variedad', '=', 'b.id_variedad')
                    ->select(
                        DB::raw('sum(b.precio * (tallos)) as tallos'),
                        DB::raw('sum(b.precio * (exportada)) as exportada'),
                        DB::raw('sum(b.tallos) as tallos_bqt'),
                        DB::raw('sum(b.exportada) as tallos_exportada')
                    )
                    ->where('b.fecha', '>=', $semana->fecha_inicial)
                    ->where('b.fecha', '<=', $semana->fecha_final)
                    ->whereIn('b.id_empresa', $fincas)
                    ->where('v.id_planta', $planta->id_planta)
                    ->get()[0];

                $flor_comprada_bqt = DB::table('bouquetera as b')
                    ->join('variedad as v', 'v.id_variedad', '=', 'b.id_variedad')
                    ->select(
                        DB::raw('sum(b.precio * (tallos)) as tallos'),
                        DB::raw('sum(b.precio * (exportada)) as exportada'),
                        DB::raw('sum(b.tallos) as tallos_bqt'),
                        DB::raw('sum(b.exportada) as tallos_exportada')
                    )
                    ->where('b.fecha', '>=', $semana->fecha_inicial)
                    ->where('b.fecha', '<=', $semana->fecha_final)
                    ->whereIn('b.id_empresa', $finca_comprada)
                    ->where('v.id_planta', $planta->id_planta)
                    ->get()[0];

                $comprada_acum = $acumulados->comprada_exp + $acumulados->comprada_bqt;
                $comprada_exp_acum = $acumulados->comprada_exp;
                $comprada_bqt_acum = $acumulados->comprada_bqt;

                $ventas_bqt = $bqt_total * $precio_tallo_bqt_4_sem;
                $venta_bqt_acum = $acumulados->venta_bqt;
                $ventas = $resumen_semanal->venta + $ventas_bqt;
                $venta_acum = $acumulados->venta_total;
                $venta_normal_acum = $acumulados->venta_total;
                $precio_tallo_bqt = $bqt_total > 0 ? round($ventas_bqt / $bqt_total, 2) : 0;

                if ($finca == 2) {
                    $tallos_acum = $producidos_acum + $comprada_acum;
                    $precio_total_anno = $tallos_acum + $tallos_prod_bqt_otras_fincas_acum > 0 ? round($venta_acum / ($tallos_acum + $tallos_prod_bqt_otras_fincas_acum), 2) : 0;
                } else {
                    $precio_total_anno = $comprada_acum + $exp_acum > 0 ? round($venta_acum / ($comprada_acum + $exp_acum), 2) : 0;
                }
                $venta_m2 = $prom_area > 0 ? $venta_acum / $prom_area : 0;
                $venta_m2_52_sem = ($venta_m2 / $num_semanas) * 52;

                array_push($listado, [
                    'planta' => $planta,
                    'area' => $area,
                    'prom_area' => $prom_area,
                    'resumen_semanal' => $resumen_semanal,
                    'cos_acum' => $acumulados->cosechados,
                    'tallos_m2' => $tallos_m2,
                    'tallos_m2_52_sem' => $tallos_m2_52_sem,
                    'compra_flor_finca' => $compra_flor_finca,
                    'producidos_acum' => $producidos_acum,
                    'exp_acum' => $exp_acum,
                    'bqt_acum' => $bqt_acum,
                    'bqt_total' => $bqt_total,
                    'bqt_total_acum' => $bqt_total_acum,
                    'compra_flor_otras_fincas' => $compra_flor_otras_fincas,
                    'tallos_prod_bqt_otras_fincas_acum' => $tallos_prod_bqt_otras_fincas_acum,
                    'flor_comprada_exp' => $flor_comprada_exp,
                    'flor_comprada_bqt' => $flor_comprada_bqt,
                    'comprada_acum' => $comprada_acum,
                    'comprada_exp_acum' => $comprada_exp_acum,
                    'comprada_bqt_acum' => $comprada_bqt_acum,
                    'ventas_bqt' => $ventas_bqt,
                    'venta_bqt_acum' => $venta_bqt_acum,
                    'ventas' => $ventas,
                    'venta_acum' => $venta_acum,
                    'venta_normal_acum' => $venta_normal_acum,
                    'precio_total_anno' => $precio_total_anno,
                    'precio_tallo_bqt' => $precio_tallo_bqt,
                    'venta_m2' => $venta_m2,
                    'venta_m2_52_sem' => $venta_m2_52_sem,
                ]);
            }
        }
        return view('adminlte.gestion.costos.ebitda_x_variedad.partials.listado', [
            'finca' => $finca,
            'listado' => $listado,
            'area_finca' => $area_finca,
            'prom_area_finca' => $prom_area_finca,
            'resumen_semanal_finca' => $resumen_semanal_finca,
            'precio_tallo_bqt_4_sem' => $precio_tallo_bqt_4_sem,
            'costos_m2' => $costos_m2,
            'costos_m2_52_sem' => $costos_m2_52_sem,
        ]);
    }
}
