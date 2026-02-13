<?php
/**
 * Created by PhpStorm.
 * User: Rafael Prats
 * Date: 2021-04-15
 * Time: 10:31
 */

namespace yura\Http\Controllers\Indicadores;


use Illuminate\Support\Facades\DB;
use yura\Modelos\ConfiguracionEmpresa;
use yura\Modelos\IndicadorVariedad;
use yura\Modelos\Variedad;

class Bouquetera
{
    public static function venta_4_semanas_atras($indicador_par)
    {
        $desde = getSemanaByDate(opDiasFecha('-', 28, hoy()));
        $hasta = getSemanaByDate(opDiasFecha('-', 7, hoy()));
        $empresas = ConfiguracionEmpresa::All();
        $variedades = Variedad::where('estado', 1)->get();
        foreach ($empresas as $pos_e => $empresa) {
            $id_empresa = $empresa->id_configuracion_empresa;
            $indicador = getIndicadorByName('B1-' . $id_empresa);  // Venta Bqt (-4 semanas)
            if ($indicador != '') {
                $valor = DB::table('resumen_total_semanal_exportcalas')
                    ->select(DB::raw('sum(venta_bouquetera) as venta_bouquetera'))
                    ->where('semana', '>=', $desde->codigo)
                    ->where('semana', '<=', $hasta->codigo)
                    ->where('id_empresa', $id_empresa)
                    ->get()[0]->venta_bouquetera;
                $indicador->valor = $valor > 0 ? $valor : 0;
                $indicador->save();
            }
        }
    }

    public static function costos_1_semana_atras($indicador_par)
    {
        $semana_pasada = getSemanaByDate(opDiasFecha('-', 7, hoy()));
        $empresas = ConfiguracionEmpresa::All();
        $variedades = Variedad::where('estado', 1)->get();
        foreach ($empresas as $pos_e => $empresa) {
            $id_empresa = $empresa->id_configuracion_empresa;
            $indicador = getIndicadorByName('B2-' . $id_empresa);  // Costos Bqt (-4 semanas)
            if ($indicador != '') {
                $fincas = [$id_empresa];
                if ($id_empresa == 2)
                    array_push($fincas, -1);
                $valor = DB::table('bouquetera')
                    ->select(DB::raw('sum((tallos + exportada) * precio) as costo'))
                    ->where('fecha', '>=', $semana_pasada->fecha_inicial)
                    ->where('fecha', '<=', $semana_pasada->fecha_final)
                    ->whereIn('id_empresa', $fincas)
                    ->get()[0]->costo;
                $indicador->valor = $valor > 0 ? $valor : 0;
                $indicador->save();
            }
        }
    }

    public static function ebitda_4_semanas_atras($indicador_par)
    {
        $empresas = ConfiguracionEmpresa::All();
        $variedades = Variedad::where('estado', 1)->get();
        foreach ($empresas as $pos_e => $empresa) {
            $id_empresa = $empresa->id_configuracion_empresa;
            $indicador = getIndicadorByName('B3-' . $id_empresa);  // Venta Bqt (-4 semanas)
            if ($indicador != '') {
                $indicador->valor = getIndicadorByName('B1-' . $id_empresa)->valor - getIndicadorByName('B2-' . $id_empresa)->valor;
                $indicador->save();
            }
        }
    }

    public static function compra_flor_bqt_1_semana_atras($indicador_par)
    {
        $semana_pasada = getSemanaByDate(opDiasFecha('-', 14, hoy()));
        $empresas = ConfiguracionEmpresa::All();
        foreach ($empresas as $pos_e => $empresa) {
            $id_empresa = $empresa->id_configuracion_empresa;
            $indicador = getIndicadorByName('B4-' . $id_empresa);  // Compra Flor Bqt (-4 semanas)
            if ($indicador != '') {
                $fincas = [$id_empresa];
                if ($id_empresa == 2)
                    array_push($fincas, -1);
                $valor = DB::table('bouquetera')
                    ->select(DB::raw('sum(tallos * precio) as costo'))
                    ->where('fecha', '>=', $semana_pasada->fecha_inicial)
                    ->where('fecha', '<=', $semana_pasada->fecha_final)
                    ->whereIn('id_empresa', $fincas)
                    ->get()[0]->costo;
                $indicador->valor = $valor > 0 ? $valor : 0;
                $indicador->save();
            }
        }
    }

    public static function compra_flor_export_1_semana_atras($indicador_par)
    {
        $semana_pasada = getSemanaByDate(opDiasFecha('-', 14, hoy()));
        $empresas = ConfiguracionEmpresa::All();
        foreach ($empresas as $pos_e => $empresa) {
            $id_empresa = $empresa->id_configuracion_empresa;
            $indicador = getIndicadorByName('B5-' . $id_empresa);  // Compra Flor Export (-4 semanas)
            if ($indicador != '') {
                $fincas = [$id_empresa];
                if ($id_empresa == 2)
                    array_push($fincas, -1);
                $valor = DB::table('bouquetera')
                    ->select(DB::raw('sum(exportada * precio) as costo'))
                    ->where('fecha', '>=', $semana_pasada->fecha_inicial)
                    ->where('fecha', '<=', $semana_pasada->fecha_final)
                    ->whereIn('id_empresa', $fincas)
                    ->get()[0]->costo;
                $indicador->valor = $valor > 0 ? $valor : 0;
                $indicador->save();
            }
        }
    }
}
