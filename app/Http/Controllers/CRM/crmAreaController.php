<?php

namespace yura\Http\Controllers\CRM;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use yura\Http\Controllers\Controller;
use yura\Modelos\Ciclo;
use yura\Modelos\ClasificacionVerde;
use yura\Modelos\Cosecha;
use yura\Modelos\Semana;
use yura\Modelos\Submenu;

class crmAreaController extends Controller
{
    public function inicio(Request $request)
    {
        $finca = getFincaActiva();
        /* =========== SEMANAL ============= */
        $semana_pasada = getSemanaByDate(opDiasFecha('-', 7, date('Y-m-d')));

        /* ========== area ========== */
        $area = DB::table('ciclo as c')
            ->select(DB::raw('sum(c.area) as area'))
            ->where('c.estado', 1)
            ->where('c.id_empresa', $finca)
            ->Where(function ($q) use ($semana_pasada) {
                $q->where('c.fecha_fin', '>=', $semana_pasada->fecha_inicial)
                    ->where('c.fecha_fin', '<=', $semana_pasada->fecha_final)
                    ->orWhere(function ($q) use ($semana_pasada) {
                        $q->where('c.fecha_inicio', '>=', $semana_pasada->fecha_inicial)
                            ->where('c.fecha_inicio', '<=', $semana_pasada->fecha_final);
                    })
                    ->orWhere(function ($q) use ($semana_pasada) {
                        $q->where('c.fecha_inicio', '<', $semana_pasada->fecha_inicial)
                            ->where('c.fecha_fin', '>', $semana_pasada->fecha_final);
                    });
            })
            ->get()[0]->area;
        $area = $area > 0 ? $area : 0;

        /* ========== ciclo ========== */
        $data_ciclos = getCiclosCerradosByRango($semana_pasada->codigo, $semana_pasada->codigo, 'T', true, $finca);
        $ciclo = $data_ciclos['ciclo'];
        $area_cerrada = $data_ciclos['area_cerrada'];
        $tallos = $data_ciclos['tallos_cosechados'];

        $ciclo_ano = $ciclo > 0 ? round(365 / $ciclo, 2) : 0;

        $semanal = [
            'ciclo_ano' => $ciclo_ano,
            'area' => $area,
            'ciclo' => $ciclo,
            'tallos' => $area_cerrada > 0 ? round($tallos / $area_cerrada, 2) : 0,
        ];

        /* ======= AÑOS ======= */
        $annos = DB::table('ciclo')
            ->select(DB::raw('year(fecha_inicio) as anno'))->distinct()
            ->where('id_empresa', $finca)
            ->orderBy(DB::raw('year(fecha_inicio)'))
            ->get();

        return view('adminlte.crm.crm_area.inicio', [
            'area_mensual' => getIndicadorByName('D7-' . $finca)->valor,
            'ciclo_mensual' => getIndicadorByName('DA1-' . $finca)->valor,
            'tallos_m2_mensual' => getIndicadorByName('D12-' . $finca)->valor,
            'semanal' => $semanal,
            'annos' => $annos,
            'semana_pasada' => $semana_pasada,

            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
        ]);
    }

