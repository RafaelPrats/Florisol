<?php

namespace yura\Http\Controllers\Postcosecha;

use DB;
use Illuminate\Http\Request;
use Picqer\Barcode\BarcodeGeneratorHTML;
use yura\Http\Controllers\Controller;
use yura\Modelos\ClasificacionRamo;
use yura\Modelos\DespachoProveedor;
use yura\Modelos\Submenu;
use yura\Modelos\Variedad;
use Barryvdh\DomPDF\Facade as PDF;

class DespachoProveedorController extends Controller
{
    public function inicio(Request $request)
    {
        $variedades = Variedad::where('estado', 1)
            ->where('receta', 0)
            ->orderBy('nombre')
            ->get();
        $usuarios = DB::table('despacho_proveedor as d')
            ->join('usuario as u', 'u.id_usuario', '=', 'd.id_usuario')
            ->select('d.id_usuario', 'u.nombre_completo')->distinct()
            ->orderBy('u.nombre_completo')
            ->get();
        return view('adminlte.gestion.postcocecha.despacho_proveedor.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'variedades' => $variedades,
            'usuarios' => $usuarios,
        ]);
    }

    public function listar_reporte(Request $request)
    {
        $listado = DespachoProveedor::join('variedad as v', 'v.id_variedad', '=', 'despacho_proveedor.id_variedad')
            ->select('despacho_proveedor.*')->distinct()
            ->where('despacho_proveedor.fecha_ingreso', $request->fecha);
        if ($request->variedad != 'T')
            $listado = $listado->where('despacho_proveedor.id_variedad', $request->variedad);
        if ($request->usuario != 'T')
            $listado = $listado->where('despacho_proveedor.id_usuario', $request->usuario);
        $listado = $listado->orderBy('v.nombre')
            ->get();
        return view('adminlte.gestion.postcocecha.despacho_proveedor.partials.listado', [
            'listado' => $listado,
        ]);
    }

    public function add_despacho(Request $request)
    {
        $proveedores = DB::table('variedad_proveedor as vp')
            ->join('variedad as v', 'v.id_variedad', '=', 'vp.id_variedad')
            ->join('configuracion_empresa as f', 'f.id_configuracion_empresa', '=', 'vp.id_proveedor')
            ->select('vp.id_proveedor', 'f.nombre')->distinct()
            ->orderBy('f.nombre')
            ->get();
        $longitudes = ClasificacionRamo::where('estado', 1)
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.postcocecha.despacho_proveedor.forms.add_despacho', [
            'proveedores' => $proveedores,
            'longitudes' => $longitudes,
        ]);
    }

    public function seleccionar_proveedor(Request $request)
    {
        $variedades = DB::table('variedad_proveedor as vp')
            ->join('variedad as v', 'v.id_variedad', '=', 'vp.id_variedad')
            ->select('vp.id_variedad', 'v.nombre')->distinct()
            ->where('vp.id_proveedor', $request->proveedor)
            ->orderBy('v.nombre')
            ->get();
        $options = '';
        foreach ($variedades as $v) {
            $options .= '<option value="' . $v->id_variedad . '">' . $v->nombre . '</option>';
        }
        return [
            'variedades' => $options,
        ];
    }

    public function store_despacho(Request $request)
    {
        DB::beginTransaction();
        try {
            $ids = [];
            foreach (json_decode($request->data) as $d) {
                $model = DespachoProveedor::where('fecha_ingreso', $request->fecha)
                    ->where('id_proveedor', $d->proveedor)
                    ->where('id_variedad', $d->variedad)
                    ->where('longitud', $d->longitud)
                    ->where('tallos_x_ramo', $d->tallos_x_ramo)
                    ->get()
                    ->first();
                if ($model == '') {
                    $model = new DespachoProveedor();
                    $model->id_proveedor = $d->proveedor;
                    $model->id_variedad = $d->variedad;
                    $model->cantidad = $d->ramos;
                    $model->disponibles = $d->ramos;
                    $model->fecha_ingreso = $request->fecha;
                    $model->tallos_x_ramo = $d->tallos_x_ramo;
                    $model->longitud = $d->longitud;
                    $model->id_usuario = session('id_usuario');
                    $model->save();
                    $model->id_despacho_proveedor = DB::table('despacho_proveedor')
                        ->select(DB::raw('max(id_despacho_proveedor) as id'))
                        ->get()[0]->id;

                    bitacora('despacho_proveedor', $model->id_despacho_proveedor, 'I', 'CREACION de un nuevo despacho_proveedor');

                    $ids[] = [
                        'id' => $model->id_despacho_proveedor,
                        'cantidad' => $d->ramos,
                    ];
                } else {
                    $model->cantidad += $d->ramos;
                    $model->disponibles += $d->ramos;
                    $model->save();

                    $ids[] = [
                        'id' => $model->id_despacho_proveedor,
                        'cantidad' => $d->ramos,
                    ];
                }
            }

            DB::commit();
            $success = true;
            $msg = 'Se han <strong>GRABADO</strong> los despachos correctamente';
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
            'ids' => $ids,
        ];
    }

    public function imprimir_all(Request $request)
    {
        $barCode = new BarcodeGeneratorHTML();
        $listado = [];
        foreach (json_decode($request->data) as $d) {
            $model = DespachoProveedor::find($d->id);
            $listado[] = [
                'model' => $model,
                'cantidad' => $d->cantidad,
            ];
        }
        return PDF::loadView(
            'adminlte.gestion.postcocecha.despacho_proveedor.partials.pdf_imprimir_all',
            compact('listado', 'barCode')
        )
            ->setPaper(array(0, 0, 140, 250), 'landscape')
            ->stream();
    }

    public function imprimir_etiqueta(Request $request)
    {
        $barCode = new BarcodeGeneratorHTML();
        $model = DespachoProveedor::find($request->id);
        return PDF::loadView(
            'adminlte.gestion.postcocecha.despacho_proveedor.partials.pdf_imprimir_etiqueta',
            compact('model', 'barCode')
        )
            ->setPaper(array(0, 0, 140, 250), 'landscape')
            ->stream();
    }

    public function delete_model(Request $request)
    {
        DB::beginTransaction();
        try {
            $model = DespachoProveedor::find($request->id);
            $model->delete();

            DB::commit();
            $success = true;
            $msg = 'Se ha <strong>ELIMINADO</strong> el pespacho correctamente';
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
