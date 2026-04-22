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
use yura\Modelos\ArmadoManual;
use yura\Modelos\CodigoAutorizacion;
use yura\Modelos\DetalleArmadoManual;
use yura\Modelos\DetalleCajaProyecto;
use yura\Modelos\DetalleOaPostco;
use yura\Modelos\DetalleOrdenTrabajo;
use yura\Modelos\DistribucionReceta;
use yura\Modelos\InventarioRecepcion;
use yura\Modelos\OaPostco;
use yura\Modelos\OrdenTrabajo;

class PreproduccionController extends Controller
{
    public function inicio(Request $request)
    {
        $finca = getFincaActiva();
        $variedades = Variedad::where('estado', 1)
            ->where('receta', 1)
            ->where('id_empresa', $finca)
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
        if ($request->tipo == 'R') {
            $views = 'listado_recetas';
            $fechas = DB::table('proyecto as p')
                ->join('caja_proyecto as cp', 'cp.id_proyecto', '=', 'p.id_proyecto')
                ->join('detalle_caja_proyecto as dc', 'dc.id_caja_proyecto', '=', 'cp.id_caja_proyecto')
                ->join('variedad as v', 'v.id_variedad', '=', 'dc.id_variedad')
                ->select('p.fecha')->distinct()
                ->where('v.receta', 1)
                ->where('p.fecha', '>=', $request->desde)
                ->where('p.fecha', '<=', $request->hasta);
            if ($request->variedad != 'T')
                $fechas = $fechas->where('dc.id_variedad', $request->variedad);
            $fechas = $fechas->orderBy('p.fecha')->get()->pluck('fecha')->toArray();

            $recetas = DB::table('proyecto as p')
                ->join('caja_proyecto as cp', 'cp.id_proyecto', '=', 'p.id_proyecto')
                ->join('detalle_caja_proyecto as dc', 'dc.id_caja_proyecto', '=', 'cp.id_caja_proyecto')
                ->join('variedad as v', 'v.id_variedad', '=', 'dc.id_variedad')
                ->select('dc.id_variedad', 'v.nombre', 'dc.longitud_ramo')->distinct()
                ->where('v.receta', 1)
                ->where('p.fecha', '>=', $request->desde)
                ->where('p.fecha', '<=', $request->hasta);
            if ($request->variedad != 'T')
                $recetas = $recetas->where('dc.id_variedad', $request->variedad);
            $recetas = $recetas->orderBy('v.nombre')
                ->get();

            $listado = [];
            foreach ($recetas as $receta) {
                $valores = DB::table('proyecto as p')
                    ->join('caja_proyecto as cp', 'cp.id_proyecto', '=', 'p.id_proyecto')
                    ->join('detalle_caja_proyecto as dc', 'dc.id_caja_proyecto', '=', 'cp.id_caja_proyecto')
                    ->select(
                        DB::raw('sum(cp.cantidad * dc.ramos_x_caja) as ramos'),
                        DB::raw('sum(dc.armados) as armados'),
                        DB::raw('sum(dc.despachados) as despachados'),
                        'p.fecha'
                    )
                    ->where('dc.id_variedad', $receta->id_variedad)
                    ->where('dc.longitud_ramo', $receta->longitud_ramo)
                    ->whereIn('p.fecha', $fechas)
                    ->orderBy('p.fecha')
                    ->groupBy('p.fecha')
                    ->get();
                $listado[] = [
                    'receta' => $receta,
                    'valores' => $valores,
                ];
            }
        } else {
            $views = 'listado_flores';
            $fechas = DB::table('proyecto as p')
                ->join('caja_proyecto as cp', 'cp.id_proyecto', '=', 'p.id_proyecto')
                ->join('detalle_caja_proyecto as dc', 'dc.id_caja_proyecto', '=', 'cp.id_caja_proyecto')
                ->join('variedad as v', 'v.id_variedad', '=', 'dc.id_variedad')
                ->select('p.fecha')->distinct()
                ->where('v.receta', 0)
                ->where('p.fecha', '>=', $request->desde)
                ->where('p.fecha', '<=', $request->hasta);
            if ($request->variedad != 'T')
                $fechas = $fechas->where('dc.id_variedad', $request->variedad);
            $fechas = $fechas->orderBy('p.fecha')->get()->pluck('fecha')->toArray();

            $flores = DB::table('proyecto as p')
                ->join('caja_proyecto as cp', 'cp.id_proyecto', '=', 'p.id_proyecto')
                ->join('detalle_caja_proyecto as dc', 'dc.id_caja_proyecto', '=', 'cp.id_caja_proyecto')
                ->join('variedad as v', 'v.id_variedad', '=', 'dc.id_variedad')
                ->select(
                    'dc.id_variedad',
                    'v.nombre'
                )->distinct()
                ->where('v.receta', 0)
                ->where('p.fecha', '>=', $request->desde)
                ->where('p.fecha', '<=', $request->hasta);
            if ($request->variedad != 'T')
                $flores = $flores->where('dc.id_variedad', $request->variedad);
            $flores = $flores->orderBy('v.nombre')
                ->get();

            $listado = [];
            foreach ($flores as $flor) {
                $valores = DB::table('proyecto as p')
                    ->join('caja_proyecto as cp', 'cp.id_proyecto', '=', 'p.id_proyecto')
                    ->join('detalle_caja_proyecto as dc', 'dc.id_caja_proyecto', '=', 'cp.id_caja_proyecto')
                    ->select(
                        DB::raw('sum(cp.cantidad * dc.ramos_x_caja * dc.tallos_x_ramo) as tallos'),
                        DB::raw('sum(cp.cantidad * dc.ramos_x_caja) as ramos'),
                        DB::raw('sum(dc.armados * dc.tallos_x_ramo) as armados'),
                        'p.fecha'
                    )
                    ->where('dc.id_variedad', $flor->id_variedad)
                    ->whereIn('p.fecha', $fechas)
                    ->orderBy('p.fecha')
                    ->groupBy('p.fecha')
                    ->get();
                $listado[] = [
                    'flor' => $flor,
                    'valores' => $valores,
                ];
            }
        }

        return view('adminlte.gestion.postco.preproduccion.partials.' . $views, [
            'listado' => $listado,
            'fechas' => $fechas,
        ]);
    }

