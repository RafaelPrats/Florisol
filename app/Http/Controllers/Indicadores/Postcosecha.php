<?php

namespace yura\Http\Controllers\Indicadores;

use Illuminate\Support\Facades\DB;
use yura\Modelos\ClasificacionBlanco;
use yura\Modelos\ClasificacionVerde;
use yura\Modelos\ConfiguracionEmpresa;
use yura\Modelos\IndicadorVariedad;
use yura\Modelos\Variedad;

class Postcosecha
{
    public static function calibre_7_dias_atras($indicador_par)
    {
        $dia_7_atras = opDiasFecha('-', 7, date('Y-m-d'));
        $dia_1_atras = opDiasFecha('-', 1, date('Y-m-d'));

        $model = getIndicadorByName('D1');  // Calibre (-7 días)
        if ($model != '') {
            $valor = getCalibreByRangoVariedad($dia_7_atras, $dia_1_atras, 'T');
            $model->valor = $valor;
            $model->save();
        }
    }

    public static function tallos_clasificados_7_dias_atras($indicador_par)
    {
        foreach (ConfiguracionEmpresa::All() as $empresa) {
            $id_empresa = $empresa->id_configuracion_empresa;
            $model = getIndicadorByName('D2-' . $id_empresa);  // Tallos clasificados (-7 días)
            if ($model != '') {
                $last_semana = DB::table('resumen_total_semanal_exportcalas')
                    ->select(DB::raw('max(semana) as last_semana'))
                    ->where('venta', '>', 0)
                    ->where('id_empresa', $id_empresa)
                    ->get()[0]->last_semana;
                $desde = $last_semana > 0 ? $last_semana : getSemanaByDate(opDiasFecha('-', 7, date('Y-m-d')))->codigo;
                $valor = DB::table('resumen_total_semanal_exportcalas')
                    ->select(DB::raw('sum(tallos_exportables) as cantidad'))
                    ->where('semana', $desde)
                    ->where('id_empresa', $id_empresa)
                    ->get()[0]->cantidad;
                $model->valor = $valor > 0 ? $valor : 0;
                $model->save();
            }
        }
    }

    public static function tallos_vendidos_1_semanas($indicador_par)
    {
        $desde = getSemanaByDate(opDiasFecha('-', 7, hoy()));
        foreach (ConfiguracionEmpresa::All() as $empresa) {
            $id_empresa = $empresa->id_configuracion_empresa;
            $model = getIndicadorByName('D19-' . $id_empresa);  // Tallos Vendidos (-1 semana)
            if ($model != '') {
                $valor = DB::table('resumen_total_semanal_exportcalas')
                    ->select(DB::raw('sum(tallos_vendidos) as cantidad'))
                    ->where('semana', $desde->codigo)
                    ->where('id_empresa', $id_empresa)
                    ->get()[0]->cantidad;
                $model->valor = $valor > 0 ? $valor : 0;
                $model->save();
            }
        }
    }

    public static function cajas_cosechadas_7_dias_atras($indicador_par)
    {
        $model = getIndicadorByName('P1');  // Cajas cosechadas (-7 días)
        if ($model != '') {
            $verdes = ClasificacionVerde::All()->where('estado', 1)
                ->where('fecha_ingreso', '>=', opDiasFecha('-', 7, date('Y-m-d')))
                ->where('fecha_ingreso', '<=', opDiasFecha('-', 1, date('Y-m-d')));
            $valor = 0;
            foreach ($verdes as $v) {
                $valor += round($v->getTotalRamosEstandar() / getConfiguracionEmpresa()->ramos_x_caja, 2);
            }
            $model->valor = $valor;
            $model->save();
        }
    }

    public static function rendimiento_desecho_7_dias_atras($indicador_par)
    {
        $model_1 = getIndicadorByName('D5');  // Rendimiento (-7 días)
        $model_2 = getIndicadorByName('D6');  // Desecho (-7 días)
        if ($model_1 != '' && $model_2 != '') {
            $fechas = [];
            for ($i = 1; $i <= 7; $i++) {
                array_push($fechas, opDiasFecha('-', $i, date('Y-m-d')));
            }

            $r_ver = 0;
            $r_ver_r = 0;
            $d_ver = 0;
            $count_ver = 0;
            $r_bla = 0;
            $d_bla = 0;
            $count_bla = 0;
            foreach ($fechas as $f) {
                $verde = ClasificacionVerde::All()->where('estado', 1)->where('fecha_ingreso', $f)->first();
                $blanco = ClasificacionBlanco::All()->where('estado', 1)->where('fecha_ingreso', $f)->first();

                if ($verde != '') {
                    $r_ver += $verde->getRendimiento();
                    $r_ver_r += $verde->getRendimientoRamos();
                    $d_ver += $verde->desecho();
                    $count_ver++;
                }
                if ($blanco != '') {
                    $r_bla += $blanco->getRendimiento();
                    $d_bla += $blanco->getDesecho();
                    $count_bla++;
                }
            }

            $rendimiento_desecho = [
                'verde' => [
                    'rendimiento' => $count_ver > 0 ? round($r_ver / $count_ver, 2) : 0,
                    'rendimiento_ramos' => $count_ver > 0 ? round($r_ver_r / $count_ver, 2) : 0,
                    'desecho' => $count_ver > 0 ? round($d_ver / $count_ver, 2) : 0
                ],
                'blanco' => [
                    'rendimiento' => $count_bla > 0 ? round($r_bla / $count_bla, 2) : 0,
                    'desecho' => $count_bla > 0 ? round($d_bla / $count_bla, 2) : 0
                ]
            ];

            $model_1->valor = round(($rendimiento_desecho['blanco']['rendimiento'] + $rendimiento_desecho['verde']['rendimiento_ramos']) / 2, 2);
            $model_1->save();
            $model_2->valor = round(($rendimiento_desecho['blanco']['desecho'] + $rendimiento_desecho['verde']['desecho']) / 2, 2);
            $model_2->save();
        }
    }
}