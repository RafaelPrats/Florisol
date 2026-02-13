<?php

namespace yura\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use yura\Modelos\Ciclo;
use yura\Modelos\ResumenFenogramaEjecucion;

class UpdateResumenFenogramaEjecucion extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:resumen_fenograma_ejecucion {fecha=0} {modulo=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comando para actualizar la tabla resumen_fenograma_ejecucion';

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
        dump('<<<<< ! >>>>> Ejecutando comando "update:resumen_fenograma_ejecucion" <<<<< ! >>>>>');
        Log::info('<<<<< ! >>>>> Ejecutando comando "update:resumen_fenograma_ejecucion" <<<<< ! >>>>>');

        $fecha_par = $this->argument('fecha');
        $modulo_par = $this->argument('modulo');
        if ($fecha_par == 0)
            $fecha_par = date('Y-m-d');

        $ciclos = Ciclo::where('ciclo.estado', 1)
            ->where('ciclo.fecha_inicio', '<=', $fecha_par)
            ->where('ciclo.fecha_fin', '>=', $fecha_par);
        if ($modulo_par != 0)
            $ciclos = $ciclos->where('ciclo.id_modulo', $modulo_par);
        $ciclos = $ciclos->get();

        /*$codigo_semana = $ciclos[0]->semana()->codigo;
        $area = 0;*/

        foreach ($ciclos as $pos_item => $item) {
            dump('ciclo: ' . ($pos_item + 1) . '/' . count($ciclos));
            $semana = $item->semana();
            $poda_siembra = $item->num_poda_siembra != '' ? $item->num_poda_siembra : $item->modulo->getPodaSiembraByCiclo($item->id_ciclo);
            $tallos_cosechados = $item->getTallosCosechados();

            $desecho = $item->desecho > 0 ? $item->desecho : $semana->desecho;
            $desecho = $desecho > 0 ? $desecho : 20;

            $conteo = $item->conteo;
            if ($item->conteo <= 0)
                if ($poda_siembra > 0)
                    $conteo = $semana->tallos_planta_poda;
                else
                    $conteo = $semana->tallos_planta_siembra;

            $plantas_actuales = $item->plantas_actuales();
            $getDensidadIniciales = $item->getDensidadIniciales();
            $tallos_m2_cos = $item->area > 0 ? round($tallos_cosechados / $item->area, 2) : 0;
            $tallos_m2_proy = $item->area > 0 ? round((($plantas_actuales * $conteo) * ((100 - $desecho) / 100)) / $item->area, 2) : 0;

            $model = ResumenFenogramaEjecucion::All()->where('id_ciclo', $item->id_ciclo)->first();
            if ($model == '') {
                $model = new ResumenFenogramaEjecucion();
                $model->id_ciclo = $item->id_ciclo;
            }
            $model->id_modulo = $item->id_modulo;
            $model->nombre_modulo = $item->modulo->nombre;
            $model->fecha_inicio = $item->fecha_inicio;
            $model->fecha_fin = $item->fecha_fin;
            $model->semana = $semana->codigo;
            $model->id_variedad = $item->id_variedad;
            $model->siglas_variedad = $item->variedad->siglas;
            $model->nombre_variedad = $item->variedad->nombre;
            $model->poda_siembra = $poda_siembra;
            $model->dias = $item->fecha_fin != '' ? difFechas($item->fecha_fin, $item->fecha_inicio)->days : difFechas(date('Y-m-d'), $item->fecha_inicio)->days;
            $model->area_m2 = $item->area;

            $model->total_x_semana_m2 = 0;

            $model->primera_flor = $item->fecha_cosecha != '' ? difFechas($item->fecha_cosecha, $item->fecha_inicio)->days : null;
            $model->porciento_mortalidad = $item->getMortalidad();
            $model->tallos_cosechados = $tallos_cosechados;
            $model->real_tallos_m2 = $tallos_m2_cos;
            $model->porciento_cosechado = $tallos_m2_proy > 0 ? round(($tallos_m2_cos / $tallos_m2_proy) * 100, 2) : 0;
            $model->proy_tallos_m2 = $tallos_m2_proy;
            $model->plantas_iniciales = $item->plantas_iniciales > 0 ? $item->plantas_iniciales : 0;
            $model->plantas_actuales = $plantas_actuales > 0 ? $plantas_actuales : 0;
            $model->plantas_muertas = $item->plantas_muertas > 0 ? $item->plantas_muertas : 0;
            $model->densidad_plantas_ini_m2 = $getDensidadIniciales;
            $model->conteo = $conteo > 0 ? $conteo : 1;
            $model->id_planta = $item->variedad->id_planta;
            $model->siglas_planta = $item->variedad->planta->siglas;
            $model->nombre_planta = $item->variedad->planta->nombre;
            $model->desecho = $desecho;

            $model->save();
        }

        $time_duration = difFechas(date('Y-m-d H:i:s'), $ini)->h . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->m . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->s;
        dump('<*> DURACION: ' . $time_duration . '  <*>');
        dump('<<<<< * >>>>> Fin satisfactorio del comando "update:resumen_fenograma_ejecucion" <<<<< * >>>>>');
        Log::info('<*> DURACION: ' . $time_duration . '  <*>');
        Log::info('<<<<< * >>>>> Fin satisfactorio del comando "update:resumen_fenograma_ejecucion" <<<<< * >>>>>');
    }
}
