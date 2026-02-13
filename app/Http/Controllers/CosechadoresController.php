<?php

namespace yura\Http\Controllers;

use Illuminate\Http\Request;
use yura\Modelos\Cosechador;
use yura\Modelos\Submenu;

class CosechadoresController extends Controller
{
    public function inicio(Request $request)
    {
        return view('adminlte.gestion.cosechadores.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
        ]);
    }

    public function buscar_listado_cosechadores(Request $request)
    {
        $listado = Cosechador::where('id_empresa', getFincaActiva())
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.cosechadores.partials.listado', [
            'listado' => $listado,
        ]);
    }

    public function store_cosechador(Request $request)
    {
        $model = new Cosechador();
        $model->nombre = espacios(mb_strtoupper($request->nombre));
        $model->id_empresa = getFincaActiva();
        $model->save();
        return [
            'success' => true,
            'mensaje' => 'Se ha <strong>GUARDADO</strong> un nuevo cosechador',
        ];
    }

    public function update_cosechador(Request $request)
    {
        $model = Cosechador::find($request->id);
        $model->nombre = espacios(mb_strtoupper($request->nombre));
        $model->save();
        return [
            'success' => true,
            'mensaje' => 'Se ha <strong>EDITADO</strong> el cosechador',
        ];
    }

    public function desactivar_cosechador(Request $request)
    {
        $model = Cosechador::find($request->id);
        $model->estado = $model->estado == 1 ? 0 : 1;
        $model->save();
        return [
            'success' => true,
            'mensaje' => 'Se ha <strong>MODIFICADO</strong> el cosechador',
        ];
    }
}
