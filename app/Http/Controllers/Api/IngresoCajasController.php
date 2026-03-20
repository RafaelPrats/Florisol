<?php

namespace yura\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use yura\Http\Controllers\Controller;
use yura\Modelos\ApiStoreCajas;
use yura\Modelos\InventarioRecepcion;
use yura\Modelos\Variedad;

class IngresoCajasController extends Controller
{
    public function store_cajas(Request $request)
    {
        if ($request->header('X-API-KEY') !== env('API_KEY')) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado'
            ], 401);
        }
        $validator = Validator::make($request->all(), [
            'id_documento' => 'required|string|unique:api_store_cajas,documento',
            'fecha' => 'required|date',
            'cajas' => 'required|array|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422);
        }
        try {
            DB::beginTransaction();
            //return response()->json($request->all());
            $model_api = new ApiStoreCajas();
            $model_api->documento = $request->id_documento;
            $model_api->fecha = $request->fecha;
            $model_api->save();

            foreach ($request->cajas as $caja) {
                $empresa = $caja['destino'] == 1 ? 1 : 2;
                if (count($caja['variedades']) > 0) {
                    foreach ($caja['variedades'] as $var) {
                        $variedad = Variedad::where('codigo_latin', $var['codigo_variedad'])
                            ->first();
                        if ($variedad != '') {
                            $model_inventario = InventarioRecepcion::where('id_variedad', $variedad->id_variedad)
                                ->where('fecha', $request->fecha)
                                ->where('tallos_x_ramo', $var['tallos_x_ramo'])
                                ->where('longitud', $var['longitud'])
                                ->where('id_empresa', $empresa)
                                ->first();
                            if ($model_inventario == '') {
                                $model_inventario = new InventarioRecepcion();
                                $model_inventario->id_variedad = $variedad->id_variedad;
                                $model_inventario->fecha = $request->fecha;
                                $model_inventario->tallos_x_ramo = $var['tallos_x_ramo'];
                                $model_inventario->ramos = $caja['cantidad_cajas'] * $var['ramos_x_caja'];
                                $model_inventario->longitud = $var['longitud'];
                                $model_inventario->disponibles = $caja['cantidad_cajas'] * $var['ramos_x_caja'] * $var['tallos_x_ramo'];
                                $model_inventario->id_empresa = $empresa;
                                $model_inventario->save();
                            } else {
                                $model_inventario->ramos += $caja['cantidad_cajas'] * $var['ramos_x_caja'];
                                $model_inventario->disponibles += $caja['cantidad_cajas'] * $var['ramos_x_caja'] * $var['tallos_x_ramo'];
                                $model_inventario->save();
                            }
                        } else {
                            DB::rollBack();

                            return response()->json([
                                'success' => false,
                                'message' => 'No se ha encontrado la variedad con codigo: ' . $var['codigo_variedad'],
                            ], 422);
                        }
                    }
                } else {
                    DB::rollBack();

                    return response()->json([
                        'success' => false,
                        'message' => 'Hay cajas vacias',
                    ], 422);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Documento guardado correctamente',
                'id' => 0
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al guardar',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
