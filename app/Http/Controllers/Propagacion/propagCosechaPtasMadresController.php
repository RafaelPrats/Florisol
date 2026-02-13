<?php

namespace yura\Http\Controllers\Propagacion;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use yura\Http\Controllers\Controller;
use yura\Modelos\Cama;
use yura\Modelos\CosechaPlantasMadres;
use yura\Modelos\ResumenPropagacion;
use yura\Modelos\Submenu;
use yura\Modelos\Variedad;

class propagCosechaPtasMadresController extends Controller
{
    public function inicio(Request $request)
    {
        $finca = getFincaActiva();
        $camas = DB::table('ciclo_cama as cc')
            ->join('cama as c', 'c.id_cama', 'cc.id_cama')
            ->join('variedad as v', 'v.id_variedad', 'cc.id_variedad')
            ->select('cc.id_cama', 'c.nombre')->distinct()
            ->where('cc.activo', 1)
            ->where('cc.id_empresa', $finca)
            ->orderBy('c.nombre')
            ->get();
        return view('adminlte.gestion.propagacion.cosecha_plantas_madres.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'camas' => $camas,
        ]);
    }

    public function listar_cosechas(Request $request)
    {
        $finca = getFincaActiva();
        $cosechas = CosechaPlantasMadres::join('variedad as var', 'var.id_variedad', '=', 'cosecha_plantas_madres.id_variedad')
            ->select('cosecha_plantas_madres.*', 'var.siglas as siglas_variedad')
            ->where('cosecha_plantas_madres.fecha', $request->fecha)
            ->where('cosecha_plantas_madres.id_empresa', $finca)
            ->orderBy('cosecha_plantas_madres.id_variedad')->get();
        $camas_activas = DB::table('ciclo_cama as cc')
            ->join('cama as c', 'c.id_cama', 'cc.id_cama')
            ->join('variedad as v', 'v.id_variedad', 'cc.id_variedad')
            ->select('cc.id_cama', 'c.nombre', 'cc.id_variedad', 'v.siglas')->distinct()
            ->where('cc.activo', 1)
            ->where('cc.id_empresa', $finca)
            ->orderBy('c.nombre')
            ->get();
        $cosecha_x_variedad = DB::table('cosecha_plantas_madres as cos')
            ->join('variedad as v', 'v.id_variedad', 'cos.id_variedad')
            ->select(DB::raw('sum(cantidad) as cantidad'), 'cos.id_variedad', 'v.nombre')
            ->where('cos.fecha', $request->fecha)
            ->where('cos.id_empresa', $finca)
            ->groupBy('cos.id_variedad', 'v.nombre')
            ->orderBy('v.siglas')
            ->get();
        return view('adminlte.gestion.propagacion.cosecha_plantas_madres.partials.listado_cosechas', [
            'cosechas' => $cosechas,
            'camas' => $camas_activas,
            'cosecha_x_variedad' => $cosecha_x_variedad,
            'semana' => getSemanaByDate($request->fecha),
        ]);
    }

    public function select_cama(Request $request)
    {
        $cama = Cama::find($request->cama);
        $ciclo_actual = $cama->ciclo_actual();
        return [
            'variedad' => $ciclo_actual->variedad
        ];
    }

    public function store_cosechas(Request $request)
    {
        $success = true;
        $msg = '<div class="alert alert-success text-center">Se ha ingresado la cosecha satisfactoriamente</div>';
        if ($request->fecha != '') {
            if (count($request->cantidades) > 0) {
                $semana = getSemanaByDate($request->fecha);
                foreach ($request->cantidades as $item) {
                    $cosecha = new CosechaPlantasMadres();
                    $cosecha->fecha = $request->fecha;
                    $cosecha->id_cama = $item['cama'];
                    $cosecha->id_variedad = $item['variedad'];
                    $cosecha->cantidad = $item['cantidad'];
                    $cosecha->fecha_registro = date('Y-m-d H:i:s');
                    $cosecha->id_empresa = Cama::find($item['cama'])->id_empresa;
                    if ($cosecha->save()) {
                        $cosecha = CosechaPlantasMadres::All()->last();
                        //bitacora('cosecha_plantas_madres', $cosecha->id_cosecha_plantas_madres, 'I', 'Insercion de una cosecha_plantas_madres');

                        $this->actualizar_cosecha($item['variedad'], $semana, $cosecha->id_empresa);
                    } else {
                        $success = false;
                        $msg = '<div class="alert alert-danger text-center">Ha ocurrido un problema al guardar la cosecha</div>';
                    }
                }
            } else {
                $success = false;
                $msg = '<div class="alert alert-danger text-center">Debe ingresar las cantidades</div>';
            }
        } else {
            $success = false;
            $msg = '<div class="alert alert-danger text-center">Debe indicar la fecha</div>';
        }
        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }

    public function update_cosecha(Request $request)
    {
        $cosecha = CosechaPlantasMadres::find($request->cosecha);
        if ($cosecha != '') {
            $cosecha->id_cama = $request->cama;
            $cosecha->id_variedad = $request->variedad;
            $cosecha->cantidad = $request->cantidad;
            if ($cosecha->save()) {
                $success = true;
                $msg = '<div class="alert alert-success text-center">Se ha actualizado la cosecha satisfactoriamente</div>';
                //bitacora('cosecha_plantas_madres', $cosecha->id_cosecha_plantas_madres, 'U', 'Update de una cosecha_plantas_madres');

                $this->actualizar_cosecha($request->variedad, $cosecha->semana(), $cosecha->id_empresa);
            } else {
                $success = false;
                $msg = '<div class="alert alert-danger text-center">Ha ocurrido un problema al guardar la cosecha</div>';
            }
        } else {
            $success = false;
            $msg = '<div class="alert alert-danger text-center">No se ha encontrado la cosecha</div>';
        }
        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }

    public function eliminar_cosecha(Request $request)
    {
        $cosecha = CosechaPlantasMadres::find($request->cosecha);
        $variedad = $cosecha->id_variedad;
        $semana = $cosecha->semana();
        $finca = $cosecha->id_empresa;
        if ($cosecha != '') {
            if ($cosecha->delete()) {
                $success = true;
                $msg = '<div class="alert alert-success text-center">Se ha eliminado la cosecha satisfactoriamente</div>';

                $this->actualizar_cosecha($variedad, $semana, $finca);
            } else {
                $success = false;
                $msg = '<div class="alert alert-danger text-center">Ha ocurrido un problema al eliminar la cosecha</div>';
            }
        } else {
            $success = false;
            $msg = '<div class="alert alert-danger text-center">No se ha encontrado la cosecha</div>';
        }
        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }

    /* ------------------------------------------------------------------- */
    function actualizar_cosecha($variedad, $semana, $finca)
    {
        $model = ResumenPropagacion::All()
            ->where('id_variedad', $variedad)
            ->where('semana', $semana->codigo)
            ->where('id_empresa', $finca)
            ->first();
        if ($model == '') {
            $model = new ResumenPropagacion();
            $model->id_variedad = $variedad;
            $model->semana = $semana->codigo;
            $model->id_empresa = $finca;
        }
        $esquejes_cosechados = DB::table('cosecha_plantas_madres as cos')
            ->select(DB::raw('sum(cantidad) as cantidad'))
            ->where('cos.id_variedad', $variedad)
            ->where('cos.fecha', '>=', $semana->fecha_inicial)
            ->where('cos.fecha', '<=', $semana->fecha_final)
            ->where('cos.id_empresa', $finca)
            ->get()[0]->cantidad;
        $model->esquejes_cosechados = $esquejes_cosechados > 0 ? $esquejes_cosechados : 0;
        $model->esquejes_x_planta = $model->plantas_sembradas > 0 ? round($model->esquejes_cosechados / $model->plantas_sembradas, 2) : 0;
        $model->save();
    }
}