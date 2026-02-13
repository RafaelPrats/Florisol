<?php

namespace yura\Http\Controllers\Costos;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use yura\Http\Controllers\Controller;
use yura\Modelos\Area;
use yura\Modelos\Submenu;
use yura\Modelos\SuperFinca;

class pygEmpresasController extends Controller
{
    public function inicio(Request $request)
    {
        $semana_actual = getSemanaByDate(opDiasFecha('-', 7, date('Y-m-d')));
        $semana_desde = getSemanaByDate(opDiasFecha('-', 42, date('Y-m-d')));
        $empresas = SuperFinca::All()->sortBy('nombre');
        return view('adminlte.gestion.costos.pyg_empresas.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'semana_actual' => $semana_actual,
            'semana_desde' => $semana_desde,
            'empresas' => $empresas,
        ]);
    }

    public function listar_reporte(Request $request)
    {
        $sf = SuperFinca::find($request->empresa);
        $ids_finca = [];
        foreach ($sf->fincas as $f)
            $ids_finca[] = $f->id_configuracion_empresa;

        $semanas = DB::table('semana')
            ->select('codigo', 'fecha_inicial', 'fecha_final')->distinct()
            ->where('codigo', '>=', $request->desde)
            ->where('codigo', '<=', $request->hasta)
            ->orderBy('codigo')
            ->get();
        $resumen_semanal = DB::table('resumen_total_semanal_exportcalas')
            ->select('semana',
                DB::raw('sum(tallos_cosechados) as tallos_cosechados'),
                DB::raw('sum(tallos_exportables) as tallos_exportables'),
                DB::raw('sum(bouquetera) as bouquetera'),
                DB::raw('sum(venta) as venta'),
                DB::raw('sum(nacional) as nacionales'),
                DB::raw('sum(bajas) as bajas'),
                DB::raw('sum(tallos_vendidos) as tallos_vendidos'),
                DB::raw('sum(venta_bouquetera) as venta_bouquetera'))
            ->whereIn('id_empresa', $ids_finca)
            ->where('semana', '>=', $request->desde)
            ->where('semana', '<=', $request->hasta)
            ->groupBy('semana')
            ->orderBy('semana')
            ->get();
        $resumen_costos = DB::table('resumen_costos_semanal')
            ->select('codigo_semana',
                DB::raw('sum(mano_obra) as mano_obra'),
                DB::raw('sum(fijos) as fijos'),
                DB::raw('sum(regalias) as regalias'),
                DB::raw('sum(insumos) as insumos'))
            ->whereIn('id_empresa', $ids_finca)
            ->where('codigo_semana', '>=', $request->desde)
            ->where('codigo_semana', '<=', $request->hasta)
            ->groupBy('codigo_semana')
            ->orderBy('codigo_semana')
            ->get();

        if (in_array(2, $ids_finca))
            array_push($ids_finca, -1);
        $compra_flor = [];
        $resumen_area = [];
        foreach ($semanas as $sem) {
            $cant = DB::table('ciclo')
                ->select(DB::raw('sum(area) as area'))
                ->where('estado', '=', 1)
                ->whereIn('id_empresa', $ids_finca)
                ->Where(function ($q) use ($sem) {
                    $q->where('fecha_fin', '>=', $sem->fecha_inicial)
                        ->where('fecha_fin', '<=', $sem->fecha_final)
                        ->orWhere(function ($q) use ($sem) {
                            $q->where('fecha_inicio', '>=', $sem->fecha_inicial)
                                ->where('fecha_inicio', '<=', $sem->fecha_final);
                        })
                        ->orWhere(function ($q) use ($sem) {
                            $q->where('fecha_inicio', '<', $sem->fecha_inicial)
                                ->where('fecha_fin', '>', $sem->fecha_final);
                        });
                })
                ->get()[0]->area;
            array_push($resumen_area, $cant);

            $cant_compra_flor = DB::table('bouquetera')
                ->select(DB::raw('sum(precio * (tallos)) as tallos'),
                    DB::raw('sum(precio * (exportada)) as exportada'),
                    DB::raw('sum(tallos) as tallos_bqt'),
                    DB::raw('sum(exportada) as tallos_exportada'))
                ->where('fecha', '>=', $sem->fecha_inicial)
                ->where('fecha', '<=', $sem->fecha_final)
                ->whereIn('id_empresa', $ids_finca)
                ->get()[0];
            array_push($compra_flor, $cant_compra_flor);
        }

        return view('adminlte.gestion.costos.pyg_empresas.partials.listado', [
            'semanas' => $semanas,
            'resumen_semanal' => $resumen_semanal,
            'resumen_costos' => $resumen_costos,
            'resumen_area' => $resumen_area,
            'compra_flor' => $compra_flor,
        ]);
    }
}
