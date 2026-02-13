<?php

namespace yura\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use yura\Modelos\Lote;
use yura\Modelos\Modulo;
use yura\Modelos\Planta;
use yura\Modelos\Sector;
use yura\Modelos\Submenu;
use Validator;

class SectorController extends Controller
{
    public function inicio(Request $request)
    {
        $sem_actual = getSemanaByDate(hoy());
        $query = DB::table('proyeccion_modulo')
            ->where('estado', 1)
            ->where('fecha_inicio', '>=', $sem_actual->fecha_inicial)
            ->where('fecha_inicio', '<=', $sem_actual->fecha_final)
            ->get();

        return view('adminlte.gestion.sectores_modulos.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'sectores' => Sector::orderBy('nombre')->get(),
            'nuevos_ciclos' => $query,
            'sem_actual' => $sem_actual,
            'plantas' => Planta::join('variedad as v', 'v.id_planta', '=', 'planta.id_planta')
                ->select('planta.*')->distinct()
                ->where('v.estado', 1)
                ->where('planta.estado', 1)
                ->where('planta.tiene_ciclos', 1)
                ->where('v.compra_flor', 0)
                ->where('planta.tipo', 'N')
                ->orderBy('planta.nombre')
                ->get(),
        ]);
    }

    public function listar_sectores_modulos(Request $request)
    {
        $finca_actual = $request->has('finca_actual') ? $request->finca_actual : getUsuario(Session::get('id_usuario'))->finca_activa;
        $sectores = Sector::where('id_empresa', $finca_actual)
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.sectores_modulos.partials.master_sectores_modulos', [
            'sectores' => $sectores,
            'finca_actual' => $finca_actual
        ]);
    }

    public function select_sector(Request $request)
    {
        $query = Modulo::where('id_sector', $request->id_sector)
            ->where('proyectar_semanal', 0)
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.sectores_modulos.partials.listado_modulo', [
            'modulos' => $query,
            'sector' => $request->id_sector,
        ]);
    }

    public function listar_modulos_x_sector(Request $request)
    {
        $modulos = [];
        if ($request->id_sector != '') {
            $s = Sector::find($request->id_sector);
            if ($s != '')
                $modulos = $s->modulos;
        }
        return view('adminlte.gestion.sectores_modulos.forms.partials.select_modulos', [
            'modulos' => $modulos
        ]);
    }

    public function select_modulo(Request $request)
    {
        $m = Modulo::find($request->id_modulo);
        return view('adminlte.gestion.sectores_modulos.partials.listado_lote', [
            'lotes' => $m->lotes
        ]);
    }

    /* =====================================================*/

    public function add_sector(Request $request)
    {
        return view('adminlte.gestion.sectores_modulos.forms.add_sector');
    }

    public function store_sector(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'nombre' => 'required|max:250',
            'interno' => 'required',
            'descripcion' => 'max:1000',
        ], [
            'nombre.required' => 'El nombre es obligatorio',
            'interno.required' => 'El campo interno es obligatorio',
            'nombre.max' => 'El nombre es muy grande',
            'descripcion.max' => 'La descripciÃ³n es muy grande',
        ]);
        if (!$valida->fails()) {
            $finca = getFincaActiva();
            $existe = Sector::All()
                ->where('id_empresa', $finca)
                ->where('nombre', str_limit(mb_strtoupper(espacios($request->nombre)), 250))
                ->first();
            if ($existe == '') {
                $model = new Sector();
                $model->nombre = str_limit(mb_strtoupper(espacios($request->nombre)), 250);
                $model->descripcion = str_limit((espacios($request->descripcion)), 1000);
                $model->interno = $request->interno;
                $model->fecha_registro = date('Y-m-d H:i:s');
                $model->id_empresa = $finca;

                if ($model->save()) {
                    $model = Sector::All()->last();
                    $success = true;
                    $msg = '<div class="alert alert-success text-center">' .
                        '<p> Se ha guardado un nuevo sector satisfactoriamente</p>'
                        . '</div>';
                    bitacora('sector', $model->id_sector, 'I', 'Inserción satisfactoria de un nuevo sector');
                } else {
                    $success = false;
                    $msg = '<div class="alert alert-warning text-center">' .
                        '<p> Ha ocurrido un problema al guardar la información al sistema</p>'
                        . '</div>';
                }
            } else {
                $success = false;
                $msg = '<div class="alert alert-warning text-center">' .
                    '<p>El sector ya existe en esta finca</p>'
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

    public function add_modulo(Request $request)
    {
        $finca_actual = $request->has('finca_actual') ? $request->finca_actual : getUsuario(Session::get('id_usuario'))->finca_activa;
        $sectores = Sector::All()->where('estado', '=', 1);
        if ($finca_actual != 'T')
            $sectores = $sectores->where('id_empresa', '=', $finca_actual);
        return view('adminlte.gestion.sectores_modulos.forms.add_modulo', [
            'sectores' => $sectores,
        ]);
    }

    public function store_modulo(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'nombre' => 'required|max:25',
            'id_sector' => 'required|',
            'area' => 'required|',
            'descripcion' => 'max:1000',
        ], [
            'nombre.required' => 'El nombre es obligatorio',
            'area.required' => 'El Ã¡rea es obligatorio',
            'id_sector.required' => 'El sector es obligatorio',
            'descripcion.max' => 'La descripciÃ³n es muy grande',
            'nombre.max' => 'El nombre es muy grande',
        ]);
        if (!$valida->fails()) {
            if (count(Modulo::All()->where('nombre', '=', str_limit(mb_strtoupper(espacios($request->nombre)), 25))
                ->where('id_sector', '=', $request->id_sector)) == 0) {
                $model = new Modulo();
                $model->nombre = str_limit(mb_strtoupper(espacios($request->nombre)), 25);
                $model->id_sector = $request->id_sector;
                $model->area = $request->area;
                $model->descripcion = str_limit((espacios($request->descripcion)), 1000);
                $model->fecha_registro = date('Y-m-d H:i:s');
                $model->id_empresa = Sector::find($request->id_sector)->id_empresa;

                if ($model->save()) {
                    $model = Modulo::All()->last();
                    $success = true;
                    $msg = '<div class="alert alert-success text-center">' .
                        '<p> Se ha guardado un nuevo mÃ³dulo satisfactoriamente</p>'
                        . '</div>';
                    bitacora('modulo', $model->id_modulo, 'I', 'InserciÃ³n satisfactoria de un nuevo mÃ³dulo');
                } else {
                    $success = false;
                    $msg = '<div class="alert alert-warning text-center">' .
                        '<p> Ha ocurrido un problema al guardar la informaciÃ³n al sistema</p>'
                        . '</div>';
                }
            } else {
                $success = false;
                $msg = '<div class="alert alert-warning text-center">' .
                    '<p> El mÃ³dulo "' . espacios($request->nombre) . '" ya se encuentra en este sector</p>'
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

    public function add_lote(Request $request)
    {
        return view('adminlte.gestion.sectores_modulos.forms.add_lote', [
            'sectores' => Sector::All()->where('estado', '=', 1),
        ]);
    }

    public function store_lote(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'nombre' => 'required|max:25',
            'descripcion' => 'max:1000',
            'area' => 'max:11|',
            'id_modulo' => 'required|',
        ], [
            'nombre.required' => 'El nombre es obligatorio',
            'area.max' => 'El Ã¡rea es muy grande',
            'descripcion.max' => 'La descripciÃ³n es muy grande',
            'id_modulo.required' => 'El mÃ³dulo es obligatorio',
            'nombre.max' => 'El nombre es muy grande',
        ]);
        if (!$valida->fails()) {
            if (count(Lote::All()->where('nombre', '=', str_limit(mb_strtoupper(espacios($request->nombre)), 25))
                ->where('id_modulo', '=', $request->id_modulo)) == 0) {
                $model = new Lote();
                $model->nombre = str_limit(mb_strtoupper(espacios($request->nombre)), 25);
                $model->descripcion = str_limit((espacios($request->descripcion)), 1000);
                $model->area = $request->area;
                $model->id_modulo = $request->id_modulo;
                $model->fecha_registro = date('Y-m-d H:i:s');

                if ($model->save()) {
                    $model = Lote::All()->last();
                    $success = true;
                    $msg = '<div class="alert alert-success text-center">' .
                        '<p> Se ha guardado un nuevo lote satisfactoriamente</p>'
                        . '</div>';
                    bitacora('lote', $model->id_lote, 'I', 'InserciÃ³n satisfactoria de un nuevo lote');
                } else {
                    $success = false;
                    $msg = '<div class="alert alert-warning text-center">' .
                        '<p> Ha ocurrido un problema al guardar la informaciÃ³n al sistema</p>'
                        . '</div>';
                }
            } else {
                $success = false;
                $msg = '<div class="alert alert-warning text-center">' .
                    '<p> El lote "' . espacios($request->nombre) . '" ya se encuentra en este mÃ³dulo</p>'
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

    /* =====================================================*/

    public function edit_sector(Request $request)
    {
        if ($request->has('id_sector')) {
            $s = Sector::find($request->id_sector);
            if ($s != '') {
                return view('adminlte.gestion.sectores_modulos.forms.edit_sector', [
                    'sector' => $s,
                    'fincas_propias' => getFincasPropias()
                ]);
            } else {
                return '<div class="alert alert-warning text-center">No se ha encontrado el sector en el sistema</div>';
            }
        } else {
            return '<div class="alert alert-warning text-center">No se ha seleccionado un sector</div>';
        }
    }

    public function update_sector(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'nombre' => 'required|max:25',
            'id_sector' => 'required|',
            'interno' => 'required|',
            'descripcion' => 'max:1000|',
        ], [
            'nombre.required' => 'El nombre es obligatorio',
            'id_sector.required' => 'El sector es obligatorio',
            'interno.required' => 'El campo interno es obligatorio',
            'nombre.max' => 'El nombre es muy grande',
            'descripcion.max' => 'La descripciÃ³n es muy grande',
        ]);
        if (!$valida->fails()) {
            $finca = getFincaActiva();
            $existe = Sector::All()
                ->where('id_empresa', $finca)
                ->where('nombre', str_limit(mb_strtoupper(espacios($request->nombre)), 250))
                ->where('id_sector', '!=', $request->id_sector)
                ->first();
            if ($existe == '') {
                $model = Sector::find($request->id_sector);
                $model->nombre = str_limit(mb_strtoupper(espacios($request->nombre)), 250);
                $model->interno = $request->interno;
                $model->descripcion = str_limit((espacios($request->descripcion)), 1000);
                $finca_anterior = $model->id_empresa;
                $finca_actual = $finca;
                $model->id_empresa = $finca_actual;

                if ($model->save()) {
                    $success = true;
                    $msg = '<div class="alert alert-success text-center">' .
                        '<p> Se ha actualizado el sector satisfactoriamente</p>'
                        . '</div>';
                    bitacora('sector', $model->id_sector, 'U', 'ActualizaciÃ³n satisfactoria de un sector');

                    /* ================ ACTUALIZAR la finca_actual a modulos y ciclos dependientes ================ */
                    if ($finca_anterior != $finca_actual) {
                        foreach ($model->modulos as $mod) {
                            $mod->id_empresa = $finca_actual;
                            $mod->save();
                            $cicloActual = $mod->cicloActual();
                            if ($cicloActual != '') {
                                $cicloActual->id_empresa = $finca_actual;
                                $cicloActual->save();
                            }
                        }
                    }
                } else {
                    $success = false;
                    $msg = '<div class="alert alert-warning text-center">' .
                        '<p> Ha ocurrido un problema al guardar la informaciÃ³n al sistema</p>'
                        . '</div>';
                }
            } else {
                $success = false;
                $msg = '<div class="alert alert-warning text-center">' .
                    '<p> El sector "' . espacios($request->nombre) . '" ya se encuentra en el sistema</p>'
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

    public function cambiar_estado_sector(Request $request)
    {
        $success = false;
        $msg = '';
        if ($request->has('id_sector')) {
            $s = Sector::find($request->id_sector);
            if ($s != '') {
                $s->estado = $request->estado;
                if ($s->save()) {
                    $texto = $request->estado == 1 ? 'Se ha activado satisfactoriamente' : 'Se ha desactivado satisfactoriamente';
                    $msg = '<div class="alert alert-success text-center">' . $texto . '</div>';
                    $success = true;

                    bitacora('sector', $s->id_sector, 'U', 'Cambio de estado de un sector');
                } else {
                    $msg = '<div class="alert alert-warning text-center">No se ha podido guardar la informaciÃ³n en el sistema</div>';
                    $success = false;
                }
            } else {
                $msg = '<div class="alert alert-warning text-center">No se ha encontrado el sector en el sistema</div>';
                $success = false;
            }
        } else {
            $msg = '<div class="alert alert-warning text-center">No se ha seleccionado un sector</div>';
            $success = false;
        }
        return [
            'success' => $success,
            'mensaje' => $msg
        ];
    }

    public function edit_modulo(Request $request)
    {
        if ($request->has('id_modulo')) {
            $m = Modulo::find($request->id_modulo);
            $finca_actual = $request->has('finca_actual') ? $request->finca_actual : getUsuario(Session::get('id_usuario'))->finca_activa;
            $sectores = Sector::All()->where('estado', '=', 1);
            if ($finca_actual != 'T')
                $sectores = $sectores->where('id_empresa', '=', $finca_actual);
            if ($m != '') {
                return view('adminlte.gestion.sectores_modulos.forms.edit_modulo', [
                    'modulo' => $m,
                    'sectores' => $sectores,
                ]);
            } else {
                return '<div class="alert alert-warning text-center">No se ha encontrado el mÃ³dulo en el sistema</div>';
            }
        } else {
            return '<div class="alert alert-warning text-center">No se ha seleccionado un mÃ³dulo</div>';
        }
    }

    public function update_modulo(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'nombre' => 'required|max:25',
            'id_modulo' => 'required|',
            'area' => 'required|',
            'id_sector' => 'required|',
            'descripcion' => 'max:1000|',
        ], [
            'nombre.required' => 'El nombre es obligatorio',
            'area.required' => 'El Ã¡rea es obligatorio',
            'descripcion.max' => 'La descripciÃ³n es muy grande',
            'id_modulo.required' => 'El mÃ³dulo es obligatorio',
            'id_sector.required' => 'El sector es obligatorio',
            'nombre.max' => 'El nombre es muy grande',
        ]);
        if (!$valida->fails()) {
            if (count(Modulo::All()->where('nombre', '=', str_limit(mb_strtoupper(espacios($request->nombre)), 25))
                ->where('id_sector', '=', $request->id_sector)
                ->where('id_modulo', '!=', $request->id_modulo)) == 0) {
                $model = Modulo::find($request->id_modulo);
                $model->nombre = str_limit(mb_strtoupper(espacios($request->nombre)), 25);
                $model->descripcion = str_limit((espacios($request->descripcion)), 1000);
                $model->area = $request->area;
                $model->id_sector = $request->id_sector;
                $model->id_empresa = $model->sector->id_empresa;

                if ($model->save()) {
                    $success = true;
                    $msg = '<div class="alert alert-success text-center">' .
                        '<p> Se ha actualizado el mÃ³dulo satisfactoriamente</p>'
                        . '</div>';
                    bitacora('modulo', $model->id_modulo, 'U', 'ActualizaciÃ³n satisfactoria de un mÃ³dulo');
                } else {
                    $success = false;
                    $msg = '<div class="alert alert-warning text-center">' .
                        '<p> Ha ocurrido un problema al guardar la informaciÃ³n al sistema</p>'
                        . '</div>';
                }
            } else {
                $success = false;
                $msg = '<div class="alert alert-warning text-center">' .
                    '<p> El mÃ³dulo "' . espacios($request->nombre) . '" ya se encuentra en este sector</p>'
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

    public function cambiar_estado_modulo(Request $request)
    {
        $success = false;
        $msg = '';
        if ($request->has('id_modulo')) {
            $m = Modulo::find($request->id_modulo);
            if ($m != '') {
                $m->estado = $request->estado;
                if ($m->save()) {
                    $texto = $request->estado == 1 ? 'Se ha activado satisfactoriamente' : 'Se ha desactivado satisfactoriamente';
                    $msg = '<div class="alert alert-success text-center">' . $texto . '</div>';
                    $success = true;

                    bitacora('modulo', $m->id_modulo, 'U', 'Cambio de estado de un mÃ³dulo');
                } else {
                    $msg = '<div class="alert alert-warning text-center">No se ha podido guardar la informaciÃ³n en el sistema</div>';
                    $success = false;
                }
            } else {
                $msg = '<div class="alert alert-warning text-center">No se ha encontrado el mÃ³dulo en el sistema</div>';
                $success = false;
            }
        } else {
            $msg = '<div class="alert alert-warning text-center">No se ha seleccionado un sector</div>';
            $success = false;
        }
        return [
            'success' => $success,
            'mensaje' => $msg
        ];
    }

    public function edit_lote(Request $request)
    {
        if ($request->has('id_lote')) {
            $l = Lote::find($request->id_lote);
            if ($l != '') {
                return view('adminlte.gestion.sectores_modulos.forms.edit_lote', [
                    'lote' => $l,
                    'sectores' => Sector::All()->where('estado', '=', 1)
                ]);
            } else {
                return '<div class="alert alert-warning text-center">No se ha encontrado el lote en el sistema</div>';
            }
        } else {
            return '<div class="alert alert-warning text-center">No se ha seleccionado un lote</div>';
        }
    }

    public function update_lote(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'nombre' => 'required|max:50',
            'area' => '|max:11',
            'descripcion' => '|max:1000',
            'id_modulo' => 'required|',
            'id_lote' => 'required|',
        ], [
            'nombre.required' => 'El nombre es obligatorio',
            'id_modulo.required' => 'El mÃ³dulo es obligatorio',
            'id_lote.required' => 'El lote es obligatorio',
            'nombre.max' => 'El nombre es muy grande',
            'area.max' => 'El Ã¡rea es muy grande',
            'descripcion.max' => 'La descripciÃ³n es muy grande',
        ]);
        if (!$valida->fails()) {
            if (count(Lote::All()->where('nombre', '=', str_limit(mb_strtoupper(espacios($request->nombre)), 25))
                ->where('id_modulo', '=', $request->id_modulo)
                ->where('id_lote', '!=', $request->id_lote)) == 0) {
                $model = Lote::find($request->id_lote);
                $model->nombre = str_limit(mb_strtoupper(espacios($request->nombre)), 25);
                $model->descripcion = str_limit((espacios($request->descripcion)), 1000);
                $model->area = $request->area;
                $model->id_modulo = $request->id_modulo;
                if ($model->save()) {
                    $success = true;
                    $msg = '<div class="alert alert-success text-center">' .
                        '<p> Se ha actualizado el lote satisfactoriamente</p>'
                        . '</div>';
                    bitacora('lote', $model->id_lote, 'U', 'ActualizaciÃ³n satisfactoria de un lote');
                } else {
                    $success = false;
                    $msg = '<div class="alert alert-warning text-center">' .
                        '<p> Ha ocurrido un problema al guardar la informaciÃ³n al sistema</p>'
                        . '</div>';
                }
            } else {
                $success = false;
                $msg = '<div class="alert alert-warning text-center">' .
                    '<p> El lote "' . espacios($request->nombre) . '" ya se encuentra en este mÃ³dulo</p>'
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

    public function cambiar_estado_lote(Request $request)
    {
        $success = false;
        $msg = '';
        if ($request->has('id_lote')) {
            $l = Lote::find($request->id_lote);
            if ($l != '') {
                $l->estado = $request->estado;
                if ($l->save()) {
                    $texto = $request->estado == 1 ? 'Se ha activado satisfactoriamente' : 'Se ha desactivado satisfactoriamente';
                    $msg = '<div class="alert alert-success text-center">' . $texto . '</div>';
                    $success = true;

                    bitacora('lote', $l->id_lote, 'U', 'Cambio de estado de un lote');
                } else {
                    $msg = '<div class="alert alert-warning text-center">No se ha podido guardar la informaciÃ³n en el sistema</div>';
                    $success = false;
                }
            } else {
                $msg = '<div class="alert alert-warning text-center">No se ha encontrado el lote en el sistema</div>';
                $success = false;
            }
        } else {
            $msg = '<div class="alert alert-warning text-center">No se ha seleccionado un lote</div>';
            $success = false;
        }
        return [
            'success' => $success,
            'mensaje' => $msg
        ];
    }
}