    public function filtrar_graficas(Request $request)
    {
        $finca = getFincaActiva();
        $desde = $request->desde;
        $hasta = $request->hasta;

        $data = [];
        if ($request->has('annos') && count($request->annos) > 0) {
            $view = '_annos';

            $periodo = 'semanal';

            if ($request->id_variedad != 'T') {    // por una variedad
                $data = [];
                $labels = [];
                foreach ($request->annos as $pos_anno => $anno) {
                    $query = DB::table('resumen_area_semanal')
                        ->where('estado', 1)
                        ->where('id_empresa', $finca)
                        ->where('id_variedad', $request->id_variedad)
                        ->where('codigo_semana', 'like', substr($anno, 2) . '%')
                        ->orderBy('codigo_semana')
                        ->get();

                    $data_area = [];
                    $data_ciclo = [];
                    $data_tallos = [];
                    foreach ($query as $pos_item => $item) {
                        if ($pos_anno == 0) {
                            array_push($labels, substr($item->codigo_semana, 2));
                        }
                        array_push($data_area, $item->area);
                        array_push($data_ciclo, $item->ciclo);
                        array_push($data_tallos, $item->tallos_m2);
                    }
                    array_push($data, [
                        'label' => $anno,
                        'color' => getListColores()[$pos_anno],
                        'data_area' => $data_area,
                        'data_ciclo' => $data_ciclo,
                        'data_tallos' => $data_tallos,
                    ]);
                }
            } else {    // acumulado
                $data = [];
                $labels = [];
                foreach ($request->annos as $pos_anno => $anno) {
                    $query = DB::table('resumen_area_semanal')
                        ->where('id_empresa', $finca)
                        ->where('estado', 1)
                        ->where('codigo_semana', 'like', substr($anno, 2) . '%')
                        ->orderBy('codigo_semana')
                        ->get();
                    if (count($query) > 0) {
                        $codigo_semana = $query[0]->codigo_semana;
                        $data_area = [];
                        $data_ciclo = [];
                        $data_tallos = [];
                        $area = 0;
                        $ciclo = 0;
                        $tallos = 0;
                        $cant_ciclo = 0;
                        $cant_tallos = 0;
                        foreach ($query as $pos_item => $item) {
                            if ($item->codigo_semana != $codigo_semana || ($pos_item + 1) == count($query)) {
                                if ($pos_anno == 0) {
                                    array_push($labels, substr($codigo_semana, 2));
                                }
                                array_push($data_area, $area);
                                array_push($data_ciclo, $cant_ciclo > 0 ? round($ciclo / $cant_ciclo, 2) : 0);
                                array_push($data_tallos, $cant_tallos > 0 ? round($tallos / $cant_tallos, 2) : 0);

                                $area = $item->area;
                                $ciclo = $item->ciclo;
                                $tallos = $item->tallos_m2;
                                $cant_ciclo = $item->ciclo > 0 ? 1 : 0;
                                $cant_tallos = $item->tallos_m2 > 0 ? 1 : 0;
                                $codigo_semana = $item->codigo_semana;
                            } else {
                                $area += $item->area;
                                $ciclo += $item->ciclo;
                                $tallos += $item->tallos_m2;
                                if ($item->ciclo > 0)
                                    $cant_ciclo++;
                                if ($item->tallos_m2 > 0)
                                    $cant_tallos++;
                            }
                        }
                        array_push($data, [
                            'label' => $anno,
                            'color' => getListColores()[$pos_anno],
                            'data_area' => $data_area,
                            'data_ciclo' => $data_ciclo,
                            'data_tallos' => $data_tallos,
                        ]);
                    }
                }
            }
        } else {
            $view = '_graficas';

            $periodo = 'semanal';

            $labels = [];
            $array_area = [];
            $array_ciclo = [];
            $array_tallos = [];
            if ($request->id_variedad == 'T') { // todas las variedades (acumulado)
                $sem_desde = getSemanaByDate($desde);
                $sem_hasta = getSemanaByDate($hasta);

                $query = DB::table('resumen_area_semanal')
                    ->where('estado', 1)
                    ->where('id_empresa', $finca)
                    ->where('codigo_semana', '>=', $sem_desde->codigo)
                    ->where('codigo_semana', '<=', $sem_hasta->codigo)
                    ->orderBy('codigo_semana')
                    ->get();

                $codigo_semana = $query[0]->codigo_semana;
                $area = 0;
                $ciclo = 0;
                $cant_ciclo = 0;
                $tallos = 0;
                $cant_tallos = 0;
                foreach ($query as $pos_item => $item) {
                    if ($item->codigo_semana != $codigo_semana || ($pos_item + 1) == count($query)) {
                        array_push($labels, $codigo_semana);
                        array_push($array_area, $area);
                        array_push($array_ciclo, $cant_ciclo > 0 ? round($ciclo / $cant_ciclo, 2) : 0);
                        array_push($array_tallos, $cant_tallos > 0 ? round($tallos / $cant_tallos, 2) : 0);

                        $area = $item->area;
                        $ciclo = $item->ciclo;
                        $tallos = $item->tallos_m2;
                        $cant_ciclo = $item->ciclo > 0 ? 1 : 0;
                        $cant_tallos = $item->tallos_m2 > 0 ? 1 : 0;
                        $codigo_semana = $item->codigo_semana;
                    } else {
                        $area += $item->area;
                        $ciclo += $item->ciclo;
                        $tallos += $item->tallos_m2;
                        if ($item->ciclo > 0)
                            $cant_ciclo++;
                        if ($item->tallos_m2 > 0)
                            $cant_tallos++;
                    }
                }
            } else {    // una variedad
                $sem_desde = getSemanaByDate($desde);
                $sem_hasta = getSemanaByDate($hasta);

                $query = DB::table('resumen_area_semanal')
                    ->where('estado', 1)
                    ->where('id_empresa', $finca)
                    ->where('id_variedad', $request->id_variedad)
                    ->where('codigo_semana', '>=', $sem_desde->codigo)
                    ->where('codigo_semana', '<=', $sem_hasta->codigo)
                    ->orderBy('codigo_semana')
                    ->get();

                foreach ($query as $pos_item => $item) {
                    array_push($labels, $item->codigo_semana);
                    array_push($array_area, $item->area);
                    array_push($array_ciclo, $item->ciclo);
                    array_push($array_tallos, $item->tallos_m2);
                }
            }

            $data = [
                'area' => $array_area,
                'ciclo' => $array_ciclo,
                'tallos' => $array_tallos,
            ];
        }

        return view('adminlte.crm.crm_area.partials.' . $view, [
            'labels' => $labels,
            'data' => $data,
            'periodo' => $periodo,
            'semana_actual' => getSemanaByDate(date('Y-m-d'))->codigo,
        ]);
    }

