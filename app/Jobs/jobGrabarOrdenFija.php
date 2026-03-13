<?php

namespace yura\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use yura\Modelos\CajaProyecto;
use yura\Modelos\CajaProyectoMarcacion;
use yura\Modelos\DetalleCajaProyecto;
use yura\Modelos\Proyecto;

class jobGrabarOrdenFija implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $proyecto;
    protected $fecha;
    public function __construct($par_proyecto, $par_fecha)
    {
        $this->proyecto = $par_proyecto;
        $this->fecha = $par_fecha;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            DB::beginTransaction();
            $proy_original = Proyecto::find($this->proyecto);
            $fecha = $this->fecha;

            // NUEVO PROYECTO
            $proyecto = new Proyecto();
            $proyecto->id_cliente = $proy_original->id_cliente;
            $proyecto->orden_fija = $proy_original->orden_fija;
            $proyecto->fecha = $fecha;
            $proyecto->tipo = $proy_original->tipo;
            $proyecto->segmento = $proy_original->segmento;
            $proyecto->id_consignatario = $proy_original->id_consignatario;
            $proyecto->id_agencia_carga = $proy_original->id_agencia_carga;
            $proyecto->save();
            $proyecto->id_proyecto = DB::table('proyecto')
                ->select(DB::raw('max(id_proyecto) as id'))
                ->get()[0]->id;

            foreach ($proy_original->cajas as $caja_original) {
                // NUEVA CAJA PROYECTO
                $caja = new CajaProyecto();
                $caja->id_proyecto = $proyecto->id_proyecto;
                $caja->cantidad = $caja_original->cantidad;
                $caja->tipo_caja = $caja_original->tipo_caja;
                $caja->save();
                $caja->id_caja_proyecto = DB::table('caja_proyecto')
                    ->select(DB::raw('max(id_caja_proyecto) as id'))
                    ->get()[0]->id;
                foreach ($caja_original->detalles as $det_original) {
                    // NUEVO DETALLE CAJA PROYECTO
                    $detalle = new DetalleCajaProyecto();
                    $detalle->id_caja_proyecto = $caja->id_caja_proyecto;
                    $detalle->id_variedad = $det_original->id_variedad;
                    $detalle->ramos_x_caja = $det_original->ramos_x_caja;
                    $detalle->tallos_x_ramo = $det_original->tallos_x_ramo;
                    $detalle->precio = $det_original->precio;
                    $detalle->longitud_ramo = $det_original->longitud_ramo;
                    $detalle->save();
                }
                foreach ($caja_original->marcaciones as $marcacion) {
                    // NUEVA CAJA PROYECTO MARCACION
                    if ($marcacion->valor != '') {
                        $caja_marcacion = new CajaProyectoMarcacion();
                        $caja_marcacion->id_caja_proyecto = $caja->id_caja_proyecto;
                        $caja_marcacion->id_dato_exportacion = $marcacion->id_dato_exportacion;
                        $caja_marcacion->valor = $marcacion->valor;
                        $caja_marcacion->save();
                    }
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
            //echo $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine();
        }
    }
}
