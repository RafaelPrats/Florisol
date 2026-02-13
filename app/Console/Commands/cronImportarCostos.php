<?php

namespace yura\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Storage as Almacenamiento;
use yura\Modelos\ConfiguracionEmpresa;

class cronImportarCostos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:importar_costos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comando para cargar los archivos finca_#-costos_I.xls y finca_#-costos_M.xls en la carpeta public/storage/pdf_loads';

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
        Log::info('<<<<< ! >>>>> Ejecutando comando "cron:importar_costos" <<<<< ! >>>>>');

        $files_search = [];
        foreach (ConfiguracionEmpresa::All() as $f) {
            array_push($files_search, 'finca_' . $f->id_configuracion_empresa . '-costos_I.xlsx');
            array_push($files_search, 'finca_' . $f->id_configuracion_empresa . '-costos_M.xlsx');
        }

        $files = Almacenamiento::disk('pdf_loads')->files('');
        foreach ($files as $nombre_archivo) {
            if (in_array($nombre_archivo, $files_search)) {
                $url = public_path('storage/pdf_loads/' . $nombre_archivo);

                Artisan::call('costos:importar_file', [
                    'url' => $url,
                    'concepto' => substr(explode('.', $nombre_archivo)[0], -1),
                    'criterio' => 'V',
                    'sobreescribir' => true,
                    'finca' => explode('_', explode('-', $nombre_archivo)[0])[1],
                ]);

                unlink($url);
            }
        }

        $time_duration = difFechas(date('Y-m-d H:i:s'), $ini)->h . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->m . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->s;
        Log::info('<*> DURACION: ' . $time_duration . '  <*>');
        Log::info('<<<<< * >>>>> Fin satisfactorio del comando "cron:importar_costos" <<<<< * >>>>>');
    }
}