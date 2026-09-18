<?php

namespace yura\Http\Controllers\Postco;

use DB;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use yura\Http\Controllers\Controller;
use yura\Modelos\CodigoAutorizacion;
use yura\Modelos\IngresoRecepcion;
use yura\Modelos\InventarioRecepcion;
use yura\Modelos\Planta;
use yura\Modelos\RegistroMovimientos;
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
            $antes = $model->tallos;
            $ahora = $request->tallos;
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

                            if ($usados > 0) {
                                $model_inventario->disponibles -= $usados;
                                $model_inventario->save();

                                $registro = new RegistroMovimientos();
                                $registro->id_variedad = $model_inventario->id_variedad;
                                $registro->id_empresa = $model_inventario->id_empresa;
                                $registro->bodega = $model_inventario->bodega;
                                $registro->fecha = hoy();
                                $registro->tipo = 'S';
                                $registro->concepto = 'MODIFICACION-COMPRA';
                                $registro->numero = $model->packing;
                                $registro->cantidad = $usados;
                                $registro->id_usuario = session('id_usuario');
                                $registro->descripcion = 'Salida de flor al MODIFICAR una COMPRA en el menu Reporte de Ingresos. Se cambio de: ' . $antes . ', a: ' . $ahora;
                                // campos de relacion
                                $registro->id_inventario_recepcion = $model_inventario->id_inventario_recepcion;
                                $registro->id_proveedor = $model->id_proveedor;
                                $registro->save();
                            }
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
                        $id_inventario = DB::table('inventario_recepcion')
                            ->select(DB::raw('max(id_inventario_recepcion) as id'))
                            ->get()[0]->id;
                    } else {
                        $model_inventario->disponibles += abs($diferencia);
                        $model_inventario->save();
                        $id_inventario = $model_inventario->id_inventario_recepcion;
                    }

                    $registro = new RegistroMovimientos();
                    $registro->id_variedad = $model_inventario->id_variedad;
                    $registro->id_empresa = $model_inventario->id_empresa;
                    $registro->bodega = $model_inventario->bodega;
                    $registro->fecha = hoy();
                    $registro->tipo = 'I';
                    $registro->concepto = 'MODIFICACION-COMPRA';
                    $registro->numero = $model->packing;
                    $registro->cantidad = abs($diferencia);
                    $registro->id_usuario = session('id_usuario');
                    $registro->descripcion = 'Ingreso de flor al MODIFICAR una COMPRA en el menu Reporte de Ingresos. Se cambio de: ' . $antes . ', a: ' . $ahora;
                    // campos de relacion
                    $registro->id_inventario_recepcion = $id_inventario;
                    $registro->id_proveedor = $model->id_proveedor;
                    $registro->save();
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

    public function exportar_reporte(Request $request)
    {
        $spread = new Spreadsheet();
        $this->excel_reporte($spread, $request);
        $fileName = "Ingresos.xlsx";
        $writer = new Xlsx($spread);

        //--------------------------- GUARDAR EL EXCEL -----------------------

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        $writer->save('php://output');

        //$writer->save('/var/www/html/Dasalflor/storage/storage/excel/excel_prueba.xlsx');
    }

    public function excel_reporte($spread, $request)
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

        $columnas = getColumnasExcel();

        // COMPRAS
        $sheet = $spread->getActiveSheet();
        $sheet->setTitle('Compras');

        $row = 1;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Fecha');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Packing');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Factura');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Proveedor');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Bodega');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Planta');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Variedad');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Longitud');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TxR');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Ramos');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Tallos');
        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        foreach ($listado_compras as $compra) {
            foreach ($compra['detalles'] as $pos_i => $item) {
                $row++;
                if ($pos_i == 0) {
                    $col = 0;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $compra['fecha']);
                    $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($compra['detalles']) - 1));
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $compra['packing']);
                    $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($compra['detalles']) - 1));
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $compra['factura']);
                    $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($compra['detalles']) - 1));
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $compra['proveedor_nombre']);
                    $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($compra['detalles']) - 1));
                }
                $col = 4;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->bodega == 'V' ? 'Ventas' : 'Produccion');
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->pta_nombre);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->var_nombre);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->longitud);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->tallos_x_ramo);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->ramos);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->tallos);
            }
        }

        setTextCenterToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);
        setBorderToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);

        for ($i = 0; $i <= $col; $i++)
            $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);

        // INTERNOS
        $sheet = $spread->createSheet()->setTitle('Internos');

        $row = 1;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Fecha');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'N°');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Documento');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Bodega');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Planta');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Variedad');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Longitud');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TxR');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Ramos');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Tallos');
        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        foreach ($listado_documentos as $documento) {
            foreach ($documento['detalles'] as $pos_i => $item) {
                $row++;
                if ($pos_i == 0) {
                    $col = 0;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $documento['fecha']);
                    $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($documento['detalles']) - 1));
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $documento['id_api_store_cajas']);
                    $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($documento['detalles']) - 1));
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $documento['documento']);
                    $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($documento['detalles']) - 1));
                }
                $col = 3;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->bodega == 'V' ? 'Ventas' : 'Produccion');
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->pta_nombre);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->var_nombre);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->longitud);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->tallos_x_ramo);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->ramos);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->tallos);
            }
        }

        setTextCenterToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);
        setBorderToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);

        for ($i = 0; $i <= $col; $i++)
            $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);

        // MOVIMIENTOS
        $sheet = $spread->createSheet()->setTitle('Movimientos');

        $row = 1;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Fecha');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'De');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'A');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Planta');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Variedad');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Longitud');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TxR');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Ramos');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Tallos');
        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        foreach ($listado_movimientos as $item) {
            $row++;
            $col = 0;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->fecha);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->cambio_bodega == 'V' ? 'Ventas' : 'Produccion');
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->bodega == 'V' ? 'Ventas' : 'Produccion');
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->pta_nombre);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->var_nombre);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->longitud);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->tallos_x_ramo);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->ramos);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->tallos);
        }

        setTextCenterToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);
        setBorderToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);

        for ($i = 0; $i <= $col; $i++)
            $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);

        // CORRECCION
        $sheet = $spread->createSheet()->setTitle('Correccion');

        $row = 1;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Fecha');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'N°');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Bodega');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Planta');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Variedad');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Longitud');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Tallos Anteriores');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Tallos Corregidos');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Tallos Ingresados');
        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        foreach ($listado_corregir as $correccion) {
            foreach ($correccion['detalles'] as $pos_i => $item) {
                $row++;
                if ($pos_i == 0) {
                    $col = 0;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $correccion['fecha']);
                    $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($correccion['detalles']) - 1));
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $correccion['orden']);
                    $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($correccion['detalles']) - 1));
                }
                $col = 2;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->bodega == 'V' ? 'Ventas' : 'Produccion');
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->pta_nombre);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->var_nombre);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->longitud);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->anterior);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->actual);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->tallos);
            }
        }

        setTextCenterToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);
        setBorderToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);

        for ($i = 0; $i <= $col; $i++)
            $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);
    }
}
