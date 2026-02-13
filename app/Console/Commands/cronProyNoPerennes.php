<?php

namespace yura\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use yura\Modelos\ConfiguracionEmpresa;
use yura\Modelos\ProyNoPerennes;
use yura\Modelos\Semana;
use yura\Modelos\Variedad;

class cronProyNoPerennes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:proy_no_perennes {desde=0} {hasta=0} {variedad=0} {empresa=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comando para llenar la tabla proy_no_perennes';

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
        dump('<<<<< ! >>>>> Ejecutando comando "cron:proy_no_perennes" <<<<< ! >>>>>');
        Log::info('<<<<< ! >>>>> Ejecutando comando "cron:proy_no_perennes" <<<<< ! >>>>>');

        $desde_par = $this->argument('desde');
        $hasta_par = $this->argument('hasta');
        $variedad_par = $this->argument('variedad');
        $empresa_par = $this->argument('empresa');

        $variedades = Variedad::All()->where('estado', 1);
        if ($variedad_par != 0)
            $variedades = $variedades->where('id_variedad', $variedad_par);

        $empresas = ConfiguracionEmpresa::All();
        if ($empresa_par != 0)
            $empresas = $empresas->where('id_configuracion_empresa', $empresa_par);

        foreach ($empresas as $pos_e => $empresa) {
            $finca = $empresa->id_configuracion_empresa;
            foreach ($variedades as $pos_v => $var) {
                $semanas = Semana::where('estado', 1)
                    ->where('id_variedad', $var->id_variedad);
                if ($desde_par != 0)
                    $semanas = $semanas->where('codigo', '>=', $desde_par);
                else
                    $semanas = $semanas->where('codigo', '>=', getSemanaByDate(hoy())->codigo);

                if ($hasta_par != 0)
                    $semanas = $semanas->where('codigo', '<=', $hasta_par);
                else
                    $semanas = $semanas->where('codigo', '<=', getLastSemanaByVariedad($var->id_variedad)->codigo);

                $semanas = $semanas->orderBy('codigo')->get();

                foreach ($semanas as $pos_s => $sem) {
                    dump('finca: '.$pos_e.'/'.count($empresas).' - var: '.$pos_v.'/'.count($variedades).' - sem: '.$pos_s.'/'.count($semanas));
                    $semanas_prev = DB::select('select * from semana 
                            where fecha_inicial >= "' . opDiasFecha('-', (25 * 7), $sem->fecha_inicial) . '" and codigo < ' . $sem->codigo . ' and id_variedad = ' . $var->id_variedad . ' 
                            order by codigo');
                    $area_produccion = 0;
                    $area_cutivo = 0;
                    $proyectados = 0;
                    foreach ($semanas_prev as $prev) {
                        $max_semanas = max($prev->semana_poda, $prev->semana_siembra);
                        $curva_siembra = explode('-', $prev->curva);
                        $curva_poda = explode('-', $prev->curva_poda);
                        $max_curvas = max(count($curva_siembra), count($curva_poda));
                        $fecha_fin = opDiasFecha('+', ($max_semanas + $max_curvas - 1) * 7, $prev->fecha_final);
                        if ($fecha_fin >= $sem->fecha_inicial) {
                            $prev = Semana::find($prev->id_semana);
                            $getSemanaEmpresaS = $prev->getSemanaEmpresa($finca, 'S');
                            $getSemanaEmpresaP = $prev->getSemanaEmpresa($finca, 'P');

                            $area_siembra = isset($getSemanaEmpresaS) && $getSemanaEmpresaS->densidad > 0 ? $getSemanaEmpresaS->plantas_iniciales / $getSemanaEmpresaS->densidad : 0;
                            $area_poda = isset($getSemanaEmpresaP) && $getSemanaEmpresaP->densidad > 0 ? $getSemanaEmpresaP->plantas_iniciales / $getSemanaEmpresaP->densidad : 0;
                            $area_produccion += $area_siembra + $area_poda;

                            if ($prev->semana_siembra > 0) {
                                $ptas_iniciales = isset($getSemanaEmpresaS) ? $getSemanaEmpresaS->plantas_iniciales : 0;
                                foreach ($curva_siembra as $i => $cv) {
                                    $fecha_meta = opDiasFecha('+', ($prev->semana_siembra + $i) * 7, $prev->fecha_inicial);
                                    if ($fecha_meta == $sem->fecha_inicial) {
                                        $area_cutivo += $area_siembra;

                                        $total = $ptas_iniciales * $prev->tallos_planta_siembra;
                                        $total = $total * ((100 - $prev->desecho) / 100);
                                        if($cv > 0)
                                            $proyectados += round($total * ($cv / 100), 2);
                                    }
                                }
                            }

                            if ($prev->semana_poda > 0) {
                                $ptas_iniciales = isset($getSemanaEmpresaP) ? $getSemanaEmpresaP->plantas_iniciales : 0;
                                foreach ($curva_poda as $i => $cv) {
                                    $fecha_meta = opDiasFecha('+', ($prev->semana_poda + $i) * 7, $prev->fecha_inicial);
                                    if ($fecha_meta == $sem->fecha_inicial) {
                                        $area_cutivo += $area_poda;

                                        $total = $ptas_iniciales * $prev->tallos_planta_poda;
                                        $total = $total * ((100 - $prev->desecho_poda) / 100);
                                        if($cv > 0)
                                            $proyectados += round($total * ($cv / 100), 2);
                                    }
                                }
                            }
                        }
                    }
                    $getSemanaEmpresaS = $sem->getSemanaEmpresa($finca, 'S');
                    $getSemanaEmpresaP = $sem->getSemanaEmpresa($finca, 'P');

                    $area_produccion += isset($getSemanaEmpresaS) && $getSemanaEmpresaS->densidad > 0 ? $getSemanaEmpresaS->plantas_iniciales / $getSemanaEmpresaS->densidad : 0;
                    $area_produccion += isset($getSemanaEmpresaP) && $getSemanaEmpresaP->densidad > 0 ? $getSemanaEmpresaP->plantas_iniciales / $getSemanaEmpresaP->densidad : 0;

                    $getProyNoPerennesByEmpresa = $sem->getProyNoPerennesByEmpresa($finca);
                    if (!isset($getProyNoPerennesByEmpresa)) {
                        $getProyNoPerennesByEmpresa = new ProyNoPerennes();
                        $getProyNoPerennesByEmpresa->id_semana = $sem->id_semana;
                        $getProyNoPerennesByEmpresa->id_empresa = $finca;
                        $getProyNoPerennesByEmpresa->save();
                        $getProyNoPerennesByEmpresa = $sem->getProyNoPerennesByEmpresa($finca);
                    }
                    $getProyNoPerennesByEmpresa->area_produccion = round($area_produccion, 2);
                    $getProyNoPerennesByEmpresa->area_semana = round($area_cutivo, 2);
                    $getProyNoPerennesByEmpresa->proyectados = $proyectados;
                    $getProyNoPerennesByEmpresa->save();
                }
            }
        }

        $time_duration = difFechas(date('Y-m-d H:i:s'), $ini)->h . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->m . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->s;
        dump('<*> DURACION: ' . $time_duration . '  <*>');
        dump('<<<<< * >>>>> Fin satisfactorio del comando "cron:proy_no_perennes" <<<<< * >>>>>');
        Log::info('<*> DURACION: ' . $time_duration . '  <*>');
        Log::info('<<<<< * >>>>> Fin satisfactorio del comando "cron:proy_no_perennes" <<<<< * >>>>>');
    }
}