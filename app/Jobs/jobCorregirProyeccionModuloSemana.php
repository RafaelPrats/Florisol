<?php

namespace yura\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Artisan;

class jobCorregirProyeccionModuloSemana implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *  llamar al comando 'comando:dev {comando} {desde=0} {hasta=0} {empresa=0} {variedad=0} {modulo=0}'
     * @return void
     */

    protected $desde;   // fecha
    protected $hasta;   // codigo semana
    protected $empresa;   // id
    protected $variedad;   // id
    protected $modulo;   // id

    public function __construct($desde, $hasta, $empresa, $variedad, $modulo)
    {
        $this->desde = $desde;
        $this->hasta = $hasta;
        $this->empresa = $empresa;
        $this->variedad = $variedad;
        $this->modulo = $modulo;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Artisan::call('comando:dev', [
            'comando' => 'corregir_proyeccion_cosecha',
            'desde' => $this->desde,
            'hasta' => $this->hasta,
            'empresa' => $this->empresa,
            'variedad' => $this->variedad,
            'modulo' => $this->modulo,
        ]);
    }
}
