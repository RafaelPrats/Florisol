<?php

namespace yura\Http\Controllers\Postco;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use yura\Http\Controllers\Controller;
use yura\Modelos\ConfiguracionEmpresa;
use yura\Modelos\DetalleApiStoreCajas;
use yura\Modelos\IngresoRecepcion;
use yura\Modelos\InventarioRecepcion;
use yura\Modelos\Planta;
use yura\Modelos\SalidasRecepcion;
use yura\Modelos\Segmento;
use yura\Modelos\Submenu;

class InventarioRecepcionController extends Controller
{
    public function inicio(Request $request)
    {
        $finca = getFincaActiva();
        $plantas = Planta::where('estado', 1)
            ->where('id_empresa', $finca)
            ->orderBy('nombre')
            ->get();
        $documentos = DB::table('api_store_cajas as api')
            ->join('detalle_api_store_cajas as detApi', 'detApi.id_api_store_cajas', '=', 'api.id_api_store_cajas')
            ->select('api.*')
            ->distinct()
            ->where('detApi.id_empresa', $finca)
            ->where('detApi.estado', 'P')
            ->get();
        return view('adminlte.gestion.postco.ingreso_inventario.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'plantas' => $plantas,
            'documentos' => $documentos,
        ]);
    }

    public function listar_reporte(Request $request)
    {
        $finca = getFincaActiva();
        $plantas = DB::table('inventario_recepcion as i')
            ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->leftJoin('detalle_api_store_cajas as da', function ($join) use ($finca, $request) {
                $join->on('da.id_variedad', '=', 'i.id_variedad')
                    ->where('da.id_empresa', '=', $finca);
            })
            ->select('v.id_planta', 'p.nombre')
            ->distinct()
            ->where('i.id_empresa', $finca)
            ->where('i.bodega', $request->bodega)
            ->where(function ($query) {
                $query->where('i.disponibles', '>', 0)
                    ->orWhere('da.estado', 'P');
            })
            ->when($request->planta != '', function ($query) use ($request) {
                return $query->where('v.id_planta', $request->planta);
            })
            ->orderBy('p.nombre')
            ->get();
        $listado = [];
        foreach ($plantas as $pta) {
            $variedades = DB::table('inventario_recepcion as i')
                ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
                ->leftJoin('detalle_api_store_cajas as da', function ($join) use ($finca, $request) {
                    $join->on('da.id_variedad', '=', 'i.id_variedad')
                        ->where('da.id_empresa', '=', $finca);
                })
                ->select(
                    'i.*',
                    'v.nombre'
                )
                ->distinct()
                ->where('i.id_empresa', $finca)
                ->where('i.bodega', $request->bodega)
                ->where('v.id_planta', $pta->id_planta)
                ->where(function ($query) {
                    $query->where('i.disponibles', '>', 0)
                        ->orWhere('da.estado', 'P');
                })
                ->when($request->variedad != '', function ($query) use ($request) {
                    return $query->where('i.id_variedad', $request->variedad);
                })
                ->orderBy('v.nombre')
                ->get();
            $listado[] = [
                'planta' => $pta,
                'variedades' => $variedades,
            ];
        }

        return view('adminlte.gestion.postco.ingreso_inventario.partials.listado', [
            'listado' => $listado,
            'documento' => $request->documento,
        ]);
    }

    public function modal_add(Request $request)
    {
        $proveedores = ConfiguracionEmpresa::where('proveedor', 1)
            ->orderBy('id_configuracion_empresa')
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.postco.ingreso_inventario.forms.modal_add', [
            'proveedores' => $proveedores
        ]);
    }

    public function seleccionar_proveedor(Request $request)
    {
        $finca = getFincaActiva();
        if ($request->id_proveedor > -1) {
            $plantas = DB::table('planta as p')
                ->join('variedad as v', 'v.id_planta', '=', 'p.id_planta')
                ->join('variedad_proveedor as vp', 'vp.id_variedad', '=', 'v.id_variedad')
                ->select('p.*')->distinct()
                ->where('p.id_empresa', $finca)
                ->where('vp.id_proveedor', $request->id_proveedor)
                ->orderBy('p.nombre')
                ->get();
        } else {
            $plantas = DB::table('planta as p')
                ->select('p.*')->distinct()
                ->where('p.id_empresa', $finca)
                ->orderBy('p.nombre')
                ->get();
        }
        $option_plantas = '<option value="">Seleccione</option>';
        foreach ($plantas as $p) {
            $option_plantas .= '<option value="' . $p->id_planta . '">' . $p->nombre . '</option>';
        }
        return [
            'plantas' => $option_plantas
        ];
    }

    public function seleccionar_planta(Request $request)
    {
        $finca = getFincaActiva();
        if ($request->id_proveedor > -1) {
            $variedades = DB::table('variedad as v')
                ->join('variedad_proveedor as vp', 'vp.id_variedad', '=', 'v.id_variedad')
                ->select('v.*')->distinct()
                ->where('v.id_empresa', $finca)
                ->where('v.id_planta', $request->id_planta)
                ->where('vp.id_proveedor', $request->id_proveedor)
                ->orderBy('v.nombre')
                ->get();
        } else {
            $variedades = DB::table('variedad as v')
                ->select('v.*')->distinct()
                ->where('v.id_planta', $request->id_planta)
                ->where('v.id_empresa', $finca)
                ->orderBy('v.nombre')
                ->get();
        }
        $option_variedades = '<option value="">Seleccione</option>';
        foreach ($variedades as $p) {
            $option_variedades .= '<option value="' . $p->id_variedad . '">' . $p->nombre . '</option>';
        }
        return [
            'variedades' => $option_variedades
        ];
    }

    public function store_inventario(Request $request)
    {
        try {
            DB::beginTransaction();
            $finca = getFincaActiva();
            foreach (json_decode($request->data) as $data) {
                $model_inventario = InventarioRecepcion::where('id_variedad', $data->variedad)
                    ->where('fecha', $data->fecha)
                    ->where('tallos_x_ramo', $data->tallos_x_ramo)
                    ->where('longitud', $data->longitud)
                    ->where('id_empresa', $finca)
                    ->where('bodega', $data->bodega)
                    ->first();
                if ($model_inventario == '') {
                    $model_inventario = new InventarioRecepcion();
                    $model_inventario->id_variedad = $data->variedad;
                    $model_inventario->fecha = $data->fecha;
                    $model_inventario->tallos_x_ramo = $data->tallos_x_ramo;
                    $model_inventario->ramos = $data->ramos;
                    $model_inventario->bodega = $data->bodega;
                    $model_inventario->longitud = $data->longitud;
                    $model_inventario->disponibles = $data->ramos * $data->tallos_x_ramo;
                    $model_inventario->id_empresa = $finca;
                    $model_inventario->save();
                } else {
                    $model_inventario->ramos += $data->ramos;
                    $model_inventario->disponibles += $data->ramos * $data->tallos_x_ramo;
                    $model_inventario->save();
                }

                $ingreso = new IngresoRecepcion();
                $ingreso->id_variedad = $data->variedad;
                $ingreso->id_proveedor = $data->proveedor;
                $ingreso->fecha_registro = date('Y-m-d H:i:s');
                $ingreso->fecha = $data->fecha;
                $ingreso->tallos_x_ramo = $data->tallos_x_ramo;
                $ingreso->ramos = $data->ramos;
                $ingreso->bodega = $data->bodega;
                $ingreso->longitud = $data->longitud;
                $ingreso->id_empresa = $finca;
                $ingreso->save();
            }

            $success = true;
            $msg = 'Se ha <strong>GRABADO</strong> la informacion correctamente';

            DB::commit();
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

    public function recibir_all_pendientes(Request $request)
    {
        try {
            DB::beginTransaction();

            foreach (json_decode($request->data) as $data) {
                $model = InventarioRecepcion::find($data->id_inv);
                $data->ramos_ventas = $data->ramos_ventas >= 0 ? $data->ramos_ventas : 0;
                $data->ramos_produccion = $data->ramos_produccion >= 0 ? $data->ramos_produccion : 0;
                $detApi = DetalleApiStoreCajas::find($data->id_detApi);
                if ($data->ramos_ventas > 0) {
                    $model_inventario = InventarioRecepcion::where('id_variedad', $model->id_variedad)
                        ->where('fecha', $model->fecha)
                        ->where('tallos_x_ramo', $model->tallos_x_ramo)
                        ->where('longitud', $model->longitud)
                        ->where('id_empresa', $model->id_empresa)
                        ->where('bodega', 'V')
                        ->first();
                    if ($model_inventario == '') {
                        $model_inventario = new InventarioRecepcion();
                        $model_inventario->id_variedad = $model->id_variedad;
                        $model_inventario->fecha = $model->fecha;
                        $model_inventario->tallos_x_ramo = $model->tallos_x_ramo;
                        $model_inventario->ramos = $data->ramos_ventas;
                        $model_inventario->bodega = 'V';
                        $model_inventario->longitud = $model->longitud;
                        $model_inventario->disponibles = $data->ramos_ventas * $model->tallos_x_ramo;
                        $model_inventario->id_empresa = $model->id_empresa;
                        $model_inventario->save();
                    } else {
                        $model_inventario->ramos += $data->ramos_ventas;
                        $model_inventario->disponibles += $data->ramos_ventas * $model->tallos_x_ramo;
                        $model_inventario->save();
                    }

                    $ingreso = new IngresoRecepcion();
                    $ingreso->id_variedad = $model_inventario->id_variedad;
                    $ingreso->id_api_store_cajas = $detApi->id_api_store_cajas;
                    $ingreso->fecha_registro = date('Y-m-d H:i:s');
                    $ingreso->fecha = $model_inventario->fecha;
                    $ingreso->tallos_x_ramo = $model_inventario->tallos_x_ramo;
                    $ingreso->ramos = $data->ramos_ventas;
                    $ingreso->bodega = 'V';
                    $ingreso->longitud = $model_inventario->longitud;
                    $ingreso->id_empresa = $model_inventario->id_empresa;
                    $ingreso->save();
                }

                if ($data->ramos_produccion > 0) {
                    $model_inventario = InventarioRecepcion::where('id_variedad', $model->id_variedad)
                        ->where('fecha', $model->fecha)
                        ->where('tallos_x_ramo', $model->tallos_x_ramo)
                        ->where('longitud', $model->longitud)
                        ->where('id_empresa', $model->id_empresa)
                        ->where('bodega', 'P')
                        ->first();
                    if ($model_inventario == '') {
                        $model_inventario = new InventarioRecepcion();
                        $model_inventario->id_variedad = $model->id_variedad;
                        $model_inventario->fecha = $model->fecha;
                        $model_inventario->tallos_x_ramo = $model->tallos_x_ramo;
                        $model_inventario->ramos = $data->ramos_produccion;
                        $model_inventario->bodega = 'P';
                        $model_inventario->longitud = $model->longitud;
                        $model_inventario->disponibles = $data->ramos_produccion * $model->tallos_x_ramo;
                        $model_inventario->id_empresa = $model->id_empresa;
                        $model_inventario->save();
                    } else {
                        $model_inventario->ramos += $data->ramos_produccion;
                        $model_inventario->disponibles += $data->ramos_produccion * $model->tallos_x_ramo;
                        $model_inventario->save();
                    }

                    $ingreso = new IngresoRecepcion();
                    $ingreso->id_variedad = $model_inventario->id_variedad;
                    $ingreso->id_api_store_cajas = $detApi->id_api_store_cajas;
                    $ingreso->fecha_registro = date('Y-m-d H:i:s');
                    $ingreso->fecha = $model_inventario->fecha;
                    $ingreso->tallos_x_ramo = $model_inventario->tallos_x_ramo;
                    $ingreso->ramos = $data->ramos_produccion;
                    $ingreso->bodega = 'P';
                    $ingreso->longitud = $model_inventario->longitud;
                    $ingreso->id_empresa = $model_inventario->id_empresa;
                    $ingreso->save();
                }

                $detApi->recibido += $data->ramos_ventas + $data->ramos_produccion;
                $detApi->estado = 'R';
                $detApi->save();
            }

            $success = true;
            $msg = 'Se ha <strong>GRABADO</strong> la informacion correctamente';

            DB::commit();
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

    public function update_inventario(Request $request)
    {
        try {
            DB::beginTransaction();
            $model = InventarioRecepcion::find($request->id);
            $model->ramos = $request->ramos_ingresados;
            $model->disponibles = $request->tallos_disponibles;
            $model->save();

            $success = true;
            $msg = 'Se ha <strong>MODIFICADO</strong> el inventario correctamente';

            DB::commit();
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

    public function delete_inventario(Request $request)
    {
        try {
            DB::beginTransaction();
            $model = InventarioRecepcion::find($request->id);
            $model->delete();

            $success = true;
            $msg = 'Se ha <strong>ELIMINADO</strong> el inventario correctamente';

            DB::commit();
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

    public function botar_inventario(Request $request)
    {
        try {
            DB::beginTransaction();
            $model = InventarioRecepcion::find($request->id);
            if ($model->disponibles < $request->botar) {
                DB::rollBack();
                $success = false;
                $msg = 'No se pueden botar mas tallos de los disponibles';

                return [
                    'success' => $success,
                    'mensaje' => $msg,
                ];
            } else {
                $model->disponibles -= $request->botar;
                $model->save();

                $salidas = new SalidasRecepcion();
                $salidas->id_inventario_recepcion = $model->id_inventario_recepcion;
                $salidas->id_variedad = $model->id_variedad;
                $salidas->cantidad = 0;
                $salidas->basura = $request->botar;
                $salidas->fecha = $request->fecha;
                $salidas->save();

                $success = true;
                $msg = 'Se ha <strong>ELIMINADO</strong> el inventario correctamente';
            }

            DB::commit();
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

    public function mover_inventario(Request $request)
    {
        $model = InventarioRecepcion::find($request->id);
        return view('adminlte.gestion.postco.ingreso_inventario.forms.mover_inventario', [
            'model' => $model
        ]);
    }

    public function store_movimiento(Request $request)
    {
        try {
            DB::beginTransaction();
            $invOriginal = InventarioRecepcion::find($request->id_inventario);
            $invOriginal->longitud = $request->original_longitud;
            $invOriginal->tallos_x_ramo = $request->original_tallos_x_ramo;
            $invOriginal->ramos = $request->original_ramos;
            $invOriginal->disponibles = $request->original_disponibles;
            $invOriginal->bodega = $request->original_bodega;
            $invOriginal->save();

            $model_inventario = InventarioRecepcion::where('id_variedad', $invOriginal->id_variedad)
                ->where('fecha', $invOriginal->fecha)
                ->where('tallos_x_ramo', $request->mover_tallos_x_ramo)
                ->where('longitud', $request->mover_longitud)
                ->where('id_empresa', $invOriginal->id_empresa)
                ->where('bodega', $request->mover_bodega)
                ->first();
            if ($model_inventario == '') {
                $model_inventario = new InventarioRecepcion();
                $model_inventario->id_variedad = $invOriginal->id_variedad;
                $model_inventario->fecha = $invOriginal->fecha;
                $model_inventario->tallos_x_ramo = $request->mover_tallos_x_ramo;
                $model_inventario->ramos = $request->mover_ramos;
                $model_inventario->bodega = $request->mover_bodega;
                $model_inventario->longitud = $request->mover_longitud;
                $model_inventario->disponibles = $request->mover_disponibles;
                $model_inventario->id_empresa = $invOriginal->id_empresa;
                $model_inventario->save();
            } else {
                $model_inventario->ramos += $request->mover_ramos;
                $model_inventario->disponibles += $request->mover_disponibles;
                $model_inventario->save();
            }

            $success = true;
            $msg = 'Se ha <strong>GRABADO</strong> la informacion correctamente';

            DB::commit();
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

    public function admin_bodegas(Request $request)
    {
        $segmentos = Segmento::orderBy('nombre')->get();
        return view('adminlte.gestion.postco.ingreso_inventario.forms.admin_bodegas', [
            'segmentos' => $segmentos
        ]);
    }

    public function update_bodega(Request $request)
    {
        try {
            DB::beginTransaction();
            $segmento = Segmento::find($request->id_segmento);
            $segmento->bodega = $request->bodega;
            $segmento->save();

            $success = true;
            $msg = 'Se ha <strong>GRABADO</strong> la informacion correctamente';

            DB::commit();
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
