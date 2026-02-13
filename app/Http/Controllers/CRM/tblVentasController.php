<?php

namespace yura\Http\Controllers\CRM;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use yura\Http\Controllers\Controller;
use yura\Modelos\HistoricoVentas;
use yura\Modelos\Pais;
use yura\Modelos\Submenu;
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Worksheet;
use PHPExcel_Worksheet_MemoryDrawing;
use PHPExcel_Style_Fill;
use PHPExcel_Style_Border;
use PHPExcel_Style_Color;
use PHPExcel_Style_Alignment;
use yura\Modelos\Planta;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class tblVentasController extends Controller
{
    public function inicio(Request $request)
    {
        $annos = DB::table('resumen_agrogana')
            ->select('anno')->distinct()
            ->orderBy('anno')
            ->get();
        $clientes = DB::table('resumen_agrogana as h')
            ->join('cliente as c', 'c.id_cliente', '=', 'h.id_cliente')
            ->join('detalle_cliente as dc', 'dc.id_cliente', '=', 'h.id_cliente')
            ->select('h.id_cliente', 'dc.nombre')->distinct()
            ->where('c.estado', 1)
            ->where('dc.estado', 1)
            ->orderBy('h.anno')
            ->get();
        $plantas = Planta::join('variedad as v', 'v.id_planta', '=', 'planta.id_planta')
            ->select('planta.*')->distinct()
            ->where('planta.estado', 1)
            ->where('v.estado', 1)
            ->where('v.receta', 0)
            ->orderBy('planta.nombre')
            ->get();
        $semana_desde = getSemanaByDate(opDiasFecha('-', 0, hoy()));

        return view('adminlte.crm.tbl_ventas.inicio', [
            'annos' => $annos,
            'plantas' => $plantas,
            'semana_desde' => $semana_desde,
            'clientes' => $clientes,

            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
        ]);
    }

    public function filtrar_tablas(Request $request)
    {
        if ($request->annos == '')
            $annos = [date('Y')];
        else
            $annos = explode(' - ', $request->annos);

        $listado = [];
        if ($request->rango == 'S') { // SEMANAL
            if ($request->desde_semanal != '' && $request->hasta_semanal != '') {
                $listado_annos = [];
                foreach ($annos as $a) {
                    $request->desde_semanal = strlen($request->desde_semanal) < 2 ? '0' . $request->desde_semanal : $request->desde_semanal;
                    $request->desde_semanal = substr($a, 2, 2) . $request->desde_semanal;
                    $request->hasta_semanal = strlen($request->hasta_semanal) < 2 ? '0' . $request->hasta_semanal : $request->hasta_semanal;
                    $request->hasta_semanal = substr($a, 2, 2) . $request->hasta_semanal;
                    $semanas = getSemanasByCodigos($request->desde_semanal, $request->hasta_semanal);
                    $listado_annos[] = [
                        'anno' => $a,
                        'semanas' => $semanas,
                    ];
                }

                if ($request->tipo_listado == 'C') {    // por clientes
                    $view = 'semanal_clientes';
                    $clientes = DB::table('resumen_agrogana as h')
                        ->join('detalle_cliente as c', 'c.id_cliente', '=', 'h.id_cliente')
                        ->select('h.id_cliente', 'c.nombre')->distinct()
                        ->where('c.estado', 1);
                    if ($request->cliente != 'T')
                        $clientes = $clientes->where('h.id_cliente', $request->cliente);
                    $clientes = $clientes->orderBy('c.nombre')
                        ->get();
                    foreach ($clientes as $c) {
                        $valores_anno = [];
                        foreach ($listado_annos as $a) {
                            $valores_semanas = [];
                            foreach ($a['semanas'] as $sem) {
                                $valor = 0;
                                if ($request->criterio == 'A') {
                                    $valor = DB::table('resumen_agrogana as h')
                                        ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                                        ->select(
                                            DB::raw('sum(h.tallos_armados) as armados')
                                        )
                                        ->where('h.id_cliente', $c->id_cliente)
                                        ->where('h.semana', $sem->codigo);
                                    if ($request->planta != 'T')
                                        $valor = $valor->where('v.id_planta', $request->planta);
                                    if ($request->variedad != 'T')
                                        $valor = $valor->where('h.id_variedad', $request->variedad);
                                    $valor = $valor->get()[0]->armados;
                                }

                                $valores_semanas[] = [
                                    'semana' => $sem->codigo,
                                    'valor' => $valor != '' ? $valor : 0,
                                ];
                            }
                            $valores_anno[] = [
                                'anno' => $a['anno'],
                                'valores_semanas' => $valores_semanas
                            ];
                        }
                        $listado[] = [
                            'cliente' => $c,
                            'valores_anno' => $valores_anno
                        ];
                    }
                } else {    // por flores
                    $view = 'semanal_flores';
                    if ($request->criterio == 'A') {
                        $plantas = DB::table('resumen_agrogana as h')
                            ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                            ->select('v.id_planta', 'p.nombre')->distinct()
                            ->where('h.semana', '>=', $request->desde_semanal)
                            ->where('h.semana', '<=', $request->hasta_semanal)
                            ->where('h.tallos_armados', '>', 0);
                    }
                    if ($request->criterio == 'C') {
                        $plantas = DB::table('resumen_fechas as h')
                            ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                            ->select('v.id_planta', 'p.nombre')->distinct()
                            ->where('h.semana', '>=', $request->desde_semanal)
                            ->where('h.semana', '<=', $request->hasta_semanal)
                            ->where('h.tallos_comprados', '>', 0);
                    }
                    if ($request->criterio == 'D') {
                        $plantas = DB::table('resumen_fechas as h')
                            ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                            ->select('v.id_planta', 'p.nombre')->distinct()
                            ->where('h.semana', '>=', $request->desde_semanal)
                            ->where('h.semana', '<=', $request->hasta_semanal)
                            ->where('h.tallos_desechados', '>', 0);
                    }
                    if ($request->criterio == 'R') {
                        $plantas = DB::table('resumen_fechas as h')
                            ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                            ->select('v.id_planta', 'p.nombre')->distinct()
                            ->where('h.semana', '>=', $request->desde_semanal)
                            ->where('h.semana', '<=', $request->hasta_semanal)
                            ->where('h.tallos_recibidos', '>', 0);
                    }
                    if ($request->planta != 'T')
                        $plantas = $plantas->where('v.id_planta', $request->planta);
                    if ($request->cliente != 'T')
                        $plantas = $plantas->where('h.id_cliente', $request->cliente);
                    $plantas = $plantas->orderBy('p.nombre')
                        ->get();
                    foreach ($plantas as $p) {
                        $valores_anno = [];
                        foreach ($listado_annos as $a) {
                            $valores_semanas = [];
                            foreach ($a['semanas'] as $sem) {
                                $valor = 0;
                                if ($request->criterio == 'A') {
                                    $valor = DB::table('resumen_agrogana as h')
                                        ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                                        ->select(
                                            DB::raw('sum(h.tallos_armados) as armados'),
                                        )
                                        ->where('v.id_planta', $p->id_planta)
                                        ->where('h.semana', $sem->codigo);
                                    if ($request->cliente != 'T')
                                        $valor = $valor->where('h.id_cliente', $request->cliente);
                                    if ($request->variedad != 'T')
                                        $valor = $valor->where('h.id_variedad', $request->variedad);
                                    $valor = $valor->get()[0]->armados;
                                }
                                if ($request->criterio == 'C') {
                                    $valor = DB::table('resumen_fechas as h')
                                        ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                                        ->select(
                                            DB::raw('sum(h.tallos_comprados) as comprados'),
                                        )
                                        ->where('v.id_planta', $p->id_planta)
                                        ->where('h.semana', $sem->codigo);
                                    if ($request->cliente != 'T')
                                        $valor = $valor->where('h.id_cliente', $request->cliente);
                                    if ($request->variedad != 'T')
                                        $valor = $valor->where('h.id_variedad', $request->variedad);
                                    $valor = $valor->get()[0]->comprados;
                                }
                                if ($request->criterio == 'D') {
                                    $valor = DB::table('resumen_fechas as h')
                                        ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                                        ->select(
                                            DB::raw('sum(h.tallos_desechados) as desechados'),
                                        )
                                        ->where('v.id_planta', $p->id_planta)
                                        ->where('h.semana', $sem->codigo);
                                    if ($request->cliente != 'T')
                                        $valor = $valor->where('h.id_cliente', $request->cliente);
                                    if ($request->variedad != 'T')
                                        $valor = $valor->where('h.id_variedad', $request->variedad);
                                    $valor = $valor->get()[0]->desechados;
                                }
                                if ($request->criterio == 'R') {
                                    $valor = DB::table('resumen_fechas as h')
                                        ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                                        ->select(
                                            DB::raw('sum(h.tallos_recibidos) as recibidos'),
                                        )
                                        ->where('v.id_planta', $p->id_planta)
                                        ->where('h.semana', $sem->codigo);
                                    if ($request->cliente != 'T')
                                        $valor = $valor->where('h.id_cliente', $request->cliente);
                                    if ($request->variedad != 'T')
                                        $valor = $valor->where('h.id_variedad', $request->variedad);
                                    $valor = $valor->get()[0]->recibidos;
                                }

                                $valores_semanas[] = [
                                    'semana' => $sem->codigo,
                                    'valor' => $valor != '' ? $valor : 0,
                                ];
                            }
                            if (count($valores_semanas))
                                $valores_anno[] = [
                                    'anno' => $a['anno'],
                                    'valores_semanas' => $valores_semanas
                                ];
                        }
                        if (count($valores_anno))
                            $listado[] = [
                                'planta' => $p,
                                'valores_anno' => $valores_anno
                            ];
                    }
                }
            } else {
                return '<div class="alert alert-warning text-center">Las Semanas "desde" y "hasta" son incorrectas.</div>';
            }
        } else {    // MENSUAL
            if ($request->desde_mensual != '' && $request->hasta_mensual != '') {
                $listado_annos = [];
                foreach ($annos as $a) {
                    $meses = [];
                    for ($m = $request->desde_mensual; $m <= $request->hasta_mensual; $m++) {
                        $meses[] = strlen($m) == 1 ? '0' . $m : $m;
                    }
                    $listado_annos[] = [
                        'anno' => $a,
                        'meses' => $meses,
                    ];
                }

                if ($request->tipo_listado == 'C') {    // por clientes
                    $view = 'mensual_clientes';
                    $clientes = DB::table('resumen_agrogana as h')
                        ->join('detalle_cliente as c', 'c.id_cliente', '=', 'h.id_cliente')
                        ->select('h.id_cliente', 'c.nombre')->distinct()
                        ->where('c.estado', 1);
                    if ($request->cliente != 'T')
                        $clientes = $clientes->where('h.id_cliente', $request->cliente);
                    $clientes = $clientes->orderBy('c.nombre')
                        ->get();
                    foreach ($clientes as $c) {
                        $valores_anno = [];
                        foreach ($listado_annos as $a) {
                            $valores_meses = [];
                            foreach ($a['meses'] as $mes) {
                                $valor = 0;
                                if ($request->criterio == 'A') {
                                    $valor = DB::table('resumen_agrogana as h')
                                        ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                                        ->select(
                                            DB::raw('sum(h.tallos_armados) as armados'),
                                        )
                                        ->where('h.id_cliente', $c->id_cliente)
                                        ->where('h.mes', $mes)
                                        ->where('h.anno', $a['anno']);
                                    if ($request->planta != 'T')
                                        $valor = $valor->where('v.id_planta', $request->planta);
                                    if ($request->variedad != 'T')
                                        $valor = $valor->where('h.id_variedad', $request->variedad);
                                    $valor = $valor->get()[0]->armados;
                                }

                                $valores_meses[] = [
                                    'mes' => $mes,
                                    'valor' => $valor != '' ? $valor : 0,
                                ];
                            }
                            $valores_anno[] = [
                                'anno' => $a['anno'],
                                'valores_meses' => $valores_meses
                            ];
                        }
                        $listado[] = [
                            'cliente' => $c,
                            'valores_anno' => $valores_anno
                        ];
                    }
                } else {    // por flores
                    $view = 'mensual_flores';
                    if ($request->criterio == 'A') {
                        $plantas = DB::table('resumen_agrogana as h')
                            ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                            ->select('v.id_planta', 'p.nombre')->distinct()
                            ->where('h.mes', '>=', strlen($request->desde_mensual) == 1 ? '0' . $request->desde_mensual : $request->desde_mensual)
                            ->where('h.mes', '<=', strlen($request->hasta_mensual) == 1 ? '0' . $request->hasta_mensual : $request->hasta_mensual)
                            ->where('h.tallos_armados', '>', 0);
                    }
                    if ($request->criterio == 'C') {
                        $plantas = DB::table('resumen_fechas as h')
                            ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                            ->select('v.id_planta', 'p.nombre')->distinct()
                            ->where('h.mes', '>=', strlen($request->desde_mensual) == 1 ? '0' . $request->desde_mensual : $request->desde_mensual)
                            ->where('h.mes', '<=', strlen($request->hasta_mensual) == 1 ? '0' . $request->hasta_mensual : $request->hasta_mensual)
                            ->where('h.tallos_comprados', '>', 0);
                    }
                    if ($request->criterio == 'D') {
                        $plantas = DB::table('resumen_fechas as h')
                            ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                            ->select('v.id_planta', 'p.nombre')->distinct()
                            ->where('h.mes', '>=', strlen($request->desde_mensual) == 1 ? '0' . $request->desde_mensual : $request->desde_mensual)
                            ->where('h.mes', '<=', strlen($request->hasta_mensual) == 1 ? '0' . $request->hasta_mensual : $request->hasta_mensual)
                            ->where('h.tallos_desechados', '>', 0);
                    }
                    if ($request->criterio == 'R') {
                        $plantas = DB::table('resumen_fechas as h')
                            ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                            ->select('v.id_planta', 'p.nombre')->distinct()
                            ->where('h.mes', '>=', strlen($request->desde_mensual) == 1 ? '0' . $request->desde_mensual : $request->desde_mensual)
                            ->where('h.mes', '<=', strlen($request->hasta_mensual) == 1 ? '0' . $request->hasta_mensual : $request->hasta_mensual)
                            ->where('h.tallos_recibidos', '>', 0);
                    }
                    if ($request->planta != 'T')
                        $plantas = $plantas->where('v.id_planta', $request->planta);
                    if ($request->cliente != 'T')
                        $plantas = $plantas->where('h.id_cliente', $request->cliente);
                    $plantas = $plantas->orderBy('p.nombre')
                        ->get();
                    foreach ($plantas as $p) {
                        $valores_anno = [];
                        foreach ($listado_annos as $a) {
                            $valores_meses = [];
                            foreach ($a['meses'] as $mes) {
                                $valor = 0;
                                if ($request->criterio == 'A') {
                                    $valor = DB::table('resumen_agrogana as h')
                                        ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                                        ->select(
                                            DB::raw('sum(h.tallos_armados) as armados'),
                                        )
                                        ->where('v.id_planta', $p->id_planta)
                                        ->where('h.mes', $mes)
                                        ->where('h.anno', $a['anno']);
                                    if ($request->cliente != 'T')
                                        $valor = $valor->where('h.id_cliente', $request->cliente);
                                    if ($request->variedad != 'T')
                                        $valor = $valor->where('h.id_variedad', $request->variedad);
                                    $valor = $valor->get()[0]->armados;
                                }
                                if ($request->criterio == 'C') {
                                    $valor = DB::table('resumen_fechas as h')
                                        ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                                        ->select(
                                            DB::raw('sum(h.tallos_comprados) as comprados'),
                                        )
                                        ->where('v.id_planta', $p->id_planta)
                                        ->where('h.mes', $mes)
                                        ->where('h.anno', $a['anno']);
                                    if ($request->cliente != 'T')
                                        $valor = $valor->where('h.id_cliente', $request->cliente);
                                    if ($request->variedad != 'T')
                                        $valor = $valor->where('h.id_variedad', $request->variedad);
                                    $valor = $valor->get()[0]->comprados;
                                }
                                if ($request->criterio == 'D') {
                                    $valor = DB::table('resumen_fechas as h')
                                        ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                                        ->select(
                                            DB::raw('sum(h.tallos_desechados) as desechados'),
                                        )
                                        ->where('v.id_planta', $p->id_planta)
                                        ->where('h.mes', $mes)
                                        ->where('h.anno', $a['anno']);
                                    if ($request->cliente != 'T')
                                        $valor = $valor->where('h.id_cliente', $request->cliente);
                                    if ($request->variedad != 'T')
                                        $valor = $valor->where('h.id_variedad', $request->variedad);
                                    $valor = $valor->get()[0]->desechados;
                                }
                                if ($request->criterio == 'R') {
                                    $valor = DB::table('resumen_fechas as h')
                                        ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                                        ->select(
                                            DB::raw('sum(h.tallos_recibidos) as recibidos'),
                                        )
                                        ->where('v.id_planta', $p->id_planta)
                                        ->where('h.mes', $mes)
                                        ->where('h.anno', $a['anno']);
                                    if ($request->cliente != 'T')
                                        $valor = $valor->where('h.id_cliente', $request->cliente);
                                    if ($request->variedad != 'T')
                                        $valor = $valor->where('h.id_variedad', $request->variedad);
                                    $valor = $valor->get()[0]->recibidos;
                                }

                                $valores_meses[] = [
                                    'mes' => $mes,
                                    'valor' => $valor != '' ? $valor : 0,
                                ];
                            }
                            if (count($valores_meses))
                                $valores_anno[] = [
                                    'anno' => $a['anno'],
                                    'valores_meses' => $valores_meses
                                ];
                        }
                        if (count($valores_anno))
                            $listado[] = [
                                'planta' => $p,
                                'valores_anno' => $valores_anno
                            ];
                    }
                }
            } else {
                return '<div class="alert alert-warning text-center">Los Meses "desde" y "hasta" son incorrectos.</div>';
            }
        }

        return view('adminlte.crm.tbl_ventas.partials.' . $view, [
            'listado' => $listado,
            'listado_annos' => $listado_annos,
            'criterio' => $request->criterio,
        ]);
    }

    public function select_planta_semanal(Request $request)
    {
        if ($request->annos == '')
            $annos = [date('Y')];
        else
            $annos = explode(' - ', $request->annos);

        $listado = [];
        if ($request->desde_semanal != '' && $request->hasta_semanal != '') {
            $listado_annos = [];
            foreach ($annos as $a) {
                $request->desde_semanal = strlen($request->desde_semanal) < 2 ? '0' . $request->desde_semanal : $request->desde_semanal;
                $request->desde_semanal = substr($a, 2, 2) . $request->desde_semanal;
                $request->hasta_semanal = strlen($request->hasta_semanal) < 2 ? '0' . $request->hasta_semanal : $request->hasta_semanal;
                $request->hasta_semanal = substr($a, 2, 2) . $request->hasta_semanal;
                $semanas = getSemanasByCodigos($request->desde_semanal, $request->hasta_semanal);
                $listado_annos[] = [
                    'anno' => $a,
                    'semanas' => $semanas,
                ];
            }
            if ($request->criterio == 'A') {
                $variedades = DB::table('resumen_agrogana as h')
                    ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                    ->select('h.id_variedad', 'v.nombre')->distinct()
                    ->where('v.id_planta', $request->planta)
                    ->where('h.semana', '>=', $request->desde_semanal)
                    ->where('h.semana', '<=', $request->hasta_semanal)
                    ->where('h.tallos_armados', '>', 0);
            }
            if ($request->criterio == 'C') {
                $variedades = DB::table('resumen_fechas as h')
                    ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                    ->select('h.id_variedad', 'v.nombre')->distinct()
                    ->where('v.id_planta', $request->planta)
                    ->where('h.semana', '>=', $request->desde_semanal)
                    ->where('h.semana', '<=', $request->hasta_semanal)
                    ->where('h.tallos_comprados', '>', 0);
            }
            if ($request->criterio == 'D') {
                $variedades = DB::table('resumen_fechas as h')
                    ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                    ->select('h.id_variedad', 'v.nombre')->distinct()
                    ->where('v.id_planta', $request->planta)
                    ->where('h.semana', '>=', $request->desde_semanal)
                    ->where('h.semana', '<=', $request->hasta_semanal)
                    ->where('h.tallos_desechados', '>', 0);
            }
            if ($request->criterio == 'R') {
                $variedades = DB::table('resumen_fechas as h')
                    ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                    ->select('h.id_variedad', 'v.nombre')->distinct()
                    ->where('v.id_planta', $request->planta)
                    ->where('h.semana', '>=', $request->desde_semanal)
                    ->where('h.semana', '<=', $request->hasta_semanal)
                    ->where('h.tallos_recibidos', '>', 0);
            }
            if ($request->cliente != 'T' && $request->criterio == 'A')
                $variedades = $variedades->where('h.id_cliente', $request->cliente);
            $variedades = $variedades->orderBy('v.nombre')->get();
            foreach ($variedades as $v) {
                $valores_anno = [];
                foreach ($listado_annos as $a) {
                    $valores_semanas = [];
                    foreach ($a['semanas'] as $sem) {

                        $valor = 0;
                        if ($request->criterio == 'A') {
                            $valor = DB::table('resumen_agrogana as h')
                                ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                                ->select(
                                    DB::raw('sum(h.tallos_armados) as armados'),
                                )
                                ->where('v.id_planta', $request->planta)
                                ->where('h.id_variedad', $v->id_variedad)
                                ->where('h.semana', $sem->codigo)
                                ->where('h.anno', $a['anno']);
                            if ($request->cliente != 'T')
                                $valor = $valor->where('h.id_cliente', $request->cliente);
                            $valor = $valor->get()[0]->armados;
                        }
                        if ($request->criterio == 'C') {
                            $valor = DB::table('resumen_fechas as h')
                                ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                                ->select(
                                    DB::raw('sum(h.tallos_comprados) as comprados'),
                                )
                                ->where('v.id_planta', $request->planta)
                                ->where('h.id_variedad', $v->id_variedad)
                                ->where('h.semana', $sem->codigo)
                                ->where('h.anno', $a['anno']);
                            if ($request->cliente != 'T')
                                $valor = $valor->where('h.id_cliente', $request->cliente);
                            $valor = $valor->get()[0]->comprados;
                        }
                        if ($request->criterio == 'D') {
                            $valor = DB::table('resumen_fechas as h')
                                ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                                ->select(
                                    DB::raw('sum(h.tallos_desechados) as desechados'),
                                )
                                ->where('v.id_planta', $request->planta)
                                ->where('h.id_variedad', $v->id_variedad)
                                ->where('h.semana', $sem->codigo)
                                ->where('h.anno', $a['anno']);
                            if ($request->cliente != 'T')
                                $valor = $valor->where('h.id_cliente', $request->cliente);
                            $valor = $valor->get()[0]->desechados;
                        }
                        if ($request->criterio == 'R') {
                            $valor = DB::table('resumen_fechas as h')
                                ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                                ->select(
                                    DB::raw('sum(h.tallos_recibidos) as recibidos'),
                                )
                                ->where('v.id_planta', $request->planta)
                                ->where('h.id_variedad', $v->id_variedad)
                                ->where('h.semana', $sem->codigo)
                                ->where('h.anno', $a['anno']);
                            if ($request->cliente != 'T')
                                $valor = $valor->where('h.id_cliente', $request->cliente);
                            $valor = $valor->get()[0]->recibidos;
                        }
                        $valores_semanas[] = [
                            'semana' => $sem->codigo,
                            'valor' => $valor != '' ? $valor : 0,
                        ];
                    }
                    $valores_anno[] = [
                        'anno' => $a['anno'],
                        'valores_semanas' => $valores_semanas
                    ];
                }
                $listado[] = [
                    'variedad' => $v,
                    'valores_anno' => $valores_anno
                ];
            }
            return view('adminlte.crm.tbl_ventas.partials.detalles.select_planta_semanal', [
                'listado' => $listado,
                'listado_annos' => $listado_annos,
                'criterio' => $request->criterio,
                'planta' => Planta::find($request->planta),
            ]);
        } else {
            return '<div class="alert alert-warning text-center">Las Semanas "desde" y "hasta" son incorrectas.</div>';
        }
    }

    public function select_planta_diario(Request $request)
    {
        $fechas = DB::table('resumen_agrogana as h')
            ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
            ->select('h.fecha')->distinct()
            ->where('v.id_planta', $request->planta)
            ->where('h.semana', $request->semana)
            ->orderBy('h.fecha')
            ->get()->pluck('fecha')->toArray();

        $listado = [];
        if ($request->criterio == 'A') {
            $variedades = DB::table('resumen_agrogana as h')
                ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                ->select('h.id_variedad', 'v.nombre')->distinct()
                ->where('v.id_planta', $request->planta)
                ->where('h.semana', $request->semana)
                ->where('h.tallos_armados', '>', 0);
        }
        if ($request->criterio == 'C') {
            $variedades = DB::table('resumen_fechas as h')
                ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                ->select('h.id_variedad', 'v.nombre')->distinct()
                ->where('v.id_planta', $request->planta)
                ->where('h.semana', $request->semana)
                ->where('h.tallos_comprados', '>', 0);
        }
        if ($request->criterio == 'D') {
            $variedades = DB::table('resumen_fechas as h')
                ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                ->select('h.id_variedad', 'v.nombre')->distinct()
                ->where('v.id_planta', $request->planta)
                ->where('h.semana', $request->semana)
                ->where('h.tallos_desechados', '>', 0);
        }
        if ($request->criterio == 'R') {
            $variedades = DB::table('resumen_fechas as h')
                ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                ->select('h.id_variedad', 'v.nombre')->distinct()
                ->where('v.id_planta', $request->planta)
                ->where('h.semana', $request->semana)
                ->where('h.tallos_recibidos', '>', 0);
        }
        $variedades = $variedades->orderBy('v.nombre')
            ->get();
        foreach ($variedades as $v) {
            $valores_fechas = [];
            foreach ($fechas as $f) {
                $valor = 0;
                if ($request->criterio == 'A') {
                    $valor = DB::table('resumen_agrogana as h')
                        ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                        ->select(
                            DB::raw('sum(h.tallos_armados) as armados'),
                        )
                        ->where('v.id_planta', $request->planta)
                        ->where('h.id_variedad', $v->id_variedad)
                        ->where('h.fecha', $f);
                    if ($request->cliente != 'T')
                        $valor = $valor->where('h.id_cliente', $request->cliente);
                    $valor = $valor->get()[0]->armados;
                }
                if ($request->criterio == 'C') {
                    $valor = DB::table('resumen_fechas as h')
                        ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                        ->select(
                            DB::raw('sum(h.tallos_comprados) as comprados'),
                        )
                        ->where('v.id_planta', $request->planta)
                        ->where('h.id_variedad', $v->id_variedad)
                        ->where('h.fecha', $f);
                    if ($request->cliente != 'T')
                        $valor = $valor->where('h.id_cliente', $request->cliente);
                    $valor = $valor->get()[0]->comprados;
                }
                if ($request->criterio == 'D') {
                    $valor = DB::table('resumen_fechas as h')
                        ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                        ->select(
                            DB::raw('sum(h.tallos_desechados) as desechados'),
                        )
                        ->where('v.id_planta', $request->planta)
                        ->where('h.id_variedad', $v->id_variedad)
                        ->where('h.fecha', $f);
                    if ($request->cliente != 'T')
                        $valor = $valor->where('h.id_cliente', $request->cliente);
                    $valor = $valor->get()[0]->desechados;
                }
                if ($request->criterio == 'R') {
                    $valor = DB::table('resumen_fechas as h')
                        ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                        ->select(
                            DB::raw('sum(h.tallos_recibidos) as recibidos'),
                        )
                        ->where('v.id_planta', $request->planta)
                        ->where('h.id_variedad', $v->id_variedad)
                        ->where('h.fecha', $f);
                    if ($request->cliente != 'T')
                        $valor = $valor->where('h.id_cliente', $request->cliente);
                    $valor = $valor->get()[0]->recibidos;
                }
                $valores_fechas[] = [
                    'fecha' => $f,
                    'valor' => $valor != '' ? $valor : 0,
                ];
            }
            $listado[] = [
                'variedad' => $v,
                'valores_fechas' => $valores_fechas
            ];
        }
        return view('adminlte.crm.tbl_ventas.partials.detalles.select_planta_diario', [
            'listado' => $listado,
            'fechas' => $fechas,
            'criterio' => $request->criterio,
            'planta' => Planta::find($request->planta),
        ]);
    }

    public function select_planta_mensual(Request $request)
    {
        if ($request->annos == '')
            $annos = [date('Y')];
        else
            $annos = explode(' - ', $request->annos);

        $listado = [];
        if ($request->desde_mensual != '' && $request->hasta_mensual != '') {
            $listado_annos = [];
            foreach ($annos as $a) {
                $meses = [];
                for ($m = $request->desde_mensual; $m <= $request->hasta_mensual; $m++) {
                    $meses[] = strlen($m) == 1 ? '0' . $m : $m;
                }
                $listado_annos[] = [
                    'anno' => $a,
                    'meses' => $meses,
                ];
            }
            if ($request->criterio == 'A') {
                $variedades = DB::table('resumen_agrogana as h')
                    ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                    ->select('h.id_variedad', 'v.nombre')->distinct()
                    ->where('v.id_planta', $request->planta)
                    ->where('h.mes', '>=', $request->desde_mensual)
                    ->where('h.mes', '<=', $request->hasta_mensual)
                    ->where('h.tallos_armados', '>', 0);
            }
            if ($request->criterio == 'C') {
                $variedades = DB::table('resumen_fechas as h')
                    ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                    ->select('h.id_variedad', 'v.nombre')->distinct()
                    ->where('v.id_planta', $request->planta)
                    ->where('h.mes', '>=', $request->desde_mensual)
                    ->where('h.mes', '<=', $request->hasta_mensual)
                    ->where('h.tallos_comprados', '>', 0);
            }
            if ($request->criterio == 'D') {
                $variedades = DB::table('resumen_fechas as h')
                    ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                    ->select('h.id_variedad', 'v.nombre')->distinct()
                    ->where('v.id_planta', $request->planta)
                    ->where('h.mes', '>=', $request->desde_mensual)
                    ->where('h.mes', '<=', $request->hasta_mensual)
                    ->where('h.tallos_desechados', '>', 0);
            }
            if ($request->criterio == 'R') {
                $variedades = DB::table('resumen_fechas as h')
                    ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                    ->select('h.id_variedad', 'v.nombre')->distinct()
                    ->where('v.id_planta', $request->planta)
                    ->where('h.mes', '>=', $request->desde_mensual)
                    ->where('h.mes', '<=', $request->hasta_mensual)
                    ->where('h.tallos_recibidos', '>', 0);
            }
            if ($request->cliente != 'T' && $request->criterio == 'A')
                $variedades = $variedades->where('h.id_cliente', $request->cliente);
            $variedades = $variedades->orderBy('v.nombre')->get();
            foreach ($variedades as $v) {
                $valores_anno = [];
                foreach ($listado_annos as $a) {
                    $valores_meses = [];
                    foreach ($a['meses'] as $mes) {
                        $valor = 0;
                        if ($request->criterio == 'A') {
                            $valor = DB::table('resumen_agrogana as h')
                                ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                                ->select(
                                    DB::raw('sum(h.tallos_armados) as armados'),
                                )
                                ->where('v.id_planta', $request->planta)
                                ->where('h.id_variedad', $v->id_variedad)
                                ->where('h.mes', $mes)
                                ->where('h.anno', $a['anno']);
                            if ($request->cliente != 'T')
                                $valor = $valor->where('h.id_cliente', $request->cliente);
                            $valor = $valor->get()[0]->armados;
                        }
                        if ($request->criterio == 'C') {
                            $valor = DB::table('resumen_fechas as h')
                                ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                                ->select(
                                    DB::raw('sum(h.tallos_comprados) as comprados'),
                                )
                                ->where('v.id_planta', $request->planta)
                                ->where('h.id_variedad', $v->id_variedad)
                                ->where('h.mes', $mes)
                                ->where('h.anno', $a['anno']);
                            if ($request->cliente != 'T')
                                $valor = $valor->where('h.id_cliente', $request->cliente);
                            $valor = $valor->get()[0]->comprados;
                        }
                        if ($request->criterio == 'D') {
                            $valor = DB::table('resumen_fechas as h')
                                ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                                ->select(
                                    DB::raw('sum(h.tallos_desechados) as desechados'),
                                )
                                ->where('v.id_planta', $request->planta)
                                ->where('h.id_variedad', $v->id_variedad)
                                ->where('h.mes', $mes)
                                ->where('h.anno', $a['anno']);
                            if ($request->cliente != 'T')
                                $valor = $valor->where('h.id_cliente', $request->cliente);
                            $valor = $valor->get()[0]->desechados;
                        }
                        if ($request->criterio == 'R') {
                            $valor = DB::table('resumen_fechas as h')
                                ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                                ->select(
                                    DB::raw('sum(h.tallos_recibidos) as recibidos'),
                                )
                                ->where('v.id_planta', $request->planta)
                                ->where('h.id_variedad', $v->id_variedad)
                                ->where('h.mes', $mes)
                                ->where('h.anno', $a['anno']);
                            if ($request->cliente != 'T')
                                $valor = $valor->where('h.id_cliente', $request->cliente);
                            $valor = $valor->get()[0]->recibidos;
                        }
                        $valores_meses[] = [
                            'mes' => $mes,
                            'valor' => $valor != '' ? $valor : 0,
                        ];
                    }
                    $valores_anno[] = [
                        'anno' => $a['anno'],
                        'valores_meses' => $valores_meses
                    ];
                }
                $listado[] = [
                    'variedad' => $v,
                    'valores_anno' => $valores_anno
                ];
            }
            return view('adminlte.crm.tbl_ventas.partials.detalles.select_planta_mensual', [
                'listado' => $listado,
                'listado_annos' => $listado_annos,
                'criterio' => $request->criterio,
                'planta' => Planta::find($request->planta),
            ]);
        } else {
            return '<div class="alert alert-warning text-center">Los Meses "desde" y "hasta" son incorrectas.</div>';
        }
    }

    /* ================= EXCEL ================= */

    public function exportar_tabla(Request $request)
    {
        $datos = json_decode($request->datos);
        $spread = new Spreadsheet();
        $this->excel_listado($spread, $datos);

        $fileName = "Tabla General.xlsx";
        $writer = new Xlsx($spread);

        //--------------------------- GUARDAR EL EXCEL -----------------------

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        $writer->save('php://output');
    }

    public function excel_listado($spread, $request)
    {
        if ($request->annos == '')
            $annos = [date('Y')];
        else
            $annos = explode(' - ', $request->annos);

        $listado = [];
        if ($request->rango == 'S') { // SEMANAL
            if ($request->desde_semanal != '' && $request->hasta_semanal != '') {
                $listado_annos = [];
                foreach ($annos as $a) {
                    $request->desde_semanal = strlen($request->desde_semanal) < 2 ? '0' . $request->desde_semanal : $request->desde_semanal;
                    $request->desde_semanal = substr($a, 2, 2) . $request->desde_semanal;
                    $request->hasta_semanal = strlen($request->hasta_semanal) < 2 ? '0' . $request->hasta_semanal : $request->hasta_semanal;
                    $request->hasta_semanal = substr($a, 2, 2) . $request->hasta_semanal;
                    $semanas = getSemanasByCodigos($request->desde_semanal, $request->hasta_semanal);
                    $listado_annos[] = [
                        'anno' => $a,
                        'semanas' => $semanas,
                    ];
                }

                if ($request->tipo_listado == 'C') {    // por clientes
                    $view = 'semanal_clientes';
                    $clientes = DB::table('resumen_agrogana as h')
                        ->join('detalle_cliente as c', 'c.id_cliente', '=', 'h.id_cliente')
                        ->select('h.id_cliente', 'c.nombre')->distinct()
                        ->where('c.estado', 1);
                    if ($request->cliente != 'T')
                        $clientes = $clientes->where('h.id_cliente', $request->cliente);
                    $clientes = $clientes->orderBy('c.nombre')
                        ->get();
                    foreach ($clientes as $c) {
                        $valores_anno = [];
                        foreach ($listado_annos as $a) {
                            $valores_semanas = [];
                            foreach ($a['semanas'] as $sem) {
                                $valor = 0;
                                if ($request->criterio == 'A') {
                                    $valor = DB::table('resumen_agrogana as h')
                                        ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                                        ->select(
                                            DB::raw('sum(h.tallos_armados) as armados')
                                        )
                                        ->where('h.id_cliente', $c->id_cliente)
                                        ->where('h.semana', $sem->codigo);
                                    if ($request->planta != 'T')
                                        $valor = $valor->where('v.id_planta', $request->planta);
                                    if ($request->variedad != 'T')
                                        $valor = $valor->where('h.id_variedad', $request->variedad);
                                    $valor = $valor->get()[0]->armados;
                                }

                                $valores_semanas[] = [
                                    'semana' => $sem->codigo,
                                    'valor' => $valor != '' ? $valor : 0,
                                ];
                            }
                            $valores_anno[] = [
                                'anno' => $a['anno'],
                                'valores_semanas' => $valores_semanas
                            ];
                        }
                        $listado[] = [
                            'cliente' => $c,
                            'valores_anno' => $valores_anno
                        ];
                    }
                } else {    // por flores
                    $view = 'semanal_flores';
                    if ($request->criterio == 'A') {
                        $plantas = DB::table('resumen_agrogana as h')
                            ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                            ->select('v.id_planta', 'p.nombre')->distinct()
                            ->where('h.semana', '>=', $request->desde_semanal)
                            ->where('h.semana', '<=', $request->hasta_semanal)
                            ->where('h.tallos_armados', '>', 0);
                    }
                    if ($request->criterio == 'C') {
                        $plantas = DB::table('resumen_fechas as h')
                            ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                            ->select('v.id_planta', 'p.nombre')->distinct()
                            ->where('h.semana', '>=', $request->desde_semanal)
                            ->where('h.semana', '<=', $request->hasta_semanal)
                            ->where('h.tallos_comprados', '>', 0);
                    }
                    if ($request->criterio == 'D') {
                        $plantas = DB::table('resumen_fechas as h')
                            ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                            ->select('v.id_planta', 'p.nombre')->distinct()
                            ->where('h.semana', '>=', $request->desde_semanal)
                            ->where('h.semana', '<=', $request->hasta_semanal)
                            ->where('h.tallos_desechados', '>', 0);
                    }
                    if ($request->criterio == 'R') {
                        $plantas = DB::table('resumen_fechas as h')
                            ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                            ->select('v.id_planta', 'p.nombre')->distinct()
                            ->where('h.semana', '>=', $request->desde_semanal)
                            ->where('h.semana', '<=', $request->hasta_semanal)
                            ->where('h.tallos_recibidos', '>', 0);
                    }
                    if ($request->planta != 'T')
                        $plantas = $plantas->where('v.id_planta', $request->planta);
                    if ($request->cliente != 'T')
                        $plantas = $plantas->where('h.id_cliente', $request->cliente);
                    $plantas = $plantas->orderBy('p.nombre')
                        ->get();
                    foreach ($plantas as $p) {
                        $valores_anno = [];
                        foreach ($listado_annos as $a) {
                            $valores_semanas = [];
                            foreach ($a['semanas'] as $sem) {
                                $valor = 0;
                                if ($request->criterio == 'A') {
                                    $valor = DB::table('resumen_agrogana as h')
                                        ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                                        ->select(
                                            DB::raw('sum(h.tallos_armados) as armados'),
                                        )
                                        ->where('v.id_planta', $p->id_planta)
                                        ->where('h.semana', $sem->codigo);
                                    if ($request->cliente != 'T')
                                        $valor = $valor->where('h.id_cliente', $request->cliente);
                                    if ($request->variedad != 'T')
                                        $valor = $valor->where('h.id_variedad', $request->variedad);
                                    $valor = $valor->get()[0]->armados;
                                }
                                if ($request->criterio == 'C') {
                                    $valor = DB::table('resumen_fechas as h')
                                        ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                                        ->select(
                                            DB::raw('sum(h.tallos_comprados) as comprados'),
                                        )
                                        ->where('v.id_planta', $p->id_planta)
                                        ->where('h.semana', $sem->codigo);
                                    if ($request->cliente != 'T')
                                        $valor = $valor->where('h.id_cliente', $request->cliente);
                                    if ($request->variedad != 'T')
                                        $valor = $valor->where('h.id_variedad', $request->variedad);
                                    $valor = $valor->get()[0]->comprados;
                                }
                                if ($request->criterio == 'D') {
                                    $valor = DB::table('resumen_fechas as h')
                                        ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                                        ->select(
                                            DB::raw('sum(h.tallos_desechados) as desechados'),
                                        )
                                        ->where('v.id_planta', $p->id_planta)
                                        ->where('h.semana', $sem->codigo);
                                    if ($request->cliente != 'T')
                                        $valor = $valor->where('h.id_cliente', $request->cliente);
                                    if ($request->variedad != 'T')
                                        $valor = $valor->where('h.id_variedad', $request->variedad);
                                    $valor = $valor->get()[0]->desechados;
                                }
                                if ($request->criterio == 'R') {
                                    $valor = DB::table('resumen_fechas as h')
                                        ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                                        ->select(
                                            DB::raw('sum(h.tallos_recibidos) as recibidos'),
                                        )
                                        ->where('v.id_planta', $p->id_planta)
                                        ->where('h.semana', $sem->codigo);
                                    if ($request->cliente != 'T')
                                        $valor = $valor->where('h.id_cliente', $request->cliente);
                                    if ($request->variedad != 'T')
                                        $valor = $valor->where('h.id_variedad', $request->variedad);
                                    $valor = $valor->get()[0]->recibidos;
                                }

                                $valores_semanas[] = [
                                    'semana' => $sem->codigo,
                                    'valor' => $valor != '' ? $valor : 0,
                                ];
                            }
                            if (count($valores_semanas))
                                $valores_anno[] = [
                                    'anno' => $a['anno'],
                                    'valores_semanas' => $valores_semanas
                                ];
                        }
                        if (count($valores_anno))
                            $listado[] = [
                                'planta' => $p,
                                'valores_anno' => $valores_anno
                            ];
                    }
                }
            } else {
                return '<div class="alert alert-warning text-center">Las Semanas "desde" y "hasta" son incorrectas.</div>';
            }
        } else {    // MENSUAL
            if ($request->desde_mensual != '' && $request->hasta_mensual != '') {
                $listado_annos = [];
                foreach ($annos as $a) {
                    $meses = [];
                    for ($m = $request->desde_mensual; $m <= $request->hasta_mensual; $m++) {
                        $meses[] = strlen($m) == 1 ? '0' . $m : $m;
                    }
                    $listado_annos[] = [
                        'anno' => $a,
                        'meses' => $meses,
                    ];
                }

                if ($request->tipo_listado == 'C') {    // por clientes
                    $view = 'mensual_clientes';
                    $clientes = DB::table('resumen_agrogana as h')
                        ->join('detalle_cliente as c', 'c.id_cliente', '=', 'h.id_cliente')
                        ->select('h.id_cliente', 'c.nombre')->distinct()
                        ->where('c.estado', 1);
                    if ($request->cliente != 'T')
                        $clientes = $clientes->where('h.id_cliente', $request->cliente);
                    $clientes = $clientes->orderBy('c.nombre')
                        ->get();
                    foreach ($clientes as $c) {
                        $valores_anno = [];
                        foreach ($listado_annos as $a) {
                            $valores_meses = [];
                            foreach ($a['meses'] as $mes) {
                                $valor = 0;
                                if ($request->criterio == 'A') {
                                    $valor = DB::table('resumen_agrogana as h')
                                        ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                                        ->select(
                                            DB::raw('sum(h.tallos_armados) as armados'),
                                        )
                                        ->where('h.id_cliente', $c->id_cliente)
                                        ->where('h.mes', $mes)
                                        ->where('h.anno', $a['anno']);
                                    if ($request->planta != 'T')
                                        $valor = $valor->where('v.id_planta', $request->planta);
                                    if ($request->variedad != 'T')
                                        $valor = $valor->where('h.id_variedad', $request->variedad);
                                    $valor = $valor->get()[0]->armados;
                                }

                                $valores_meses[] = [
                                    'mes' => $mes,
                                    'valor' => $valor != '' ? $valor : 0,
                                ];
                            }
                            $valores_anno[] = [
                                'anno' => $a['anno'],
                                'valores_meses' => $valores_meses
                            ];
                        }
                        $listado[] = [
                            'cliente' => $c,
                            'valores_anno' => $valores_anno
                        ];
                    }
                } else {    // por flores
                    $view = 'mensual_flores';
                    if ($request->criterio == 'A') {
                        $plantas = DB::table('resumen_agrogana as h')
                            ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                            ->select('v.id_planta', 'p.nombre')->distinct()
                            ->where('h.mes', '>=', strlen($request->desde_mensual) == 1 ? '0' . $request->desde_mensual : $request->desde_mensual)
                            ->where('h.mes', '<=', strlen($request->hasta_mensual) == 1 ? '0' . $request->hasta_mensual : $request->hasta_mensual)
                            ->where('h.tallos_armados', '>', 0);
                    }
                    if ($request->criterio == 'C') {
                        $plantas = DB::table('resumen_fechas as h')
                            ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                            ->select('v.id_planta', 'p.nombre')->distinct()
                            ->where('h.mes', '>=', strlen($request->desde_mensual) == 1 ? '0' . $request->desde_mensual : $request->desde_mensual)
                            ->where('h.mes', '<=', strlen($request->hasta_mensual) == 1 ? '0' . $request->hasta_mensual : $request->hasta_mensual)
                            ->where('h.tallos_comprados', '>', 0);
                    }
                    if ($request->criterio == 'D') {
                        $plantas = DB::table('resumen_fechas as h')
                            ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                            ->select('v.id_planta', 'p.nombre')->distinct()
                            ->where('h.mes', '>=', strlen($request->desde_mensual) == 1 ? '0' . $request->desde_mensual : $request->desde_mensual)
                            ->where('h.mes', '<=', strlen($request->hasta_mensual) == 1 ? '0' . $request->hasta_mensual : $request->hasta_mensual)
                            ->where('h.tallos_desechados', '>', 0);
                    }
                    if ($request->criterio == 'R') {
                        $plantas = DB::table('resumen_fechas as h')
                            ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                            ->select('v.id_planta', 'p.nombre')->distinct()
                            ->where('h.mes', '>=', strlen($request->desde_mensual) == 1 ? '0' . $request->desde_mensual : $request->desde_mensual)
                            ->where('h.mes', '<=', strlen($request->hasta_mensual) == 1 ? '0' . $request->hasta_mensual : $request->hasta_mensual)
                            ->where('h.tallos_recibidos', '>', 0);
                    }
                    if ($request->planta != 'T')
                        $plantas = $plantas->where('v.id_planta', $request->planta);
                    if ($request->cliente != 'T')
                        $plantas = $plantas->where('h.id_cliente', $request->cliente);
                    $plantas = $plantas->orderBy('p.nombre')
                        ->get();
                    foreach ($plantas as $p) {
                        $valores_anno = [];
                        foreach ($listado_annos as $a) {
                            $valores_meses = [];
                            foreach ($a['meses'] as $mes) {
                                $valor = 0;
                                if ($request->criterio == 'A') {
                                    $valor = DB::table('resumen_agrogana as h')
                                        ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                                        ->select(
                                            DB::raw('sum(h.tallos_armados) as armados'),
                                        )
                                        ->where('v.id_planta', $p->id_planta)
                                        ->where('h.mes', $mes)
                                        ->where('h.anno', $a['anno']);
                                    if ($request->cliente != 'T')
                                        $valor = $valor->where('h.id_cliente', $request->cliente);
                                    if ($request->variedad != 'T')
                                        $valor = $valor->where('h.id_variedad', $request->variedad);
                                    $valor = $valor->get()[0]->armados;
                                }
                                if ($request->criterio == 'C') {
                                    $valor = DB::table('resumen_fechas as h')
                                        ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                                        ->select(
                                            DB::raw('sum(h.tallos_comprados) as comprados'),
                                        )
                                        ->where('v.id_planta', $p->id_planta)
                                        ->where('h.mes', $mes)
                                        ->where('h.anno', $a['anno']);
                                    if ($request->cliente != 'T')
                                        $valor = $valor->where('h.id_cliente', $request->cliente);
                                    if ($request->variedad != 'T')
                                        $valor = $valor->where('h.id_variedad', $request->variedad);
                                    $valor = $valor->get()[0]->comprados;
                                }
                                if ($request->criterio == 'D') {
                                    $valor = DB::table('resumen_fechas as h')
                                        ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                                        ->select(
                                            DB::raw('sum(h.tallos_desechados) as desechados'),
                                        )
                                        ->where('v.id_planta', $p->id_planta)
                                        ->where('h.mes', $mes)
                                        ->where('h.anno', $a['anno']);
                                    if ($request->cliente != 'T')
                                        $valor = $valor->where('h.id_cliente', $request->cliente);
                                    if ($request->variedad != 'T')
                                        $valor = $valor->where('h.id_variedad', $request->variedad);
                                    $valor = $valor->get()[0]->desechados;
                                }
                                if ($request->criterio == 'R') {
                                    $valor = DB::table('resumen_fechas as h')
                                        ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                                        ->select(
                                            DB::raw('sum(h.tallos_recibidos) as recibidos'),
                                        )
                                        ->where('v.id_planta', $p->id_planta)
                                        ->where('h.mes', $mes)
                                        ->where('h.anno', $a['anno']);
                                    if ($request->cliente != 'T')
                                        $valor = $valor->where('h.id_cliente', $request->cliente);
                                    if ($request->variedad != 'T')
                                        $valor = $valor->where('h.id_variedad', $request->variedad);
                                    $valor = $valor->get()[0]->recibidos;
                                }

                                $valores_meses[] = [
                                    'mes' => $mes,
                                    'valor' => $valor != '' ? $valor : 0,
                                ];
                            }
                            if (count($valores_meses))
                                $valores_anno[] = [
                                    'anno' => $a['anno'],
                                    'valores_meses' => $valores_meses
                                ];
                        }
                        if (count($valores_anno))
                            $listado[] = [
                                'planta' => $p,
                                'valores_anno' => $valores_anno
                            ];
                    }
                }
            } else {
                return '<div class="alert alert-warning text-center">Los Meses "desde" y "hasta" son incorrectos.</div>';
            }
        }

        $columnas = getColumnasExcel();
        $sheet = $spread->getActiveSheet();
        $sheet->setTitle('Tabla General');

        if ($view == 'semanal_flores') {
            $this->get_hoja_semanal_flores($sheet, $listado_annos, $columnas, $listado);
        }
        if ($view == 'semanal_clientes') {
            $this->get_hoja_semanal_clientes($sheet, $listado_annos, $columnas, $listado);
        }
        if ($view == 'mensual_flores') {
            $this->get_hoja_mensual_flores($sheet, $listado_annos, $columnas, $listado);
        }
        if ($view == 'mensual_clientes') {
            $this->get_hoja_mensual_clientes($sheet, $listado_annos, $columnas, $listado);
        }
    }

    public function get_hoja_semanal_flores($sheet, $listado_annos, $columnas, $listado)
    {
        $row = 1;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Flores / Años');
        $totales_annos = [];
        foreach ($listado_annos as $a) {
            $totales_semanas = [];
            foreach ($a['semanas'] as $sem) {
                $totales_semanas[] = [
                    'suma' => 0,
                ];
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $sem->codigo);
            }
            $totales_annos[] = $totales_semanas;
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TOTAL ' . $a['anno']);
        }
        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        foreach ($listado as $item) {
            $row++;
            $col = 0;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item['planta']->nombre);
            setBgToCeldaExcel($sheet, $columnas[$col] . $row, '00b388');
            setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
            foreach ($item['valores_anno'] as $pos_a => $a) {
                $total_anno_item = 0;
                foreach ($a['valores_semanas'] as $pos_sem => $sem) {
                    $total_anno_item += $sem['valor'];
                    $totales_annos[$pos_a][$pos_sem]['suma'] += $sem['valor'];
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $sem['valor']);
                }
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_anno_item);
            }
        }

        $row++;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TOTALES');
        foreach ($totales_annos as $t) {
            $total_anno = 0;
            foreach ($t as $val) {
                $total_anno += $val['suma'];
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $val['suma']);
            }
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_anno);
        }
        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        setTextCenterToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);
        setBorderToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);

        for ($i = 0; $i <= $col; $i++)
            $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);
    }

    public function get_hoja_semanal_clientes($sheet, $listado_annos, $columnas, $listado)
    {
        $row = 1;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Clientes / Años');
        $totales_annos = [];
        foreach ($listado_annos as $a) {
            $totales_semanas = [];
            foreach ($a['semanas'] as $sem) {
                $totales_semanas[] = [
                    'suma' => 0,
                ];
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $sem->codigo);
            }
            $totales_annos[] = $totales_semanas;
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TOTAL ' . $a['anno']);
        }
        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        foreach ($listado as $item) {
            $row++;
            $col = 0;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item['cliente']->nombre);
            setBgToCeldaExcel($sheet, $columnas[$col] . $row, '00b388');
            setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
            foreach ($item['valores_anno'] as $pos_a => $a) {
                $total_anno_item = 0;
                foreach ($a['valores_semanas'] as $pos_sem => $sem) {
                    $total_anno_item += $sem['valor'];
                    $totales_annos[$pos_a][$pos_sem]['suma'] += $sem['valor'];
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $sem['valor']);
                }
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_anno_item);
            }
        }

        $row++;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TOTALES');
        foreach ($totales_annos as $t) {
            $total_anno = 0;
            foreach ($t as $val) {
                $total_anno += $val['suma'];
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $val['suma']);
            }
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_anno);
        }
        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        setTextCenterToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);
        setBorderToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);

        for ($i = 0; $i <= $col; $i++)
            $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);
    }

    public function get_hoja_mensual_flores($sheet, $listado_annos, $columnas, $listado)
    {
        $row = 1;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Flores / Años');
        $totales_annos = [];
        foreach ($listado_annos as $a) {
            $totales_meses = [];
            foreach ($a['meses'] as $mes) {
                $totales_meses[] = [
                    'suma' => 0,
                ];
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, getMeses()[$mes - 1]);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, '%');
            }
            $totales_annos[] = $totales_meses;
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TOTAL ' . $a['anno']);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, '%');
        }
        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        foreach ($listado as $item) {
            foreach ($item['valores_anno'] as $pos_a => $a) {
                foreach ($a['valores_meses'] as $pos_mes => $mes) {
                    $totales_annos[$pos_a][$pos_mes]['suma'] += $mes['valor'];
                }
            }
        }

        foreach ($listado as $item) {
            $row++;
            $col = 0;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item['planta']->nombre);
            setBgToCeldaExcel($sheet, $columnas[$col] . $row, '00b388');
            setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
            foreach ($item['valores_anno'] as $pos_a => $a) {
                $total_anno_item = 0;
                $total_anno = 0;
                foreach ($totales_annos[$pos_a] as $pos_mes => $mes) {
                    $total_anno += $mes['suma'];
                }
                foreach ($a['valores_meses'] as $pos_mes => $mes) {
                    $total_anno_item += $mes['valor'];
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $mes['valor']);
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, porcentaje($mes['valor'], $totales_annos[$pos_a][$pos_mes]['suma'], 1) . '%');
                }
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_anno_item);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, porcentaje($total_anno_item, $total_anno, 1) . '%');
            }
        }

        $row++;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TOTALES');
        foreach ($totales_annos as $t) {
            $total_anno = 0;
            foreach ($t as $val) {
                $total_anno += $val['suma'];
            }
            foreach ($t as $val) {
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $val['suma']);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, porcentaje($val['suma'], $total_anno, 1) . '%');
            }
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_anno);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, '100%');
        }
        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        setTextCenterToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);
        setBorderToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);

        for ($i = 0; $i <= $col; $i++)
            $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);
    }

    public function get_hoja_mensual_clientes($sheet, $listado_annos, $columnas, $listado)
    {
        $row = 1;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Clientes / Años');
        $totales_annos = [];
        foreach ($listado_annos as $a) {
            $totales_meses = [];
            foreach ($a['meses'] as $mes) {
                $totales_meses[] = [
                    'suma' => 0,
                ];
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, getMeses()[$mes - 1]);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, '%');
            }
            $totales_annos[] = $totales_meses;
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TOTAL ' . $a['anno']);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, '%');
        }
        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        foreach ($listado as $item) {
            foreach ($item['valores_anno'] as $pos_a => $a) {
                foreach ($a['valores_meses'] as $pos_mes => $mes) {
                    $totales_annos[$pos_a][$pos_mes]['suma'] += $mes['valor'];
                }
            }
        }

        foreach ($listado as $item) {
            $row++;
            $col = 0;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item['cliente']->nombre);
            setBgToCeldaExcel($sheet, $columnas[$col] . $row, '00b388');
            setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
            foreach ($item['valores_anno'] as $pos_a => $a) {
                $total_anno_item = 0;
                $total_anno = 0;
                foreach ($totales_annos[$pos_a] as $pos_mes => $mes) {
                    $total_anno += $mes['suma'];
                }
                foreach ($a['valores_meses'] as $pos_mes => $mes) {
                    $total_anno_item += $mes['valor'];
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $mes['valor']);
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, porcentaje($mes['valor'], $totales_annos[$pos_a][$pos_mes]['suma'], 1) . '%');
                }
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_anno_item);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, porcentaje($total_anno_item, $total_anno, 1) . '%');
            }
        }

        $row++;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TOTALES');
        foreach ($totales_annos as $t) {
            $total_anno = 0;
            foreach ($t as $val) {
                $total_anno += $val['suma'];
            }
            foreach ($t as $val) {
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $val['suma']);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, porcentaje($val['suma'], $total_anno, 1) . '%');
            }
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_anno);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, '100%');
        }
        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        setTextCenterToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);
        setBorderToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);

        for ($i = 0; $i <= $col; $i++)
            $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);
    }

    public function exportar_planta_semanal(Request $request)
    {
        $spread = new Spreadsheet();
        $this->excel_planta_semanal($spread, $request);
        $fileName = "DESGLOSE FLOR SEMANAL.xlsx";
        $writer = new Xlsx($spread);

        //--------------------------- GUARDAR EL EXCEL -----------------------

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        $writer->save('php://output');

        //$writer->save('/var/www/html/Dasalflor/storage/storage/excel/excel_prueba.xlsx');
    }

    public function excel_planta_semanal($spread, $request)
    {
        $request = json_decode($request->datos);
        if ($request->annos == '')
            $annos = [date('Y')];
        else
            $annos = explode(' - ', $request->annos);

        $listado = [];
        if ($request->desde_semanal != '' && $request->hasta_semanal != '') {
            $listado_annos = [];
            foreach ($annos as $a) {
                $request->desde_semanal = strlen($request->desde_semanal) < 2 ? '0' . $request->desde_semanal : $request->desde_semanal;
                $request->desde_semanal = substr($a, 2, 2) . $request->desde_semanal;
                $request->hasta_semanal = strlen($request->hasta_semanal) < 2 ? '0' . $request->hasta_semanal : $request->hasta_semanal;
                $request->hasta_semanal = substr($a, 2, 2) . $request->hasta_semanal;
                $semanas = getSemanasByCodigos($request->desde_semanal, $request->hasta_semanal);
                $listado_annos[] = [
                    'anno' => $a,
                    'semanas' => $semanas,
                ];
            }
            if ($request->criterio == 'A') {
                $variedades = DB::table('resumen_agrogana as h')
                    ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                    ->select('h.id_variedad', 'v.nombre')->distinct()
                    ->where('v.id_planta', $request->planta)
                    ->where('h.semana', '>=', $request->desde_semanal)
                    ->where('h.semana', '<=', $request->hasta_semanal)
                    ->where('h.tallos_armados', '>', 0);
            }
            if ($request->criterio == 'C') {
                $variedades = DB::table('resumen_fechas as h')
                    ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                    ->select('h.id_variedad', 'v.nombre')->distinct()
                    ->where('v.id_planta', $request->planta)
                    ->where('h.semana', '>=', $request->desde_semanal)
                    ->where('h.semana', '<=', $request->hasta_semanal)
                    ->where('h.tallos_comprados', '>', 0);
            }
            if ($request->criterio == 'D') {
                $variedades = DB::table('resumen_fechas as h')
                    ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                    ->select('h.id_variedad', 'v.nombre')->distinct()
                    ->where('v.id_planta', $request->planta)
                    ->where('h.semana', '>=', $request->desde_semanal)
                    ->where('h.semana', '<=', $request->hasta_semanal)
                    ->where('h.tallos_desechados', '>', 0);
            }
            if ($request->criterio == 'R') {
                $variedades = DB::table('resumen_fechas as h')
                    ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                    ->select('h.id_variedad', 'v.nombre')->distinct()
                    ->where('v.id_planta', $request->planta)
                    ->where('h.semana', '>=', $request->desde_semanal)
                    ->where('h.semana', '<=', $request->hasta_semanal)
                    ->where('h.tallos_recibidos', '>', 0);
            }
            if ($request->cliente != 'T' && $request->criterio == 'A')
                $variedades = $variedades->where('h.id_cliente', $request->cliente);
            $variedades = $variedades->orderBy('v.nombre')->get();
            foreach ($variedades as $v) {
                $valores_anno = [];
                foreach ($listado_annos as $a) {
                    $valores_semanas = [];
                    foreach ($a['semanas'] as $sem) {

                        $valor = 0;
                        if ($request->criterio == 'A') {
                            $valor = DB::table('resumen_agrogana as h')
                                ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                                ->select(
                                    DB::raw('sum(h.tallos_armados) as armados'),
                                )
                                ->where('v.id_planta', $request->planta)
                                ->where('h.id_variedad', $v->id_variedad)
                                ->where('h.semana', $sem->codigo);
                            if ($request->cliente != 'T')
                                $valor = $valor->where('h.id_cliente', $request->cliente);
                            $valor = $valor->get()[0]->armados;
                        }
                        if ($request->criterio == 'C') {
                            $valor = DB::table('resumen_fechas as h')
                                ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                                ->select(
                                    DB::raw('sum(h.tallos_comprados) as comprados'),
                                )
                                ->where('v.id_planta', $request->planta)
                                ->where('h.id_variedad', $v->id_variedad)
                                ->where('h.semana', $sem->codigo);
                            if ($request->cliente != 'T')
                                $valor = $valor->where('h.id_cliente', $request->cliente);
                            $valor = $valor->get()[0]->comprados;
                        }
                        if ($request->criterio == 'D') {
                            $valor = DB::table('resumen_fechas as h')
                                ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                                ->select(
                                    DB::raw('sum(h.tallos_desechados) as desechados'),
                                )
                                ->where('v.id_planta', $request->planta)
                                ->where('h.id_variedad', $v->id_variedad)
                                ->where('h.semana', $sem->codigo);
                            if ($request->cliente != 'T')
                                $valor = $valor->where('h.id_cliente', $request->cliente);
                            $valor = $valor->get()[0]->desechados;
                        }
                        if ($request->criterio == 'R') {
                            $valor = DB::table('resumen_fechas as h')
                                ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                                ->select(
                                    DB::raw('sum(h.tallos_recibidos) as recibidos'),
                                )
                                ->where('v.id_planta', $request->planta)
                                ->where('h.id_variedad', $v->id_variedad)
                                ->where('h.semana', $sem->codigo);
                            if ($request->cliente != 'T')
                                $valor = $valor->where('h.id_cliente', $request->cliente);
                            $valor = $valor->get()[0]->recibidos;
                        }
                        $valores_semanas[] = [
                            'semana' => $sem->codigo,
                            'valor' => $valor != '' ? $valor : 0,
                        ];
                    }
                    $valores_anno[] = [
                        'anno' => $a['anno'],
                        'valores_semanas' => $valores_semanas
                    ];
                }
                $listado[] = [
                    'variedad' => $v,
                    'valores_anno' => $valores_anno
                ];
            }
            $criterio = $request->criterio;
            $planta = Planta::find($request->planta);
        } else {
            dd('Las Semanas "desde" y "hasta" son incorrectas.');
        }

        $columnas = getColumnasExcel();
        $sheet = $spread->getActiveSheet();
        $sheet->setTitle('DESGLOSE FLOR SEMANAL');

        $row = 1;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Colores ' . $planta->nombre);
        $array_totales_vacio = [];
        foreach ($listado_annos as $a) {
            $array_totales_semanas_vacio = [];
            foreach ($a['semanas'] as $sem) {
                $array_totales_semanas_vacio[] = [
                    'suma' => 0,
                ];
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $sem->codigo);
            }
            $array_totales_vacio[] = $array_totales_semanas_vacio;
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TOTAL ' . $a['anno']);
        }
        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        $totales_annos = $array_totales_vacio;
        foreach ($listado as $var) {
            $totales_annos_long = $array_totales_vacio;
            $row++;
            $col = 0;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $var['variedad']->nombre);
            foreach ($var['valores_anno'] as $pos_a => $a) {
                $total_anno_item = 0;
                foreach ($a['valores_semanas'] as $pos_sem => $sem) {
                    $total_anno_item += $sem['valor'];
                    $totales_annos_long[$pos_a][$pos_sem]['suma'] += $sem['valor'];
                    $totales_annos[$pos_a][$pos_sem]['suma'] += $sem['valor'];

                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $sem['valor']);
                }
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_anno_item);
            }
        }

        $row++;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TOTALES');
        foreach ($totales_annos as $t) {
            $total_anno = 0;
            foreach ($t as $val) {
                $total_anno += $val['suma'];
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $val['suma']);
            }
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_anno);
        }
        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        setTextCenterToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);
        setBorderToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);

        for ($i = 0; $i <= $col; $i++)
            $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);
    }

    public function exportar_planta_mensual(Request $request)
    {
        $spread = new Spreadsheet();
        $this->excel_planta_mensual($spread, $request);
        $fileName = "DESGLOSE FLOR MENSUAL.xlsx";
        $writer = new Xlsx($spread);

        //--------------------------- GUARDAR EL EXCEL -----------------------

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        $writer->save('php://output');

        //$writer->save('/var/www/html/Dasalflor/storage/storage/excel/excel_prueba.xlsx');
    }

    public function excel_planta_mensual($spread, $request)
    {
        $request = json_decode($request->datos);
        if ($request->annos == '')
            $annos = [date('Y')];
        else
            $annos = explode(' - ', $request->annos);
        $request->desde_mensual = strlen($request->desde_mensual) == 1 ? '0' . $request->desde_mensual : $request->desde_mensual;
        $request->hasta_mensual = strlen($request->hasta_mensual) == 1 ? '0' . $request->hasta_mensual : $request->hasta_mensual;

        $listado = [];
        if ($request->desde_mensual != '' && $request->hasta_mensual != '') {
            $listado_annos = [];
            foreach ($annos as $a) {
                $meses = [];
                for ($m = $request->desde_mensual; $m <= $request->hasta_mensual; $m++) {
                    $meses[] = strlen($m) == 1 ? '0' . $m : $m;
                }
                $listado_annos[] = [
                    'anno' => $a,
                    'meses' => $meses,
                ];
            }
            if ($request->criterio == 'A') {
                $variedades = DB::table('resumen_agrogana as h')
                    ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                    ->select('h.id_variedad', 'v.nombre')->distinct()
                    ->where('v.id_planta', $request->planta)
                    ->where('h.mes', '>=', $request->desde_mensual)
                    ->where('h.mes', '<=', $request->hasta_mensual)
                    ->where('h.tallos_armados', '>', 0);
            }
            if ($request->criterio == 'C') {
                $variedades = DB::table('resumen_fechas as h')
                    ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                    ->select('h.id_variedad', 'v.nombre')->distinct()
                    ->where('v.id_planta', $request->planta)
                    ->where('h.mes', '>=', $request->desde_mensual)
                    ->where('h.mes', '<=', $request->hasta_mensual)
                    ->where('h.tallos_comprados', '>', 0);
            }
            if ($request->criterio == 'D') {
                $variedades = DB::table('resumen_fechas as h')
                    ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                    ->select('h.id_variedad', 'v.nombre')->distinct()
                    ->where('v.id_planta', $request->planta)
                    ->where('h.mes', '>=', $request->desde_mensual)
                    ->where('h.mes', '<=', $request->hasta_mensual)
                    ->where('h.tallos_desechados', '>', 0);
            }
            if ($request->criterio == 'R') {
                $variedades = DB::table('resumen_fechas as h')
                    ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                    ->select('h.id_variedad', 'v.nombre')->distinct()
                    ->where('v.id_planta', $request->planta)
                    ->where('h.mes', '>=', $request->desde_mensual)
                    ->where('h.mes', '<=', $request->hasta_mensual)
                    ->where('h.tallos_recibidos', '>', 0);
            }
            if ($request->cliente != 'T' && $request->criterio == 'A')
                $variedades = $variedades->where('h.id_cliente', $request->cliente);
            $variedades = $variedades->orderBy('v.nombre')->get();
            foreach ($variedades as $v) {
                $valores_anno = [];
                foreach ($listado_annos as $a) {
                    $valores_meses = [];
                    foreach ($a['meses'] as $mes) {
                        $valor = 0;
                        if ($request->criterio == 'A') {
                            $valor = DB::table('resumen_agrogana as h')
                                ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                                ->select(
                                    DB::raw('sum(h.tallos_armados) as armados'),
                                )
                                ->where('v.id_planta', $request->planta)
                                ->where('h.id_variedad', $v->id_variedad)
                                ->where('h.mes', $mes)
                                ->where('h.anno', $a['anno']);
                            if ($request->cliente != 'T')
                                $valor = $valor->where('h.id_cliente', $request->cliente);
                            $valor = $valor->get()[0]->armados;
                        }
                        if ($request->criterio == 'C') {
                            $valor = DB::table('resumen_fechas as h')
                                ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                                ->select(
                                    DB::raw('sum(h.tallos_comprados) as comprados'),
                                )
                                ->where('v.id_planta', $request->planta)
                                ->where('h.id_variedad', $v->id_variedad)
                                ->where('h.mes', $mes)
                                ->where('h.anno', $a['anno']);
                            if ($request->cliente != 'T')
                                $valor = $valor->where('h.id_cliente', $request->cliente);
                            $valor = $valor->get()[0]->comprados;
                        }
                        if ($request->criterio == 'D') {
                            $valor = DB::table('resumen_fechas as h')
                                ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                                ->select(
                                    DB::raw('sum(h.tallos_desechados) as desechados'),
                                )
                                ->where('v.id_planta', $request->planta)
                                ->where('h.id_variedad', $v->id_variedad)
                                ->where('h.mes', $mes)
                                ->where('h.anno', $a['anno']);
                            if ($request->cliente != 'T')
                                $valor = $valor->where('h.id_cliente', $request->cliente);
                            $valor = $valor->get()[0]->desechados;
                        }
                        if ($request->criterio == 'R') {
                            $valor = DB::table('resumen_fechas as h')
                                ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                                ->select(
                                    DB::raw('sum(h.tallos_recibidos) as recibidos'),
                                )
                                ->where('v.id_planta', $request->planta)
                                ->where('h.id_variedad', $v->id_variedad)
                                ->where('h.mes', $mes)
                                ->where('h.anno', $a['anno']);
                            if ($request->cliente != 'T')
                                $valor = $valor->where('h.id_cliente', $request->cliente);
                            $valor = $valor->get()[0]->recibidos;
                        }
                        $valores_meses[] = [
                            'mes' => $mes,
                            'valor' => $valor != '' ? $valor : 0,
                        ];
                    }
                    $valores_anno[] = [
                        'anno' => $a['anno'],
                        'valores_meses' => $valores_meses
                    ];
                }
                $listado[] = [
                    'variedad' => $v,
                    'valores_anno' => $valores_anno
                ];
            }
            $criterio = $request->criterio;
            $planta = Planta::find($request->planta);
        } else {
            dd('Las Semanas "desde" y "hasta" son incorrectas.');
        }

        $columnas = getColumnasExcel();
        $sheet = $spread->getActiveSheet();
        $sheet->setTitle('DESGLOSE FLOR MENSUAL');

        $row = 1;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Colores ' . $planta->nombre);
        $array_totales_vacio = [];
        foreach ($listado_annos as $a) {
            $array_totales_meses_vacio = [];
            foreach ($a['meses'] as $mes) {
                $array_totales_meses_vacio[] = [
                    'suma' => 0,
                ];
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, getMeses()[$mes - 1]);
            }
            $array_totales_vacio[] = $array_totales_meses_vacio;
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TOTAL ' . $a['anno']);
        }
        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        $totales_annos = $array_totales_vacio;
        foreach ($listado as $var) {
            $totales_annos_long = $array_totales_vacio;
            $row++;
            $col = 0;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $var['variedad']->nombre);
            foreach ($var['valores_anno'] as $pos_a => $a) {
                $total_anno_item = 0;
                foreach ($a['valores_meses'] as $pos_sem => $sem) {
                    $total_anno_item += $sem['valor'];
                    $totales_annos_long[$pos_a][$pos_sem]['suma'] += $sem['valor'];
                    $totales_annos[$pos_a][$pos_sem]['suma'] += $sem['valor'];

                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $sem['valor']);
                }
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_anno_item);
            }
        }

        $row++;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TOTALES');
        foreach ($totales_annos as $t) {
            $total_anno = 0;
            foreach ($t as $val) {
                $total_anno += $val['suma'];
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $val['suma']);
            }
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_anno);
        }
        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        setTextCenterToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);
        setBorderToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);

        for ($i = 0; $i <= $col; $i++)
            $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);
    }

    /* EXCEL VARIEDADES */
    public function exportar_variedades(Request $request)
    {
        $datos = json_decode($request->datos);
        $spread = new Spreadsheet();
        $this->excel_variedades($spread, $datos);

        $fileName = "Tabla General.xlsx";
        $writer = new Xlsx($spread);

        //--------------------------- GUARDAR EL EXCEL -----------------------

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        $writer->save('php://output');
    }

    public function excel_variedades($spread, $request)
    {
        if ($request->annos == '')
            $annos = [date('Y')];
        else
            $annos = explode(' - ', $request->annos);

        $listado = [];
        if ($request->rango == 'S') { // SEMANAL
            if ($request->desde_semanal != '' && $request->hasta_semanal != '') {
                $listado_annos = [];
                foreach ($annos as $a) {
                    $request->desde_semanal = strlen($request->desde_semanal) < 2 ? '0' . $request->desde_semanal : $request->desde_semanal;
                    $request->desde_semanal = substr($a, 2, 2) . $request->desde_semanal;
                    $request->hasta_semanal = strlen($request->hasta_semanal) < 2 ? '0' . $request->hasta_semanal : $request->hasta_semanal;
                    $request->hasta_semanal = substr($a, 2, 2) . $request->hasta_semanal;
                    $semanas = getSemanasByCodigos($request->desde_semanal, $request->hasta_semanal);
                    $listado_annos[] = [
                        'anno' => $a,
                        'semanas' => $semanas,
                    ];
                }

                $view = 'semanal_flores';
                if ($request->criterio == 'A') {
                    $plantas = DB::table('resumen_agrogana as h')
                        ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                        ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                        ->select('h.id_variedad', 'v.nombre as var_nombre', 'p.nombre as pta_nombre')->distinct()
                        ->where('h.semana', '>=', $request->desde_semanal)
                        ->where('h.semana', '<=', $request->hasta_semanal)
                        ->where('h.tallos_armados', '>', 0);
                }
                if ($request->criterio == 'C') {
                    $plantas = DB::table('resumen_fechas as h')
                        ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                        ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                        ->select('h.id_variedad', 'v.nombre as var_nombre', 'p.nombre as pta_nombre')->distinct()
                        ->where('h.semana', '>=', $request->desde_semanal)
                        ->where('h.semana', '<=', $request->hasta_semanal)
                        ->where('h.tallos_comprados', '>', 0);
                }
                if ($request->criterio == 'D') {
                    $plantas = DB::table('resumen_fechas as h')
                        ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                        ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                        ->select('h.id_variedad', 'v.nombre as var_nombre', 'p.nombre as pta_nombre')->distinct()
                        ->where('h.semana', '>=', $request->desde_semanal)
                        ->where('h.semana', '<=', $request->hasta_semanal)
                        ->where('h.tallos_desechados', '>', 0);
                }
                if ($request->criterio == 'R') {
                    $plantas = DB::table('resumen_fechas as h')
                        ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                        ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                        ->select('h.id_variedad', 'v.nombre as var_nombre', 'p.nombre as pta_nombre')->distinct()
                        ->where('h.semana', '>=', $request->desde_semanal)
                        ->where('h.semana', '<=', $request->hasta_semanal)
                        ->where('h.tallos_recibidos', '>', 0);
                }
                $plantas = $plantas->orderBy('p.nombre')->orderBy('v.nombre')
                    ->get();
                foreach ($plantas as $p) {
                    $valores_anno = [];
                    foreach ($listado_annos as $a) {
                        $valores_semanas = [];
                        foreach ($a['semanas'] as $sem) {
                            $valor = 0;
                            if ($request->criterio == 'A') {
                                $valor = DB::table('resumen_agrogana as h')
                                    ->select(
                                        DB::raw('sum(h.tallos_armados) as armados'),
                                    )
                                    ->where('h.id_variedad', $p->id_variedad)
                                    ->where('h.semana', $sem->codigo)
                                    ->get()[0]->armados;
                            }
                            if ($request->criterio == 'C') {
                                $valor = DB::table('resumen_fechas as h')
                                    ->select(
                                        DB::raw('sum(h.tallos_comprados) as comprados'),
                                    )
                                    ->where('h.id_variedad', $p->id_variedad)
                                    ->where('h.semana', $sem->codigo)
                                    ->get()[0]->comprados;
                            }
                            if ($request->criterio == 'D') {
                                $valor = DB::table('resumen_fechas as h')
                                    ->select(
                                        DB::raw('sum(h.tallos_desechados) as desechados'),
                                    )
                                    ->where('h.id_variedad', $p->id_variedad)
                                    ->where('h.semana', $sem->codigo)
                                    ->get()[0]->desechados;
                            }
                            if ($request->criterio == 'R') {
                                $valor = DB::table('resumen_fechas as h')
                                    ->select(
                                        DB::raw('sum(h.tallos_recibidos) as recibidos'),
                                    )
                                    ->where('h.id_variedad', $p->id_variedad)
                                    ->where('h.semana', $sem->codigo)
                                    ->get()[0]->recibidos;
                            }

                            $valores_semanas[] = [
                                'semana' => $sem->codigo,
                                'valor' => $valor != '' ? $valor : 0,
                            ];
                        }
                        if (count($valores_semanas))
                            $valores_anno[] = [
                                'anno' => $a['anno'],
                                'valores_semanas' => $valores_semanas
                            ];
                    }
                    if (count($valores_anno))
                        $listado[] = [
                            'planta' => $p,
                            'valores_anno' => $valores_anno
                        ];
                }
            } else {
                return '<div class="alert alert-warning text-center">Las Semanas "desde" y "hasta" son incorrectas.</div>';
            }
        } else {    // MENSUAL
            if ($request->desde_mensual != '' && $request->hasta_mensual != '') {
                $listado_annos = [];
                foreach ($annos as $a) {
                    $meses = [];
                    for ($m = $request->desde_mensual; $m <= $request->hasta_mensual; $m++) {
                        $meses[] = strlen($m) == 1 ? '0' . $m : $m;
                    }
                    $listado_annos[] = [
                        'anno' => $a,
                        'meses' => $meses,
                    ];
                }

                $view = 'mensual_flores';
                if ($request->criterio == 'A') {
                    $plantas = DB::table('resumen_agrogana as h')
                        ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                        ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                        ->select('h.id_variedad', 'v.nombre as var_nombre', 'p.nombre as pta_nombre')->distinct()
                        ->whereIn('h.anno', $annos)
                        ->where('h.mes', '>=', strlen($request->desde_mensual) == 1 ? '0' . $request->desde_mensual : $request->desde_mensual)
                        ->where('h.mes', '<=', strlen($request->hasta_mensual) == 1 ? '0' . $request->hasta_mensual : $request->hasta_mensual)
                        ->where('h.tallos_armados', '>', 0);
                }
                if ($request->criterio == 'C') {
                    $plantas = DB::table('resumen_fechas as h')
                        ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                        ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                        ->select('h.id_variedad', 'v.nombre as var_nombre', 'p.nombre as pta_nombre')->distinct()
                        ->whereIn('h.anno', $annos)
                        ->where('h.mes', '>=', strlen($request->desde_mensual) == 1 ? '0' . $request->desde_mensual : $request->desde_mensual)
                        ->where('h.mes', '<=', strlen($request->hasta_mensual) == 1 ? '0' . $request->hasta_mensual : $request->hasta_mensual)
                        ->where('h.tallos_comprados', '>', 0);
                }
                if ($request->criterio == 'D') {
                    $plantas = DB::table('resumen_fechas as h')
                        ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                        ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                        ->select('h.id_variedad', 'v.nombre as var_nombre', 'p.nombre as pta_nombre')->distinct()
                        ->whereIn('h.anno', $annos)
                        ->where('h.mes', '>=', strlen($request->desde_mensual) == 1 ? '0' . $request->desde_mensual : $request->desde_mensual)
                        ->where('h.mes', '<=', strlen($request->hasta_mensual) == 1 ? '0' . $request->hasta_mensual : $request->hasta_mensual)
                        ->where('h.tallos_desechados', '>', 0);
                }
                if ($request->criterio == 'R') {
                    $plantas = DB::table('resumen_fechas as h')
                        ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                        ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                        ->select('h.id_variedad', 'v.nombre as var_nombre', 'p.nombre as pta_nombre')->distinct()
                        ->whereIn('h.anno', $annos)
                        ->where('h.mes', '>=', strlen($request->desde_mensual) == 1 ? '0' . $request->desde_mensual : $request->desde_mensual)
                        ->where('h.mes', '<=', strlen($request->hasta_mensual) == 1 ? '0' . $request->hasta_mensual : $request->hasta_mensual)
                        ->where('h.tallos_recibidos', '>', 0);
                }
                $plantas = $plantas->orderBy('p.nombre')->orderBy('v.nombre')
                    ->get();
                foreach ($plantas as $p) {
                    $valores_anno = [];
                    foreach ($listado_annos as $a) {
                        $valores_meses = [];
                        foreach ($a['meses'] as $mes) {
                            $valor = 0;
                            if ($request->criterio == 'A') {
                                $valor = DB::table('resumen_agrogana as h')
                                    ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                                    ->select(
                                        DB::raw('sum(h.tallos_armados) as armados'),
                                    )
                                    ->where('h.id_variedad', $p->id_variedad)
                                    ->where('h.mes', $mes)
                                    ->where('h.anno', $a['anno'])
                                    ->get()[0]->armados;
                            }
                            if ($request->criterio == 'C') {
                                $valor = DB::table('resumen_fechas as h')
                                    ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                                    ->select(
                                        DB::raw('sum(h.tallos_comprados) as comprados'),
                                    )
                                    ->where('h.id_variedad', $p->id_variedad)
                                    ->where('h.mes', $mes)
                                    ->where('h.anno', $a['anno'])
                                    ->get()[0]->comprados;
                            }
                            if ($request->criterio == 'D') {
                                $valor = DB::table('resumen_fechas as h')
                                    ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                                    ->select(
                                        DB::raw('sum(h.tallos_desechados) as desechados'),
                                    )
                                    ->where('h.id_variedad', $p->id_variedad)
                                    ->where('h.mes', $mes)
                                    ->where('h.anno', $a['anno'])
                                    ->get()[0]->desechados;
                            }
                            if ($request->criterio == 'R') {
                                $valor = DB::table('resumen_fechas as h')
                                    ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                                    ->select(
                                        DB::raw('sum(h.tallos_recibidos) as recibidos'),
                                    )
                                    ->where('h.id_variedad', $p->id_variedad)
                                    ->where('h.mes', $mes)
                                    ->where('h.anno', $a['anno'])
                                    ->get()[0]->recibidos;
                            }

                            $valores_meses[] = [
                                'mes' => $mes,
                                'valor' => $valor != '' ? $valor : 0,
                            ];
                        }
                        if (count($valores_meses))
                            $valores_anno[] = [
                                'anno' => $a['anno'],
                                'valores_meses' => $valores_meses
                            ];
                    }
                    if (count($valores_anno))
                        $listado[] = [
                            'planta' => $p,
                            'valores_anno' => $valores_anno
                        ];
                }
            } else {
                return '<div class="alert alert-warning text-center">Los Meses "desde" y "hasta" son incorrectos.</div>';
            }
        }

        $columnas = getColumnasExcel();
        $sheet = $spread->getActiveSheet();
        $sheet->setTitle('Tabla General');

        if ($view == 'semanal_flores') {
            $this->get_hoja_semanal_variedades($sheet, $listado_annos, $columnas, $listado);
        }
        if ($view == 'mensual_flores') {
            $this->get_hoja_mensual_variedades($sheet, $listado_annos, $columnas, $listado);
        }
    }

    public function get_hoja_semanal_variedades($sheet, $listado_annos, $columnas, $listado)
    {
        $row = 1;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Flores / Años');
        $totales_annos = [];
        foreach ($listado_annos as $a) {
            $totales_semanas = [];
            foreach ($a['semanas'] as $sem) {
                $totales_semanas[] = [
                    'suma' => 0,
                ];
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $sem->codigo);
            }
            $totales_annos[] = $totales_semanas;
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TOTAL ' . $a['anno']);
        }
        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        foreach ($listado as $item) {
            $row++;
            $col = 0;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item['planta']->pta_nombre . ': ' . $item['planta']->var_nombre);
            setBgToCeldaExcel($sheet, $columnas[$col] . $row, '00b388');
            setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
            foreach ($item['valores_anno'] as $pos_a => $a) {
                $total_anno_item = 0;
                foreach ($a['valores_semanas'] as $pos_sem => $sem) {
                    $total_anno_item += $sem['valor'];
                    $totales_annos[$pos_a][$pos_sem]['suma'] += $sem['valor'];
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $sem['valor']);
                }
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_anno_item);
            }
        }

        $row++;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TOTALES');
        foreach ($totales_annos as $t) {
            $total_anno = 0;
            foreach ($t as $val) {
                $total_anno += $val['suma'];
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $val['suma']);
            }
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_anno);
        }
        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        setTextCenterToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);
        setBorderToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);

        for ($i = 0; $i <= $col; $i++)
            $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);
    }

    public function get_hoja_mensual_variedades($sheet, $listado_annos, $columnas, $listado)
    {
        $row = 1;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Flores / Años');
        $totales_annos = [];
        foreach ($listado_annos as $a) {
            $totales_meses = [];
            foreach ($a['meses'] as $mes) {
                $totales_meses[] = [
                    'suma' => 0,
                ];
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, getMeses()[$mes - 1]);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, '%');
            }
            $totales_annos[] = $totales_meses;
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TOTAL ' . $a['anno']);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, '%');
        }
        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        foreach ($listado as $item) {
            foreach ($item['valores_anno'] as $pos_a => $a) {
                foreach ($a['valores_meses'] as $pos_mes => $mes) {
                    $totales_annos[$pos_a][$pos_mes]['suma'] += $mes['valor'];
                }
            }
        }

        foreach ($listado as $item) {
            $row++;
            $col = 0;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item['planta']->pta_nombre . ': ' . $item['planta']->var_nombre);
            setBgToCeldaExcel($sheet, $columnas[$col] . $row, '00b388');
            setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
            foreach ($item['valores_anno'] as $pos_a => $a) {
                $total_anno_item = 0;
                $total_anno = 0;
                foreach ($totales_annos[$pos_a] as $pos_mes => $mes) {
                    $total_anno += $mes['suma'];
                }
                foreach ($a['valores_meses'] as $pos_mes => $mes) {
                    $total_anno_item += $mes['valor'];
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $mes['valor']);
                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, porcentaje($mes['valor'], $totales_annos[$pos_a][$pos_mes]['suma'], 1) . '%');
                }
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_anno_item);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, porcentaje($total_anno_item, $total_anno, 1) . '%');
            }
        }

        $row++;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TOTALES');
        foreach ($totales_annos as $t) {
            $total_anno = 0;
            foreach ($t as $val) {
                $total_anno += $val['suma'];
            }
            foreach ($t as $val) {
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $val['suma']);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, porcentaje($val['suma'], $total_anno, 1) . '%');
            }
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_anno);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, '100%');
        }
        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        setTextCenterToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);
        setBorderToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);

        for ($i = 0; $i <= $col; $i++)
            $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);
    }
}