    public function desglose_indicador(Request $request)
    {
        $finca = getFincaActiva();

        $semana_ini = getSemanaByDate(opDiasFecha('-', 28, date('Y-m-d')));
        $semana_fin = getSemanaByDate(opDiasFecha('-', 7, date('Y-m-d')));

        if ($request->option == 'area') {
            $query_variedades = DB::table('ciclo as c')
                ->join('variedad as v', 'v.id_variedad', '=', 'c.id_variedad')
                ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                ->select('c.id_variedad', 'v.nombre as variedad_nombre', 'p.nombre as planta_nombre')->distinct()
                ->where('c.estado', 1)
                ->Where(function ($q) use ($semana_ini, $semana_fin) {
                    $q->where('c.fecha_fin', '>=', $semana_ini->fecha_inicial)
                        ->where('c.fecha_fin', '<=', $semana_fin->fecha_final)
                        ->orWhere(function ($q) use ($semana_ini, $semana_fin) {
                            $q->where('c.fecha_inicio', '>=', $semana_ini->fecha_inicial)
                                ->where('c.fecha_inicio', '<=', $semana_fin->fecha_final);
                        })
                        ->orWhere(function ($q) use ($semana_ini, $semana_fin) {
                            $q->where('c.fecha_inicio', '<', $semana_ini->fecha_inicial)
                                ->where('c.fecha_fin', '>', $semana_fin->fecha_final);
                        });
                })->where('id_empresa', $finca)
                ->orderBy('p.nombre')
                ->orderBy('v.nombre')
                ->get();

            return view('adminlte.crm.crm_area.partials.listado_area', [
                'variedades' => $query_variedades
            ]);
        } else if ($request->option == 'ciclo' || $request->option == 'tallos') {
            $query_variedades = DB::table('ciclo as c')
                ->join('variedad as v', 'v.id_variedad', '=', 'c.id_variedad')
                ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                ->select('c.id_variedad', 'v.nombre as variedad_nombre', 'p.nombre as planta_nombre')->distinct()
                ->where('c.estado', 1)
                ->where('c.activo', 0)
                ->where('c.fecha_fin', '>=', $semana_ini->fecha_inicial)
                ->where('c.fecha_fin', '<=', $semana_fin->fecha_final)
                ->where('c.id_empresa', $finca)
                ->orderBy('p.nombre')
                ->orderBy('v.nombre')
                ->get();

            return view('adminlte.crm.crm_area.partials.listado_ciclo', [
                'variedades' => $query_variedades,
            ]);
        }
    }

