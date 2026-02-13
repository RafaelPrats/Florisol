<?php

namespace yura\Http\Controllers\Propagacion;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use yura\Http\Controllers\Controller;
use yura\Modelos\PropagDisponibilidad;
use yura\Modelos\Submenu;

class IngresoDisponibilidadController extends Controller
{
    public function inicio(Request $request)
    {
        $semana_desde = getSemanaByDate(opDiasFecha('-', 7, hoy()));
        $semana_hasta = getSemanaByDate(opDiasFecha('+', 42, hoy()));
        return view('adminlte.gestion.propagacion.ingreso_disponibilidad.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'semana_desde' => $semana_desde,
            'semana_hasta' => $semana_hasta,
        ]);
    }

    public function listar_ingreso_disponibilidad(Request $request)
    {
        $semanas = DB::table('semana')
            ->select('codigo', 'fecha_inicial', 'fecha_final')->distinct()
            ->where('codigo', '>=', $request->desde)
            ->where('codigo', '<=', $request->hasta)
            ->orderBy('codigo')
            ->get();

        $finca = getFincaActiva();
        $desde = $semanas[0];
        $hasta = $semanas[count($semanas) - 1];
        $plantas = DB::table('propag_disponibilidad as p')
            ->join('variedad as v', 'v.id_variedad', '=', 'p.id_variedad')
            ->select('v.id_planta')->distinct()
            ->where('p.id_empresa', $finca)
            ->where('v.estado', 1)
            ->Where(function ($q) {
                $q->Where('p.plantas_sembradas', '!=', 0)
                    ->orWhere('p.requerimientos', '!=', 0);
            })
            ->where('p.semana', '>=', $desde->codigo)
            ->where('p.semana', '<=', $hasta->codigo)
            ->get();
        $plantas_query = [];
        foreach ($plantas as $v)
            array_push($plantas_query, $v->id_planta);

        $plantas = DB::table('planta')
            ->select('nombre', 'id_planta')->distinct()
            ->where('estado', 1)
            ->whereIn('id_planta', $plantas_query)
            ->orderBy('nombre', 'asc')
            ->get();

        $data = [];
        foreach ($plantas as $pos => $pta) {
            $valores = [];
            foreach ($semanas as $sem) {
                $val = DB::table('propag_disponibilidad as p')
                    ->join('variedad as v', 'v.id_variedad', '=', 'p.id_variedad')
                    ->select(DB::raw('sum(p.plantas_sembradas) as plantas_disponibles'), DB::raw('sum(p.requerimientos) as requerimientos'))
                    ->where('p.id_empresa', $finca)
                    ->where('v.id_planta', $pta->id_planta)
                    ->where('p.semana', $sem->codigo)
                    ->get()[0];

                $valores[] = $val;
            }

            array_push($data, [
                'planta' => $pta,
                'valores' => $valores,
            ]);
        }

        return view('adminlte.gestion.propagacion.ingreso_disponibilidad.partials.listado', [
            'semanas' => $semanas,
            'data' => $data,
        ]);
    }

    public function select_desglose_planta(Request $request)
    {
        $semanas = DB::table('semana')
            ->select('codigo', 'fecha_inicial', 'fecha_final')->distinct()
            ->where('codigo', '>=', $request->desde)
            ->where('codigo', '<=', $request->hasta)
            ->orderBy('codigo')
            ->get();

        $finca = getFincaActiva();
        $desde = $semanas[0];
        $hasta = $semanas[count($semanas) - 1];
        $variedades = DB::table('propag_disponibilidad as p')
            ->join('variedad as v', 'v.id_variedad', '=', 'p.id_variedad')
            ->select('v.id_variedad')->distinct()
            ->where('p.id_empresa', $finca)
            ->where('v.estado', 1)
            ->where('v.id_planta', $request->id_pta)
            ->Where(function ($q) {
                $q->Where('p.plantas_sembradas', '!=', 0)
                    ->orWhere('p.requerimientos', '!=', 0);
            })
            ->where('p.semana', '>=', $desde->codigo)
            ->where('p.semana', '<=', $hasta->codigo)
            ->get();
        $variedades_query = [];
        foreach ($variedades as $v)
            array_push($variedades_query, $v->id_variedad);

        $variedades = DB::table('variedad')
            ->select('nombre', 'id_variedad')->distinct()
            ->where('estado', 1)
            ->whereIn('id_variedad', $variedades_query)
            ->orderBy('nombre', 'asc')
            ->get();

        $data = [];
        foreach ($variedades as $pos => $var) {
            $valores = [];
            foreach ($semanas as $sem) {
                $val = DB::table('propag_disponibilidad as p')
                    ->select('p.plantas_sembradas as plantas_disponibles', 'p.requerimientos', 'p.destino_plantas_sembradas')
                    ->where('p.id_empresa', $finca)
                    ->where('p.id_variedad', $var->id_variedad)
                    ->where('p.semana', $sem->codigo)
                    ->get()[0];

                $valores[] = $val;
            }

            array_push($data, [
                'variedad' => $var,
                'valores' => $valores,
            ]);
        }

        return [
            'semanas' => $semanas,
            'data' => $data,
        ];
    }

    public function update_requerimiento(Request $request)
    {
        $finca = getFincaActiva();
        $model = PropagDisponibilidad::All()
            ->where('id_variedad', $request->variedad)
            ->where('semana', $request->semana)
            ->where('id_empresa', $finca)
            ->first();
        $model->requerimientos = $request->valor;
        $model->save();
        return [
            'success' => true,
            'mensaje' => '',
        ];
    }
}
