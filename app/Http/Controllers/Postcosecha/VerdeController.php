<?php

namespace yura\Http\Controllers\Postcosecha;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use yura\Http\Controllers\Controller;
use yura\Modelos\Ciclo;
use yura\Modelos\ClasificacionUnitaria;
use yura\Modelos\ClasificacionVerde;
use yura\Modelos\MonitoreoCalibre;

class VerdeController extends Controller
{
    public function add_verde(Request $request)
    {
        $finca = getFincaActiva();

        $var_cos = DB::table('recepcion as r')
            ->join('desglose_recepcion as dr', 'dr.id_recepcion', '=', 'r.id_recepcion')
            ->join('variedad as v', 'v.id_variedad', '=', 'dr.id_variedad')
            ->join('planta as p', 'v.id_planta', '=', 'p.id_planta')
            ->select('dr.id_variedad', 'v.siglas', 'v.nombre', DB::raw('sum(dr.cantidad_mallas * dr.tallos_x_malla) as cantidad'))
            ->where('r.fecha_ingreso', 'like', $request->fecha . '%')
            ->where('v.estado', 1)
            ->where('p.tiene_ciclos', 1)
            ->where('dr.id_empresa', $finca)
            ->groupBy('dr.id_variedad', 'v.nombre', 'v.siglas')
            ->orderBy('v.nombre')
            ->get();
        $variedades = [];
        foreach ($var_cos as $item) {
            $desecho = DB::table('monitoreo_calibre as m')
                ->join('ciclo as c', 'c.id_ciclo', '=', 'm.id_ciclo')
                ->select(DB::raw('avg(m.desecho) as desecho'))
                ->where('c.id_variedad', $item->id_variedad)
                ->where('c.id_empresa', $finca)
                ->where('m.fecha', $request->fecha)
                ->get()[0]->desecho;
            $calibre = DB::table('monitoreo_calibre as m')
                ->join('ciclo as c', 'c.id_ciclo', '=', 'm.id_ciclo')
                ->select(DB::raw('avg(m.calibre) as calibre'))
                ->where('m.fecha', $request->fecha)
                ->where('c.id_variedad', $item->id_variedad)
                ->where('c.id_empresa', $finca)
                ->get()[0]->calibre;

            array_push($variedades, [
                'id_variedad' => $item->id_variedad,
                'nombre' => $item->nombre,
                'siglas' => $item->siglas,
                'cosechado' => $item->cantidad,
                'calibre' => porcentaje(100 - $desecho, $calibre, 2),
            ]);
        }

        $ciclos = Ciclo::join('modulo as m', 'm.id_modulo', '=', 'ciclo.id_modulo')
            ->join('desglose_recepcion as dr', 'dr.id_modulo', '=', 'ciclo.id_modulo')
            ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
            ->join('variedad as v', 'v.id_variedad', '=', 'dr.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->select('ciclo.id_ciclo', 'ciclo.id_modulo', 'm.nombre as mod_nombre', 'v.siglas as var_nombre',
                DB::raw('avg(dr.tallos_x_malla) as tallos_x_malla'))
            ->where('dr.estado', 1)
            ->where('r.estado', 1)
            ->where('r.fecha_ingreso', 'like', $request->fecha . '%')
            ->where('ciclo.fecha_inicio', '<', $request->fecha)
            ->where('ciclo.fecha_fin', '>=', $request->fecha)
            ->where('p.tiene_ciclos', 1)
            ->where('ciclo.id_empresa', $finca)
            ->groupBy('ciclo.id_ciclo', 'ciclo.id_modulo', 'm.nombre', 'v.siglas')
            ->orderBy('m.nombre')
            ->get();

        return view('adminlte.gestion.postcocecha.ingreso_clasificacion.forms.verde', [
            'fecha' => $request->fecha,
            'variedades' => $variedades,
            'ciclos' => $ciclos,
            'getCalibreRamoEstandar' => getCalibreRamoEstandar(),
        ]);
    }

