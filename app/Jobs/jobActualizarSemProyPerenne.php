<?php

namespace yura\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use yura\Modelos\Semana;
use yura\Modelos\SemanaProyPerenne;

class jobActualizarSemProyPerenne implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $semana;  // codigo
    protected $variedad;
    protected $finca;

    public function __construct($semana, $variedad, $finca)
    {
        $this->semana = $semana;
        $this->variedad = $variedad;
        $this->finca = $finca;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        ini_set('max_execution_time', env('MAX_EXECUTION_TIME'));
        set_time_limit(1800);

        $semanas = DB::select('select * from semana where id_variedad = ' . $this->variedad . ' and codigo = ' . $this->semana);
        foreach ($semanas as $semana) {
            $sem_52_desde = getSemanaByDate(opDiasFecha('-', 357, $semana->fecha_inicial));
            $sem_52_hasta = getSemanaByDate(opDiasFecha('-', 7, $semana->fecha_inicial));
            if ($sem_52_desde == '') {
                $sem_52_desde = DB::select('select * from semana where id_variedad = ' . $this->variedad . ' order by codigo asc limit 1')[0];
            }
            if ($sem_52_hasta == '') {
                $sem_52_hasta = $semana;
            }

            $model = SemanaProyPerenne::All()
                ->where('id_semana', $semana->id_semana)
                ->where('id_empresa', $this->finca)
                ->first();
            if ($model == '') {
                $model = new SemanaProyPerenne();
                $model->id_semana = $semana->id_semana;
                $model->id_empresa = $this->finca;
                $model->curva = 0;
            }
            $produccion = DB::table('ciclo as c')
                ->select(DB::raw('sum(c.area) as area'), DB::raw('sum(c.plantas_iniciales) as plantas_iniciales'))
                ->where('c.estado', 1)
                ->where('c.id_variedad', $this->variedad)
                ->where('c.id_empresa', $this->finca)
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
                        })
                        ->orWhere('c.fecha_fin', date('Y-m-d'))
                        ->orWhere('c.activo', 1);
                })
                ->get()[0];
            $area = $produccion->area > 0 ? $produccion->area : 0;
            $plantas_iniciales = $produccion->plantas_iniciales > 0 ? $produccion->plantas_iniciales : 0;
            $acum = DB::table('semana_proy_perenne as p')
                ->join('semana as s', 's.id_semana', '=', 'p.id_semana')
                ->select(DB::raw('sum(p.proyectados) as proyectados'), DB::raw('sum(p.cosechados) as cosechados'))
                ->where('p.id_empresa', $this->finca)
                ->where('s.id_variedad', $this->variedad)
                ->where('s.codigo', '>=', $sem_52_desde->codigo)
                ->where('s.codigo', '<=', $sem_52_hasta->codigo)
                ->get()[0];

            $model->plantas_iniciales = $plantas_iniciales;

            $proyectados = $area > 0 && $model->curva > 0 ? round($area * $model->curva, 2) : 0;
            $model->proyectados = $proyectados;

            $proyectados_acum = ($acum != '' && $acum->proyectados) > 0 ? ($acum->proyectados + $proyectados) : 0;
            $model->proyectados_acum = $proyectados_acum;

            /*$cosechados = DB::table('resumen_total_semanal_exportcalas')
                ->select(DB::raw('sum(tallos_cosechados) as cantidad'))
                ->where('id_variedad', $this->variedad)
                ->where('id_empresa', $this->finca)
                ->where('semana', $semana->codigo)
                ->get()[0]->cantidad;*/
            $cosechados = DB::table('cosecha_diaria')
                ->select(DB::raw('sum(cosechados) as cantidad'))
                ->where('id_variedad', $this->variedad)
                ->where('id_empresa', $this->finca)
                ->where('fecha', '>=', $semana->fecha_inicial)
                ->where('fecha', '<=', $semana->fecha_final)
                ->get()[0]->cantidad;
            $cosechados = $cosechados > 0 ? $cosechados : 0;
            $model->cosechados = $cosechados;

            /*$cosechados_52_sem = DB::table('resumen_total_semanal_exportcalas')
                ->select(DB::raw('sum(tallos_cosechados) as cantidad'))
                ->where('id_variedad', $this->variedad)
                ->where('id_empresa', $this->finca)
                ->where('semana', '>=', $sem_52_desde->codigo)
                ->where('semana', '<=', $sem_52_hasta->codigo)
                ->get()[0]->cantidad;*/
            $cosechados_52_sem = DB::table('cosecha_diaria')
                ->select(DB::raw('sum(cosechados) as cantidad'))
                ->where('id_variedad', $this->variedad)
                ->where('id_empresa', $this->finca)
                ->where('fecha', '>=', $sem_52_desde->fecha_inicial)
                ->where('fecha', '<=', $sem_52_hasta->fecha_final)
                ->get()[0]->cantidad;
            $cosechados_52_sem = $cosechados_52_sem > 0 ? $cosechados_52_sem : 0;
            $model->cosechados_52_sem = $cosechados_52_sem;

            $cosechados_acum = ($acum != '' && $acum->cosechados) > 0 ? ($acum->cosechados + $cosechados) : 0;
            $model->cosechados_acum = $cosechados_acum;

            $model->porcentaje_cumplimiento = $proyectados > 0 ? round(($cosechados * 100) / $proyectados, 2) : 0;

            $model->porcentaje_cumplimiento_acum = $proyectados_acum > 0 ? round(($cosechados_acum * 100) / $proyectados_acum, 2) : 0;

            $model->tallos_m2_ejecutado = $area > 0 ? round($cosechados / $area, 2) : 0;

            $model->tallos_m2_ejecutado_acum = $area > 0 ? round($cosechados_acum / $area, 2) : 0;

            $desde = getSemanaByDate(opDiasFecha('-', 28, $semana->fecha_inicial));
            $hasta = getSemanaByDate(opDiasFecha('-', 7, $semana->fecha_inicial));

            if ($desde == '') {
                $desde = DB::select('select * from semana where id_variedad = ' . $this->variedad . ' order by codigo asc limit 1')[0];
            }
            if ($hasta == '') {
                $hasta = $semana;
            }

            $sum_ejec_4_sem = DB::table('semana_proy_perenne as p')
                ->join('semana as s', 's.id_semana', '=', 'p.id_semana')
                ->select(DB::raw('sum(p.tallos_m2_ejecutado) as cantidad'))
                ->where('p.id_empresa', $this->finca)
                ->where('s.id_variedad', $this->variedad)
                ->where('s.codigo', '>=', $desde->codigo)
                ->where('s.codigo', '<=', $hasta->codigo)
                ->get()[0]->cantidad;
            $sum_ejec_4_sem = $sum_ejec_4_sem > 0 ? $sum_ejec_4_sem : 0;
            $model->sum_ejec_4_sem = $sum_ejec_4_sem;

            $desde = getSemanaByDate(opDiasFecha('-', 91, $semana->fecha_inicial));
            if ($desde == '') {
                $desde = DB::select('select * from semana where id_variedad = ' . $this->variedad . ' order by codigo asc limit 1')[0];
            }

            $sum_ejec_13_sem = DB::table('semana_proy_perenne as p')
                ->join('semana as s', 's.id_semana', '=', 'p.id_semana')
                ->select(DB::raw('sum(p.tallos_m2_ejecutado) as cantidad'))
                ->where('p.id_empresa', $this->finca)
                ->where('s.id_variedad', $this->variedad)
                ->where('s.codigo', '>=', $desde->codigo)
                ->where('s.codigo', '<=', $hasta->codigo)
                ->get()[0]->cantidad;
            $sum_ejec_13_sem = $sum_ejec_13_sem > 0 ? $sum_ejec_13_sem : 0;
            $model->sum_ejec_13_sem = $sum_ejec_13_sem;

            $desde = getSemanaByDate(opDiasFecha('-', 364, $semana->fecha_inicial));
            if ($desde == '') {
                $desde = DB::select('select * from semana where id_variedad = ' . $this->variedad . ' order by codigo asc limit 1')[0];
            }

            $sum_ejec_52_sem = DB::table('semana_proy_perenne as p')
                ->join('semana as s', 's.id_semana', '=', 'p.id_semana')
                ->select(DB::raw('sum(p.tallos_m2_ejecutado) as cantidad'))
                ->where('p.id_empresa', $this->finca)
                ->where('s.id_variedad', $this->variedad)
                ->where('s.codigo', '>=', $desde->codigo)
                ->where('s.codigo', '<=', $hasta->codigo)
                ->get()[0]->cantidad;
            $sum_ejec_52_sem = $sum_ejec_52_sem > 0 ? $sum_ejec_52_sem : 0;
            $model->sum_ejec_52_sem = $sum_ejec_52_sem;

            $model->save();
        }
    }
}
