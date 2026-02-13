<?php

namespace yura\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use yura\Modelos\Ciclo;
use yura\Modelos\ConfiguracionEmpresa;
use yura\Modelos\Modulo;
use yura\Modelos\ProyeccionModuloSemana;
use yura\Modelos\Sector;
use yura\Modelos\Semana;
use yura\Modelos\SemanaEmpresa;
use yura\Modelos\Variedad;

class cronGenerarProyFuturas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:generar_proy_futuras {variedad=0} {desde=0} {hasta=0} {empresa=0} {dev=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comando para generar los ciclos desde lo programado en SEMANA';

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
        $dev_par = $this->argument('dev');
        $ini = date('Y-m-d H:i:s');
        if ($dev_par == 1)
            dump('<<<<< ! >>>>> Ejecutando comando "cron:generar_proy_futuras" <<<<< ! >>>>>');
        Log::info('<<<<< ! >>>>> Ejecutando comando "cron:generar_proy_futuras" <<<<< ! >>>>>');

        $variedad_par = $this->argument('variedad');
        $desde_par = $this->argument('desde');
        $hasta_par = $this->argument('hasta');
        $empresa_par = $this->argument('empresa');

        if ($desde_par == 0)
            $desde_par = getSemanaByDate(hoy())->codigo;
        if ($hasta_par == 0)
            $hasta_par = getSemanaByDate(opDiasFecha('+', 70, hoy()))->codigo;

        $empresas = ConfiguracionEmpresa::All();
        if ($empresa_par != 0)
            $empresas = $empresas->where('id_configuracion_empresa', $empresa_par);
        $empresas = $empresas->sortBy('nombre');

        $variedades = Variedad::where('estado', 1);
        if ($variedad_par != 0)
            $variedades = $variedades->where('id_variedad', $variedad_par);
        $variedades = $variedades->orderBy('nombre')->get();

