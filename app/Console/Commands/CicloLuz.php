<?php

namespace yura\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use yura\Modelos\Ciclo;
use yura\Modelos\CicloLuz as Modelo;

class CicloLuz extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ciclo:luz';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comando para actualizar la tabla ciclo_luz usando el ultimo registro de cada ciclo';

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
        dump('<<<<< ! >>>>> Ejecutando comando "ciclo:luz" <<<<< ! >>>>>');
        Log::info('<<<<< ! >>>>> Ejecutando comando "ciclo:luz" <<<<< ! >>>>>');

        $ciclos = Ciclo::join('variedad as v', 'v.id_variedad', '=', 'ciclo.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->select('ciclo.*')->distinct()
            ->where('ciclo.estado', 1)
            ->where('v.estado', 1)
            ->where('p.estado', 1)
            ->where('ciclo.activo', 1)
            ->where('p.tiene_ciclos', 1)
            //->where('ciclo.id_empresa', $finca)
            ->orderBy('v.nombre')
            ->orderBy('ciclo.fecha_inicio')
            ->get();
        foreach ($ciclos as $pos_c => $c) {
            dump('ciclo: ' . $pos_c . '/' . count($ciclos));
            $model = Modelo::All()
                ->where('id_ciclo', $c->id_ciclo)
                ->where('fecha', hoy())
                ->first();
            if ($model == '') {
                $ids_ciclos = [];
                foreach ($c->modulo->ciclos as $ci)
                    array_push($ids_ciclos, $ci->id_ciclo);
                $last_luz = Modelo::whereIn('id_ciclo', $ids_ciclos)
                    ->orderBy('fecha', 'desc')
                    ->get()
                    ->first();

                if (isset($last_luz)) {
                    $model = new Modelo();
                    $model->id_ciclo = $c->id_ciclo;
                    $model->fecha = hoy();
                    $model->tipo_luz = isset($last_luz) ? $last_luz->tipo_luz : 250;
                    $model->lamparas = isset($last_luz) ? $last_luz->lamparas : 25;
                    if ($c->poda_siembra == 'P')
                        $model->inicio_luz = isset($last_luz) ? $last_luz->inicio_luz : 42;
                    else
                        $model->inicio_luz = isset($last_luz) ? $last_luz->inicio_luz : 35;
                    $model->dias_adicional = 0;
                    if (isset($last_luz))
                        $model->dias_adicional = $last_luz->id_ciclo == $model->id_ciclo ? $last_luz->dias_adicional : 0;
                    $model->dias_proy = isset($last_luz) ? $last_luz->dias_proy : 41;
                    $model->hora_ini = isset($last_luz) ? $last_luz->hora_ini : '22:00';
                    $model->hora_fin = isset($last_luz) ? $last_luz->hora_fin : '06:00';
                    $model->save();
                }
            }
        }

        $time_duration = difFechas(date('Y-m-d H:i:s'), $ini)->h . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->m . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->s;
        dump('<*> DURACION: ' . $time_duration . '  <*>');
        dump('<<<<< * >>>>> Fin satisfactorio del comando "ciclo:luz" <<<<< * >>>>>');
        Log::info('<*> DURACION: ' . $time_duration . '  <*>');
        Log::info('<<<<< * >>>>> Fin satisfactorio del comando "ciclo:luz" <<<<< * >>>>>');
    }
}
