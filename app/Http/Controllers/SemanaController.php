<?php

namespace yura\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Validator;
use yura\Jobs\jobIgualarDatosSemanaAllVariedades;
use yura\Modelos\Ciclo;
use yura\Modelos\ProyeccionModulo;
use yura\Modelos\ProyeccionModuloSemana;
use yura\Modelos\Semana;
use yura\Modelos\SemanaEmpresa;
use yura\Modelos\Submenu;
use yura\Modelos\Variedad;

class SemanaController extends Controller
{
    public function inicio(Request $request)
    {
        return view('adminlte.gestion.semanas.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
        ]);
    }

    public function get_accion(Request $request)
    {
        if ($request->accion == 1) {    // filtrar
            return view('adminlte.gestion.semanas.partials.accion_filtrar', [
                'annos' => DB::table('semana as s')
                    ->select('s.anno')->distinct()
                    ->where('s.estado', '=', 1)->orderBy('s.anno')->get()
            ]);
        } else if ($request->accion == 2) { // procesar semanas
            return view('adminlte.gestion.semanas.partials.accion_procesar', [
            ]);
        } else if ($request->accion == 3) { // copiar semanas
            return view('adminlte.gestion.semanas.partials.accion_copiar', [
                'variedades' => Variedad::where('estado', '=', 1)->orderBy('nombre')->get(),
                'annos' => DB::table('semana as s')
                    ->select('s.anno')->distinct()
                    ->where('s.estado', '=', 1)->orderBy('s.anno', 'desc')->get()
            ]);
        }
    }

    public function procesar(Request $request)
    {
        $msg = '';
        $success = true;
        if (count(Semana::All()->where('anno', '=', $request->anno)
                ->where('id_variedad', '=', $request->id_variedad)) == 0) {
            if ($request->fecha_inicial < $request->fecha_final) {
                /* =========================== OBTENER LAS SEMANAS =======================*/
                $arreglo = [];
                $inicio = $request->fecha_inicial;
                $fin = strtotime('+6 day', strtotime($inicio));
                $fin = date('Y-m-d', $fin);

                array_push($arreglo, [
                    'inicio' => $inicio,
                    'fin' => $fin
                ]);

                $inicio = strtotime('+1 day', strtotime($fin));
                $inicio = date('Y-m-j', $inicio);

                while ($inicio < $request->fecha_final) {
                    if (existInSemana($inicio, $request->id_variedad, $request->anno) && existInSemana($fin, $request->id_variedad, $request->anno)) {
                        $fin = strtotime('+6 day', strtotime($inicio));
                        $fin = date('Y-m-d', $fin);

                        array_push($arreglo, [
                            'inicio' => $inicio,
                            'fin' => $fin
                        ]);

                        $inicio = strtotime('+1 day', strtotime($fin));
                        $inicio = date('Y-m-d', $inicio);
                    } else {
                        $success = false;
                        $msg = '<div class="text-center alert alert-danger">El rango indicado incluye al menos una fecha que ya está registrada</div>';
                        break;
                    }
                }
                /* =========================== VERIFICAR LA CANTIDAD DE SEMANAS EN UN AÑO =======================*/
                if (count($arreglo) >= 52 && count($arreglo) <= 53) {
                    /* =========================== GRABAR EN LA BASE LAS SEMANAS =======================*/
                    for ($i = 0; $i < count($arreglo); $i++) {
                        $model = new Semana();
                        $model->id_variedad = $request->id_variedad;
                        $model->anno = $request->anno;
                        $pref = ($i + 1) < 10 ? '0' : '';
                        $model->codigo = substr($request->anno, 2) . $pref . ($i + 1);
                        $model->fecha_inicial = $arreglo[$i]['inicio'];
                        $model->fecha_final = $arreglo[$i]['fin'];
                        if ($model->save()) {
                            $model = Semana::All()->last();
                            bitacora('semana', $model->id_semana, 'I', 'Inserción satisfactoria de una semana');
                        } else {
                            $success = false;
                            $msg .= '<div class="text-center alert alert-danger">' .
                                'Ha ocurrido un problema al guardar la información de la semana ' . $model->codigo .
                                '</div>';
                        }
                    }
                } else {
                    $success = false;
                    $msg = '<div class="text-center alert alert-danger">No se ha cumplido el rango de 52-53 semanas de un año en el rango indicado</div>';
                }
            } else {
                $success = false;
                $msg = '<div class="text-center alert alert-danger">La fecha inicial debe ser menor que la final</div>';
            }
        } else {
            $success = false;
            $msg = '<div class="text-center alert alert-danger">Ya existe una programación para esta variedad en el año ' . $request->anno . '</div>';
        }
        if ($success)
            $msg = '<div class="text-center alert alert-success">Sa han procesado correctamente las semanas</div>';
        return [
            'mensaje' => $msg,
            'success' => $success
        ];
    }

    public function listar_semanas(Request $request)
    {
        $r = Semana::where('anno', '=', $request->anno)
            ->where('id_variedad', '=', $request->id_variedad)
            ->orderBy('codigo')
            ->get();
        return view('adminlte.gestion.semanas.partials.listado', [
            'semanas' => $r,
            'variedad' => getVariedad($request->id_variedad),
            'empresa' => getFincaActiva(),
        ]);
    }

    public function update_semana(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'curva' => 'required',
            'desecho' => 'required|max:2|',
            'semana_poda' => 'required|max:2|',
            'semana_siembra' => 'required|max:2|',
            'id_semana' => 'required|',
            'tallos_planta_siembra' => 'required|',
            'tallos_planta_poda' => 'required|',
            'tallos_ramo_siembra' => 'required|',
            'tallos_ramo_poda' => 'required|',
        ], [
            'id_semana.required' => 'La semana es obligatoria',
            'semana_siembra.required' => 'La semana de inicio de siembra es obligatoria',
            'semana_siembra.max' => 'La semana de inicio de siembra es muy grande',
            'semana_poda.required' => 'La semana de inicio de poda es obligatoria',
            'semana_poda.max' => 'La semana de inicio de poda es muy grande',
            'desecho.required' => 'El porcentaje de desecho es obligatorio',
            'desecho.max' => 'El porcentaje de desecho es muy grande',
            'curva.required' => 'La curva es obligatoria',
        ]);
        if (!$valida->fails()) {
            $model = Semana::find($request->id_semana);
            $model->curva = str_limit(strtoupper(espacios($request->curva)), 250);
            $model->desecho = $request->desecho;
            $model->semana_poda = $request->semana_poda;
            $model->semana_siembra = $request->semana_siembra;
            $model->tallos_planta_siembra = $request->tallos_planta_siembra;
            $model->tallos_planta_poda = $request->tallos_planta_poda;
            $model->tallos_ramo_siembra = $request->tallos_ramo_siembra;
            $model->tallos_ramo_poda = $request->tallos_ramo_poda;
            $model->porcent_bqt = $request->porcent_bqt;
            $model->porcent_export = $request->porcent_export;

            $finca = getFincaActiva();
            $se = $model->getSemanaEmpresa($finca);
            if ($se == '') {
                $se = new SemanaEmpresa();
                $se->id_semana = $model->id_semana;
                $se->id_empresa = $finca;
            }
            $se->plantas_iniciales = $request->plantas_iniciales;
            $se->densidad = $request->densidad;
            $se->save();

            if ($model->save()) {
                $success = true;
                $msg = '<div class="alert alert-success text-center">' .
                    '<p>Se ha actualizado la semana satisfactoriamente</p>'
                    . '</div>';
                bitacora('semana', $model->id_semana, 'U', 'Actualización satisfactoria de una semana');

                /* =================== ACTUALIZAR PROYECCION_MODULO_SEMANA ================== */
                self::actualizar_proyecciones($model);
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

    public function update_semanas(Request $request)
    {
        foreach ($request->data as $d) {
            $model = Semana::find($d['id_semana']);
            $model->curva = str_limit(strtoupper(espacios($d['curva'])), 250);
            $model->desecho = $d['desecho'];
            $model->semana_poda = $d['semana_poda'];
            $model->semana_siembra = $d['semana_siembra'];
            $model->tallos_planta_siembra = $d['tallos_planta_siembra'];
            $model->tallos_planta_poda = $d['tallos_planta_poda'];
            $model->tallos_ramo_siembra = $d['tallos_ramo_siembra'];
            $model->tallos_ramo_poda = $d['tallos_ramo_poda'];
            $model->tallos_ramo_poda = $d['tallos_ramo_poda'];
            $model->porcent_bqt = $d['porcent_bqt'];
            $model->porcent_export = $d['porcent_export'];

            $finca = getFincaActiva();
            $se = $model->getSemanaEmpresa($finca);
            if ($se == '') {
                $se = new SemanaEmpresa();
                $se->id_semana = $model->id_semana;
                $se->id_empresa = $finca;
            }
            $se->plantas_iniciales = $d['plantas_iniciales'];
            $se->densidad = $d['densidad'];
            $se->save();

            $model->save();
        }
        return [
            'success' => true,
            'mensaje' => '<div class="alert alert-success text-center">' .
                '<p> Se han actualizado las semanas satisfactoriamente</p>'
                . '</div>',
        ];
    }

    public function igualar_datos(Request $request)
    {
        return view('adminlte.gestion.semanas.forms.igualar_datos', [
            'curva' => $request->curva,
            'desecho' => $request->desecho,
            'semana_poda' => $request->semana_poda,
            'semana_siembra' => $request->semana_siembra,
            'tallos_planta_siembra' => $request->tallos_planta_siembra,
            'tallos_planta_poda' => $request->tallos_planta_poda,
            'tallos_ramo_siembra' => $request->tallos_ramo_siembra,
            'tallos_ramo_poda' => $request->tallos_ramo_poda,
            'plantas_iniciales' => $request->plantas_iniciales,
            'densidad' => $request->densidad,
            'porcent_bqt' => $request->porcent_bqt,
            'porcent_export' => $request->porcent_export,
            'selection' => $request->selection,
        ]);
    }

    public function store_igualar_datos(Request $request)
    {
        $success = true;
        $msg = '';
        $valida = Validator::make($request->all(), [
            'ids' => 'required|',
        ], [
            'ids.required' => 'Al menos seleccione una semana',
        ]);
        if (!$valida->fails()) {
            foreach ($request->ids as $id) {
                $model = Semana::find($id);
                if ($request->curva != null)
                    $model->curva = str_limit(strtoupper(espacios($request->curva)), 250);
                if ($request->desecho != null)
                    $model->desecho = str_limit(strtoupper(espacios($request->desecho)), 2);
                if ($request->semana_poda != null)
                    $model->semana_poda = str_limit(strtoupper(espacios($request->semana_poda)), 2);
                if ($request->semana_siembra != null)
                    $model->semana_siembra = str_limit(strtoupper(espacios($request->semana_siembra)), 2);
                if ($request->tallos_planta_siembra != null)
                    $model->tallos_planta_siembra = $request->tallos_planta_siembra;
                if ($request->tallos_planta_poda != null)
                    $model->tallos_planta_poda = $request->tallos_planta_poda;
                if ($request->tallos_ramo_siembra != null)
                    $model->tallos_ramo_siembra = $request->tallos_ramo_siembra;
                if ($request->tallos_ramo_poda != null)
                    $model->tallos_ramo_poda = $request->tallos_ramo_poda;
                if ($request->plantas_iniciales != null) {
                    $finca = getFincaActiva();
                    $se = $model->getSemanaEmpresa($finca);
                    if ($se == '') {
                        $se = new SemanaEmpresa();
                        $se->id_semana = $model->id_semana;
                        $se->id_empresa = $finca;
                    }
                    $se->plantas_iniciales = $request->plantas_iniciales;
                    $se->save();
                }
                if ($request->densidad != null) {
                    $finca = getFincaActiva();
                    $se = $model->getSemanaEmpresa($finca);
                    if ($se == '') {
                        $se = new SemanaEmpresa();
                        $se->id_semana = $model->id_semana;
                        $se->id_empresa = $finca;
                    }
                    $se->densidad = $request->densidad;
                    $se->save();
                }
                if ($request->porcent_bqt != null)
                    $model->porcent_bqt = $request->porcent_bqt;
                if ($request->porcent_export != null)
                    $model->porcent_export = $request->porcent_export;

                if ($model->save()) {
                    $msg .= '<div class="alert alert-success text-center">' .
                        '<p> Se ha actualizado la semana ' . $model->codigo . ' satisfactoriamente</p>'
                        . '</div>';
                    bitacora('semana', $model->id_semana, 'U', 'Actualización satisfactoria de una semana');
                } else {
                    $success = false;
                    $msg = '<div class="alert alert-warning text-center">' .
                        '<p> Ha ocurrido un problema al guardar la información al sistema</p>'
                        . '</div>';
                }
            }
            /* ================ OTRAS VARIEDADES ================= */
            if ($request->variedades == 'true') {
                jobIgualarDatosSemanaAllVariedades::dispatch($request->all())->onQueue('datos_proyeccion');
                $msg = '<div class="alert alert-success text-center">' .
                    '<p> Se han actualizado las semanas señaladas, y se estarán procesando las semanas correspondientes a las demás variedades de la misma planta</p>'
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

    public function copiar_semanas(Request $request)
    {
        $semanas = Semana::where('estado', 1)
            ->where('id_variedad', $request->variedad)
            ->where('anno', $request->anno)
            ->get();
        if (count($semanas) > 0) {
            foreach (getVariedades() as $var) {
                $sem_var = Semana::where('estado', 1)
                    ->where('id_variedad', $var->id_variedad)
                    ->where('anno', $request->anno)
                    ->get();
                if (count($sem_var) == 0)
                    foreach ($semanas as $sem) {
                        $new = new Semana();
                        $new->id_variedad = $var->id_variedad;
                        $new->anno = $sem->anno;
                        $new->codigo = $sem->codigo;
                        $new->fecha_inicial = $sem->fecha_inicial;
                        $new->fecha_final = $sem->fecha_final;
                        $new->curva = $sem->curva;
                        $new->desecho = $sem->desecho;
                        $new->semana_poda = $sem->semana_poda;
                        $new->semana_siembra = $sem->semana_siembra;
                        $new->tallos_planta_siembra = $sem->tallos_planta_siembra;
                        $new->tallos_planta_poda = $sem->tallos_planta_poda;
                        $new->tallos_ramo_siembra = $sem->tallos_ramo_siembra;
                        $new->tallos_ramo_poda = $sem->tallos_ramo_poda;
                        $new->plantas_iniciales = $sem->plantas_iniciales;
                        $new->densidad = $sem->densidad;
                        $new->porcent_bqt = $sem->porcent_bqt;
                        $new->porcent_export = $sem->porcent_export;
                        $new->mes = $sem->mes;

                        $new->save();
                        $new = Semana::All()->last();

                        $finca = getFincaActiva();
                        $se = $sem->getSemanaEmpresa($finca);
                        if ($se != '') {
                            $new_se = new SemanaEmpresa();
                            $new_se->id_semana = $new->id_semana;
                            $new_se->id_empresa = $finca;
                            $new_se->plantas_iniciales = $se->plantas_iniciales;
                            $new_se->densidad = $se->densidad;
                            $new_se->save();
                        }
                    }
            }
            $success = true;
            $msg = '<div class="alert alert-success text-center">Se han copiado las semanas satisfactoriamente</div>';
        } else {
            $success = false;
            $msg = '<div class="alert alert-danger text-center">La variedad no tiene semanas ingresadas para el año indicado</div>';
        }
        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }

    /* ---------------------------------------------------------------- */
    static function actualizar_proyecciones($semana)
    {
        $ini = date('Y-m-d H:i:s');

        $semanas = DB::select("SELECT distinct codigo, fecha_inicial, fecha_final 
                    FROM semana 
                    WHERE semana_guia = 1 and estado = 1 AND codigo >= '" . $semana->codigo . "'");

        $ciclos = Ciclo::join('modulo as m', 'm.id_modulo', '=', 'ciclo.id_modulo')
            ->select('ciclo.*')->distinct()
            ->where('ciclo.fecha_inicio', '>=', $semana->fecha_inicial)
            ->where('ciclo.fecha_inicio', '<=', $semana->fecha_final)
            ->where('ciclo.id_variedad', $semana->id_variedad)
            ->where('ciclo.activo', 1)
            ->where('ciclo.no_recalcular_curva', 0)
            ->where('ciclo.mantener_valores', 0)
            ->where('m.proyectar_semanal', $semana->variedad->proyectar_semanal)
            ->get();
        foreach ($ciclos as $c) {
            $semana_cosecha = $c->poda_siembra == 'P' ? $semana->semana_poda : $semana->semana_siembra;
            $tallos_planta = $c->conteo > 0 ? $c->conteo : $semana->tallos_planta_poda;
            $tallos_ramo = $c->poda_siembra == 'P' ? $semana->tallos_ramo_poda : $semana->tallos_ramo_siembra;
            $semanas_curva = count(explode('-', $semana->curva));
            $getPodaSiembraByCiclo = $c->modulo->getPodaSiembraByCiclo($c->id_ciclo);
            $proyecciones_ciclo = ProyeccionModuloSemana::where('id_modulo', $c->id_modulo)
                ->where('id_variedad', $semana->id_variedad)
                ->where('semana', '>=', $semana->codigo);
            $next_proy = ProyeccionModulo::All()
                ->where('id_modulo', $c->id_modulo)
                ->where('id_variedad', $c->id_variedad)
                ->first();
            if ($next_proy != '') {
                $cant_semanas_ciclo = $semana_cosecha + $semanas_curva - 1;
                $semana_next_proy = getSemanaByDateVariedad(opDiasFecha('+', $cant_semanas_ciclo * 7, $c->fecha_inicio), $c->id_variedad);
                $next_proy->id_semana = $semana_next_proy->id_semana;
                $next_proy->save();
            }
            $proyecciones_ciclo->delete();
            $array_semanas_prog = [];
            $pos_cosecha = 0;
            foreach ($semanas as $pos_sem => $sem) {
                $sem_actual = $pos_sem + 1;

                $proy = new ProyeccionModuloSemana();
                $proy->id_modulo = $c->id_modulo;
                $proy->id_variedad = $c->id_variedad;
                $proy->semana = $sem->codigo;
                $proy->plantas_iniciales = $c->plantas_iniciales;
                $proy->plantas_actuales = $c->plantas_actuales();
                $proy->fecha_inicio = $c->fecha_inicio;
                $proy->activo = 1;
                $proy->area = $c->area;
                $proy->tallos_planta = $tallos_planta;
                $proy->tallos_ramo = $tallos_ramo;
                $proy->curva = $semana->curva;
                $proy->poda_siembra = $c->poda_siembra;
                $proy->semana_poda_siembra = $semana_cosecha;
                $proy->desecho = $semana->desecho;
                $proy->tabla = 'C';
                $proy->modelo = $c->id_ciclo;
                $proy->cosechados = DB::table('desglose_recepcion as dr')
                    ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
                    ->select(DB::raw('sum(dr.cantidad_mallas * dr.tallos_x_malla) as cantidad'))
                    ->where('dr.id_variedad', $c->id_variedad)
                    ->where('dr.id_modulo', $c->id_modulo)
                    ->where('r.fecha_ingreso', '>=', $sem->fecha_inicial)
                    ->where('r.fecha_ingreso', '<=', $sem->fecha_final)
                    ->get()[0]->cantidad;

                if ($sem_actual == 1) {    // primera semana de ciclo
                    $proy->tipo = $c->poda_siembra;
                    $proy->info = $c->poda_siembra . '-' . $getPodaSiembraByCiclo;
                    $proy->save();
                } elseif ($sem_actual < $semana_cosecha) {   // semana info antes de inicio de cosecha
                    $proy->tipo = 'I';
                    $proy->info = $sem_actual . 'º';
                    $proy->save();
                } elseif ($sem_actual >= $semana_cosecha && $sem_actual <= $semana_cosecha + $semanas_curva - 1) {  // semanas de cosecha-curva
                    $proy->tipo = 'T';
                    $proy->info = $sem_actual . 'º';
                    $total = $proy->plantas_actuales * $tallos_planta;
                    $total = $total * ((100 - $proy->desecho) / 100);
                    $proy->proyectados = round($total * (explode('-', $proy->curva)[$pos_cosecha] / 100), 2);
                    $pos_cosecha++;
                    $proy->save();
                } else {    // semanas despues del ciclo
                    if ($next_proy != '' && $sem->codigo < $next_proy->semana->codigo) { // semanas antes de la programacion
                        $proy->tipo = 'F';
                        $proy->info = '-';
                        $proy->save();
                    } else {
                        array_push($array_semanas_prog, $sem);
                    }
                }
            }

            /* ------------ Programacion siguiente --------------- */
            $pos_cosecha = 0;
            foreach ($array_semanas_prog as $pos_sem => $sem) {
                $proy = new ProyeccionModuloSemana();
                $proy->id_modulo = $c->id_modulo;
                $proy->id_variedad = $c->id_variedad;
                $proy->semana = $sem->codigo;

                if ($next_proy != '') { // tiene siguiente proyeccion

                    $sem_actual = $pos_sem + 1;

                    $proy->plantas_iniciales = $c->plantas_iniciales;
                    $proy->plantas_actuales = $c->plantas_iniciales;
                    $proy->fecha_inicio = $c->fecha_inicio;
                    $proy->activo = 1;
                    $proy->area = $c->area;
                    $proy->tallos_planta = $tallos_planta;
                    $proy->tallos_ramo = $tallos_ramo;
                    $proy->curva = $semana->curva;
                    $proy->poda_siembra = $next_proy->poda_siembra;
                    $proy->semana_poda_siembra = $semana_cosecha;
                    $proy->desecho = $semana->desecho;
                    $proy->tabla = 'P';
                    $proy->modelo = $next_proy->id_proyeccion_modulo;
                    $proy->cosechados = 0;

                    if ($sem_actual == 1) {    // primera semana de programacion
                        $proy->tipo = 'Y';
                        $proy->info = $next_proy->tipo;
                        $proy->save();
                    } elseif ($sem_actual < $semana_cosecha) {   // semana info antes de inicio de cosecha
                        $proy->tipo = 'I';
                        $proy->info = $sem_actual . 'º';
                        $proy->save();
                    } elseif ($sem_actual >= $semana_cosecha && $sem_actual <= $semana_cosecha + $semanas_curva - 1) {  // semanas de cosecha-curva
                        $proy->tipo = 'T';
                        $proy->info = $sem_actual . 'º';
                        $total = $proy->plantas_actuales * $tallos_planta;
                        $total = $total * ((100 - $proy->desecho) / 100);
                        $proy->proyectados = round($total * (explode('-', $proy->curva)[$pos_cosecha] / 100), 2);
                        $pos_cosecha++;
                        $proy->save();
                    } else {    // semanas despues de la programacion
                        $proy->tipo = 'F';
                        $proy->info = '-';
                        $proy->save();
                    }
                } else {    // no hay mas programacion hacia adelante
                    $proy->tipo = 'F';
                    $proy->info = '-';
                    $proy->save();
                }
            }

            $c->curva = $semana->curva;
            $c->semana_poda_siembra = $semana_cosecha;
            $c->desecho = $semana->desecho;
            $c->save();
        }
        $time_duration = difFechas(date('Y-m-d H:i:s'), $ini)->h . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->m . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->s;
    }

    public function actualizar_proyecciones_by_semanas(Request $request)
    {
        foreach ($request->semanas as $id) {
            $semana = Semana::find($id);
            self::actualizar_proyecciones($semana);
        }
        return [
            'success' => true,
            'mensaje' => '<div class="alert alert-info text-center">Se han actualizado los ciclos satisfactoriamente</div>',
        ];
    }

    public function actualizar_siembras_by_semanas(Request $request)
    {
        $finca = getFincaActiva();
        foreach ($request->semanas as $id) {
            $semana = Semana::find($id);
            Artisan::call('cron:generar_proy_futuras', [
                'variedad' => $semana->id_variedad,
                'desde' => $semana->codigo,
                'hasta' => $semana->codigo,
                'empresa' => $finca,
                'dev' => 0,
            ]);
        }
        return [
            'success' => true,
            'mensaje' => '<div class="alert alert-info text-center">Se han actualizado las siembras satisfactoriamente</div>',
        ];
    }
}