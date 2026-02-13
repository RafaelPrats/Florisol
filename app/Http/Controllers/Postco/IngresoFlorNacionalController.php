<?php

namespace yura\Http\Controllers\Postco;

use DB;
use Illuminate\Http\Request;
use yura\Http\Controllers\Controller;
use yura\Modelos\FincaFlorNacional;
use yura\Modelos\FlorNacional;
use yura\Modelos\MotivoFlorNacional;
use yura\Modelos\Planta;
use yura\Modelos\Submenu;
use yura\Modelos\Variedad;

class IngresoFlorNacionalController extends Controller
{
    public function inicio(Request $request)
    {
        $plantas = Planta::where('estado', 1)
            ->orderBy('nombre')
            ->get();
        $motivos = MotivoFlorNacional::where('estado', 1)
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.postco.ingreso_flor_nacional.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'plantas' => $plantas,
            'motivos' => $motivos,
        ]);
    }

    public function listar_reporte(Request $request)
    {
        $listado = FlorNacional::join('variedad as v', 'v.id_variedad', '=', 'flor_nacional.id_variedad')
            ->select('flor_nacional.*')->distinct()
            ->where('fecha', $request->fecha);
        if ($request->planta != '')
            $listado = $listado->where('v.id_planta', $request->planta);
        if ($request->variedad != '')
            $listado = $listado->where('flor_nacional.id_variedad', $request->variedad);
        if ($request->motivo != '')
            $listado = $listado->where('flor_nacional.id_motivo_flor_nacional', $request->motivo);
        $listado = $listado->get();
        $motivos = MotivoFlorNacional::where('estado', 1)
            ->orderBy('nombre')
            ->get();
        $fincas = FincaFlorNacional::where('estado', 1)
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.postco.ingreso_flor_nacional.partials.listado', [
            'listado' => $listado,
            'motivos' => $motivos,
            'fincas' => $fincas,
        ]);
    }

    public function modal_motivos(Request $request)
    {
        $motivos = MotivoFlorNacional::orderBy('nombre')
            ->get();
        return view('adminlte.gestion.postco.ingreso_flor_nacional.forms.modal_motivos', [
            'motivos' => $motivos,
        ]);
    }

    public function store_motivos(Request $request)
    {
        DB::beginTransaction();
        try {
            foreach (json_decode($request->data) as $data) {
                $model = new MotivoFlorNacional();
                $model->nombre = $data;
                $model->save();
            }

            DB::commit();
            $success = true;
            $msg = 'Se han <strong>GRABADO</strong> los motivos correctamente';
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
        DB::beginTransaction();
        try {
            $model = MotivoFlorNacional::find($request->id);
            $model->nombre = $request->nombre;
            $model->save();

            DB::commit();
            $success = true;
            $msg = 'Se ha <strong>MODIFICADO</strong> el motivo correctamente';
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
        DB::beginTransaction();
        try {
            $model = MotivoFlorNacional::find($request->id);
            $model->estado = $model->estado ? 0 : 1;
            $model->save();

            DB::commit();
            $success = true;
            $msg = 'Se ha <strong>MODIFICADO</strong> el motivo correctamente';
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

    public function modal_nacional(Request $request)
    {
        $plantas = Planta::where('estado', 1)
            ->orderBy('nombre')
            ->get();
        $motivos = MotivoFlorNacional::where('estado', 1)
            ->orderBy('nombre')
            ->get();
        $fincas = FincaFlorNacional::where('estado', 1)
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.postco.ingreso_flor_nacional.forms.modal_nacional', [
            'plantas' => $plantas,
            'motivos' => $motivos,
            'fincas' => $fincas,
        ]);
    }

    public function store_flor_nacional(Request $request)
    {
        DB::beginTransaction();
        try {
            foreach (json_decode($request->data) as $data) {
                $model = new FlorNacional();
                $model->id_variedad = $data->variedad;
                $model->id_motivo_flor_nacional = $data->motivo;
                $model->id_finca_flor_nacional = $data->finca_origen;
                $model->produccion = $data->produccion;
                $model->porcentaje = $data->porcentaje;
                $model->nacional = $data->nacional;
                $model->fecha = $data->fecha;
                $model->save();
                $model->id_flor_nacional = DB::table('flor_nacional')
                    ->select(DB::raw('max(id_flor_nacional) as id'))
                    ->get()[0]->id;
                bitacora('flor_nacional', $model->id_flor_nacional, 'I', 'STORE FLOR NACIONAL');
            }

            DB::commit();
            $success = true;
            $msg = 'Se ha <strong>GRABADO</strong> la flor nacional correctamente';
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

    public function update_flor_nacional(Request $request)
    {
        DB::beginTransaction();
        try {
            $model = FlorNacional::find($request->id);
            $model->id_motivo_flor_nacional = $request->motivo;
            $model->id_finca_flor_nacional = $request->finca_origen;
            $model->produccion = $request->produccion;
            $model->porcentaje = $request->porcentaje;
            $model->nacional = $request->nacional;
            $model->fecha = $request->fecha;
            $model->save();
            bitacora('flor_nacional', $model->id_flor_nacional, 'U', 'ACTUALIZAR FLOR NACIONAL');

            DB::commit();
            $success = true;
            $msg = 'Se ha <strong>GRABADO</strong> la flor nacional correctamente';
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

    public function delete_flor_nacional(Request $request)
    {
        DB::beginTransaction();
        try {
            $model = FlorNacional::find($request->id);
            bitacora('flor_nacional', $model->id_flor_nacional, 'D', 'Eliminar FLOR NACIONAL: ' . $model->variedad->nombre . ', ' . $model->motivo_flor_nacional->nombre . ', ' . $model->produccion . ' prod. ' . $model->porcentaje . '%');
            $model->delete();

            DB::commit();
            $success = true;
            $msg = 'Se ha <strong>ELIMINADO</strong> la flor nacional correctamente';
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

    public function modal_fincas(Request $request)
    {
        $fincas = FincaFlorNacional::orderBy('nombre')
            ->get();
        return view('adminlte.gestion.postco.ingreso_flor_nacional.forms.modal_fincas', [
            'fincas' => $fincas,
        ]);
    }

    public function store_fincas(Request $request)
    {
        DB::beginTransaction();
        try {
            foreach (json_decode($request->data) as $data) {
                $model = new FincaFlorNacional();
                $model->nombre = $data;
                $model->save();
            }

            DB::commit();
            $success = true;
            $msg = 'Se han <strong>GRABADO</strong> las fincas correctamente';
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

    public function update_finca(Request $request)
    {
        DB::beginTransaction();
        try {
            $model = FincaFlorNacional::find($request->id);
            $model->nombre = $request->nombre;
            $model->save();

            DB::commit();
            $success = true;
            $msg = 'Se ha <strong>MODIFICADO</strong> la finca correctamente';
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

    public function cambiar_estado_finca(Request $request)
    {
        DB::beginTransaction();
        try {
            $model = FincaFlorNacional::find($request->id);
            $model->estado = $model->estado ? 0 : 1;
            $model->save();

            DB::commit();
            $success = true;
            $msg = 'Se ha <strong>MODIFICADO</strong> la finca correctamente';
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
