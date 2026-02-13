<?php

namespace yura\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use yura\Modelos\ResumenAreaSemanal;

class jobResumenAreaSemanal implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $semana;  // modelo
    protected $variedad;    // id
    protected $finca;   // id

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
        $finca = $this->finca;
        $semana = $this->semana;
        $model = ResumenAreaSemanal::All()
            ->where('estado', 1)
            ->where('id_empresa', $finca)
            ->where('id_variedad', $this->variedad)
            ->where('codigo_semana', $semana->codigo)
            ->first();
        if ($model == '') {
            $model = new ResumenAreaSemanal();
            $model->id_variedad = $this->variedad;
            $model->id_empresa = $finca;
            $model->codigo_semana = $semana->codigo;
        }

        $area = DB::table('ciclo as c')
            ->select(DB::raw('sum(c.area) as area'))
            ->where('c.estado', 1)
            ->where('c.id_empresa', $finca)
            ->where('c.id_variedad', $this->variedad)
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
                    });
            })
            ->get()[0]->area;
        $area = $area > 0 ? $area : 0;

        $data_ciclos = getCiclosCerradosByRango($semana->codigo, $semana->codigo, $this->variedad, true, $finca);
        $ciclo = $data_ciclos['ciclo'];
        $tallos_m2 = $data_ciclos['area_cerrada'] > 0 ? round($data_ciclos['tallos_cosechados'] / $data_ciclos['area_cerrada'], 2) : 0;

        $model->area = $area;
        $model->ciclo = $ciclo;
        $model->tallos_m2 = $tallos_m2;
        $model->ramos_m2 = 0;
        $model->ramos_m2_anno = 0;
        $model->save();
    }
}