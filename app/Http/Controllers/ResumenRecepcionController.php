<?php

namespace yura\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use yura\Modelos\Planta;
use yura\Modelos\Submenu;
use yura\Modelos\Variedad;

class ResumenRecepcionController extends Controller
{
    public function inicio(Request $request)
    {
        $plantas = Planta::where('estado', 1)
            ->orderBy('nombre')
            ->get();
        $variedades = Variedad::where('estado', 1)
            ->where('receta', 0)
            ->orderBy('nombre')
            ->get();
        $desde = opDiasFecha('-', 7, hoy());
        $hasta = opDiasFecha('-', 0, hoy());
        return view('adminlte.gestion.postcocecha.resumen_recepcion.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'plantas' => $plantas,
            'variedades' => $variedades,
            'desde' => $desde,
            'hasta' => $hasta,
        ]);
    }

    public function listar_reporte(Request $request)
    {
        $finca = getFincaActiva();

        $fechas = [];
        $f = $request->desde;
        while ($f <= $request->hasta) {
            $fechas[] = $f;
            $f = opDiasFecha('+', 1, $f);
        }

        $combinaciones_recepcion = DB::table('desglose_recepcion as i')
            ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->select(
                'i.id_variedad',
                'v.nombre as variedad_nombre',
                'p.nombre as planta_nombre',
            )->distinct()
            ->where('i.disponibles', '>', 0)
            ->where('i.estado', 1)
            ->where('i.id_empresa', $finca);
        if ($request->planta != '')
            $combinaciones_recepcion = $combinaciones_recepcion->where('v.id_planta', $request->planta);
        if ($request->variedad != '')
            $combinaciones_recepcion = $combinaciones_recepcion->where('i.id_variedad', $request->variedad);
        $combinaciones_recepcion = $combinaciones_recepcion->orderBy('p.nombre')
            ->orderBy('v.nombre')
            ->get();
        $ids_variedad_recepcion = $combinaciones_recepcion->pluck('id_variedad')->toArray();

        $combinaciones_salidas = DB::table('salidas_recepcion as i')
            ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->select(
                'i.id_variedad',
                'v.nombre as variedad_nombre',
                'p.nombre as planta_nombre',
            )->distinct()
            ->whereNotIn('i.id_variedad', $ids_variedad_recepcion);
        if ($request->planta != '')
            $combinaciones_salidas = $combinaciones_salidas->where('v.id_planta', $request->planta);
        if ($request->variedad != '')
            $combinaciones_salidas = $combinaciones_salidas->where('i.id_variedad', $request->variedad);
        $combinaciones_salidas = $combinaciones_salidas->orderBy('p.nombre')
            ->orderBy('v.nombre')
            ->get();
        $combinaciones = $combinaciones_recepcion->merge($combinaciones_salidas);

        $listado = [];
        foreach ($combinaciones as $item) {
            $valores = [];
            foreach ($fechas as $f) {
                $recepcion = DB::table('desglose_recepcion as i')
                    ->select(
                        DB::raw('sum(i.tallos_x_malla) as cantidad'),
                        DB::raw('sum(i.disponibles) as disponibles')
                    )
                    //->where('i.disponibles', '>', 0)
                    ->where('i.estado', 1)
                    ->where('i.id_variedad', $item->id_variedad)
                    ->where('i.id_empresa', $finca)
                    ->where('i.fecha', $f)
                    ->get()[0];
                $salidas = DB::table('salidas_recepcion as i')
                    ->select(
                        DB::raw('sum(i.cantidad) as cantidad')
                    )
                    ->where('i.id_variedad', $item->id_variedad)
                    ->where('i.fecha', $f)
                    ->get()[0]->cantidad;
                $valores[] = [
                    'recepcion' => $recepcion,
                    'salidas' => $salidas,
                ];
            }
            $listado[] = [
                'planta' => $item,
                'valores' => $valores,
            ];
        }
        return view('adminlte.gestion.postcocecha.resumen_recepcion.partials.listado', [
            'listado' => $listado,
            'fechas' => $fechas,
        ]);
    }
}
