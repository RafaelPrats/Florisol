<?php

namespace yura\Http\Controllers\Indicadores;

use Illuminate\Support\Facades\DB;
use yura\Modelos\ConfiguracionEmpresa;
use yura\Modelos\Indicador;
use yura\Modelos\Pedido;
use yura\Modelos\SuperFinca;
use yura\Modelos\Variedad;
use yura\Modelos\IndicadorVariedad;

class Venta
{
    public static function venta_7_dias_atras($indicador_par)
    {
        $semana_pasada = getSemanaByDate(opDiasFecha('-', 14, hoy()));

        foreach (ConfiguracionEmpresa::All() as $empresa) {
            $finca = $empresa->id_configuracion_empresa;
            /* RESUMEN_SEMANAL */
            $resumen_semanal = DB::table('resumen_total_semanal_exportcalas as r')
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
                ->where('r.semana', $semana_pasada->codigo)
                ->get()[0];
            $ventas_totales = $resumen_semanal->venta + $resumen_semanal->venta_bouquetera;

            $model = getIndicadorByName('D4-' . $finca);  // Ventas (-7 dias)
            $model->valor = $ventas_totales;
            $model->save();
        }
    }

    public static function porcentaje_venta_normal($indicador_par)
    {
        $semana_pasada = getSemanaByDate(opDiasFecha('-', 14, hoy()));

        foreach (ConfiguracionEmpresa::All() as $empresa) {
            $finca = $empresa->id_configuracion_empresa;
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
                ->where('r.semana', $semana_pasada->codigo)
                ->get()[0];
            $ventas_totales = $resumen_semanal->venta + $resumen_semanal->venta_bouquetera;

            $model = getIndicadorByName('D20-' . $finca);
            $model->valor = porcentaje($resumen_semanal->venta, $ventas_totales, 1);
            $model->save();
        }
    }

    public static function porcentaje_venta_bqt($indicador_par)
    {
        $semana_pasada = getSemanaByDate(opDiasFecha('-', 14, hoy()));

        foreach (ConfiguracionEmpresa::All() as $empresa) {
            $finca = $empresa->id_configuracion_empresa;
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
                ->where('r.semana', $semana_pasada->codigo)
                ->get()[0];
            $ventas_totales = $resumen_semanal->venta + $resumen_semanal->venta_bouquetera;

            $model = getIndicadorByName('D21-' . $finca);
            $model->valor = porcentaje($resumen_semanal->venta_bouquetera, $ventas_totales, 1);
            $model->save();
        }
    }

    public static function venta_m2_anno_4_semanas_atras($indicador_par)
    {
        $semana_hasta = getSemanaByDate(opDiasFecha('-', 14, hoy()));
        $semana_desde = getSemanaByDate(opDiasFecha('-', 21, $semana_hasta->fecha_inicial));
        dump('semana_desde: ' . $semana_desde->codigo . '; semana_hasta: ' . $semana_hasta->codigo);
        foreach (ConfiguracionEmpresa::All() as $empresa) {
            $finca = $empresa->id_configuracion_empresa;
            $model = getIndicadorByName('D18-' . $finca);  // Venta $/m2/año (-4 semanas)
            if ($model != '') {
                /* RESUMEN_SEMANAL */
                $resumen_semanal = DB::table('resumen_total_semanal_exportcalas as r')
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
                $ventas_totales = $resumen_semanal->venta + $resumen_semanal->venta_bouquetera;

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

                $valor = ($ventas_totales > 0 && $prom_area > 0) ? round($ventas_totales / $prom_area, 2) : 0;
                dump('finca: ' . $finca . '; venta: ' . $ventas_totales . '; area: ' . $prom_area . '; valor: ' . $valor * 13);
                $model->valor = $valor * 13;
                $model->save();
            }
        }
    }

