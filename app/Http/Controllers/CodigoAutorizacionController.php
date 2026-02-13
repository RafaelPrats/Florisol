<?php

namespace yura\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use yura\Modelos\CodigoAutorizacion;
use yura\Modelos\Submenu;

class CodigoAutorizacionController extends Controller
{
    public function inicio(Request $request)
    {
        $codigos = CodigoAutorizacion::orderBy('nombre')->get();
        return view('adminlte.gestion.codigo_autorizacion.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'codigos' => $codigos
        ]);
    }

    public function store_codigos(Request $request)
    {
        try {
            DB::beginTransaction();
            foreach (json_decode($request->data) as $d) {
                $model = CodigoAutorizacion::find($d->id);
                $model->valor = $d->valor;
                $model->save();
            }
            $success = true;
            $msg = 'Se ha <strong>CREADO</strong> el sector correctamente';

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $success = false;
            $msg = '<div class="alert alert-danger text-center">' .
                '<p> Ha ocurrido un problema al guardar la informacion al sistema</p>' .
                '<p>' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine() . '</p>'
                . '</div>';
        }

        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }
}
