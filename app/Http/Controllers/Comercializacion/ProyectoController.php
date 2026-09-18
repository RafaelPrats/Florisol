<?php

namespace yura\Http\Controllers\Comercializacion;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use yura\Http\Controllers\Controller;
use yura\Jobs\jobGrabarOrdenFija;
use yura\Jobs\jobStoreProyecto;
use yura\Modelos\CajaProyecto;
use yura\Modelos\CajaProyectoMarcacion;
use yura\Modelos\Cliente;
use yura\Modelos\DatosExportacion;
use yura\Modelos\DetalleCajaProyecto;
use yura\Modelos\DistribucionReceta;
use yura\Modelos\InventarioRecepcion;
use yura\Modelos\OrdenTrabajo;
use yura\Modelos\OtNacional;
use yura\Modelos\Proyecto;
use yura\Modelos\RegistroMovimientos;
use yura\Modelos\RenovarOrdenFija;
use yura\Modelos\SalidasRecepcion;
use yura\Modelos\Segmento;
use yura\Modelos\Submenu;
use yura\Modelos\Variedad;

class ProyectoController extends Controller
{
    public function inicio(Request $request)
    {
        $finca = getFincaActiva();
        $clientes = Cliente::where('estado', '=', '1')
            ->where('id_empresa', $finca)
            ->get();
        $segmentos = DB::table('detalle_cliente as dc')
            ->join('cliente as c', 'c.id_cliente', '=', 'dc.id_cliente')
            ->select('dc.segmento')->distinct()
            ->where('dc.estado', '1')
            ->where('c.id_empresa', $finca)
            ->get()->pluck('segmento')->toArray();
        return view('adminlte.gestion.comercializacion.proyectos.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'clientes' => $clientes,
            'segmentos' => $segmentos,
        ]);
    }

    public function listar_reporte(Request $request)
    {
        $finca = getFincaActiva();
        $listado = Proyecto::where('estado', 1)
            ->where('fecha', '>=', $request->desde)
            ->where('fecha', '<=', $request->hasta)
            ->where('id_empresa', $finca);
        if ($request->segmento != 'T')
            $listado = $listado->where('segmento', $request->segmento);
        if ($request->cliente != 'T')
            $listado = $listado->where('id_cliente', $request->cliente);
        if ($request->tipo != 'T')
            $listado = $listado->where('tipo', $request->tipo);
        $listado = $listado->orderBy('packing')
            ->orderBy('fecha')
            ->get();
        return view('adminlte.gestion.comercializacion.proyectos.partials.listado', [
            'proyectos' => $listado,
        ]);
    }

    public function add_proyecto(Request $request)
    {
        $finca = getFincaActiva();
        $segmentos = DB::table('detalle_cliente as dc')
            ->join('cliente as c', 'c.id_cliente', '=', 'dc.id_cliente')
            ->select('dc.segmento')->distinct()
            ->where('dc.estado', '1')
            ->where('c.id_empresa', $finca)
            ->get()->pluck('segmento')->toArray();
        $datos_exportacion = DatosExportacion::where('estado', 1)->get();
        return view('adminlte.gestion.comercializacion.proyectos.forms.add_proyecto', [
            'segmentos' => $segmentos,
            'datos_exportacion' => $datos_exportacion,
        ]);
    }

    public function cargar_opciones_orden_fija(Request $request)
    {
        return view(
            'adminlte.gestion.comercializacion.proyectos.forms.inputs_opciones_pedido_fijo',
            [
                'opcion' => $request->opcion
            ]
        );
    }

    public function seleccionar_segmento(Request $request)
    {
        $finca = getFincaActiva();
        $clientes = DB::table('detalle_cliente as dc')
            ->join('cliente as c', 'c.id_cliente', '=', 'dc.id_cliente')
            ->select('dc.*')->distinct()
            ->where('c.id_empresa', $finca)
            ->where('dc.estado', 1)
            ->where('dc.segmento', $request->segmento)
            ->orderBy('dc.nombre')
            ->get();
        $options_cliente = '<option value="">Seleccione</option>';
        foreach ($clientes as $con) {
            $options_cliente .= '<option value="' . $con->id_cliente . '">' . $con->nombre . '</option>';
        }
        return [
            'options_cliente' => $options_cliente,
        ];
    }

    public function seleccionar_cliente(Request $request)
    {
        $finca = getFincaActiva();
        $variedades = [];
        if ($request->usar_especificaciones) {
            $variedades = DB::table('especificaciones as e')
                ->join('variedad as v', 'v.id_variedad', '=', 'e.id_variedad')
                ->select('v.nombre', 'e.id_variedad')->distinct()
                ->where('e.id_cliente', $request->cliente)
                ->orderBy('v.nombre')
                ->get();
        }
        if (count($variedades) == 0 || !$request->usar_especificaciones) {
            $variedades = Variedad::where('estado', 1)
                //->where('receta', 1)
                ->where('id_empresa', $finca)
                ->orderBy('nombre')
                ->get();
        }
        $options_variedades = '<option>Seleccione...</option>';
        foreach ($variedades as $var) {
            $options_variedades .= '<option value="' . $var->id_variedad . '">' . $var->nombre . '</option>';
        }

        $consignatarios = DB::table('cliente_consignatario as cc')
            ->join('consignatario as c', 'c.id_consignatario', '=', 'cc.id_consignatario')
            ->select('c.nombre', 'cc.id_consignatario')->distinct()
            ->where('c.estado', 1)
            ->where('cc.id_cliente', $request->cliente)
            ->orderBy('c.nombre')
            ->get();
        $options_consignatario = '';
        foreach ($consignatarios as $con) {
            $options_consignatario .= '<option value="' . $con->id_consignatario . '">' . $con->nombre . '</option>';
        }

        $options_agencia = '';
        $agencias_cliente = DB::table('cliente_agenciacarga as ca')
            ->join('agencia_carga as a', 'a.id_agencia_carga', '=', 'ca.id_agencia_carga')
            ->select('a.nombre', 'ca.id_agencia_carga')->distinct()
            ->where('a.estado', 1)
            ->where('ca.id_cliente', $request->cliente)
            ->orderBy('a.nombre')
            ->get();
        foreach ($agencias_cliente as $age) {
            $options_agencia .= '<option value="' . $age->id_agencia_carga . '">' . $age->nombre . '</option>';
        }
        $agencias = DB::table('agencia_carga as a')
            ->select('a.nombre', 'a.id_agencia_carga')->distinct()
            ->where('a.estado', 1)
            ->whereNotIn('a.id_agencia_carga', $agencias_cliente->pluck('id_agencia_carga')->toArray())
            ->orderBy('a.nombre')
            ->get();
        foreach ($agencias as $age) {
            $options_agencia .= '<option value="' . $age->id_agencia_carga . '">' . $age->nombre . '</option>';
        }
        return [
            'options_consignatario' => $options_consignatario,
            'options_agencia' => $options_agencia,
            'options_variedades' => $options_variedades,
        ];
    }

    public function form_combos_seleccionar_receta(Request $request)
    {
        $especificaciones = DB::table('especificaciones')
            ->where('id_cliente', $request->cliente)
            ->where('id_variedad', $request->receta)
            ->get();
        $options_tipo_caja = '';
        $options_ramos_x_caja = '';
        $options_tallos_x_ramo = '';
        $options_longitud = '';
        $options_precio = '';
        $tipo_caja = '';
        foreach ($especificaciones as $pos => $esp) {
            $options_tipo_caja .= '<option value="' . $esp->tipo_caja . '">' . $esp->tipo_caja . '</option>';
            $options_ramos_x_caja .= '<option value="' . $esp->ramos_x_caja . '">' . $esp->ramos_x_caja . '</option>';
            $options_tallos_x_ramo .= '<option value="' . $esp->tallos_x_ramo . '">' . $esp->tallos_x_ramo . '</option>';
            $options_longitud .= '<option value="' . $esp->longitud . '">' . $esp->longitud . '</option>';
            $options_precio .= '<option value="' . $esp->precio . '">' . $esp->precio . '</option>';
            if ($pos == 0)
                $tipo_caja = $esp->tipo_caja;
        }
        $tallos_x_ramo = DB::table('detalle_receta')
            ->select(DB::raw('sum(unidades) as cantidad'))
            ->where('id_variedad', $request->receta)
            ->where('defecto', 1)
            ->get()[0]->cantidad;
        $cliente = Cliente::find($request->cliente);
        $inventario = getTotalInventarioByVariedadSegmento($request->receta, $cliente->detalle()->segmento);
        return [
            'especificaciones' => $especificaciones,
            'tipo_caja' => $tipo_caja,
            'options_tipo_caja' => $options_tipo_caja,
            'options_ramos_x_caja' => $options_ramos_x_caja,
            'options_tallos_x_ramo' => $options_tallos_x_ramo,
            'options_longitud' => $options_longitud,
            'options_precio' => $options_precio,
            'tallos_x_ramo' => $tallos_x_ramo,
            'inventario' => $inventario,
        ];
    }

    public function agregar_combos_pedido(Request $request)
    {
        $detalles_combo = [];
        foreach (json_decode($request->data) as $d) {
            $detalles_combo[] = [
                'receta' => Variedad::find($d->receta),
                'longitud' => $d->longitud,
                'ramos_x_caja' => $d->ramos_x_caja,
                'tallos_x_ramos' => $d->tallos_x_ramos,
                'precio' => $d->precio,
            ];
        }
        return view('adminlte.gestion.comercializacion.proyectos.forms._agregar_combos_pedido', [
            'piezas' => $request->piezas,
            'caja' => $request->caja,
            'celdas_marcaciones' => json_decode($request->celdas_marcaciones),
            'detalles_combo' => $detalles_combo,
            'form_cant_detalles' => $request->form_cant_detalles,
        ]);
    }

    public function store_proyecto(Request $request)
    {
        try {
            DB::beginTransaction();
            $finca = getFincaActiva();
            $renovacion = '';
            if ($request->tipo == 'SO') {
                $fechas = [];
                if ($request->fecha['opcion_pedido_fijo'] == 1 || $request->fecha['opcion_pedido_fijo'] == 2) {
                    $f = $request->fecha['desde'];
                    while ($f <= $request->fecha['hasta']) {
                        if ($request->fecha['opcion_pedido_fijo'] == 1 && date('N', strtotime($f)) == $request->fecha['dia_semana'])
                            $fechas[] = $f;

                        if ($request->fecha['opcion_pedido_fijo'] == 2 && substr($f, 8, 2) == $request->fecha['dia_mes'])
                            $fechas[] = $f;
                        $f = opDiasFecha('+', 1, $f);
                    }
                    if ($request->fecha['intervalo'] == 2) {
                        foreach ($fechas as $pos => $f) {
                            if ($pos % 2 == 1)
                                unset($fechas[$pos]);
                        }
                    }
                    $renovacion = [
                        'renovar' => $request->fecha['renovar'],
                        'intervalo' => $request->fecha['intervalo'] == 1 ? 7 : 14
                    ];
                } else {
                    $fechas = $request->fecha['fechas'];
                }
                $fecha = $fechas[0];
            } else {
                $fecha = $request->fecha;
            }

            // NUEVO PROYECTO
            $proyecto = new Proyecto();
            $proyecto->id_cliente = $request->cliente;
            if ($request->tipo == 'SO') {
                $numeroOrdenFija = DB::table('proyecto')
                    ->select(DB::raw('max(orden_fija) as cantidad'))
                    ->get()[0]->cantidad;
                $numeroOrdenFija = $numeroOrdenFija != '' ? ($numeroOrdenFija + 1) : 1;
                $proyecto->orden_fija = $numeroOrdenFija;
            }
            $proyecto->id_empresa = $finca;
            $proyecto->fecha = $fecha;
            $proyecto->tipo = $request->tipo;
            $proyecto->segmento = $request->segmento;
            $proyecto->id_consignatario = $request->consignatario;
            $proyecto->id_agencia_carga = $request->agencia;
            $proyecto->save();
            $proyecto->id_proyecto = DB::table('proyecto')
                ->select(DB::raw('max(id_proyecto) as id'))
                ->get()[0]->id;

            foreach (json_decode($request->detalles_pedido) as $det_ped) {
                // NUEVA CAJA PROYECTO
                $caja = new CajaProyecto();
                $caja->id_proyecto = $proyecto->id_proyecto;
                $caja->cantidad = $det_ped->piezas;
                $caja->tipo_caja = $det_ped->caja;
                $caja->save();
                $caja->id_caja_proyecto = DB::table('caja_proyecto')
                    ->select(DB::raw('max(id_caja_proyecto) as id'))
                    ->get()[0]->id;
                foreach ($det_ped->detalles_combo as $det_caj) {
                    // NUEVO DETALLE CAJA PROYECTO
                    $detalle = new DetalleCajaProyecto();
                    $detalle->id_caja_proyecto = $caja->id_caja_proyecto;
                    $detalle->id_variedad = $det_caj->receta;
                    $detalle->ramos_x_caja = $det_caj->ramos_x_caja;
                    $detalle->tallos_x_ramo = $det_caj->tallos_x_ramos;
                    $detalle->precio = $det_caj->precio_ped;
                    $detalle->longitud_ramo = $det_caj->longitud;
                    $detalle->save();
                    $detalle->id_detalle_caja_proyecto = DB::table('detalle_caja_proyecto')
                        ->select(DB::raw('max(id_detalle_caja_proyecto) as id'))
                        ->get()[0]->id;

                    $variedad = $detalle->variedad;
                    if ($request->liquidar && !$variedad->receta) {
                        if ($fecha >= '2026-09-17') {
                            // DESPACHAR SOLIDOS
                            $return = $this->despachar_ramos_solidos($detalle, $variedad, $proyecto);
                            if ($return['success'] == false) {
                                DB::rollBack();

                                return [
                                    'success' => false,
                                    'mensaje' => $return['mensaje'],
                                ];
                            }
                        } else {
                            DB::rollBack();
                            $success = false;
                            $msg = '<div class="alert alert-danger text-center">' .
                                '<h4>No esta permitido usar esta funcion para pedidos previos al 15 de Sept 2026</h4>' .
                                '</div>';

                            return [
                                'success' => $success,
                                'mensaje' => $msg,
                            ];
                        }
                    }

                    $getDetallesReceta = Variedad::find($det_caj->receta)->getDetallesReceta();
                    foreach ($getDetallesReceta as $det_receta) {
                        $dist_receta = new DistribucionReceta();
                        $dist_receta->id_detalle_caja_proyecto = $detalle->id_detalle_caja_proyecto;
                        $dist_receta->id_variedad = $det_receta->id_item;
                        $dist_receta->unidades = $det_receta->unidades;
                        $dist_receta->longitud = $detalle->longitud_ramo;
                        $dist_receta->save();
                    }
                }
                foreach ($det_ped->valores_marcaciones as $marcacion) {
                    // NUEVA CAJA PROYECTO MARCACION
                    if ($marcacion->valor_marcacion != '') {
                        $caja_marcacion = new CajaProyectoMarcacion();
                        $caja_marcacion->id_caja_proyecto = $caja->id_caja_proyecto;
                        $caja_marcacion->id_dato_exportacion = $marcacion->id_marcacion;
                        $caja_marcacion->valor = $marcacion->valor_marcacion;
                        $caja_marcacion->save();
                    }
                }
            }

            $msg = 'Se ha <b>GRABADO</b> el pedido correctamente';
            /* CREAR EL RESTO DE LA ORDEN FIJA */
            if ($request->tipo == 'SO') {
                dump('* CREAR EL RESTO DE LA ORDEN FIJA *');
                $msg = 'Se esta <b>CREANDO</b> la orden fija en un segundo plano';
                foreach ($fechas as $pos => $f) {
                    if ($pos > 0) {
                        jobGrabarOrdenFija::dispatch($proyecto->id_proyecto, $f)->onQueue('grabar_orden_fija')->onConnection('database');
                    }
                }

                /* CREAR RENOVACION */
                if ($renovacion['renovar'] == true) {
                    $model_renovar = new RenovarOrdenFija();
                    $model_renovar->orden_fija = $numeroOrdenFija;
                    $model_renovar->renovacion = $renovacion['intervalo'];
                    $model_renovar->save();
                }
            }
            /*jobStoreProyecto::dispatch(
            $request->all(),
            session('id_usuario'),
            \Request::ip(),
            $finca
        )->onQueue('store_proyecto')->onConnection('database');*/

            $success = true;
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

    private function despachar_ramos_solidos($det_caja, $variedad, $proyecto)
    {
        $finca = getFincaActiva();
        $segmento = Segmento::where('nombre', $proyecto->segmento)->first();
        $bodega = $segmento != '' ? $segmento->bodega : '';
        $caja_proyecto = $det_caja->caja_proyecto;
        $last_orden = DB::table('salidas_recepcion as s')
            ->join('inventario_recepcion as i', 'i.id_inventario_recepcion', '=', 's.id_inventario_recepcion')
            ->select(DB::raw('max(s.orden_flor_solida) as orden'))
            ->where('i.id_empresa', $finca)
            ->get()[0]->orden;
        $last_orden++;

        $query = DB::table('inventario_recepcion as i')
            ->select('i.*')->distinct()
            ->where('i.disponibles', '>', 0)
            ->where('i.id_variedad', $variedad->id_variedad)
            ->where('i.id_empresa', $finca)
            ->where('i.bodega', $bodega)
            ->get();
        $inventarios = [];
        $total_inventario = 0;
        foreach ($query as $q) {
            $fecha_desde = $q->fecha;
            $fecha_hasta = opDiasFecha('+', $variedad->dias_rotacion_recepcion, $q->fecha);
            if ($proyecto->fecha >= $fecha_desde && $proyecto->fecha <= $fecha_hasta) {
                $inventarios[] = InventarioRecepcion::find($q->id_inventario_recepcion);
                $total_inventario += $q->disponibles;
            }
        }

        $sacar = $caja_proyecto->cantidad * $det_caja->ramos_x_caja * $det_caja->tallos_x_ramo;
        if ($sacar <= $total_inventario) {
            foreach ($inventarios as $inv) {
                if ($sacar > 0) {
                    $usados = 0;
                    $disponible = $inv->disponibles;
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
                        $inv->disponibles -= $usados;
                        $inv->save();

                        $new_salida = new SalidasRecepcion();
                        $new_salida->id_inventario_recepcion = $inv->id_inventario_recepcion;
                        $new_salida->id_detalle_caja_proyecto = $det_caja->id_detalle_caja_proyecto;
                        $new_salida->id_variedad = $det_caja->id_variedad;
                        $new_salida->fecha = hoy();
                        $new_salida->cantidad = $usados;
                        $new_salida->basura = 0;
                        $new_salida->orden_flor_solida = $last_orden;
                        $new_salida->save();

                        $registro = new RegistroMovimientos();
                        $registro->id_variedad = $det_caja->id_variedad;
                        $registro->id_empresa = $finca;
                        $registro->bodega = $bodega;
                        $registro->fecha = hoy();
                        $registro->tipo = 'S';
                        $registro->concepto = 'FLOR_SOLIDA';
                        $registro->numero = $last_orden;
                        $registro->cantidad = $usados;
                        $registro->id_usuario = session('id_usuario');
                        $registro->descripcion = 'Despacho a traves del boton GRABAR Y DESPACHAR del formulario de pedidos';
                        // campos de relacion
                        $registro->id_inventario_recepcion = $inv->id_inventario_recepcion;
                        $registro->id_detalle_caja_proyecto = $det_caja->id_detalle_caja_proyecto;
                        $registro->save();
                    }
                }
            }

            $det_caja->armados += $det_caja->ramos_x_caja * $caja_proyecto->cantidad;
            $det_caja->save();

            return [
                'success' => true,
                'mensaje' => 'OK'
            ];
        } else {
            return [
                'success' => false,
                'mensaje' => 'No hay flor disponible en el inventario'
            ];
        }
    }

    public function editar_proyecto(Request $request)
    {
        $finca = getFincaActiva();
        $proyecto = Proyecto::find($request->id);
        $datos_exportacion = DatosExportacion::where('estado', 1)->get();

        /*$query_variedades = DB::table('especificaciones as e')
            ->join('variedad as v', 'v.id_variedad', '=', 'e.id_variedad')
            ->select('v.nombre', 'e.id_variedad')->distinct()
            ->where('e.id_cliente', $proyecto->id_cliente)
            ->orderBy('v.nombre')
            ->get();
        if (count($query_variedades) == 0) {
            $query_variedades = Variedad::where('estado', 1)
                //->where('receta', 1)
                ->where('id_empresa', $finca)
                ->orderBy('nombre')
                ->get();
        }*/

        $query_variedades = Variedad::where('estado', 1)
            //->where('receta', 1)
            ->where('id_empresa', $finca)
            ->orderBy('nombre')
            ->get();
        $options_variedades = '<option>Seleccione...</option>';
        foreach ($query_variedades as $var) {
            $options_variedades .= '<option value="' . $var->id_variedad . '">' . $var->nombre . '</option>';
        }
        return view('adminlte.gestion.comercializacion.proyectos.forms.editar_proyecto', [
            'datos_exportacion' => $datos_exportacion,
            'variedades' => $query_variedades,
            'options_variedades' => $options_variedades,
            'proyecto' => $proyecto,
        ]);
    }

    public function update_proyecto(Request $request)
    {
        try {
            DB::beginTransaction();
            $proyecto = Proyecto::find($request->id);
            //$proyecto->id_cliente = $request->cliente;
            $proyecto->fecha = $request->fecha;
            $proyecto->tipo = $request->tipo;
            $proyecto->segmento = $request->segmento;
            $proyecto->id_consignatario = $request->consignatario;
            $proyecto->id_agencia_carga = $request->agencia;
            $proyecto->save();

            $cajas_actuales = '';
            foreach (json_decode($request->detalles_pedido) as $pos_det => $det_ped) {
                if ($det_ped->id_caja_proyecto != '') {
                    // CAJA PROYECTO EXISTENTE
                    $caja = CajaProyecto::find($det_ped->id_caja_proyecto);
                } else {
                    // NUEVA CAJA PROYECTO
                    $caja = new CajaProyecto();
                    $caja->id_proyecto = $proyecto->id_proyecto;
                }
                $caja->cantidad = $det_ped->piezas;
                $caja->tipo_caja = $det_ped->caja;
                $caja->save();
                if ($det_ped->id_caja_proyecto == '') {
                    $caja->id_caja_proyecto = DB::table('caja_proyecto')
                        ->select(DB::raw('max(id_caja_proyecto) as id'))
                        ->get()[0]->id;
                }
                foreach ($det_ped->detalles_combo as $det_caj) {
                    $isCambioReceta = false;
                    if ($det_caj->id_detalle_caja_proyecto != '') {
                        // DETALLE CAJA PROYECTO EXISTENTE
                        $detalle = DetalleCajaProyecto::find($det_caj->id_detalle_caja_proyecto);
                        $isNuevo = false;
                        if ($det_caj->receta != $detalle->id_variedad)
                            $isCambioReceta = true;

                        $variedad = $detalle->variedad;
                        if ($variedad->receta == 0 && $detalle->armados > 0 && $detalle->id_variedad != $det_caj->receta) {
                            // es flor solida con ramos armados y hay cambio de variedad
                            DB::rollBack();
                            $success = false;
                            $msg = '<div class="alert alert-warning text-center">' .
                                '<h4><i class="fa fa-fw fa-exclamation-triangle"></i>Ya existen ramos armados para la flor: <b>' . $variedad->nombre . '</b>, debe devolver dichos ramos antes de cambiar de flor</h4>' .
                                '</div>';

                            return [
                                'success' => $success,
                                'mensaje' => $msg,
                            ];
                        }
                    } else {
                        // NUEVO DETALLE CAJA PROYECTO
                        $detalle = new DetalleCajaProyecto();
                        $detalle->id_caja_proyecto = $caja->id_caja_proyecto;
                        $isNuevo = true;
                    }
                    $detalle->id_variedad = $det_caj->receta;
                    $detalle->ramos_x_caja = $det_caj->ramos_x_caja;
                    $detalle->tallos_x_ramo = $det_caj->tallos_x_ramos;
                    $detalle->precio = $det_caj->precio_ped;
                    $detalle->longitud_ramo = $det_caj->longitud;
                    $detalle->save();

                    if ($isNuevo) {
                        $detalle->id_detalle_caja_proyecto = DB::table('detalle_caja_proyecto')
                            ->select(DB::raw('max(id_detalle_caja_proyecto) as id'))
                            ->get()[0]->id;

                        $getDetallesReceta = Variedad::find($det_caj->receta)->getDetallesReceta();
                        foreach ($getDetallesReceta as $det_receta) {
                            $dist_receta = new DistribucionReceta();
                            $dist_receta->id_detalle_caja_proyecto = $detalle->id_detalle_caja_proyecto;
                            $dist_receta->id_variedad = $det_receta->id_item;
                            $dist_receta->unidades = $det_receta->unidades;
                            $dist_receta->longitud = $detalle->longitud_ramo;
                            $dist_receta->save();
                        }
                    } elseif ($isCambioReceta) {
                        DB::select('delete from distribucion_receta where id_detalle_caja_proyecto = ' . $detalle->id_detalle_caja_proyecto);

                        $getDetallesReceta = Variedad::find($detalle->id_variedad)->getDetallesReceta();
                        foreach ($getDetallesReceta as $det_receta) {
                            $dist_receta = new DistribucionReceta();
                            $dist_receta->id_detalle_caja_proyecto = $detalle->id_detalle_caja_proyecto;
                            $dist_receta->id_variedad = $det_receta->id_item;
                            $dist_receta->unidades = $det_receta->unidades;
                            $dist_receta->longitud = $detalle->longitud_ramo;
                            $dist_receta->save();
                        }
                    }
                }
                DB::select('delete from caja_proyecto_marcacion where id_caja_proyecto = ' . $caja->id_caja_proyecto);
                foreach ($det_ped->valores_marcaciones as $marcacion) {
                    // NUEVA CAJA PROYECTO MARCACION
                    if ($marcacion->valor_marcacion != '') {
                        $caja_marcacion = new CajaProyectoMarcacion();
                        $caja_marcacion->id_caja_proyecto = $caja->id_caja_proyecto;
                        $caja_marcacion->id_dato_exportacion = $marcacion->id_marcacion;
                        $caja_marcacion->valor = $marcacion->valor_marcacion;
                        $caja_marcacion->save();
                    }
                }
                if ($pos_det == 0)
                    $cajas_actuales = $caja->id_caja_proyecto;
                else
                    $cajas_actuales .= ', ' . $caja->id_caja_proyecto;
            }
            DB::select('delete from caja_proyecto where id_proyecto = ' . $proyecto->id_proyecto . ' and id_caja_proyecto not in (' . $cajas_actuales . ')');
            bitacora('proyecto', $proyecto->id_proyecto, 'U', 'ACTUALIZAR PEDIDO');

            DB::commit();
            $success = true;
            $msg = 'Se ha <strong>MODIFICADO</strong> el pedido correctamente';
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

    public function copiar_pedido(Request $request)
    {
        $pedido = Proyecto::find($request->pedido);
        return view('adminlte.gestion.comercializacion.proyectos.forms.copiar_pedido', [
            'pedido' => $pedido,
        ]);
    }

    public function store_copiar_pedido(Request $request)
    {
        try {
            $pedOriginal = Proyecto::find($request->id_ped);
            foreach (json_decode($request->data) as $d) {
                DB::beginTransaction();
                // NUEVO PROYECTO
                $proyecto = new Proyecto();
                $proyecto->id_empresa = $pedOriginal->id_empresa;
                $proyecto->id_cliente = $pedOriginal->id_cliente;
                $proyecto->segmento = $pedOriginal->segmento;
                $proyecto->fecha = $d;
                $proyecto->tipo = 'OM';
                $proyecto->id_consignatario = $pedOriginal->id_consignatario;
                $proyecto->id_agencia_carga = $pedOriginal->id_agencia_carga;
                $proyecto->save();
                $proyecto->id_proyecto = DB::table('proyecto')
                    ->select(DB::raw('max(id_proyecto) as id'))
                    ->get()[0]->id;

                foreach ($pedOriginal->cajas as $det_ped) {
                    // NUEVA CAJA PROYECTO
                    $caja = new CajaProyecto();
                    $caja->id_proyecto = $proyecto->id_proyecto;
                    $caja->cantidad = $det_ped->cantidad;
                    $caja->tipo_caja = $det_ped->tipo_caja;
                    $caja->save();
                    $caja->id_caja_proyecto = DB::table('caja_proyecto')
                        ->select(DB::raw('max(id_caja_proyecto) as id'))
                        ->get()[0]->id;
                    foreach ($det_ped->detalles as $det_caj) {
                        // NUEVO DETALLE CAJA PROYECTO
                        $detalle = new DetalleCajaProyecto();
                        $detalle->id_caja_proyecto = $caja->id_caja_proyecto;
                        $detalle->id_variedad = $det_caj->id_variedad;
                        $detalle->ramos_x_caja = $det_caj->ramos_x_caja;
                        $detalle->tallos_x_ramo = $det_caj->tallos_x_ramo;
                        $detalle->precio = $det_caj->precio;
                        $detalle->longitud_ramo = $det_caj->longitud_ramo;
                        $detalle->save();
                        $detalle->id_detalle_caja_proyecto = DB::table('detalle_caja_proyecto')
                            ->select(DB::raw('max(id_detalle_caja_proyecto) as id'))
                            ->get()[0]->id;

                        $getDetallesReceta = Variedad::find($det_caj->id_variedad)->getDetallesReceta();
                        foreach ($getDetallesReceta as $det_receta) {
                            $dist_receta = new DistribucionReceta();
                            $dist_receta->id_detalle_caja_proyecto = $detalle->id_detalle_caja_proyecto;
                            $dist_receta->id_variedad = $det_receta->id_item;
                            $dist_receta->unidades = $det_receta->unidades;
                            $dist_receta->longitud = $detalle->longitud_ramo;
                            $dist_receta->save();
                        }
                    }
                    foreach ($det_ped->marcaciones as $marcacion) {
                        // NUEVA CAJA PROYECTO MARCACION
                        if ($marcacion->valor != '') {
                            $caja_marcacion = new CajaProyectoMarcacion();
                            $caja_marcacion->id_caja_proyecto = $caja->id_caja_proyecto;
                            $caja_marcacion->id_dato_exportacion = $marcacion->id_dato_exportacion;
                            $caja_marcacion->valor = $marcacion->valor;
                            $caja_marcacion->save();
                        }
                    }
                }
                DB::commit();
            }

            $success = true;
            $msg = 'Se ha <b>COPIADO</b> el pedido correctamente';
        } catch (\Exception $e) {

            DB::rollBack();
            $success = false;
            $msg = '<div class="alert alert-danger text-center">' .
                '<p> Ha ocurrido un problema al guardar la informacion al sistema</p>' .
                '<p>' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine() . '</p>'
                . '</div>';
        }
        return [
            'mensaje' => $msg,
            'success' => $success,
        ];
    }

    public function delete_pedido(Request $request)
    {
        try {
            DB::beginTransaction();
            $proyecto = Proyecto::find($request->id);
            $proyecto->delete();

            DB::commit();
            $success = true;
            $msg = 'Se ha <b>CANCELADO</b> el pedido correctamente';
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

    public function descargar_despachos(Request $request)
    {
        $proyecto = Proyecto::find($request->id);
        $spread = new Spreadsheet();
        $this->excel_descargar_despachos($spread, $request);
        $fileName = "Despachos " . $proyecto->fecha . " " . $proyecto->cliente->detalle()->nombre . ".xlsx";
        $writer = new Xlsx($spread);

        //--------------------------- GUARDAR EL EXCEL -----------------------

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        $writer->save('php://output');

        //$writer->save('/var/www/html/Dasalflor/storage/storage/excel/excel_prueba.xlsx');
    }

    public function excel_descargar_despachos($spread, $request)
    {
        $proyecto = Proyecto::find($request->id);
        $ids_detalles = DB::table('detalle_caja_proyecto as dc')
            ->join('caja_proyecto as c', 'c.id_caja_proyecto', '=', 'dc.id_caja_proyecto')
            ->select('dc.id_detalle_caja_proyecto')->distinct()
            ->where('c.id_proyecto', $proyecto->id_proyecto)
            ->get()->pluck('id_detalle_caja_proyecto')->toArray();

        // ORDENES DE TRABAJO
        $listado_ot = OrdenTrabajo::whereIn('id_detalle_caja_proyecto', $ids_detalles)
            ->orderBy('id_orden_trabajo')
            ->get();

        $columnas = getColumnasExcel();
        $sheet = $spread->getActiveSheet();
        $sheet->setTitle('OT');

        $row_ini = 1;
        foreach ($listado_ot as $orden_trabajo) {
            $despachador = $orden_trabajo->despachador;

            $row = $row_ini;
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

            setTextCenterToCeldaExcel($sheet, 'A' . $row_ini . ':' . $columnas[$col] . $row);
            setBorderToCeldaExcel($sheet, 'A' . $row_ini . ':' . $columnas[$col] . $row);

            for ($i = 0; $i <= $col; $i++)
                $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);

            $row_ini = $row + 2;
        }

        // OT NACIONALES
        $listado_ot_nacional = DB::table('ot_nacional')
            ->select('id_detalle_caja_proyecto')->distinct()
            ->whereIn('id_detalle_caja_proyecto', $ids_detalles)
            ->orderBy('numero')
            ->get()->pluck('id_detalle_caja_proyecto')->toArray();

        $sheet = $spread->createSheet()->setTitle('OT_Nacional');

        $row_ini = 1;
        foreach ($listado_ot_nacional as $id_det) {
            $detalle = DetalleCajaProyecto::find($id_det);
            $caja = $detalle->caja_proyecto;

            $row = $row_ini;
            $col = 0;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, '#' . $detalle->ot_nacional[0]->numero);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $caja->cantidad . ' Cajas');
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $caja->cantidad * $detalle->ramos_x_caja . ' Ramos');
            $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col + 3] . $row);
            $col = 6;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $detalle->variedad->nombre);
            $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col + 4] . $row);
            $col = 10;

            $row++;
            $col = 0;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Fecha');
            $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + 1));
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Distribucion RECETA ORIGINAL');
            $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col + 4] . $row);
            $col += 5;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'VARIEDAD / ESPECIE');
            $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col + 3] . $row);
            $col += 4;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Total Tallos');
            $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + 1));

            setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
            setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

            $row++;
            $col = 1;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'PLANTA');
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'VARIEDAD');
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'UNIDADES');
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TALLOS');
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TxR');
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'PLANTA');
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'VARIEDAD');
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'LONGITUD');
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TALLOS');
            setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '5a7177');
            setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');
            $col++;

            $getOtNacional = $detalle->getOtNacional();
            $total_row = count($detalle->ot_nacional);
            $tallos_x_ramo = 0;
            $total_tallos = 0;
            foreach ($getOtNacional as $pos) {
                $tallos_x_ramo += $pos['unidades_dist'];
                foreach ($pos['detalles'] as $det) {
                    $total_tallos += $det->tallos;
                }
            }
            foreach ($getOtNacional as $pos_pos => $pos) {
                foreach ($pos['detalles'] as $pos_det => $det) {
                    $row++;
                    $col = 0;
                    if ($pos_pos == 0 && $pos_det == 0) {
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $pos['fecha']);
                        $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + $total_row - 1));
                    }
                    if ($pos_det == 0) {
                        $col++;
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $pos['pta_dist_nombre']);
                        $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($pos['detalles']) - 1));
                        $col++;
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $pos['var_dist_nombre'] . ' ' . $pos['longitud_dist'] . 'cm');
                        $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($pos['detalles']) - 1));
                        $col++;
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $pos['unidades_dist']);
                        $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($pos['detalles']) - 1));
                        $col++;
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $pos['total_tallos_dist']);
                        $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($pos['detalles']) - 1));
                    }
                    if ($pos_pos == 0 && $pos_det == 0) {
                        $col = 5;
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $tallos_x_ramo);
                        $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + $total_row - 1));
                    }
                    $col = 6;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $det->pta_nombre);
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $det->var_nombre);
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $det->longitud . 'cm');
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $det->tallos);
                    if ($pos_pos == 0 && $pos_det == 0) {
                        $col = 10;
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_tallos);
                        $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + $total_row - 1));
                    }
                }
            }
            $col = 10;

            setTextCenterToCeldaExcel($sheet, 'A' . $row_ini . ':' . $columnas[$col] . $row);
            setBorderToCeldaExcel($sheet, 'A' . $row_ini . ':' . $columnas[$col] . $row);

            for ($i = 0; $i <= $col; $i++)
                $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);

            $row_ini = $row + 2;
        }

        // FLOR SOLIDA
        $listado_solido = [];
        foreach ($proyecto->cajas as $caja) {
            if (count($caja->detalles) == 1) {
                foreach ($caja->detalles as $det_caja) {
                    if ($det_caja->variedad->receta == 0) {
                        $listado_solido[] = $det_caja;
                    }
                }
            }
        }

        $sheet = $spread->createSheet()->setTitle('FLOR_SOLIDA');

        $row_ini = 1;
        foreach ($listado_solido as $model) {
            $salidas = DB::table('salidas_recepcion')
                ->select(
                    'orden_flor_solida',
                    DB::raw('sum(cantidad) as tallos'),
                )
                ->where('id_detalle_caja_proyecto', $model->id_detalle_caja_proyecto)
                ->whereNull('id_ot_nacional')
                ->whereNotNull('orden_flor_solida')
                ->where('cantidad', '>', 0)
                ->groupBy('orden_flor_solida')
                ->orderBy('orden_flor_solida')
                ->get();

            if (count($salidas) > 0) {
                $row = $row_ini;
                $col = 0;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'N°');
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'FECHA');
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'CLIENTE');
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'VARIEDAD');
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TxR');
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'LONGITUD');
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'RAMOS');
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TALLOS');
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'RESPONSABLE');
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'OBSERVACION');

                setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
                setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

                $total_ramos = 0;
                foreach ($salidas as $pos => $salida) {
                    $row++;
                    $col = 0;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $salida->orden_flor_solida);
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $model->caja_proyecto->proyecto->fecha);
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $model->caja_proyecto->proyecto->cliente->detalle()->nombre);
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $model->variedad->nombre);
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $model->tallos_x_ramo);
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $model->longitud_ramo);
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $salida->tallos / $model->tallos_x_ramo);
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $salida->tallos);
                    $col += 2;

                    $total_ramos += $salida->tallos / $model->tallos_x_ramo;
                }
                if (count($salidas) > 0) {
                    $row++;
                    $col = 0;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TOTALES');
                    $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col + 5] . $row);
                    $col += 6;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_ramos);
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_ramos * $model->tallos_x_ramo);
                    $col += 2;

                    setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
                    setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');
                }

                setTextCenterToCeldaExcel($sheet, 'A' . $row_ini . ':' . $columnas[$col] . $row);
                setBorderToCeldaExcel($sheet, 'A' . $row_ini . ':' . $columnas[$col] . $row);

                for ($i = 0; $i <= $col; $i++)
                    $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);

                $row_ini = $row + 2;
            }
        }

        // CAJAS COMBOS
        $listado_combos = [];
        foreach ($proyecto->cajas as $caja) {
            if (count($caja->detalles) > 1) {
                $list_detalles = [];
                foreach ($caja->detalles as $det_caja) {
                    if ($det_caja->variedad->receta == 0) {
                        $salidas = DB::table('salidas_recepcion')
                            ->select(
                                'orden_flor_solida',
                                DB::raw('sum(cantidad) as tallos'),
                            )
                            ->where('id_detalle_caja_proyecto', $det_caja->id_detalle_caja_proyecto)
                            ->whereNull('id_ot_nacional')
                            ->whereNotNull('orden_flor_solida')
                            ->where('cantidad', '>', 0)
                            ->groupBy('orden_flor_solida')
                            ->orderBy('orden_flor_solida')
                            ->get();
                        if (count($salidas) > 0) {
                            $list_detalles[] = [
                                'det_caja' => $det_caja,
                                'query' => $salidas,
                                'receta' => 0,
                                'tipo' => 'SOLIDO',
                            ];
                        }
                    } else {
                        $ordenes = OrdenTrabajo::where('id_detalle_caja_proyecto', $det_caja->id_detalle_caja_proyecto)
                            ->orderBy('id_orden_trabajo')
                            ->get();
                        if (count($ordenes) > 0) {
                            $list_detalles[] = [
                                'det_caja' => $det_caja,
                                'query' => $ordenes,
                                'receta' => 1,
                                'tipo' => 'OT',
                            ];
                        } else {
                            $ordenes_nacional = DB::table('ot_nacional')
                                ->select('numero')->distinct()
                                ->where('id_detalle_caja_proyecto', $det_caja->id_detalle_caja_proyecto)
                                ->orderBy('numero')
                                ->get()->pluck('numero')->toArray();
                            if (count($ordenes_nacional) > 0) {
                                $list_detalles[] = [
                                    'det_caja' => $det_caja,
                                    'query' => $ordenes_nacional[0],
                                    'receta' => 1,
                                    'tipo' => 'NACIONAL',
                                ];
                            }
                        }
                    }
                }
                if (count($list_detalles) > 0)
                    $listado_combos[] = [
                        'caja' => $caja,
                        'detalles' => $list_detalles,
                    ];
            }
        }

        $sheet = $spread->createSheet()->setTitle('COMBOS');

        $row_ini = 1;
        foreach ($listado_combos as $combo) {
            $row = $row_ini;
            $col = 0;
            $marcaciones = $combo['caja']->marcaciones->pluck('valor')->toArray();
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $combo['caja']->cantidad . ' Caja(s) ' . $combo['caja']->tipo_caja . ': ' . implode(' - ', $marcaciones));
            $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col + 11] . $row);
            setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '5a7177');
            setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

            $row++;
            $col = 0;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TIPO');
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'N°');
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'FECHA');
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'CLIENTE');
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'VARIEDAD');
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TxR');
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'LONGITUD');
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'RxC');
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'RAMOS');
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TALLOS');
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'RESPONSABLE');
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'OBSERVACION');

            setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
            setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

            foreach ($combo['detalles'] as $det) {
                if ($det['tipo'] == 'NACIONAL') {
                    $row++;
                    $col = 0;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $det['tipo']);
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $det['query']);
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $det['det_caja']->caja_proyecto->proyecto->fecha);
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $det['det_caja']->caja_proyecto->proyecto->cliente->detalle()->nombre);
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $det['det_caja']->variedad->nombre);
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $det['det_caja']->tallos_x_ramo);
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $det['det_caja']->longitud_ramo);
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $det['det_caja']->ramos_x_caja);
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $det['det_caja']->ramos_x_caja * $combo['caja']->cantidad);
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $det['det_caja']->ramos_x_caja * $combo['caja']->cantidad * $det['det_caja']->tallos_x_ramo);
                    $col += 2;
                }
                if ($det['tipo'] == 'OT') {
                    foreach ($det['query'] as $pos_ot => $ot) {
                        $row++;
                        $col = 0;
                        if ($pos_ot == 0) {
                            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $det['tipo']);
                            $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($det['query']) - 1));
                        }
                        $col++;
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $ot->id_orden_trabajo);
                        $col++;
                        if ($pos_ot == 0) {
                            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $det['det_caja']->caja_proyecto->proyecto->fecha);
                            $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($det['query']) - 1));
                        }
                        $col++;
                        if ($pos_ot == 0) {
                            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $det['det_caja']->caja_proyecto->proyecto->cliente->detalle()->nombre);
                            $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($det['query']) - 1));
                        }
                        $col++;
                        if ($pos_ot == 0) {
                            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $det['det_caja']->variedad->nombre);
                            $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($det['query']) - 1));
                        }
                        $col++;
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $ot->getTxR());
                        $col++;
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $ot->longitud);
                        $col++;
                        if ($pos_ot == 0) {
                            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $det['det_caja']->ramos_x_caja);
                            $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($det['query']) - 1));
                        }
                        $col++;
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $ot->ramos);
                        $col++;
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $ot->getTotalTallos());
                        $col += 2;
                    }
                }
                if ($det['tipo'] == 'SOLIDO') {
                    foreach ($det['query'] as $pos_salida => $salida) {
                        $row++;
                        $col = 0;
                        if ($pos_salida == 0) {
                            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $det['tipo']);
                            $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($det['query']) - 1));
                        }
                        $col++;
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $salida->orden_flor_solida);
                        $col++;
                        if ($pos_salida == 0) {
                            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $det['det_caja']->caja_proyecto->proyecto->fecha);
                            $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($det['query']) - 1));
                        }
                        $col++;
                        if ($pos_salida == 0) {
                            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $det['det_caja']->caja_proyecto->proyecto->cliente->detalle()->nombre);
                            $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($det['query']) - 1));
                        }
                        $col++;
                        if ($pos_salida == 0) {
                            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $det['det_caja']->variedad->nombre);
                            $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($det['query']) - 1));
                        }
                        $col++;
                        if ($pos_salida == 0) {
                            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $det['det_caja']->tallos_x_ramo);
                            $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($det['query']) - 1));
                        }
                        $col++;
                        if ($pos_salida == 0) {
                            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $det['det_caja']->longitud_ramo);
                            $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($det['query']) - 1));
                        }
                        $col++;
                        if ($pos_salida == 0) {
                            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $det['det_caja']->ramos_x_caja);
                            $sheet->mergeCells($columnas[$col] . $row . ':' . $columnas[$col] . ($row + count($det['query']) - 1));
                        }
                        $col++;
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $salida->tallos / $det['det_caja']->tallos_x_ramo);
                        $col++;
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $salida->tallos);
                        $col += 2;
                    }
                }
            }

            setTextCenterToCeldaExcel($sheet, 'A' . $row_ini . ':' . $columnas[$col] . $row);
            setBorderToCeldaExcel($sheet, 'A' . $row_ini . ':' . $columnas[$col] . $row);

            for ($i = 0; $i <= $col; $i++)
                $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);

            $row_ini = $row + 2;
        }
    }
}
