<?php

namespace yura\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Artisan;

class jobActualizarDisponibilidad implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $semana_desde;
    protected $semana_hasta;
    protected $variedad;
    protected $finca;

    public function __construct($semana_desde, $semana_hasta, $variedad, $finca)
    {
        $this->semana_desde = $semana_desde;
        $this->semana_hasta = $semana_hasta;
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
        set_time_limit(3600);

        /* --------- ACTUALIZR RESUMEN_PROPAGACION -------------- */
        jobActualizarResumenPropag::dispatch($this->semana_desde, $this->semana_hasta, $this->variedad, $this->finca)
            ->onQueue('propag');

        Artisan::call('update:propag_disponibilidad', [
            'semana_desde' => $this->semana_desde,
            'semana_hasta' => $this->semana_hasta,
            'variedad' => $this->variedad,
            'empresa' => $this->finca,
        ]);
    }
}
