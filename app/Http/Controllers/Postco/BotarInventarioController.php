<?php

namespace yura\Http\Controllers\Postco;

use DB;
use Illuminate\Http\Request;
use yura\Http\Controllers\Controller;
use yura\Modelos\CodigoAutorizacion;
use yura\Modelos\InventarioRecepcion;
use yura\Modelos\MotivoBaja;
use yura\Modelos\Planta;
use yura\Modelos\RegistroMovimientos;
use yura\Modelos\SalidasRecepcion;
use yura\Modelos\Submenu;

class BotarInventarioController extends Controller
{
    public function inicio(Request $request)
    {
        $finca = getFincaActiva();
        $plantas = Planta::where('estado', 1)
            ->where('id_empresa', $finca)
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.postco.botar_inventario.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'plantas' => $plantas,
        ]);
    }

    public function listar_reporte(Request $request)
    {
        $finca = getFincaActiva();
        $plantas = DB::table('inventario_recepcion as i')
            ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->leftJoin('detalle_api_store_cajas as da', function ($join) use ($finca, $request) {
                $join->on('da.id_variedad', '=', 'i.id_variedad')
                    ->where('da.id_empresa', '=', $finca);
            })
            ->select('v.id_planta', 'p.nombre')
            ->distinct()
            ->where('i.id_empresa', $finca)
            ->where('i.bodega', $request->bodega)
            ->where(function ($query) {
                $query->where('i.disponibles', '>', 0)
                    ->orWhere('da.estado', 'P');
            })
            ->when($request->planta != '', function ($query) use ($request) {
                return $query->where('v.id_planta', $request->planta);
            })
            ->orderBy('p.nombre')
            ->get();
        $listado = [];
        foreach ($plantas as $pta) {
            $variedades = DB::table('inventario_recepcion as i')
                ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
                ->leftJoin('detalle_api_store_cajas as da', function ($join) use ($finca, $request) {
                    $join->on('da.id_variedad', '=', 'i.id_variedad')
                        ->where('da.id_empresa', '=', $finca);
                })
                ->select(
                    'i.*',
                    'v.nombre'
                )
                ->distinct()
                ->where('i.id_empresa', $finca)
                ->where('i.bodega', $request->bodega)
                ->where('v.id_planta', $pta->id_planta)
                ->where(function ($query) {
                    $query->where('i.disponibles', '>', 0)
                        ->orWhere('da.estado', 'P');
                })
                ->when($request->variedad != '', function ($query) use ($request) {
                    return $query->where('i.id_variedad', $request->variedad);
                })
                ->orderBy('v.nombre')
                ->get();
            $listado[] = [
                'planta' => $pta,
                'variedades' => $variedades,
            ];
        }

        return view('adminlte.gestion.postco.botar_inventario.partials.listado', [
            'listado' => $listado,
        ]);
    }

    public function listar_ordenes(Request $request)
    {
        $finca = getFincaActiva();
        $listado = DB::table('salidas_recepcion as s')
            ->join('inventario_recepcion as inv', 'inv.id_inventario_recepcion', '=', 's.id_inventario_recepcion')
            ->join('variedad as v', 'v.id_variedad', '=', 's.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->join('motivo_baja as m', 'm.id_motivo_baja', '=', 's.id_motivo_baja')
            ->select(
                's.*',
                'inv.tallos_x_ramo',
                'inv.longitud',
                'p.nombre as pta_nombre',
                'v.nombre as var_nombre',
                'm.nombre as motivo_nombre'
            )->distinct()
            ->where('s.fecha', $request->fecha)
            ->where('inv.bodega', $request->bodega)
            ->where('inv.id_empresa', $finca)
            ->whereNotNull('s.orden_basura');
        if ($request->planta != '') {
            $listado = $listado->where('v.id_planta', $request->planta);
        }
        if ($request->variedad != '') {
            $listado = $listado->where('s.id_variedad', $request->variedad);
        }
        $listado = $listado->orderBy('s.orden_basura')
            ->orderBy('p.nombre')
            ->orderBy('v.nombre')
            ->orderBy('s.fecha')
            ->get()
            ->groupBy('orden_basura')
            ->map(function ($items, $orden_basura) {
                return [
                    'orden_basura' => $orden_basura,
                    'fecha' => $items->first()->fecha,
                    'estado_orden_basura' => $items->first()->estado_orden_basura,
                    'detalles' => $items->values(),
                ];
            })
            ->values();

        return view('adminlte.gestion.postco.botar_inventario.partials.listado_ordenes', [
            'listado' => $listado,
        ]);
    }

    public function admin_motivos(Request $request)
    {
        $motivos = MotivoBaja::orderBy('nombre')->get();
        return view('adminlte.gestion.postco.botar_inventario.forms.admin_motivos', [
            'motivos' => $motivos
        ]);
    }

    public function store_motivos(Request $request)
    {
        DB::beginTransaction();
        try {
            foreach (json_decode($request->data) as $data) {
                $model = new MotivoBaja();
                $model->nombre = $data;
                $model->save();
            }

            DB::commit();
            $success = true;
            $msg = 'Se han <strong>GRABADO</strong> los motivos correctamente';
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

    public function update_orden(Request $request)
    {
        DB::beginTransaction();
        try {
            foreach (json_decode($request->data) as $data) {
                $model = SalidasRecepcion::find($data->id);
                if ($data->basura > 0) {
                    $model->basura = $data->basura;
                    $model->save();
                } else {
                    $model->delete();
                }
            }

            DB::commit();
            $success = true;
            $msg = 'Se ha <strong>ACTUALIZADO</strong> la orden correctamente';
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

    public function update_motivo(Request $request)
    {
        DB::beginTransaction();
        try {
            $model = MotivoBaja::find($request->id);
            $model->nombre = $request->nombre;
            $model->save();

            DB::commit();
            $success = true;
            $msg = 'Se ha <strong>MODIFICADO</strong> el motivo correctamente';
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

    public function cambiar_estado_motivo(Request $request)
    {
        DB::beginTransaction();
        try {
            $model = MotivoBaja::find($request->id);
            $model->estado = $model->estado ? 0 : 1;
            $model->save();

            DB::commit();
            $success = true;
            $msg = 'Se ha <strong>MODIFICADO</strong> el motivo correctamente';
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

    public function store_orden_basura(Request $request)
    {
        try {
            $fecha = $request->fecha;
            $orden = filter_var($request->orden, FILTER_VALIDATE_INT);
            $registros = json_decode($request->data);

            if (!is_string($fecha) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)
                || !checkdate((int) substr($fecha, 5, 2), (int) substr($fecha, 8, 2), (int) substr($fecha, 0, 4))
                || $fecha < '2026-09-17') {
                return [
                    'success' => false,
                    'mensaje' => 'La fecha debe ser válida y superior al 17 de septiembre de 2026.',
                ];
            }
            if (!$orden || $orden < 1 || !in_array($request->bodega, ['V', 'P'], true)
                || !is_array($registros) || count($registros) === 0) {
                return [
                    'success' => false,
                    'mensaje' => 'La orden no tiene datos válidos para grabar.',
                ];
            }

            DB::beginTransaction();

            $finca = getFincaActiva();
            $grupos_procesados = [];
            foreach ($registros as $data) {
                if (!is_object($data)) {
                    DB::rollBack();
                    return [
                        'success' => false,
                        'mensaje' => 'La información de la orden no tiene el formato esperado.',
                    ];
                }

                $id_variedad = isset($data->id_variedad) ? filter_var($data->id_variedad, FILTER_VALIDATE_INT) : false;
                $cantidad = isset($data->botar) ? filter_var($data->botar, FILTER_VALIDATE_INT) : false;
                $id_motivo = isset($data->motivo) ? filter_var($data->motivo, FILTER_VALIDATE_INT) : false;
                $longitud = isset($data->longitud) ? $data->longitud : null;

                if (!$id_variedad || !$cantidad || $cantidad < 1 || !$id_motivo
                    || !is_numeric($longitud) || !is_finite((float) $longitud)) {
                    DB::rollBack();
                    return [
                        'success' => false,
                        'mensaje' => 'La cantidad, variedad, longitud o motivo ingresado no es válido.',
                    ];
                }

                $clave_grupo = $id_variedad . '|' . rtrim(rtrim(sprintf('%.8F', (float) $longitud), '0'), '.');
                if (isset($grupos_procesados[$clave_grupo])) {
                    DB::rollBack();
                    return [
                        'success' => false,
                        'mensaje' => 'La orden contiene una variedad y longitud repetida.',
                    ];
                }
                $grupos_procesados[$clave_grupo] = true;

                $motivo = MotivoBaja::where('id_motivo_baja', $id_motivo)
                    ->where('estado', 1)
                    ->first();
                if (!$motivo) {
                    DB::rollBack();
                    return [
                        'success' => false,
                        'mensaje' => 'Uno de los motivos seleccionados ya no está disponible.',
                    ];
                }

                $lotes = InventarioRecepcion::where('id_variedad', $id_variedad)
                    ->where('longitud', $longitud)
                    ->where('bodega', $request->bodega)
                    ->where('id_empresa', $finca)
                    ->where('disponibles', '>', 0)
                    ->orderBy('fecha', 'asc')
                    ->orderBy('id_inventario_recepcion', 'asc')
                    ->lockForUpdate()
                    ->get();
                $disponibles = $lotes->sum('disponibles');

                if ($disponibles < $cantidad) {
                    DB::rollBack();
                    $variedad = $lotes->isEmpty() ? 'seleccionada' : $lotes->first()->variedad->nombre;
                    return [
                        'success' => false,
                        'mensaje' => 'No hay suficientes tallos disponibles para ' . $variedad .
                            ' en la longitud ' . $longitud . '. Disponibles: ' . number_format($disponibles) . '.',
                    ];
                }

                $pendientes = $cantidad;
                foreach ($lotes as $lote) {
                    if ($pendientes === 0) {
                        break;
                    }

                    $cantidad_lote = min((int) $lote->disponibles, $pendientes);
                    $salidas = new SalidasRecepcion();
                    $salidas->id_inventario_recepcion = $lote->id_inventario_recepcion;
                    $salidas->id_variedad = $lote->id_variedad;
                    $salidas->id_motivo_baja = $id_motivo;
                    $salidas->cantidad = 0;
                    $salidas->basura = $cantidad_lote;
                    $salidas->fecha = $fecha;
                    $salidas->orden_basura = $orden;
                    $salidas->estado_orden_basura = 0;
                    $salidas->save();

                    $pendientes -= $cantidad_lote;
                }
            }

            DB::commit();
            $success = true;
            $msg = 'Se ha <strong>CREADO la ORDEN de FLOR de BAJA</strong> correctamente';
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

    public function modal_baja(Request $request)
    {
        $finca = getFincaActiva();
        $last_orden = DB::table('salidas_recepcion as s')
            ->join('inventario_recepcion as inv', 'inv.id_inventario_recepcion', '=', 's.id_inventario_recepcion')
            ->select(
                DB::raw('max(s.orden_basura) as orden_basura')
            )
            ->where('inv.id_empresa', $finca)
            ->where('s.basura', '>', 0)
            ->get()[0]->orden_basura;
        $motivos = MotivoBaja::where('estado', 1)->orderBy('nombre')->get();
        $plantas = Planta::where('id_empresa', $finca)
            ->orderBy('nombre')
            ->get();

        $listado = DB::table('inventario_recepcion as i')
            ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->select(
                'i.*',
                'p.nombre as pta_nombre',
                'v.nombre as var_nombre',
            )->distinct()
            ->where('i.id_empresa', $finca)
            ->where('i.bodega', $request->bodega)
            ->where('i.disponibles', '>', 0)
            ->orderBy('p.nombre')
            ->orderBy('v.nombre')
            ->orderBy('i.fecha')
            ->get()
            ->groupBy(function ($lote) {
                return $lote->id_variedad . '|' . $lote->longitud;
            })
            ->map(function ($lotes) {
                $grupo = $lotes->first();
                $grupo->disponibles = $lotes->sum('disponibles');
                return $grupo;
            })
            ->values();

        return view('adminlte.gestion.postco.botar_inventario.forms.modal_baja', [
            'last_orden' => $last_orden,
            'listado' => $listado,
            'motivos' => $motivos,
            'plantas' => $plantas,
            'bodega' => $request->bodega,
        ]);
    }

    public function delete_orden_basura(Request $request)
    {
        DB::beginTransaction();
        try {
            $query = SalidasRecepcion::where('orden_basura', $request->orden)->get();
            foreach ($query as $q) {
                $q->delete();
            }

            DB::commit();
            $success = true;
            $msg = 'Se ha <strong>ELIMINADO</strong> la orden correctamente';
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

    public function completar_orden(Request $request)
    {
        DB::beginTransaction();
        try {
            $codigo = CodigoAutorizacion::where('nombre', 'desechar_orden')
                ->first();
            if ($codigo != '' && $codigo->valor == $request->codigo) {
                $finca = getFincaActiva();
                $query = SalidasRecepcion::where('orden_basura', $request->orden)->where('estado_orden_basura', 0)->get();
                foreach ($query as $q) {
                    $inventario = $q->inventario_recepcion;
                    if ($inventario->disponibles >= $q->basura) {
                        $inventario->disponibles -= $q->basura;
                        $inventario->save();

                        $q->estado_orden_basura = 1;
                        $q->save();

                        $registro = new RegistroMovimientos();
                        $registro->id_variedad = $inventario->id_variedad;
                        $registro->id_empresa = $inventario->id_empresa;
                        $registro->bodega = $inventario->bodega;
                        $registro->fecha = hoy();
                        $registro->tipo = 'S';
                        $registro->concepto = 'FLOR_BAJA';
                        $registro->numero = $q->orden_basura;
                        $registro->cantidad = $q->basura;
                        $registro->id_usuario = session('id_usuario');
                        $registro->descripcion = 'Salida a traves del formulario de Flor de Baja';
                        // campos de relacion
                        $registro->id_inventario_recepcion = $inventario->id_inventario_recepcion;
                        $registro->save();
                    } else {
                        $query_inventarios = InventarioRecepcion::where('id_variedad', $inventario->id_variedad)
                            ->where('longitud', $inventario->longitud)
                            ->where('bodega', $inventario->bodega)
                            ->where('id_empresa', $inventario->id_empresa)
                            ->where('disponibles', '>', 0)
                            ->orderBy('fecha', 'asc')
                            ->orderBy('id_inventario_recepcion', 'asc')
                            ->get();
                        if (count($query_inventarios) > 0) {
                            $total_inventario = 0;
                            foreach ($query_inventarios as $inv) {
                                $total_inventario += $inv->disponibles;
                            }
                            if ($total_inventario >= $q->basura) {
                                $sacar = $q->basura;
                                foreach ($query_inventarios as $model) {
                                    if ($sacar >= 0) {
                                        $usados = 0;
                                        $disponible = $model->disponibles;
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
                                            $model->disponibles -= $usados;
                                            $model->save();

                                            $salidas = new SalidasRecepcion();
                                            $salidas->id_inventario_recepcion = $model->id_inventario_recepcion;
                                            $salidas->id_variedad = $model->id_variedad;
                                            $salidas->id_motivo_baja = $q->id_motivo_baja;
                                            $salidas->cantidad = 0;
                                            $salidas->basura = $usados;
                                            $salidas->fecha = $q->fecha;
                                            $salidas->orden_basura = $q->orden_basura;
                                            $salidas->estado_orden_basura = 1;
                                            $salidas->save();

                                            $registro = new RegistroMovimientos();
                                            $registro->id_variedad = $model->id_variedad;
                                            $registro->id_empresa = $model->id_empresa;
                                            $registro->bodega = $model->bodega;
                                            $registro->fecha = hoy();
                                            $registro->tipo = 'S';
                                            $registro->concepto = 'FLOR_BAJA';
                                            $registro->numero = $q->orden_basura;
                                            $registro->cantidad = $usados;
                                            $registro->id_usuario = session('id_usuario');
                                            $registro->descripcion = 'Salida a traves del formulario de Flor de Baja. (se tuvo que completar con otro inventario)';
                                            // campos de relacion
                                            $registro->id_inventario_recepcion = $model->id_inventario_recepcion;
                                            $registro->save();
                                        }
                                    }
                                }

                                $q->delete();
                            } else {
                                DB::rollBack();
                                $success = false;
                                $msg = '<div class="alert alert-danger text-center">' .
                                    '<h3>No hay flor suficiente, ' . $inventario->variedad->nombre . '</h3>' .
                                    '</div>';

                                return [
                                    'success' => $success,
                                    'mensaje' => $msg,
                                ];
                            }
                        } else {
                            DB::rollBack();
                            $success = false;
                            $msg = '<div class="alert alert-danger text-center">' .
                                '<h3>No hay flor suficiente, ' . $inventario->variedad->nombre . '</h3>' .
                                '</div>';

                            return [
                                'success' => $success,
                                'mensaje' => $msg,
                            ];
                        }
                    }
                }

                DB::commit();
                $success = true;
                $msg = 'Se ha <strong>MODIFICADO</strong> el motivo correctamente';
            } else {
                DB::rollBack();
                $success = false;
                $msg = '<div class="alert alert-danger text-center">' .
                    '<h3>El codigo de autorizacion es incorrecto</h3>' .
                    '</div>';
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
}
