<?php

namespace yura\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use yura\Modelos\SalidasRecepcion;
use yura\Modelos\Submenu;

class SalidasRecepcionController extends Controller
{
    public function inicio(Request $request)
    {
        $plantas = DB::table('salidas_recepcion as s')
            ->join('variedad as v', 'v.id_variedad', '=', 's.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->select('v.id_planta', 'p.nombre')->distinct()
            ->where('v.estado', 1)
            ->where('p.estado', 1)
            ->where('v.receta', 0)
            ->orderBy('p.nombre')
            ->get();
        return view('adminlte.gestion.postcocecha.salidas_recepcion.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'plantas' => $plantas,
        ]);
    }

    public function listar_reporte(Request $request)
    {
        $fechas = [];
        $f = $request->desde;
        while ($f <= $request->hasta) {
            $fechas[] = $f;
            $f = opDiasFecha('+', 1, $f);
        }
        if ($request->criterio == 'S') {
            /* SALIDAS */
            $variedades = DB::table('salidas_recepcion as s')
                ->join('variedad as v', 'v.id_variedad', '=', 's.id_variedad')
                ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                ->select('p.nombre as nombre_planta', 's.id_variedad', 'v.nombre as nombre_variedad')->distinct()
                ->where('s.fecha', '>=', $request->desde)
                ->where('s.fecha', '>=', $request->desde)
                ->where('s.basura', 0)
                ->where('s.combos', 0);
            if ($request->planta != 'T') {  // el filtro tiene una planta
                if ($request->variedad != 'T') {    // el filtro tiene una variedad
                    $variedades = $variedades->where('s.id_variedad', $request->variedad);
                } else {    // el filtro tiene todas las variedades
                    $variedades = $variedades->where('v.id_planta', $request->planta);
                }
            }
            $variedades = $variedades->orderBy('p.nombre')
                ->orderBy('v.nombre')
                ->get();
            $listado = [];
            foreach ($variedades as $var) {
                $valores_fechas = [];
                foreach ($fechas as $f) {
                    $cantidad = DB::table('salidas_recepcion as s')
                        ->select(DB::raw('sum(s.cantidad) as cantidad'))
                        ->where('s.fecha', $f)
                        ->where('s.id_variedad', $var->id_variedad)
                        ->where('s.basura', 0)
                        ->where('s.combos', 0)
                        ->get()[0]->cantidad;
                    $valores_fechas[] = $cantidad;
                }
                $listado[] = [
                    'variedad' => $var,
                    'valores_fechas' => $valores_fechas,
                ];
            }
        }
        if ($request->criterio == 'B') {
            /* SALIDAS */
            $variedades = DB::table('salidas_recepcion as s')
                ->join('variedad as v', 'v.id_variedad', '=', 's.id_variedad')
                ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                ->select('p.nombre as nombre_planta', 's.id_variedad', 'v.nombre as nombre_variedad')->distinct()
                ->where('s.fecha', '>=', $request->desde)
                ->where('s.fecha', '>=', $request->desde)
                ->where('s.basura', 1)
                ->where('s.combos', 0);
            if ($request->planta != 'T') {  // el filtro tiene una planta
                if ($request->variedad != 'T') {    // el filtro tiene una variedad
                    $variedades = $variedades->where('s.id_variedad', $request->variedad);
                } else {    // el filtro tiene todas las variedades
                    $variedades = $variedades->where('v.id_planta', $request->planta);
                }
            }
            $variedades = $variedades->orderBy('p.nombre')
                ->orderBy('v.nombre')
                ->get();
            $listado = [];
            foreach ($variedades as $var) {
                $valores_fechas = [];
                foreach ($fechas as $f) {
                    $cantidad = DB::table('salidas_recepcion as s')
                        ->select(DB::raw('sum(s.cantidad) as cantidad'))
                        ->where('s.fecha', $f)
                        ->where('s.id_variedad', $var->id_variedad)
                        ->where('s.basura', 1)
                        ->where('s.combos', 0)
                        ->get()[0]->cantidad;
                    $valores_fechas[] = $cantidad;
                }
                $listado[] = [
                    'variedad' => $var,
                    'valores_fechas' => $valores_fechas,
                ];
            }
        }
        if ($request->criterio == 'C') {
            /* SALIDAS */
            $variedades = DB::table('salidas_recepcion as s')
                ->join('variedad as v', 'v.id_variedad', '=', 's.id_variedad')
                ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                ->select('p.nombre as nombre_planta', 's.id_variedad', 'v.nombre as nombre_variedad')->distinct()
                ->where('s.fecha', '>=', $request->desde)
                ->where('s.fecha', '>=', $request->desde)
                ->where('s.basura', 0)
                ->where('s.combos', 1);
            if ($request->planta != 'T') {  // el filtro tiene una planta
                if ($request->variedad != 'T') {    // el filtro tiene una variedad
                    $variedades = $variedades->where('s.id_variedad', $request->variedad);
                } else {    // el filtro tiene todas las variedades
                    $variedades = $variedades->where('v.id_planta', $request->planta);
                }
            }
            $variedades = $variedades->orderBy('p.nombre')
                ->orderBy('v.nombre')
                ->get();
            $listado = [];
            foreach ($variedades as $var) {
                $valores_fechas = [];
                foreach ($fechas as $f) {
                    $cantidad = DB::table('salidas_recepcion as s')
                        ->select(DB::raw('sum(s.cantidad) as cantidad'))
                        ->where('s.fecha', $f)
                        ->where('s.id_variedad', $var->id_variedad)
                        ->where('s.basura', 0)
                        ->where('s.combos', 1)
                        ->get()[0]->cantidad;
                    $valores_fechas[] = $cantidad;
                }
                $listado[] = [
                    'variedad' => $var,
                    'valores_fechas' => $valores_fechas,
                ];
            }
        }
        return view('adminlte.gestion.postcocecha.salidas_recepcion.partials.listado', [
            'fechas' => $fechas,
            'listado' => $listado,
        ]);
    }
}
