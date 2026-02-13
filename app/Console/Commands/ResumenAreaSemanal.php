<?php

namespace yura\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use yura\Modelos\ConfiguracionEmpresa;
use yura\Modelos\Semana;
use yura\Modelos\ResumenAreaSemanal as ResumenArea;
use yura\Modelos\Variedad;

class ResumenAreaSemanal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'area:update_semanal {semana_desde=0} {semana_hasta=0} {variedad=0} {finca=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comando para actualizar semanalmente la info sobre el área';

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
        dump('<<<<< ! >>>>> Ejecutando comando "area:update_semanal" <<<<< ! >>>>>');
        Log::info('<<<<< ! >>>>> Ejecutando comando "area:update_semanal" <<<<< ! >>>>>');

        $semana_actual = getSemanaByDate(date('Y-m-d'));

        $desde_par = $this->argument('semana_desde') != 0 ? $this->argument('semana_desde') : $semana_actual->codigo;
        $hasta_par = $this->argument('semana_hasta') != 0 ? $this->argument('semana_hasta') : $semana_actual->codigo;
        $variedad_par = $this->argument('variedad');
        $finca_par = $this->argument('finca');

        $fincas = ConfiguracionEmpresa::All();
        if ($finca_par != 0)
            $fincas = $fincas->where('id_configuracion_empresa', $finca_par);

        $semanas = DB::table('semana')
            ->select('codigo', 'fecha_inicial', 'fecha_final')->distinct()
            ->where('codigo', '>=', $desde_par)
            ->where('codigo', '<=', $hasta_par)
            ->orderBy('codigo')
            ->get();

        $desde_par = getObjSemana($desde_par);
        $hasta_par = getObjSemana($hasta_par);

        foreach ($fincas as $pos_finca => $emp) {
            $finca = $emp->id_configuracion_empresa;
            $variedades = DB::table('ciclo as c')
                ->join('variedad as v', 'v.id_variedad', '=', 'c.id_variedad')
                ->select('c.id_variedad', 'v.nombre')->distinct()
                ->where('c.estado', 1)
                ->where('v.estado', 1)
                ->where('c.id_empresa', $finca)
                ->Where(function ($q) use ($desde_par, $hasta_par) {
                    $q->where('c.fecha_fin', '>=', $desde_par->fecha_inicial)
                        ->where('c.fecha_fin', '<=', $hasta_par->fecha_final)
                        ->orWhere(function ($q) use ($desde_par, $hasta_par) {
                            $q->where('c.fecha_inicio', '>=', $desde_par->fecha_inicial)
                                ->where('c.fecha_inicio', '<=', $hasta_par->fecha_final);
                        })
                        ->orWhere(function ($q) use ($desde_par, $hasta_par) {
                            $q->where('c.fecha_inicio', '<', $desde_par->fecha_inicial)
                                ->where('c.fecha_fin', '>', $hasta_par->fecha_final);
                        });
                });
            if ($variedad_par != 0)
                $variedades = $variedades->where('c.id_variedad', $variedad_par);
            $variedades = $variedades->get();
            foreach ($variedades as $pos_var => $var) {
                foreach ($semanas as $pos_sem => $semana) {
                    dump('finca: ' . $emp->nombre . '; var: ' . $var->nombre . '; sem: ' . $semana->codigo);
                    dump('finca: ' . ($pos_finca + 1) . '/' . count($fincas) .
                        '; var: ' . ($pos_var + 1) . '/' . count($variedades) .
                        '; sem: ' . ($pos_sem + 1) . '/' . count($semanas));
                    $model = ResumenArea::All()
                        ->where('estado', 1)
                        ->where('id_empresa', $finca)
                        ->where('id_variedad', $var->id_variedad)
                        ->where('codigo_semana', $semana->codigo)
                        ->first();
                    if ($model == '') {
                        $model = new ResumenArea();
                        $model->id_variedad = $var->id_variedad;
                        $model->id_empresa = $finca;
                        $model->codigo_semana = $semana->codigo;
                    }

                    $area = DB::table('ciclo as c')
                        ->select(DB::raw('sum(c.area) as area'))
                        ->where('c.estado', 1)
                        ->where('c.id_empresa', $finca)
                        ->where('c.id_variedad', $var->id_variedad)
                        ->Where(function ($q) use ($semana) {
                            $q->where('c.fecha_fin', '>=', $semana->fecha_inicial)
                                ->where('c.fecha_fin', '<=', $semana->fecha_final)
                                ->orWhere(function ($q) use ($semana) {
                                    $q->where('c.fecha_inicio', '>=', $semana->fecha_inicial)
                                        ->where('c.fecha_inicio', '<=', $semana->fecha_final);
                                })
                                ->orWhere(function ($q) use ($semana) {
                                    $q->where('c.fecha_inicio', '<', $semana->fecha_inicial)
                                        ->where('c.fecha_fin', '>', $semana->fecha_final);
                                });
                        })
                        ->get()[0]->area;
                    $area = $area > 0 ? $area : 0;

                    $data_ciclos = getCiclosCerradosByRango($semana->codigo, $semana->codigo, $var->id_variedad, true, $finca);
                    $ciclo = $data_ciclos['ciclo'];
                    $tallos_m2 = $data_ciclos['area_cerrada'] > 0 ? round($data_ciclos['tallos_cosechados'] / $data_ciclos['area_cerrada'], 2) : 0;

                    $model->area = $area;
                    $model->ciclo = $ciclo;
                    $model->tallos_m2 = $tallos_m2;
                    $model->ramos_m2 = 0;
                    $model->ramos_m2_anno = 0;
                    $model->save();
                }
            }
        }

        $time_duration = difFechas(date('Y-m-d H:i:s'), $ini)->h . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->m . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->s;
        dump('<*> DURACION: ' . $time_duration . '  <*>');
        dump('<<<<< * >>>>> Fin satisfactorio del comando "area:update_semanal" <<<<< * >>>>>');
        Log::info('<*> DURACION: ' . $time_duration . '  <*>');
        Log::info('<<<<< * >>>>> Fin satisfactorio del comando "area:update_semanal" <<<<< * >>>>>');
    }
}