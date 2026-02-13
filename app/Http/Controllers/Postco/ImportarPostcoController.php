<?php

namespace yura\Http\Controllers\Postco;

use Illuminate\Http\Request;
use yura\Http\Controllers\Controller;
use yura\Modelos\Submenu;
use yura\Modelos\Variedad;
use yura\Modelos\Postco;
use yura\Modelos\DistribucionPostco;
use yura\Modelos\DetalleCliente;
use yura\Modelos\PostcoClientes;
use Validator;
use Storage as Almacenamiento;
use \PhpOffice\PhpSpreadsheet\IOFactory as IOFactory;
use Illuminate\Support\Facades\DB;

class ImportarPostcoController extends Controller
{
    public function inicio(Request $request)
    {
        $variedades = Variedad::where('estado', 1)
            ->where('receta', 1)
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.postco.importar_postco.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'variedades' => $variedades,
        ]);
    }

    public function listar_reporte(Request $request)
    {
        $listado = Postco::where('fecha', $request->fecha);
        if ($request->variedad != 'T')
            $listado = $listado->where('id_variedad', $request->variedad);
        $listado = $listado->get();
        return view('adminlte.gestion.postco.importar_postco.partials.listado', [
            'listado' => $listado
        ]);
    }

    public function modal_importar(Request $request)
    {
        return view('adminlte.gestion.postco.importar_postco.forms.modal_importar', []);
    }

    public function post_importar_pedidos(Request $request)
    {
        ini_set('max_execution_time', env('MAX_EXECUTION_TIME'));
        $valida = Validator::make($request->all(), [
            'file_pedidos' => 'required',
        ]);
        $msg = '<div class="alert alert-success text-center">Se ha importado el archivo. Revise su contenido antes de grabar.</div>';
        $success = true;
        if (!$valida->fails()) {
            try {
                $archivo = $request->file_pedidos;
                $extension = $archivo->getClientOriginalExtension();
                $nombre_archivo = "upload_pedidos." . $extension;
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

    public function get_importar_pedidos(Request $request)
    {
        try {
            $url = public_path('storage/file_loads/upload_pedidos.xlsx');
            $document = IOFactory::load($url);
            $sheet = $document->getActiveSheet()->toArray(null, true, true, true);
            $listado = [];
            $fallos = false;
            $cajas_anterior = $sheet[8]['J'];
            foreach ($sheet as $pos => $row) {
                if ($pos > 7 && $row['B'] != '') {
                    $variedad = Variedad::where('nombre', espacios(mb_strtoupper($row['L'])))
                        ->where('siglas', espacios(mb_strtoupper($row['M'])))
                        ->where('id_planta', 128)
                        ->where('estado', 1)
                        ->get()
                        ->first();
                    $cliente = DetalleCliente::where('nombre', espacios(mb_strtoupper($row['D'])))
                        ->where('estado', 1)
                        ->get()
                        ->first();
                    if ($variedad == '' || $cliente == '') {
                        $fallos = true;
                    }

                    $cajas_anterior = $row['J'] != '' ? $row['J'] : $cajas_anterior;
                    $pos_en_listado = -1;
                    foreach ($listado as $pos => $r) {
                        if (espacios(mb_strtoupper($r['row']['M'])) == espacios(mb_strtoupper($row['M'])) && $r['longitud'] == $row['N']) {
                            $pos_en_listado = $pos;
                        }
                    }

                    $ramos = $cajas_anterior * $row['P'];

                    $cliente = [
                        'id_cliente' => $cliente != '' ? $cliente->id_cliente : '',
                        'nombre' => $cliente != '' ? $cliente->nombre : espacios(mb_strtoupper($row['D'])),
                        'ramos' => $ramos
                    ];

                    if ($pos_en_listado != -1) {
                        $listado[$pos_en_listado]['ramos'] += $ramos;

                        $pos_clientes = -1;
                        foreach ($listado[$pos_en_listado]['clientes'] as $pos_c => $c) {
                            if ($c['nombre'] == espacios(mb_strtoupper($row['D']))) {
                                $pos_clientes = $pos_c;
                            }
                        }
                        if ($pos_clientes != -1) {
                            $listado[$pos_en_listado]['clientes'][$pos_clientes]['ramos'] += $ramos;
                        } else {
                            $listado[$pos_en_listado]['clientes'][] = $cliente;
                        }
                    } else {
                        $listado[] = [
                            'row' => $row,
                            'longitud' => $row['N'],
                            'ramos' => $ramos,
                            'variedad' => $variedad,
                            'clientes' => [$cliente]
                        ];
                    }
                }
            }
        } catch (\Exception $e) {
            return '<div class="alert alert-danger text-center">' .
                '<p> Ha ocurrido un problema con el contenido del archivo. Pongase en contacto con el administrador del sistema</p>' .
                '<p>' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine() . '</p>'
                . '</div>';
        }
        return view('adminlte.gestion.postco.importar_postco.forms.importar_proyectos', [
            'listado' => $listado,
            'fallos' => $fallos,
        ]);
    }

    public function store_postco(Request $request)
    {
        DB::beginTransaction();
        try {
            $ids_postco = [];
            foreach (json_decode($request->data) as $data) {
                $postco = Postco::where('id_variedad', $data->id_variedad)
                    ->where('fecha', $request->fecha)
                    ->where('longitud', $data->longitud)
                    ->get()
                    ->first();
                if ($postco == '') {
                    $postco = new Postco();
                    $postco->id_variedad = $data->id_variedad;
                    $postco->fecha = $request->fecha;
                    $postco->longitud = $data->longitud;
                    $postco->ramos = $data->ramos;
                    $postco->save();
                    $postco->id_postco = DB::table('postco')
                        ->select(DB::raw('max(id_postco) as id'))
                        ->get()[0]->id;
                    bitacora('POSTCO', $postco->id_postco, 'I', 'IMPORTAR NUEVO "PEDIDO"');

                    $detalles_receta = DB::table('detalle_receta')
                        ->where('id_variedad', $data->id_variedad)
                        ->where('defecto', 1)
                        ->get();

                    foreach ($detalles_receta as $det) {
                        $dist = new DistribucionPostco();
                        $dist->id_postco = $postco->id_postco;
                        $dist->id_item = $det->id_item;
                        $dist->unidades = $det->unidades;
                        $dist->longitud = $det->longitud;
                        $dist->save();
                    }
                } else {
                    $postco->ramos = $data->ramos;
                    $postco->save();
                    bitacora('POSTCO', $postco->id_postco, 'U', 'IMPORTAR "PEDIDO" EXISTENTE');
                }

                DB::select('delete from postco_clientes where id_postco = ' . $postco->id_postco);
                foreach (json_decode($data->clientes) as $cliente) {
                    $p_cliente = new PostcoClientes();
                    $p_cliente->id_postco = $postco->id_postco;
                    $p_cliente->id_cliente = $cliente->id;
                    $p_cliente->cantidad = $cliente->ramos;
                    $p_cliente->save();
                }

                $ids_postco[] = $postco->id_postco;
            }
            Postco::where('fecha', $request->fecha)
                ->whereNotIn('id_postco', $ids_postco)
                ->delete();

            DB::commit();
            $success = true;
            $msg = 'Se han <strong>IMPORTADO</strong> las recetas correctamente';
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

    public function delete_postco(Request $request)
    {
        DB::beginTransaction();
        try {
            $model = Postco::find($request->id);
            $texto = $model->id_variedad . ' ' . $model->longitud . 'cm' . '; fecha = ' . $model->fecha;
            $model->delete();

            bitacora('POSTCO', $request->id, 'D', 'ELIMINAR MANUALMENTE: ' . $texto);
            DB::commit();
            $success = true;
            $msg = 'Se ha <strong>ELIMINADO</strong> la receta correctamente';
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
