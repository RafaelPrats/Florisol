<?php

namespace yura\Http\Controllers\Propagacion;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use yura\Http\Controllers\Controller;
use yura\Modelos\Submenu;

class InventarioEnraizamientoController extends Controller
{
    public function inicio(Request $request)
    {
        $semana_desde = getSemanaByDate(opDiasFecha('-', 21, hoy()));
        $semana_hasta = getSemanaByDate(opDiasFecha('+', 42, hoy()));
        return view('adminlte.gestion.propagacion.inventario_enraizamiento.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'semana_desde' => $semana_desde,
            'semana_hasta' => $semana_hasta,
        ]);
    }

    public function listar_inventario_enraizamiento(Request $request)
    {
        $finca = getFincaActiva();
        $semanas = DB::table('semana')
            ->select('codigo', 'fecha_inicial', 'fecha_final')->distinct()
            ->where('estado', 1)
            ->where('codigo', '>=', $request->desde)
            ->where('codigo', '<=', $request->hasta)
            ->get();
        $contenedores = DB::table('detalle_enraizamiento_semanal as d')
            ->join('enraizamiento_semanal as e', 'e.id_enraizamiento_semanal', '=', 'd.id_enraizamiento_semanal')
            ->join('contenedor_propag as c', 'c.id_contenedor_propag', '=', 'd.id_contenedor_propag')
            ->select('d.id_contenedor_propag', 'c.nombre', 'c.cantidad')->distinct()
            ->where('c.estado', 1)
            ->where('e.semana_ini', '>=', $request->desde)
            ->where('e.semana_ini', '<=', $request->hasta)
            ->where('e.id_empresa', $finca)
            ->orderBy('c.nombre')
            ->get();
        $data = [];
        $semana_anterior = getSemanaByDate(opDiasFecha('-', 7, $semanas[0]->codigo));
        foreach ($contenedores as $c) {
            $valores = [];
            foreach ($semanas as $s) {
                $ingresos = DB::table('detalle_enraizamiento_semanal as d')
                    ->join('enraizamiento_semanal as e', 'e.id_enraizamiento_semanal', '=', 'd.id_enraizamiento_semanal')
                    ->select(DB::raw('sum(d.cantidad_siembra) as cantidad'))
                    ->where('d.id_contenedor_propag', $c->id_contenedor_propag)
                    ->where('e.semana_ini', $s->codigo)
                    ->where('e.id_empresa', $finca)
                    ->get()[0]->cantidad;
                $ingresos = $ingresos > 0 ? intVal($ingresos / $c->cantidad) : 0;
                $usando = DB::table('detalle_enraizamiento_semanal as d')
                    ->join('enraizamiento_semanal as e', 'e.id_enraizamiento_semanal', '=', 'd.id_enraizamiento_semanal')
                    ->select(DB::raw('sum(d.cantidad_siembra) as cantidad'))
                    ->where('d.id_contenedor_propag', $c->id_contenedor_propag)
                    ->where('e.semana_ini', '<=', $s->codigo)
                    ->where('e.semana_fin', '>=', $s->codigo)
                    ->where('e.id_empresa', $finca)
                    ->get()[0]->cantidad;
                $usando = $usando > 0 ? intVal($usando / $c->cantidad) : 0;
                $disponibles = DB::table('detalle_enraizamiento_semanal as d')
                    ->join('enraizamiento_semanal as e', 'e.id_enraizamiento_semanal', '=', 'd.id_enraizamiento_semanal')
                    ->select(DB::raw('sum(d.cantidad_siembra) as cantidad'))
                    ->where('d.id_contenedor_propag', $c->id_contenedor_propag)
                    ->where('e.semana_fin', $semana_anterior->codigo)
                    ->where('e.id_empresa', $finca)
                    ->get()[0]->cantidad;
                $disponibles = $disponibles > 0 ? intVal($disponibles / $c->cantidad) : 0;
                $semana_anterior = $s;

                array_push($valores, [
                    'ingresos' => $ingresos,
                    'usando' => $usando,
                    'disponibles' => $disponibles,
                ]);
            }
            array_push($data, [
                'contenedor' => $c,
                'valores' => $valores,
            ]);
        }

        return view('adminlte.gestion.propagacion.inventario_enraizamiento.partials.listado', [
            'semanas' => $semanas,
            'data' => $data,
        ]);
    }
}
