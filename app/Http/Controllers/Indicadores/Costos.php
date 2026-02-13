<?php

/**
 * Created by PhpStorm.
 * User: Rafael Prats
 * Date: 2020-01-09
 * Time: 12:19
 */

namespace yura\Http\Controllers\Indicadores;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use yura\Modelos\ConfiguracionEmpresa;
use yura\Modelos\Cosecha;
use yura\Modelos\Area;
use yura\Modelos\IndicadorVariedad;
use yura\Modelos\ResumenCostosSemanal;
use yura\Modelos\Semana;
use yura\Modelos\SuperFinca;
use yura\Modelos\Variedad;

class Costos
{
    public static function mano_de_obra_1_semana_atras($indicador_par)
    {
        foreach (ConfiguracionEmpresa::All() as $emp) {
            $finca = $emp->id_configuracion_empresa;
            $model = getIndicadorByName('C1-' . $finca);  // Costos Mano de Obra (-1 semana)
            if ($model != '') {
                $last_semana = DB::table('costos_semana_mano_obra')
                    ->select(DB::raw('max(codigo_semana) as last_semana'))
                    ->where('valor', '>', 0)
                    ->where('id_empresa', $finca)
                    ->get()[0]->last_semana;
                if ($last_semana != '') {
                    $valor = DB::table('costos_semana_mano_obra')
                        ->select(DB::raw('sum(valor) as cant'))
                        ->where('codigo_semana', $last_semana)
                        ->where('id_empresa', $finca)
                        ->get()[0]->cant;

                    $model->valor = $last_semana . ':' . round($valor, 2);
                    $model->save();
                }
            }
        }
    }

    public static function costos_x_planta_4_semanas_atras($indicador_par)
    {
        foreach (ConfiguracionEmpresa::All() as $empresa) {
            $finca = $empresa->id_configuracion_empresa;
            $model = getIndicadorByName('C12-' . $finca);  // Costo x Planta (-4 semanas)
            if ($model != '') {
                $last_semana = $last_semana = DB::table('costos_semana')
                    ->select(DB::raw('max(codigo_semana) as last_semana'))
                    ->where('valor', '>', 0)
                    ->where('id_empresa', $finca)
                    ->get()[0]->last_semana;
                $last_semana = getObjSemana($last_semana);
                if ($last_semana != '') {
                    $sem_desde = getSemanaByDate(opDiasFecha('-', 21, $last_semana->fecha_inicial));   // 4 semana atras
                    $sem_hasta = $last_semana;

                    $areas = Area::where('estado', 1)
                        ->where('nombre', 'like', '%PROPAGACION%')
                        ->where('id_empresa', $finca)
                        ->get();
                    $ids_areas = [];
                    foreach ($areas as $a)
                        array_push($ids_areas, $a->id_area);
                    $insumos = DB::table('costos_semana as c')
                        ->select(DB::raw('sum(c.valor) as cant'))
                        ->join('actividad_producto as ac', 'ac.id_actividad_producto', '=', 'c.id_actividad_producto')
                        ->join('actividad as a', 'a.id_actividad', '=', 'ac.id_actividad')
                        ->whereIn('a.id_area', $ids_areas)
                        ->where('c.codigo_semana', '>=', $sem_desde->codigo)
                        ->where('c.codigo_semana', '<=', $sem_hasta->codigo)
                        ->get()[0]->cant;
                    $mano_obra = DB::table('costos_semana_mano_obra as c')
                        ->select(DB::raw('sum(c.valor) as cant'))
                        ->join('actividad_mano_obra as am', 'am.id_actividad_mano_obra', '=', 'c.id_actividad_mano_obra')
                        ->join('actividad as a', 'a.id_actividad', '=', 'am.id_actividad')
                        ->whereIn('a.id_area', $ids_areas)
                        ->where('c.codigo_semana', '>=', $sem_desde->codigo)
                        ->where('c.codigo_semana', '<=', $sem_hasta->codigo)
                        ->get()[0]->cant;
                    $otros = DB::table('otros_gastos as o')
                        ->select(DB::raw('sum(o.gip + o.ga) as cant'))
                        ->whereIn('o.id_area', $ids_areas)
                        ->where('o.codigo_semana', '>=', $sem_desde->codigo)
                        ->where('o.codigo_semana', '<=', $sem_hasta->codigo)
                        ->get()[0]->cant;

                    $costos_propagacion = $insumos + $mano_obra + $otros;

                    $requerimientos = DB::table('propag_disponibilidad')
                        ->select(DB::raw('sum(requerimientos) as cant'))
                        ->where('semana', '>=', $sem_desde->codigo)
                        ->where('semana', '<=', $sem_hasta->codigo)
                        ->where('id_empresa', $finca)
                        ->get()[0]->cant;
                    $requerimientos = $requerimientos > 0 ? $requerimientos : 0;

                    $valor = $requerimientos > 0 ? $costos_propagacion / $requerimientos : 0;

                    $model->valor = $valor;
                    $model->save();
                }
            }
        }
    }

