<?php

namespace yura\Http\Controllers\Propagacion;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use yura\Modelos\CicloCama;
use yura\Modelos\ResumenFenogramaPropagacion;
use yura\Modelos\Submenu;
use yura\Http\Controllers\Controller;

class propagFenogramaController extends Controller
{
    public function inicio(Request $request)
    {
        $plantas = DB::table('ciclo_cama as cc')
            ->join('variedad as v', 'v.id_variedad', '=', 'cc.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->select('v.id_planta', 'p.nombre')->distinct()
            ->where('cc.activo', 1)
            ->where('cc.id_empresa', getFincaActiva())
            ->orderBy('p.nombre')->get();
        return view('adminlte.crm.propagacion.fenograma.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'plantas' => $plantas,
        ]);
    }

    public function filtrar_ciclos(Request $request)
    {
        $finca_actual = $request->has('finca_actual') ? $request->finca_actual : getUsuario(Session::get('id_usuario'))->finca_activa;
        $list = ResumenFenogramaPropagacion::where('fecha_inicio', '<=', $request->fecha)
            ->where('fecha_fin', '>=', $request->fecha);
        if ($request->variedad != 'T')
            $list = $list->where('id_variedad', $request->variedad);
        elseif ($request->planta != '')
            $list = $list->where('id_planta', $request->planta);

        if ($finca_actual != 'T')
            $list = $list->where('id_empresa', $finca_actual);

        $list = $list->orderBy('fecha_inicio')->orderBy('cama_nombre')->get();

        return view('adminlte.crm.propagacion.fenograma.partials.filtrar_ciclos', [
            'ciclos' => $list
        ]);
    }
}