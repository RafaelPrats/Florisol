<?php

namespace yura\Http\Controllers\Comercializacion;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use yura\Http\Controllers\Controller;
use yura\Modelos\PedidoModificacion;
use yura\Modelos\Submenu;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Validator;
use Storage as Almacenamiento;
use \PhpOffice\PhpSpreadsheet\IOFactory as IOFactory;
use yura\Jobs\jobDistribucionReceta;
use yura\Jobs\jobUpdateResumenAgrogana;
use yura\Jobs\jobUpdateVariedadByFecha;
use yura\Jobs\jobUpdateVariedades;
use yura\Modelos\DetalleCliente;
use yura\Modelos\DetalleImportPedido;
use yura\Modelos\DetalleReceta;
use yura\Modelos\DistribucionReceta;
use yura\Modelos\ImportPedido;
use yura\Modelos\ItemsImportPedido;
use yura\Modelos\Planta;
use yura\Modelos\ProcesoQueue;
use yura\Modelos\Variedad;
use yura\Modelos\Cliente;

class ImportarPedidosController extends Controller
{
    public function inicio(Request $request)
    {
        $clientes = DB::table('cliente as c')
            ->join('detalle_cliente as dc', 'c.id_cliente', '=', 'dc.id_cliente')
            ->select('dc.nombre', 'c.id_cliente')->distinct()
            ->where('dc.estado', 1)
            ->orderBy('dc.nombre', 'asc')
            ->get();

        $fincas = DB::table('configuracion_empresa')
            ->where('proveedor', 0)
            ->orderBy('nombre')
            ->get();

        return view('adminlte.gestion.comercializacion.importar_pedidos.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'clientes' => $clientes,
            'fincas' => $fincas
        ]);
    }

    public function listar_reporte(Request $request)
    {
        $finca = getFincaActiva();
        $listado = ImportPedido::join('detalle_cliente as dc', 'dc.id_cliente', '=', 'import_pedido.id_cliente')
            ->select('import_pedido.*')->distinct()
            ->where('import_pedido.estado', 1)
            ->where('dc.estado', 1)
            ->where('import_pedido.id_empresa', $finca)
            ->where('import_pedido.fecha', $request->fecha);
        if ($request->cliente != '')
            $listado = $listado->where('import_pedido.id_cliente', $request->cliente);
        $listado = $listado->orderBy('dc.nombre')
            ->get();
        return view('adminlte.gestion.comercializacion.importar_pedidos.partials.listado', [
            'listado' => $listado
        ]);
    }

    public function add_pedido(Request $request)
    {
        return view('adminlte.gestion.comercializacion.importar_pedidos.forms.add_pedido', []);
    }

    public function descargar_plantilla(Request $request)
    {
        $fileName = basename('pedidos.xlsx');
        $filePath = public_path('storage/plantillas/' . $fileName);
        if (!empty($fileName) && file_exists($filePath)) {
            // Define headers
            header("Cache-Control: public");
            header("Content-Description: File Transfer");
            header("Content-Disposition: attachment; filename=$fileName");
            header("Content-Type: application/zip");
            header("Content-Transfer-Encoding: binary");

            // Read the file
            readfile($filePath);
            exit;
        }
    }

    public function post_importar_pedidos(Request $request)
    {
        ini_set('max_execution_time', env('MAX_EXECUTION_TIME'));
        $valida = Validator::make($request->all(), [
            'file_pedidos' => 'required',
        ]);
        $msg = '<div class="alert alert-success text-center">Se ha importado el archivo. Revise su contenido antes de grabar.</div>';
        $success = true;
        if (!$valida->fails()) {
            try {
                $archivo = $request->file_pedidos;
                $extension = $archivo->getClientOriginalExtension();
                $nombre_archivo = "upload_pedidos." . $extension;
                $r1 = Almacenamiento::disk('file_loads')->put($nombre_archivo, \File::get($archivo));
            } catch (\Exception $e) {
                if (strpos($e->getMessage(), 'DOMDocument::loadHTML(): Invalid char in CDATA') !== false)
                    $mensaje_error = 'Problema con el archivo excel';
                else
                    $mensaje_error = $e->getMessage();
                return [
                    'mensaje' => '<div class="alert alert-danger text-center">' .
                        '<p>¡Ha ocurrido un problema al subir el archivo, contacte al administrador del sistema!</p>' .
                        '<legend style="font-size: 0.9em; color: white; margin-bottom: 2px">mensaje de error</legend>' .
                        $mensaje_error .
                        '</div>',
                    'success' => false
                ];
            }
        } else {
            $errores = '';
            foreach ($valida->errors()->all() as $mi_error) {
                if ($errores == '') {
                    $errores = '<li>' . $mi_error . '</li>';
                } else {
                    $errores .= '<li>' . $mi_error . '</li>';
                }
            }
            $success = false;
            $msg = '<div class="alert alert-danger">' .
                '<p class="text-center">¡Por favor corrija los siguientes errores!</p>' .
                '<ul>' .
                $errores .
                '</ul>' .
                '</div>';
        }
        return [
            'mensaje' => $msg,
            'success' => $success,
        ];
    }

    public function get_importar_pedidos(Request $request)
    {
        try {
            $url = public_path('storage/file_loads/upload_pedidos.xlsx');
            $document = IOFactory::load($url);
            $sheet = $document->getActiveSheet()->toArray(null, true, true, true);
            $listado = [];
            $fallos = false;
            $cajas_anterior = $sheet[8]['J'];
            foreach ($sheet as $pos => $row) {
                if ($pos > 7 && $row['B'] != '') {
                    $cliente = DetalleCliente::where('nombre', espacios(mb_strtoupper($row['D'])))
                        ->where('estado', 1)
                        ->get()
                        ->first();
                    $variedad = Variedad::where('nombre', espacios(mb_strtoupper($row['L'])))
                        ->where('siglas', espacios(mb_strtoupper($row['M'])))
                        ->where('id_planta', 128)
                        ->where('estado', 1)
                        ->get()
                        ->first();
                    if ($cliente == '' || $variedad == '' || $row['G'] == '')
                        $fallos = true;
                    $row_fecha = explode(' ', $row['B'])[0];
                    /*$mes = explode('/', $row_fecha)[1];
                    $mes = strlen($mes) == 2 ? $mes : '0' . $mes;
                    $dia = explode('/', $row_fecha)[0];
                    $dia = strlen($dia) == 2 ? $dia : '0' . $dia;
                    $anno = '20' . explode('/', $row_fecha)[2];
                    $fecha = $anno . '-' . $mes . '-' . $dia;*/

                    /* VALIDAR ORDEN FIJA */
                    $error_orden = false;
                    if($row['A'] == ''){
                        $existe_orden_fija = ImportPedido::where('codigo_ref', intVal($row['G']))
                            ->where('orden_fija', 1)
                            ->get()
                            ->first();
                            if($existe_orden_fija != '')
                                $error_orden = true;
                    }

                    $cajas_anterior = $row['J'] != '' ? $row['J'] : $cajas_anterior;
                    $pos_en_listado = -1;
                    foreach ($listado as $pos => $r) {
                        if ($r['pedido'] == $row['G'] && espacios($r['row']['M']) == espacios($row['M']) && $r['longitud'] == $row['N']) {
                            $pos_en_listado = $pos;
                        }
                    }
                    if ($pos_en_listado != -1) {
                        $listado[$pos_en_listado]['ramos'] += $cajas_anterior * $row['P'];
                    } else {
                        $listado[] = [
                            'row' => $row,
                            'pedido' => $row['F'],
                            'codigo_ref' => $row['G'],
                            'cajas' => 1,
                            'longitud' => $row['N'],
                            'ramos' => $cajas_anterior * $row['P'],
                            'cliente' => $cliente,
                            'fecha' => $request->fecha,
                            'variedad' => $variedad,
                            'error_orden' => $error_orden,
                        ];
                    }
                }
            }
        } catch (\Exception $e) {
            return '<div class="alert alert-danger text-center">' .
                '<p> Ha ocurrido un problema con el contenido del archivo. Pongase en contacto con el administrador del sistema</p>' .
                '<p>' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine() . '</p>'
                . '</div>';
        }
        return view('adminlte.gestion.comercializacion.importar_pedidos.forms.importar_proyectos', [
            'listado' => $listado,
            'fallos' => $fallos,
        ]);
    }

    public function store_importar_pedidos(Request $request)
    {
        DB::beginTransaction();
        try {
            $target_resumen = [];
            $finca = getFincaActiva();
            $fechas_archivo = [];
            $codigos_archivo = [];
            $ids_pedido_archivo = [];
            $pedidos_existentes = [];
            $resumen_existentes = [];
            $pedidos_nuevos = [];
            foreach (json_decode($request->data) as $pos => $d) {
                if ($d->es_orden_fija == 0) {   // open market
                    $orden_fija = 0;
                    $pedido = ImportPedido::where('codigo', $d->codigo_ref)
                        ->get()
                        ->first();
                } else {    // orden fija
                    $orden_fija = 1;
                    $pedido = ImportPedido::where('codigo', $d->codigo_ref)
                        ->where('fecha', $d->fecha)
                        ->get()
                        ->first();
                }
                $oldFecha = '';
                $newFecha = '';
                if ($pedido == '') {
                    $pedido = new ImportPedido();
                    $pedido->id_cliente = $d->cliente;
                    $pedido->id_empresa = $finca;
                    $pedido->codigo = $d->codigo_ref;
                    $pedido->codigo_ref = $d->codigo_ref;
                    $pedido->fecha = $d->fecha;
                    $pedido->orden_fija = $orden_fija;
                    $pedido->save();
                    $pedido->id_import_pedido = DB::table('import_pedido')
                        ->select(DB::raw('max(id_import_pedido) as id'))
                        ->get()[0]->id;
                    $pedidos_nuevos[] = $pedido->id_import_pedido;
                } else {
                    if (!in_array($pedido->id_import_pedido, $pedidos_existentes) && !in_array($pedido->id_import_pedido, $pedidos_nuevos)) {
                        $oldFecha = $pedido->fecha;
                        $newFecha = $d->fecha;
                        $pedido->fecha = $d->fecha;
                        $pedido->estado = 1;
                        $pedido->save();

                        $pedidos_existentes[] = $pedido->id_import_pedido;
                        $resumen_existentes[] = [
                            'pedido' => $pedido->id_import_pedido,
                            'detalles' => []
                        ];
                    }
                }
                $codigos_archivo[] = $pedido->codigo;
                if (!in_array($pedido->fecha, $fechas_archivo))
                    $fechas_archivo[] = $pedido->fecha;

                if (in_array($pedido->id_import_pedido, $pedidos_nuevos)) { // es un pedido nuevo
                    /* AGREGAR NUEVO DETALLE */
                    $detalle = new DetalleImportPedido();
                    $detalle->id_import_pedido = $pedido->id_import_pedido;
                    $detalle->id_variedad = $d->variedad;
                    $detalle->caja = $d->cajas;
                    $detalle->longitud = $d->longitud;
                    $detalle->ramos = $d->ramos;
                    $detalle->save();

                    /* GUARDAR CAMBIOS */
                    $detalles_receta = DB::table('detalle_receta')
                        ->where('id_variedad', $d->variedad)
                        ->where('defecto', 1)
                        ->get();
                    if ($d->fecha >= hoy() && $d->fecha <= opDiasFecha('+', 4, hoy())) {
                        foreach ($detalles_receta as $item) {
                            $model = new PedidoModificacion();
                            $model->id_cliente = $pedido->id_cliente;
                            $model->fecha_anterior = $d->fecha;
                            $model->fecha_nueva = $d->fecha;
                            $model->tallos = $d->ramos * $item->unidades;
                            $model->operador = '+';
                            $model->id_usuario = session('id_usuario');
                            $model->id_variedad = $item->id_item;
                            $model->longitud = $d->longitud;
                            $model->save();
                        }
                    }

                    foreach ($detalles_receta as $item) {
                        if (!in_array([
                            'fecha' => $d->fecha,
                            'variedad' => $item->id_item,
                        ], $target_resumen))
                            $target_resumen[] = [
                                'fecha' => $d->fecha,
                                'variedad' => $item->id_item,
                            ];
                    }
                }

                if (in_array($pedido->id_import_pedido, $pedidos_existentes)) { // es un pedido existente
                    /* BUSCAR si EXISTE el DETALLE */
                    $detalle = DetalleImportPedido::where('id_import_pedido', $pedido->id_import_pedido)
                        ->where('id_variedad', $d->variedad)
                        ->where('longitud', $d->longitud)
                        ->get()
                        ->first();
                    if ($detalle == '') {   // es un nuevo detalle
                        $detalle = new DetalleImportPedido();
                        $detalle->id_import_pedido = $pedido->id_import_pedido;
                        $detalle->id_variedad = $d->variedad;
                        $detalle->caja = $d->cajas;
                        $detalle->longitud = $d->longitud;
                        $detalle->ramos = $d->ramos;
                        $detalle->save();
                        $detalle->id_detalle_import_pedido = DB::table('detalle_import_pedido')
                            ->select(DB::raw('max(id_detalle_import_pedido) as id'))
                            ->get()[0]->id;

                        /* GUARDAR CAMBIOS */
                        $detalles_receta = DB::table('detalle_receta')
                            ->where('id_variedad', $d->variedad)
                            ->where('defecto', 1)
                            ->get();
                        if ($d->fecha >= hoy() && $d->fecha <= opDiasFecha('+', 4, hoy())) {
                            foreach ($detalles_receta as $item) {
                                $model = new PedidoModificacion();
                                $model->id_cliente = $pedido->id_cliente;
                                $model->fecha_anterior = $d->fecha;
                                $model->fecha_nueva = $d->fecha;
                                $model->tallos = $d->ramos * $item->unidades;
                                $model->operador = '+';
                                $model->id_usuario = session('id_usuario');
                                $model->id_variedad = $item->id_item;
                                $model->longitud = $d->longitud;
                                $model->save();
                            }
                        }

                        foreach ($detalles_receta as $item) {
                            if (!in_array([
                                'fecha' => $d->fecha,
                                'variedad' => $item->id_item,
                            ], $target_resumen))
                                $target_resumen[] = [
                                    'fecha' => $d->fecha,
                                    'variedad' => $item->id_item,
                                ];
                        }


                        /* guardar detalle en array de existentes */
                        foreach ($resumen_existentes as $pos_r => $r) {
                            if ($r['pedido'] == $pedido->id_import_pedido)
                                $r['detalles'][] = $detalle->id_detalle_import_pedido;
                            $resumen_existentes[$pos_r] = $r;
                        }
                    } else {    // es un detalle existente
                        /* GUARDAR CAMBIOS */
                        if ($oldFecha != $newFecha) { //cambio de fecha
                            $detalles_receta = DB::table('detalle_receta')
                                ->where('id_variedad', $d->variedad)
                                ->where('defecto', 1)
                                ->get();
                            if ($oldFecha >= hoy() && $oldFecha <= opDiasFecha('+', 4, hoy())) {
                                foreach ($detalles_receta as $item) {
                                    $model = new PedidoModificacion();
                                    $model->id_cliente = $pedido->id_cliente;
                                    $model->fecha_anterior = $oldFecha;
                                    $model->fecha_nueva = $newFecha;
                                    $model->tallos = $detalle->ramos * $item->unidades;
                                    $model->operador = '-';
                                    $model->id_usuario = session('id_usuario');
                                    $model->id_variedad = $item->id_item;
                                    $model->longitud = $detalle->longitud;
                                    $model->cambio_fecha = 1;
                                    $model->save();
                                }
                            }
                            if ($newFecha >= hoy() && $newFecha <= opDiasFecha('+', 4, hoy())) {
                                foreach ($detalles_receta as $item) {
                                    $model = new PedidoModificacion();
                                    $model->id_cliente = $pedido->id_cliente;
                                    $model->fecha_anterior = $newFecha;
                                    $model->fecha_nueva = $oldFecha;
                                    $model->tallos = $d->ramos * $item->unidades;
                                    $model->operador = '+';
                                    $model->id_usuario = session('id_usuario');
                                    $model->id_variedad = $item->id_item;
                                    $model->longitud = $d->longitud;
                                    $model->cambio_fecha = 1;
                                    $model->save();
                                }
                            }

                            foreach ($detalles_receta as $item) {
                                if (!in_array([
                                    'fecha' => $oldFecha,
                                    'variedad' => $item->id_item,
                                ], $target_resumen))
                                    $target_resumen[] = [
                                        'fecha' => $oldFecha,
                                        'variedad' => $item->id_item,
                                    ];

                                if (!in_array([
                                    'fecha' => $newFecha,
                                    'variedad' => $item->id_item,
                                ], $target_resumen))
                                    $target_resumen[] = [
                                        'fecha' => $newFecha,
                                        'variedad' => $item->id_item,
                                    ];
                            }
                        } else { // es la misma fecha
                            $detalles_receta = DB::table('detalle_receta')
                                ->where('id_variedad', $d->variedad)
                                ->where('defecto', 1)
                                ->get();
                            if ($d->fecha >= hoy() && $d->fecha <= opDiasFecha('+', 4, hoy()) && $detalle->ramos != $d->ramos) {
                                foreach ($detalles_receta as $item) {
                                    $model = new PedidoModificacion();
                                    $model->id_cliente = $pedido->id_cliente;
                                    $model->fecha_anterior = $d->fecha;
                                    $model->fecha_nueva = $d->fecha;
                                    $tallos_anteriores = $detalle->ramos * $item->unidades;
                                    $tallos_nuevos = $d->ramos * $item->unidades;
                                    $diferencia = $tallos_nuevos - $tallos_anteriores;
                                    $model->tallos = abs($diferencia);
                                    $model->operador = $diferencia > 0 ? '+' : '-';
                                    $model->id_usuario = session('id_usuario');
                                    $model->id_variedad = $item->id_item;
                                    $model->longitud = $d->longitud;
                                    $model->save();
                                }
                            }

                            foreach ($detalles_receta as $item) {
                                if (!in_array([
                                    'fecha' => $d->fecha,
                                    'variedad' => $item->id_item,
                                ], $target_resumen))
                                    $target_resumen[] = [
                                        'fecha' => $d->fecha,
                                        'variedad' => $item->id_item,
                                    ];
                            }
                        }

                        $detalle->ramos = $d->ramos;
                        $detalle->save();

                        /* guardar detalle en array de existentes */
                        foreach ($resumen_existentes as $pos_r => $r) {
                            if ($r['pedido'] == $pedido->id_import_pedido) {
                                $r['detalles'][] = $detalle->id_detalle_import_pedido;
                            }
                            $resumen_existentes[$pos_r] = $r;
                        }
                    }
                }

                if (!in_array($pedido->id_import_pedido, $ids_pedido_archivo))
                    $ids_pedido_archivo[] = $pedido->id_import_pedido;
            }

            /* ELIMINAR los PEDIDOS que no esten incluidos en el archivo */
            $delete_pedido = ImportPedido::whereIn('fecha', $fechas_archivo)
                ->whereNotIn('codigo', $codigos_archivo)
                ->get();
            foreach ($delete_pedido as $del) {
                /* GUARDAR CAMBIOS */
                if ($del->fecha >= hoy() && $del->fecha <= opDiasFecha('+', 4, hoy())) {
                    foreach ($del->detalles as $d) {
                        $detalles_receta = DB::table('detalle_receta')
                            ->where('id_variedad', $d->id_variedad)
                            ->where('defecto', 1)
                            ->get();
                        foreach ($detalles_receta as $item) {
                            $model = new PedidoModificacion();
                            $model->id_cliente = $del->id_cliente;
                            $model->fecha_anterior = $del->fecha;
                            $model->fecha_nueva = $del->fecha;
                            $model->tallos = $d->ramos * $item->unidades;
                            $model->operador = '-';
                            $model->id_usuario = session('id_usuario');
                            $model->id_variedad = $item->id_item;
                            $model->longitud = $d->longitud;
                            $model->save();
                        }
                    }
                }
                foreach ($del->detalles as $d) {
                    $detalles_receta = DB::table('detalle_receta')
                        ->where('id_variedad', $d->id_variedad)
                        ->where('defecto', 1)
                        ->get();
                    foreach ($detalles_receta as $item) {
                        if (!in_array([
                            'fecha' => $del->fecha,
                            'variedad' => $item->id_item,
                        ], $target_resumen))
                            $target_resumen[] = [
                                'fecha' => $del->fecha,
                                'variedad' => $item->id_item,
                            ];
                    }
                }

                $del->estado = 0;
                $del->save();
            }


            /* ELIMINAR los DETALLES de los PEDIDOS que no esten incluidos en el archivo */
            foreach ($resumen_existentes as $pos_r => $r) {
                $delete_detalle = DetalleImportPedido::where('id_import_pedido', $r['pedido'])
                    ->whereNotIn('id_detalle_import_pedido', $r['detalles'])
                    ->get();

                foreach ($delete_detalle as $del) {
                    $detalles_receta = DB::table('detalle_receta')
                        ->where('id_variedad', $del->id_variedad)
                        ->where('defecto', 1)
                        ->get();
                    $fecha = $del->pedido->fecha;
                    foreach ($detalles_receta as $item) {
                        if (!in_array([
                            'fecha' => $fecha,
                            'variedad' => $item->id_item,
                        ], $target_resumen))
                            $target_resumen[] = [
                                'fecha' => $fecha,
                                'variedad' => $item->id_item,
                            ];
                    }

                    $del->delete();
                }
            }

            /* JOB para llenar la tabla DISTRIBUCION_RECETA */
            foreach ($ids_pedido_archivo as $pedido)
                jobDistribucionReceta::dispatch($pedido)->onQueue('distribucion_receta')->onConnection('database');

            foreach ($ids_pedido_archivo as $id_ped) {
                $pedido = ImportPedido::find($id_ped);
                $fecha = $pedido->fecha;
                foreach ($pedido->detalles as $det) {
                    $detalles_receta = DB::table('detalle_receta')
                        ->where('id_variedad', $det->id_variedad)
                        ->where('defecto', 1)
                        ->get();
                    foreach ($detalles_receta as $item) {
                        if (!in_array([
                            'fecha' => $fecha,
                            'variedad' => $item->id_item,
                        ], $target_resumen))
                            $target_resumen[] = [
                                'fecha' => $fecha,
                                'variedad' => $item->id_item,
                            ];
                    }
                }
            }

            /* TABLA RESUMEN_FECHAS */
            $fechas_updating = [];
            $reply_resumen = $target_resumen;
            foreach ($reply_resumen as $t) {
                if (!in_array($t['fecha'], $fechas_updating)) {
                    $fechas_updating[] = $t['fecha'];
                }
                $otros_resumen = DB::table('resumen_fechas')
                    ->select('id_variedad', 'fecha')->distinct()
                    ->where('fecha', $t['fecha'])
                    ->where('id_variedad', '!=', $t['variedad'])
                    ->where('tallos_venta', '>', 0)
                    ->get();
                foreach ($otros_resumen as $o) {
                    if (!in_array([
                        'fecha' => $o->fecha,
                        'variedad' => $o->id_variedad,
                    ], $target_resumen))
                        $target_resumen[] = [
                            'fecha' => $o->fecha,
                            'variedad' => $o->id_variedad,
                        ];
                }
            }

            $last_codigo = DB::table('proceso_queue')
                ->select(DB::raw('max(codigo) as codigo'))
                ->get()[0]->codigo;
            $next_codigo = $last_codigo != '' ? $last_codigo + 1 : 1;
            foreach ($target_resumen as $pos => $t) {
                jobUpdateVariedadByFecha::dispatch($t['fecha'], $t['variedad'], ($pos + 1), count($target_resumen), $next_codigo, 'Actualizacion de ventas a partir de un archivo de pedidos: ', session('id_usuario'))
                    ->onQueue('resumen_fecha_variedad')
                    ->onConnection('database');
            }
            /*foreach ($fechas_updating as $f) {
                jobUpdateVariedades::dispatch($t['fecha'], 0)->onQueue('update_variedades')->onConnection('database');
            }*/

            DB::commit();
            $success = true;
            $msg = 'Se ha <strong>GRABADO</strong> el archivo correctamente';
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

    public function eliminar_pedido(Request $request)
    {
        DB::beginTransaction();
        try {
            $pedido = ImportPedido::find($request->id_pedido);
            $variedades = [];
            foreach ($pedido->detalles as $d) {
                $detalles_receta = DB::table('detalle_receta')
                    ->where('id_variedad', $d->id_variedad)
                    ->where('defecto', 1)
                    ->get();
                foreach ($detalles_receta as $item) {
                    if ($request->guardar_cambio == 1 && $pedido->fecha >= hoy() && $pedido->fecha <= opDiasFecha('+', 4, hoy())) {
                        $model = new PedidoModificacion();
                        $model->id_cliente = $pedido->id_cliente;
                        $model->fecha_anterior = $pedido->fecha;
                        $model->fecha_nueva = $pedido->fecha;
                        $model->tallos = $d->ramos * $item->unidades;
                        $model->operador = '-';
                        $model->id_usuario = session('id_usuario');
                        $model->id_variedad = $item->id_item;
                        $model->longitud = $d->longitud;
                        $model->save();
                    }

                    /* TABLA RESUMEN_FECHAS */
                    /*Artisan::call('resumen:fecha', [
                        'fecha' => $pedido->fecha,
                        'variedad' => $item->id_item,
                        'dev' => 1,
                    ]);*/
                    $variedades[] = $item->id_item;
                }
            }

            bitacora('import_pedido', $pedido->id_import_pedido, 'E', 'CANCELAR PEDIDO DESDE LA OPCION: ELIMIAR_PEDIDO');
            $pedido->estado = 0;
            $pedido->save();

            foreach ($variedades as $var) {
                /* ACTUALIZAR TABLA RESUMEN_AGROGANA */
                jobUpdateResumenAgrogana::dispatch($pedido->fecha, $var)->onQueue('resumen_agrogana')->onConnection('database');
            }
            jobUpdateVariedades::dispatch($pedido->fecha, 0)->onQueue('update_variedades')->onConnection('database');

            DB::commit();
            $success = true;
            $msg = 'Se ha <strong>ELIMINADO</strong> el pedido correctamente';
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
        $detalle_pedido = DetalleImportPedido::find($request->det_ped);
        return view('adminlte.gestion.comercializacion.importar_pedidos.forms.admin_receta', [
            'det_pedido' => $detalle_pedido,
            'plantas' => Planta::where('estado', '=', 1)->orderBy('nombre')->get(),
        ]);
    }

    public function cargar_receta(Request $request)
    {
        $detalle_pedido = DetalleImportPedido::find($request->id_detalle_pedido);

        $detalles_receta = DetalleReceta::where('id_variedad', $detalle_pedido->id_variedad)
            ->where('numero_receta', $request->numero_receta)
            ->get();
        return view('adminlte.gestion.comercializacion.importar_pedidos.forms.cargar_receta', [
            'detalle_pedido' => $detalle_pedido,
            'detalles_receta' => $detalles_receta,
        ]);
    }

    public function buscar_variedades(Request $request)
    {
        $listado = Variedad::where('id_planta', $request->planta)
            //->where('assorted', 0)
            ->where('receta', 0)
            ->orderBy('nombre')
            ->get();

        return view('adminlte.gestion.comercializacion.importar_pedidos.forms.buscar_variedades', [
            'listado' => $listado,
            'ramos_pedido' => $request->ramos_pedido,
            'longitud_pedido' => $request->longitud_pedido,
        ]);
    }

    public function store_distribucion_receta(Request $request)
    {
        DB::beginTransaction();
        try {
            $det_pedido = DetalleImportPedido::find($request->id_detalle_pedido);
            $pedido = $det_pedido->pedido;
            $variedades = [];

            $delete = DistribucionReceta::where('id_detalle_import_pedido', $request->id_detalle_pedido)
                ->get();
            foreach ($delete as $del) {
                $variedades[] = $del->id_variedad;  // guardar en este listado los ids de variedades para actualizar la tabla resumen_agrogana
                $del->delete();
            }

            foreach (json_decode($request->data) as $d) {
                $model = new DistribucionReceta();
                $model->id_detalle_import_pedido = $request->id_detalle_pedido;
                $model->id_variedad = $d->id_item;
                $model->longitud = $d->longitud;
                $model->unidades = $d->unidades;
                $model->save();

                if (!in_array($d->id_item, $variedades))
                    $variedades[] = $d->id_item;  // guardar en este listado los ids de variedades para actualizar la tabla resumen_agrogana
            }

            foreach ($variedades as $var) {
                /* ACTUALIZAR TABLA RESUMEN_AGROGANA */
                jobUpdateResumenAgrogana::dispatch($pedido->fecha, $var)->onQueue('resumen_agrogana')->onConnection('database');

                /* TABLA RESUMEN_FECHAS */
                Artisan::call('resumen:fecha', [
                    'fecha' => $pedido->fecha,
                    'variedad' => $var,
                    'dev' => 1,
                ]);
            }
            jobUpdateVariedades::dispatch($pedido->fecha, 0)->onQueue('update_variedades')->onConnection('database');

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

    public function reiniciar_distribucion_receta(Request $request)
    {
        DB::beginTransaction();
        try {
            $det_pedido = DetalleImportPedido::find($request->id_detalle_pedido);
            $pedido = $det_pedido->pedido;
            $variedades = [];

            $delete = DistribucionReceta::where('id_detalle_import_pedido', $request->id_detalle_pedido)
                ->get();
            foreach ($delete as $del) {
                $variedades[] = $del->id_variedad;  // guardar en este listado los ids de variedades para actualizar la tabla resumen_agrogana
                $del->delete();
            }

            $det = DetalleImportPedido::find($request->id_detalle_pedido);
            foreach ($det->variedad->detalles_receta as $item) {
                $model = new DistribucionReceta();
                $model->id_detalle_import_pedido = $det->id_detalle_import_pedido;
                $model->id_variedad = $item->id_item;
                $model->longitud = $det->longitud;
                $model->unidades = $item->unidades;
                $model->save();

                if (!in_array($item->id_item, $variedades))
                    $variedades[] = $item->id_item;  // guardar en este listado los ids de variedades para actualizar la tabla resumen_agrogana
            }


            foreach ($variedades as $var) {
                /* ACTUALIZAR TABLA RESUMEN_AGROGANA */
                jobUpdateResumenAgrogana::dispatch($pedido->fecha, $var)->onQueue('resumen_agrogana')->onConnection('database');

                /* TABLA RESUMEN_FECHAS */
                Artisan::call('resumen:fecha', [
                    'fecha' => $pedido->fecha,
                    'variedad' => $var,
                    'dev' => 1,
                ]);
            }
            jobUpdateVariedades::dispatch($pedido->fecha, 0)->onQueue('update_variedades')->onConnection('database');

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

    public function mover_fecha_pedido(Request $request)
    {
        $pedido = ImportPedido::find($request->ped);
        return view('adminlte.gestion.comercializacion.importar_pedidos.forms.mover_fecha_pedido', [
            'pedido' => $pedido,
        ]);
    }

    public function store_mover_fecha_pedido(Request $request)
    {
        DB::beginTransaction();
        try {
            $pedido = ImportPedido::find($request->id_pedido);
            $oldFecha = $pedido->fecha;
            $newFecha = $request->fecha;
            $pedido->fecha = $request->fecha;
            $pedido->save();

            foreach ($pedido->detalles as $d) {
                $detalles_receta = DB::table('detalle_receta')
                    ->where('id_variedad', $d->id_variedad)
                    ->where('defecto', 1)
                    ->get();
                foreach ($detalles_receta as $item) {
                    if ($oldFecha >= hoy() && $oldFecha <= opDiasFecha('+', 4, hoy())) {
                        $model = new PedidoModificacion();
                        $model->id_cliente = $pedido->id_cliente;
                        $model->fecha_anterior = $oldFecha;
                        $model->fecha_nueva = $newFecha;
                        $model->tallos = $d->ramos * $item->unidades;
                        $model->operador = '-';
                        $model->id_usuario = session('id_usuario');
                        $model->id_variedad = $item->id_item;
                        $model->longitud = $d->longitud;
                        $model->cambio_fecha = 1;
                        $model->save();
                    }
                    if ($newFecha >= hoy() && $newFecha <= opDiasFecha('+', 4, hoy())) {
                        $model = new PedidoModificacion();
                        $model->id_cliente = $pedido->id_cliente;
                        $model->fecha_anterior = $newFecha;
                        $model->fecha_nueva = $oldFecha;
                        $model->tallos = $d->ramos * $item->unidades;
                        $model->operador = '+';
                        $model->id_usuario = session('id_usuario');
                        $model->id_variedad = $item->id_item;
                        $model->longitud = $d->longitud;
                        $model->cambio_fecha = 1;
                        $model->save();
                    }

                    /* TABLA RESUMEN_FECHAS */
                    Artisan::call('resumen:fecha', [
                        'fecha' => $oldFecha,
                        'variedad' => $item->id_item,
                        'dev' => 1,
                    ]);

                    /* TABLA RESUMEN_FECHAS */
                    Artisan::call('resumen:fecha', [
                        'fecha' => $newFecha,
                        'variedad' => $item->id_item,
                        'dev' => 1,
                    ]);

                    /* ACTUALIZAR TABLA RESUMEN_AGROGANA */
                    jobUpdateResumenAgrogana::dispatch($oldFecha, $item->id_item)->onQueue('resumen_agrogana');
                    jobUpdateResumenAgrogana::dispatch($newFecha, $item->id_item)->onQueue('resumen_agrogana');
                    jobUpdateVariedades::dispatch($oldFecha, $item->id_item)->onQueue('update_variedades')->onConnection('database');
                    jobUpdateVariedades::dispatch($newFecha, $item->id_item)->onQueue('update_variedades')->onConnection('database');
                }
            }

            DB::commit();
            $success = true;
            $msg = 'Se ha <strong>MOVIDO</strong> de fecha el pedido correctamente';
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

    public function ver_procesos(Request $request)
    {
        return view('adminlte.gestion.comercializacion.importar_pedidos.partials.ver_procesos', []);
    }

    public function cargar_procesos(Request $request)
    {
        $listado = ProcesoQueue::where('fecha_registro', 'like', hoy() . '%')
            ->orderBy('fecha_registro', 'desc')
            ->get();

        return view('adminlte.gestion.comercializacion.importar_pedidos.partials._cargar_procesos', [
            'listado' => $listado,
        ]);
    }
}
