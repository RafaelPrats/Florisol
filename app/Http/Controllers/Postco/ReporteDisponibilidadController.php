<?php

namespace yura\Http\Controllers\Postco;

use Illuminate\Http\Request;
use yura\Http\Controllers\Controller;
use yura\Modelos\Planta;
use yura\Modelos\Submenu;
use yura\Modelos\Variedad;
use yura\Modelos\Postco;
use DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReporteDisponibilidadController extends Controller
{
    public function inicio(Request $request)
    {
        $plantas = Planta::where('estado', 1)
            ->where('id_planta', '!=', 151)
            ->where('id_planta', '!=', 128)
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.postco.reporte_disponibilidad.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'plantas' => $plantas,
        ]);
    }

    public function listar_reporte(Request $request)
    {
        $desde = hoy();
        if ($request->variedad != '') {
            $variedad = Variedad::find($request->variedad);
            $compras = DB::table('desglose_compra_flor')
                ->select(
                    DB::raw('sum(cantidad_mallas * tallos_x_malla) as cantidad'),
                    'fecha'
                )
                ->where('fecha', '>', $desde)
                ->where('estado', 1)
                ->where('id_variedad', $request->variedad)
                ->groupBy('fecha')
                ->orderBy('fecha', 'asc')
                ->get();
            $recepciones = DB::table('desglose_recepcion')
                ->select(
                    DB::raw('sum(disponibles) as cantidad'),
                    'fecha'
                )
                ->where('fecha', '<=', $desde)
                ->where('estado', 1)
                ->where('disponibles', '>', 0)
                ->where('id_variedad', $request->variedad)
                ->groupBy('fecha')
                ->orderBy('fecha', 'asc')
                ->get();

            $fechas = [];
            $f = $desde;
            while ($f <= $request->hasta) {
                $fechas[] = $f;
                $f = opDiasFecha('+', 1, $f);
            }
            $valores_postco = [];
            foreach ($fechas as $f) {
                $venta = getTallosVentaByVariedad($variedad->id_variedad, $f);

                $armados = DB::table('ot_postco as o')
                    ->join('postco as p', 'p.id_postco', '=', 'o.id_postco')
                    ->join('detalle_ot_postco as do', 'do.id_ot_postco', '=', 'o.id_ot_postco')
                    ->select(
                        DB::raw('sum(do.unidades * o.ramos) as cantidad'),
                    )
                    ->where('do.id_item', $variedad->id_variedad)
                    ->where('o.estado', 'A')
                    ->where('p.fecha', $f)
                    ->get()[0]->cantidad;
                $armados += DB::table('postco as p')
                    ->join('armado_postco as a', 'a.id_postco', '=', 'p.id_postco')
                    ->join('detalle_armado_postco as r', 'r.id_armado_postco', '=', 'a.id_armado_postco')
                    ->select(
                        DB::raw('sum(a.ramos * r.unidades) as cantidad'),
                    )
                    ->where('r.id_item', $variedad->id_variedad)
                    ->where('p.fecha', $f)
                    ->get()[0]->cantidad;

                $valores_postco[] = [
                    'venta' => $venta,
                    'armados' => $armados,
                    'fecha' => $f,
                ];
            }
            $valores_recepciones = $recepciones->pluck('cantidad')->toArray();
            $valores_compras = $compras->pluck('cantidad')->toArray();
            return view('adminlte.gestion.postco.reporte_disponibilidad.partials.listado_variedad', [
                'variedad' => $variedad,
                'compras' => $compras,
                'recepciones' => $recepciones,
                'valores_recepciones' => $valores_recepciones,
                'valores_compras' => $valores_compras,
                'valores_postco' => $valores_postco,
                'desde' => $desde,
            ]);
        } else {
            $combinaciones_compra_flor = DB::table('desglose_compra_flor as i')
                ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
                ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                ->select(
                    'i.id_variedad',
                    'v.id_planta',
                    'v.nombre as variedad_nombre',
                    'p.nombre as planta_nombre',
                    'v.dias_rotacion_recepcion',
                )->distinct()
                ->where('fecha', '>', $desde);
            if ($request->planta != '')
                $combinaciones_compra_flor = $combinaciones_compra_flor->where('v.id_planta', $request->planta);
            $combinaciones_compra_flor = $combinaciones_compra_flor->orderBy('p.nombre')
                ->orderBy('v.nombre')
                ->get();
            $ids_variedad_compra_flor = $combinaciones_compra_flor->pluck('id_variedad')->toArray();
            $combinaciones_recepcion = DB::table('desglose_recepcion as i')
                ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
                ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                ->select(
                    'i.id_variedad',
                    'v.id_planta',
                    'v.nombre as variedad_nombre',
                    'p.nombre as planta_nombre',
                    'v.dias_rotacion_recepcion',
                )->distinct()
                ->where('i.disponibles', '>', 0)
                ->where('i.estado', 1)
                ->whereNotIn('i.id_variedad', $ids_variedad_compra_flor);
            if ($request->planta != '')
                $combinaciones_recepcion = $combinaciones_recepcion->where('v.id_planta', $request->planta);
            $combinaciones_recepcion = $combinaciones_recepcion->orderBy('p.nombre')
                ->orderBy('v.nombre')
                ->get();
            $ids_variedad_recepcion = $combinaciones_recepcion->pluck('id_variedad')->toArray();
            $combinaciones_pedido = DB::table('postco as h')
                ->join('distribucion_postco as r', 'r.id_postco', '=', 'h.id_postco')
                ->join('variedad as v', 'v.id_variedad', '=', 'r.id_item')
                ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                ->select(
                    'v.id_planta',
                    'r.id_item as id_variedad',
                    'v.nombre as variedad_nombre',
                    'p.nombre as planta_nombre',
                )->distinct()
                ->where('h.fecha', '>=', $desde)
                ->where('h.fecha', '<=', $request->hasta)
                ->whereNotIn('r.id_item', $ids_variedad_compra_flor)
                ->whereNotIn('r.id_item', $ids_variedad_recepcion);
            if ($request->planta != '')
                $combinaciones_pedido = $combinaciones_pedido->where('v.id_planta', $request->planta);
            $combinaciones_pedido = $combinaciones_pedido->orderBy('p.nombre')
                ->orderBy('v.nombre')
                ->get();
            $ids_variedad_pedido = $combinaciones_pedido->pluck('id_variedad')->toArray();
            $combinaciones_ot = DB::table('postco as h')
                ->join('ot_postco as o', 'o.id_postco', '=', 'h.id_postco')
                ->join('detalle_ot_postco as r', 'r.id_ot_postco', '=', 'o.id_ot_postco')
                ->join('variedad as v', 'v.id_variedad', '=', 'r.id_item')
                ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                ->select(
                    'v.id_planta',
                    'r.id_item as id_variedad',
                    'v.nombre as variedad_nombre',
                    'p.nombre as planta_nombre',
                )->distinct()
                ->where('h.fecha', '>=', $desde)
                ->where('h.fecha', '<=', $request->hasta)
                ->whereNotIn('r.id_item', $ids_variedad_compra_flor)
                ->whereNotIn('r.id_item', $ids_variedad_recepcion)
                ->whereNotIn('r.id_item', $ids_variedad_pedido);
            if ($request->planta != '')
                $combinaciones_ot = $combinaciones_ot->where('v.id_planta', $request->planta);
            $combinaciones_ot = $combinaciones_ot->orderBy('p.nombre')
                ->orderBy('v.nombre')
                ->get();
            $combinaciones = $combinaciones_compra_flor->merge($combinaciones_pedido)->merge($combinaciones_recepcion)->merge($combinaciones_ot);
            $fechas = [];
            $f = $desde;
            while ($f <= $request->hasta) {
                $fechas[] = $f;
                $f = opDiasFecha('+', 1, $f);
            }
            $listado = [];
            foreach ($combinaciones as $pos_i => $item) {
                $compras = DB::table('desglose_compra_flor')
                    ->select(
                        DB::raw('sum(cantidad_mallas * tallos_x_malla) as cantidad'),
                        'fecha'
                    )
                    ->where('fecha', '>', $desde)
                    ->where('estado', 1)
                    ->where('id_variedad', $item->id_variedad)
                    ->groupBy('fecha')
                    ->orderBy('fecha', 'asc')
                    ->get();
                $recepciones = DB::table('desglose_recepcion')
                    ->select(
                        DB::raw('sum(disponibles) as cantidad'),
                        'fecha'
                    )
                    ->where('fecha', '<=', $desde)
                    ->where('estado', 1)
                    ->where('disponibles', '>', 0)
                    ->where('id_variedad', $item->id_variedad)
                    ->groupBy('fecha')
                    ->orderBy('fecha', 'asc')
                    ->get();
                $valores_postco = [];
                foreach ($fechas as $f) {
                    $venta = getTallosVentaByVariedad($item->id_variedad, $f);

                    $armados = DB::table('ot_postco as o')
                        ->join('postco as p', 'p.id_postco', '=', 'o.id_postco')
                        ->join('detalle_ot_postco as do', 'do.id_ot_postco', '=', 'o.id_ot_postco')
                        ->select(
                            DB::raw('sum(do.unidades * o.ramos) as cantidad'),
                        )
                        ->where('do.id_item', $item->id_variedad)
                        ->where('o.estado', 'A')
                        ->where('p.fecha', $f)
                        ->get()[0]->cantidad;
                    $armados += DB::table('postco as p')
                        ->join('armado_postco as a', 'a.id_postco', '=', 'p.id_postco')
                        ->join('detalle_armado_postco as r', 'r.id_armado_postco', '=', 'a.id_armado_postco')
                        ->select(
                            DB::raw('sum(a.ramos * r.unidades) as cantidad'),
                        )
                        ->where('r.id_item', $item->id_variedad)
                        ->where('p.fecha', $f)
                        ->get()[0]->cantidad;
                    $valores_postco[] = [
                        'venta' => $venta,
                        'armados' => $armados,
                        'fecha' => $f,
                    ];
                }
                $valores_recepciones = $recepciones->pluck('cantidad')->toArray();
                $valores_compras = $compras->pluck('cantidad')->toArray();

                /* HACER CALCULOS */
                $meta = 0;
                $total_negativos = 0;
                $total_perdidas = 0;
                $total_ventas = 0;
                $list_saldos = [];
                $list_perdidas = [];
                foreach ($valores_postco as $pos_v => $v) {
                    $inventario = 0;
                    $perdida = 0;
                    $pos_perdidas_recepciones = [];
                    $pos_perdidas_compras = [];
                    for ($pos_r = 0; $pos_r < count($valores_recepciones); $pos_r++) {
                        $r = $valores_recepciones[$pos_r];
                        $fecha_exp = opDiasFecha('+', $item->dias_rotacion_recepcion, $recepciones[$pos_r]->fecha);
                        if ($fecha_exp >= $desde) {
                            if ($v['fecha'] >= opDiasFecha('+', $item->dias_rotacion_recepcion, $recepciones[$pos_r]->fecha)) {
                                if ($valores_recepciones[$pos_r] > 0) {
                                    $perdida += $valores_recepciones[$pos_r];
                                    $pos_perdidas_recepciones[] = $pos_r;
                                }
                                $valores_recepciones[$pos_r] = 0;
                                $r = 0;
                            }
                            if ($meta > 0 && $r > 0) {
                                if ($r >= $meta) {
                                    $valores_recepciones[$pos_r] = $r - $meta;
                                    $meta = 0;
                                } else {
                                    $meta -= $r;
                                    $valores_recepciones[$pos_r] = 0;
                                }
                            }
                            $inventario += $valores_recepciones[$pos_r];
                        }
                    }
                    for ($pos_c = 0; $pos_c < count($valores_compras); $pos_c++) {
                        $c = $valores_compras[$pos_c];
                        if ($v['fecha'] >= opDiasFecha('+', $item->dias_rotacion_recepcion, $compras[$pos_c]->fecha)) {
                            if ($valores_compras[$pos_c] > 0) {
                                $perdida += $valores_compras[$pos_c];
                                $pos_perdidas_compras[] = $pos_c;
                            }
                            $valores_compras[$pos_c] = 0;
                            $c = 0;
                        }
                        if ($meta > 0 && $c > 0 && $compras[$pos_c]->fecha <= $v['fecha'] && $v['fecha'] <= opDiasFecha('+', $item->dias_rotacion_recepcion, $compras[$pos_c]->fecha)) {
                            if ($c >= $meta) {
                                $valores_compras[$pos_c] = $c - $meta;
                                $meta = 0;
                            } else {
                                $meta -= $c;
                                $valores_compras[$pos_c] = 0;
                            }
                        }
                        if ($compras[$pos_c]->fecha <= $v['fecha'] && $v['fecha'] < opDiasFecha('+', $item->dias_rotacion_recepcion, $compras[$pos_c]->fecha)) {
                            $inventario += $valores_compras[$pos_c];
                        }
                    }
                    $venta = $v['venta'];
                    $armados = $v['armados'];
                    $faltante = $venta - $armados;
                    $saldo = $inventario - $faltante;
                    $meta = $faltante >= 0 ? $faltante : 0;
                    $total_negativos += $saldo < 0 ? $saldo : 0;
                    $total_perdidas += $perdida;
                    $total_ventas += $venta;

                    $list_saldos[] = $saldo;
                    $list_perdidas[] = $perdida;
                }

                $listado[] = [
                    'variedad' => $item,
                    'list_saldos' => $list_saldos,
                    'list_perdidas' => $list_perdidas,
                    'total_negativos' => $total_negativos,
                    'total_perdidas' => $total_perdidas,
                    'total_ventas' => $total_ventas,
                ];
            }
            return view('adminlte.gestion.postco.reporte_disponibilidad.partials.listado', [
                'combinaciones' => $combinaciones,
                'fechas' => $fechas,
                'desde' => $desde,
                'listado' => $listado,
                'pos_listado' => 0
            ]);
        }
    }

    public function exportar_listado(Request $request)
    {
        $spread = new Spreadsheet();
        $this->excel_listado($spread, $request);

        $fileName = "InventarioCompraFlor.xlsx";
        $writer = new Xlsx($spread);

        //--------------------------- GUARDAR EL EXCEL -----------------------

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        $writer->save('php://output');

        //$writer->save('/var/www/html/Dasalflor/storage/storage/excel/excel_prueba.xlsx');
    }

    public function excel_listado($spread, $request)
    {
        $combinaciones_compra_flor = DB::table('desglose_compra_flor as i')
            ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->select(
                'i.id_variedad',
                'v.nombre as variedad_nombre',
                'p.nombre as planta_nombre',
                'v.dias_rotacion_recepcion',
            )->distinct()
            ->where('fecha', '>', hoy());
        if ($request->planta != '')
            $combinaciones_compra_flor = $combinaciones_compra_flor->where('v.id_planta', $request->planta);
        $combinaciones_compra_flor = $combinaciones_compra_flor->orderBy('p.nombre')
            ->orderBy('v.nombre')
            ->get();
        $ids_variedad_compra_flor = $combinaciones_compra_flor->pluck('id_variedad')->toArray();
        $combinaciones_recepcion = DB::table('desglose_recepcion as i')
            ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->select(
                'i.id_variedad',
                'v.nombre as variedad_nombre',
                'p.nombre as planta_nombre',
                'v.dias_rotacion_recepcion',
            )->distinct()
            ->where('i.disponibles', '>', 0)
            ->where('i.estado', 1)
            ->whereNotIn('i.id_variedad', $ids_variedad_compra_flor);
        if ($request->planta != '')
            $combinaciones_recepcion = $combinaciones_recepcion->where('v.id_planta', $request->planta);
        $combinaciones_recepcion = $combinaciones_recepcion->orderBy('p.nombre')
            ->orderBy('v.nombre')
            ->get();
        $ids_variedad_recepcion = $combinaciones_recepcion->pluck('id_variedad')->toArray();
        $combinaciones_pedido = DB::table('postco as h')
            ->join('distribucion_postco as r', 'r.id_postco', '=', 'h.id_postco')
            ->join('variedad as v', 'v.id_variedad', '=', 'r.id_item')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->select(
                'r.id_item as id_variedad',
                'v.nombre as variedad_nombre',
                'p.nombre as planta_nombre',
            )->distinct()
            ->where('h.fecha', '>=', hoy())
            ->where('h.fecha', '<=', $request->hasta)
            ->whereNotIn('r.id_item', $ids_variedad_compra_flor)
            ->whereNotIn('r.id_item', $ids_variedad_recepcion);
        if ($request->planta != '')
            $combinaciones_pedido = $combinaciones_pedido->where('v.id_planta', $request->planta);
        $combinaciones_pedido = $combinaciones_pedido->orderBy('p.nombre')
            ->orderBy('v.nombre')
            ->get();
        $combinaciones = $combinaciones_compra_flor->merge($combinaciones_pedido)->merge($combinaciones_recepcion);
        $fechas = [];
        $f = hoy();
        while ($f <= $request->hasta) {
            $fechas[] = $f;
            $f = opDiasFecha('+', 1, $f);
        }

        $listado = [];
        foreach ($combinaciones as $item) {
            $compras = DB::table('desglose_compra_flor')
                ->select(
                    DB::raw('sum(cantidad_mallas * tallos_x_malla) as cantidad'),
                    'fecha'
                )
                ->where('fecha', '>', hoy())
                ->where('estado', 1)
                ->where('id_variedad', $item->id_variedad)
                ->groupBy('fecha')
                ->orderBy('fecha', 'asc')
                ->get();
            $recepciones = DB::table('desglose_recepcion')
                ->select(
                    DB::raw('sum(disponibles) as cantidad'),
                    'fecha'
                )
                ->where('fecha', '<=', hoy())
                ->where('estado', 1)
                ->where('disponibles', '>', 0)
                ->where('id_variedad', $item->id_variedad)
                ->groupBy('fecha')
                ->orderBy('fecha', 'asc')
                ->get();
            $valores_postco = [];
            foreach ($fechas as $f) {
                $venta = getTallosVentaByVariedad($item->id_variedad, $f);

                $armados = DB::table('ot_postco as o')
                    ->join('postco as p', 'p.id_postco', '=', 'o.id_postco')
                    ->join('detalle_ot_postco as do', 'do.id_ot_postco', '=', 'o.id_ot_postco')
                    ->select(
                        DB::raw('sum(do.unidades * o.ramos) as cantidad'),
                    )
                    ->where('do.id_item', $item->id_variedad)
                    ->where('o.estado', 'A')
                    ->where('p.fecha', $f)
                    ->get()[0]->cantidad;
                $armados += DB::table('postco as p')
                    ->join('armado_postco as a', 'a.id_postco', '=', 'p.id_postco')
                    ->join('detalle_armado_postco as r', 'r.id_armado_postco', '=', 'a.id_armado_postco')
                    ->select(
                        DB::raw('sum(a.ramos * r.unidades) as cantidad'),
                    )
                    ->where('r.id_item', $item->id_variedad)
                    ->where('p.fecha', $f)
                    ->get()[0]->cantidad;
                $valores_postco[] = [
                    'venta' => $venta,
                    'armados' => $armados,
                    'fecha' => $f,
                ];
            }
            $valores_recepciones = $recepciones->pluck('cantidad')->toArray();
            $valores_compras = $compras->pluck('cantidad')->toArray();

            /* HACER CALCULOS */
            $meta = 0;
            $total_negativos = 0;
            $total_perdidas = 0;
            $total_ventas = 0;
            $list_saldos = [];
            $list_perdidas = [];
            foreach ($valores_postco as $pos_v => $v) {
                $inventario = 0;
                $perdida = 0;
                $pos_perdidas_recepciones = [];
                $pos_perdidas_compras = [];
                for ($pos_r = 0; $pos_r < count($valores_recepciones); $pos_r++) {
                    $r = $valores_recepciones[$pos_r];
                    if ($v['fecha'] >= opDiasFecha('+', $item->dias_rotacion_recepcion, $recepciones[$pos_r]->fecha)) {
                        if ($valores_recepciones[$pos_r] > 0) {
                            $perdida += $valores_recepciones[$pos_r];
                            $pos_perdidas_recepciones[] = $pos_r;
                        }
                        $valores_recepciones[$pos_r] = 0;
                        $r = 0;
                    }
                    if ($meta > 0 && $r > 0) {
                        if ($r >= $meta) {
                            $valores_recepciones[$pos_r] = $r - $meta;
                            $meta = 0;
                        } else {
                            $meta -= $r;
                            $valores_recepciones[$pos_r] = 0;
                        }
                    }
                    $inventario += $valores_recepciones[$pos_r];
                }
                for ($pos_c = 0; $pos_c < count($valores_compras); $pos_c++) {
                    $c = $valores_compras[$pos_c];
                    if ($v['fecha'] >= opDiasFecha('+', $item->dias_rotacion_recepcion, $compras[$pos_c]->fecha)) {
                        if ($valores_compras[$pos_c] > 0) {
                            $perdida += $valores_compras[$pos_c];
                            $pos_perdidas_compras[] = $pos_c;
                        }
                        $valores_compras[$pos_c] = 0;
                        $c = 0;
                    }
                    if ($meta > 0 && $c > 0 && $compras[$pos_c]->fecha <= $v['fecha'] && $v['fecha'] <= opDiasFecha('+', $item->dias_rotacion_recepcion, $compras[$pos_c]->fecha)) {
                        if ($c >= $meta) {
                            $valores_compras[$pos_c] = $c - $meta;
                            $meta = 0;
                        } else {
                            $meta -= $c;
                            $valores_compras[$pos_c] = 0;
                        }
                    }
                    if ($compras[$pos_c]->fecha <= $v['fecha'] && $v['fecha'] < opDiasFecha('+', $item->dias_rotacion_recepcion, $compras[$pos_c]->fecha)) {
                        $inventario += $valores_compras[$pos_c];
                    }
                }
                $venta = $v['venta'];
                $armados = $v['armados'];
                $faltante = $venta - $armados;
                $saldo = $inventario - $faltante;
                $meta = $faltante >= 0 ? $faltante : 0;
                $total_negativos += $saldo < 0 ? $saldo : 0;
                $total_perdidas += $perdida;
                $total_ventas += $venta;

                $list_saldos[] = $saldo;
                $list_perdidas[] = $perdida;
            }

            $listado[] = [
                'variedad' => $item,
                'list_saldos' => $list_saldos,
                'list_perdidas' => $list_perdidas,
                'total_negativos' => $total_negativos,
                'total_perdidas' => $total_perdidas,
                'total_ventas' => $total_ventas,
            ];
        }

        $columnas = getColumnasExcel();
        $sheet = $spread->getActiveSheet();
        $sheet->setTitle('Disponibilidad de Flor');

        $row = 1;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Planta');
        $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + 1));
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Variedad');
        $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + 1));
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Venta Total');
        $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + 1));
        foreach ($fechas as $f) {
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $f);
            $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col + 1] . $row);
            $col += 1;
        }
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Negativos Totales');
        $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + 1));
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Perdidas Totales');
        $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + 1));

        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');
        $row++;
        $col = 2;
        foreach ($fechas as $f) {
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Saldo');
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Perdida');
        }
        setBgToCeldaExcel($sheet, $columnas[3] . $row . ':' . $columnas[$col] . $row, '5a7177');
        setColorTextToCeldaExcel($sheet, $columnas[3] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        foreach ($listado as $item) {
            $row++;
            $col = 0;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item['variedad']->planta_nombre);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item['variedad']->variedad_nombre);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item['total_ventas']);
            setBgToCeldaExcel($sheet, $columnas[$col] . $row, '5a7177');
            setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
            foreach ($item['list_saldos'] as $pos_s => $s) {
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $s);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item['list_perdidas'][$pos_s]);
            }
            $col++;
            if ($item['total_negativos'] < 0)
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item['total_negativos']);
            setBgToCeldaExcel($sheet, $columnas[$col] . $row, '5a7177');
            setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
            $col++;
            if ($item['total_perdidas'] > 0)
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item['total_perdidas']);
        }

        setTextCenterToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);
        setBorderToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);

        for ($i = 0; $i <= $col; $i++)
            $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);
    }

    public function detalle_ventas(Request $request)
    {
        $desde = hoy();
        $postcos = DB::table('postco as p')
            ->join('distribucion_postco as r', 'r.id_postco', '=', 'p.id_postco')
            ->select(
                'p.id_postco',
            )->distinct()
            ->where('p.fecha', '>=', $desde)
            ->where('p.fecha', '<=', $request->hasta)
            ->where('r.id_item', $request->variedad)
            ->orderBy('p.fecha')
            ->get();
        $ids_postcos = $postcos->pluck('id_postco')->toArray();
        $postcos_ot = DB::table('postco as p')
            ->join('ot_postco as o', 'o.id_postco', '=', 'p.id_postco')
            ->join('detalle_ot_postco as do', 'do.id_ot_postco', '=', 'o.id_ot_postco')
            ->select(
                'p.id_postco',
            )->distinct()
            ->where('p.fecha', '>=', $desde)
            ->where('p.fecha', '<=', $request->hasta)
            ->where('do.id_item', $request->variedad)
            ->whereNotIn('p.id_postco', $ids_postcos)
            ->orderBy('p.fecha')
            ->get();
        $postcos = $postcos->merge($postcos_ot);

        $listado = [];
        foreach ($postcos as $p) {
            $postco = Postco::find($p->id_postco);
            $clientes = $postco->clientes;
            $tallos_venta = 0;
            $ramos_venta = $postco->ramos; // ramos totales del pedido
            //$ramos_procesados = $postco->getRamosOt();  // ramos procesados mediante OT y Pre-OT
            $tallos_ot = 0;

            $list_ot = DB::table('ot_postco as o')
                ->join('detalle_ot_postco as do', 'do.id_ot_postco', '=', 'o.id_ot_postco')
                ->select(
                    'o.id_ot_postco',
                    'o.ramos',
                    DB::raw('sum(do.unidades * o.ramos) as tallos'),
                )
                ->where('do.id_item', $request->variedad)
                ->where('o.id_postco', $postco->id_postco)
                ->groupBy(
                    'o.id_ot_postco',
                    'o.ramos'
                )
                ->get();
            foreach ($list_ot as $ot) {
                $tallos_venta += $ot->tallos;
                $tallos_ot += $ot->tallos;
            }

            $tallos_restantes = 0;
            if ($ramos_venta > $postco->armados) {   // aun quedan ramos del pedido por procesar
                $diferencia = $ramos_venta - $postco->armados;
                $unidades = DB::table('postco as d')
                    ->join('distribucion_postco as r', 'r.id_postco', '=', 'd.id_postco')
                    ->select(DB::raw('sum(r.unidades) as cantidad'))
                    ->where('r.id_item', $request->variedad)
                    ->where('d.id_postco', $postco->id_postco)
                    ->get()[0]->cantidad;
                $tallos_venta += $diferencia * $unidades;
                $tallos_restantes = $diferencia * $unidades;
            }

            $listado[] = [
                'clientes' => $clientes,
                'postco' => $postco,
                'tallos_venta' => $tallos_venta,
                'tallos_ot' => $tallos_ot,
                'tallos_restantes' => $tallos_restantes,
            ];
        }
        return view('adminlte.gestion.postco.reporte_disponibilidad.partials.detalle_ventas', [
            'total_inventario' => $request->total,
            'desde' => $desde,
            'hasta' => $request->hasta,
            'listado' => $listado,
            'variedad' => Variedad::find($request->variedad)
        ]);
    }
}
