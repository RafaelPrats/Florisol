<?php

namespace yura\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use yura\Jobs\jobActualizarSemProyPerenne;
use yura\Modelos\ConfiguracionEmpresa;
use yura\Modelos\Semana;

class cronActualizarProyPerennes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:proy_perenne {fecha=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comando para actualizar las proyecciones de perennes de una fecha segun las variedades que se cosecharon';

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
        Log::info('<<<<< ! >>>>> Ejecutando comando "update:proy_perenne" <<<<< ! >>>>>');
        dump('<<<<< ! >>>>> Ejecutando comando "update:proy_perenne" <<<<< ! >>>>>');

        $fecha_par = $this->argument('fecha');
        $fecha_par = $fecha_par != 0 ? $fecha_par : hoy();

        $semana = getSemanaByDate($fecha_par);
        $empresas = ConfiguracionEmpresa::All();
        foreach ($empresas as $pos_emp => $empresa) {
            $finca = $empresa->id_configuracion_empresa;
            $variedades = DB::table('desglose_recepcion as dr')
                ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
                ->select('dr.id_variedad')->distinct()
                ->where('dr.estado', 1)
                ->where('r.estado', 1)
                ->where('dr.id_empresa', $finca)
                //->where('r.fecha_ingreso', 'like', $fecha_par . '%')
                ->where('r.fecha_ingreso', '>=', $fecha_par . ' 00:00:00')
                ->get();
            foreach ($variedades as $pos_var => $var) {
                $semanas = DB::select('select * from semana where id_variedad = ' . $var->id_variedad . ' and codigo >= ' . $semana->codigo);
                foreach ($semanas as $pos_sem => $sem) {
                    dump('finca: ' . ($pos_emp + 1) . '/' . count($empresas) .
                        ' - var: ' . ($pos_var + 1) . '/' . count($variedades) .
                        ' - sem: ' . ($pos_sem + 1) . '/' . count($semanas));
                    jobActualizarSemProyPerenne::dispatch($sem->codigo, $sem->id_variedad, $finca)
                        ->onConnection('sync');
                }
            }
        }

        $time_duration = difFechas(date('Y-m-d H:i:s'), $ini)->h . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->m . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->s;
        Log::info('<*> DURACION: ' . $time_duration . '  <*>');
        Log::info('<<<<< * >>>>> Fin satisfactorio del comando "update:proy_perenne" <<<<< * >>>>>');
        dump('<*> DURACION: ' . $time_duration . '  <*>');
        dump('<<<<< * >>>>> Fin satisfactorio del comando "update:proy_perenne" <<<<< * >>>>>');
    }
}
