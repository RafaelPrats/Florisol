<?php

namespace yura\Http\Controllers\Proyecciones;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use yura\Http\Controllers\Controller;
use yura\Jobs\jobActualizarProyeccion;
use yura\Jobs\jobActualizarSemProyPerenne;
use yura\Modelos\Planta;
use yura\Modelos\Semana;
use yura\Modelos\SemanaProyPerenne;
use yura\Modelos\Submenu;

class proyPerennesController extends Controller
{
    public function inicio(Request $request)
    {
        $finca = getFincaActiva();
        $plantas = DB::table('ciclo as c')
            ->join('variedad as v', 'v.id_variedad', '=', 'c.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->select('v.id_planta', 'p.nombre')->distinct()
            ->where('c.estado', 1)
            ->where('v.estado', 1)
            ->where('p.estado', 1)
            ->where('c.activo', 1)
            ->where('c.id_empresa', $finca)
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.proyecciones.perennes.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'plantas' => $plantas,
            'annos' => DB::table('semana as s')
                ->select('s.anno')->distinct()
                ->where('s.estado', '=', 1)
                ->orderBy('s.anno', 'desc')
                ->get()
        ]);
    }

    public function listar_proyecciones(Request $request)
    {
        $finca = getFincaActiva();
        $area = DB::table('ciclo as c')
            ->select(DB::raw('sum(c.area) as area'))
            ->where('c.estado', 1)
            ->where('c.activo', 1)
            ->where('c.id_variedad', $request->variedad)
            ->where('c.id_empresa', $finca)
            ->get()[0]->area;
        $area = $area > 0 ? $area : 0;
        $semanas = DB::table('semana as sem')
            ->leftJoin('semana_proy_perenne as p', 'p.id_semana', '=', 'sem.id_semana')
            ->select('sem.id_semana', 'sem.codigo', 'sem.fecha_inicial', 'sem.fecha_final',
                'p.curva as curva_perenne', 'p.proyectados', 'p.cosechados', 'p.cosechados_52_sem', 'p.porcentaje_cumplimiento', 'p.tallos_m2_ejecutado',
                'p.sum_ejec_4_sem', 'p.sum_ejec_13_sem', 'p.sum_ejec_52_sem',
                'p.proyectados_acum', 'p.cosechados_acum', 'p.porcentaje_cumplimiento_acum', 'p.tallos_m2_ejecutado_acum')
            ->where('sem.estado', 1)
            ->where('sem.id_variedad', $request->variedad)
            ->where('sem.anno', $request->anno)
            ->where('p.id_empresa', $finca)
            ->orderBy('sem.codigo')
            ->get();
        if (count($semanas) < 52) {
            $semanas = DB::table('semana as sem')
                ->select('sem.id_semana', 'sem.codigo', 'sem.fecha_inicial', 'sem.fecha_final', 'sem.curva_perenne')
                ->where('sem.estado', 1)
                ->where('sem.id_variedad', $request->variedad)
                ->where('sem.anno', $request->anno)
                ->orderBy('sem.codigo')
                ->get();
        }
        return view('adminlte.gestion.proyecciones.perennes.partials.listado', [
            'area' => $area,
            'semanas' => $semanas,
        ]);
    }

    public function update_semana(Request $request)
    {
        $finca = getFincaActiva();
        $semana = Semana::find($request->semana);
        $model = SemanaProyPerenne::All()
            ->where('id_semana', $request->semana)
            ->where('id_empresa', $finca)
            ->first();
        if ($model == '') {
            $model = new SemanaProyPerenne();
            $model->id_semana = $request->semana;
            $model->id_empresa = $finca;
        }
        $model->curva = $request->curva > 0 ? $request->curva : 0;
        $model->proyectados = ($request->curva > 0 && $request->area_total) ? round($request->area_total * $request->curva, 2) : 0;

        if ($model->save()) {
            $success = true;
            $msg = '';
            /* ---------------- ACTUALIZR SEMANA_PROYECCION_PERENNE ------------------- */
            $semanas = Semana::where('id_variedad', $semana->id_variedad)
                ->where('codigo', '>=', $semana->codigo)
                ->get();
            $queue = getQueueForProyPerenne($semana->id_variedad);
            foreach ($semanas as $sem)
                jobActualizarSemProyPerenne::dispatch($sem->codigo, $sem->id_variedad, $finca)->onQueue($queue);

            /* ---------------- ACTUALIZR PROYECCIONES ------------------- */
            //jobActualizarProyeccion::dispatch($semana->id_variedad, '', $semana, $finca)->onQueue('proy_cosecha');
        } else {
            $success = false;
            $msg = '<div class="alert alert-danger text-center">Ha ocurrido un problema al guardar la información</div>';
        }
        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }

    public function update_all_semanas(Request $request)
    {
        $finca = getFincaActiva();
        foreach ($request->data as $pos => $d) {
            $model = SemanaProyPerenne::All()
                ->where('id_semana', $d['semana'])
                ->where('id_empresa', $finca)
                ->first();
            if ($model == '') {
                $model = new SemanaProyPerenne();
                $model->id_semana = $d['semana'];
                $model->id_empresa = $finca;
            }
            $model->curva = $d['curva'] > 0 ? $d['curva'] : 0;
            $model->proyectados = ($d['curva'] > 0 && $request->area_total) ? round($request->area_total * $d['curva'], 2) : 0;
            if ($model->save()) {
                $success = true;
                $msg = '<div class="alert alert-success text-center">Se ha guardado la información satisfactoriamente</div>';
                if ($pos == 0) {
                    /* ---------------- ACTUALIZR SEMANA_PROYECCION_PERENNE ------------------- */
                    $semanas = Semana::where('id_variedad', $model->semana->id_variedad)
                        ->where('codigo', '>=', $model->semana->codigo)
                        ->get();
                    $queue = getQueueForProyPerenne($model->semana->id_variedad);
                    foreach ($semanas as $sem)
                        jobActualizarSemProyPerenne::dispatch($sem->codigo, $sem->id_variedad, $finca)->onQueue($queue);

                    /* ---------------- ACTUALIZR PROYECCIONES ------------------- */
                    //jobActualizarProyeccion::dispatch($model->semana->id_variedad, '', $model->semana, $finca)->onQueue('proy_cosecha');
                }
            } else {
                $success = false;
                $msg = '<div class="alert alert-danger text-center">Ha ocurrido un problema al guardar la información</div>';
                return [
                    'success' => $success,
                    'mensaje' => $msg,
                ];
            }
        }
        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }

    public function copiar_a_finca(Request $request)
    {
        $area = DB::table('ciclo as c')
            ->join('variedad as v', 'v.id_variedad', '=', 'c.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->select(DB::raw('sum(c.area) as area'))
            ->where('c.estado', 1)
            ->where('p.estado', 1)
            ->where('v.estado', 1)
            ->where('c.activo', 1)
            ->where('p.tipo', 'P')
            ->where('c.id_variedad', $request->variedad)
            ->where('c.id_empresa', $request->finca)
            ->get()[0]->area;
        $area = $area > 0 ? $area : 0;
        foreach ($request->data as $d) {
            $model = SemanaProyPerenne::All()
                ->where('id_semana', $d['semana'])
                ->where('id_empresa', $request->finca)
                ->first();
            if ($model == '') {
                $model = new SemanaProyPerenne();
                $model->id_semana = $d['semana'];
                $model->id_empresa = $request->finca;
            }
            $model->curva = $d['curva'] > 0 ? $d['curva'] : 0;
            $model->proyectados = ($d['curva'] > 0 && $area) ? round($area * $d['curva'], 2) : 0;
            if ($model->save()) {
                $success = true;
                $msg = '<div class="alert alert-success text-center">Se ha guardado la información satisfactoriamente</div>';
            } else {
                $success = false;
                $msg = '<div class="alert alert-danger text-center">Ha ocurrido un problema al guardar la información</div>';
                return [
                    'success' => $success,
                    'mensaje' => $msg,
                ];
            }
        }
        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }

    public function corregir_proy_sem_perenne(Request $request)
    {
        Artisan::call('comando:dev', [
            'comando' => 'corregir_proy_sem_perenne',
            'desde' => $request->anno,
            'hasta' => 0,
            'empresa' => getFincaActiva(),
            'variedad' => $request->variedad,
            'opcion' => 1,
        ]);
        return [
            'success' => true,
            'mensaje' => '<div class="alert alert-info text-center">Se han corregido las semanas correctamente</div>',
        ];
    }

    public function copiar_semanas(Request $request)
    {
        Artisan::call('comando:dev', [
            'comando' => 'copiar_semanas',
            'opcion' => 1,
            'desde' => $request->anno,
            'variedad' => $request->variedad,
        ]);
        return [
            'success' => true,
            'mensaje' => '<div class="alert-success alert text-center">Se han copiado las semanas correctamente</div>',
        ];
    }
}
