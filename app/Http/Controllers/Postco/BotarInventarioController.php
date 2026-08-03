<?php

namespace yura\Http\Controllers\Postco;

use DB;
use Illuminate\Http\Request;
use yura\Http\Controllers\Controller;
use yura\Modelos\CodigoAutorizacion;
use yura\Modelos\InventarioRecepcion;
use yura\Modelos\MotivoBaja;
use yura\Modelos\Planta;
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
            DB::beginTransaction();
            foreach (json_decode($request->data) as $data) {
                $model = InventarioRecepcion::find($data->id_inv);
                if ($model->disponibles < $data->botar) {
                    DB::rollBack();
                    $success = false;
                    $msg = 'No se pueden botar mas tallos de los disponibles, flor: ' . $model->variedad->nombre;

                    return [
                        'success' => $success,
                        'mensaje' => $msg,
                    ];
                } else {
                    $salidas = new SalidasRecepcion();
                    $salidas->id_inventario_recepcion = $model->id_inventario_recepcion;
                    $salidas->id_variedad = $model->id_variedad;
                    $salidas->id_motivo_baja = $data->motivo;
                    $salidas->cantidad = 0;
                    $salidas->basura = $data->botar;
                    $salidas->fecha = $request->fecha;
                    $salidas->orden_basura = $request->orden;
                    $salidas->estado_orden_basura = 0;
                    $salidas->save();

                    $success = true;
                    $msg = 'Se ha <strong>CREADO la ORDEN de FLOR de BAJA</strong> correctamente';
                }
            }

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
        $motivos = MotivoBaja::orderBy('nombre')->get();
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
            ->where('i.bodega', $request->bodega)
            ->where('i.disponibles', '>', 0)
            ->orderBy('p.nombre')
            ->orderBy('v.nombre')
            ->orderBy('i.fecha')
            ->get();

        return view('adminlte.gestion.postco.botar_inventario.forms.modal_baja', [
            'last_orden' => $last_orden,
            'listado' => $listado,
            'motivos' => $motivos,
            'plantas' => $plantas,
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
                $query = SalidasRecepcion::where('orden_basura', $request->orden)->where('estado_orden_basura', 0)->get();
                foreach ($query as $q) {
                    $inventario = $q->inventario_recepcion;
                    if ($inventario->disponibles >= $q->basura) {
                        $inventario->disponibles -= $q->basura;
                        $inventario->save();

                        $q->estado_orden_basura = 1;
                        $q->save();
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
