<?php

namespace yura\Http\Controllers\CRM;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use yura\Http\Controllers\Controller;
use yura\Modelos\Submenu;

class crmBqtDiariaController extends Controller
{
    public function inicio(Request $request)
    {
        return view('adminlte.crm.bqt_diaria.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'plantas' => DB::table('bouquetera as b')
                ->join('planta as p', 'p.id_planta', '=', 'b.id_planta')
                ->select('b.id_planta', 'p.nombre')->distinct()
                ->whereIn('b.id_empresa', [getFincaActiva(), -1])
                ->orderBy('p.nombre')->get(),
            'desde' => opDiasFecha('-', 6, hoy()),
            'hasta' => hoy(),
        ]);
    }

    public function buscar_bqt_diaria(Request $request)
    {
        $fechas = DB::table('bouquetera')
            ->select('fecha')->distinct()
            ->where('fecha', '>=', $request->desde)
            ->where('fecha', '<=', $request->hasta);
        if ($request->variedad != 'T')
            $fechas = $fechas->where('id_variedad', $request->variedad);
        elseif ($request->planta != '')
            $fechas = $fechas->where('id_planta', $request->planta);
        $fechas = $fechas->orderBy('fecha')
            ->get();
        $combinaciones = DB::table('bouquetera as b')
            ->leftJoin('configuracion_empresa as emp', 'emp.id_configuracion_empresa', '=', 'b.id_empresa')
            ->join('planta as p', 'p.id_planta', '=', 'b.id_planta')
            ->join('variedad as v', 'v.id_variedad', '=', 'b.id_variedad')
            ->select('b.id_empresa', 'emp.nombre as empresa', 'b.id_variedad', 'b.precio', 'p.nombre as planta_nombre', 'v.nombre as var_nombre')->distinct()
            ->where('b.fecha', '>=', $request->desde)
            ->where('b.fecha', '<=', $request->hasta);
        if ($request->variedad != 'T')
            $combinaciones = $combinaciones->where('b.id_variedad', $request->variedad);
        elseif ($request->planta != '')
            $combinaciones = $combinaciones->where('b.id_planta', $request->planta);
        $combinaciones = $combinaciones->orderBy('emp.nombre')
            ->orderBy('p.nombre')
            ->orderBy('v.nombre')
            ->orderBy('b.precio')
            ->get();
        $data = [];
        foreach ($combinaciones as $comb) {
            $valores = [];
            foreach ($fechas as $f) {
                $query = DB::table('bouquetera')
                    ->select(DB::raw('sum(tallos) as tallos'), DB::raw('sum(exportada) as exportada'))
                    ->where('id_empresa', $comb->id_empresa)
                    ->where('id_variedad', $comb->id_variedad)
                    ->where('fecha', $f->fecha)
                    ->where('precio', '' . $comb->precio);
                if ($request->variedad != 'T')
                    $query = $query->where('id_variedad', $request->variedad);
                elseif ($request->planta != '')
                    $query = $query->where('id_planta', $request->planta);
                $query = $query->get()[0];
                array_push($valores, [
                    'fecha' => $f->fecha,
                    'valor' => $query,
                ]);
            }
            array_push($data, [
                'comb' => $comb,
                'valores' => $valores,
            ]);
        }
        return view('adminlte.crm.bqt_diaria.partials.listado', [
            'fechas' => $fechas,
            'data' => $data,
        ]);
    }
}
