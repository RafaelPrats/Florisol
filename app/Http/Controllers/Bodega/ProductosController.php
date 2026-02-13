<?php

namespace yura\Http\Controllers\Bodega;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use yura\Http\Controllers\Controller;
use yura\Modelos\Producto;
use yura\Modelos\Submenu;
use Validator;
use yura\Modelos\CategoriaProducto;
use yura\Modelos\Proveedor;
use Storage as Almacenamiento;
use \PhpOffice\PhpSpreadsheet\IOFactory as IOFactory;

class ProductosController extends Controller
{
    public function inicio(Request $request)
    {
        $categorias = CategoriaProducto::where('estado', 1)
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.bodega.productos.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'categorias' => $categorias
        ]);
    }

    public function listar_reporte(Request $request)
    {
        $finca = getFincaActiva();
        $listado = Producto::Where(function ($q) use ($request) {
            $q->Where('nombre', 'like', '%' . mb_strtoupper($request->busqueda) . '%')
                ->orWhere('codigo', 'like', '%' . mb_strtoupper($request->busqueda) . '%');
        })->where('id_empresa', $finca);
        if ($request->categoria != 'T')
            $listado = $listado->where('id_categoria_producto', $request->categoria);
        $listado = $listado->orderBy('nombre')
            ->get();

        $categorias = CategoriaProducto::where('estado', 1)
            ->orderBy('nombre')
            ->get();
        $proveedores = Proveedor::where('estado', 1)
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.bodega.productos.partials.listado', [
            'listado' => $listado,
            'categorias' => $categorias,
            'proveedores' => $proveedores,
        ]);
    }

    public function store_producto(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'nombre' => 'required|max:500|unique:producto',
            'codigo' => 'required|max:500|unique:producto',
            'unidad_medida' => 'required',
            'stock_minimo' => 'required',
            'disponibles' => 'required',
            'conversion' => 'required',
        ], [
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.unique' => 'El nombre ya existe',
            'unidad_medida.required' => 'La unidad de medida es obligatoria',
            'nombre.max' => 'El nombre es muy grande',
            'codigo.required' => 'El codigo es obligatorio',
            'codigo.unique' => 'El codigo ya existe',
            'codigo.max' => 'El codigo es muy grande',
            'stock_minimo.required' => 'El stock minimo es obligatorio',
            'disponibles.required' => 'Los disponibles son obligatorios',
            'conversion.required' => 'La conversion es obligatoria',
        ]);
        if (!$valida->fails()) {
            $model = new Producto();
            $model->codigo = $request->codigo;
            $model->nombre = espacios(mb_strtoupper($request->nombre));
            $model->id_categoria_producto = $request->categoria;
            $model->id_proveedor = $request->proveedor;
            $model->unidad_medida = $request->unidad_medida;
            $model->stock_minimo = $request->stock_minimo;
            $model->disponibles = 0;
            $model->conversion = $request->conversion;
            $model->precio_costo = $request->precio_costo;
            $model->save();
            $model = Producto::All()->last();
            bitacora('producto', $model->id_producto, 'I', 'Creacion del producto');
            $success = true;
            $msg = 'Se ha <strong>CREADO</strong> el producto satisfactoriamente';
        } else {
            $success = false;
            $errores = '';
            foreach ($valida->errors()->all() as $mi_error) {
                if ($errores == '') {
                    $errores = '<li>' . $mi_error . '</li>';
                } else {
                    $errores .= '<li>' . $mi_error . '</li>';
                }
            }
            $msg = '<div class="alert alert-danger">' .
                '<p class="text-center">¡Por favor corrija los siguientes errores!</p>' .
                '<ul>' .
                $errores .
                '</ul>' .
                '</div>';
        }
        return [
            'mensaje' => $msg,
            'success' => $success
        ];
    }

    public function update_producto(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'nombre' => 'required|max:500',
            'codigo' => 'required|max:500',
            'unidad_medida' => 'required',
            'stock_minimo' => 'required',
            'conversion' => 'required',
        ], [
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.max' => 'El nombre es muy grande',
            'codigo.required' => 'El codigo es obligatorio',
            'codigo.max' => 'El codigo es muy grande',
            'unidad_medida.required' => 'La unidad de medida es obligatoria',
            'stock_minimo.required' => 'El stock minimo es obligatorio',
            'conversion.required' => 'La conversion es obligatoria',
        ]);
        if (!$valida->fails()) {
            $existe_nombre = Producto::All()
                ->where('id_producto', '!=', $request->id)
                ->where('nombre', espacios(mb_strtoupper($request->nombre)))
                ->first();
            if ($existe_nombre != '') {
                $success = false;
                $msg = '<div class="alert alert-warning text-center">' .
                    '<p>El nombre del producto y existe</p>'
                    . '</div>';
            } else {
                $existe_codigo = Producto::All()
                    ->where('id_producto', '!=', $request->id)
                    ->where('codigo', espacios(mb_strtoupper($request->codigo)))
                    ->first();
                if ($existe_codigo != '') {
                    $success = false;
                    $msg = '<div class="alert alert-warning text-center">' .
                        '<p>El codigo del producto y existe</p>'
                        . '</div>';
                } else {
                    $model = Producto::find($request->id);
                    $model->codigo = $request->codigo;
                    $model->nombre = espacios(mb_strtoupper($request->nombre));
                    $model->id_categoria_producto = $request->categoria;
                    $model->id_proveedor = $request->proveedor;
                    $model->stock_minimo = $request->stock_minimo;
                    $model->unidad_medida = $request->unidad_medida;
                    $model->conversion = $request->conversion;
                    $model->precio_costo = $request->precio_costo;
                    $model->save();

                    if ($model->save()) {
                        $success = true;
                        $msg = 'Se ha <strong>MODIFICADO</strong> el producto satisfactoriamente';
                        bitacora('producto', $model->id_producto, 'U', 'Modifico el producto');
                    } else {
                        $success = false;
                        $msg = '<div class="alert alert-warning text-center">' .
                            '<p> Ha ocurrido un problema al guardar la información al sistema</p>'
                            . '</div>';
                    }
                }
            }
        } else {
            $success = false;
            $errores = '';
            foreach ($valida->errors()->all() as $mi_error) {
                if ($errores == '') {
                    $errores = '<li>' . $mi_error . '</li>';
                } else {
                    $errores .= '<li>' . $mi_error . '</li>';
                }
            }
            $msg = '<div class="alert alert-danger">' .
                '<p class="text-center">¡Por favor corrija los siguientes errores!</p>' .
                '<ul>' .
                $errores .
                '</ul>' .
                '</div>';
        }
        return [
            'mensaje' => $msg,
            'success' => $success
        ];
    }

    public function cambiar_estado_producto(Request $request)
    {
        $model = Producto::find($request->id);
        $model->estado = $model->estado == 1 ? 0 : 1;
        $model->save();

        $success = true;
        $msg = 'Se ha <strong>MODIFICADO</strong> el producto satisfactoriamente';
        return [
            'mensaje' => $msg,
            'success' => $success
        ];
    }

    public function upload_productos(Request $request)
    {
        return view('adminlte.gestion.bodega.productos.forms.upload_productos', []);
    }

    public function post_importar_productos(Request $request)
    {
        ini_set('max_execution_time', env('MAX_EXECUTION_TIME'));
        $valida = Validator::make($request->all(), [
            'file_producto' => 'required',
        ]);
        $msg = '<div class="alert alert-success text-center">Se ha importado el archivo. Revise su contenido antes de grabar.</div>';
        $success = true;
        if (!$valida->fails()) {
            try {
                $archivo = $request->file_producto;
                $extension = $archivo->getClientOriginalExtension();
                $nombre_archivo = "upload_productos." . $extension;
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

    public function get_importar_productos(Request $request)
    {
        try {
            $url = public_path('storage/file_loads/upload_productos.xlsx');
            $document = IOFactory::load($url);
            $sheet = $document->getActiveSheet()->toArray(null, true, true, true);
            $listado = [];
            foreach ($sheet as $pos => $row) {
                if ($pos > 10 && $row['B'] != '') {
                    $producto = Producto::where('codigo', $row['B'])
                        ->get()
                        ->first();
                    $listado[] = [
                        'model' => $producto,
                        'codigo' => $row['B'],
                        'nombre' => $row['C'],
                        'um' => $row['E'],
                    ];
                }
            }
        } catch (\Exception $e) {
            return '<div class="alert alert-danger text-center">' .
                '<p> Ha ocurrido un problema con el contenido del archivo. Pongase en contacto con el administrador del sistema</p>' .
                '<p>' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine() . '</p>'
                . '</div>';
        }
        return view('adminlte.gestion.bodega.productos.forms.importar_productos', [
            'listado' => $listado,
        ]);
    }

    public function store_importar_productos(Request $request)
    {
        try {
            DB::beginTransaction();
            foreach (json_decode($request->data) as $d) {
                $model = Producto::find($d->id_prod);
                if ($model == '') {
                    $model = new Producto();
                    $model->stock_minimo = 0;
                    $model->id_empresa = 1;
                    $model->disponibles = 0;
                    $model->conversion = 1;
                    $model->codigo = $d->codigo;
                }
                $model->nombre = $d->nombre;
                $model->unidad_medida = $d->um;
                $model->save();
            }

            DB::commit();
            $success = true;
            $msg = 'Se han <strong>GRABADO</strong> los productos correctamente';
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
