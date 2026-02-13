<?php

namespace yura\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Storage as Almacenamiento;
//use PHPExcel_IOFactory;
use \PhpOffice\PhpSpreadsheet\IOFactory as IOFactory;
use yura\Modelos\ConfiguracionEmpresa;
use yura\Modelos\ResumenTotalSemanalExportcalas;
use yura\Modelos\Variedad;

class cronImportarUnosoftBouquetera extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:importar_unosoft_bouquetera';

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
        Log::info('<<<<< ! >>>>> Ejecutando comando "cron:importar_unosoft_bouquetera" <<<<< ! >>>>>');
        dump('<<<<< ! >>>>> Ejecutando comando "cron:importar_unosoft_bouquetera" <<<<< ! >>>>>');

        $files_search = ['unosoft_bouquetera.xlsx', 'unosoft_bouquetera.csv'];

        $files = Almacenamiento::disk('pdf_loads')->files('');
        $faltantes = [];
        foreach ($files as $nombre_archivo) {
            if (in_array($nombre_archivo, $files_search)) {
                $url = public_path('storage/pdf_loads/' . $nombre_archivo);

                $document = IOFactory::load($url);
                $activeSheetData = $document->getActiveSheet()->toArray(null, true, true, true);

                $data = [];
                foreach ($activeSheetData as $pos => $row) {
                    if ($pos > 1) {
                        $anno = explode('/', $row['B'])[2];
                        $mes = explode('/', $row['B'])[0];
                        $mes = strlen($mes) == 1 ? '0' . $mes : $mes;
                        $dia = explode('/', $row['B'])[1];
                        $dia = strlen($dia) == 1 ? '0' . $dia : $dia;
                        $fecha = $anno . '-' . $mes . '-' . $dia;
                        $semana = getSemanaByDate($fecha);

                        $finca = ConfiguracionEmpresa::All()->where('nombre', $row['A'])->first();
                        if ($finca != '') {
                            $variedad = Variedad::where('variedad.nombre', mb_strtoupper($row['D']))
                                ->join('planta as p', 'p.id_planta', 'variedad.id_planta')
                                ->select('variedad.*')
                                ->where('p.nombre', mb_strtoupper($row['C']))
                                ->first();
                            if ($variedad != '') {
                                dump($row);
                                dump('pos: ' . $pos . '/' . count($activeSheetData) . ' - sem: ' . $semana->codigo . ' - finca: ' . $finca->nombre . ' - var: ' . $variedad->nombre);
                                $existe = false;
                                for ($i = 0; $i < count($data); $i++) {
                                    if ($data[$i]['finca']->id_configuracion_empresa == $finca->id_configuracion_empresa &&
                                        $data[$i]['semana']->codigo == $semana->codigo &&
                                        $data[$i]['variedad']->id_variedad == $variedad->id_variedad) {
                                        $existe = true;
                                        $venta = str_replace('$', '', $row['I']);
                                        $venta = str_replace('"', '', $venta);
                                        $venta = str_replace(',', '', $venta);
                                        $data[$i]['venta_bouquetera'] += $venta;
                                        dump('acumulado: ' . $data[$i]['venta_bouquetera'] . ' += ' . $venta);
                                        //$data[$i]['bouquetera'] += $row['J'];
                                    }
                                }
                                if (!$existe) {
                                    $venta = str_replace('$', '', $row['I']);
                                    $venta = str_replace('"', '', $venta);
                                    $venta = str_replace(',', '', $venta);
                                    dump('acumulado ini: ' . $venta);
                                    array_push($data, [
                                        'finca' => $finca,
                                        'semana' => $semana,
                                        'variedad' => $variedad,
                                        'venta_bouquetera' => $venta,
                                        //'bouquetera' => $row['J'],
                                    ]);
                                }
                            } else {
                                dump('******************* ERROR *********************');
                                dump('NO SE HA ENCONTRADO LA VARIEDAD: "' . mb_strtoupper($row['D']) . '" PLANTA: "' . mb_strtoupper($row['C']) . '"');
                                if (!in_array('NO SE HA ENCONTRADO LA VARIEDAD: ' . mb_strtoupper($row['D']) . ' PLANTA: ' . mb_strtoupper($row['C']), $faltantes))
                                    array_push($faltantes, 'NO SE HA ENCONTRADO LA VARIEDAD: ' . mb_strtoupper($row['D']) . ' PLANTA: ' . mb_strtoupper($row['C']));
                            }
                        } else {
                            dump('******************* ERROR *********************');
                            dump('NO SE HA ENCONTRADO LA FINCA: "' . $row['A'] . '"');
                            if (!in_array('NO SE HA ENCONTRADO LA FINCA: ' . $row['A'], $faltantes))
                                array_push($faltantes, 'NO SE HA ENCONTRADO LA FINCA: ' . $row['A']);
                        }
                    }
                }

                dump('----------------- GUARDAR DATOS ----------------');
                dump($data);
                foreach ($data as $pos => $d) {
                    dump('pos: ' . ($pos + 1) . '/' . count($data) . ' - finca: ' . $d['finca']->nombre . ' - sem: ' . $d['semana']->codigo . ' - var: ' . $d['variedad']->nombre);

                    $model = ResumenTotalSemanalExportcalas::All()
                        ->where('id_empresa', $d['finca']->id_configuracion_empresa)
                        ->where('id_variedad', $d['variedad']->id_variedad)
                        ->where('semana', $d['semana']->codigo)->first();
                    if ($model == '') {
                        $model = new ResumenTotalSemanalExportcalas();
                        $model->id_empresa = $d['finca']->id_configuracion_empresa;
                        $model->id_variedad = $d['variedad']->id_variedad;
                        $model->semana = $d['semana']->codigo;
                        $model->tallos_exportables = 0;
                        $model->nacional = 0;
                        $model->bajas = 0;
                        $model->tallos_vendidos = 0;
                        $model->venta = 0;
                        $model->bouquetera = 0;
                    }
                    //$model->bouquetera = $d['bouquetera'];
                    $model->venta_bouquetera = $d['venta_bouquetera'];

                    $model->save();
                }

                /* ------------ ACTUALIZR NOTIFICACION fallos_upload_unosoft_bouquetera --------------- */
                NotificacionesSistema::fallos_upload_unosoft_bouquetera($faltantes);

                unlink($url);
            }
        }

        $time_duration = difFechas(date('Y-m-d H:i:s'), $ini)->h . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->m . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->s;
        Log::info('<*> DURACION: ' . $time_duration . '  <*>');
        Log::info('<<<<< * >>>>> Fin satisfactorio del comando "cron:importar_unosoft_bouquetera" <<<<< * >>>>>');
        dump('FALTANTES:', $faltantes);
        dump('<*> DURACION: ' . $time_duration . '  <*>');
        dump('<<<<< * >>>>> Fin satisfactorio del comando "cron:importar_unosoft_bouquetera" <<<<< * >>>>>');
    }
}
