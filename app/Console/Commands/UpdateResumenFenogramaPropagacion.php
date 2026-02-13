<?php

namespace yura\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use yura\Modelos\CicloCama;
use yura\Modelos\ConfiguracionEmpresa;
use yura\Modelos\ResumenFenogramaPropagacion;

class UpdateResumenFenogramaPropagacion extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:resumen_fenograma_propagacion {fecha=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
     */
    public function handle()
    {
        $ini = date('Y-m-d H:i:s');
        Log::info('<<<<< ! >>>>> Ejecutando comando "update:resumen_fenograma_propagacion" <<<<< ! >>>>>');

        $fecha_par = $this->argument('fecha');
        if ($fecha_par == 0)
            $fecha_par = date('Y-m-d');

        $empresas = ConfiguracionEmpresa::All();
        $variedades = getVariedadesNormales();
        foreach ($empresas as $pos_emp => $empresa) {
            $finca_actual = $empresa->id_configuracion_empresa;
            foreach ($variedades as $pos_var => $var) {
                $ciclos = CicloCama::where('fecha_inicio', '<=', $fecha_par)
                    ->where('fecha_fin', '>=', $fecha_par)
                    ->where('id_variedad', $var->id_variedad)
                    ->where('id_empresa', $finca_actual)
                    ->where('activo', 1)
                    ->get();

                foreach ($ciclos as $pos_c => $c) {
                    dump('finca: ' . ($pos_emp + 1) . '/' . count($empresas) . ' - var: ' . ($pos_var + 1) . '/' . count($variedades) . ' - ciclo: ' . ($pos_c + 1) . '/' . count($ciclos));
                    $model = ResumenFenogramaPropagacion::All()
                        ->where('id_empresa', $finca_actual)
                        ->where('id_variedad', $var->id_variedad)
                        ->where('id_ciclo_cama', $c->id_ciclo_cama)
                        ->first();
                    if ($model == '') {
                        $model = new ResumenFenogramaPropagacion();
                        $model->id_empresa = $finca_actual;
                        $model->id_variedad = $var->id_variedad;
                        $model->id_ciclo_cama = $c->id_ciclo_cama;
                    }
                    $model->id_cama = $c->id_cama;
                    $model->cama_nombre = $c->cama->nombre;
                    $model->id_planta = $var->id_planta;
                    $model->planta_nombre = $var->planta->nombre;
                    $model->planta_siglas = $var->planta->siglas;
                    $model->variedad_nombre = $var->nombre;
                    $model->variedad_siglas = $var->siglas;
                    /* -------------------------------------------------- */
                    $getPlantasProductivas = $c->getPlantasProductivas();
                    $getExquejesCosechados = $c->getEsquejesCosechados();
                    $getExquejesCosechadosByLastSemana = $c->getExquejesCosechadosByLastSemana();
                    $fechaCosecha = $c->getFechaCosecha();
                    $getSemanaActual = $c->semana_vida();
                    $semanas_cosecha = round(difFechas($fechaCosecha, $c->fecha_inicio)->days / 7);
                    $semanas_cosechando = $getSemanaActual - $semanas_cosecha;
                    $semana_fin = getSemanaByDate(opDiasFecha('+', (7 * $c->total_semanas_cosecha), $c->fecha_inicio));
                    /* --------------------------------------------------- */
                    $model->fecha_inicio = $c->fecha_inicio;
                    $model->fecha_fin = $c->fecha_fin;
                    $model->semana_siembra = $c->semana_ini()->codigo;
                    $model->semana_actual = $getSemanaActual;
                    $model->plantas_iniciales = $getPlantasProductivas;
                    $model->cosecha = $getExquejesCosechados > 0 ? $getExquejesCosechados : 0;
                    $model->semana_cosecha = $fechaCosecha != '' ? $semanas_cosecha : 0;
                    $model->esq_x_sem = $getPlantasProductivas > 0 ? round($getExquejesCosechadosByLastSemana / $getPlantasProductivas, 2) : 0;
                    $model->esq_x_sem_acum = ($fechaCosecha != '' && $getPlantasProductivas > 0 && $semanas_cosechando > 0) ? round(($getExquejesCosechados / $semanas_cosechando) / $getPlantasProductivas, 2) : 0;
                    $model->esq_x_planta = $getPlantasProductivas > 0 ? round($getExquejesCosechados / $getPlantasProductivas, 2) : 0;
                    $model->fin_produccion = $semana_fin != '' ? $semana_fin->codigo : null;
                    $model->conteo = $c->esq_x_planta;
                    $model->save();
                }
            }
        }

        $time_duration = difFechas(date('Y-m-d H:i:s'), $ini)->h . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->m . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->s;
        dump('<*> DURACION: ' . $time_duration . '  <*>');
        dump('<<<<< * >>>>> Fin satisfactorio del comando "update:resumen_fenograma_propagacion" <<<<< * >>>>>');
        Log::info('<*> DURACION: ' . $time_duration . '  <*>');
        Log::info('<<<<< * >>>>> Fin satisfactorio del comando "update:resumen_fenograma_propagacion" <<<<< * >>>>>');
    }
}
