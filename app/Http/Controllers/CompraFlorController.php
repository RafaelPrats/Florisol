<?php

namespace yura\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use yura\Modelos\ClasificacionRamo;
use yura\Modelos\DesgloseCompraFlor;
use yura\Modelos\Planta;
use yura\Modelos\Submenu;
use yura\Modelos\Variedad;
use Validator;
use Storage as Almacenamiento;
use \PhpOffice\PhpSpreadsheet\IOFactory as IOFactory;

class CompraFlorController extends Controller
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
        return view('adminlte.gestion.postcocecha.compra_flor.inicio', [
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
            $inventario = DB::table('desglose_compra_flor as i')
                ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
                ->select(DB::raw('sum(i.cantidad_mallas * i.tallos_x_malla) as cantidad'))
                ->where('v.id_planta', $p->id_planta)
                ->where('i.fecha', '>', hoy())
                ->where('i.estado', 1)
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

        return view('adminlte.gestion.postcocecha.compra_flor.partials.listado', [
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
        $inventario = DB::table('desglose_compra_flor as i')
            ->select(DB::raw('sum(i.cantidad_mallas * i.tallos_x_malla) as cantidad'))
            ->where('i.id_variedad', $request->variedad)
            ->where('i.estado', 1)
            ->where('i.id_proveedor', $request->proveedor)
            ->where('i.longitud', $request->longitud)
            ->where('i.fecha', '>', hoy())
            ->where('i.id_empresa', $finca)
            ->get()[0]->cantidad;
        return [
            'inventario' => $inventario != '' ? $inventario : 0,
        ];
    }

    public function store_compra_flor_planta(Request $request)
    {
        DB::beginTransaction();
        try {
            $finca = getFincaActiva();
            foreach (json_decode($request->data) as $d) {
                $model = DesgloseCompraFlor::All()
                    ->where('id_variedad', $d->variedad)
                    ->where('id_empresa', $finca)
                    ->where('id_proveedor', $d->proveedor)
                    ->where('fecha', $request->fecha)
                    ->where('estado', 1)
                    ->where('longitud', $d->longitud)
                    ->first();
                if ($model == '') {
                    $model = new DesgloseCompraFlor();
                    $model->id_variedad = $d->variedad;
                    $model->id_empresa = $finca;
                    $model->id_proveedor = $d->proveedor;
                    $model->fecha = $request->fecha;
                    $model->cantidad_mallas = 1;
                    $model->tallos_x_malla = $d->tallos_x_malla;
                    $model->longitud = $d->longitud;
                    $model->save();
                } else {
                    $model->tallos_x_malla += $d->tallos_x_malla;
                    $model->save();
                }
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
        ];
    }

    public function listar_inventario_planta(Request $request)
    {
        $finca = getFincaActiva();

        $listado = DB::table('desglose_compra_flor as i')
            ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
            ->join('configuracion_empresa as p', 'p.id_configuracion_empresa', '=', 'i.id_proveedor')
            ->select(
                'i.id_desglose_compra_flor',
                'i.fecha',
                'i.longitud',
                'i.cantidad_mallas',
                'i.tallos_x_malla',
                'i.id_variedad',
                'i.id_proveedor',
                'v.id_planta',
                'v.nombre as variedad_nombre',
                'p.nombre as proveedor_nombre',
            )
            ->where('i.estado', 1)
            ->where('i.fecha', '>', hoy())
            ->where('v.id_planta', $request->planta)
            ->where('i.id_empresa', $finca)
            ->get();
        return view('adminlte.gestion.postcocecha.compra_flor.partials.listar_inventario_planta', [
            'listado' => $listado,
            'id_planta' => $request->planta,
        ]);
    }

    public function update_inventario(Request $request)
    {
        DB::beginTransaction();
        try {
            $model = DesgloseCompraFlor::find($request->id);
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
            $model = DesgloseCompraFlor::find($request->id);
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

    public function importar_compras(Request $request)
    {
        return view('adminlte.gestion.postcocecha.compra_flor.forms.importar_compras', []);
    }

    public function post_importar_compras(Request $request)
    {
        ini_set('max_execution_time', env('MAX_EXECUTION_TIME'));
        $valida = Validator::make($request->all(), [
            'file_compras' => 'required',
        ]);
        $msg = '<div class="alert alert-success text-center">Se ha importado el archivo. Revise su contenido antes de grabar.</div>';
        $success = true;
        if (!$valida->fails()) {
            try {
                $archivo = $request->file_compras;
                $extension = $archivo->getClientOriginalExtension();
                $nombre_archivo = "upload_compras." . $extension;
                $r1 = Almacenamiento::disk('file_loads')->put($nombre_archivo, \File::get($archivo));
            } catch (\Exception $e) {
                if (strpos($e->getMessage(), 'DOMDocument::loadHTML(): Invalid char in CDATA') !== false)
                    $mensaje_error = 'Problema con el archivo excel';
                else
                    $mensaje_error = $e->getMessage();
                return [
                    'mensaje' => '<div class="alert alert-danger text-center">' .
                        '<p>¡Ha ocurrido un problema al subir el archivo, contacte al administrador del sistema!</p>' .
                        '<legend style="font-size: 0.9em; color: white; margin-bottom: 2px">mensaje de error</legend>' .
                        $mensaje_error .
                        '</div>',
                    'success' => false
                ];
            }
        } else {
            $errores = '';
            foreach ($valida->errors()->all() as $mi_error) {
                if ($errores == '') {
                    $errores = '<li>' . $mi_error . '</li>';
                } else {
                    $errores .= '<li>' . $mi_error . '</li>';
                }
            }
            $success = false;
            $msg = '<div class="alert alert-danger">' .
                '<p class="text-center">¡Por favor corrija los siguientes errores!</p>' .
                '<ul>' .
                $errores .
                '</ul>' .
                '</div>';
        }
        return [
            'mensaje' => $msg,
            'success' => $success,
        ];
    }

    public function get_importar_compras(Request $request)
    {
        try {
            $url = public_path('storage/file_loads/upload_compras.xlsx');
            $document = IOFactory::load($url);
            $sheet = $document->getActiveSheet()->toArray(null, true, true, true);
            $columnas = getColumnasExcel();
            $listado = [];
            $fechas = [];
            foreach ($sheet as $pos => $row) {
                if ($pos == 1) {
                    for ($i = 2; $i < count($columnas); $i++) {
                        if (isset($row[$columnas[$i]])) {
                            $fechas[] = $row[$columnas[$i]];
                        } else
                            break;
                    }
                } else {
                    $variedad = Variedad::join('planta as p', 'p.id_planta', 'variedad.id_planta')
                        ->select('variedad.*')->distinct()
                        ->where('p.nombre', $row['A'])
                        ->where('variedad.nombre', $row['B'])
                        ->first();
                    if ($variedad != '') {
                        $valores = [];
                        for ($i = 0; $i < count($fechas); $i++) {
                            $valores[] = $row[$columnas[$i + 2]] < 0 ? $row[$columnas[$i + 2]] : 0;
                        }
                        $listado[] = [
                            'variedad' => $variedad,
                            'valores' => $valores,
                        ];
                    } else {
                        return '<div class="alert alert-danger text-center">' .
                            '<p> En la fila: #' . $pos . ' <b>NO</b> se ha encontrado en el sistema la flor ' . $row['A'] . ' ' . $row['B'] . '.</p>' .
                            '</div>';
                    }
                }
            }
        } catch (\Exception $e) {
            return '<div class="alert alert-danger text-center">' .
                '<p> Ha ocurrido un problema con el contenido del archivo. Pongase en contacto con el administrador del sistema</p>' .
                '<p>' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine() . '</p>'
                . '</div>';
        }
        return view('adminlte.gestion.postcocecha.compra_flor.forms.get_importar_compras', [
            'listado' => $listado,
            'fechas' => $fechas,
        ]);
    }

    public function store_import_compras(Request $request)
    {
        DB::beginTransaction();
        try {
            $finca = getFincaActiva();
            foreach (json_decode($request->data) as $d) {
                foreach (json_decode($request->fechas) as $pos_f => $fecha) {
                    if ($d->necesidades[$pos_f] != '' && $d->longitud != '') {
                        $model = DesgloseCompraFlor::All()
                            ->where('id_variedad', $d->variedad)
                            ->where('id_empresa', $finca)
                            ->where('id_proveedor', -1)
                            ->where('fecha', $fecha)
                            ->where('estado', 1)
                            ->where('longitud', $d->longitud)
                            ->first();
                        if ($model == '') {
                            $model = new DesgloseCompraFlor();
                            $model->id_variedad = $d->variedad;
                            $model->id_empresa = $finca;
                            $model->id_proveedor = -1;
                            $model->fecha = $fecha;
                            $model->cantidad_mallas = 1;
                            $model->tallos_x_malla = $d->necesidades[$pos_f];
                            $model->longitud = $d->longitud;
                            $model->save();
                        } else {
                            $model->tallos_x_malla += $d->necesidades[$pos_f];
                            $model->save();
                        }
                    }
                }
            }

            DB::commit();
            $success = true;
            $msg = 'Se ha <strong>GRABADO</strong> el archivo correctamente';
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