    public static function costos_insumos_1_semana_atras($indicador_par)
    {
        foreach (ConfiguracionEmpresa::All() as $emp) {
            $finca = $emp->id_configuracion_empresa;
            $model = getIndicadorByName('C2-' . $finca);  // Costos Insumos (-1 semana)
            if ($model != '') {
                $last_semana = DB::table('costos_semana')
                    ->select(DB::raw('max(codigo_semana) as last_semana'))
                    ->where('valor', '>', 0)
                    ->where('id_empresa', $finca)
                    ->get()[0]->last_semana;
                if ($last_semana != '') {
                    $valor = DB::table('costos_semana')
                        ->select(DB::raw('sum(valor) as cant'))
                        ->where('codigo_semana', $last_semana)
                        ->where('id_empresa', $finca)
                        ->get()[0]->cant;

                    $model->valor = $last_semana . ':' . round($valor, 2);
                    $model->save();
                }
            }
        }
    }

    public static function costos_fijos_1_semana_atras($indicador_par)
    {
        foreach (ConfiguracionEmpresa::All() as $emp) {
            $finca = $emp->id_configuracion_empresa;
            $model = getIndicadorByName('C7-' . $finca);  // Costos Fijos (-1 semana)
            if ($model != '') {
                $last_semana = DB::table('costos_semana')
                    ->select(DB::raw('max(codigo_semana) as last_semana'))
                    ->where('valor', '>', 0)
                    ->where('id_empresa', $finca)
                    ->get()[0]->last_semana;
                $otros_gastos = DB::table('otros_gastos')
                    ->select(DB::raw('sum(gip) as cant_gip'), DB::raw('sum(ga) as cant_ga'))
                    ->where('codigo_semana', $last_semana)
                    ->where('id_empresa', $finca)
                    ->get()[0];

                $valor = $otros_gastos->cant_gip + $otros_gastos->cant_ga;

                $model->valor = $last_semana . ':' . round($valor, 2);
                $model->save();
            }
        }
    }

    public static function costos_regalias_1_semana_atras($indicador_par)
    {
        foreach (ConfiguracionEmpresa::All() as $emp) {
            $finca = $emp->id_configuracion_empresa;
            $model = getIndicadorByName('C8-' . $finca);  // Costos Regalias (-1 semana)
            if ($model != '') {
                $last_semana = DB::table('costos_semana')
                    ->select(DB::raw('max(codigo_semana) as last_semana'))
                    ->where('valor', '>', 0)
                    ->where('id_empresa', $finca)
                    ->get()[0]->last_semana;
                $regalias = DB::table('otros_gastos')
                    ->select(DB::raw('sum(regalias) as regalias'))
                    ->where('codigo_semana', $last_semana)
                    ->where('id_empresa', $finca)
                    ->get()[0]->regalias;
                $valor = $regalias;

                dump('finca: ' . $finca . ' - sem: ' . $last_semana . ' - valor: ' . $valor);
                $model->valor = $last_semana . ':' . round($valor, 2);
                $model->save();
            }
        }
    }

