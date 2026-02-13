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

class cronImportarUnosoft extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:importar_unosoft';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comando para cargar el archivo unosoft.xlsx en la carpeta public/storage/pdf_loads';

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
        Log::info('<<<<< ! >>>>> Ejecutando comando "cron:importar_unosoft" <<<<< ! >>>>>');
        dump('<<<<< ! >>>>> Ejecutando comando "cron:importar_unosoft" <<<<< ! >>>>>');

        $files_search = ['unosoft.xlsx', 'unosoft.csv'];

        $files = Almacenamiento::disk('pdf_loads')->files('');
        foreach ($files as $nombre_archivo) {
            if (in_array($nombre_archivo, $files_search)) {
                $url = public_path('storage/pdf_loads/' . $nombre_archivo);

                $document = IOFactory::load($url);
                $activeSheetData = $document->getActiveSheet()->toArray(null, true, true, true);

                $data = [];
                $faltantes = [];
                foreach ($activeSheetData as $pos => $row) {
                    if ($pos > 1) {
                        dump($row);
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
                                dump('pos: ' . $pos . '/' . count($activeSheetData) . ' - finca: ' . $finca->nombre . ' - var: ' . $variedad->nombre . ' - data: ' . $row["I"]);
                                $existe = false;
                                for ($i = 0; $i < count($data); $i++) {
                                    if ($data[$i]['finca']->id_configuracion_empresa == $finca->id_configuracion_empresa &&
                                        $data[$i]['semana']->codigo == $semana->codigo &&
                                        $data[$i]['variedad']->id_variedad == $variedad->id_variedad) {
                                        $existe = true;
                                        $data[$i]['tallos_exportables'] += $row['E'];
                                        $data[$i]['nacional'] += $row['F'];
                                        $data[$i]['bajas'] += $row['G'];
                                        $data[$i]['tallos_vendidos'] += $row['H'];
                                        dump($data[$i]['venta']);
                                        $venta = str_replace('$', '', $row['I']);
                                        $venta = str_replace('"', '', $venta);
                                        $venta = str_replace(',', '', $venta);
                                        $data[$i]['venta'] += $venta;
                                    }
                                }
                                if (!$existe) {
                                    $venta = str_replace('$', '', $row['I']);
                                    $venta = str_replace('"', '', $venta);
                                    $venta = str_replace(',', '', $venta);
                                    array_push($data, [
                                        'finca' => $finca,
                                        'semana' => $semana,
                                        'variedad' => $variedad,
                                        'tallos_exportables' => $row['E'],
                                        'nacional' => $row['F'],
                                        'bajas' => $row['G'],
                                        'tallos_vendidos' => $row['H'],
                                        'venta' => $venta,
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
                        $model->bouquetera = 0;
                        $model->venta_bouquetera = 0;
                    }
                    $model->tallos_exportables = $d['tallos_exportables'];
                    $model->nacional = $d['nacional'];
                    $model->bajas = $d['bajas'];
                    $model->tallos_vendidos = $d['tallos_vendidos'];
                    $model->venta = $d['venta'];

                    $model->save();
                }

                dump('FALTANTES:', $faltantes);
                /* ------------ ACTUALIZR NOTIFICACION fallos_upload_unosoft_venta --------------- */
                NotificacionesSistema::fallos_upload_unosoft_venta($faltantes);

                unlink($url);
            }
        }

        $time_duration = difFechas(date('Y-m-d H:i:s'), $ini)->h . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->m . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->s;
        Log::info('<*> DURACION: ' . $time_duration . '  <*>');
        Log::info('<<<<< * >>>>> Fin satisfactorio del comando "cron:importar_unosoft" <<<<< * >>>>>');
        dump('<*> DURACION: ' . $time_duration . '  <*>');
        dump('<<<<< * >>>>> Fin satisfactorio del comando "cron:importar_unosoft" <<<<< * >>>>>');
    }
}