    public static function dinero_m2_anno_13_semanas_atras($indicador_par)
    {
        $semana_hasta = getSemanaByDate(opDiasFecha('-', 14, hoy()));
        $semana_desde = getSemanaByDate(opDiasFecha('-', 91, $semana_hasta->fecha_inicial));
        dump('semana_desde: ' . $semana_desde->codigo . '; semana_hasta: ' . $semana_hasta->codigo);
        foreach (ConfiguracionEmpresa::All() as $empresa) {
            $finca = $empresa->id_configuracion_empresa;
            $model = getIndicadorByName('D9-' . $finca);
            if ($model != '') {
                /* RESUMEN_SEMANAL */
                $resumen_semanal = DB::table('resumen_total_semanal_exportcalas as r')
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
                $ventas_totales = $resumen_semanal->venta + $resumen_semanal->venta_bouquetera;

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

                $valor = ($ventas_totales > 0 && $prom_area > 0) ? round($ventas_totales / $prom_area, 2) : 0;
                dump('finca: ' . $finca . '; venta: ' . $ventas_totales . '; area: ' . $prom_area);
                $model->valor = $valor * 4;
                $model->save();
            }
        }
    }

    public static function dinero_m2_anual($indicador_par)
    {
        $semana_hasta = getSemanaByDate(opDiasFecha('-', 14, hoy()));
        $semana_desde = substr($semana_hasta->codigo, 0, 2) . '01';
        $semana_desde = getObjSemana($semana_desde);
        dump('semana_desde: ' . $semana_desde->codigo . '; semana_hasta: ' . $semana_hasta->codigo);
        foreach (ConfiguracionEmpresa::All() as $empresa) {
            $finca = $empresa->id_configuracion_empresa;
            $model = getIndicadorByName('D10-' . $finca);
            if ($model != '') {
                /* RESUMEN_SEMANAL */
                $resumen_semanal = DB::table('resumen_total_semanal_exportcalas as r')
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
                $ventas_totales = $resumen_semanal->venta + $resumen_semanal->venta_bouquetera;

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

                $valor = ($ventas_totales > 0 && $prom_area > 0) ? round($ventas_totales / $prom_area, 2) : 0;
                //dump($prom_area, $ventas_totales, $semana_hasta->codigo, $semana_desde->codigo);
                dump('finca: ' . $finca . '; venta: ' . $ventas_totales . '; area: ' . $prom_area);
                $model->valor = $valor;
                $model->save();
            }
        }
    }

    public static function venta_comprada_1_anno($indicador_par)
    {
        $semana_hasta = getSemanaByDate(opDiasFecha('-', 14, hoy()));
        $semana_desde = substr($semana_hasta->codigo, 0, 2) . '01';
        $semana_desde = getObjSemana($semana_desde);
        dump('semana_desde: ' . $semana_desde->codigo . '; semana_hasta: ' . $semana_hasta->codigo);
        foreach (ConfiguracionEmpresa::All() as $empresa) {
            $finca = $empresa->id_configuracion_empresa;
            $model = getIndicadorByName('FC1-' . $finca);
            if ($model != '') {
                $fincas = [$finca];
                $finca_comprada = [];
                if ($finca == 2) {
                    array_push($fincas, -1);
                    array_push($finca_comprada, -1);
                }
                $flor_comprada_exp = DB::table('bouquetera as b')
                    ->join('variedad as v', 'v.id_variedad', '=', 'b.id_variedad')
                    ->select(
                        DB::raw('sum(b.precio * (tallos)) as tallos'),
                        DB::raw('sum(b.precio * (exportada)) as exportada'),
                        DB::raw('sum(b.tallos) as tallos_bqt'),
                        DB::raw('sum(b.exportada) as tallos_exportada')
                    )
                    ->where('b.fecha', '>=', $semana_desde->fecha_inicial)
                    ->where('b.fecha', '<=', $semana_hasta->fecha_final)
                    ->whereIn('b.id_empresa', $fincas)
                    ->get()[0];

                $flor_comprada_bqt = DB::table('bouquetera as b')
                    ->join('variedad as v', 'v.id_variedad', '=', 'b.id_variedad')
                    ->select(
                        DB::raw('sum(b.precio * (tallos)) as tallos'),
                        DB::raw('sum(b.precio * (exportada)) as exportada'),
                        DB::raw('sum(b.tallos) as tallos_bqt'),
                        DB::raw('sum(b.exportada) as tallos_exportada')
                    )
                    ->where('b.fecha', '>=', $semana_desde->fecha_inicial)
                    ->where('b.fecha', '<=', $semana_hasta->fecha_final)
                    ->whereIn('b.id_empresa', $finca_comprada)
                    ->get()[0];
                $comprada_acum = $flor_comprada_exp->tallos_exportada + $flor_comprada_bqt->tallos_bqt;

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

                $precio = getIndicadorByName('D14-' . $finca)->valor;
                $valor = $prom_area > 0 ? round(($comprada_acum * $precio) / $prom_area, 2) : 0;
                dump('finca: ' . $finca . '; comprada_acum: ' . $comprada_acum . '; precio: ' . $precio . '; area: ' . $prom_area);
                $model->valor = $valor;
                $model->save();
            }
        }
    }

