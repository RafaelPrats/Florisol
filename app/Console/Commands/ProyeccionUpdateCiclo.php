<?php

namespace yura\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use yura\Modelos\Ciclo;
use yura\Modelos\ProyeccionModulo;
use yura\Modelos\Semana;

class ProyeccionUpdateCiclo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'proyeccion:update_ciclo {id_ciclo} {semana_poda_siembra} {curva} {poda_siembra} {plantas_iniciales} {plantas_muertas} {desecho} {conteo} {area} {no_recalcular_curva}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Commando para actualizar los datos en las tablas Ciclo y Proyeccion_Modulo';

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
        Log::info('<<<<< ! >>>>> Ejecutando comando "proyeccion:update_ciclo" <<<<< ! >>>>>');
        $par_id_ciclo = $this->argument('id_ciclo');
        $par_semana_poda_siembra = $this->argument('semana_poda_siembra');
        $par_curva = $this->argument('curva');
        $par_poda_siembra = $this->argument('poda_siembra');
        $par_plantas_iniciales = $this->argument('plantas_iniciales');
        $par_plantas_muertas = $this->argument('plantas_muertas');
        $par_desecho = $this->argument('desecho');
        $par_conteo = $this->argument('conteo');
        $par_area = $this->argument('area');
        $par_no_recalcular_curva = $this->argument('no_recalcular_curva');

        $model = Ciclo::find($par_id_ciclo);

        $model->poda_siembra = $par_poda_siembra;
        $model->curva = $par_curva;
        $model->semana_poda_siembra = $par_semana_poda_siembra;
        $model->plantas_iniciales = $par_plantas_iniciales;
        $model->plantas_muertas = $par_plantas_muertas;
        $model->desecho = $par_desecho;
        $model->conteo = $par_conteo;
        $model->area = $par_area;
        $model->no_recalcular_curva = $par_no_recalcular_curva;

        $model->save();
        bitacora('ciclo', $model->id_ciclo, 'U', 'Actualización satisfactoria de un ciclo');

        $poda_siembra = $model->modulo->getPodaSiembraByCiclo($model->id_ciclo);

        /* ------------------------ OBTENER LA SEMANA DONDE TERMINA EL CICLO ---------------------- */
        $semana_cosecha = $model->semana_poda_siembra;
        $semanas_curva = count(explode('-', $model->curva));
        $cant_semanas_ciclo = $semana_cosecha + $semanas_curva - 1;
        $semana_next_proy = getSemanaByDateVariedad(opDiasFecha('+', $cant_semanas_ciclo * 7, $model->fecha_inicio), $model->id_variedad);

        if ($semana_next_proy != '') {
            $proy = ProyeccionModulo::where('estado', 1)
                ->where('id_modulo', $model->id_modulo)
                ->where('id_variedad', $model->id_variedad)
                ->orderBy('fecha_inicio')
                ->get()->first();
            if ($proy != '') {
                $proy->id_semana = $semana_next_proy->id_semana;
                $proy->fecha_inicio = $semana_next_proy->fecha_final;
                $proy->desecho = $semana_next_proy->desecho > 0 ? $semana_next_proy->desecho : 0;
                $proy->tallos_planta = $semana_next_proy->tallos_planta_poda > 0 ? $semana_next_proy->tallos_planta_poda : 0;
                $proy->tallos_ramo = $semana_next_proy->tallos_ramo_poda > 0 ? $semana_next_proy->tallos_ramo_poda : 0;

                $proy->poda_siembra = $proy->tipo != 'S' ? $poda_siembra + 1 : 0;

                $proy->save();
                $proy->restaurar_proyecciones();
            }
        } else {
            /* ======================== QUITAR PROYECCIONES ======================= */
            $proys = ProyeccionModulo::where('estado', 1)
                ->where('id_modulo', $model->id_modulo)
                ->where('id_variedad', $model->id_variedad)
                ->orderBy('fecha_inicio')
                ->get();
            foreach ($proys as $proy)
                $proy->delete();
        }
        Log::info('<<<<< * >>>>> Fin satisfactorio del comando "proyeccion:update_ciclo" <<<<< * >>>>>');
    }
}
