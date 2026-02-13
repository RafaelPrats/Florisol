<?php

namespace yura\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use yura\Modelos\ProyNoPerennes;
use yura\Modelos\Semana;

class jobProyNoPerennes implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $variedad;    // id
    protected $semana;       // codigo semana
    protected $empresa;   // id

    public function __construct($variedad, $semana, $empresa)
    {
        $this->variedad = $variedad;
        $this->semana = $semana;
        $this->empresa = $empresa;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $semana = Semana::All()
            ->where('codigo', $this->semana)
            ->where('id_variedad', $this->variedad)
            ->first();
        $finca = $this->empresa;
        $semanas_prev = DB::select('select * from semana 
                            where fecha_inicial >= "' . opDiasFecha('-', (25 * 7), $semana->fecha_inicial) . '" and codigo < ' . $semana->codigo . ' and id_variedad = ' . $this->variedad . ' 
                            order by codigo');
        $area_produccion = 0;
        $area_cutivo = 0;
        $proyectados = 0;
        foreach ($semanas_prev as $prev) {
            $max_semanas = max($prev->semana_poda, $prev->semana_siembra);
            $curva_siembra = explode('-', $prev->curva);
            $curva_poda = explode('-', $prev->curva_poda);
            $max_curvas = max(count($curva_siembra), count($curva_poda));
            $fecha_fin = opDiasFecha('+', ($max_semanas + $max_curvas - 1) * 7, $prev->fecha_final);
            if ($fecha_fin >= $semana->fecha_inicial) {
                $prev = Semana::find($prev->id_semana);
                $getSemanaEmpresaS = $prev->getSemanaEmpresa($finca, 'S');
                $getSemanaEmpresaP = $prev->getSemanaEmpresa($finca, 'P');

                $area_siembra = isset($getSemanaEmpresaS) && $getSemanaEmpresaS->densidad > 0 ? $getSemanaEmpresaS->plantas_iniciales / $getSemanaEmpresaS->densidad : 0;
                $area_poda = isset($getSemanaEmpresaP) && $getSemanaEmpresaP->densidad > 0 ? $getSemanaEmpresaP->plantas_iniciales / $getSemanaEmpresaP->densidad : 0;
                $area_produccion += $area_siembra + $area_poda;

                if ($prev->semana_siembra > 0) {
                    $ptas_iniciales = isset($getSemanaEmpresaS) ? $getSemanaEmpresaS->plantas_iniciales : 0;
                    foreach ($curva_siembra as $i => $cv) {
                        $fecha_meta = opDiasFecha('+', ($prev->semana_siembra + $i) * 7, $prev->fecha_inicial);
                        if ($fecha_meta == $semana->fecha_inicial) {
                            $area_cutivo += $area_siembra;

                            $total = $ptas_iniciales * $prev->tallos_planta_siembra;
                            $total = $total * ((100 - $prev->desecho) / 100);
                            if ($cv > 0)
                                $proyectados += round($total * ($cv / 100), 2);
                        }
                    }
                }

                if ($prev->semana_poda > 0) {
                    $ptas_iniciales = isset($getSemanaEmpresaP) ? $getSemanaEmpresaP->plantas_iniciales : 0;
                    foreach ($curva_poda as $i => $cv) {
                        $fecha_meta = opDiasFecha('+', ($prev->semana_poda + $i) * 7, $prev->fecha_inicial);
                        if ($fecha_meta == $semana->fecha_inicial) {
                            $area_cutivo += $area_poda;

                            $total = $ptas_iniciales * $prev->tallos_planta_poda;
                            $total = $total * ((100 - $prev->desecho_poda) / 100);
                            if ($cv > 0)
                                $proyectados += round($total * ($cv / 100), 2);
                        }
                    }
                }
            }
        }
        $getSemanaEmpresaS = $semana->getSemanaEmpresa($finca, 'S');
        $getSemanaEmpresaP = $semana->getSemanaEmpresa($finca, 'P');

        $area_produccion += isset($getSemanaEmpresaS) && $getSemanaEmpresaS->densidad > 0 ? $getSemanaEmpresaS->plantas_iniciales / $getSemanaEmpresaS->densidad : 0;
        $area_produccion += isset($getSemanaEmpresaP) && $getSemanaEmpresaP->densidad > 0 ? $getSemanaEmpresaP->plantas_iniciales / $getSemanaEmpresaP->densidad : 0;

        $getProyNoPerennesByEmpresa = $semana->getProyNoPerennesByEmpresa($finca);
        if (!isset($getProyNoPerennesByEmpresa)) {
            $getProyNoPerennesByEmpresa = new ProyNoPerennes();
            $getProyNoPerennesByEmpresa->id_semana = $semana->id_semana;
            $getProyNoPerennesByEmpresa->id_empresa = $finca;
            $getProyNoPerennesByEmpresa->save();
            $getProyNoPerennesByEmpresa = $semana->getProyNoPerennesByEmpresa($finca);
        }
        $getProyNoPerennesByEmpresa->area_produccion = round($area_produccion, 2);
        $getProyNoPerennesByEmpresa->area_semana = round($area_cutivo, 2);
        $getProyNoPerennesByEmpresa->proyectados = $proyectados;
        $getProyNoPerennesByEmpresa->save();
    }
}