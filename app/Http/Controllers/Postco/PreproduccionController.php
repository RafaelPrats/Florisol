<?php

namespace yura\Http\Controllers\Postco;

use Illuminate\Http\Request;
use yura\Http\Controllers\Controller;
use yura\Modelos\Variedad;
use yura\Modelos\Submenu;
use yura\Modelos\Postco;
use yura\Modelos\OtPostco;
use yura\Modelos\DetalleOtPostco;
use yura\Modelos\DistribucionPostco;
use yura\Modelos\Planta;
use yura\Modelos\DetalleReceta;
use yura\Modelos\Despachador;
use yura\Modelos\ArmadoPostco;
use yura\Modelos\DetalleArmadoPostco;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PDF;
use Picqer\Barcode\BarcodeGeneratorHTML;
use yura\Modelos\CodigoAutorizacion;
use yura\Modelos\DetalleOaPostco;
use yura\Modelos\OaPostco;

class PreproduccionController extends Controller
{
    public function inicio(Request $request)
    {
        $variedades = Variedad::where('estado', 1)
            ->where('receta', 1)
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.postco.preproduccion.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'variedades' => $variedades,
        ]);
    }

    public function listar_reporte(Request $request)
    {
        $fechas = DB::table('postco')
            ->select('fecha')->distinct()
            ->where('fecha', '>=', $request->desde)
            ->where('fecha', '<=', $request->hasta);
        if ($request->variedad != 'T')
            $fechas = $fechas->where('id_variedad', $request->variedad);
        $fechas = $fechas->orderBy('fecha')->get()->pluck('fecha')->toArray();

        $recetas = DB::table('postco as p')
            ->join('variedad as v', 'v.id_variedad', '=', 'p.id_variedad')
            ->select('p.id_variedad', 'v.nombre', 'p.longitud')->distinct()
            ->where('p.fecha', '>=', $request->desde)
            ->where('p.fecha', '<=', $request->hasta);
        if ($request->variedad != 'T')
            $recetas = $recetas->where('p.id_variedad', $request->variedad);
        $recetas = $recetas->orderBy('v.nombre')
            ->get();

        $listado = [];
        foreach ($recetas as $receta) {
            $valores = DB::table('postco')
                ->select(
                    DB::raw('sum(ramos) as ramos'),
                    DB::raw('sum(armados) as armados'),
                    DB::raw('sum(despachados) as despachados'),
                    'fecha'
                )
                ->where('id_variedad', $receta->id_variedad)
                ->where('longitud', $receta->longitud)
                ->whereIn('fecha', $fechas)
                ->orderBy('fecha')
                ->groupBy('fecha')
                ->get();
            $listado[] = [
                'receta' => $receta,
                'valores' => $valores,
            ];
        }
        return view('adminlte.gestion.postco.preproduccion.partials.listado', [
            'listado' => $listado,
            'fechas' => $fechas,
        ]);
    }

    public function modal_receta(Request $request)
    {
        $listado = Postco::where('id_variedad', $request->variedad)
            ->where('longitud', $request->longitud)
            ->whereIn('fecha', json_decode($request->fechas))
            ->orderBy('fecha')
            ->get();
        return view('adminlte.gestion.postco.preproduccion.forms.modal_receta', [
            'listado' => $listado,
            'receta' => Variedad::find($request->variedad),
            'longitud' => $request->longitud,
        ]);
    }

    public function armar_ramos(Request $request)
    {
        DB::beginTransaction();
        try {
            $model = Postco::find($request->id);
            $model->armados += $request->cantidad;
            $model->save();
            $texto = $model->id_variedad . ' ' . $model->longitud . 'cm' . '; fecha = ' . $model->fecha;
            bitacora('POSTCO', $request->id, 'U', 'ARMAR MANUALMENTE: (' . $request->cantidad . ') ramos de ' . $texto);

            $model_armado = new ArmadoPostco();
            $model_armado->id_postco = $model->id_postco;
            $model_armado->ramos = $request->cantidad;
            $model_armado->id_cliente = $model->clientes[0]->id_cliente;
            $model_armado->save();
            $model_armado->id_armado_postco = DB::table('armado_postco')
                ->select(DB::raw('max(id_armado_postco) as id'))
                ->get()[0]->id;

            foreach ($model->distribuciones as $key => $dist) {
                $det_armado = new DetalleArmadoPostco();
                $det_armado->id_armado_postco = $model_armado->id_armado_postco;
                $det_armado->id_item = $dist->id_item;
                $det_armado->unidades = $dist->unidades;
                $det_armado->save();
            }

            DB::commit();
            $success = true;
            $msg = 'Se ha <strong>GRABADO</strong> la informacion correctamente';
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

    public function store_ot(Request $request)
    {
        DB::beginTransaction();
        try {
            $postco = Postco::find($request->id);
            $ot = new OtPostco();
            $ot->id_postco  = $request->id;
            $ot->ramos = $request->cantidad;
            $ot->fecha = $request->fecha;
            $ot->id_cliente = $request->cliente;
            $ot->longitud = $request->longitud;
            $ot->estado = 'P';
            $ot->save();
            $ot->id_ot_postco = DB::table('ot_postco')
                ->select(DB::raw('max(id_ot_postco) as id'))
                ->get()[0]->id;

            $texto = $postco->id_variedad . ' ' . $postco->longitud . 'cm' . '; fecha = ' . $postco->fecha . ' id_postco = ' . $postco->id_postco;
            bitacora('OT_POSTCO', $ot->id_ot_postco, 'I', 'PROCESAR OT: (' . $request->cantidad . ') ramos de ' . $texto);

            foreach ($postco->distribuciones as $dist) {
                $det_ot = new DetalleOtPostco();
                $det_ot->id_ot_postco = $ot->id_ot_postco;
                $det_ot->id_item = $dist->id_item;
                $det_ot->unidades = $dist->unidades;
                $det_ot->save();
            }

            DB::commit();
            $success = true;
            $msg = 'Se ha <strong>GRABADO</strong> la OT correctamente';
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

    public function admin_receta(Request $request)
    {
        $postco = Postco::find($request->id);
        $numeros_receta = DB::table('detalle_receta')
            ->select('numero_receta')->distinct()
            ->where('id_variedad', $postco->id_variedad)
            ->get()->pluck('numero_receta')->toArray();
        return view('adminlte.gestion.postco.preproduccion.forms.admin_receta', [
            'postco' => $postco,
            'numeros_receta' => $numeros_receta,
            'plantas' => Planta::where('estado', '=', 1)->orderBy('nombre')->get(),
        ]);
    }

    public function buscar_variedades(Request $request)
    {
        $listado = Variedad::where('id_planta', $request->planta)
            //->where('assorted', 0)
            ->where('receta', 0)
            ->orderBy('nombre')
            ->get();

        return view('adminlte.gestion.postco.preproduccion.forms.buscar_variedades', [
            'listado' => $listado,
            'ramos_pedido' => $request->ramos_pedido,
            'longitud_pedido' => $request->longitud_pedido,
            'postco_fecha' => $request->postco_fecha,
        ]);
    }

    public function store_distribucion_receta(Request $request)
    {
        DB::beginTransaction();
        try {
            $postco = Postco::find($request->id_postco);
            $variedades = [];

            $delete = DistribucionPostco::where('id_postco', $request->id_postco)
                ->get();
            foreach ($delete as $del) {
                $variedades[] = $del->id_variedad;  // guardar en este listado los ids de variedades para actualizar la tabla resumen_agrogana
                $del->delete();
            }

            foreach (json_decode($request->data) as $d) {
                $model = new DistribucionPostco();
                $model->id_postco = $request->id_postco;
                $model->id_item = $d->id_item;
                $model->longitud = $d->longitud;
                $model->unidades = $d->unidades;
                $model->save();

                if (!in_array($d->id_item, $variedades))
                    $variedades[] = $d->id_item;  // guardar en este listado los ids de variedades para actualizar la tabla resumen_agrogana
            }

            DB::commit();
            $success = true;
            $msg = 'Se han <strong>DISTRIBUIDO</strong> los tallos correctamente';
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

    public function cargar_receta(Request $request)
    {
        $postco = Postco::find($request->id_postco);

        $detalles_receta = DetalleReceta::where('id_variedad', $postco->id_variedad)
            ->where('numero_receta', $request->numero_receta)
            ->get();
        return view('adminlte.gestion.postco.preproduccion.forms.cargar_receta', [
            'postco' => $postco,
            'detalles_receta' => $detalles_receta,
        ]);
    }

    public function listar_ordenes_trabajo(Request $request)
    {
        $listado = OtPostco::where('id_postco', $request->postco)
            ->orderBy('id_ot_postco')
            ->orderBy('longitud')
            ->get();
        $despachadores = Despachador::where('estado', 1)
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.postco.preproduccion.forms.listar_ordenes_trabajo', [
            'listado' => $listado,
            'despachadores' => $despachadores,
            'postco' => Postco::find($request->postco)
        ]);
    }

    public function update_despachador(Request $request)
    {
        try {
            DB::beginTransaction();
            $model = OtPostco::find($request->id_ot);
            $model->id_despachador = $request->despachador;
            $model->save();

            $success = true;
            $msg = 'Se ha <strong>ASIGNADO</strong> el responsable correctamente';
            bitacora('OT_POSTCO', $model->id_ot_postco, 'U', 'MODIFICAR EL DESPACHADOR de la OT desde PREPRODUCCION (' . $model->ramos . ' ramos)');
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

    public function eliminar_orden_trabajo(Request $request)
    {
        DB::beginTransaction();
        try {
            $variedades = [];
            $model = OtPostco::find($request->id);
            foreach ($model->detalles as $det) {
                $variedades[] = $det->id_variedad;
            }
            $postco = $model->postco;
            $texto = $postco->id_variedad . ' ' . $postco->longitud . 'cm' . '; fecha = ' . $postco->fecha . ' id_postco = ' . $postco->id_postco;
            bitacora('OT_POSTCO', $model->id_ot_postco, 'D', 'ELIMINAR OT desde PREPRODUCCION: (' . $model->ramos . ') ramos de ' . $texto);

            $model->delete();

            DB::commit();
            $success = true;
            $msg = 'Se ha <strong>ELIMINADO</strong> la orden de trabajo correctamente';
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

    public function exportar_orden_trabajo(Request $request)
    {
        $spread = new Spreadsheet();
        $this->excel_orden_trabajo($spread, $request);
        $fileName = "OT.xlsx";
        $writer = new Xlsx($spread);

        //--------------------------- GUARDAR EL EXCEL -----------------------

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        $writer->save('php://output');

        //$writer->save('/var/www/html/Dasalflor/storage/storage/excel/excel_prueba.xlsx');
    }

    public function excel_orden_trabajo($spread, $request)
    {
        $orden_trabajo = OtPostco::find($request->id);
        $despachador = $orden_trabajo->despachador;
        $columnas = getColumnasExcel();
        $sheet = $spread->getActiveSheet();
        $sheet->setTitle('Preproduccion');

        $row = 1;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'OT');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'FECHA');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'CLIENTE');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'RECETA');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'LONGITUD');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'RAMOS');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'VARIEDAD');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TALLOS');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'UNIDADES');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'RESPONSABLE');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'OBSERVACION');

        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        $postco = $orden_trabajo->postco;
        $tallos_x_ramo = 0;
        $total_tallos = 0;
        foreach ($orden_trabajo->detalles as $det) {
            $total_tallos += $det->unidades * $orden_trabajo->ramos;
            $tallos_x_ramo += $det->unidades;
        }

        foreach ($orden_trabajo->detalles as $pos_d => $det) {
            $row++;
            if ($pos_d == 0) {
                $col = 0;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, '#' . $orden_trabajo->id_ot_postco);
                $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($orden_trabajo->detalles) - 1));
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, convertDateToText($postco->fecha));
                $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($orden_trabajo->detalles) - 1));
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $orden_trabajo->cliente->detalle()->nombre);
                $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($orden_trabajo->detalles) - 1));
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $postco->variedad->nombre);
                $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($orden_trabajo->detalles) - 1));
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $orden_trabajo->longitud);
                $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($orden_trabajo->detalles) - 1));
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $orden_trabajo->ramos);
                $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($orden_trabajo->detalles) - 1));
            }
            $col = 6;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $det->item->nombre);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $det->unidades * $orden_trabajo->ramos);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $det->unidades);
            if ($pos_d == 0) {
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $despachador != '' ? $despachador->nombre : '');
                $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($orden_trabajo->detalles) - 1));
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $orden_trabajo->observacion);
                $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($orden_trabajo->detalles) - 1));
            }
        }
        $row++;
        $col = 7;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_tallos);
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $tallos_x_ramo);
        $col = 10;
        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        setTextCenterToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);
        setBorderToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);

        for ($i = 0; $i <= $col; $i++)
            $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);
    }

    public function copiar_receta(Request $request)
    {
        DB::beginTransaction();
        try {
            $postco = Postco::find($request->id);
            $query = Postco::where('fecha', '>=', $request->desde)
                ->where('fecha', '<=', $request->hasta)
                ->where('id_variedad', $postco->id_variedad)
                ->where('longitud', $postco->longitud)
                ->get();
            $distribucion = $postco->distribuciones;
            foreach ($query as $p_query) {
                DB::select('delete from distribucion_postco where id_postco = ' . $p_query->id_postco);
                foreach ($distribucion as $dist) {
                    $model = new DistribucionPostco();
                    $model->id_postco = $p_query->id_postco;
                    $model->id_item = $dist->id_item;
                    $model->unidades = $dist->unidades;
                    $model->longitud = $dist->longitud;
                    $model->save();
                }
            }

            DB::commit();
            $success = true;
            $msg = 'Se ha <strong>ELIMINADO</strong> la orden de trabajo correctamente';
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
        $this->excel_total($spread, $request);
        $fileName = "Planificacion.xlsx";
        $writer = new Xlsx($spread);

        //--------------------------- GUARDAR EL EXCEL -----------------------

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        $writer->save('php://output');

        //$writer->save('/var/www/html/Dasalflor/storage/storage/excel/excel_prueba.xlsx');
    }

    public function excel_total($spread, $request)
    {
        /* HOJA 1 */
        $fechas = DB::table('postco as p')
            ->select('p.fecha')->distinct()
            ->where('p.fecha', '>=', $request->desde)
            ->where('p.fecha', '<=', $request->hasta);
        if ($request->variedad != 'T')
            $fechas = $fechas->where('d.id_variedad', $request->variedad);
        $fechas = $fechas->orderBy('p.fecha')
            ->get()
            ->pluck('fecha')
            ->toArray();

        $listado = [];
        if (count($fechas) > 0) {
            $fecha_ini = $fechas[0];
            $fecha_fin = $fechas[count($fechas) - 1];
            $combinaciones = DB::table('postco as p')
                ->join('variedad as v', 'v.id_variedad', '=', 'p.id_variedad')
                ->select('p.id_variedad', 'p.longitud', 'v.nombre', 'v.siglas')->distinct()
                ->where('p.fecha', '>=', $fecha_ini)
                ->where('p.fecha', '<=', $fecha_fin);
            if ($request->variedad != 'T')
                $combinaciones = $combinaciones->where('p.id_variedad', $request->variedad);
            $combinaciones = $combinaciones->get();

            foreach ($combinaciones as $item) {
                $valores = [];
                foreach ($fechas as $fecha) {
                    $venta = DB::table('postco as p')
                        ->select(DB::raw('sum(p.ramos) as cantidad'))
                        ->where('p.fecha', $fecha)
                        ->where('p.id_variedad', $item->id_variedad)
                        ->where('p.longitud', $item->longitud)
                        ->get()[0]->cantidad;

                    $valores[] = $venta;
                }
                $listado[] = [
                    'item' => $item,
                    'valores' => $valores,
                ];
            }
        }

        $columnas = getColumnasExcel();
        $sheet = $spread->getActiveSheet();
        $sheet->setTitle('TOTAL');

        $row = 1;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Siglas');
        setBgToCeldaExcel($sheet, $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Nombre');
        setBgToCeldaExcel($sheet, $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Longitud');
        setBgToCeldaExcel($sheet, $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
        $totales_fecha = [];
        foreach ($fechas as $pos_f => $f) {
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, getDias(TP_ABREVIADO, FR_ARREGLO)[transformDiaPhp(date('w', strtotime($f)))] . ' ' . explode(' del ', convertDateToText($f))[0]);
            setBgToCeldaExcel($sheet, $columnas[$col] . $row, '5a7177');
            setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
            $totales_fecha[] = 0;
        }
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Total');
        setBgToCeldaExcel($sheet, $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');

        foreach ($listado as $pos => $item) {
            $row++;
            $col = 0;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item['item']->siglas);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item['item']->nombre);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item['item']->longitud . 'cm');

            $total_receta = 0;
            foreach ($item['valores'] as $pos_f => $val) {
                $totales_fecha[$pos_f] += $val;
                $total_receta += $val;
                $col++;
                if ($val > 0)
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $val);
            }
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_receta);
        }
        $row++;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Totales');
        $col += 2;
        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        $total = 0;
        foreach ($totales_fecha as $pos_f => $val) {
            $total += $val;
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $val);
            setBgToCeldaExcel($sheet, $columnas[$col] . $row, '5a7177');
            setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
        }
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total);
        setBgToCeldaExcel($sheet, $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');

        setTextCenterToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);
        setBorderToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);

        for ($i = 0; $i <= $col; $i++)
            $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);

        /* HOJA 2 */
        $listado = [];
        if (count($fechas) > 0) {
            $combinaciones = DB::table('postco as p')
                ->join('distribucion_postco as r', 'r.id_postco', '=', 'p.id_postco')
                ->join('variedad as v', 'v.id_variedad', '=', 'r.id_item')
                ->join('planta as pta', 'pta.id_planta', '=', 'v.id_planta')
                ->select(
                    'r.id_item as id_variedad',
                    'v.nombre as nombre_variedad',
                    'v.id_planta',
                    'pta.nombre as nombre_planta'
                )->distinct()
                ->where('p.fecha', '>=', $fecha_ini)
                ->where('p.fecha', '<=', $fecha_fin)
                ->orderBy('pta.nombre')
                ->orderBy('v.nombre')
                ->get();

            foreach ($combinaciones as $item) {
                $valores = [];
                foreach ($fechas as $fecha) {
                    $venta = DB::table('postco as p')
                        ->join('distribucion_postco as r', 'r.id_postco', '=', 'p.id_postco')
                        ->select(DB::raw('sum(p.ramos * r.unidades) as cantidad'))
                        ->where('p.fecha', $fecha)
                        ->where('r.id_item', $item->id_variedad)
                        ->get()[0]->cantidad;

                    $valores[] = $venta;
                }
                $listado[] = [
                    'item' => $item,
                    'valores' => $valores,
                ];
            }
        }

        $sheet = $spread->createSheet();
        $sheet->setTitle('Resumen');

        $row = 1;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Planta');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Variedad');
        $totales_fecha = [];
        foreach ($fechas as $pos_f => $f) {
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, getDias(TP_ABREVIADO, FR_ARREGLO)[transformDiaPhp(date('w', strtotime($f)))] . ' ' . explode(' del ', convertDateToText($f))[0]);
            $totales_fecha[] = 0;
        }
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Total');
        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        foreach ($listado as $pos => $item) {
            $row++;
            $col = 0;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item['item']->nombre_planta);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item['item']->nombre_variedad);

            $total_variedad = 0;
            foreach ($item['valores'] as $pos_f => $val) {
                $totales_fecha[$pos_f] += $val;
                $total_variedad += $val;
                $col++;
                if ($val > 0)
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $val);
            }
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_variedad);
        }
        $row++;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Totales');
        $col++;
        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        $total = 0;
        foreach ($totales_fecha as $pos_f => $val) {
            $total += $val;
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $val);
            setBgToCeldaExcel($sheet, $columnas[$col] . $row, '5a7177');
            setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
        }
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total);
        setBgToCeldaExcel($sheet, $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');

        setTextCenterToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);
        setBorderToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);

        for ($i = 0; $i <= $col; $i++)
            $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);
    }

    public function bloquear_postco(Request $request)
    {
        try {
            DB::beginTransaction();
            $codigo_model = CodigoAutorizacion::where('nombre', 'bloquear_receta')
                ->get()
                ->first();
            if ($codigo_model->valor == $request->codigo) {
                $model = Postco::find($request->id);
                $model->bloqueado = $model->bloqueado == 1 ? 0 : 1;
                $model->save();

                $success = true;
                $msg = 'Se ha <strong>BLOQUEADO</strong> la receta correctamente';
                bitacora('POSTCO', $model->id_postco, 'U', 'BLOQUEO/DESBLOQUEO de RECETA');
                DB::commit();
            } else {
                DB::rollBack();
                return [
                    'success' => false,
                    'mensaje' => '<div class="alert alert-warning text-center">El <b>CODIGO</b> es <b>INCORRECTO</b></div>',
                ];
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

    public function store_oa(Request $request)
    {
        DB::beginTransaction();
        try {
            $postco = Postco::find($request->id);
            $oa = new OaPostco();
            $oa->id_postco  = $request->id;
            $oa->ramos = $request->cantidad;
            $oa->fecha = $request->fecha;
            $oa->id_cliente = $request->cliente;
            $oa->longitud = $request->longitud;
            $oa->estado = 'P';
            $oa->save();
            $oa->id_oa_postco = DB::table('oa_postco')
                ->select(DB::raw('max(id_oa_postco) as id'))
                ->get()[0]->id;

            $texto = $postco->id_variedad . ' ' . $postco->longitud . 'cm' . '; fecha = ' . $postco->fecha . ' id_postco = ' . $postco->id_postco;
            bitacora('OA_POSTCO', $oa->id_oa_postco, 'I', 'PROCESAR OA: (' . $request->cantidad . ') ramos de ' . $texto);

            foreach ($postco->distribuciones as $dist) {
                $det_ot = new DetalleOaPostco();
                $det_ot->id_oa_postco = $oa->id_oa_postco;
                $det_ot->id_item = $dist->id_item;
                $det_ot->unidades = $dist->unidades;
                $det_ot->save();
            }

            DB::commit();
            $success = true;
            $msg = 'Se ha <strong>GRABADO</strong> la OA correctamente';
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

    public function listar_ordenes_alistamiento(Request $request)
    {
        $listado = OaPostco::where('id_postco', $request->postco)
            ->orderBy('id_oa_postco')
            ->orderBy('longitud')
            ->get();
        $despachadores = Despachador::where('estado', 1)
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.postco.preproduccion.forms.listar_ordenes_alistamiento', [
            'listado' => $listado,
            'despachadores' => $despachadores,
            'postco' => Postco::find($request->postco)
        ]);
    }

    public function update_despachador_oa(Request $request)
    {
        try {
            DB::beginTransaction();
            $model = OaPostco::find($request->id_oa);
            $model->id_despachador = $request->despachador;
            $model->save();

            $success = true;
            $msg = 'Se ha <strong>ASIGNADO</strong> el responsable correctamente';
            bitacora('OA_POSTCO', $model->id_oa_postco, 'U', 'MODIFICAR EL DESPACHADOR de la OA desde PREPRODUCCION (' . $model->ramos . ' ramos)');
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

    public function eliminar_orden_alistamiento(Request $request)
    {
        DB::beginTransaction();
        try {
            $variedades = [];
            $model = OaPostco::find($request->id);
            foreach ($model->detalles as $det) {
                $variedades[] = $det->id_variedad;
            }
            $postco = $model->postco;
            $texto = $postco->id_variedad . ' ' . $postco->longitud . 'cm' . '; fecha = ' . $postco->fecha . ' id_postco = ' . $postco->id_postco;
            bitacora('OA_POSTCO', $model->id_oa_postco, 'D', 'ELIMINAR OA desde PREPRODUCCION: (' . $model->ramos . ') ramos de ' . $texto);

            $model->delete();

            DB::commit();
            $success = true;
            $msg = 'Se ha <strong>ELIMINADO</strong> la orden de trabajo correctamente';
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

    public function convertir_ot(Request $request)
    {
        try {
            DB::beginTransaction();
            $model = OaPostco::find($request->id_oa);
            $model->estado = 'A';
            $model->save();

            $postco = $model->postco;
            $ot = new OtPostco();
            $ot->id_postco  = $model->id_postco;
            $ot->ramos = $model->ramos;
            $ot->fecha = $model->fecha;
            $ot->id_cliente = $model->id_cliente;
            $ot->longitud = $model->longitud;
            $ot->estado = 'P';
            $ot->save();
            $ot->id_ot_postco = DB::table('ot_postco')
                ->select(DB::raw('max(id_ot_postco) as id'))
                ->get()[0]->id;

            $texto = $postco->id_variedad . ' ' . $postco->longitud . 'cm' . '; fecha = ' . $postco->fecha . ' id_postco = ' . $postco->id_postco;
            bitacora('OT_POSTCO', $ot->id_ot_postco, 'I', 'PROCESAR OT: (' . $model->ramos . ') ramos de ' . $texto);

            foreach ($model->detalles as $det) {
                $det_ot = new DetalleOtPostco();
                $det_ot->id_ot_postco = $ot->id_ot_postco;
                $det_ot->id_item = $det->id_item;
                $det_ot->unidades = $det->unidades;
                $det_ot->save();
            }

            $success = true;
            $msg = 'Se ha <strong>CONVERTIDO a OT</strong> la orden correctamente';
            bitacora('OA_POSTCO', $model->id_oa_postco, 'U', 'CONVERTIR a OT desde PREPRODUCCION (' . $model->ramos . ' ramos)');
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