    public static function costos_propagacion_tallo_4_semana_atras($indicador_par)
    {
        foreach (ConfiguracionEmpresa::All() as $emp) {
            $finca = $emp->id_configuracion_empresa;
            $model = getIndicadorByName('C3-' . $finca);  // Costos Propagacion x tallo (-4 semanas)
            if ($model != '') {
                $last_semana = $last_semana = DB::table('costos_semana')
                    ->select(DB::raw('max(codigo_semana) as last_semana'))
                    ->where('valor', '>', 0)
                    ->where('id_empresa', $finca)
                    ->get()[0]->last_semana;
                $last_semana = getObjSemana($last_semana);
                if ($last_semana != '') {
                    $sem_desde = getSemanaByDate(opDiasFecha('-', 21, $last_semana->fecha_inicial));
                    $sem_hasta = $last_semana;

                    $areas = Area::where('estado', 1)
                        ->where('nombre', 'like', '%PROPAGACION%')
                        ->where('id_empresa', $finca)
                        ->get();
                    $ids_areas = [];
                    foreach ($areas as $a)
                        array_push($ids_areas, $a->id_area);
                    $insumos = DB::table('costos_semana as c')
                        ->select(DB::raw('sum(c.valor) as cant'))
                        ->join('actividad_producto as ac', 'ac.id_actividad_producto', '=', 'c.id_actividad_producto')
                        ->join('actividad as a', 'a.id_actividad', '=', 'ac.id_actividad')
                        ->whereIn('a.id_area', $ids_areas)
                        ->where('c.codigo_semana', '>=', $sem_desde->codigo)
                        ->where('c.codigo_semana', '<=', $sem_hasta->codigo)
                        ->get()[0]->cant;
                    $mano_obra = DB::table('costos_semana_mano_obra as c')
                        ->select(DB::raw('sum(c.valor) as cant'))
                        ->join('actividad_mano_obra as am', 'am.id_actividad_mano_obra', '=', 'c.id_actividad_mano_obra')
                        ->join('actividad as a', 'a.id_actividad', '=', 'am.id_actividad')
                        ->whereIn('a.id_area', $ids_areas)
                        ->where('c.codigo_semana', '>=', $sem_desde->codigo)
                        ->where('c.codigo_semana', '<=', $sem_hasta->codigo)
                        ->get()[0]->cant;
                    $otros = DB::table('otros_gastos as o')
                        ->select(DB::raw('sum(o.gip + o.ga) as cant'))
                        ->whereIn('o.id_area', $ids_areas)
                        ->where('o.codigo_semana', '>=', $sem_desde->codigo)
                        ->where('o.codigo_semana', '<=', $sem_hasta->codigo)
                        ->get()[0]->cant;

                    $costos_total = $insumos + $mano_obra + $otros;

                    $tallos = DB::table('resumen_propagacion')
                        ->select(DB::raw('sum(esquejes_cosechados) as cant'))
                        ->where('id_empresa', $finca)
                        ->where('semana', '>=', $sem_desde->codigo)
                        ->where('semana', '<=', $sem_hasta->codigo)
                        ->get()[0]->cant;
                    $tallos = $tallos > 0 ? $tallos : 0;
                    dump('$costos_total : ' . $costos_total . ' = $insumos + ' . $insumos . ' $mano_obra + ' . $mano_obra . ' $otros + ' . $otros . ' $tallos / ' . $tallos);
                    $model->valor = $tallos > 0 ? round(($costos_total / $tallos) * 100, 2) : 0;
                    $model->save();
                }
            }
        }
    }

