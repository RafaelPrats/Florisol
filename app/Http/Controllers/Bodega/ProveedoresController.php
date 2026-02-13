<?php

namespace yura\Http\Controllers\Bodega;

use Illuminate\Http\Request;
use yura\Http\Controllers\Controller;
use yura\Modelos\Proveedor;
use yura\Modelos\Submenu;
use Validator;

class ProveedoresController extends Controller
{
    public function inicio(Request $request)
    {
        return view('adminlte.gestion.bodega.proveedores.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
        ]);
    }

    public function listar_reporte(Request $request)
    {
        $listado = Proveedor::where('nombre', 'like', '%' . mb_strtoupper($request->busqueda))
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.bodega.proveedores.partials.listado', [
            'listado' => $listado,
        ]);
    }

    public function store_proveedor(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'nombre' => 'required|max:500|unique:proveedor',
        ], [
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.unique' => 'El nombre ya existe',
            'nombre.max' => 'El nombre es muy grande',
        ]);
        if (!$valida->fails()) {
            $model = new Proveedor();
            $model->nombre = espacios(mb_strtoupper($request->nombre));
            $model->save();
            $model = Proveedor::All()->last();

            bitacora('proveedor', $model->id_proveedor, 'I', 'Creacion del proveedor');
            $success = true;
            $msg = 'Se ha <strong>CREADO</strong> el proveedor satisfactoriamente';
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

    public function update_proveedor(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'nombre' => 'required|max:500',
        ], [
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.max' => 'El nombre es muy grande',
        ]);
        if (!$valida->fails()) {
            $existe_nombre = Proveedor::All()
                ->where('id_proveedor', '!=', $request->id)
                ->where('nombre', espacios(mb_strtoupper($request->nombre)))
                ->first();
            if ($existe_nombre != '') {
                $success = false;
                $msg = '<div class="alert alert-warning text-center">' .
                    '<p>El nombre del proveedor y existe</p>'
                    . '</div>';
            } else {
                $model = Proveedor::find($request->id);
                $model->nombre = espacios(mb_strtoupper($request->nombre));
                $model->save();
                $success = true;
                $msg = 'Se ha <strong>MODIFICADO</strong> el proveedor satisfactoriamente';
                bitacora('proveedor', $model->id_proveedor, 'U', 'Modifico el proveedor');
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

    public function cambiar_estado_proveedor(Request $request)
    {
        $model = Proveedor::find($request->id);
        $model->estado = $model->estado == 1 ? 0 : 1;
        $model->save();

        $success = true;
        $msg = 'Se ha <strong>MODIFICADO</strong> el proveedor satisfactoriamente';
        return [
            'mensaje' => $msg,
            'success' => $success
        ];
    }
}
