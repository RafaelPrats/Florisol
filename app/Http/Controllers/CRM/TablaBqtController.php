<?php

namespace yura\Http\Controllers\CRM;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use yura\Http\Controllers\Controller;
use yura\Modelos\DetalleImportPedido;
use yura\Modelos\Planta;
use yura\Modelos\Submenu;
use yura\Modelos\Variedad;

class TablaBqtController extends Controller
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
        $recetas = DetalleImportPedido::join('variedad as v', 'v.id_variedad', '=', 'detalle_import_pedido.id_variedad')
            ->select('detalle_import_pedido.id_variedad', 'v.nombre', 'v.siglas')->distinct()
            ->orderBy('v.nombre')
            ->get();
        $semana_pasada = getSemanaByDate(opDiasFecha('-', 0, hoy()));
        return view('adminlte.crm.tabla_bqt.inicio', [
            'annos' => $annos,
            'recetas' => $recetas,
            'semana_pasada' => $semana_pasada,
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
                $sem_desde = $listado_annos[0]['semanas'][0];
                $sem_hasta = end($listado_annos[count($listado_annos) - 1]['semanas']);

                if ($request->tipo_listado == 'C') {    // por clientes
                    $view = 'semanal_clientes';
                    // clientes version anterior
                    $clientes_old = DB::table('import_pedido as p')
                        ->join('detalle_import_pedido as d', 'd.id_import_pedido', '=', 'p.id_import_pedido')
                        ->join('detalle_cliente as c', 'c.id_cliente', '=', 'p.id_cliente')
                        ->select('p.id_cliente', 'c.nombre')->distinct()
                        ->where('c.estado', 1)
                        ->where('p.fecha', '>=', $sem_desde->fecha_inicial)
                        ->where('p.fecha', '<=', $sem_hasta->fecha_final);
                    if ($request->cliente != 'T')
                        $clientes_old = $clientes_old->where('p.id_cliente', $request->cliente);
                    if ($request->receta != 'T')
                        $clientes_old = $clientes_old->where('d.id_variedad', $request->receta);
                    $clientes_old = $clientes_old->orderBy('c.nombre')
                        ->get();
                    $ids_clientes = $clientes_old->pluck('id_cliente')->toArray();
                    // clientes version nueva
                    $clientes_new = DB::table('postco as p')
                        ->join('postco_clientes as pc', 'pc.id_postco', '=', 'p.id_postco')
                        ->join('detalle_cliente as c', 'c.id_cliente', '=', 'pc.id_cliente')
                        ->select('pc.id_cliente', 'c.nombre')->distinct()
                        ->where('c.estado', 1)
                        ->where('p.fecha', '>=', $sem_desde->fecha_inicial)
                        ->where('p.fecha', '<=', $sem_hasta->fecha_final)
                        ->whereNotIn('pc.id_cliente', $ids_clientes);
                    if ($request->cliente != 'T')
                        $clientes_new = $clientes_new->where('pc.id_cliente', $request->cliente);
                    if ($request->receta != 'T')
                        $clientes_new = $clientes_new->where('p.id_variedad', $request->receta);
                    $clientes_new = $clientes_new->orderBy('c.nombre')
                        ->get();
                    $clientes = $clientes_old->merge($clientes_new);
                    foreach ($clientes as $c) {
                        $valores_anno = [];
                        foreach ($listado_annos as $a) {
                            $valores_semanas = [];
                            foreach ($a['semanas'] as $sem) {
                                if ($sem->codigo < 2515) {
                                    $valor = DB::table('import_pedido as p')
                                        ->join('detalle_import_pedido as d', 'd.id_import_pedido', '=', 'p.id_import_pedido')
                                        ->select(
                                            DB::raw('sum(d.caja * d.ramos) as cantidad')
                                        )
                                        ->where('p.id_cliente', $c->id_cliente)
                                        ->where('p.fecha', '>=', $sem->fecha_inicial)
                                        ->where('p.fecha', '<=', $sem->fecha_final);
                                    if ($request->receta != 'T')
                                        $valor = $valor->where('d.id_variedad', $request->receta);
                                    $valor = $valor->get()[0]->cantidad;
                                } else {
                                    $valor = DB::table('postco as p')
                                        ->join('postco_clientes as pc', 'pc.id_postco', '=', 'p.id_postco')
                                        ->select(
                                            DB::raw('sum(p.ramos) as cantidad')
                                        )
                                        ->where('pc.id_cliente', $c->id_cliente)
                                        ->where('p.fecha', '>=', $sem->fecha_inicial)
                                        ->where('p.fecha', '<=', $sem->fecha_final);
                                    if ($request->receta != 'T')
                                        $valor = $valor->where('p.id_variedad', $request->receta);
                                    $valor = $valor->get()[0]->cantidad;
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
                    // plantas version anterior
                    $plantas_old = DB::table('import_pedido as p')
                        ->join('detalle_import_pedido as d', 'd.id_import_pedido', '=', 'p.id_import_pedido')
                        ->join('variedad as v', 'v.id_variedad', '=', 'd.id_variedad')
                        ->select('d.id_variedad', 'v.siglas', 'v.nombre')->distinct()
                        ->where('p.fecha', '>=', $sem_desde->fecha_inicial)
                        ->where('p.fecha', '<=', $sem_hasta->fecha_final);
                    if ($request->receta != 'T')
                        $plantas_old = $plantas_old->where('d.id_variedad', $request->receta);
                    if ($request->cliente != 'T')
                        $plantas_old = $plantas_old->where('p.id_cliente', $request->cliente);
                    $plantas_old = $plantas_old->orderBy('v.nombre')
                        ->get();
                    $ids_plantas = $plantas_old->pluck('id_variedad')->toArray();
                    // plantas nueva version
                    $plantas_new = DB::table('postco as p')
                        ->join('variedad as v', 'v.id_variedad', '=', 'p.id_variedad')
                        ->join('postco_clientes as pc', 'pc.id_postco', '=', 'p.id_postco')
                        ->select('p.id_variedad', 'v.siglas', 'v.nombre')->distinct()
                        ->where('p.fecha', '>=', $sem_desde->fecha_inicial)
                        ->where('p.fecha', '<=', $sem_hasta->fecha_final)
                        ->whereNotIn('p.id_variedad', $ids_plantas);
                    if ($request->receta != 'T')
                        $plantas_new = $plantas_new->where('p.id_variedad', $request->receta);
                    if ($request->cliente != 'T')
                        $plantas_new = $plantas_new->where('pc.id_cliente', $request->cliente);
                    $plantas_new = $plantas_new->orderBy('v.nombre')
                        ->get();
                    $plantas = $plantas_old->merge($plantas_new);
                    foreach ($plantas as $p) {
                        $valores_anno = [];
                        foreach ($listado_annos as $a) {
                            $valores_semanas = [];
                            foreach ($a['semanas'] as $sem) {
                                if ($sem->codigo < 2515) {
                                    $valor = DB::table('import_pedido as p')
                                        ->join('detalle_import_pedido as d', 'd.id_import_pedido', '=', 'p.id_import_pedido')
                                        ->select(
                                            DB::raw('sum(d.caja * d.ramos) as cantidad'),
                                        )
                                        ->where('d.id_variedad', $p->id_variedad)
                                        ->where('p.fecha', '>=', $sem->fecha_inicial)
                                        ->where('p.fecha', '<=', $sem->fecha_final);
                                    if ($request->cliente != 'T')
                                        $valor = $valor->where('p.id_cliente', $request->cliente);
                                    $valor = $valor->get()[0]->cantidad;
                                } else {
                                    $valor = DB::table('postco as p')
                                        ->join('postco_clientes as pc', 'pc.id_postco', '=', 'p.id_postco')
                                        ->select(
                                            DB::raw('sum(pc.cantidad) as cantidad'),
                                        )
                                        ->where('p.id_variedad', $p->id_variedad)
                                        ->where('p.fecha', '>=', $sem->fecha_inicial)
                                        ->where('p.fecha', '<=', $sem->fecha_final);
                                    if ($request->cliente != 'T')
                                        $valor = $valor->where('pc.id_cliente', $request->cliente);
                                    $valor = $valor->get()[0]->cantidad;
                                }

                                if ($request->filtro_criterio == 'P') {   // PRECIO
                                    $precio = DB::table('detalle_receta')
                                        ->select(DB::raw('sum(unidades * precio) as monto'))
                                        ->where('id_variedad', $p->id_variedad)
                                        ->where('defecto', 1)
                                        ->get()[0]->monto;
                                    $valor = $valor * $precio;
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
                $mes_desde = $listado_annos[0]['anno'] . '-' . $listado_annos[0]['meses'][0] . '-01';
                $mes_hasta = $listado_annos[count($listado_annos) - 1]['anno'] . '-' . end($listado_annos[count($listado_annos) - 1]['meses']) . '-31';
                if ($request->tipo_listado == 'C') {    // por clientes
                    $view = 'mensual_clientes';
                    // clientes version anterior
                    $clientes_old = DB::table('import_pedido as p')
                        ->join('detalle_import_pedido as d', 'd.id_import_pedido', '=', 'p.id_import_pedido')
                        ->join('detalle_cliente as c', 'c.id_cliente', '=', 'p.id_cliente')
                        ->select('p.id_cliente', 'c.nombre')->distinct()
                        ->where('c.estado', 1)
                        ->where('p.fecha', '>=', $mes_desde)
                        ->where('p.fecha', '<=', $mes_hasta);
                    if ($request->cliente != 'T')
                        $clientes_old = $clientes_old->where('p.id_cliente', $request->cliente);
                    if ($request->receta != 'T')
                        $clientes_old = $clientes_old->where('d.id_variedad', $request->receta);
                    $clientes_old = $clientes_old->orderBy('c.nombre')
                        ->get();
                    $ids_clientes = $clientes_old->pluck('id_cliente')->toArray();
                    // clientes version nueva
                    $clientes_new = DB::table('postco as p')
                        ->join('postco_clientes as pc', 'pc.id_postco', '=', 'p.id_postco')
                        ->join('detalle_cliente as c', 'c.id_cliente', '=', 'pc.id_cliente')
                        ->select('pc.id_cliente', 'c.nombre')->distinct()
                        ->where('c.estado', 1)
                        ->where('p.fecha', '>=', $mes_desde)
                        ->where('p.fecha', '<=', $mes_hasta)
                        ->whereNotIn('pc.id_cliente', $ids_clientes);
                    if ($request->cliente != 'T')
                        $clientes_new = $clientes_new->where('pc.id_cliente', $request->cliente);
                    if ($request->receta != 'T')
                        $clientes_new = $clientes_new->where('p.id_variedad', $request->receta);
                    $clientes_new = $clientes_new->orderBy('c.nombre')
                        ->get();
                    $clientes = $clientes_old->merge($clientes_new);
                    foreach ($clientes as $c) {
                        $valores_anno = [];
                        foreach ($listado_annos as $a) {
                            $valores_meses = [];
                            foreach ($a['meses'] as $mes) {
                                if ($a['anno'] . '-' . $mes . '-01' < '2025-04-01') {
                                    $valor = DB::table('import_pedido as p')
                                        ->join('detalle_import_pedido as d', 'd.id_import_pedido', '=', 'p.id_import_pedido')
                                        ->select(
                                            DB::raw('sum(d.caja * d.ramos) as cantidad'),
                                        )
                                        ->where('p.id_cliente', $c->id_cliente)
                                        ->whereMonth('p.fecha', $mes)
                                        ->whereYear('p.fecha', $a['anno']);
                                    if ($request->receta != 'T')
                                        $valor = $valor->where('d.id_variedad', $request->receta);
                                    $valor = $valor->get()[0]->cantidad;
                                } else {
                                    $valor = DB::table('postco as p')
                                        ->join('postco_clientes as pc', 'pc.id_postco', '=', 'p.id_postco')
                                        ->select(
                                            DB::raw('sum(p.ramos) as cantidad'),
                                        )
                                        ->where('pc.id_cliente', $c->id_cliente)
                                        ->whereMonth('p.fecha', $mes)
                                        ->whereYear('p.fecha', $a['anno']);
                                    if ($request->receta != 'T')
                                        $valor = $valor->where('p.id_variedad', $request->receta);
                                    $valor = $valor->get()[0]->cantidad;
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
                    // plantas version anterior
                    $plantas_old = DB::table('import_pedido as p')
                        ->join('detalle_import_pedido as d', 'd.id_import_pedido', '=', 'p.id_import_pedido')
                        ->join('variedad as v', 'v.id_variedad', '=', 'd.id_variedad')
                        ->select('d.id_variedad', 'v.siglas', 'v.nombre')->distinct()
                        ->where('p.fecha', '>=', $mes_desde)
                        ->where('p.fecha', '<=', $mes_hasta);
                    if ($request->receta != 'T')
                        $plantas_old = $plantas_old->where('d.id_variedad', $request->receta);
                    if ($request->cliente != 'T')
                        $plantas_old = $plantas_old->where('p.id_cliente', $request->cliente);
                    $plantas_old = $plantas_old->orderBy('v.nombre')
                        ->get();
                    $ids_plantas = $plantas_old->pluck('id_variedad')->toArray();
                    // plantas nueva version
                    $plantas_new = DB::table('postco as p')
                        ->join('variedad as v', 'v.id_variedad', '=', 'p.id_variedad')
                        ->join('postco_clientes as pc', 'pc.id_postco', '=', 'p.id_postco')
                        ->select('p.id_variedad', 'v.siglas', 'v.nombre')->distinct()
                        ->where('p.fecha', '>=', $mes_desde)
                        ->where('p.fecha', '<=', $mes_hasta)
                        ->whereNotIn('p.id_variedad', $ids_plantas);
                    if ($request->receta != 'T')
                        $plantas_new = $plantas_new->where('p.id_variedad', $request->receta);
                    if ($request->cliente != 'T')
                        $plantas_new = $plantas_new->where('pc.id_cliente', $request->cliente);
                    $plantas_new = $plantas_new->orderBy('v.nombre')
                        ->get();
                    $plantas = $plantas_old->merge($plantas_new);
                    foreach ($plantas as $p) {
                        $valores_anno = [];
                        foreach ($listado_annos as $a) {
                            $valores_meses = [];
                            foreach ($a['meses'] as $mes) {
                                if ($a['anno'] . '-' . $mes . '-01' < '2025-04-01') {
                                    $valor = DB::table('import_pedido as p')
                                        ->join('detalle_import_pedido as d', 'd.id_import_pedido', '=', 'p.id_import_pedido')
                                        ->select(
                                            DB::raw('sum(d.caja * d.ramos) as cantidad'),
                                        )
                                        ->where('d.id_variedad', $p->id_variedad)
                                        ->whereMonth('p.fecha', $mes)
                                        ->whereYear('p.fecha', $a['anno']);
                                    if ($request->cliente != 'T')
                                        $valor = $valor->where('p.id_cliente', $request->cliente);
                                    $valor = $valor->get()[0]->cantidad;
                                } else {
                                    $valor = DB::table('postco as p')
                                        ->join('postco_clientes as pc', 'pc.id_postco', '=', 'p.id_postco')
                                        ->select(
                                            DB::raw('sum(p.ramos) as cantidad'),
                                        )
                                        ->where('p.id_variedad', $p->id_variedad)
                                        ->whereMonth('p.fecha', $mes)
                                        ->whereYear('p.fecha', $a['anno']);
                                    if ($request->cliente != 'T')
                                        $valor = $valor->where('pc.id_cliente', $request->cliente);
                                    $valor = $valor->get()[0]->cantidad;
                                }

                                if ($request->filtro_criterio == 'P') {   // PRECIO
                                    $precio = DB::table('detalle_receta')
                                        ->select(DB::raw('sum(unidades * precio) as monto'))
                                        ->where('id_variedad', $p->id_variedad)
                                        ->where('defecto', 1)
                                        ->get()[0]->monto;
                                    $valor = $valor * $precio;
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
        return view('adminlte.crm.tabla_bqt.partials.' . $view, [
            'listado' => $listado,
            'listado_annos' => $listado_annos,
            'criterio' => $request->criterio,
            'filtro_criterio' => $request->filtro_criterio,
        ]);
    }

    public function select_planta_diario(Request $request)
    {
        $sem = getObjSemana($request->semana);
        if ($sem->codigo < 2515) {
            $fechas = DB::table('import_pedido as p')
                ->join('detalle_import_pedido as d', 'd.id_import_pedido', '=', 'p.id_import_pedido')
                ->select('p.fecha')->distinct()
                ->where('d.id_variedad', $request->planta)
                ->where('p.fecha', '>=', $sem->fecha_inicial)
                ->where('p.fecha', '<=', $sem->fecha_final)
                ->orderBy('p.fecha')
                ->get()->pluck('fecha')->toArray();
        } else {
            $fechas = DB::table('postco as p')
                ->select('p.fecha')->distinct()
                ->where('p.id_variedad', $request->planta)
                ->where('p.fecha', '>=', $sem->fecha_inicial)
                ->where('p.fecha', '<=', $sem->fecha_final)
                ->orderBy('p.fecha')
                ->get()->pluck('fecha')->toArray();
        }

        $listado = [];
        foreach ($fechas as $f) {
            if ($sem->codigo < 2515) {
                $valor = DB::table('import_pedido as p')
                    ->join('detalle_import_pedido as d', 'd.id_import_pedido', '=', 'p.id_import_pedido')
                    ->select(
                        DB::raw('sum(d.caja * d.ramos) as cantidad'),
                    )
                    ->where('d.id_variedad', $request->planta)
                    ->where('p.fecha', $f);
                if ($request->cliente != 'T')
                    $valor = $valor->where('p.id_cliente', $request->cliente);
                $valor = $valor->get()[0]->cantidad;
            } else {
                $valor = DB::table('postco as p')
                    ->join('postco_clientes as pc', 'pc.id_postco', '=', 'p.id_postco')
                    ->select(
                        DB::raw('sum(p.ramos) as cantidad'),
                    )
                    ->where('p.id_variedad', $request->planta)
                    ->where('p.fecha', $f);
                if ($request->cliente != 'T')
                    $valor = $valor->where('pc.id_cliente', $request->cliente);
                $valor = $valor->get()[0]->cantidad;
            }
            $precio = DB::table('detalle_receta')
                ->select(DB::raw('sum(unidades * precio) as monto'))
                ->where('id_variedad', $request->planta)
                ->where('defecto', 1)
                ->get()[0]->monto;
            $listado[] = [
                'fecha' => $f,
                'valor' => $valor != '' ? $valor : 0,
                'precio' => $precio != '' ? $precio : 0,
            ];
        }
        return view('adminlte.crm.tabla_bqt.partials.detalles.select_planta_diario', [
            'listado' => $listado,
            'fechas' => $fechas,
            'semana' => $sem,
            'receta' => Variedad::find($request->planta),
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
                                ->where('h.mes', $mes);
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
                                ->where('h.mes', $mes);
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
                                ->where('h.mes', $mes);
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
                                ->where('h.mes', $mes);
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
            return view('adminlte.crm.tabla_bqt.partials.detalles.select_planta_mensual', [
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

        $fileName = "Tabla BQT.xlsx";
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
                $sem_desde = $listado_annos[0]['semanas'][0];
                $sem_hasta = end($listado_annos[count($listado_annos) - 1]['semanas']);

                if ($request->tipo_listado == 'C') {    // por clientes
                    $view = 'semanal_clientes';
                    // clientes version anterior
                    $clientes_old = DB::table('import_pedido as p')
                        ->join('detalle_import_pedido as d', 'd.id_import_pedido', '=', 'p.id_import_pedido')
                        ->join('detalle_cliente as c', 'c.id_cliente', '=', 'p.id_cliente')
                        ->select('p.id_cliente', 'c.nombre')->distinct()
                        ->where('c.estado', 1)
                        ->where('p.fecha', '>=', $sem_desde->fecha_inicial)
                        ->where('p.fecha', '<=', $sem_hasta->fecha_final);
                    if ($request->cliente != 'T')
                        $clientes_old = $clientes_old->where('p.id_cliente', $request->cliente);
                    if ($request->receta != 'T')
                        $clientes_old = $clientes_old->where('d.id_variedad', $request->receta);
                    $clientes_old = $clientes_old->orderBy('c.nombre')
                        ->get();
                    $ids_clientes = $clientes_old->pluck('id_cliente')->toArray();
                    // clientes version nueva
                    $clientes_new = DB::table('postco as p')
                        ->join('postco_clientes as pc', 'pc.id_postco', '=', 'p.id_postco')
                        ->join('detalle_cliente as c', 'c.id_cliente', '=', 'pc.id_cliente')
                        ->select('pc.id_cliente', 'c.nombre')->distinct()
                        ->where('c.estado', 1)
                        ->where('p.fecha', '>=', $sem_desde->fecha_inicial)
                        ->where('p.fecha', '<=', $sem_hasta->fecha_final)
                        ->whereNotIn('pc.id_cliente', $ids_clientes);
                    if ($request->cliente != 'T')
                        $clientes_new = $clientes_new->where('pc.id_cliente', $request->cliente);
                    if ($request->receta != 'T')
                        $clientes_new = $clientes_new->where('p.id_variedad', $request->receta);
                    $clientes_new = $clientes_new->orderBy('c.nombre')
                        ->get();
                    $clientes = $clientes_old->merge($clientes_new);
                    foreach ($clientes as $c) {
                        $valores_anno = [];
                        foreach ($listado_annos as $a) {
                            $valores_semanas = [];
                            foreach ($a['semanas'] as $sem) {
                                if ($sem->codigo < 2515) {
                                    $valor = DB::table('import_pedido as p')
                                        ->join('detalle_import_pedido as d', 'd.id_import_pedido', '=', 'p.id_import_pedido')
                                        ->select(
                                            DB::raw('sum(d.caja * d.ramos) as cantidad')
                                        )
                                        ->where('p.id_cliente', $c->id_cliente)
                                        ->where('p.fecha', '>=', $sem->fecha_inicial)
                                        ->where('p.fecha', '<=', $sem->fecha_final);
                                    if ($request->receta != 'T')
                                        $valor = $valor->where('d.id_variedad', $request->receta);
                                    $valor = $valor->get()[0]->cantidad;
                                } else {
                                    $valor = DB::table('postco as p')
                                        ->join('postco_clientes as pc', 'pc.id_postco', '=', 'p.id_postco')
                                        ->select(
                                            DB::raw('sum(p.ramos) as cantidad')
                                        )
                                        ->where('pc.id_cliente', $c->id_cliente)
                                        ->where('p.fecha', '>=', $sem->fecha_inicial)
                                        ->where('p.fecha', '<=', $sem->fecha_final);
                                    if ($request->receta != 'T')
                                        $valor = $valor->where('p.id_variedad', $request->receta);
                                    $valor = $valor->get()[0]->cantidad;
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
                    // plantas version anterior
                    $plantas_old = DB::table('import_pedido as p')
                        ->join('detalle_import_pedido as d', 'd.id_import_pedido', '=', 'p.id_import_pedido')
                        ->join('variedad as v', 'v.id_variedad', '=', 'd.id_variedad')
                        ->select('d.id_variedad', 'v.siglas', 'v.nombre')->distinct()
                        ->where('p.fecha', '>=', $sem_desde->fecha_inicial)
                        ->where('p.fecha', '<=', $sem_hasta->fecha_final);
                    if ($request->receta != 'T')
                        $plantas_old = $plantas_old->where('d.id_variedad', $request->receta);
                    if ($request->cliente != 'T')
                        $plantas_old = $plantas_old->where('p.id_cliente', $request->cliente);
                    $plantas_old = $plantas_old->orderBy('v.nombre')
                        ->get();
                    $ids_plantas = $plantas_old->pluck('id_variedad')->toArray();
                    // plantas nueva version
                    $plantas_new = DB::table('postco as p')
                        ->join('variedad as v', 'v.id_variedad', '=', 'p.id_variedad')
                        ->join('postco_clientes as pc', 'pc.id_postco', '=', 'p.id_postco')
                        ->select('p.id_variedad', 'v.siglas', 'v.nombre')->distinct()
                        ->where('p.fecha', '>=', $sem_desde->fecha_inicial)
                        ->where('p.fecha', '<=', $sem_hasta->fecha_final)
                        ->whereNotIn('p.id_variedad', $ids_plantas);
                    if ($request->receta != 'T')
                        $plantas_new = $plantas_new->where('p.id_variedad', $request->receta);
                    if ($request->cliente != 'T')
                        $plantas_new = $plantas_new->where('pc.id_cliente', $request->cliente);
                    $plantas_new = $plantas_new->orderBy('v.nombre')
                        ->get();
                    $plantas = $plantas_old->merge($plantas_new);
                    foreach ($plantas as $p) {
                        $valores_anno = [];
                        foreach ($listado_annos as $a) {
                            $valores_semanas = [];
                            foreach ($a['semanas'] as $sem) {
                                if ($sem->codigo < 2515) {
                                    $valor = DB::table('import_pedido as p')
                                        ->join('detalle_import_pedido as d', 'd.id_import_pedido', '=', 'p.id_import_pedido')
                                        ->select(
                                            DB::raw('sum(d.caja * d.ramos) as cantidad'),
                                        )
                                        ->where('d.id_variedad', $p->id_variedad)
                                        ->where('p.fecha', '>=', $sem->fecha_inicial)
                                        ->where('p.fecha', '<=', $sem->fecha_final);
                                    if ($request->cliente != 'T')
                                        $valor = $valor->where('p.id_cliente', $request->cliente);
                                    $valor = $valor->get()[0]->cantidad;
                                } else {
                                    $valor = DB::table('postco as p')
                                        ->join('postco_clientes as pc', 'pc.id_postco', '=', 'p.id_postco')
                                        ->select(
                                            DB::raw('sum(pc.cantidad) as cantidad'),
                                        )
                                        ->where('p.id_variedad', $p->id_variedad)
                                        ->where('p.fecha', '>=', $sem->fecha_inicial)
                                        ->where('p.fecha', '<=', $sem->fecha_final);
                                    if ($request->cliente != 'T')
                                        $valor = $valor->where('pc.id_cliente', $request->cliente);
                                    $valor = $valor->get()[0]->cantidad;
                                }

                                if ($request->filtro_criterio == 'P') {   // PRECIO
                                    $precio = DB::table('detalle_receta')
                                        ->select(DB::raw('sum(unidades * precio) as monto'))
                                        ->where('id_variedad', $p->id_variedad)
                                        ->where('defecto', 1)
                                        ->get()[0]->monto;
                                    $valor = $valor * $precio;
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
                $mes_desde = $listado_annos[0]['anno'] . '-' . $listado_annos[0]['meses'][0] . '-01';
                $mes_hasta = $listado_annos[count($listado_annos) - 1]['anno'] . '-' . end($listado_annos[count($listado_annos) - 1]['meses']) . '-31';
                if ($request->tipo_listado == 'C') {    // por clientes
                    $view = 'mensual_clientes';
                    // clientes version anterior
                    $clientes_old = DB::table('import_pedido as p')
                        ->join('detalle_import_pedido as d', 'd.id_import_pedido', '=', 'p.id_import_pedido')
                        ->join('detalle_cliente as c', 'c.id_cliente', '=', 'p.id_cliente')
                        ->select('p.id_cliente', 'c.nombre')->distinct()
                        ->where('c.estado', 1)
                        ->where('p.fecha', '>=', $mes_desde)
                        ->where('p.fecha', '<=', $mes_hasta);
                    if ($request->cliente != 'T')
                        $clientes_old = $clientes_old->where('p.id_cliente', $request->cliente);
                    if ($request->receta != 'T')
                        $clientes_old = $clientes_old->where('d.id_variedad', $request->receta);
                    $clientes_old = $clientes_old->orderBy('c.nombre')
                        ->get();
                    $ids_clientes = $clientes_old->pluck('id_cliente')->toArray();
                    // clientes version nueva
                    $clientes_new = DB::table('postco as p')
                        ->join('postco_clientes as pc', 'pc.id_postco', '=', 'p.id_postco')
                        ->join('detalle_cliente as c', 'c.id_cliente', '=', 'pc.id_cliente')
                        ->select('pc.id_cliente', 'c.nombre')->distinct()
                        ->where('c.estado', 1)
                        ->where('p.fecha', '>=', $mes_desde)
                        ->where('p.fecha', '<=', $mes_hasta)
                        ->whereNotIn('pc.id_cliente', $ids_clientes);
                    if ($request->cliente != 'T')
                        $clientes_new = $clientes_new->where('pc.id_cliente', $request->cliente);
                    if ($request->receta != 'T')
                        $clientes_new = $clientes_new->where('p.id_variedad', $request->receta);
                    $clientes_new = $clientes_new->orderBy('c.nombre')
                        ->get();
                    $clientes = $clientes_old->merge($clientes_new);
                    foreach ($clientes as $c) {
                        $valores_anno = [];
                        foreach ($listado_annos as $a) {
                            $valores_meses = [];
                            foreach ($a['meses'] as $mes) {
                                if ($a['anno'] . '-' . $mes . '-01' < '2025-04-01') {
                                    $valor = DB::table('import_pedido as p')
                                        ->join('detalle_import_pedido as d', 'd.id_import_pedido', '=', 'p.id_import_pedido')
                                        ->select(
                                            DB::raw('sum(d.caja * d.ramos) as cantidad'),
                                        )
                                        ->where('p.id_cliente', $c->id_cliente)
                                        ->whereMonth('p.fecha', $mes)
                                        ->whereYear('p.fecha', $a['anno']);
                                    if ($request->receta != 'T')
                                        $valor = $valor->where('d.id_variedad', $request->receta);
                                    $valor = $valor->get()[0]->cantidad;
                                } else {
                                    $valor = DB::table('postco as p')
                                        ->join('postco_clientes as pc', 'pc.id_postco', '=', 'p.id_postco')
                                        ->select(
                                            DB::raw('sum(p.ramos) as cantidad'),
                                        )
                                        ->where('pc.id_cliente', $c->id_cliente)
                                        ->whereMonth('p.fecha', $mes)
                                        ->whereYear('p.fecha', $a['anno']);
                                    if ($request->receta != 'T')
                                        $valor = $valor->where('p.id_variedad', $request->receta);
                                    $valor = $valor->get()[0]->cantidad;
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
                    // plantas version anterior
                    $plantas_old = DB::table('import_pedido as p')
                        ->join('detalle_import_pedido as d', 'd.id_import_pedido', '=', 'p.id_import_pedido')
                        ->join('variedad as v', 'v.id_variedad', '=', 'd.id_variedad')
                        ->select('d.id_variedad', 'v.siglas', 'v.nombre')->distinct()
                        ->where('p.fecha', '>=', $mes_desde)
                        ->where('p.fecha', '<=', $mes_hasta);
                    if ($request->receta != 'T')
                        $plantas_old = $plantas_old->where('d.id_variedad', $request->receta);
                    if ($request->cliente != 'T')
                        $plantas_old = $plantas_old->where('p.id_cliente', $request->cliente);
                    $plantas_old = $plantas_old->orderBy('v.nombre')
                        ->get();
                    $ids_plantas = $plantas_old->pluck('id_variedad')->toArray();
                    // plantas nueva version
                    $plantas_new = DB::table('postco as p')
                        ->join('variedad as v', 'v.id_variedad', '=', 'p.id_variedad')
                        ->join('postco_clientes as pc', 'pc.id_postco', '=', 'p.id_postco')
                        ->select('p.id_variedad', 'v.siglas', 'v.nombre')->distinct()
                        ->where('p.fecha', '>=', $mes_desde)
                        ->where('p.fecha', '<=', $mes_hasta)
                        ->whereNotIn('p.id_variedad', $ids_plantas);
                    if ($request->receta != 'T')
                        $plantas_new = $plantas_new->where('p.id_variedad', $request->receta);
                    if ($request->cliente != 'T')
                        $plantas_new = $plantas_new->where('pc.id_cliente', $request->cliente);
                    $plantas_new = $plantas_new->orderBy('v.nombre')
                        ->get();
                    $plantas = $plantas_old->merge($plantas_new);
                    foreach ($plantas as $p) {
                        $valores_anno = [];
                        foreach ($listado_annos as $a) {
                            $valores_meses = [];
                            foreach ($a['meses'] as $mes) {
                                if ($a['anno'] . '-' . $mes . '-01' < '2025-04-01') {
                                    $valor = DB::table('import_pedido as p')
                                        ->join('detalle_import_pedido as d', 'd.id_import_pedido', '=', 'p.id_import_pedido')
                                        ->select(
                                            DB::raw('sum(d.caja * d.ramos) as cantidad'),
                                        )
                                        ->where('d.id_variedad', $p->id_variedad)
                                        ->whereMonth('p.fecha', $mes)
                                        ->whereYear('p.fecha', $a['anno']);
                                    if ($request->cliente != 'T')
                                        $valor = $valor->where('p.id_cliente', $request->cliente);
                                    $valor = $valor->get()[0]->cantidad;
                                } else {
                                    $valor = DB::table('postco as p')
                                        ->join('postco_clientes as pc', 'pc.id_postco', '=', 'p.id_postco')
                                        ->select(
                                            DB::raw('sum(p.ramos) as cantidad'),
                                        )
                                        ->where('p.id_variedad', $p->id_variedad)
                                        ->whereMonth('p.fecha', $mes)
                                        ->whereYear('p.fecha', $a['anno']);
                                    if ($request->cliente != 'T')
                                        $valor = $valor->where('pc.id_cliente', $request->cliente);
                                    $valor = $valor->get()[0]->cantidad;
                                }

                                if ($request->filtro_criterio == 'P') {   // PRECIO
                                    $precio = DB::table('detalle_receta')
                                        ->select(DB::raw('sum(unidades * precio) as monto'))
                                        ->where('id_variedad', $p->id_variedad)
                                        ->where('defecto', 1)
                                        ->get()[0]->monto;
                                    $valor = $valor * $precio;
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
}
