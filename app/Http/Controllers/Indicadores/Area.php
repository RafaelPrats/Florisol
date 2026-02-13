<?php

namespace yura\Http\Controllers\Indicadores;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use yura\Modelos\Ciclo;
use yura\Modelos\ConfiguracionEmpresa;
use yura\Modelos\Cosecha;
use yura\Modelos\IndicadorVariedad;
use yura\Modelos\ResumenSemanaCosecha;
use yura\Modelos\Variedad;

class Area
{
    public static function area_produccion_4_semanas_atras($indicador_par)
    {
        $semana_hasta = getSemanaByDate(opDiasFecha('-', 14, hoy()));
        $semana_desde = substr($semana_hasta->codigo, 0, 2) . '01';
        $semana_desde = getObjSemana($semana_desde);

        foreach (ConfiguracionEmpresa::All() as $empresa) {
            $finca = $empresa->id_configuracion_empresa;
            $model = getIndicadorByName('D7-' . $finca);
            if ($model != '') {
                $semanas = DB::table('semana')
                    ->select('codigo', 'fecha_inicial', 'fecha_final')->distinct()
                    ->where('codigo', '>=', $semana_desde->codigo)
                    ->where('codigo', '<=', $semana_hasta->codigo)
                    ->orderBy('codigo')
                    ->get();
                $area_acum = 0;
                foreach ($semanas as $sem) {
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
                        ->Where(function ($q) {
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

                    $area_acum += $area_a + $area_b;
                }
                $prom_area = $area_acum / count($semanas);

                $model->valor = $prom_area > 0 ? round($prom_area, 2) : 0;
                $model->save();
            }
        }
    }

    public static function ciclo_4_semanas_atras($indicador_par)
    {
        $desde_sem = getSemanaByDate(opDiasFecha('-', 28, date('Y-m-d')));
        $hasta_sem = getSemanaByDate(opDiasFecha('-', 7, date('Y-m-d')));

        foreach (ConfiguracionEmpresa::All() as $empresa) {
            $id_empresa = $empresa->id_configuracion_empresa;
            $model = getIndicadorByName('DA1-' . $id_empresa);  // Tallos/m2 (-4 meses)
            if ($model != '') {
                $ciclos = Ciclo::where('activo', 0)
                    ->where('fecha_fin', '>=', $desde_sem->fecha_inicial)
                    ->where('fecha_fin', '<=', $hasta_sem->fecha_final)
                    ->where('estado', 1)
                    ->where('id_empresa', $id_empresa)
                    ->get();
                $dias = 0;
                foreach ($ciclos as $c) {
                    $dias += difFechas($c->fecha_fin, $c->fecha_inicio)->days;
                }
                $model->valor = count($ciclos) > 0 ? round($dias / count($ciclos), 2) : 0;
                $model->save();
            }
        }
    }

    public static function ramos_m2_anno_4_semanas_atras($indicador_par)
    {
        $desde = opDiasFecha('-', 28, date('Y-m-d'));
        $hasta = opDiasFecha('-', 7, date('Y-m-d'));

        $model = getIndicadorByName('D8');  // Calibre (-7 días)
        if ($model != '') {
            $semanas_4 = DB::table('semana as s')
                ->select('s.codigo as semana')->distinct()
                ->Where(function ($q) use ($desde, $hasta) {
                    $q->where('s.fecha_inicial', '>=', $desde)
                        ->where('s.fecha_inicial', '<=', $hasta);
                })
                ->orWhere(function ($q) use ($desde, $hasta) {
                    $q->where('s.fecha_final', '>=', $desde)
                        ->Where('s.fecha_final', '<=', $hasta);
                })
                ->orderBy('codigo')
                ->get();

            $data_ciclos = getCiclosCerradosByRango($semanas_4[0]->semana, $semanas_4[3]->semana, 'T');
            $ciclo = $data_ciclos['ciclo'];
            $area_cerrada = $data_ciclos['area_cerrada'];
            $tallos_ciclo = $data_ciclos['tallos_cosechados'];

            $data_cosecha = getCosechaByRango($semanas_4[0]->semana, $semanas_4[3]->semana, 'T');
            $calibre_ciclo = $data_cosecha['calibre'];
            $ramos_ciclo = $calibre_ciclo > 0 ? round($tallos_ciclo / $calibre_ciclo, 2) : 0;

            $ciclo_ano = $area_cerrada > 0 ? round(365 / $ciclo, 2) : 0;

            $mensual = [
                'ciclo_ano' => $ciclo_ano,
                'ciclo' => $ciclo,
                'area_cerrada' => $area_cerrada,
                'tallos_m2' => $area_cerrada > 0 ? round($tallos_ciclo / $area_cerrada, 2) : 0,
                'ramos_m2' => $area_cerrada > 0 ? round($ramos_ciclo / $area_cerrada, 2) : 0,
                'ramos_m2_anno' => $area_cerrada > 0 ? round($ciclo_ano * round($ramos_ciclo / $area_cerrada, 2), 2) : 0,
            ];

            $model->valor = $mensual['ramos_m2_anno'];
            $model->save();
        }
    }

    public static function tallos_m2_4_semanas_atras($indicador_par)
    {
        $semana_hasta = getSemanaByDate(opDiasFecha('-', 14, hoy()));
        $semana_desde = substr($semana_hasta->codigo, 0, 2) . '01';
        $semana_desde = getObjSemana($semana_desde);

        foreach (ConfiguracionEmpresa::All() as $empresa) {
            $finca = $empresa->id_configuracion_empresa;
            $model = getIndicadorByName('D12-' . $finca);
            if ($model != '') {
                $semanas = DB::table('semana')
                    ->select('codigo', 'fecha_inicial', 'fecha_final')->distinct()
                    ->where('codigo', '>=', $semana_desde->codigo)
                    ->where('codigo', '<=', $semana_hasta->codigo)
                    ->orderBy('codigo')
                    ->get();
                $area_acum = 0;
                foreach ($semanas as $sem) {
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
                        ->Where(function ($q) {
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

                    $area_acum += $area_a + $area_b;
                }
                $prom_area = $area_acum / count($semanas);

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
                    ->where('r.semana', '>=', $semana_desde->codigo)
                    ->where('r.semana', '<=', $semana_hasta->codigo)
                    ->get()[0];
                $tallos_m2 = $prom_area > 0 ? $resumen_semanal->tallos_cosechados / $prom_area : 0;
                $tallos_m2_52_sem = ($tallos_m2 / count($semanas)) * 52;

                $model->valor = $tallos_m2_52_sem > 0 ? round($tallos_m2_52_sem, 2) : 0;
                $model->save();
            }
        }
    }

    public static function ramos_m2_4_semanas_atras($indicador_par)
    {
        $model = getIndicadorByName('DA2');  // Ramos/m2 (-4 meses)
        if ($model != '') {
            $desde = opDiasFecha('-', 28, date('Y-m-d'));
            $hasta = opDiasFecha('-', 7, date('Y-m-d'));

            $fechas = DB::table('semana as s')
                ->select('s.codigo as semana')->distinct()
                ->Where(function ($q) use ($desde, $hasta) {
                    $q->where('s.fecha_inicial', '>=', $desde)
                        ->where('s.fecha_inicial', '<=', $hasta);
                })
                ->orWhere(function ($q) use ($desde, $hasta) {
                    $q->where('s.fecha_final', '>=', $desde)
                        ->Where('s.fecha_final', '<=', $hasta);
                })
                ->orderBy('codigo')
                ->get();

            $data_ciclos = getCiclosCerradosByRango($fechas[0]->semana, $fechas[3]->semana, 'T');
            $area_cerrada = $data_ciclos['area_cerrada'];
            $tallos = $data_ciclos['tallos_cosechados'];

            $data_cosecha = getCosechaByRango($fechas[0]->semana, $fechas[3]->semana, 'T');
            $calibre = $data_cosecha['calibre'];
            $calibre > 0 ? $ramos = round($tallos / $calibre, 2) : $ramos = 0;

            $model->valor = $area_cerrada > 0 ? round($ramos / $area_cerrada, 2) : 0;
            $model->save();
        }
    }
}
