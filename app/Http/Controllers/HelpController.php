<?php

namespace yura\Http\Controllers;

use Illuminate\Http\Request;

class HelpController extends Controller
{
    public function help_costos_importar(Request $request)
    {
        return view('adminlte.help.help_costos_importar', []);
    }

    public function help_importar_unosoft(Request $request)
    {
        return view('adminlte.help.help_importar_unosoft', []);
    }
}