        foreach ($variedades as $pos_v => $var) {
            $semanas = DB::select('select *
                        from semana 
                        where codigo >= ' . $desde_par . ' and codigo <= ' . $hasta_par . ' 
                            and id_variedad = ' . $var->id_variedad . ' and estado = 1
                        order by codigo');
            foreach ($semanas as $pos_s => $sem) {
                foreach ($empresas as $pos_e => $e) {
                    if ($dev_par == 1)
                        dump('var: ' . $pos_v . '/' . count($variedades) . '; sem: ' . $pos_s . '/' . count($semanas) . '; emp: ' . $pos_e . '/' . count($empresas));
                    $finca = $e->id_configuracion_empresa;
                    $se = SemanaEmpresa::All()
                        ->where('id_empresa', $finca)
                        ->where('id_semana', $sem->id_semana)
                        ->first();
                    if ($se != '' && $se->plantas_iniciales > 0 && $se->densidad > 0) {
                        $modulo = Modulo::All()
                            ->where('estado', 1)
                            ->where('id_empresa', $finca)
                            ->where('proyectar_semanal', 1)
                            ->where('nombre', 'S' . $var->id_variedad . '-' . $sem->codigo)
                            ->first();
                        if ($modulo == '') {
                            $sector = Sector::All()
                                ->where('id_empresa', $finca)
                                ->where('estado', 1)
                                ->first();
                            $modulo = new Modulo();
                            $modulo->id_sector = $sector->id_sector;
                            $modulo->id_empresa = $finca;
                            $modulo->area = round($se->plantas_iniciales / $se->densidad, 2);
                            $modulo->proyectar_semanal = 1;
                            $modulo->nombre = 'S' . $var->id_variedad . '-' . $sem->codigo;
                            $modulo->save();
                            $modulo = Modulo::All()->last();
                        }
                        /* crear ciclo */
                        $ciclo = Ciclo::All()
                            ->where('id_modulo', $modulo->id_modulo)
                            ->where('id_variedad', $var->id_variedad)
                            ->where('id_empresa', $finca)
                            ->first();
                        if ($ciclo == '') {
                            $ciclo = new Ciclo();
                            $ciclo->id_modulo = $modulo->id_modulo;
                            $ciclo->id_variedad = $var->id_variedad;
                            $ciclo->id_empresa = $finca;
                            $ciclo->poda_siembra = 'S';
                            $ciclo->fecha_inicio = $sem->fecha_inicial;
                            $ciclo->fecha_fin = hoy();
                            $ciclo->activo = 1;
                            $ciclo->area = $modulo->area;
                            $ciclo->plantas_iniciales = $se->plantas_iniciales;
                            $ciclo->conteo = $sem->tallos_planta_siembra;
                            $ciclo->curva = $sem->curva;
                            $ciclo->semana_poda_siembra = $sem->semana_siembra;
                            $ciclo->desecho = $sem->desecho;
                            $ciclo->save();
                            $ciclo = Ciclo::All()->last();
                        }
                        /* crear proy_sem */
                        $del_proy = ProyeccionModuloSemana::where('id_modulo', $modulo->id_modulo)
                            ->where('modelo', $ciclo->id_ciclo)
                            ->where('id_variedad', $var->id_variedad)
                            ->where('tabla', 'C')
                            ->delete();

                        $cant_semanas_new = $ciclo->semana_poda_siembra + count(explode('-', $ciclo->curva)) - 1;   // cantidad de semanas que durará el ciclo new

                        $pos_cosecha = 0;
                        for ($i = 1; $i <= $cant_semanas_new; $i++) {
                            $semana = getSemanaByDate(opDiasFecha('+', ($i - 1) * 7, $sem->fecha_inicial));
                            $proy = new ProyeccionModuloSemana();
                            $proy->tabla = 'C';
                            $proy->id_variedad = $var->id_variedad;
                            $proy->id_modulo = $modulo->id_modulo;
                            $proy->modelo = $ciclo->id_ciclo;
                            $proy->semana = $semana->codigo;
                            $proy->plantas_iniciales = $ciclo->plantas_iniciales;
                            $proy->tallos_planta = $ciclo->conteo;
                            $proy->tallos_ramo = 0;
                            $proy->curva = $ciclo->curva;
                            $proy->poda_siembra = $ciclo->poda_siembra;
                            $proy->semana_poda_siembra = $ciclo->semana_poda_siembra;
                            $proy->desecho = $ciclo->desecho;
                            $proy->area = $ciclo->area;
                            $proy->tipo = 'I';
                            $proy->info = $i . 'º';
                            $proy->proyectados = 0;
                            $proy->activo = 1;
                            if ($sem->fecha_inicial <= hoy()) { // se trata de una semana <= actual y hay q calcular los tallos cosechados
                                $cosechados = getTallosCosechadosByModRangoVar($modulo->id_modulo, $sem->fecha_inicial, $sem->fecha_final, $var->id_variedades);
                                $proy->cosechados = $cosechados;
                            }

                            if ($i == 1) {   // primera semana de ciclo
                                $proy->tipo = 'S';
                                $proy->info = 'S-0';
                            }
                            if ($i >= $ciclo->semana_poda_siembra) {  // semana de cosecha **
                                $proy->tipo = 'T';
                                $total = $ciclo->plantas_actuales() * $ciclo->conteo;
                                $total = $total * ((100 - $ciclo->desecho) / 100);
                                $proy->proyectados = round($total * (explode('-', $ciclo->curva)[$pos_cosecha] / 100), 2);
                                $pos_cosecha++;
                            }
                            $proy->save();
                        }
                    }
                }
            }
        }

        $time_duration = difFechas(date('Y-m-d H:i:s'), $ini)->h . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->m . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->s;
        if ($dev_par == 1) {
            dump('<*> DURACION: ' . $time_duration . '  <*>');
            dump('<<<<< * >>>>> Fin satisfactorio del comando "cron:generar_proy_futuras" <<<<< * >>>>>');
        }
        Log::info('<*> DURACION: ' . $time_duration . '  <*>');
        Log::info('<<<<< * >>>>> Fin satisfactorio del comando "cron:generar_proy_futuras" <<<<< * >>>>>');
    }
}
