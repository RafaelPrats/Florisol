<?php

namespace yura\Http\Controllers\Postco;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use yura\Http\Controllers\Controller;
use yura\Modelos\Postco;
use yura\Modelos\Submenu;
use yura\Modelos\Variedad;

class ReArmadoController extends Controller
{
    public function inicio(Request $request)
    {
        $variedades = Variedad::where('estado', 1)
            ->where('receta', 1)
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.postco.re_armado.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'variedades' => $variedades,
        ]);
    }

    public function listar_reporte(Request $request)
    {
        $variedades = DB::table('postco as p')
            ->join('variedad as v', 'v.id_variedad', '=', 'p.id_variedad')
            ->select('p.id_variedad', 'v.nombre', 'p.longitud')->distinct()
            ->where('v.estado', 1)
            ->whereColumn('p.armados', '>', 'p.ramos')
            ->where('p.fecha', '>=', $request->desde)
            ->where('p.fecha', '<=', $request->hasta);
        if ($request->variedad != 'T')
            $variedades = $variedades->where('p.id_variedad', $request->variedad);
        $variedades = $variedades->orderBy('v.nombre')
            ->get();
        $fechas = DB::table('postco as p')
            ->select('p.fecha')->distinct()
            ->whereColumn('p.armados', '>', 'p.ramos')
            ->where('p.fecha', '>=', $request->desde)
            ->where('p.fecha', '<=', $request->hasta)
            ->orderBy('p.fecha')
            ->get()->pluck('fecha')->toArray();
        $listado = [];
        foreach ($variedades as $var) {
            $valores = DB::table('postco')
                ->select(DB::raw('sum(armados - ramos) as cantidad'), 'fecha')
                ->whereColumn('armados', '>', 'ramos')
                ->where('id_variedad', $var->id_variedad)
                ->where('longitud', $var->longitud)
                ->where('fecha', '>=', $request->desde)
                ->where('fecha', '<=', $request->hasta)
                ->groupBy('fecha')
                ->orderBy('fecha')
                ->get();
            $listado[] = [
                'var' => $var,
                'valores' => $valores,
            ];
        }
        return view('adminlte.gestion.postco.re_armado.partials.listado', [
            'listado' => $listado,
            'fechas' => $fechas,
        ]);
    }

    public function modal_receta(Request $request)
    {
        $listado = Postco::where('id_variedad', $request->variedad)
            ->where('longitud', $request->longitud)
            ->whereIn('fecha', json_decode($request->fechas))
            ->orderBy('fecha')
            ->get();
        return view('adminlte.gestion.postco.re_armado.forms.modal_receta', [
            'listado' => $listado,
            'receta' => Variedad::find($request->variedad),
            'longitud' => $request->longitud,
        ]);
    }
}