    public static function venta_comprada_4_semana($indicador_par)
    {
        $semana_hasta = getSemanaByDate(opDiasFecha('-', 14, hoy()));
        $semana_desde = getSemanaByDate(opDiasFecha('-', 21, $semana_hasta->fecha_inicial));
        dump('semana_desde: ' . $semana_desde->codigo . '; semana_hasta: ' . $semana_hasta->codigo);
        foreach (ConfiguracionEmpresa::All() as $empresa) {
            $finca = $empresa->id_configuracion_empresa;
            $model = getIndicadorByName('FC2-' . $finca);
            if ($model != '') {
                $fincas = [$finca];
                $finca_comprada = [];
                if ($finca == 2) {
                    array_push($fincas, -1);
                    array_push($finca_comprada, -1);
                }
                $flor_comprada_exp = DB::table('bouquetera as b')
                    ->join('variedad as v', 'v.id_variedad', '=', 'b.id_variedad')
                    ->select(
                        DB::raw('sum(b.precio * (tallos)) as tallos'),
                        DB::raw('sum(b.precio * (exportada)) as exportada'),
                        DB::raw('sum(b.tallos) as tallos_bqt'),
                        DB::raw('sum(b.exportada) as tallos_exportada')
                    )
                    ->where('b.fecha', '>=', $semana_desde->fecha_inicial)
                    ->where('b.fecha', '<=', $semana_hasta->fecha_final)
                    ->whereIn('b.id_empresa', $fincas)
                    ->get()[0];

                $flor_comprada_bqt = DB::table('bouquetera as b')
                    ->join('variedad as v', 'v.id_variedad', '=', 'b.id_variedad')
                    ->select(
                        DB::raw('sum(b.precio * (tallos)) as tallos'),
                        DB::raw('sum(b.precio * (exportada)) as exportada'),
                        DB::raw('sum(b.tallos) as tallos_bqt'),
                        DB::raw('sum(b.exportada) as tallos_exportada')
                    )
                    ->where('b.fecha', '>=', $semana_desde->fecha_inicial)
                    ->where('b.fecha', '<=', $semana_hasta->fecha_final)
                    ->whereIn('b.id_empresa', $finca_comprada)
                    ->get()[0];
                $comprada_acum = $flor_comprada_exp->tallos_exportada + $flor_comprada_bqt->tallos_bqt;

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

                $precio = getIndicadorByName('D14-' . $finca)->valor;
                $valor = $prom_area > 0 ? round(($comprada_acum * $precio) / $prom_area, 2) : 0;
                dump('finca: ' . $finca . '; comprada_acum: ' . $comprada_acum . '; precio: ' . $precio . '; area: ' . $prom_area);
                $model->valor = round($valor * 13, 2);
                $model->save();
            }
        }
    }

