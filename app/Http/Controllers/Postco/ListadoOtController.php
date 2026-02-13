<?php

namespace yura\Http\Controllers\Postco;

use Illuminate\Http\Request;
use yura\Http\Controllers\Controller;
use yura\Modelos\Variedad;
use yura\Modelos\Submenu;
use yura\Modelos\OtPostco;
use yura\Modelos\Despachador;
use yura\Modelos\DesgloseRecepcion;
use yura\Modelos\SalidasRecepcion;
use Picqer\Barcode\BarcodeGeneratorHTML;
use PDF;
use Illuminate\Support\Facades\DB;
use yura\Modelos\MotivoReclamo;
use yura\Modelos\OtReclamo;

class ListadoOtController extends Controller
{
    public function inicio(Request $request)
    {
        $variedades = Variedad::where('estado', 1)
            ->where('receta', 1)
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.postco.ot_postco.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'variedades' => $variedades,
        ]);
    }

    public function listar_reporte(Request $request)
    {
        $listado = OtPostco::join('postco as p', 'p.id_postco', '=', 'ot_postco.id_postco')
            ->select('ot_postco.*')->distinct()
            ->where('p.fecha', '>=', $request->desde)
            ->where('p.fecha', '<=', $request->hasta);
        if ($request->variedad != 'T')
            $listado = $listado->where('p.id_variedad', $request->variedad);
        $listado = $listado->orderBy('ot_postco.id_ot_postco')
            ->get();
        $despachadores = Despachador::where('estado', 1)
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.postco.ot_postco.partials.listado', [
            'listado' => $listado,
            'despachadores' => $despachadores,
        ]);
    }

    public function modal_reclamos(Request $request)
    {
        $ot_postco = OtPostco::find($request->id);
        $motivos_reclamo = MotivoReclamo::where('estado', 1)
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.postco.ot_postco.forms.modal_reclamos', [
            'ot_postco' => $ot_postco,
            'motivos_reclamo' => $motivos_reclamo,
        ]);
    }

    public function exportar_orden_trabajo_pdf(Request $request)
    {
        $barCode = new BarcodeGeneratorHTML();
        $model = OtPostco::find($request->id);
        $datos = [
            'model' => $model,
        ];
        return PDF::loadView('adminlte.gestion.postco.ot_postco.partials.pdf_orden_trabajo', compact('datos', 'barCode'))
            ->setPaper(array(0, 0, 700, 500), 'A4')->stream();
    }

    public function despachar_orden_trabajo(Request $request)
    {
        DB::beginTransaction();
        try {
            $orden_trabajo = OtPostco::find($request->id);
            if ($orden_trabajo->id_despachador != '' || 1) {
                // --------- VALIDAR DISPONIBLES --------- //
                $valida = true;
                foreach ($orden_trabajo->detalles as $det) {
                    $inventario = getTotalInventarioByVariedad($det->id_item);
                    if ($inventario < $det->unidades * $orden_trabajo->ramos) {
                        $valida = false;
                    }
                }
                if ($valida) {
                    $orden_trabajo->estado = 'D';
                    $orden_trabajo->save();

                    $postco = $orden_trabajo->postco;
                    $postco->despachados += $orden_trabajo->ramos;
                    $postco->save();

                    // --------- REGISTRAR LAS SALIDAS ------------ //
                    foreach ($orden_trabajo->detalles as $d) {
                        $inventarios = DesgloseRecepcion::where('estado', 1)
                            ->where('disponibles', '>', 0)
                            ->where('id_variedad', $d->id_item)
                            ->orderBy('fecha', 'asc')
                            ->get();

                        $sacar = $d->unidades * $orden_trabajo->ramos;
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
                        $new_salida->id_variedad = $d->id_item;
                        $new_salida->fecha = $orden_trabajo->fecha;
                        $new_salida->cantidad = $d->unidades * $orden_trabajo->ramos;
                        $new_salida->disponibles = $d->unidades * $orden_trabajo->ramos;
                        $new_salida->basura = 0;
                        $new_salida->save();
                    }
                    $success = true;
                    $msg = 'Se ha <strong>DESPACHADO</strong> la orden de trabajo correctamente';
                    bitacora('ORDEN_TRABAJO', $orden_trabajo->id_ot_postco, 'U', 'DESPACHAR OT desde ORDENES DE TRABAJO');
                } else {
                    $success = false;
                    $msg = '<div class="alert alert-warning text-center">No hay flor disponible en el inventario actualmente</div>';
                }
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
            $model = OtPostco::find($request->id);
            if ($model->id_despachador != '') {
                $model->armados += $request->armar;
                if ($model->ramos == $model->armados)
                    $model->estado = 'A';
                $model->save();

                $postco = $model->postco;
                $postco->armados += $request->armar;
                $postco->save();

                $success = true;
                $msg = 'Se ha <strong>ARMADO</strong> los ramos de la ot correctamente';
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

    public function store_reclamo(Request $request)
    {
        try {
            DB::beginTransaction();
            $model = new OtReclamo();
            $model->id_ot_postco = $request->id;
            $model->fecha = $request->fecha;
            $model->cantidad = $request->cantidad;
            $model->link = $request->link;
            $model->id_motivo_reclamo = $request->id_motivo;
            $model->save();
            $model->id_ot_reclamo = DB::table('ot_reclamo')
                ->select(DB::raw('max(id_ot_reclamo) as id'))
                ->get();

            DB::commit();
            $success = true;
            $msg = 'Se ha <strong>GRABADO</strong> un nuevo reclamo correctamente';
            bitacora('ot_reclamo', $model->id_ot_reclamo, 'I', 'NUEVO reclamo');
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

    public function update_reclamo(Request $request)
    {
        try {
            DB::beginTransaction();
            $model = OtReclamo::find($request->id);
            $model->fecha = $request->fecha;
            $model->cantidad = $request->cantidad;
            $model->link = $request->link;
            $model->id_motivo_reclamo = $request->id_motivo;
            $model->save();

            DB::commit();
            $success = true;
            $msg = 'Se ha <strong>ACTUALIZADO</strong> el reclamo correctamente';
            bitacora('ot_reclamo', $model->id_ot_reclamo, 'U', 'EDITAR reclamo');
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

    public function eliminar_reclamo(Request $request)
    {
        try {
            DB::beginTransaction();
            $model = OtReclamo::find($request->id);
            $ramos = $model->cantidad;
            $id_ot = $model->id_ot_postco;
            $id_reclamo = $model->id_motivo_reclamo;
            $model->delete();

            DB::commit();
            $success = true;
            $msg = 'Se ha <strong>ACTUALIZADO</strong> el reclamo correctamente';
            bitacora('motivo_reclamo', $id_reclamo, 'U', 'ELIMINAR reclamo de ' . $ramos . ' ramos OT#' . $id_ot);
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

    public function update_observacion(Request $request)
    {
        try {
            DB::beginTransaction();
            $model = OtPostco::find($request->id_ot);
            $model->observacion = $request->observacion;
            $model->save();

            $success = true;
            $msg = 'Se ha <strong>GRABADO</strong> la observacion correctamente';
            bitacora('OT_POSTCO', $model->id_ot_postco, 'U', 'MODIFICAR la observacion de la OT desde PREPRODUCCION (' . $model->ramos . ' ramos)');
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
