<?php

namespace yura\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use yura\Modelos\AcumuladosAnno;
use yura\Modelos\Planta;

class cronAcumuladosAnno extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'acumulados:anno {desde=0} {hasta=0} {empresa=0} {planta=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comando para guardar los acumulados del año por semana/planta';

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
        dump('<<<<< ! >>>>> Ejecutando comando "acumulados:anno" <<<<< ! >>>>>');
        $desde = $this->argument('desde');
        $hasta = $this->argument('hasta');
        $empresa = $this->argument('empresa');
        $planta = $this->argument('planta');

        $empresas = DB::table('configuracion_empresa');
        if ($empresa != 0)
            $empresas = $empresas->where('id_configuracion_empresa', $empresa);
        $empresas = $empresas->get();

        $plantas = DB::table('planta');
        if ($planta != 0)
            $plantas = $plantas->where('id_configuracion_empresa', $empresa);
        $plantas = $plantas->get();

        if ($desde == 0)
            $desde = getSemanaByDate(opDiasFecha('-', 21, hoy()))->codigo;
        if ($hasta == 0)
            $hasta = getSemanaByDate(opDiasFecha('-', 0, hoy()))->codigo;

        $semanas = getSemanasByCodigos($desde, $hasta);
        foreach ($empresas as $pos_f => $f) {
            $finca = $f->id_configuracion_empresa;
            $fincas = [$finca];
            $finca_comprada = [];
            $otras_fincas = [];
            if ($finca == 2) {
                array_push($fincas, -1);
                array_push($finca_comprada, -1);
                $otras_fincas = [1, 3];
            }
            foreach ($plantas as $pos_p => $planta) {
                foreach ($semanas as $pos_s => $semana) {
                    $semana_desde = getObjSemana(substr($semana->codigo, 0, 2) . '01');
                    $num_semanas = (difFechas($semana->fecha_inicial, $semana_desde->fecha_inicial)->days / 7) + 1;
                    $model = AcumuladosAnno::All()
                        ->where('id_planta', $planta->id_planta)
                        ->where('id_empresa', $finca)
                        ->where('semana', $semana->codigo)
                        ->first();
                    if ($model == '') {
                        $model = new AcumuladosAnno();
                        $model->id_planta = $planta->id_planta;
                        $model->id_empresa = $finca;
                        $model->semana = $semana->codigo;
                    }

                    $resumen_semanal_acum = DB::table('resumen_total_semanal_exportcalas as r')
                        ->join('variedad as v', 'v.id_variedad', '=', 'r.id_variedad')
                        ->select(
                            DB::raw('sum(r.tallos_cosechados) as tallos_cosechados'),
                            DB::raw('sum(r.tallos_exportables) as tallos_exportables'),
                            DB::raw('sum(r.bouquetera) as bouquetera'),
                            DB::raw('sum(r.venta) as venta'),
                            DB::raw('sum(r.nacional) as nacionales'),
                            DB::raw('sum(r.bajas) as bajas'),
                            DB::raw('sum(r.tallos_vendidos) as tallos_vendidos'),
                            DB::raw('sum(r.tallos_bqt_4_sem) as tallos_bqt_4_sem'),
                            DB::raw('sum(r.ventas_bqt_4_sem) as ventas_bqt_4_sem'),
                            DB::raw('sum(r.venta_bouquetera) as venta_bouquetera')
                        )
                        ->where('r.id_empresa', $finca)
                        ->where('r.semana', '>=', $semana_desde->codigo)
                        ->where('r.semana', '<=', $semana->codigo)
                        ->where('v.id_planta', $planta->id_planta)
                        ->get()[0];
                    $cos_acum = $resumen_semanal_acum->tallos_cosechados;

                    $compra_flor_finca_acum = DB::table('bouquetera as b')
                        ->join('variedad as v', 'v.id_variedad', '=', 'b.id_variedad')
                        ->select(
                            DB::raw('sum(b.precio * (tallos)) as tallos'),
                            DB::raw('sum(b.precio * (exportada)) as exportada'),
                            DB::raw('sum(b.tallos) as tallos_bqt'),
                            DB::raw('sum(b.exportada) as tallos_exportada')
                        )
                        ->where('b.fecha', '>=', $semana_desde->fecha_final)
                        ->where('b.fecha', '<=', $semana->fecha_final)
                        ->where('b.id_empresa', $finca)
                        ->where('v.id_planta', $planta->id_planta)
                        ->get()[0];
                    $producidos_acum = $resumen_semanal_acum->tallos_exportables + $compra_flor_finca_acum->tallos_bqt;
                    $exp_acum = $resumen_semanal_acum->tallos_exportables;
                    $bqt_acum = $compra_flor_finca_acum->tallos_bqt;
                    $bqt_total_acum = $compra_flor_finca_acum->tallos_bqt;
                    $compra_flor_otras_fincas_acum = DB::table('bouquetera as b')
                        ->join('variedad as v', 'v.id_variedad', '=', 'b.id_variedad')
                        ->select(
                            DB::raw('sum(b.precio * (tallos)) as tallos'),
                            DB::raw('sum(b.precio * (exportada)) as exportada'),
                            DB::raw('sum(b.tallos) as tallos_bqt'),
                            DB::raw('sum(b.exportada) as tallos_exportada')
                        )
                        ->where('b.fecha', '>=', $semana_desde->fecha_inicial)
                        ->where('b.fecha', '<=', $semana->fecha_final)
                        ->whereIn('b.id_empresa', $otras_fincas)
                        ->where('v.id_planta', $planta->id_planta)
                        ->get()[0];
                    $tallos_prod_bqt_otras_fincas_acum = $compra_flor_otras_fincas_acum->tallos_bqt;

                    $flor_comprada_exp_acum = DB::table('bouquetera as b')
                        ->join('variedad as v', 'v.id_variedad', '=', 'b.id_variedad')
                        ->select(
                            DB::raw('sum(b.precio * (tallos)) as tallos'),
                            DB::raw('sum(b.precio * (exportada)) as exportada'),
                            DB::raw('sum(b.tallos) as tallos_bqt'),
                            DB::raw('sum(b.exportada) as tallos_exportada')
                        )
                        ->where('b.fecha', '>=', $semana_desde->fecha_inicial)
                        ->where('b.fecha', '<=', $semana->fecha_final)
                        ->whereIn('b.id_empresa', $fincas)
                        ->where('v.id_planta', $planta->id_planta)
                        ->get()[0];

                    $flor_comprada_bqt_acum = DB::table('bouquetera as b')
                        ->join('variedad as v', 'v.id_variedad', '=', 'b.id_variedad')
                        ->select(
                            DB::raw('sum(b.precio * (tallos)) as tallos'),
                            DB::raw('sum(b.precio * (exportada)) as exportada'),
                            DB::raw('sum(b.tallos) as tallos_bqt'),
                            DB::raw('sum(b.exportada) as tallos_exportada')
                        )
                        ->where('b.fecha', '>=', $semana_desde->fecha_inicial)
                        ->where('b.fecha', '<=', $semana->fecha_final)
                        ->whereIn('b.id_empresa', $finca_comprada)
                        ->where('v.id_planta', $planta->id_planta)
                        ->get()[0];
                    $comprada_acum = $flor_comprada_exp_acum->tallos_exportada + $flor_comprada_bqt_acum->tallos_bqt;
                    $comprada_exp_acum = $flor_comprada_exp_acum->tallos_exportada;
                    $comprada_bqt_acum = $flor_comprada_bqt_acum->tallos_bqt;

                    $venta_normal_acum = $resumen_semanal_acum->venta;
                    $ventas_bqt_acum = 0;
                    $area_acum = 0;
                    foreach (getSemanasByCodigos($semana_desde->codigo, $semana->codigo) as $sem) {
                        $resumen_semanal_finca = DB::table('resumen_total_semanal_exportcalas as r')
                            ->select(
                                DB::raw('sum(r.tallos_bqt_4_sem) as tallos_bqt_4_sem'),
                                DB::raw('sum(r.ventas_bqt_4_sem) as ventas_bqt_4_sem'),
                            )
                            ->where('r.id_empresa', 2)
                            ->where('r.semana', $sem->codigo)
                            ->get()[0];
                        $precio_tallo_bqt_4_sem = $resumen_semanal_finca->tallos_bqt_4_sem > 0 ? number_format($resumen_semanal_finca->ventas_bqt_4_sem / $resumen_semanal_finca->tallos_bqt_4_sem, 2) : 0;
                        $compra_flor_finca = DB::table('bouquetera as b')
                            ->join('variedad as v', 'v.id_variedad', '=', 'b.id_variedad')
                            ->select(
                                DB::raw('sum(b.precio * (tallos)) as tallos'),
                                DB::raw('sum(b.precio * (exportada)) as exportada'),
                                DB::raw('sum(b.tallos) as tallos_bqt'),
                                DB::raw('sum(b.exportada) as tallos_exportada')
                            )
                            ->where('b.fecha', '>=', $sem->fecha_inicial)
                            ->where('b.fecha', '<=', $sem->fecha_final)
                            ->where('b.id_empresa', $finca)
                            ->where('v.id_planta', $planta->id_planta)
                            ->get()[0];
                        $bqt_total = $compra_flor_finca->tallos_bqt;
                        $ventas_bqt = $bqt_total * $precio_tallo_bqt_4_sem;
                        $ventas_bqt_acum += $ventas_bqt;


                        $area_acum_a = $area_acum_b = 0;
                        if ($planta->tiene_ciclos == 0 && $planta->tipo == 'N') {   // Normales y Sin ciclos
                            $area_acum_b = DB::table('proy_no_perennes as proy')
                                ->join('semana as s', 's.id_semana', '=', 'proy.id_semana')
                                ->join('variedad as v', 'v.id_variedad', '=', 's.id_variedad')
                                ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                                ->select(
                                    DB::raw('sum(proy.area_produccion) as area_produccion'),
                                    DB::raw('sum(proy.area_semana) as area_semana')
                                )
                                ->where('s.codigo', $sem->codigo)
                                ->where('p.estado', 1)
                                ->where('v.estado', 1)
                                ->where('p.tiene_ciclos', 0)
                                ->where('p.tipo', 'N')
                                ->where('proy.id_empresa', $finca)
                                ->where('v.id_planta', $planta->id_planta)
                                ->get()[0]->area_produccion;
                        } else {    // Perennes o Con ciclos
                            $area_acum_a = DB::table('ciclo as c')
                                ->join('variedad as v', 'v.id_variedad', '=', 'c.id_variedad')
                                ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                                ->select(DB::raw('sum(c.area) as area'))
                                ->where('v.estado', 1)
                                ->where('p.estado', 1)
                                ->where('c.estado', '=', 1)
                                ->where('c.id_empresa', $finca)
                                ->Where(function ($q) use ($sem) {
                                    $q->where('c.fecha_fin', '>=', $sem->fecha_inicial)
                                        ->where('c.fecha_fin', '<=', $sem->fecha_final)
                                        ->orWhere(function ($q) use ($sem) {
                                            $q->where('c.fecha_inicio', '>=', $sem->fecha_inicial)
                                                ->where('c.fecha_inicio', '<=', $sem->fecha_final);
                                        })
                                        ->orWhere(function ($q) use ($sem) {
                                            $q->where('c.fecha_inicio', '<', $sem->fecha_inicial)
                                                ->where('c.fecha_fin', '>', $sem->fecha_final);
                                        });
                                })
                                ->Where(function ($q) use ($sem) {
                                    $q->where('p.tipo', 'P')
                                        ->orWhere('p.tiene_ciclos', 1);
                                })
                                ->where('v.id_planta', $planta->id_planta)
                                ->get()[0]->area;
                        }
                        $area_acum += $area_acum_a + $area_acum_b;
                    }
                    $prom_area = round($area_acum / $num_semanas, 2);
                    $ventas_acum = $venta_normal_acum + $ventas_bqt_acum;

                    $model->area = $prom_area;
                    $model->cosechados = $cos_acum;
                    $model->producidos = $producidos_acum;
                    $model->prod_exp = $exp_acum;
                    $model->prod_bqt = $bqt_acum;
                    $model->bqt_total = $bqt_total_acum;
                    $model->flor_comprada = $comprada_acum;
                    $model->tallos_prod_bqt_otras_fincas = $tallos_prod_bqt_otras_fincas_acum;
                    $model->comprada_exp = $comprada_exp_acum;
                    $model->comprada_bqt = $comprada_bqt_acum;
                    $model->venta_total = $ventas_acum;
                    $model->venta_exp = $venta_normal_acum;
                    $model->venta_bqt = $ventas_bqt_acum;
                    $model->save();
                    dump('finca: ' . ($pos_f + 1) . '/' . count($empresas) . ' - var:' . ($pos_p + 1) . '/' . count($plantas) . ' - sem:' . ($pos_s + 1) . '/' . count($semanas));
                }
            }
        }

        $time_duration = difFechas(date('Y-m-d H:i:s'), $ini)->h . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->m . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->s;
        dump('<*> DURACION: ' . $time_duration . '  <*>');
        dump('<<<<< * >>>>> Fin satisfactorio del comando "acumulados:anno" <<<<< * >>>>>');
    }
}