    public static function costos_cosecha_tallo_4_semana_atras($indicador_par)
    {
        foreach (ConfiguracionEmpresa::All() as $emp) {
            $finca = $emp->id_configuracion_empresa;
            $model = getIndicadorByName('C4-' . $finca);  // Costos Cultivo x Tallo (-4 semanas)
            if ($model != '') {
                $last_semana = $last_semana = DB::table('costos_semana')
                    ->select(DB::raw('max(codigo_semana) as last_semana'))
                    ->where('valor', '>', 0)
                    ->where('id_empresa', $finca)
                    ->get()[0]->last_semana;
                $last_semana = getObjSemana($last_semana);
                if ($last_semana != '') {
                    $sem_desde = getSemanaByDate(opDiasFecha('-', 21, $last_semana->fecha_inicial));
                    $sem_hasta = $last_semana;

                    $areas = Area::where('estado', 1)
                        ->where('nombre', 'like', '%CULTIVO%')
                        ->where('id_empresa', $finca)
                        ->get();
                    $ids_areas = [];
                    foreach ($areas as $a)
                        array_push($ids_areas, $a->id_area);
                    $insumos = DB::table('costos_semana as c')
                        ->select(DB::raw('sum(c.valor) as cant'))
                        ->join('actividad_producto as ac', 'ac.id_actividad_producto', '=', 'c.id_actividad_producto')
                        ->join('actividad as a', 'a.id_actividad', '=', 'ac.id_actividad')
                        ->whereIn('a.id_area', $ids_areas)
                        ->where('c.codigo_semana', '>=', $sem_desde->codigo)
                        ->where('c.codigo_semana', '<=', $sem_hasta->codigo)
                        ->get()[0]->cant;
                    $mano_obra = DB::table('costos_semana_mano_obra as c')
                        ->select(DB::raw('sum(c.valor) as cant'))
                        ->join('actividad_mano_obra as am', 'am.id_actividad_mano_obra', '=', 'c.id_actividad_mano_obra')
                        ->join('actividad as a', 'a.id_actividad', '=', 'am.id_actividad')
                        ->whereIn('a.id_area', $ids_areas)
                        ->where('c.codigo_semana', '>=', $sem_desde->codigo)
                        ->where('c.codigo_semana', '<=', $sem_hasta->codigo)
                        ->get()[0]->cant;
                    $otros = DB::table('otros_gastos as o')
                        ->select(DB::raw('sum(o.gip + o.ga) as cant'))
                        ->whereIn('o.id_area', $ids_areas)
                        ->where('o.codigo_semana', '>=', $sem_desde->codigo)
                        ->where('o.codigo_semana', '<=', $sem_hasta->codigo)
                        ->get()[0]->cant;

                    $costos_total = $insumos + $mano_obra + $otros;

                    $tallos = DB::table('resumen_total_semanal_exportcalas')
                        ->select(DB::raw('sum(tallos_cosechados) as cant'))
                        ->where('id_empresa', $finca)
                        ->where('semana', '>=', $sem_desde->codigo)
                        ->where('semana', '<=', $sem_hasta->codigo)
                        ->get()[0]->cant;
                    $tallos = $tallos > 0 ? $tallos : 0;

                    $model->valor = $tallos > 0 ? round(($costos_total / $tallos) * 100, 2) : 0;
                    $model->save();
                }
            }
        }
    }

    public static function costos_postcosecha_tallo_4_semana_atras($indicador_par)
    {
        foreach (ConfiguracionEmpresa::All() as $emp) {
            $finca = $emp->id_configuracion_empresa;
            $model = getIndicadorByName('C5-' . $finca);  // Costos Postcosecha x Tallo (-4 semanas)
            if ($model != '') {
                $last_semana = $last_semana = DB::table('costos_semana')
                    ->select(DB::raw('max(codigo_semana) as last_semana'))
                    ->where('valor', '>', 0)
                    ->where('id_empresa', $finca)
                    ->get()[0]->last_semana;
                $last_semana = getObjSemana($last_semana);
                if ($last_semana != '') {
                    $sem_desde = getSemanaByDate(opDiasFecha('-', 21, $last_semana->fecha_inicial));
                    $sem_hasta = $last_semana;

                    $areas = Area::where('estado', 1)
                        ->where('nombre', 'like', '%POSCOSECHA%')
                        ->where('id_empresa', $finca)
                        ->get();
                    $ids_areas = [];
                    foreach ($areas as $a)
                        array_push($ids_areas, $a->id_area);
                    $insumos = DB::table('costos_semana as c')
                        ->select(DB::raw('sum(c.valor) as cant'))
                        ->join('actividad_producto as ac', 'ac.id_actividad_producto', '=', 'c.id_actividad_producto')
                        ->join('actividad as a', 'a.id_actividad', '=', 'ac.id_actividad')
                        ->whereIn('a.id_area', $ids_areas)
                        ->where('c.codigo_semana', '>=', $sem_desde->codigo)
                        ->where('c.codigo_semana', '<=', $sem_hasta->codigo)
                        ->get()[0]->cant;
                    $mano_obra = DB::table('costos_semana_mano_obra as c')
                        ->select(DB::raw('sum(c.valor) as cant'))
                        ->join('actividad_mano_obra as am', 'am.id_actividad_mano_obra', '=', 'c.id_actividad_mano_obra')
                        ->join('actividad as a', 'a.id_actividad', '=', 'am.id_actividad')
                        ->whereIn('a.id_area', $ids_areas)
                        ->where('c.codigo_semana', '>=', $sem_desde->codigo)
                        ->where('c.codigo_semana', '<=', $sem_hasta->codigo)
                        ->get()[0]->cant;
                    $otros = DB::table('otros_gastos as o')
                        ->select(DB::raw('sum(o.gip + o.ga) as cant'))
                        ->whereIn('o.id_area', $ids_areas)
                        ->where('o.codigo_semana', '>=', $sem_desde->codigo)
                        ->where('o.codigo_semana', '<=', $sem_hasta->codigo)
                        ->get()[0]->cant;

                    $costos_total = $insumos + $mano_obra + $otros;

                    $tallos = DB::table('resumen_total_semanal_exportcalas')
                        ->select(DB::raw('sum(tallos_cosechados) as cant'))
                        ->where('id_empresa', $finca)
                        ->where('semana', '>=', $sem_desde->codigo)
                        ->where('semana', '<=', $sem_hasta->codigo)
                        ->get()[0]->cant;
                    $tallos = $tallos > 0 ? $tallos : 0;

                    dump('costos totales = ' . $costos_total . '; insumos ' . $insumos . ' + mano_obra ' . $mano_obra . ' + otros ' . $otros . ' / tallos ' . $tallos);
                    $model->valor = $tallos > 0 ? round(($costos_total / $tallos) * 100, 2) : 0;
                    $model->save();
                }
            }
        }
    }

