<?php

namespace yura\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use yura\Modelos\ConfiguracionEmpresa;
use yura\Modelos\DesgloseRecepcion;
use yura\Modelos\Planta;
use yura\Modelos\SalidasRecepcion;
use yura\Modelos\Submenu;
use yura\Modelos\Variedad;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class InventarioCosechaController extends Controller
{
    public function inicio(Request $request)
    {
        $plantas = Planta::where('estado', 1)
            ->orderBy('nombre')
            ->get();
        $proveedores = ConfiguracionEmpresa::where('proveedor', 1)
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.postcocecha.inventario_cosecha.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'plantas' => $plantas,
            'proveedores' => $proveedores,
        ]);
    }

    public function listar_inventario_cosecha(Request $request)
    {
        $finca = getFincaActiva();

        $combinaciones = DB::table('desglose_recepcion as i')
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
            ->where('i.disponibles', '>', 0)
            ->where('i.estado', 1)
            ->where('i.id_empresa', $finca);
        if ($request->proveedor != '')
            $combinaciones = $combinaciones->where('i.id_proveedor', $request->proveedor);
        if ($request->planta != '')
            $combinaciones = $combinaciones->where('v.id_planta', $request->planta);
        if ($request->variedad != '')
            $combinaciones = $combinaciones->where('i.id_variedad', $request->variedad);
        $combinaciones = $combinaciones->orderBy('f.nombre')
            ->orderBy('p.nombre')
            ->orderBy('v.nombre')
            ->orderBy('i.longitud')
            ->get();

        $fechas = [];
        for ($i = 0; $i <= 7; $i++) {
            $fechas[] = opDiasFecha('-', $i, hoy());
        }

        $listado = [];
        foreach ($combinaciones as $item) {
            $valores = [];
            foreach ($fechas as $pos_f => $f) {
                $disponibles = DB::table('desglose_recepcion as i')
                    ->select(DB::raw('sum(i.disponibles) as cantidad'))
                    ->where('i.disponibles', '>', 0)
                    ->where('i.estado', 1)
                    ->where('i.id_variedad', $item->id_variedad)
                    ->where('i.id_proveedor', $item->id_proveedor)
                    ->where('i.longitud', $item->longitud)
                    ->where('i.id_empresa', $finca);
                if ($pos_f == count($fechas) - 1)
                    $disponibles = $disponibles->where('i.fecha', '<=', $f);
                else
                    $disponibles = $disponibles->where('i.fecha', $f);
                $disponibles = $disponibles->get()[0]->cantidad;
                $valores[] = $disponibles;
            }
            $listado[] = [
                'combinacion' => $item,
                'valores' => $valores,
            ];
        }
        return view('adminlte.gestion.postcocecha.inventario_cosecha.partials.listado', [
            'listado' => $listado,
            'fechas' => $fechas,
        ]);
    }

    public function listar_inventario_cosecha_acumulado(Request $request)
    {
        $finca = getFincaActiva();

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
            ->where('i.id_empresa', $finca);
        if ($request->planta != '')
            $combinaciones_recepcion = $combinaciones_recepcion->where('v.id_planta', $request->planta);
        if ($request->variedad != '')
            $combinaciones_recepcion = $combinaciones_recepcion->where('i.id_variedad', $request->variedad);
        $combinaciones_recepcion = $combinaciones_recepcion->orderBy('p.nombre')
            ->orderBy('v.nombre')
            ->get();
        $ids_variedad_recepcion = $combinaciones_recepcion->pluck('id_variedad')->toArray();

        $combinaciones_salidas = DB::table('salidas_recepcion as i')
            ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->select(
                'i.id_variedad',
                'v.nombre as variedad_nombre',
                'p.nombre as planta_nombre',
            )->distinct()
            //->where('i.disponibles', '>', 0)
            ->where('i.fecha', $request->fecha)
            ->whereNotIn('i.id_variedad', $ids_variedad_recepcion);
        if ($request->planta != '')
            $combinaciones_salidas = $combinaciones_salidas->where('v.id_planta', $request->planta);
        if ($request->variedad != '')
            $combinaciones_salidas = $combinaciones_salidas->where('i.id_variedad', $request->variedad);
        $combinaciones_salidas = $combinaciones_salidas->orderBy('p.nombre')
            ->orderBy('v.nombre')
            ->get();

        $combinaciones = $combinaciones_recepcion->merge($combinaciones_salidas);

        $fechas = [];
        for ($i = 0; $i <= 9; $i++) {
            $fechas[] = opDiasFecha('-', $i, hoy());
        }

        $listado = [];
        foreach ($combinaciones as $item) {
            $valores = [];
            foreach ($fechas as $pos_f => $f) {
                $disponibles = DB::table('desglose_recepcion as i')
                    ->select(
                        DB::raw('sum(i.tallos_x_malla) as cantidad'),
                        DB::raw('sum(i.disponibles) as disponibles')
                    )
                    ->where('i.disponibles', '>', 0)
                    ->where('i.estado', 1)
                    ->where('i.id_variedad', $item->id_variedad)
                    ->where('i.id_empresa', $finca);
                if ($pos_f == count($fechas) - 1)
                    $disponibles = $disponibles->where('i.fecha', '<=', $f);
                else
                    $disponibles = $disponibles->where('i.fecha', $f);
                $disponibles = $disponibles->get()[0];
                $valores[] = $disponibles;
            }

            $salidas = DB::table('salidas_recepcion')
                ->select(DB::raw('sum(cantidad) as cantidad'))
                ->where('fecha', $request->fecha_venta)
                ->where('id_variedad', $item->id_variedad)
                ->where('basura', 0)
                ->where('combos', 0)
                ->get()[0]->cantidad;
            $basura = DB::table('salidas_recepcion')
                ->select(DB::raw('sum(cantidad) as cantidad'))
                ->where('fecha', $request->fecha_venta)
                ->where('id_variedad', $item->id_variedad)
                ->where('basura', 1)
                ->get()[0]->cantidad;
            $combos = DB::table('salidas_recepcion')
                ->select(DB::raw('sum(cantidad) as cantidad'))
                ->where('fecha', $request->fecha_venta)
                ->where('id_variedad', $item->id_variedad)
                ->where('combos', 1)
                ->get()[0]->cantidad;

            $model_variedad = Variedad::find($item->id_variedad);
            $listado[] = [
                'combinacion' => $item,
                'valores' => $valores,
                'salidas' => $salidas,
                'basura' => $basura,
                'combos' => $combos,
                'model_variedad' => $model_variedad,
            ];
        }

        $usuarios = DB::table('permiso_accion')
            ->where('accion', 'SALIDAS_RECEPCION')
            ->get()
            ->pluck('id_usuario')
            ->toArray();
        //dump($listado);
        return view('adminlte.gestion.postcocecha.inventario_cosecha.partials.listado_acumulado', [
            'listado' => $listado,
            'fechas' => $fechas,
            'usuarios' => $usuarios,
        ]);
    }

    public function sacar_inventario(Request $request)
    {
        DB::beginTransaction();
        try {
            $inventarios = DesgloseRecepcion::where('estado', 1)
                ->where('disponibles', '>', 0)
                ->where('id_variedad', $request->variedad)
                ->orderBy('fecha', 'asc')
                ->get();

            $sacar = $request->sacar + $request->basura + $request->combos;
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
            if ($request->sacar > 0) {
                $model = new SalidasRecepcion();
                $model->id_variedad = $request->variedad;
                $model->fecha = $request->fecha;
                $model->cantidad = $request->sacar;
                $model->disponibles = $request->sacar;
                $model->basura = 0;
                $model->combos = 0;
                $model->save();
            }
            if ($request->basura > 0) {
                $model = new SalidasRecepcion();
                $model->id_variedad = $request->variedad;
                $model->fecha = $request->fecha;
                $model->cantidad = $request->basura;
                $model->disponibles = 0;
                $model->basura = 1;
                $model->save();
            }
            if ($request->combos > 0) {
                $model = new SalidasRecepcion();
                $model->id_variedad = $request->variedad;
                $model->fecha = $request->fecha;
                $model->cantidad = $request->combos;
                $model->disponibles = 0;
                $model->combos = 1;
                $model->save();
            }

            DB::commit();
            $success = true;
            $msg = 'Se han <strong>SACADO</strong> los tallos correctamente';
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

    public function sacar_all_inventario(Request $request)
    {
        DB::beginTransaction();
        try {
            foreach (json_decode($request->data) as $d) {
                $inventarios = DesgloseRecepcion::where('estado', 1)
                    ->where('disponibles', '>', 0)
                    ->where('id_variedad', $d->variedad)
                    ->orderBy('fecha', 'asc')
                    ->get();

                $sacar = $d->sacar + $d->basura + $d->combos;
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
                if ($d->sacar > 0) {
                    $model = new SalidasRecepcion();
                    $model->id_variedad = $d->variedad;
                    $model->fecha = $request->fecha;
                    $model->cantidad = $d->sacar;
                    $model->disponibles = $d->sacar;
                    $model->basura = 0;
                    $model->combos = 0;
                    $model->save();
                }
                if ($d->basura > 0) {
                    $model = new SalidasRecepcion();
                    $model->id_variedad = $d->variedad;
                    $model->fecha = $request->fecha;
                    $model->cantidad = $d->basura;
                    $model->disponibles = 0;
                    $model->basura = 1;
                    $model->save();
                }
                if ($d->combos > 0) {
                    $model = new SalidasRecepcion();
                    $model->id_variedad = $d->variedad;
                    $model->fecha = $request->fecha;
                    $model->cantidad = $d->combos;
                    $model->disponibles = 0;
                    $model->combos = 1;
                    $model->save();
                }
            }

            DB::commit();
            $success = true;
            $msg = 'Se han <strong>SACADO</strong> los tallos correctamente';
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

    public function detalle_ventas(Request $request)
    {
        $finca = getFincaActiva();
        $longitudes = DB::table('detalle_import_pedido as d')
            ->join('import_pedido as p', 'p.id_import_pedido', '=', 'd.id_import_pedido')
            ->join('distribucion_receta as r', 'r.id_detalle_import_pedido', '=', 'd.id_detalle_import_pedido')
            ->select('d.longitud')->distinct()
            ->where('p.fecha', $request->fecha)
            ->where('r.id_variedad', $request->variedad)
            ->where('p.id_empresa', $finca)
            ->orderBy('d.longitud')
            ->get()->pluck('longitud')->toArray();

        $pedidos = DB::table('detalle_import_pedido as d')
            ->join('import_pedido as p', 'p.id_import_pedido', '=', 'd.id_import_pedido')
            ->join('distribucion_receta as r', 'r.id_detalle_import_pedido', '=', 'd.id_detalle_import_pedido')
            ->join('detalle_cliente as dc', 'dc.id_cliente', '=', 'p.id_cliente')
            ->select('p.id_import_pedido', 'p.codigo', 'p.id_cliente', 'dc.nombre as nombre_cliente')->distinct()
            ->where('p.fecha', $request->fecha)
            ->where('r.id_variedad', $request->variedad)
            ->where('p.id_empresa', $finca)
            ->where('dc.estado', 1)
            ->orderBy('dc.nombre')
            ->get();

        $listado = [];
        foreach ($pedidos as $p) {
            $detalles = DB::table('detalle_import_pedido as d')
                ->join('variedad as v', 'v.id_variedad', '=', 'd.id_variedad')
                ->join('import_pedido as p', 'p.id_import_pedido', '=', 'd.id_import_pedido')
                ->join('distribucion_receta as r', 'r.id_detalle_import_pedido', '=', 'd.id_detalle_import_pedido')
                ->select('d.id_detalle_import_pedido', 'd.id_variedad', 'v.nombre as nombre_variedad')->distinct()
                ->where('r.id_variedad', $request->variedad)
                ->where('d.id_import_pedido', $p->id_import_pedido)
                ->orderBy('v.nombre')
                ->get();
            $valores_detalle = [];
            $total_pedido = 0;
            foreach ($detalles as $det) {
                $valores_longitud = [];
                foreach ($longitudes as $long) {
                    $venta = DB::table('import_pedido as p')
                        ->join('detalle_import_pedido as d', 'd.id_import_pedido', '=', 'p.id_import_pedido')
                        ->join('distribucion_receta as r', 'r.id_detalle_import_pedido', '=', 'd.id_detalle_import_pedido')
                        ->select(DB::raw('sum(d.ramos * d.caja * r.unidades) as cantidad'))
                        ->where('d.id_detalle_import_pedido', $det->id_detalle_import_pedido)
                        ->where('r.id_variedad', $request->variedad)
                        ->where('d.longitud', $long)
                        ->get()[0]->cantidad;
                    $valores_longitud[] = $venta;
                    $total_pedido += $venta;
                }
                $valores_detalle[] = [
                    'detalle' => $det,
                    'valores_longitud' => $valores_longitud
                ];
            }
            $listado[] = [
                'pedido' => $p,
                'total_pedido' => $total_pedido,
                'valores_detalle' => $valores_detalle
            ];
        }
        return view('adminlte.gestion.postcocecha.inventario_cosecha.partials.detalle_ventas', [
            'listado' => $listado,
            'longitudes' => $longitudes,
            'variedad' => Variedad::find($request->variedad)
        ]);
    }

    public function store_devolucion(Request $request)
    {
        DB::beginTransaction();
        try {
            $inventario = DesgloseRecepcion::where('estado', 1)
                ->where('id_variedad', $request->variedad)
                ->orderBy('fecha', 'desc')
                ->first();

            $inventario->disponibles += $request->sacar + $request->basura;
            $inventario->save();

            if ($request->sacar > 0) {
                $model = new SalidasRecepcion();
                $model->id_variedad = $request->variedad;
                $model->fecha = $request->fecha;
                $model->cantidad = -1 * $request->sacar;
                $model->disponibles = -1 * $request->sacar;
                $model->basura = 0;
                $model->save();
            }
            if ($request->basura > 0) {
                $model = new SalidasRecepcion();
                $model->id_variedad = $request->variedad;
                $model->fecha = $request->fecha;
                $model->cantidad = -1 * $request->basura;
                $model->disponibles = 0;
                $model->basura = 1;
                $model->save();
            }

            DB::commit();
            $success = true;
            $msg = 'Se han <strong>DEVUELTO</strong> los tallos correctamente';
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

    public function exportar_listado_acumulado(Request $request)
    {
        $spread = new Spreadsheet();
        $this->excel_listado_acumulado($spread, $request);

        $fileName = "InventarioRecepcion.xlsx";
        $writer = new Xlsx($spread);

        //--------------------------- GUARDAR EL EXCEL -----------------------

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        $writer->save('php://output');

        //$writer->save('/var/www/html/Dasalflor/storage/storage/excel/excel_prueba.xlsx');
    }

    public function excel_listado_acumulado($spread, $request)
    {
        $finca = getFincaActiva();

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
            ->where('i.id_empresa', $finca);
        if ($request->planta != '')
            $combinaciones_recepcion = $combinaciones_recepcion->where('v.id_planta', $request->planta);
        if ($request->variedad != '')
            $combinaciones_recepcion = $combinaciones_recepcion->where('i.id_variedad', $request->variedad);
        $combinaciones_recepcion = $combinaciones_recepcion->orderBy('p.nombre')
            ->orderBy('v.nombre')
            ->get();
        $ids_variedad_recepcion = $combinaciones_recepcion->pluck('id_variedad')->toArray();

        $combinaciones_salidas = DB::table('salidas_recepcion as i')
            ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->select(
                'i.id_variedad',
                'v.nombre as variedad_nombre',
                'p.nombre as planta_nombre',
            )->distinct()
            ->where('i.disponibles', '>', 0)
            ->whereNotIn('i.id_variedad', $ids_variedad_recepcion);
        if ($request->planta != '')
            $combinaciones_salidas = $combinaciones_salidas->where('v.id_planta', $request->planta);
        if ($request->variedad != '')
            $combinaciones_salidas = $combinaciones_salidas->where('i.id_variedad', $request->variedad);
        $combinaciones_salidas = $combinaciones_salidas->orderBy('p.nombre')
            ->orderBy('v.nombre')
            ->get();

        $combinaciones = $combinaciones_recepcion->merge($combinaciones_salidas);

        $fechas = [];
        for ($i = 0; $i <= 7; $i++) {
            $fechas[] = opDiasFecha('-', $i, hoy());
        }

        $listado = [];
        foreach ($combinaciones as $item) {
            $valores = [];
            foreach ($fechas as $pos_f => $f) {
                $disponibles = DB::table('desglose_recepcion as i')
                    ->select(
                        DB::raw('sum(i.tallos_x_malla) as cantidad'),
                        DB::raw('sum(i.disponibles) as disponibles')
                    )
                    ->where('i.disponibles', '>', 0)
                    ->where('i.estado', 1)
                    ->where('i.id_variedad', $item->id_variedad)
                    ->where('i.id_empresa', $finca);
                if ($pos_f == count($fechas) - 1)
                    $disponibles = $disponibles->where('i.fecha', '<=', $f);
                else
                    $disponibles = $disponibles->where('i.fecha', $f);
                $disponibles = $disponibles->get()[0];
                $valores[] = $disponibles;
            }

            $salidas = DB::table('salidas_recepcion')
                ->select(DB::raw('sum(cantidad) as cantidad'))
                ->where('fecha', $request->fecha_venta)
                ->where('id_variedad', $item->id_variedad)
                ->where('basura', 0)
                ->where('combos', 0)
                ->get()[0]->cantidad;
            $basura = DB::table('salidas_recepcion')
                ->select(DB::raw('sum(cantidad) as cantidad'))
                ->where('fecha', $request->fecha_venta)
                ->where('id_variedad', $item->id_variedad)
                ->where('basura', 1)
                ->get()[0]->cantidad;
            $combos = DB::table('salidas_recepcion')
                ->select(DB::raw('sum(cantidad) as cantidad'))
                ->where('fecha', $request->fecha_venta)
                ->where('id_variedad', $item->id_variedad)
                ->where('combos', 1)
                ->get()[0]->cantidad;

            $listado[] = [
                'combinacion' => $item,
                'valores' => $valores,
                'salidas' => $salidas,
                'basura' => $basura,
                'combos' => $combos,
            ];
        }

        $columnas = getColumnasExcel();
        $sheet = $spread->getActiveSheet();
        $sheet->setTitle('Inventario Recepcion');

        $row = 1;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Planta');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Variedad');
        $totales_fechas = [];
        foreach ($fechas as $pos_f => $f) {
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, difFechas(hoy(), $f)->d);
            $totales_fechas[] = 0;
        }
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Inventario');

        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        $total_salidas = 0;
        $total_basura = 0;
        $total_combos = 0;

        foreach ($listado as $item) {
            $row++;
            $col = 0;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item['combinacion']->planta_nombre);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item['combinacion']->variedad_nombre);

            $total_combinacion = 0;
            foreach ($item['valores'] as $pos_v => $v) {
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $v->disponibles);
                $total_combinacion += $v->disponibles;
                $totales_fechas[$pos_v] += $v->disponibles;
            }
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_combinacion);
        }

        $row++;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Totales');
        $col++;
        $total_inventario = 0;
        foreach ($totales_fechas as $pos_v => $v) {
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $v);
            $total_inventario += $v;
        }
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_inventario);

        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        setTextCenterToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);
        setBorderToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);

        for ($i = 0; $i <= $col; $i++)
            $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);
    }
}
