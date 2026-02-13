<?php

namespace yura\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use yura\Modelos\CosechaDiaria;
use yura\Modelos\Modulo;
use yura\Modelos\ProyeccionModuloSemana;
use yura\Modelos\ResumenTotalSemanalExportcalas;

class jobActualizarCosecha implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $variedad;
    protected $fecha;
    protected $finca;
    protected $modulo;

    public function __construct($variedad, $fecha, $finca, $modulo)
    {
        $this->variedad = $variedad;
        $this->fecha = $fecha;
        $this->finca = $finca;
        $this->modulo = $modulo;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //$this->actualizar_cosechados($this->variedad, $this->modulo, $this->semana, $this->finca);
        $this->actualizar_cosecha_diaria($this->variedad, $this->fecha, $this->finca, $this->modulo);
    }

    function actualizar_cosechados($variedad, $modulo, $semana, $finca)
    {
        /*$cosechados = DB::table('desglose_recepcion as dr')
            ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
            ->select(DB::raw('sum(dr.cantidad_mallas * dr.tallos_x_malla) as cantidad'))
            ->where('dr.id_variedad', $variedad)
            ->where('dr.estado', 1)
            ->where('r.estado', 1)
            ->where('dr.id_modulo', $modulo)
            ->where('r.fecha_ingreso', '>=', $semana->fecha_inicial . ' 00:00:00')
            ->where('r.fecha_ingreso', '<=', $semana->fecha_final . ' 23:59:59')
            ->get()[0]->cantidad;
        $proy = ProyeccionModuloSemana::All()
            ->where('semana', $semana->codigo)
            ->where('id_variedad', $variedad)
            ->where('id_modulo', $modulo)
            ->first();
        if ($proy != '') {
            $proy->cosechados = $cosechados > 0 ? $cosechados : 0;
            $proy->save();
        }*/

        $cosechados = DB::table('desglose_recepcion as dr')
            ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
            ->select(DB::raw('sum(dr.cantidad_mallas * dr.tallos_x_malla) as cantidad'))
            ->where('dr.id_variedad', $variedad)
            ->where('dr.estado', 1)
            ->where('r.estado', 1)
            ->where('dr.id_empresa', $finca)
            ->where('r.fecha_ingreso', '>=', $semana->fecha_inicial . ' 00:00:00')
            ->where('r.fecha_ingreso', '<=', $semana->fecha_final . ' 23:59:59')
            ->get()[0]->cantidad;
        $cosechados = $cosechados > 0 ? $cosechados : 0;
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
        $res->tallos_cosechados = $cosechados;
        $res->save();
    }

    function actualizar_cosecha_diaria($variedad, $fecha, $finca, $modulo)
    {
        $modulo = Modulo::find($modulo);
        $model = CosechaDiaria::All()
            ->where('id_variedad', $variedad)
            ->where('fecha', $fecha)
            ->where('id_empresa', $finca)
            ->where('id_sector', $modulo->id_sector)
            ->first();
        if ($model == '') {
            $variedad_model = getVariedad($variedad);
            $model = new CosechaDiaria();
            $model->id_sector = $modulo->id_sector;
            $model->id_variedad = $variedad_model->id_variedad;
            $model->variedad_nombre = $variedad_model->nombre;
            $model->id_planta = $variedad_model->id_planta;
            $model->planta_nombre = $variedad_model->planta->nombre;
            $model->id_empresa = $finca;
            $model->fecha = $fecha;
        }
        $cosechados = DB::table('desglose_recepcion as dr')
            ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
            ->join('modulo as m', 'm.id_modulo', '=', 'dr.id_modulo')
            ->select(DB::raw('sum(dr.cantidad_mallas * dr.tallos_x_malla) as cantidad'))
            ->where('r.estado', 1)
            ->where('dr.estado', 1)
            ->where('dr.id_variedad', $variedad)
            ->where('m.id_sector', $modulo->id_sector)
            ->where('dr.id_empresa', $finca)
            ->where('r.fecha_ingreso', 'like', $fecha . '%')
            ->get()[0]->cantidad;
        $model->cosechados = $cosechados > 0 ? $cosechados : 0;
        $model->save();
    }
}
