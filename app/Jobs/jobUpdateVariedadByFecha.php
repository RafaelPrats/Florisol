<?php

namespace yura\Jobs;

use Artisan;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Modelos\ProcesoQueue;
use yura\Modelos\Variedad;

class jobUpdateVariedadByFecha implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    protected $fecha;
    protected $variedad;
    protected $num_proceso;
    protected $total_procesos;
    protected $codigo_proceso;
    protected $descripcion_proceso;
    protected $usuario;
    public function __construct($fecha, $variedad, $num_proceso, $total_procesos, $codigo_proceso, $descripcion_proceso = '', $usuario)
    {
        $this->fecha = $fecha;
        $this->variedad = $variedad;
        $this->num_proceso = $num_proceso;
        $this->total_procesos = $total_procesos;
        $this->codigo_proceso = $codigo_proceso;
        $this->descripcion_proceso = $descripcion_proceso;
        $this->usuario = $usuario;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Artisan::call('resumen:fecha', [
            'fecha' => $this->fecha,
            'variedad' => $this->variedad,
            'dev' => 1,
        ]);
        $proceso = ProcesoQueue::where('codigo', $this->codigo_proceso)
            ->get()
            ->first();
        if ($proceso == '') {
            $proceso = new ProcesoQueue();
            $proceso->codigo = $this->codigo_proceso;
            $proceso->numero = $this->num_proceso;
            $proceso->total_proceso = $this->total_procesos;
            $proceso->descripcion = $this->descripcion_proceso . ' <li><b>Fecha:</b> ' . $this->fecha . ' <b>variedad:</b> ' . Variedad::find($this->variedad)->nombre . '</li>';
            $proceso->id_usuario = $this->usuario;
            $proceso->save();
        } else {
            $proceso->numero = $this->num_proceso;
            $proceso->descripcion .= '<li><b>Fecha:</b> ' . $this->fecha . ' <b>variedad:</b> ' . Variedad::find($this->variedad)->nombre . '</li>';
            $proceso->save();
        }
    }
}
