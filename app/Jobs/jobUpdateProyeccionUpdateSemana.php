<?php

namespace yura\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use yura\Modelos\ProyeccionModulo;
use yura\Modelos\ProyeccionModuloSemana;

class jobUpdateProyeccionUpdateSemana implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $modulo;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($modulo = 0)
    {
        $this->modulo = $modulo;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $modulo = getModuloById($this->modulo);
        $ciclo = $modulo->cicloActual();
        $semana = getSemanaByDateVariedad($ciclo->fecha_inicio, $ciclo->id_variedad);
        $semanas = DB::table('semana as s')
            ->select('s.codigo', 's.fecha_inicial', 's.fecha_final')->distinct()
            ->where('s.codigo', '>=', $semana->codigo)
            ->where('s.estado', 1)
            ->get();

        $semana_cosecha = $ciclo->semana_poda_siembra;
        $tallos_planta = $ciclo->conteo;
        $tallos_ramo = $ciclo->poda_siembra == 'P' ? $semana->tallos_ramo_poda : $semana->tallos_ramo_siembra;
        $semanas_curva = count(explode('-', $ciclo->curva));
        $getPodaSiembraByCiclo = $modulo->getPodaSiembraByCiclo($ciclo->id_ciclo);
        $proyecciones_ciclo = ProyeccionModuloSemana::where('id_modulo', $ciclo->id_modulo)
            ->where('id_variedad', $ciclo->id_variedad)
            ->where('semana', '>=', $semana->codigo)
            ->delete();
        $next_proy = ProyeccionModulo::All()
            ->where('id_modulo', $ciclo->id_modulo)
            ->where('id_variedad', $ciclo->id_variedad)
            ->first();
        $array_semanas_prog = [];
        $pos_cosecha = 0;

        foreach ($semanas as $pos_sem => $sem) {
            $sem_actual = $pos_sem + 1;

            $proy = new ProyeccionModuloSemana();
            $proy->id_modulo = $ciclo->id_modulo;
            $proy->id_variedad = $ciclo->id_variedad;
            $proy->semana = $sem->codigo;
            $proy->plantas_iniciales = $ciclo->plantas_iniciales;
            $proy->plantas_actuales = $ciclo->plantas_actuales();
            $proy->fecha_inicio = $ciclo->fecha_inicio;
            $proy->activo = 1;
            $proy->area = $ciclo->area;
            $proy->tallos_planta = $tallos_planta;
            $proy->tallos_ramo = $tallos_ramo;
            $proy->curva = $ciclo->curva;
            $proy->poda_siembra = $ciclo->poda_siembra;
            $proy->semana_poda_siembra = $semana_cosecha;
            $proy->desecho = $semana->desecho;
            $proy->tabla = 'C';
            $proy->modelo = $ciclo->id_ciclo;
            $proy->cosechados = DB::table('desglose_recepcion as dr')
                ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
                ->select(DB::raw('sum(dr.cantidad_mallas * dr.tallos_x_malla) as cantidad'))
                ->where('dr.id_variedad', $ciclo->id_variedad)
                ->where('dr.id_modulo', $ciclo->id_modulo)
                ->where('r.fecha_ingreso', '>=', $sem->fecha_inicial)
                ->where('r.fecha_ingreso', '<=', $sem->fecha_final)
                ->get()[0]->cantidad;

            if ($sem_actual == 1) {    // primera semana de ciclo
                $proy->tipo = $ciclo->poda_siembra;
                $proy->info = $ciclo->poda_siembra . '-' . $getPodaSiembraByCiclo;
                $proy->save();
            } elseif ($sem_actual < $semana_cosecha) {   // semana info antes de inicio de cosecha
                $proy->tipo = 'I';
                $proy->info = $sem_actual . "'";
                $proy->save();
            } elseif ($sem_actual >= $semana_cosecha && $sem_actual <= $semana_cosecha + $semanas_curva - 1) {  // semanas de cosecha-curva
                $proy->tipo = 'T';
                $proy->info = $sem_actual . "'";
                $total = $proy->plantas_actuales * $tallos_planta;
                $total = $total * ((100 - $proy->desecho) / 100);
                $proy->proyectados = round($total * (explode('-', $proy->curva)[$pos_cosecha] / 100), 2);
                $pos_cosecha++;
                $proy->save();
            } else {    // semanas despues del ciclo
                if ($next_proy != '' && $sem->codigo < $next_proy->semana->codigo) { // semanas antes de la programacion
                    $proy->tipo = 'F';
                    $proy->info = '-';
                    $proy->save();
                } else {
                    array_push($array_semanas_prog, $sem);
                }
            }
        }

        /* ------------ Programacion siguiente --------------- */
        $pos_cosecha = 0;
        foreach ($array_semanas_prog as $pos_sem => $sem) {
            $proy = new ProyeccionModuloSemana();
            $proy->id_modulo = $ciclo->id_modulo;
            $proy->id_variedad = $ciclo->id_variedad;
            $proy->semana = $sem->codigo;

            if ($next_proy != '') { // tiene siguiente proyeccion

                $sem_actual = $pos_sem + 1;

                $proy->plantas_iniciales = $ciclo->plantas_iniciales;
                $proy->plantas_actuales = $ciclo->plantas_iniciales;
                $proy->fecha_inicio = $ciclo->fecha_inicio;
                $proy->activo = 1;
                $proy->area = $ciclo->area;
                $proy->tallos_planta = $tallos_planta;
                $proy->tallos_ramo = $tallos_ramo;
                $proy->curva = $ciclo->curva;
                $proy->poda_siembra = $next_proy->poda_siembra;
                $proy->semana_poda_siembra = $semana_cosecha;
                $proy->desecho = $semana->desecho;
                $proy->tabla = 'P';
                $proy->modelo = $next_proy->id_proyeccion_modulo;
                $proy->cosechados = 0;

                if ($sem_actual == 1) {    // primera semana de programacion
                    $proy->tipo = 'Y';
                    $proy->info = $next_proy->tipo;
                    $proy->save();
                } elseif ($sem_actual < $semana_cosecha) {   // semana info antes de inicio de cosecha
                    $proy->tipo = 'I';
                    $proy->info = $sem_actual . "'";
                    $proy->save();
                } elseif ($sem_actual >= $semana_cosecha && $sem_actual <= $semana_cosecha + $semanas_curva - 1) {  // semanas de cosecha-curva
                    $proy->tipo = 'T';
                    $proy->info = $sem_actual . "'";
                    $total = $proy->plantas_actuales * $tallos_planta;
                    $total = $total * ((100 - $proy->desecho) / 100);
                    $proy->proyectados = round($total * (explode('-', $proy->curva)[$pos_cosecha] / 100), 2);
                    $pos_cosecha++;
                    $proy->save();
                } else {    // semanas despues de la programacion
                    $proy->tipo = 'F';
                    $proy->info = '-';
                    $proy->save();
                }
            } else {    // no hay mas programacion hacia adelante
                $proy->tipo = 'F';
                $proy->info = '-';
                $proy->save();
            }
        }
    }
}