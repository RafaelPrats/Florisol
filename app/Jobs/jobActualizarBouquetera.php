<?php

namespace yura\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use yura\Modelos\ResumenTotalSemanalExportcalas;

class jobActualizarBouquetera implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $variedad;    // id
    protected $semana;  // modelo
    protected $finca;  // id

    public function __construct($variedad, $semana, $finca)
    {
        $this->variedad = $variedad;
        $this->semana = $semana;
        $this->finca = $finca;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $model = ResumenTotalSemanalExportcalas::All()
            ->where('id_variedad', $this->variedad)
            ->where('id_empresa', $this->finca)
            ->where('semana', $this->semana->codigo)
            ->first();
        if ($model == '') {
            $model = new ResumenTotalSemanalExportcalas();
            $model->semana = $this->semana->codigo;
            $model->id_variedad = $this->variedad;
            $model->id_empresa = $this->finca;
            $model->tallos_cosechados = 0;
            $model->tallos_proyectados = 0;
            $model->tallos_exportables = 0;
            $model->nacional = 0;
            $model->bajas = 0;
            $model->tallos_vendidos = 0;
            $model->venta = 0;
            $model->venta_bouquetera = 0;
        }
        $bouquetera = DB::table('bouquetera')
            ->select(DB::raw('sum(tallos) as cantidad'))
            ->where('id_empresa', $this->finca)
            ->where('id_variedad', $this->variedad)
            ->where('fecha', '>=', $this->semana->fecha_inicial)
            ->where('fecha', '<=', $this->semana->fecha_final)
            ->get()[0]->cantidad;
        $bouquetera = $bouquetera > 0 ? $bouquetera : 0;
        $model->bouquetera = $bouquetera;
        $model->save();
    }
}
