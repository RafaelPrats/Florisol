<?php

namespace yura\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use yura\Modelos\DetalleImportPedido;
use yura\Modelos\Planta;
use yura\Modelos\Submenu;
use yura\Modelos\Variedad;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class DisponibilidadFlorController extends Controller
{
    public function inicio(Request $request)
    {
        $plantas = Planta::where('estado', 1)
            ->where('id_planta', '!=', 151)
            ->where('id_planta', '!=', 128)
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.postcocecha.disponibilidad_flor.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'plantas' => $plantas,
        ]);
    }

    public function listar_reporte(Request $request)
    {
        if ($request->variedad != '') {
            $variedad = Variedad::find($request->variedad);
            $compras = DB::table('desglose_compra_flor')
                ->select(
                    DB::raw('sum(cantidad_mallas * tallos_x_malla) as cantidad'),
                    'fecha'
                )
                ->where('fecha', '>', hoy())
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
                ->where('fecha', '<=', hoy())
                ->where('estado', 1)
                ->where('disponibles', '>', 0)
                ->where('id_variedad', $request->variedad)
                ->groupBy('fecha')
                ->orderBy('fecha', 'asc')
                ->get();
            $ventas = DB::table('resumen_fechas')
                ->select(
                    DB::raw('sum(tallos_venta) as cantidad'),
                    'fecha'
                )
                ->where('fecha', '>=', hoy())
                ->where('fecha', '<=', $request->hasta)
                ->where('id_variedad', $request->variedad)
                ->groupBy('fecha')
                ->orderBy('fecha', 'asc')
                ->get();
            $armados = [];
            foreach ($ventas as $v) {
                $query_armados = DB::table('orden_trabajo as o')
                    ->join('detalle_import_pedido as d', 'd.id_detalle_import_pedido', '=', 'o.id_detalle_import_pedido')
                    ->join('import_pedido as p', 'p.id_import_pedido', '=', 'd.id_import_pedido')
                    ->join('detalle_orden_trabajo as do', 'do.id_orden_trabajo', '=', 'o.id_orden_trabajo')
                    ->select(
                        DB::raw('sum(do.tallos) as cantidad'),
                    )
                    ->where('do.id_variedad', $request->variedad)
                    ->where('o.armado', 1)
                    ->where('p.fecha', $v->fecha)
                    ->where('p.estado', 1)
                    ->get()[0]->cantidad;
                $query_armados += DB::table('detalle_import_pedido as d')
                    ->join('import_pedido as p', 'p.id_import_pedido', '=', 'd.id_import_pedido')
                    ->join('distribucion_receta as r', 'r.id_detalle_import_pedido', '=', 'd.id_detalle_import_pedido')
                    ->select(
                        DB::raw('sum(d.ramos_armados * r.unidades) as cantidad'),
                    )
                    ->where('r.id_variedad', $request->variedad)
                    ->where('p.fecha', $v->fecha)
                    ->where('p.estado', 1)
                    ->get()[0]->cantidad;
                $armados[] = [
                    'cantidad' => $query_armados,
                    'fecha' => $v->fecha,
                ];
            }
            $valores_recepciones = $recepciones->pluck('cantidad')->toArray();
            $valores_compras = $compras->pluck('cantidad')->toArray();
            return view('adminlte.gestion.postcocecha.disponibilidad_flor.partials.listado', [
                'variedad' => $variedad,
                'compras' => $compras,
                'recepciones' => $recepciones,
                'valores_recepciones' => $valores_recepciones,
                'valores_compras' => $valores_compras,
                'ventas' => $ventas,
                'list_armados' => $armados,
            ]);
        } else {
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
            $combinaciones_pedido = DB::table('resumen_fechas as h')
                ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                ->select(
                    'h.id_variedad',
                    'v.nombre as variedad_nombre',
                    'p.nombre as planta_nombre',
                )->distinct()
                ->where('h.fecha', '>=', hoy())
                ->where('h.fecha', '<=', $request->hasta)
                ->where('h.tallos_venta', '>', 0)
                ->whereNotIn('h.id_variedad', $ids_variedad_compra_flor)
                ->whereNotIn('h.id_variedad', $ids_variedad_recepcion);
            if ($request->planta != '')
                $combinaciones_pedido = $combinaciones_pedido->where('v.id_planta', $request->planta);
            $combinaciones_pedido = $combinaciones_pedido->orderBy('p.nombre')
                ->orderBy('v.nombre')
                ->get();
            $combinaciones = $combinaciones_compra_flor->merge($combinaciones_pedido)->merge($combinaciones_recepcion);
            $fechas = DB::table('resumen_fechas')
                ->select(
                    'fecha'
                )->distinct()
                ->where('fecha', '>=', hoy())
                ->where('fecha', '<=', $request->hasta)
                ->orderBy('fecha', 'asc')
                ->get();
            return view('adminlte.gestion.postcocecha.disponibilidad_flor.partials.listado_all', [
                'combinaciones' => $combinaciones,
                'fechas' => $fechas,
                'pos_listado' => 0
            ]);
        }
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
            ->where('ped.fecha', '>=', hoy())
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
            ->where('ped.fecha', '>=', hoy())
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
            ->where('ped.fecha', '>=', hoy())
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
        return view('adminlte.gestion.postcocecha.disponibilidad_flor.partials.detalle_ventas', [
            'total_inventario' => $request->total,
            'desde' => hoy(),
            'hasta' => $request->hasta,
            'listado' => $listado,
            'variedad' => Variedad::find($request->variedad)
        ]);
    }

    public function cargar_tabla(Request $request)
    {
        $ids_listado = json_decode($request->ids_listado);
        $fechas = DB::table('resumen_fechas')
            ->select(
                'fecha'
            )
            ->where('fecha', '>=', hoy())
            ->where('fecha', '<=', $request->hasta)
            ->groupBy('fecha')
            ->orderBy('fecha', 'asc')
            ->get()->pluck('fecha')->toArray();
        $listado = [];
        foreach ($ids_listado as $id_var) {
            if ($id_var != '') {
                $variedad = Variedad::find($id_var);
                $compras = DB::table('desglose_compra_flor')
                    ->select(
                        DB::raw('sum(cantidad_mallas * tallos_x_malla) as cantidad'),
                        'fecha'
                    )
                    ->where('fecha', '>', hoy())
                    ->where('estado', 1)
                    ->where('id_variedad', $variedad->id_variedad)
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
                    ->where('id_variedad', $variedad->id_variedad)
                    ->groupBy('fecha')
                    ->orderBy('fecha', 'asc')
                    ->get();
                $ventas = [];
                $list_armados = [];
                foreach ($fechas as $f) {
                    $venta = DB::table('resumen_fechas')
                        ->select(
                            DB::raw('sum(tallos_venta) as cantidad')
                        )
                        ->where('fecha', $f)
                        ->where('id_variedad', $variedad->id_variedad)
                        ->get()[0]->cantidad;
                    $query_armados = DB::table('orden_trabajo as o')
                        ->join('detalle_import_pedido as d', 'd.id_detalle_import_pedido', '=', 'o.id_detalle_import_pedido')
                        ->join('import_pedido as p', 'p.id_import_pedido', '=', 'd.id_import_pedido')
                        ->join('detalle_orden_trabajo as do', 'do.id_orden_trabajo', '=', 'o.id_orden_trabajo')
                        ->select(
                            DB::raw('sum(do.tallos) as cantidad'),
                        )
                        ->where('do.id_variedad', $variedad->id_variedad)
                        ->where('o.armado', 1)
                        ->where('p.fecha', $f)
                        ->where('p.estado', 1)
                        ->get()[0]->cantidad;
                    $query_armados += DB::table('detalle_import_pedido as d')
                        ->join('import_pedido as p', 'p.id_import_pedido', '=', 'd.id_import_pedido')
                        ->join('distribucion_receta as r', 'r.id_detalle_import_pedido', '=', 'd.id_detalle_import_pedido')
                        ->select(
                            DB::raw('sum(d.ramos_armados * r.unidades) as cantidad'),
                        )
                        ->where('r.id_variedad', $variedad->id_variedad)
                        ->where('p.fecha', $f)
                        ->where('p.estado', 1)
                        ->get()[0]->cantidad;
                    $list_armados[] = [
                        'cantidad' => $query_armados,
                        'fecha' => $f,
                    ];
                    $ventas[] = [
                        'cantidad' => $venta,
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
                foreach ($ventas as $pos_v => $v) {
                    $inventario = 0;
                    $perdida = 0;
                    $pos_perdidas_recepciones = [];
                    $pos_perdidas_compras = [];
                    for ($pos_r = 0; $pos_r < count($valores_recepciones); $pos_r++) {
                        $r = $valores_recepciones[$pos_r];
                        if ($v['fecha'] >= opDiasFecha('+', $variedad->dias_rotacion_recepcion, $recepciones[$pos_r]->fecha)) {
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
                        if ($v['fecha'] >= opDiasFecha('+', $variedad->dias_rotacion_recepcion, $compras[$pos_c]->fecha)) {
                            if ($valores_compras[$pos_c] > 0) {
                                $perdida += $valores_compras[$pos_c];
                                $pos_perdidas_compras[] = $pos_c;
                            }
                            $valores_compras[$pos_c] = 0;
                            $c = 0;
                        }
                        if ($meta > 0 && $c > 0 && $compras[$pos_c]->fecha <= $v['fecha'] && $v['fecha'] <= opDiasFecha('+', $variedad->dias_rotacion_recepcion, $compras[$pos_c]->fecha)) {
                            if ($c >= $meta) {
                                $valores_compras[$pos_c] = $c - $meta;
                                $meta = 0;
                            } else {
                                $meta -= $c;
                                $valores_compras[$pos_c] = 0;
                            }
                        }
                        if ($compras[$pos_c]->fecha <= $v['fecha'] && $v['fecha'] < opDiasFecha('+', $variedad->dias_rotacion_recepcion, $compras[$pos_c]->fecha)) {
                            $inventario += $valores_compras[$pos_c];
                        }
                    }
                    $venta = $v['cantidad'];
                    $armados = $list_armados[$pos_v]['cantidad'];
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
                    'variedad' => $variedad,
                    'list_saldos' => $list_saldos,
                    'list_perdidas' => $list_perdidas,
                    'total_negativos' => $total_negativos,
                    'total_perdidas' => $total_perdidas,
                    'total_ventas' => $total_ventas,
                ];
            }
        }

        return view('adminlte.gestion.postcocecha.disponibilidad_flor.partials.cargar_tabla', [
            'listado' => $listado,
            'num_filas' => $request->num_filas,
        ]);
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
        $combinaciones_pedido = DB::table('resumen_fechas as h')
            ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->select(
                'h.id_variedad',
                'v.nombre as variedad_nombre',
                'p.nombre as planta_nombre',
                'v.dias_rotacion_recepcion',
            )->distinct()
            ->where('h.fecha', '>=', hoy())
            ->where('h.fecha', '<=', $request->hasta)
            ->where('h.tallos_venta', '>', 0)
            ->whereNotIn('h.id_variedad', $ids_variedad_compra_flor)
            ->whereNotIn('h.id_variedad', $ids_variedad_recepcion);
        if ($request->planta != '')
            $combinaciones_pedido = $combinaciones_pedido->where('v.id_planta', $request->planta);
        $combinaciones_pedido = $combinaciones_pedido->orderBy('p.nombre')
            ->orderBy('v.nombre')
            ->get();
        $combinaciones = $combinaciones_compra_flor->merge($combinaciones_pedido)->merge($combinaciones_recepcion);
        $fechas = DB::table('resumen_fechas')
            ->select(
                'fecha'
            )->distinct()
            ->where('fecha', '>=', hoy())
            ->where('fecha', '<=', $request->hasta)
            ->orderBy('fecha', 'asc')
            ->get();

        $listado = [];
        foreach ($combinaciones as $variedad) {
            $compras = DB::table('desglose_compra_flor')
                ->select(
                    DB::raw('sum(cantidad_mallas * tallos_x_malla) as cantidad'),
                    'fecha'
                )
                ->where('fecha', '>', hoy())
                ->where('estado', 1)
                ->where('id_variedad', $variedad->id_variedad)
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
                ->where('id_variedad', $variedad->id_variedad)
                ->groupBy('fecha')
                ->orderBy('fecha', 'asc')
                ->get();
            $ventas = [];
            $list_armados = [];
            foreach ($fechas as $f) {
                $venta = DB::table('resumen_fechas')
                    ->select(
                        DB::raw('sum(tallos_venta) as cantidad')
                    )
                    ->where('fecha', $f->fecha)
                    ->where('id_variedad', $variedad->id_variedad)
                    ->get()[0]->cantidad;
                $query_armados = DB::table('orden_trabajo as o')
                    ->join('detalle_import_pedido as d', 'd.id_detalle_import_pedido', '=', 'o.id_detalle_import_pedido')
                    ->join('import_pedido as p', 'p.id_import_pedido', '=', 'd.id_import_pedido')
                    ->join('detalle_orden_trabajo as do', 'do.id_orden_trabajo', '=', 'o.id_orden_trabajo')
                    ->select(
                        DB::raw('sum(do.tallos) as cantidad'),
                    )
                    ->where('do.id_variedad', $variedad->id_variedad)
                    ->where('o.armado', 1)
                    ->where('p.fecha', $f->fecha)
                    ->where('p.estado', 1)
                    ->get()[0]->cantidad;
                $query_armados += DB::table('detalle_import_pedido as d')
                    ->join('import_pedido as p', 'p.id_import_pedido', '=', 'd.id_import_pedido')
                    ->join('distribucion_receta as r', 'r.id_detalle_import_pedido', '=', 'd.id_detalle_import_pedido')
                    ->select(
                        DB::raw('sum(d.ramos_armados * r.unidades) as cantidad'),
                    )
                    ->where('r.id_variedad', $variedad->id_variedad)
                    ->where('p.fecha', $f->fecha)
                    ->where('p.estado', 1)
                    ->get()[0]->cantidad;
                $list_armados[] = [
                    'cantidad' => $query_armados,
                    'fecha' => $f->fecha,
                ];
                $ventas[] = [
                    'cantidad' => $venta,
                    'fecha' => $f->fecha,
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
            foreach ($ventas as $pos_v => $v) {
                $inventario = 0;
                $perdida = 0;
                $pos_perdidas_recepciones = [];
                $pos_perdidas_compras = [];
                for ($pos_r = 0; $pos_r < count($valores_recepciones); $pos_r++) {
                    $r = $valores_recepciones[$pos_r];
                    if ($v['fecha'] >= opDiasFecha('+', $variedad->dias_rotacion_recepcion, $recepciones[$pos_r]->fecha)) {
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
                    if ($v['fecha'] >= opDiasFecha('+', $variedad->dias_rotacion_recepcion, $compras[$pos_c]->fecha)) {
                        if ($valores_compras[$pos_c] > 0) {
                            $perdida += $valores_compras[$pos_c];
                            $pos_perdidas_compras[] = $pos_c;
                        }
                        $valores_compras[$pos_c] = 0;
                        $c = 0;
                    }
                    if ($meta > 0 && $c > 0 && $compras[$pos_c]->fecha <= $v['fecha'] && $v['fecha'] <= opDiasFecha('+', $variedad->dias_rotacion_recepcion, $compras[$pos_c]->fecha)) {
                        if ($c >= $meta) {
                            $valores_compras[$pos_c] = $c - $meta;
                            $meta = 0;
                        } else {
                            $meta -= $c;
                            $valores_compras[$pos_c] = 0;
                        }
                    }
                    if ($compras[$pos_c]->fecha <= $v['fecha'] && $v['fecha'] < opDiasFecha('+', $variedad->dias_rotacion_recepcion, $compras[$pos_c]->fecha)) {
                        $inventario += $valores_compras[$pos_c];
                    }
                }
                $venta = $v['cantidad'];
                $armados = $list_armados[$pos_v]['cantidad'];
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
                'variedad' => $variedad,
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
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $f->fecha);
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
            foreach ($item['list_saldos'] as $pos_s => $s) {
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $s);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item['list_perdidas'][$pos_s]);
            }
            $col++;
            if ($item['total_negativos'] < 0)
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item['total_negativos']);
            $col++;
            if ($item['total_perdidas'] > 0)
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item['total_perdidas']);
        }

        setTextCenterToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);
        setBorderToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);

        for ($i = 0; $i <= $col; $i++)
            $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);
    }
}
