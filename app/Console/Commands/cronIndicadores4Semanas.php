<?php

namespace yura\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use yura\Modelos\Area;
use yura\Modelos\Ciclo;
use yura\Modelos\ConfiguracionEmpresa;
use yura\Modelos\Indicadores4Semanas;
use yura\Modelos\Semana;

class cronIndicadores4Semanas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:indicador_4_semanas {desde=0} {hasta=0} {empresa=0}';

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
        dump('<<<<< ! >>>>> Ejecutando comando "cron:indicador_4_semanas" <<<<< ! >>>>>');
        Log::info('<<<<< ! >>>>> Ejecutando comando "cron:indicador_4_semanas" <<<<< ! >>>>>');

        $desde_par = $this->argument('desde');
        $hasta_par = $this->argument('hasta');
        $empresa_par = $this->argument('empresa');

        if ($desde_par <= $hasta_par) {
            if ($desde_par != 0)
                $semana_desde = Semana::All()->where('estado', 1)->where('codigo', $desde_par)->first();
            else
                $semana_desde = getSemanaByDate(opDiasFecha('-', 42, date('Y-m-d')));
            if ($hasta_par != 0)
                $semana_hasta = Semana::All()->where('estado', 1)->where('codigo', $hasta_par)->first();
            else
                $semana_hasta = getSemanaByDate(date('Y-m-d'));

            $empresas = ConfiguracionEmpresa::All();
            if ($empresa_par != 0)
                $empresas = $empresas->where('id_configuracion_empresa', $empresa_par);

            $array_semanas = DB::table('semana')
                ->select('codigo', 'fecha_inicial', 'fecha_final', 'last_4_semana')->distinct()
                ->where('codigo', '>=', $semana_desde->codigo)
                ->where('codigo', '<=', $semana_hasta->codigo)
                ->where('estado', 1)
                ->where('semana_guia', 1)
                ->orderBy('codigo')
                ->get();

            foreach ($empresas as $pos_e => $empresa) {
                $finca = $empresa->id_configuracion_empresa;
                $areas_propagacion = Area::where('estado', 1)
                    ->where('nombre', 'like', '%PROPAGACION%')
                    ->where('id_empresa', $finca)
                    ->get();
                $ids_areas_propagacion = [];
                foreach ($areas_propagacion as $a)
                    array_push($ids_areas_propagacion, $a->id_area);

                $areas_cultivo = Area::where('estado', 1)
                    ->where('nombre', 'like', '%CULTIVO%')
                    ->where('id_empresa', $finca)
                    ->get();
                $ids_areas_cultivo = [];
                foreach ($areas_cultivo as $a)
                    array_push($ids_areas_cultivo, $a->id_area);

                $areas_post = Area::where('estado', 1)
                    ->where('nombre', 'like', '%POSCOSECHA%')
                    ->where('id_empresa', $finca)
                    ->get();
                $ids_areas_post = [];
                foreach ($areas_post as $a)
                    array_push($ids_areas_post, $a->id_area);

                foreach ($array_semanas as $pos_sem => $sem) {
                    dump('finca: ' . $finca . ' - sem: ' . $sem->codigo);
                    $desde_fecha_inicial = opDiasFecha('-', 21, $sem->fecha_inicial);
                    $desde_codigo = $sem->last_4_semana;
                    $model = Indicadores4Semanas::All()
                        ->where('semana', $sem->codigo)
                        ->where('id_empresa', $finca)
                        ->first();
                    if ($model == '') {
                        $model = new Indicadores4Semanas();
                        $model->semana = $sem->codigo;
                        $model->id_empresa = $finca;
                    }
                    $resumen_semanal_total = DB::table('resumen_total_semanal_exportcalas')
                        ->select(DB::raw('sum(tallos_cosechados) as tallos_cosechados'),
                            DB::raw('sum(tallos_exportables) as tallos_exportables'),
                            DB::raw('sum(bouquetera) as bouquetera'),
                            DB::raw('sum(venta) as venta'),
                            DB::raw('sum(venta_bouquetera) as venta_bouquetera'),
                            DB::raw('sum(tallos_proyectados) as tallos_proyectados'))
                        ->where('id_empresa', $finca)
                        ->where('semana', '>=', $desde_codigo)
                        ->where('semana', '<=', $sem->codigo)
                        ->get()[0];
                    $resumen_costos = DB::table('resumen_costos_semanal')
                        ->select(DB::raw('sum(mano_obra) as mano_obra'),
                            DB::raw('sum(insumos) as insumos'),
                            DB::raw('sum(regalias) as regalias'),
                            DB::raw('sum(fijos) as fijos'))
                        ->where('id_empresa', $finca)
                        ->where('codigo_semana', '>=', $desde_codigo)
                        ->where('codigo_semana', '<=', $sem->codigo)
                        ->get()[0];
                    $fincas = [$finca];
                    if ($finca == 2)
                        array_push($fincas, -1);
                    $compra_flor = DB::table('bouquetera')
                        ->select(DB::raw('sum(precio * (tallos)) as tallos'),
                            DB::raw('sum(precio * (exportada)) as exportada'))
                        ->where('fecha', '>=', $desde_fecha_inicial)
                        ->where('fecha', '<=', $sem->fecha_final)
                        ->whereIn('id_empresa', $fincas)
                        ->get()[0];

                    $semanas = DB::table('semana')
                        ->select('codigo', 'fecha_inicial', 'fecha_final')->distinct()
                        ->where('codigo', '>=', $desde_codigo)
                        ->where('codigo', '<=', $sem->codigo)
                        ->where('estado', 1)
                        ->orderBy('codigo')
                        ->get();
                    $areas = 0;
                    foreach ($semanas as $s) {
                        $area_a = DB::table('ciclo as c')
                            ->join('variedad as v', 'v.id_variedad', '=', 'c.id_variedad')
                            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                            ->select(DB::raw('sum(c.area) as area'))
                            ->where('v.estado', 1)
                            ->where('p.estado', 1)
                            ->where('c.estado', '=', 1)
                            ->where('c.id_empresa', $finca)
                            ->Where(function ($q) use ($s) {
                                $q->where('c.fecha_fin', '>=', $s->fecha_inicial)
                                    ->where('c.fecha_fin', '<=', $s->fecha_final)
                                    ->orWhere(function ($q) use ($s) {
                                        $q->where('c.fecha_inicio', '>=', $s->fecha_inicial)
                                            ->where('c.fecha_inicio', '<=', $s->fecha_final);
                                    })
                                    ->orWhere(function ($q) use ($s) {
                                        $q->where('c.fecha_inicio', '<', $s->fecha_inicial)
                                            ->where('c.fecha_fin', '>', $s->fecha_final);
                                    });
                            })
                            ->Where(function ($q) use ($s) {
                                $q->where('p.tipo', 'P')
                                    ->orWhere('p.tiene_ciclos', 1);
                            })
                            ->get()[0]->area;
                        $area_b = DB::table('proy_no_perennes as proy')
                            ->join('semana as s', 's.id_semana', '=', 'proy.id_semana')
                            ->join('variedad as v', 'v.id_variedad', '=', 's.id_variedad')
                            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                            ->select(DB::raw('sum(proy.area_produccion) as area_produccion'),
                                DB::raw('sum(proy.area_semana) as area_semana'))
                            ->where('s.codigo', $s->codigo)
                            ->where('p.estado', 1)
                            ->where('v.estado', 1)
                            ->where('p.tiene_ciclos', 0)
                            ->where('p.tipo', 'N')
                            ->where('proy.id_empresa', $finca)
                            ->get()[0]->area_produccion;
                        $areas += $area_a + $area_b;
                    }

                    /* -------------- Precio x tallo --------------- */
                    $tallos = $resumen_semanal_total->tallos_exportables + $resumen_semanal_total->bouquetera;
                    $venta = ($resumen_semanal_total->venta + $resumen_semanal_total->venta_bouquetera);
                    $precio_x_tallo = $tallos > 0 ? $venta / $tallos : 0;
                    $model->precio_x_tallo = $precio_x_tallo;
                    /* -------------- Precio x tallo Bqt --------------- */
                    $tallos_bqt = $resumen_semanal_total->bouquetera;
                    $venta_bqt = $resumen_semanal_total->venta_bouquetera;
                    $precio_x_tallo_bqt = $tallos_bqt > 0 ? $venta_bqt / $tallos_bqt : 0;
                    $model->precio_x_tallo_bqt = $precio_x_tallo_bqt;
                    /* -------------- Venta/m2 --------------- */
                    //dd($desde_codigo, $sem->codigo, $venta, $areas);
                    $venta_m2 = $areas > 0 ? $venta / ($areas / 4) : 0;
                    $model->venta_m2 = $venta_m2;
                    /* -------------- Costos/m2 --------------- */
                    $costos = $resumen_costos->mano_obra + $resumen_costos->insumos + $resumen_costos->fijos + $resumen_costos->regalias + ($compra_flor->tallos + $compra_flor->exportada);
                    $costos_m2 = $areas > 0 ? $costos / ($areas / 4) : 0;
                    $model->costos_m2 = $costos_m2;
                    /* -------------- EBITDA/m2 --------------- */
                    $model->ebitda_m2 = $model->venta_m2 - $model->costos_m2;
                    /* -------------- Costos Finca/m2 --------------- */
                    $costos_finca = $resumen_costos->mano_obra + $resumen_costos->insumos + $resumen_costos->fijos + $resumen_costos->regalias;
                    $costos_finca_m2 = $areas > 0 ? $costos_finca / ($areas / 4) : 0;
                    $model->costos_finca_m2 = $costos_finca_m2;
                    /* -------------- Propagacion x Tallo --------------- */
                    $insumos_propag = DB::table('costos_semana as c')
                        ->select(DB::raw('sum(c.valor) as cant'))
                        ->join('actividad_producto as ac', 'ac.id_actividad_producto', '=', 'c.id_actividad_producto')
                        ->join('actividad as a', 'a.id_actividad', '=', 'ac.id_actividad')
                        ->whereIn('a.id_area', $ids_areas_propagacion)
                        ->where('c.codigo_semana', '>=', $desde_codigo)
                        ->where('c.codigo_semana', '<=', $sem->codigo)
                        ->get()[0]->cant;
                    $mano_obra_propag = DB::table('costos_semana_mano_obra as c')
                        ->select(DB::raw('sum(c.valor) as cant'))
                        ->join('actividad_mano_obra as am', 'am.id_actividad_mano_obra', '=', 'c.id_actividad_mano_obra')
                        ->join('actividad as a', 'a.id_actividad', '=', 'am.id_actividad')
                        ->whereIn('a.id_area', $ids_areas_propagacion)
                        ->where('c.codigo_semana', '>=', $desde_codigo)
                        ->where('c.codigo_semana', '<=', $sem->codigo)
                        ->get()[0]->cant;
                    $otros_propag = DB::table('otros_gastos as o')
                        ->select(DB::raw('sum(o.gip + o.ga) as cant'))
                        ->whereIn('o.id_area', $ids_areas_propagacion)
                        ->where('o.codigo_semana', '>=', $desde_codigo)
                        ->where('o.codigo_semana', '<=', $sem->codigo)
                        ->get()[0]->cant;
                    $costos_total_propag = $insumos_propag + $mano_obra_propag + $otros_propag;
                    $esquejes_cosechados = DB::table('resumen_propagacion')
                        ->select(DB::raw('sum(esquejes_cosechados) as cant'))
                        ->where('id_empresa', $finca)
                        ->where('semana', '>=', $desde_codigo)
                        ->where('semana', '<=', $sem->codigo)
                        ->get()[0]->cant;
                    $esquejes_cosechados = $esquejes_cosechados > 0 ? $esquejes_cosechados : 0;
                    $model->propagacion_x_tallo = $esquejes_cosechados > 0 ? round(($costos_total_propag / $esquejes_cosechados) * 100, 2) : 0;
                    /* -------------- Cultivo x Tallo --------------- */
                    $insumos_cultivo = DB::table('costos_semana as c')
                        ->select(DB::raw('sum(c.valor) as cant'))
                        ->join('actividad_producto as ac', 'ac.id_actividad_producto', '=', 'c.id_actividad_producto')
                        ->join('actividad as a', 'a.id_actividad', '=', 'ac.id_actividad')
                        ->whereIn('a.id_area', $ids_areas_cultivo)
                        ->where('c.codigo_semana', '>=', $desde_codigo)
                        ->where('c.codigo_semana', '<=', $sem->codigo)
                        ->get()[0]->cant;
                    $mano_obra_cultivo = DB::table('costos_semana_mano_obra as c')
                        ->select(DB::raw('sum(c.valor) as cant'))
                        ->join('actividad_mano_obra as am', 'am.id_actividad_mano_obra', '=', 'c.id_actividad_mano_obra')
                        ->join('actividad as a', 'a.id_actividad', '=', 'am.id_actividad')
                        ->whereIn('a.id_area', $ids_areas_cultivo)
                        ->where('c.codigo_semana', '>=', $desde_codigo)
                        ->where('c.codigo_semana', '<=', $sem->codigo)
                        ->get()[0]->cant;
                    $otros_cultivo = DB::table('otros_gastos as o')
                        ->select(DB::raw('sum(o.gip + o.ga) as cant'))
                        ->whereIn('o.id_area', $ids_areas_cultivo)
                        ->where('o.codigo_semana', '>=', $desde_codigo)
                        ->where('o.codigo_semana', '<=', $sem->codigo)
                        ->get()[0]->cant;
                    $costos_total_cultivo = $insumos_cultivo + $mano_obra_cultivo + $otros_cultivo;
                    $model->cultivo_x_tallo = $resumen_semanal_total->tallos_cosechados > 0 ? round(($costos_total_cultivo / $resumen_semanal_total->tallos_cosechados) * 100, 2) : 0;
                    /* -------------- Postcosecha x Tallo --------------- */
                    $insumos_post = DB::table('costos_semana as c')
                        ->select(DB::raw('sum(c.valor) as cant'))
                        ->join('actividad_producto as ac', 'ac.id_actividad_producto', '=', 'c.id_actividad_producto')
                        ->join('actividad as a', 'a.id_actividad', '=', 'ac.id_actividad')
                        ->whereIn('a.id_area', $ids_areas_post)
                        ->where('c.codigo_semana', '>=', $desde_codigo)
                        ->where('c.codigo_semana', '<=', $sem->codigo)
                        ->get()[0]->cant;
                    $mano_obra_post = DB::table('costos_semana_mano_obra as c')
                        ->select(DB::raw('sum(c.valor) as cant'))
                        ->join('actividad_mano_obra as am', 'am.id_actividad_mano_obra', '=', 'c.id_actividad_mano_obra')
                        ->join('actividad as a', 'a.id_actividad', '=', 'am.id_actividad')
                        ->whereIn('a.id_area', $ids_areas_post)
                        ->where('c.codigo_semana', '>=', $desde_codigo)
                        ->where('c.codigo_semana', '<=', $sem->codigo)
                        ->get()[0]->cant;
                    $otros_post = DB::table('otros_gastos as o')
                        ->select(DB::raw('sum(o.gip + o.ga) as cant'))
                        ->whereIn('o.id_area', $ids_areas_post)
                        ->where('o.codigo_semana', '>=', $desde_codigo)
                        ->where('o.codigo_semana', '<=', $sem->codigo)
                        ->get()[0]->cant;
                    $costos_total_post = $insumos_post + $mano_obra_post + $otros_post;
                    $model->postcosecha_x_tallo = $resumen_semanal_total->tallos_cosechados > 0 ? round(($costos_total_post / $resumen_semanal_total->tallos_cosechados) * 100, 2) : 0;
                    /* -------------- Costos Total x Tallo --------------- */
                    $model->costos_total_x_tallo = $resumen_semanal_total->tallos_cosechados > 0 ? round(($costos / $resumen_semanal_total->tallos_cosechados) * 100, 2) : 0;
                    /* -------------- % Cumplimiento --------------- */
                    $model->porcentaje_cumplimiento = ($resumen_semanal_total->tallos_cosechados > 0 && $resumen_semanal_total->tallos_proyectados > 0) ? round(($resumen_semanal_total->tallos_cosechados * 100) / $resumen_semanal_total->tallos_proyectados, 2) : 0;
                    /* -------------- Tallos x m2 --------------- */
                    $model->tallos_m2 = $areas > 0 ? $resumen_semanal_total->tallos_cosechados / ($areas / 4) : 0;
                    /* -------------- Ciclo --------------- */
                    $ciclos = Ciclo::where('activo', 0)
                        ->where('fecha_fin', '>=', $desde_fecha_inicial)
                        ->where('fecha_fin', '<=', $sem->fecha_final)
                        ->where('estado', 1)
                        ->where('id_empresa', $finca)
                        ->get();
                    $dias = 0;
                    foreach ($ciclos as $c) {
                        $dias += difFechas($c->fecha_fin, $c->fecha_inicio)->days;
                    }
                    $model->ciclo = count($ciclos) > 0 ? round($dias / count($ciclos), 2) : 0;

                    $model->save();
                }
            }
        }

        $time_duration = difFechas(date('Y-m-d H:i:s'), $ini)->h . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->m . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->s;
        dump('<*> DURACION: ' . $time_duration . '  <*>');
        dump('<<<<< * >>>>> Fin satisfactorio del comando "cron:indicador_4_semanas" <<<<< * >>>>>');
        Log::info('<*> DURACION: ' . $time_duration . '  <*>');
        Log::info('<<<<< * >>>>> Fin satisfactorio del comando "cron:indicador_4_semanas" <<<<< * >>>>>');
    }
}