    public function modal_receta(Request $request)
    {
        $listado = DetalleCajaProyecto::join('caja_proyecto as cp', 'cp.id_caja_proyecto', '=', 'detalle_caja_proyecto.id_caja_proyecto')
            ->join('proyecto as p', 'p.id_proyecto', '=', 'cp.id_proyecto')
            ->join('detalle_cliente as c', 'c.id_cliente', '=', 'p.id_cliente')
            ->select(
                'detalle_caja_proyecto.id_detalle_caja_proyecto',
                'detalle_caja_proyecto.longitud_ramo',
                'detalle_caja_proyecto.armados',
                'p.fecha',
                'c.nombre as cliente_nombre',
                DB::raw('cp.cantidad * detalle_caja_proyecto.ramos_x_caja as ramos')
            )->distinct()
            ->where('detalle_caja_proyecto.id_variedad', $request->variedad)
            ->where('detalle_caja_proyecto.longitud_ramo', $request->longitud)
            ->where('c.estado', 1)
            ->whereIn('p.fecha', json_decode($request->fechas))
            ->orderBy('p.fecha')
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
            $model = DetalleCajaProyecto::find($request->id);
            $model->armados += $request->cantidad;
            $model->save();
            $texto = $model->id_variedad . ' ' . $model->longitud_ramo . 'cm' . '; fecha = ' . $model->getFecha();
            bitacora('DETALLE_CAJA_PROYECTO', $request->id, 'U', 'ARMAR MANUALMENTE: (' . $request->cantidad . ') ramos de ' . $texto);

            $model_armado = new ArmadoManual();
            $model_armado->id_detalle_caja_proyecto = $model->id_detalle_caja_proyecto;
            $model_armado->ramos = $request->cantidad;
            $model_armado->save();
            $model_armado->id_armado_manual = DB::table('armado_manual')
                ->select(DB::raw('max(id_armado_manual) as id'))
                ->get()[0]->id;

            foreach ($model->distribuciones as $key => $dist) {
                $det_armado = new DetalleArmadoManual();
                $det_armado->id_armado_manual = $model_armado->id_armado_manual;
                $det_armado->id_variedad = $dist->id_variedad;
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
            $detalle = DetalleCajaProyecto::find($request->id);
            $ot = new OrdenTrabajo();
            $ot->id_detalle_caja_proyecto = $request->id;
            $ot->ramos = $request->cantidad;
            $ot->fecha = $request->fecha;
            $ot->id_cliente = $detalle->caja_proyecto->proyecto->id_cliente;
            $ot->longitud = $request->longitud;
            $ot->estado = 'P';
            $ot->save();
            $ot->id_orden_trabajo = DB::table('orden_trabajo')
                ->select(DB::raw('max(id_orden_trabajo) as id'))
                ->get()[0]->id;

            $texto = $detalle->id_variedad . ' ' . $detalle->longitud . 'cm' . '; fecha = ' . $detalle->getFecha() . ' id_detalle_caja_proyecto = ' . $detalle->id_detalle_caja_proyecto;
            bitacora('ORDEN_TRABAJO', $ot->id_orden_trabajo, 'I', 'PROCESAR OT: (' . $request->cantidad . ') ramos de ' . $texto);

            foreach ($detalle->distribuciones as $dist) {
                $det_ot = new DetalleOrdenTrabajo();
                $det_ot->id_orden_trabajo = $ot->id_orden_trabajo;
                $det_ot->id_variedad = $dist->id_variedad;
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
        $finca = getFincaActiva();
        $det_caja = DetalleCajaProyecto::find($request->id);
        $caja = $det_caja->caja_proyecto;
        $numeros_receta = DB::table('detalle_receta')
            ->select('numero_receta')->distinct()
            ->where('id_variedad', $det_caja->id_variedad)
            ->get()->pluck('numero_receta')->toArray();
        return view('adminlte.gestion.postco.preproduccion.forms.admin_receta', [
            'proyecto' => $caja->proyecto,
            'caja' => $caja,
            'det_caja' => $det_caja,
            'numeros_receta' => $numeros_receta,
            'plantas' => Planta::where('estado', '=', 1)->where('id_empresa', $finca)->orderBy('nombre')->get(),
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
            $variedades = [];

            $delete = DistribucionReceta::where('id_detalle_caja_proyecto', $request->id_detalle)
                ->get();
            foreach ($delete as $del) {
                $variedades[] = $del->id_variedad;  // guardar en este listado los ids de variedades para actualizar la tabla resumen_agrogana
                $del->delete();
            }

            foreach (json_decode($request->data) as $d) {
                $model = new DistribucionReceta();
                $model->id_detalle_caja_proyecto = $request->id_detalle;
                $model->id_variedad = $d->id_variedad;
                $model->longitud = $d->longitud;
                $model->unidades = $d->unidades;
                $model->save();

                if (!in_array($d->id_variedad, $variedades))
                    $variedades[] = $d->id_variedad;  // guardar en este listado los ids de variedades para actualizar la tabla resumen_agrogana
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
        $listado = OrdenTrabajo::where('id_detalle_caja_proyecto', $request->id)
            ->orderBy('id_orden_trabajo')
            ->orderBy('longitud')
            ->get();
        $despachadores = Despachador::where('estado', 1)
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.postco.preproduccion.forms.listar_ordenes_trabajo', [
            'listado' => $listado,
            'despachadores' => $despachadores,
            'detalle' => DetalleCajaProyecto::find($request->id)
        ]);
    }

    public function update_despachador(Request $request)
    {
        try {
            DB::beginTransaction();
            $model = OrdenTrabajo::find($request->id_ot);
            $model->id_despachador = $request->despachador;
            $model->save();

            $success = true;
            $msg = 'Se ha <strong>ASIGNADO</strong> el responsable correctamente';
            bitacora('ORDEN_TRABAJO', $model->id_orden_trabajo, 'U', 'MODIFICAR EL DESPACHADOR de la OT desde PREPRODUCCION (' . $model->ramos . ' ramos)');
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
            $model = OrdenTrabajo::find($request->id);
            foreach ($model->detalles as $det) {
                $variedades[] = $det->id_variedad;
            }
            $detalle = $model->detalle_caja_proyecto;
            $texto = $detalle->id_variedad . ' ' . $detalle->longitud_ramo . 'cm' . '; fecha = ' . $detalle->getFecha() . ' id_detalle_caja_proyecto = ' . $detalle->id_detalle_caja_proyecto;
            bitacora('ORDEN_TRABAJO', $model->id_orden_trabajo, 'D', 'ELIMINAR OT desde PREPRODUCCION: (' . $model->ramos . ') ramos de ' . $texto);

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
        $orden_trabajo = OrdenTrabajo::find($request->id);
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
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TxR');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'RESPONSABLE');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'OBSERVACION');

        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        $detalle = $orden_trabajo->detalle_caja_proyecto;
        $tallos_x_ramo = 0;
        $total_tallos = 0;
        $detalles_ot = $orden_trabajo->detalles;
        foreach ($detalles_ot as $det) {
            $total_tallos += $det->unidades * $orden_trabajo->ramos;
            $tallos_x_ramo += $det->unidades;
        }

        foreach ($detalles_ot as $pos_d => $det_ot) {
            $row++;
            if ($pos_d == 0) {
                $col = 0;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, '#' . $orden_trabajo->id_orden_trabajo);
                $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($detalles_ot) - 1));
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, convertDateToText($detalle->getFecha()));
                $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($detalles_ot) - 1));
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $orden_trabajo->cliente->detalle()->nombre);
                $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($detalles_ot) - 1));
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $detalle->variedad->nombre);
                $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($detalles_ot) - 1));
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $orden_trabajo->longitud);
                $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($detalles_ot) - 1));
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $orden_trabajo->ramos);
                $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($detalles_ot) - 1));
            }
            $col = 6;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $det_ot->variedad->nombre);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $det_ot->unidades * $orden_trabajo->ramos);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $det_ot->unidades);
            if ($pos_d == 0) {
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $tallos_x_ramo);
                $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($detalles_ot) - 1));
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $despachador != '' ? $despachador->nombre : '');
                $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($detalles_ot) - 1));
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $orden_trabajo->observacion);
                $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($detalles_ot) - 1));
            }
        }
        $row++;
        $col = 7;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_tallos);
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $tallos_x_ramo);
        $col = 11;
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
            $detalle = DetalleCajaProyecto::find($request->id);
            $distribucion = $detalle->distribuciones;
            $recetas = DB::table('proyecto as p')
                ->join('caja_proyecto as cp', 'cp.id_proyecto', '=', 'p.id_proyecto')
                ->join('detalle_caja_proyecto as dc', 'dc.id_caja_proyecto', '=', 'cp.id_caja_proyecto')
                ->join('variedad as v', 'v.id_variedad', '=', 'dc.id_variedad')
                ->select('dc.*')->distinct()
                ->where('p.fecha', '>=', $request->desde)
                ->where('p.fecha', '<=', $request->hasta)
                ->where('dc.id_variedad', $detalle->id_variedad)
                ->where('dc.longitud_ramo', $detalle->longitud_ramo)
                ->orderBy('v.nombre')
                ->get()->pluck('id_detalle_caja_proyecto')->toArray();
            foreach ($recetas as $p_query) {
                DB::select('delete from distribucion_receta where id_detalle_caja_proyecto = ' . $p_query);
                foreach ($distribucion as $dist) {
                    $model = new DistribucionReceta();
                    $model->id_detalle_caja_proyecto = $p_query;
                    $model->id_variedad = $dist->id_variedad;
                    $model->unidades = $dist->unidades;
                    $model->longitud = $dist->longitud;
                    $model->save();
                }
            }

            DB::commit();
            $success = true;
            $msg = 'Se ha <strong>COPIADO</strong> la distribucion correctamente';
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

    public function modal_flor(Request $request)
    {
        $listado = DetalleCajaProyecto::join('caja_proyecto as cp', 'cp.id_caja_proyecto', '=', 'detalle_caja_proyecto.id_caja_proyecto')
            ->join('proyecto as p', 'p.id_proyecto', '=', 'cp.id_proyecto')
            ->join('detalle_cliente as c', 'c.id_cliente', '=', 'p.id_cliente')
            ->select(
                'detalle_caja_proyecto.id_detalle_caja_proyecto',
                'detalle_caja_proyecto.longitud_ramo',
                'detalle_caja_proyecto.tallos_x_ramo',
                'detalle_caja_proyecto.armados',
                'p.fecha',
                'p.packing',
                'c.nombre as cliente_nombre',
                DB::raw('cp.cantidad * detalle_caja_proyecto.ramos_x_caja as ramos'),
                DB::raw('cp.cantidad * detalle_caja_proyecto.ramos_x_caja * detalle_caja_proyecto.tallos_x_ramo as tallos')
            )->distinct()
            ->where('detalle_caja_proyecto.id_variedad', $request->variedad)
            ->where('c.estado', 1)
            ->whereIn('p.fecha', json_decode($request->fechas))
            ->orderBy('p.fecha')
            ->get();
        $inventario = getTotalInventarioByVariedad($request->variedad);
        return view('adminlte.gestion.postco.preproduccion.forms.modal_flor', [
            'listado' => $listado,
            'variedad' => Variedad::find($request->variedad),
            'inventario' => $inventario,
            'fechas' => $request->fechas,
        ]);
    }

    public function store_armar_flor(Request $request)
    {
        try {
            DB::beginTransaction();
            $finca = getFincaActiva();
            $det_caja = DetalleCajaProyecto::find($request->id);

            $inventarios = InventarioRecepcion::where('id_empresa', $finca)
                ->where('disponibles', '>', 0)
                ->where('id_variedad', $det_caja->id_variedad)
                ->orderBy('fecha', 'asc')
                ->get();

            $sacar = $request->armar * $det_caja->tallos_x_ramo;
            foreach ($inventarios as $model) {
                if ($sacar >= 0) {
                    $disponible = $model->disponibles;
                    if ($sacar >= $disponible) {
                        $sacar = $sacar - $disponible;
                        $disponible = 0;
                    } else {
                        $disponible = $disponible - $sacar;
                        $sacar = 0;
                    }

                    $model->disponibles = $disponible;
                    $model->save();
                }
            }

            $det_caja->armados += $request->armar;
            $det_caja->save();

            $success = true;
            $msg = 'Se han <strong>ARMADO</strong> los ramos correctamente';
            bitacora('POSTCO', $model->id_postco, 'U', 'BLOQUEO/DESBLOQUEO de RECETA');
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
