<?php

namespace yura\Http\Controllers\Postco;

use DB;
use Illuminate\Http\Request;
use yura\Http\Controllers\Controller;
use yura\Modelos\Planta;
use yura\Modelos\Submenu;

class InventarioDiarioController extends Controller
{
    public function inicio(Request $request)
    {
        $finca = getFincaActiva();
        $plantas = Planta::where('estado', 1)
            ->where('id_empresa', $finca)
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.postco.inventario_recepcion.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'plantas' => $plantas,
        ]);
    }

    public function listar_reporte(Request $request)
    {
        $finca = getFincaActiva();

        $variedades = DB::table('inventario_recepcion as i')
            ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->select(
                'i.id_variedad',
                'v.nombre as var_nombre',
                'p.nombre as pta_nombre'
            )
            ->where('i.disponibles', '>', 0)
            ->where('i.id_empresa', $finca)
            ->where('i.bodega', $request->bodega)
            ->when($request->planta != '', function ($q) use ($request) {
                $q->where('v.id_planta', $request->planta);
            })
            ->when($request->variedad != '', function ($q) use ($request) {
                $q->where('i.id_variedad', $request->variedad);
            })
            ->groupBy(
                'i.id_variedad',
                'v.nombre',
                'p.nombre'
            )
            ->orderBy('p.nombre')
            ->orderBy('v.nombre')
            ->get();

        $idsVariedad = $variedades->pluck('id_variedad')->toArray();

        $valores = DB::table('inventario_recepcion')
            ->select(
                'id_variedad',
                'fecha',
                DB::raw('SUM(disponibles) as tallos')
            )
            ->where('disponibles', '>', 0)
            ->where('id_empresa', $finca)
            ->where('bodega', $request->bodega)
            ->whereIn('id_variedad', $idsVariedad)
            ->groupBy('id_variedad', 'fecha')
            ->orderBy('fecha')
            ->get()
            ->groupBy('id_variedad');

        foreach ($variedades as $var) {
            $var->valores = $valores[$var->id_variedad] ?? collect();
        }

        return view('adminlte.gestion.postco.inventario_recepcion.partials.listado', [
            'variedades' => $variedades
        ]);
    }
}
