<?php

namespace yura\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Artisan;

class jobUpdateResumenAgrogana implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $fecha;
    protected $variedad;

    public function __construct($fecha, $variedad)
    {
        $this->fecha = $fecha;
        $this->variedad = $variedad;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Artisan::call('resumen:update', [
            'fecha' => $this->fecha,
            'variedad' => $this->variedad,
        ]);
    }
}