    public function store_monitoreo(Request $request)
    {
        $finca = getFincaActiva();
        foreach ($request->data as $d) {
            $eliminar = MonitoreoCalibre::All()
                ->where('id_ciclo', $d['ciclo'])
                ->where('fecha', $request->fecha)
                ->where('id_empresa', $finca);
            foreach ($eliminar as $e)
                $e->delete();

            if (isset($d['mallas']))
                foreach ($d['mallas'] as $m) {
                    $model = MonitoreoCalibre::All()
                        ->where('id_ciclo', $d['ciclo'])
                        ->where('fecha', $request->fecha)
                        ->where('num_malla', $m['num'])
                        ->where('id_empresa', $finca)
                        ->first();
                    if ($model == '') {
                        $model = new MonitoreoCalibre();
                        $model->id_ciclo = $d['ciclo'];
                        $model->id_empresa = $finca;
                        $model->fecha = $request->fecha;
                        $model->num_malla = $m['num'];
                    }
                    $model->peso = $m['peso'];
                    $model->calibre = $m['calibre'];
                    $model->desecho = $d['desecho'];
                    $model->save();
                }
        }
        return [
            'success' => true,
            'mensaje' => 'Se han <strong>guardado</strong> los muestreos satisfactoriamente',
        ];
    }

    public function listar_verde(Request $request)
    {
        $finca = getFincaActiva();

        $var_cos = DB::table('recepcion as r')
            ->join('desglose_recepcion as dr', 'dr.id_recepcion', '=', 'r.id_recepcion')
            ->join('variedad as v', 'v.id_variedad', '=', 'dr.id_variedad')
            ->join('planta as p', 'v.id_planta', '=', 'p.id_planta')
            ->select('dr.id_variedad', 'v.siglas', 'v.nombre', DB::raw('sum(dr.cantidad_mallas * dr.tallos_x_malla) as cantidad'))
            ->where('r.fecha_ingreso', 'like', $request->fecha . '%')
            ->where('v.estado', 1)
            ->where('p.tiene_ciclos', 1)
            ->where('dr.id_empresa', $finca)
            ->groupBy('dr.id_variedad', 'v.nombre', 'v.siglas')
            ->orderBy('v.nombre')
            ->get();
        $variedades = [];
        $total_cosechados = 0;
        foreach ($var_cos as $item) {
            $desecho = DB::table('monitoreo_calibre as m')
                ->join('ciclo as c', 'c.id_ciclo', '=', 'm.id_ciclo')
                ->select(DB::raw('avg(m.desecho) as desecho'))
                ->where('c.id_variedad', $item->id_variedad)
                ->where('c.id_empresa', $finca)
                ->where('m.fecha', $request->fecha)
                ->get()[0]->desecho;
            $calibre = DB::table('monitoreo_calibre as m')
                ->join('ciclo as c', 'c.id_ciclo', '=', 'm.id_ciclo')
                ->select(DB::raw('avg(m.calibre) as calibre'))
                ->where('m.fecha', $request->fecha)
                ->where('c.id_variedad', $item->id_variedad)
                ->where('c.id_empresa', $finca)
                ->get()[0]->calibre;

            $total_cosechados += $item->cantidad;
            array_push($variedades, [
                'id_variedad' => $item->id_variedad,
                'nombre' => $item->nombre,
                'siglas' => $item->siglas,
                'cosechado' => $item->cantidad,
                'calibre' => porcentaje(100 - $desecho, $calibre, 2),
            ]);
        }

        $getPromMonitoreoCalibreByFecha = getPromMonitoreoCalibreByFecha($request->fecha);
        $getCalibreRamoEstandar = getCalibreRamoEstandar();
        $total_ramos = ($total_cosechados * $getPromMonitoreoCalibreByFecha) / $getCalibreRamoEstandar->nombre;
        return view('adminlte.gestion.postcocecha.ingreso_clasificacion.partials._verde', [
            'fecha' => $request->fecha,
            'semana' => getSemanaByDate($request->fecha),
            'total_cosechados' => $total_cosechados,
            'getPromMonitoreoCalibreByFecha' => $getPromMonitoreoCalibreByFecha,
            'total_ramos' => $total_ramos,
            'variedades' => $variedades,
            'getCalibreRamoEstandar' => $getCalibreRamoEstandar,
        ]);
    }
}