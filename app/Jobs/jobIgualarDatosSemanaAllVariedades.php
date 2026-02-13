<?php

namespace yura\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Modelos\Semana;
use yura\Modelos\Variedad;

class jobIgualarDatosSemanaAllVariedades implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $variedades = Variedad::where('id_planta', $this->request['id_planta'])->where('estado', 1)->get();
        foreach ($this->request['ids'] as $id) {
            $model = Semana::find($id);
            foreach ($variedades as $item) {
                $semana = Semana::All()->where('codigo', $model->codigo)
                    ->where('id_variedad', $item->id_variedad)
                    ->first();
                if ($semana == '') {
                    $semana = new Semana();
                    $semana->id_variedad = $item->id_variedad;
                    $semana->codigo = $model->codigo;
                    $semana->fecha_inicial = $model->fecha_inicial;
                    $semana->fecha_final = $model->fecha_final;
                    $semana->anno = $model->anno;
                }

                if ($this->request['curva'] != null)
                    $semana->curva = str_limit(strtoupper(espacios($this->request['curva'])), 11);
                if ($this->request['desecho'] != null)
                    $semana->desecho = str_limit(strtoupper(espacios($this->request['desecho'])), 2);
                if ($this->request['semana_poda'] != null)
                    $semana->semana_poda = str_limit(strtoupper(espacios($this->request['semana_poda'])), 2);
                if ($this->request['semana_siembra'] != null)
                    $semana->semana_siembra = str_limit(strtoupper(espacios($this->request['semana_siembra'])), 2);
                if ($this->request['tallos_planta_siembra'] != null)
                    $semana->tallos_planta_siembra = $this->request['tallos_planta_siembra'];
                if ($this->request['tallos_planta_poda'] != null)
                    $semana->tallos_planta_poda = $this->request['tallos_planta_poda'];
                if ($this->request['tallos_ramo_siembra'] != null)
                    $semana->tallos_ramo_siembra = $this->request['tallos_ramo_siembra'];
                if ($this->request['tallos_ramo_poda'] != null)
                    $semana->tallos_ramo_poda = $this->request['tallos_ramo_poda'];
                $semana->save();
            }
        }
    }
}
