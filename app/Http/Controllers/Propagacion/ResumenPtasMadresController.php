<?php

namespace yura\Http\Controllers\Propagacion;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use yura\Http\Controllers\Controller;
use yura\Jobs\jobActualizarDisponibilidad;
use yura\Modelos\Planta;
use yura\Modelos\ResumenPropagacion;
use yura\Modelos\Submenu;
use yura\Modelos\Variedad;

class ResumenPtasMadresController extends Controller
{
    public function inicio(Request $request)
    {
        $finca = getFincaActiva();
        $semana_desde = getSemanaByDate(opDiasFecha('-', 42, date('Y-m-d')));
        $plantas = getPlantasPropag($finca);

        return view('adminlte.gestion.propagacion.resumen_ptas_madres.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'semana_desde' => $semana_desde,
            'plantas' => $plantas,
        ]);
    }

    public function listar_resumen(Request $request)
    {
        $semanas = DB::table('semana')
            ->select('codigo', 'fecha_inicial', 'fecha_final')->distinct()
            ->where('codigo', '>=', $request->desde)
            ->where('codigo', '<=', $request->hasta)
            ->orderBy('codigo')
            ->get();

        if ($request->reporte == 1) {    // esquejes cosechados
            $datos = $this->listar_resumen_pta_esquejes_cosechados($semanas);
            $view = 'listado';
        }
        if ($request->reporte == 2) {    // esquejes x planta
            $datos = $this->listar_resumen_pta_esquejes_x_planta($semanas);
            $view = 'listado_promedio';
        }
        if ($request->reporte == 3) {    // requerimientos
            $datos = $this->listar_resumen_pta_requerimientos($semanas);
            $view = 'listado';
        }
        if ($request->reporte == 4) {    // % requerimientos
            $datos = $this->listar_resumen_pta_porcentaje_requerimientos($semanas);
            $view = 'listado_promedio';
        }
        return view('adminlte.gestion.propagacion.resumen_ptas_madres.partials.' . $view, $datos);
    }

    function listar_resumen_pta_esquejes_cosechados($semanas)
    {
        $listado = [];
        if (count($semanas) > 0) {
            $finca = getFincaActiva();
            $desde = $semanas[0];
            $hasta = $semanas[count($semanas) - 1];

            $plantas = DB::table('cosecha_plantas_madres as cos')
                ->join('variedad as v', 'v.id_variedad', '=', 'cos.id_variedad')
                ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                ->select('v.id_planta', 'p.nombre')->distinct()
                ->where('cos.fecha', '>=', $desde->fecha_inicial)
                ->where('cos.fecha', '<=', $hasta->fecha_final)
                ->where('cos.id_empresa', $finca)
                ->orderBy('p.nombre')
                ->get();

            foreach ($plantas as $pos => $pta) {
                $valores = [];
                foreach ($semanas as $sem) {
                    $cant = DB::table('cosecha_plantas_madres as cos')
                        ->join('variedad as v', 'v.id_variedad', '=', 'cos.id_variedad')
                        ->select(DB::raw('sum(cantidad) as cantidad'))
                        ->where('cos.fecha', '>=', $sem->fecha_inicial)
                        ->where('cos.fecha', '<=', $sem->fecha_final)
                        ->where('cos.id_empresa', $finca)
                        ->where('v.id_planta', $pta->id_planta)
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
        ];
    }

    function listar_resumen_pta_esquejes_x_planta($semanas)
    {
        $listado = [];
        if (count($semanas) > 0) {
            $finca = getFincaActiva();
            $desde = $semanas[0];
            $hasta = $semanas[count($semanas) - 1];

            $plantas = DB::table('cosecha_plantas_madres as cos')
                ->join('variedad as v', 'v.id_variedad', '=', 'cos.id_variedad')
                ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                ->select('v.id_planta', 'p.nombre')->distinct()
                ->where('cos.fecha', '>=', $desde->fecha_inicial)
                ->where('cos.fecha', '<=', $hasta->fecha_final)
                ->where('cos.id_empresa', $finca)
                ->orderBy('p.nombre')
                ->get();

            foreach ($plantas as $pos => $pta) {
                $valores = [];
                foreach ($semanas as $sem) {
                    $cos = DB::table('cosecha_plantas_madres as cos')
                        ->join('variedad as v', 'v.id_variedad', '=', 'cos.id_variedad')
                        ->select(DB::raw('sum(cantidad) as cantidad'))
                        ->where('cos.fecha', '>=', $sem->fecha_inicial)
                        ->where('cos.fecha', '<=', $sem->fecha_final)
                        ->where('cos.id_empresa', $finca)
                        ->where('v.id_planta', $pta->id_planta)
                        ->get()[0]->cantidad;
                    $plantas_sembradas = DB::table('ciclo_cama_contenedor as ccc')
                        ->join('ciclo_cama as cc', 'cc.id_ciclo_cama', '=', 'ccc.id_ciclo_cama')
                        ->join('contenedor_propag as cp', 'cp.id_contenedor_propag', '=', 'ccc.id_contenedor_propag')
                        ->join('variedad as v', 'v.id_variedad', '=', 'cc.id_variedad')
                        ->select(DB::raw('sum(ccc.cantidad * cp.cantidad) as cantidad'))
                        ->where('v.id_planta', $pta->id_planta)
                        ->where('cc.id_empresa', $finca)
                        ->Where(function ($q) use ($desde, $hasta) {
                            $q->where('cc.fecha_fin', '>=', $desde->fecha_inicial)
                                ->where('cc.fecha_fin', '<=', $hasta->fecha_final)
                                ->orWhere(function ($q) use ($desde, $hasta) {
                                    $q->where('cc.fecha_inicio', '>=', $desde->fecha_inicial)
                                        ->where('cc.fecha_inicio', '<=', $hasta->fecha_final);
                                })
                                ->orWhere(function ($q) use ($desde, $hasta) {
                                    $q->where('cc.fecha_inicio', '<', $desde->fecha_inicial)
                                        ->where('cc.fecha_fin', '>', $hasta->fecha_final);
                                });
                        })
                        ->get()[0]->cantidad;
                    $valores[] = $plantas_sembradas > 0 ? round($cos / $plantas_sembradas, 2) : 0;
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
        ];
    }

    function listar_resumen_pta_requerimientos($semanas)
    {
        $listado = [];
        if (count($semanas) > 0) {
            $finca = getFincaActiva();
            $desde = $semanas[0];
            $hasta = $semanas[count($semanas) - 1];

            $plantas = DB::table('propag_disponibilidad as p')
                ->join('variedad as v', 'v.id_variedad', '=', 'p.id_variedad')
                ->join('planta as pta', 'pta.id_planta', '=', 'v.id_planta')
                ->select('v.id_planta', 'pta.nombre')->distinct()
                ->where('p.semana', '>=', $desde->codigo)
                ->where('p.semana', '<=', $hasta->codigo)
                ->where('p.id_empresa', $finca)
                ->where('p.requerimientos', '>', 0)
                ->orderBy('pta.nombre')
                ->get();

            foreach ($plantas as $pos => $pta) {
                $valores = [];
                foreach ($semanas as $sem) {
                    $cant = DB::table('propag_disponibilidad as p')
                        ->join('variedad as v', 'v.id_variedad', '=', 'p.id_variedad')
                        ->select(DB::raw('sum(p.requerimientos) as cantidad'))
                        ->where('p.semana', $sem->codigo)
                        ->where('p.id_empresa', $finca)
                        ->where('v.id_planta', $pta->id_planta)
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
        ];
    }

    function listar_resumen_pta_porcentaje_requerimientos($semanas)
    {
        $listado = [];
        if (count($semanas) > 0) {
            $finca = getFincaActiva();
            $desde = $semanas[0];
            $hasta = $semanas[count($semanas) - 1];

            $plantas = DB::table('propag_disponibilidad as p')
                ->join('variedad as v', 'v.id_variedad', '=', 'p.id_variedad')
                ->join('planta as pta', 'pta.id_planta', '=', 'v.id_planta')
                ->select('v.id_planta', 'pta.nombre')->distinct()
                ->where('p.semana', '>=', $desde->codigo)
                ->where('p.semana', '<=', $hasta->codigo)
                ->where('p.id_empresa', $finca)
                //->where('p.requerimientos', '>', 0)
                ->orderBy('pta.nombre')
                ->get();

            foreach ($plantas as $pos => $pta) {
                $valores = [];
                foreach ($semanas as $sem) {
                    $cant = DB::table('propag_disponibilidad as p')
                        ->join('variedad as v', 'v.id_variedad', '=', 'p.id_variedad')
                        ->select(DB::raw('avg(p.desecho) as cantidad'))
                        ->where('p.semana', $sem->codigo)
                        ->where('p.id_empresa', $finca)
                        ->where('v.id_planta', $pta->id_planta)
                        ->get()[0]->cantidad;
                    $valores[] = round(100 - $cant, 2);
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

        if ($request->reporte == 1)    // esquejes cosechados
            $datos = $this->listar_resumen_var_esquejes_cosechados($semanas, $request->id_pta);
        if ($request->reporte == 2)    // esquejes x planta
            $datos = $this->listar_resumen_var_esquejes_x_planta($semanas, $request->id_pta);
        if ($request->reporte == 3)    // requerimientos
            $datos = $this->listar_resumen_var_requerimientos($semanas, $request->id_pta);
        if ($request->reporte == 4)    // % requerimientos
            $datos = $this->listar_resumen_var_porcentaje_requerimientos($semanas, $request->id_pta);
        return $datos;
    }

    function listar_resumen_var_esquejes_cosechados($semanas, $pta)
    {
        $listado = [];
        if (count($semanas) > 0) {
            $finca = getFincaActiva();
            $desde = $semanas[0];
            $hasta = $semanas[count($semanas) - 1];

            $variedades = DB::table('cosecha_plantas_madres as cos')
                ->join('variedad as v', 'v.id_variedad', '=', 'cos.id_variedad')
                ->select('cos.id_variedad', 'v.nombre')->distinct()
                ->where('cos.fecha', '>=', $desde->fecha_inicial)
                ->where('cos.fecha', '<=', $hasta->fecha_final)
                ->where('cos.id_empresa', $finca)
                ->where('v.id_planta', $pta)
                ->orderBy('v.nombre', 'desc')
                ->get();

            foreach ($variedades as $pos => $var) {
                $valores = [];
                foreach ($semanas as $sem) {
                    $cant = DB::table('cosecha_plantas_madres as cos')
                        ->select(DB::raw('sum(cantidad) as cantidad'))
                        ->where('cos.fecha', '>=', $sem->fecha_inicial)
                        ->where('cos.fecha', '<=', $sem->fecha_final)
                        ->where('cos.id_empresa', $finca)
                        ->where('cos.id_variedad', $var->id_variedad)
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

    function listar_resumen_var_esquejes_x_planta($semanas, $pta)
    {
        $listado = [];
        if (count($semanas) > 0) {
            $finca = getFincaActiva();
            $desde = $semanas[0];
            $hasta = $semanas[count($semanas) - 1];

            $variedades = DB::table('cosecha_plantas_madres as cos')
                ->join('variedad as v', 'v.id_variedad', '=', 'cos.id_variedad')
                ->select('cos.id_variedad', 'v.nombre')->distinct()
                ->where('cos.fecha', '>=', $desde->fecha_inicial)
                ->where('cos.fecha', '<=', $hasta->fecha_final)
                ->where('cos.id_empresa', $finca)
                ->where('v.id_planta', $pta)
                ->orderBy('v.nombre', 'desc')
                ->get();

            foreach ($variedades as $pos => $var) {
                $valores = [];
                foreach ($semanas as $sem) {
                    $cos = DB::table('cosecha_plantas_madres as cos')
                        ->select(DB::raw('sum(cantidad) as cantidad'))
                        ->where('cos.fecha', '>=', $sem->fecha_inicial)
                        ->where('cos.fecha', '<=', $sem->fecha_final)
                        ->where('cos.id_empresa', $finca)
                        ->where('cos.id_variedad', $var->id_variedad)
                        ->get()[0]->cantidad;
                    $plantas_sembradas = DB::table('ciclo_cama_contenedor as ccc')
                        ->join('ciclo_cama as cc', 'cc.id_ciclo_cama', '=', 'ccc.id_ciclo_cama')
                        ->join('contenedor_propag as cp', 'cp.id_contenedor_propag', '=', 'ccc.id_contenedor_propag')
                        ->select(DB::raw('sum(ccc.cantidad * cp.cantidad) as cantidad'))
                        ->where('cc.id_variedad', $var->id_variedad)
                        ->where('cc.id_empresa', $finca)
                        ->Where(function ($q) use ($desde, $hasta) {
                            $q->where('cc.fecha_fin', '>=', $desde->fecha_inicial)
                                ->where('cc.fecha_fin', '<=', $hasta->fecha_final)
                                ->orWhere(function ($q) use ($desde, $hasta) {
                                    $q->where('cc.fecha_inicio', '>=', $desde->fecha_inicial)
                                        ->where('cc.fecha_inicio', '<=', $hasta->fecha_final);
                                })
                                ->orWhere(function ($q) use ($desde, $hasta) {
                                    $q->where('cc.fecha_inicio', '<', $desde->fecha_inicial)
                                        ->where('cc.fecha_fin', '>', $hasta->fecha_final);
                                });
                        })
                        ->get()[0]->cantidad;
                    $valores[] = $plantas_sembradas > 0 ? round($cos / $plantas_sembradas, 2) : 0;
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

    function listar_resumen_var_requerimientos($semanas, $pta)
    {
        $listado = [];
        if (count($semanas) > 0) {
            $finca = getFincaActiva();
            $desde = $semanas[0];
            $hasta = $semanas[count($semanas) - 1];

            $variedades = DB::table('propag_disponibilidad as p')
                ->join('variedad as v', 'v.id_variedad', '=', 'p.id_variedad')
                ->select('p.id_variedad', 'v.nombre')->distinct()
                ->where('p.semana', '>=', $desde->codigo)
                ->where('p.semana', '<=', $hasta->codigo)
                ->where('p.id_empresa', $finca)
                ->where('p.requerimientos', '>', 0)
                ->where('v.id_planta', $pta)
                ->orderBy('v.nombre')
                ->get();

            foreach ($variedades as $pos => $var) {
                $valores = [];
                foreach ($semanas as $sem) {
                    $cant = DB::table('propag_disponibilidad as p')
                        ->select(DB::raw('sum(p.requerimientos) as cantidad'))
                        ->where('p.semana', $sem->codigo)
                        ->where('p.id_empresa', $finca)
                        ->where('p.id_variedad', $var->id_variedad)
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

    function listar_resumen_var_porcentaje_requerimientos($semanas, $pta)
    {
        $listado = [];
        if (count($semanas) > 0) {
            $finca = getFincaActiva();
            $desde = $semanas[0];
            $hasta = $semanas[count($semanas) - 1];

            $variedades = DB::table('propag_disponibilidad as p')
                ->join('variedad as v', 'v.id_variedad', '=', 'p.id_variedad')
                ->select('p.id_variedad', 'v.nombre')->distinct()
                ->where('p.semana', '>=', $desde->codigo)
                ->where('p.semana', '<=', $hasta->codigo)
                ->where('p.id_empresa', $finca)
                //->where('p.requerimientos', '>', 0)
                ->where('v.id_planta', $pta)
                ->orderBy('v.nombre')
                ->get();

            foreach ($variedades as $pos => $var) {
                $valores = [];
                foreach ($semanas as $sem) {
                    $cant = DB::table('propag_disponibilidad as p')
                        ->select(DB::raw('avg(p.desecho) as cantidad'))
                        ->where('p.semana', $sem->codigo)
                        ->where('p.id_empresa', $finca)
                        ->where('p.id_variedad', $var->id_variedad)
                        ->get()[0]->cantidad;
                    $valores[] = round(100 - $cant, 2);
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

    /* ----------------------------------------------------------------------- */

    public function job_update_propag(Request $request)
    {
        /* ------------ ACTUALIZAR propag_disponibilidad ------------ */
        jobActualizarDisponibilidad::dispatch($request->desde, $request->hasta, $request->variedad, getFincaActiva())
            ->onQueue('propag');
        return [
            'success' => true,
            'mensaje' => '<div class="alert alert-info text-center">Se está procesando la información en segundo plano</div>',
        ];
    }
}