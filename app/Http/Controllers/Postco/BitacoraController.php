<?php

namespace yura\Http\Controllers\Postco;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use yura\Http\Controllers\Controller;
use yura\Modelos\Planta;
use yura\Modelos\RegistroMovimientos;
use yura\Modelos\Submenu;
use yura\Modelos\Variedad;

class BitacoraController extends Controller
{
    public function inicio(Request $request)
    {
        $finca = getFincaActiva();
        $plantas = Planta::where('estado', 1)
            ->where('id_empresa', $finca)
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.postco.bitacora_recepcion.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'plantas' => $plantas,
        ]);
    }

    public function listar_reporte(Request $request)
    {
        $finca = getFincaActiva();
        $desde = $request->desde >= '2026-09-17' ? $request->desde : '2026-09-17';
        $hasta = $request->hasta;
        // Calcular saldo inicial
        $ingreso_inicial = DB::table('ingreso_recepcion')
            ->select(DB::raw('sum(tallos) as cantidad'))
            ->where('id_variedad', $request->variedad)
            ->where('id_empresa', $finca)
            ->where('bodega', $request->bodega)
            ->where('fecha', '<', $desde)
            ->where('fecha', '>=', '2026-09-17')
            ->where('fecha_registro', '>=', '2026-09-17 00:00:00')
            ->get()[0]->cantidad;
        $salida_inicial = DB::table('salidas_recepcion as s')
            ->join('inventario_recepcion as i', 'i.id_inventario_recepcion', '=', 's.id_inventario_recepcion')
            ->select(DB::raw('sum(s.cantidad + s.basura) as cantidad'))
            ->where('s.id_variedad', $request->variedad)
            ->where('i.id_empresa', $finca)
            ->where('i.bodega', $request->bodega)
            ->where('s.fecha', '<', $desde)
            ->where('s.fecha', '>=', '2026-09-17')
            ->where('s.fecha_registro', '>=', '2026-09-17 00:00:00')
            ->get()[0]->cantidad;
        $saldo_inicial = $ingreso_inicial - $salida_inicial;

        $variedad = Variedad::find($request->variedad);
        $listado = RegistroMovimientos::where('fecha', '>=', $request->desde)
            ->where('fecha', '<=', $request->hasta)
            ->where('id_empresa', $finca)
            ->where('bodega', $request->bodega)
            ->where('id_variedad', $request->variedad)
            ->orderBy('fecha_registro')
            ->get();
        return view('adminlte.gestion.postco.bitacora_recepcion.partials.listado', [
            'listado' => $listado,
            'variedad' => $variedad,
            'saldo' => $saldo_inicial,
            'desde' => $desde,
            'hasta' => $hasta,
            'bodega' => $request->bodega == 'V' ? 'Ventas' : 'Produccion',
        ]);
    }
}
