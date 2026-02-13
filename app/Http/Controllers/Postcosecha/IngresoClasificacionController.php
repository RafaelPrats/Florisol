<?php

namespace yura\Http\Controllers\Postcosecha;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use PDF;
use Picqer\Barcode\BarcodeGeneratorHTML;
use yura\Http\Controllers\Controller;
use yura\Modelos\DetalleImportPedido;
use yura\Modelos\InventarioFrio;
use yura\Modelos\OrdenTrabajo;
use yura\Modelos\PreOrdenTrabajo;
use yura\Modelos\SalidasRecepcion;
use yura\Modelos\Submenu;
use yura\Modelos\Variedad;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use yura\Jobs\jobUpdateResumenAgrogana;
use yura\Jobs\jobUpdateVariedades;
use yura\Modelos\DesgloseRecepcion;
use yura\Modelos\Despachador;
use yura\Modelos\DetalleOrdenTrabajo;
use yura\Modelos\DetallePreOrdenTrabajo;
use yura\Modelos\DistribucionReceta;
use yura\Modelos\ItemsImportPedido;
use yura\Modelos\Planta;
use yura\Modelos\PreOrdenParcial;

class IngresoClasificacionController extends Controller
{
    public function inicio(Request $request)
    {
        $variedades = Variedad::where('estado', 1)
            ->where('receta', 1)
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.postcocecha.ingreso_clasificacion.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'variedades' => $variedades,
        ]);
    }

    public function listar_reporte(Request $request)
    {
        $fecha_ini = DB::table('detalle_import_pedido as d')
            ->join('import_pedido as p', 'p.id_import_pedido', '=', 'd.id_import_pedido')
            ->select(DB::raw('min(p.fecha) as fecha'))
            ->where('d.ejecutado', 0)
            ->where('p.estado', 1);
        if ($request->variedad != 'T')
            $fecha_ini = $fecha_ini->where('d.id_variedad', $request->variedad);
        $fecha_ini = $fecha_ini->get()[0]->fecha;

        $fecha_ini = hoy();
        if ($fecha_ini != '') {
            $fecha_fin = opDiasFecha('+', $request->dias, $fecha_ini);

            $fechas = DB::table('detalle_import_pedido as d')
                ->join('import_pedido as p', 'p.id_import_pedido', '=', 'd.id_import_pedido')
                ->select('p.fecha')->distinct()
                ->where('p.fecha', '>=', $fecha_ini)
                ->where('p.fecha', '<=', $fecha_fin)
                ->where('p.estado', 1)
                ->where('d.ejecutado', 0);
            if ($request->variedad != 'T')
                $fechas = $fechas->where('d.id_variedad', $request->variedad);
            $fechas = $fechas->orderBy('fecha')
                ->get()->pluck('fecha')->toArray();

            $combinaciones = DB::table('detalle_import_pedido as d')
                ->join('import_pedido as p', 'p.id_import_pedido', '=', 'd.id_import_pedido')
                ->join('variedad as v', 'v.id_variedad', '=', 'd.id_variedad')
                ->select('d.id_variedad', 'd.longitud', 'v.nombre', 'v.siglas')->distinct()
                ->where('p.estado', 1)
                ->where('p.fecha', '>=', $fecha_ini)
                ->where('p.fecha', '<=', $fecha_fin);
            if ($request->variedad != 'T')
                $combinaciones = $combinaciones->where('d.id_variedad', $request->variedad);
            $combinaciones = $combinaciones->get();

            $listado = [];
            foreach ($combinaciones as $pos_i => $item) {
                $valores = [];
                //if ($pos_i > -1)
                    foreach ($fechas as $fecha) {
                        $venta = DB::table('import_pedido as p')
                            ->join('detalle_import_pedido as d', 'd.id_import_pedido', '=', 'p.id_import_pedido')
                            ->select(DB::raw('sum(d.ramos * d.caja) as cantidad'))
                            ->where('p.fecha', $fecha)
                            ->where('d.id_variedad', $item->id_variedad)
                            ->where('d.longitud', $item->longitud)
                            ->where('p.estado', 1)
                            ->get()[0]->cantidad;
                        $distribuciones = DB::table('detalle_import_pedido as d')
                            ->join('import_pedido as p', 'p.id_import_pedido', '=', 'd.id_import_pedido')
                            ->join('distribucion_receta as r', 'r.id_detalle_import_pedido', '=', 'd.id_detalle_import_pedido')
                            ->join('variedad as v', 'v.id_variedad', '=', 'r.id_variedad')
                            ->join('planta as pta', 'pta.id_planta', '=', 'v.id_planta')
                            ->select(
                                'r.id_variedad',
                                'r.unidades',
                            )->distinct()
                            ->where('p.fecha', $fecha)
                            ->where('d.id_variedad', $item->id_variedad)
                            ->where('d.longitud', $item->longitud)
                            ->where('p.estado', 1)
                            ->get();
                        $ramos_disponibles = $venta;
                        foreach ($distribuciones as $dist) {
                            $inventario = getTotalInventarioByVariedad($dist->id_variedad);
                            if ($ramos_disponibles > intVal($inventario / $dist->unidades)) {
                                $ramos_disponibles = intVal($inventario / $dist->unidades);
                            }
                        }
                        $ot_despachos = DB::table('orden_trabajo as o')
                            ->join('detalle_import_pedido as d', 'd.id_detalle_import_pedido', '=', 'o.id_detalle_import_pedido')
                            ->join('import_pedido as p', 'p.id_import_pedido', '=', 'd.id_import_pedido')
                            ->select(
                                DB::raw('sum(o.ramos) as cantidad'),
                            )
                            ->where('d.id_variedad', $item->id_variedad)
                            ->where('d.longitud', $item->longitud)
                            ->where('o.entregado', 1)
                            //->where('o.armado', 0)
                            ->where('p.fecha', $fecha)
                            ->where('p.estado', 1)
                            ->get()[0]->cantidad;

                        $armados = DB::table('orden_trabajo as o')
                            ->join('detalle_import_pedido as d', 'd.id_detalle_import_pedido', '=', 'o.id_detalle_import_pedido')
                            ->join('import_pedido as p', 'p.id_import_pedido', '=', 'd.id_import_pedido')
                            ->select(
                                DB::raw('sum(o.ramos_armados) as cantidad'),
                            )
                            ->where('d.id_variedad', $item->id_variedad)
                            ->where('o.longitud', $item->longitud)
                            ->where('o.armado', 1)
                            ->where('p.fecha', $fecha)
                            ->where('p.estado', 1)
                            ->get()[0]->cantidad;
                        $armados += DB::table('detalle_import_pedido as d')
                            ->join('import_pedido as p', 'p.id_import_pedido', '=', 'd.id_import_pedido')
                            ->select(
                                DB::raw('sum(d.ramos_armados) as cantidad'),
                            )
                            ->where('d.id_variedad', $item->id_variedad)
                            ->where('d.longitud', $item->longitud)
                            ->where('p.fecha', $fecha)
                            ->where('p.estado', 1)
                            ->get()[0]->cantidad;

                        $valores[] = [
                            'venta' => $venta,
                            'armados' => $armados,
                            'ramos_disponibles' => $ramos_disponibles,
                            'ot_despachos' => $ot_despachos,
                        ];
                    }

                $ramos_orden = DB::table('orden_trabajo as o')
                    ->join('detalle_import_pedido as d', 'd.id_detalle_import_pedido', '=', 'o.id_detalle_import_pedido')
                    ->join('import_pedido as p', 'p.id_import_pedido', '=', 'd.id_import_pedido')
                    ->select(
                        DB::raw('sum(o.ramos) as cantidad'),
                    )
                    ->where('d.id_variedad', $item->id_variedad)
                    ->where('o.longitud', $item->longitud)
                    ->where('o.entregado', 1)
                    //->where('o.armado', 0)
                    ->whereIn('p.fecha', $fechas)
                    ->where('p.estado', 1)
                    ->get()[0]->cantidad;

                $listado[] = [
                    'ramos_orden' => $ramos_orden,
                    'item' => $item,
                    'valores' => $valores,
                ];
            }

            /*$salidas_recepcion = DB::table('salidas_recepcion as s')
                ->join('variedad as v', 'v.id_variedad', '=', 's.id_variedad')
                ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                ->select(
                    'p.nombre as planta_nombre',
                    'v.nombre as variedad_nombre',
                    's.id_variedad',
                    'v.id_planta',
                    DB::raw('sum(s.cantidad) as cantidad'),
                    DB::raw('sum(s.disponibles) as disponibles'),
                    DB::raw('sum(s.usados) as usados')
                )
                ->where('s.fecha', $request->fecha)
                ->where('s.basura', 0)
                ->groupBy(
                    'p.nombre',
                    'v.nombre',
                    's.id_variedad',
                    'v.id_planta'
                )
                ->get();*/

            $listado_resumen_salidas = [];
            /*foreach ($salidas_recepcion as $item) {
                $venta = DB::table('import_pedido as p')
                    ->join('detalle_import_pedido as d', 'd.id_import_pedido', '=', 'p.id_import_pedido')
                    ->join('distribucion_receta as r', 'r.id_detalle_import_pedido', '=', 'd.id_detalle_import_pedido')
                    ->select(DB::raw('sum(d.ramos * d.caja * r.unidades) as cantidad'))
                    ->where('r.id_variedad', $item->id_variedad)
                    ->where('p.estado', 1)
                    ->get()[0]->cantidad;
                $listado_resumen_salidas[] = [
                    'item' => $item,
                    'venta' => $venta,
                ];
            }*/

            return view('adminlte.gestion.postcocecha.ingreso_clasificacion.partials.listado', [
                'listado' => $listado,
                'fechas' => $fechas,
                'listado_resumen_salidas' => $listado_resumen_salidas,
            ]);
        } else
            return '<div class="alert alert-info text-center">No hay pedidos para confirmar</div>';
    }

    public function armar_combinacion(Request $request)
    {
        $finca = getFincaActiva();
        $pedidos = DB::table('detalle_import_pedido as d')
            ->join('import_pedido as p', 'p.id_import_pedido', '=', 'd.id_import_pedido')
            ->join('detalle_cliente as dc', 'dc.id_cliente', '=', 'p.id_cliente')
            ->select(
                'p.id_import_pedido',
                'd.id_detalle_import_pedido',
                'd.bloquear_distribucion',
                'd.ramos_armados',
                'd.ejecutado',
                'p.codigo',
                'p.codigo_ref',
                'p.id_cliente',
                'dc.nombre as nombre_cliente'
            )->distinct()
            ->where('p.fecha', $request->fecha)
            ->where('d.id_variedad', $request->variedad)
            ->where('d.longitud', $request->longitud)
            ->where('p.id_empresa', $finca)
            ->where('dc.estado', 1)
            ->where('p.estado', 1)
            ->orderBy('dc.nombre')
            ->get();

        $listado = [];
        foreach ($pedidos as $p) {
            $ramos_venta = DB::table('import_pedido as p')
                ->join('detalle_import_pedido as d', 'd.id_import_pedido', '=', 'p.id_import_pedido')
                ->select(DB::raw('sum(d.ramos * d.caja) as cantidad'))
                ->where('d.id_import_pedido', $p->id_import_pedido)
                ->where('d.id_detalle_import_pedido', $p->id_detalle_import_pedido)
                ->where('d.id_variedad', $request->variedad)
                ->where('d.longitud', $request->longitud)
                ->get()[0]->cantidad;
            $resumen_orden = DB::table('orden_trabajo')
                ->select(
                    DB::raw('sum(ramos) as ramos'),
                    DB::raw('sum(ramos_armados) as ramos_armados'),
                )
                ->where('id_detalle_import_pedido', $p->id_detalle_import_pedido)
                ->where('longitud', $request->longitud)
                ->get()[0];
            $ramos_orden = $resumen_orden->ramos;
            $ramos_armados_orden = $resumen_orden->ramos_armados;
            $ramos_pre_ot = DB::table('pre_orden_trabajo')
                ->select(
                    DB::raw('sum(ramos) as ramos')
                )
                ->where('id_detalle_import_pedido', $p->id_detalle_import_pedido)
                ->where('longitud', $request->longitud)
                ->where('estado', 1)
                ->get()[0];
            $distribucion = DB::table('detalle_import_pedido as d')
                ->join('import_pedido as p', 'p.id_import_pedido', '=', 'd.id_import_pedido')
                ->join('distribucion_receta as r', 'r.id_detalle_import_pedido', '=', 'd.id_detalle_import_pedido')
                ->join('variedad as v', 'v.id_variedad', '=', 'r.id_variedad')
                ->join('planta as pta', 'pta.id_planta', '=', 'v.id_planta')
                ->select(
                    'r.id_variedad',
                    'v.nombre as nombre_variedad',
                    'r.unidades',
                    'v.id_planta',
                    'pta.nombre as nombre_planta'
                )->distinct()
                ->where('d.id_import_pedido', $p->id_import_pedido)
                ->where('d.id_detalle_import_pedido', $p->id_detalle_import_pedido)
                ->where('d.id_variedad', $request->variedad)
                ->where('d.longitud', $request->longitud)
                ->orderBy('pta.nombre')
                ->orderBy('v.nombre')
                ->get();
            $listado[] = [
                'pedido' => $p,
                'ramos_venta' => $ramos_venta,
                'ramos_orden' => $ramos_orden,
                'ramos_armados_orden' => $ramos_armados_orden,
                'ramos_pre_ot' => $ramos_pre_ot->ramos,
                'distribucion' => $distribucion
            ];
        }

        $usuarios = DB::table('permiso_accion')
            ->where('accion', 'CONFIRMAR_PREPRODUCCION')
            ->get()
            ->pluck('id_usuario')
            ->toArray();
        return view('adminlte.gestion.postcocecha.ingreso_clasificacion.forms.armar_combinacion', [
            'listado' => $listado,
            'variedad' => Variedad::find($request->variedad),
            'fecha' => $request->fecha,
            'fecha_trabajo' => $request->fecha_trabajo,
            'longitud' => $request->longitud,
            'usuarios' => $usuarios,
        ]);
    }

    public function store_armar_pedido(Request $request)
    {
        DB::beginTransaction();
        try {
            $finca = getFincaActiva();
            $variedades = [];
            // --------- REGISTRAR ORDEN DE TRABAJO ------------ //
            $orden = new OrdenTrabajo();
            $orden->id_detalle_import_pedido = $request->detalle_pedido;
            $orden->fecha = $request->fecha_trabajo;
            $orden->longitud = $request->longitud;
            $orden->ramos = $request->armar;
            $orden->id_empresa = $finca;
            $orden->save();
            $orden->id_orden_trabajo = DB::table('orden_trabajo')
                ->select(DB::raw('max(id_orden_trabajo) as id'))
                ->get()[0]->id;

            foreach (json_decode($request->data) as $d) {
                // --------- REGISTRAR DETALLE de la ORDEN DE TRABAJO ------------ //
                $detalle = new DetalleOrdenTrabajo();
                $detalle->id_orden_trabajo = $orden->id_orden_trabajo;
                $detalle->id_variedad = $d->variedad;
                $detalle->tallos = $d->usar;
                $detalle->save();

                $variedades[] = $d->variedad;
            }

            foreach ($variedades as $var) {
                /* ACTUALIZAR TABLA RESUMEN_AGROGANA */
                jobUpdateResumenAgrogana::dispatch($request->fecha_trabajo, $var)->onQueue('resumen_agrogana')->onConnection('database');

                /* TABLA RESUMEN_FECHAS */
                Artisan::call('resumen:fecha', [
                    'fecha' => $request->fecha_trabajo,
                    'variedad' => $var,
                    'dev' => 1,
                ]);
            }
            jobUpdateVariedades::dispatch($request->fecha_trabajo, 0)->onQueue('update_variedades')->onConnection('database');
            bitacora('ORDEN_TRABAJO', $orden->id_orden_trabajo, 'I', 'CREAR OT desde PREPRODUCCION (' . $request->armar . ' ramos)');

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

    public function exportar_excel_fecha(Request $request)
    {
        $spread = new Spreadsheet();
        $this->excel_reporte($spread, $request);

        $fileName = "Preproduccion.xlsx";
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
        $detalles = DetalleImportPedido::join('import_pedido as p', 'p.id_import_pedido', '=', 'detalle_import_pedido.id_import_pedido')
            ->select(
                'detalle_import_pedido.*'
            )->distinct()
            ->where('p.estado', 1)
            ->where('p.fecha', $request->fecha)
            ->where('p.id_empresa', $finca)
            ->get();

        $columnas = getColumnasExcel();
        $sheet = $spread->getActiveSheet();
        $sheet->setTitle('Preproduccion');

        $row = 1;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Pedido');
        $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + 1));
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Cliente');
        $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + 1));
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Receta');
        $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + 1));
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Longitud');
        $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + 1));
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Ramos Pedidos');
        $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + 1));
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Distribucion Receta');
        $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col + 2] . $row);
        $col += 3;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Tallos');
        $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + 1));
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Inventario');
        $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + 1));

        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        $row++;
        $col = 5;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Planta');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Variedad');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Unidades');

        setBgToCeldaExcel($sheet, $columnas[5] . $row . ':' . $columnas[$col] . $row, '5a7177');
        setColorTextToCeldaExcel($sheet, $columnas[5] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        $total_ramos_pedidos = 0;
        $resumen_variedades = [];
        foreach ($detalles as $det) {
            $pedido = $det->pedido;
            $detalles_receta = $det->detalles_receta;
            foreach ($detalles_receta as $pos_dist => $dist) {
                $inventario = getTotalInventarioByVariedad($dist->id_variedad);

                $pos_en_resumen = -1;
                foreach ($resumen_variedades as $pos => $r) {
                    if ($r['variedad']->id_variedad == $dist->id_variedad) {
                        $pos_en_resumen = $pos;
                    }
                }
                if ($pos_en_resumen != -1) {
                    $resumen_variedades[$pos_en_resumen]['tallos'] += $dist->unidades * $det->ramos * $det->caja;
                } else {
                    $resumen_variedades[] = [
                        'variedad' => Variedad::find($dist->id_variedad),
                        'tallos' => $dist->unidades * $det->ramos * $det->caja,
                    ];
                }

                $row++;
                if ($pos_dist == 0) {
                    $col = 0;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, '#' . $pedido->codigo);
                    $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($detalles_receta) - 1));
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $pedido->cliente->detalle()->nombre);
                    $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($detalles_receta) - 1));
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $det->variedad->nombre);
                    $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($detalles_receta) - 1));
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $det->longitud . 'cm');
                    $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($detalles_receta) - 1));
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $det->ramos * $det->caja);
                    $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($detalles_receta) - 1));

                    $total_ramos_pedidos += $det->ramos * $det->caja;
                }
                $col = 5;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $dist->variedad->planta->nombre);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $dist->variedad->nombre);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $dist->unidades);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $dist->unidades * $det->ramos * $det->caja);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $inventario);
            }
        }

        $row++;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TOTALES');
        $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col + 3] . $row);
        $col += 4;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_ramos_pedidos);
        $col = 9;
        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        setTextCenterToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);

        setBorderToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);

        for ($i = 0; $i <= $col; $i++)
            $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);

        $sheet = $spread->createSheet()->setTitle('RESUMEN por VARIEDADES');

        $row = 1;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Planta');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Variedad');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Pedidos');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Inventario');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Saldo');
        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        $total_tallos = 0;
        $total_inventario = 0;
        foreach ($resumen_variedades as $r) {
            $inventario = getTotalInventarioByVariedad($r['variedad']->id_variedad);
            $saldo = $inventario - $r['tallos'] + 0;
            $total_tallos += $r['tallos'];
            $total_inventario += $inventario;

            $row++;
            $col = 0;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $r['variedad']->planta->nombre);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $r['variedad']->nombre);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $r['tallos']);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $inventario);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $saldo);
        }
        $saldo_total = $total_inventario - $total_tallos + 0;
        $row++;
        $col = 0;
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TOTALES');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_tallos);
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_inventario);
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $saldo_total);
        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        setTextCenterToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);

        setBorderToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);

        for ($i = 0; $i <= $col; $i++)
            $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);
    }

    public function admin_receta(Request $request)
    {
        $detalle_pedido = DetalleImportPedido::find($request->det_ped);

        $numeros_receta = DB::table('detalle_receta')
            ->select('numero_receta')->distinct()
            ->where('id_variedad', $detalle_pedido->id_variedad)
            ->get()->pluck('numero_receta')->toArray();
        return view('adminlte.gestion.postcocecha.ingreso_clasificacion.forms.admin_receta', [
            'detalle_pedido' => $detalle_pedido,
            'numeros_receta' => $numeros_receta,
            'plantas' => Planta::where('estado', '=', 1)->orderBy('nombre')->get(),
        ]);
    }

    public function exportar_receta(Request $request)
    {
        $spread = new Spreadsheet();
        $this->excel_reporte_receta($spread, $request);
        $fileName = "Preproduccion.xlsx";
        $writer = new Xlsx($spread);

        //--------------------------- GUARDAR EL EXCEL -----------------------

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        $writer->save('php://output');

        //$writer->save('/var/www/html/Dasalflor/storage/storage/excel/excel_prueba.xlsx');
    }

    public function excel_reporte_receta($spread, $request)
    {
        $finca = getFincaActiva();
        $pedidos = DB::table('detalle_import_pedido as d')
            ->join('import_pedido as p', 'p.id_import_pedido', '=', 'd.id_import_pedido')
            ->join('detalle_cliente as dc', 'dc.id_cliente', '=', 'p.id_cliente')
            ->select(
                'p.id_import_pedido',
                'd.id_detalle_import_pedido',
                'd.ejecutado',
                'p.codigo',
                'p.id_cliente',
                'dc.nombre as nombre_cliente'
            )->distinct()
            ->where('p.fecha', $request->fecha)
            ->where('d.id_variedad', $request->variedad)
            ->where('d.longitud', $request->longitud)
            ->where('p.id_empresa', $finca)
            ->where('dc.estado', 1)
            ->where('p.estado', 1)
            ->orderBy('dc.nombre')
            ->get();

        $listado = [];
        foreach ($pedidos as $p) {
            $ramos_venta = DB::table('import_pedido as p')
                ->join('detalle_import_pedido as d', 'd.id_import_pedido', '=', 'p.id_import_pedido')
                ->select(DB::raw('sum(d.ramos * d.caja) as cantidad'))
                ->where('d.id_import_pedido', $p->id_import_pedido)
                ->where('d.id_detalle_import_pedido', $p->id_detalle_import_pedido)
                ->where('d.id_variedad', $request->variedad)
                ->where('d.longitud', $request->longitud)
                ->where('p.estado', 1)
                ->get()[0]->cantidad;
            $distribucion = DB::table('detalle_import_pedido as d')
                ->join('import_pedido as p', 'p.id_import_pedido', '=', 'd.id_import_pedido')
                ->join('distribucion_receta as r', 'r.id_detalle_import_pedido', '=', 'd.id_detalle_import_pedido')
                ->join('variedad as v', 'v.id_variedad', '=', 'r.id_variedad')
                ->join('planta as pta', 'pta.id_planta', '=', 'v.id_planta')
                ->select(
                    'r.id_variedad',
                    'v.nombre as nombre_variedad',
                    'r.unidades',
                    'v.id_planta',
                    'pta.nombre as nombre_planta'
                )->distinct()
                ->where('p.estado', 1)
                ->where('d.id_import_pedido', $p->id_import_pedido)
                ->where('d.id_detalle_import_pedido', $p->id_detalle_import_pedido)
                ->where('d.id_variedad', $request->variedad)
                ->where('d.longitud', $request->longitud)
                ->orderBy('pta.nombre')
                ->orderBy('v.nombre')
                ->get();
            $listado[] = [
                'pedido' => $p,
                'ramos_venta' => $ramos_venta,
                'distribucion' => $distribucion
            ];
        }
        $variedad = Variedad::find($request->variedad);

        $columnas = getColumnasExcel();
        $sheet = $spread->getActiveSheet();
        $sheet->setTitle('Preproduccion');

        $row = 1;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'PEDIDO');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'CLIENTE');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'RAMOS del PEDIDO');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'PLANTA DE LA RECETA');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'VARIEDAD DE LA RECETA');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'UNIDADES DE LA RECETA');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TALLOS');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'INVENTARIO');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'RAMOS DISPONIBLES');

        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        $total_ramos = 0;
        $total_tallos = 0;
        $resumen_variedades = [];
        foreach ($listado as $pos_p => $item) {
            $total_ramos += $item['ramos_venta'];
            $ramos_disponibles = $item['ramos_venta'];
            $row_ini = $row + 1;
            foreach ($item['distribucion'] as $pos_d => $dist) {
                $total_tallos += $dist->unidades * $item['ramos_venta'];
                $inventario = getTotalInventarioByVariedad($dist->id_variedad);
                if ($ramos_disponibles > intVal($inventario / $dist->unidades)) {
                    $ramos_disponibles = intVal($inventario / $dist->unidades);
                }

                $pos_en_resumen = -1;
                foreach ($resumen_variedades as $pos => $r) {
                    if ($r['variedad']->id_variedad == $dist->id_variedad) {
                        $pos_en_resumen = $pos;
                    }
                }
                if ($pos_en_resumen != -1) {
                    $resumen_variedades[$pos_en_resumen]['tallos'] += $dist->unidades * $item['ramos_venta'];
                } else {
                    $resumen_variedades[] = [
                        'variedad' => $dist,
                        'tallos' => $dist->unidades * $item['ramos_venta'],
                    ];
                }

                $row++;
                if ($pos_d == 0) {
                    $col = 0;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, '#' . $item['pedido']->codigo);
                    $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($item['distribucion']) - 1));
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item['pedido']->nombre_cliente);
                    $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($item['distribucion']) - 1));
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item['ramos_venta']);
                    $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($item['distribucion']) - 1));
                }
                $col = 3;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $dist->nombre_planta);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $dist->nombre_variedad);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $dist->unidades);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $dist->unidades * $item['ramos_venta']);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $inventario);
                $col++;
            }
            setValueToCeldaExcel($sheet, $columnas[8] . $row_ini, $ramos_disponibles);
            $sheet->mergeCells($columnas[8] . $row_ini . ':' . $columnas[8] . ($row_ini + count($item['distribucion']) - 1));
        }

        setTextCenterToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);
        setBorderToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);

        for ($i = 0; $i <= $col; $i++)
            $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);

        $sheet = $spread->createSheet();
        $sheet->setTitle('Resumen');

        $row = 1;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Planta');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Variedad');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Pedidos');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Inventario');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Saldo');

        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        $total_tallos = 0;
        $total_inventario = 0;
        foreach ($resumen_variedades as $r) {
            $inventario = getTotalInventarioByVariedad($r['variedad']->id_variedad);
            $saldo = $inventario - $r['tallos'];
            $total_tallos += $r['tallos'];
            $total_inventario += $inventario;

            $row++;
            $col = 0;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $r['variedad']->nombre_planta);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $r['variedad']->nombre_variedad);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $r['tallos']);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $inventario);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $saldo);
        }
        $saldo_total = $total_inventario - $total_tallos;
        $row++;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TOTALES');
        $col++;
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_tallos);
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_inventario);
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $saldo_total);

        setTextCenterToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);
        setBorderToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);

        for ($i = 0; $i <= $col; $i++)
            $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);
    }

    public function listar_ordenes_trabajo(Request $request)
    {
        $listado = OrdenTrabajo::where('id_detalle_import_pedido', $request->det_ped)
            ->orderBy('id_detalle_import_pedido')
            ->orderBy('longitud')
            ->get();
        $despachadores = Despachador::where('estado', 1)
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.postcocecha.ingreso_clasificacion.forms.listar_ordenes_trabajo', [
            'listado' => $listado,
            'despachadores' => $despachadores,
        ]);
    }

    public function listar_pre_ordenes_trabajo(Request $request)
    {
        $listado = PreOrdenTrabajo::where('id_detalle_import_pedido', $request->det_ped)
            ->orderBy('id_detalle_import_pedido')
            ->orderBy('longitud')
            ->get();
        return view('adminlte.gestion.postcocecha.ingreso_clasificacion.forms.listar_pre_ordenes_trabajo', [
            'listado' => $listado,
        ]);
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
            bitacora('ORDEN_TRABAJO', $model->id_orden_trabajo, 'D', 'ELIMINAR OT desde PREPRODUCCION (' . $model->ramos . ' ramos)');
            $model->delete();

            foreach ($variedades as $var) {
                /* ACTUALIZAR TABLA RESUMEN_AGROGANA */
                jobUpdateResumenAgrogana::dispatch($model->fecha, $var)->onQueue('resumen_agrogana')->onConnection('database');

                /* TABLA RESUMEN_FECHAS */
                Artisan::call('resumen:fecha', [
                    'fecha' => $model->fecha,
                    'variedad' => $var,
                    'dev' => 1,
                ]);
            }
            jobUpdateVariedades::dispatch($model->fecha, 0)->onQueue('update_variedades')->onConnection('database');

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

    public function eliminar_pre_orden_trabajo(Request $request)
    {
        DB::beginTransaction();
        try {
            $variedades = [];
            $model = PreOrdenTrabajo::find($request->id);
            foreach ($model->detalles as $det) {
                $variedades[] = $det->id_variedad;
            }
            bitacora('PRE_ORDEN_TRABAJO', $model->id_pre_orden_trabajo, 'D', 'ELIMINAR PRE-OT desde PREPRODUCCION (' . $model->ramos . ' ramos)');
            $model->delete();

            foreach ($variedades as $var) {
                /* ACTUALIZAR TABLA RESUMEN_AGROGANA */
                jobUpdateResumenAgrogana::dispatch($model->fecha, $var)->onQueue('resumen_agrogana')->onConnection('database');

                /* TABLA RESUMEN_FECHAS */
                Artisan::call('resumen:fecha', [
                    'fecha' => $model->fecha,
                    'variedad' => $var,
                    'dev' => 1,
                ]);
            }
            jobUpdateVariedades::dispatch($model->fecha, 0)->onQueue('update_variedades')->onConnection('database');

            DB::commit();
            $success = true;
            $msg = 'Se ha <strong>ELIMINADO</strong> la Pre-OT correctamente';
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

    public function convertir_a_orden_trabajo(Request $request)
    {
        DB::beginTransaction();
        try {
            $variedades = [];
            $finca = getFincaActiva();
            $pre_ot = PreOrdenTrabajo::find($request->id);

            $orden = new OrdenTrabajo();
            $orden->id_detalle_import_pedido = $pre_ot->id_detalle_import_pedido;
            $orden->fecha = $request->fecha;
            $orden->longitud = $pre_ot->longitud;
            $orden->ramos = $pre_ot->ramos;
            $orden->id_empresa = $finca;
            $orden->save();
            $orden->id_orden_trabajo = DB::table('orden_trabajo')
                ->select(DB::raw('max(id_orden_trabajo) as id'))
                ->get()[0]->id;

            foreach ($pre_ot->detalles as $d) {
                // --------- REGISTRAR DETALLE de la ORDEN DE TRABAJO ------------ //
                $detalle = new DetalleOrdenTrabajo();
                $detalle->id_orden_trabajo = $orden->id_orden_trabajo;
                $detalle->id_variedad = $d->id_variedad;
                $detalle->tallos = $d->tallos * $pre_ot->ramos;
                $detalle->save();

                $variedades[] = $d->id_variedad;
            }
            $pre_ot->estado = 0;
            $pre_ot->id_orden_trabajo = $orden->id_orden_trabajo;
            $pre_ot->save();

            foreach ($variedades as $var) {
                /* ACTUALIZAR TABLA RESUMEN_AGROGANA */
                jobUpdateResumenAgrogana::dispatch($orden->fecha, $var)->onQueue('resumen_agrogana')->onConnection('database');

                /* TABLA RESUMEN_FECHAS */
                Artisan::call('resumen:fecha', [
                    'fecha' => $orden->fecha,
                    'variedad' => $var,
                    'dev' => 1,
                ]);
                Artisan::call('resumen:fecha', [
                    'fecha' => $pre_ot->fecha,
                    'variedad' => $var,
                    'dev' => 1,
                ]);
            }
            jobUpdateVariedades::dispatch($orden->fecha, 0)->onQueue('update_variedades')->onConnection('database');
            jobUpdateVariedades::dispatch($pre_ot->fecha, 0)->onQueue('update_variedades')->onConnection('database');
            bitacora('ORDEN_TRABAJO', $orden->id_orden_trabajo, 'I', 'CREAR (CONVERTIR desde PRE_OT) una OT desde PREPRODUCCION (' . $orden->ramos . ' ramos)');

            DB::commit();
            $success = true;
            $msg = 'Se ha <strong>CONVERTIDO en OT</strong> la Pre-OT correctamente';
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
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'PEDIDO');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'CLIENTE');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'FECHA');
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

        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        $detalle_pedido = $orden_trabajo->detalle_import_pedido;
        $pedido = $detalle_pedido->pedido;
        $total_tallos = 0;
        $total_tallos_ramo = 0;
        foreach ($orden_trabajo->detalles as $det) {
            $total_tallos += $det->tallos;
            $total_tallos_ramo += $det->tallos / $orden_trabajo->ramos;
        }

        foreach ($orden_trabajo->detalles as $pos_d => $det) {

            $row++;
            if ($pos_d == 0) {
                $col = 0;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, '#' . $orden_trabajo->id_orden_trabajo);
                $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($orden_trabajo->detalles) - 1));
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, '#' . $pedido->codigo);
                $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($orden_trabajo->detalles) - 1));
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $pedido->cliente->detalle()->nombre);
                $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($orden_trabajo->detalles) - 1));
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, convertDateToText($pedido->fecha));
                $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($orden_trabajo->detalles) - 1));
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $detalle_pedido->variedad->nombre);
                $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($orden_trabajo->detalles) - 1));
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $orden_trabajo->longitud);
                $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($orden_trabajo->detalles) - 1));
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $orden_trabajo->ramos);
                $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($orden_trabajo->detalles) - 1));
            }
            $col = 7;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $det->variedad->nombre);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $det->tallos);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $det->tallos / $orden_trabajo->ramos);
            if ($pos_d == 0) {
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $despachador != '' ? $despachador->nombre : '');
                $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($orden_trabajo->detalles) - 1));
            }
        }
        $row++;
        $col = 8;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_tallos);
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_tallos_ramo);
        $col = 10;
        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        setTextCenterToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);
        setBorderToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);

        for ($i = 0; $i <= $col; $i++)
            $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);
    }

    public function exportar_orden_trabajo_pdf(Request $request)
    {
        $barCode = new BarcodeGeneratorHTML();
        $model = OrdenTrabajo::find($request->id);
        $datos = [
            'model' => $model,
        ];
        return PDF::loadView('adminlte.gestion.postcocecha.ingreso_clasificacion.partials.pdf_orden_trabajo', compact('datos', 'barCode'))
            ->setPaper(array(0, 0, 700, 500), 'A4')->stream();
    }

    public function store_armar_ramos(Request $request)
    {
        try {
            foreach (json_decode($request->data) as $d) {
                DB::beginTransaction();
                $inventario = new InventarioFrio();
                $inventario->id_variedad = $d->variedad;
                $inventario->longitud = $d->longitud;
                $inventario->fecha = $request->fecha;
                $inventario->cantidad = $d->armar;
                $inventario->disponibles = $d->armar;
                $inventario->basura = 0;
                $inventario->disponibilidad = 1;
                $inventario->save();
                DB::commit();
            }
            jobUpdateVariedades::dispatch($request->fecha, 0)->onQueue('update_variedades')->onConnection('database');

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

    public function confirmar_pedido(Request $request)
    {
        try {
            DB::beginTransaction();
            $combinaciones = DB::table('detalle_import_pedido as d')
                ->join('import_pedido as p', 'p.id_import_pedido', '=', 'd.id_import_pedido')
                ->join('variedad as v', 'v.id_variedad', '=', 'd.id_variedad')
                ->select('d.id_variedad', 'd.longitud', 'v.nombre', 'd.id_detalle_import_pedido')->distinct()
                ->where('p.fecha', $request->fecha);
            if ($request->variedad != 'T')
                $combinaciones = $combinaciones->where('d.id_variedad', $request->variedad);
            $combinaciones = $combinaciones->get();

            $total_ventas = 0;
            foreach ($combinaciones as $item) {
                $venta = DB::table('import_pedido as p')
                    ->join('detalle_import_pedido as d', 'd.id_import_pedido', '=', 'p.id_import_pedido')
                    ->select(DB::raw('sum(d.ramos * d.caja) as cantidad'))
                    ->where('d.id_detalle_import_pedido', $item->id_detalle_import_pedido)
                    ->where('p.fecha', $request->fecha)
                    ->where('d.id_variedad', $item->id_variedad)
                    ->where('d.longitud', $item->longitud)
                    ->get()[0]->cantidad;
                $total_ventas += $venta;

                /*MARCAR COMO EJECUTADO LOS DETALLES_IMPORT_PEDIDO*/
                $pedidos = DB::table('detalle_import_pedido as d')
                    ->join('import_pedido as p', 'p.id_import_pedido', '=', 'd.id_import_pedido')
                    ->join('detalle_cliente as dc', 'dc.id_cliente', '=', 'p.id_cliente')
                    ->select(
                        'p.id_import_pedido',
                        'd.id_detalle_import_pedido',
                        'd.ejecutado',
                        'p.codigo',
                        'p.id_cliente',
                        'dc.nombre as nombre_cliente'
                    )->distinct()
                    ->where('d.id_detalle_import_pedido', $item->id_detalle_import_pedido)
                    ->where('p.fecha', $request->fecha)
                    ->where('d.id_variedad', $item->id_variedad)
                    ->where('d.longitud', $item->longitud)
                    ->where('dc.estado', 1)
                    ->orderBy('dc.nombre')
                    ->get();

                foreach ($pedidos as $ped) {
                    $model_detalle = DetalleImportPedido::find($item->id_detalle_import_pedido);
                    $model_detalle->ejecutado = 1;
                    $model_detalle->save();
                }

                /*MARCAR COMO ARMADO LAS ORDENES_TRABAJO*/
                $ordenes = OrdenTrabajo::where('id_detalle_import_pedido', $item->id_detalle_import_pedido)
                    ->get();

                foreach ($ordenes as $ord) {
                    $ord->armado = 1;
                    $ord->save();
                    bitacora('ORDEN_TRABAJO', $ord->id_orden_trabajo, 'U', 'MARCAR COMO ARMADO la OT desde PREPRODUCCION (' . $ord->ramos . ' ramos)');
                }
            }
            jobUpdateVariedades::dispatch($request->fecha, 0)->onQueue('update_variedades')->onConnection('database');

            /* RESTAR DEL INVENTARIO FRIO */
            $inventarios = InventarioFrio::where('estado', 1)
                ->where('disponibles', '>', 0);
            if ($request->variedad != 'T')
                $inventarios = $inventarios->where('id_variedad', $request->variedad);
            $inventarios = $inventarios->orderBy('fecha')
                ->get();

            $sacar = $total_ventas;
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

            $success = true;
            $msg = 'Se han <strong>DISTRIBUIDO</strong> los tallos correctamente';
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

    public function bloquear_distribucion(Request $request)
    {
        try {
            DB::beginTransaction();

            $detalle_pedido = DetalleImportPedido::find($request->detalle_pedido);
            $detalle_pedido->bloquear_distribucion = !$detalle_pedido->bloquear_distribucion;
            $detalle_pedido->save();

            $success = true;
            $msg = 'Se ha <strong>BLOQUEADO</strong> la distribucion correctamente';
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

    public function copiar_distribucion(Request $request)
    {
        try {
            DB::beginTransaction();
            if (isset($request->data)) {
                $distribucion = DistribucionReceta::where('id_detalle_import_pedido', $request->detalle_pedido)->get();
                $fecha = DetalleImportPedido::find($request->detalle_pedido)->pedido->fecha;
                $target_resumen = [];
                foreach ($request->data as $data) {
                    $distribuciones_del = DistribucionReceta::where('id_detalle_import_pedido', $data)->get();
                    foreach ($distribuciones_del as $dist) {
                        if (
                            !in_array([
                                'fecha' => $fecha,
                                'variedad' => $dist->id_variedad,
                            ], $target_resumen)
                        )
                            $target_resumen[] = [
                                'fecha' => $fecha,
                                'variedad' => $dist->id_variedad,
                            ];
                    }
                    DB::select('delete from distribucion_receta where id_detalle_import_pedido = ' . $data);

                    foreach ($distribucion as $dist) {
                        $model = new DistribucionReceta();
                        $model->id_detalle_import_pedido = $data;
                        $model->id_variedad = $dist->id_variedad;
                        $model->longitud = $dist->longitud;
                        $model->unidades = $dist->unidades;
                        $model->save();

                        if (
                            !in_array([
                                'fecha' => $fecha,
                                'variedad' => $dist->id_variedad,
                            ], $target_resumen)
                        )
                            $target_resumen[] = [
                                'fecha' => $fecha,
                                'variedad' => $dist->id_variedad,
                            ];
                    }
                }

                /* TABLA RESUMEN_FECHAS */
                foreach ($target_resumen as $t) {
                    Artisan::call('resumen:fecha', [
                        'fecha' => $t['fecha'],
                        'variedad' => $t['variedad'],
                        'dev' => 1,
                    ]);

                    /* ACTUALIZAR TABLA RESUMEN_AGROGANA */
                    jobUpdateResumenAgrogana::dispatch($t['fecha'], $t['variedad'])->onQueue('resumen_agrogana')->onConnection('database');
                    jobUpdateVariedades::dispatch($t['fecha'], $t['variedad'])->onQueue('update_variedades')->onConnection('database');
                }
            }

            $success = true;
            $msg = 'Se ha <strong>COPIADO</strong> la distribucion correctamente';
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

    public function confirmar_ramos(Request $request)
    {
        try {
            DB::beginTransaction();
            $model = DetalleImportPedido::find($request->id_det_ped);
            $model->ramos_armados += $request->armar;
            $model->save();

            $fecha = $model->pedido->fecha;

            /* TABLA RESUMEN_FECHAS */
            $distribucion = $model->detalles_receta;
            foreach ($distribucion as $dist) {
                Artisan::call('resumen:fecha', [
                    'fecha' => $fecha,
                    'variedad' => $dist->id_variedad,
                    'dev' => 1,
                ]);

                /* ACTUALIZAR TABLA RESUMEN_AGROGANA */
                jobUpdateResumenAgrogana::dispatch($fecha, $dist->id_variedad)->onQueue('resumen_agrogana')->onConnection('database');
            }
            jobUpdateVariedades::dispatch($fecha, 0)->onQueue('update_variedades')->onConnection('database');
            bitacora('DETALLE_IMPORT_PEDIDO', $model->id_detalle_import_pedido, 'U', 'ARMAR RAMOS MANUALMENTE desde PREPRODUCCION (' . $request->armar . ' ramos)');
            DB::commit();

            $success = true;
            $msg = 'Se han <strong>ARMADO</strong> los ramos correctamente';
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

    public function dividir_receta(Request $request)
    {
        $detalle_pedido = DetalleImportPedido::find($request->det_ped);

        $numeros_receta = DB::table('detalle_receta')
            ->select('numero_receta')->distinct()
            ->where('id_variedad', $detalle_pedido->id_variedad)
            ->get()->pluck('numero_receta')->toArray();
        return view('adminlte.gestion.postcocecha.ingreso_clasificacion.forms.dividir_receta', [
            'detalle_pedido' => $detalle_pedido,
            'numeros_receta' => $numeros_receta,
            'plantas' => Planta::where('estado', '=', 1)->orderBy('nombre')->get(),
        ]);
    }

    public function store_dividir_receta(Request $request)
    {
        DB::beginTransaction();
        try {
            $variedades = [];
            $det_ped = DetalleImportPedido::find($request->id_detalle_pedido);
            $pre_ot = new PreOrdenTrabajo();
            $pre_ot->id_detalle_import_pedido = $request->id_detalle_pedido;
            $pre_ot->fecha = $request->fecha;
            $pre_ot->longitud = $det_ped->longitud;
            $pre_ot->ramos = $request->ramos;
            $pre_ot->save();
            $pre_ot->id_pre_orden_trabajo = DB::table('pre_orden_trabajo')
                ->select(DB::raw('max(id_pre_orden_trabajo) as id'))
                ->get()[0]->id;

            foreach (json_decode($request->data) as $d) {
                $det_ot = new DetallePreOrdenTrabajo();
                $det_ot->id_pre_orden_trabajo = $pre_ot->id_pre_orden_trabajo;
                $det_ot->id_variedad = $d->id_item;
                $det_ot->tallos = $d->unidades;
                $det_ot->save();

                $variedades[] = $d->id_item;
            }

            foreach ($variedades as $var) {
                /* ACTUALIZAR TABLA RESUMEN_AGROGANA */
                jobUpdateResumenAgrogana::dispatch($request->fecha, $var)->onQueue('resumen_agrogana')->onConnection('database');

                /* TABLA RESUMEN_FECHAS */
                Artisan::call('resumen:fecha', [
                    'fecha' => $request->fecha,
                    'variedad' => $var,
                    'dev' => 1,
                ]);
            }
            jobUpdateVariedades::dispatch($request->fecha, 0)->onQueue('update_variedades')->onConnection('database');
            bitacora('PRE_ORDEN_TRABAJO', $pre_ot->id_pre_orden_trabajo, 'I', 'CREAR una PRE-OT desde PREPRODUCCION (' . $pre_ot->ramos . ' ramos)');

            DB::commit();
            $success = true;
            $msg = 'Se ha <strong>DIVIDIDO</strong> la receta correctamente';
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

    public function editar_preorden(Request $request)
    {
        $pre_orden = PreOrdenTrabajo::find($request->id);
        return view('adminlte.gestion.postcocecha.ingreso_clasificacion.forms.editar_preorden', [
            'pre_orden' => $pre_orden,
            'plantas' => Planta::where('estado', '=', 1)->orderBy('nombre')->get(),
        ]);
    }

    public function store_distribucion_pre_orden(Request $request)
    {
        DB::beginTransaction();
        try {
            $pre_orden = PreOrdenTrabajo::find($request->id_pre_orden);
            $variedades = [];

            $delete = DetallePreOrdenTrabajo::where('id_pre_orden_trabajo', $request->id_pre_orden)
                ->get();
            foreach ($delete as $del) {
                $variedades[] = $del->id_variedad;  // guardar en este listado los ids de variedades para actualizar la tabla resumen_agrogana
                $del->delete();
            }

            foreach (json_decode($request->data) as $d) {
                $model = new DetallePreOrdenTrabajo();
                $model->id_pre_orden_trabajo = $request->id_pre_orden;
                $model->id_variedad = $d->id_item;
                $model->tallos = $d->unidades;
                $model->save();

                if (!in_array($d->id_item, $variedades))
                    $variedades[] = $d->id_item;  // guardar en este listado los ids de variedades para actualizar la tabla resumen_agrogana
            }

            foreach ($variedades as $var) {
                /* ACTUALIZAR TABLA RESUMEN_AGROGANA */
                jobUpdateResumenAgrogana::dispatch($pre_orden->fecha, $var)->onQueue('resumen_agrogana')->onConnection('database');

                /* TABLA RESUMEN_FECHAS */
                Artisan::call('resumen:fecha', [
                    'fecha' => $pre_orden->fecha,
                    'variedad' => $var,
                    'dev' => 1,
                ]);
            }
            jobUpdateVariedades::dispatch($pre_orden->fecha, 0)->onQueue('update_variedades')->onConnection('database');
            bitacora('PRE_ORDEN_TRABAJO', $pre_orden->id_pre_orden_trabajo, 'U', 'MODIFICAR LA DISTRIBUCION de la PRE-OT desde PREPRODUCCION (' . $pre_orden->ramos . ' ramos)');

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

    public function convertir_parcial(Request $request)
    {
        DB::beginTransaction();
        try {
            $variedades = [];
            $finca = getFincaActiva();
            $pre_ot = PreOrdenTrabajo::find($request->id);

            $orden = new OrdenTrabajo();
            $orden->id_detalle_import_pedido = $pre_ot->id_detalle_import_pedido;
            $orden->fecha = $pre_ot->fecha;
            $orden->longitud = $pre_ot->longitud;
            $orden->ramos = $request->ramos;
            $orden->id_empresa = $finca;
            $orden->save();
            $orden->id_orden_trabajo = DB::table('orden_trabajo')
                ->select(DB::raw('max(id_orden_trabajo) as id'))
                ->get()[0]->id;

            foreach ($pre_ot->detalles as $d) {
                // --------- REGISTRAR DETALLE de la ORDEN DE TRABAJO ------------ //
                $detalle = new DetalleOrdenTrabajo();
                $detalle->id_orden_trabajo = $orden->id_orden_trabajo;
                $detalle->id_variedad = $d->id_variedad;
                $detalle->tallos = $d->tallos * $pre_ot->ramos;
                $detalle->save();

                $variedades[] = $d->id_variedad;
            }

            $parcial = new PreOrdenParcial();
            $parcial->id_pre_orden_trabajo = $pre_ot->id_pre_orden_trabajo;
            $parcial->id_orden_trabajo = $orden->id_orden_trabajo;
            $parcial->ramos = $request->ramos;
            $parcial->save();

            foreach ($variedades as $var) {
                /* ACTUALIZAR TABLA RESUMEN_AGROGANA */
                jobUpdateResumenAgrogana::dispatch($orden->fecha, $var)->onQueue('resumen_agrogana')->onConnection('database');

                /* TABLA RESUMEN_FECHAS */
                Artisan::call('resumen:fecha', [
                    'fecha' => $orden->fecha,
                    'variedad' => $var,
                    'dev' => 1,
                ]);
                Artisan::call('resumen:fecha', [
                    'fecha' => $pre_ot->fecha,
                    'variedad' => $var,
                    'dev' => 1,
                ]);
            }
            jobUpdateVariedades::dispatch($orden->fecha, 0)->onQueue('update_variedades')->onConnection('database');
            jobUpdateVariedades::dispatch($pre_ot->fecha, 0)->onQueue('update_variedades')->onConnection('database');
            bitacora('ORDEN_TRABAJO', $orden->id_orden_trabajo, 'I', 'CREAR (CONVERTIR PARCIAL desde PRE_OT) una OT desde PREPRODUCCION (' . $orden->ramos . ' ramos)');

            DB::commit();
            $success = true;
            $msg = 'Se ha <strong>CONVERTIDO en OT</strong> la Pre-OT correctamente';
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
