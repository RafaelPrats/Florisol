<?php

namespace yura\Http\Controllers\Postcosecha;

use Illuminate\Http\Request;
use yura\Http\Controllers\Controller;
use yura\Modelos\Exportador;
use yura\Modelos\Submenu;

class ExportadorController extends Controller
{
    public function inicio(Request $request)
    {
        return view('adminlte.gestion.exportadores.inicio',[
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', substr($request->getRequestUri(), 1))->get()[0],
            'text' => ['titulo'=>'Exportadores','subtitulo'=>'módulo de postcocecha']
        ]);
    }

    public function listar()
    {
        $exportadores = Exportador::orderBy('estado','asc')->orderBy('nombre','asc')->get();
        return view('adminlte.gestion.exportadores.partials.listado', [
            'exportadores' => $exportadores,
        ]);
    }

    public function store(Request $request)
    {
        $model = new Exportador;
        $model->nombre = $request->nombre;
        $model->identificacion = $request->identificacion;
        $model->codigo_externo = $request->codigo_externo;
        $model->id_empresa = getFincaActiva();
        $model->save();

        $success = true;
        $msg = 'Se ha <strong>creado</strong> un nuevo exportador satisfactoriamente';
        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }

    public function update(Request $request)
    {
        $model = Exportador::find($request->id);
        $model->nombre = $request->nombre;
        $model->identificacion = $request->identificacion;
        $model->codigo_externo = $request->codigo_externo;
        $model->id_empresa = getFincaActiva();
        $model->save();

        $success = true;
        $msg = 'Se ha <strong>actualizado</strong> el exportador satisfactoriamente';
        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }

    public function cambiar_estado(Request $request)
    {
        $model = Exportador::find($request->id);
        $model->estado = $model->estado == 1 ? 0 : 1;
        $model->save();

        $success = true;
        $msg = 'Se ha <strong>cambiado el estado</strong> del exportador satisfactoriamente';
        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }
}
