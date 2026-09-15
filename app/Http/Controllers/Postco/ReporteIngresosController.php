<?php

namespace yura\Http\Controllers\Postco;

use DB;
use Illuminate\Http\Request;
use yura\Http\Controllers\Controller;
use yura\Modelos\CodigoAutorizacion;
use yura\Modelos\IngresoRecepcion;
use yura\Modelos\InventarioRecepcion;
use yura\Modelos\Planta;
use yura\Modelos\Submenu;

class ReporteIngresosController extends Controller
{
    public function inicio(Request $request)
    {
        $finca = getFincaActiva();
        $plantas = Planta::where('estado', 1)
            ->where('id_empresa', $finca)
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.postco.reporte_ingresos.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'plantas' => $plantas,
        ]);
    }

    public function listar_reporte(Request $request)
    {
        $finca = getFincaActiva();
        $listado_documentos = DB::table('ingreso_recepcion as i')
            ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->join('api_store_cajas as api', 'api.id_api_store_cajas', '=', 'i.id_api_store_cajas')
            ->select(
                'i.id_api_store_cajas',
                'i.id_variedad',
                'v.nombre as var_nombre',
                'p.nombre as pta_nombre',
                'api.documento',
                'api.fecha',
                'i.tallos_x_ramo',
                'i.ramos',
                'i.tallos',
                'i.longitud',
                'i.bodega',
                'i.id_ingreso_recepcion',
            )->distinct()
            ->where('i.id_empresa', $finca)
            ->where('api.fecha', '>=', $request->desde)
            ->where('api.fecha', '<=', $request->hasta);
        if ($request->documento != '')
            $listado_documentos = $listado_documentos->where('api.documento', $request->documento);
        if ($request->bodega != 'T')
            $listado_documentos = $listado_documentos->where('i.bodega', $request->bodega);
        if ($request->planta != '')
            $listado_documentos = $listado_documentos->where('v.id_planta', $request->planta);
        if ($request->variedad != '')
            $listado_documentos = $listado_documentos->where('i.id_variedad', $request->variedad);
        $listado_documentos = $listado_documentos->orderBy('api.fecha')
            ->orderBy('api.documento')
            ->get()
            ->groupBy('documento')
            ->map(function ($items, $documento) {
                return [
                    'documento' => $documento,
                    'fecha' => $items->first()->fecha,
                    'id_api_store_cajas' => $items->first()->id_api_store_cajas,
                    'detalles' => $items->values(),
                ];
            })
            ->values();

        $listado_compras = DB::table('ingreso_recepcion as i')
            ->leftJoin('configuracion_empresa as prov', 'prov.id_configuracion_empresa', '=', 'i.id_proveedor')
            ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->select(
                'i.id_variedad',
                'v.nombre as var_nombre',
                'p.nombre as pta_nombre',
                'i.tallos_x_ramo',
                'i.ramos',
                'i.tallos',
                'i.longitud',
                'i.factura',
                'i.packing',
                'i.bodega',
                'i.fecha',
                'i.id_ingreso_recepcion',
                'i.fecha_registro',
                'prov.nombre as proveedor_nombre',
            )->distinct()
            ->whereNotNull('i.packing')
            ->where('i.id_empresa', $finca)
            ->where('i.fecha', '>=', $request->desde)
            ->where('i.fecha', '<=', $request->hasta);
        if ($request->documento != '')
            $listado_compras = $listado_compras->where('i.factura', $request->documento);
        if ($request->bodega != 'T')
            $listado_compras = $listado_compras->where('i.bodega', $request->bodega);
        if ($request->planta != '')
            $listado_compras = $listado_compras->where('v.id_planta', $request->planta);
        if ($request->variedad != '')
            $listado_compras = $listado_compras->where('i.id_variedad', $request->variedad);
        $listado_compras = $listado_compras->orderBy('i.fecha')
            ->orderBy('i.packing')
            ->get()
            ->groupBy('packing')
            ->map(function ($items, $packing) {
                return [
                    'packing' => $packing,
                    'fecha' => $items->first()->fecha,
                    'factura' => $items->first()->factura,
                    'proveedor_nombre' => $items->first()->proveedor_nombre,
                    'detalles' => $items->values(),
                ];
            })
            ->values();

        $listado_movimientos = DB::table('ingreso_recepcion as i')
            ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->select(
                'i.id_variedad',
                'v.nombre as var_nombre',
                'p.nombre as pta_nombre',
                'i.tallos_x_ramo',
                'i.ramos',
                'i.tallos',
                'i.longitud',
                'i.bodega',
                'i.cambio_bodega',
                'i.fecha',
                'i.id_ingreso_recepcion',
            )->distinct()
            ->whereNotNull('i.cambio_bodega')
            ->where('i.id_empresa', $finca)
            ->where('i.fecha', '>=', $request->desde)
            ->where('i.fecha', '<=', $request->hasta);
        if ($request->bodega != 'T')
            $listado_movimientos = $listado_movimientos->where('i.bodega', $request->bodega);
        if ($request->planta != '')
            $listado_movimientos = $listado_movimientos->where('v.id_planta', $request->planta);
        if ($request->variedad != '')
            $listado_movimientos = $listado_movimientos->where('i.id_variedad', $request->variedad);
        $listado_movimientos = $listado_movimientos->orderBy('i.fecha')
            ->orderBy('p.nombre')
            ->orderBy('v.nombre')
            ->get();

        $listado_corregir = DB::table('ingreso_recepcion as i')
            ->join('correccion_recepcion as c', 'c.id_correccion_recepcion', '=', 'i.id_correccion_recepcion')
            ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->select(
                'i.id_variedad',
                'v.nombre as var_nombre',
                'p.nombre as pta_nombre',
                'i.tallos_x_ramo',
                'i.ramos',
                'i.tallos',
                'i.longitud',
                'i.bodega',
                'i.fecha',
                'i.id_ingreso_recepcion',
                'c.orden',
                'c.anterior',
                'c.actual',
            )->distinct()
            ->whereNotNull('i.id_correccion_recepcion')
            ->where('i.id_empresa', $finca)
            ->where('i.fecha', '>=', $request->desde)
            ->where('i.fecha', '<=', $request->hasta);
        if ($request->bodega != 'T')
            $listado_corregir = $listado_corregir->where('i.bodega', $request->bodega);
        if ($request->planta != '')
            $listado_corregir = $listado_corregir->where('v.id_planta', $request->planta);
        if ($request->variedad != '')
            $listado_corregir = $listado_corregir->where('i.id_variedad', $request->variedad);
        $listado_corregir = $listado_corregir->orderBy('i.fecha')
            ->orderBy('c.orden')
            ->get()
            ->groupBy('orden')
            ->map(function ($items, $orden) {
                return [
                    'orden' => $orden,
                    'fecha' => $items->first()->fecha,
                    'detalles' => $items->values(),
                ];
            })
            ->values();

        return view('adminlte.gestion.postco.reporte_ingresos.partials.listado', [
            'listado_documentos' => $listado_documentos,
            'listado_compras' => $listado_compras,
            'listado_movimientos' => $listado_movimientos,
            'listado_corregir' => $listado_corregir,
        ]);
    }

    public function habilitar_modificar(Request $request)
    {
        try {
            DB::beginTransaction();
            $codigo = CodigoAutorizacion::where('nombre', 'habilitar_modificar')
                ->first();
            if ($codigo != '' && $codigo->valor == $request->codigo) {
                $success = true;
                $msg = 'Se ha <strong>HABILITADO</strong> la opcion para modificar';

                DB::commit();
            } else {
                DB::rollBack();
                $success = false;
                $msg = '<div class="alert alert-danger text-center">' .
                    '<h3>El codigo de autorizacion es incorrecto</h3>' .
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

    public function update_compra(Request $request)
    {
        try {
            DB::beginTransaction();
            $model = IngresoRecepcion::find($request->id);
            $diferencia = $request->tallos - $model->tallos;
            $model->tallos = $request->tallos;
            $model->save();

            if ($diferencia != 0) {
                if ($diferencia < 0) {  // salida
                    $inventarios = InventarioRecepcion::where('id_variedad', $model->id_variedad)
                        ->where('disponibles', '>', 0)
                        ->where('bodega', $model->bodega)
                        ->where('id_empresa', $model->id_empresa)
                        ->orderBy('fecha')
                        ->get();

                    $sacar = abs($diferencia);
                    foreach ($inventarios as $model_inventario) {
                        if ($sacar >= 0) {
                            $usados = 0;
                            $disponible = $model_inventario->disponibles;
                            if ($sacar >= $disponible) {
                                $sacar = $sacar - $disponible;
                                $usados = $disponible;
                                $disponible = 0;
                            } else {
                                $disponible = $disponible - $sacar;
                                $usados = $sacar;
                                $sacar = 0;
                            }

                            $model_inventario->disponibles = $disponible;
                            $model_inventario->save();
                        }
                    }
                    if ($sacar > 0) {
                        DB::rollBack();
                        $success = false;
                        $msg = '<div class="alert alert-danger text-center">' .
                            '<h4><em>No se ha podido modificar la compra:</em><br>' .
                            '<b><i class="fa fa-fw fa-exclamation-triangle"></i> Actualmente no hay flor ' .
                            'disponible en el inventario para reducir ' . abs($diferencia) . ' tallos de diferencia.<br></b>' .
                            '<i class="fa fa-fw fa-check"></i> Debe realizar el cambio a traves de un ajuste de inventario</h4>' .
                            '</div>';

                        return [
                            'success' => $success,
                            'mensaje' => $msg,
                        ];
                    }
                } else {    // ingreso
                    $model_inventario = InventarioRecepcion::where('id_variedad', $model->id_variedad)
                        ->where('fecha', $model->fecha)
                        ->where('tallos_x_ramo', $model->tallos_x_ramo)
                        ->where('bodega', $model->bodega)
                        ->where('longitud', $model->longitud)
                        ->where('id_empresa', $model->id_empresa)
                        ->first();
                    if ($model_inventario == '') {
                        $model_inventario = new InventarioRecepcion();
                        $model_inventario->id_variedad = $model->id_variedad;
                        $model_inventario->fecha = $model->fecha;
                        $model_inventario->tallos_x_ramo = $model->tallos_x_ramo;
                        $model_inventario->ramos = abs($diferencia);
                        $model_inventario->bodega = $model->bodega;
                        $model_inventario->longitud = $model->longitud;
                        $model_inventario->disponibles = abs($diferencia);
                        $model_inventario->id_empresa = $model->id_empresa;
                        $model_inventario->save();
                    } else {
                        $model_inventario->disponibles += abs($diferencia);
                        $model_inventario->save();
                    }
                }
            }

            $success = true;
            $msg = 'Se ha <strong>MODIFICADO</strong> la compra correctamente';

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