    public static function venta_comprada_13_semana($indicador_par)
    {
        $semana_hasta = getSemanaByDate(opDiasFecha('-', 14, hoy()));
        $semana_desde = getSemanaByDate(opDiasFecha('-', 91, $semana_hasta->fecha_inicial));
        dump('semana_desde: ' . $semana_desde->codigo . '; semana_hasta: ' . $semana_hasta->codigo);
        foreach (ConfiguracionEmpresa::All() as $empresa) {
            $finca = $empresa->id_configuracion_empresa;
            $model = getIndicadorByName('FC3-' . $finca);
            if ($model != '') {
                $fincas = [$finca];
                $finca_comprada = [];
                if ($finca == 2) {
                    array_push($fincas, -1);
                    array_push($finca_comprada, -1);
                }
                $flor_comprada_exp = DB::table('bouquetera as b')
                    ->join('variedad as v', 'v.id_variedad', '=', 'b.id_variedad')
                    ->select(
                        DB::raw('sum(b.precio * (tallos)) as tallos'),
                        DB::raw('sum(b.precio * (exportada)) as exportada'),
                        DB::raw('sum(b.tallos) as tallos_bqt'),
                        DB::raw('sum(b.exportada) as tallos_exportada')
                    )
                    ->where('b.fecha', '>=', $semana_desde->fecha_inicial)
                    ->where('b.fecha', '<=', $semana_hasta->fecha_final)
                    ->whereIn('b.id_empresa', $fincas)
                    ->get()[0];

                $flor_comprada_bqt = DB::table('bouquetera as b')
                    ->join('variedad as v', 'v.id_variedad', '=', 'b.id_variedad')
                    ->select(
                        DB::raw('sum(b.precio * (tallos)) as tallos'),
                        DB::raw('sum(b.precio * (exportada)) as exportada'),
                        DB::raw('sum(b.tallos) as tallos_bqt'),
                        DB::raw('sum(b.exportada) as tallos_exportada')
                    )
                    ->where('b.fecha', '>=', $semana_desde->fecha_inicial)
                    ->where('b.fecha', '<=', $semana_hasta->fecha_final)
                    ->whereIn('b.id_empresa', $finca_comprada)
                    ->get()[0];
                $comprada_acum = $flor_comprada_exp->tallos_exportada + $flor_comprada_bqt->tallos_bqt;

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

                $precio = getIndicadorByName('D14-' . $finca)->valor;
                $valor = $prom_area > 0 ? round(($comprada_acum * $precio) / $prom_area, 2) : 0;
                dump('finca: ' . $finca . '; comprada_acum: ' . $comprada_acum . '; precio: ' . $precio . '; area: ' . $prom_area);
                $model->valor = round($valor * 4, 2);
                $model->save();
            }
        }
    }

    public static function cajas_equivalentes_vendidas_7_dias_atras($indicador_par)
    {
        $model = getIndicadorByName('D13'); // Cajas equivalentes vendidas (-7 días)
        $pedidos_semanal = Pedido::where('estado', 1)
            ->where('fecha_pedido', '>=', opDiasFecha('-', 7, date('Y-m-d')))
            ->where('fecha_pedido', '<=', opDiasFecha('-', 1, date('Y-m-d')))->get();
        $valor = 0;
        foreach ($pedidos_semanal as $pedido) {
            $valor += $pedido->getCajas();
        }

        $model->valor = $valor;
        $model->save();
    }

