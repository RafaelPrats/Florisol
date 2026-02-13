<?php

namespace yura\Http\Controllers\CRM;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use yura\Http\Controllers\Controller;
use yura\Modelos\Ciclo;
use yura\Modelos\IndicadorSemana;
use yura\Modelos\IndicadorVariedad;
use yura\Modelos\IndicadorVariedadSemana;
use yura\Modelos\Planta;
use yura\Modelos\Semana;
use yura\Modelos\Submenu;
use yura\Modelos\Variedad;

class ResumenEBITDAController extends Controller
{
    public function inicio(Request $request)
    {
        return view('adminlte.crm.resumen_ebitda.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'desde' => getSemanaByDate(opDiasFecha('-', 42, hoy())),
            'hasta' => getSemanaByDate(opDiasFecha('-', 7, hoy())),
        ]);
    }

    public function buscar_resumen_ebitda_old(Request $request)
    {
        $finca = getFincaActiva();
        $semanas = DB::table('semana')
            ->select('codigo', 'fecha_inicial', 'fecha_final', 'last_4_semana')->distinct()
            ->where('estado', 1)
            ->where('semana_guia', 1)
            ->where('codigo', '>=', $request->desde)
            ->where('codigo', '<=', $request->hasta)
            ->get();

        $variedades = DB::table('variedad as v')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->select('v.id_variedad', 'v.nombre as variedad_nombre', 'p.nombre as planta_nombre', 'v.id_planta')->distinct()
            ->where('v.estado', 1)
            ->where('p.estado', 1);
        if (!in_array($request->variedad, ['T', 'A'])) {    // una variedad
            $variedades = $variedades->where('v.id_variedad', $request->variedad);
        } elseif ($request->planta != 'T') {    // variedades de una planta
            $variedades = $variedades->where('v.id_planta', $request->planta);
        }
        $variedades = $variedades->orderBy('p.nombre')
            ->orderBy('v.nombre')
            ->get();

        $resumen_semanal_finca_last_4 = [];

        $listado = [];
        foreach ($variedades as $pos_v => $var) {
            /* AREA */
            $resumen_area = [];
            $resumen_area_last_4 = [];
            $resumen_semanal = [];
            $resumen_semanal_last_4 = [];
            $compra_flor = [];
            foreach ($semanas as $sem) {
                $cant = DB::table('ciclo')
                    ->select(DB::raw('sum(area) as area'))
                    ->where('estado', '=', 1)
                    ->where('id_variedad', $var->id_variedad)
                    ->where('id_empresa', $finca)
                    ->Where(function ($q) use ($sem) {
                        $q->where('fecha_fin', '>=', $sem->fecha_inicial)
                            ->where('fecha_fin', '<=', $sem->fecha_final)
                            ->orWhere(function ($q) use ($sem) {
                                $q->where('fecha_inicio', '>=', $sem->fecha_inicial)
                                    ->where('fecha_inicio', '<=', $sem->fecha_final);
                            })
                            ->orWhere(function ($q) use ($sem) {
                                $q->where('fecha_inicio', '<', $sem->fecha_inicial)
                                    ->where('fecha_fin', '>', $sem->fecha_final);
                            });
                    })
                    ->get()[0]->area;
                array_push($resumen_area, $cant);

                $query = DB::table('resumen_total_semanal_exportcalas')
                    ->select('semana',
                        DB::raw('sum(tallos_cosechados) as tallos_cosechados'),
                        DB::raw('sum(tallos_exportables) as tallos_exportables'),
                        DB::raw('sum(bouquetera) as bouquetera'),
                        DB::raw('sum(venta) as venta'),
                        DB::raw('sum(venta_bouquetera) as venta_bouquetera'),
                        DB::raw('sum(nacional) as nacionales'),
                        DB::raw('sum(bajas) as bajas'),
                        DB::raw('sum(tallos_vendidos) as tallos_vendidos'))
                    ->where('id_variedad', $var->id_variedad)
                    ->where('id_empresa', $finca)
                    ->where('semana', $sem->codigo)
                    ->groupBy('semana')
                    ->orderBy('semana')
                    ->first();

                array_push($resumen_semanal, $query);

                /* ---------- compra de flor ---------- */
                $cant = DB::table('bouquetera')
                    ->select(DB::raw('sum(precio * (tallos)) as tallos'),
                        DB::raw('sum(precio * (exportada)) as exportada'),
                        DB::raw('sum(tallos) as tallos_bqt'),
                        DB::raw('sum(exportada) as tallos_exportada'))
                    ->where('fecha', '>=', $sem->fecha_inicial)
                    ->where('fecha', '<=', $sem->fecha_final);
                if ($finca != 2)
                    $cant = $cant->where('id_empresa', 0);
                $cant = $cant->get()[0];
                array_push($compra_flor, $cant);

                /* ---------- last 4 semanas ---------- */
                $desde_fecha_4 = opDiasFecha('-', 21, $sem->fecha_inicial);
                $cant = DB::table('ciclo')
                    ->select(DB::raw('sum(area) as area'))
                    ->where('estado', '=', 1)
                    ->where('id_variedad', $var->id_variedad)
                    ->where('id_empresa', $finca)
                    ->Where(function ($q) use ($desde_fecha_4, $sem) {
                        $q->where('fecha_fin', '>=', $desde_fecha_4)
                            ->where('fecha_fin', '<=', $sem->fecha_final)
                            ->orWhere(function ($q) use ($desde_fecha_4, $sem) {
                                $q->where('fecha_inicio', '>=', $desde_fecha_4)
                                    ->where('fecha_inicio', '<=', $sem->fecha_final);
                            })
                            ->orWhere(function ($q) use ($desde_fecha_4, $sem) {
                                $q->where('fecha_inicio', '<', $desde_fecha_4)
                                    ->where('fecha_fin', '>', $sem->fecha_final);
                            });
                    })
                    ->get()[0]->area;
                array_push($resumen_area_last_4, $cant);

                $desde_sem_4 = $sem->last_4_semana;
                $query = DB::table('resumen_total_semanal_exportcalas')
                    ->select(DB::raw('sum(tallos_cosechados) as tallos_cosechados'),
                        DB::raw('sum(tallos_exportables) as tallos_exportables'),
                        DB::raw('sum(bouquetera) as bouquetera'),
                        DB::raw('sum(venta) as venta'),
                        DB::raw('sum(venta_bouquetera) as venta_bouquetera'))
                    ->where('id_variedad', $var->id_variedad)
                    ->where('id_empresa', $finca)
                    ->where('semana', '>=', $desde_sem_4)
                    ->where('semana', '<=', $sem->codigo)
                    ->first();

                array_push($resumen_semanal_last_4, $query);

                if ($pos_v == 0) {
                    $query = DB::table('resumen_total_semanal_exportcalas')
                        ->select(DB::raw('sum(tallos_cosechados) as tallos_cosechados'),
                            DB::raw('sum(tallos_exportables) as tallos_exportables'),
                            DB::raw('sum(bouquetera) as bouquetera'),
                            DB::raw('sum(venta) as venta'),
                            DB::raw('sum(venta_bouquetera) as venta_bouquetera'))
                        ->where('id_empresa', $finca)
                        ->where('semana', '>=', $desde_sem_4)
                        ->where('semana', '<=', $sem->codigo)
                        ->first();
                    array_push($resumen_semanal_finca_last_4, $query);
                }
            }

            array_push($listado, [
                'variedad' => $var,
                'area' => $resumen_area,
                'area_last_4' => $resumen_area_last_4,
                'resumen_semanal_last_4' => $resumen_semanal_last_4,
                'resumen_semanal' => $resumen_semanal,
                'compra_flor' => $compra_flor,
            ]);
        }

        $indicadores_4_semanas = DB::table('indicadores_4_semanas')
            ->where('semana', '>=', $request->desde)
            ->where('semana', '<=', $request->hasta)
            ->where('id_empresa', $finca)
            ->orderBy('semana')
            ->get();

        $resumen_semanal_finca = DB::table('resumen_total_semanal_exportcalas')
            ->select('semana',
                DB::raw('sum(tallos_cosechados) as tallos_cosechados'),
                DB::raw('sum(tallos_exportables) as tallos_exportables'),
                DB::raw('sum(bouquetera) as bouquetera'),
                DB::raw('sum(venta) as venta'),
                DB::raw('sum(venta_bouquetera) as venta_bouquetera'))
            ->where('id_empresa', $finca)
            ->where('semana', '>=', $request->desde)
            ->where('semana', '<=', $request->hasta)
            ->groupBy('semana')
            ->orderBy('semana')
            ->get();

        return view('adminlte.crm.resumen_ebitda.partials.listado', [
            'semanas' => $semanas,
            'listado' => $listado,
            'resumen_semanal_finca' => $resumen_semanal_finca,
            'resumen_semanal_finca_last_4' => $resumen_semanal_finca_last_4,
            'indicadores_4_semanas' => $indicadores_4_semanas,
        ]);
    }

    public function buscar_resumen_ebitda(Request $request)
    {
        $semanas = DB::table('semana')
            ->select('codigo', 'fecha_inicial', 'fecha_final')->distinct()
            ->where('codigo', '>=', $request->desde)
            ->where('codigo', '<=', $request->hasta)
            ->orderBy('codigo')
            ->get();

        if ($request->reporte == 1) {
            $datos = $this->listar_area($semanas);
            $view = 'listado';
        }
        if ($request->reporte == 2) {
            $datos = $this->listar_tallos_cosechados($semanas);
            $view = 'listado';
        }
        if ($request->reporte == 3) {
            $datos = $this->listar_tallos_producidos($semanas);
            $view = 'listado_doble';
        }
        if ($request->reporte == 4) {
            $datos = $this->listar_ventas($semanas);
            $view = 'listado_doble';
        }
        if ($request->reporte == 5) {
            $datos = $this->listar_precio_x_tallo($semanas);
            $view = 'listado_promedio';
        }
        if ($request->reporte == 6) {
            $datos = $this->listar_tallo_m2($semanas);
            $view = 'listado_promedio';
        }
        if ($request->reporte == 7) {
            $datos = $this->listar_venta_m2($semanas);
            $view = 'listado_promedio';
        }
        return view('adminlte.crm.resumen_ebitda.partials.' . $view, $datos);
    }

    function listar_area($semanas)
    {
        $listado = [];
        if (count($semanas) > 0) {
            $finca = getFincaActiva();
            $desde = $semanas[0];
            $hasta = $semanas[count($semanas) - 1];

            $plantas = DB::table('planta as p')
                ->select('p.id_planta', 'p.nombre')->distinct()
                ->where('p.estado', 1)
                ->orderBy('p.nombre')
                ->get();

            foreach ($plantas as $pos => $pta) {
                $pta = Planta::find($pta->id_planta);
                $valores = [];
                foreach ($semanas as $sem) {
                    if ($pta->tipo == 'P' || $pta->tiene_ciclos == 1) { // tiene ciclos o es perenne
                        $cant = DB::table('ciclo as c')
                            ->join('variedad as v', 'v.id_variedad', '=', 'c.id_variedad')
                            ->select(DB::raw('sum(c.area) as area'))
                            ->where('v.estado', 1)
                            ->where('c.estado', 1)
                            ->where('v.id_planta', $pta->id_planta)
                            ->where('c.id_empresa', $finca)
                            ->Where(function ($q) use ($sem) {
                                $q->where('fecha_fin', '>=', $sem->fecha_inicial)
                                    ->where('fecha_fin', '<=', $sem->fecha_final)
                                    ->orWhere(function ($q) use ($sem) {
                                        $q->where('fecha_inicio', '>=', $sem->fecha_inicial)
                                            ->where('fecha_inicio', '<=', $sem->fecha_final);
                                    })
                                    ->orWhere(function ($q) use ($sem) {
                                        $q->where('fecha_inicio', '<', $sem->fecha_inicial)
                                            ->where('fecha_fin', '>', $sem->fecha_final);
                                    });
                            })
                            ->get()[0]->area;
                    } else {
                        $cant = DB::table('proy_no_perennes as proy')
                            ->join('semana as s', 's.id_semana', '=', 'proy.id_semana')
                            ->join('variedad as v', 'v.id_variedad', '=', 's.id_variedad')
                            ->select(DB::raw('sum(proy.area_produccion) as area_produccion'),
                                DB::raw('sum(proy.area_semana) as area_semana'))
                            ->where('s.codigo', $sem->codigo)
                            ->where('proy.id_empresa', $finca)
                            ->where('v.id_planta', $pta->id_planta)
                            ->get()[0]->area_produccion;
                    }
                    $valores[] = $cant;
                }

                array_push($listado, [
                    'planta' => $pta,
                    'valores' => $valores,
                ]);
            }
        }
        return [
            'listado' => $listado,
            'semanas' => $semanas,
            'number_format' => 2,
        ];
    }

    function listar_tallos_cosechados($semanas)
    {
        $listado = [];
        if (count($semanas) > 0) {
            $finca = getFincaActiva();
            $desde = $semanas[0];
            $hasta = $semanas[count($semanas) - 1];

            $plantas = DB::table('cosecha_diaria')
                ->select('id_planta', 'planta_nombre as nombre')->distinct()
                ->where('id_empresa', $finca)
                ->where('cosechados', '>', 0)
                ->where('fecha', '>=', $desde->fecha_inicial)
                ->where('fecha', '<=', $hasta->fecha_final)
                ->orderBy('planta_nombre')
                ->get();

            foreach ($plantas as $pos => $pta) {
                $valores = [];
                foreach ($semanas as $sem) {
                    $cant = DB::table('cosecha_diaria')
                        ->select(DB::raw('sum(cosechados) as cantidad'))->distinct()
                        ->where('id_empresa', $finca)
                        ->where('id_planta', $pta->id_planta)
                        ->where('fecha', '>=', $sem->fecha_inicial)
                        ->where('fecha', '<=', $sem->fecha_final)
                        ->get()[0]->cantidad;
                    $valores[] = $cant;
                }

                array_push($listado, [
                    'planta' => $pta,
                    'valores' => $valores,
                ]);
            }
        }
        return [
            'listado' => $listado,
            'semanas' => $semanas,
            'number_format' => 0,
        ];
    }

    function listar_tallos_producidos($semanas)
    {
        $listado = [];
        if (count($semanas) > 0) {
            $finca = getFincaActiva();
            $fincas = [$finca];
            if ($finca == 2)
                array_push($fincas, -1);
            $desde = $semanas[0];
            $hasta = $semanas[count($semanas) - 1];

            $plantas_export = DB::table('resumen_total_semanal_exportcalas as r')
                ->join('variedad as v', 'v.id_variedad', '=', 'r.id_variedad')
                ->select('v.id_planta')->distinct()
                ->where('v.estado', 1)
                ->where('r.id_empresa', $finca)
                ->where('r.tallos_exportables', '>', 0)
                ->where('r.semana', '>=', $desde->codigo)
                ->where('r.semana', '<=', $hasta->codigo)
                ->get();
            $plantas_bqt = DB::table('bouquetera as b')
                ->join('planta as p', 'p.id_planta', '=', 'b.id_planta')
                ->select('b.id_planta')->distinct()
                ->where('p.estado', 1)
                ->where('b.fecha', '>=', $desde->fecha_inicial)
                ->where('b.fecha', '<=', $hasta->fecha_final)
                ->whereIn('b.id_empresa', $fincas)
                ->get();
            $plantas_query = [];
            foreach ($plantas_export as $v)
                array_push($plantas_query, $v->id_planta);
            foreach ($plantas_bqt as $v)
                if (!in_array($v->id_planta, $plantas_query))
                    array_push($plantas_query, $v->id_planta);

            $plantas = DB::table('planta')
                ->select('nombre', 'id_planta')->distinct()
                ->where('estado', 1)
                ->whereIn('id_planta', $plantas_query)
                ->orderBy('nombre', 'asc')
                ->get();

            foreach ($plantas as $pos => $pta) {
                $exportables = [];
                $bouquetera = [];
                foreach ($semanas as $sem) {
                    $export = DB::table('resumen_total_semanal_exportcalas as r')
                        ->join('variedad as v', 'v.id_variedad', '=', 'r.id_variedad')
                        ->select(DB::raw('sum(tallos_exportables) as cantidad'))
                        ->where('v.id_planta', $pta->id_planta)
                        ->where('r.id_empresa', $finca)
                        ->where('r.semana', $sem->codigo)
                        ->get()[0]->cantidad;
                    $exportables[] = $export;
                    $bqt = DB::table('bouquetera')
                        ->select(DB::raw('sum(tallos) as cantidad'))
                        ->where('id_planta', $pta->id_planta)
                        ->where('fecha', '>=', $sem->fecha_inicial)
                        ->where('fecha', '<=', $sem->fecha_final)
                        ->whereIn('id_empresa', $fincas)
                        ->get()[0]->cantidad;
                    $bouquetera[] = $bqt;
                }

                array_push($listado, [
                    'planta' => $pta,
                    'valores1' => $exportables,
                    'valores2' => $bouquetera,
                ]);
            }
        }
        return [
            'listado' => $listado,
            'semanas' => $semanas,
            'number_format' => 0,
            'labels' => ['Exportables', 'Bouquetera'],
        ];
    }

    function listar_ventas($semanas)
    {
        $listado = [];
        if (count($semanas) > 0) {
            $finca = getFincaActiva();
            $fincas = [$finca];
            if ($finca == 2)
                array_push($fincas, -1);
            $desde = $semanas[0];
            $hasta = $semanas[count($semanas) - 1];

            $plantas_ventas = DB::table('resumen_total_semanal_exportcalas as r')
                ->join('variedad as v', 'v.id_variedad', '=', 'r.id_variedad')
                ->select('v.id_planta')->distinct()
                ->where('v.estado', 1)
                ->where('r.id_empresa', $finca)
                ->where('r.venta', '>', 0)
                ->where('r.semana', '>=', $desde->codigo)
                ->where('r.semana', '<=', $hasta->codigo)
                ->get();
            $plantas_bqt = DB::table('resumen_total_semanal_exportcalas as r')
                ->join('variedad as v', 'v.id_variedad', '=', 'r.id_variedad')
                ->select('v.id_planta')->distinct()
                ->where('v.estado', 1)
                ->where('r.id_empresa', $finca)
                ->where('r.venta_bouquetera', '>', 0)
                ->where('r.semana', '>=', $desde->codigo)
                ->where('r.semana', '<=', $hasta->codigo)
                ->get();
            $plantas_query = [];
            foreach ($plantas_ventas as $v)
                array_push($plantas_query, $v->id_planta);
            foreach ($plantas_bqt as $v)
                if (!in_array($v->id_planta, $plantas_query))
                    array_push($plantas_query, $v->id_planta);

            $plantas = DB::table('planta')
                ->select('nombre', 'id_planta')->distinct()
                ->where('estado', 1)
                ->whereIn('id_planta', $plantas_query)
                ->orderBy('nombre', 'asc')
                ->get();

            foreach ($plantas as $pos => $pta) {
                $ventas = [];
                $bouquetera = [];
                foreach ($semanas as $sem) {
                    $venta = DB::table('resumen_total_semanal_exportcalas as r')
                        ->join('variedad as v', 'v.id_variedad', '=', 'r.id_variedad')
                        ->select(DB::raw('sum(venta) as cantidad'))
                        ->where('v.id_planta', $pta->id_planta)
                        ->where('r.id_empresa', $finca)
                        ->where('r.semana', $sem->codigo)
                        ->get()[0]->cantidad;
                    $ventas[] = $venta;
                    $bqt = DB::table('resumen_total_semanal_exportcalas as r')
                        ->join('variedad as v', 'v.id_variedad', '=', 'r.id_variedad')
                        ->select(DB::raw('sum(venta_bouquetera) as cantidad'))
                        ->where('v.id_planta', $pta->id_planta)
                        ->where('r.id_empresa', $finca)
                        ->where('r.semana', $sem->codigo)
                        ->get()[0]->cantidad;
                    $bouquetera[] = $bqt;
                }

                array_push($listado, [
                    'planta' => $pta,
                    'valores1' => $ventas,
                    'valores2' => $bouquetera,
                ]);
            }
        }
        return [
            'listado' => $listado,
            'semanas' => $semanas,
            'number_format' => 2,
            'labels' => ['Normal', 'Bouquetera'],
        ];
    }

    function listar_precio_x_tallo($semanas)
    {
        $listado = [];
        if (count($semanas) > 0) {
            $finca = getFincaActiva();
            $fincas = [$finca];
            if ($finca == 2)
                array_push($fincas, -1);
            $desde = $semanas[0];
            $hasta = $semanas[count($semanas) - 1];

            $plantas_export = DB::table('resumen_total_semanal_exportcalas as r')
                ->join('variedad as v', 'v.id_variedad', '=', 'r.id_variedad')
                ->select('v.id_planta')->distinct()
                ->where('v.estado', 1)
                ->where('r.id_empresa', $finca)
                ->where('r.tallos_exportables', '>', 0)
                ->where('r.semana', '>=', $desde->codigo)
                ->where('r.semana', '<=', $hasta->codigo)
                ->get();
            $plantas_bqt = DB::table('bouquetera as b')
                ->join('planta as p', 'p.id_planta', '=', 'b.id_planta')
                ->select('b.id_planta')->distinct()
                ->where('p.estado', 1)
                ->where('b.fecha', '>=', $desde->fecha_inicial)
                ->where('b.fecha', '<=', $hasta->fecha_final)
                ->whereIn('b.id_empresa', $fincas)
                ->get();
            $plantas_ventas = DB::table('resumen_total_semanal_exportcalas as r')
                ->join('variedad as v', 'v.id_variedad', '=', 'r.id_variedad')
                ->select('v.id_planta')->distinct()
                ->where('v.estado', 1)
                ->where('r.id_empresa', $finca)
                ->where('r.venta', '>', 0)
                ->where('r.semana', '>=', $desde->codigo)
                ->where('r.semana', '<=', $hasta->codigo)
                ->get();
            $plantas_ventas_bqt = DB::table('resumen_total_semanal_exportcalas as r')
                ->join('variedad as v', 'v.id_variedad', '=', 'r.id_variedad')
                ->select('v.id_planta')->distinct()
                ->where('v.estado', 1)
                ->where('r.id_empresa', $finca)
                ->where('r.venta_bouquetera', '>', 0)
                ->where('r.semana', '>=', $desde->codigo)
                ->where('r.semana', '<=', $hasta->codigo)
                ->get();
            $plantas_query = [];
            foreach ($plantas_export as $v)
                array_push($plantas_query, $v->id_planta);
            foreach ($plantas_bqt as $v)
                if (!in_array($v->id_planta, $plantas_query))
                    array_push($plantas_query, $v->id_planta);
            foreach ($plantas_ventas as $v)
                if (!in_array($v->id_planta, $plantas_query))
                    array_push($plantas_query, $v->id_planta);
            foreach ($plantas_ventas_bqt as $v)
                if (!in_array($v->id_planta, $plantas_query))
                    array_push($plantas_query, $v->id_planta);

            $plantas = DB::table('planta')
                ->select('nombre', 'id_planta')->distinct()
                ->where('estado', 1)
                ->whereIn('id_planta', $plantas_query)
                ->orderBy('nombre', 'asc')
                ->get();

            foreach ($plantas as $pos => $pta) {
                $valores = [];
                foreach ($semanas as $sem) {
                    $export = DB::table('resumen_total_semanal_exportcalas as r')
                        ->join('variedad as v', 'v.id_variedad', '=', 'r.id_variedad')
                        ->select(DB::raw('sum(tallos_exportables) as cantidad'))
                        ->where('v.id_planta', $pta->id_planta)
                        ->where('r.id_empresa', $finca)
                        ->where('r.semana', $sem->codigo)
                        ->get()[0]->cantidad;
                    $bqt = DB::table('bouquetera')
                        ->select(DB::raw('sum(tallos) as cantidad'))
                        ->where('id_planta', $pta->id_planta)
                        ->where('fecha', '>=', $sem->fecha_inicial)
                        ->where('fecha', '<=', $sem->fecha_final)
                        ->whereIn('id_empresa', $fincas)
                        ->get()[0]->cantidad;
                    $tallos = $export + $bqt;

                    $venta = DB::table('resumen_total_semanal_exportcalas as r')
                        ->join('variedad as v', 'v.id_variedad', '=', 'r.id_variedad')
                        ->select(DB::raw('sum(venta + venta_bouquetera) as cantidad'))
                        ->where('v.id_planta', $pta->id_planta)
                        ->where('r.id_empresa', $finca)
                        ->where('r.semana', $sem->codigo)
                        ->get()[0]->cantidad;

                    $valores[] = $tallos > 0 ? round($venta / $tallos, 2) : 0;
                }

                array_push($listado, [
                    'planta' => $pta,
                    'valores' => $valores,
                ]);
            }
        }
        return [
            'listado' => $listado,
            'semanas' => $semanas,
            'number_format' => 2,
        ];
    }

    function listar_tallo_m2($semanas)
    {
        $listado = [];
        if (count($semanas) > 0) {
            $finca = getFincaActiva();
            $fincas = [$finca];
            if ($finca == 2)
                array_push($fincas, -1);
            $desde = $semanas[0];
            $hasta = $semanas[count($semanas) - 1];

            $plantas_export = DB::table('resumen_total_semanal_exportcalas as r')
                ->join('variedad as v', 'v.id_variedad', '=', 'r.id_variedad')
                ->select('v.id_planta')->distinct()
                ->where('v.estado', 1)
                ->where('r.id_empresa', $finca)
                ->where('r.tallos_exportables', '>', 0)
                ->where('r.semana', '>=', $desde->codigo)
                ->where('r.semana', '<=', $hasta->codigo)
                ->get();
            $plantas_bqt = DB::table('bouquetera as b')
                ->join('planta as p', 'p.id_planta', '=', 'b.id_planta')
                ->select('b.id_planta')->distinct()
                ->where('p.estado', 1)
                ->where('b.fecha', '>=', $desde->fecha_inicial)
                ->where('b.fecha', '<=', $hasta->fecha_final)
                ->whereIn('b.id_empresa', $fincas)
                ->get();
            $plantas_area = DB::table('ciclo as c')
                ->join('variedad as v', 'v.id_variedad', '=', 'c.id_variedad')
                ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                ->select('v.id_planta')->distinct()
                ->where('c.estado', 1)
                ->where('v.estado', 1)
                ->where('p.estado', 1)
                ->where('c.id_empresa', $finca)
                ->Where(function ($q) use ($desde, $hasta) {
                    $q->where('c.fecha_fin', '>=', $desde->fecha_inicial)
                        ->where('c.fecha_fin', '<=', $hasta->fecha_final)
                        ->orWhere(function ($q) use ($desde, $hasta) {
                            $q->where('c.fecha_inicio', '>=', $desde->fecha_inicial)
                                ->where('c.fecha_inicio', '<=', $hasta->fecha_final);
                        })
                        ->orWhere(function ($q) use ($desde, $hasta) {
                            $q->where('c.fecha_inicio', '<', $desde->fecha_inicial)
                                ->where('c.fecha_fin', '>', $hasta->fecha_final);
                        });
                })
                ->orderBy('p.nombre')
                ->get();
            $plantas_query = [];
            foreach ($plantas_export as $v)
                array_push($plantas_query, $v->id_planta);
            foreach ($plantas_bqt as $v)
                if (!in_array($v->id_planta, $plantas_query))
                    array_push($plantas_query, $v->id_planta);
            foreach ($plantas_area as $v)
                if (!in_array($v->id_planta, $plantas_query))
                    array_push($plantas_query, $v->id_planta);

            $plantas = DB::table('planta')
                ->select('nombre', 'id_planta')->distinct()
                ->where('estado', 1)
                ->whereIn('id_planta', $plantas_query)
                ->orderBy('nombre', 'asc')
                ->get();

            foreach ($plantas as $pos => $pta) {
                $pta = Planta::find($pta->id_planta);
                $valores = [];
                foreach ($semanas as $sem) {
                    $tallos = DB::table('resumen_total_semanal_exportcalas as p')
                        ->join('variedad as v', 'v.id_variedad', '=', 'p.id_variedad')
                        ->select(DB::raw('sum(p.tallos_cosechados) as tallos_cosechados'))
                        ->where('v.id_planta', $pta->id_planta)
                        ->where('p.id_empresa', $finca)
                        ->where('p.semana', '>=', substr($sem->codigo, 0, 2) . '01')
                        ->where('p.semana', '<=', $sem->codigo)
                        ->get()[0]->tallos_cosechados;

                    if ($pta->tipo == 'P' || $pta->tiene_ciclos == 1) { // tiene ciclos o es perenne
                        $area = DB::table('ciclo as c')
                            ->join('variedad as v', 'v.id_variedad', '=', 'c.id_variedad')
                            ->select(DB::raw('sum(c.area) as area'))
                            ->where('v.estado', 1)
                            ->where('c.estado', 1)
                            ->where('v.id_planta', $pta->id_planta)
                            ->where('c.id_empresa', $finca)
                            ->Where(function ($q) use ($sem) {
                                $q->where('fecha_fin', '>=', $sem->fecha_inicial)
                                    ->where('fecha_fin', '<=', $sem->fecha_final)
                                    ->orWhere(function ($q) use ($sem) {
                                        $q->where('fecha_inicio', '>=', $sem->fecha_inicial)
                                            ->where('fecha_inicio', '<=', $sem->fecha_final);
                                    })
                                    ->orWhere(function ($q) use ($sem) {
                                        $q->where('fecha_inicio', '<', $sem->fecha_inicial)
                                            ->where('fecha_fin', '>', $sem->fecha_final);
                                    });
                            })
                            ->get()[0]->area;
                    } else {
                        $area = DB::table('proy_no_perennes as proy')
                            ->join('semana as s', 's.id_semana', '=', 'proy.id_semana')
                            ->join('variedad as v', 'v.id_variedad', '=', 's.id_variedad')
                            ->select(DB::raw('sum(proy.area_produccion) as area_produccion'),
                                DB::raw('sum(proy.area_semana) as area_semana'))
                            ->where('s.codigo', $sem->codigo)
                            ->where('proy.id_empresa', $finca)
                            ->where('v.id_planta', $pta->id_planta)
                            ->get()[0]->area_semana;
                    }

                    $num_sem = intval(substr($sem->codigo, 2, 2));
                    $flor_m2_anno_52 = $area > 0 && $num_sem > 0 ? round((($tallos / $area) / $num_sem) * 52, 2) : 0;
                    $valores[] = $flor_m2_anno_52;
                }

                array_push($listado, [
                    'planta' => $pta,
                    'valores' => $valores,
                ]);
            }
        }
        return [
            'listado' => $listado,
            'semanas' => $semanas,
            'number_format' => 2,
        ];
    }

    function listar_venta_m2($semanas)
    {
        $listado = [];
        if (count($semanas) > 0) {
            $finca = getFincaActiva();
            $fincas = [$finca];
            if ($finca == 2)
                array_push($fincas, -1);
            $desde = $semanas[0];
            $hasta = $semanas[count($semanas) - 1];

            $plantas_ventas = DB::table('resumen_total_semanal_exportcalas as r')
                ->join('variedad as v', 'v.id_variedad', '=', 'r.id_variedad')
                ->select('v.id_planta')->distinct()
                ->where('v.estado', 1)
                ->where('r.id_empresa', $finca)
                ->where('r.venta', '>', 0)
                ->where('r.semana', '>=', $desde->codigo)
                ->where('r.semana', '<=', $hasta->codigo)
                ->get();
            $plantas_ventas_bqt = DB::table('resumen_total_semanal_exportcalas as r')
                ->join('variedad as v', 'v.id_variedad', '=', 'r.id_variedad')
                ->select('v.id_planta')->distinct()
                ->where('v.estado', 1)
                ->where('r.id_empresa', $finca)
                ->where('r.venta_bouquetera', '>', 0)
                ->where('r.semana', '>=', $desde->codigo)
                ->where('r.semana', '<=', $hasta->codigo)
                ->get();
            $plantas_area = DB::table('ciclo as c')
                ->join('variedad as v', 'v.id_variedad', '=', 'c.id_variedad')
                ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                ->select('v.id_planta')->distinct()
                ->where('c.estado', 1)
                ->where('v.estado', 1)
                ->where('p.estado', 1)
                ->where('c.id_empresa', $finca)
                ->Where(function ($q) use ($desde, $hasta) {
                    $q->where('c.fecha_fin', '>=', $desde->fecha_inicial)
                        ->where('c.fecha_fin', '<=', $hasta->fecha_final)
                        ->orWhere(function ($q) use ($desde, $hasta) {
                            $q->where('c.fecha_inicio', '>=', $desde->fecha_inicial)
                                ->where('c.fecha_inicio', '<=', $hasta->fecha_final);
                        })
                        ->orWhere(function ($q) use ($desde, $hasta) {
                            $q->where('c.fecha_inicio', '<', $desde->fecha_inicial)
                                ->where('c.fecha_fin', '>', $hasta->fecha_final);
                        });
                })
                ->orderBy('p.nombre')
                ->get();
            $plantas_query = [];
            foreach ($plantas_ventas as $v)
                array_push($plantas_query, $v->id_planta);
            foreach ($plantas_ventas_bqt as $v)
                if (!in_array($v->id_planta, $plantas_query))
                    array_push($plantas_query, $v->id_planta);
            foreach ($plantas_area as $v)
                if (!in_array($v->id_planta, $plantas_query))
                    array_push($plantas_query, $v->id_planta);

            $plantas = DB::table('planta')
                ->select('nombre', 'id_planta')->distinct()
                ->where('estado', 1)
                ->whereIn('id_planta', $plantas_query)
                ->orderBy('nombre', 'asc')
                ->get();

            foreach ($plantas as $pos => $pta) {
                $pta = Planta::find($pta->id_planta);
                $valores = [];
                foreach ($semanas as $sem) {
                    $venta = DB::table('resumen_total_semanal_exportcalas as r')
                        ->join('variedad as v', 'v.id_variedad', '=', 'r.id_variedad')
                        ->select(DB::raw('sum(venta + venta_bouquetera) as cantidad'))
                        ->where('v.id_planta', $pta->id_planta)
                        ->where('r.id_empresa', $finca)
                        ->where('r.semana', $sem->codigo)
                        ->get()[0]->cantidad;

                    if ($pta->tipo == 'P' || $pta->tiene_ciclos == 1) { // tiene ciclos o es perenne
                        $area = DB::table('ciclo as c')
                            ->join('variedad as v', 'v.id_variedad', '=', 'c.id_variedad')
                            ->select(DB::raw('sum(c.area) as area'))
                            ->where('v.estado', 1)
                            ->where('c.estado', 1)
                            ->where('v.id_planta', $pta->id_planta)
                            ->where('c.id_empresa', $finca)
                            ->Where(function ($q) use ($sem) {
                                $q->where('fecha_fin', '>=', $sem->fecha_inicial)
                                    ->where('fecha_fin', '<=', $sem->fecha_final)
                                    ->orWhere(function ($q) use ($sem) {
                                        $q->where('fecha_inicio', '>=', $sem->fecha_inicial)
                                            ->where('fecha_inicio', '<=', $sem->fecha_final);
                                    })
                                    ->orWhere(function ($q) use ($sem) {
                                        $q->where('fecha_inicio', '<', $sem->fecha_inicial)
                                            ->where('fecha_fin', '>', $sem->fecha_final);
                                    });
                            })
                            ->get()[0]->area;
                    } else {
                        $area = DB::table('proy_no_perennes as proy')
                            ->join('semana as s', 's.id_semana', '=', 'proy.id_semana')
                            ->join('variedad as v', 'v.id_variedad', '=', 's.id_variedad')
                            ->select(DB::raw('sum(proy.area_produccion) as area_produccion'),
                                DB::raw('sum(proy.area_semana) as area_semana'))
                            ->where('s.codigo', $sem->codigo)
                            ->where('proy.id_empresa', $finca)
                            ->where('v.id_planta', $pta->id_planta)
                            ->get()[0]->area_produccion;
                    }

                    $valores[] = $area > 0 ? round($venta / $area, 2) : 0;
                }

                array_push($listado, [
                    'planta' => $pta,
                    'valores' => $valores,
                ]);
            }
        }
        return [
            'listado' => $listado,
            'semanas' => $semanas,
            'number_format' => 2,
        ];
    }

    /* ----------------------------------------------------------------------- */

    public function select_desglose_planta(Request $request)
    {
        $semanas = DB::table('semana')
            ->select('codigo', 'fecha_inicial', 'fecha_final')->distinct()
            ->where('codigo', '>=', $request->desde)
            ->where('codigo', '<=', $request->hasta)
            ->orderBy('codigo')
            ->get();

        if ($request->reporte == 1)
            $datos = $this->buscar_resumen_ebitda_var_area($semanas, $request->id_pta);
        if ($request->reporte == 2)
            $datos = $this->buscar_resumen_ebitda_var_tallos_cosechados($semanas, $request->id_pta);
        if ($request->reporte == 3)
            $datos = $this->buscar_resumen_ebitda_var_tallos_producidos($semanas, $request->id_pta);
        if ($request->reporte == 4)
            $datos = $this->buscar_resumen_ebitda_var_ventas($semanas, $request->id_pta);
        if ($request->reporte == 5)
            $datos = $this->buscar_resumen_ebitda_var_precio_x_tallo($semanas, $request->id_pta);
        if ($request->reporte == 6)
            $datos = $this->buscar_resumen_ebitda_var_tallo_m2($semanas, $request->id_pta);
        if ($request->reporte == 7)
            $datos = $this->buscar_resumen_ebitda_var_venta_m2($semanas, $request->id_pta);
        return $datos;
    }

    function buscar_resumen_ebitda_var_area($semanas, $pta)
    {
        $listado = [];
        if (count($semanas) > 0) {
            $finca = getFincaActiva();
            $desde = $semanas[0];
            $hasta = $semanas[count($semanas) - 1];

            $variedades = DB::table('ciclo as c')
                ->join('variedad as v', 'v.id_variedad', '=', 'c.id_variedad')
                ->select('c.id_variedad', 'v.nombre')->distinct()
                ->where('c.estado', 1)
                ->where('v.estado', 1)
                ->where('v.id_planta', $pta)
                ->where('c.id_empresa', $finca)
                ->Where(function ($q) use ($desde, $hasta) {
                    $q->where('c.fecha_fin', '>=', $desde->fecha_inicial)
                        ->where('c.fecha_fin', '<=', $hasta->fecha_final)
                        ->orWhere(function ($q) use ($desde, $hasta) {
                            $q->where('c.fecha_inicio', '>=', $desde->fecha_inicial)
                                ->where('c.fecha_inicio', '<=', $hasta->fecha_final);
                        })
                        ->orWhere(function ($q) use ($desde, $hasta) {
                            $q->where('c.fecha_inicio', '<', $desde->fecha_inicial)
                                ->where('c.fecha_fin', '>', $hasta->fecha_final);
                        });
                })
                ->orderBy('v.nombre')
                ->get();
            $pta = Planta::find($pta);
            foreach ($variedades as $pos => $var) {
                $valores = [];
                foreach ($semanas as $sem) {
                    if ($pta->tipo == 'P' || $pta->tiene_ciclos == 1) { // tiene ciclos o es perenne
                        $cant = DB::table('ciclo as c')
                            ->select(DB::raw('sum(c.area) as area'))
                            ->where('c.estado', 1)
                            ->where('c.id_variedad', $var->id_variedad)
                            ->where('c.id_empresa', $finca)
                            ->Where(function ($q) use ($sem) {
                                $q->where('fecha_fin', '>=', $sem->fecha_inicial)
                                    ->where('fecha_fin', '<=', $sem->fecha_final)
                                    ->orWhere(function ($q) use ($sem) {
                                        $q->where('fecha_inicio', '>=', $sem->fecha_inicial)
                                            ->where('fecha_inicio', '<=', $sem->fecha_final);
                                    })
                                    ->orWhere(function ($q) use ($sem) {
                                        $q->where('fecha_inicio', '<', $sem->fecha_inicial)
                                            ->where('fecha_fin', '>', $sem->fecha_final);
                                    });
                            })
                            ->get()[0]->area;
                    } else {
                        $cant = DB::table('proy_no_perennes as proy')
                            ->join('semana as s', 's.id_semana', '=', 'proy.id_semana')
                            ->select(DB::raw('sum(proy.area_produccion) as area_produccion'),
                                DB::raw('sum(proy.area_semana) as area_semana'))
                            ->where('s.codigo', $sem->codigo)
                            ->where('proy.id_empresa', $finca)
                            ->where('s.id_variedad', $var->id_variedad)
                            ->get()[0]->area_produccion;
                    }
                    $valores[] = $cant;
                }

                array_push($listado, [
                    'variedad' => $var,
                    'valores' => $valores,
                ]);
            }
        }
        return [
            'listado' => $listado,
            'semanas' => $semanas,
        ];
    }

    function buscar_resumen_ebitda_var_tallos_cosechados($semanas, $pta)
    {
        $listado = [];
        if (count($semanas) > 0) {
            $finca = getFincaActiva();
            $desde = $semanas[0];
            $hasta = $semanas[count($semanas) - 1];

            $variedades = DB::table('cosecha_diaria')
                ->select('id_variedad', 'variedad_nombre as nombre')->distinct()
                ->where('id_empresa', $finca)
                ->where('id_planta', $pta)
                ->where('cosechados', '>', 0)
                ->where('fecha', '>=', $desde->fecha_inicial)
                ->where('fecha', '<=', $hasta->fecha_final)
                ->orderBy('variedad_nombre')
                ->get();

            foreach ($variedades as $pos => $var) {
                $valores = [];
                foreach ($semanas as $sem) {
                    $cant = DB::table('cosecha_diaria')
                        ->select(DB::raw('sum(cosechados) as cantidad'))->distinct()
                        ->where('id_empresa', $finca)
                        ->where('id_variedad', $var->id_variedad)
                        ->where('fecha', '>=', $sem->fecha_inicial)
                        ->where('fecha', '<=', $sem->fecha_final)
                        ->get()[0]->cantidad;
                    $valores[] = $cant;
                }

                array_push($listado, [
                    'variedad' => $var,
                    'valores' => $valores,
                ]);
            }
        }
        return [
            'listado' => $listado,
            'semanas' => $semanas,
        ];
    }

    function buscar_resumen_ebitda_var_tallos_producidos($semanas, $pta)
    {
        $listado = [];
        if (count($semanas) > 0) {
            $finca = getFincaActiva();
            $fincas = [$finca];
            if ($finca == 2)
                array_push($fincas, -1);
            $desde = $semanas[0];
            $hasta = $semanas[count($semanas) - 1];

            $variedades_export = DB::table('resumen_total_semanal_exportcalas as r')
                ->join('variedad as v', 'v.id_variedad', '=', 'r.id_variedad')
                ->select('r.id_variedad')->distinct()
                ->where('v.estado', 1)
                ->where('r.id_empresa', $finca)
                ->where('v.id_planta', $pta)
                ->where('r.tallos_exportables', '>', 0)
                ->where('r.semana', '>=', $desde->codigo)
                ->where('r.semana', '<=', $hasta->codigo)
                ->get();
            $variedades_bqt = DB::table('bouquetera')
                ->select('id_variedad')->distinct()
                ->where('id_planta', $pta)
                ->where('fecha', '>=', $desde->fecha_inicial)
                ->where('fecha', '<=', $hasta->fecha_final)
                ->whereIn('id_empresa', $fincas)
                ->get();
            $variedades_query = [];
            foreach ($variedades_export as $v)
                array_push($variedades_query, $v->id_variedad);
            foreach ($variedades_bqt as $v)
                if (!in_array($v->id_variedad, $variedades_query))
                    array_push($variedades_query, $v->id_variedad);

            $variedades = DB::table('variedad')
                ->select('nombre', 'id_variedad')->distinct()
                ->where('estado', 1)
                ->whereIn('id_variedad', $variedades_query)
                ->orderBy('nombre', 'asc')
                ->get();

            foreach ($variedades as $pos => $var) {
                $exportables = [];
                $bouquetera = [];
                foreach ($semanas as $sem) {
                    $export = DB::table('resumen_total_semanal_exportcalas as r')
                        ->select(DB::raw('sum(tallos_exportables) as cantidad'))
                        ->where('r.id_variedad', $var->id_variedad)
                        ->where('r.id_empresa', $finca)
                        ->where('r.semana', '>=', $sem->codigo)
                        ->where('r.semana', '<=', $sem->codigo)
                        ->get()[0]->cantidad;
                    $exportables[] = $export;
                    $bqt = DB::table('bouquetera')
                        ->select(DB::raw('sum(tallos) as cantidad'))
                        ->where('id_variedad', $var->id_variedad)
                        ->where('fecha', '>=', $sem->fecha_inicial)
                        ->where('fecha', '<=', $sem->fecha_final)
                        ->whereIn('id_empresa', $fincas)
                        ->get()[0]->cantidad;
                    $bouquetera[] = $bqt;
                }

                array_push($listado, [
                    'variedad' => $var,
                    'valores1' => $exportables,
                    'valores2' => $bouquetera,
                ]);
            }
        }
        return [
            'listado' => $listado,
            'semanas' => $semanas,
        ];
    }

    function buscar_resumen_ebitda_var_ventas($semanas, $pta)
    {
        $listado = [];
        if (count($semanas) > 0) {
            $finca = getFincaActiva();
            $fincas = [$finca];
            if ($finca == 2)
                array_push($fincas, -1);
            $desde = $semanas[0];
            $hasta = $semanas[count($semanas) - 1];

            $variedades_venta = DB::table('resumen_total_semanal_exportcalas as r')
                ->join('variedad as v', 'v.id_variedad', '=', 'r.id_variedad')
                ->select('r.id_variedad')->distinct()
                ->where('v.estado', 1)
                ->where('r.id_empresa', $finca)
                ->where('v.id_planta', $pta)
                ->where('r.venta', '>', 0)
                ->where('r.semana', '>=', $desde->codigo)
                ->where('r.semana', '<=', $hasta->codigo)
                ->get();
            $variedades_bqt = DB::table('resumen_total_semanal_exportcalas as r')
                ->join('variedad as v', 'v.id_variedad', '=', 'r.id_variedad')
                ->select('r.id_variedad')->distinct()
                ->where('v.estado', 1)
                ->where('r.id_empresa', $finca)
                ->where('v.id_planta', $pta)
                ->where('r.venta_bouquetera', '>', 0)
                ->where('r.semana', '>=', $desde->codigo)
                ->where('r.semana', '<=', $hasta->codigo)
                ->get();
            $variedades_query = [];
            foreach ($variedades_venta as $v)
                array_push($variedades_query, $v->id_variedad);
            foreach ($variedades_bqt as $v)
                if (!in_array($v->id_variedad, $variedades_query))
                    array_push($variedades_query, $v->id_variedad);

            $variedades = DB::table('variedad')
                ->select('nombre', 'id_variedad')->distinct()
                ->where('estado', 1)
                ->whereIn('id_variedad', $variedades_query)
                ->orderBy('nombre', 'desc')
                ->get();

            foreach ($variedades as $pos => $var) {
                $normales = [];
                $bouquetera = [];
                foreach ($semanas as $sem) {
                    $export = DB::table('resumen_total_semanal_exportcalas as r')
                        ->select(DB::raw('sum(venta) as cantidad'))
                        ->where('r.id_variedad', $var->id_variedad)
                        ->where('r.id_empresa', $finca)
                        ->where('r.semana', $sem->codigo)
                        ->get()[0]->cantidad;
                    $normales[] = $export;
                    $bqt = DB::table('resumen_total_semanal_exportcalas as r')
                        ->select(DB::raw('sum(venta_bouquetera) as cantidad'))
                        ->where('r.id_variedad', $var->id_variedad)
                        ->where('r.id_empresa', $finca)
                        ->where('r.semana', $sem->codigo)
                        ->get()[0]->cantidad;
                    $bouquetera[] = $bqt;
                }

                array_push($listado, [
                    'variedad' => $var,
                    'valores1' => $normales,
                    'valores2' => $bouquetera,
                ]);
            }
        }
        return [
            'listado' => $listado,
            'semanas' => $semanas,
        ];
    }

    function buscar_resumen_ebitda_var_precio_x_tallo($semanas, $pta)
    {
        $listado = [];
        if (count($semanas) > 0) {
            $finca = getFincaActiva();
            $fincas = [$finca];
            if ($finca == 2)
                array_push($fincas, -1);
            $desde = $semanas[0];
            $hasta = $semanas[count($semanas) - 1];

            $variedades_export = DB::table('resumen_total_semanal_exportcalas as r')
                ->join('variedad as v', 'v.id_variedad', '=', 'r.id_variedad')
                ->select('r.id_variedad')->distinct()
                ->where('v.estado', 1)
                ->where('r.id_empresa', $finca)
                ->where('v.id_planta', $pta)
                ->where('r.tallos_exportables', '>', 0)
                ->where('r.semana', '>=', $desde->codigo)
                ->where('r.semana', '<=', $hasta->codigo)
                ->get();
            $variedades_bqt = DB::table('bouquetera')
                ->select('id_variedad')->distinct()
                ->where('id_planta', $pta)
                ->where('fecha', '>=', $desde->fecha_inicial)
                ->where('fecha', '<=', $hasta->fecha_final)
                ->whereIn('id_empresa', $fincas)
                ->get();
            $variedades_venta = DB::table('resumen_total_semanal_exportcalas as r')
                ->join('variedad as v', 'v.id_variedad', '=', 'r.id_variedad')
                ->select('r.id_variedad')->distinct()
                ->where('v.estado', 1)
                ->where('r.id_empresa', $finca)
                ->where('v.id_planta', $pta)
                ->where('r.venta', '>', 0)
                ->where('r.semana', '>=', $desde->codigo)
                ->where('r.semana', '<=', $hasta->codigo)
                ->get();
            $variedades_venta_bqt = DB::table('resumen_total_semanal_exportcalas as r')
                ->join('variedad as v', 'v.id_variedad', '=', 'r.id_variedad')
                ->select('r.id_variedad')->distinct()
                ->where('v.estado', 1)
                ->where('r.id_empresa', $finca)
                ->where('v.id_planta', $pta)
                ->where('r.venta_bouquetera', '>', 0)
                ->where('r.semana', '>=', $desde->codigo)
                ->where('r.semana', '<=', $hasta->codigo)
                ->get();
            $variedades_query = [];
            foreach ($variedades_export as $v)
                array_push($variedades_query, $v->id_variedad);
            foreach ($variedades_bqt as $v)
                if (!in_array($v->id_variedad, $variedades_query))
                    array_push($variedades_query, $v->id_variedad);
            foreach ($variedades_venta as $v)
                if (!in_array($v->id_variedad, $variedades_query))
                    array_push($variedades_query, $v->id_variedad);
            foreach ($variedades_venta_bqt as $v)
                if (!in_array($v->id_variedad, $variedades_query))
                    array_push($variedades_query, $v->id_variedad);

            $variedades = DB::table('variedad')
                ->select('nombre', 'id_variedad')->distinct()
                ->where('estado', 1)
                ->whereIn('id_variedad', $variedades_query)
                ->orderBy('nombre', 'asc')
                ->get();

            foreach ($variedades as $pos => $var) {
                $valores = [];
                foreach ($semanas as $sem) {
                    $export = DB::table('resumen_total_semanal_exportcalas as r')
                        ->select(DB::raw('sum(tallos_exportables) as cantidad'))
                        ->where('r.id_variedad', $var->id_variedad)
                        ->where('r.id_empresa', $finca)
                        ->where('r.semana', '>=', $sem->codigo)
                        ->where('r.semana', '<=', $sem->codigo)
                        ->get()[0]->cantidad;
                    $bqt = DB::table('bouquetera')
                        ->select(DB::raw('sum(tallos) as cantidad'))
                        ->where('id_variedad', $var->id_variedad)
                        ->where('fecha', '>=', $sem->fecha_inicial)
                        ->where('fecha', '<=', $sem->fecha_final)
                        ->whereIn('id_empresa', $fincas)
                        ->get()[0]->cantidad;
                    $tallos = $export + $bqt;
                    $venta = DB::table('resumen_total_semanal_exportcalas as r')
                        ->select(DB::raw('sum(venta + venta_bouquetera) as cantidad'))
                        ->where('r.id_variedad', $var->id_variedad)
                        ->where('r.id_empresa', $finca)
                        ->where('r.semana', $sem->codigo)
                        ->get()[0]->cantidad;
                    $valores[] = $tallos > 0 ? round($venta / $tallos, 2) : 0;
                }

                array_push($listado, [
                    'variedad' => $var,
                    'valores' => $valores,
                ]);
            }
        }
        return [
            'listado' => $listado,
            'semanas' => $semanas,
        ];
    }

    function buscar_resumen_ebitda_var_tallo_m2($semanas, $pta)
    {
        $listado = [];
        if (count($semanas) > 0) {
            $finca = getFincaActiva();
            $fincas = [$finca];
            if ($finca == 2)
                array_push($fincas, -1);
            $desde = $semanas[0];
            $hasta = $semanas[count($semanas) - 1];

            $variedades_export = DB::table('resumen_total_semanal_exportcalas as r')
                ->join('variedad as v', 'v.id_variedad', '=', 'r.id_variedad')
                ->select('r.id_variedad')->distinct()
                ->where('v.estado', 1)
                ->where('r.id_empresa', $finca)
                ->where('v.id_planta', $pta)
                ->where('r.tallos_exportables', '>', 0)
                ->where('r.semana', '>=', $desde->codigo)
                ->where('r.semana', '<=', $hasta->codigo)
                ->get();
            $variedades_bqt = DB::table('bouquetera')
                ->select('id_variedad')->distinct()
                ->where('id_planta', $pta)
                ->where('fecha', '>=', $desde->fecha_inicial)
                ->where('fecha', '<=', $hasta->fecha_final)
                ->whereIn('id_empresa', $fincas)
                ->get();
            $variedades_area = DB::table('ciclo as c')
                ->join('variedad as v', 'v.id_variedad', '=', 'c.id_variedad')
                ->select('c.id_variedad')->distinct()
                ->where('c.estado', 1)
                ->where('v.estado', 1)
                ->where('v.id_planta', $pta)
                ->where('c.id_empresa', $finca)
                ->Where(function ($q) use ($desde, $hasta) {
                    $q->where('c.fecha_fin', '>=', $desde->fecha_inicial)
                        ->where('c.fecha_fin', '<=', $hasta->fecha_final)
                        ->orWhere(function ($q) use ($desde, $hasta) {
                            $q->where('c.fecha_inicio', '>=', $desde->fecha_inicial)
                                ->where('c.fecha_inicio', '<=', $hasta->fecha_final);
                        })
                        ->orWhere(function ($q) use ($desde, $hasta) {
                            $q->where('c.fecha_inicio', '<', $desde->fecha_inicial)
                                ->where('c.fecha_fin', '>', $hasta->fecha_final);
                        });
                })
                ->orderBy('v.nombre')
                ->get();
            $variedades_query = [];
            foreach ($variedades_export as $v)
                array_push($variedades_query, $v->id_variedad);
            foreach ($variedades_bqt as $v)
                if (!in_array($v->id_variedad, $variedades_query))
                    array_push($variedades_query, $v->id_variedad);
            foreach ($variedades_area as $v)
                if (!in_array($v->id_variedad, $variedades_query))
                    array_push($variedades_query, $v->id_variedad);

            $variedades = DB::table('variedad')
                ->select('nombre', 'id_variedad')->distinct()
                ->where('estado', 1)
                ->whereIn('id_variedad', $variedades_query)
                ->orderBy('nombre', 'asc')
                ->get();

            $pta = Planta::find($pta);
            foreach ($variedades as $pos => $var) {
                $valores = [];
                foreach ($semanas as $sem) {
                    $tallos = DB::table('resumen_total_semanal_exportcalas as p')
                        ->select(DB::raw('sum(p.tallos_cosechados) as tallos_cosechados'))
                        ->where('p.id_variedad', $var->id_variedad)
                        ->where('p.id_empresa', $finca)
                        ->where('p.semana', '>=', substr($sem->codigo, 0, 2) . '01')
                        ->where('p.semana', '<=', $sem->codigo)
                        ->get()[0]->tallos_cosechados;

                    if ($pta->tipo == 'P' || $pta->tiene_ciclos == 1) { // tiene ciclos o es perenne
                        $area = DB::table('ciclo as c')
                            ->select(DB::raw('sum(c.area) as area'))
                            ->where('c.estado', 1)
                            ->where('c.id_variedad', $var->id_variedad)
                            ->where('c.id_empresa', $finca)
                            ->Where(function ($q) use ($sem) {
                                $q->where('fecha_fin', '>=', $sem->fecha_inicial)
                                    ->where('fecha_fin', '<=', $sem->fecha_final)
                                    ->orWhere(function ($q) use ($sem) {
                                        $q->where('fecha_inicio', '>=', $sem->fecha_inicial)
                                            ->where('fecha_inicio', '<=', $sem->fecha_final);
                                    })
                                    ->orWhere(function ($q) use ($sem) {
                                        $q->where('fecha_inicio', '<', $sem->fecha_inicial)
                                            ->where('fecha_fin', '>', $sem->fecha_final);
                                    });
                            })
                            ->get()[0]->area;
                    } else {
                        $area = DB::table('proy_no_perennes as proy')
                            ->join('semana as s', 's.id_semana', '=', 'proy.id_semana')
                            ->select(DB::raw('sum(proy.area_produccion) as area_produccion'),
                                DB::raw('sum(proy.area_semana) as area_semana'))
                            ->where('s.codigo', $sem->codigo)
                            ->where('proy.id_empresa', $finca)
                            ->where('s.id_variedad', $var->id_variedad)
                            ->get()[0]->area_semana;
                    }
                    $num_sem = intval(substr($sem->codigo, 2, 2));
                    $flor_m2_anno_52 = $area > 0 && $num_sem > 0 ? round((($tallos / $area) / $num_sem) * 52, 2) : 0;
                    $valores[] = $flor_m2_anno_52;
                }

                array_push($listado, [
                    'variedad' => $var,
                    'valores' => $valores,
                ]);
            }
        }
        return [
            'listado' => $listado,
            'semanas' => $semanas,
        ];
    }

    function buscar_resumen_ebitda_var_venta_m2($semanas, $pta)
    {
        $listado = [];
        if (count($semanas) > 0) {
            $finca = getFincaActiva();
            $fincas = [$finca];
            if ($finca == 2)
                array_push($fincas, -1);
            $desde = $semanas[0];
            $hasta = $semanas[count($semanas) - 1];

            $variedades_venta = DB::table('resumen_total_semanal_exportcalas as r')
                ->join('variedad as v', 'v.id_variedad', '=', 'r.id_variedad')
                ->select('r.id_variedad')->distinct()
                ->where('v.estado', 1)
                ->where('r.id_empresa', $finca)
                ->where('v.id_planta', $pta)
                ->where('r.venta', '>', 0)
                ->where('r.semana', '>=', $desde->codigo)
                ->where('r.semana', '<=', $hasta->codigo)
                ->get();
            $variedades_venta_bqt = DB::table('resumen_total_semanal_exportcalas as r')
                ->join('variedad as v', 'v.id_variedad', '=', 'r.id_variedad')
                ->select('r.id_variedad')->distinct()
                ->where('v.estado', 1)
                ->where('r.id_empresa', $finca)
                ->where('v.id_planta', $pta)
                ->where('r.venta_bouquetera', '>', 0)
                ->where('r.semana', '>=', $desde->codigo)
                ->where('r.semana', '<=', $hasta->codigo)
                ->get();
            $variedades_area = DB::table('ciclo as c')
                ->join('variedad as v', 'v.id_variedad', '=', 'c.id_variedad')
                ->select('c.id_variedad')->distinct()
                ->where('c.estado', 1)
                ->where('v.estado', 1)
                ->where('v.id_planta', $pta)
                ->where('c.id_empresa', $finca)
                ->Where(function ($q) use ($desde, $hasta) {
                    $q->where('c.fecha_fin', '>=', $desde->fecha_inicial)
                        ->where('c.fecha_fin', '<=', $hasta->fecha_final)
                        ->orWhere(function ($q) use ($desde, $hasta) {
                            $q->where('c.fecha_inicio', '>=', $desde->fecha_inicial)
                                ->where('c.fecha_inicio', '<=', $hasta->fecha_final);
                        })
                        ->orWhere(function ($q) use ($desde, $hasta) {
                            $q->where('c.fecha_inicio', '<', $desde->fecha_inicial)
                                ->where('c.fecha_fin', '>', $hasta->fecha_final);
                        });
                })
                ->orderBy('v.nombre')
                ->get();
            $variedades_query = [];
            foreach ($variedades_venta as $v)
                array_push($variedades_query, $v->id_variedad);
            foreach ($variedades_venta_bqt as $v)
                if (!in_array($v->id_variedad, $variedades_query))
                    array_push($variedades_query, $v->id_variedad);
            foreach ($variedades_area as $v)
                if (!in_array($v->id_variedad, $variedades_query))
                    array_push($variedades_query, $v->id_variedad);

            $variedades = DB::table('variedad')
                ->select('nombre', 'id_variedad')->distinct()
                ->where('estado', 1)
                ->whereIn('id_variedad', $variedades_query)
                ->orderBy('nombre', 'asc')
                ->get();

            $pta = Planta::find($pta);
            foreach ($variedades as $pos => $var) {
                $valores = [];
                foreach ($semanas as $sem) {
                    $venta = DB::table('resumen_total_semanal_exportcalas as r')
                        ->select(DB::raw('sum(venta + venta_bouquetera) as cantidad'))
                        ->where('r.id_variedad', $var->id_variedad)
                        ->where('r.id_empresa', $finca)
                        ->where('r.semana', $sem->codigo)
                        ->get()[0]->cantidad;
                    if ($pta->tipo == 'P' || $pta->tiene_ciclos == 1) { // tiene ciclos o es perenne
                        $area = DB::table('ciclo as c')
                            ->select(DB::raw('sum(c.area) as area'))
                            ->where('c.estado', 1)
                            ->where('c.id_variedad', $var->id_variedad)
                            ->where('c.id_empresa', $finca)
                            ->Where(function ($q) use ($sem) {
                                $q->where('fecha_fin', '>=', $sem->fecha_inicial)
                                    ->where('fecha_fin', '<=', $sem->fecha_final)
                                    ->orWhere(function ($q) use ($sem) {
                                        $q->where('fecha_inicio', '>=', $sem->fecha_inicial)
                                            ->where('fecha_inicio', '<=', $sem->fecha_final);
                                    })
                                    ->orWhere(function ($q) use ($sem) {
                                        $q->where('fecha_inicio', '<', $sem->fecha_inicial)
                                            ->where('fecha_fin', '>', $sem->fecha_final);
                                    });
                            })
                            ->get()[0]->area;
                    } else {
                        $area = DB::table('proy_no_perennes as proy')
                            ->join('semana as s', 's.id_semana', '=', 'proy.id_semana')
                            ->select(DB::raw('sum(proy.area_produccion) as area_produccion'),
                                DB::raw('sum(proy.area_semana) as area_semana'))
                            ->where('s.codigo', $sem->codigo)
                            ->where('proy.id_empresa', $finca)
                            ->where('s.id_variedad', $var->id_variedad)
                            ->get()[0]->area_produccion;
                    }
                    $valores[] = $area > 0 ? round($venta / $area, 2) : 0;
                }

                array_push($listado, [
                    'variedad' => $var,
                    'valores' => $valores,
                ]);
            }
        }
        return [
            'listado' => $listado,
            'semanas' => $semanas,
        ];
    }
}