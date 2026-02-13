<?php

namespace yura\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Storage as Almacenamiento;
//use PHPExcel_IOFactory;
use \PhpOffice\PhpSpreadsheet\IOFactory as IOFactory;
use yura\Modelos\Bouquetera;
use yura\Modelos\ConfiguracionEmpresa;
use yura\Modelos\Variedad;

class cronImportarIngresoBqt extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:importar_ingreso_bqt';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $ini = date('Y-m-d H:i:s');
        Log::info('<<<<< ! >>>>> Ejecutando comando "cron:importar_ingreso_bqt" <<<<< ! >>>>>');
        dump('<<<<< ! >>>>> Ejecutando comando "cron:importar_ingreso_bqt" <<<<< ! >>>>>');

        $opciones = ['S', 'N'];
        $files_search = [];
        foreach ($opciones as $bqt)
            foreach ($opciones as $export)
                foreach ($opciones as $precio) {
                    array_push($files_search, $bqt . '-' . $export . '-' . $precio . '-upload_ingreso_bqt.xlsx');
                    array_push($files_search, $bqt . '-' . $export . '-' . $precio . '-upload_ingreso_bqt.csv');
                }

        $files = Almacenamiento::disk('pdf_loads')->files('');
        $faltantes = [];
        foreach ($files as $nombre_archivo) {
            if (in_array($nombre_archivo, $files_search)) {
                $url = public_path('storage/pdf_loads/' . $nombre_archivo);

                $document = IOFactory::load($url);
                $activeSheetData = $document->getActiveSheet()->toArray(null, true, true, true);

                $fecha_min = '';
                $fecha_max = '';
                if (count($activeSheetData) > 1) {
                    $anno = explode('/', $activeSheetData[2]['A'])[2];
                    $mes = explode('/', $activeSheetData[2]['A'])[0];
                    $mes = strlen($mes) == 1 ? '0' . $mes : $mes;
                    $dia = explode('/', $activeSheetData[2]['A'])[1];
                    $dia = strlen($dia) == 1 ? '0' . $dia : $dia;
                    $fecha = $anno . '-' . $mes . '-' . $dia;
                    $fecha_min = $fecha;
                    $fecha_max = $fecha;
                }

                $tallos_bqt_par = explode('-', $nombre_archivo)[0];
                $tallos_export_par = explode('-', $nombre_archivo)[1];
                $precio_par = explode('-', $nombre_archivo)[2];
                foreach ($activeSheetData as $pos => $row) {
                    if ($pos > 1) {
                        dump($row);
                        $anno = explode('/', $row['A'])[2];
                        $mes = explode('/', $row['A'])[0];
                        $mes = strlen($mes) == 1 ? '0' . $mes : $mes;
                        $dia = explode('/', $row['A'])[1];
                        $dia = strlen($dia) == 1 ? '0' . $dia : $dia;
                        $fecha = $anno . '-' . $mes . '-' . $dia;

                        if ($fecha < $fecha_min)
                            $fecha_min = $fecha;
                        if ($fecha > $fecha_max)
                            $fecha_max = $fecha;

                        if (mb_strtoupper($row['B']) == 'COMPRADA') {
                            $finca = -1;
                            $id_finca = -1;
                        } else {
                            $finca = ConfiguracionEmpresa::All()->where('nombre', $row['B'])->first();
                            $id_finca = $finca != '' ? $finca->id_configuracion_empresa : '';
                        }
                        if ($finca != '') {
                            $variedad = Variedad::where('variedad.nombre', mb_strtoupper($row['D']))
                                ->join('planta as p', 'p.id_planta', 'variedad.id_planta')
                                ->select('variedad.*')
                                ->where('p.nombre', mb_strtoupper($row['C']))
                                ->first();
                            if ($variedad != '') {
                                dump('pos: ' . $pos . ' - ' . porcentaje($pos, count($activeSheetData), 1) . '% - finca: ' . $id_finca . ' - var: ' . $variedad->nombre);
                                /*$model = Bouquetera::All()
                                    ->where('id_variedad', $variedad->id_variedad)
                                    ->where('id_planta', $variedad->id_planta)
                                    ->where('id_empresa', $id_finca)
                                    ->where('fecha', $fecha)
                                    ->where('precio', $row['F'])
                                    ->first();*/
                                //if ($model == '') {
                                $model = new Bouquetera();
                                $model->id_empresa = $id_finca;
                                $model->id_variedad = $variedad->id_variedad;
                                $model->id_planta = $variedad->id_planta;
                                $model->fecha = $fecha;
                                //}
                                if ($tallos_bqt_par == 'S')
                                    $model->tallos = $row['E'];
                                if ($precio_par == 'S')
                                    $model->precio = $row['F'];
                                if ($tallos_export_par == 'S')
                                    $model->exportada = $row['G'];
                                $model->save();
                            } else {
                                dump('******************* ERROR *********************');
                                dump('NO SE HA ENCONTRADO LA VARIEDAD: "' . mb_strtoupper($row['D']) . '; " PLANTA: "' . mb_strtoupper($row['C']) . '"');
                                if (!in_array('NO SE HA ENCONTRADO LA VARIEDAD: ' . mb_strtoupper($row['D']) . '; PLANTA: ' . mb_strtoupper($row['C']), $faltantes))
                                    array_push($faltantes, 'NO SE HA ENCONTRADO LA VARIEDAD: ' . mb_strtoupper($row['D']) . '; PLANTA: ' . mb_strtoupper($row['C']));
                            }
                        } else {
                            dump('******************* ERROR *********************');
                            dump('NO SE HA ENCONTRADO LA FINCA: "' . $row['B'] . '"');
                            if (!in_array('NO SE HA ENCONTRADO LA FINCA: ' . $row['B'], $faltantes))
                                array_push($faltantes, 'NO SE HA ENCONTRADO LA FINCA: ' . $row['B']);
                        }
                    }
                }

                /* ------------ ACTUALIZR NOTIFICACION fallos_upload_unosoft_bouquetera --------------- */
                NotificacionesSistema::fallos_upload_ingreso_bouquetera($faltantes);

                unlink($url);

                /* ------------ ACTUALIZR RESUMEN_TOTAL_SEMANAL_EXPORTCALAS --------------- */
                Artisan::call('comando:dev', [
                    'comando' => 'calcular_resumen_total_semanal_bqt',
                    'desde' => $fecha_min,
                    'hasta' => $fecha_max,
                ]);

            }
        }

        $time_duration = difFechas(date('Y-m-d H:i:s'), $ini)->h . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->m . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->s;
        Log::info('<*> DURACION: ' . $time_duration . '  <*>');
        Log::info('<<<<< * >>>>> Fin satisfactorio del comando "cron:importar_ingreso_bqt" <<<<< * >>>>>');
        dump('FALTANTES:', $faltantes);
        dump('<*> DURACION: ' . $time_duration . '  <*>');
        dump('<<<<< * >>>>> Fin satisfactorio del comando "cron:importar_ingreso_bqt" <<<<< * >>>>>');
    }
}