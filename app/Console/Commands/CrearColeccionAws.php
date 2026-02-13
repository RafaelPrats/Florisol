<?php

namespace yura\Console\Commands;

use Illuminate\Console\Command;
use yura\Modelos\ConfiguracionEmpresa;
use AWS;
use Illuminate\Support\Str;

class CrearColeccionAws extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crear:coleccion-aws';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comando usado para crear las colecciones de imagenes en aws';

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
        $ces = ConfiguracionEmpresa::get();

        try{
            $aws = AWS::createClient('rekognition');

            foreach($ces as $x => $ce){

                /*$aws->DeleteCollection([
                    'CollectionId' => $ce->coleccion_aws
                ]);
                dump('eliminada '.$ce->coleccion_aws);*/

                try{

                    if($ce->coleccion_aws == ''){

                        $nombreColeccion= Str::slug($ce->nombre);

                        $a = $aws->CreateCollection([
                            'CollectionId' => $nombreColeccion
                        ]);

                        if($a->get('StatusCode') === 200){
                            $ce->coleccion_aws = $nombreColeccion;
                            $ce->save();
                            dump('Colección creada '.$nombreColeccion);
                        }

                    }

                }catch(\Exception $e){

                    dd($e->getMessage());

                }

            }

        }catch(\Exception $e){
            dd($e->getMessage());
        }
    }
}