    public function mostrar_desglose_area(Request $request)
    {
        $finca = getFincaActiva();

        $semana_ini = getSemanaByDate(opDiasFecha('-', 28, date('Y-m-d')));
        $semana_fin = getSemanaByDate(opDiasFecha('-', 7, date('Y-m-d')));

        $semanas = DB::table('semana')
            ->select('codigo', 'fecha_inicial', 'fecha_final')->distinct()
            ->where('codigo', '>=', $semana_ini->codigo)
            ->where('codigo', '<=', $semana_fin->codigo)
            ->orderBy('codigo')
            ->get();

        $ciclos = [];
        $query = DB::table('ciclo')
            ->select('id_ciclo as id')->distinct()
            ->where('estado', '=', 1)
            ->Where(function ($q) use ($semana_ini, $semana_fin) {
                $q->where('fecha_fin', '>=', $semana_ini->fecha_inicial)
                    ->where('fecha_fin', '<=', $semana_fin->fecha_final)
                    ->orWhere(function ($q) use ($semana_ini, $semana_fin) {
                        $q->where('fecha_inicio', '>=', $semana_ini->fecha_inicial)
                            ->where('fecha_inicio', '<=', $semana_fin->fecha_final);
                    })
                    ->orWhere(function ($q) use ($semana_ini, $semana_fin) {
                        $q->where('fecha_inicio', '<', $semana_ini->fecha_inicial)
                            ->where('fecha_fin', '>', $semana_fin->fecha_final);
                    });
            })
            ->where('id_empresa', $finca);
        if ($request->variedad != 'T')
            $query = $query->where('id_variedad', '=', $request->variedad);

        $query = $query->orderBy('fecha_inicio')->get();

        foreach ($query as $q) {
            $flag = false;
            $ciclo = Ciclo::find($q->id);
            $areas = [];
            foreach ($semanas as $sem) {
                if (($ciclo->fecha_fin >= $sem->fecha_inicial && $ciclo->fecha_fin <= $sem->fecha_final) ||
                    ($ciclo->fecha_inicio >= $sem->fecha_inicial && $ciclo->fecha_inicio <= $sem->fecha_final) ||
                    ($ciclo->fecha_inicio < $sem->fecha_inicial && $ciclo->fecha_fin > $sem->fecha_final)) {
                    $exist_other = DB::table('ciclo')
                        ->select('*')
                        ->where('estado', '=', 1)
                        ->where('id_modulo', '=', $ciclo->id_modulo)
                        ->where('id_variedad', '=', $ciclo->id_variedad)
                        ->where('id_ciclo', '!=', $ciclo->id_ciclo)
                        ->Where(function ($q) use ($sem) {
                            $q->where('fecha_inicio', '>=', $sem->fecha_inicial)
                                ->where('fecha_inicio', '<=', $sem->fecha_final);
                        })
                        ->get();
                    if (count($exist_other) > 0) {
                        $area = 0;
                    } else {
                        $area = $ciclo->area;
                        $flag = true;
                    }
                } else
                    $area = 0;
                array_push($areas, round($area, 2));
            }
            if ($flag)
                array_push($ciclos, [
                    'ciclo' => $ciclo,
                    'areas' => $areas
                ]);
        }
        return view('adminlte.crm.crm_area.partials._desglose_area', [
            'semanas' => $semanas,
            'ciclos' => $ciclos,
        ]);
    }

    public function mostrar_desglose_ciclo(Request $request)
    {
        $finca = getFincaActiva();

        $semana_ini = getSemanaByDate(opDiasFecha('-', 28, date('Y-m-d')));
        $semana_fin = getSemanaByDate(opDiasFecha('-', 7, date('Y-m-d')));

        $semanas = DB::table('semana')
            ->select('codigo', 'fecha_inicial', 'fecha_final')->distinct()
            ->where('codigo', '>=', $semana_ini->codigo)
            ->where('codigo', '<=', $semana_fin->codigo)
            ->orderBy('codigo')
            ->get();

        $data = [];
        foreach ($semanas as $semana) {
            $ciclos = DB::table('resumen_fenograma_ejecucion as f')
                ->join('ciclo as c', 'c.id_ciclo', '=', 'f.id_ciclo')
                ->select('f.id_ciclo', 'f.nombre_modulo', 'f.fecha_inicio', 'f.poda_siembra', 'f.area_m2', 'f.dias',
                    'f.primera_flor', 'f.tallos_cosechados', 'f.real_tallos_m2', 'c.fecha_fin')->distinct()
                ->where('c.estado', 1)
                ->where('c.activo', 0)
                ->where('c.id_variedad', $request->variedad)
                ->where('c.fecha_fin', '>=', $semana->fecha_inicial)
                ->where('c.fecha_fin', '<=', $semana->fecha_final)
                ->where('c.id_empresa', $finca)
                ->orderBy('c.fecha_fin')
                ->get();

            array_push($data, [
                'ciclos' => $ciclos
            ]);
        }

        return view('adminlte.crm.crm_area.partials._ciclo_desglose', [
            'data' => $data,
            'colores_semana' => ['#ADD8E6', '#FFEF92', '#FFC1A6', '#E9ECEF'],
            'semanas' => $semanas,
        ]);
    }

    /* ===================== REGALIAS SEMANAS ===================== */
    public function regalias_semanas(Request $request)
    {
        $semana_actual = getSemanaByDate(date('Y-m-d'));
        $semana_desde = getSemanaByDate(opDiasFecha('-', 28, date('Y-m-d')));
        return view('adminlte.crm.regalias_semanas.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'semana_actual' => $semana_actual,
            'semana_desde' => $semana_desde,
        ]);
    }

    public function buscar_listado(Request $request)
    {
        $data = getAreaCiclosByRango($request->desde, $request->hasta, $request->variedad);
        return view('adminlte.crm.regalias_semanas.partials.listado', [
            'variedades' => $data['variedades'],
            'semanas' => $data['semanas']
        ]);
    }
}