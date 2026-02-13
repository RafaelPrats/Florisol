<?php

namespace yura\Http\Controllers\Bodega;

use DB;
use Illuminate\Http\Request;
use yura\Http\Controllers\Controller;
use yura\Modelos\IngresoBodega;
use yura\Modelos\Producto;
use yura\Modelos\SalidaBodega;
use yura\Modelos\Submenu;

class MovimientosBodegaController extends Controller
{
    public function inicio(Request $request)
    {
        return view('adminlte.gestion.bodega.movimientos_bodega.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
        ]);
    }

    public function listar_reporte(Request $request)
    {
        $finca = getFincaActiva();
        $listado = Producto::Where(function ($q) use ($request) {
            $q->Where('nombre', 'like', '%' . mb_strtoupper($request->busqueda) . '%')
                ->orWhere('codigo', 'like', '%' . mb_strtoupper($request->busqueda) . '%');
        })->where('id_empresa', $finca)
            ->orderBy('nombre')
            ->get();

        return view('adminlte.gestion.bodega.movimientos_bodega.partials.listado', [
            'listado' => $listado,
        ]);
    }

    public function add_ingresos(Request $request)
    {
        $finca = getFincaActiva();
        $listado = Producto::where('id_empresa', $finca)
            ->orderBy('nombre')
            ->get();

        return view('adminlte.gestion.bodega.movimientos_bodega.forms.add_ingresos', [
            'listado' => $listado,
        ]);
    }

    public function store_ingresos(Request $request)
    {
        try {
            DB::beginTransaction();
            foreach (json_decode($request->data) as $d) {
                $model = Producto::find($d->id_prod);
                $model->disponibles += $d->unidades;
                $model->save();
                bitacora('producto', $model->id_producto, 'U', 'INGRESO A BODEGA de ' . $d->unidades . ' UNIDADES');

                /* INGRESO_BODEGA */
                $ingreso = new IngresoBodega();
                $ingreso->id_producto = $d->id_prod;
                $ingreso->fecha = $request->fecha;
                $ingreso->cantidad = $d->unidades;
                $ingreso->precio_costo = $d->precio_costo;
                $ingreso->save();
                $ingreso = IngresoBodega::All()->last();
                bitacora('ingreso_bodega', $ingreso->id_ingreso_bodega, 'I', 'INGRESO A BODEGA de ' . $d->unidades . ' UNIDADES de id_producto=' . $d->id_prod);
            }

            DB::commit();
            $success = true;
            $msg = 'Se han <strong>GRABADO</strong> los ingresos correctamente';
        } catch (\Exception $e) {
            DB::rollBack();
            $success = false;
            $msg = '<div class="alert alert-danger text-center">' .
                '<p> Ha ocurrido un problema al guardar la informacion al sistema</p>' .
                '<p>' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine() . '</p>'
                . '</div>';
        }

        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }

    public function add_salidas(Request $request)
    {
        $finca = getFincaActiva();
        $listado = Producto::where('id_empresa', $finca)
            ->orderBy('nombre')
            ->get();

        return view('adminlte.gestion.bodega.movimientos_bodega.forms.add_salidas', [
            'listado' => $listado,
        ]);
    }

    public function store_salidas(Request $request)
    {
        try {
            DB::beginTransaction();
            foreach (json_decode($request->data) as $d) {
                $model = Producto::find($d->id_prod);
                if ($model->disponibles >= $d->unidades) {
                    $model->disponibles -= $d->unidades;
                    $model->save();
                    bitacora('producto', $model->id_producto, 'U', 'SALIDA DE BODEGA de ' . $d->unidades . ' UNIDADES');

                    /* SALIDA_BODEGA */
                    $salida = new SalidaBodega();
                    $salida->id_producto = $d->id_prod;
                    $salida->fecha = $request->fecha;
                    $salida->cantidad = $d->unidades;
                    $salida->save();
                    $salida = SalidaBodega::All()->last();
                    bitacora('salida_bodega', $salida->id_salida_bodega, 'I', 'SALIDA A BODEGA de ' . $d->unidades . ' UNIDADES de id_producto=' . $d->id_prod);
                } else {
                    DB::rollBack();
                    $success = false;
                    $msg = '<div class="alert alert-danger text-center">La cantidad a sacar del producto: <b>' . $model->nombre . '</b> es <b>MAYOR</b> que el <strong>DISPONIBLE</strong> en bodega</div>';

                    return [
                        'success' => $success,
                        'mensaje' => $msg,
                    ];
                }
            }

            DB::commit();
            $success = true;
            $msg = 'Se han <strong>GRABADO</strong> los ingresos correctamente';
        } catch (\Exception $e) {
            DB::rollBack();
            $success = false;
            $msg = '<div class="alert alert-danger text-center">' .
                '<p> Ha ocurrido un problema al guardar la informacion al sistema</p>' .
                '<p>' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine() . '</p>'
                . '</div>';
        }

        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }
}
