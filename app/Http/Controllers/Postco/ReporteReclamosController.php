<?php

namespace yura\Http\Controllers\Postco;

use DB;
use Illuminate\Http\Request;
use yura\Http\Controllers\Controller;
use yura\Modelos\Submenu;

class ReporteReclamosController extends Controller
{
    public function inicio(Request $request)
    {
        $motivos = DB::table('motivo_reclamo')
            ->where('estado', 1)
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.postco.reporte_reclamos.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'motivos' => $motivos,
            'desde' => opDiasFecha('-', 7, hoy()),
            'hasta' => opDiasFecha('-', 1, hoy()),
        ]);
    }

    public function listar_reporte(Request $request)
    {
        if ($request->tipo == 'V') {    // reporte por variedades
            $variedades = DB::table('ot_reclamo as r')
                ->join('ot_postco as ot', 'ot.id_ot_postco', '=', 'r.id_ot_postco')
                ->join('postco as p', 'p.id_postco', '=', 'ot.id_postco')
                ->join('variedad as v', 'v.id_variedad', '=', 'p.id_variedad')
                ->select('p.id_variedad', 'v.nombre')->distinct()
                ->where('v.estado', 1)
                ->where('p.fecha', '>=', $request->desde)
                ->where('p.fecha', '<=', $request->hasta);
            if ($request->motivo != '')
                $variedades = $variedades->where('r.id_motivo_reclamo', $request->motivo);
            $variedades = $variedades->orderBy('v.nombre')
                ->get();

            $fechas = DB::table('ot_reclamo as r')
                ->join('ot_postco as ot', 'ot.id_ot_postco', '=', 'r.id_ot_postco')
                ->join('postco as p', 'p.id_postco', '=', 'ot.id_postco')
                ->select('p.fecha')->distinct()
                ->where('p.fecha', '>=', $request->desde)
                ->where('p.fecha', '<=', $request->hasta);
            if ($request->motivo != '')
                $fechas = $fechas->where('r.id_motivo_reclamo', $request->motivo);
            $fechas = $fechas->orderBy('p.fecha')
                ->get()->pluck('fecha')->toArray();

            $listado = [];
            $total_reclamos = 0;
            $total_armados = 0;
            foreach ($variedades as $var) {
                $valores = [];
                $armados = DB::table('postco as p')
                    ->select(DB::raw('sum(p.armados) as cantidad'))
                    ->where('p.fecha', '>=', $request->desde)
                    ->where('p.fecha', '<=', $request->hasta)
                    ->where('p.id_variedad', $var->id_variedad)
                    ->get()[0]->cantidad;
                foreach ($fechas as $f) {
                    $cantidad = DB::table('ot_reclamo as r')
                        ->join('ot_postco as ot', 'ot.id_ot_postco', '=', 'r.id_ot_postco')
                        ->join('postco as p', 'p.id_postco', '=', 'ot.id_postco')
                        ->select(DB::raw('sum(r.cantidad) as cantidad'))
                        ->where('p.fecha', $f)
                        ->where('p.id_variedad', $var->id_variedad);
                    if ($request->motivo != '')
                        $cantidad = $cantidad->where('r.id_motivo_reclamo', $request->motivo);
                    $cantidad = $cantidad->get()[0]->cantidad;

                    $valores[] = $cantidad;
                    $total_reclamos += $cantidad;
                }
                $total_armados += $armados;
                $total_ramos_ot = DB::table('ot_postco as o')
                    ->join('postco as p', 'p.id_postco', '=', 'o.id_postco')
                    ->select(DB::raw('sum(o.ramos) as cantidad'))
                    ->where('p.fecha', '>=', $request->desde)
                    ->where('p.fecha', '<=', $request->hasta)
                    ->where('p.id_variedad', $var->id_variedad)
                    ->get()[0]->cantidad;
                $listado[] = [
                    'label' => $var,
                    'valores' => $valores,
                    'total_ramos_ot' => $total_ramos_ot,
                    'armados' => $armados,
                ];
            }

            return view('adminlte.gestion.postco.reporte_reclamos.partials.listado', [
                'listado' => $listado,
                'fechas' => $fechas,
                'total_reclamos' => $total_reclamos,
                'total_armados' => $total_armados,
                'criterio' => 'RECETAS',
            ]);
        } elseif ($request->tipo == 'M') {    // reporte por motivos
            $motivos = DB::table('ot_reclamo as r')
                ->join('ot_postco as ot', 'ot.id_ot_postco', '=', 'r.id_ot_postco')
                ->join('postco as p', 'p.id_postco', '=', 'ot.id_postco')
                ->join('motivo_reclamo as m', 'm.id_motivo_reclamo', '=', 'r.id_motivo_reclamo')
                ->select('r.id_motivo_reclamo', 'm.nombre')->distinct()
                ->where('m.estado', 1)
                ->where('p.fecha', '>=', $request->desde)
                ->where('p.fecha', '<=', $request->hasta);
            if ($request->motivo != '')
                $motivos = $motivos->where('r.id_motivo_reclamo', $request->motivo);
            $motivos = $motivos->orderBy('m.nombre')
                ->get();

            $fechas = DB::table('ot_reclamo as r')
                ->join('ot_postco as ot', 'ot.id_ot_postco', '=', 'r.id_ot_postco')
                ->join('postco as p', 'p.id_postco', '=', 'ot.id_postco')
                ->select('p.fecha')->distinct()
                ->where('p.fecha', '>=', $request->desde)
                ->where('p.fecha', '<=', $request->hasta);
            if ($request->motivo != '')
                $fechas = $fechas->where('r.id_motivo_reclamo', $request->motivo);
            $fechas = $fechas->orderBy('p.fecha')
                ->get()->pluck('fecha')->toArray();

            $listado = [];
            $total_reclamos = 0;
            foreach ($motivos as $item) {
                $valores = [];
                foreach ($fechas as $f) {
                    $cantidad = DB::table('ot_reclamo as r')
                        ->join('ot_postco as ot', 'ot.id_ot_postco', '=', 'r.id_ot_postco')
                        ->join('postco as p', 'p.id_postco', '=', 'ot.id_postco')
                        ->select(DB::raw('sum(r.cantidad) as cantidad'))
                        ->where('p.fecha', $f)
                        ->where('r.id_motivo_reclamo', $item->id_motivo_reclamo)
                        ->get()[0]->cantidad;

                    $valores[] = $cantidad;
                    $total_reclamos += $cantidad;
                }
                $total_ramos_ot = DB::table('ot_reclamo as r')
                    ->join('ot_postco as ot', 'ot.id_ot_postco', '=', 'r.id_ot_postco')
                    ->join('postco as p', 'p.id_postco', '=', 'ot.id_postco')
                    ->select(DB::raw('sum(ot.ramos) as cantidad'))
                    ->where('p.fecha', '>=', $request->desde)
                    ->where('p.fecha', '<=', $request->hasta)
                    ->where('r.id_motivo_reclamo', $item->id_motivo_reclamo)
                    ->get()[0]->cantidad;
                $listado[] = [
                    'label' => $item,
                    'valores' => $valores,
                    'total_ramos_ot' => $total_ramos_ot,
                ];
            }

            return view('adminlte.gestion.postco.reporte_reclamos.partials.listado', [
                'listado' => $listado,
                'fechas' => $fechas,
                'total_reclamos' => $total_reclamos,
                'criterio' => 'MOTIVOS',
            ]);
        } elseif ($request->tipo == 'C') {    // reporte por clientes
            $clientes = DB::table('ot_reclamo as r')
                ->join('ot_postco as ot', 'ot.id_ot_postco', '=', 'r.id_ot_postco')
                ->join('postco as p', 'p.id_postco', '=', 'ot.id_postco')
                ->join('detalle_cliente as dc', 'dc.id_cliente', '=', 'ot.id_cliente')
                ->select('ot.id_cliente', 'dc.nombre')->distinct()
                ->where('dc.estado', 1)
                ->where('p.fecha', '>=', $request->desde)
                ->where('p.fecha', '<=', $request->hasta);
            if ($request->motivo != '')
                $clientes = $clientes->where('r.id_motivo_reclamo', $request->motivo);
            $clientes = $clientes->orderBy('dc.nombre')
                ->get();

            $fechas = DB::table('ot_reclamo as r')
                ->join('ot_postco as ot', 'ot.id_ot_postco', '=', 'r.id_ot_postco')
                ->join('postco as p', 'p.id_postco', '=', 'ot.id_postco')
                ->select('p.fecha')->distinct()
                ->where('p.fecha', '>=', $request->desde)
                ->where('p.fecha', '<=', $request->hasta);
            if ($request->motivo != '')
                $fechas = $fechas->where('r.id_motivo_reclamo', $request->motivo);
            $fechas = $fechas->orderBy('p.fecha')
                ->get()->pluck('fecha')->toArray();

            $listado = [];
            $total_reclamos = 0;
            foreach ($clientes as $item) {
                $valores = [];
                foreach ($fechas as $f) {
                    $cantidad = DB::table('ot_reclamo as r')
                        ->join('ot_postco as ot', 'ot.id_ot_postco', '=', 'r.id_ot_postco')
                        ->join('postco as p', 'p.id_postco', '=', 'ot.id_postco')
                        ->select(DB::raw('sum(r.cantidad) as cantidad'))
                        ->where('p.fecha', $f)
                        ->where('ot.id_cliente', $item->id_cliente);
                    if ($request->motivo != '')
                        $cantidad = $cantidad->where('r.id_motivo_reclamo', $request->motivo);
                    $cantidad = $cantidad->get()[0]->cantidad;

                    $valores[] = $cantidad;
                    $total_reclamos += $cantidad;
                }
                $total_ramos_ot = DB::table('ot_postco as ot')
                    ->join('postco as p', 'p.id_postco', '=', 'ot.id_postco')
                    ->select(DB::raw('sum(ot.ramos) as cantidad'))
                    ->where('p.fecha', '>=', $request->desde)
                    ->where('p.fecha', '<=', $request->hasta)
                    ->where('ot.id_cliente', $item->id_cliente)
                    ->get()[0]->cantidad;
                $listado[] = [
                    'label' => $item,
                    'valores' => $valores,
                    'total_ramos_ot' => $total_ramos_ot,
                ];
            }

            return view('adminlte.gestion.postco.reporte_reclamos.partials.listado', [
                'listado' => $listado,
                'fechas' => $fechas,
                'total_reclamos' => $total_reclamos,
                'criterio' => 'CLIENTES',
            ]);
        } elseif ($request->tipo == 'L') {    // reporte por linea de produccion
            $lineas = DB::table('ot_reclamo as r')
                ->join('ot_postco as ot', 'ot.id_ot_postco', '=', 'r.id_ot_postco')
                ->join('postco as p', 'p.id_postco', '=', 'ot.id_postco')
                ->join('despachador as d', 'd.id_despachador', '=', 'ot.id_despachador')
                ->select('ot.id_despachador', 'd.nombre')->distinct()
                ->where('d.estado', 1)
                ->where('p.fecha', '>=', $request->desde)
                ->where('p.fecha', '<=', $request->hasta);
            if ($request->motivo != '')
                $lineas = $lineas->where('r.id_motivo_reclamo', $request->motivo);
            $lineas = $lineas->orderBy('d.nombre')
                ->get();

            $fechas = DB::table('ot_reclamo as r')
                ->join('ot_postco as ot', 'ot.id_ot_postco', '=', 'r.id_ot_postco')
                ->join('postco as p', 'p.id_postco', '=', 'ot.id_postco')
                ->select('p.fecha')->distinct()
                ->where('p.fecha', '>=', $request->desde)
                ->where('p.fecha', '<=', $request->hasta);
            if ($request->motivo != '')
                $fechas = $fechas->where('r.id_motivo_reclamo', $request->motivo);
            $fechas = $fechas->orderBy('p.fecha')
                ->get()->pluck('fecha')->toArray();

            $listado = [];
            $total_reclamos = 0;
            foreach ($lineas as $item) {
                $valores = [];
                foreach ($fechas as $f) {
                    $cantidad = DB::table('ot_reclamo as r')
                        ->join('ot_postco as ot', 'ot.id_ot_postco', '=', 'r.id_ot_postco')
                        ->join('postco as p', 'p.id_postco', '=', 'ot.id_postco')
                        ->select(DB::raw('sum(r.cantidad) as cantidad'))
                        ->where('p.fecha', $f)
                        ->where('ot.id_despachador', $item->id_despachador);
                    if ($request->motivo != '')
                        $cantidad = $cantidad->where('r.id_motivo_reclamo', $request->motivo);
                    $cantidad = $cantidad->get()[0]->cantidad;

                    $valores[] = $cantidad;
                    $total_reclamos += $cantidad;
                }
                $total_ramos_ot = DB::table('ot_postco as ot')
                    ->join('postco as p', 'p.id_postco', '=', 'ot.id_postco')
                    ->select(DB::raw('sum(ot.ramos) as cantidad'))
                    ->where('p.fecha', '>=', $request->desde)
                    ->where('p.fecha', '<=', $request->hasta)
                    ->where('ot.id_despachador', $item->id_despachador)
                    ->get()[0]->cantidad;
                $listado[] = [
                    'label' => $item,
                    'valores' => $valores,
                    'total_ramos_ot' => $total_ramos_ot,
                ];
            }

            return view('adminlte.gestion.postco.reporte_reclamos.partials.listado', [
                'listado' => $listado,
                'fechas' => $fechas,
                'total_reclamos' => $total_reclamos,
                'criterio' => 'RESPONSABLE',
            ]);
        }
    }
}