    public static function costos_total_tallo_4_semana_atras($indicador_par)
    {
        foreach (ConfiguracionEmpresa::All() as $emp) {
            $finca = $emp->id_configuracion_empresa;
            $model = getIndicadorByName('C6-' . $finca);  // Costos Total x Tallo
            if ($model != '') {
                $last_semana = $last_semana = DB::table('costos_semana')
                    ->select(DB::raw('max(codigo_semana) as last_semana'))
                    ->where('valor', '>', 0)
                    ->where('id_empresa', $finca)
                    ->get()[0]->last_semana;
                $last_semana = getObjSemana($last_semana);
                if ($last_semana != '') {
                    $sem_desde = getSemanaByDate(opDiasFecha('-', 21, $last_semana->fecha_inicial));
                    $sem_hasta = $last_semana;

                    $costos_total = DB::table('resumen_costos_semanal')
                        ->select(DB::raw('sum(mano_obra + insumos + fijos + regalias) as cant'))
                        ->where('codigo_semana', '>=', $sem_desde->codigo)
                        ->where('codigo_semana', '<=', $sem_hasta->codigo)
                        ->where('id_empresa', $finca)
                        ->get()[0]->cant;

                    $tallos = DB::table('resumen_total_semanal_exportcalas')
                        ->select(DB::raw('sum(tallos_cosechados) as cant'))
                        ->where('id_empresa', $finca)
                        ->where('semana', '>=', $sem_desde->codigo)
                        ->where('semana', '<=', $sem_hasta->codigo)
                        ->get()[0]->cant;
                    $tallos = $tallos > 0 ? $tallos : 0;

                    //dd($sem_desde->codigo, $sem_hasta->codigo, $costos_total, $tallos);
                    $model->valor = $tallos > 0 ? round(($costos_total / $tallos) * 100, 2) : 0;
                    $model->save();
                }
            }
        }
    }

    public static function costos_m2_4_semanas_atras($indicador_par)
    {
        $semana_hasta = getSemanaByDate(opDiasFecha('-', 14, hoy()));
        $semana_desde = getSemanaByDate(opDiasFecha('-', 21, $semana_hasta->fecha_inicial));
        dump('semana_desde: ' . $semana_desde->codigo . '; semana_hasta: ' . $semana_hasta->codigo);
        foreach (ConfiguracionEmpresa::All() as $empresa) {
            $finca = $empresa->id_configuracion_empresa;
            $model = getIndicadorByName('C13-' . $finca);
            if ($model != '') {
                $fincas = [$finca];
                if ($finca == 2)
                    array_push($fincas, -1);

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

                /* COMPRA_FLOR */
                $compra_flor = DB::table('bouquetera as b')
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
                /* RESUMEN_COSTOS */
                $resumen_costos = DB::table('resumen_costos_semanal')
                    ->select(
                        DB::raw('sum(mano_obra) as mano_obra'),
                        DB::raw('sum(insumos) as insumos'),
                        DB::raw('sum(fijos) as fijos'),
                        DB::raw('sum(regalias) as regalias')
                    )
                    ->where('id_empresa', $finca)
                    ->where('codigo_semana', '>=', $semana_desde->codigo)
                    ->where('codigo_semana', '<=', $semana_hasta->codigo)
                    ->get()[0];
                $costos_operativos = $resumen_costos->mano_obra + $resumen_costos->insumos + $resumen_costos->fijos + $resumen_costos->regalias + ($compra_flor->tallos + $compra_flor->exportada);

                $valor = ($costos_operativos > 0 && $prom_area > 0) ? round($costos_operativos / $prom_area, 2) : 0;
                dump('finca: ' . $finca . '; costos: ' . $costos_operativos . '; area: ' . $prom_area);
                $model->valor = $valor * 13;
                $model->save();
            }
        }
    }

