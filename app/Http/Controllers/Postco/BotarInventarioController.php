<?php

namespace yura\Http\Controllers\Postco;

use DB;
use Illuminate\Http\Request;
use yura\Http\Controllers\Controller;
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
        $motivos = MotivoBaja::orderBy('nombre')->get();

        return view('adminlte.gestion.postco.botar_inventario.partials.listado', [
            'listado' => $listado,
            'motivos' => $motivos,
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

    public function botar_inventario(Request $request)
    {
        try {
            DB::beginTransaction();
            $model = InventarioRecepcion::find($request->id);
            if ($model->disponibles < $request->botar) {
                DB::rollBack();
                $success = false;
                $msg = 'No se pueden botar mas tallos de los disponibles';

                return [
                    'success' => $success,
                    'mensaje' => $msg,
                ];
            } else {
                $model->disponibles -= $request->botar;
                $model->save();

                $salidas = new SalidasRecepcion();
                $salidas->id_inventario_recepcion = $model->id_inventario_recepcion;
                $salidas->id_variedad = $model->id_variedad;
                $salidas->id_motivo_baja = $request->motivo;
                $salidas->cantidad = 0;
                $salidas->basura = $request->botar;
                $salidas->fecha = $request->fecha;
                $salidas->save();

                $success = true;
                $msg = 'Se ha <strong>ELIMINADO</strong> el inventario correctamente';
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
}
