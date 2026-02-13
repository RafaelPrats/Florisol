<?php

namespace yura\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use yura\Jobs\DeleteRecepciones;
use yura\Jobs\jobActualizarCicloByModulo;
use yura\Jobs\jobActualizarCosecha;
use yura\Jobs\jobActualizarFenogramaEjecucion;
use yura\Jobs\jobActualizarSemProyPerenne;
use yura\Jobs\jobResumenAreaSemanal;
use yura\Jobs\jobUpdateResumenTotalSemanalExportcalas;
use yura\Jobs\ProyeccionUpdateSemanal;
use yura\Jobs\ResumenSemanaCosecha;
use yura\Jobs\UpdateTallosCosechadosProyeccion;
use yura\Modelos\Apertura;
use yura\Modelos\Ciclo;
use yura\Modelos\ClasificacionVerde;
use yura\Modelos\ConfiguracionEmpresa;
use yura\Modelos\Cosecha;
use yura\Modelos\CosechaDiaria;
use yura\Modelos\CosechaPersonal;
use yura\Modelos\DesgloseRecepcion;
use yura\Modelos\Modulo;
use yura\Modelos\Planta;
use yura\Modelos\ProyeccionModuloSemana;
use yura\Modelos\Recepcion;
use yura\Modelos\ResumenTotalSemanalExportcalas;
use yura\Modelos\Semana;
use yura\Modelos\Submenu;
use yura\Modelos\Variedad;
use Validator;
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Worksheet;
use PHPExcel_Style_Alignment;
use PHPExcel_Style_Fill;
use PHPExcel_Style_Border;
use PHPExcel_Style_Color;
use yura\Modelos\ClasificacionRamo;
use yura\Modelos\Cosechador;
use Barryvdh\DomPDF\Facade as PDF;
use Picqer\Barcode\BarcodeGeneratorHTML;
use yura\Modelos\DespachoProveedor;

