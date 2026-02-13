<?php

namespace yura\Http\Controllers\Propagacion;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use yura\Http\Controllers\Controller;
use yura\Jobs\jobActualizarDisponibilidad;
use yura\Modelos\ContenedorPropag;
use yura\Modelos\DetalleEnraizamientoSemanal;
use yura\Modelos\EnraizamientoSemanal;
use yura\Modelos\Planta;
use yura\Modelos\Submenu;
use yura\Modelos\Variedad;

class EnraizamientoController extends Controller
{
    public function inicio(Request $request)
    {
        $plantas = Planta::where('estado', 1)->orderBy('nombre')->get();
        return view('adminlte.gestion.propagacion.enraizamiento.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'plantas' => $plantas,
            'contenedores' => ContenedorPropag::where('estado', 1)->orderBy('cantidad')->orderBy('nombre')->get(),
        ]);
    }

    public function store_enraizamiento(Request $request)
    {
        $finca = getFincaActiva();
        $msg = '<div class="alert alert-success text-center">Se ha guardado la información satisfactoriamente</div>';
        $success = true;
        $semana_ini = getSemanaByDate($request->fecha);
        foreach ($request->data as $d) {
            $enr_sem = EnraizamientoSemanal::All()
                ->where('semana_ini', $semana_ini->codigo)
                ->where('id_variedad', $d['variedad'])
                ->where('id_empresa', $finca)
                ->first();
            if ($enr_sem == '') {
                $new_enr = true;
                $enr_sem = new EnraizamientoSemanal();
                $enr_sem->semana_ini = $semana_ini->codigo;
                $enr_sem->id_variedad = $d['variedad'];
                $enr_sem->cantidad_siembra = $d['cantidad'];
                $enr_sem->id_empresa = $finca;
            } else {
                $new_enr = false;
                $enr_sem->cantidad_siembra += $d['cantidad'];
            }
            $enr_sem->cantidad_semanas = $d['semanas'];
            $semana_fin = getSemanaByDate(opDiasFecha('+', ($d['semanas'] * 7), $semana_ini->fecha_inicial));
            $enr_sem->semana_fin = $semana_fin->codigo;
            if ($enr_sem->save()) {
                if ($new_enr)
                    $enr_sem = EnraizamientoSemanal::All()->last();

                /* =============== DetalleEnraizamientoSemanal ================== */
                $det_enr = new DetalleEnraizamientoSemanal();
                $det_enr->id_enraizamiento_semanal = $enr_sem->id_enraizamiento_semanal;
                $det_enr->fecha = $request->fecha;
                $det_enr->cantidad_siembra = $d['cantidad'];
                $det_enr->id_contenedor_propag = $d['contenedor'];
                $det_enr->save();

                /* ------------ ACTUALIZAR propag_disponibilidad ------------ */
                jobActualizarDisponibilidad::dispatch($enr_sem->semana_ini, getLastSemanaByVariedad($enr_sem->id_variedad)->codigo, $enr_sem->id_variedad, $finca)
                    ->onQueue('propag');
            } else {
                $success = false;
                $msg = '<div class="alert alert-danger text-center">Ha ocurrido un problema al guardar el enraizamiento</div>';
            }
        }
        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }

    public function buscar_enraizamiento_semanal(Request $request)
    {
        $finca = getFincaActiva();
        $semana_ini = getSemanaByDate($request->fecha);
        $model = EnraizamientoSemanal::All()
            //->where('semana_ini', $semana_ini->codigo)
            ->where('id_variedad', $request->variedad)
            ->where('id_empresa', $finca)
            ->last();
        $variedad = Variedad::find($request->variedad);
        if ($model != '')
            return [
                'cantidad_semanas' => $model->cantidad_semanas,
                'bandeja' => $variedad->id_contenedor_propag,
            ];
        else
            return [
                'cantidad_semanas' => '',
                'bandeja' => $variedad->id_contenedor_propag,
            ];
    }

    public function listar_enraizamientos(Request $request)
    {
        $finca = getFincaActiva();
        $detalles = DetalleEnraizamientoSemanal::join('enraizamiento_semanal as es', 'es.id_enraizamiento_semanal', '=', 'detalle_enraizamiento_semanal.id_enraizamiento_semanal')
            ->join('variedad as v', 'v.id_variedad', '=', 'es.id_variedad')
            ->select('detalle_enraizamiento_semanal.*')
            ->where('detalle_enraizamiento_semanal.fecha', $request->fecha)
            ->where('es.id_empresa', $finca)
            ->orderBy('v.nombre')->get();
        return view('adminlte.gestion.propagacion.enraizamiento.partials.listado', [
            'detalles' => $detalles,
            'contenedores' => ContenedorPropag::where('estado', 1)->orderBy('cantidad')->orderBy('nombre')->get(),
        ]);
    }

    public function update_enraizamiento(Request $request)
    {
        if ($request->cantidad > 0) {
            $enrz = EnraizamientoSemanal::find($request->id);
            $enrz->cantidad_semanas = $request->cantidad;
            $semana_fin = getSemanaByDate(opDiasFecha('+', ($enrz->cantidad_semanas * 7), $enrz->semana_ini()->fecha_inicial));
            $enrz->semana_fin = $semana_fin->codigo;
            if ($enrz->save()) {
                /* ------------ ACTUALIZAR propag_disponibilidad ------------ */
                jobActualizarDisponibilidad::dispatch($enrz->semana_ini, getLastSemanaByVariedad($enrz->id_variedad)->codigo, $enrz->id_variedad, getFincaActiva())
                    ->onQueue('propag');
                $success = true;
                $msg = '<div class="alert alert-success text-center">Se ha guardado la información satisfactoriamente</div>';
            } else {
                $success = false;
                $msg = '<div class="alert alert-warning text-center">Ha ocurrido un problema al guardar la información</div>';
            }
        } else {
            $success = false;
            $msg = '<div class="alert alert-warning text-center">La cantidad es obligatoria</div>';
        }
        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }

    public function update_detalle_enraizamiento(Request $request)
    {
        if ($request->cantidad > 0) {
            $det = DetalleEnraizamientoSemanal::find($request->id);
            $det->cantidad_siembra = $request->cantidad;
            $det->id_contenedor_propag = $request->contenedor;
            if ($det->save()) {
                $enr = $det->enraizamiento_semanal;
                $total = 0;
                foreach ($enr->detalles as $d)
                    $total += $d->cantidad_siembra;
                $enr->cantidad_siembra = $total;
                $enr->save();

                /* ------------ ACTUALIZAR propag_disponibilidad ------------ */
                jobActualizarDisponibilidad::dispatch($enr->semana_ini, getLastSemanaByVariedad($enr->id_variedad)->codigo, $enr->id_variedad, getFincaActiva())
                    ->onQueue('propag');

                $success = true;
                $msg = '<div class="alert alert-success text-center">Se ha guardado la información satisfactoriamente</div>';
            } else {
                $success = false;
                $msg = '<div class="alert alert-warning text-center">Ha ocurrido un problema al guardar la información</div>';
            }
        } else {
            $success = false;
            $msg = '<div class="alert alert-warning text-center">La cantidad es obligatoria</div>';
        }
        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }

    public function delete_detalle_enraizamiento(Request $request)
    {
        $det = DetalleEnraizamientoSemanal::find($request->id);
        $enr = $det->enraizamiento_semanal;

        if ($det->delete()) {
            $total = 0;
            foreach ($enr->detalles as $d)
                $total += $d->cantidad_siembra;
            $enr->cantidad_siembra = $total;
            $enr->save();

            /* ------------ ACTUALIZAR propag_disponibilidad ------------ */
            jobActualizarDisponibilidad::dispatch($enr->semana_ini, getLastSemanaByVariedad($enr->id_variedad)->codigo, $enr->id_variedad, getFincaActiva())
                ->onQueue('propag');

            $success = true;
            $msg = '<div class="alert alert-success text-center">Se ha eliminado la siembra satisfactoriamente</div>';
        } else {
            $success = false;
            $msg = '<div class="alert alert-warning text-center">Ha ocurrido un problema al eliminar la siembra</div>';
        }
        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }
}
