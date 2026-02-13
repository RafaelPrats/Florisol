<?php

namespace yura\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use yura\Modelos\Ciclo;
use yura\Modelos\ConfiguracionEmpresa;
use yura\Modelos\IndicadorSemana;
use yura\Modelos\IndicadorVariedad;
use yura\Modelos\IndicadorVariedadSemana;
use yura\Modelos\Semana;
use yura\Modelos\Variedad;

class IndicadorSemanal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'indicador_semana:update {desde=0} {hasta=0} {indicador=0} {variedad=0} {planta=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comando para actualizar los indicadores por semana';

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
        Log::info('<<<<< ! >>>>> Ejecutando comando "indicador_semana:update" <<<<< ! >>>>>');
        dump('<<<<< ! >>>>> Ejecutando comando "indicador_semana:update" <<<<< ! >>>>>');

        $desde_par = $this->argument('desde');
        $hasta_par = $this->argument('hasta');
        $indicador_par = $this->argument('indicador');
        $variedad_par = $this->argument('variedad');
        $planta_par = $this->argument('planta');

        if ($desde_par <= $hasta_par) {
            if ($desde_par != 0)
                $semana_desde = Semana::All()->where('estado', 1)->where('codigo', $desde_par)->first();
            else
                $semana_desde = getSemanaByDate(date('Y-m-d'));
            if ($hasta_par != 0)
                $semana_hasta = Semana::All()->where('estado', 1)->where('codigo', $hasta_par)->first();
            else
                $semana_hasta = getSemanaByDate(date('Y-m-d'));

            $variedades = DB::table('variedad')
                ->where('estado', 1);
            if ($planta_par > 0)
                $variedades = $variedades->where('id_planta', $planta_par);
            elseif ($variedad_par > 0)
                $variedades = $variedades->where('id_variedad', $variedad_par);
            $variedades = $variedades->get();

            Log::info('SEMANA PARAMETRO DESDE: ' . $desde_par . ' => ' . $semana_desde->codigo);
            Log::info('SEMANA PARAMETRO HASTA: ' . $hasta_par . ' => ' . $semana_hasta->codigo);

            $array_semanas = DB::table('semana')
                ->select('codigo', 'fecha_inicial', 'fecha_final')->distinct()
                ->where('estado', 1)
                ->where('codigo', '>=', $semana_desde->codigo)
                ->where('codigo', '<=', $semana_hasta->codigo)
                ->get();

            $empresas = ConfiguracionEmpresa::All();
            /* ========================== D11 Tallos cosechados (-7 dias) =========================== */
            if ($indicador_par == 'D11' || $indicador_par == 0) {
                dump('============ D11 Tallos cosechados (-7 dias) ============= ');
                foreach ($empresas as $pos_emp => $emp) {
                    $finca = $emp->id_configuracion_empresa;
                    $indicador = getIndicadorByName('D11-' . $finca);  // Tallos cosechados (-7 dias)
                    if ($indicador != '') {
                        foreach ($array_semanas as $pos_sem => $sem) {
                            dump('finca: ' . ($pos_emp + 1) . '/' . count($empresas) . ' - sem: ' . ($pos_sem + 1) . '/' . count($array_semanas));
                            $model = $indicador->getSemana($sem->codigo);
                            if ($model == '') {
                                $model = new IndicadorSemana();
                                $model->id_indicador = $indicador->id_indicador;
                                $model->codigo_semana = $sem->codigo;
                            }
                            $valor = $this->indicador_D11($sem, $finca, $model, $variedades);
                            $model->valor = $valor;
                            $model->save();
                        }
                    }
                }
            }

            /* ========================== D19 Tallos Vendidos (-1 semana) =========================== */
            if ($indicador_par == 'D19' || $indicador_par == 0) {
                dump('============ D19 Tallos Vendidos (-1 semana) ============= ');
                foreach ($empresas as $pos_emp => $emp) {
                    $finca = $emp->id_configuracion_empresa;
                    $indicador = getIndicadorByName('D19-' . $finca);  // Tallos Vendidos (-1 semana)
                    if ($indicador != '') {
                        foreach ($array_semanas as $pos_sem => $sem) {
                            dump('finca: ' . ($pos_emp + 1) . '/' . count($empresas) . ' - sem: ' . ($pos_sem + 1) . '/' . count($array_semanas));
                            $model = $indicador->getSemana($sem->codigo);
                            if ($model == '') {
                                $model = new IndicadorSemana();
                                $model->id_indicador = $indicador->id_indicador;
                                $model->codigo_semana = $sem->codigo;
                            }
                            $valor = $this->indicador_D19($sem, $finca, $model, $variedades);
                            $model->valor = $valor;
                            $model->save();
                        }
                    }
                }
            }

            /* ========================== D4 Dinero ingresado (-7 dias) =========================== */
            if ($indicador_par == 'D4' || $indicador_par == 0) {
                dump('============ D4 Dinero ingresado (-7 dias) ============= ');
                foreach ($empresas as $pos_emp => $emp) {
                    $finca = $emp->id_configuracion_empresa;
                    $indicador = getIndicadorByName('D4-' . $finca);  // Dinero ingresado (-7 dias)
                    if ($indicador != '') {
                        foreach ($array_semanas as $pos_sem => $sem) {
                            dump('finca: ' . ($pos_emp + 1) . '/' . count($empresas) . ' - sem: ' . ($pos_sem + 1) . '/' . count($array_semanas));
                            $model = $indicador->getSemana($sem->codigo);
                            if ($model == '') {
                                $model = new IndicadorSemana();
                                $model->id_indicador = $indicador->id_indicador;
                                $model->codigo_semana = $sem->codigo;
                            }
                            $valor = $this->indicador_D4($sem, $finca, $model, $variedades);
                            $model->valor = $valor;
                            $model->save();
                        }
                    }
                }
            }

            /* ========================== D14 Precio por tallo (-7 dias) =========================== */
            if ($indicador_par == 'D14' || $indicador_par == 0) {
                dump('============ D14 Precio por tallo (-7 dias) ============= ');
                foreach ($empresas as $pos_emp => $emp) {
                    $finca = $emp->id_configuracion_empresa;
                    $indicador = getIndicadorByName('D14-' . $finca);  // Precio por tallo (-7 dias)
                    if ($indicador != '') {
                        foreach ($array_semanas as $pos_sem => $sem) {
                            dump('finca: ' . ($pos_emp + 1) . '/' . count($empresas) . ' - sem: ' . ($pos_sem + 1) . '/' . count($array_semanas));
                            $model = $indicador->getSemana($sem->codigo);
                            if ($model == '') {
                                $model = new IndicadorSemana();
                                $model->id_indicador = $indicador->id_indicador;
                                $model->codigo_semana = $sem->codigo;
                            }
                            $valor = $this->indicador_D14($sem, $finca, $model, $variedades);
                            $model->valor = $valor;
                            $model->save();
                        }
                    }
                }
            }

            /* ========================== D12 Tallos/m2 (-4 semanas) =========================== */
            if ($indicador_par == 'D12' || $indicador_par == 0) {
                dump('============ D12 Tallos/m2 (-4 semanas) ============= ');
                foreach ($empresas as $pos_emp => $emp) {
                    $finca = $emp->id_configuracion_empresa;
                    $indicador = getIndicadorByName('D12-' . $finca);  // Tallos/m2 (-4 semanas)
                    if ($indicador != '') {
                        foreach ($array_semanas as $pos_sem => $sem) {
                            dump('finca: ' . ($pos_emp + 1) . '/' . count($empresas) . ' - sem: ' . ($pos_sem + 1) . '/' . count($array_semanas));
                            $model = $indicador->getSemana($sem->codigo);
                            if ($model == '') {
                                $model = new IndicadorSemana();
                                $model->id_indicador = $indicador->id_indicador;
                                $model->codigo_semana = $sem->codigo;
                            }
                            $valor = $this->indicador_D12($sem, $finca, $model, $variedades);
                            $model->valor = $valor;
                            $model->save();
                        }
                    }
                }
            }

            /* ========================== D18 Venta $/m2/anno (-4 semanas) =========================== */
            if ($indicador_par == 'D18' || $indicador_par == 0) {
                dump('============ D18 Venta $/m2/anno (-4 semanas) ============= ');
                foreach ($empresas as $pos_emp => $emp) {
                    $finca = $emp->id_configuracion_empresa;
                    $indicador = getIndicadorByName('D18-' . $finca);  // Venta $/m2/anno (-4 semanas)
                    if ($indicador != '') {
                        foreach ($array_semanas as $pos_sem => $sem) {
                            dump('finca: ' . ($pos_emp + 1) . '/' . count($empresas) . ' - sem: ' . ($pos_sem + 1) . '/' . count($array_semanas));
                            $model = $indicador->getSemana($sem->codigo);
                            if ($model == '') {
                                $model = new IndicadorSemana();
                                $model->id_indicador = $indicador->id_indicador;
                                $model->codigo_semana = $sem->codigo;
                            }
                            $valor = $this->indicador_D18($sem, $finca, $model, $variedades);
                            $model->valor = $valor;
                            $model->save();
                        }
                    }
                }
            }

            /* ========================== D9 Venta $/m2/anno (-13 semanas) =========================== */
            if ($indicador_par == 'D9' || $indicador_par == 0) {
                dump('============ D9 Venta $/m2/anno (-13 semanas) ============= ');
                foreach ($empresas as $pos_emp => $emp) {
                    $finca = $emp->id_configuracion_empresa;
                    $indicador = getIndicadorByName('D9-' . $finca);  // Venta $/m2/anno (-13 semanas)
                    if ($indicador != '') {
                        foreach ($array_semanas as $pos_sem => $sem) {
                            dump('finca: ' . ($pos_emp + 1) . '/' . count($empresas) . ' - sem: ' . ($pos_sem + 1) . '/' . count($array_semanas));
                            $model = $indicador->getSemana($sem->codigo);
                            if ($model == '') {
                                $model = new IndicadorSemana();
                                $model->id_indicador = $indicador->id_indicador;
                                $model->codigo_semana = $sem->codigo;
                            }
                            $valor = $this->indicador_D9($sem, $finca, $model, $variedades);
                            $model->valor = $valor;
                            $model->save();
                        }
                    }
                }
            }

            /* ========================== D10 Venta $/m2/anno (-52 semanas) =========================== */
            if ($indicador_par == 'D10' || $indicador_par == 0) {
                dump('============ D10 Venta $/m2/anno (-52 semanas) ============= ');
                foreach ($empresas as $pos_emp => $emp) {
                    $finca = $emp->id_configuracion_empresa;
                    $indicador = getIndicadorByName('D10-' . $finca);  // Venta $/m2/anno (-52 semanas)
                    if ($indicador != '') {
                        foreach ($array_semanas as $pos_sem => $sem) {
                            dump('finca: ' . ($pos_emp + 1) . '/' . count($empresas) . ' - sem: ' . ($pos_sem + 1) . '/' . count($array_semanas));
                            $model = $indicador->getSemana($sem->codigo);
                            if ($model == '') {
                                $model = new IndicadorSemana();
                                $model->id_indicador = $indicador->id_indicador;
                                $model->codigo_semana = $sem->codigo;
                            }
                            $valor = $this->indicador_D10($sem, $finca, $model, $variedades);
                            $model->valor = $valor;
                            $model->save();
                        }
                    }
                }
            }
        }

        $time_duration = difFechas(date('Y-m-d H:i:s'), $ini)->h . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->m . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->s;
        Log::info('<*> DURACION: ' . $time_duration . '  <*>');
        Log::info('<<<<< * >>>>> Fin satisfactorio del comando "indicador_semana:update" <<<<< * >>>>>');
        dump('<*> DURACION: ' . $time_duration . '  <*>');
        dump('<<<<< * >>>>> Fin satisfactorio del comando "indicador_semana:update" <<<<< * >>>>>');
    }

    function indicador_D11($sem, $finca, $indicador, $variedades)
    {
        /* ------------------------ VARIEDADES ------------------------ */
        foreach ($variedades as $pos_var => $var) {
            dump('var: ' . ($pos_var + 1) . '/' . count($variedades));
            $ind_var = IndicadorVariedad::All()
                ->where('id_indicador', $indicador->id_indicador)
                ->where('id_variedad', $var->id_variedad)
                ->first();
            if ($ind_var == '') {
                $ind_var = new IndicadorVariedad();
                $ind_var->id_indicador = $indicador->id_indicador;
                $ind_var->id_variedad = $var->id_variedad;
                $ind_var->valor = 0;
                $ind_var->save();
                $ind_var = IndicadorVariedad::All()->last();
            }
            if ($ind_var != '') {
                $model = $ind_var->getSemana($sem->codigo);
                if ($model == '') {
                    $model = new IndicadorVariedadSemana();
                    $model->id_indicador_variedad = $ind_var->id_indicador_variedad;
                    $model->codigo_semana = $sem->codigo;
                }
                $valor = DB::table('cosecha_diaria')
                    ->select(DB::raw('sum(cosechados) as cantidad'))
                    ->where('id_empresa', $finca)
                    ->where('id_variedad', $var->id_variedad)
                    ->where('fecha', '>=', $sem->fecha_inicial)
                    ->where('fecha', '<=', $sem->fecha_final)
                    ->get()[0]->cantidad;
                $valor = $valor > 0 ? $valor : 0;
                $model->valor = $valor;
                $model->save();
            }
        }

        /* ------------------------ VALOR INDICADOR ------------------------ */
        $valor = DB::table('cosecha_diaria')
            ->select(DB::raw('sum(cosechados) as cantidad'))
            ->where('id_empresa', $finca)
            ->where('fecha', '>=', $sem->fecha_inicial)
            ->where('fecha', '<=', $sem->fecha_final)
            ->get()[0]->cantidad;
        $valor = $valor > 0 ? $valor : 0;
        return $valor;
    }

    function indicador_D19($sem, $finca, $indicador, $variedades)
    {
        /* ------------------------ VARIEDADES ------------------------ */
        foreach ($variedades as $pos_var => $var) {
            dump('var: ' . ($pos_var + 1) . '/' . count($variedades));
            $ind_var = IndicadorVariedad::All()
                ->where('id_indicador', $indicador->id_indicador)
                ->where('id_variedad', $var->id_variedad)
                ->first();
            if ($ind_var == '') {
                $ind_var = new IndicadorVariedad();
                $ind_var->id_indicador = $indicador->id_indicador;
                $ind_var->id_variedad = $var->id_variedad;
                $ind_var->valor = 0;
                $ind_var->save();
                $ind_var = IndicadorVariedad::All()->last();
            }
            if ($ind_var != '') {
                $model = $ind_var->getSemana($sem->codigo);
                if ($model == '') {
                    $model = new IndicadorVariedadSemana();
                    $model->id_indicador_variedad = $ind_var->id_indicador_variedad;
                    $model->codigo_semana = $sem->codigo;
                }
                $valor = DB::table('resumen_total_semanal_exportcalas')
                    ->select(DB::raw('sum(tallos_vendidos) as cantidad'))
                    ->where('semana', $sem->codigo)
                    ->where('id_empresa', $finca)
                    ->where('id_variedad', $var->id_variedad)
                    ->get()[0]->cantidad;
                $valor = $valor > 0 ? $valor : 0;
                $model->valor = $valor;
                $model->save();
            }
        }

        /* ------------------------ VALOR INDICADOR ------------------------ */
        $valor = DB::table('resumen_total_semanal_exportcalas')
            ->select(DB::raw('sum(tallos_vendidos) as cantidad'))
            ->where('semana', $sem->codigo)
            ->where('id_empresa', $finca)
            ->get()[0]->cantidad;
        return $valor > 0 ? $valor : 0;
    }

    function indicador_D4($sem, $finca, $indicador, $variedades)
    {
        /* ------------------------ VARIEDADES ------------------------ */
        foreach ($variedades as $pos_var => $var) {
            dump('var: ' . ($pos_var + 1) . '/' . count($variedades));
            $ind_var = IndicadorVariedad::All()
                ->where('id_indicador', $indicador->id_indicador)
                ->where('id_variedad', $var->id_variedad)
                ->first();
            if ($ind_var == '') {
                $ind_var = new IndicadorVariedad();
                $ind_var->id_indicador = $indicador->id_indicador;
                $ind_var->id_variedad = $var->id_variedad;
                $ind_var->valor = 0;
                $ind_var->save();
                $ind_var = IndicadorVariedad::All()->last();
            }
            if ($ind_var != '') {
                $model = $ind_var->getSemana($sem->codigo);
                if ($model == '') {
                    $model = new IndicadorVariedadSemana();
                    $model->id_indicador_variedad = $ind_var->id_indicador_variedad;
                    $model->codigo_semana = $sem->codigo;
                }
                $valor = DB::table('resumen_total_semanal_exportcalas')
                    ->select(DB::raw('sum(venta) as venta'))
                    ->where('semana', $sem->codigo)
                    ->where('id_empresa', $finca)
                    ->where('id_variedad', $var->id_variedad)
                    ->get()[0]->venta;
                $valor = $valor > 0 ? $valor : 0;
                $model->valor = $valor;
                $model->save();
            }
        }

        /* ------------------------ VALOR INDICADOR ------------------------ */
        $valor = DB::table('resumen_total_semanal_exportcalas')
            ->select(DB::raw('sum(venta) as venta'))
            ->where('semana', $sem->codigo)
            ->where('id_empresa', $finca)
            ->get()[0]->venta;
        return $valor > 0 ? $valor : 0;
    }

    function indicador_D14($sem, $finca, $indicador, $variedades)
    {
        /* ------------------------ VARIEDADES ------------------------ */
        foreach ($variedades as $pos_var => $var) {
            dump('var: ' . ($pos_var + 1) . '/' . count($variedades));
            $ind_var = IndicadorVariedad::All()
                ->where('id_indicador', $indicador->id_indicador)
                ->where('id_variedad', $var->id_variedad)
                ->first();
            if ($ind_var == '') {
                $ind_var = new IndicadorVariedad();
                $ind_var->id_indicador = $indicador->id_indicador;
                $ind_var->id_variedad = $var->id_variedad;
                $ind_var->valor = 0;
                $ind_var->save();
                $ind_var = IndicadorVariedad::All()->last();
            }
            if ($ind_var != '') {
                $model = $ind_var->getSemana($sem->codigo);
                if ($model == '') {
                    $model = new IndicadorVariedadSemana();
                    $model->id_indicador_variedad = $ind_var->id_indicador_variedad;
                    $model->codigo_semana = $sem->codigo;
                }
                $valor = DB::table('resumen_total_semanal_exportcalas')
                    ->select(DB::raw('sum(venta) as venta'), DB::raw('sum(tallos_vendidos) as tallos_vendidos'))
                    ->where('semana', $sem->codigo)
                    ->where('id_empresa', $finca)
                    ->where('id_variedad', $var->id_variedad)
                    ->get()[0];
                $valor = ($valor != '' && $valor->tallos_vendidos > 0) ? round($valor->venta / $valor->tallos_vendidos, 2) : 0;
                $model->valor = $valor;
                $model->save();
            }
        }

        /* ------------------------ VALOR INDICADOR ------------------------ */
        $valor = DB::table('resumen_total_semanal_exportcalas')
            ->select(DB::raw('sum(venta) as venta'), DB::raw('sum(tallos_vendidos) as tallos_vendidos'))
            ->where('semana', $sem->codigo)
            ->where('id_empresa', $finca)
            ->get()[0];
        return ($valor != '' && $valor->tallos_vendidos > 0) ? round($valor->venta / $valor->tallos_vendidos, 2) : 0;
    }

    function indicador_D12($sem, $finca, $indicador, $variedades)
    {
        $desde = opDiasFecha('-', 28, $sem->fecha_inicial);
        $hasta = opDiasFecha('-', 7, $sem->fecha_final);

        /* ------------------------ VARIEDADES ------------------------ */
        foreach ($variedades as $pos_var => $var) {
            dump('var: ' . ($pos_var + 1) . '/' . count($variedades));
            $ind_var = IndicadorVariedad::All()
                ->where('id_indicador', $indicador->id_indicador)
                ->where('id_variedad', $var->id_variedad)
                ->first();
            if ($ind_var == '') {
                $ind_var = new IndicadorVariedad();
                $ind_var->id_indicador = $indicador->id_indicador;
                $ind_var->id_variedad = $var->id_variedad;
                $ind_var->valor = 0;
                $ind_var->save();
                $ind_var = IndicadorVariedad::All()->last();
            }
            if ($ind_var != '') {
                $model = $ind_var->getSemana($sem->codigo);
                if ($model == '') {
                    $model = new IndicadorVariedadSemana();
                    $model->id_indicador_variedad = $ind_var->id_indicador_variedad;
                    $model->codigo_semana = $sem->codigo;
                }
                $cantidades = DB::table('ciclo as c')
                    ->leftJoin('resumen_fenograma_ejecucion as f', 'f.id_ciclo', '=', 'c.id_ciclo')
                    ->select(DB::raw('sum(c.area) as area'), DB::raw('sum(f.tallos_cosechados) as tallos_cosechados'))
                    ->where('c.activo', 0)
                    ->where('c.fecha_fin', '>=', $desde)
                    ->where('c.fecha_fin', '<=', $hasta)
                    ->where('c.id_empresa', $finca)
                    ->where('c.id_variedad', $var->id_variedad)
                    ->get()[0];
                $tallos = $cantidades->tallos_cosechados > 0 ? $cantidades->tallos_cosechados : 0;
                $area = $cantidades->area > 0 ? $cantidades->area : 0;
                $valor = $area > 0 ? round($tallos / $area, 2) : 0;
                $model->valor = $valor;
                $model->save();
            }
        }

        /* ------------------------ VALOR INDICADOR ------------------------ */
        $cantidades = DB::table('ciclo as c')
            ->leftJoin('resumen_fenograma_ejecucion as f', 'f.id_ciclo', '=', 'c.id_ciclo')
            ->select(DB::raw('sum(c.area) as area'), DB::raw('sum(f.tallos_cosechados) as tallos_cosechados'))
            ->where('c.activo', 0)
            ->where('c.fecha_fin', '>=', $desde)
            ->where('c.fecha_fin', '<=', $hasta)
            ->where('c.id_empresa', $finca)
            ->get()[0];
        $tallos = $cantidades->tallos_cosechados > 0 ? $cantidades->tallos_cosechados : 0;
        $area = $cantidades->area > 0 ? $cantidades->area : 0;
        $valor = $area > 0 ? round($tallos / $area, 2) : 0;
        return $valor;
    }

    function indicador_D18($sem, $finca, $indicador, $variedades)
    {
        $desde = getSemanaByDate(opDiasFecha('-', 28, $sem->fecha_inicial));
        $hasta = getSemanaByDate(opDiasFecha('-', 7, $sem->fecha_inicial));

        /* ------------------------ VARIEDADES ------------------------ */
        foreach ($variedades as $pos_var => $var) {
            dump('var: ' . ($pos_var + 1) . '/' . count($variedades));
            $ind_var = IndicadorVariedad::All()
                ->where('id_indicador', $indicador->id_indicador)
                ->where('id_variedad', $var->id_variedad)
                ->first();
            if ($ind_var == '') {
                $ind_var = new IndicadorVariedad();
                $ind_var->id_indicador = $indicador->id_indicador;
                $ind_var->id_variedad = $var->id_variedad;
                $ind_var->valor = 0;
                $ind_var->save();
                $ind_var = IndicadorVariedad::All()->last();
            }
            if ($ind_var != '') {
                $model = $ind_var->getSemana($sem->codigo);
                if ($model == '') {
                    $model = new IndicadorVariedadSemana();
                    $model->id_indicador_variedad = $ind_var->id_indicador_variedad;
                    $model->codigo_semana = $sem->codigo;
                }
                $venta = DB::table('resumen_total_semanal_exportcalas')
                    ->select(DB::raw('sum(venta) as venta'))
                    ->where('semana', '>=', $desde->codigo)
                    ->where('semana', '<=', $hasta->codigo)
                    ->where('id_variedad', $var->id_variedad)
                    ->where('id_empresa', $finca)
                    ->get()[0]->venta;
                $area = DB::table('ciclo')
                    ->select(DB::raw('sum(area) as area'))
                    ->where('estado', '=', 1)
                    ->where('id_empresa', $finca)
                    ->where('id_variedad', $var->id_variedad)
                    ->Where(function ($q) use ($desde, $hasta) {
                        $q->where('fecha_fin', '>=', $desde->fecha_inicial)
                            ->where('fecha_fin', '<=', $hasta->fecha_final)
                            ->orWhere(function ($q) use ($desde, $hasta) {
                                $q->where('fecha_inicio', '>=', $desde->fecha_inicial)
                                    ->where('fecha_inicio', '<=', $hasta->fecha_final);
                            })
                            ->orWhere(function ($q) use ($desde, $hasta) {
                                $q->where('fecha_inicio', '<', $desde->fecha_inicial)
                                    ->where('fecha_fin', '>', $hasta->fecha_final);
                            });
                    })
                    ->get()[0]->area;
                $valor = ($venta > 0 && $area > 0) ? round($venta / $area, 2) : 0;
                $valor = $valor * 13;
                $model->valor = $valor;
                $model->save();
            }
        }

        /* ------------------------ VALOR INDICADOR ------------------------ */
        $venta = DB::table('resumen_total_semanal_exportcalas')
            ->select(DB::raw('sum(venta) as venta'))
            ->where('semana', '>=', $desde->codigo)
            ->where('semana', '<=', $hasta->codigo)
            ->where('id_empresa', $finca)
            ->get()[0]->venta;
        $area = DB::table('ciclo')
            ->select(DB::raw('sum(area) as area'))
            ->where('estado', '=', 1)
            ->where('id_empresa', $finca)
            ->Where(function ($q) use ($desde, $hasta) {
                $q->where('fecha_fin', '>=', $desde->fecha_inicial)
                    ->where('fecha_fin', '<=', $hasta->fecha_final)
                    ->orWhere(function ($q) use ($desde, $hasta) {
                        $q->where('fecha_inicio', '>=', $desde->fecha_inicial)
                            ->where('fecha_inicio', '<=', $hasta->fecha_final);
                    })
                    ->orWhere(function ($q) use ($desde, $hasta) {
                        $q->where('fecha_inicio', '<', $desde->fecha_inicial)
                            ->where('fecha_fin', '>', $hasta->fecha_final);
                    });
            })
            ->get()[0]->area;
        $valor = ($venta > 0 && $area > 0) ? round($venta / $area, 2) : 0;
        $valor = $valor * 13;
        return $valor;
    }

    function indicador_D9($sem, $finca, $indicador, $variedades)
    {
        $desde = getSemanaByDate(opDiasFecha('-', 112, $sem->fecha_inicial));
        $hasta = getSemanaByDate(opDiasFecha('-', 7, $sem->fecha_inicial));

        /* ------------------------ VARIEDADES ------------------------ */
        foreach ($variedades as $pos_var => $var) {
            dump('var: ' . ($pos_var + 1) . '/' . count($variedades));
            $ind_var = IndicadorVariedad::All()
                ->where('id_indicador', $indicador->id_indicador)
                ->where('id_variedad', $var->id_variedad)
                ->first();
            if ($ind_var == '') {
                $ind_var = new IndicadorVariedad();
                $ind_var->id_indicador = $indicador->id_indicador;
                $ind_var->id_variedad = $var->id_variedad;
                $ind_var->valor = 0;
                $ind_var->save();
                $ind_var = IndicadorVariedad::All()->last();
            }
            if ($ind_var != '') {
                $model = $ind_var->getSemana($sem->codigo);
                if ($model == '') {
                    $model = new IndicadorVariedadSemana();
                    $model->id_indicador_variedad = $ind_var->id_indicador_variedad;
                    $model->codigo_semana = $sem->codigo;
                }
                $venta = DB::table('resumen_total_semanal_exportcalas')
                    ->select(DB::raw('sum(venta) as venta'))
                    ->where('semana', '>=', $desde->codigo)
                    ->where('semana', '<=', $hasta->codigo)
                    ->where('id_variedad', $var->id_variedad)
                    ->where('id_empresa', $finca)
                    ->get()[0]->venta;
                $area = DB::table('ciclo')
                    ->select(DB::raw('sum(area) as area'))
                    ->where('estado', '=', 1)
                    ->where('id_empresa', $finca)
                    ->where('id_variedad', $var->id_variedad)
                    ->Where(function ($q) use ($desde, $hasta) {
                        $q->where('fecha_fin', '>=', $desde->fecha_inicial)
                            ->where('fecha_fin', '<=', $hasta->fecha_final)
                            ->orWhere(function ($q) use ($desde, $hasta) {
                                $q->where('fecha_inicio', '>=', $desde->fecha_inicial)
                                    ->where('fecha_inicio', '<=', $hasta->fecha_final);
                            })
                            ->orWhere(function ($q) use ($desde, $hasta) {
                                $q->where('fecha_inicio', '<', $desde->fecha_inicial)
                                    ->where('fecha_fin', '>', $hasta->fecha_final);
                            });
                    })
                    ->get()[0]->area;
                $valor = ($venta > 0 && $area > 0) ? round($venta / $area, 2) : 0;
                $valor = $valor * 4;
                $model->valor = $valor;
                $model->save();
            }
        }

        /* ------------------------ VALOR INDICADOR ------------------------ */
        $venta = DB::table('resumen_total_semanal_exportcalas')
            ->select(DB::raw('sum(venta) as venta'))
            ->where('semana', '>=', $desde->codigo)
            ->where('semana', '<=', $hasta->codigo)
            ->where('id_empresa', $finca)
            ->get()[0]->venta;
        $area = DB::table('ciclo')
            ->select(DB::raw('sum(area) as area'))
            ->where('estado', '=', 1)
            ->where('id_empresa', $finca)
            ->Where(function ($q) use ($desde, $hasta) {
                $q->where('fecha_fin', '>=', $desde->fecha_inicial)
                    ->where('fecha_fin', '<=', $hasta->fecha_final)
                    ->orWhere(function ($q) use ($desde, $hasta) {
                        $q->where('fecha_inicio', '>=', $desde->fecha_inicial)
                            ->where('fecha_inicio', '<=', $hasta->fecha_final);
                    })
                    ->orWhere(function ($q) use ($desde, $hasta) {
                        $q->where('fecha_inicio', '<', $desde->fecha_inicial)
                            ->where('fecha_fin', '>', $hasta->fecha_final);
                    });
            })
            ->get()[0]->area;
        $valor = ($venta > 0 && $area > 0) ? round($venta / $area, 2) : 0;
        $valor = $valor * 4;
        return $valor;
    }

    function indicador_D10($sem, $finca, $indicador, $variedades)
    {
        $desde = getSemanaByDate(opDiasFecha('-', 364, $sem->fecha_inicial));
        $hasta = getSemanaByDate(opDiasFecha('-', 7, $sem->fecha_inicial));

        /* ------------------------ VARIEDADES ------------------------ */
        foreach ($variedades as $pos_var => $var) {
            dump('var: ' . ($pos_var + 1) . '/' . count($variedades));
            $ind_var = IndicadorVariedad::All()
                ->where('id_indicador', $indicador->id_indicador)
                ->where('id_variedad', $var->id_variedad)
                ->first();
            if ($ind_var == '') {
                $ind_var = new IndicadorVariedad();
                $ind_var->id_indicador = $indicador->id_indicador;
                $ind_var->id_variedad = $var->id_variedad;
                $ind_var->valor = 0;
                $ind_var->save();
                $ind_var = IndicadorVariedad::All()->last();
            }
            if ($ind_var != '') {
                $model = $ind_var->getSemana($sem->codigo);
                if ($model == '') {
                    $model = new IndicadorVariedadSemana();
                    $model->id_indicador_variedad = $ind_var->id_indicador_variedad;
                    $model->codigo_semana = $sem->codigo;
                }
                $venta = DB::table('resumen_total_semanal_exportcalas')
                    ->select(DB::raw('sum(venta) as venta'))
                    ->where('semana', '>=', $desde->codigo)
                    ->where('semana', '<=', $hasta->codigo)
                    ->where('id_variedad', $var->id_variedad)
                    ->where('id_empresa', $finca)
                    ->get()[0]->venta;
                $area = DB::table('ciclo')
                    ->select(DB::raw('sum(area) as area'))
                    ->where('estado', '=', 1)
                    ->where('id_empresa', $finca)
                    ->where('id_variedad', $var->id_variedad)
                    ->Where(function ($q) use ($desde, $hasta) {
                        $q->where('fecha_fin', '>=', $desde->fecha_inicial)
                            ->where('fecha_fin', '<=', $hasta->fecha_final)
                            ->orWhere(function ($q) use ($desde, $hasta) {
                                $q->where('fecha_inicio', '>=', $desde->fecha_inicial)
                                    ->where('fecha_inicio', '<=', $hasta->fecha_final);
                            })
                            ->orWhere(function ($q) use ($desde, $hasta) {
                                $q->where('fecha_inicio', '<', $desde->fecha_inicial)
                                    ->where('fecha_fin', '>', $hasta->fecha_final);
                            });
                    })
                    ->get()[0]->area;
                $valor = ($venta > 0 && $area > 0) ? round($venta / $area, 2) : 0;
                $valor = $valor * 1;
                $model->valor = $valor;
                $model->save();
            }
        }

        /* ------------------------ VALOR INDICADOR ------------------------ */
        $venta = DB::table('resumen_total_semanal_exportcalas')
            ->select(DB::raw('sum(venta) as venta'))
            ->where('semana', '>=', $desde->codigo)
            ->where('semana', '<=', $hasta->codigo)
            ->where('id_empresa', $finca)
            ->get()[0]->venta;
        $area = DB::table('ciclo')
            ->select(DB::raw('sum(area) as area'))
            ->where('estado', '=', 1)
            ->where('id_empresa', $finca)
            ->Where(function ($q) use ($desde, $hasta) {
                $q->where('fecha_fin', '>=', $desde->fecha_inicial)
                    ->where('fecha_fin', '<=', $hasta->fecha_final)
                    ->orWhere(function ($q) use ($desde, $hasta) {
                        $q->where('fecha_inicio', '>=', $desde->fecha_inicial)
                            ->where('fecha_inicio', '<=', $hasta->fecha_final);
                    })
                    ->orWhere(function ($q) use ($desde, $hasta) {
                        $q->where('fecha_inicio', '<', $desde->fecha_inicial)
                            ->where('fecha_fin', '>', $hasta->fecha_final);
                    });
            })
            ->get()[0]->area;
        $valor = ($venta > 0 && $area > 0) ? round($venta / $area, 2) : 0;
        $valor = $valor * 1;
        return $valor;
    }
}