class RecepcionController extends Controller
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
        return view('adminlte.gestion.postcocecha.recepciones.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'plantas' => $plantas,
            'variedades' => $variedades,
        ]);
    }

    public function listar_reporte(Request $request)
    {
        $finca = getFincaActiva();

        $all_plantas = Planta::join('variedad as v', 'v.id_planta', '=', 'planta.id_planta')
            ->select('planta.*')->distinct()
            ->where('planta.estado', 1)
            ->where('v.estado', 1)
            ->where('v.receta', 0);
        if ($request->variedad != 'T')
            $all_plantas = $all_plantas->where('v.id_variedad', $request->variedad);
        elseif ($request->planta != 'T')
            $all_plantas = $all_plantas->where('planta.id_planta', $request->planta);
        $all_plantas = $all_plantas->orderBy('planta.nombre')->get();

        $listado = [];
        foreach ($all_plantas as $p) {
            $variedades = $p->variedades;
            $inventario = DB::table('desglose_recepcion as i')
                ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
                ->select(DB::raw('sum(i.disponibles) as cantidad'))
                ->where('i.disponibles', '>', 0)
                ->where('i.estado', 1)
                ->where('v.id_planta', $p->id_planta)
                ->where('i.id_empresa', $finca)
                ->get()[0]->cantidad;
            $proveedores = DB::table('variedad_proveedor as vp')
                ->join('variedad as v', 'v.id_variedad', '=', 'vp.id_variedad')
                ->join('configuracion_empresa as f', 'f.id_configuracion_empresa', '=', 'vp.id_proveedor')
                ->select('vp.id_proveedor', 'f.nombre')->distinct()
                ->where('v.id_planta', $p->id_planta)
                ->orderBy('f.nombre')
                ->get();

            array_push($listado, [
                'planta' => $p,
                'variedades' => $variedades,
                'inventario' => $inventario,
                'proveedores' => $proveedores,
            ]);
        }

        $longitudes = ClasificacionRamo::where('estado', 1)
            ->orderBy('nombre')
            ->get();

        return view('adminlte.gestion.postcocecha.recepciones.partials.listado', [
            'fecha' => $request->fecha,
            'listado' => $listado,
            'longitudes' => $longitudes,
        ]);
    }

    public function seleccionar_finca_origen(Request $request)
    {
        $variedades = DB::table('variedad_proveedor as vp')
            ->join('variedad as v', 'v.id_variedad', '=', 'vp.id_variedad')
            ->select('vp.id_variedad', 'v.nombre')->distinct()
            ->where('v.id_planta', $request->planta)
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

    public function buscar_inventario(Request $request)
    {
        $finca = getFincaActiva();
        $inventario = DB::table('desglose_recepcion as i')
            ->select(DB::raw('sum(i.disponibles) as cantidad'))
            ->where('i.disponibles', '>', 0)
            ->where('i.estado', 1)
            ->where('i.id_variedad', $request->variedad)
            ->where('i.id_proveedor', $request->proveedor)
            ->where('i.longitud', $request->longitud)
            ->where('i.id_empresa', $finca)
            ->get()[0]->cantidad;
        return [
            'inventario' => $inventario != '' ? $inventario : 0,
        ];
    }

    public function store_recepcion_planta(Request $request)
    {
        DB::beginTransaction();
        try {
            $finca = getFincaActiva();
            $ids = [];
            foreach (json_decode($request->data) as $d) {
                $model = DesgloseRecepcion::where('estado', 1)
                    ->where('factura', $d->factura)
                    ->where('id_variedad', $d->variedad)
                    ->where('id_empresa', $finca)
                    ->where('id_proveedor', $d->proveedor)
                    ->where('fecha', $request->fecha)
                    ->where('tallos_x_malla', $d->tallos_x_malla)
                    ->where('longitud', $d->longitud)
                    ->get()
                    ->first();
                if ($model == '' || 1) {    // OJO se esta creando siempre un nuevo registro
                    $model = new DesgloseRecepcion();
                    $model->factura = $d->factura != '' ? $d->factura : 0;
                    $model->id_variedad = $d->variedad;
                    $model->id_empresa = $finca;
                    $model->id_proveedor = $d->proveedor;
                    $model->fecha = $request->fecha;
                    $model->cantidad_mallas = $d->ramos;
                    $model->tallos_x_malla = $d->tallos_x_malla;
                    $model->disponibles = $d->ramos * $d->tallos_x_malla;
                    $model->longitud = $d->longitud;
                    $model->save();
                    $model->id_desglose_recepcion = DB::table('desglose_recepcion')
                        ->select(DB::raw('max(id_desglose_recepcion) as id'))
                        ->get()[0]->id;
                } else {
                    $model->cantidad_mallas += $d->ramos;
                    $model->disponibles += $d->ramos * $d->tallos_x_malla;
                    $model->save();
                }
                $ids[] = $model->id_desglose_recepcion;
                bitacora('desglose_recepcion', $model->id_desglose_recepcion, 'I', 'Ingreso de recepcion de ' . $d->tallos_x_malla . ' tallos_x_ramo y ' . $d->ramos . ' ramos');
            }
            DB::commit();
            $success = true;
            $msg = 'Se han <strong>GRABADO</strong> los tallos correctamente';
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

    public function listar_inventario_planta(Request $request)
    {
        $finca = getFincaActiva();

        $listado = DB::table('desglose_recepcion as i')
            ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
            ->join('configuracion_empresa as p', 'p.id_configuracion_empresa', '=', 'i.id_proveedor')
            ->select(
                'i.id_desglose_recepcion',
                'i.factura',
                'i.fecha',
                'i.longitud',
                'i.disponibles',
                'i.cantidad_mallas',
                'i.tallos_x_malla',
                'i.id_variedad',
                'i.id_proveedor',
                'v.id_planta',
                'v.nombre as variedad_nombre',
                'p.nombre as proveedor_nombre',
            )
            ->where('i.disponibles', '>', 0)
            ->where('i.estado', 1)
            ->where('v.id_planta', $request->planta)
            ->where('i.id_empresa', $finca)
            ->get();
        return view('adminlte.gestion.postcocecha.recepciones.partials.listar_inventario_planta', [
            'listado' => $listado,
            'id_planta' => $request->planta,
        ]);
    }

    public function update_inventario(Request $request)
    {
        DB::beginTransaction();
        try {
            $model = DesgloseRecepcion::find($request->id);
            $model->factura = $request->factura != '' ? $request->factura : 0;
            $model->cantidad_mallas = $request->ramos;
            $model->tallos_x_malla = $request->tallos_x_malla;
            $model->save();

            DB::commit();
            $success = true;
            $msg = 'Se han <strong>MODIFICADO</strong> los tallos correctamente';
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

    public function delete_inventario(Request $request)
    {
        DB::beginTransaction();
        try {
            $model = DesgloseRecepcion::find($request->id);
            $model->delete();

            DB::commit();
            $success = true;
            $msg = 'Se han <strong>ELIMINADO</strong> los tallos correctamente';
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

    public function view_pdf_inventario(Request $request)
    {
        set_time_limit(600);
        ini_set('memory_limit', '-1');
        $barCode = new BarcodeGeneratorHTML();
        $id = $request->id;
        $model = DesgloseRecepcion::find($id);
        $datos = [
            'model' => $model,
        ];
        return PDF::loadView('adminlte.gestion.postcocecha.recepciones.partials.pdf_etiqueta', compact('datos', 'barCode'))
            ->setPaper(array(0, 0, 340, 250), 'landscape')->stream();
    }

    public function view_all_pdf_inventario(Request $request)
    {
        set_time_limit(600);
        ini_set('memory_limit', '-1');
        $barCode = new BarcodeGeneratorHTML();
        $listado = [];
        foreach (json_decode($request->ids) as $id) {
            $model = DesgloseRecepcion::find($id);
            $listado[] = $model;
        }
        $datos = [
            'listado' => $listado,
        ];
        return PDF::loadView('adminlte.gestion.postcocecha.recepciones.partials.pdf_all_etiqueta', compact('datos', 'barCode'))
            ->setPaper(array(0, 0, 140, 250), 'landscape')->stream();
    }

    public function modal_scan(Request $request)
    {
        return view('adminlte.gestion.postcocecha.recepciones.forms.modal_scan', []);
    }

    public function escanear_codigo(Request $request)
    {
        $barCode = new BarcodeGeneratorHTML();
        $model = DespachoProveedor::find($request->codigo);
        return view('adminlte.gestion.postcocecha.recepciones.forms.escanear_codigo', [
            'model' => $model,
            'barCode' => $barCode,
            'consulta' => $request->consulta,
        ]);
    }

    public function store_despachos(Request $request)
    {
        DB::beginTransaction();
        try {
            $finca = getFincaActiva();
            foreach (json_decode($request->data) as $d) {
                $despacho = DespachoProveedor::find($d->id_model);
                $model = DesgloseRecepcion::where('estado', 1)
                    ->where('factura', 0)
                    ->where('id_variedad', $despacho->id_variedad)
                    ->where('id_empresa', $finca)
                    ->where('id_proveedor', $despacho->id_proveedor)
                    ->where('fecha', $request->fecha)
                    ->where('tallos_x_malla', $despacho->tallos_x_ramo)
                    ->where('longitud', $despacho->longitud)
                    ->where('id_despacho_proveedor', $despacho->id_despacho_proveedor)
                    ->get()
                    ->first();
                if ($model == '') {    // OJO se esta creando siempre un nuevo registro
                    $model = new DesgloseRecepcion();
                    $model->factura = 0;
                    $model->id_variedad = $despacho->id_variedad;
                    $model->id_empresa = $finca;
                    $model->id_proveedor = $despacho->id_proveedor;
                    $model->fecha = $request->fecha;
                    $model->cantidad_mallas = $d->ramos;
                    $model->tallos_x_malla = $despacho->tallos_x_ramo;
                    $model->disponibles = $d->ramos * $despacho->tallos_x_ramo;
                    $model->longitud = $despacho->longitud;
                    $model->id_despacho_proveedor = $despacho->id_despacho_proveedor;
                    $model->save();
                    $model->id_desglose_recepcion = DB::table('desglose_recepcion')
                        ->select(DB::raw('max(id_desglose_recepcion) as id'))
                        ->get()[0]->id;
                } else {
                    $model->cantidad_mallas += $d->ramos;
                    $model->disponibles += $d->ramos * $despacho->tallos_x_ramo;
                    $model->save();
                }
                $ids[] = $model->id_desglose_recepcion;
                bitacora('desglose_recepcion', $model->id_desglose_recepcion, 'I', 'Ingreso de recepcion (ESCANEO) de ' . $despacho->tallos_x_ramo . ' tallos_x_ramo y ' . $d->ramos . ' ramos');

                // DISMINIUR DESPACHOS
                $despacho->disponibles -= $d->ramos;
                $despacho->save();
                bitacora('despacho_proveedor', $despacho->id_despacho_proveedor, 'U', 'Escaneo de despacho en recepcion de ' . $despacho->tallos_x_ramo . ' tallos_x_ramo y ' . $d->ramos . ' ramos');
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
        ];
    }
}
