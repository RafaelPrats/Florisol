<?php

namespace yura\Http\Controllers\Bodega;

use Illuminate\Http\Request;
use yura\Http\Controllers\Controller;
use yura\Modelos\CategoriaProducto;
use yura\Modelos\Submenu;
use Validator;

class CategoriaProductoController extends Controller
{
    public function inicio(Request $request)
    {
        return view('adminlte.gestion.bodega.categorias_producto.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
        ]);
    }

    public function listar_reporte(Request $request)
    {
        $listado = CategoriaProducto::where('nombre', 'like', '%' . mb_strtoupper($request->busqueda))
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.bodega.categorias_producto.partials.listado', [
            'listado' => $listado,
        ]);
    }

    public function store_categoria(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'nombre' => 'required|max:500|unique:categoria_producto',
        ], [
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.unique' => 'El nombre ya existe',
            'nombre.max' => 'El nombre es muy grande',
        ]);
        if (!$valida->fails()) {
            $model = new CategoriaProducto();
            $model->nombre = espacios(mb_strtoupper($request->nombre));
            $model->save();
            $model = CategoriaProducto::All()->last();

            bitacora('categoria_producto', $model->id_categoria_producto, 'I', 'Creacion de la categoria');
            $success = true;
            $msg = 'Se ha <strong>CREADO</strong> la categoria satisfactoriamente';
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

    public function update_categoria(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'nombre' => 'required|max:500',
        ], [
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.max' => 'El nombre es muy grande',
        ]);
        if (!$valida->fails()) {
            $existe_nombre = CategoriaProducto::All()
                ->where('id_categoria_producto', '!=', $request->id)
                ->where('nombre', espacios(mb_strtoupper($request->nombre)))
                ->first();
            if ($existe_nombre != '') {
                $success = false;
                $msg = '<div class="alert alert-warning text-center">' .
                    '<p>El nombre de la categoria y existe</p>'
                    . '</div>';
            } else {
                $model = CategoriaProducto::find($request->id);
                $model->nombre = espacios(mb_strtoupper($request->nombre));
                $model->save();
                $success = true;
                $msg = 'Se ha <strong>MODIFICADO</strong> la categoria satisfactoriamente';
                bitacora('categoria_producto', $model->id_categoria_producto, 'U', 'Modifico la categoria');
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

    public function cambiar_estado_categoria(Request $request)
    {
        $model = CategoriaProducto::find($request->id);
        $model->estado = $model->estado == 1 ? 0 : 1;
        $model->save();

        $success = true;
        $msg = 'Se ha <strong>MODIFICADO</strong> la categoria satisfactoriamente';
        return [
            'mensaje' => $msg,
            'success' => $success
        ];
    }
}
