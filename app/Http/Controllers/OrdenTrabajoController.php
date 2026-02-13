<?php

namespace yura\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use yura\Modelos\DesgloseRecepcion;
use yura\Modelos\Despachador;
use yura\Modelos\OrdenTrabajo;
use yura\Modelos\SalidasRecepcion;
use yura\Modelos\Submenu;
use yura\Modelos\Variedad;

class OrdenTrabajoController extends Controller
{
    public function inicio(Request $request)
    {
        $recetas = Variedad::where('estado', 1)
            ->where('receta', 1)
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.postcocecha.orden_trabajo.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'recetas' => $recetas,
        ]);
    }

    public function listar_reporte(Request $request)
    {
        $ordenes = OrdenTrabajo::join('detalle_import_pedido as d', 'd.id_detalle_import_pedido', '=', 'orden_trabajo.id_detalle_import_pedido')
            ->join('import_pedido as p', 'p.id_import_pedido', '=', 'd.id_import_pedido')
            ->select('orden_trabajo.*')->distinct()
            ->where('p.fecha', $request->fecha);
        if ($request->variedad != '')
            $ordenes = $ordenes->where('d.id_variedad', $request->variedad);
        $ordenes = $ordenes->orderBy('orden_trabajo.id_detalle_import_pedido')
            ->orderBy('orden_trabajo.longitud')
            ->get();
        $listado = [];
        foreach ($ordenes as $item) {
            $listado[] = [
                'orden' => $item
            ];
        }
        $despachadores = Despachador::where('estado', 1)
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.postcocecha.orden_trabajo.partials.listado', [
            'listado' => $listado,
            'despachadores' => $despachadores,
        ]);
    }

    public function despachar_orden_trabajo(Request $request)
    {
        DB::beginTransaction();
        try {
            $orden_trabajo = OrdenTrabajo::find($request->id);
            if ($orden_trabajo->id_despachador != '' || 1) {
                $orden_trabajo->entregado = 1;
                $orden_trabajo->save();

                // --------- REGISTRAR LAS SALIDAS ------------ //
                foreach ($orden_trabajo->detalles as $d) {
                    $inventarios = DesgloseRecepcion::where('estado', 1)
                        ->where('disponibles', '>', 0)
                        ->where('id_variedad', $d->id_variedad)
                        ->orderBy('fecha', 'asc')
                        ->get();

                    $sacar = $d->tallos;
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

                    $new_salida = new SalidasRecepcion();
                    $new_salida->id_variedad = $d->id_variedad;
                    $new_salida->fecha = $orden_trabajo->fecha;
                    $new_salida->cantidad = $d->tallos;
                    $new_salida->disponibles = $d->tallos;
                    $new_salida->basura = 0;
                    $new_salida->save();

                    /* TABLA RESUMEN_FECHAS */
                    Artisan::call('resumen:fecha', [
                        'fecha' => $orden_trabajo->fecha,
                        'variedad' => $d->id_variedad,
                        'dev' => 1,
                    ]);
                }
                $success = true;
                $msg = 'Se ha <strong>DESPACHADO</strong> la orden de trabajo correctamente';
                bitacora('ORDEN_TRABAJO', $orden_trabajo->id_orden_trabajo, 'U', 'DESPACHAR OT desde ORDENES DE TRABAJO (' . $request->armar . ' ramos)');
            } else {
                $success = false;
                $msg = '<div class="alert alert-warning text-center">Antes de DESPACHAR la OT debes asiganarle un responsable</div>';
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

    public function store_armar(Request $request)
    {
        DB::beginTransaction();
        try {
            $model = OrdenTrabajo::find($request->id);
            if ($model->id_despachador != '') {
                $model->ramos_armados += $request->armar;
                if ($model->ramos == $model->ramos_armados)
                    $model->armado = 1;
                $model->save();

                foreach ($model->detalles as $d) {
                    /* TABLA RESUMEN_FECHAS */
                    Artisan::call('resumen:fecha', [
                        'fecha' => $model->fecha,
                        'variedad' => $d->id_variedad,
                        'dev' => 1,
                    ]);
                }
                $success = true;
                $msg = 'Se ha <strong>DESPACHADO</strong> la orden de trabajo correctamente';
                bitacora('ORDEN_TRABAJO', $model->id_orden_trabajo, 'U', 'ARMAR OT desde ORDENES DE TRABAJO (' . $request->armar . ' ramos)');
            } else {
                $success = false;
                $msg = '<div class="alert alert-warning text-center">Antes de ARMAR la OT debes asiganarle un responsable</div>';
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

    public function update_despachador(Request $request)
    {
        DB::beginTransaction();
        try {
            $orden_trabajo = OrdenTrabajo::find($request->id);
            $orden_trabajo->id_despachador = $request->despachador;
            $orden_trabajo->save();

            $success = true;
            $msg = 'Se ha <strong>ASIGNADO</strong> el despachador correctamente';
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
