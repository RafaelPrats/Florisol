<?php

namespace yura\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use yura\Modelos\ConfiguracionEmpresa;
use yura\Modelos\ResumenTotalSemanalExportcalas;
use yura\Modelos\Semana;
use yura\Modelos\Variedad;

class UpdateResumenTotalSemanalExportcalas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exportcalas:resumen_total_semanal {semana_desde=0} {semana_hasta=0} {variedad=0} {empresa=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comando para actualizar la tabla resumen_total_semanal_exportcalas';

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
        dump('<<<<< ! >>>>> Ejecutando comando "exportcalas:resumen_total_semanal" <<<<< ! >>>>>');
        Log::info('<<<<< ! >>>>> Ejecutando comando "exportcalas:resumen_total_semanal" <<<<< ! >>>>>');

        $sem_parametro_desde = $this->argument('semana_desde');
        $sem_parametro_hasta = $this->argument('semana_hasta');
        $var_parametro = $this->argument('variedad');
        $empresa_parametro = $this->argument('empresa');

        if ($sem_parametro_desde <= $sem_parametro_hasta) {
            if ($sem_parametro_desde != 0)
                $semana_desde = Semana::All()->where('estado', 1)->where('codigo', $sem_parametro_desde)->first();
            else
                $semana_desde = getSemanaByDate(date('Y-m-d'));
            if ($sem_parametro_hasta != 0)
                $semana_hasta = Semana::All()->where('estado', 1)->where('codigo', $sem_parametro_hasta)->first();
            else
                $semana_hasta = getSemanaByDate(date('Y-m-d'));

            $variedades = Variedad::All()->where('estado', 1);
            if ($var_parametro != 0)
                $variedades = $variedades->where('id_variedad', $var_parametro);

            $empresas = ConfiguracionEmpresa::All();
            if ($empresa_parametro != 0)
                $empresas = $empresas->where('id_configuracion_empresa', $empresa_parametro);

            $array_semanas = DB::select('select distinct codigo, fecha_inicial, fecha_final, ' .
                'last_4_semana, last_13_semana, last_52_semana ' .
                'from semana where ' .
                'codigo >= ' . $semana_desde->codigo .
                ' and codigo <= ' . $semana_hasta->codigo .
                ' and semana_guia = 1' .
                ' order by codigo');

            $cant_proc = 0;
            foreach ($empresas as $pos_e => $emp) {
                $finca = $emp->id_configuracion_empresa;
                $fincas = [$finca];
                $finca_comprada = [];
                $otras_fincas = [];
                if ($finca == 2) {
                    array_push($fincas, -1);
                    array_push($finca_comprada, -1);
                    $otras_fincas = [1, 3];
                }
                foreach ($array_semanas as $pos_s => $sem) {
                    $last_4_semana_fecha_inicial = opDiasFecha('-', 21, $sem->fecha_inicial);
                    /*$last_13_semana_fecha_inicial = opDiasFecha('-', 84, $sem->fecha_inicial);
                    $last_52_semana_fecha_inicial = opDiasFecha('-', 357, $sem->fecha_inicial);*/
                    foreach ($variedades as $pos_v => $var) {
                        $cant_proc++;
                        dump('finca: ' . ($pos_e + 1) . '/' . count($empresas) . ' _ ' .
                            porcentaje($cant_proc, count($empresas) * count($array_semanas) * count($variedades), 1) . '%' .
                            ' - sem: ' . ($pos_s + 1) . '/' . count($array_semanas) .
                            ' - var: ' . ($pos_v + 1) . '/' . count($variedades));
                        $model = ResumenTotalSemanalExportcalas::All()
                            ->where('semana', $sem->codigo)
                            ->where('id_variedad', $var->id_variedad)
                            ->where('id_empresa', $finca)
                            ->first();
                        if ($model == '') {
                            $model = new ResumenTotalSemanalExportcalas();
                            $model->semana = $sem->codigo;
                            $model->id_variedad = $var->id_variedad;
                            $model->id_empresa = $finca;
                        }

                        /* ============== Tallos Cosechados ============== */
                        $valor = DB::table('desglose_recepcion as dr')
                            ->join('recepcion as r', 'dr.id_recepcion', '=', 'r.id_recepcion')
                            ->select(DB::raw('sum(dr.cantidad_mallas * dr.tallos_x_malla) as cantidad'))
                            ->where('r.fecha_ingreso', '>=', $sem->fecha_inicial . ' 00:00:00')
                            ->where('r.fecha_ingreso', '<=', $sem->fecha_final . ' 23:59:59')
                            ->where('dr.id_variedad', $var->id_variedad)
                            ->where('dr.id_empresa', $finca)
                            ->get()[0]->cantidad;
                        $model->tallos_cosechados = $valor;

                        /* ============== Tallos Proyectados ============== */
                        $valor = DB::table('proyeccion_modulo_semana as pr')
                            ->join('modulo as m', 'pr.id_modulo', '=', 'm.id_modulo')
                            ->select(DB::raw('sum(pr.proyectados) as cantidad'))
                            ->where('pr.semana', $sem->codigo)
                            ->where('pr.id_variedad', $var->id_variedad)
                            ->where('m.id_empresa', $finca)
                            ->get()[0]->cantidad;
                        $model->tallos_proyectados = $valor;

                        if ($var->id_variedad == 1) {
                            /* ============== Tallos Bqt (-4 semanas) ============== */
                            $compra_flor_finca = DB::table('bouquetera as b')
                                ->join('variedad as v', 'v.id_variedad', '=', 'b.id_variedad')
                                ->select(
                                    DB::raw('sum(b.precio * (tallos)) as tallos'),
                                    DB::raw('sum(b.precio * (exportada)) as exportada'),
                                    DB::raw('sum(b.tallos) as tallos_bqt'),
                                    DB::raw('sum(b.exportada) as tallos_exportada')
                                )
                                ->where('b.fecha', '>=', $last_4_semana_fecha_inicial)
                                ->where('b.fecha', '<=', $sem->fecha_final)
                                ->where('b.id_empresa', $finca)
                                ->get()[0];

                            $compra_flor_otras_fincas = DB::table('bouquetera as b')
                                ->join('variedad as v', 'v.id_variedad', '=', 'b.id_variedad')
                                ->select(
                                    DB::raw('sum(b.precio * (tallos)) as tallos'),
                                    DB::raw('sum(b.precio * (exportada)) as exportada'),
                                    DB::raw('sum(b.tallos) as tallos_bqt'),
                                    DB::raw('sum(b.exportada) as tallos_exportada')
                                )
                                ->where('b.fecha', '>=', $last_4_semana_fecha_inicial)
                                ->where('b.fecha', '<=', $sem->fecha_final)
                                ->whereIn('b.id_empresa', $otras_fincas)
                                ->get()[0];

                            $flor_comprada_bqt = DB::table('bouquetera as b')
                                ->join('variedad as v', 'v.id_variedad', '=', 'b.id_variedad')
                                ->select(
                                    DB::raw('sum(b.precio * (tallos)) as tallos'),
                                    DB::raw('sum(b.precio * (exportada)) as exportada'),
                                    DB::raw('sum(b.tallos) as tallos_bqt'),
                                    DB::raw('sum(b.exportada) as tallos_exportada')
                                )
                                ->where('b.fecha', '>=', $last_4_semana_fecha_inicial)
                                ->where('b.fecha', '<=', $sem->fecha_final)
                                ->whereIn('b.id_empresa', $finca_comprada)
                                ->get()[0];

                            if ($finca == 2) {
                                $bqt_total = $compra_flor_finca->tallos_bqt + $compra_flor_otras_fincas->tallos_bqt + $flor_comprada_bqt->tallos_bqt;
                            } else {
                                $bqt_total = $compra_flor_finca->tallos_bqt + $flor_comprada_bqt->tallos_bqt;
                            }

                            $model->tallos_bqt_4_sem = $bqt_total;

                            /* ============== Venta Bqt (-4 semanas) ============== */
                            $resumen_semanal_4 = DB::table('resumen_total_semanal_exportcalas')
                                ->select(
                                    /*DB::raw('sum(tallos_cosechados) as tallos_cosechados'),
                                DB::raw('sum(tallos_exportables) as tallos_exportables'),
                                DB::raw('sum(bouquetera) as bouquetera'),
                                DB::raw('sum(venta) as venta'),
                                DB::raw('sum(nacional) as nacionales'),
                                DB::raw('sum(bajas) as bajas'),
                                DB::raw('sum(tallos_vendidos) as tallos_vendidos'),*/
                                    DB::raw('sum(venta_bouquetera) as venta_bouquetera')
                                )
                                ->where('id_empresa', $emp->id_configuracion_empresa)
                                ->where('semana', '>=', $sem->last_4_semana)
                                ->where('semana', '<=', $sem->codigo)
                                //->where('id_variedad', $var->id_variedad)
                                ->get()[0];
                            $model->ventas_bqt_4_sem = $resumen_semanal_4->venta_bouquetera != '' ? $resumen_semanal_4->venta_bouquetera : 0;
                        }

                        $model->save();
                    }
                }
            }
        }
        $time_duration = difFechas(date('Y-m-d H:i:s'), $ini)->h . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->m . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->s;
        Log::info('<*> DURACION: ' . $time_duration . '  <*>');
        Log::info('<<<<< * >>>>> Fin satisfactorio del comando "exportcalas:resumen_total_semanal" <<<<< * >>>>>');
    }
}
