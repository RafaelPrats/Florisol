<?php

namespace yura\Console\Commands;

use DB;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;
use yura\Http\Controllers\RRHH\rrhhPersonalController;
use yura\Modelos\Actividad;
use yura\Modelos\ActividadManoObra;
use yura\Modelos\Grupo;
use yura\Modelos\ManoObra;
use yura\Modelos\Rol;
use yura\Modelos\Sucursal;
use Illuminate\Http\Request;

class CargarPersonal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cargar:personal';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comando usado para cargar masivamente el personal';

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
        $url = public_path('personal.xlsx');
        $document = IOFactory::load($url);
        $activeSheetData = $document->getSheet(0)->toArray(null, true, true, true);

        try{

            foreach ($activeSheetData as $x => $data) {

                if($x>1){

                   $validaciones = $this->validacion($data,$x);

                   if(!count($validaciones)){ //NO HAY ERRORES

                       $rrhhPeronsal= new rrhhPersonalController;
                       $rrhhPeronsal->store_personal(new Request([
                            'nombre' => '',
                            'apellido' => '',
                            'cedula_identidad' => '',
                            'id_sucursal' => '',
                            'id_area' => '',
                            'id_actividad' => '',
                            'id_mano_obra' => '',
                            'sueldo' => '',
                            'tipo_rol' => '',
                            'grupo' => ''
                       ]));

                       if(!$rrhhPeronsal['success']){
                            throw new \Exception($rrhhPeronsal['mensaje']);
                       }else{
                            $this->info($rrhhPeronsal['mensaje']);
                       }

                   }else{

                        $this->warn(implode("\n",$validaciones));

                   }


                }

            }

        }catch(\Exception $e){

           $this->warn( $e->getMessage().' archivo '.$e->getFile().' Línea'. $e->getLine());

        }

    }

    public function validacion($data,$row)
    {
        try{

            $mensajes = [
                'A' => 'Faltan los nombres del personal en la columna A de la fila '.$row."\n",
                'B' => 'Faltan los apellidos del personal en la columna B de la filaa '.$row."\n",
                'C' => 'Falta la identificación del personal en la columna C de la fila '.$row."\n",
                'D' => 'Falta la fehca de ingreso del personal en la columna D de la fila '.$row."\n",
                'E' => 'Falta la sucursal del personal en la columna E de la fila '.$row."\n",
                'F' => 'Falta el sueldo del personal en la columna F de la fila'.$row."\n",
                'G' => 'Falta el rol del personal en la columna G de la fila '.$row."\n",
                'H' => 'Falta la agrupación del personal en la columna H de la fila '.$row."\n",
                'I' => 'Falta la mano de obra del personal en la columna I de la fila '.$row."\n",
                'J' => 'Falta la actividad del personal en la columna J de la fila '.$row."\n"
            ];

            $validaciones= [];

            foreach(range('A','J') as $letra){

                if($data[$letra] == null)
                    $validaciones[] = $mensajes[$letra];

                if($letra == 'E'){
                    if(!Sucursal::where(DB::raw("UPPER(nombre)"),strtoupper(trim($data[$letra])))->exists()){
                        $validaciones[]='No existe la sucursal '.$data[$letra].' en la columna E de la fila '.$row."\n";
                    }
                }

                if($letra == 'G'){
                    if(!Rol::where(DB::raw("UPPER(nombre)"),strtoupper(trim($data[$letra])))->exists()){
                        $validaciones[]='No existe el rol '.$data[$letra].' en la columna G de la fila '.$row."\n";
                    }
                }

                if($letra == 'H'){
                    if(!Grupo::where(DB::raw("UPPER(nombre)"),strtoupper(trim($data[$letra])))->exists()){
                        $validaciones[]='No existe la agrupación '.$data[$letra].' en la columna H de la fila '.$row."\n";
                    }
                }

                if($letra == 'I'){
                    $manoObra = ManoObra::where(DB::raw("UPPER(nombre)"),strtoupper(trim($data[$letra])))->first();
                    if(!isset($manoObra)){
                        $validaciones[]='No existe la mano de obra '.$data[$letra].' en la columna I de la fila '.$row."\n";
                    }
                }

                if($letra == 'J'){
                    $actividad = Actividad::where(DB::raw("UPPER(nombre)"),strtoupper(trim($data[$letra])))->first();
                    if(!isset($actividad)){
                        $validaciones[]='No existe la actividad '.$data[$letra].' en la columna J de la fila '.$row."\n";
                    }
                }

                if(isset($actividad) && isset($manoObra)){

                    $existsActividadManoObra = ActividadManoObra::where([
                        ['id_actividad',$actividad->id_actividad],
                        ['id_mano_obra',$manoObra->id_mano_obra]
                    ])->exists();

                    if(!$existsActividadManoObra){
                        $validaciones[]='No existe la relación de la actividad '.$actividad->nombre.' con la mano de obra '.$manoObra->nombre.' en la fila '.$row."\n";
                    }

                }
            }

            return $validaciones;

        }catch(\Exception $e){
            dd($e->getMessage());
        }

    }
}

