<?php

namespace yura\Http\Controllers\Postco;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use yura\Http\Controllers\Controller;
use yura\Modelos\DetalleApiStoreCajas;
use yura\Modelos\InventarioRecepcion;
use yura\Modelos\Planta;
use yura\Modelos\Submenu;

class InventarioRecepcionController extends Controller
{
    public function inicio(Request $request)
    {
        $finca = getFincaActiva();
        $plantas = Planta::where('estado', 1)
            ->where('id_empresa', $finca)
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.postco.ingreso_inventario.inicio', [
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
            ->leftJoin('detalle_api_store_cajas as da', function ($join) use ($finca) {
                $join->on('da.id_variedad', '=', 'i.id_variedad')
                    ->where('da.id_empresa', '=', $finca);
            })
            ->select('v.id_planta', 'p.nombre')
            ->distinct()
            ->where('i.id_empresa', $finca)
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
                ->leftJoin('detalle_api_store_cajas as da', function ($join) use ($finca) {
                    $join->on('da.id_variedad', '=', 'i.id_variedad')
                        ->where('da.id_empresa', '=', $finca);
                })
                ->select(
                    'i.*',
                    'v.nombre'
                )
                ->distinct()
                ->where('i.id_empresa', $finca)
                ->where('v.id_planta', $pta->id_planta)
                ->where(function ($query) {
                    $query->where('i.disponibles', '>', 0)
                        ->orWhere('da.estado', 'P');
                })
                ->orderBy('i.fecha')
                ->get();
            $listado[] = [
                'planta' => $pta,
                'variedades' => $variedades,
            ];
        }

        return view('adminlte.gestion.postco.ingreso_inventario.partials.listado', [
            'listado' => $listado,
        ]);
    }

    public function modal_add(Request $request)
    {
        $finca = getFincaActiva();
        $plantas = Planta::where('estado', 1)
            ->where('id_empresa', $finca)
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.postco.ingreso_inventario.forms.modal_add', [
            'plantas' => $plantas
        ]);
    }

    public function store_inventario(Request $request)
    {
        try {
            DB::beginTransaction();
            $finca = getFincaActiva();
            foreach (json_decode($request->data) as $data) {
                $model_inventario = InventarioRecepcion::where('id_variedad', $data->variedad)
                    ->where('fecha', $data->fecha)
                    ->where('tallos_x_ramo', $data->tallos_x_ramo)
                    ->where('longitud', $data->longitud)
                    ->where('id_empresa', $finca)
                    ->first();
                if ($model_inventario == '') {
                    $model_inventario = new InventarioRecepcion();
                    $model_inventario->id_variedad = $data->variedad;
                    $model_inventario->fecha = $data->fecha;
                    $model_inventario->tallos_x_ramo = $data->tallos_x_ramo;
                    $model_inventario->ramos = $data->ramos;
                    $model_inventario->longitud = $data->longitud;
                    $model_inventario->disponibles = $data->ramos * $data->tallos_x_ramo;
                    $model_inventario->id_empresa = $finca;
                    $model_inventario->save();
                } else {
                    $model_inventario->ramos += $data->ramos;
                    $model_inventario->disponibles += $data->ramos * $data->tallos_x_ramo;
                    $model_inventario->save();
                }
            }

            $success = true;
            $msg = 'Se ha <strong>GRABADO</strong> la informacion correctamente';

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

    public function recibir_all_pendientes(Request $request)
    {
        try {
            DB::beginTransaction();

            foreach (json_decode($request->data) as $data) {
                $model = InventarioRecepcion::find($data->id_inv);
                $model->ramos += $data->ramos;
                $model->disponibles += $data->ramos * $model->tallos_x_ramo;
                $model->save();

                $detApi = DetalleApiStoreCajas::find($data->id_detApi);
                $detApi->recibido += $data->ramos;
                $detApi->estado = 'R';
                $detApi->save();
            }

            $success = true;
            $msg = 'Se ha <strong>GRABADO</strong> la informacion correctamente';

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