    public static function precio_por_tallo_7_dias_atras($indicador_par)
    {
        $semana_hasta = getSemanaByDate(opDiasFecha('-', 14, hoy()));
        $semana_desde = substr($semana_hasta->codigo, 0, 2) . '01';
        $semana_desde = getObjSemana($semana_desde);

        foreach (ConfiguracionEmpresa::All() as $empresa) {
            $finca = $empresa->id_configuracion_empresa;
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
            $venta_acum = $resumen_semanal->venta + $resumen_semanal->venta_bouquetera;
            /* COMPRA_FLOR */
            $fincas = [$finca];
            $finca_comprada = [];
            $otras_fincas = [];
            if ($finca == 2) {
                array_push($fincas, -1);
                array_push($finca_comprada, -1);
                $otras_fincas = [1, 3];
            }
            $compra_flor_finca = DB::table('bouquetera as b')
                ->join('variedad as v', 'v.id_variedad', '=', 'b.id_variedad')
                ->select(
                    DB::raw('sum(b.precio * (tallos)) as tallos'),
                    DB::raw('sum(b.precio * (exportada)) as exportada'),
                    DB::raw('sum(b.tallos) as tallos_bqt'),
                    DB::raw('sum(b.exportada) as tallos_exportada')
                )
                ->where('b.fecha', '>=', $semana_desde->fecha_inicial)
                ->where('b.fecha', '<=', $semana_hasta->fecha_final)
                ->where('b.id_empresa', $finca)
                ->get()[0];
            $producidos_acum = $resumen_semanal->tallos_exportables + $compra_flor_finca->tallos_bqt;
            $exp_acum = $resumen_semanal->tallos_exportables;

            $flor_comprada_exp = DB::table('bouquetera as b')
                ->join('variedad as v', 'v.id_variedad', '=', 'b.id_variedad')
                ->select(
                    DB::raw('sum(b.precio * (tallos)) as tallos'),
                    DB::raw('sum(b.precio * (exportada)) as exportada'),
                    DB::raw('sum(b.tallos) as tallos_bqt'),
                    DB::raw('sum(b.exportada) as tallos_exportada')
                )
                ->where('b.fecha', '>=', $semana_desde->fecha_inicial)
                ->where('b.fecha', '<=', $semana_hasta->fecha_final)
                ->whereIn('b.id_empresa', $fincas)
                ->get()[0];

            $flor_comprada_bqt = DB::table('bouquetera as b')
                ->join('variedad as v', 'v.id_variedad', '=', 'b.id_variedad')
                ->select(
                    DB::raw('sum(b.precio * (tallos)) as tallos'),
                    DB::raw('sum(b.precio * (exportada)) as exportada'),
                    DB::raw('sum(b.tallos) as tallos_bqt'),
                    DB::raw('sum(b.exportada) as tallos_exportada')
                )
                ->where('b.fecha', '>=', $semana_desde->fecha_inicial)
                ->where('b.fecha', '<=', $semana_hasta->fecha_final)
                ->whereIn('b.id_empresa', $finca_comprada)
                ->get()[0];
            $comprada_acum = $flor_comprada_exp->tallos_exportada + $flor_comprada_bqt->tallos_bqt;

            $compra_flor_otras_fincas = DB::table('bouquetera as b')
                ->join('variedad as v', 'v.id_variedad', '=', 'b.id_variedad')
                ->select(
                    DB::raw('sum(b.precio * (tallos)) as tallos'),
                    DB::raw('sum(b.precio * (exportada)) as exportada'),
                    DB::raw('sum(b.tallos) as tallos_bqt'),
                    DB::raw('sum(b.exportada) as tallos_exportada')
                )
                ->where('b.fecha', '>=', $semana_desde->fecha_inicial)
                ->where('b.fecha', '<=', $semana_hasta->fecha_final)
                ->whereIn('b.id_empresa', $otras_fincas)
                ->get()[0];
            $tallos_prod_bqt_otras_fincas_acum = $compra_flor_otras_fincas->tallos_bqt;

            $tallos_acum = $producidos_acum + $comprada_acum;


            if ($finca == 2) {
                $valor = $tallos_acum + $tallos_prod_bqt_otras_fincas_acum > 0 ? number_format($venta_acum / ($tallos_acum + $tallos_prod_bqt_otras_fincas_acum), 2) : 0;
            } else {
                $valor = $comprada_acum + $exp_acum > 0 ? number_format($venta_acum / ($comprada_acum + $exp_acum), 2) : 0;
            }
            $model = getIndicadorByName('D14-' . $finca);  // Precio por tallo AÑO
            $model->valor = $valor;
            $model->save();
        }
    }

    public static function precio_por_tallo_normal_1_semana_atras($indicador_par)
    {
        $semana_hasta = getSemanaByDate(opDiasFecha('-', 14, hoy()));
        $semana_desde = substr($semana_hasta->codigo, 0, 2) . '01';
        $semana_desde = getObjSemana($semana_desde);

        foreach (ConfiguracionEmpresa::All() as $empresa) {
            $finca = $empresa->id_configuracion_empresa;
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
            /* COMPRA_FLOR */
            $fincas = [$finca];
            $finca_comprada = [];
            if ($finca == 2) {
                array_push($fincas, -1);
                array_push($finca_comprada, -1);
            }

            $flor_comprada_exp = DB::table('bouquetera as b')
                ->join('variedad as v', 'v.id_variedad', '=', 'b.id_variedad')
                ->select(
                    DB::raw('sum(b.precio * (tallos)) as tallos'),
                    DB::raw('sum(b.precio * (exportada)) as exportada'),
                    DB::raw('sum(b.tallos) as tallos_bqt'),
                    DB::raw('sum(b.exportada) as tallos_exportada')
                )
                ->where('b.fecha', '>=', $semana_desde->fecha_inicial)
                ->where('b.fecha', '<=', $semana_hasta->fecha_final)
                ->whereIn('b.id_empresa', $fincas)
                ->get()[0];

            $valor = $resumen_semanal->tallos_exportables + $flor_comprada_exp->tallos_exportada > 0 ? round($resumen_semanal->venta / ($resumen_semanal->tallos_exportables + $flor_comprada_exp->tallos_exportada), 2) : 0;
            $model = getIndicadorByName('D23-' . $finca);
            $model->valor = $valor;
            $model->save();
        }
    }

