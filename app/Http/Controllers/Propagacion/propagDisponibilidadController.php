<?php

namespace yura\Http\Controllers\Propagacion;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use yura\Http\Controllers\Controller;
use yura\Jobs\jobActualizarDisponibilidad;
use yura\Modelos\Planta;
use yura\Modelos\PropagDisponibilidad;
use yura\Modelos\Submenu;

class propagDisponibilidadController extends Controller
{
    public function inicio(Request $request)
    {
        $semana_hasta = getSemanaByDate(opDiasFecha('+', 42, date('Y-m-d')));
        $plantas = Planta::where('estado', 1)->where('tipo', 'N')->orderBy('nombre')->get();
        return view('adminlte.gestion.propagacion.disponibilidad.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'semana_hasta' => $semana_hasta,
            'plantas' => $plantas,
        ]);
    }

    public function listar_disponibilidades(Request $request)
    {
        $finca = getFincaActiva();
        $listado = PropagDisponibilidad::where('id_variedad', $request->variedad)
            ->where('semana', '>=', $request->desde)
            ->where('semana', '<=', $request->hasta)
            ->where('id_empresa', $finca)
            ->orderBy('semana')
            ->get();
        return view('adminlte.gestion.propagacion.disponibilidad.partials.listado', [
            'listado' => $listado,
        ]);
    }

    public function update_disponibilidad(Request $request)
    {
        $model = PropagDisponibilidad::find($request->id);
        $model->desecho = $request->desecho;
        $model->plantas_disponibles = ($model->saldo_inicial + $model->plantas_sembradas) - $model->desecho();
        $model->requerimientos_adicionales = $request->req_adicionales;
        $model->mantener_cambios = $request->mantener_cambios;
        $model->requerimientos = $request->requerimientos;
        $model->saldo = $model->plantas_disponibles - $model->calcular_requerimientos();
        if ($model->save()) {
            Artisan::call('update:propag_disponibilidad', [
                'semana_desde' => $request->semana_desde,
                'semana_hasta' => $request->semana_hasta,
                'variedad' => $model->id_variedad,
                'empresa' => getFincaActiva(),
                'cron' => 0,
            ]);
            jobActualizarDisponibilidad::dispatch($request->semana_desde, getLastSemanaByVariedad($model->id_variedad)->codigo, $model->id_variedad, getFincaActiva())
                ->onQueue('propag');
            $success = true;
            $msg = '<div class="alert alert-success text-center">Se ha actualizado la información satisfactoriamente</div>';
        } else {
            $success = false;
            $msg = '<div class="alert alert-danger text-center">Ha ocurrido un problema al guardar la información</div>';
        }
        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }

    public function update_semana(Request $request)
    {
        Artisan::call('update:propag_disponibilidad', [
            'semana_desde' => $request->semana,
            'semana_hasta' => $request->semana,
            'variedad' => $request->variedad,
            'obligatorio' => 1,
            'empresa' => getFincaActiva(),
            'cron' => 0,
        ]);
        return [
            'success' => true,
            'mensaje' => '<div class="alert alert-success text-center">Se ha actualizado la información satisfactoriamente</div>',
        ];
    }
}
