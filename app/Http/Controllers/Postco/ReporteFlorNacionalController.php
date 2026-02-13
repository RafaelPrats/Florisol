<?php

namespace yura\Http\Controllers\Postco;

use DB;
use Illuminate\Http\Request;
use yura\Http\Controllers\Controller;
use yura\Modelos\FincaFlorNacional;
use yura\Modelos\Planta;
use yura\Modelos\Submenu;

class ReporteFlorNacionalController extends Controller
{
    public function inicio(Request $request)
    {
        $motivos = DB::table('motivo_flor_nacional')
            ->where('estado', 1)
            ->orderBy('nombre')
            ->get();
        $plantas = Planta::where('estado', 1)
            ->orderBy('nombre')
            ->get();
        $fincas = FincaFlorNacional::where('estado', 1)
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.postco.reporte_flor_nacional.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'motivos' => $motivos,
            'plantas' => $plantas,
            'fincas' => $fincas,
            'desde' => opDiasFecha('-', 7, hoy()),
            'hasta' => opDiasFecha('-', 1, hoy()),
        ]);
    }

    public function listar_reporte(Request $request)
    {
        $variedades = DB::table('flor_nacional as fn')
            ->join('variedad as v', 'v.id_variedad', '=', 'fn.id_variedad')
            ->join('planta as pl', 'pl.id_planta', '=', 'v.id_planta')
            ->select(
                'fn.id_variedad',
                'v.nombre as var_nombre',
                'v.id_planta',
                'pl.nombre as pta_nombre',
            )->distinct()
            ->where('v.estado', 1)
            ->where('fn.fecha', '>=', $request->desde)
            ->where('fn.fecha', '<=', $request->hasta);
        if ($request->finca != '')
            $variedades = $variedades->where('fn.id_finca_flor_nacional', $request->finca);
        if ($request->motivo != '')
            $variedades = $variedades->where('fn.id_motivo_flor_nacional', $request->motivo);
        if ($request->planta != '')
            $variedades = $variedades->where('v.id_planta', $request->planta);
        if ($request->variedad != '')
            $variedades = $variedades->where('fn.id_variedad', $request->variedad);
        $variedades = $variedades->orderBy(
            'pl.nombre',
            'v.nombre'
        )->get();

        $fechas = DB::table('flor_nacional as fn')
            ->join('variedad as v', 'v.id_variedad', '=', 'fn.id_variedad')
            ->select('fn.fecha')->distinct()
            ->where('v.estado', 1)
            ->where('fn.fecha', '>=', $request->desde)
            ->where('fn.fecha', '<=', $request->hasta);
        if ($request->finca != '')
            $fechas = $fechas->where('fn.id_finca_flor_nacional', $request->finca);
        if ($request->motivo != '')
            $fechas = $fechas->where('fn.id_motivo_flor_nacional', $request->motivo);
        if ($request->planta != '')
            $fechas = $fechas->where('v.id_planta', $request->planta);
        if ($request->variedad != '')
            $fechas = $fechas->where('fn.id_variedad', $request->variedad);
        $fechas = $fechas->orderBy('fn.fecha')
            ->get()->pluck('fecha')->toArray();

        $listado = [];
        foreach ($variedades as $var) {
            $valores = DB::table('flor_nacional as fn')
                ->select(
                    'fn.fecha',
                    DB::raw('sum(fn.nacional) as nacional'),
                    DB::raw('sum(fn.produccion) as produccion'),
                )
                ->where('fn.fecha', '>=', $request->desde)
                ->where('fn.fecha', '<=', $request->hasta)
                ->where('fn.id_variedad', $var->id_variedad);
            if ($request->finca != '')
                $valores = $valores->where('fn.id_finca_flor_nacional', $request->finca);
            if ($request->motivo != '')
                $valores = $valores->where('fn.id_motivo_flor_nacional', $request->motivo);
            $valores = $valores->groupBy('fn.fecha')
                ->orderBy('fn.fecha')
                ->get();

            $listado[] = [
                'label' => $var,
                'valores' => $valores,
            ];
        }
        return view('adminlte.gestion.postco.reporte_flor_nacional.partials.listado', [
            'listado' => $listado,
            'fechas' => $fechas,
        ]);
    }
}
