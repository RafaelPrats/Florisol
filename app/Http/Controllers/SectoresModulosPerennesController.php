<?php

namespace yura\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use yura\Jobs\jobActualizarProyeccion;
use yura\Jobs\jobActualizarSemProyPerenne;
use yura\Jobs\jobResumenAreaSemanal;
use yura\Modelos\Ciclo;
use yura\Modelos\Modulo;
use yura\Modelos\Planta;
use yura\Modelos\ProyeccionModulo;
use yura\Modelos\Sector;
use yura\Modelos\Semana;
use yura\Modelos\Submenu;
use Validator;

class SectoresModulosPerennesController extends Controller
{
    public function inicio(Request $request)
    {
        return view('adminlte.gestion.sectores_modulos_perennes.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'plantas' => Planta::where('estado', 1)->where('tipo', 'P')->get(),
        ]);
    }

    public function listar_ciclos(Request $request)
    {
        $finca = getFincaActiva();
        if ($request->estado == 1) {
            $ciclos = Ciclo::where('estado', 1)
                ->where('activo', $request->estado)
                ->where('id_variedad', $request->variedad)
                ->where('id_empresa', $finca)
                ->orderBy('fecha_inicio')
                ->get();
            $view = 'activos';
            $datos = [
                'ciclos' => $ciclos
            ];
        } else {
            $view = 'inactivos';
            $datos = [
                'sectores' => Sector::where('id_empresa', $finca)->where('estado', 1)->where('interno', 1)->orderBy('nombre')->get()
            ];
        }
        return view('adminlte.gestion.sectores_modulos_perennes.forms.' . $view, $datos);
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
            DB::beginTransaction();
            try {
                if (count(Modulo::All()->where('nombre', '=', str_limit(mb_strtoupper(espacios($request->modulo)), 25))
                    ->where('id_sector', '=', $request->sector)) == 0) {
                    $semana = getSemanaByDateVariedad($request->fecha_inicio, $request->variedad);
                    if ($semana != '') {
                        $modulo = new Modulo();
                        $modulo->nombre = str_limit(mb_strtoupper(espacios($request->modulo)), 25);
                        $modulo->id_sector = $request->sector;
                        $modulo->area = $request->area;
                        $modulo->descripcion = '';
                        $modulo->fecha_registro = date('Y-m-d H:i:s');
                        $modulo->id_empresa = Sector::find($request->sector)->id_empresa;

                        $modulo->save();
                        $modulo = Modulo::All()->last();
                        bitacora('modulo', $modulo->id_modulo, 'I', 'Insercion satisfactoria de un nuevo modulo');

                        /* ================= ACTIVAR CICLO ================ */
                        $ciclo = new Ciclo();
                        $ciclo->id_modulo = $modulo->id_modulo;
                        $ciclo->id_variedad = $request->variedad;
                        $ciclo->area = $request->area;
                        $ciclo->fecha_inicio = $request->fecha_inicio;
                        $ciclo->poda_siembra = $request->poda_siembra;
                        $ciclo->fecha_fin = date('Y-m-d');
                        $ciclo->plantas_muertas = $request->plantas_muertas;

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

                        $ciclo->save();
                        $ciclo = Ciclo::All()->last();
                        $success = true;
                        $msg = '<div class="alert alert-success text-center">' .
                            '<p> Se ha guardado un nuevo ciclo satisfactoriamente</p>'
                            . '</div>';
                        bitacora('ciclo', $ciclo->id_ciclo, 'I', 'Insercion satisfactoria de un nuevo ciclo');

                        /* ---------------- ACTUALIZR SEMANA_PROYECCION_PERENNE ------------------- */
                        $semanas = Semana::where('id_variedad', $ciclo->id_variedad)
                            ->where('codigo', '>=', $semana->codigo)
                            ->get();
                        foreach ($semanas as $sem)
                            jobActualizarSemProyPerenne::dispatch($sem->codigo, $sem->id_variedad, $ciclo->id_empresa)->onQueue('proy_cosecha');

                        /* ---------------- ACTUALIZR PROYECCIONES ------------------- */
                        jobActualizarProyeccion::dispatch($ciclo->id_variedad, '', $semana, $ciclo->id_empresa)->onQueue('proy_cosecha');

                        /* ======================== ACTUALIZAR LA TABLA RESUMEN_AREA_SEMANAL ====================== */
                        jobResumenAreaSemanal::dispatch($semana, $ciclo->id_variedad, $ciclo->id_empresa)
                            ->onQueue('actualizar_resumen_job');
                    } else {
                        $success = false;
                        $msg = '<div class="alert alert-warning text-center">' .
                            '<p> No se ha creado la semana para esta variedad</p>'
                            . '</div>';
                    }
                } else {
                    $success = false;
                    $msg = '<div class="alert alert-warning text-center">' .
                        '<p> El modulo "' . espacios($request->modulo) . '" ya se encuentra en este sector</p>'
                        . '</div>';
                }
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $success = false;
                $msg = '<div class="alert alert-danger text-center">' .
                    '<p> Ha ocurrido un problema al ingresar la informacion, intente nuevamente</p>'
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

    public function update_ciclo(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'ciclo' => 'required',
            'variedad' => 'required',
            'area' => 'required',
            'fecha_inicio' => 'required',
            'plantas_iniciales' => 'required',
            'fecha_fin' => 'required',
        ], [
            'ciclo.required' => 'El ciclo es obligatorio',
            'area.required' => 'El Ã¡rea es obligatorio',
            'variedad.required' => 'La variedad es obligatoria',
            'fecha_inicio.required' => 'La fecha de inicio de cilo es obligatoria',
            'fecha_fin.required' => 'La fecha de fin de cilo es obligatoria',
            'plantas_iniciales.required' => 'Las plantas iniciales son obligatorias',
        ]);
        if (!$valida->fails()) {
            $ciclo = Ciclo::find($request->ciclo);

            /* ======================== MOVER PROYECCION_MODULO ====================== */
            $semana_req = Semana::All()
                ->where('estado', 1)
                ->where('id_variedad', $request->variedad)
                ->where('fecha_inicial', '<=', $request->fecha_inicio)
                ->where('fecha_final', '>=', $request->fecha_inicio)
                ->first();
            /* ------------------------ ******************************************* ---------------------- */
            foreach ($ciclo->modulo->ciclos->where('estado', 1) as $c) {
                if ($c->id_ciclo != $ciclo->id_ciclo) {
                    if ($request->fecha_inicio > $c->fecha_inicio && $request->fecha_inicio < $c->fecha_fin)
                        return [
                            'success' => false,
                            'mensaje' => '<div class="alert alert-warning text-center">' .
                                '<p>La fecha de inicio ya se encuentra incluida en un ciclo anterior</p>'
                                . '</div>',
                        ];
                    if ($request->fecha_fin > $c->fecha_inicio && $request->fecha_fin < $c->fecha_fin)
                        return [
                            'success' => false,
                            'mensaje' => '<div class="alert alert-warning text-center">' .
                                '<p>La fecha fin ya se encuentra incluida en un ciclo anterior: ' . $c->fecha_inicio . ' / ' . $c->fecha_fin . '</p>'
                                . '</div>',
                        ];
                }
            }

            if ($request->fecha_fin != '' && $request->fecha_inicio > $request->fecha_fin) {
                return [
                    'success' => false,
                    'mensaje' => '<div class="alert alert-warning text-center">' .
                        '<p>La fecha de inicio debe ser menor que la fecha fin</p>'
                        . '</div>',
                ];
            }

            $ciclo->id_variedad = $request->variedad;
            $ciclo->area = $request->area;
            $ciclo->fecha_inicio = $request->fecha_inicio;
            $ciclo->fecha_fin = $request->fecha_fin != '' ? $request->fecha_fin : date('Y-m-d');
            $ciclo->plantas_iniciales = $request->plantas_iniciales;

            $ciclo->desecho = $semana_req->desecho != '' ? $semana_req->desecho : 0;
            $ciclo->curva = $semana_req->curva;
            $ciclo->id_empresa = $ciclo->modulo->id_empresa;
            if ($ciclo->save()) {
                $success = true;
                $msg = '<div class="alert alert-success text-center">' .
                    '<p> Se ha actualizado el ciclo satisfactoriamente</p>'
                    . '</div>';
                bitacora('ciclo', $ciclo->id_ciclo, 'U', 'Actualziacion satisfactoria de un ciclo');

                /* ---------------- ACTUALIZR SEMANA_PROYECCION_PERENNE ------------------- */
                $semanas = Semana::where('id_variedad', $ciclo->id_variedad)
                    ->where('codigo', '>=', $semana_req->codigo)
                    ->get();
                foreach ($semanas as $sem)
                    jobActualizarSemProyPerenne::dispatch($sem->codigo, $sem->id_variedad, $ciclo->id_empresa)->onQueue('proy_cosecha');

                /* ---------------- ACTUALIZR PROYECCIONES ------------------- */
                jobActualizarProyeccion::dispatch($ciclo->id_variedad, '', $semana_req, $ciclo->id_empresa)->onQueue('proy_cosecha');
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

    public function reiniciar_ciclo(Request $request)
    {
        $ciclo = Ciclo::find($request->ciclo);
        $ciclo->fecha_fin = $request->fecha_fin;
        $ciclo->activo = 0;
        if ($ciclo->save()) {
            $new_ciclo = new Ciclo();
            $new_ciclo->id_modulo = $ciclo->id_modulo;
            $new_ciclo->id_variedad = $request->variedad;
            $new_ciclo->fecha_inicio = $request->fecha_fin;
            $new_ciclo->fecha_fin = date('Y-m-d');
            $new_ciclo->activo = 1;
            $new_ciclo->area = $ciclo->area;
            $new_ciclo->poda_siembra = 'S';
            $new_ciclo->plantas_iniciales = $ciclo->plantas_iniciales;
            $new_ciclo->plantas_muertas = $ciclo->plantas_muertas;
            $new_ciclo->conteo = $ciclo->conteo;
            $new_ciclo->curva = $ciclo->curva;
            $new_ciclo->semana_poda_siembra = $ciclo->semana_poda_siembra;
            $new_ciclo->desecho = $ciclo->desecho;
            $new_ciclo->id_empresa = $ciclo->id_empresa;
            if ($new_ciclo->save()) {
                $success = true;
                $msg = '<div class="alert alert-success text-center">Se ha guardado la informacion satisfactoriamente</div>';
            } else {
                $success = false;
                $msg = '<div class="alert alert-danger text-center">Ha ocurrido un problema al crear el siguiente ciclo</div>';
            }
        } else {
            $success = false;
            $msg = '<div class="alert alert-danger text-center">Ha ocurrido un problema al cerrar el ciclo actual</div>';
        }
        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }

    public function ver_ciclos_historicos(Request $request)
    {
        return view('adminlte.gestion.sectores_modulos_perennes.partials.ver_ciclos', [
            'modulo' => getModuloById($request->modulo),
        ]);
    }
}