    public static function precio_por_tallo_bqt_1_semana_atras($indicador_par)
    {
        $semana_hasta = getSemanaByDate(opDiasFecha('-', 14, hoy()));
        $semana_desde = substr($semana_hasta->codigo, 0, 2) . '01';
        $semana_desde = getObjSemana($semana_desde);

        foreach (ConfiguracionEmpresa::All() as $empresa) {
            $finca = $empresa->id_configuracion_empresa;
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
            $ventas_bqt = $resumen_semanal->venta_bouquetera;
            /* COMPRA_FLOR */
            $fincas = [$finca];
            $finca_comprada = [];
            $otras_fincas = [];
            if ($finca == 2) {
                array_push($fincas, -1);
                array_push($finca_comprada, -1);
                $otras_fincas = [1, 3];
            }
            $compra_flor_finca = DB::table('bouquetera as b')
                ->join('variedad as v', 'v.id_variedad', '=', 'b.id_variedad')
                ->select(
                    DB::raw('sum(b.precio * (tallos)) as tallos'),
                    DB::raw('sum(b.precio * (exportada)) as exportada'),
                    DB::raw('sum(b.tallos) as tallos_bqt'),
                    DB::raw('sum(b.exportada) as tallos_exportada')
                )
                ->where('b.fecha', '>=', $semana_desde->fecha_inicial)
                ->where('b.fecha', '<=', $semana_hasta->fecha_final)
                ->where('b.id_empresa', $finca)
                ->get()[0];

            $flor_comprada_bqt = DB::table('bouquetera as b')
                ->join('variedad as v', 'v.id_variedad', '=', 'b.id_variedad')
                ->select(
                    DB::raw('sum(b.precio * (tallos)) as tallos'),
                    DB::raw('sum(b.precio * (exportada)) as exportada'),
                    DB::raw('sum(b.tallos) as tallos_bqt'),
                    DB::raw('sum(b.exportada) as tallos_exportada')
                )
                ->where('b.fecha', '>=', $semana_desde->fecha_inicial)
                ->where('b.fecha', '<=', $semana_hasta->fecha_final)
                ->whereIn('b.id_empresa', $finca_comprada)
                ->get()[0];

            $compra_flor_otras_fincas = DB::table('bouquetera as b')
                ->join('variedad as v', 'v.id_variedad', '=', 'b.id_variedad')
                ->select(
                    DB::raw('sum(b.precio * (tallos)) as tallos'),
                    DB::raw('sum(b.precio * (exportada)) as exportada'),
                    DB::raw('sum(b.tallos) as tallos_bqt'),
                    DB::raw('sum(b.exportada) as tallos_exportada')
                )
                ->where('b.fecha', '>=', $semana_desde->fecha_inicial)
                ->where('b.fecha', '<=', $semana_hasta->fecha_final)
                ->whereIn('b.id_empresa', $otras_fincas)
                ->get()[0];

            if ($finca == 2) {
                $bqt_total = $compra_flor_finca->tallos_bqt + $compra_flor_otras_fincas->tallos_bqt + $flor_comprada_bqt->tallos_bqt;
            } else {
                $bqt_total = $compra_flor_finca->tallos_bqt + $flor_comprada_bqt->tallos_bqt;
            }

            $valor = $bqt_total > 0 ? number_format($ventas_bqt / $bqt_total, 2) : 0;
            $model = getIndicadorByName('D24-' . $finca);
            $model->valor = $valor;
            $model->save();
        }
    }

    public static function nacional_1_semana_atras($indicador_par)
    {
        $desde = getSemanaByDate(opDiasFecha('-', 7, date('Y-m-d')));
        foreach (ConfiguracionEmpresa::All() as $empresa) {
            $id_empresa = $empresa->id_configuracion_empresa;
            $model_1 = getIndicadorByName('D15-' . $id_empresa);  // Nacional (-1 semana)
            if ($model_1 != '') {
                $valor = DB::table('resumen_total_semanal_exportcalas')
                    ->select(DB::raw('sum(nacional) as nacional'))
                    ->where('semana', $desde->codigo)
                    ->where('id_empresa', $id_empresa)
                    ->get()[0]->nacional;
                $model_1->valor = $valor > 0 ? $valor : 0;
                $model_1->save();
            }
        }
    }

