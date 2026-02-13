<?php

namespace yura\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Artisan;

class jobUpdateResumenTotalSemanalExportcalas implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $semana_desde;
    protected $semana_hasta;
    protected $variedad;
    protected $empresa;

    public function __construct($desde = 0, $hasta = 0, $variedad = 0, $empresa = 0)
    {
        $this->semana_desde = $desde;
        $this->semana_hasta = $hasta;
        $this->variedad = $variedad;
        $this->empresa = $empresa;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Artisan::call('exportcalas:resumen_total_semanal', [
            'semana_desde' => $this->semana_desde,
            'semana_hasta' => $this->semana_hasta,
            'variedad' => $this->variedad,
            'empresa' => $this->empresa,
        ]);
    }
}
