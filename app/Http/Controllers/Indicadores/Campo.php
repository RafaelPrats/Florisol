<?php

namespace yura\Http\Controllers\Indicadores;

use Illuminate\Support\Facades\DB;
use yura\Modelos\ConfiguracionEmpresa;
use yura\Modelos\Cosecha;
use yura\Modelos\IndicadorVariedad;
use yura\Modelos\Variedad;

class Campo
{
    public static function tallos_cosechados_7_dias_atras($indicador_par)
    {
        $semana_pasada = getSemanaByDate(opDiasFecha('-', 14, hoy()));

        foreach (ConfiguracionEmpresa::All() as $empresa) {
            $finca = $empresa->id_configuracion_empresa;
            $model = getIndicadorByName('D11-' . $finca);
            if ($model != '') {
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

                $model->valor = $resumen_semanal->tallos_cosechados != '' ? $resumen_semanal->tallos_cosechados : 0;
                $model->save();
            }
        }
    }
    public static function tallos_anno($indicador_par)
    {
        $semana_hasta = getSemanaByDate(opDiasFecha('-', 14, hoy()));
        $semana_desde = substr($semana_hasta->codigo, 0, 2) . '01';
        $semana_desde = getObjSemana($semana_desde);

        foreach (ConfiguracionEmpresa::All() as $empresa) {
            $finca = $empresa->id_configuracion_empresa;
            $model = getIndicadorByName('D22-' . $finca);
            if ($model != '') {
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

                $model->valor = $resumen_semanal->tallos_cosechados;
                $model->save();
            }
        }
    }
}
