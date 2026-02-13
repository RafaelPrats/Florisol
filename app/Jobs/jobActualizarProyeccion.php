<?php

namespace yura\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use yura\Modelos\ResumenTotalSemanalExportcalas;

class jobActualizarProyeccion implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $variedad;    // id
    protected $fecha;
    protected $semana;  // modelo
    protected $finca;   // id

    public function __construct($variedad, $fecha, $semana, $finca)
    {
        $this->variedad = $variedad;
        $this->fecha = $fecha;
        $this->semana = $semana;
        $this->finca = $finca;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->actualizar_proyectados($this->variedad, $this->semana, $this->finca);
    }

    function actualizar_proyectados($variedad, $semana, $finca)
    {
        $proyectados_normal = DB::table('proyeccion_modulo_semana as p')
            ->join('modulo as m', 'm.id_modulo', '=', 'p.id_modulo')
            ->join('variedad as v', 'v.id_variedad', '=', 'p.id_variedad')
            ->join('planta as pta', 'pta.id_planta', '=', 'v.id_planta')
            ->select(DB::raw('sum(p.proyectados) as cantidad'))
            ->where('p.id_variedad', $variedad)
            ->where('p.estado', 1)
            ->where('m.estado', 1)
            ->where('m.id_empresa', $finca)
            ->where('p.semana', $semana->codigo)
            ->where('pta.tipo', 'N')
            ->get()[0]->cantidad;
        $proyectados_normal = $proyectados_normal > 0 ? $proyectados_normal : 0;

        $proyectados_perenne = DB::table('semana_proy_perenne as p')
            ->join('semana as s', 's.id_semana', '=', 'p.id_semana')
            ->join('variedad as v', 'v.id_variedad', '=', 's.id_variedad')
            ->join('planta as pta', 'pta.id_planta', '=', 'v.id_planta')
            ->select(DB::raw('sum(p.proyectados) as cantidad'))
            ->where('s.id_variedad', $variedad)
            ->where('s.codigo', $semana->codigo)
            ->where('p.id_empresa', $finca)
            ->where('pta.tipo', 'P')
            ->get()[0]->cantidad;
        $proyectados_perenne = $proyectados_perenne > 0 ? $proyectados_perenne : 0;

        $res = ResumenTotalSemanalExportcalas::All()
            ->where('semana', $semana->codigo)
            ->where('id_variedad', $variedad)
            ->where('id_empresa', $finca)
            ->first();
        if ($res == '') {
            $res = new ResumenTotalSemanalExportcalas();
            $res->semana = $semana->codigo;
            $res->id_variedad = $variedad;
            $res->id_empresa = $finca;
        }
        $res->tallos_proyectados = $proyectados_normal + $proyectados_perenne;
        $res->save();
    }
}
