<?php

namespace yura\Http\Controllers\Comercializacion;

use Illuminate\Http\Request;
use yura\Http\Controllers\Controller;
use yura\Modelos\Submenu;
use DB;

class PedidoController extends Controller
{
    public function inicio(Request $request)
    {
        $clientes = DB::table('cliente as c')
            ->join('detalle_cliente as dc', 'c.id_cliente', '=', 'dc.id_cliente')
            ->select('dc.nombre', 'c.id_cliente')->distinct()
            ->where('dc.estado', 1)
            ->orderBy('dc.nombre', 'asc')
            ->get();

        $fincas = DB::table('configuracion_empresa')
            ->where('proveedor', 0)
            ->orderBy('nombre')
            ->get();

        return view('adminlte.gestion.comercializacion.pedidos.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'clientes' => $clientes,
            'fincas' => $fincas
        ]);
    }

    public function add_pedido(Request $request)
    {
        $clientes = DB::table('cliente as c')
            ->join('detalle_cliente as dc', 'c.id_cliente', '=', 'dc.id_cliente')
            ->select('dc.nombre', 'c.id_cliente')->distinct()
            ->where('dc.estado', 1)
            ->orderBy('dc.nombre', 'asc')
            ->get();

        $fincas = DB::table('configuracion_empresa')
            ->where('proveedor', 0)
            ->get();

        return view('adminlte.gestion.comercializacion.pedidos.forms.add_pedido', [
            'clientes' => $clientes,
            'fincas' => $fincas
        ]);
    }

    public function buscar_inventario(Request $request)
    {
        $fincas = DB::table('configuracion_empresa')
            ->select('id_configuracion_empresa', 'nombre');
        if ($request->finca != '')
            $fincas = $fincas->where('id_configuracion_empresa', $request->finca);
        $fincas = $fincas->get();

        $listado = [];
        foreach ($fincas as $f) {
            $inventarios = DB::table('inventario_frio as i')
                ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
                ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                ->join('configuracion_empresa as fd', 'fd.id_configuracion_empresa', '=', 'i.finca_destino')
                ->join('clasificacion_ramo as c', 'c.id_clasificacion_ramo', '=', 'i.id_clasificacion_ramo')
                ->select(
                    'i.id_inventario_frio',
                    'i.id_variedad',
                    'i.finca_destino',
                    'i.id_clasificacion_ramo',
                    'i.tallos_x_ramo',
                    'i.disponibles',
                    'i.fecha',
                    'fd.nombre as finca_destino_nombre',
                    'p.nombre as planta_nombre',
                    'v.nombre as variedad_nombre',
                    'c.nombre as longitud',
                )
                ->where('i.estado', 1)
                ->where('i.disponibilidad', 1)
                ->where('i.id_empresa', $f->id_configuracion_empresa)
                ->where('v.nombre', 'like', '%' . espacios(mb_strtoupper($request->buscar)) . '%')
                ->orderBy('i.finca_destino')
                ->orderBy('p.nombre')
                ->orderBy('v.nombre')
                ->orderBy('i.fecha')
                ->get();
            if (count($inventarios) > 0)
                $listado[] = [
                    'finca' => $f,
                    'inventarios' => $inventarios,
                ];
        }
        return view('adminlte.gestion.comercializacion.pedidos.forms._buscar_inventario', [
            'listado' => $listado,
        ]);
    }
}