    public static function costos_m2_13_semanas_atras($indicador_par)
    {
        $semana_hasta = getSemanaByDate(opDiasFecha('-', 14, hoy()));
        $semana_desde = getSemanaByDate(opDiasFecha('-', 91, $semana_hasta->fecha_inicial));
        dump('semana_desde: ' . $semana_desde->codigo . '; semana_hasta: ' . $semana_hasta->codigo);
        foreach (ConfiguracionEmpresa::All() as $empresa) {
            $finca = $empresa->id_configuracion_empresa;
            $model = getIndicadorByName('C9-' . $finca);
            if ($model != '') {
                $fincas = [$finca];
                if ($finca == 2)
                    array_push($fincas, -1);

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

                /* COMPRA_FLOR */
                $compra_flor = DB::table('bouquetera as b')
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
                /* RESUMEN_COSTOS */
                $resumen_costos = DB::table('resumen_costos_semanal')
                    ->select(
                        DB::raw('sum(mano_obra) as mano_obra'),
                        DB::raw('sum(insumos) as insumos'),
                        DB::raw('sum(fijos) as fijos'),
                        DB::raw('sum(regalias) as regalias')
                    )
                    ->where('id_empresa', $finca)
                    ->where('codigo_semana', '>=', $semana_desde->codigo)
                    ->where('codigo_semana', '<=', $semana_hasta->codigo)
                    ->get()[0];
                $costos_operativos = $resumen_costos->mano_obra + $resumen_costos->insumos + $resumen_costos->fijos + $resumen_costos->regalias + ($compra_flor->tallos + $compra_flor->exportada);

                $valor = ($costos_operativos > 0 && $prom_area > 0) ? round($costos_operativos / $prom_area, 2) : 0;
                dump('finca: ' . $finca . '; costos: ' . $costos_operativos . '; area: ' . $prom_area);
                $model->valor = $valor * 4;
                $model->save();
            }
        }
    }

    public static function costos_m2_52_semanas_atras($indicador_par)
    {
        $semana_hasta = getSemanaByDate(opDiasFecha('-', 14, hoy()));
        $semana_desde = substr($semana_hasta->codigo, 0, 2) . '01';
        $semana_desde = getObjSemana($semana_desde);
        dump('semana_desde: ' . $semana_desde->codigo . '; semana_hasta: ' . $semana_hasta->codigo);
        foreach (ConfiguracionEmpresa::All() as $empresa) {
            $finca = $empresa->id_configuracion_empresa;
            $model = getIndicadorByName('C10-' . $finca);
            if ($model != '') {
                $fincas = [$finca];
                if ($finca == 2)
                    array_push($fincas, -1);

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

                /* COMPRA_FLOR */
                $compra_flor = DB::table('bouquetera as b')
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
                /* RESUMEN_COSTOS */
                $resumen_costos = DB::table('resumen_costos_semanal')
                    ->select(
                        DB::raw('sum(mano_obra) as mano_obra'),
                        DB::raw('sum(insumos) as insumos'),
                        DB::raw('sum(fijos) as fijos'),
                        DB::raw('sum(regalias) as regalias')
                    )
                    ->where('id_empresa', $finca)
                    ->where('codigo_semana', '>=', $semana_desde->codigo)
                    ->where('codigo_semana', '<=', $semana_hasta->codigo)
                    ->get()[0];
                $costos_operativos = $resumen_costos->mano_obra + $resumen_costos->insumos + $resumen_costos->fijos + $resumen_costos->regalias + ($compra_flor->tallos + $compra_flor->exportada);

                $valor = ($costos_operativos > 0 && $prom_area > 0) ? round($costos_operativos / $prom_area, 2) : 0;
                dump('finca: ' . $finca . '; costos: ' . $costos_operativos . '; area: ' . $prom_area);
                $model->valor = $valor;
                $model->save();
            }
        }
    }

