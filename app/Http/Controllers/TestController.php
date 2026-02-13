<?php

namespace yura\Http\Controllers;

use Illuminate\Http\Request;
use yura\Modelos\Submenu;

class TestController extends Controller
{
    public function inicio(Request $request)
    {
        return view('adminlte.gestion.test.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'hasta' => opDiasFecha('+', 45, hoy()),
            'desde' => opDiasFecha('-', 7, hoy()),
        ]);
    }

    public function test(Request $request)
    {
        return view('adminlte.gestion.test.partials.listado', [
            'hasta' => $request->hasta,
            'desde' => $request->desde,
        ]);
    }
}
