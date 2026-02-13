<?php

namespace yura\Http\Controllers\Proyecciones;

use Illuminate\Http\Request;
use yura\Http\Controllers\Controller;
use yura\Modelos\Submenu;

class DashboardTemperaturasController extends Controller
{
    public function inicio(Request $request)
    {
        $desde = opDiasFecha('-', 90, hoy());
        $hasta = hoy();
        return view('adminlte.gestion.proyecciones.dashboard_temperaturas.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'desde' => $desde,
            'hasta' => $hasta,
        ]);
    }

    public function listar_graficas_temperaturas(Request $request)
    {
        return view('adminlte.gestion.proyecciones.dashboard_temperaturas.partials.listado', [

        ]);
    }
}
