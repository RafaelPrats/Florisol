<?php

namespace yura\Http\Controllers\Postcosecha;

use DB;
use Illuminate\Http\Request;
use yura\Http\Controllers\Controller;
use yura\Modelos\Despachador;
use yura\Modelos\Submenu;

class DespachosPreproduccionController extends Controller
{
    public function inicio(Request $request)
    {
        $clientes = DB::table('detalle_cliente as dc')
            ->join('cliente as c', 'c.id_cliente', '=', 'dc.id_cliente')
            ->select('dc.id_cliente', 'dc.nombre')->distinct()
            ->where('c.estado', 1)
            ->where('dc.estado', 1)
            ->orderBy('dc.nombre')
            ->get();
        $despachadores = Despachador::where('estado', 1)
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.postcocecha.despachos_preproduccion.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'clientes' => $clientes,
            'despachadores' => $despachadores,
        ]);
    }

    public function listar_reporte(Request $request)
    {
        if ($request->fecha < '2025-04-08') {
            $clientes = DB::table('detalle_cliente as dc')
                ->join('cliente as c', 'c.id_cliente', '=', 'dc.id_cliente')
                ->join('import_pedido as p', 'p.id_cliente', '=', 'c.id_cliente')
                ->join('detalle_import_pedido as dp', 'dp.id_import_pedido', '=', 'p.id_import_pedido')
                ->join('orden_trabajo as o', 'o.id_detalle_import_pedido', '=', 'dp.id_detalle_import_pedido')
                ->select('dc.id_cliente', 'dc.nombre')->distinct()
                ->where('c.estado', 1)
                ->where('dc.estado', 1)
                ->where('o.fecha', $request->fecha);
            if ($request->cliente != '')
                $clientes = $clientes->where('p.id_cliente', $request->cliente);
            if ($request->despachador != 'T')
                $clientes = $clientes->where('o.id_despachador', $request->despachador);
            $clientes = $clientes->orderBy('dc.nombre')
                ->get();

            $listado = [];
            foreach ($clientes as $c) {
                $ramos = DB::table('import_pedido as p')
                    ->join('detalle_import_pedido as dp', 'dp.id_import_pedido', '=', 'p.id_import_pedido')
                    ->join('variedad as v', 'v.id_variedad', '=', 'dp.id_variedad')
                    ->join('orden_trabajo as o', 'o.id_detalle_import_pedido', '=', 'dp.id_detalle_import_pedido')
                    ->select('dp.id_variedad', 'v.nombre', 'dp.longitud', DB::raw('sum(o.ramos) as ramos'))
                    ->where('o.fecha', $request->fecha)
                    ->where('p.id_cliente', $c->id_cliente);
                if ($request->despachador != 'T')
                    $ramos = $ramos->where('o.id_despachador', $request->despachador);
                $ramos = $ramos->orderBy('v.nombre')
                    ->groupBy('dp.id_variedad', 'v.nombre', 'dp.longitud')
                    ->get();
                $tallos = DB::table('import_pedido as p')
                    ->join('detalle_import_pedido as dp', 'dp.id_import_pedido', '=', 'p.id_import_pedido')
                    ->join('orden_trabajo as o', 'o.id_detalle_import_pedido', '=', 'dp.id_detalle_import_pedido')
                    ->join('detalle_orden_trabajo as do', 'do.id_orden_trabajo', '=', 'o.id_orden_trabajo')
                    ->join('variedad as v', 'v.id_variedad', '=', 'do.id_variedad')
                    ->select('do.id_variedad', 'v.nombre', 'dp.longitud', DB::raw('sum(do.tallos) as tallos'))
                    ->where('o.fecha', $request->fecha)
                    ->where('p.id_cliente', $c->id_cliente);
                if ($request->despachador != 'T')
                    $tallos = $tallos->where('o.id_despachador', $request->despachador);
                $tallos = $tallos
                    ->orderBy('v.nombre')
                    ->groupBy('do.id_variedad', 'v.nombre', 'dp.longitud')
                    ->get();
                $listado[] = [
                    'cliente' => $c,
                    'ramos' => $ramos,
                    'tallos' => $tallos,
                ];
            }
        } else {
            $clientes = DB::table('detalle_cliente as dc')
                ->join('cliente as c', 'c.id_cliente', '=', 'dc.id_cliente')
                ->join('ot_postco as o', 'o.id_cliente', '=', 'dc.id_cliente')
                ->select('dc.id_cliente', 'dc.nombre')->distinct()
                ->where('c.estado', 1)
                ->where('dc.estado', 1)
                ->where('o.fecha', $request->fecha);
            if ($request->cliente != '')
                $clientes = $clientes->where('o.id_cliente', $request->cliente);
            if ($request->despachador != 'T')
                $clientes = $clientes->where('o.id_despachador', $request->despachador);
            $clientes = $clientes->orderBy('dc.nombre')
                ->get();

            $listado = [];
            foreach ($clientes as $c) {
                $ramos = DB::table('postco as p')
                    ->join('variedad as v', 'v.id_variedad', '=', 'p.id_variedad')
                    ->join('ot_postco as o', 'o.id_postco', '=', 'p.id_postco')
                    ->select('p.id_variedad', 'v.nombre', 'p.longitud', DB::raw('sum(o.ramos) as ramos'))
                    ->where('o.fecha', $request->fecha)
                    ->where('o.id_cliente', $c->id_cliente);
                if ($request->despachador != 'T')
                    $ramos = $ramos->where('o.id_despachador', $request->despachador);
                $ramos = $ramos->orderBy('v.nombre')
                    ->groupBy('p.id_variedad', 'v.nombre', 'p.longitud')
                    ->get();
                $tallos = DB::table('postco as p')
                    ->join('ot_postco as o', 'o.id_postco', '=', 'p.id_postco')
                    ->join('detalle_ot_postco as do', 'do.id_ot_postco', '=', 'o.id_ot_postco')
                    ->join('variedad as v', 'v.id_variedad', '=', 'do.id_item')
                    ->select('do.id_item', 'v.nombre', 'p.longitud', DB::raw('sum(do.unidades * o.ramos) as tallos'))
                    ->where('o.fecha', $request->fecha)
                    ->where('o.id_cliente', $c->id_cliente);
                if ($request->despachador != 'T')
                    $tallos = $tallos->where('o.id_despachador', $request->despachador);
                $tallos = $tallos
                    ->orderBy('v.nombre')
                    ->groupBy('do.id_item', 'v.nombre', 'p.longitud')
                    ->get();
                $listado[] = [
                    'cliente' => $c,
                    'ramos' => $ramos,
                    'tallos' => $tallos,
                ];
            }
        }

        return view('adminlte.gestion.postcocecha.despachos_preproduccion.partials.listado', [
            'listado' => $listado,
        ]);
    }
}
