<?php

namespace yura\Http\Controllers\Proyecciones;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use yura\Http\Controllers\Controller;
use yura\Modelos\Submenu;

class EjecucionNoPerennesController extends Controller
{
    public function inicio(Request $request)
    {
        $semanas = DB::table('semana')
            ->select('codigo', 'fecha_inicial', 'fecha_final')->distinct()
            ->where('estado', 1)
            ->where('fecha_inicial', '<=', hoy())
            ->orderBy('codigo', 'desc')
            ->get();
        return view('adminlte.gestion.proyecciones.ejecucion_no_perennes.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'semanas' => $semanas,
        ]);
    }

    public function listar_ejecucion_no_perennes(Request $request)
    {
        $finca = getFincaActiva();
        $plantas_S = DB::table('semana_empresa as se')
            ->join('semana as s', 's.id_semana', '=', 'se.id_semana')
            ->join('variedad as v', 'v.id_variedad', '=', 's.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->select('v.id_planta', 'p.nombre')->distinct()
            ->where('s.codigo', $request->semana)
            ->where('se.plantas_iniciales', '>', 0)
            ->where('se.densidad', '>', 0)
            ->where('se.poda_siembra', 'S')
            ->where('p.tipo', 'N')
            ->where('se.id_empresa', $finca)
            ->orderBy('p.nombre')
            ->get();
        $plantas_P = DB::table('semana_empresa as se')
            ->join('semana as s', 's.id_semana', '=', 'se.id_semana')
            ->join('variedad as v', 'v.id_variedad', '=', 's.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->select('v.id_planta', 'p.nombre')->distinct()
            ->where('s.codigo', $request->semana)
            ->where('se.plantas_iniciales', '>', 0)
            ->where('se.densidad', '>', 0)
            ->where('se.poda_siembra', 'P')
            ->where('p.tipo', 'N')
            ->where('se.id_empresa', $finca)
            ->orderBy('p.nombre')
            ->get();
        $listado_S = [];
        foreach ($plantas_S as $p) {
            $variedades = DB::table('semana_empresa as se')
                ->join('semana as s', 's.id_semana', '=', 'se.id_semana')
                ->join('variedad as v', 'v.id_variedad', '=', 's.id_variedad')
                ->select('s.id_variedad', 'v.nombre', 's.ejecutado')->distinct()
                ->where('s.codigo', $request->semana)
                ->where('v.id_planta', $p->id_planta)
                ->where('se.plantas_iniciales', '>', 0)
                ->where('se.densidad', '>', 0)
                ->where('se.poda_siembra', 'S')
                ->where('se.id_empresa', $finca)
                ->orderBy('v.nombre')
                ->get();
            array_push($listado_S, [
                'planta' => $p,
                'variedades' => $variedades,
            ]);
        }
        $listado_P = [];
        foreach ($plantas_P as $p) {
            $variedades = DB::table('semana_empresa as se')
                ->join('semana as s', 's.id_semana', '=', 'se.id_semana')
                ->join('variedad as v', 'v.id_variedad', '=', 's.id_variedad')
                ->select('s.id_variedad', 'v.nombre', 's.ejecutado')->distinct()
                ->where('s.codigo', $request->semana)
                ->where('v.id_planta', $p->id_planta)
                ->where('se.plantas_iniciales', '>', 0)
                ->where('se.densidad', '>', 0)
                ->where('se.poda_siembra', 'P')
                ->where('se.id_empresa', $finca)
                ->orderBy('v.nombre')
                ->get();
            array_push($listado_P, [
                'planta' => $p,
                'variedades' => $variedades,
            ]);
        }
        return view('adminlte.gestion.proyecciones.ejecucion_no_perennes.partials.listado', [
            'listado_S' => $listado_S,
            'listado_P' => $listado_P,
        ]);
    }
}
