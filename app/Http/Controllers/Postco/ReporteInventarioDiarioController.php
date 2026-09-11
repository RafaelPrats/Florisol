<?php

namespace yura\Http\Controllers\Postco;

use DB;
use Illuminate\Http\Request;
use yura\Http\Controllers\Controller;
use yura\Modelos\Planta;
use yura\Modelos\Submenu;

class ReporteInventarioDiarioController extends Controller
{
    public function inicio(Request $request)
    {
        $finca = getFincaActiva();
        $plantas = Planta::where('estado', 1)
            ->where('id_empresa', $finca)
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.postco.inventario_diario.inicio', [
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
        $fecha = $request->fecha >= '2026-09-12' ? $request->fecha : '2026-09-12';
        $fecha = opDiasFecha('+', 1, $fecha);

        foreach ($variedades as $var) {
            $ingreso = DB::table('ingreso_recepcion')
                ->select(DB::raw('sum(tallos) as cantidad'))
                ->where('id_variedad', $var->id_variedad)
                ->where('id_empresa', $finca)
                ->where('bodega', $request->bodega)
                ->where('fecha', '<', $fecha)
                ->where('fecha', '>=', '2026-09-12')
                ->get()[0]->cantidad;
            $salida = DB::table('salidas_recepcion as s')
                ->join('inventario_recepcion as i', 'i.id_inventario_recepcion', '=', 's.id_inventario_recepcion')
                ->select(DB::raw('sum(s.cantidad + s.basura) as cantidad'))
                ->where('s.id_variedad', $var->id_variedad)
                ->where('i.id_empresa', $finca)
                ->where('i.bodega', $request->bodega)
                ->where('s.fecha', '<', $fecha)
                ->where('s.fecha', '>=', '2026-09-12')
                ->where('s.fecha_registro', '>=', '2026-09-12')
                ->get()[0]->cantidad;
            $saldo = $ingreso - $salida;

            $var->saldo = $saldo;
        }
        return view('adminlte.gestion.postco.inventario_diario.partials.listado', [
            'listado' => $variedades,
            'fecha' => $request->fecha,
        ]);
    }
}
