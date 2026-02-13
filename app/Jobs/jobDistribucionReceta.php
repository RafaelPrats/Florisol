<?php

namespace yura\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Modelos\DistribucionReceta;
use yura\Modelos\ImportPedido;

class jobDistribucionReceta implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    protected $pedido;

    public function __construct($pedido)
    {
        $this->pedido = $pedido;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $pedido = ImportPedido::find($this->pedido);
        foreach ($pedido->detalles as $det) {
            if ($det->bloquear_distribucion == 0) {
                DistribucionReceta::where('id_detalle_import_pedido', $det->id_detalle_import_pedido)
                    ->delete();
            }
        }
        foreach ($pedido->detalles as $det)
            if ($det->bloquear_distribucion == 0)
                foreach ($det->variedad->detalles_receta->where('defecto', 1) as $item) {
                    $model = new DistribucionReceta();
                    $model->id_detalle_import_pedido = $det->id_detalle_import_pedido;
                    $model->id_variedad = $item->id_item;
                    $model->longitud = $det->longitud;
                    $model->unidades = $item->unidades;
                    $model->save();
                }
    }
}
