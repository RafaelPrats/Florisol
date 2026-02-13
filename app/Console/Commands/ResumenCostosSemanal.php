<?php

namespace yura\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use yura\Modelos\ActividadManoObra;
use yura\Modelos\ActividadProducto;
use yura\Modelos\ConfiguracionEmpresa;
use yura\Modelos\CostosSemana;
use yura\Modelos\CostosSemanaManoObra;
use yura\Modelos\ManoObra;
use yura\Modelos\Semana;
use yura\Modelos\ResumenCostosSemanal as ModelResumen;

class ResumenCostosSemanal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'costos:update_semanal {desde=0} {hasta=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comando para resumir los costos por semana';

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
        dump('<<<<< ! >>>>> Ejecutando comando "costos:update_semanal" <<<<< ! >>>>>');
        Log::info('<<<<< ! >>>>> Ejecutando comando "costos:update_semanal" <<<<< ! >>>>>');


        $desde_par = $this->argument('desde') != 0 ? $this->argument('desde') : getSemanaByDate(opDiasFecha('-', 49, date('Y-m-d')))->codigo;
        $hasta_par = $this->argument('hasta') != 0 ? $this->argument('hasta') : getSemanaByDate(date('Y-m-d'))->codigo;

        $array_semanas = DB::table('semana')
            ->select('codigo', 'fecha_inicial', 'fecha_final')->distinct()
            ->where('estado', 1)
            ->where('codigo', '>=', $desde_par)
            ->where('codigo', '<=', $hasta_par)
            ->get();

        $empresas = ConfiguracionEmpresa::All();
        foreach ($empresas as $pos_e => $empresa) {
            $finca = $empresa->id_configuracion_empresa;
            foreach ($array_semanas as $pos_s => $semana) {
                dump('finca : ' . ($pos_e + 1) . '/' . count($empresas) . ' - sem: ' . ($pos_s + 1) . '/' . count($array_semanas));
                $mano_obra = DB::table('costos_semana_mano_obra')
                    ->select(DB::raw('sum(valor) as cant'))
                    ->where('codigo_semana', $semana->codigo)
                    ->where('id_empresa', $finca)
                    ->get()[0]->cant;
                $insumos = DB::table('costos_semana')
                    ->select(DB::raw('sum(valor) as cant'))
                    ->where('codigo_semana', $semana->codigo)
                    ->where('id_empresa', $finca)
                    ->get()[0]->cant;
                $fijos = DB::table('otros_gastos')
                    ->select(DB::raw('sum(gip + ga) as cant'))
                    ->where('codigo_semana', $semana->codigo)
                    ->where('id_empresa', $finca)
                    ->get()[0]->cant;
                $regalias = DB::table('otros_gastos')
                    ->select(DB::raw('sum(regalias) as cant'))
                    ->where('codigo_semana', $semana->codigo)
                    ->where('id_empresa', $finca)
                    ->get()[0]->cant;
                $compra_flor = DB::table('bouquetera')
                    ->select(DB::raw('sum(precio * (tallos + exportada)) as cant'))
                    ->where('fecha', '>=', $semana->fecha_inicial)
                    ->where('fecha', '<=', $semana->fecha_final)
                    ->where('id_empresa', $finca)
                    ->get()[0]->cant;

                $resumen = ModelResumen::All()
                    ->where('codigo_semana', $semana->codigo)
                    ->where('id_empresa', $finca)
                    ->first();
                if ($resumen == '') {   // es nuevo
                    $resumen = new ModelResumen();
                    $resumen->codigo_semana = $semana->codigo;
                    $resumen->id_empresa = $finca;
                }
                $resumen->mano_obra = $mano_obra != '' ? $mano_obra : 0;
                $resumen->insumos = $insumos != '' ? $insumos : 0;
                $resumen->fijos = $fijos != '' ? $fijos : 0;
                $resumen->regalias = $regalias != '' ? $regalias : 0;
                $resumen->compra_flor = $compra_flor != '' ? $compra_flor : 0;
                $resumen->save();
            }
        }

        $time_duration = difFechas(date('Y-m-d H:i:s'), $ini)->h . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->m . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->s;
        Log::info('<*> DURACION: ' . $time_duration . '  <*>');
        Log::info('<<<<< * >>>>> Fin satisfactorio del comando "costos:update_semanal" <<<<< * >>>>>');
    }
}