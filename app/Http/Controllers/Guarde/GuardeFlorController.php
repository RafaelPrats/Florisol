<?php

namespace yura\Http\Controllers\Guarde;

use Illuminate\Http\Request;
use yura\Http\Controllers\Controller;
use yura\Modelos\Variedad;
use yura\Modelos\Planta;
use yura\Modelos\Submenu;
use yura\Modelos\Guarde;
use yura\Modelos\SalidasGuarde;
use Illuminate\Support\Facades\DB;

class GuardeFlorController extends Controller
{
    public function inicio(Request $request)
    {
        $variedades = Variedad::where('estado', 1)
            ->where('receta', 0)
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.guarde.guarde_flor.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'variedades' => $variedades,
        ]);
    }

    public function listar_reporte(Request $request)
    {
        $listado = Guarde::where('fecha', '>=', $request->desde)
            ->where('fecha', '<=', $request->hasta);
        if($request->variedad != 'T')
            $listado = $listado->where('id_variedad', $request->variedad);
        $listado = $listado->orderBy('fecha')
            ->get();
        return view('adminlte.gestion.guarde.guarde_flor.partials.listado', [
            'listado' => $listado,
        ]);
    }

    public function add_guardes(Request $request)
    {
        $plantas = Planta::where('estado', 1)
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.guarde.guarde_flor.forms.add_guardes', [
            'plantas' => $plantas,
        ]);
    }

    public function store_guardes(Request $request)
    {
        DB::beginTransaction();
        try {
            foreach(json_decode($request->data) as $data){
                $model = Guarde::where('id_variedad', $data->variedad)
                    ->where('tallos_x_malla', $data->tallos_x_malla)
                    ->where('fecha', $request->fecha)
                    ->get()
                    ->first();
                if($model == ''){
                    $model = new Guarde();
                    $model->id_variedad = $data->variedad;
                    $model->fecha = $request->fecha;
                    $model->mallas = $data->mallas;
                    $model->disponibles = $data->mallas;
                    $model->tallos_x_malla = $data->tallos_x_malla;
                    $model->save();
                } else {
                    $model->mallas += $data->mallas;
                    $model->disponibles += $data->mallas;
                    $model->save();
                }
            }

            DB::commit();
            $success = true;
            $msg = 'Se ha <strong>GRABADO</strong> el guarde de flor correctamente';
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

    public function update_guarde(Request $request)
    {
        DB::beginTransaction();
        try {
            $model = Guarde::find($request->id);
            $model->disponibles = $request->disponibles;
            $model->tallos_x_malla = $request->tallos_x_malla;
            $model->save();

            DB::commit();
            $success = true;
            $msg = 'Se ha <strong>MODIFICADO</strong> el guarde de flor correctamente';
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

    public function delete_guarde(Request $request)
    {
        DB::beginTransaction();
        try {
            $model = Guarde::find($request->id);
            $model->delete();

            DB::commit();
            $success = true;
            $msg = 'Se ha <strong>ELIMINADO</strong> el guarde de flor correctamente';
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

    public function sacar_guarde(Request $request)
    {
        DB::beginTransaction();
        try {
            $model = Guarde::find($request->id);
            if($model->disponibles >= $request->sacar){
                $model->disponibles -= $request->sacar;
                $model->save();

                $salida = new SalidasGuarde();
                $salida->id_guarde = $model->id_guarde;
                $salida->fecha = hoy();
                $salida->cantidad = $request->sacar;
                $salida->save();

                DB::commit();
                $success = true;
                $msg = 'Se ha <strong>GRABADO</strong> la salida correctamente';
            } else {
                DB::rollBack();
                $success = false;
                $msg = '<div class="alert alert-warning text-center">La cantidad a sacar debe ser menor o igual a las mallas disponibles</div>';
            }
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