    public static function rentabilidad_4_meses($indicador_par)
    {
        foreach (ConfiguracionEmpresa::All() as $empresa) {
            $id_empresa = $empresa->id_configuracion_empresa;
            $model = getIndicadorByName('R1-' . $id_empresa);  // Rentabilidad (-4 meses)
            if ($model != '') {
                $valor = getIndicadorByName('D9-' . $id_empresa)->valor - getIndicadorByName('C9-' . $id_empresa)->valor;
                $model->valor = $valor;
                $model->save();
            }
        }
    }

    public static function rentabilidad_1_anno($indicador_par)
    {
        foreach (ConfiguracionEmpresa::All() as $empresa) {
            $id_empresa = $empresa->id_configuracion_empresa;
            $model = getIndicadorByName('R2-' . $id_empresa);  // Rentabilidad (-1 año)
            if ($model != '') {
                $valor = getIndicadorByName('D10-' . $id_empresa)->valor - getIndicadorByName('C10-' . $id_empresa)->valor;
                $model->valor = $valor;
                $model->save();
            }
        }
    }

    public static function rentabilidad_1_mes($indicador_par)
    {
        foreach (ConfiguracionEmpresa::All() as $empresa) {
            $id_empresa = $empresa->id_configuracion_empresa;
            $model = getIndicadorByName('R3-' . $id_empresa);  // Rentabilidad (-1 mes)
            if ($model != '') {
                $valor = getIndicadorByName('D18-' . $id_empresa)->valor - getIndicadorByName('C13-' . $id_empresa)->valor;
                $model->valor = $valor;
                $model->save();
            }
        }
    }

