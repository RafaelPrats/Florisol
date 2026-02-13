<?php

namespace yura\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Artisan;

class jobActualizarResumenPropag implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $desde;
    protected $hasta;
    protected $variedad;
    protected $finca;

    public function __construct($desde, $hasta, $variedad, $finca)
    {
        $this->desde = $desde;
        $this->hasta = $hasta;
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

        Artisan::call('update:resumen_propagacion', [
            'desde' => $this->desde,
            'hasta' => $this->hasta,
            'variedad' => $this->variedad,
            'empresa' => $this->finca,
        ]);
    }
}
