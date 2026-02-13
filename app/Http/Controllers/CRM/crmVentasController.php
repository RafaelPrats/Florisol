<?php

namespace yura\Http\Controllers\CRM;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use yura\Http\Controllers\Controller;
use yura\Modelos\Indicador;
use yura\Modelos\Pedido;
use yura\Modelos\Planta;
use yura\Modelos\ProyeccionVentaSemanalReal;
use yura\Modelos\ResumenVentaDiaria;
use yura\Modelos\Semana;
use yura\Modelos\Submenu;

class crmVentasController extends Controller
{
    public function inicio(Request $request)
    {
        /* ======= INDICADORES ======= */
        $semana_desde = getSemanaByDate(opDiasFecha('-', 28, hoy()));
        $semana_hasta = getSemanaByDate(opDiasFecha('-', 7, hoy()));
        $indicadores = DB::table('resumen_fechas as h')
            ->select(
                'h.semana',
                DB::raw('sum(h.tallos_armados) as armados'),
                DB::raw('sum(h.tallos_comprados) as comprados'),
                DB::raw('sum(h.tallos_desechados) as desechados'),
                DB::raw('sum(h.tallos_recibidos) as recibidos'),
            )
            ->where('h.semana', '>=', $semana_desde->codigo)
            ->where('h.semana', '<=', $semana_hasta->codigo)
            ->groupBy('h.semana')
            ->orderBy('h.semana')
            ->get();

        /* ======= GRAFICAS ======= */
        $annos = DB::table('resumen_fechas')
            ->select('anno')->distinct()
            ->orderBy('anno', 'desc')
            ->get();
        $plantas = Planta::join('variedad as v', 'v.id_planta', '=', 'planta.id_planta')
            ->select('planta.*')->distinct()
            ->where('planta.estado', 1)
            ->where('v.estado', 1)
            ->where('v.receta', 0)
            ->orderBy('planta.nombre')
            ->get();
        $clientes = DB::table('resumen_agrogana as h')
            ->join('cliente as c', 'c.id_cliente', '=', 'h.id_cliente')
            ->join('detalle_cliente as dc', 'dc.id_cliente', '=', 'h.id_cliente')
            ->select('h.id_cliente', 'dc.nombre')->distinct()
            ->where('c.estado', 1)
            ->where('dc.estado', 1)
            ->orderBy('h.anno')
            ->get();
        return view('adminlte.crm.ventas.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'annos' => $annos,
            'indicadores' => $indicadores,
            'plantas' => $plantas,
            'clientes' => $clientes,
        ]);
    }

    public function listar_graficas(Request $request)
    {
        if ($request->annos == '') {
            $view = 'graficas_rango';

            if ($request->rango == 'D') {   // diario
                $labels = DB::table('resumen_fechas')
                    ->select('fecha')->distinct()
                    ->where('fecha', '>=', $request->desde)
                    ->where('fecha', '<=', $request->hasta)
                    ->orderBy('fecha')
                    ->get()->pluck('fecha')->toArray();
            } else if ($request->rango == 'M') {   // mensual
                $labels = DB::table('resumen_fechas')
                    ->select(DB::raw('DISTINCT DATE_FORMAT(fecha, "%Y-%m") AS mes'))
                    ->where('fecha', '>=', $request->desde)
                    ->where('fecha', '<=', $request->hasta)
                    ->orderBy('fecha')
                    ->groupBy('mes', 'fecha')
                    ->get()
                    ->pluck('mes')
                    ->toArray();
            } else {    // semanal
                $labels = DB::table('resumen_fechas')
                    ->select('semana')->distinct()
                    ->where('fecha', '>=', $request->desde)
                    ->where('fecha', '<=', $request->hasta)
                    ->orderBy('semana')
                    ->get()->pluck('semana')->toArray();
            }
            $data = [];
            foreach ($labels as $l) {
                if ($request->cliente != 'T') {   // por X cliente
                    $query = DB::table('resumen_agrogana as h')
                        ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                        ->select(
                            DB::raw('sum(h.tallos_armados) as armados'),
                            DB::raw('sum(0) as comprados'),
                            DB::raw('sum(0) as desechados'),
                            DB::raw('sum(0) as recibidos'),
                        )
                        ->where('h.id_cliente', $request->cliente);
                    if ($request->rango == 'D') { // diario
                        $query = $query->where('h.fecha', $l);
                    } else if ($request->rango == 'M') { // mensual
                        $query = $query->whereMonth('h.fecha', '=', date('m', strtotime($l)))
                            ->whereYear('h.fecha', '=', date('Y', strtotime($l)));
                    } else { // semanal
                        $query = $query->where('h.semana', $l);
                    }
                    if ($request->planta != 'T')
                        $query = $query->where('v.id_planta', $request->planta);
                    $query = $query->get()[0];
                } else {    // solo flores
                    $query = DB::table('resumen_fechas as h')
                        ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                        ->select(
                            DB::raw('sum(h.tallos_armados) as armados'),
                            DB::raw('sum(h.tallos_comprados) as comprados'),
                            DB::raw('sum(h.tallos_desechados) as desechados'),
                            DB::raw('sum(h.tallos_recibidos) as recibidos'),
                        );
                    if ($request->rango == 'D') { // diario
                        $query = $query->where('h.fecha', $l);
                    } else if ($request->rango == 'M') { // mensual
                        $query = $query->whereMonth('h.fecha', '=', date('m', strtotime($l)))
                            ->whereYear('h.fecha', '=', date('Y', strtotime($l)));
                    } else { // semanal
                        $query = $query->where('h.semana', $l);
                    }
                    if ($request->planta != 'T')
                        $query = $query->where('v.id_planta', $request->planta);
                    $query = $query->get()[0];
                }

                $data[] = $query;
            }
            if ($request->tipo_grafica == 'line') {
                $tipo_grafica = 'line';
                $fill_grafica = 'false';
            } else if ($request->tipo_grafica == 'area') {
                $tipo_grafica = 'line';
                $fill_grafica = 'true';
            } else {
                $tipo_grafica = 'bar';
                $fill_grafica = 'true';
            }
            $datos = [
                'labels' => $labels,
                'data' => $data,
                'tipo_grafica' => $tipo_grafica,
                'fill_grafica' => $fill_grafica,
            ];
        } else {
            $view = 'graficas_annos';
            $annos = explode(' - ', $request->annos);

            en_desarrollo();
        }

        return view('adminlte.crm.ventas.partials.' . $view, $datos);
    }

    public function listar_ranking(Request $request)
    {
        if ($request->tipo_ranking == 'C') {  // clientes
            $query = DB::table('resumen_agrogana as h')
                ->join('detalle_cliente as c', 'c.id_cliente', '=', 'h.id_cliente')
                ->select(
                    'h.id_cliente',
                    'c.nombre',
                    DB::raw('sum(h.tallos_armados) as armados'),
                )
                ->where('c.estado', 1)
                ->where('h.fecha', '>=', $request->desde)
                ->where('h.fecha', '<=', $request->hasta)
                ->groupBy(
                    'h.id_cliente',
                    'c.nombre'
                )
                ->orderBy('armados', 'desc')
                ->limit(4)->get();
        } else {    // flores
            $query = DB::table('resumen_fechas as h')
                ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                ->select(
                    'v.id_planta',
                    'p.nombre',
                    DB::raw('sum(h.tallos_armados) as armados'),
                    DB::raw('sum(h.tallos_comprados) as comprados'),
                    DB::raw('sum(h.tallos_desechados) as desechados'),
                    DB::raw('sum(h.tallos_recibidos) as recibidos'),
                )
                ->where('h.fecha', '>=', $request->desde)
                ->where('h.fecha', '<=', $request->hasta)
                ->groupBy(
                    'v.id_planta',
                    'p.nombre'
                );
            if ($request->criterio_ranking == 'A')   // Armados
                $query = $query->orderBy('armados', 'desc');
            if ($request->criterio_ranking == 'C')   // Comprados
                $query = $query->orderBy('comprados', 'desc');
            if ($request->criterio_ranking == 'D')   // Desechados
                $query = $query->orderBy('desechados', 'desc');
            if ($request->criterio_ranking == 'R')   // Recibidos
                $query = $query->orderBy('recibidos', 'desc');
            $query = $query->limit(4)->get();
        }
        return view('adminlte.crm.ventas.partials.listar_ranking', [
            'query' => $query,
            'criterio' => $request->criterio_ranking,
        ]);
    }
}