    /* -------------- SUPER_FINCA -------------------- */
    public static function sf_costos_m2_4_semanas_atras($indicador_par)
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
            $model = getIndicadorByName('SF4-' . $id_empresa);  // Costos/m2/año (-4 semanas) SF
            if ($model != '') {
                $fincas_compra = $ids_finca;
                if (in_array(2, $fincas_compra))
                    array_push($fincas_compra, -1);

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

                /* COMPRA_FLOR */
                $compra_flor = DB::table('bouquetera as b')
                    ->join('variedad as v', 'v.id_variedad', '=', 'b.id_variedad')
                    ->select(
                        DB::raw('sum(b.precio * (tallos)) as tallos'),
                        DB::raw('sum(b.precio * (exportada)) as exportada'),
                        DB::raw('sum(b.tallos) as tallos_bqt'),
                        DB::raw('sum(b.exportada) as tallos_exportada')
                    )
                    ->where('b.fecha', '>=', $semana_desde->fecha_inicial)
                    ->where('b.fecha', '<=', $semana_hasta->fecha_final)
                    ->whereIn('b.id_empresa', $fincas_compra)
                    ->get()[0];
                /* RESUMEN_COSTOS */
                $resumen_costos = DB::table('resumen_costos_semanal')
                    ->select(
                        DB::raw('sum(mano_obra) as mano_obra'),
                        DB::raw('sum(insumos) as insumos'),
                        DB::raw('sum(fijos) as fijos'),
                        DB::raw('sum(regalias) as regalias')
                    )
                    ->whereIn('id_empresa', $ids_finca)
                    ->where('codigo_semana', '>=', $semana_desde->codigo)
                    ->where('codigo_semana', '<=', $semana_hasta->codigo)
                    ->get()[0];
                $costos_operativos = $resumen_costos->mano_obra + $resumen_costos->insumos + $resumen_costos->fijos + $resumen_costos->regalias + ($compra_flor->tallos + $compra_flor->exportada);

                $valor = ($costos_operativos > 0 && $prom_area > 0) ? round($costos_operativos / $prom_area, 2) : 0;
                dump('Sfinca: ' . $sf->nombre . '; costos: ' . $costos_operativos . '; area: ' . $prom_area);
                $model->valor = $valor * 13;
                $model->save();
            }
        }
    }

    public static function sf_costos_m2_13_semanas_atras($indicador_par)
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
            $model = getIndicadorByName('SF5-' . $id_empresa);  // Costos/m2/año (-13 semanas) SF
            if ($model != '') {
                $fincas_compra = $ids_finca;
                if (in_array(2, $fincas_compra))
                    array_push($fincas_compra, -1);

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

                /* COMPRA_FLOR */
                $compra_flor = DB::table('bouquetera as b')
                    ->join('variedad as v', 'v.id_variedad', '=', 'b.id_variedad')
                    ->select(
                        DB::raw('sum(b.precio * (tallos)) as tallos'),
                        DB::raw('sum(b.precio * (exportada)) as exportada'),
                        DB::raw('sum(b.tallos) as tallos_bqt'),
                        DB::raw('sum(b.exportada) as tallos_exportada')
                    )
                    ->where('b.fecha', '>=', $semana_desde->fecha_inicial)
                    ->where('b.fecha', '<=', $semana_hasta->fecha_final)
                    ->whereIn('b.id_empresa', $fincas_compra)
                    ->get()[0];
                /* RESUMEN_COSTOS */
                $resumen_costos = DB::table('resumen_costos_semanal')
                    ->select(
                        DB::raw('sum(mano_obra) as mano_obra'),
                        DB::raw('sum(insumos) as insumos'),
                        DB::raw('sum(fijos) as fijos'),
                        DB::raw('sum(regalias) as regalias')
                    )
                    ->whereIn('id_empresa', $ids_finca)
                    ->where('codigo_semana', '>=', $semana_desde->codigo)
                    ->where('codigo_semana', '<=', $semana_hasta->codigo)
                    ->get()[0];
                $costos_operativos = $resumen_costos->mano_obra + $resumen_costos->insumos + $resumen_costos->fijos + $resumen_costos->regalias + ($compra_flor->tallos + $compra_flor->exportada);

                $valor = ($costos_operativos > 0 && $prom_area > 0) ? round($costos_operativos / $prom_area, 2) : 0;
                dump('Sfinca: ' . $sf->nombre . '; costos: ' . $costos_operativos . '; area: ' . $prom_area);
                $model->valor = $valor * 4;
                $model->save();
            }
        }
    }

    public static function sf_costos_m2_52_semanas_atras($indicador_par)
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
            $model = getIndicadorByName('SF6-' . $id_empresa);  // Costos/m2/año (-52 semanas) SF
            if ($model != '') {
                $fincas_compra = $ids_finca;
                if (in_array(2, $fincas_compra))
                    array_push($fincas_compra, -1);

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

                /* COMPRA_FLOR */
                $compra_flor = DB::table('bouquetera as b')
                    ->join('variedad as v', 'v.id_variedad', '=', 'b.id_variedad')
                    ->select(
                        DB::raw('sum(b.precio * (tallos)) as tallos'),
                        DB::raw('sum(b.precio * (exportada)) as exportada'),
                        DB::raw('sum(b.tallos) as tallos_bqt'),
                        DB::raw('sum(b.exportada) as tallos_exportada')
                    )
                    ->where('b.fecha', '>=', $semana_desde->fecha_inicial)
                    ->where('b.fecha', '<=', $semana_hasta->fecha_final)
                    ->whereIn('b.id_empresa', $fincas_compra)
                    ->get()[0];
                /* RESUMEN_COSTOS */
                $resumen_costos = DB::table('resumen_costos_semanal')
                    ->select(
                        DB::raw('sum(mano_obra) as mano_obra'),
                        DB::raw('sum(insumos) as insumos'),
                        DB::raw('sum(fijos) as fijos'),
                        DB::raw('sum(regalias) as regalias')
                    )
                    ->whereIn('id_empresa', $ids_finca)
                    ->where('codigo_semana', '>=', $semana_desde->codigo)
                    ->where('codigo_semana', '<=', $semana_hasta->codigo)
                    ->get()[0];
                $costos_operativos = $resumen_costos->mano_obra + $resumen_costos->insumos + $resumen_costos->fijos + $resumen_costos->regalias + ($compra_flor->tallos + $compra_flor->exportada);

                $valor = ($costos_operativos > 0 && $prom_area > 0) ? round($costos_operativos / $prom_area, 2) : 0;
                dump('Sfinca: ' . $sf->nombre . '; costos: ' . $costos_operativos . '; area: ' . $prom_area);
                $model->valor = $valor;
                $model->save();
            }
        }
    }
}
