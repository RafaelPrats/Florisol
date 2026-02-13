<?php

namespace yura\Http\Controllers\Campo;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use yura\Http\Controllers\Controller;
use yura\Modelos\Ciclo;
use yura\Modelos\CicloLuz;
use yura\Modelos\Planta;
use yura\Modelos\Submenu;
use Validator;

class LuzController extends Controller
{
    public function inicio(Request $request)
    {
        $plantas = Planta::where('estado', 1)
            //->where('tiene_ciclos', 1)
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.campo.ciclo_luz.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'plantas' => $plantas,
        ]);
    }

    public function listar_ciclo_luz(Request $request)
    {
        $finca = getFincaActiva();
        $ciclos = Ciclo::where('estado', 1)
            ->where('activo', 1)
            ->where('id_variedad', $request->variedad);
        if ($request->poda_siembra != 'T')
            $ciclos = $ciclos->where('poda_siembra', $request->poda_siembra);
        $ciclos = $ciclos->where('id_empresa', $finca)
            ->orderBy('fecha_inicio')
            ->get();
        return view('adminlte.gestion.campo.ciclo_luz.partials.listado', [
            'ciclos' => $ciclos,
            'fecha' => $request->fecha,
        ]);
    }

    public function store_luz(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'tipo_luz' => 'required',
            'lamparas' => 'required',
            'inicio_luz' => 'required',
            'fecha' => 'required',
            'dias_proy' => 'required',
            'hora_ini' => 'required',
            'hora_fin' => 'required',
        ], [
            'tipo_luz.required' => 'El tipo de luz es obligatorio',
            'lamparas.required' => 'El número de lámparas es obligatorio',
            'inicio_luz.required' => 'El inicio de luz es obligatorio',
            'hora_ini.required' => 'El horario de inicio es obligatorio',
            'hora_fin.required' => 'El horario de cierre es obligatorio',
            'fecha.required' => 'La fecha es obligatoria',
            'dias_proy.required' => 'Los días de duración son obligatorios',
        ]);
        if (!$valida->fails()) {
            $existe = CicloLuz::All()
                ->where('id_ciclo', $request->ciclo)
                ->where('fecha', $request->fecha)
                ->first();
            if ($existe == '') {
                $model = new CicloLuz();
                $model->id_ciclo = $request->ciclo;
                $model->tipo_luz = $request->tipo_luz;
                $model->tipo_lampara = $request->tipo_lampara;
                $model->lamparas = $request->lamparas;
                $model->inicio_luz = $request->inicio_luz;
                $model->dias_adicional = $request->dias_adicional > 0 ? $request->dias_adicional : 0;
                $model->fecha = $request->fecha;
                $model->dias_proy = $request->dias_proy;
                $model->hora_ini = $request->hora_ini;
                $model->hora_fin = $request->hora_fin;

                if ($model->save()) {
                    $model = CicloLuz::All()->last();
                    $success = true;
                    $msg = 'Se ha <strong>creado</strong> el registro de luz satisfactoriamente</p>';
                    bitacora('ciclo_luz', $model->id_ciclo_luz, 'I', 'Inserción satisfactoria de un nuevo ciclo_luz');
                } else {
                    $success = false;
                    $msg = 'Ha ocurrido un problema al guardar la información al sistema';
                }
            } else {
                $success = false;
                $msg = 'Ya existe un registro de luz del ciclo en el día indicado';
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
            $msg = '<p class="text-center">¡Por favor corrija los siguientes errores!</p>' .
                '<ul>' .
                $errores .
                '</ul>';
        }
        return [
            'mensaje' => $msg,
            'success' => $success
        ];
    }

    public function update_luz(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'tipo_luz' => 'required',
            'lamparas' => 'required',
            'inicio_luz' => 'required',
            'dias_proy' => 'required',
            'hora_ini' => 'required',
            'hora_fin' => 'required',
        ], [
            'tipo_luz.required' => 'El tipo de luz es obligatorio',
            'lamparas.required' => 'El número de lámparas es obligatorio',
            'inicio_luz.required' => 'El inicio de luz es obligatorio',
            'hora_ini.required' => 'El horario de inicio es obligatorio',
            'hora_fin.required' => 'El horario de cierre es obligatorio',
            'dias_proy.required' => 'Los días de duración son obligatorios',
        ]);
        if (!$valida->fails()) {
            $model = CicloLuz::find($request->id);
            $model->tipo_luz = $request->tipo_luz;
            $model->tipo_lampara = $request->tipo_lampara;
            $model->lamparas = $request->lamparas;
            $model->inicio_luz = $request->inicio_luz;
            $model->dias_adicional = $request->dias_adicional;
            $model->dias_proy = $request->dias_proy;
            $model->hora_ini = $request->hora_ini;
            $model->hora_fin = $request->hora_fin;

            if ($model->save()) {
                if ($model->fecha < hoy()) {    // se trata de una fecha anterior y hay que actualizar los mismos datos hasta hoy
                    $posteriores = CicloLuz::All()
                        ->where('id_ciclo', $model->id_ciclo)
                        ->where('fecha', '>', $model->fecha)
                        ->where('fecha', '<=', hoy());
                    foreach ($posteriores as $p) {
                        $p->tipo_luz = $model->tipo_luz;
                        $p->tipo_lampara = $model->tipo_lampara;
                        $p->lamparas = $model->lamparas;
                        $p->inicio_luz = $model->inicio_luz;
                        $p->dias_adicional = $model->dias_adicional;
                        $p->dias_proy = $model->dias_proy;
                        $p->hora_ini = $model->hora_ini;
                        $p->hora_fin = $model->hora_fin;
                        $p->save();
                    }
                } else {    // se trata de una fecha actual
                    /*$anteriores = CicloLuz::All()
                        ->where('id_ciclo', $model->id_ciclo)
                        ->where('fecha', '<=', $model->fecha);
                    foreach ($anteriores as $p) {
                        $p->inicio_luz = $model->inicio_luz;
                        $p->dias_adicional = $model->dias_adicional;
                        $p->dias_proy = $model->dias_proy;
                        $p->lamparas = $model->lamparas;
                        $p->save();
                    }*/
                }

                $success = true;
                $msg = 'Se ha <strong>actualizado</strong> el registro de luz</p>';
                bitacora('ciclo_luz', $model->id_ciclo_luz, 'I', 'Actualizacion satisfactoria del ciclo_luz');
            } else {
                $success = false;
                $msg = 'Ha ocurrido un problema al guardar la información al sistema';
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
            $msg = '<p class="text-center">¡Por favor corrija los siguientes errores!</p>' .
                '<ul>' .
                $errores .
                '</ul>';
        }
        return [
            'mensaje' => $msg,
            'success' => $success
        ];
    }

    public function ejecutar_luz(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'tipo_luz' => 'required',
            'lamparas' => 'required',
            'inicio_luz' => 'required',
            'dias_proy' => 'required',
            'hora_ini' => 'required',
            'hora_fin' => 'required',
        ], [
            'tipo_luz.required' => 'El tipo de luz es obligatorio',
            'lamparas.required' => 'El número de lámparas es obligatorio',
            'inicio_luz.required' => 'El inicio de luz es obligatorio',
            'hora_ini.required' => 'El horario de inicio es obligatorio',
            'hora_fin.required' => 'El horario de cierre es obligatorio',
            'dias_proy.required' => 'Los días de duración son obligatorios',
        ]);
        if (!$valida->fails()) {
            $model = CicloLuz::find($request->id);
            $model->tipo_luz = $request->tipo_luz;
            $model->tipo_lampara = $request->tipo_lampara;
            $model->lamparas = $request->lamparas;
            $model->inicio_luz = $request->inicio_luz;
            $model->dias_adicional = $request->dias_adicional;
            $model->dias_proy = $request->dias_proy;
            $model->hora_ini = $request->hora_ini;
            $model->hora_fin = $request->hora_fin;

            if ($model->save()) {
                if ($model->fecha < hoy()) {    // se trata de una fecha anterior y hay que actualizar los mismos datos hasta hoy
                    $posteriores = CicloLuz::All()
                        ->where('id_ciclo', $model->id_ciclo)
                        ->where('fecha', '>', $model->fecha)
                        ->where('fecha', '<=', hoy());
                    foreach ($posteriores as $p) {
                        $p->tipo_luz = $model->tipo_luz;
                        $p->tipo_lampara = $model->tipo_lampara;
                        $p->lamparas = $model->lamparas;
                        $p->inicio_luz = $model->inicio_luz;
                        $p->dias_adicional = $model->dias_adicional;
                        $p->dias_proy = $model->dias_proy;
                        $p->hora_ini = $model->hora_ini;
                        $p->hora_fin = $model->hora_fin;
                        $p->save();
                    }
                } else {    // se trata de una fecha actual
                    /*$anteriores = CicloLuz::All()
                        ->where('id_ciclo', $model->id_ciclo)
                        ->where('fecha', '<=', $model->fecha);
                    foreach ($anteriores as $p) {
                        $p->inicio_luz = $model->inicio_luz;
                        $p->dias_adicional = $model->dias_adicional;
                        $p->dias_proy = $model->dias_proy;
                        $p->lamparas = $model->lamparas;
                        $p->save();
                    }*/
                }

                $ciclo = $model->ciclo;
                if ($request->tipo == 'I') {
                    $inicio_luz = opDiasFecha('+', $model->inicio_luz, $ciclo->fecha_inicio);
                    $ciclo->ejec_ini_luz = $inicio_luz;
                } else {
                    $fin_luz = opDiasFecha('+', $model->inicio_luz + $model->dias_proy + $model->dias_adicional - 1, $ciclo->fecha_inicio);
                    $ciclo->ejec_fin_luz = $fin_luz;
                }
                $ciclo->save();

                $success = true;
                $msg = 'Se ha <strong>actualizado</strong> el registro de luz</p>';
                bitacora('ciclo_luz', $model->id_ciclo_luz, 'I', 'Actualizacion satisfactoria del ciclo_luz');
            } else {
                $success = false;
                $msg = 'Ha ocurrido un problema al guardar la información al sistema';
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
            $msg = '<p class="text-center">¡Por favor corrija los siguientes errores!</p>' .
                '<ul>' .
                $errores .
                '</ul>';
        }
        return [
            'mensaje' => $msg,
            'success' => $success
        ];
    }

    public function store_all(Request $request)
    {
        foreach ($request->data as $d) {
            $model = CicloLuz::All()
                ->where('id_ciclo', $d['ciclo'])
                ->where('fecha', $request->fecha)
                ->first();
            if ($model == '') {
                $model = new CicloLuz();
                $model->id_ciclo = $d['ciclo'];
                $model->fecha = $request->fecha;
            }
            $model->tipo_luz = $d['tipo_luz'];
            $model->tipo_lampara = $d['tipo_lampara'];
            $model->lamparas = $d['lamparas'];
            $model->inicio_luz = $d['inicio_luz'];
            $model->dias_adicional = $d['dias_adicional'] > 0 ? $d['dias_adicional'] : 0;
            $model->dias_proy = $d['dias_proy'];
            $model->hora_ini = $d['hora_ini'];
            $model->hora_fin = $d['hora_fin'];
            $model->save();

            if ($model->fecha < hoy()) {    // se trata de una fecha anterior y hay que actualizar los mismos datos hasta hoy
                $posteriores = CicloLuz::All()
                    ->where('id_ciclo', $model->id_ciclo)
                    ->where('fecha', '>', $model->fecha)
                    ->where('fecha', '<=', hoy());
                foreach ($posteriores as $p) {
                    $p->tipo_luz = $model->tipo_luz;
                    $p->lamparas = $model->lamparas;
                    $p->inicio_luz = $model->inicio_luz;
                    $p->dias_adicional = $model->dias_adicional;
                    $p->dias_proy = $model->dias_proy;
                    $p->hora_ini = $model->hora_ini;
                    $p->hora_fin = $model->hora_fin;
                    $p->save();
                }
            }
        }
        return [
            'success' => true,
            'mensaje' => 'Se ha <strong>GUARDADO</strong> la información correctamente</p>',
        ];
    }
}
