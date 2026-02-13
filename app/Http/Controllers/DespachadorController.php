<?php

namespace yura\Http\Controllers;

use Illuminate\Http\Request;
use yura\Modelos\Despachador;
use yura\Modelos\Submenu;

class DespachadorController extends Controller
{
    public function inicio(Request $request)
    {
        return view('adminlte.gestion.despachadores.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
        ]);
    }

    public function buscar_listado_despachadores(Request $request)
    {
        $listado = Despachador::orderBy('nombre')
            ->get();
        return view('adminlte.gestion.despachadores.partials.listado', [
            'listado' => $listado,
        ]);
    }

    public function store_despachador(Request $request)
    {
        $model = new Despachador();
        $model->nombre = espacios(mb_strtoupper($request->nombre));
        $model->save();
        return [
            'success' => true,
            'mensaje' => 'Se ha <strong>GUARDADO</strong> un nuevo despachador',
        ];
    }

    public function update_despachador(Request $request)
    {
        $model = Despachador::find($request->id);
        $model->nombre = espacios(mb_strtoupper($request->nombre));
        $model->save();
        return [
            'success' => true,
            'mensaje' => 'Se ha <strong>EDITADO</strong> el despachador',
        ];
    }

    public function desactivar_despachador(Request $request)
    {
        $model = Despachador::find($request->id);
        $model->estado = !$model->estado;
        $model->save();
        return [
            'success' => true,
            'mensaje' => 'Se ha <strong>MODIFICADO</strong> el despachador',
        ];
    }
}
