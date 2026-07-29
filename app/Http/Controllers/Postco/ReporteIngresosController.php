<?php

namespace yura\Http\Controllers\Postco;

use DB;
use Illuminate\Http\Request;
use yura\Http\Controllers\Controller;
use yura\Modelos\Planta;
use yura\Modelos\Submenu;

class ReporteIngresosController extends Controller
{
    public function inicio(Request $request)
    {
        $finca = getFincaActiva();
        $plantas = Planta::where('estado', 1)
            ->where('id_empresa', $finca)
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.postco.reporte_ingresos.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'plantas' => $plantas,
        ]);
    }

    public function listar_reporte(Request $request)
    {
        $finca = getFincaActiva();
        $listado_documentos = DB::table('ingreso_recepcion as i')
            ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->join('api_store_cajas as api', 'api.id_api_store_cajas', '=', 'i.id_api_store_cajas')
            ->select(
                'i.id_api_store_cajas',
                'i.id_variedad',
                'v.nombre as var_nombre',
                'p.nombre as pta_nombre',
                'api.documento',
                'api.fecha',
                'i.tallos_x_ramo',
                'i.ramos',
                'i.longitud',
                'i.bodega',
                'i.id_ingreso_recepcion'
            )->distinct()
            ->where('i.id_empresa', $finca)
            ->where('api.fecha', '>=', $request->desde)
            ->where('api.fecha', '<=', $request->hasta);
        if ($request->documento != '')
            $listado_documentos = $listado_documentos->where('api.documento', $request->documento);
        if ($request->bodega != 'T')
            $listado_documentos = $listado_documentos->where('i.bodega', $request->bodega);
        if ($request->planta != '')
            $listado_documentos = $listado_documentos->where('v.id_planta', $request->planta);
        if ($request->variedad != '')
            $listado_documentos = $listado_documentos->where('i.id_variedad', $request->variedad);
        $listado_documentos = $listado_documentos->orderBy('api.fecha')
            ->orderBy('api.documento')
            ->get()
            ->groupBy('documento')
            ->map(function ($items, $documento) {
                return [
                    'documento' => $documento,
                    'fecha' => $items->first()->fecha,
                    'id_api_store_cajas' => $items->first()->id_api_store_cajas,
                    'detalles' => $items->values(),
                ];
            })
            ->values();

        $listado_compras = DB::table('ingreso_recepcion as i')
            ->leftJoin('configuracion_empresa as prov', 'prov.id_configuracion_empresa', '=', 'i.id_proveedor')
            ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->select(
                'i.id_variedad',
                'v.nombre as var_nombre',
                'p.nombre as pta_nombre',
                'i.tallos_x_ramo',
                'i.ramos',
                'i.longitud',
                'i.bodega',
                'i.factura',
                'i.packing',
                'i.bodega',
                'i.fecha',
                'i.id_ingreso_recepcion',
                'prov.nombre as proveedor_nombre',
            )->distinct()
            ->whereNotNull('i.packing')
            ->where('i.id_empresa', $finca)
            ->where('i.fecha', '>=', $request->desde)
            ->where('i.fecha', '<=', $request->hasta);
        if ($request->documento != '')
            $listado_compras = $listado_compras->where('i.factura', $request->documento);
        if ($request->bodega != 'T')
            $listado_compras = $listado_compras->where('i.bodega', $request->bodega);
        if ($request->planta != '')
            $listado_compras = $listado_compras->where('v.id_planta', $request->planta);
        if ($request->variedad != '')
            $listado_compras = $listado_compras->where('i.id_variedad', $request->variedad);
        $listado_compras = $listado_compras->orderBy('i.fecha')
            ->orderBy('i.packing')
            ->get()
            ->groupBy('packing')
            ->map(function ($items, $packing) {
                return [
                    'packing' => $packing,
                    'fecha' => $items->first()->fecha,
                    'factura' => $items->first()->factura,
                    'proveedor_nombre' => $items->first()->proveedor_nombre,
                    'detalles' => $items->values(),
                ];
            })
            ->values();

        return view('adminlte.gestion.postco.reporte_ingresos.partials.listado', [
            'listado_documentos' => $listado_documentos,
            'listado_compras' => $listado_compras,
        ]);
    }
}
