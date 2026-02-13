<?php

namespace yura\Http\Controllers\Postcosecha;

use DB;
use Illuminate\Http\Request;
use yura\Http\Controllers\Controller;
use yura\Modelos\DetalleImportPedido;
use yura\Modelos\Planta;
use yura\Modelos\Submenu;
use yura\Modelos\Variedad;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PlanificacionController extends Controller
{
    public function inicio(Request $request)
    {
        $variedades = Variedad::where('estado', 1)
            ->where('receta', 1)
            ->orderBy('nombre')
            ->get();
        $flores = Variedad::where('estado', 1)
            ->where('receta', 0)
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.postcocecha.planificacion.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'variedades' => $variedades,
            'flores' => $flores,
        ]);
    }

    public function listar_reporte(Request $request)
    {
        $fechas = DB::table('detalle_import_pedido as d')
            ->join('import_pedido as p', 'p.id_import_pedido', '=', 'd.id_import_pedido')
            ->select('p.fecha')->distinct()
            ->where('p.estado', 1)
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
            if ($request->flor == '') {
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

                foreach ($combinaciones as $item) {
                    $valores = [];
                    $bloqueado = [];
                    foreach ($fechas as $fecha) {
                        $venta = DB::table('import_pedido as p')
                            ->join('detalle_import_pedido as d', 'd.id_import_pedido', '=', 'p.id_import_pedido')
                            ->select(DB::raw('sum(d.ramos * d.caja) as cantidad'))
                            ->where('p.fecha', $fecha)
                            ->where('d.id_variedad', $item->id_variedad)
                            ->where('d.longitud', $item->longitud)
                            ->where('p.estado', 1)
                            ->get()[0]->cantidad;
                        $query_bloqueo = DB::table('import_pedido as p')
                            ->join('detalle_import_pedido as d', 'd.id_import_pedido', '=', 'p.id_import_pedido')
                            ->select('d.bloquear_distribucion')->distinct()
                            ->where('p.fecha', $fecha)
                            ->where('d.id_variedad', $item->id_variedad)
                            ->where('d.longitud', $item->longitud)
                            ->where('p.estado', 1)
                            ->get();
                        $bloqueo = true;
                        foreach ($query_bloqueo as $b) {
                            if ($b->bloquear_distribucion == 0)
                                $bloqueo = false;
                        }

                        $valores[] = $venta;
                        $bloqueado[] = $bloqueo;
                    }
                    $listado[] = [
                        'item' => $item,
                        'valores' => $valores,
                        'bloqueado' => $bloqueado,
                    ];
                }
                return view('adminlte.gestion.postcocecha.planificacion.partials.listado', [
                    'listado' => $listado,
                    'fechas' => $fechas,
                ]);
            } else {
                $combinaciones = DB::table('detalle_import_pedido as d')
                    ->join('import_pedido as p', 'p.id_import_pedido', '=', 'd.id_import_pedido')
                    ->join('distribucion_receta as dist', 'dist.id_detalle_import_pedido', '=', 'd.id_detalle_import_pedido')
                    ->join('variedad as v', 'v.id_variedad', '=', 'dist.id_variedad')
                    ->select('dist.id_variedad', 'v.nombre', 'v.siglas')->distinct()
                    ->where('p.estado', 1)
                    ->where('p.fecha', '>=', $fecha_ini)
                    ->where('p.fecha', '<=', $fecha_fin);
                if ($request->flor != 'T')
                    $combinaciones = $combinaciones->where('dist.id_variedad', $request->flor);
                $combinaciones = $combinaciones->get();

                foreach ($combinaciones as $pos => $item) {
                    $valores = [];
                    foreach ($fechas as $fecha) {
                        $venta = DB::table('resumen_fechas')
                            ->select(DB::raw('sum(tallos_venta) as cantidad'))
                            ->where('fecha', $fecha)
                            ->where('id_variedad', $item->id_variedad)
                            ->get()[0]->cantidad;

                        $valores[] = $venta;
                    }
                    $listado[] = [
                        'item' => $item,
                        'valores' => $valores,
                    ];
                }
                return view('adminlte.gestion.postcocecha.planificacion.partials.listado_flor', [
                    'listado' => $listado,
                    'fechas' => $fechas,
                ]);
            }
        }
    }

    public function modal_planificacion(Request $request)
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
            $ramos_pre_ot = DB::table('pre_orden_trabajo')
                ->select(
                    DB::raw('sum(ramos) as ramos')
                )
                ->where('id_detalle_import_pedido', $p->id_detalle_import_pedido)
                ->where('longitud', $request->longitud)
                ->where('estado', 1)
                ->get()[0];
            $listado[] = [
                'pedido' => $p,
                'ramos_venta' => $ramos_venta,
                'distribucion' => $distribucion,
                'ramos_pre_ot' => $ramos_pre_ot->ramos,
            ];
        }
        return view('adminlte.gestion.postcocecha.planificacion.forms.modal_planificacion', [
            'listado' => $listado,
            'variedad' => Variedad::find($request->variedad),
            'fecha' => $request->fecha,
            'longitud' => $request->longitud,
            'pos' => $request->pos,
        ]);
    }

    public function admin_receta(Request $request)
    {
        $detalle_pedido = DetalleImportPedido::find($request->det_ped);

        $numeros_receta = DB::table('detalle_receta')
            ->select('numero_receta')->distinct()
            ->where('id_variedad', $detalle_pedido->id_variedad)
            ->get()->pluck('numero_receta')->toArray();
        return view('adminlte.gestion.postcocecha.planificacion.forms.admin_receta', [
            'detalle_pedido' => $detalle_pedido,
            'numeros_receta' => $numeros_receta,
            'plantas' => Planta::where('estado', '=', 1)->orderBy('nombre')->get(),
        ]);
    }

    public function exportar_receta(Request $request)
    {
        $spread = new Spreadsheet();
        $this->excel_reporte_receta($spread, $request);
        $fileName = "Planificacion.xlsx";
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
                'distribucion' => $distribucion
            ];
        }
        $variedad = Variedad::find($request->variedad);

        $columnas = getColumnasExcel();
        $sheet = $spread->getActiveSheet();
        $sheet->setTitle('Planificacion');

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

        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        $total_ramos = 0;
        $total_tallos = 0;
        $resumen_variedades = [];
        foreach ($listado as $pos_p => $item) {
            $total_ramos += $item['ramos_venta'];
            $row_ini = $row + 1;
            foreach ($item['distribucion'] as $pos_d => $dist) {
                $total_tallos += $dist->unidades * $item['ramos_venta'];

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
            }
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
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Tallos');

        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        $total_tallos = 0;
        foreach ($resumen_variedades as $r) {
            $total_tallos += $r['tallos'];

            $row++;
            $col = 0;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $r['variedad']->nombre_planta);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $r['variedad']->nombre_variedad);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $r['tallos']);
        }
        $row++;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TOTALES');
        $col++;
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_tallos);

        setTextCenterToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);
        setBorderToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);

        for ($i = 0; $i <= $col; $i++)
            $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);
    }

    public function exportar_excel_fecha(Request $request)
    {
        $spread = new Spreadsheet();
        $this->excel_fecha($spread, $request);
        $fileName = "Planificacion.xlsx";
        $writer = new Xlsx($spread);

        //--------------------------- GUARDAR EL EXCEL -----------------------

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        $writer->save('php://output');

        //$writer->save('/var/www/html/Dasalflor/storage/storage/excel/excel_prueba.xlsx');
    }

    public function excel_fecha($spread, $request)
    {
        $finca = getFincaActiva();
        $pedidos = DB::table('detalle_import_pedido as d')
            ->join('import_pedido as p', 'p.id_import_pedido', '=', 'd.id_import_pedido')
            ->join('detalle_cliente as dc', 'dc.id_cliente', '=', 'p.id_cliente')
            ->join('variedad as v', 'v.id_variedad', '=', 'd.id_variedad')
            ->select(
                'p.id_import_pedido',
                'd.id_detalle_import_pedido',
                'd.bloquear_distribucion',
                'd.ramos_armados',
                'd.ejecutado',
                'p.codigo',
                'p.codigo_ref',
                'p.id_cliente',
                'dc.nombre as nombre_cliente',
                'v.nombre as nombre_variedad',
                'd.longitud',
            )->distinct()
            ->where('p.fecha', $request->fecha)
            ->where('p.id_empresa', $finca)
            ->where('dc.estado', 1)
            ->where('p.estado', 1)
            ->orderBy('v.nombre')
            ->orderBy('dc.nombre')
            ->get();

        $listado = [];
        foreach ($pedidos as $p) {
            $ramos_venta = DB::table('import_pedido as p')
                ->join('detalle_import_pedido as d', 'd.id_import_pedido', '=', 'p.id_import_pedido')
                ->select(DB::raw('sum(d.ramos * d.caja) as cantidad'))
                ->where('d.id_import_pedido', $p->id_import_pedido)
                ->where('d.id_detalle_import_pedido', $p->id_detalle_import_pedido)
                /*->where('d.id_variedad', $request->variedad)
                ->where('d.longitud', $request->longitud)*/
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
                ->where('d.id_import_pedido', $p->id_import_pedido)
                ->where('d.id_detalle_import_pedido', $p->id_detalle_import_pedido)
                /*->where('d.id_variedad', $request->variedad)
                ->where('d.longitud', $request->longitud)*/
                ->orderBy('pta.nombre')
                ->orderBy('v.nombre')
                ->get();
            $listado[] = [
                'pedido' => $p,
                'ramos_venta' => $ramos_venta,
                'distribucion' => $distribucion
            ];
        }

        $columnas = getColumnasExcel();
        $sheet = $spread->getActiveSheet();
        $sheet->setTitle($request->fecha);

        $row = 1;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'RECETA/LONGITUD');
        $col++;
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

        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        $total_ramos = 0;
        $total_tallos = 0;
        $resumen_variedades = [];
        foreach ($listado as $pos_p => $item) {
            $total_ramos += $item['ramos_venta'];
            $row_ini = $row + 1;
            foreach ($item['distribucion'] as $pos_d => $dist) {
                $total_tallos += $dist->unidades * $item['ramos_venta'];

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
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item['pedido']->nombre_variedad . ' ' . $item['pedido']->longitud . 'cm');
                    $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($item['distribucion']) - 1));
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, '#' . $item['pedido']->codigo);
                    $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($item['distribucion']) - 1));
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item['pedido']->nombre_cliente);
                    $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($item['distribucion']) - 1));
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item['ramos_venta']);
                    $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($item['distribucion']) - 1));
                }
                $col = 4;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $dist->nombre_planta);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $dist->nombre_variedad);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $dist->unidades);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $dist->unidades * $item['ramos_venta']);
            }
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
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Tallos');

        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        $total_tallos = 0;
        foreach ($resumen_variedades as $r) {
            $total_tallos += $r['tallos'];

            $row++;
            $col = 0;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $r['variedad']->nombre_planta);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $r['variedad']->nombre_variedad);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $r['tallos']);
        }
        $row++;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TOTALES');
        $col++;
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_tallos);

        setTextCenterToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);
        setBorderToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);

        for ($i = 0; $i <= $col; $i++)
            $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);
    }

    public function exportar_excel_total(Request $request)
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
        $finca = getFincaActiva();
        $fechas = DB::table('detalle_import_pedido as d')
            ->join('import_pedido as p', 'p.id_import_pedido', '=', 'd.id_import_pedido')
            ->select('p.fecha')->distinct()
            ->where('p.estado', 1)
            ->where('p.id_empresa', $finca)
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
            $combinaciones = DB::table('detalle_import_pedido as d')
                ->join('import_pedido as p', 'p.id_import_pedido', '=', 'd.id_import_pedido')
                ->join('variedad as v', 'v.id_variedad', '=', 'd.id_variedad')
                ->select('d.id_variedad', 'd.longitud', 'v.nombre', 'v.siglas')->distinct()
                ->where('p.estado', 1)
                ->where('p.id_empresa', $finca)
                ->where('p.fecha', '>=', $fecha_ini)
                ->where('p.fecha', '<=', $fecha_fin);
            if ($request->variedad != 'T')
                $combinaciones = $combinaciones->where('d.id_variedad', $request->variedad);
            $combinaciones = $combinaciones->get();

            foreach ($combinaciones as $item) {
                $valores = [];
                foreach ($fechas as $fecha) {
                    $venta = DB::table('import_pedido as p')
                        ->join('detalle_import_pedido as d', 'd.id_import_pedido', '=', 'p.id_import_pedido')
                        ->select(DB::raw('sum(d.ramos * d.caja) as cantidad'))
                        ->where('p.id_empresa', $finca)
                        ->where('p.fecha', $fecha)
                        ->where('d.id_variedad', $item->id_variedad)
                        ->where('d.longitud', $item->longitud)
                        ->where('p.estado', 1)
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
            $combinaciones = DB::table('detalle_import_pedido as d')
                ->join('import_pedido as p', 'p.id_import_pedido', '=', 'd.id_import_pedido')
                ->join('distribucion_receta as r', 'r.id_detalle_import_pedido', '=', 'd.id_detalle_import_pedido')
                ->join('variedad as v', 'v.id_variedad', '=', 'r.id_variedad')
                ->join('planta as pta', 'pta.id_planta', '=', 'v.id_planta')
                ->select(
                    'r.id_variedad',
                    'v.nombre as nombre_variedad',
                    'v.id_planta',
                    'pta.nombre as nombre_planta'
                )->distinct()
                ->where('p.fecha', '>=', $fecha_ini)
                ->where('p.fecha', '<=', $fecha_fin)
                ->where('p.id_empresa', $finca)
                ->where('p.estado', 1)
                ->orderBy('pta.nombre')
                ->orderBy('v.nombre')
                ->get();

            foreach ($combinaciones as $item) {
                $valores = [];
                foreach ($fechas as $fecha) {
                    $venta = DB::table('import_pedido as p')
                        ->join('detalle_import_pedido as d', 'd.id_import_pedido', '=', 'p.id_import_pedido')
                        ->join('distribucion_receta as r', 'r.id_detalle_import_pedido', '=', 'd.id_detalle_import_pedido')
                        ->select(DB::raw('sum(d.ramos * d.caja * r.unidades) as cantidad'))
                        ->where('p.id_empresa', $finca)
                        ->where('p.fecha', $fecha)
                        ->where('r.id_variedad', $item->id_variedad)
                        ->where('p.estado', 1)
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

        /* HOJA 3 */
        $listado = [];
        if (count($fechas) > 0) {
            $variedades = DB::table('detalle_pre_orden_trabajo as d')
                ->join('pre_orden_trabajo as p', 'p.id_pre_orden_trabajo', '=', 'd.id_pre_orden_trabajo')
                ->join('variedad as v', 'v.id_variedad', '=', 'd.id_variedad')
                ->join('planta as pta', 'pta.id_planta', '=', 'v.id_planta')
                ->join('detalle_import_pedido as det', 'det.id_detalle_import_pedido', '=', 'p.id_detalle_import_pedido')
                ->join('import_pedido as ped', 'ped.id_import_pedido', '=', 'det.id_import_pedido')
                ->select('d.id_variedad', 'v.nombre as nombre_variedad', 'pta.nombre as nombre_planta')->distinct()
                ->whereIn('ped.fecha', $fechas)
                ->orderBy('v.nombre')
                ->get();
            foreach ($variedades as $var) {
                $valores = [];
                foreach ($fechas as $f) {
                    $cantidad = DB::table('detalle_pre_orden_trabajo as d')
                        ->join('pre_orden_trabajo as p', 'p.id_pre_orden_trabajo', '=', 'd.id_pre_orden_trabajo')
                        ->join('variedad as v', 'v.id_variedad', '=', 'd.id_variedad')
                        ->join('detalle_import_pedido as det', 'det.id_detalle_import_pedido', '=', 'p.id_detalle_import_pedido')
                        ->join('import_pedido as ped', 'ped.id_import_pedido', '=', 'det.id_import_pedido')
                        ->select(DB::raw('sum(p.ramos * d.tallos) as cantidad'))
                        ->where('ped.fecha', $f)
                        ->where('d.id_variedad', $var->id_variedad)
                        ->get()[0]->cantidad;
                    $valores[] = $cantidad;
                }
                $listado[] = [
                    'item' => $var,
                    'valores' => $valores,
                ];
            }
        }

        $sheet = $spread->createSheet();
        $sheet->setTitle('Pre-OT');

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

    public function detalle_ventas(Request $request)
    {
        $detalle_pedidos = DB::table('detalle_import_pedido as d')
            ->join('import_pedido as ped', 'ped.id_import_pedido', '=', 'd.id_import_pedido')
            ->join('distribucion_receta as r', 'r.id_detalle_import_pedido', '=', 'd.id_detalle_import_pedido')
            ->select(
                'd.id_detalle_import_pedido',
            )->distinct()
            ->where('ped.estado', 1)
            ->where('ped.fecha', '>=', $request->desde)
            ->where('ped.fecha', '<=', $request->hasta)
            ->where('r.id_variedad', $request->variedad)
            ->orderBy('ped.fecha')
            ->get();
        $ids_detalle_pedidos = $detalle_pedidos->pluck('id_detalle_import_pedido')->toArray();
        $detalle_pedidos_ot = DB::table('detalle_import_pedido as d')
            ->join('import_pedido as ped', 'ped.id_import_pedido', '=', 'd.id_import_pedido')
            ->join('orden_trabajo as o', 'o.id_detalle_import_pedido', '=', 'd.id_detalle_import_pedido')
            ->join('detalle_orden_trabajo as do', 'do.id_orden_trabajo', '=', 'o.id_orden_trabajo')
            ->select(
                'd.id_detalle_import_pedido',
            )->distinct()
            ->where('ped.estado', 1)
            ->where('ped.fecha', '>=', $request->desde)
            ->where('ped.fecha', '<=', $request->hasta)
            ->where('do.id_variedad', $request->variedad)
            ->whereNotIn('d.id_detalle_import_pedido', $ids_detalle_pedidos)
            ->orderBy('ped.fecha')
            ->get();
        $ids_detalle_pedidos_ot = $detalle_pedidos_ot->pluck('id_detalle_import_pedido')->toArray();
        $detalle_pedidos_pre_ot = DB::table('detalle_import_pedido as d')
            ->join('import_pedido as ped', 'ped.id_import_pedido', '=', 'd.id_import_pedido')
            ->join('pre_orden_trabajo as po', 'po.id_detalle_import_pedido', '=', 'd.id_detalle_import_pedido')
            ->join('detalle_pre_orden_trabajo as pdo', 'pdo.id_pre_orden_trabajo', '=', 'po.id_pre_orden_trabajo')
            ->select(
                'd.id_detalle_import_pedido',
            )->distinct()
            ->where('po.estado', 1)
            ->where('ped.estado', 1)
            ->where('ped.fecha', '>=', $request->desde)
            ->where('ped.fecha', '<=', $request->hasta)
            ->where('pdo.id_variedad', $request->variedad)
            ->whereNotIn('d.id_detalle_import_pedido', $ids_detalle_pedidos)
            ->whereNotIn('d.id_detalle_import_pedido', $ids_detalle_pedidos_ot)
            ->orderBy('ped.fecha')
            ->get();
        $detalle_pedidos = $detalle_pedidos->merge($detalle_pedidos_ot)->merge($detalle_pedidos_pre_ot);

        $listado = [];
        foreach ($detalle_pedidos as $det_ped) {
            $det_ped = DetalleImportPedido::find($det_ped->id_detalle_import_pedido);
            $pedido = $det_ped->pedido;
            $tallos_venta = 0;
            $ramos_venta = $det_ped->ramos; // ramos totales del pedido
            $ramos_procesados = 0;  // ramos procesados mediante OT y Pre-OT

            $tallos_ot = 0;
            $query_ot = DB::table('orden_trabajo as o')
                ->select(
                    'o.ramos',
                )
                ->where('o.id_detalle_import_pedido', $det_ped->id_detalle_import_pedido)
                ->get();
            $list_ot = DB::table('orden_trabajo as o')
                ->join('detalle_orden_trabajo as do', 'do.id_orden_trabajo', '=', 'o.id_orden_trabajo')
                ->select(
                    'o.id_orden_trabajo',
                    'o.ramos',
                    DB::raw('sum(do.tallos) as tallos'),
                )
                ->where('o.id_detalle_import_pedido', $det_ped->id_detalle_import_pedido)
                ->where('do.id_variedad', $request->variedad)
                ->groupBy(
                    'o.id_orden_trabajo',
                    'o.ramos'
                )->get();
            foreach ($list_ot as $ot) {
                $tallos_venta += $ot->tallos;
                $tallos_ot += $ot->tallos;
            }
            foreach ($query_ot as $ot) {
                $ramos_procesados += $ot->ramos;
            }

            $tallos_pre_ot = 0;
            $list_pre_ot = DB::table('pre_orden_trabajo as o')
                ->join('detalle_pre_orden_trabajo as do', 'do.id_pre_orden_trabajo', '=', 'o.id_pre_orden_trabajo')
                ->select(
                    'o.id_pre_orden_trabajo',
                    'o.ramos',
                    DB::raw('sum(o.ramos * do.tallos) as tallos'),
                )
                ->where('o.estado', 1)
                ->where('do.id_variedad', $request->variedad)
                ->where('o.id_detalle_import_pedido', $det_ped->id_detalle_import_pedido)
                ->groupBy(
                    'o.id_pre_orden_trabajo',
                    'o.ramos'
                )
                ->get();
            $query_pre_ot = DB::table('pre_orden_trabajo as o')
                ->select(
                    'o.ramos',
                )
                ->where('o.estado', 1)
                ->where('o.id_detalle_import_pedido', $det_ped->id_detalle_import_pedido)
                ->get();
            foreach ($list_pre_ot as $pre_ot) {
                $tallos_venta += $pre_ot->tallos;
                $tallos_pre_ot += $pre_ot->tallos;
            }
            foreach ($query_pre_ot as $ot) {
                $ramos_procesados += $ot->ramos;
            }

            $tallos_restantes = 0;
            if ($ramos_venta > $ramos_procesados) {   // aun quedan ramos del pedido por procesar
                $diferencia = $ramos_venta - $ramos_procesados;
                $unidades = DB::table('detalle_import_pedido as d')
                    ->join('distribucion_receta as r', 'r.id_detalle_import_pedido', '=', 'd.id_detalle_import_pedido')
                    ->select(DB::raw('sum(r.unidades) as cantidad'))
                    ->where('r.id_variedad', $request->variedad)
                    ->where('d.id_detalle_import_pedido', $det_ped->id_detalle_import_pedido)
                    ->get()[0]->cantidad;
                $tallos_venta += $diferencia * $unidades;
                $tallos_restantes = $diferencia * $unidades;
            }

            $listado[] = [
                'pedido' => $pedido,
                'det_ped' => $det_ped,
                'tallos_venta' => $tallos_venta,
                'tallos_ot' => $tallos_ot,
                'tallos_pre_ot' => $tallos_pre_ot,
                'tallos_restantes' => $tallos_restantes,
            ];
        }
        return view('adminlte.gestion.postcocecha.planificacion.partials.detalle_ventas', [
            'desde' => $request->desde,
            'hasta' => $request->hasta,
            'listado' => $listado,
            'variedad' => Variedad::find($request->variedad)
        ]);
    }
}
