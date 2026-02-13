<?php

namespace yura\Http\Controllers\Propagacion;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use yura\Http\Controllers\Controller;
use yura\Modelos\Semana;
use yura\Modelos\Submenu;

class ReporteEnraizamientoController extends Controller
{
    public function inicio(Request $request)
    {
        $semana_desde = getSemanaByDate(opDiasFecha('-', 28, hoy()));
        $semana_hasta = getSemanaByDate(hoy());
        return view('adminlte.crm.propagacion.enraizamiento.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'semana_desde' => $semana_desde,
            'semana_hasta' => $semana_hasta,
        ]);
    }

    public function listar_reporte_enraizamiento(Request $request)
    {
        $semanas = getSemanasByCodigos($request->desde, $request->hasta);
        $finca = getFincaActiva();
        $plantas = DB::table('enraizamiento_semanal as p')
            ->join('variedad as v', 'v.id_variedad', '=', 'p.id_variedad')
            ->select('v.id_planta')->distinct()
            ->where('p.id_empresa', $finca)
            ->where('v.estado', 1)
            ->where('p.cantidad_siembra', '>', 0)
            ->where('p.semana_ini', '>=', $request->desde)
            ->where('p.semana_ini', '<=', $request->hasta)
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
            $requerimientos = [];

            $variedades = DB::table('enraizamiento_semanal as p')
                ->join('variedad as v', 'v.id_variedad', '=', 'p.id_variedad')
                ->select('v.id_variedad')->distinct()
                ->where('p.id_empresa', $finca)
                ->where('v.estado', 1)
                ->where('v.id_planta', $pta->id_planta)
                ->where('p.cantidad_siembra', '>', 0)
                ->where('p.semana_ini', '>=', $request->desde)
                ->where('p.semana_ini', '<=', $request->hasta)
                ->get();

            foreach ($semanas as $pos_s => $sem) {
                $val = DB::table('enraizamiento_semanal as p')
                    ->join('variedad as v', 'v.id_variedad', '=', 'p.id_variedad')
                    ->select(DB::raw('sum(p.cantidad_siembra) as cantidad_siembra'))
                    ->where('p.id_empresa', $finca)
                    ->where('v.id_planta', $pta->id_planta)
                    ->where('p.semana_ini', $sem->codigo)
                    ->get()[0];

                $valores[] = $val;

                /* requerimientos a futuro */
                $req = 0;
                foreach ($variedades as $var) {
                    $enr = DB::table('enraizamiento_semanal as p')
                        ->where('id_empresa', $finca)
                        ->where('id_variedad', $var->id_variedad)
                        ->where('semana_ini', '<=', $sem->codigo)
                        ->where('cantidad_semanas', '>', 0)
                        ->orderBy('semana_ini', 'asc')
                        ->get()
                        ->last();
                    if ($enr != '') {
                        $semana_req = getSemanaByDate(opDiasFecha('+', ($enr->cantidad_semanas * 7), $sem->fecha_inicial));
                        $r = DB::table('propag_disponibilidad as p')
                            ->select(DB::raw('sum(p.requerimientos) as requerimientos'))
                            ->where('p.id_empresa', $finca)
                            ->where('p.id_variedad', $var->id_variedad)
                            ->where('p.semana', $semana_req->codigo)
                            ->get()[0]->requerimientos;
                        $req += $r != '' ? $r : 0;
                    }
                }
                $requerimientos[] = $req;
            }

            array_push($data, [
                'planta' => $pta,
                'valores' => $valores,
                'requerimientos' => $requerimientos,
            ]);
        }
        return view('adminlte.crm.propagacion.enraizamiento.partials.listado', [
            'semanas' => $semanas,
            'data' => $data,
        ]);
    }

    public function select_desglose_planta(Request $request)
    {
        $semanas = getSemanasByCodigos($request->desde, $request->hasta);

        $finca = getFincaActiva();
        $variedades = DB::table('enraizamiento_semanal as p')
            ->join('variedad as v', 'v.id_variedad', '=', 'p.id_variedad')
            ->select('v.id_variedad')->distinct()
            ->where('p.id_empresa', $finca)
            ->where('v.estado', 1)
            ->where('v.id_planta', $request->id_pta)
            ->where('p.cantidad_siembra', '>', 0)
            ->where('p.semana_ini', '>=', $request->desde)
            ->where('p.semana_ini', '<=', $request->hasta)
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
            $requerimientos = [];
            foreach ($semanas as $sem) {
                $val = DB::table('enraizamiento_semanal as p')
                    ->select('p.*')
                    ->where('p.id_empresa', $finca)
                    ->where('p.id_variedad', $var->id_variedad)
                    ->where('p.semana_ini', $sem->codigo)
                    ->get();

                $valores[] = count($val) > 0 ? $val[0] : '';

                /* requerimientos a futuro */
                $req = 0;
                $enr = DB::table('enraizamiento_semanal as p')
                    ->where('id_empresa', $finca)
                    ->where('id_variedad', $var->id_variedad)
                    ->where('semana_ini', '<=', $sem->codigo)
                    ->where('cantidad_semanas', '>', 0)
                    ->orderBy('semana_ini', 'asc')
                    ->get()
                    ->last();
                if ($enr != '') {
                    $semana_req = getSemanaByDate(opDiasFecha('+', ($enr->cantidad_semanas * 7), $sem->fecha_inicial));
                    $r = DB::table('propag_disponibilidad as p')
                        ->select(DB::raw('sum(p.requerimientos) as requerimientos'))
                        ->where('p.id_empresa', $finca)
                        ->where('p.id_variedad', $var->id_variedad)
                        ->where('p.semana', $semana_req->codigo)
                        ->get()[0]->requerimientos;
                    $req += $r != '' ? $r : 0;
                }
                $requerimientos[] = [
                    'valor' => $req,
                    'semana_req' => $semana_req->codigo,
                ];
            }

            array_push($data, [
                'variedad' => $var,
                'valores' => $valores,
                'requerimientos' => $requerimientos,
            ]);
        }

        return [
            'semanas' => $semanas,
            'data' => $data,
        ];
    }
}