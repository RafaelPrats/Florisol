<?php

namespace yura\Http\Controllers\Postco;

use DB;
use Illuminate\Http\Request;
use yura\Http\Controllers\Controller;
use yura\Modelos\FincaProveedor;
use yura\Modelos\Submenu;

class MisFincasController extends Controller
{
    public function inicio(Request $request)
    {
        $listado = FincaProveedor::orderBy('nombre')
            ->get();
        return view('adminlte.gestion.postco.mis_fincas.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'listado' => $listado,
        ]);
    }

    public function store_finca(Request $request)
    {
        DB::beginTransaction();
        try {
            $telefonos = [];
            foreach (explode('|', $request->telefonos ?? '') as $telefono) {
                $telefono = preg_replace('/\D/', '', trim($telefono));
                if ($telefono === '') {
                    continue;
                }
                if (strlen($telefono) == 10 && substr($telefono, 0, 2) == '09') {
                    $telefono = '+593' . substr($telefono, 1);
                } elseif (strlen($telefono) == 12 && substr($telefono, 0, 4) == '5939') {
                    $telefono = '+' . $telefono;
                } else {
                    continue;
                }
                if (!in_array($telefono, $telefonos)) {
                    $telefonos[] = $telefono;
                }
            }

            $telefonos = implode('|', $telefonos);
            if ($telefonos != '') {
                $model = new FincaProveedor();
                $model->nombre = mb_strtoupper($request->nombre);
                $model->telefonos = $telefonos;
                $model->save();

                DB::commit();
                $success = true;
                $msg = 'Se ha <strong>GRABADO</strong> la finca correctamente';
            } else {
                DB::rollBack();
                $success = false;
                $msg = '<div class="alert alert-danger text-center">' .
                    '<h3>Debe ingresar un telefono valido</h3>' .
                    '</div>';
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

    public function update_finca(Request $request)
    {
        DB::beginTransaction();
        try {
            $telefonos = [];
            foreach (explode('|', $request->telefonos ?? '') as $telefono) {
                $telefono = preg_replace('/\D/', '', trim($telefono));
                if ($telefono === '') {
                    continue;
                }
                if (strlen($telefono) == 10 && substr($telefono, 0, 2) == '09') {
                    $telefono = '+593' . substr($telefono, 1);
                } elseif (strlen($telefono) == 12 && substr($telefono, 0, 4) == '5939') {
                    $telefono = '+' . $telefono;
                } else {
                    continue;
                }
                if (!in_array($telefono, $telefonos)) {
                    $telefonos[] = $telefono;
                }
            }

            $telefonos = implode('|', $telefonos);
            if ($telefonos != '') {
                $model = FincaProveedor::find($request->id);
                $model->nombre = mb_strtoupper($request->nombre);
                $model->telefonos = $telefonos;
                $model->save();

                DB::commit();
                $success = true;
                $msg = 'Se ha <strong>MODIFICADO</strong> la finca correctamente';
            } else {
                DB::rollBack();
                $success = false;
                $msg = '<div class="alert alert-danger text-center">' .
                    '<h3>Debe ingresar un telefono valido</h3>' .
                    '</div>';
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

    public function cambiar_estado(Request $request)
    {
        DB::beginTransaction();
        try {
            $model = FincaProveedor::find($request->id);
            $model->estado = !$model->estado;
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
