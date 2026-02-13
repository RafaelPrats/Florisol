<?php

namespace yura\Http\Controllers\Proyecciones;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use yura\Http\Controllers\Controller;
use yura\Jobs\jobProyNoPerennes;
use yura\Modelos\Planta;
use yura\Modelos\Semana;
use yura\Modelos\SemanaEmpresa;
use yura\Modelos\Submenu;
use Validator;

class ProyeccionesController extends Controller
{
    public function inicio(Request $request)
    {
        $plantas = Planta::where('estado', 1)->where('tipo', 'N')->orderBy('nombre')->get();
        return view('adminlte.gestion.proyecciones.proyecciones.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'plantas' => $plantas,
            'annos' => DB::table('semana as s')
                ->select('s.anno')->distinct()
                ->where('s.estado', '=', 1)->orderBy('s.anno', 'desc')->get()
        ]);
    }

    public function listar_ingreso_proyecciones(Request $request)
    {
        $semanas = Semana::where('anno', '=', $request->anno)
            ->where('id_variedad', '=', $request->variedad)
            ->orderBy('codigo')
            ->get();
        return view('adminlte.gestion.proyecciones.proyecciones.partials.listado', [
            'semanas' => $semanas,
            'variedad' => getVariedad($request->variedad),
            'empresa' => getFincaActiva(),
        ]);
    }

    public function update_semana(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'sem' => 'required|',
            'curva_s' => 'required|max:250',
            'curva_p' => 'required|max:250',
            'plantas_iniciales_s' => 'required',
            'plantas_iniciales_p' => 'required',
            'densidad_s' => 'required',
            'densidad_p' => 'required',
        ], [
            'sem.required' => 'La semana es obligatoria',
            'curva_s.required' => 'La curva de siembra es obligatoria',
            'curva_p.required' => 'La curva de poda es obligatoria',
            'densidad_s.required' => 'La densidad de poda es obligatoria',
            'densidad_p.required' => 'La densidad de poda es obligatoria',
            'plantas_iniciales_s.required' => 'Las plantas iniciales de siembra son obligatorias',
            'plantas_iniciales_p.required' => 'Las plantas iniciales de poda son obligatorias',
            'semana.max' => 'La semana es muy grande',
        ]);
        if (!$valida->fails()) {
            $finca = getFincaActiva();
            $model = Semana::find($request->sem);
            $model->curva = str_limit(espacios($request->curva_s), 250);
            $model->curva_poda = str_limit(espacios($request->curva_p), 250);
            $model->tallos_planta_siembra = $request->tallos_x_pta_s;
            $model->tallos_planta_poda = $request->tallos_x_pta_p;
            $model->desecho = $request->desecho_s;
            $model->desecho_poda = $request->desecho_p;
            $model->semana_siembra = $request->semana_cosecha_s;
            $model->semana_poda = $request->semana_cosecha_p;

            if ($model->save()) {
                $success = true;
                $msg = '<div class="alert alert-success text-center">' .
                    '<p> Se ha actualizado la semana satisfactoriamente</p>'
                    . '</div>';
                bitacora('semana', $model->id_semana, 'U', 'Actualización satisfactoria de una semana');

                $getSemanaEmpresaS = $model->getSemanaEmpresa($finca, 'S');
                $getSemanaEmpresaP = $model->getSemanaEmpresa($finca, 'P');
                if (!isset($getSemanaEmpresaS)) {
                    $getSemanaEmpresaS = new SemanaEmpresa();
                    $getSemanaEmpresaS->id_semana = $model->id_semana;
                    $getSemanaEmpresaS->id_empresa = $finca;
                    $getSemanaEmpresaS->poda_siembra = 'S';
                    $getSemanaEmpresaS->plantas_iniciales = 0;
                    $getSemanaEmpresaS->densidad = 0;
                    $getSemanaEmpresaS->save();
                    $getSemanaEmpresaS = $model->getSemanaEmpresa($finca, 'S');
                }
                if (!isset($getSemanaEmpresaP)) {
                    $getSemanaEmpresaP = new SemanaEmpresa();
                    $getSemanaEmpresaP->id_semana = $model->id_semana;
                    $getSemanaEmpresaP->id_empresa = $finca;
                    $getSemanaEmpresaP->poda_siembra = 'P';
                    $getSemanaEmpresaP->plantas_iniciales = 0;
                    $getSemanaEmpresaP->densidad = 0;
                    $getSemanaEmpresaP->save();
                    $getSemanaEmpresaP = $model->getSemanaEmpresa($finca, 'P');
                }
                $getSemanaEmpresaS->plantas_iniciales = $request->plantas_iniciales_s;
                $getSemanaEmpresaP->plantas_iniciales = $request->plantas_iniciales_p;
                $getSemanaEmpresaS->densidad = $request->densidad_s;
                $getSemanaEmpresaP->densidad = $request->densidad_p;
                $getSemanaEmpresaS->porcent_bqt = $request->porcent_bqt_s;
                $getSemanaEmpresaP->porcent_bqt = $request->porcent_bqt_p;
                $getSemanaEmpresaS->porcent_exp = $request->porcent_exp_s;
                $getSemanaEmpresaP->porcent_exp = $request->porcent_exp_p;
                $getSemanaEmpresaS->save();
                $getSemanaEmpresaP->save();

                /* ======================== ACTUALIZAR LA TABLA PROY_NO_PERENNES ====================== */
                $last_semana = getLastSemanaByVariedad($model->id_variedad);
                $semanas = getSemanasByCodigosVariedad($model->codigo, $last_semana->codigo, $model->id_variedad);
                $queue = getQueueForProyNoPerenne($model->id_variedad);
                foreach ($semanas as $sem) {
                    jobProyNoPerennes::dispatch($model->id_variedad, $sem->codigo, $finca)
                        ->onQueue($queue);
                }
            } else {
                $success = false;
                $msg = '<div class="alert alert-warning text-center">' .
                    '<p> Ha ocurrido un problema al guardar la información al sistema</p>'
                    . '</div>';
            }
        } else {
            $success = false;
            $errores = '';
            foreach ($valida->errors()->all() as $mi_error) {
                if ($errores == '') {
                    $errores = '<li>' . $mi_error . '</li>';
                } else {
                    $errores .= '<li>' . $mi_error . '</li>';
                }
            }
            $msg = '<div class="alert alert-danger">' .
                '<p class="text-center">¡Por favor corrija los siguientes errores!</p>' .
                '<ul>' .
                $errores .
                '</ul>' .
                '</div>';
        }
        return [
            'mensaje' => $msg,
            'success' => $success
        ];
    }

    public function update_all_semanas(Request $request)
    {
        $finca = getFincaActiva();
        $success = true;
        $msg = '<div class="alert alert-success text-center">' .
            '<p> Se han actualizado las semanas satisfactoriamente</p>'
            . '</div>';
        $semana_menor = 9999;
        foreach ($request->data as $data) {
            $model = Semana::find($data['sem']);
            if ($data['curva_s'] != '' && $data['curva_p'] != '' && $data['plantas_iniciales_s'] >= 0 && $data['plantas_iniciales_p'] >= 0 && $data['densidad_s'] >= 0 && $data['densidad_p'] >= 0) {
                $model->curva = str_limit(espacios($data['curva_s']), 250);
                $model->curva_poda = str_limit(espacios($data['curva_p']), 250);
                $model->tallos_planta_siembra = $data['tallos_x_pta_s'];
                $model->tallos_planta_poda = $data['tallos_x_pta_p'];
                $model->desecho = $data['desecho_s'];
                $model->desecho_poda = $data['desecho_p'];
                $model->semana_siembra = $data['semana_cosecha_s'];
                $model->semana_poda = $data['semana_cosecha_p'];
                $model->save();

                $getSemanaEmpresaS = $model->getSemanaEmpresa($finca, 'S');
                $getSemanaEmpresaP = $model->getSemanaEmpresa($finca, 'P');
                if (!isset($getSemanaEmpresaS)) {
                    $getSemanaEmpresaS = new SemanaEmpresa();
                    $getSemanaEmpresaS->id_semana = $model->id_semana;
                    $getSemanaEmpresaS->id_empresa = $finca;
                    $getSemanaEmpresaS->poda_siembra = 'S';
                    $getSemanaEmpresaS->plantas_iniciales = 0;
                    $getSemanaEmpresaS->densidad = 0;
                    $getSemanaEmpresaS->save();
                    $getSemanaEmpresaS = $model->getSemanaEmpresa($finca, 'S');
                }
                if (!isset($getSemanaEmpresaP)) {
                    $getSemanaEmpresaP = new SemanaEmpresa();
                    $getSemanaEmpresaP->id_semana = $model->id_semana;
                    $getSemanaEmpresaP->id_empresa = $finca;
                    $getSemanaEmpresaP->poda_siembra = 'P';
                    $getSemanaEmpresaP->plantas_iniciales = 0;
                    $getSemanaEmpresaP->densidad = 0;
                    $getSemanaEmpresaP->save();
                    $getSemanaEmpresaP = $model->getSemanaEmpresa($finca, 'P');
                }
                $getSemanaEmpresaS->plantas_iniciales = $data['plantas_iniciales_s'];
                $getSemanaEmpresaP->plantas_iniciales = $data['plantas_iniciales_p'];
                $getSemanaEmpresaS->densidad = $data['densidad_s'];
                $getSemanaEmpresaP->densidad = $data['densidad_p'];
                $getSemanaEmpresaS->porcent_bqt = $data['porcent_bqt_s'];
                $getSemanaEmpresaP->porcent_bqt = $data['porcent_bqt_p'];
                $getSemanaEmpresaS->porcent_exp = $data['porcent_exp_s'];
                $getSemanaEmpresaP->porcent_exp = $data['porcent_exp_p'];
                $getSemanaEmpresaS->save();
                $getSemanaEmpresaP->save();
            } else {
                $success = false;
                $msg = '<div class="alert alert-danger text-center">' .
                    '<p>Faltan datos necesarios en la semana: ' . $model->codigo . '</p>'
                    . '</div>';
            }
            if ($semana_menor > $model->codigo)
                $semana_menor = $model->codigo;
        }

        /* ======================== ACTUALIZAR LA TABLA PROY_NO_PERENNES ====================== */
        $last_semana = getLastSemanaByVariedad($request->variedad);
        $semanas = getSemanasByCodigosVariedad($semana_menor, $last_semana->codigo, $request->variedad);
        $queue = getQueueForProyNoPerenne($request->variedad);
        foreach ($semanas as $sem) {
            jobProyNoPerennes::dispatch($request->variedad, $sem->codigo, $finca)
                ->onQueue($queue);
        }
        return [
            'mensaje' => $msg,
            'success' => $success
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

    public function generar_semanas(Request $request)
    {
        $semanas = DB::table('semana')
            ->select('codigo', 'fecha_inicial', 'fecha_final')->distinct()
            ->where('anno', $request->anno)
            ->get();
        if (count($semanas) > 0) {
            foreach ($semanas as $sem) {
                $model = DB::select("SELECT * FROM semana WHERE codigo = '" . $sem->codigo . "' AND id_variedad = " . $request->variedad . " LIMIT 1");
                if (count($model) == 0) {
                    $model = new Semana();
                    $model->id_variedad = $request->variedad;
                    $model->codigo = $sem->codigo;
                    $model->fecha_inicial = $sem->fecha_inicial;
                    $model->fecha_final = $sem->fecha_final;
                    $model->anno = $request->anno;
                    $model->save();
                }
            }
            $msg = '<div class="alert-success alert text-center">Se han generado las semanas correctamente</div>';
        } else
            $msg = '<div class="alert-warning alert text-center">No hay semanas para el año indicado</div>';
        return [
            'success' => true,
            'mensaje' => $msg,
        ];
    }

    public function refresh_jobs(Request $request)
    {
        $finca = getFincaActiva();
        $variedad = $request->variedad;
        $jobs = DB::table('jobs')
            ->where('queue', 'like', 'proy_no_perennes%')
            ->Where(function ($q) use ($variedad, $finca) {
                $q->where(
                    'payload',
                    'like',
                    '%jobProyNoPerennes%variedad%";i:' . $variedad . '%empresa%";i:' . $finca . '%'
                )
                    ->orWhere(function ($q) use ($variedad, $finca) {
                        $q->where(
                            'payload',
                            'like',
                            '%jobProyNoPerennes%variedad%";s:%:%"' . $variedad . '%empresa%";i:' . $finca . '%'
                        );
                    });
            })
            ->orderBy('fecha_registro')
            ->get();
        $en_proceso = 0;
        $en_espera = 0;
        foreach ($jobs as $j)
            if ($j->attempts > 0)
                $en_proceso++;
            else
                $en_espera++;

        return view('adminlte.gestion.proyecciones.proyecciones.partials._refresh_jobs', [
            'jobs' => $jobs,
            'en_proceso' => $en_proceso,
            'en_espera' => $en_espera,
        ]);
    }

    public function ejecutar_semana(Request $request)
    {
        $model = Semana::find($request->sem);
        $model->ejecutado = $model->ejecutado == 0 ? 1 : 0;
        $model->save();

        return [
            'success' => true,
            'mensaje' => 'Se ha <strong>GUARDADO</strong> la información',
        ];
    }
}
