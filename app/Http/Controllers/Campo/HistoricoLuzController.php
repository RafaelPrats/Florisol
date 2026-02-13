<?php

namespace yura\Http\Controllers\Campo;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use yura\Http\Controllers\Controller;
use yura\Modelos\Ciclo;
use yura\Modelos\CicloLuz;
use yura\Modelos\Submenu;

class HistoricoLuzController extends Controller
{
    public function inicio(Request $request)
    {
        return view('adminlte.gestion.campo.historico_luz.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'desde' => opDiasFecha('-', 7, hoy()),
            'hasta' => hoy(),
        ]);
    }

    public function listar_historico_luz(Request $request)
    {
        $query_luz = CicloLuz::join('ciclo as c', 'c.id_ciclo', '=', 'ciclo_luz.id_ciclo')
            ->select('ciclo_luz.*')->distinct()
            ->where('ciclo_luz.fecha', '>=', $request->desde)
            ->where('ciclo_luz.fecha', '<=', $request->hasta)
            ->where('c.estado', 1)
            ->orderBy('ciclo_luz.id_ciclo')
            ->orderBy('c.fecha_inicio')
            ->orderBy('ciclo_luz.fecha')
            ->get();

        $listado = [];
        foreach ($query_luz as $luz) {
            $c = $luz->ciclo;
            $inicio_luz = opDiasFecha('+', $luz->inicio_luz, $c->fecha_inicio);
            $fin_luz = opDiasFecha('+', $luz->inicio_luz + $luz->dias_proy + $luz->dias_adicional - 1, $c->fecha_inicio);
            if ($luz->fecha >= $inicio_luz && $luz->fecha <= $fin_luz) {
                $listado[] = [
                    'ciclo' => $c,
                    'luz' => $luz,
                ];
            }
        }
        return view('adminlte.gestion.campo.historico_luz.partials.listado', [
            'listado' => $listado,
        ]);
    }
}
