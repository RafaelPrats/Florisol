<?php

namespace yura\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class jobUpdateVariedades implements ShouldQueue
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
        $variedades = [];
        if ($this->variedad != 0)
            $variedades[] = $this->variedad;
        else {
            $fecha = $this->fecha;
            $combinaciones_compra_flor = DB::table('desglose_compra_flor as i')
                ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
                ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                ->select(
                    'i.id_variedad',
                    'v.nombre as variedad_nombre',
                    'p.nombre as planta_nombre',
                    'v.dias_rotacion_recepcion',
                )->distinct()
                ->where('i.fecha', $this->fecha)
                ->orderBy('p.nombre')
                ->orderBy('v.nombre')
                ->get();
            $ids_variedad_compra_flor = $combinaciones_compra_flor->pluck('id_variedad')->toArray();
            $combinaciones_recepcion = DB::table('desglose_recepcion as i')
                ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
                ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                ->select(
                    'i.id_variedad',
                    'v.nombre as variedad_nombre',
                    'p.nombre as planta_nombre',
                    'v.dias_rotacion_recepcion',
                )->distinct()
                ->where('i.fecha', $this->fecha)
                ->where('i.disponibles', '>', 0)
                ->where('i.estado', 1)
                ->whereNotIn('i.id_variedad', $ids_variedad_compra_flor)
                ->orderBy('p.nombre')
                ->orderBy('v.nombre')
                ->get();
            $ids_variedad_recepcion = $combinaciones_recepcion->pluck('id_variedad')->toArray();
            $combinaciones_resumen = DB::table('resumen_fechas as h')
                ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                ->select(
                    'h.id_variedad',
                    'v.nombre as variedad_nombre',
                    'p.nombre as planta_nombre',
                )->distinct()
                ->where('h.fecha', $this->fecha)
                ->where('h.tallos_venta', '>', 0)
                ->whereNotIn('h.id_variedad', $ids_variedad_compra_flor)
                ->whereNotIn('h.id_variedad', $ids_variedad_recepcion)
                ->orderBy('p.nombre')
                ->orderBy('v.nombre')
                ->get();
            $ids_variedad_resumen = $combinaciones_resumen->pluck('id_variedad')->toArray();
            $combinaciones_pedidos = DB::table('distribucion_receta as h')
                ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                ->join('detalle_import_pedido as det', 'det.id_detalle_import_pedido', '=', 'h.id_detalle_import_pedido')
                ->join('import_pedido as ped', 'ped.id_import_pedido', '=', 'det.id_import_pedido')
                ->select(
                    'h.id_variedad',
                    'v.nombre as variedad_nombre',
                    'p.nombre as planta_nombre',
                )->distinct()
                ->where('ped.fecha', $this->fecha)
                ->where('h.unidades', '>', 0)
                ->whereNotIn('h.id_variedad', $ids_variedad_compra_flor)
                ->whereNotIn('h.id_variedad', $ids_variedad_recepcion)
                ->whereNotIn('h.id_variedad', $ids_variedad_resumen)
                ->orderBy('p.nombre')
                ->orderBy('v.nombre')
                ->get();
            $ids_variedad_pedidos = $combinaciones_pedidos->pluck('id_variedad')->toArray();
            $combinaciones_ot = DB::table('detalle_orden_trabajo as h')
                ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                ->join('orden_trabajo as ot', 'ot.id_orden_trabajo', '=', 'h.id_orden_trabajo')
                ->join('detalle_import_pedido as det', 'det.id_detalle_import_pedido', '=', 'ot.id_detalle_import_pedido')
                ->join('import_pedido as ped', 'ped.id_import_pedido', '=', 'det.id_import_pedido')
                ->select(
                    'h.id_variedad',
                    'v.nombre as variedad_nombre',
                    'p.nombre as planta_nombre',
                )->distinct()
                ->Where(function ($q) use ($fecha) {
                    $q->Where('ped.fecha', $fecha)
                        ->orWhere('ot.fecha', $fecha);
                })
                ->where('h.tallos', '>', 0)
                ->whereNotIn('h.id_variedad', $ids_variedad_compra_flor)
                ->whereNotIn('h.id_variedad', $ids_variedad_recepcion)
                ->whereNotIn('h.id_variedad', $ids_variedad_resumen)
                ->whereNotIn('h.id_variedad', $ids_variedad_pedidos)
                ->orderBy('p.nombre')
                ->orderBy('v.nombre')
                ->get();
            $ids_variedad_ot = $combinaciones_ot->pluck('id_variedad')->toArray();
            $combinaciones_pre_ot = DB::table('detalle_pre_orden_trabajo as h')
                ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                ->join('pre_orden_trabajo as ot', 'ot.id_pre_orden_trabajo', '=', 'h.id_pre_orden_trabajo')
                ->join('detalle_import_pedido as det', 'det.id_detalle_import_pedido', '=', 'ot.id_detalle_import_pedido')
                ->join('import_pedido as ped', 'ped.id_import_pedido', '=', 'det.id_import_pedido')
                ->select(
                    'h.id_variedad',
                    'v.nombre as variedad_nombre',
                    'p.nombre as planta_nombre',
                )->distinct()
                ->Where(function ($q) use ($fecha) {
                    $q->Where('ped.fecha', $fecha)
                        ->orWhere('ot.fecha', $fecha);
                })
                ->where('h.tallos', '>', 0)
                ->whereNotIn('h.id_variedad', $ids_variedad_compra_flor)
                ->whereNotIn('h.id_variedad', $ids_variedad_recepcion)
                ->whereNotIn('h.id_variedad', $ids_variedad_resumen)
                ->whereNotIn('h.id_variedad', $ids_variedad_pedidos)
                ->whereNotIn('h.id_variedad', $ids_variedad_ot)
                ->orderBy('p.nombre')
                ->orderBy('v.nombre')
                ->get();
            $ids_variedad_pre_ot = $combinaciones_pre_ot->pluck('id_variedad')->toArray();
            $variedades = $combinaciones_compra_flor->merge($combinaciones_resumen)
                ->merge($combinaciones_recepcion)
                ->merge($combinaciones_pedidos)
                ->merge($combinaciones_ot)
                ->merge($combinaciones_pre_ot)
                ->pluck('id_variedad')->toArray();
        }
        foreach ($variedades as $pos_variedad => $var) {
            dump('var: ' . $pos_variedad . '/' . count($variedades));
            Artisan::call('resumen:fecha', [
                'fecha' => $this->fecha,
                'variedad' => $var,
            ]);
        }
        /*Artisan::call('resumen:update', [
            'fecha' => $this->fecha,
            'variedad' => $this->variedad,
        ]);*/
        /*Artisan::call('update:variedades', [
            'fecha' => $this->fecha,
            'variedad' => $this->variedad,
            'dev' => 1,
        ]);*/
    }
}