    public static function bajas_1_semana_atras($indicador_par)
    {
        foreach (ConfiguracionEmpresa::All() as $empresa) {
            $id_empresa = $empresa->id_configuracion_empresa;
            $model_1 = getIndicadorByName('D16-' . $id_empresa);  // Bajas (-1 semana)
            if ($model_1 != '') {
                $last_semana = DB::table('resumen_total_semanal_exportcalas')
                    ->select(DB::raw('max(semana) as last_semana'))
                    ->where('venta', '>', 0)
                    ->where('id_empresa', $id_empresa)
                    ->get()[0]->last_semana;
                $desde = $last_semana > 0 ? $last_semana : getSemanaByDate(opDiasFecha('-', 7, date('Y-m-d')))->codigo;
                $valor = DB::table('resumen_total_semanal_exportcalas')
                    ->select(DB::raw('sum(bajas) as bajas'))
                    ->where('semana', $desde)
                    ->where('id_empresa', $id_empresa)
                    ->get()[0]->bajas;
                $model_1->valor = $valor > 0 ? $valor : 0;
                $model_1->save();
            }
        }
    }

    public static function porcentaje_cumplimiento_1_semana_atras($indicador_par)
    {
        $desde = getSemanaByDate(opDiasFecha('-', 7, date('Y-m-d')));
        foreach (ConfiguracionEmpresa::All() as $empresa) {
            $id_empresa = $empresa->id_configuracion_empresa;
            $model_1 = getIndicadorByName('D17-' . $id_empresa);  // % Cumplimiento (-1 semana)
            if ($model_1 != '') {
                $valor = DB::table('resumen_total_semanal_exportcalas')
                    ->select(DB::raw('sum(tallos_cosechados) as tallos_cosechados'), DB::raw('sum(tallos_proyectados) as tallos_proyectados'))
                    ->where('semana', $desde->codigo)
                    ->where('id_empresa', $id_empresa)
                    ->get()[0];
                $model_1->valor = ($valor != '' && $valor->tallos_cosechados > 0 && $valor->tallos_proyectados > 0) ? round(($valor->tallos_cosechados * 100) / $valor->tallos_proyectados, 2) : 0;
                $model_1->save();
            }
        }
    }

    public static function variedades()
    {
        return Variedad::where('estado', 1)->select('id_variedad')->get();
    }

