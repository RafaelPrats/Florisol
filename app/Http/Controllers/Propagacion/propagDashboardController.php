<?php

namespace yura\Http\Controllers\Propagacion;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use yura\Http\Controllers\Controller;
use yura\Modelos\Indicador;
use yura\Modelos\Planta;
use yura\Modelos\ResumenPropagacion;
use yura\Modelos\Submenu;

class propagDashboardController extends Controller
{
    public function inicio(Request $request)
    {
        $finca = getFincaActiva();
        $sem_ind_desde = getSemanaByDate(opDiasFecha('-', 28, date('Y-m-d')));
        $sem_ind_hasta = getSemanaByDate(opDiasFecha('-', 7, date('Y-m-d')));

        $requerimientos = DB::table('resumen_propagacion')
            ->select(DB::raw('sum(requerimientos) as cantidad'))
            ->where('semana', '=', $sem_ind_hasta->codigo)
            ->where('id_empresa', $finca)
            ->get()[0]->cantidad;
        $esqueje_x_planta = DB::table('resumen_propagacion')
            ->select(DB::raw('sum(esquejes_cosechados) as esquejes'), DB::raw('sum(plantas_sembradas) as plantas'))
            ->where('semana', '>=', $sem_ind_desde->codigo)
            ->where('semana', '<=', $sem_ind_hasta->codigo)
            //->where('esquejes_x_planta', '>', 0)
            ->where('id_empresa', $finca)
            ->get()[0];
        $esqueje_x_planta = $esqueje_x_planta->plantas > 0 ? round($esqueje_x_planta->esquejes / $esqueje_x_planta->plantas, 2) : 0;
        $porcentaje_enraizamiento = DB::table('propag_disponibilidad')
            ->select(DB::raw('sum(desecho) as desecho'), DB::raw('count(*) as cantidad'))
            ->where('semana', '>=', $sem_ind_desde->codigo)
            ->where('semana', '<=', $sem_ind_hasta->codigo)
            ->where('plantas_sembradas', '>', 0)
            ->where('id_empresa', $finca)
            ->get()[0];
        $porcentaje_enraizamiento = $porcentaje_enraizamiento->cantidad > 0 ? round(100 - ($porcentaje_enraizamiento->desecho / $porcentaje_enraizamiento->cantidad), 2) : 0;
        $costo_x_planta = getIndicadorByName('C12-' . $finca);
        $costo_x_planta = $costo_x_planta != '' ? $costo_x_planta->valor : 0;
        $plantas = Planta::where('estado', 1)->where('tipo', 'N')->orderBy('nombre')->get();
        return view('adminlte.crm.propagacion.dashboard.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'esqueje_x_planta' => $esqueje_x_planta,
            'porcentaje_enraizamiento' => $porcentaje_enraizamiento,
            'costo_x_planta' => $costo_x_planta,
            'requerimientos' => $requerimientos,
            'plantas' => $plantas,
        ]);
    }

    public function listar_graficas(Request $request)
    {
        $finca = getFincaActiva();
        $sem_desde = getSemanaByDate(opDiasFecha('-', $request->rango * 7, date('Y-m-d')));
        $sem_hasta = getSemanaByDate(opDiasFecha('-', 7, date('Y-m-d')));

        $query = DB::table('variedad as v')
            ->select('v.id_variedad')->distinct()
            ->where('estado', 1);
        if ($request->variedad != 'A')
            $query = $query->where('v.id_variedad', $request->variedad);
        elseif ($request->planta != '')
            $query = $query->where('v.id_planta', $request->planta);
        $query = $query->get();
        $ids_variedad = [];
        foreach ($query as $q)
            array_push($ids_variedad, $q->id_variedad);

        $resumen_propagacion = DB::table('resumen_propagacion')
            ->select(DB::raw('sum(esquejes_cosechados) as esquejes_cosechados'),
                DB::raw('sum(plantas_sembradas) as plantas_sembradas'),
                DB::raw('sum(costo_x_esqueje) as costo_x_esqueje'),
                DB::raw('sum(requerimientos) as requerimientos'), 'semana')
            ->where('semana', '>=', $sem_desde->codigo)
            ->where('semana', '<=', $sem_hasta->codigo)
            //->where('plantas_sembradas', '>', 0)
            ->whereIn('id_variedad', $ids_variedad)
            ->where('id_empresa', $finca)
            ->groupBy('semana')
            ->orderBy('semana')
            ->get();

        $porcentaje_enraizamiento = DB::table('propag_disponibilidad')
            ->select(DB::raw('sum(desecho) as cantidad'), 'semana')
            ->where('semana', '>=', $sem_desde->codigo)
            ->where('semana', '<=', $sem_hasta->codigo)
            //->where('plantas_sembradas', '>', 0)
            ->whereIn('id_variedad', $ids_variedad)
            ->where('id_empresa', $finca)
            ->groupBy('semana')
            ->orderBy('semana')
            ->get();
        $cant_validos_porc_enr = DB::table('propag_disponibilidad')
            ->select(DB::raw('count(*) as cantidad'), 'semana')
            ->where('semana', '>=', $sem_desde->codigo)
            ->where('semana', '<=', $sem_hasta->codigo)
            ->where('plantas_sembradas', '>', 0)
            ->whereIn('id_variedad', $ids_variedad)
            ->where('id_empresa', $finca)
            ->groupBy('semana')
            ->orderBy('semana')
            ->get();

        $costo_x_planta = DB::table('resumen_propagacion')
            ->select(DB::raw('sum(costo_x_planta) as cantidad'), 'semana')
            ->where('semana', '>=', $sem_desde->codigo)
            ->where('semana', '<=', $sem_hasta->codigo)
            ->where('costo_x_planta', '>', 0)
            ->whereIn('id_variedad', $ids_variedad)
            ->where('id_empresa', $finca)
            ->groupBy('semana')
            ->orderBy('semana')
            ->get();
        $cant_validos_cost_x_plta = DB::table('resumen_propagacion')
            ->select(DB::raw('count(*) as cantidad'), 'semana')
            ->where('semana', '>=', $sem_desde->codigo)
            ->where('semana', '<=', $sem_hasta->codigo)
            ->where('costo_x_planta', '>', 0)
            ->whereIn('id_variedad', $ids_variedad)
            ->where('id_empresa', $finca)
            ->groupBy('semana')
            ->orderBy('semana')
            ->get();

        return view('adminlte.crm.propagacion.dashboard.partials._graficas', [
            'resumen_propagacion' => $resumen_propagacion,
            'porcentaje_enraizamiento' => $porcentaje_enraizamiento,
            'cant_validos_porc_enr' => $cant_validos_porc_enr,
            'costo_x_planta' => $costo_x_planta,
            'cant_validos_cost_x_plta' => $cant_validos_cost_x_plta,
            'ids_variedad' => $ids_variedad,
        ]);
    }
}