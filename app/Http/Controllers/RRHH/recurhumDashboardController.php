<?php

namespace yura\Http\Controllers\RRHH;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use yura\Http\Controllers\Controller;
use yura\Modelos\PersonalDetalle;
use yura\Modelos\Personal;
use yura\Modelos\Submenu;

  class recurhumDashboardController extends Controller
  {
      public function inicio(Request $request)
      {
        $users = Personal::select(DB::raw('count(*) as total, id_sexo'))
        ->groupBy('id_sexo')
        ->get();
       return view('adminlte.gestion.rrhh.recursos_humanos.inicio', [
         'users' => $users,
       'url' => $request->getRequestUri(),
       'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
   ]);
}
      
}