<?php

namespace yura\Http\Controllers;

use Illuminate\Http\Request;
use yura\Modelos\ConfiguracionEmpresa;
use yura\Modelos\Submenu;
use yura\Modelos\SuperFinca;
use Validator;

class FincasController extends Controller
{
    public function inicio(Request $request)
    {
        return view('adminlte.gestion.fincas.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
        ]);
    }

    public function listar_super_fincas(Request $request)
    {
        $listado = SuperFinca::where('estado', 1)->orderBy('nombre')->get();
        return view('adminlte.gestion.fincas.partials.listado_super_fincas', [
            'listado' => $listado,
        ]);
    }

    public function listar_fincas(Request $request)
    {
        $listado = ConfiguracionEmpresa::orderBy('nombre')->get();
        $super_fincas = SuperFinca::where('estado', 1)->orderBy('nombre')->get();
        return view('adminlte.gestion.fincas.partials.listado_fincas', [
            'listado' => $listado,
            'super_fincas' => $super_fincas,
        ]);
    }

    public function store_super_finca(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'nombre' => 'required|max:250|unique:super_finca',
        ], [
            'nombre.unique' => 'El nombre ya existe',
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.max' => 'El nombre es muy grande',
        ]);
        if (!$valida->fails()) {
            $model = new SuperFinca();
            $model->nombre = str_limit(mb_strtoupper(espacios($request->nombre)), 250);

            if ($model->save()) {
                $model = SuperFinca::All()->last();
                $success = true;
                $msg = '<div class="alert alert-success text-center">' .
                    '<p> Se ha guardado la empresa satisfactoriamente</p>'
                    . '</div>';
                bitacora('super_finca', $model->id_super_finca, 'I', 'Inserción satisfactoria de una nueva super_finca');
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

    public function update_finca(Request $request)
    {
        $finca = ConfiguracionEmpresa::find($request->finca);
        $finca->id_super_finca = $request->super_finca;
        $finca->save();
        return [
            'success' => true,
            'mensaje' => '<div class="alert alert-success text-center">Se ha modificado la finca satisfactoriamente</div>',
        ];
    }

    public function update_super_finca(Request $request)
    {
        $existe = SuperFinca::All()
            ->where('id_super_finca', '!=', $request->super_finca)
            ->where('nombre', str_limit(mb_strtoupper(espacios($request->nombre)), 250))
            ->first();
        if ($existe == '') {
            $sf = SuperFinca::find($request->super_finca);
            $sf->nombre = str_limit(mb_strtoupper(espacios($request->nombre)), 250);
            $sf->save();

            $success = true;
            $msg = '<div class="alert alert-success text-center">Se ha modificado la finca satisfactoriamente</div>';
        } else {
            $success = false;
            $msg = '<div class="alert alert-danger text-center">El nombre ya existe</div>';
        }

        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }
}
