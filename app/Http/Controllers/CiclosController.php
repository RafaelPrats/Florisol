<?php

namespace yura\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use yura\Jobs\jobActualizarFenogramaEjecucion;
use yura\Jobs\jobCorregirProyeccionModuloSemana;
use yura\Jobs\jobResumenAreaSemanal;
use yura\Jobs\jobUpdateProyeccionUpdateSemana;
use yura\Jobs\ProyeccionUpdateSemanal;
use yura\Modelos\Ciclo;
use yura\Modelos\Cosecha;
use yura\Modelos\Modulo;
use yura\Modelos\ProyeccionModulo;
use yura\Modelos\Sector;
use Validator;
use yura\Modelos\Semana;

class CiclosController extends Controller
{
    public function listar_ciclos(Request $request)
    {
        $finca = getFincaActiva();
        if ($request->tipo == 0) {  // inactivos
            $view = 'buscar_inactivos';
            $datos = [
                'sectores' => Sector::where('estado', 1)->where('id_empresa', $finca)->get(),
            ];
        } else {    // activos
            $view = 'listar_ciclos';
            $listado = DB::table('ciclo as c')
                ->join('modulo as mod', 'mod.id_modulo', '=', 'c.id_modulo')
                ->select(
                    'c.id_ciclo',
                    'c.id_modulo',
                    'c.poda_siembra',
                    'c.fecha_inicio',
                    'c.fecha_cosecha',
                    'c.fecha_fin',
                    'c.conteo',
                    'c.area',
                    'c.plantas_iniciales',
                    'c.plantas_muertas',
                    'c.num_poda_siembra',
                    'mod.nombre as modulo_nombre',
                    'c.ancho_camino',
                    'c.ancho_cama',
                )->distinct()
                ->where('c.estado', 1)
                ->where('mod.estado', 1)
                ->where('c.activo', 1)
                ->where('c.id_variedad', $request->variedad)
                ->where('c.id_empresa', $finca);
            if ($request->sector != 'T')
                $listado = $listado->where('mod.id_sector', $request->sector);
            $listado = $listado->orderBy('c.fecha_inicio')
                ->get();
            $datos = [
                'listado' => $listado
            ];
        }

        return view('adminlte.gestion.sectores_modulos.partials.' . $view, $datos);
    }

    function buscar_modulos_inactivos(Request $request)
    {
        $finca = getFincaActiva();
        $listado = [];
        if (espacios($request->nombre) != '' || $request->sector != '') {
            $activos = DB::table('ciclo')
                ->select('id_modulo')->distinct()
                ->where('estado', 1)
                ->where('activo', 1)
                ->where('id_empresa', $finca)
                ->get();
            $ids_activos = [];
            foreach ($activos as $i)
                array_push($ids_activos, $i->id_modulo);
            $listado = Modulo::where('estado', 1)
                ->whereNotIn('id_modulo', $ids_activos)
                ->where('id_empresa', $finca)
                ->where('nombre', 'like', '%' . espacios(mb_strtoupper($request->nombre)) . '%');
            if ($request->sector != '')
                $listado = $listado->where('id_sector', $request->sector);
            $listado = $listado->orderBy('nombre')->get();
        }
        return view('adminlte.gestion.sectores_modulos.partials.listar_ciclos_inactivos', [
            'modulos' => $listado,
            'sector' => $request->sector,
            'nombre' => $request->nombre,
        ]);
    }

    public function ver_ciclos(Request $request)
    {
        $variedades = getVariedades();
        $modulo = Modulo::find($request->modulo);
        $cicloActual = $modulo->cicloActual();
        $ciclos = Ciclo::where('id_modulo', $request->modulo)
            ->where('estado', 1)
            ->orderBy('fecha_inicio', 'desc')
            ->get();
        return view('adminlte.gestion.sectores_modulos.partials.ver_ciclos', [
            'modulo' => $modulo,
            'ciclos' => $ciclos,
            'variedades' => $variedades,
            'cicloActual' => $cicloActual,
        ]);
    }

    public function ver_cosechas(Request $request)
    {
        $ciclo = Ciclo::find($request->ciclo);
        $cosechas = DB::table('desglose_recepcion as dr')
            ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
            ->select('r.id_cosecha as id')->distinct()
            ->where('dr.estado', '=', 1)
            ->where('r.estado', '=', 1)
            ->where('dr.id_modulo', '=', $ciclo->id_modulo)
            ->where('r.fecha_ingreso', '>', opDiasFecha('+', 1, $ciclo->fecha_inicio))
            ->orderBy('r.fecha_ingreso')
            ->get();

        $r = [];
        foreach ($cosechas as $c)
            array_push($r, Cosecha::find($c->id));

        return view('adminlte.gestion.sectores_modulos.partials.ver_cosechas', [
            'modulo' => getModuloById($ciclo->id_modulo),
            'cosechas' => $r,
            'ciclo' => $ciclo,
        ]);
    }

