<?php

namespace yura\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use yura\Http\Controllers\Indicadores\Area;
use yura\Http\Controllers\Indicadores\Bouquetera;
use yura\Http\Controllers\Indicadores\Campo;
use yura\Http\Controllers\Indicadores\Costos;
use yura\Http\Controllers\Indicadores\Postcosecha;
use yura\Http\Controllers\Indicadores\Venta;
use yura\Http\Controllers\Indicadores\Proyecciones;
use yura\Modelos\ConfiguracionEmpresa;
use yura\Modelos\SuperFinca;

class UpdateIndicador extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'indicador:update {indicador=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Commando para actualizar los indicadores de los reportes del sistema';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     * @argument D => dashboard
     * @argument DP => dashboard de proyección
     */
    public function handle()
    {
        $ini = date('Y-m-d H:i:s');
        Log::info('<<<<< ! >>>>> Ejecutando comando "indicador:update" <<<<< ! >>>>>');

        $array_calibres = [];   // Calibre (-7 días)
        $array_tallos_anno = [];   // Tallos Año
        $array_tallos_clasificados_semanal = [];  // Tallos clasificados (-7 días)
        $array_prom_ramo_y_dinero_ingresado = [];  // Precio promedio por ramo (-7 días) - Dinero ingresado (-7 días)
        $array_porcentaje_venta_normal = [];  // % Venta Normal (-7 días)
        $array_porcentaje_venta_bqt = [];  // % Venta bqt (-7 días)
        $array_rendimiento_y_desecho = [];  // Rendimiento (-7 días) - Desecho (-7 días)
        $array_cajas_cosechadas_mensual_proy = [];  // Cajas cosechadas +4 semanas
        $array_tallos_cosechados_mensual_proy = [];  // Tallos cosechados +4 semanas
        $array_cajas_vendidas_mensual_proy = [];  // Cajas vendidas a futuro +4 semanas
        $array_dinero_mensual_proy = [];  // Dinero generado ventas a futuro +4 semanas
        $array_dinero_trimestre_proy = [];  // Dinero generado ventas a futuro mes 1|mes 2|mes 3
        $array_tallos_cosechados_semanal_proy = [];  // Tallos cosechados a futuro +1 semana
        $array_cajas_vendidas_semanal_proy = [];  // Cajas vendidas futuro +1 semana
        $array_cajas_cosechadas_semanal_proy = [];  // Cajas cosechadas a futuro +1 semana
        $array_dinero_semanal_proy = [];  // Dinero generado en ventas a futuro +1 semana
        $array_area_4meses = [];  // Área en producción (-4 meses)
        $array_ciclo_mensual = [];  // Ciclo (-4 semanas)
        $array_ramos_m2_anno_4meses = [];  // Ramos/m2/año (-4 meses)
        $array_venta_m2_anno_4meses = [];  // Venta $/m2/año (-4 meses)
        $array_venta_m2_anno_anual = [];  // Venta $/m2/año (-1 año)
        $array_tallos_cosechados_semanal = [];  // Tallos cosechados (-7 días)
        $array_tallos_m2_mensual = [];  // Tallos/m2 (-4 semanas)
        $array_ramos_m2_mensual = [];  // Ramos/m2 (-4 semanas)
        $array_cajas_eq_vendidas_semanal = [];  // Cajas equivalentes vendidas(-7 dias)
        $array_precio_tallo_semanal = [];  // Precio por tallo (-7 dias)
        $array_precio_tallo_normal = [];  // Precio por tallo Normal (-7 dias)
        $array_precio_tallo_bqt = [];  // Precio por tallo bqt (-7 dias)
        $array_cajas_cosechadas_semanal = [];  // Cajas cosechadas (-7 dias)
        $array_costos_mo_semanal = [];  // Costos Mano de Obra (-1 semana)
        $array_costos_insumos_semanal = [];  // Costos Insumos (-1 semana)
        $array_campo_ha_semana_mensual = [];  // Costos Campo/ha/semana (-4 semanas)
        $array_costos_cosecha_x_tallo_mensual = [];  // Costos Cosecha x Tallo (-4 semanas)
        $array_costos_postcosecha_x_tallo_mensual = [];  // Costos Postcosecha x Tallo (-4 semanas)
        $array_costos_total_x_tallo_mensual = [];  // Costos Total x Tallo (-4 semanas)
        $array_costos_fijos_semanal = [];  // Costos Fijos (-1 semana)
        $array_costos_regalias_semanal = [];  // Costos Regalías (-1 semana)
        $array_costos_m2_4semnas = [];  // Costos/m2 (-4 semanas)
        $array_costos_m2_13semnas = [];  // Costos/m2 (-13 semanas)
        $array_costos_m2_52semnas = [];  // Costos/m2 (-52 semanas)
        $array_costos_x_planta_4semnas = [];  // Costo x Planta (-4 semanas)
        $array_rentabilidad_1mes = [];  // Rentabilidad (-1 meses)
        $array_rentabilidad_4meses = [];  // Rentabilidad (-4 meses)
        $array_rentabilidad_anual = [];  // Rentabilidad (-1 año)
        $array_nacional = [];  // Nacional (-1 semana)
        $array_bajas = [];  // Bajas (-1 semana)
        $array_porcentaje_cumplimiento = [];  // % Cumplimiento (-1 semana)
        $array_venta_m2_anno_4_semanas = [];  // Venta $/m2/año (-4 semanas)
        $array_tallos_vendidos_1_semanas = [];  // Tallos Vendidos (-1 semana)
        $array_venta_bqt_4_semanas = [];  // Venta Bqt (-4 semana)
        $array_costos_bqt_1_semana = [];  // Costos Bqt (-4 semana)
        $array_ebitda_bqt_4_semanas = [];  // EBITDA Bqt (-4 semana)
        $array_compra_flor_bqt_1_semana = [];  // Compra Flor Bqt (-4 semanas)
        $array_compra_flor_export_1_semana = [];  // Compra Flor Export (-4 semanas)
        $array_venta_comprada_4_semana = [];  // Venta Comprada (-4 semanas)
        $array_venta_comprada_13_semana = [];  // Venta Comprada (-13 semanas)
        $array_venta_comprada_1_anno = [];  // Venta Comprada (año)
        foreach (ConfiguracionEmpresa::All() as $f) {
            //array_push($array_calibres, 'D1-' . $f->id_configuracion_empresa);
            array_push($array_tallos_anno, 'D22-' . $f->id_configuracion_empresa);
            array_push($array_tallos_clasificados_semanal, 'D2-' . $f->id_configuracion_empresa);
            //array_push($array_prom_ramo_y_dinero_ingresado, 'D3-' . $f->id_configuracion_empresa);
            array_push($array_prom_ramo_y_dinero_ingresado, 'D4-' . $f->id_configuracion_empresa);
            array_push($array_porcentaje_venta_normal, 'D20-' . $f->id_configuracion_empresa);
            array_push($array_porcentaje_venta_bqt, 'D21-' . $f->id_configuracion_empresa);
            array_push($array_rendimiento_y_desecho, 'D5-' . $f->id_configuracion_empresa);
            array_push($array_rendimiento_y_desecho, 'D6-' . $f->id_configuracion_empresa);
            array_push($array_cajas_cosechadas_mensual_proy, 'DP1-' . $f->id_configuracion_empresa);
            array_push($array_tallos_cosechados_mensual_proy, 'DP2-' . $f->id_configuracion_empresa);
            array_push($array_cajas_vendidas_mensual_proy, 'DP3-' . $f->id_configuracion_empresa);
            array_push($array_dinero_mensual_proy, 'DP4-' . $f->id_configuracion_empresa);
            array_push($array_dinero_trimestre_proy, 'DP5-' . $f->id_configuracion_empresa);
            array_push($array_tallos_cosechados_semanal_proy, 'DP6-' . $f->id_configuracion_empresa);
            array_push($array_cajas_vendidas_semanal_proy, 'DP7-' . $f->id_configuracion_empresa);
            array_push($array_cajas_cosechadas_semanal_proy, 'DP8-' . $f->id_configuracion_empresa);
            array_push($array_dinero_semanal_proy, 'DP9-' . $f->id_configuracion_empresa);
            array_push($array_area_4meses, 'D7-' . $f->id_configuracion_empresa);
            array_push($array_ciclo_mensual, 'DA1-' . $f->id_configuracion_empresa);
            array_push($array_ramos_m2_anno_4meses, 'D8-' . $f->id_configuracion_empresa);
            array_push($array_venta_m2_anno_4meses, 'D9-' . $f->id_configuracion_empresa);
            array_push($array_venta_m2_anno_anual, 'D10-' . $f->id_configuracion_empresa);
            array_push($array_tallos_cosechados_semanal, 'D11-' . $f->id_configuracion_empresa);
            array_push($array_tallos_m2_mensual, 'D12-' . $f->id_configuracion_empresa);
            array_push($array_ramos_m2_mensual, 'DA2-' . $f->id_configuracion_empresa);
            array_push($array_cajas_eq_vendidas_semanal, 'D13-' . $f->id_configuracion_empresa);
            array_push($array_precio_tallo_semanal, 'D14-' . $f->id_configuracion_empresa);
            array_push($array_precio_tallo_normal, 'D23-' . $f->id_configuracion_empresa);
            array_push($array_precio_tallo_bqt, 'D24-' . $f->id_configuracion_empresa);
            array_push($array_cajas_cosechadas_semanal, 'P1-' . $f->id_configuracion_empresa);
            array_push($array_costos_mo_semanal, 'C1-' . $f->id_configuracion_empresa);
            array_push($array_costos_insumos_semanal, 'C2-' . $f->id_configuracion_empresa);
            array_push($array_campo_ha_semana_mensual, 'C3-' . $f->id_configuracion_empresa);
            array_push($array_costos_cosecha_x_tallo_mensual, 'C4-' . $f->id_configuracion_empresa);
            array_push($array_costos_postcosecha_x_tallo_mensual, 'C5-' . $f->id_configuracion_empresa);
            array_push($array_costos_total_x_tallo_mensual, 'C6-' . $f->id_configuracion_empresa);
            array_push($array_costos_fijos_semanal, 'C7-' . $f->id_configuracion_empresa);
            array_push($array_costos_regalias_semanal, 'C8-' . $f->id_configuracion_empresa);
            array_push($array_costos_m2_13semnas, 'C9-' . $f->id_configuracion_empresa);
            array_push($array_costos_m2_52semnas, 'C10-' . $f->id_configuracion_empresa);
            array_push($array_costos_m2_4semnas, 'C13-' . $f->id_configuracion_empresa);
            array_push($array_costos_x_planta_4semnas, 'C12-' . $f->id_configuracion_empresa);
            array_push($array_rentabilidad_1mes, 'R3-' . $f->id_configuracion_empresa);
            array_push($array_rentabilidad_4meses, 'R1-' . $f->id_configuracion_empresa);
            array_push($array_rentabilidad_anual, 'R2-' . $f->id_configuracion_empresa);
            array_push($array_nacional, 'D15-' . $f->id_configuracion_empresa);
            array_push($array_bajas, 'D16-' . $f->id_configuracion_empresa);
            array_push($array_porcentaje_cumplimiento, 'D17-' . $f->id_configuracion_empresa);
            array_push($array_venta_m2_anno_4_semanas, 'D18-' . $f->id_configuracion_empresa);
            array_push($array_tallos_vendidos_1_semanas, 'D19-' . $f->id_configuracion_empresa);
            array_push($array_venta_bqt_4_semanas, 'B1-' . $f->id_configuracion_empresa);
            array_push($array_costos_bqt_1_semana, 'B2-' . $f->id_configuracion_empresa);
            array_push($array_ebitda_bqt_4_semanas, 'B3-' . $f->id_configuracion_empresa);
            array_push($array_compra_flor_bqt_1_semana, 'B4-' . $f->id_configuracion_empresa);
            array_push($array_compra_flor_export_1_semana, 'B5-' . $f->id_configuracion_empresa);
            array_push($array_venta_comprada_1_anno, 'FC1-' . $f->id_configuracion_empresa);
            array_push($array_venta_comprada_4_semana, 'FC2-' . $f->id_configuracion_empresa);
            array_push($array_venta_comprada_13_semana, 'FC3-' . $f->id_configuracion_empresa);
        }

        $array_sf_venta_m2_anno_4_semana = [];  // Ventas/m2/año (-4 semanas) de Super_Finca
        $array_sf_venta_m2_anno_13_semana = [];  // Ventas/m2/año (-13 semanas) de Super_Finca
        $array_sf_venta_m2_anno_52_semana = [];  // Ventas/m2/año (-52 semanas) de Super_Finca
        $array_sf_costos_m2_anno_4_semana = [];  // Costos/m2/año (-4 semanas) de Super_Finca
        $array_sf_costos_m2_anno_13_semana = [];  // Costos/m2/año (-13 semanas) de Super_Finca
        $array_sf_costos_m2_anno_52_semana = [];  // Costos/m2/año (-52 semanas) de Super_Finca
        foreach (SuperFinca::All() as $sf) {
            array_push($array_sf_venta_m2_anno_4_semana, 'SF1-' . $sf->id_super_finca);
            array_push($array_sf_venta_m2_anno_13_semana, 'SF2-' . $sf->id_super_finca);
            array_push($array_sf_venta_m2_anno_52_semana, 'SF3-' . $sf->id_super_finca);
            array_push($array_sf_costos_m2_anno_4_semana, 'SF4-' . $sf->id_super_finca);
            array_push($array_sf_costos_m2_anno_13_semana, 'SF5-' . $sf->id_super_finca);
            array_push($array_sf_costos_m2_anno_52_semana, 'SF6-' . $sf->id_super_finca);
        }

        $indicador_par = $this->argument('indicador');
        if ($indicador_par !== 0)
            dump('Indicador Parámetro: ' . $indicador_par);
        else
            dump('Indicador Parámetro: TODOS');

        if (in_array($indicador_par, $array_venta_comprada_1_anno) || $indicador_par == '0') { // Venta Comprada año
            dump('CALCULAR INDICADOR: "Venta Comprada año"');
            Venta::venta_comprada_1_anno($indicador_par);
            Log::info('INDICADOR: "Venta Comprada año"');
        }
        if (in_array($indicador_par, $array_venta_comprada_4_semana) || $indicador_par == '0') { // Venta Comprada -4 semana
            dump('CALCULAR INDICADOR: "Venta Comprada -4 semana"');
            Venta::venta_comprada_4_semana($indicador_par);
            Log::info('INDICADOR: "Venta Comprada -4 semana"');
        }
        if (in_array($indicador_par, $array_venta_comprada_13_semana) || $indicador_par == '0') { // Venta Comprada -13 semana
            dump('CALCULAR INDICADOR: "Venta Comprada -13 semana"');
            Venta::venta_comprada_13_semana($indicador_par);
            Log::info('INDICADOR: "Venta Comprada -13 semana"');
        }
        if (in_array($indicador_par, $array_tallos_anno) || $indicador_par == '0') { // Tallos Año
            dump('CALCULAR INDICADOR: "Tallos Año"');
            Campo::tallos_anno($indicador_par);
            Log::info('INDICADOR: "Tallos Año"');
        }
        if (in_array($indicador_par, $array_porcentaje_venta_bqt) || $indicador_par == '0') { // % Venta bqt (-1 semana)
            dump('CALCULAR INDICADOR: "% Venta bqt (-1 semana)"');
            Venta::porcentaje_venta_bqt($indicador_par);
            Log::info('INDICADOR: "% Venta bqt (-1 semana)"');
        }
        if (in_array($indicador_par, $array_porcentaje_venta_normal) || $indicador_par == '0') { // % Venta Normal (-1 semana)
            dump('CALCULAR INDICADOR: "% Venta Normal (-1 semana)"');
            Venta::porcentaje_venta_normal($indicador_par);
            Log::info('INDICADOR: "% Venta Normal (-1 semana)"');
        }
        if (in_array($indicador_par, $array_sf_costos_m2_anno_4_semana) || $indicador_par == '0') { // Costos/m2/año (-4 semanas) de Super_Finca
            dump('CALCULAR INDICADOR: "Costos/m2/año (-4 semanas) de Super_Finca"');
            Costos::sf_costos_m2_4_semanas_atras($indicador_par);
            Log::info('INDICADOR: "Costos/m2/año (-4 semanas) de Super_Finca"');
        }
        if (in_array($indicador_par, $array_sf_costos_m2_anno_13_semana) || $indicador_par == '0') { // Costos/m2/año (-13 semanas) de Super_Finca
            dump('CALCULAR INDICADOR: "Costos/m2/año (-13 semanas) de Super_Finca"');
            Costos::sf_costos_m2_13_semanas_atras($indicador_par);
            Log::info('INDICADOR: "Costos/m2/año (-13 semanas) de Super_Finca"');
        }
        if (in_array($indicador_par, $array_sf_costos_m2_anno_52_semana) || $indicador_par == '0') { // Costos/m2/año (-52 semanas) de Super_Finca
            dump('CALCULAR INDICADOR: "Costos/m2/año (-52 semanas) de Super_Finca"');
            Costos::sf_costos_m2_52_semanas_atras($indicador_par);
            Log::info('INDICADOR: "Costos/m2/año (-52 semanas) de Super_Finca"');
        }
        if (in_array($indicador_par, $array_sf_venta_m2_anno_4_semana) || $indicador_par == '0') {  // Ventas/m2/año (-4 semanas) de Super_Finca
            dump('INDICADOR: "Ventas/m2/año (-4 semanas) de Super_Finca"');
            Venta::sf_venta_m2_anno_4_semanas_atras($indicador_par);
            Log::info('INDICADOR: "Ventas/m2/año (-4 semanas) de Super_Finca"');
        }
        if (in_array($indicador_par, $array_sf_venta_m2_anno_13_semana) || $indicador_par == '0') {  // Ventas/m2/año (-13 semanas) de Super_Finca
            dump('INDICADOR: "Ventas/m2/año (-13 semanas) de Super_Finca"');
            Venta::sf_venta_m2_anno_13_semanas_atras($indicador_par);
            Log::info('INDICADOR: "Ventas/m2/año (-13 semanas) de Super_Finca"');
        }
        if (in_array($indicador_par, $array_sf_venta_m2_anno_52_semana) || $indicador_par == '0') {  // Ventas/m2/año (-52 semanas) de Super_Finca
            dump('INDICADOR: "Ventas/m2/año (-52 semanas) de Super_Finca"');
            Venta::sf_venta_m2_anno_52_semanas_atras($indicador_par);
            Log::info('INDICADOR: "Ventas/m2/año (-52 semanas) de Super_Finca"');
        }
        if (in_array($indicador_par, $array_calibres)) {  // Calibre (-7 días)
            Postcosecha::calibre_7_dias_atras($indicador_par);
            Log::info('INDICADOR: "Calibre (-7 dias)"');
        }
        if (in_array($indicador_par, $array_venta_bqt_4_semanas) || $indicador_par == '0') {  // Venta Bqt (-4 semanas)
            dump('CALCULAR INDICADOR: "Venta Bqt (-4 semanas)"');
            Bouquetera::venta_4_semanas_atras($indicador_par);
            Log::info('INDICADOR: "Venta Bqt (-4 semanas)"');
        }
        if (in_array($indicador_par, $array_costos_bqt_1_semana) || $indicador_par == '0') {  // Costos Bqt (-4 semanas)
            dump('CALCULAR INDICADOR: "Costos Bqt (-4 semanas)"');
            Bouquetera::costos_1_semana_atras($indicador_par);
            Log::info('INDICADOR: "Costos Bqt (-4 semanas)"');
        }
        if (in_array($indicador_par, $array_compra_flor_bqt_1_semana) || $indicador_par == '0') {  // Compra Flor Bqt (-4 semanas)
            dump('CALCULAR INDICADOR: "Compra Flor Bqt (-4 semanas)"');
            Bouquetera::compra_flor_bqt_1_semana_atras($indicador_par);
            Log::info('INDICADOR: "Compra Flor Bqt (-4 semanas)"');
        }
        if (in_array($indicador_par, $array_compra_flor_export_1_semana) || $indicador_par == '0') {  // Compra Flor Export (-4 semanas)
            dump('CALCULAR INDICADOR: "Compra Flor Export (-4 semanas)"');
            Bouquetera::compra_flor_export_1_semana_atras($indicador_par);
            Log::info('INDICADOR: "Compra Flor Export (-4 semanas)"');
        }
        if (in_array($indicador_par, $array_ebitda_bqt_4_semanas) || $indicador_par == '0') {  // EBITDA Bqt (-4 semanas)
            dump('CALCULAR INDICADOR: "EBITDA Bqt (-4 semanas)"');
            Bouquetera::ebitda_4_semanas_atras($indicador_par);
            Log::info('INDICADOR: "EBITDA Bqt (-4 semanas)"');
        }
        if (in_array($indicador_par, $array_tallos_clasificados_semanal) || $indicador_par == '0') {  // Tallos clasificados (-7 días)
            dump('CALCULAR INDICADOR: "Tallos clasificados (-7 dias)"');
            Postcosecha::tallos_clasificados_7_dias_atras($indicador_par);
            Log::info('INDICADOR: "Tallos clasificados (-7 dias)"');
        }
        if (in_array($indicador_par, $array_tallos_vendidos_1_semanas) || $indicador_par == '0') {  // Tallos Vendidos (-1 semana)
            dump('CALCULAR INDICADOR: "Tallos Vendidos (-1 semana)"');
            Postcosecha::tallos_vendidos_1_semanas($indicador_par);
            Log::info('INDICADOR: "Tallos Vendidos (-1 semana)"');
        }
        if (in_array($indicador_par, $array_prom_ramo_y_dinero_ingresado) || $indicador_par == '0') {
            // Precio promedio por ramo (-7 días) - Dinero ingresado (-7 días)
            dump('CALCULAR INDICADOR: "Precio promedio por ramo (-7 días) - Dinero ingresado (-7 días)"');
            Venta::venta_7_dias_atras($indicador_par);
            Log::info('INDICADOR: "Precio promedio por ramo (-7 días) - Dinero ingresado (-7 días)"');
        }
        if (in_array($indicador_par, $array_rendimiento_y_desecho)) { // Rendimiento (-7 días) - Desecho (-7 días)
            Postcosecha::rendimiento_desecho_7_dias_atras($indicador_par);
            Log::info('INDICADOR: "Rendimiento (-7 días) - Desecho (-7 días)"');
        }
        if (in_array($indicador_par, $array_cajas_cosechadas_mensual_proy)) {   // Cajas cosechadas +4 semanas
            Proyecciones::sumCajasFuturas4Semanas($indicador_par);
            Log::info('INDICADOR: "Cajas cosechadas +4 semanas"');
        }
        if (in_array($indicador_par, $array_tallos_cosechados_mensual_proy)) {   // Tallos cosechados +4 semanas
            Proyecciones::sumTallosFuturos4Semanas($indicador_par);
            Log::info('INDICADOR: "Tallos cosechados +4 semanas"');
        }
        if (in_array($indicador_par, $array_cajas_vendidas_mensual_proy)) {   // Cajas vendidas a futuro +4 semanas
            Proyecciones::sumCajasVendidas($indicador_par);
            Log::info('INDICADOR: "Cajas vendidas a futuro +4 semanas"');
        }
        if (in_array($indicador_par, $array_dinero_mensual_proy)) {   // Dinero generado ventas a futuro +4 semanas
            Proyecciones::sumDineroGeneradoVentas($indicador_par);
            Log::info('INDICADOR: "Dinero generado ventas a futuro +4 semanas"');
        }
        if (in_array($indicador_par, $array_dinero_trimestre_proy)) {   // Dinero generado ventas a futuro mes 1|mes 2|mes 3
            Proyecciones::proyeccionVentaFutura3Meses($indicador_par);
            Log::info('INDICADOR: "Dinero generado ventas a futuro mes 1|mes 2|mes 3"');
        }
        if (in_array($indicador_par, $array_tallos_cosechados_semanal_proy)) {   // Tallos cosechados a futuro +1 semana
            Proyecciones::sumTallosCosechadosFuturo1Semana($indicador_par);
            Log::info('INDICADOR: "Tallos cosechados a futuro +1 semana"');
        }
        if (in_array($indicador_par, $array_cajas_vendidas_semanal_proy)) {   // Cajas vendidas futuro +1 semana
            Proyecciones::sumCajasVendidasFuturas1Semana($indicador_par);
            Log::info('INDICADOR: "Cajas vendidas futuro +1 semana"');
        }
        if (in_array($indicador_par, $array_cajas_cosechadas_semanal_proy)) {   // Cajas cosechadas a futuro +1 semana
            Proyecciones::sumCajasCosechadasFuturas1Semana($indicador_par);
            Log::info('INDICADOR: "Cajas cosechadas a futuro +1 semana"');
        }
        if (in_array($indicador_par, $array_dinero_semanal_proy)) {   // Dinero generado en ventas a futuro +1 semana
            Proyecciones::sumDineroGeneradoFuturo1Semana($indicador_par);
            Log::info('INDICADOR: "Dinero generado en ventas a futuro +1 semana"');
        }
        if (in_array($indicador_par, $array_area_4meses) || $indicador_par == '0') { // Área en producción (-4 meses)
            dump('CALCULAR INDICADOR: "Área en producción (-4 meses)"');
            Area::area_produccion_4_semanas_atras($indicador_par);
            Log::info('INDICADOR: "Área en producción (-4 meses)"');
        }
        if (in_array($indicador_par, $array_ciclo_mensual) || $indicador_par == '0') { // Ciclo (-4 semanas)
            dump('CALCULAR INDICADOR: "Ciclo (-4 semanas)"');
            Area::ciclo_4_semanas_atras($indicador_par);
            Log::info('INDICADOR: "Ciclo (-4 semanas)"');
        }
        if (in_array($indicador_par, $array_ramos_m2_anno_4meses)) { // Ramos/m2/año (-4 meses)
            Area::ramos_m2_anno_4_semanas_atras($indicador_par);
            Log::info('INDICADOR: "Ramos/m2/año (-4 meses)"');
        }
        if (in_array($indicador_par, $array_venta_m2_anno_4meses) || $indicador_par == '0') { // Venta $/m2/año (-13 semanas)
            dump('CALCULAR INDICADOR: "Venta $/m2/año (-13 semanas)"');
            Venta::dinero_m2_anno_13_semanas_atras($indicador_par);
            Log::info('INDICADOR: "Venta $/m2/año (-13 semanas)"');
        }
        if (in_array($indicador_par, $array_venta_m2_anno_anual) || $indicador_par == '0') { // Venta $/m2/año (anual)
            dump('CALCULAR INDICADOR: "Venta $/m2/año (anual)"');
            Venta::dinero_m2_anual($indicador_par);
            Log::info('INDICADOR: "Venta $/m2/año (anual)"');
        }
        if (in_array($indicador_par, $array_tallos_cosechados_semanal) || $indicador_par == '0') { // Tallos cosechados (-7 días)
            dump('CALCULAR INDICADOR: "Tallos cosechados (-7 días)"');
            Campo::tallos_cosechados_7_dias_atras($indicador_par);
            Log::info('INDICADOR: "Tallos cosechados (-7 días)"');
        }
        if (in_array($indicador_par, $array_nacional) || $indicador_par == '0') { // Nacional (-1 semana)
            dump('CALCULAR INDICADOR: "Nacional (-1 semana)"');
            Venta::nacional_1_semana_atras($indicador_par);
            Log::info('INDICADOR: "Nacional (-1 semana)"');
        }
        if (in_array($indicador_par, $array_bajas) || $indicador_par == '0') { // Bajas (-1 semana)
            dump('CALCULAR INDICADOR: "Bajas (-1 semana)"');
            Venta::bajas_1_semana_atras($indicador_par);
            Log::info('INDICADOR: "Bajas (-1 semana)"');
        }
        if (in_array($indicador_par, $array_venta_m2_anno_4_semanas) || $indicador_par == '0') { // Venta $/m2/año (-4 semanas)
            dump('CALCULAR INDICADOR: "Venta $/m2/año (-4 semanas)"');
            Venta::venta_m2_anno_4_semanas_atras($indicador_par);
            Log::info('INDICADOR: "Venta $/m2/año (-4 semanas)"');
        }
        if (in_array($indicador_par, $array_costos_m2_4semnas) || $indicador_par == '0') { // Costos/m2/año (-4 semanas)
            dump('CALCULAR INDICADOR: "Costos/m2/año (-4 semanas)"');
            Costos::costos_m2_4_semanas_atras($indicador_par);
            Log::info('INDICADOR: "Costos/m2/año (-4 semanas)"');
        }
        if (in_array($indicador_par, $array_porcentaje_cumplimiento) || $indicador_par == '0') { // % Cumplimiento (-1 semana)
            dump('CALCULAR INDICADOR: "% Cumplimiento (-1 semana)"');
            Venta::porcentaje_cumplimiento_1_semana_atras($indicador_par);
            Log::info('INDICADOR: "% Cumplimiento (-1 semana)"');
        }
        if (in_array($indicador_par, $array_tallos_m2_mensual) || $indicador_par == '0') { // Tallos/m2 (-4 semanas)
            dump('CALCULAR INDICADOR: "Tallos/m2 (-4 semanas)"');
            Area::tallos_m2_4_semanas_atras($indicador_par);
            Log::info('INDICADOR: "Tallos/m2 (-4 semanas)"');
        }
        if (in_array($indicador_par, $array_ramos_m2_mensual)) { // Ramos/m2 (-4 semanas)
            Area::ramos_m2_4_semanas_atras($indicador_par);
            Log::info('INDICADOR: "Ramos/m2 (-4 semanas)"');
        }
        if (in_array($indicador_par, $array_cajas_eq_vendidas_semanal)) { // Cajas equivalentes vendidas(-7 dias)
            Venta::cajas_equivalentes_vendidas_7_dias_atras($indicador_par);
            Log::info('INDICADOR: "Cajas equivalentes vendidas (-7 dias)"');
        }
        if (in_array($indicador_par, $array_precio_tallo_semanal) || $indicador_par == '0') { // Precio por tallo (-7 dias)
            dump('INDICADOR: "Precio por tallo (-7 dias)"');
            Venta::precio_por_tallo_7_dias_atras($indicador_par);
            Log::info('INDICADOR: "Precio por tallo (-7 dias)"');
        }
        if (in_array($indicador_par, $array_precio_tallo_normal) || $indicador_par == '0') { // Precio por tallo Normal (-7 dias)
            dump('INDICADOR: "Precio por tallo Normal (-7 dias)"');
            Venta::precio_por_tallo_normal_1_semana_atras($indicador_par);
            Log::info('INDICADOR: "Precio por tallo Normal (-7 dias)"');
        }
        if (in_array($indicador_par, $array_precio_tallo_bqt) || $indicador_par == '0') { // Precio por tallo bqt (-7 dias)
            dump('INDICADOR: "Precio por tallo bqt (-7 dias)"');
            Venta::precio_por_tallo_bqt_1_semana_atras($indicador_par);
            Log::info('INDICADOR: "Precio por tallo bqt (-7 dias)"');
        }
        if (in_array($indicador_par, $array_cajas_cosechadas_semanal)) { // Cajas cosechadas (-7 dias)
            Postcosecha::cajas_cosechadas_7_dias_atras($indicador_par);
            Log::info('INDICADOR: "Cajas cosechadas (-7 dias)"');
        }
        if (in_array($indicador_par, $array_costos_mo_semanal) || $indicador_par == '0') { // Costos Mano de Obra (-1 semana)
            dump('INDICADOR: "Costos Mano de Obra (-1 semana)"');
            Costos::mano_de_obra_1_semana_atras($indicador_par);
            Log::info('INDICADOR: "Costos Mano de Obra (-1 semana)"');
        }
        if (in_array($indicador_par, $array_costos_insumos_semanal) || $indicador_par == '0') { // Costos Insumos (-1 semana)
            dump('CALCULAR INDICADOR: "Costos Insumos (-1 semana)"');
            Costos::costos_insumos_1_semana_atras($indicador_par);
            Log::info('INDICADOR: "Costos Insumos (-1 semana)"');
        }
        if (in_array($indicador_par, $array_campo_ha_semana_mensual)) { // Costos Propagacion x tallo (-4 semanas)
            dump('INDICADOR: "Costos Propagacion x tallo (-4 semanas)"');
            Costos::costos_propagacion_tallo_4_semana_atras($indicador_par);
            Log::info('INDICADOR: "Costos Propagacion x tallo (-4 semanas)"');
        }
        if (in_array($indicador_par, $array_costos_cosecha_x_tallo_mensual) || $indicador_par == '0') { // Costos Cultivo x Tallo (-4 semanas)
            dump('INDICADOR: "Costos Cultivo x Tallo (-4 semanas)"');
            Costos::costos_cosecha_tallo_4_semana_atras($indicador_par);
            Log::info('INDICADOR: "Costos Cultivo x Tallo (-4 semanas)"');
        }
        if (in_array($indicador_par, $array_costos_postcosecha_x_tallo_mensual) || $indicador_par == '0') { // Costos Postcosecha x Tallo (-4 semanas)
            dump('INDICADOR: "Costos Postcosecha x Tallo (-4 semanas)"');
            Costos::costos_postcosecha_tallo_4_semana_atras($indicador_par);
            Log::info('INDICADOR: "Costos Postcosecha x Tallo (-4 semanas)"');
        }
        if (in_array($indicador_par, $array_costos_total_x_tallo_mensual) || $indicador_par == '0') { // Costos Total x Tallo (-4 semanas)
            dump('INDICADOR: "Costos Total x Tallo (-4 semanas)"');
            Costos::costos_total_tallo_4_semana_atras($indicador_par);
            Log::info('INDICADOR: "Costos Total x Tallo (-4 semanas)"');
        }
        if (in_array($indicador_par, $array_costos_fijos_semanal) || $indicador_par == '0') { // Costos Fijos (-1 semana)
            dump('INDICADOR: "Costos Fijos (-1 semana)"');
            Costos::costos_fijos_1_semana_atras($indicador_par);
            Log::info('INDICADOR: "Costos Fijos (-1 semana)"');
        }
        if (in_array($indicador_par, $array_costos_regalias_semanal) || $indicador_par == '0') { // Costos Regalías (-1 semana)
            dump('INDICADOR: "Costos Regalías (-1 semana)"');
            Costos::costos_regalias_1_semana_atras($indicador_par);
            Log::info('INDICADOR: "Costos Regalías (-1 semana)"');
        }
        if (in_array($indicador_par, $array_costos_m2_13semnas) || $indicador_par == '0') { // Costos/m2 (-16 semanas)
            dump('INDICADOR: "Costos/m2 (-16 semanas)"');
            Costos::costos_m2_13_semanas_atras($indicador_par);
            Log::info('INDICADOR: "Costos/m2 (-16 semanas)"');
        }
        if (in_array($indicador_par, $array_costos_m2_52semnas) || $indicador_par == '0') { // Costos/m2 (-52 semanas)
            dump('INDICADOR: "Costos/m2 (-52 semanas)"');
            Costos::costos_m2_52_semanas_atras($indicador_par);
            Log::info('INDICADOR: "Costos/m2 (-52 semanas)"');
        }
        if (in_array($indicador_par, $array_costos_x_planta_4semnas) || $indicador_par == '0') { // Costo x Planta (-4 semanas)
            dump('INDICADOR: "Costo x Planta (-4 semanas)"');
            Costos::costos_x_planta_4_semanas_atras($indicador_par);
            Log::info('INDICADOR: "Costo x Planta (-4 semanas)"');
        }
        if (in_array($indicador_par, $array_rentabilidad_1mes) || $indicador_par == '0') { // Rentabilidad (-1 mes)
            dump('INDICADOR: "Rentabilidad (-1 mes)"');
            Costos::rentabilidad_1_mes($indicador_par);
            Log::info('INDICADOR: "Rentabilidad (-1 mes)"');
        }
        if (in_array($indicador_par, $array_rentabilidad_4meses) || $indicador_par == '0') { // Rentabilidad (-4 meses)
            dump('INDICADOR: "Rentabilidad (-4 meses)"');
            Costos::rentabilidad_4_meses($indicador_par);
            Log::info('INDICADOR: "Rentabilidad (-4 meses)"');
        }
        if (in_array($indicador_par, $array_rentabilidad_anual) || $indicador_par == '0') { // Rentabilidad (-1 año)
            dump('INDICADOR: "Rentabilidad (-1 año)"');
            Costos::rentabilidad_1_anno($indicador_par);
            Log::info('INDICADOR: "Rentabilidad (-1 año)"');
        }

        $time_duration = difFechas(date('Y-m-d H:i:s'), $ini)->h . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->m . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->s;
        Log::info('<*> DURACION: ' . $time_duration . '  <*>');
        Log::info('<<<<< * >>>>> Fin satisfactorio del comando "indicador:update" <<<<< * >>>>>');
    }
}
