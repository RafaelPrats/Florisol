<?php

namespace yura\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use yura\Modelos\CosechaDiaria;

class jobActualizarCosechaDiaria implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $fecha;
    protected $hasta;
    protected $variedades;
    protected $finca;

    public function __construct($fecha, $hasta, $variedades, $finca)
    {
        $this->fecha = $fecha;
        $this->hasta = $hasta;
        $this->variedades = $variedades;
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
        set_time_limit(3600);

        $hasta = $this->hasta != null ? $this->hasta : $this->fecha;
        $fechas = DB::table('cosecha_diaria')
            ->select('fecha')->distinct()
            ->where('id_empresa', $this->finca)
            ->where('fecha', '>=', $this->fecha)
            ->where('fecha', '<=', $hasta)
            ->orderBy('fecha')->get();
        foreach ($fechas as $fecha) {
            foreach ($this->variedades as $var) {
                $model = CosechaDiaria::All()
                    ->where('id_variedad', $var)
                    ->where('fecha', $fecha->fecha)
                    ->where('id_empresa', $this->finca)
                    ->first();
                if ($model == '') {
                    $variedad = getVariedad($var);
                    $model = new CosechaDiaria();
                    $model->id_variedad = $variedad->id_variedad;
                    $model->variedad_nombre = $variedad->nombre;
                    $model->id_planta = $variedad->id_planta;
                    $model->planta_nombre = $variedad->planta->nombre;
                    $model->id_empresa = $this->finca;
                    $model->fecha = $fecha->fecha;
                }
                $cosechados = DB::table('desglose_recepcion as dr')
                    ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
                    ->select(DB::raw('sum(dr.cantidad_mallas * dr.tallos_x_malla) as cantidad'))
                    ->where('r.estado', 1)
                    ->where('dr.estado', 1)
                    ->where('dr.id_variedad', $var)
                    ->where('dr.id_empresa', $this->finca)
                    ->where('r.fecha_ingreso', 'like', $fecha->fecha . '%')
                    ->get()[0]->cantidad;
                $model->cosechados = $cosechados > 0 ? $cosechados : 0;
                $model->save();
            }
        }
    }
}