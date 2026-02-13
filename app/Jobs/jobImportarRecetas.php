<?php

namespace yura\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Storage as Almacenamiento;
use \PhpOffice\PhpSpreadsheet\IOFactory as IOFactory;
use yura\Console\Commands\NotificacionesSistema;
use yura\Modelos\DetalleReceta;
use yura\Modelos\Planta;
use yura\Modelos\Variedad;

class jobImportarRecetas implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    protected $extension;

    public function __construct($extension)
    {
        $this->extension = $extension;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $url = public_path('storage/file_loads/upload_recetas.' . $this->extension);
            $document = IOFactory::load($url);
            $sheet = $document->getActiveSheet()->toArray(null, true, true, true);

            $fallos = [];
            $receta = '';
            $cant_recetas = 0;
            foreach ($sheet as $pos_row => $row) {
                if ($pos_row > 9 && $row['C'] != '') {
                    dump('pos: ' . $pos_row . '/' . count($sheet));
                    $cambio = false;
                    if ($receta != $row['C']) {
                        $cant_recetas++;
                        $receta = $row['C'];
                        $cambio = true;
                    }
                    if ($cambio) {
                        $model_receta = Variedad::where('receta', 1)
                            ->where('estado', 1)
                            ->where('siglas', espacios(mb_strtoupper($row['C'])))
                            ->where('nombre', espacios(mb_strtoupper($row['D'])))
                            ->get()
                            ->first();
                        if ($model_receta == '') {
                            $model_receta = new Variedad();
                            $model_receta->id_planta = 128; // PLANTA BOUQUETS
                            $model_receta->siglas = espacios(mb_strtoupper($row['C']));
                            $model_receta->nombre = espacios(mb_strtoupper($row['D']));
                            $model_receta->receta = 1;
                            $model_receta->tallos_x_malla = 1;
                            $model_receta->color = 'RECETA';
                            $model_receta->tipo = 'L';
                            $model_receta->save();
                            $model_receta->id_variedad = DB::table('variedad')
                                ->select(DB::raw('max(id_variedad) as id'))
                                ->get()[0]->id;

                            $bloqueado = 0;
                        } else {
                            $detalles_defecto = DetalleReceta::where('defecto', 1)
                                ->where('id_variedad', $model_receta->id_variedad)
                                ->get();
                            $bloqueado = count($detalles_defecto) > 0 && $detalles_defecto[0]->bloquear == 1 ? 1 : 0;
                        }

                        if ($bloqueado == 0)
                            DB::select('delete from detalle_receta where id_variedad = ' . $model_receta->id_variedad . ' and defecto = 1');
                    }
                    $variedad = Variedad::join('planta as p', 'variedad.id_planta', '=', 'p.id_planta')
                        ->select('variedad.*')->distinct()
                        ->where('variedad.nombre', espacios(mb_strtoupper($row['H'])))
                        ->where('p.estado', 1)
                        ->get()
                        ->first();
                    if ($variedad != '') {
                        if ($bloqueado == 0) {
                            $detalle = new DetalleReceta();
                            $detalle->id_variedad = $model_receta->id_variedad;
                            $detalle->id_item = $variedad->id_variedad;
                            $detalle->longitud = 60;
                            $detalle->defecto = 1;
                            $detalle->numero_receta = 'MASTER';
                            $detalle->unidades = $row['I'];
                            $detalle->precio = $row['J'];
                            $detalle->save();
                        }
                    } else {
                        dump('*************** FALLO ****************');
                        dump('No se encontro la variedad <b>' . espacios(mb_strtoupper($row['H'])) . '</b> a la buquets <b>' . $model_receta->nombre . '</b>');
                        if (!in_array('No se encontro la variedad <b>' . espacios(mb_strtoupper($row['H'])) . '</b> a la buquets <b>' . $model_receta->nombre . '</b>', $fallos))
                            $fallos[] = 'No se encontro la variedad <b>' . espacios(mb_strtoupper($row['H'])) . '</b> a la buquets <b>' . $model_receta->nombre . '</b>';
                    }
                }
            }

            if (count($fallos) > 0) {
                dump('=========== FALTANTES ===========');
                dump($fallos);
                /* ------------ ACTUALIZR NOTIFICACION fallos_upload_insumos --------------- */
                NotificacionesSistema::fallos_upload_recetas($fallos);
            }
        } catch (\Exception $e) {
            $fallos[] = 'Ha ocurrido un problema con el contenido del archivo. Pongase en contacto con el administrador del sistema';
        }
    }
}
