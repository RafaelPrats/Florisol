<?php

namespace yura\Http\Controllers;

use Illuminate\Http\Request;
use yura\Modelos\PermisoAccion;
use yura\Modelos\Submenu;
use yura\Modelos\Usuario;

class PermisosAccionesController extends Controller
{
    public function inicio(Request $request)
    {
        return view('adminlte.gestion.permisos_acciones.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
        ]);
    }

    public function buscar_listado_permisos(Request $request)
    {
        $listado = PermisoAccion::orderBy('accion')
            ->get();
        $usuarios = Usuario::where('estado', 'A')
            ->whereNotIn('id_rol', [1, 2])
            ->orderBy('nombre_completo')
            ->get();
        return view('adminlte.gestion.permisos_acciones.partials.listado', [
            'listado' => $listado,
            'usuarios' => $usuarios,
        ]);
    }

    public function store_permiso(Request $request)
    {
        $existe = PermisoAccion::All()
            ->where('id_usuario', $request->usuario)
            ->where('accion', $request->accion)
            ->first();
        if ($existe == '') {
            $model = new PermisoAccion();
            $model->id_usuario = $request->usuario;
            $model->accion = $request->accion;
            $model->save();
        }
        return [
            'success' => true,
            'mensaje' => 'Se ha <strong>GUARDADO</strong> un nuevo permiso',
        ];
    }

    public function update_permiso(Request $request)
    {
        $existe = PermisoAccion::where('id_usuario', $request->usuario)
            ->where('accion', $request->accion)
            ->where('id_permiso_accion', '!=', $request->id)
            ->get()
            ->first();
        if ($existe == '') {
            $model = PermisoAccion::find($request->id);
            $model->id_usuario = $request->usuario;
            $model->accion = $request->accion;
            $model->save();
        } else {
            return [
                'success' => false,
                'mensaje' => '<div class="alert alert-info text-center">Ya existe ese permiso en el sistema</div>',
            ];
        }
        return [
            'success' => true,
            'mensaje' => 'Se ha <strong>EDITADO</strong> el permiso',
        ];
    }

    public function desactivar_permiso(Request $request)
    {
        $model = PermisoAccion::find($request->id);
        $model->estado = $model->estado == 1 ? 0 : 1;
        $model->save();
        return [
            'success' => true,
            'mensaje' => 'Se ha <strong>MODIFICADO</strong> el permiso',
        ];
    }
}