    public function store_ciclo(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'modulo' => 'required',
            'variedad' => 'required',
            'area' => 'required',
            'fecha_inicio' => 'required',
            'poda_siembra' => 'required',
        ], [
            'modulo.required' => 'El mÃ³dulo es obligatorio',
            'area.required' => 'El Ã¡rea es obligatorio',
            'variedad.required' => 'La variedad es obligatoria',
            'fecha_inicio.required' => 'La fecha de inicio de cilo es obligatoria',
            'poda_siembra.required' => 'El campo poda/siembra es obligatorio',
        ]);
        if (!$valida->fails()) {
            if ($request->fecha_fin != '' && $request->fecha_inicio > $request->fecha_fin) {
                return [
                    'success' => false,
                    'mensaje' => '<div class="alert alert-warning text-center">' .
                        '<p>La fecha de inicio debe ser menor que la fecha fin</p>'
                        . '</div>',
                ];
            }
            $modulo = Modulo::find($request->modulo);
            $ciclo = new Ciclo();
            $ciclo->id_modulo = $request->modulo;
            $ciclo->id_variedad = $request->variedad;
            $ciclo->area = $request->area;
            $ciclo->fecha_inicio = $request->fecha_inicio;
            $ciclo->poda_siembra = $request->poda_siembra;
            if ($request->fecha_cosecha != '')
                $ciclo->fecha_cosecha = opDiasFecha('+', $request->fecha_cosecha, $request->fecha_inicio);
            $ciclo->fecha_fin = $request->fecha_fin != '' ? $request->fecha_fin : date('Y-m-d');
            $ciclo->plantas_muertas = $request->plantas_muertas;

            $semana = Semana::All()
                ->where('estado', 1)
                ->where('id_variedad', $ciclo->id_variedad)
                ->where('fecha_inicial', '<=', $ciclo->fecha_inicio)
                ->where('fecha_final', '>=', $ciclo->fecha_inicio)
                ->first();
            $ciclo->desecho = $semana->desecho != '' ? $semana->desecho : 0;
            $ciclo->curva = $semana->curva;
            if ($ciclo->poda_siembra == 'P') {
                $ciclo->semana_poda_siembra = $semana->semana_poda;
                $ciclo->conteo = $request->conteo > 0 ? $request->conteo : $semana->tallos_planta_poda;
            } else {
                $ciclo->semana_poda_siembra = $semana->semana_siembra;
                $ciclo->conteo = $request->conteo > 0 ? $request->conteo : $semana->tallos_planta_siembra;
            }

            $last_siembra = Ciclo::All()->where('estado', 1)->where('id_modulo', $request->modulo)
                ->where('poda_siembra', 'S')->sortBy('fecha_inicio')->last();

            if ($last_siembra != '')
                $ciclo->plantas_iniciales = $request->plantas_iniciales > 0 ? $request->plantas_iniciales : $last_siembra->plantas_iniciales;
            else
                $ciclo->plantas_iniciales = $request->plantas_iniciales;

            $ciclo->id_empresa = $modulo->id_empresa;

            if ($ciclo->save()) {
                $ciclo = Ciclo::All()->last();
                $success = true;
                $msg = '<div class="alert alert-success text-center">' .
                    '<p> Se ha guardado un nuevo ciclo satisfactoriamente</p>'
                    . '</div>';
                bitacora('ciclo', $ciclo->id_ciclo, 'I', 'InserciÃ³n satisfactoria de un nuevo ciclo');

                /* ===================== QUITAR PROYECCIONES =================== */
                $proyecciones = ProyeccionModulo::where('estado', 1)
                    ->where('id_variedad', $request->variedad)
                    ->where('id_modulo', $request->modulo)
                    ->where('id_semana', $semana->id_semana)
                    ->delete();

                /* ===================== CREAR SIGUIENTE PROYECCION ==================== */
                $cant_semanas_ciclo = $ciclo->semana_poda_siembra + count(explode('-', $ciclo->curva)) - 1;
                $semana_next_proy = getSemanaByDateVariedad(opDiasFecha('+', $cant_semanas_ciclo * 7, $ciclo->fecha_inicio), $ciclo->id_variedad);

                $proy = new ProyeccionModulo();
                $proy->id_modulo = $ciclo->id_modulo;
                $proy->id_semana = $semana_next_proy->id_semana;
                $proy->id_variedad = $ciclo->id_variedad;
                $proy->tipo = 'P';
                $proy->curva = $ciclo->curva;
                $proy->semana_poda_siembra = $ciclo->semana_poda_siembra;
                $proy->poda_siembra = $ciclo->modulo->getPodaSiembraByCiclo($ciclo->id_ciclo) + 1;
                $proy->plantas_iniciales = $ciclo->plantas_iniciales != '' ? $ciclo->plantas_iniciales : 0;
                $proy->desecho = $ciclo->desecho;
                $proy->tallos_planta = $ciclo->conteo != '' ? $ciclo->conteo : 0;
                $proy->tallos_ramo = $semana_next_proy->tallos_ramo_poda != '' ? $semana_next_proy->tallos_ramo_poda : 0;
                $proy->fecha_inicio = $semana_next_proy->fecha_final;
                $proy->id_empresa = $modulo->id_empresa;

                $proy->save();

                /* ======================== ACTUALIZAR LA TABLA PROYECCION_MODULO_SEMANA ====================== */
                jobCorregirProyeccionModuloSemana::dispatch($ciclo->fecha_inicio, getLastSemanaByVariedad($ciclo->id_variedad)->codigo, $ciclo->id_empresa, $ciclo->id_variedad, $ciclo->id_modulo)
                    ->onQueue('proy_cosecha');

                /* ======================== ACTUALIZAR LA TABLA RESUMEN_AREA_SEMANAL ====================== */
                jobResumenAreaSemanal::dispatch($semana, $ciclo->id_variedad, $ciclo->id_empresa)
                    ->onQueue('actualizar_resumen_job');

                /* ======================== ACTUALIZAR LA TABLA RESUMEN_FENOGRAMA_EJECUCION ====================== */
                jobActualizarFenogramaEjecucion::dispatch($ciclo->id_modulo)
                    ->onQueue('proy_cosecha');
            } else {
                $success = false;
                $msg = '<div class="alert alert-warning text-center">' .
                    '<p> Ha ocurrido un problema al guardar la informaciÃ³n al sistema</p>'
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
                '<p class="text-center">Â¡Por favor corrija los siguientes errores!</p>' .
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

    public function update_ciclo(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'ciclo' => 'required',
            'area' => 'required',
            'fecha_inicio' => 'required',
            'poda_siembra' => 'required',
            'fecha_fin' => 'required',
            'plantas_iniciales' => 'required',
            'conteo' => 'required',
            'plantas_muertas' => 'required',
            'ancho_cama' => 'required',
            'ancho_camino' => 'required',
        ], [
            'ciclo.required' => 'El ciclo es obligatorio',
            'area.required' => 'El Área es obligatorio',
            'fecha_inicio.required' => 'La fecha de inicio de ciclo es obligatoria',
            'fecha_fin.required' => 'La fecha final de ciclo es obligatoria',
            'poda_siembra.required' => 'El campo poda/siembra es obligatorio',
            'plantas_iniciales.required' => 'El campo plantas iniciales es obligatorio',
            'conteo.required' => 'El campo conteo es obligatorio',
            'ancho_cama.required' => 'El campo ancho_cama es obligatorio',
            'ancho_camino.required' => 'El campo ancho_camino es obligatorio',
            'plantas_muertas.required' => 'El campo plantas muertas es obligatorio',
        ]);
        if (!$valida->fails()) {
            $ciclo = Ciclo::find($request->ciclo);

            /* ======================== MOVER PROYECCION_MODULO ====================== */
            $semana_req = Semana::All()
                ->where('estado', 1)
                ->where('id_variedad', $ciclo->id_variedad)
                ->where('fecha_inicial', '<=', $request->fecha_inicio)
                ->where('fecha_final', '>=', $request->fecha_inicio)
                ->first();
            $semana_model_inicio = getSemanaByDate($ciclo->fecha_inicio);
            $semana_req_inicio = getSemanaByDate($request->fecha_inicio);
            if ($semana_model_inicio->codigo != $semana_req_inicio->codigo) {   // hay que mover las proyecciones
                //$sum_semana = $ciclo->semana_poda_siembra + count(explode('-', $ciclo->curva));
                $semana = Semana::All()
                    ->where('estado', 1)
                    ->where('id_variedad', $ciclo->id_variedad)
                    ->where('fecha_inicial', '<=', $ciclo->fecha_inicio)
                    ->where('fecha_final', '>=', $ciclo->fecha_inicio)
                    ->first();

                /* ------------------------ OBTENER LAS SEMANAS NEW/OLD ---------------------- */
                $cant_semanas_ciclo = $ciclo->semana_poda_siembra + count(explode('-', $semana_req->curva)) - 1;
                $semana_new_req = getSemanaByDateVariedad(opDiasFecha('+', $cant_semanas_ciclo * 7, $request->fecha_inicio), $ciclo->id_variedad);

                $proy = ProyeccionModulo::where('estado', 1)
                    ->where('id_modulo', $ciclo->id_modulo)
                    ->where('id_variedad', $ciclo->id_variedad)
                    ->orderBy('fecha_inicio')
                    ->get()->first();

                if ($proy != '') {
                    $proy->id_semana = $semana_new_req->id_semana;
                    $proy->fecha_inicio = $semana_new_req->fecha_final;
                    $proy->desecho = $semana_new_req->desecho > 0 ? $semana_new_req->desecho : 0;
                    $proy->tallos_planta = $semana_new_req->tallos_planta_poda > 0 ? $semana_new_req->tallos_planta_poda : 0;
                    $proy->tallos_ramo = $semana_new_req->tallos_ramo_poda > 0 ? $semana_new_req->tallos_ramo_poda : 0;

                    $proy->save();
                    //$proy->restaurar_proyecciones();  restaurar la segunda programacion
                }
            }
            /* ------------------------ ******************************************* ---------------------- */

            $otros_ciclos_by_mod = Ciclo::where('estado', 1)
                ->where('id_modulo', $ciclo->id_modulo)
                ->where('id_ciclo', '!=', $ciclo->id_ciclo)
                ->get();
            foreach ($otros_ciclos_by_mod as $c) {
                if ($request->fecha_inicio >= $c->fecha_inicio && $request->fecha_inicio < $c->fecha_fin)
                    return [
                        'success' => false,
                        'mensaje' => '<div class="alert alert-warning text-center">' .
                            '<p>La fecha de inicio ya se encuentra incluida en un ciclo anterior</p>'
                            . '</div>',
                    ];
                if ($request->fecha_fin > $c->fecha_inicio && $request->fecha_fin <= $c->fecha_fin)
                    return [
                        'success' => false,
                        'mensaje' => '<div class="alert alert-warning text-center">' .
                            '<p>La fecha fin ya se encuentra incluida en un ciclo anterior: ' . $c->fecha_inicio . ' / ' . $c->fecha_fin . '</p>'
                            . '</div>',
                    ];
            }

            if ($request->fecha_fin != '' && $request->fecha_inicio > $request->fecha_fin) {
                return [
                    'success' => false,
                    'mensaje' => '<div class="alert alert-warning text-center">' .
                        '<p>La fecha de inicio debe ser menor que la fecha fin</p>'
                        . '</div>',
                ];
            }

            $ciclo->area = $request->area;
            $ciclo->fecha_inicio = $request->fecha_inicio;
            $ciclo->poda_siembra = $request->poda_siembra;
            if ($request->fecha_cosecha != '')
                $ciclo->fecha_cosecha = opDiasFecha('+', $request->fecha_cosecha, $request->fecha_inicio);
            else
                $ciclo->fecha_cosecha = null;
            $ciclo->fecha_fin = $request->fecha_fin != '' ? $request->fecha_fin : date('Y-m-d');
            $ciclo->plantas_iniciales = $request->plantas_iniciales;
            $ciclo->plantas_muertas = $request->plantas_muertas;
            $ciclo->ancho_cama = $request->ancho_cama;
            $ciclo->ancho_camino = $request->ancho_camino;

            $semana = Semana::All()
                ->where('estado', 1)
                ->where('id_variedad', $ciclo->id_variedad)
                ->where('fecha_inicial', '<=', $ciclo->fecha_inicio)
                ->where('fecha_final', '>=', $ciclo->fecha_inicio)
                ->first();
            if ($ciclo->desecho == '')
                $ciclo->desecho = $semana_req->desecho != '' ? $semana_req->desecho : 0;
            if ($ciclo->curva == '')
                $ciclo->curva = $semana_req->curva;
            if ($ciclo->poda_siembra == 'P') {
                $ciclo->semana_poda_siembra = $semana_req->semana_poda;
                $ciclo->conteo = $request->conteo > 0 ? $request->conteo : $semana->tallos_planta_poda;
            } else {
                $ciclo->semana_poda_siembra = $semana_req->semana_siembra;
                $ciclo->conteo = $request->conteo > 0 ? $request->conteo : $semana->tallos_planta_siembra;
            }
            //$ciclo->id_empresa = $ciclo->modulo->id_empresa;
            if ($ciclo->save()) {
                $success = true;
                $msg = '<div class="alert alert-success text-center">' .
                    '<p> Se ha actualizado el ciclo satisfactoriamente</p>'
                    . '</div>';
                bitacora('ciclo', $ciclo->id_ciclo, 'U', 'Actualziacion satisfactoria de un ciclo');

                /* ======================== ACTUALIZAR LA TABLA PROYECCION_MODULO_SEMANA ====================== */
                jobCorregirProyeccionModuloSemana::dispatch($ciclo->fecha_inicio, getLastSemanaByVariedad($ciclo->id_variedad)->codigo, $ciclo->id_empresa, $ciclo->id_variedad, $ciclo->id_modulo)
                    ->onQueue('proy_cosecha');

                /* ======================== ACTUALIZAR LA TABLA RESUMEN_AREA_SEMANAL ====================== */
                jobResumenAreaSemanal::dispatch($semana, $ciclo->id_variedad, $ciclo->id_empresa)
                    ->onQueue('actualizar_resumen_job');

                /* ======================== ACTUALIZAR LA TABLA RESUMEN_FENOGRAMA_EJECUCION ====================== */
                jobActualizarFenogramaEjecucion::dispatch($ciclo->id_modulo)
                    ->onQueue('proy_cosecha');
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

    public function terminar_ciclo(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'modulo' => 'required',
            'fecha_fin' => 'required',
        ], [
            'modulo.required' => 'El mÃ³dulo es obligatorio',
            'fecha_fin.required' => 'La fecha final es obligatoria',
        ]);
        if (!$valida->fails()) {
            $modulo = Modulo::find($request->modulo);
            $ciclo = $modulo->cicloActual();
            if ($ciclo->fecha_fin != '') {
                $ciclo->activo = 0;
                $ciclo->fecha_fin = $request->fecha_fin;

                if ($ciclo->save()) {
                    $success = true;
                    $msg = '<div class="alert alert-success text-center">' .
                        '<p> Se ha terminado el ciclo satisfactoriamente</p>'
                        . '</div>';
                    bitacora('ciclo', $ciclo->id_ciclo, 'U', 'Actualizacion satisfactoria de un ciclo (terminar ciclo)');

                    /* ======================== ACTUALIZAR LA TABLA RESUMEN_AREA_SEMANAL ====================== */
                    $semana = getSemanaByDate($ciclo->fecha_inicio);
                    jobResumenAreaSemanal::dispatch($semana, $ciclo->id_variedad, $ciclo->id_empresa)
                        ->onQueue('actualizar_resumen_job');
                } else {
                    $success = false;
                    $msg = '<div class="alert alert-warning text-center">' .
                        '<p> Ha ocurrido un problema al guardar la informaciÃ³n al sistema</p>'
                        . '</div>';
                }
            } else {
                $success = false;
                $msg = '<div class="alert alert-warning text-center">' .
                    '<p>Faltan las fechas necesarias para terminar el ciclo</p>'
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
                '<p class="text-center">Â¡Por favor corrija los siguientes errores!</p>' .
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

    public function abrir_ciclo(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'ciclo' => 'required',
        ], [
            'ciclo.required' => 'El ciclo es obligatorio',
        ]);
        if (!$valida->fails()) {
            $ciclo = Ciclo::find($request->ciclo);
            if ($request->abrir == 'true')
                $ciclo->activo = 1;
            else
                $ciclo->activo = 0;

            if ($ciclo->save()) {
                $success = true;
                $msg = '<div class="alert alert-success text-center">' .
                    '<p> Se ha abierto el ciclo satisfactoriamente</p>'
                    . '</div>';
                bitacora('ciclo', $ciclo->id_ciclo, 'U', 'Actualizacion satisfactoria de un ciclo (abrir ciclo)');

                /* ======================== ACTUALIZAR LA TABLA PROYECCION_MODULO_SEMANA ====================== */
                jobCorregirProyeccionModuloSemana::dispatch($ciclo->fecha_inicio, getLastSemanaByVariedad($ciclo->id_variedad)->codigo, $ciclo->id_empresa, $ciclo->id_variedad, $ciclo->id_modulo)
                    ->onQueue('proy_cosecha');

                /* ======================== ACTUALIZAR LA TABLA RESUMEN_FENOGRAMA_EJECUCION ====================== */
                jobActualizarFenogramaEjecucion::dispatch($ciclo->id_modulo)
                    ->onQueue('proy_cosecha');

                /* ======================== ACTUALIZAR LA TABLA RESUMEN_AREA_SEMANAL ====================== */
                $semana = getSemanaByDate($ciclo->fecha_inicio);
                jobResumenAreaSemanal::dispatch($semana, $ciclo->id_variedad, $ciclo->id_empresa)
                    ->onQueue('actualizar_resumen_job');
            } else {
                $success = false;
                $msg = '<div class="alert alert-warning text-center">' .
                    '<p> Ha ocurrido un problema al guardar la informaciÃ³n al sistema</p>'
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
                '<p class="text-center">Â¡Por favor corrija los siguientes errores!</p>' .
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

    public function eliminar_ciclo(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'ciclo' => 'required',
        ], [
            'ciclo.required' => 'El ciclo es obligatorio',
        ]);
        if (!$valida->fails()) {
            $ciclo = Ciclo::find($request->ciclo);
            $ciclo->activo = 0;
            $ciclo->estado = 0;

            if ($ciclo->save()) {
                $success = true;
                $msg = '<div class="alert alert-success text-center">' .
                    '<p> Se ha eliminado el ciclo satisfactoriamente</p>'
                    . '</div>';
                bitacora('ciclo', $ciclo->id_ciclo, 'U', 'Actualizacion satisfactoria de un ciclo');

                /* ======================== ACTUALIZAR LA TABLA RESUMEN_AREA_SEMANAL ====================== */
                $semana = getSemanaByDate($ciclo->fecha_inicio);
                jobResumenAreaSemanal::dispatch($semana, $ciclo->id_variedad, $ciclo->id_empresa)
                    ->onQueue('actualizar_resumen_job');
            } else {
                $success = false;
                $msg = '<div class="alert alert-warning text-center">' .
                    '<p> Ha ocurrido un problema al guardar la informaciÃ³n al sistema</p>'
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
                '<p class="text-center">Â¡Por favor corrija los siguientes errores!</p>' .
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

    public function nuevos_ciclos(Request $request)
    {
        $sem_actual = getSemanaByDate(date('Y-m-d'));
        $query = ProyeccionModulo::where('estado', 1)
            ->where('fecha_inicio', '>=', $sem_actual->fecha_inicial)
            ->where('fecha_inicio', '<=', $sem_actual->fecha_final)
            ->get();

        return view('adminlte.gestion.sectores_modulos.partials.nuevos_ciclos', [
            'nuevos_ciclos' => $query,
            'sem_actual' => $sem_actual,
        ]);
    }

    public function store_nuevo_ciclo(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'id_modulo' => 'required',
            'id_variedad' => 'required',
            'id_semana' => 'required',
            'id_proyeccion_modulo' => 'required',
            'area' => 'required',
            'fecha_inicio' => 'required',
            'poda_siembra' => 'required',
            'plantas_iniciales' => 'required',
            'curva' => 'required',
            'desecho' => 'required',
            'semana_poda_siembra' => 'required',
        ], [
            'id_modulo.required' => 'El mÃ³dulo es obligatorio',
            'area.required' => 'El Ã¡rea es obligatoria',
            'desecho.required' => 'El desecho es obligatorio',
            'id_variedad.required' => 'La variedad es obligatoria',
            'id_semana.required' => 'La semana es obligatoria',
            'id_proyeccion_modulo.required' => 'La proyecciÃ³n es obligatoria',
            'curva.required' => 'La curva es obligatoria',
            'semana_poda_siembra.required' => 'La semana de inicio de cosecha es obligatoria',
            'plantas_iniciales.required' => 'Las plantas iniciales son obligatorias',
            'fecha_inicio.required' => 'La fecha de inicio de cilo es obligatoria',
            'poda_siembra.required' => 'El campo poda/siembra es obligatorio',
        ]);
        if (!$valida->fails()) {
            /* ------------------------ Cerrar ciclo anterior ------------------- */
            $last_ciclo = Ciclo::All()
                ->where('estado', 1)
                ->where('activo', 1)
                ->where('id_modulo', $request->id_modulo)
                ->first();
            if ($last_ciclo != '') {
                $last_ciclo->activo = 0;
                $last_ciclo->fecha_fin = $request->fecha_fin;
                $last_ciclo->save();
            }

            $ciclo = new Ciclo();
            $ciclo->id_modulo = $request->id_modulo;
            $ciclo->id_variedad = $request->id_variedad;
            $ciclo->area = $request->area;
            $ciclo->fecha_inicio = $request->fecha_inicio;
            $ciclo->fecha_fin = date('Y-m-d');
            $ciclo->poda_siembra = $request->poda_siembra;
            $ciclo->desecho = $request->desecho;
            $ciclo->curva = $request->curva;
            $ciclo->semana_poda_siembra = $request->semana_poda_siembra;
            $ciclo->conteo = $request->conteo;
            $ciclo->plantas_iniciales = $request->plantas_iniciales;
            $ciclo->id_empresa = Modulo::find($request->id_modulo)->id_empresa;

            if ($ciclo->save()) {
                $ciclo = Ciclo::All()->last();
                $success = true;
                $msg = '<div class="alert alert-success text-center">' .
                    '<p> Se ha guardado un nuevo ciclo satisfactoriamente</p>'
                    . '</div>';
                bitacora('ciclo', $ciclo->id_ciclo, 'I', 'InserciÃ³n satisfactoria de un nuevo ciclo');

                /* ===================== QUITAR PROYECCIONES =================== */
                $proyecciones = ProyeccionModulo::find($request->id_proyeccion_modulo);
                $proyecciones->estado = 0;
                $proyecciones->save();
                bitacora('proyeccion_modulo', $proyecciones->id_proyeccion_modulo, 'U', 'Actualizacion satisfactoria del estado');

                /* ===================== CREAR SIGUIENTE PROYECCION ==================== */
                $cant_semanas_ciclo = $ciclo->semana_poda_siembra + count(explode('-', $ciclo->curva)) - 1;
                $semana_next_proy = getSemanaByDateVariedad(opDiasFecha('+', $cant_semanas_ciclo * 7, $ciclo->fecha_inicio), $ciclo->id_variedad);

                $proy = new ProyeccionModulo();
                $proy->id_modulo = $ciclo->id_modulo;
                $proy->id_semana = $semana_next_proy->id_semana;
                $proy->id_variedad = $ciclo->id_variedad;
                $proy->tipo = 'P';
                $proy->curva = $ciclo->curva;
                $proy->semana_poda_siembra = $ciclo->semana_poda_siembra;
                $proy->poda_siembra = $ciclo->modulo->getPodaSiembraByCiclo($ciclo->id_ciclo) + 1;
                $proy->plantas_iniciales = $ciclo->plantas_iniciales != '' ? $ciclo->plantas_iniciales : 0;
                $proy->desecho = $ciclo->desecho;
                $proy->tallos_planta = $ciclo->conteo != '' ? $ciclo->conteo : 0;
                $proy->tallos_ramo = $semana_next_proy->tallos_ramo_poda != '' ? $semana_next_proy->tallos_ramo_poda : 0;
                $proy->fecha_inicio = $semana_next_proy->fecha_final;

                $proy->save();

                /* ======================== ACTUALIZAR LA TABLA PROYECCION_MODULO_SEMANA ====================== */
                jobCorregirProyeccionModuloSemana::dispatch($ciclo->fecha_inicio, getLastSemanaByVariedad($ciclo->id_variedad)->codigo, $ciclo->id_empresa, $ciclo->id_variedad, $ciclo->id_modulo)
                    ->onQueue('proy_cosecha');

                /* ======================== ACTUALIZAR LA TABLA RESUMEN_AREA_SEMANAL ====================== */
                $semana = getSemanaByDate($ciclo->fecha_inicio);
                jobResumenAreaSemanal::dispatch($semana, $ciclo->id_variedad, $ciclo->id_empresa)
                    ->onQueue('actualizar_resumen_job');

                /* ======================== ACTUALIZAR LA TABLA RESUMEN_FENOGRAMA_EJECUCION ====================== */
                jobActualizarFenogramaEjecucion::dispatch($ciclo->id_modulo)
                    ->onQueue('proy_cosecha');
            } else {
                $success = false;
                $msg = '<div class="alert alert-warning text-center">' .
                    '<p> Ha ocurrido un problema al guardar la informaciÃ³n al sistema</p>'
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
                '<p class="text-center">Â¡Por favor corrija los siguientes errores!</p>' .
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

    public function crear_activar_modulo(Request $request)
    {
        $finca_actual = $request->has('finca_actual') ? $request->finca_actual : getUsuario(Session::get('id_usuario'))->finca_activa;
        $finca_actual = $finca_actual != 'T' ? $finca_actual : getFincasPropias()[0]->id_configuracion_empresa;
        return view('adminlte.gestion.sectores_modulos.forms.crear_activar_modulo', [
            'sectores' => Sector::where('id_empresa', $finca_actual)->where('estado', 1)->where('interno', 1)->orderBy('nombre')->get()
        ]);
    }

    public function store_crear_activar_modulo(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'variedad' => 'required',
            'sector' => 'required|',
            'modulo' => 'required|max:25',
            'fecha_inicio' => 'required|',
            'area' => 'required|',
            'poda_siembra' => 'required|',
        ], [
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria',
            'variedad.required' => 'La variedad es obligatoria',
            'sector.required' => 'El sector es obligatorio',
            'modulo.required' => 'El mÃ³dulo es obligatorio',
            'modulo.max' => 'El nombre del mÃ³dulo es muy grande',
            'area.required' => 'El Ã¡rea es obligatorio',
            'poda_siembra.required' => 'La poda/siembra es obligatoria',
        ]);
        if (!$valida->fails()) {
            if (count(Modulo::All()->where('nombre', '=', str_limit(mb_strtoupper(espacios($request->modulo)), 25))
                ->where('id_sector', '=', $request->sector)) == 0) {
                $modulo = new Modulo();
                $modulo->nombre = str_limit(mb_strtoupper(espacios($request->modulo)), 25);
                $modulo->id_sector = $request->sector;
                $modulo->area = $request->area;
                $modulo->descripcion = '';
                $modulo->fecha_registro = date('Y-m-d H:i:s');
                $modulo->id_empresa = Sector::find($request->sector)->id_empresa;

                if ($modulo->save()) {
                    $modulo = Modulo::All()->last();
                    bitacora('modulo', $modulo->id_modulo, 'I', 'InserciÃ³n satisfactoria de un nuevo mÃ³dulo');

                    /* ================= ACTIVAR CICLO ================ */
                    $ciclo = new Ciclo();
                    $ciclo->id_modulo = $modulo->id_modulo;
                    $ciclo->id_variedad = $request->variedad;
                    $ciclo->area = $request->area;
                    $ciclo->fecha_inicio = $request->fecha_inicio;
                    $ciclo->poda_siembra = $request->poda_siembra;
                    $ciclo->fecha_fin = date('Y-m-d');
                    $ciclo->plantas_muertas = $request->plantas_muertas;

                    $semana = Semana::All()
                        ->where('estado', 1)
                        ->where('id_variedad', $ciclo->id_variedad)
                        ->where('fecha_inicial', '<=', $ciclo->fecha_inicio)
                        ->where('fecha_final', '>=', $ciclo->fecha_inicio)
                        ->first();
                    $ciclo->desecho = $semana->desecho != '' ? $semana->desecho : 0;
                    $ciclo->curva = $semana->curva;
                    if ($ciclo->poda_siembra == 'P') {
                        $ciclo->semana_poda_siembra = $semana->semana_poda;
                        $ciclo->conteo = $request->conteo > 0 ? $request->conteo : $semana->tallos_planta_poda;
                    } else {
                        $ciclo->semana_poda_siembra = $semana->semana_siembra;
                        $ciclo->conteo = $request->conteo > 0 ? $request->conteo : $semana->tallos_planta_siembra;
                    }
                    $ciclo->plantas_iniciales = $request->plantas_iniciales;
                    $ciclo->id_empresa = $modulo->id_empresa;

                    if ($ciclo->save()) {
                        $ciclo = Ciclo::All()->last();
                        $success = true;
                        $msg = '<div class="alert alert-success text-center">' .
                            '<p> Se ha guardado un nuevo ciclo satisfactoriamente</p>'
                            . '</div>';
                        bitacora('ciclo', $ciclo->id_ciclo, 'I', 'InserciÃ³n satisfactoria de un nuevo ciclo');

                        /* ===================== CREAR SIGUIENTE PROYECCION ==================== */
                        $cant_semanas_ciclo = $ciclo->semana_poda_siembra + count(explode('-', $ciclo->curva)) - 1;
                        $semana_next_proy = getSemanaByDateVariedad(opDiasFecha('+', $cant_semanas_ciclo * 7, $ciclo->fecha_inicio), $ciclo->id_variedad);

                        $proy = new ProyeccionModulo();
                        $proy->id_modulo = $ciclo->id_modulo;
                        $proy->id_semana = $semana_next_proy->id_semana;
                        $proy->id_variedad = $ciclo->id_variedad;
                        $proy->tipo = 'P';
                        $proy->curva = $ciclo->curva;
                        $proy->semana_poda_siembra = $ciclo->semana_poda_siembra;
                        $proy->poda_siembra = $ciclo->modulo->getPodaSiembraByCiclo($ciclo->id_ciclo) + 1;
                        $proy->plantas_iniciales = $ciclo->plantas_iniciales != '' ? $ciclo->plantas_iniciales : 0;
                        $proy->desecho = $ciclo->desecho;
                        $proy->tallos_planta = $ciclo->conteo != '' ? $ciclo->conteo : 0;
                        $proy->tallos_ramo = $semana_next_proy->tallos_ramo_poda != '' ? $semana_next_proy->tallos_ramo_poda : 0;
                        $proy->fecha_inicio = $semana_next_proy->fecha_final;

                        $proy->save();

                        /* ======================== ACTUALIZAR LA TABLA PROYECCION_MODULO_SEMANA ====================== */
                        jobUpdateProyeccionUpdateSemana::dispatch($ciclo->id_modulo)->onQueue('proy_cosecha');

                        /* ======================== ACTUALIZAR LA TABLA RESUMEN_AREA_SEMANAL ====================== */
                        jobResumenAreaSemanal::dispatch($semana, $ciclo->id_variedad, $ciclo->id_empresa)
                            ->onQueue('actualizar_resumen_job');

                        /* ======================== ACTUALIZAR LA TABLA RESUMEN_FENOGRAMA_EJECUCION ====================== */
                        jobActualizarFenogramaEjecucion::dispatch($ciclo->id_modulo)
                            ->onQueue('proy_cosecha');
                    } else {
                        $success = false;
                        $msg = '<div class="alert alert-warning text-center">' .
                            '<p> Ha ocurrido un problema al guardar la información al sistema</p>'
                            . '</div>';
                    }
                } else {
                    $success = false;
                    $msg = '<div class="alert alert-warning text-center">' .
                        '<p> Ha ocurrido un problema al guardar la información al sistema</p>'
                        . '</div>';
                }
            } else {
                $success = false;
                $msg = '<div class="alert alert-warning text-center">' .
                    '<p> El módulo "' . espacios($request->modulo) . '" ya se encuentra en este sector</p>'
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
                '<p class="text-center">Â¡Por favor corrija los siguientes errores!</p>' .
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
}
