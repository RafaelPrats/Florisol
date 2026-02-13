<?php

namespace yura\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use yura\Modelos\ConfiguracionEmpresa;
use yura\Modelos\DesgloseCompraFlor;
use yura\Modelos\DesgloseRecepcion;
use yura\Modelos\Planta;
use yura\Modelos\RegistroCorrecciones;
use yura\Modelos\Submenu;
use yura\Modelos\Variedad;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use yura\Modelos\CompraParcialDiaria;
use yura\Modelos\DetalleImportPedido;

class InventarioCompraFlorController extends Controller
{
    public function inicio(Request $request)
    {
        $plantas = Planta::where('estado', 1)
            ->orderBy('nombre')
            ->get();
        $proveedores = ConfiguracionEmpresa::where('proveedor', 1)
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.postcocecha.inventario_compra_flor.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'plantas' => $plantas,
            'proveedores' => $proveedores,
        ]);
    }

    public function listar_inventario_compra_flor(Request $request)
    {
        $ini_timer = date('Y-m-d H:i:s');
        $finca = getFincaActiva();

        $combinaciones = DB::table('desglose_compra_flor as i')
            ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->join('configuracion_empresa as f', 'f.id_configuracion_empresa', '=', 'i.id_proveedor')
            ->select(
                'i.longitud',
                'i.id_variedad',
                'i.id_proveedor',
                'v.nombre as variedad_nombre',
                'p.nombre as planta_nombre',
                'f.nombre as proveedor_nombre',
            )->distinct()
            ->where('i.fecha', '>=', hoy())
            ->where('i.estado', 1)
            ->where('i.id_empresa', $finca);
        if ($request->proveedor != '')
            $combinaciones = $combinaciones->where('i.id_proveedor', $request->proveedor);
        if ($request->planta != '')
            $combinaciones = $combinaciones->where('v.id_planta', $request->planta);
        $combinaciones = $combinaciones->orderBy('f.nombre')
            ->orderBy('p.nombre')
            ->orderBy('v.nombre')
            ->orderBy('i.longitud')
            ->get();

        $fechas = [];
        for ($i = 1; $i <= 3; $i++) {
            $fechas[] = opDiasFecha('+', $i, hoy());
        }

        $listado = [];
        foreach ($combinaciones as $item) {
            $valores = [];
            foreach ($fechas as $pos_f => $f) {
                $inventario = DB::table('desglose_compra_flor as i')
                    ->select(DB::raw('sum(i.tallos_x_malla) as cantidad'))
                    ->where('i.id_variedad', $item->id_variedad)
                    ->where('i.id_proveedor', $item->id_proveedor)
                    ->where('i.longitud', $item->longitud)
                    ->where('i.estado', 1)
                    ->where('i.id_empresa', $finca);
                if ($pos_f == count($fechas) - 1)
                    $inventario = $inventario->where('i.fecha', '>=', $f);
                else
                    $inventario = $inventario->where('i.fecha', $f);
                $inventario = $inventario->get()[0]->cantidad;
                $valores[] = $inventario;
            }

            $inventario_hoy = DB::table('desglose_compra_flor as i')
                ->select(DB::raw('sum(i.tallos_x_malla) as cantidad'))
                ->where('i.id_variedad', $item->id_variedad)
                ->where('i.id_proveedor', $item->id_proveedor)
                ->where('i.longitud', $item->longitud)
                ->where('i.id_empresa', $finca)
                ->where('i.fecha', hoy())
                ->where('i.estado', 1)
                ->get()[0]->cantidad;

            $compra_parcial = DB::table('compra_parcial_diaria as i')
                ->select(DB::raw('sum(i.tallos) as cantidad'))
                ->where('i.id_variedad', $item->id_variedad)
                ->where('i.id_proveedor', $item->id_proveedor)
                ->where('i.longitud', $item->longitud)
                //->where('i.id_empresa', $finca)
                ->where('i.fecha', hoy())
                ->get()[0]->cantidad;

            $listado[] = [
                'combinacion' => $item,
                'valores' => $valores,
                'inventario_hoy' => $inventario_hoy,
                'compra_parcial' => $compra_parcial,
            ];
        }
        return view('adminlte.gestion.postcocecha.inventario_compra_flor.partials.listado', [
            'listado' => $listado,
            'fechas' => $fechas,
        ]);
    }

    public function listar_inventario_compra_flor_acumulado(Request $request)
    {
        $hora_ini = date('Y-m-d H:i:s');
        $finca = getFincaActiva();
        if (in_array($request->tipo, ['T', 'I'])) {
            $combinaciones_compra_flor = DB::table('desglose_compra_flor as i')
                ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
                ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                ->select(
                    'i.id_variedad',
                    'v.nombre as variedad_nombre',
                    'p.nombre as planta_nombre',
                )->distinct()
                ->where('i.id_empresa', $finca);
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
                )->distinct()
                ->where('i.disponibles', '>', 0)
                ->where('i.estado', 1)
                ->whereNotIn('i.id_variedad', $ids_variedad_compra_flor)
                ->where('i.id_empresa', $finca);
            if ($request->planta != '')
                $combinaciones_recepcion = $combinaciones_recepcion->where('v.id_planta', $request->planta);
            if ($request->variedad != '')
                $combinaciones_recepcion = $combinaciones_recepcion->where('i.id_variedad', $request->variedad);
            $combinaciones_recepcion = $combinaciones_recepcion->orderBy('p.nombre')
                ->orderBy('v.nombre')
                ->get();
            $ids_variedad_recepcion = $combinaciones_recepcion->pluck('id_variedad')->toArray();
        }

        if (in_array($request->tipo, ['T', 'V'])) {
            $combinaciones_pedido = DB::table('resumen_fechas as h')
                ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                ->select(
                    'h.id_variedad',
                    'v.nombre as variedad_nombre',
                    'p.nombre as planta_nombre',
                )->distinct()
                ->where('h.fecha', '>=', $request->desde)
                ->where('h.fecha', '<=', $request->hasta)
                ->where('h.tallos_venta', '>', 0);
            if ($request->planta != '')
                $combinaciones_pedido = $combinaciones_pedido->where('v.id_planta', $request->planta);
            if ($request->tipo == 'T')
                $combinaciones_pedido = $combinaciones_pedido->whereNotIn('h.id_variedad', $ids_variedad_compra_flor)
                    ->whereNotIn('h.id_variedad', $ids_variedad_recepcion);
            $combinaciones_pedido = $combinaciones_pedido->orderBy('p.nombre')
                ->orderBy('v.nombre')
                ->get();
        }

        if ($request->tipo == 'T')
            $combinaciones = $combinaciones_compra_flor->merge($combinaciones_pedido)->merge($combinaciones_recepcion);
        elseif ($request->tipo == 'V')
            $combinaciones = $combinaciones_pedido;
        elseif ($request->tipo == 'I')
            $combinaciones = $combinaciones_compra_flor->merge($combinaciones_recepcion);

        $fechas_compra_flor = [];
        for ($i = 3; $i >= 1; $i--) {
            $fechas_compra_flor[] = opDiasFecha('+', $i, hoy());
        }

        $fechas_recepcion = [];
        for ($i = 0; $i <= 8; $i++) {
            $fechas_recepcion[] = opDiasFecha('-', $i, hoy());
        }

        $fechas_ventas = DB::table('import_pedido')
            ->select('fecha')->distinct()
            ->where('fecha', '>=', $request->desde)
            ->where('fecha', '<=', $request->hasta)
            ->where('estado', 1)
            ->orderBy('fecha')
            ->get()->pluck('fecha')->toArray();

        $last_pos = '';
        $last_var = '';
        $total_pos = count($combinaciones);
        $listado = [];
        foreach ($combinaciones as $pos_comb => $item) {
            if ($pos_comb > $request->last_pos) {
                $model_variedad = Variedad::find($item->id_variedad);
                $total_inventario = 0;
                $valores_compra_flor = [];
                foreach ($fechas_compra_flor as $pos_f => $f) {
                    $disponibles = DB::table('desglose_compra_flor as i')
                        ->select(DB::raw('sum(i.tallos_x_malla) as cantidad'))
                        ->where('i.id_variedad', $item->id_variedad)
                        ->where('i.id_empresa', $finca);
                    if ($pos_f == 0)
                        $disponibles = $disponibles->where('i.fecha', '>=', $f);
                    else
                        $disponibles = $disponibles->where('i.fecha', $f);
                    $disponibles = $disponibles->get()[0]->cantidad;
                    $valores_compra_flor[] = $disponibles;
                    $total_inventario += $disponibles;
                }

                $valores_recepcion = [];
                foreach ($fechas_recepcion as $pos_f => $f) {
                    $disponibles = DB::table('desglose_recepcion as i')
                        ->select(DB::raw('sum(i.disponibles) as cantidad'))
                        ->where('i.disponibles', '>', 0)
                        ->where('i.estado', 1)
                        ->where('i.id_variedad', $item->id_variedad)
                        ->where('i.id_empresa', $finca);
                    if ($pos_f == count($fechas_recepcion) - 1)
                        $disponibles = $disponibles->where('i.fecha', '<=', $f);
                    else
                        $disponibles = $disponibles->where('i.fecha', $f);
                    $disponibles = $disponibles->get()[0]->cantidad;
                    $valores_recepcion[] = $disponibles;
                    $total_inventario += $disponibles;
                }

                $valores_ventas = [];
                $valores_armados = [];
                $valores_compras = [];
                foreach ($fechas_ventas as $pos_f =>  $f) {
                    $venta = DB::table('resumen_fechas')
                        ->select(DB::raw('sum(tallos_venta) as cantidad'))
                        ->where('fecha', $f)
                        ->where('id_variedad', $item->id_variedad)
                        ->get()[0]->cantidad;

                    $armados = DB::table('orden_trabajo as o')
                        ->join('detalle_import_pedido as d', 'd.id_detalle_import_pedido', '=', 'o.id_detalle_import_pedido')
                        ->join('import_pedido as p', 'p.id_import_pedido', '=', 'd.id_import_pedido')
                        ->join('detalle_orden_trabajo as do', 'do.id_orden_trabajo', '=', 'o.id_orden_trabajo')
                        ->select(
                            DB::raw('sum(do.tallos) as cantidad'),
                        )
                        ->where('do.id_variedad', $item->id_variedad)
                        ->where('o.armado', 1)
                        ->where('p.fecha', $f)
                        ->where('p.estado', 1)
                        ->get()[0]->cantidad;
                    $armados += DB::table('detalle_import_pedido as d')
                        ->join('import_pedido as p', 'p.id_import_pedido', '=', 'd.id_import_pedido')
                        ->join('distribucion_receta as r', 'r.id_detalle_import_pedido', '=', 'd.id_detalle_import_pedido')
                        ->select(
                            DB::raw('sum(d.ramos_armados * r.unidades) as cantidad'),
                        )
                        ->where('r.id_variedad', $item->id_variedad)
                        ->where('p.fecha', $f)
                        ->where('p.estado', 1)
                        ->get()[0]->cantidad;
                    //$venta -= $armados;
                    $valores_ventas[] = $venta;
                    $valores_armados[] = $armados;

                    if ($f > hoy())
                        $compra = DB::table('desglose_compra_flor as i')
                            ->select(DB::raw('sum(i.tallos_x_malla) as cantidad'))
                            ->where('i.id_variedad', $item->id_variedad)
                            ->where('i.id_empresa', $finca)
                            ->where('i.fecha', $f)
                            ->get()[0]->cantidad;
                    else
                        $compra = 0;
                    $valores_compras[] = $compra;
                }
                $venta = DB::table('resumen_fechas')
                    ->select(DB::raw('sum(tallos_venta) as cantidad'))
                    ->where('fecha', '>=', $request->desde)
                    ->where('fecha', '<=', $request->hasta)
                    ->where('id_variedad', $item->id_variedad)
                    ->get()[0]->cantidad;

                $armados = DB::table('orden_trabajo as o')
                    ->join('detalle_orden_trabajo as do', 'do.id_orden_trabajo', '=', 'o.id_orden_trabajo')
                    ->join('detalle_import_pedido as d', 'd.id_detalle_import_pedido', '=', 'o.id_detalle_import_pedido')
                    ->join('import_pedido as p', 'p.id_import_pedido', '=', 'd.id_import_pedido')
                    ->select(
                        DB::raw('sum(do.tallos) as cantidad'),
                    )
                    ->where('do.id_variedad', $item->id_variedad)
                    ->where('o.armado', 1)
                    ->where('p.fecha', '>=', $request->desde)
                    ->where('p.fecha', '<=', $request->hasta)
                    ->where('p.estado', 1)
                    ->get()[0]->cantidad;
                $armados += DB::table('detalle_import_pedido as d')
                    ->join('import_pedido as p', 'p.id_import_pedido', '=', 'd.id_import_pedido')
                    ->join('distribucion_receta as r', 'r.id_detalle_import_pedido', '=', 'd.id_detalle_import_pedido')
                    ->select(
                        DB::raw('sum(d.ramos_armados * r.unidades) as cantidad'),
                    )
                    ->where('r.id_variedad', $item->id_variedad)
                    ->where('p.fecha', '>=', $request->desde)
                    ->where('p.fecha', '<=', $request->hasta)
                    ->where('p.estado', 1)
                    ->get()[0]->cantidad;

                $agregar = false;
                if ($request->tipo == 'T') {
                    if ($venta > 0 || $total_inventario > 0) {
                        if ($request->negativas) {
                            $total_combinacion = 0;
                            foreach ($valores_compra_flor as $v) {
                                $total_combinacion += $v;
                            }
                            foreach ($valores_recepcion as $pos_v => $v) {
                                $total_combinacion += $model_variedad->dias_rotacion_recepcion != '' && $model_variedad->dias_rotacion_recepcion <= $pos_v ? 0 : $v;
                            }
                            $necesidad = $total_combinacion - $venta + $armados;
                            if ($necesidad < 0)
                                $agregar = true;
                        } else
                            $agregar = true;
                    }
                } else if ($request->tipo == 'I') {
                    if ($total_inventario > 0)
                        $agregar = true;
                } else if ($request->tipo == 'V') {
                    if ($venta > 0) {
                        $agregar = true;
                    }
                }
                if ($agregar) {
                    $listado[] = [
                        'combinacion' => $item,
                        'valores_recepcion' => $valores_recepcion,
                        'valores_compra_flor' => $valores_compra_flor,
                        'valores_ventas' => $valores_ventas,
                        'valores_armados' => $valores_armados,
                        'valores_compras' => $valores_compras,
                        'venta' => $venta,
                        'armados' => $armados,
                        'model_variedad' => $model_variedad,
                    ];
                }
                $last_pos = $pos_comb;
                $last_var = $item->id_variedad;
                if (count($listado) >= 20) {
                    break;
                }
            }
        }

        $hora_fin = date('Y-m-d H:i:s');
        $duration_back = difFechas($hora_fin, $hora_ini);
        return view('adminlte.gestion.postcocecha.inventario_compra_flor.partials.listado_acumulado', [
            'listado' => $listado,
            'fechas_ventas' => $fechas_ventas,
            'fechas_compra_flor' => $fechas_compra_flor,
            'fechas_recepcion' => $fechas_recepcion,
            'negativas' => $request->negativas,
            'tipo' => $request->tipo,

            'last_pos' => $last_pos,
            'last_var' => $last_var,
            'total_pos' => $total_pos,
            'duration_back' => $duration_back,
        ]);
    }

    public function get_thead_acumulado(Request $request)
    {
        $hora_ini = date('Y-m-d H:i:s');

        $fechas_compra_flor = [];
        for ($i = 3; $i >= 1; $i--) {
            $fechas_compra_flor[] = opDiasFecha('+', $i, hoy());
        }

        $fechas_recepcion = [];
        for ($i = 0; $i <= 8; $i++) {
            $fechas_recepcion[] = opDiasFecha('-', $i, hoy());
        }

        $fechas_ventas = DB::table('import_pedido')
            ->select('fecha')->distinct()
            ->where('fecha', '>=', $request->desde)
            ->where('fecha', '<=', $request->hasta)
            ->where('estado', 1)
            ->orderBy('fecha')
            ->get()->pluck('fecha')->toArray();

        $hora_fin = date('Y-m-d H:i:s');
        $duration_back = difFechas($hora_fin, $hora_ini);
        return view('adminlte.gestion.postcocecha.inventario_compra_flor.partials.thead_acumulado', [
            'fechas_ventas' => $fechas_ventas,
            'fechas_compra_flor' => $fechas_compra_flor,
            'fechas_recepcion' => $fechas_recepcion,
            'negativas' => $request->negativas,
            'tipo' => $request->tipo,
            'duration_back' => $duration_back,
        ]);
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
        return view('adminlte.gestion.postcocecha.inventario_compra_flor.partials.detalle_ventas', [
            'total_inventario' => $request->total,
            'desde' => $request->desde,
            'hasta' => $request->hasta,
            'listado' => $listado,
            'variedad' => Variedad::find($request->variedad)
        ]);
    }

    public function confirmar_compra(Request $request)
    {
        DB::beginTransaction();
        try {
            $finca = getFincaActiva();
            $inventarios = DesgloseCompraFlor::where('id_empresa', $finca)
                ->where('id_variedad', $request->variedad)
                ->where('id_proveedor', $request->proveedor)
                ->where('longitud', $request->longitud)
                ->where('estado', 1)
                ->where('fecha', hoy())
                ->get();
            foreach ($inventarios as $i) {
                $i->estado = 0;
                $i->save();
            }

            $model = DesgloseRecepcion::where('estado', 1)
                ->where('factura', 0)
                ->where('id_variedad', $request->variedad)
                ->where('id_empresa', $finca)
                ->where('id_proveedor', $request->proveedor)
                ->where('fecha', hoy())
                ->where('longitud', $request->longitud)
                ->get()
                ->first();
            if ($model == '') {
                $model = new DesgloseRecepcion();
                $model->factura = 0;
                $model->id_variedad = $request->variedad;
                $model->id_empresa = $finca;
                $model->id_proveedor = $request->proveedor;
                $model->fecha = hoy();
                $model->tallos_x_malla = $request->cantidad;
                $model->disponibles = $request->cantidad;
                $model->longitud = $request->longitud;
                $model->save();
            } else {
                $model->tallos_x_malla += $request->cantidad;
                $model->disponibles += $request->cantidad;
                $model->save();
            }

            DB::commit();
            $success = true;
            $msg = 'Se han <strong>GRABADO</strong> los tallos correctamente';
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

    public function store_compra_parcial(Request $request)
    {
        DB::beginTransaction();
        try {
            $model = DesgloseRecepcion::where('estado', 1)
                ->where('factura', 0)
                ->where('id_variedad', $request->variedad)
                ->where('id_proveedor', $request->proveedor)
                ->where('fecha', hoy())
                ->where('longitud', $request->longitud)
                ->get()
                ->first();
            if ($model == '') {
                $model = new DesgloseRecepcion();
                $model->factura = 0;
                $model->id_variedad = $request->variedad;
                $model->id_proveedor = $request->proveedor;
                $model->fecha = hoy();
                $model->tallos_x_malla = $request->cantidad;
                $model->disponibles = $request->cantidad;
                $model->longitud = $request->longitud;
                $model->save();
            } else {
                $model->tallos_x_malla += $request->cantidad;
                $model->disponibles += $request->cantidad;
                $model->save();
            }

            $compra_parcial = new CompraParcialDiaria();
            $compra_parcial->id_variedad = $request->variedad;
            $compra_parcial->id_proveedor = $request->proveedor;
            $compra_parcial->longitud = $request->longitud;
            $compra_parcial->fecha = hoy();
            $compra_parcial->tallos = $request->cantidad;
            $compra_parcial->save();

            DB::commit();
            $success = true;
            $msg = 'Se han <strong>GRABADO</strong> los tallos correctamente';
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
        DB::beginTransaction();
        try {
            $finca = getFincaActiva();
            $model = DesgloseCompraFlor::where('id_empresa', $finca)
                ->where('id_variedad', $request->variedad)
                ->where('id_proveedor', $request->proveedor)
                ->where('longitud', $request->longitud)
                ->where('fecha', $request->fecha)
                ->where('estado', 1)
                ->get()
                ->first();
            if ($model != '') {
                $model->tallos_x_malla = $request->cantidad;
                $model->save();
            }

            DB::commit();
            $success = true;
            $msg = 'Se han <strong>GRABADO</strong> los tallos correctamente';
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

    public function prorrogar_compra(Request $request)
    {
        return view('adminlte.gestion.postcocecha.inventario_compra_flor.forms.prorrogar_compra', [
            'proveedor' => $request->proveedor,
            'variedad' => $request->variedad,
            'longitud' => $request->longitud,
        ]);
    }

    public function store_prorroga(Request $request)
    {
        DB::beginTransaction();
        try {
            $finca = getFincaActiva();
            $model = DesgloseCompraFlor::where('id_variedad', $request->variedad)
                ->where('estado', 1)
                ->where('id_empresa', $finca)
                ->where('id_proveedor', $request->proveedor)
                ->where('fecha', $request->fecha)
                ->where('longitud', $request->longitud)
                ->get()
                ->first();
            if ($model == '') {
                $model = new DesgloseCompraFlor();
                $model->id_variedad = $request->variedad;
                $model->id_empresa = $finca;
                $model->id_proveedor = $request->proveedor;
                $model->fecha = $request->fecha;
                $model->cantidad_mallas = 1;
                $model->tallos_x_malla = $request->cantidad;
                $model->longitud = $request->longitud;
                $model->save();
            } else {
                $model->tallos_x_malla += $request->cantidad;
                $model->save();
            }

            DB::commit();
            $success = true;
            $msg = 'Se han <strong>GRABADO</strong> los tallos correctamente';
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

    public function exportar_listado_compra_flor(Request $request)
    {
        $spread = new Spreadsheet();
        $this->excel_listado_compra_flor($spread, $request);

        $fileName = "InventarioCompraFlor.xlsx";
        $writer = new Xlsx($spread);

        //--------------------------- GUARDAR EL EXCEL -----------------------

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        $writer->save('php://output');

        //$writer->save('/var/www/html/Dasalflor/storage/storage/excel/excel_prueba.xlsx');
    }

    public function excel_listado_compra_flor($spread, $request)
    {
        $finca = getFincaActiva();

        $combinaciones = DB::table('desglose_compra_flor as i')
            ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->join('configuracion_empresa as f', 'f.id_configuracion_empresa', '=', 'i.id_proveedor')
            ->select(
                'i.longitud',
                'i.id_variedad',
                'i.id_proveedor',
                'v.nombre as variedad_nombre',
                'p.nombre as planta_nombre',
                'f.nombre as proveedor_nombre',
            )->distinct()
            ->where('i.fecha', '>=', hoy())
            ->where('i.estado', 1)
            ->where('i.id_empresa', $finca);
        if ($request->proveedor != '')
            $combinaciones = $combinaciones->where('i.id_proveedor', $request->proveedor);
        if ($request->planta != '')
            $combinaciones = $combinaciones->where('v.id_planta', $request->planta);
        $combinaciones = $combinaciones->orderBy('f.nombre')
            ->orderBy('p.nombre')
            ->orderBy('v.nombre')
            ->orderBy('i.longitud')
            ->get();

        $fechas = [];
        for ($i = 1; $i <= 3; $i++) {
            $fechas[] = opDiasFecha('+', $i, hoy());
        }

        $listado = [];
        foreach ($combinaciones as $item) {
            $valores = [];
            foreach ($fechas as $pos_f => $f) {
                $inventario = DB::table('desglose_compra_flor as i')
                    ->select(DB::raw('sum(i.tallos_x_malla) as cantidad'))
                    ->where('i.id_variedad', $item->id_variedad)
                    ->where('i.id_proveedor', $item->id_proveedor)
                    ->where('i.longitud', $item->longitud)
                    ->where('i.estado', 1)
                    ->where('i.id_empresa', $finca);
                if ($pos_f == count($fechas) - 1)
                    $inventario = $inventario->where('i.fecha', '>=', $f);
                else
                    $inventario = $inventario->where('i.fecha', $f);
                $inventario = $inventario->get()[0]->cantidad;
                $valores[] = $inventario;
            }

            $inventario_hoy = DB::table('desglose_compra_flor as i')
                ->select(DB::raw('sum(i.tallos_x_malla) as cantidad'))
                ->where('i.id_variedad', $item->id_variedad)
                ->where('i.id_proveedor', $item->id_proveedor)
                ->where('i.longitud', $item->longitud)
                ->where('i.id_empresa', $finca)
                ->where('i.fecha', hoy())
                ->where('i.estado', 1)
                ->get()[0]->cantidad;

            $listado[] = [
                'combinacion' => $item,
                'valores' => $valores,
                'inventario_hoy' => $inventario_hoy,
            ];
        }

        $columnas = getColumnasExcel();
        $sheet = $spread->getActiveSheet();
        $sheet->setTitle('Inventario Compra Flor');

        $row = 1;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Proveedor');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Planta');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Variedad');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Longitud');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Hoy');
        $totales_fechas = [];
        foreach ($fechas as $pos_f => $f) {
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, '-' . difFechas(hoy(), $f)->d);
            $totales_fechas[] = 0;
        }
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Saldo');

        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        foreach ($listado as $pos => $item) {
            $row++;
            $col = 0;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item['combinacion']->proveedor_nombre);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item['combinacion']->planta_nombre);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item['combinacion']->variedad_nombre);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item['combinacion']->longitud);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item['inventario_hoy']);

            $total_combinacion = 0;
            foreach ($item['valores'] as $pos_v => $v) {
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $v);
                $total_combinacion += $v;
                $totales_fechas[$pos_v] += $v;
            }
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_combinacion);
        }

        $row++;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Totales');
        $col += 4;
        $total = 0;
        foreach ($totales_fechas as $pos_v => $v) {
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $v);
            $total += $v;
        }
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total);

        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        setTextCenterToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);
        setBorderToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);

        for ($i = 0; $i <= $col; $i++)
            $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);
    }

    public function exportar_listado_compra_flor_acumulado(Request $request)
    {
        $spread = new Spreadsheet();
        $this->excel_listado_compra_flor_acumulado($spread, $request);

        $fileName = "InventarioCompraFlor.xlsx";
        $writer = new Xlsx($spread);

        //--------------------------- GUARDAR EL EXCEL -----------------------

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        $writer->save('php://output');

        //$writer->save('/var/www/html/Dasalflor/storage/storage/excel/excel_prueba.xlsx');
    }

    public function excel_listado_compra_flor_acumulado($spread, $request)
    {
        $finca = getFincaActiva();

        if (in_array($request->tipo, ['T', 'I'])) {
            $combinaciones_compra_flor = DB::table('desglose_compra_flor as i')
                ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
                ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                ->select(
                    'i.id_variedad',
                    'v.nombre as variedad_nombre',
                    'p.nombre as planta_nombre',
                )->distinct()
                ->where('i.id_empresa', $finca);
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
                )->distinct()
                ->where('i.disponibles', '>', 0)
                ->where('i.estado', 1)
                ->whereNotIn('i.id_variedad', $ids_variedad_compra_flor)
                ->where('i.id_empresa', $finca);
            if ($request->planta != '')
                $combinaciones_recepcion = $combinaciones_recepcion->where('v.id_planta', $request->planta);
            if ($request->variedad != '')
                $combinaciones_recepcion = $combinaciones_recepcion->where('i.id_variedad', $request->variedad);
            $combinaciones_recepcion = $combinaciones_recepcion->orderBy('p.nombre')
                ->orderBy('v.nombre')
                ->get();
            $ids_variedad_recepcion = $combinaciones_recepcion->pluck('id_variedad')->toArray();
        }

        if (in_array($request->tipo, ['T', 'V'])) {
            $combinaciones_pedido = DB::table('resumen_fechas as h')
                ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                ->select(
                    'h.id_variedad',
                    'v.nombre as variedad_nombre',
                    'p.nombre as planta_nombre',
                )->distinct()
                ->where('h.fecha', '>=', $request->desde)
                ->where('h.fecha', '<=', $request->hasta)
                ->where('h.tallos_venta', '>', 0);
            if ($request->planta != '')
                $combinaciones_pedido = $combinaciones_pedido->where('v.id_planta', $request->planta);
            if ($request->tipo == 'T')
                $combinaciones_pedido = $combinaciones_pedido->whereNotIn('h.id_variedad', $ids_variedad_compra_flor)
                    ->whereNotIn('h.id_variedad', $ids_variedad_recepcion);
            $combinaciones_pedido = $combinaciones_pedido->orderBy('p.nombre')
                ->orderBy('v.nombre')
                ->get();
        }

        if ($request->tipo == 'T')
            $combinaciones = $combinaciones_compra_flor->merge($combinaciones_pedido)->merge($combinaciones_recepcion);
        elseif ($request->tipo == 'V')
            $combinaciones = $combinaciones_pedido;
        elseif ($request->tipo == 'I')
            $combinaciones = $combinaciones_compra_flor->merge($combinaciones_recepcion);

        $fechas_compra_flor = [];
        for ($i = 3; $i >= 1; $i--) {
            $fechas_compra_flor[] = opDiasFecha('+', $i, hoy());
        }

        $fechas_recepcion = [];
        for ($i = 0; $i <= 8; $i++) {
            $fechas_recepcion[] = opDiasFecha('-', $i, hoy());
        }

        $fechas_ventas = DB::table('import_pedido')
            ->select('fecha')->distinct()
            ->where('fecha', '>=', $request->desde)
            ->where('fecha', '<=', $request->hasta)
            ->where('estado', 1)
            ->orderBy('fecha')
            ->get()->pluck('fecha')->toArray();

        $listado = [];
        foreach ($combinaciones as $item) {
            $model_variedad = Variedad::find($item->id_variedad);
            $total_inventario = 0;
            $valores_compra_flor = [];
            foreach ($fechas_compra_flor as $pos_f => $f) {
                $disponibles = DB::table('desglose_compra_flor as i')
                    ->select(DB::raw('sum(i.tallos_x_malla) as cantidad'))
                    ->where('i.id_variedad', $item->id_variedad)
                    ->where('i.id_empresa', $finca);
                if ($pos_f == 0)
                    $disponibles = $disponibles->where('i.fecha', '>=', $f);
                else
                    $disponibles = $disponibles->where('i.fecha', $f);
                $disponibles = $disponibles->get()[0]->cantidad;
                $valores_compra_flor[] = $disponibles;
                $total_inventario += $disponibles;
            }

            $valores_recepcion = [];
            foreach ($fechas_recepcion as $pos_f => $f) {
                $disponibles = DB::table('desglose_recepcion as i')
                    ->select(DB::raw('sum(i.disponibles) as cantidad'))
                    ->where('i.disponibles', '>', 0)
                    ->where('i.estado', 1)
                    ->where('i.id_variedad', $item->id_variedad)
                    ->where('i.id_empresa', $finca);
                if ($pos_f == count($fechas_recepcion) - 1)
                    $disponibles = $disponibles->where('i.fecha', '<=', $f);
                else
                    $disponibles = $disponibles->where('i.fecha', $f);
                $disponibles = $disponibles->get()[0]->cantidad;
                $valores_recepcion[] = $disponibles;
                $total_inventario += $disponibles;
            }

            $valores_ventas = [];
            $valores_armados = [];
            $valores_cambios = [];
            $valores_compras = [];
            $venta_total = 0;
            foreach ($fechas_ventas as $pos_f => $f) {
                $venta = getTallosVenta($item->id_variedad, $f);
                $venta_total += $venta;

                $armados = DB::table('orden_trabajo as o')
                    ->join('detalle_import_pedido as d', 'd.id_detalle_import_pedido', '=', 'o.id_detalle_import_pedido')
                    ->join('import_pedido as p', 'p.id_import_pedido', '=', 'd.id_import_pedido')
                    ->join('detalle_orden_trabajo as do', 'do.id_orden_trabajo', '=', 'o.id_orden_trabajo')
                    ->select(
                        DB::raw('sum(do.tallos) as cantidad'),
                    )
                    ->where('do.id_variedad', $item->id_variedad)
                    ->where('o.armado', 1)
                    ->where('p.fecha', $f)
                    ->where('p.estado', 1)
                    ->get()[0]->cantidad;
                $armados += DB::table('detalle_import_pedido as d')
                    ->join('import_pedido as p', 'p.id_import_pedido', '=', 'd.id_import_pedido')
                    ->join('distribucion_receta as r', 'r.id_detalle_import_pedido', '=', 'd.id_detalle_import_pedido')
                    ->select(
                        DB::raw('sum(d.ramos_armados * r.unidades) as cantidad'),
                    )
                    ->where('r.id_variedad', $item->id_variedad)
                    ->where('p.fecha', $f)
                    ->where('p.estado', 1)
                    ->get()[0]->cantidad;
                //$venta -= $armados;
                $valores_ventas[] = $venta;
                $valores_armados[] = $armados;

                $cambios_positivos = DB::table('pedido_modificacion as d')
                    ->select(
                        DB::raw('sum(tallos) as cantidad'),
                    )
                    ->where('operador', '+')
                    ->where('id_variedad', $item->id_variedad)
                    ->where('fecha_anterior', $f)
                    ->get()[0]->cantidad;
                $cambios_negativos = DB::table('pedido_modificacion as d')
                    ->select(
                        DB::raw('sum(tallos) as cantidad'),
                    )
                    ->where('operador', '-')
                    ->where('id_variedad', $item->id_variedad)
                    ->where('fecha_anterior', $f)
                    ->get()[0]->cantidad;
                $cambios = $cambios_positivos - $cambios_negativos;
                $valores_cambios[] = $cambios;

                if ($f > hoy())
                    $compra = DB::table('desglose_compra_flor as i')
                        ->select(DB::raw('sum(i.tallos_x_malla) as cantidad'))
                        ->where('i.id_variedad', $item->id_variedad)
                        ->where('i.id_empresa', $finca)
                        ->where('i.fecha', $f)
                        ->get()[0]->cantidad;
                else
                    $compra = 0;
                $valores_compras[] = $compra;
            }
            $armados = DB::table('orden_trabajo as o')
                ->join('detalle_orden_trabajo as do', 'do.id_orden_trabajo', '=', 'o.id_orden_trabajo')
                ->join('detalle_import_pedido as d', 'd.id_detalle_import_pedido', '=', 'o.id_detalle_import_pedido')
                ->join('import_pedido as p', 'p.id_import_pedido', '=', 'd.id_import_pedido')
                ->select(
                    DB::raw('sum(do.tallos) as cantidad'),
                )
                ->where('do.id_variedad', $item->id_variedad)
                ->where('o.armado', 1)
                ->where('p.fecha', '>=', $request->desde)
                ->where('p.fecha', '<=', $request->hasta)
                ->where('p.estado', 1)
                ->get()[0]->cantidad;
            $armados += DB::table('detalle_import_pedido as d')
                ->join('import_pedido as p', 'p.id_import_pedido', '=', 'd.id_import_pedido')
                ->join('distribucion_receta as r', 'r.id_detalle_import_pedido', '=', 'd.id_detalle_import_pedido')
                ->select(
                    DB::raw('sum(d.ramos_armados * r.unidades) as cantidad'),
                )
                ->where('r.id_variedad', $item->id_variedad)
                ->where('p.fecha', '>=', $request->desde)
                ->where('p.fecha', '<=', $request->hasta)
                ->where('p.estado', 1)
                ->get()[0]->cantidad;

            $agregar = false;
            if ($request->tipo == 'T') {
                if ($venta_total > 0 || $total_inventario > 0) {
                    if ($request->negativas) {
                        $total_combinacion = 0;
                        foreach ($valores_compra_flor as $v) {
                            $total_combinacion += $v;
                        }
                        foreach ($valores_recepcion as $pos_v => $v) {
                            $total_combinacion += $model_variedad->dias_rotacion_recepcion != '' && $model_variedad->dias_rotacion_recepcion <= $pos_v ? 0 : $v;
                        }
                        $necesidad = $total_combinacion - $venta_total + $armados;
                        if ($necesidad < 0)
                            $agregar = true;
                    } else
                        $agregar = true;
                }
            } else if ($request->tipo == 'I') {
                if ($total_inventario > 0)
                    $agregar = true;
            } else if ($request->tipo == 'V') {
                if ($venta_total > 0)
                    $agregar = true;
            }
            if ($agregar) {
                $listado[] = [
                    'combinacion' => $item,
                    'valores_recepcion' => $valores_recepcion,
                    'valores_compra_flor' => $valores_compra_flor,
                    'valores_ventas' => $valores_ventas,
                    'valores_armados' => $valores_armados,
                    'valores_cambios' => $valores_cambios,
                    'valores_compras' => $valores_compras,
                    'venta' => $venta_total,
                    'armados' => $armados,
                    'model_variedad' => $model_variedad,
                ];
            }
        }
        $columnas = getColumnasExcel();
        $sheet = $spread->getActiveSheet();
        $sheet->setTitle('Inventario Compra Flor');

        $row = 1;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Planta');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Variedad');
        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');
        $totales_fechas_compra_flor = [];
        $totales_fechas_recepcion = [];
        foreach ($fechas_compra_flor as $pos_f => $f) {
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, '-' . difFechas(hoy(), $f)->d);
            setBgToCeldaExcel($sheet, $columnas[$col] . $row, '5a7177');
            setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
            $totales_fechas_compra_flor[] = 0;
        }
        foreach ($fechas_recepcion as $pos_f => $f) {
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, difFechas(hoy(), $f)->d);
            setBgToCeldaExcel($sheet, $columnas[$col] . $row, '5a7177');
            setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
            $totales_fechas_recepcion[] = 0;
        }
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Compra');
        setBgToCeldaExcel($sheet, $columnas[$col] . $row, '5a7177');
        setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Recepcion');
        setBgToCeldaExcel($sheet, $columnas[$col] . $row, '5a7177');
        setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');

        if (count($fechas_ventas) > 1) {
            foreach ($fechas_ventas as $f) {
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Ventas:' . $f);
                setBgToCeldaExcel($sheet, $columnas[$col] . $row, '5a7177');
                setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Armados:' . $f);
                setBgToCeldaExcel($sheet, $columnas[$col] . $row, '5a7177');
                setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Inventario:' . $f);
                setBgToCeldaExcel($sheet, $columnas[$col] . $row, '5a7177');
                setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
            }
        }
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Ventas');
        setBgToCeldaExcel($sheet, $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Armados');
        setBgToCeldaExcel($sheet, $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Necesidad');
        setBgToCeldaExcel($sheet, $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');

        $total_ventas = 0;
        $total_armados = 0;
        $total_inv_compra_flor = 0;
        foreach ($listado as $pos_item => $item) {
            $total_combinacion = 0;
            foreach ($item['valores_compra_flor'] as $pos_v => $v)
                $total_combinacion += $v;
            foreach ($item['valores_recepcion'] as $pos_v => $v)
                $total_combinacion += $item['model_variedad']->dias_rotacion_recepcion != '' && $item['model_variedad']->dias_rotacion_recepcion <= $pos_v ? 0 : $v;
            $necesidad = $total_combinacion - $item['venta'] + $item['armados'];

            if (($request->negativas == true && $necesidad < 0) || $request->negativas == false) {
                $row++;
                $col = 0;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item['combinacion']->planta_nombre);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item['combinacion']->variedad_nombre);
                $total_compra_flor = 0;
                $total_recepcion = 0;
                foreach ($item['valores_compra_flor'] as $pos_v => $v) {
                    //$total_combinacion += $v;
                    $total_compra_flor += $v;
                    $total_inv_compra_flor += $v;
                    $totales_fechas_compra_flor[$pos_v] += $v;
                    $col++;
                    if ($v > 0)
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $v);
                }
                foreach ($item['valores_recepcion'] as $pos_v => $v) {
                    //$total_combinacion += $v;
                    $total_recepcion += $v;
                    $totales_fechas_recepcion[$pos_v] += $v;
                    $col++;
                    if ($v > 0)
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $v);
                }
                $total_ventas += $item['venta'];
                $total_armados += $item['armados'];
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_compra_flor);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_combinacion - $total_compra_flor);
                if (count($fechas_ventas) > 1) {
                    $ventas_acum = 0;
                    $armados_acum = 0;
                    $compras_acum = 0;
                    $necesidad_anterior = 0;
                    $valores_necesidades = [];
                    foreach ($item['valores_ventas'] as $pos_v => $v) {
                        $ventas_acum += $v;
                        $armados_acum += $item['valores_armados'][$pos_v];
                        $compras_acum += $item['valores_compras'][$pos_v];
                        $col++;
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $v);
                        $col++;
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item['valores_armados'][$pos_v]);
                        $col++;
                        $recepcion_disponible = 0;
                        foreach ($item['valores_recepcion'] as $pos_r => $r) {
                            if ($r > 0) {
                                $dias_disponible = $item['model_variedad']->dias_rotacion_recepcion - $pos_r;
                                $fecha_disponible = opDiasFecha('+', $dias_disponible, hoy());
                                if ($fecha_disponible > $fechas_ventas[$pos_v]) {
                                    $recepcion_disponible += $r;
                                }
                            }
                        }
                        $necesidad_fecha = $recepcion_disponible + $compras_acum - $ventas_acum + $armados_acum;
                        if ($necesidad_anterior < 0) {
                            $diferencia = $necesidad_fecha - $necesidad_anterior;
                        } else {
                            $diferencia = $necesidad_fecha;
                        }
                        if ($v > 0) {
                            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $diferencia);
                            if ($diferencia < 0)
                                setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ff0200');
                        }
                        $valores_necesidades[] = $diferencia;
                        $necesidad_anterior = $necesidad_fecha;
                    }
                }
                $col++;
                if ($item['venta'] > 0)
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item['venta']);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item['armados']);
                $necesidad_global = 0;
                for ($i = count($valores_necesidades) - 1; $i >= 0; $i--) {
                    if ($valores_necesidades[$i] <= 0) {
                        $necesidad_global += $valores_necesidades[$i];
                    } else {
                        break;
                    }
                }
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $necesidad_global);
                if ($necesidad_global < 0)
                    setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ff0200');
            }
        }

        setTextCenterToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);
        setBorderToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);

        for ($i = 0; $i <= $col; $i++)
            $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);
    }

    public function exportar_archivo_compras(Request $request)
    {
        $spread = new Spreadsheet();
        $this->excel_archivo_compras($spread, $request);

        $fileName = "Compras.xlsx";
        $writer = new Xlsx($spread);

        //--------------------------- GUARDAR EL EXCEL -----------------------

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        $writer->save('php://output');

        //$writer->save('/var/www/html/Dasalflor/storage/storage/excel/excel_prueba.xlsx');
    }

    public function excel_archivo_compras($spread, $request)
    {
        $finca = getFincaActiva();

        $combinaciones = DB::table('resumen_fechas as h')
            ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->select(
                'h.id_variedad',
                'v.nombre as variedad_nombre',
                'p.nombre as planta_nombre',
            )->distinct()
            ->where('h.fecha', '>=', $request->desde)
            ->where('h.fecha', '<=', $request->hasta)
            ->where('h.tallos_venta', '>', 0);
        if ($request->planta != '')
            $combinaciones = $combinaciones->where('v.id_planta', $request->planta);
        $combinaciones = $combinaciones->orderBy('p.nombre')
            ->orderBy('v.nombre')
            ->get();

        $fechas_compra_flor = [];
        for ($i = 3; $i >= 1; $i--) {
            $fechas_compra_flor[] = opDiasFecha('+', $i, hoy());
        }

        $fechas_recepcion = [];
        for ($i = 0; $i <= 8; $i++) {
            $fechas_recepcion[] = opDiasFecha('-', $i, hoy());
        }

        $fechas_ventas = DB::table('import_pedido')
            ->select('fecha')->distinct()
            ->where('fecha', '>=', $request->desde)
            ->where('fecha', '<=', $request->hasta)
            ->where('estado', 1)
            ->orderBy('fecha')
            ->get()->pluck('fecha')->toArray();

        $listado = [];
        foreach ($combinaciones as $item) {
            $total_inventario = 0;
            $valores_compra_flor = [];
            foreach ($fechas_compra_flor as $pos_f => $f) {
                $disponibles = DB::table('desglose_compra_flor as i')
                    ->select(DB::raw('sum(i.tallos_x_malla) as cantidad'))
                    ->where('i.id_variedad', $item->id_variedad)
                    ->where('i.id_empresa', $finca);
                if ($pos_f == 0)
                    $disponibles = $disponibles->where('i.fecha', '>=', $f);
                else
                    $disponibles = $disponibles->where('i.fecha', $f);
                $disponibles = $disponibles->get()[0]->cantidad;
                $valores_compra_flor[] = $disponibles;
                $total_inventario += $disponibles;
            }

            $valores_recepcion = [];
            foreach ($fechas_recepcion as $pos_f => $f) {
                $disponibles = DB::table('desglose_recepcion as i')
                    ->select(DB::raw('sum(i.disponibles) as cantidad'))
                    ->where('i.disponibles', '>', 0)
                    ->where('i.estado', 1)
                    ->where('i.id_variedad', $item->id_variedad)
                    ->where('i.id_empresa', $finca);
                if ($pos_f == count($fechas_recepcion) - 1)
                    $disponibles = $disponibles->where('i.fecha', '<=', $f);
                else
                    $disponibles = $disponibles->where('i.fecha', $f);
                $disponibles = $disponibles->get()[0]->cantidad;
                $valores_recepcion[] = $disponibles;
                $total_inventario += $disponibles;
            }

            $valores_ventas = [];
            $valores_armados = [];
            $valores_cambios = [];
            $valores_compras = [];
            $venta_total = 0;
            foreach ($fechas_ventas as $pos_f => $f) {
                $venta = getTallosVenta($item->id_variedad, $f);
                $venta_total += $venta;

                $armados = DB::table('orden_trabajo as o')
                    ->join('detalle_import_pedido as d', 'd.id_detalle_import_pedido', '=', 'o.id_detalle_import_pedido')
                    ->join('import_pedido as p', 'p.id_import_pedido', '=', 'd.id_import_pedido')
                    ->join('detalle_orden_trabajo as do', 'do.id_orden_trabajo', '=', 'o.id_orden_trabajo')
                    ->select(
                        DB::raw('sum(do.tallos) as cantidad'),
                    )
                    ->where('do.id_variedad', $item->id_variedad)
                    ->where('o.armado', 1)
                    ->where('p.fecha', $f)
                    ->where('p.estado', 1)
                    ->get()[0]->cantidad;
                $armados += DB::table('detalle_import_pedido as d')
                    ->join('import_pedido as p', 'p.id_import_pedido', '=', 'd.id_import_pedido')
                    ->join('distribucion_receta as r', 'r.id_detalle_import_pedido', '=', 'd.id_detalle_import_pedido')
                    ->select(
                        DB::raw('sum(d.ramos_armados * r.unidades) as cantidad'),
                    )
                    ->where('r.id_variedad', $item->id_variedad)
                    ->where('p.fecha', $f)
                    ->where('p.estado', 1)
                    ->get()[0]->cantidad;
                //$venta -= $armados;
                $valores_ventas[] = $venta;
                $valores_armados[] = $armados;

                $cambios_positivos = DB::table('pedido_modificacion as d')
                    ->select(
                        DB::raw('sum(tallos) as cantidad'),
                    )
                    ->where('operador', '+')
                    ->where('id_variedad', $item->id_variedad)
                    ->where('fecha_anterior', $f)
                    ->get()[0]->cantidad;
                $cambios_negativos = DB::table('pedido_modificacion as d')
                    ->select(
                        DB::raw('sum(tallos) as cantidad'),
                    )
                    ->where('operador', '-')
                    ->where('id_variedad', $item->id_variedad)
                    ->where('fecha_anterior', $f)
                    ->get()[0]->cantidad;
                $cambios = $cambios_positivos - $cambios_negativos;
                $valores_cambios[] = $cambios;

                if ($f > hoy())
                    $compra = DB::table('desglose_compra_flor as i')
                        ->select(DB::raw('sum(i.tallos_x_malla) as cantidad'))
                        ->where('i.id_variedad', $item->id_variedad)
                        ->where('i.id_empresa', $finca)
                        ->where('i.fecha', $f)
                        ->get()[0]->cantidad;
                else
                    $compra = 0;
                $valores_compras[] = $compra;
            }

            $armados = DB::table('orden_trabajo as o')
                ->join('detalle_orden_trabajo as do', 'do.id_orden_trabajo', '=', 'o.id_orden_trabajo')
                ->join('detalle_import_pedido as d', 'd.id_detalle_import_pedido', '=', 'o.id_detalle_import_pedido')
                ->join('import_pedido as p', 'p.id_import_pedido', '=', 'd.id_import_pedido')
                ->select(
                    DB::raw('sum(do.tallos) as cantidad'),
                )
                ->where('do.id_variedad', $item->id_variedad)
                ->where('o.armado', 1)
                ->where('p.fecha', '>=', $request->desde)
                ->where('p.fecha', '<=', $request->hasta)
                ->where('p.estado', 1)
                ->get()[0]->cantidad;
            $armados += DB::table('detalle_import_pedido as d')
                ->join('import_pedido as p', 'p.id_import_pedido', '=', 'd.id_import_pedido')
                ->join('distribucion_receta as r', 'r.id_detalle_import_pedido', '=', 'd.id_detalle_import_pedido')
                ->select(
                    DB::raw('sum(d.ramos_armados * r.unidades) as cantidad'),
                )
                ->where('r.id_variedad', $item->id_variedad)
                ->where('p.fecha', '>=', $request->desde)
                ->where('p.fecha', '<=', $request->hasta)
                ->where('p.estado', 1)
                ->get()[0]->cantidad;

            $model_variedad = Variedad::find($item->id_variedad);
            $listado[] = [
                'combinacion' => $item,
                'valores_recepcion' => $valores_recepcion,
                'valores_compra_flor' => $valores_compra_flor,
                'valores_ventas' => $valores_ventas,
                'valores_armados' => $valores_armados,
                'valores_cambios' => $valores_cambios,
                'valores_compras' => $valores_compras,
                'venta' => $venta_total,
                'armados' => $armados,
                'model_variedad' => $model_variedad,
            ];
        }

        $columnas = getColumnasExcel();
        $sheet = $spread->getActiveSheet();
        $sheet->setTitle('Inventario Compra Flor');

        $row = 1;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Planta');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Variedad');
        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');
        $totales_fechas_compra_flor = [];
        $totales_fechas_recepcion = [];
        foreach ($fechas_compra_flor as $pos_f => $f) {
            $totales_fechas_compra_flor[] = 0;
        }
        foreach ($fechas_recepcion as $pos_f => $f) {
            $totales_fechas_recepcion[] = 0;
        }

        if (count($fechas_ventas) > 1) {
            foreach ($fechas_ventas as $f) {
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $f);
                setBgToCeldaExcel($sheet, $columnas[$col] . $row, '5a7177');
                setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
            }
        }

        foreach ($listado as $pos_item => $item) {
            $total_combinacion = 0;
            $total_compra_flor = 0;
            $total_recepcion = 0;
            foreach ($item['valores_compra_flor'] as $pos_v => $v) {
                $total_combinacion += $v;
                $total_compra_flor += $v;
            }
            $total_recepcion = 0;
            foreach ($item['valores_recepcion'] as $pos_v => $v) {
                $total_combinacion += $item['model_variedad']->dias_rotacion_recepcion != '' && $item['model_variedad']->dias_rotacion_recepcion <= $pos_v ? 0 : $v;
                $total_recepcion += $item['model_variedad']->dias_rotacion_recepcion != '' && $item['model_variedad']->dias_rotacion_recepcion <= $pos_v ? 0 : $v;
            }
            $necesidad = $total_combinacion - $item['venta'] + $item['armados'];
            $mostrar = false;
            $ventas_acum = 0;
            $armados_acum = 0;
            $compras_acum = 0;
            $necesidad_anterior = 0;
            foreach ($item['valores_ventas'] as $pos_v => $v) {
                $ventas_acum += $v;
                $armados_acum += $item['valores_armados'][$pos_v];
                $compras_acum += $item['valores_compras'][$pos_v];

                $recepcion_disponible = 0;
                foreach ($item['valores_recepcion'] as $pos_r => $r) {
                    if ($r > 0) {
                        $dias_disponible = $item['model_variedad']->dias_rotacion_recepcion - $pos_r;
                        $fecha_disponible = opDiasFecha('+', $dias_disponible, hoy());
                        if ($fecha_disponible > $fechas_ventas[$pos_v]) {
                            $recepcion_disponible += $r;
                        }
                    }
                }
                $necesidad_fecha = $recepcion_disponible + $compras_acum - $ventas_acum + $armados_acum;
                if ($necesidad_anterior < 0) {
                    $diferencia = $necesidad_fecha - $necesidad_anterior;
                } else {
                    $diferencia = $necesidad_fecha;
                }
                $necesidad_anterior = $necesidad_fecha;
                if ($diferencia < 0 && $v > 0) {
                    $mostrar = true;
                }
            }

            if ($mostrar) {
                $row++;
                $col = 0;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item['combinacion']->planta_nombre);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item['combinacion']->variedad_nombre);
                $total_combinacion = 0;
                $total_compra_flor = 0;
                $total_recepcion = 0;
                foreach ($item['valores_compra_flor'] as $pos_v => $v) {
                    $total_combinacion += $v;
                    $total_compra_flor += $v;
                    $totales_fechas_compra_flor[$pos_v] += $v;
                }
                foreach ($item['valores_recepcion'] as $pos_v => $v) {
                    $total_combinacion += $item['model_variedad']->dias_rotacion_recepcion != '' && $item['model_variedad']->dias_rotacion_recepcion <= $pos_v ? 0 : $v;
                    $total_recepcion += $item['model_variedad']->dias_rotacion_recepcion != '' && $item['model_variedad']->dias_rotacion_recepcion <= $pos_v ? 0 : $v;
                    $totales_fechas_recepcion[$pos_v] += $v;
                }
                $necesidad = $total_combinacion - $item['venta'] + $item['armados'];
                if (count($fechas_ventas) > 1) {
                    $ventas_acum = 0;
                    $armados_acum = 0;
                    $compras_acum = 0;
                    $necesidad_anterior = 0;
                    foreach ($item['valores_ventas'] as $pos_v => $v) {
                        $ventas_acum += $v;
                        $armados_acum += $item['valores_armados'][$pos_v];
                        $compras_acum += $item['valores_compras'][$pos_v];
                        $col++;
                        $recepcion_disponible = 0;
                        foreach ($item['valores_recepcion'] as $pos_r => $r) {
                            if ($r > 0) {
                                $dias_disponible = $item['model_variedad']->dias_rotacion_recepcion - $pos_r;
                                $fecha_disponible = opDiasFecha('+', $dias_disponible, hoy());
                                if ($fecha_disponible > $fechas_ventas[$pos_v]) {
                                    $recepcion_disponible += $r;
                                }
                            }
                        }
                        $necesidad_fecha = $recepcion_disponible + $compras_acum - $ventas_acum + $armados_acum;
                        if ($necesidad_anterior < 0) {
                            $diferencia = $necesidad_fecha - $necesidad_anterior;
                        } else {
                            $diferencia = $necesidad_fecha;
                        }
                        $necesidad_anterior = $necesidad_fecha;
                        if ($diferencia < 0 && $v > 0) {
                            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $diferencia);
                            setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ff0200');
                        }
                    }
                }
            }
        }

        setTextCenterToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);
        setBorderToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);

        for ($i = 0; $i <= $col; $i++)
            $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);
    }

    public function refrescar_ventas(Request $request)
    {
        DB::beginTransaction();
        try {
            Artisan::call('resumen:fecha', [
                'fecha' => $request->fecha,
                'variedad' => $request->variedad,
                'dev' => 1,
            ]);
            $pedidos_actuales = DB::table('resumen_fechas')
                ->select(DB::raw('sum(tallos_venta) as cantidad'))
                ->where('fecha', $request->fecha)
                ->where('id_variedad', $request->variedad)
                ->get()[0]->cantidad;

            DB::commit();
            $success = true;
            $msg = 'Se han <strong>GRABADO</strong> los tallos correctamente';
        } catch (\Exception $e) {
            DB::rollBack();
            $success = false;
            $msg = '<div class="alert alert-danger text-center">' .
                '<p> Ha ocurrido un problema al guardar la informacion al sistema</p>' .
                '<p>' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine() . '</p>'
                . '</div>';
            $pedidos_actuales = 0;
        }

        return [
            'success' => $success,
            'mensaje' => $msg,
            'pedidos_actuales' => $pedidos_actuales,
        ];
    }

    public function refrescar_all_ventas(Request $request)
    {
        try {
            $pedidos_actuales = [];
            foreach (json_decode($request->data) as $var) {
                Artisan::call('resumen:fecha', [
                    'fecha' => $request->fecha,
                    'variedad' => $var,
                    'dev' => 1,
                ]);
                $venta = DB::table('resumen_fechas')
                    ->select(DB::raw('sum(tallos_venta) as cantidad'))
                    ->where('fecha', $request->fecha)
                    ->where('id_variedad', $var)
                    ->get()[0]->cantidad;
                $pedidos_actuales[] = [
                    'variedad' => $var,
                    'venta' => $venta
                ];
            }

            $success = true;
            $msg = 'Se han <strong>ACTUALIZADO</strong> los tallos correctamente';
        } catch (\Exception $e) {
            $success = false;
            $msg = '<div class="alert alert-danger text-center">' .
                '<p> Ha ocurrido un problema al guardar la informacion al sistema</p>' .
                '<p>' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine() . '</p>'
                . '</div>';
            $pedidos_actuales = [];
        }

        return [
            'success' => $success,
            'mensaje' => $msg,
            'pedidos_actuales' => $pedidos_actuales,
        ];
    }

    public function actualizar_variedad(Request $request)
    {
        $fechas_ventas = [];
        $f = $request->desde;
        while ($f <= $request->hasta) {
            $fechas_ventas[] = $f;
            $f = opDiasfecha('+', 1, $f);
        }

        $fechas_compra_flor = [];
        for ($i = 3; $i >= 1; $i--) {
            $fechas_compra_flor[] = opDiasFecha('+', $i, hoy());
        }

        $fechas_recepcion = [];
        for ($i = 0; $i <= 8; $i++) {
            $fechas_recepcion[] = opDiasFecha('-', $i, hoy());
        }

        $total_inventario = 0;
        $valores_compra_flor = [];
        foreach ($fechas_compra_flor as $pos_f => $f) {
            $disponibles = DB::table('desglose_compra_flor as i')
                ->select(DB::raw('sum(i.tallos_x_malla) as cantidad'))
                ->where('i.id_variedad', $request->variedad);
            if ($pos_f == 0)
                $disponibles = $disponibles->where('i.fecha', '>=', $f);
            else
                $disponibles = $disponibles->where('i.fecha', $f);
            $disponibles = $disponibles->get()[0]->cantidad;
            $valores_compra_flor[] = $disponibles;
            $total_inventario += $disponibles;
        }

        $valores_recepcion = [];
        foreach ($fechas_recepcion as $pos_f => $f) {
            $disponibles = DB::table('desglose_recepcion as i')
                ->select(DB::raw('sum(i.disponibles) as cantidad'))
                ->where('i.disponibles', '>', 0)
                ->where('i.estado', 1)
                ->where('i.id_variedad', $request->variedad);
            if ($pos_f == count($fechas_recepcion) - 1)
                $disponibles = $disponibles->where('i.fecha', '<=', $f);
            else
                $disponibles = $disponibles->where('i.fecha', $f);
            $disponibles = $disponibles->get()[0]->cantidad;
            $valores_recepcion[] = $disponibles;
            $total_inventario += $disponibles;
        }

        $valores_ventas = [];
        $valores_armados = [];
        $valores_compras = [];
        foreach ($fechas_ventas as $f) {
            Artisan::call('resumen:fecha', [
                'fecha' => $f,
                'variedad' => $request->variedad,
                'dev' => 1,
            ]);

            $venta = DB::table('resumen_fechas')
                ->select(DB::raw('sum(tallos_venta) as cantidad'))
                ->where('fecha', $f)
                ->where('id_variedad', $request->variedad)
                ->get()[0]->cantidad;

            $armados = DB::table('orden_trabajo as o')
                ->join('detalle_import_pedido as d', 'd.id_detalle_import_pedido', '=', 'o.id_detalle_import_pedido')
                ->join('import_pedido as p', 'p.id_import_pedido', '=', 'd.id_import_pedido')
                ->join('detalle_orden_trabajo as do', 'do.id_orden_trabajo', '=', 'o.id_orden_trabajo')
                ->select(
                    DB::raw('sum(do.tallos) as cantidad'),
                )
                ->where('do.id_variedad', $request->variedad)
                ->where('o.armado', 1)
                ->where('p.fecha', $f)
                ->where('p.estado', 1)
                ->get()[0]->cantidad;
            $armados += DB::table('detalle_import_pedido as d')
                ->join('import_pedido as p', 'p.id_import_pedido', '=', 'd.id_import_pedido')
                ->join('distribucion_receta as r', 'r.id_detalle_import_pedido', '=', 'd.id_detalle_import_pedido')
                ->select(
                    DB::raw('sum(d.ramos_armados * r.unidades) as cantidad'),
                )
                ->where('r.id_variedad', $request->variedad)
                ->where('p.fecha', $f)
                ->where('p.estado', 1)
                ->get()[0]->cantidad;
            //$venta -= $armados;
            $valores_ventas[] = $venta;
            $valores_armados[] = $armados;

            if ($f < hoy())
                $compra = DB::table('desglose_compra_flor as i')
                    ->select(DB::raw('sum(i.tallos_x_malla) as cantidad'))
                    ->where('i.id_variedad', $request->variedad)
                    ->where('i.fecha', $f)
                    ->get()[0]->cantidad;
            else
                $compra = 0;
            $valores_compras[] = $compra;
        }

        $venta = DB::table('resumen_fechas')
            ->select(DB::raw('sum(tallos_venta) as cantidad'))
            ->where('fecha', '>=', $request->desde)
            ->where('fecha', '<=', $request->hasta)
            ->where('id_variedad', $request->variedad)
            ->get()[0]->cantidad;

        $armados = DB::table('orden_trabajo as o')
            ->join('detalle_orden_trabajo as do', 'do.id_orden_trabajo', '=', 'o.id_orden_trabajo')
            ->join('detalle_import_pedido as d', 'd.id_detalle_import_pedido', '=', 'o.id_detalle_import_pedido')
            ->join('import_pedido as p', 'p.id_import_pedido', '=', 'd.id_import_pedido')
            ->select(
                DB::raw('sum(do.tallos) as cantidad'),
            )
            ->where('do.id_variedad', $request->variedad)
            ->where('o.armado', 1)
            ->where('p.fecha', '>=', $request->desde)
            ->where('p.fecha', '<=', $request->hasta)
            ->where('p.estado', 1)
            ->get()[0]->cantidad;
        $armados += DB::table('detalle_import_pedido as d')
            ->join('import_pedido as p', 'p.id_import_pedido', '=', 'd.id_import_pedido')
            ->join('distribucion_receta as r', 'r.id_detalle_import_pedido', '=', 'd.id_detalle_import_pedido')
            ->select(
                DB::raw('sum(d.ramos_armados * r.unidades) as cantidad'),
            )
            ->where('r.id_variedad', $request->variedad)
            ->where('p.fecha', '>=', $request->desde)
            ->where('p.fecha', '<=', $request->hasta)
            ->where('p.estado', 1)
            ->get()[0]->cantidad;

        $model_variedad = Variedad::find($request->variedad);
        $datos = [
            'valores_recepcion' => $valores_recepcion,
            'valores_compra_flor' => $valores_compra_flor,
            'valores_ventas' => $valores_ventas,
            'valores_armados' => $valores_armados,
            'valores_compras' => $valores_compras,
            'venta' => $venta,
            'armados' => $armados,
            'model_variedad' => $model_variedad,
            'fechas_compra_flor' => $fechas_compra_flor,
            'fechas_recepcion' => $fechas_recepcion,
            'fechas_ventas' => $fechas_ventas,
        ];

        /* registro correcciones */
        $model = new RegistroCorrecciones();
        $model->id_usuario = session('id_usuario');
        $model->fecha = hoy();
        $model->descripcion = 'Corregir tabla resumen_fechas para la variedad ' . $request->variedad . '(' . $model_variedad->nombre . ') en el rango de fechas ' . $request->desde . '<=>' . $request->hasta . ' desde el inventario de COMPRA_FLOR; boton DETALLE_VENTAS';
        $model->save();

        return view('adminlte.gestion.postcocecha.inventario_compra_flor.partials._row_acumulado', $datos);
    }
}
