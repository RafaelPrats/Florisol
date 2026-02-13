<?php

namespace yura\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use yura\Modelos\ConfiguracionEmpresa;
use yura\Modelos\CosechaDiaria;

class UpdateCosechaDiaria extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:cosecha_diaria {desde=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comando para actualizar la tabla cosecha_diaria';

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
        Log::info('<<<<< ! >>>>> Ejecutando comando "update:cosecha_diaria" <<<<< ! >>>>>');

        $desde_par = $this->argument('desde') != 0 ? $this->argument('desde') : opDiasFecha('-', 7, date('Y-m-d'));

        $fechas = DB::table('desglose_recepcion as dr')
            ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
            ->select('r.fecha_ingreso')->distinct()
            ->where('r.estado', 1)
            ->where('r.fecha_ingreso', '>=', $desde_par)
            ->where('r.fecha_ingreso', '<=', hoy() . ' 23:59:59')
            ->get();
        $variedades = DB::table('desglose_recepcion as dr')
            ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
            ->join('variedad as v', 'v.id_variedad', '=', 'dr.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->select('dr.id_variedad', 'v.id_planta', 'v.nombre as variedad_nombre', 'p.nombre as planta_nombre')->distinct()
            ->where('r.estado', 1)
            ->where('r.fecha_ingreso', '>=', $desde_par)
            ->where('r.fecha_ingreso', '<=', hoy() . ' 23:59:59')
            ->get();
        $sectores = DB::table('desglose_recepcion as dr')
            ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
            ->join('modulo as m', 'm.id_modulo', '=', 'dr.id_modulo')
            ->select('m.id_sector')->distinct()
            ->where('r.estado', 1)
            ->where('m.estado', 1)
            ->where('r.fecha_ingreso', '>=', $desde_par)
            ->where('r.fecha_ingreso', '<=', hoy() . ' 23:59:59')
            ->get();
        $empresas = ConfiguracionEmpresa::All();
        foreach ($empresas as $pos_e => $empresa) {
            $finca = $empresa->id_configuracion_empresa;
            foreach ($variedades as $pos_v => $var) {
                foreach ($sectores as $pos_s => $sec) {
                    foreach ($fechas as $pos_f => $f) {
                        dump('finca: ' . ($pos_e + 1) . '/' . count($empresas) . ' - var: ' . ($pos_v + 1) . '/' . count($variedades) . ' - sec: ' . ($pos_s + 1) . '/' . count($sectores) . ' - fecha: ' . ($pos_f + 1) . '/' . count($fechas));
                        $f = substr($f->fecha_ingreso, 0, 10);
                        $model = CosechaDiaria::All()
                            ->where('id_empresa', $finca)
                            ->where('id_variedad', $var->id_variedad)
                            ->where('id_sector', $sec->id_sector)
                            ->where('fecha', $f)
                            ->first();
                        if ($model == '') {
                            $model = new CosechaDiaria();
                            $model->id_empresa = $finca;
                            $model->id_variedad = $var->id_variedad;
                            $model->id_sector = $sec->id_sector;
                            $model->fecha = $f;
                        }
                        $model->id_planta = $var->id_planta;
                        $model->variedad_nombre = $var->variedad_nombre;
                        $model->planta_nombre = $var->planta_nombre;
                        $cosechados = DB::table('desglose_recepcion as dr')
                            ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
                            ->join('modulo as m', 'm.id_modulo', '=', 'dr.id_modulo')
                            ->select(DB::raw('sum(dr.cantidad_mallas * dr.tallos_x_malla) as cantidad'))
                            ->where('dr.estado', 1)
                            ->where('dr.id_variedad', $var->id_variedad)
                            ->where('m.id_sector', $sec->id_sector)
                            ->where('dr.id_empresa', $finca)
                            ->where('r.estado', 1)
                            ->where('r.fecha_ingreso', 'like', $f . '%')
                            ->get()[0]->cantidad;
                        $model->cosechados = $cosechados > 0 ? $cosechados : 0;
                        $model->save();
                    }
                }
            }
        }

        $time_duration = difFechas(date('Y-m-d H:i:s'), $ini)->h . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->m . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->s;
        dump('<*> DURACION: ' . $time_duration . '  <*>');
        dump('<<<<< * >>>>> Fin satisfactorio del comando "update:cosecha_diaria" <<<<< * >>>>>');
        Log::info('<*> DURACION: ' . $time_duration . '  <*>');
        Log::info('<<<<< * >>>>> Fin satisfactorio del comando "update:cosecha_diaria" <<<<< * >>>>>');
    }
}
