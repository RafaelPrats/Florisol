<?php

namespace yura\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Modelos\Modulo;

class jobActualizarCicloByModulo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    protected $modulo;
    protected $fecha;

    public function __construct($modulo, $fecha)
    {
        $this->modulo = $modulo;
        $this->fecha = $fecha;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $modulo = Modulo::find($this->modulo);
        $ciclo = $modulo->getCicloByFecha($this->fecha);
        if ($ciclo != '') {
            if ($ciclo->fecha_cosecha == '' || $this->fecha < $ciclo->fecha_cosecha) {
                $ciclo->fecha_cosecha = $this->fecha;
            }
            if ($ciclo->fecha_fin == '' || $this->fecha > $ciclo->fecha_fin) {
                $ciclo->fecha_fin = $this->fecha;
            }

            $ciclo->save();
            bitacora('ciclo', $ciclo->id_ciclo, 'U', 'Actualizacion satisfactoria de un ciclo desde el ingreso de la cosecha: ' . $this->fecha);
        }
    }
}
