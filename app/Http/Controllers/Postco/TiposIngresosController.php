<?php

namespace yura\Http\Controllers\Postco;

use DB;
use Illuminate\Http\Request;
use yura\Http\Controllers\Controller;
use yura\Modelos\Planta;
use yura\Modelos\Submenu;

class TiposIngresosController extends Controller
{
    public function inicio(Request $request)
    {
        $plantas = Planta::where('estado', 1)
            ->where('id_planta', '!=', 151)
            ->where('id_planta', '!=', 128)
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.postco.ingresos_proveedor.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'plantas' => $plantas,
        ]);
    }

    public function listar_reporte(Request $request)
    {
        $variedades = DB::table('desglose_recepcion as dr')
            ->join('variedad as v', 'v.id_variedad', '=', 'dr.id_variedad')
            ->select(
                'v.id_planta',
                'dr.id_variedad',
                'v.nombre as var_nombre'
            )->distinct()
            ->where('dr.fecha', '>=', $request->desde)
            ->where('dr.fecha', '<=', $request->hasta);
        if ($request->planta != '')
            $variedades = $variedades->where('v.id_planta', $request->planta);
        if ($request->variedad != '')
            $variedades = $variedades->where('dr.id_variedad', $request->variedad);
        $variedades = $variedades->orderBy('v.id_planta')
            ->orderBy('v.nombre')
            ->get();
        $fechas = DB::table('desglose_recepcion as dr')
            ->join('variedad as v', 'v.id_variedad', '=', 'dr.id_variedad')
            ->select('dr.fecha')->distinct()
            ->where('dr.fecha', '>=', $request->desde)
            ->where('dr.fecha', '<=', $request->hasta);
        if ($request->planta != '')
            $fechas = $fechas->where('v.id_planta', $request->planta);
        if ($request->variedad != '')
            $fechas = $fechas->where('dr.id_variedad', $request->variedad);
        $fechas = $fechas->orderBy('dr.fecha')
            ->get()->pluck('fecha')->toArray();
        $listado = [];
        foreach ($variedades as $var) {
            $planta = Planta::find($var->id_planta);
            $valores_comprados = DB::table('desglose_recepcion')
                ->select(
                    'fecha',
                    DB::raw('sum(cantidad_mallas * tallos_x_malla) as cantidad')
                )
                ->where('id_variedad', $var->id_variedad)
                ->whereNull('id_despacho_proveedor')
                ->whereIn('fecha', $fechas)
                ->groupBy('fecha')
                ->get();
            $valores_finca = DB::table('desglose_recepcion')
                ->select(
                    'fecha',
                    DB::raw('sum(cantidad_mallas * tallos_x_malla) as cantidad')
                )
                ->where('id_variedad', $var->id_variedad)
                ->whereNotNull('id_despacho_proveedor')
                ->whereIn('fecha', $fechas)
                ->groupBy('fecha')
                ->get();
            $listado[] = [
                'planta' => $planta,
                'var' => $var,
                'valores_comprados' => $valores_comprados,
                'valores_finca' => $valores_finca,
            ];
        }

        return view('adminlte.gestion.postco.ingresos_proveedor.partials.listado', [
            'fechas' => $fechas,
            'listado' => $listado,
        ]);
    }
}
