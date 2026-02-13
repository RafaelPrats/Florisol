<?php

namespace yura\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use yura\Modelos\Actividad;
use yura\Modelos\Area;
use yura\Modelos\ConfiguracionEmpresa;
use yura\Modelos\PropagDisponibilidad;
use yura\Modelos\ResumenPropagacion;
use yura\Modelos\Semana;
use yura\Modelos\Variedad;

class UpdateResumenPropagacion extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:resumen_propagacion {desde=0} {hasta=0} {variedad=0} {empresa=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comando para actualizar la tabla resumen_propagacion';

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
        Log::info('<<<<< ! >>>>> Ejecutando comando "update:resumen_propagacion" <<<<< ! >>>>>');

        $desde_par = $this->argument('desde');
        $hasta_par = $this->argument('hasta');
        $variedad_par = $this->argument('variedad');
        $empresa_parametro = $this->argument('empresa');

        if ($desde_par <= $hasta_par) {
            if ($desde_par == 0)
                $desde_par = getSemanaByDate(opDiasFecha('-', 42, date('Y-m-d')))->codigo;
            if ($hasta_par == 0)
                $hasta_par = getSemanaByDate(date('Y-m-d'))->codigo;

            $semanas = DB::table('semana')
                ->select('codigo', 'fecha_inicial', 'fecha_final')->distinct()
                ->where('codigo', '>=', $desde_par)
                ->where('codigo', '<=', $hasta_par)
                ->orderBy('codigo')
                ->get();

            $empresas = ConfiguracionEmpresa::All();
            if ($empresa_parametro != 0)
                $empresas = $empresas->where('id_configuracion_empresa', $empresa_parametro);

            $variedades = Variedad::All()->where('estado', 1);
            if ($variedad_par != 0)
                $variedades = $variedades->where('id_variedad', $variedad_par);

            foreach ($empresas as $pos_emp => $emp) {
                $finca = $emp->id_configuracion_empresa;
                foreach ($semanas as $pos_sem => $sem) {
                    $esquejes_cosechados_semanal = DB::table('cosecha_plantas_madres as cos')
                        ->select(DB::raw('sum(cantidad) as cantidad'))
                        ->where('cos.fecha', '>=', $sem->fecha_inicial)
                        ->where('cos.fecha', '<=', $sem->fecha_final)
                        ->where('cos.id_empresa', $finca)
                        ->get()[0]->cantidad;
                    $esquejes_cosechados_semanal = $esquejes_cosechados_semanal != '' ? $esquejes_cosechados_semanal : 0;
                    foreach ($variedades as $pos_var => $var) {
                        dump('finca: ' . ($pos_emp + 1) . '/' . count($empresas) . ' - sem: ' . ($pos_sem + 1) . '/' . count($semanas) . ' - var: ' . ($pos_var + 1) . '/' . count($variedades));
                        $model = ResumenPropagacion::All()
                            ->where('id_variedad', $var->id_variedad)
                            ->where('semana', $sem->codigo)
                            ->where('id_empresa', $finca)
                            ->first();
                        if ($model == '') {
                            $model = new ResumenPropagacion();
                            $model->id_variedad = $var->id_variedad;
                            $model->semana = $sem->codigo;
                            $model->id_empresa = $finca;
                        }
                        /* esquejes_cosechados */
                        $esquejes_cosechados = DB::table('cosecha_plantas_madres as cos')
                            ->select(DB::raw('sum(cantidad) as cantidad'))
                            ->where('cos.id_variedad', $var->id_variedad)
                            ->where('cos.fecha', '>=', $sem->fecha_inicial)
                            ->where('cos.fecha', '<=', $sem->fecha_final)
                            ->where('cos.id_empresa', $finca)
                            ->get()[0]->cantidad;
                        $model->esquejes_cosechados = $esquejes_cosechados != '' ? $esquejes_cosechados : 0;
                        /* plantas_sembradas */
                        $desde = $sem->fecha_inicial;
                        $hasta = $sem->fecha_final;
                        $plantas_sembradas = DB::table('ciclo_cama_contenedor as ccc')
                            ->join('ciclo_cama as cc', 'cc.id_ciclo_cama', '=', 'ccc.id_ciclo_cama')
                            ->join('contenedor_propag as cp', 'cp.id_contenedor_propag', '=', 'ccc.id_contenedor_propag')
                            ->select(DB::raw('sum(ccc.cantidad * cp.cantidad) as cantidad'))
                            ->where('cc.id_variedad', $var->id_variedad)
                            ->where('cc.id_empresa', $finca)
                            ->Where(function ($q) use ($desde, $hasta) {
                                $q->where('cc.fecha_fin', '>=', $desde)
                                    ->where('cc.fecha_fin', '<=', $hasta)
                                    ->orWhere(function ($q) use ($desde, $hasta) {
                                        $q->where('cc.fecha_inicio', '>=', $desde)
                                            ->where('cc.fecha_inicio', '<=', $hasta);
                                    })
                                    ->orWhere(function ($q) use ($desde, $hasta) {
                                        $q->where('cc.fecha_inicio', '<', $desde)
                                            ->where('cc.fecha_fin', '>', $hasta);
                                    });
                            })
                            ->get()[0]->cantidad;
                        //dump($emp->nombre.' - '.$sem->codigo.' - '.$var->nombre.' - Ptas Sembradas: '.$plantas_sembradas);
                        $model->plantas_sembradas = $plantas_sembradas != '' ? $plantas_sembradas : 0;
                        /* esquejes_x_planta */
                        $model->esquejes_x_planta = $model->plantas_sembradas > 0 ? round($model->esquejes_cosechados / $model->plantas_sembradas, 2) : 0;
                        /* costo_x_esqueje */
                        $actividad = DB::table('actividad')
                            ->where('nombre', 'like', '%PLANTAS MADRES%')
                            ->where('id_empresa', $finca)
                            ->first();
                        if ($actividad != '') {
                            $costos_mo = DB::table('costos_semana_mano_obra as c')
                                ->join('actividad_mano_obra as a', 'a.id_actividad_mano_obra', '=', 'c.id_actividad_mano_obra')
                                ->select(DB::raw('sum(c.valor) as cantidad'))
                                ->where('c.codigo_semana', $sem->codigo)
                                ->where('a.id_actividad', $actividad->id_actividad)
                                ->where('c.id_empresa', $finca)
                                ->get()[0]->cantidad;
                            $costos_ins = DB::table('costos_semana as c')
                                ->join('actividad_producto as a', 'a.id_actividad_producto', '=', 'c.id_actividad_producto')
                                ->select(DB::raw('sum(c.valor) as cantidad'))
                                ->where('c.codigo_semana', $sem->codigo)
                                ->where('a.id_actividad', $actividad->id_actividad)
                                ->where('c.id_empresa', $finca)
                                ->get()[0]->cantidad;
                            $model->costo_x_esqueje = $esquejes_cosechados_semanal > 0 ? round(($costos_mo + $costos_ins) / $esquejes_cosechados_semanal, 3) : 0;
                            //dump('Costo x esquejes = ($costos_mo + $costos_ins) / $esquejes_cosechados_semanal _ ' . $costos_mo . '+' . $costos_ins . '/' . $esquejes_cosechados_semanal);
                        } else {
                            $model->costo_x_esqueje = 0;
                            //dump('Costo x esquejes = 0');
                        }
                        /* costo_x_planta */
                        $areas = Area::where('estado', 1)
                            ->where('nombre', 'like', '%PROPAGACION%')
                            ->where('id_empresa', $finca)
                            ->get();
                        $ids_areas = [];
                        foreach ($areas as $a)
                            array_push($ids_areas, $a->id_area);
                        $insumos = DB::table('costos_semana as c')
                            ->select(DB::raw('sum(c.valor) as cant'))
                            ->join('actividad_producto as ac', 'ac.id_actividad_producto', '=', 'c.id_actividad_producto')
                            ->join('actividad as a', 'a.id_actividad', '=', 'ac.id_actividad')
                            ->whereIn('a.id_area', $ids_areas)
                            ->where('c.codigo_semana', $sem->codigo)
                            ->get()[0]->cant;
                        $mano_obra = DB::table('costos_semana_mano_obra as c')
                            ->select(DB::raw('sum(c.valor) as cant'))
                            ->join('actividad_mano_obra as am', 'am.id_actividad_mano_obra', '=', 'c.id_actividad_mano_obra')
                            ->join('actividad as a', 'a.id_actividad', '=', 'am.id_actividad')
                            ->whereIn('a.id_area', $ids_areas)
                            ->where('c.codigo_semana', $sem->codigo)
                            ->get()[0]->cant;
                        $otros = DB::table('otros_gastos as o')
                            ->select(DB::raw('sum(o.gip + o.ga) as cant'))
                            ->whereIn('o.id_area', $ids_areas)
                            ->where('o.codigo_semana', $sem->codigo)
                            ->get()[0]->cant;

                        $costos_propagacion = $insumos + $mano_obra + $otros;

                        $requerimientos = DB::table('propag_disponibilidad')
                            ->select(DB::raw('sum(requerimientos) as cant'))
                            ->where('semana', $sem->codigo)
                            ->where('id_empresa', $finca)
                            ->get()[0]->cant;
                        $requerimientos = $requerimientos > 0 ? $requerimientos : 0;

                        $model->costo_x_planta = $requerimientos > 0 ? round($costos_propagacion / $requerimientos, 3) : 0;
                        //dump('Costo x planta = ($costos_propagacion) / $requerimientos _ ' . $insumos . '+' . $mano_obra . '+' . $otros . '/' . $requerimientos);
                        /* requerimientos */
                        $propag_disponibilidad = PropagDisponibilidad::where('id_variedad', $var->id_variedad)
                            ->where('semana', $sem->codigo)
                            ->where('id_empresa', $finca)
                            ->first();
                        $model->requerimientos = $propag_disponibilidad != '' ? $propag_disponibilidad->calcular_requerimientos() : 0;
                        /* porcentaje_requerimiento */
                        $model->porcentaje_requerimiento = $propag_disponibilidad != '' ? (100 - $propag_disponibilidad->desecho) : 0;
                        $model->save();
                    }
                }
            }
        }

        $time_duration = difFechas(date('Y-m-d H:i:s'), $ini)->h . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->m . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->s;
        dump('<*> DURACION: ' . $time_duration . '  <*>');
        dump('<<<<< * >>>>> Fin satisfactorio del comando "update:resumen_propagacion" <<<<< * >>>>>');
        Log::info('<*> DURACION: ' . $time_duration . '  <*>');
        Log::info('<<<<< * >>>>> Fin satisfactorio del comando "update:resumen_propagacion" <<<<< * >>>>>');
    }
}