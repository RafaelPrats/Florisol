<?php

namespace yura\Http\Controllers\Postcosecha;

use Illuminate\Http\Request;
use yura\Http\Controllers\Controller;
use yura\Modelos\Caja;
use yura\Modelos\ClasificacionRamo;
use yura\Modelos\ClasificacionUnitaria;
use yura\Modelos\Empaque;
use yura\Modelos\Submenu;
use yura\Modelos\UnidadMedida;

class ClasificacionesController extends Controller
{
    public function inicio(Request $request)
    {
        return view('adminlte.gestion.postcocecha.clasificaciones.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
        ]);
    }

    public function listar_ramos(Request $request)
    {
        $ramos = ClasificacionRamo::orderBy('nombre')->get();
        $unidades = UnidadMedida::where('estado', 1)
            ->orderBy('siglas')
            ->get();
        return view('adminlte.gestion.postcocecha.clasificaciones.partials.ramos', [
            'ramos' => $ramos,
            'unidades' => $unidades,
        ]);
    }

    public function listar_presentaciones(Request $request)
    {
        $finca = getFincaActiva();
        $empaques = Empaque::where('id_empresa', $finca)
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.postcocecha.clasificaciones.partials.presentaciones', [
            'empaques' => $empaques,
        ]);
    }

    public function store_ramo(Request $request)
    {
        $model = new ClasificacionRamo();
        $model->nombre = $request->nombre;
        $model->id_unidad_medida = $request->unidad_medida;
        $model->estandar = $request->estandar;
        $model->id_empresa = getFincaActiva();
        $model->save();

        if ($request->estandar == 1) {  // cambiar a 0 el campo "estandar" de las demas clasificaciones_ramo
            $model = ClasificacionRamo::All()->last();
            $otros = ClasificacionRamo::All()
                ->where('id_clasificacion_ramo', '!=', $model->id_clasificacion_ramo);
            foreach ($otros as $o) {
                $o->estandar = 0;
                $o->save();
            }
        }

        $success = true;
        $msg = 'Se ha <strong>creado</strong> una nueva clasificación satisfactoriamente';
        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }

    public function store_presentacion(Request $request)
    {
        $model = new Empaque();
        $model->nombre = $request->nombre;
        $model->id_empresa = getFincaActiva();
        $model->save();

        $success = true;
        $msg = 'Se ha <strong>creado</strong> una nueva presentación satisfactoriamente';
        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }

    public function update_ramo(Request $request)
    {
        $model = ClasificacionRamo::find($request->id);
        $model->nombre = $request->nombre;
        $model->id_unidad_medida = $request->unidad_medida;
        $model->estandar = $request->estandar;
        $model->id_empresa = getFincaActiva();
        $model->save();

        if ($request->estandar == 1) {  // cambiar a 0 el campo "estandar" de las demas clasificaciones_ramo
            $otros = ClasificacionRamo::All()
                ->where('id_clasificacion_ramo', '!=', $model->id_clasificacion_ramo);
            foreach ($otros as $o) {
                $o->estandar = 0;
                $o->save();
            }
        }

        $success = true;
        $msg = 'Se ha <strong>actualizado</strong> la clasificación satisfactoriamente';
        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }

    public function update_presentacion(Request $request)
    {
        $model = Empaque::find($request->id);
        $model->nombre = $request->nombre;
        $model->id_empresa = getFincaActiva();
        $model->save();

        $success = true;
        $msg = 'Se ha <strong>actualizado</strong> la presentación satisfactoriamente';
        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }

    public function cambiar_estado_ramo(Request $request)
    {
        $model = ClasificacionRamo::find($request->id);
        $model->estado = $model->estado == 1 ? 0 : 1;
        $model->save();

        $success = true;
        $msg = 'Se ha <strong>cambiado el estado</strong> de la clasificación satisfactoriamente';
        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }

    public function cambiar_estado_presentacion(Request $request)
    {
        $model = Empaque::find($request->id);
        $model->estado = $model->estado == 1 ? 0 : 1;
        $model->save();

        $success = true;
        $msg = 'Se ha <strong>cambiado el estado</strong> de la presentación satisfactoriamente';
        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }

    public function listar_cajas(Request $request)
    {
        $finca = getFincaActiva();
        $cajas = Caja::where('id_empresa', $finca)
            ->orderBy('nombre')
            ->get();

        return view('adminlte.gestion.postcocecha.clasificaciones.partials.cajas', [
            'cajas' => $cajas
        ]);
    }

    public function store_caja(Request $request)
    {
        $model = new Caja;
        $model->nombre = $request->nombre;
        $model->factor_conversion = $request->factor_conversion;
        $model->peso = $request->peso;
        $model->id_empresa = getFincaActiva();
        $model->save();

        $success = true;
        $msg = 'Se ha <strong>creado</strong> una nueva presentación satisfactoriamente';
        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }

    public function update_caja(Request $request)
    {
        $model = Caja::find($request->id);
        $model->nombre = $request->nombre;
        $model->factor_conversion = $request->factor_conversion;
        $model->peso = $request->peso;
        $model->id_empresa = getFincaActiva();
        $model->save();

        $success = true;
        $msg = 'Se ha <strong>actualizado</strong> la caja satisfactoriamente';

        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }

    public function cambiar_estado_caja(Request $request)
    {
        $model = Caja::find($request->id);
        $model->estado = $model->estado == 1 ? 0 : 1;
        $model->save();

        $success = true;
        $msg = 'Se ha <strong>cambiado el estado</strong> de la caja satisfactoriamente';
        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }
}