    /* -------------------- SUPER_FINCA ----------------------- */
    public static function sf_venta_m2_anno_4_semanas_atras($indicador_par)
    {
        $semana_hasta = getSemanaByDate(opDiasFecha('-', 14, hoy()));
        $semana_desde = getSemanaByDate(opDiasFecha('-', 21, $semana_hasta->fecha_inicial));
        dump('semana_desde: ' . $semana_desde->codigo . '; semana_hasta: ' . $semana_hasta->codigo);
        $empresas = SuperFinca::All()->sortBy('nombre');
        foreach ($empresas as $sf) {
            $id_empresa = $sf->id_super_finca;
            $ids_finca = [];
            foreach ($sf->fincas as $f)
                $ids_finca[] = $f->id_configuracion_empresa;
            $model = getIndicadorByName('SF1-' . $id_empresa);  // Venta $/m2/año (-4 semanas) SF
            if ($model != '') {
                /* RESUMEN_SEMANAL */
                $resumen_semanal = DB::table('resumen_total_semanal_exportcalas as r')
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
                    ->whereIn('r.id_empresa', $ids_finca)
                    ->where('r.semana', '>=', $semana_desde->codigo)
                    ->where('r.semana', '<=', $semana_hasta->codigo)
                    ->get()[0];
                $ventas_totales = $resumen_semanal->venta + $resumen_semanal->venta_bouquetera;

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
                        ->whereIn('c.id_empresa', $ids_finca)
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
                        ->whereIn('proy.id_empresa', $ids_finca)
                        ->get()[0]->area_produccion;
                    $area_acum += $area_a + $area_b;
                }
                $prom_area = $area_acum / count($semanas);

                $valor = ($ventas_totales > 0 && $prom_area > 0) ? round($ventas_totales / $prom_area, 2) : 0;
                dump('Sfinca: ' . $sf->nombre . '; venta: ' . $ventas_totales . '; area: ' . $prom_area . '; valor: ' . $valor * 13);
                $model->valor = $valor * 13;
                $model->save();
            }
        }
    }

    public static function sf_venta_m2_anno_13_semanas_atras($indicador_par)
    {
        $semana_hasta = getSemanaByDate(opDiasFecha('-', 14, hoy()));
        $semana_desde = getSemanaByDate(opDiasFecha('-', 91, $semana_hasta->fecha_inicial));
        dump('semana_desde: ' . $semana_desde->codigo . '; semana_hasta: ' . $semana_hasta->codigo);
        $empresas = SuperFinca::All()->sortBy('nombre');
        foreach ($empresas as $sf) {
            $id_empresa = $sf->id_super_finca;
            $ids_finca = [];
            foreach ($sf->fincas as $f)
                $ids_finca[] = $f->id_configuracion_empresa;
            $model = getIndicadorByName('SF2-' . $id_empresa);  // Venta $/m2/año (-4 semanas) SF
            if ($model != '') {
                $resumen_semanal = DB::table('resumen_total_semanal_exportcalas as r')
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
                    ->whereIn('r.id_empresa', $ids_finca)
                    ->where('r.semana', '>=', $semana_desde->codigo)
                    ->where('r.semana', '<=', $semana_hasta->codigo)
                    ->get()[0];
                $ventas_totales = $resumen_semanal->venta + $resumen_semanal->venta_bouquetera;

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
                        ->whereIn('c.id_empresa', $ids_finca)
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
                        ->whereIn('proy.id_empresa', $ids_finca)
                        ->get()[0]->area_produccion;
                    $area_acum += $area_a + $area_b;
                }
                $prom_area = $area_acum / count($semanas);

                $valor = ($ventas_totales > 0 && $prom_area > 0) ? round($ventas_totales / $prom_area, 2) : 0;
                $model->valor = $valor * 4;
                $model->save();
            }
        }
    }

    public static function sf_venta_m2_anno_52_semanas_atras($indicador_par)
    {
        $semana_hasta = getSemanaByDate(opDiasFecha('-', 14, hoy()));
        $semana_desde = substr($semana_hasta->codigo, 0, 2) . '01';
        $semana_desde = getObjSemana($semana_desde);
        dump('semana_desde: ' . $semana_desde->codigo . '; semana_hasta: ' . $semana_hasta->codigo);
        $empresas = SuperFinca::All()->sortBy('nombre');
        foreach ($empresas as $sf) {
            $id_empresa = $sf->id_super_finca;
            $ids_finca = [];
            foreach ($sf->fincas as $f)
                $ids_finca[] = $f->id_configuracion_empresa;
            $model = getIndicadorByName('SF3-' . $id_empresa);  // Venta $/m2/año (-4 semanas) SF
            if ($model != '') {
                /* RESUMEN_SEMANAL */
                $resumen_semanal = DB::table('resumen_total_semanal_exportcalas as r')
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
                    ->whereIn('r.id_empresa', $ids_finca)
                    ->where('r.semana', '>=', $semana_desde->codigo)
                    ->where('r.semana', '<=', $semana_hasta->codigo)
                    ->get()[0];
                $ventas_totales = $resumen_semanal->venta + $resumen_semanal->venta_bouquetera;

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
                        ->whereIn('c.id_empresa', $ids_finca)
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
                        ->whereIn('proy.id_empresa', $ids_finca)
                        ->get()[0]->area_produccion;
                    $area_acum += $area_a + $area_b;
                }
                $prom_area = $area_acum / count($semanas);

                $valor = ($ventas_totales > 0 && $prom_area > 0) ? round($ventas_totales / $prom_area, 2) : 0;
                dump('Sfinca: ' . $sf->nombre . '; venta: ' . $ventas_totales . '; area: ' . $prom_area);
                $model->valor = $valor;
                $model->save();
            }
        }
    }
}
