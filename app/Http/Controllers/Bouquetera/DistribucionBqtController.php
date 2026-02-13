<?php

namespace yura\Http\Controllers\Bouquetera;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use yura\Http\Controllers\Controller;
use yura\Modelos\Indicadores4Semanas;
use yura\Modelos\Submenu;

class DistribucionBqtController extends Controller
{
    public function inicio(Request $request)
    {
        return view('adminlte.gestion.bouquetera.distribucion_bqt.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'plantas' => getPlantas(),
            'desde' => getSemanaByDate(opDiasFecha('-', 42, hoy())),
            'hasta' => getSemanaByDate(hoy()),
        ]);
    }

    public function listar_distribucion_bqt(Request $request)
    {
        $semanas = getSemanasByCodigos($request->desde, $request->hasta);
        if (count($semanas) > 0) {
            $desde = $semanas[0];
            $hasta = $semanas[count($semanas) - 1];
            $variedades = DB::table('bouquetera as b')
                ->join('planta as p', 'p.id_planta', '=', 'b.id_planta')
                ->join('configuracion_empresa as e', 'e.id_configuracion_empresa', '=', 'b.id_empresa')
                ->select('b.id_empresa', 'e.nombre as empresa', 'b.id_planta', 'p.nombre as pta_nombre')->distinct()
                ->where('b.id_empresa', '!=', -1)
                ->where('b.fecha', '>=', $desde->fecha_inicial)
                ->where('b.fecha', '<=', $hasta->fecha_final)
                ->orderBy('e.nombre')
                ->orderBy('p.nombre')
                ->get();
            $listado = [];
            foreach ($variedades as $var) {
                $valores = [];
                foreach ($semanas as $sem) {
                    $indicadores_4_semanas = Indicadores4Semanas::All()
                        ->where('semana', $sem->codigo)
                        ->where('id_empresa', $var->id_empresa)
                        ->first();
                    $query = DB::table('bouquetera as b')
                        ->select(DB::raw('sum(tallos) as tallos'),
                            DB::raw('sum(exportada) as exportada'),
                            DB::raw('sum(precio * exportada) as costo'))
                        ->where('b.id_empresa', $var->id_empresa)
                        ->where('b.id_planta', $var->id_planta)
                        ->where('b.fecha', '>=', $sem->fecha_inicial)
                        ->where('b.fecha', '<=', $sem->fecha_final)
                        ->get()[0];
                    array_push($valores, [
                        'tallos' => $query->tallos,
                        'exportada' => $query->exportada,
                        'costo' => $query->costo,
                        'indicadores_4_semanas' => $indicadores_4_semanas,
                    ]);
                }
                array_push($listado, [
                    'id_empresa' => $var->id_empresa,
                    'empresa' => $var->empresa,
                    'id_planta' => $var->id_planta,
                    'pta_nombre' => $var->pta_nombre,
                    'valores' => $valores,
                ]);
            }
            return view('adminlte.gestion.bouquetera.distribucion_bqt.partials.listado', [
                'semanas' => $semanas,
                'listado' => $listado,
            ]);
        }
        return '<div class="alert alert-info text-center">Las semanas están incorrectas</div>';
    }
}
