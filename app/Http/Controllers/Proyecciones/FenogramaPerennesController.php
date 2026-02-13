<?php

namespace yura\Http\Controllers\Proyecciones;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use yura\Http\Controllers\Controller;
use yura\Modelos\Submenu;
use yura\Modelos\Variedad;

class FenogramaPerennesController extends Controller
{
    public function inicio(Request $request)
    {
        $semana_pasada = getSemanaByDate(opDiasFecha('-', 7, date('Y-m-d')));
        $finca = getFincaActiva();
        $plantas = DB::table('ciclo as c')
            ->join('variedad as v', 'v.id_variedad', '=', 'c.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->select('v.id_planta', 'p.nombre', 'p.tipo')->distinct()
            ->where('c.estado', 1)
            ->where('v.estado', 1)
            ->where('p.estado', 1)
            ->where('c.activo', 1)
            ->where('c.id_empresa', $finca)
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.proyecciones.fenograma_perennes.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'semana_pasada' => $semana_pasada,
            'plantas' => $plantas,
        ]);
    }

    public function listar_fenograma_perennes(Request $request)
    {
        $finca = getFincaActiva();
        if ($request->planta != 'T') {   // por variedades
            $variedades = Variedad::where('estado', 1);
            if ($request->variedad != 'T')
                $variedades = $variedades->where('id_variedad', $request->variedad);
            elseif ($request->planta != '')
                $variedades = $variedades->where('id_planta', $request->planta);
            $variedades = $variedades->orderBy('nombre')->get();

            $listado = [];
            foreach ($variedades as $var) {
                $tallos_m2_anno = DB::table('semana as sem')
                    ->join('semana_proy_perenne as p', 'p.id_semana', '=', 'sem.id_semana')
                    ->select(DB::raw('sum(p.curva) as cantidad'))
                    ->where('sem.estado', 1)
                    ->where('sem.id_variedad', $var->id_variedad)
                    ->where('sem.codigo', 'like', substr($request->semana, 0, 2) . '%')
                    ->where('p.id_empresa', $finca)
                    ->get()[0]->cantidad;
                $tallos_m2_anno = $tallos_m2_anno > 0 ? $tallos_m2_anno : 0;
                if ($tallos_m2_anno > 0) {
                    $valores = DB::table('semana as sem')
                        ->join('semana_proy_perenne as p', 'p.id_semana', '=', 'sem.id_semana')
                        ->select('sem.id_semana', 'sem.codigo', 'sem.fecha_inicial', 'sem.fecha_final',
                            'p.curva as curva_perenne', 'p.proyectados', 'p.cosechados', 'p.porcentaje_cumplimiento', 'p.tallos_m2_ejecutado',
                            'p.sum_ejec_4_sem', 'p.sum_ejec_13_sem', 'p.sum_ejec_52_sem', 'p.plantas_iniciales',
                            'p.proyectados_acum', 'p.cosechados_acum', 'p.porcentaje_cumplimiento_acum', 'p.tallos_m2_ejecutado_acum')
                        ->where('sem.estado', 1)
                        ->where('sem.id_variedad', $var->id_variedad)
                        ->where('sem.codigo', $request->semana)
                        ->where('p.id_empresa', $finca)
                        ->orderBy('sem.codigo')
                        ->get();
                    $valores = count($valores) == 1 ? $valores : '';
                    $acum_anual = DB::table('semana_proy_perenne as p')
                        ->join('semana as s', 's.id_semana', '=', 'p.id_semana')
                        ->select(DB::raw('sum(p.proyectados) as proyectados'),
                            DB::raw('sum(p.cosechados) as cosechados'),
                            DB::raw('sum(p.tallos_m2_ejecutado) as tallos_m2_ejecutado'))
                        ->where('p.id_empresa', $finca)
                        ->where('s.id_variedad', $var->id_variedad)
                        ->where('s.codigo', '>=', substr($request->semana, 0, 2) . '01')
                        ->where('s.codigo', '<=', $request->semana)
                        ->get()[0];
                    $area = DB::table('ciclo as c')
                        ->select(DB::raw('sum(c.area) as area'))
                        ->where('c.estado', 1)
                        ->where('c.activo', 1)
                        ->where('c.id_variedad', $var->id_variedad)
                        ->where('c.id_empresa', $finca)
                        ->get()[0]->area;
                    $area = $area > 0 ? $area : 0;

                    array_push($listado, [
                        'variedad' => $var,
                        'valores' => $valores[0],
                        'tallos_m2_anno' => $tallos_m2_anno,
                        'area' => $area,
                        'cos_acum_anual' => $acum_anual->cosechados,
                        'proy_acum_anual' => $acum_anual->proyectados,
                        'proy_eje_acum_anual' => $acum_anual->tallos_m2_ejecutado,
                    ]);
                }
            }

            $datos = [
                'listado' => $listado,
                'semana' => $request->semana,
            ];
            $view = 'listado';
        } else {    // por plantas
            $plantas = getPlantasNormales('P');

            $listado = [];
            foreach ($plantas as $pta) {
                $total_area = 0;
                $prom_tallos_m2_anno = 0;
                $total_ptas_iniciales = 0;
                $prom_total_m2_semana = 0;
                $total_proyectados = 0;
                $total_proyectados_acum = 0;
                $total_cosechados = 0;
                $total_cosechados_anno = 0;
                $total_cosechados_acum = 0;
                $prom_tallos_m2_ejec = 0;
                $prom_tallos_m2_ejec_acum = 0;
                $prom_flor_m2_anno_52 = 0;
                $total_proy_acum_anual = 0;
                $cantidad = 0;
                foreach ($pta->variedades->where('estado', 1) as $var) {
                    $tallos_m2_anno = DB::table('semana as sem')
                        ->join('semana_proy_perenne as p', 'p.id_semana', '=', 'sem.id_semana')
                        ->select(DB::raw('sum(p.curva) as cantidad'))
                        ->where('sem.estado', 1)
                        ->where('sem.id_variedad', $var->id_variedad)
                        ->where('sem.codigo', 'like', substr($request->semana, 0, 2) . '%')
                        ->where('p.id_empresa', $finca)
                        ->get()[0]->cantidad;
                    $tallos_m2_anno = $tallos_m2_anno > 0 ? $tallos_m2_anno : 0;
                    if ($tallos_m2_anno > 0) {
                        $prom_tallos_m2_anno += $tallos_m2_anno;
                        $cantidad++;

                        $valores = DB::table('semana as sem')
                            ->join('semana_proy_perenne as p', 'p.id_semana', '=', 'sem.id_semana')
                            ->select('sem.id_semana', 'sem.codigo', 'sem.fecha_inicial', 'sem.fecha_final',
                                'p.curva as curva_perenne', 'p.proyectados', 'p.cosechados', 'p.porcentaje_cumplimiento', 'p.tallos_m2_ejecutado',
                                'p.sum_ejec_52_sem', 'p.plantas_iniciales',
                                'p.proyectados_acum', 'p.cosechados_acum', 'p.porcentaje_cumplimiento_acum', 'p.tallos_m2_ejecutado_acum')
                            ->where('sem.estado', 1)
                            ->where('sem.id_variedad', $var->id_variedad)
                            ->where('sem.codigo', $request->semana)
                            ->where('p.id_empresa', $finca)
                            ->orderBy('sem.codigo')
                            ->get()
                            ->first();
                        $acum_anual = DB::table('semana_proy_perenne as p')
                            ->join('semana as s', 's.id_semana', '=', 'p.id_semana')
                            ->select(DB::raw('sum(p.proyectados) as proyectados'),
                                DB::raw('sum(p.cosechados) as cosechados'),
                                DB::raw('sum(p.tallos_m2_ejecutado) as tallos_m2_ejecutado'))
                            ->where('p.id_empresa', $finca)
                            ->where('s.id_variedad', $var->id_variedad)
                            ->where('s.codigo', '>=', substr($request->semana, 0, 2) . '01')
                            ->where('s.codigo', '<=', $request->semana)
                            ->get()[0];
                        $area = DB::table('ciclo as c')
                            ->select(DB::raw('sum(c.area) as area'))
                            ->where('c.estado', 1)
                            ->where('c.activo', 1)
                            ->where('c.id_variedad', $var->id_variedad)
                            ->where('c.id_empresa', $finca)
                            ->get()[0]->area;
                        $area = $area > 0 ? $area : 0;
                        $total_area += $area;

                        $total_ptas_iniciales += $valores->plantas_iniciales;
                        $prom_total_m2_semana += $valores->curva_perenne;
                        $total_proyectados += $valores->proyectados;
                        $total_proyectados_acum += $valores->proyectados_acum;
                        $total_cosechados += $valores->cosechados;
                        $total_cosechados_anno += $acum_anual->cosechados;
                        $total_cosechados_acum += $valores->cosechados_acum;
                        $prom_tallos_m2_ejec += $valores->tallos_m2_ejecutado;
                        $prom_tallos_m2_ejec_acum += $acum_anual->tallos_m2_ejecutado;
                        $prom_flor_m2_anno_52 += $valores->sum_ejec_52_sem * 1;
                        $total_proy_acum_anual += $acum_anual->proyectados;
                    }
                }

                if ($prom_tallos_m2_anno > 0) {
                    $num_sem = intval(substr($request->semana, 2, 2));
                    array_push($listado, [
                        'planta' => $pta,
                        'area' => $total_area,
                        'plantas_iniciales' => $total_ptas_iniciales,
                        'densidad' => $total_area > 0 ? round($total_ptas_iniciales / $total_area, 2) : 0,
                        'tallos_m2_anno' => $cantidad > 0 ? round($prom_tallos_m2_anno / $cantidad, 2) : 0,
                        'tallos_m2_semana' => $cantidad > 0 ? round($prom_total_m2_semana / $cantidad, 2) : 0,
                        'total_proyectados' => $total_proyectados,
                        'total_proyectados_acum' => $total_proyectados_acum,
                        'total_cosechados' => $total_cosechados,
                        'total_cosechados_anno' => $total_cosechados_anno,
                        'total_cosechados_acum' => $total_cosechados_acum,
                        'cumplimiento' => porcentaje($total_cosechados, $total_proyectados, 1),
                        'cumplimiento_acum' => porcentaje($total_cosechados_anno, $total_proy_acum_anual, 1),
                        'tallos_m2_ejec' => $cantidad > 0 ? round($prom_tallos_m2_ejec / $cantidad, 2) : 0,
                        'tallos_m2_ejec_acum' => $cantidad > 0 ? round($prom_tallos_m2_ejec_acum / $cantidad, 2) : 0,
                        'flor_m2_anno_52' => $total_area > 0 && $num_sem > 0 ? round((($total_cosechados_anno / $total_area) / $num_sem) * 52, 2) : 0,
                        'proy_acum_anual' => $total_proy_acum_anual,
                    ]);

                }
            }

            $datos = [
                'listado' => $listado,
                'semana' => $request->semana,
            ];
            $view = 'listado_acumulado';
        }
        return view('adminlte.gestion.proyecciones.fenograma_perennes.partials.' . $view, $datos);
    }
}
