<?php

namespace yura\Http\Controllers\Postco;

use DB;
use Illuminate\Http\Request;
use yura\Http\Controllers\Controller;
use yura\Modelos\MotivoReclamo;
use yura\Modelos\Submenu;

class MotivosReclamosController extends Controller
{
    public function inicio(Request $request)
    {
        return view('adminlte.gestion.postco.motivos_reclamos.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
        ]);
    }

    public function listar_reporte(Request $request)
    {
        $listado = MotivoReclamo::get();
        return view('adminlte.gestion.postco.motivos_reclamos.partials.listado', [
            'listado' => $listado
        ]);
    }

    public function store_motivo(Request $request)
    {
        try {
            DB::beginTransaction();
            $model = new MotivoReclamo();
            $model->nombre = $request->nombre;
            $model->save();
            $model->id_motivo_reclamo = DB::table('motivo_reclamo')
                ->select(DB::raw('max(id_motivo_reclamo) as id'))
                ->get();

            DB::commit();
            $success = true;
            $msg = 'Se ha <strong>GRABADO</strong> un nuevo motivo correctamente';
            bitacora('motivo_reclamo', $model->id_motivo_reclamo, 'I', 'NUEVO motivo');
        } catch (\Exception $e) {
            DB::rollBack();
            $success = false;
            $msg = '<div class="alert alert-danger text-center">' .
                '<p> Ha ocurrido un problema al guardar la informacion al sistema</p>' .
                '<p>' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine() . '</p>'
                . '</div>';
        }

        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }

    public function update_motivo(Request $request)
    {
        try {
            DB::beginTransaction();
            $model = MotivoReclamo::find($request->id);
            $model->nombre = $request->nombre;
            $model->save();

            DB::commit();
            $success = true;
            $msg = 'Se ha <strong>ACTUALIZADO</strong> el motivo correctamente';
            bitacora('motivo_reclamo', $model->id_motivo_reclamo, 'U', 'EDITAR motivo');
        } catch (\Exception $e) {
            DB::rollBack();
            $success = false;
            $msg = '<div class="alert alert-danger text-center">' .
                '<p> Ha ocurrido un problema al guardar la informacion al sistema</p>' .
                '<p>' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine() . '</p>'
                . '</div>';
        }

        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }

    public function cambiar_estado_motivo(Request $request)
    {
        try {
            DB::beginTransaction();
            $model = MotivoReclamo::find($request->id);
            $model->estado = $model->estado == 1 ? 0 : 1;
            $model->save();

            DB::commit();
            $success = true;
            $msg = 'Se ha <strong>ACTUALIZADO</strong> el motivo correctamente';
            bitacora('motivo_reclamo', $model->id_motivo_reclamo, 'U', 'CAMBIAR ESTADO de motivo');
        } catch (\Exception $e) {
            DB::rollBack();
            $success = false;
            $msg = '<div class="alert alert-danger text-center">' .
                '<p> Ha ocurrido un problema al guardar la informacion al sistema</p>' .
                '<p>' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine() . '</p>'
                . '</div>';
        }

        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }
}
