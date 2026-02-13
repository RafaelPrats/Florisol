<?php

namespace yura\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class cronUpdateVariedad extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:variedades {fecha=0} {variedad=0} {dev=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comando para actualizar los tallos de ventas de las variedades, o de una. En una fecha';

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
        $ini_x = date('Y-m-d H:i:s');
        $dev = $this->argument('dev');
        if ($dev == 0) {
            dump('<<<<< ! >>>>> Ejecutando comando "resumen:fecha" <<<<< ! >>>>>');
            Log::info('<<<<< ! >>>>> Ejecutando comando "resumen:fecha" <<<<< ! >>>>>');
        }
        $fecha = $this->argument('fecha');
        $variedad = $this->argument('variedad');
        $dev = $this->argument('dev');
        if ($fecha == 0)
            $fecha = hoy();

        if ($variedad == 0) {
            $combinaciones_compra_flor = DB::table('desglose_compra_flor as i')
                ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
                ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                ->select(
                    'i.id_variedad',
                    'v.nombre as variedad_nombre',
                    'p.nombre as planta_nombre',
                    'v.dias_rotacion_recepcion',
                )->distinct()
                ->where('fecha', '>', hoy())
                ->orderBy('p.nombre')
                ->orderBy('v.nombre')
                ->get();
            $ids_variedad_compra_flor = $combinaciones_compra_flor->pluck('id_variedad')->toArray();
            $combinaciones_recepcion = DB::table('desglose_recepcion as i')
                ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
                ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                ->select(
                    'i.id_variedad',
                    'v.nombre as variedad_nombre',
                    'p.nombre as planta_nombre',
                    'v.dias_rotacion_recepcion',
                )->distinct()
                ->where('i.disponibles', '>', 0)
                ->where('i.estado', 1)
                ->whereNotIn('i.id_variedad', $ids_variedad_compra_flor)
                ->orderBy('p.nombre')
                ->orderBy('v.nombre')
                ->get();
            $ids_variedad_recepcion = $combinaciones_recepcion->pluck('id_variedad')->toArray();
            $combinaciones_pedido = DB::table('resumen_fechas as h')
                ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                ->select(
                    'h.id_variedad',
                    'v.nombre as variedad_nombre',
                    'p.nombre as planta_nombre',
                )->distinct()
                ->where('h.fecha', $fecha)
                ->where('h.tallos_venta', '>', 0)
                ->whereNotIn('h.id_variedad', $ids_variedad_compra_flor)
                ->whereNotIn('h.id_variedad', $ids_variedad_recepcion)
                ->orderBy('p.nombre')
                ->orderBy('v.nombre')
                ->get();
            $variedades = $combinaciones_compra_flor->merge($combinaciones_pedido)
                ->merge($combinaciones_recepcion)
                ->pluck('id_variedad')->toArray();
        } else {
            $variedades = [$variedad];
        }

        foreach ($variedades as $i_var => $var) {
            if ($dev == 1) {
                dump('var: ' . $i_var . '/' . count($variedades) . '; id"' . $var . '"');
                Artisan::call('resumen:fecha', [
                    'fecha' => $fecha,
                    'variedad' => $var,
                    'dev' => 1,
                ]);
            }
        }

        $time_duration_x = difFechas(date('Y-m-d H:i:s'), $ini_x)->h . ':' . difFechas(date('Y-m-d H:i:s'), $ini_x)->m . ':' . difFechas(date('Y-m-d H:i:s'), $ini_x)->s;
        if ($dev == 1) {
            dump('<*> DURACION: ' . $time_duration_x . '  <*>');
            dump('<<<<< * >>>>> Fin satisfactorio del comando "resumen:fecha" <<<<< * >>>>>');
            Log::info('<*> DURACION: ' . $time_duration_x . '  <*>');
            Log::info('<<<<< * >>>>> Fin satisfactorio del comando "resumen:fecha" <<<<< * >>>>>');
        }
    }
}
