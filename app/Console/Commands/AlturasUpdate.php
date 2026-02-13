<?php

namespace yura\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use yura\Modelos\Ciclo;
use yura\Modelos\Monitoreo;

class AlturasUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'alturas:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comando para guardar automaticamente en 0 las alturas semanales faltantes';

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
        Log::info('<<<<< ! >>>>> Ejecutando comando "alturas:update" <<<<< ! >>>>>');
        dump('<<<<< ! >>>>> Ejecutando comando "alturas:update" <<<<< ! >>>>>');

        $query = Ciclo::join('variedad as v', 'v.id_variedad', '=', 'ciclo.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->select('ciclo.*')->distinct()
            ->where('ciclo.estado', 1)
            ->where('ciclo.activo', 1)
            ->where('p.tipo', 'N')
            ->orderBy('ciclo.fecha_inicio', 'desc')
            ->get();    // ciclos activos

        foreach ($query as $pos => $item) {
            $num_sem = intval(difFechas($item->fecha_inicio, date('Y-m-d'))->days / 7);
            $max_mon = DB::table('monitoreo')
                ->select(DB::raw('max(num_sem) as maximo'))
                ->where('id_ciclo', $item->id_ciclo)
                ->where('estado', 1)
                ->get()[0]->maximo;
            if ($max_mon == '' || $max_mon < $num_sem) {
                for ($i = $max_mon + 1; $i <= $num_sem; $i++) {
                    dump('ciclo: ' . ($pos + 1) . '/' . count($query) . ' - sem: ' . $i . '/' . $num_sem);
                    $altura = Monitoreo::All()
                        ->where('estado', 1)
                        ->where('id_ciclo', $item->id_ciclo)
                        ->where('num_sem', $i)
                        ->first();
                    if ($altura == '') {
                        $altura = new Monitoreo();
                        $altura->id_ciclo = $item->id_ciclo;
                        $altura->num_sem = $i;
                        $altura->altura = 0;
                        $altura->save();
                    }
                }
            }
        }

        $time_duration = difFechas(date('Y-m-d H:i:s'), $ini)->h . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->m . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->s;
        dump('<*> DURACION: ' . $time_duration . '  <*>');
        dump('<<<<< * >>>>> Fin satisfactorio del comando "alturas:update" <<<<< * >>>>>');
        Log::info('<*> DURACION: ' . $time_duration . '  <*>');
        Log::info('<<<<< * >>>>> Fin satisfactorio del comando "alturas:update" <<<<< * >>>>>');
    }
}
