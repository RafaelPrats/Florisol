<?php

namespace yura\Http\Controllers\Comercializacion;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use yura\Http\Controllers\Controller;
use yura\Jobs\jobStoreProyecto;
use yura\Modelos\CajaProyecto;
use yura\Modelos\CajaProyectoMarcacion;
use yura\Modelos\Cliente;
use yura\Modelos\DatosExportacion;
use yura\Modelos\DetalleCajaProyecto;
use yura\Modelos\Proyecto;
use yura\Modelos\Submenu;
use yura\Modelos\Variedad;

class ProyectoController extends Controller
{
    public function inicio(Request $request)
    {
        $clientes = Cliente::where('estado', '=', '1')->get();
        $segmentos = DB::table('detalle_cliente')
            ->select('segmento')->distinct()
            ->where('estado', '1')
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
        $listado = Proyecto::where('estado', 1)
            ->where('fecha', $request->fecha);
        if ($request->segmento != 'T')
            $listado = $listado->where('segmento', $request->segmento);
        if ($request->cliente != 'T')
            $listado = $listado->where('id_cliente', $request->cliente);
        if ($request->tipo != 'T')
            $listado = $listado->where('tipo', $request->tipo);
        $listado = $listado->orderBy('packing')
            ->get();
        return view('adminlte.gestion.comercializacion.proyectos.partials.listado', [
            'proyectos' => $listado,
        ]);
    }

    public function add_proyecto(Request $request)
    {
        $segmentos = DB::table('detalle_cliente')
            ->select('segmento')->distinct()
            ->where('estado', '1')
            ->get()->pluck('segmento')->toArray();
        $datos_exportacion = DatosExportacion::where('estado', 1)->get();
        $recetas = Variedad::where('estado', 1)
            ->where('receta', 1)
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.comercializacion.proyectos.forms.add_proyecto', [
            'segmentos' => $segmentos,
            'datos_exportacion' => $datos_exportacion,
            'recetas' => $recetas,
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
        $clientes = DB::table('detalle_cliente')
            ->select('*')->distinct()
            ->where('estado', 1)
            ->where('segmento', $request->segmento)
            ->orderBy('nombre')
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
        ];
    }

    public function form_combos_seleccionar_receta(Request $request)
    {
        $tallos_x_ramo = DB::table('detalle_receta')
            ->select(DB::raw('sum(unidades) as cantidad'))
            ->where('id_variedad', $request->receta)
            ->get()[0]->cantidad;
        return [
            'longitud' => '',
            'tallos_x_ramos' => $tallos_x_ramo,
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
        jobStoreProyecto::dispatch(
            $request->all(),
            session('id_usuario'),
            \Request::ip()
        )->onQueue('store_proyecto')->onConnection('database');

        $msg = 'Se esta <b>CREANDO</b> el pedido en un segundo plano';
        $success = true;
        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }

    public function editar_proyecto(Request $request)
    {
        $proyecto = Proyecto::find($request->id);
        $datos_exportacion = DatosExportacion::where('estado', 1)->get();
        $recetas = Variedad::where('estado', 1)
            ->where('receta', 1)
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.comercializacion.proyectos.forms.editar_proyecto', [
            'datos_exportacion' => $datos_exportacion,
            'recetas' => $recetas,
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
                    if ($det_caj->id_detalle_caja_proyecto != '') {
                        // DETALLE CAJA PROYECTO EXISTENTE
                        $detalle = DetalleCajaProyecto::find($det_caj->id_detalle_caja_proyecto);
                    } else {
                        // NUEVO DETALLE CAJA PROYECTO
                        $detalle = new DetalleCajaProyecto();
                        $detalle->id_caja_proyecto = $caja->id_caja_proyecto;
                    }
                    $detalle->id_variedad = $det_caj->receta;
                    $detalle->ramos_x_caja = $det_caj->ramos_x_caja;
                    $detalle->tallos_x_ramo = $det_caj->tallos_x_ramos;
                    $detalle->precio = $det_caj->precio_ped;
                    $detalle->longitud_ramo = $det_caj->longitud;
                    $detalle->save();
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
}
