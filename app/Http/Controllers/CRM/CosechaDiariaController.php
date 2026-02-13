<?php

namespace yura\Http\Controllers\CRM;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use yura\Http\Controllers\Controller;
use yura\Jobs\jobActualizarCosechaDiaria;
use yura\Modelos\CosechaDiaria;
use yura\Modelos\Planta;
use yura\Modelos\Submenu;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class CosechaDiariaController extends Controller
{
    public function inicio(Request $request)
    {
        $finca = getFincaActiva();
        $sectores = DB::table('desglose_recepcion as dr')
            ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
            ->join('modulo as m', 'm.id_modulo', '=', 'dr.id_modulo')
            ->join('sector as s', 's.id_sector', '=', 'm.id_sector')
            ->select('m.id_sector', 's.nombre')->distinct()
            ->where('dr.id_empresa', $finca)
            ->where('r.estado', 1)
            ->where('m.estado', 1)
            ->orderBy('s.nombre')
            ->get();
        return view('adminlte.crm.cosecha_diaria.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'plantas' => DB::table('cosecha_diaria')
                ->select('id_planta', 'planta_nombre as nombre')->distinct()
                ->where('id_empresa', $finca)
                ->where('cosechados', '>', 0)
                ->orderBy('planta_nombre')->get(),
            'sectores' => $sectores,
            'desde' => opDiasFecha('-', 7, hoy()),
            'hasta' => opDiasFecha('-', 1, hoy()),
        ]);
    }

    public function buscar_cosecha_diaria(Request $request)
    {
        $finca = getFincaActiva();
        $fechas = DB::table('cosecha_diaria')
            ->select('fecha')->distinct()
            ->where('id_empresa', $finca)
            ->where('fecha', '>=', $request->desde)
            ->where('fecha', '<=', $request->hasta)
            ->where('cosechados', '>', 0)
            ->orderBy('fecha')->get();
        $variedades = DB::table('cosecha_diaria as c')
            ->join('variedad as v', 'v.id_variedad', '=', 'c.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->select('c.id_variedad', 'v.id_planta', 'v.nombre as variedad_nombre', 'p.nombre as planta_nombre')->distinct()
            ->where('c.id_empresa', $finca)
            ->where('c.cosechados', '>', 0)
            ->where('c.fecha', '>=', $request->desde)
            ->where('c.fecha', '<=', $request->hasta);
        if ($request->variedad != 'T')
            $variedades = $variedades->where('c.id_variedad', $request->variedad);
        elseif ($request->planta != '')
            $variedades = $variedades->where('v.id_planta', $request->planta);
        $variedades = $variedades
            ->orderBy('p.nombre')
            ->orderBy('v.nombre')
            ->get();
        $data = [];
        if (count($variedades) > 0) {
            $planta_anterior = $variedades[0]->id_planta;
            $resumen = DB::table('cosecha_diaria')
                ->select(DB::raw('sum(cosechados) as cantidad'), 'fecha')
                ->where('id_empresa', $finca)
                ->where('id_planta', $variedades[0]->id_planta)
                ->where('fecha', '>=', $request->desde)
                ->where('fecha', '<=', $request->hasta);
            if ($request->sector != '')
                $resumen = $resumen->where('id_sector', $request->sector);
            $resumen = $resumen->groupBy('fecha')
                ->orderBy('fecha')
                ->get();
            $plantas_iniciales_resumen = DB::table('ciclo as c')
                ->join('variedad as v', 'v.id_variedad', '=', 'c.id_variedad')
                ->join('modulo as m', 'm.id_modulo', '=', 'c.id_modulo')
                ->select(DB::raw('sum(c.plantas_iniciales) as cant'))
                ->where('c.estado', 1)
                ->where('c.activo', 1)
                ->where('c.id_empresa', $finca)
                ->where('v.id_planta', $variedades[0]->id_planta);
            if ($request->sector != '')
                $plantas_iniciales_resumen = $plantas_iniciales_resumen->where('m.id_sector', $request->sector);
            $plantas_iniciales_resumen = $plantas_iniciales_resumen->Where(function ($q) use ($request) {
                $q->where('c.fecha_fin', '>=', $request->desde)
                    ->where('c.fecha_fin', '<=', $request->hasta)
                    ->orWhere(function ($q) use ($request) {
                        $q->where('c.fecha_inicio', '>=', $request->desde)
                            ->where('c.fecha_inicio', '<=', $request->hasta);
                    })
                    ->orWhere(function ($q) use ($request) {
                        $q->where('c.fecha_inicio', '<', $request->desde)
                            ->where('c.fecha_fin', '>', $request->hasta);
                    });
            })
                ->get()[0]->cant;
            $lista = DB::table('cosecha_diaria')
                ->select(DB::raw('sum(cosechados) as cantidad'), 'fecha')
                ->where('id_empresa', $finca)
                ->where('id_variedad', $variedades[0]->id_variedad)
                ->where('fecha', '>=', $request->desde)
                ->where('fecha', '<=', $request->hasta);
            if ($request->sector != '')
                $lista = $lista->where('id_sector', $request->sector);
            $lista = $lista->groupBy('fecha')
                ->orderBy('fecha')
                ->get();
            $plantas_iniciales = DB::table('ciclo as c')
                ->join('variedad as v', 'v.id_variedad', '=', 'c.id_variedad')
                ->join('modulo as m', 'm.id_modulo', '=', 'c.id_modulo')
                ->select(DB::raw('sum(c.plantas_iniciales) as cant'))
                ->where('c.estado', 1)
                ->where('c.activo', 1)
                ->where('c.id_empresa', $finca)
                ->where('c.id_variedad', $variedades[0]->id_variedad);
            if ($request->sector != '')
                $plantas_iniciales = $plantas_iniciales->where('m.id_sector', $request->sector);
            $plantas_iniciales = $plantas_iniciales->Where(function ($q) use ($request) {
                $q->where('c.fecha_fin', '>=', $request->desde)
                    ->where('c.fecha_fin', '<=', $request->hasta)
                    ->orWhere(function ($q) use ($request) {
                        $q->where('c.fecha_inicio', '>=', $request->desde)
                            ->where('c.fecha_inicio', '<=', $request->hasta);
                    })
                    ->orWhere(function ($q) use ($request) {
                        $q->where('c.fecha_inicio', '<', $request->desde)
                            ->where('c.fecha_fin', '>', $request->hasta);
                    });
            })
                ->get()[0]->cant;
            array_push($data, [
                'tipo' => 'P',  // planta
                'variedad' => $variedades[0],
                'resumen' => $resumen,
                'plantas_iniciales_resumen' => $plantas_iniciales_resumen,
                'lista' => $lista,
                'plantas_iniciales' => $plantas_iniciales,
            ]);
            foreach ($variedades as $pos => $var) {
                if ($pos > 0) {
                    if ($var->id_planta == $planta_anterior) {
                        $lista = DB::table('cosecha_diaria')
                            ->select(DB::raw('sum(cosechados) as cantidad'), 'fecha')
                            ->where('id_empresa', $finca)
                            ->where('id_variedad', $var->id_variedad)
                            ->where('fecha', '>=', $request->desde)
                            ->where('fecha', '<=', $request->hasta);
                        if ($request->sector != '')
                            $lista = $lista->where('id_sector', $request->sector);
                        $lista = $lista->groupBy('fecha')
                            ->orderBy('fecha')
                            ->get();
                        $plantas_iniciales = DB::table('ciclo as c')
                            ->join('variedad as v', 'v.id_variedad', '=', 'c.id_variedad')
                            ->join('modulo as m', 'm.id_modulo', '=', 'c.id_modulo')
                            ->select(DB::raw('sum(c.plantas_iniciales) as cant'))
                            ->where('c.estado', 1)
                            ->where('c.activo', 1)
                            ->where('c.id_empresa', $finca)
                            ->where('c.id_variedad', $var->id_variedad);
                        if ($request->sector != '')
                            $plantas_iniciales = $plantas_iniciales->where('m.id_sector', $request->sector);
                        $plantas_iniciales = $plantas_iniciales->Where(function ($q) use ($request) {
                            $q->where('c.fecha_fin', '>=', $request->desde)
                                ->where('c.fecha_fin', '<=', $request->hasta)
                                ->orWhere(function ($q) use ($request) {
                                    $q->where('c.fecha_inicio', '>=', $request->desde)
                                        ->where('c.fecha_inicio', '<=', $request->hasta);
                                })
                                ->orWhere(function ($q) use ($request) {
                                    $q->where('c.fecha_inicio', '<', $request->desde)
                                        ->where('c.fecha_fin', '>', $request->hasta);
                                });
                        })
                            ->get()[0]->cant;
                        array_push($data, [
                            'tipo' => 'V',  // variedad
                            'variedad' => $var,
                            'lista' => $lista,
                            'plantas_iniciales' => $plantas_iniciales,
                        ]);
                    } else {
                        $resumen = DB::table('cosecha_diaria')
                            ->select(DB::raw('sum(cosechados) as cantidad'), 'fecha')
                            ->where('id_empresa', $finca)
                            ->where('id_planta', $var->id_planta)
                            ->where('fecha', '>=', $request->desde)
                            ->where('fecha', '<=', $request->hasta);
                        if ($request->sector != '')
                            $resumen = $resumen->where('id_sector', $request->sector);
                        $resumen = $resumen->groupBy('fecha')
                            ->orderBy('fecha')
                            ->get();
                        $plantas_iniciales_resumen = DB::table('ciclo as c')
                            ->join('variedad as v', 'v.id_variedad', '=', 'c.id_variedad')
                            ->join('modulo as m', 'm.id_modulo', '=', 'c.id_modulo')
                            ->select(DB::raw('sum(c.plantas_iniciales) as cant'))
                            ->where('c.estado', 1)
                            ->where('c.activo', 1)
                            ->where('c.id_empresa', $finca)
                            ->where('v.id_planta', $var->id_planta);
                        if ($request->sector != '')
                            $plantas_iniciales_resumen = $plantas_iniciales_resumen->where('m.id_sector', $request->sector);
                        $plantas_iniciales_resumen = $plantas_iniciales_resumen->Where(function ($q) use ($request) {
                            $q->where('c.fecha_fin', '>=', $request->desde)
                                ->where('c.fecha_fin', '<=', $request->hasta)
                                ->orWhere(function ($q) use ($request) {
                                    $q->where('c.fecha_inicio', '>=', $request->desde)
                                        ->where('c.fecha_inicio', '<=', $request->hasta);
                                })
                                ->orWhere(function ($q) use ($request) {
                                    $q->where('c.fecha_inicio', '<', $request->desde)
                                        ->where('c.fecha_fin', '>', $request->hasta);
                                });
                        })
                            ->get()[0]->cant;
                        $lista = DB::table('cosecha_diaria')
                            ->select(DB::raw('sum(cosechados) as cantidad'), 'fecha')
                            ->where('id_empresa', $finca)
                            ->where('id_variedad', $var->id_variedad)
                            ->where('fecha', '>=', $request->desde)
                            ->where('fecha', '<=', $request->hasta);
                        if ($request->sector != '')
                            $lista = $lista->where('id_sector', $request->sector);
                        $lista = $lista->groupBy('fecha')
                            ->orderBy('fecha')
                            ->get();
                        $plantas_iniciales = DB::table('ciclo as c')
                            ->join('variedad as v', 'v.id_variedad', '=', 'c.id_variedad')
                            ->join('modulo as m', 'm.id_modulo', '=', 'c.id_modulo')
                            ->select(DB::raw('sum(c.plantas_iniciales) as cant'))
                            ->where('c.estado', 1)
                            ->where('c.activo', 1)
                            ->where('c.id_empresa', $finca)
                            ->where('c.id_variedad', $var->id_variedad);
                        if ($request->sector != '')
                            $plantas_iniciales = $plantas_iniciales->where('m.id_sector', $request->sector);
                        $plantas_iniciales = $plantas_iniciales->Where(function ($q) use ($request) {
                            $q->where('c.fecha_fin', '>=', $request->desde)
                                ->where('c.fecha_fin', '<=', $request->hasta)
                                ->orWhere(function ($q) use ($request) {
                                    $q->where('c.fecha_inicio', '>=', $request->desde)
                                        ->where('c.fecha_inicio', '<=', $request->hasta);
                                })
                                ->orWhere(function ($q) use ($request) {
                                    $q->where('c.fecha_inicio', '<', $request->desde)
                                        ->where('c.fecha_fin', '>', $request->hasta);
                                });
                        })
                            ->get()[0]->cant;
                        array_push($data, [
                            'tipo' => 'P',  // planta
                            'variedad' => $var,
                            'resumen' => $resumen,
                            'plantas_iniciales_resumen' => $plantas_iniciales_resumen,
                            'lista' => $lista,
                            'plantas_iniciales' => $plantas_iniciales,
                        ]);
                        $planta_anterior = $var->id_planta;
                    }
                }
            }
        }
        return view('adminlte.crm.cosecha_diaria.partials.listado', [
            'variedades' => $variedades,
            'fechas' => $fechas,
            'data' => $data,
        ]);
    }

    public function actualizar_fecha(Request $request)
    {
        $finca = getFincaActiva();
        if ($request->hasta == '') {
            $model = CosechaDiaria::All()
                ->where('id_variedad', $request->variedad)
                ->where('fecha', $request->fecha)
                ->where('id_empresa', $finca)
                ->first();
            if ($model == '') {
                $variedad = getVariedad($request->variedad);
                $model = new CosechaDiaria();
                $model->id_variedad = $variedad->id_variedad;
                $model->variedad_nombre = $variedad->nombre;
                $model->id_planta = $variedad->id_planta;
                $model->planta_nombre = $variedad->planta->nombre;
                $model->id_empresa = $finca;
                $model->fecha = $request->fecha;
            }
            $cosechados = DB::table('desglose_recepcion as dr')
                ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
                ->select(DB::raw('sum(dr.cantidad_mallas * dr.tallos_x_malla) as cantidad'))
                ->where('r.estado', 1)
                ->where('dr.estado', 1)
                ->where('dr.id_variedad', $request->variedad)
                ->where('dr.id_empresa', $finca)
                ->where('r.fecha_ingreso', 'like', $request->fecha . '%')
                ->get()[0]->cantidad;
            $model->cosechados = $cosechados > 0 ? $cosechados : 0;
            $model->save();
        } else {
            $this->actualizar_rango_fechas($request);
        }
        $msg = '<div class="alert alert-success text-center" style="margin-top: 5px">Actualice la búsqueda para visualizar los cambios</div>';
        return [
            'success' => true,
            'mensaje' => $msg,
        ];
    }

    public function actualizar_rango_fechas($request)
    {
        $finca = getFincaActiva();
        $fechas = DB::table('cosecha_diaria')
            ->select('fecha')->distinct()
            ->where('id_empresa', $finca)
            ->where('fecha', '>=', $request->fecha)
            ->where('fecha', '<=', $request->hasta)
            ->orderBy('fecha')->get();

        $variedad = getVariedad($request->variedad);
        foreach ($fechas as $fecha) {
            $model = CosechaDiaria::All()
                ->where('id_variedad', $request->variedad)
                ->where('fecha', $fecha->fecha)
                ->where('id_empresa', $finca)
                ->first();
            if ($model == '') {
                $model = new CosechaDiaria();
                $model->id_variedad = $variedad->id_variedad;
                $model->variedad_nombre = $variedad->nombre;
                $model->id_planta = $variedad->id_planta;
                $model->planta_nombre = $variedad->planta->nombre;
                $model->id_empresa = $finca;
                $model->fecha = $fecha->fecha;
            }
            $cosechados = DB::table('desglose_recepcion as dr')
                ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
                ->select(DB::raw('sum(dr.cantidad_mallas * dr.tallos_x_malla) as cantidad'))
                ->where('r.estado', 1)
                ->where('dr.estado', 1)
                ->where('dr.id_variedad', $variedad->id_variedad)
                ->where('dr.id_empresa', $finca)
                ->where('r.fecha_ingreso', 'like', $fecha->fecha . '%')
                ->get()[0]->cantidad;
            $model->cosechados = $cosechados > 0 ? $cosechados : 0;
            $model->save();
        }
    }

    public function actualizar_all_fechas(Request $request)
    {
        $finca = getFincaActiva();
        jobActualizarCosechaDiaria::dispatch($request->fecha, $request->hasta, $request->variedades, $finca)
            ->onQueue('job');
        $msg = '<div class="alert alert-success text-center" style="margin-top: 5px">Se están calculando los cambios</div>';
        return [
            'success' => true,
            'mensaje' => $msg,
        ];
    }

    public function exportar_reporte(Request $request)
    {
        $spread = new Spreadsheet();
        $this->excel_reporte($spread, $request);

        $fileName = "Cosecha_Diaria.xlsx";
        $writer = new Xlsx($spread);

        //--------------------------- GUARDAR EL EXCEL -----------------------

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        $writer->save('php://output');

        //$writer->save('/var/www/html/Dasalflor/storage/storage/excel/excel_prueba.xlsx');
    }

    public function excel_reporte($spread, $request)
    {
        $finca = getFincaActiva();
        $fechas = DB::table('cosecha_diaria')
            ->select('fecha')->distinct()
            ->where('id_empresa', $finca)
            ->where('fecha', '>=', $request->desde)
            ->where('fecha', '<=', $request->hasta)
            ->where('cosechados', '>', 0)
            ->orderBy('fecha')->get();
        $variedades = DB::table('cosecha_diaria as c')
            ->join('variedad as v', 'v.id_variedad', '=', 'c.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->select('c.id_variedad', 'v.id_planta', 'v.nombre as variedad_nombre', 'p.nombre as planta_nombre')->distinct()
            ->where('c.id_empresa', $finca)
            ->where('c.cosechados', '>', 0)
            ->where('c.fecha', '>=', $request->desde)
            ->where('c.fecha', '<=', $request->hasta);
        if ($request->variedad != 'T')
            $variedades = $variedades->where('c.id_variedad', $request->variedad);
        elseif ($request->planta != '')
            $variedades = $variedades->where('v.id_planta', $request->planta);
        $variedades = $variedades
            ->orderBy('p.nombre')
            ->orderBy('v.nombre')
            ->get();
        $data = [];
        if (count($variedades) > 0) {
            $planta_anterior = $variedades[0]->id_planta;
            $resumen = DB::table('cosecha_diaria')
                ->select(DB::raw('sum(cosechados) as cantidad'), 'fecha')
                ->where('id_empresa', $finca)
                ->where('id_planta', $variedades[0]->id_planta)
                ->where('fecha', '>=', $request->desde)
                ->where('fecha', '<=', $request->hasta);
            if ($request->sector != '')
                $resumen = $resumen->where('id_sector', $request->sector);
            $resumen = $resumen->groupBy('fecha')
                ->orderBy('fecha')
                ->get();
            $plantas_iniciales_resumen = DB::table('ciclo as c')
                ->join('variedad as v', 'v.id_variedad', '=', 'c.id_variedad')
                ->join('modulo as m', 'm.id_modulo', '=', 'c.id_modulo')
                ->select(DB::raw('sum(c.plantas_iniciales) as cant'))
                ->where('c.estado', 1)
                ->where('c.activo', 1)
                ->where('v.id_planta', $variedades[0]->id_planta);
            if ($request->sector != '')
                $plantas_iniciales_resumen = $plantas_iniciales_resumen->where('m.id_sector', $request->sector);
            $plantas_iniciales_resumen = $plantas_iniciales_resumen->Where(function ($q) use ($request) {
                $q->where('c.fecha_fin', '>=', $request->desde)
                    ->where('c.fecha_fin', '<=', $request->hasta)
                    ->orWhere(function ($q) use ($request) {
                        $q->where('c.fecha_inicio', '>=', $request->desde)
                            ->where('c.fecha_inicio', '<=', $request->hasta);
                    })
                    ->orWhere(function ($q) use ($request) {
                        $q->where('c.fecha_inicio', '<', $request->desde)
                            ->where('c.fecha_fin', '>', $request->hasta);
                    });
            })
                ->get()[0]->cant;
            $lista = DB::table('cosecha_diaria')
                ->select(DB::raw('sum(cosechados) as cantidad'), 'fecha')
                ->where('id_empresa', $finca)
                ->where('id_variedad', $variedades[0]->id_variedad)
                ->where('fecha', '>=', $request->desde)
                ->where('fecha', '<=', $request->hasta);
            if ($request->sector != '')
                $lista = $lista->where('id_sector', $request->sector);
            $lista = $lista->groupBy('fecha')
                ->orderBy('fecha')
                ->get();
            $plantas_iniciales = DB::table('ciclo as c')
                ->join('variedad as v', 'v.id_variedad', '=', 'c.id_variedad')
                ->join('modulo as m', 'm.id_modulo', '=', 'c.id_modulo')
                ->select(DB::raw('sum(c.plantas_iniciales) as cant'))
                ->where('c.estado', 1)
                ->where('c.activo', 1)
                ->where('c.id_variedad', $variedades[0]->id_variedad);
            if ($request->sector != '')
                $plantas_iniciales = $plantas_iniciales->where('m.id_sector', $request->sector);
            $plantas_iniciales = $plantas_iniciales->Where(function ($q) use ($request) {
                $q->where('c.fecha_fin', '>=', $request->desde)
                    ->where('c.fecha_fin', '<=', $request->hasta)
                    ->orWhere(function ($q) use ($request) {
                        $q->where('c.fecha_inicio', '>=', $request->desde)
                            ->where('c.fecha_inicio', '<=', $request->hasta);
                    })
                    ->orWhere(function ($q) use ($request) {
                        $q->where('c.fecha_inicio', '<', $request->desde)
                            ->where('c.fecha_fin', '>', $request->hasta);
                    });
            })
                ->get()[0]->cant;
            array_push($data, [
                'tipo' => 'P',  // planta
                'variedad' => $variedades[0],
                'resumen' => $resumen,
                'plantas_iniciales_resumen' => $plantas_iniciales_resumen,
                'lista' => $lista,
                'plantas_iniciales' => $plantas_iniciales,
            ]);
            foreach ($variedades as $pos => $var) {
                if ($pos > 0) {
                    if ($var->id_planta == $planta_anterior) {
                        $lista = DB::table('cosecha_diaria')
                            ->select(DB::raw('sum(cosechados) as cantidad'), 'fecha')
                            ->where('id_empresa', $finca)
                            ->where('id_variedad', $var->id_variedad)
                            ->where('fecha', '>=', $request->desde)
                            ->where('fecha', '<=', $request->hasta);
                        if ($request->sector != '')
                            $lista = $lista->where('id_sector', $request->sector);
                        $lista = $lista->groupBy('fecha')
                            ->orderBy('fecha')
                            ->get();
                        $plantas_iniciales = DB::table('ciclo as c')
                            ->join('variedad as v', 'v.id_variedad', '=', 'c.id_variedad')
                            ->join('modulo as m', 'm.id_modulo', '=', 'c.id_modulo')
                            ->select(DB::raw('sum(c.plantas_iniciales) as cant'))
                            ->where('c.estado', 1)
                            ->where('c.activo', 1)
                            ->where('c.id_variedad', $var->id_variedad);
                        if ($request->sector != '')
                            $plantas_iniciales = $plantas_iniciales->where('m.id_sector', $request->sector);
                        $plantas_iniciales = $plantas_iniciales->Where(function ($q) use ($request) {
                            $q->where('c.fecha_fin', '>=', $request->desde)
                                ->where('c.fecha_fin', '<=', $request->hasta)
                                ->orWhere(function ($q) use ($request) {
                                    $q->where('c.fecha_inicio', '>=', $request->desde)
                                        ->where('c.fecha_inicio', '<=', $request->hasta);
                                })
                                ->orWhere(function ($q) use ($request) {
                                    $q->where('c.fecha_inicio', '<', $request->desde)
                                        ->where('c.fecha_fin', '>', $request->hasta);
                                });
                        })
                            ->get()[0]->cant;
                        array_push($data, [
                            'tipo' => 'V',  // variedad
                            'variedad' => $var,
                            'lista' => $lista,
                            'plantas_iniciales' => $plantas_iniciales,
                        ]);
                    } else {
                        $resumen = DB::table('cosecha_diaria')
                            ->select(DB::raw('sum(cosechados) as cantidad'), 'fecha')
                            ->where('id_empresa', $finca)
                            ->where('id_planta', $var->id_planta)
                            ->where('fecha', '>=', $request->desde)
                            ->where('fecha', '<=', $request->hasta);
                        if ($request->sector != '')
                            $resumen = $resumen->where('id_sector', $request->sector);
                        $resumen = $resumen->groupBy('fecha')
                            ->orderBy('fecha')
                            ->get();
                        $plantas_iniciales_resumen = DB::table('ciclo as c')
                            ->join('variedad as v', 'v.id_variedad', '=', 'c.id_variedad')
                            ->join('modulo as m', 'm.id_modulo', '=', 'c.id_modulo')
                            ->select(DB::raw('sum(c.plantas_iniciales) as cant'))
                            ->where('c.estado', 1)
                            ->where('c.activo', 1)
                            ->where('v.id_planta', $var->id_planta);
                        if ($request->sector != '')
                            $plantas_iniciales_resumen = $plantas_iniciales_resumen->where('m.id_sector', $request->sector);
                        $plantas_iniciales_resumen = $plantas_iniciales_resumen->Where(function ($q) use ($request) {
                            $q->where('c.fecha_fin', '>=', $request->desde)
                                ->where('c.fecha_fin', '<=', $request->hasta)
                                ->orWhere(function ($q) use ($request) {
                                    $q->where('c.fecha_inicio', '>=', $request->desde)
                                        ->where('c.fecha_inicio', '<=', $request->hasta);
                                })
                                ->orWhere(function ($q) use ($request) {
                                    $q->where('c.fecha_inicio', '<', $request->desde)
                                        ->where('c.fecha_fin', '>', $request->hasta);
                                });
                        })
                            ->get()[0]->cant;
                        $lista = DB::table('cosecha_diaria')
                            ->select(DB::raw('sum(cosechados) as cantidad'), 'fecha')
                            ->where('id_empresa', $finca)
                            ->where('id_variedad', $var->id_variedad)
                            ->where('fecha', '>=', $request->desde)
                            ->where('fecha', '<=', $request->hasta);
                        if ($request->sector != '')
                            $lista = $lista->where('id_sector', $request->sector);
                        $lista = $lista->groupBy('fecha')
                            ->orderBy('fecha')
                            ->get();
                        $plantas_iniciales = DB::table('ciclo as c')
                            ->join('variedad as v', 'v.id_variedad', '=', 'c.id_variedad')
                            ->join('modulo as m', 'm.id_modulo', '=', 'c.id_modulo')
                            ->select(DB::raw('sum(c.plantas_iniciales) as cant'))
                            ->where('c.estado', 1)
                            ->where('c.activo', 1)
                            ->where('c.id_variedad', $var->id_variedad);
                        if ($request->sector != '')
                            $plantas_iniciales = $plantas_iniciales->where('m.id_sector', $request->sector);
                        $plantas_iniciales = $plantas_iniciales->Where(function ($q) use ($request) {
                            $q->where('c.fecha_fin', '>=', $request->desde)
                                ->where('c.fecha_fin', '<=', $request->hasta)
                                ->orWhere(function ($q) use ($request) {
                                    $q->where('c.fecha_inicio', '>=', $request->desde)
                                        ->where('c.fecha_inicio', '<=', $request->hasta);
                                })
                                ->orWhere(function ($q) use ($request) {
                                    $q->where('c.fecha_inicio', '<', $request->desde)
                                        ->where('c.fecha_fin', '>', $request->hasta);
                                });
                        })
                            ->get()[0]->cant;
                        array_push($data, [
                            'tipo' => 'P',  // planta
                            'variedad' => $var,
                            'resumen' => $resumen,
                            'plantas_iniciales_resumen' => $plantas_iniciales_resumen,
                            'lista' => $lista,
                            'plantas_iniciales' => $plantas_iniciales,
                        ]);
                        $planta_anterior = $var->id_planta;
                    }
                }
            }
        }

        $columnas = getColumnasExcel();
        $sheet = $spread->getActiveSheet();
        $sheet->setTitle('Cosecha Diaria');

        $row = 1;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Variedad/Tipo');
        setBgToCeldaExcel($sheet, $columnas[$col] . $row, '00b388');
        $total_fechas = [];
        foreach ($fechas as $f) {
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $f->fecha);
            setBgToCeldaExcel($sheet, $columnas[$col] . $row, '5a7177');
            array_push($total_fechas, 0);
        }
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Total');
        setBgToCeldaExcel($sheet, $columnas[$col] . $row, '00b388');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Plantas Iniciales');
        setBgToCeldaExcel($sheet, $columnas[$col] . $row, '00b388');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Productividad');
        setBgToCeldaExcel($sheet, $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row, 'ffffff');

        $total = 0;
        $total_plantas_iniciales = 0;
        foreach ($data as $d) {
            if ($d['tipo'] == 'P') {
                $row++;
                $col = 0;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $d['variedad']->planta_nombre);
                setBgToCeldaExcel($sheet, $columnas[$col] . $row, '8fdbc9');
                $total_fila = 0;
                foreach ($fechas as $pos_f => $f) {
                    $valor = 0;
                    foreach ($d['resumen'] as $pos => $v) {
                        if ($f->fecha == $v->fecha) {
                            $valor = $v->cantidad;
                        }
                    }
                    $total_fila += $valor;
                    $total += $valor;
                    $total_fechas[$pos_f] += $valor;

                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $valor);
                    setBgToCeldaExcel($sheet, $columnas[$col] . $row, '8fdbc9');
                }
                $total_plantas_iniciales += $d['plantas_iniciales_resumen'];
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_fila);
                setBgToCeldaExcel($sheet, $columnas[$col] . $row, '8fdbc9');
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $d['plantas_iniciales_resumen']);
                setBgToCeldaExcel($sheet, $columnas[$col] . $row, '8fdbc9');
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $d['plantas_iniciales_resumen'] > 0 ? round($total_fila / $d['plantas_iniciales_resumen'], 2) : 0);
                setBgToCeldaExcel($sheet, $columnas[$col] . $row, '8fdbc9');

                $row++;
                $col = 0;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $d['variedad']->variedad_nombre);
                setBgToCeldaExcel($sheet, $columnas[$col] . $row, 'e9ecef');
                $total_fila = 0;
                foreach ($fechas as $pos_f => $f) {
                    $valor = 0;
                    foreach ($d['lista'] as $v) {
                        if ($f->fecha == $v->fecha)
                            $valor = $v->cantidad;
                    }
                    $total_fila += $valor;

                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $valor);
                }
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_fila);
                setBgToCeldaExcel($sheet, $columnas[$col] . $row, 'e9ecef');
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $d['plantas_iniciales']);
                setBgToCeldaExcel($sheet, $columnas[$col] . $row, 'e9ecef');
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $d['plantas_iniciales'] > 0 ? round($total_fila / $d['plantas_iniciales'], 2) : 0);
                setBgToCeldaExcel($sheet, $columnas[$col] . $row, 'e9ecef');
            } else {
                $row++;
                $col = 0;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $d['variedad']->variedad_nombre);
                setBgToCeldaExcel($sheet, $columnas[$col] . $row, 'e9ecef');
                $total_fila = 0;
                foreach ($fechas as $pos_f => $f) {
                    $valor = 0;
                    foreach ($d['lista'] as $v) {
                        if ($f->fecha == $v->fecha)
                            $valor = $v->cantidad;
                    }
                    $total_fila += $valor;

                    $col++;
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $valor);
                }
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_fila);
                setBgToCeldaExcel($sheet, $columnas[$col] . $row, 'e9ecef');
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $d['plantas_iniciales']);
                setBgToCeldaExcel($sheet, $columnas[$col] . $row, 'e9ecef');
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $d['plantas_iniciales'] > 0 ? round($total_fila / $d['plantas_iniciales'], 2) : 0);
                setBgToCeldaExcel($sheet, $columnas[$col] . $row, 'e9ecef');
            }
        }
        $row++;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Totales');
        setBgToCeldaExcel($sheet, $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
        foreach ($total_fechas as $v) {
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $v);
            setBgToCeldaExcel($sheet, $columnas[$col] . $row, '5a7177');
            setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
        }
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total);
        setBgToCeldaExcel($sheet, $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_plantas_iniciales);
        setBgToCeldaExcel($sheet, $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_plantas_iniciales > 0 ? round($total / $total_plantas_iniciales, 2) : 0);
        setBgToCeldaExcel($sheet, $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');

        setTextCenterToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);

        setBorderToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);

        for ($i = 0; $i <= $col; $i++)
            $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);
    }
}
