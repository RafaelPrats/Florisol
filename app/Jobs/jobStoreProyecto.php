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
use yura\Modelos\RenovarOrdenFija;

class jobStoreProyecto implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $request;
    protected $usuario;
    protected $ip;
    protected $finca;

    public function __construct($par_request, $par_usuario, $par_ip, $par_finca)
    {
        $this->request = $par_request;
        $this->usuario = $par_usuario;
        $this->ip = $par_ip;
        $this->finca = $par_finca;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $request = $this->request;

        try {
            DB::beginTransaction();
            $renovacion = '';
            if ($request['tipo'] == 'SO') {
                $fechas = [];
                if ($request['fecha']['opcion_pedido_fijo'] == 1 || $request['fecha']['opcion_pedido_fijo'] == 2) {
                    $f = $request['fecha']['desde'];
                    while ($f <= $request['fecha']['hasta']) {
                        if ($request['fecha']['opcion_pedido_fijo'] == 1 && date('N', strtotime($f)) == $request['fecha']['dia_semana'])
                            $fechas[] = $f;

                        if ($request['fecha']['opcion_pedido_fijo'] == 2 && substr($f, 8, 2) == $request['fecha']['dia_mes'])
                            $fechas[] = $f;
                        $f = opDiasFecha('+', 1, $f);
                    }
                    if ($request['fecha']['intervalo'] == 2) {
                        foreach ($fechas as $pos => $f) {
                            if ($pos % 2 == 1)
                                unset($fechas[$pos]);
                        }
                    }
                    $renovacion = [
                        'renovar' => $request['fecha']['renovar'],
                        'intervalo' => $request['fecha']['intervalo'] == 1 ? 7 : 14
                    ];
                } else {
                    $fechas = $request['fecha']['fechas'];
                }
                $fecha = $fechas[0];
            } else {
                $fecha = $request['fecha'];
            }

            // NUEVO PROYECTO
            $proyecto = new Proyecto();
            $proyecto->id_cliente = $request['cliente'];
            if ($request['tipo'] == 'SO') {
                $numeroOrdenFija = DB::table('proyecto')
                    ->select(DB::raw('max(orden_fija) as cantidad'))
                    ->get()[0]->cantidad;
                $numeroOrdenFija = $numeroOrdenFija != '' ? ($numeroOrdenFija + 1) : 1;
                $proyecto->orden_fija = $numeroOrdenFija;
            }
            $proyecto->id_empresa = $this->finca;
            $proyecto->fecha = $fecha;
            $proyecto->tipo = $request['tipo'];
            $proyecto->segmento = $request['segmento'];
            $proyecto->id_consignatario = $request['consignatario'];
            $proyecto->id_agencia_carga = $request['agencia'];
            $proyecto->save();
            $proyecto->id_proyecto = DB::table('proyecto')
                ->select(DB::raw('max(id_proyecto) as id'))
                ->get()[0]->id;

            foreach (json_decode($request['detalles_pedido']) as $det_ped) {
                // NUEVA CAJA PROYECTO
                $caja = new CajaProyecto();
                $caja->id_proyecto = $proyecto->id_proyecto;
                $caja->cantidad = $det_ped->piezas;
                $caja->tipo_caja = $det_ped->caja;
                $caja->save();
                $caja->id_caja_proyecto = DB::table('caja_proyecto')
                    ->select(DB::raw('max(id_caja_proyecto) as id'))
                    ->get()[0]->id;
                foreach ($det_ped->detalles_combo as $det_caj) {
                    // NUEVO DETALLE CAJA PROYECTO
                    $detalle = new DetalleCajaProyecto();
                    $detalle->id_caja_proyecto = $caja->id_caja_proyecto;
                    $detalle->id_variedad = $det_caj->receta;
                    $detalle->ramos_x_caja = $det_caj->ramos_x_caja;
                    $detalle->tallos_x_ramo = $det_caj->tallos_x_ramos;
                    $detalle->precio = $det_caj->precio_ped;
                    $detalle->longitud_ramo = $det_caj->longitud;
                    $detalle->save();
                }
                foreach ($det_ped->valores_marcaciones as $marcacion) {
                    // NUEVA CAJA PROYECTO MARCACION
                    if ($marcacion->valor_marcacion != '') {
                        $caja_marcacion = new CajaProyectoMarcacion();
                        $caja_marcacion->id_caja_proyecto = $caja->id_caja_proyecto;
                        $caja_marcacion->id_dato_exportacion = $marcacion->id_marcacion;
                        $caja_marcacion->valor = $marcacion->valor_marcacion;
                        $caja_marcacion->save();
                    }
                }
            }

            /* CREAR EL RESTO DE LA ORDEN FIJA */
            if ($request['tipo'] == 'SO') {
                dump('* CREAR EL RESTO DE LA ORDEN FIJA *');
                foreach ($fechas as $pos => $f) {
                    if ($pos > 0) {
                        jobGrabarOrdenFija::dispatch($proyecto->id_proyecto, $f)->onQueue('grabar_orden_fija')->onConnection('database');
                    }
                }

                /* CREAR RENOVACION */
                if ($renovacion['renovar'] == true) {
                    $model_renovar = new RenovarOrdenFija();
                    $model_renovar->orden_fija = $numeroOrdenFija;
                    $model_renovar->renovacion = $renovacion['intervalo'];
                    $model_renovar->save();
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            //dd($e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
            //echo $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine();
        }
    }
}
