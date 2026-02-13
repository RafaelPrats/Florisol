<?php

namespace yura\Http\Controllers\Proyecciones;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use yura\Http\Controllers\Controller;
use yura\Modelos\Planta;
use yura\Modelos\Submenu;
use yura\Modelos\Variedad;

class FenogramaNoPerennesController extends Controller
{
    public function inicio(Request $request)
    {
        $semana_pasada = getSemanaByDate(opDiasFecha('-', 7, date('Y-m-d')));
        $plantas = Planta::All()
            ->where('estado', 1)
            ->where('tipo', 'N')
            ->where('tiene_ciclos', 0)
            ->sortBy('nombre');
        return view('adminlte.gestion.proyecciones.fenograma_no_perennes.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'semana_pasada' => $semana_pasada,
            'plantas' => $plantas,
        ]);
    }

    public function listar_fenograma(Request $request)
    {
        $finca = getFincaActiva();
        if ($request->planta != 'T') {  // una
            $variedades = Variedad::where('estado', 1);
            if ($request->variedad != 'T')
                $variedades = $variedades->where('id_variedad', $request->variedad);
            elseif ($request->planta != '')
                $variedades = $variedades->where('id_planta', $request->planta);
            $variedades = $variedades->orderBy('nombre')->get();

            $listado = [];
            foreach ($variedades as $var) {
                $proy = DB::table('proy_no_perennes as proy')
                    ->join('semana as s', 's.id_semana', '=', 'proy.id_semana')
                    ->select(DB::raw('sum(proy.area_produccion) as area_produccion'),
                        DB::raw('sum(proy.area_semana) as area_semana'),
                        DB::raw('sum(proy.proyectados) as proyectados'))
                    ->where('s.codigo', $request->semana)
                    ->where('proy.id_empresa', $finca)
                    ->where('s.id_variedad', $var->id_variedad)
                    ->get()[0];
                $proy_acum = DB::table('proy_no_perennes as proy')
                    ->join('semana as s', 's.id_semana', '=', 'proy.id_semana')
                    ->select(DB::raw('sum(proy.proyectados) as proyectados'))
                    ->where('s.codigo', '>=', substr($request->semana, 0, 2) . '01')
                    ->where('s.codigo', '<=', $request->semana)
                    ->where('proy.id_empresa', $finca)
                    ->where('s.id_variedad', $var->id_variedad)
                    ->get()[0];
                $cos = DB::table('resumen_total_semanal_exportcalas as p')
                    ->select(DB::raw('sum(p.tallos_cosechados) as tallos_cosechados'))
                    ->where('p.id_variedad', $var->id_variedad)
                    ->where('p.id_empresa', $finca)
                    ->where('p.semana', $request->semana)
                    ->get()[0];
                $cos_acum = DB::table('resumen_total_semanal_exportcalas as p')
                    ->select(DB::raw('sum(p.tallos_cosechados) as tallos_cosechados'))
                    ->where('p.id_variedad', $var->id_variedad)
                    ->where('p.id_empresa', $finca)
                    ->where('p.semana', '>=', substr($request->semana, 0, 2) . '01')
                    ->where('p.semana', '<=', $request->semana)
                    ->get()[0];
                if ($proy_acum->proyectados > 0 || $cos_acum->tallos_cosechados > 0)
                    array_push($listado, [
                        'variedad' => $var,
                        'area_produccion' => $proy->area_produccion,
                        'area_semana' => $proy->area_semana,
                        'proyectados' => $proy->proyectados,
                        'proyectados_acum' => $proy_acum->proyectados,
                        'tallos_cosechados' => $cos->tallos_cosechados,
                        'tallos_cosechados_acum' => $cos_acum->tallos_cosechados,
                    ]);
            }
        } else {
            $plantas = Planta::where('estado', 1)->where('tipo', 'N')
                ->orderBy('nombre')->get();

            $listado = [];
            foreach ($plantas as $pta) {
                $proy = DB::table('proy_no_perennes as proy')
                    ->join('semana as s', 's.id_semana', '=', 'proy.id_semana')
                    ->join('variedad as v', 'v.id_variedad', '=', 's.id_variedad')
                    ->select(DB::raw('sum(proy.area_produccion) as area_produccion'),
                        DB::raw('sum(proy.area_semana) as area_semana'),
                        DB::raw('sum(proy.proyectados) as proyectados'))
                    ->where('s.codigo', $request->semana)
                    ->where('proy.id_empresa', $finca)
                    ->where('v.id_planta', $pta->id_planta)
                    ->get()[0];
                $proy_acum = DB::table('proy_no_perennes as proy')
                    ->join('semana as s', 's.id_semana', '=', 'proy.id_semana')
                    ->join('variedad as v', 'v.id_variedad', '=', 's.id_variedad')
                    ->select(DB::raw('sum(proy.proyectados) as proyectados'))
                    ->where('s.codigo', '>=', substr($request->semana, 0, 2) . '01')
                    ->where('s.codigo', '<=', $request->semana)
                    ->where('proy.id_empresa', $finca)
                    ->where('v.id_planta', $pta->id_planta)
                    ->get()[0];
                $cos = DB::table('resumen_total_semanal_exportcalas as p')
                    ->join('variedad as v', 'v.id_variedad', '=', 'p.id_variedad')
                    ->select(DB::raw('sum(p.tallos_cosechados) as tallos_cosechados'))
                    ->where('v.id_planta', $pta->id_planta)
                    ->where('p.id_empresa', $finca)
                    ->where('p.semana', $request->semana)
                    ->get()[0];
                $cos_acum = DB::table('resumen_total_semanal_exportcalas as p')
                    ->join('variedad as v', 'v.id_variedad', '=', 'p.id_variedad')
                    ->select(DB::raw('sum(p.tallos_cosechados) as tallos_cosechados'))
                    ->where('v.id_planta', $pta->id_planta)
                    ->where('p.id_empresa', $finca)
                    ->where('p.semana', '>=', substr($request->semana, 0, 2) . '01')
                    ->where('p.semana', '<=', $request->semana)
                    ->get()[0];
                if ($proy_acum->proyectados > 0 || $cos_acum->tallos_cosechados > 0)
                    array_push($listado, [
                        'variedad' => $pta,
                        'area_produccion' => $proy->area_produccion,
                        'area_semana' => $proy->area_semana,
                        'proyectados' => $proy->proyectados,
                        'proyectados_acum' => $proy_acum->proyectados,
                        'tallos_cosechados' => $cos->tallos_cosechados,
                        'tallos_cosechados_acum' => $cos_acum->tallos_cosechados,
                    ]);
            }
        }
        return view('adminlte.gestion.proyecciones.fenograma_no_perennes.partials.listado', [
            'listado' => $listado,
            'semana' => $request->semana,
        ]);
    }
}