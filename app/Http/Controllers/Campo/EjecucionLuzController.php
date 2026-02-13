<?php

namespace yura\Http\Controllers\Campo;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use yura\Http\Controllers\Controller;
use yura\Modelos\Ciclo;
use yura\Modelos\CicloLuz;
use yura\Modelos\Submenu;

class EjecucionLuzController extends Controller
{
    public function inicio(Request $request)
    {
        $semanas = DB::table('semana')
            ->select('codigo', 'fecha_inicial', 'fecha_final')->distinct()
            ->where('estado', 1)
            ->where('fecha_inicial', '<=', hoy())
            ->orderBy('codigo', 'desc')
            ->get();
        $sectores = DB::table('sector')
            ->where('estado', 1)
            ->where('id_empresa', getFincaActiva())
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.campo.ejecucion_luz.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'semanas' => $semanas,
            'sectores' => $sectores,
        ]);
    }

    public function listar_ejecucion_luz(Request $request)
    {
        $finca = getFincaActiva();
        $semana = getObjSemana($request->semana);
        $ciclos = Ciclo::join('variedad as v', 'v.id_variedad', '=', 'ciclo.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->join('modulo as m', 'm.id_modulo', '=', 'ciclo.id_modulo')
            ->select('ciclo.*')->distinct()
            ->where('ciclo.estado', 1)
            ->where('v.estado', 1)
            ->where('p.estado', 1)
            ->where('ciclo.activo', 1)
            ->where('p.tiene_ciclos', 1)
            ->where('ciclo.id_empresa', $finca)
            ->where('m.id_sector', $request->sector)
            ->orderBy('v.nombre')
            ->orderBy('ciclo.fecha_inicio')
            ->get();
        $entradas_si = [];
        $entradas_no = [];
        $salidas_si = [];
        $salidas_no = [];

        foreach ($ciclos as $c) {
            if ($semana->codigo == getSemanaByDate(hoy())->codigo)  // semana actual
                $luz = $c->getLuzBySemana($semana);
            else
                $luz = CicloLuz::where('id_ciclo', $c->id_ciclo)
                    ->where('fecha', hoy())
                    ->first();
            if ($luz != '') {
                $inicio_luz = opDiasFecha('+', $luz->inicio_luz, $c->fecha_inicio);
                $semana_inicio_luz = getSemanaByDate($inicio_luz);
                $fin_luz = opDiasFecha('+', $luz->inicio_luz + $luz->dias_proy + $luz->dias_adicional - 1, $c->fecha_inicio);
                if ($semana_inicio_luz->codigo == $semana->codigo) {
                    if ($c->ejec_ini_luz != null)
                        array_push($entradas_si, $luz);
                    else
                        array_push($entradas_no, $luz);
                } else if (getSemanaByDate($fin_luz)->codigo == $semana->codigo) {
                    if ($c->ejec_fin_luz != null)
                        array_push($salidas_si, $luz);
                    else
                        array_push($salidas_no, $luz);
                }
            }
        }

        /* order by fecha_inicio */
        if (count($entradas_si) > 0) {
            for ($i = 0; $i < count($entradas_si) - 1; $i++) {
                for ($y = $i + 1; $y < count($entradas_si); $y++) {
                    $ciclo_i = $entradas_si[$i]->ciclo;
                    $inicio_luz_i = opDiasFecha('+', $entradas_si[$i]->inicio_luz, $ciclo_i->fecha_inicio);
                    $ciclo_y = $entradas_si[$y]->ciclo;
                    $inicio_luz_y = opDiasFecha('+', $entradas_si[$y]->inicio_luz, $ciclo_y->fecha_inicio);
                    if ($inicio_luz_i > $inicio_luz_y) {
                        $temp = $entradas_si[$i];
                        $entradas_si[$i] = $entradas_si[$y];
                        $entradas_si[$y] = $temp;
                    }
                }
            }
        }
        if (count($entradas_no) > 0) {
            for ($i = 0; $i < count($entradas_no) - 1; $i++) {
                for ($y = $i + 1; $y < count($entradas_no); $y++) {
                    $ciclo_i = $entradas_no[$i]->ciclo;
                    $inicio_luz_i = opDiasFecha('+', $entradas_no[$i]->inicio_luz, $ciclo_i->fecha_inicio);
                    $ciclo_y = $entradas_no[$y]->ciclo;
                    $inicio_luz_y = opDiasFecha('+', $entradas_no[$y]->inicio_luz, $ciclo_y->fecha_inicio);
                    if ($inicio_luz_i > $inicio_luz_y) {
                        $temp = $entradas_no[$i];
                        $entradas_no[$i] = $entradas_no[$y];
                        $entradas_no[$y] = $temp;
                    }
                }
            }
        }
        if (count($salidas_si) > 0) {
            for ($i = 0; $i < count($salidas_si) - 1; $i++) {
                for ($y = $i + 1; $y < count($salidas_si); $y++) {
                    $ciclo_i = $salidas_si[$i]->ciclo;
                    $fin_luz_i = opDiasFecha('+', $salidas_si[$i]->inicio_luz + $salidas_si[$i]->dias_proy + $salidas_si[$i]->dias_adicional - 1, $ciclo_i->fecha_inicio);
                    $ciclo_y = $salidas_si[$y]->ciclo;
                    $fin_luz_y = opDiasFecha('+', $salidas_si[$y]->inicio_luz + $salidas_si[$y]->dias_proy + $salidas_si[$y]->dias_adicional - 1, $ciclo_y->fecha_inicio);
                    if ($fin_luz_i > $fin_luz_y) {
                        $temp = $salidas_si[$i];
                        $salidas_si[$i] = $salidas_si[$y];
                        $salidas_si[$y] = $temp;
                    }
                }
            }
        }
        if (count($salidas_no) > 0) {
            for ($i = 0; $i < count($salidas_no) - 1; $i++) {
                for ($y = $i + 1; $y < count($salidas_no); $y++) {
                    $ciclo_i = $salidas_no[$i]->ciclo;
                    $fin_luz_i = opDiasFecha('+', $salidas_no[$i]->inicio_luz + $salidas_no[$i]->dias_proy + $salidas_no[$i]->dias_adicional - 1, $ciclo_i->fecha_inicio);
                    $ciclo_y = $salidas_no[$y]->ciclo;
                    $fin_luz_y = opDiasFecha('+', $salidas_no[$y]->inicio_luz + $salidas_no[$y]->dias_proy + $salidas_no[$y]->dias_adicional - 1, $ciclo_y->fecha_inicio);
                    if ($fin_luz_i > $fin_luz_y) {
                        $temp = $salidas_no[$i];
                        $salidas_no[$i] = $salidas_no[$y];
                        $salidas_no[$y] = $temp;
                    }
                }
            }
        }
        return view('adminlte.gestion.campo.ejecucion_luz.partials.listado', [
            'entradas_si' => $entradas_si,
            'entradas_no' => $entradas_no,
            'salidas_si' => $salidas_si,
            'salidas_no' => $salidas_no,
            'semana' => $semana,
        ]);
    }
}
