<?php

namespace yura\Http\Controllers\Costos;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use yura\Http\Controllers\Controller;
use yura\Jobs\ImportarCostos;
use yura\Modelos\Actividad;
use yura\Modelos\ActividadManoObra;
use yura\Modelos\ActividadProducto;
use yura\Modelos\Area;
use yura\Modelos\CostosSemana;
use yura\Modelos\CostosSemanaManoObra;
use yura\Modelos\ManoObra;
use yura\Modelos\OtrosGastos;
use yura\Modelos\ResumenCostosSemanal;
use yura\Modelos\Semana;
use yura\Modelos\Submenu;
use Validator;
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Worksheet;
use yura\Modelos\Producto;
use Storage as Almacenamiento;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class CostosController extends Controller
{
    public function gestion_insumo(Request $request)
    {
        $finca = getFincaActiva();
        return view('adminlte.gestion.costos.insumo.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'areas' => Area::All()->where('id_empresa', $finca)->sortBy('nombre'),
            'actividades' => Actividad::All()->where('id_empresa', $finca)->sortBy('nombre'),
            'productos' => Producto::All()->where('id_empresa', $finca)->sortBy('nombre'),
            'fincas_propias' => getFincasPropias(),
        ]);
    }

    public function store_area(Request $request)
    {
        $finca = getFincaActiva();
        $valida = Validator::make($request->all(), [
            'nombre' => 'required|max:250',
        ], [
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.max' => 'El nombre es muy grande',
        ]);
        $msg = '';
        if (!$valida->fails()) {
            $model = new Area();
            $model->nombre = str_limit(mb_strtoupper(espacios($request->nombre)), 250);
            $model->fecha_registro = date('Y-m-d H:i:s');
            $model->id_empresa = $finca;

            if ($model->save()) {
                $model = Area::All()->last();
                $success = true;
                bitacora('area', $model->id_area, 'I', 'Inserción satisfactoria de una nueva area');
            } else {
                $success = false;
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
            'success' => $success,
            'mensaje' => $msg,
        ];
    }

    public function update_area(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'nombre' => 'required|max:250',
            'id_area' => 'required|',
        ], [
            'nombre.required' => 'El nombre es obligatorio',
            'id_area.required' => 'El área es obligatorio',
            'nombre.max' => 'El nombre es muy grande',
        ]);
        $msg = '';
        if (!$valida->fails()) {
            if (count(Area::All()->where('nombre', '=', str_limit(mb_strtoupper(espacios($request->nombre)), 250))
                    ->where('id_empresa', getFincaActiva())
                    ->where('id_area', '!=', $request->id_area)) == 0) {
                $model = Area::find($request->id_area);
                $model->nombre = str_limit(mb_strtoupper(espacios($request->nombre)), 250);

                if ($model->save()) {
                    $success = true;
                    bitacora('area', $model->id_area, 'U', 'Actualización satisfactoria de una area');
                } else {
                    $success = false;
                }
            } else {
                $success = false;
                $msg = '<div class="alert alert-warning text-center">' .
                    '<p> El área "' . espacios($request->nombre) . '" ya se encuentra en el sistema</p>'
                    . '</div>';
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

    public function store_actividad(Request $request)
    {
        $finca = getFincaActiva();
        $valida = Validator::make($request->all(), [
            'nombre' => 'required|max:250',
            'area' => 'required',
        ], [
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.max' => 'El nombre es muy grande',
            'area.required' => 'El área es obligatoria',
        ]);
        $msg = '';
        if (!$valida->fails()) {
            $model = new Actividad();
            $model->nombre = str_limit(mb_strtoupper(espacios($request->nombre)), 250);
            $model->id_area = $request->area;
            $model->fecha_registro = date('Y-m-d H:i:s');
            $model->id_empresa = $finca;

            if ($model->save()) {
                $model = Actividad::All()->last();
                $success = true;
                bitacora('actividad', $model->id_actividad, 'I', 'Inserción satisfactoria de una nueva actividad');
            } else {
                $success = false;
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
            'success' => $success,
            'mensaje' => $msg,
        ];
    }

    public function update_actividad(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'nombre' => 'required|max:250',
            'id_actividad' => 'required|',
            'area' => 'required|',
        ], [
            'nombre.required' => 'El nombre es obligatorio',
            'id_actividad.required' => 'La actividad es obligatoria',
            'nombre.max' => 'El nombre es muy grande',
            'area.required' => 'El área es obligatoria',
        ]);
        $msg = '';
        if (!$valida->fails()) {
            if (count(Actividad::All()->where('nombre', '=', str_limit(mb_strtoupper(espacios($request->nombre)), 250))
                    ->where('id_empresa', getFincaActiva())
                    ->where('id_actividad', '!=', $request->id_actividad)) == 0) {
                $model = Actividad::find($request->id_actividad);
                $model->nombre = str_limit(mb_strtoupper(espacios($request->nombre)), 250);
                $model->id_area = $request->area;

                if ($model->save()) {
                    $success = true;
                    bitacora('actividad', $model->id_actividad, 'U', 'Actualización satisfactoria de una actividad');
                } else {
                    $success = false;
                }
            } else {
                $success = false;
                $msg = '<div class="alert alert-warning text-center">' .
                    '<p> La actividad "' . espacios($request->nombre) . '" ya se encuentra en el sistema</p>'
                    . '</div>';
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

    public function importar_actividad(Request $request)
    {
        return view('adminlte.gestion.costos.insumo.forms.importar_actividad', [
            'areas' => Area::All(),
        ]);
    }

    public function importar_file_actividad(Request $request)
    {
        ini_set('max_execution_time', env('MAX_EXECUTION_TIME'));
        $valida = Validator::make($request->all(), [
            'file_actividad' => 'required',
            'id_area_actividad' => 'required',
        ]);
        $msg = '';
        $success = true;
        if (!$valida->fails()) {

            $document = PHPExcel_IOFactory::load($request->file_actividad);
            $activeSheetData = $document->getActiveSheet()->toArray(null, true, true, true);

            $titles = $activeSheetData[1];
            $finca = getFincaActiva();

            foreach ($activeSheetData as $pos_row => $row) {
                if ($pos_row > 1) {
                    if ($row['A'] != '') {
                        $nombre = str_limit(mb_strtoupper(espacios($row['A'])), 250);
                        if (count(Actividad::All()->where('nombre', $nombre)) == 0) {
                            $model = new Actividad();
                            $model->nombre = $nombre;
                            $model->id_area = $request->id_area_actividad;
                            $model->fecha_registro = date('Y-m-d');
                            $model->id_empresa = $finca;

                            $model->save();
                            $model = Actividad::All()->last();
                            bitacora('actividad', $model->id_actividad, 'I', 'Inserción satisfactoria de una nueva actividad');
                            $msg .= '<li class="bg-green">Se ha importado la actividad: "' . $nombre . '."</li>';
                        }
                    }
                }
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

    public function store_producto(Request $request)
    {
        $finca = getFincaActiva();
        $request->nombre = str_limit(mb_strtoupper(espacios($request->nombre)), 250);
        $valida = Validator::make($request->all(), [
            'nombre' => 'required|max:250',
        ], [
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.max' => 'El nombre es muy grande',
        ]);
        $msg = '';
        if (!$valida->fails()) {
            if (count(Producto::All()
                    ->where('id_empresa', getFincaActiva())
                    ->where('nombre', str_limit(mb_strtoupper(espacios($request->nombre)), 250))
                    ->where('estado', 1)) == 0) {
                $model = new Producto();
                $model->nombre = str_limit(mb_strtoupper(espacios($request->nombre)), 250);
                $model->fecha_registro = date('Y-m-d H:i:s');
                $model->id_empresa = $finca;

                if ($model->save()) {
                    $model = Producto::All()->last();
                    $success = true;
                    bitacora('producto', $model->id_producto, 'I', 'Inserción satisfactoria de un nuevo producto');
                } else {
                    $success = false;
                }
            } else {
                $success = false;
                $msg = '<div class="alert alert-danger text-center">El nombre ya existe</div>';
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
            'success' => $success,
            'mensaje' => $msg,
        ];
    }

    public function update_producto(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'nombre' => 'required|max:250',
            'id_producto' => 'required|',
        ], [
            'nombre.required' => 'El nombre es obligatorio',
            'id_producto.required' => 'El producto es obligatorio',
            'nombre.max' => 'El nombre es muy grande',
        ]);
        $msg = '';
        if (!$valida->fails()) {
            if (count(Producto::All()->where('nombre', '=', str_limit(mb_strtoupper(espacios($request->nombre)), 250))
                    ->where('id_empresa', getFincaActiva())
                    ->where('id_producto', '!=', $request->id_producto)) == 0) {
                $model = Producto::find($request->id_producto);
                $model->nombre = str_limit(mb_strtoupper(espacios($request->nombre)), 250);

                if ($model->save()) {
                    $success = true;
                    bitacora('producto', $model->id_producto, 'U', 'Actualización satisfactoria de un producto');
                } else {
                    $success = false;
                }
            } else {
                $success = false;
                $msg = '<div class="alert alert-warning text-center">' .
                    '<p> El producto "' . espacios($request->nombre) . '" ya se encuentra en el sistema</p>'
                    . '</div>';
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

    public function importar_producto(Request $request)
    {
        return view('adminlte.gestion.costos.insumo.forms.importar_producto', [
        ]);
    }

    public function vincular_actividad_producto(Request $request)
    {
        $finca = getFincaActiva();
        $actividad = Actividad::find($request->id);
        $productos_vinc = [];
        foreach ($actividad->productos->where('estado', 1) as $p) {
            array_push($productos_vinc, $p->id_producto);
        }

        return view('adminlte.gestion.costos.insumo.forms.vincular_actividad_producto', [
            'actividad' => $actividad,
            'productos_vinc' => $productos_vinc,
            'productos' => Producto::All()
                ->where('id_empresa', $finca)
                ->where('estado', 1)
                ->sortBy('nombre'),
        ]);
    }

    public function store_actividad_producto(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'actividad' => 'required',
            'producto' => 'required',
        ], [
            'actividad.required' => 'La actividad es obligatoria',
            'producto.required' => 'El producto es obligatorio',
        ]);
        $msg = '';
        $estado = 1;
        if (!$valida->fails()) {
            $model = ActividadProducto::All()
                ->where('id_actividad', $request->actividad)
                ->where('id_producto', $request->producto)
                ->first();
            if ($model == '') {
                $model = new ActividadProducto();
                $model->id_actividad = $request->actividad;
                $model->id_producto = $request->producto;
                $model->fecha_registro = date('Y-m-d H:i:s');

                if ($model->save()) {
                    $model = ActividadProducto::All()->last();
                    $success = true;
                    bitacora('actividad_producto', $model->actividad_producto, 'I', 'Inserción satisfactoria de un nuevo vínculo actividad_producto');
                } else {
                    $success = false;
                }
            } else {
                $model->estado = $model->estado == 1 ? 0 : 1;
                $estado = $model->estado;
                $success = true;

                $model->save();
                bitacora('producto', $model->id_producto, 'U', 'Modificacion satisfactoria del estado de un producto');
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
            'success' => $success,
            'mensaje' => $msg,
            'estado' => $estado,
        ];
    }

    public function importar_file_producto(Request $request)
    {
        ini_set('max_execution_time', env('MAX_EXECUTION_TIME'));
        $finca_actual = getFincaActiva();
        $valida = Validator::make($request->all(), [
            'file_producto' => 'required',
        ]);
        $msg = '<div class="alert alert-info text-center">Se ha importado el archivo, en menos de una hora se reflejarán los datos en el sistema</div>';
        $success = true;
        if (!$valida->fails()) {
            try {
                $archivo = $request->file_producto;
                $extension = $archivo->getClientOriginalExtension();
                $nombre_archivo = date('d-H-i') . "upload_insumos_" . $finca_actual . "." . $extension;
                $r1 = Almacenamiento::disk('pdf_loads')->put($nombre_archivo, \File::get($archivo));

                //$url = public_path('storage/pdf_loads/' . $nombre_archivo);

                //$document = \PHPExcel_IOFactory::load($url);
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

    public function importar_file_act_producto(Request $request)
    {
        ini_set('max_execution_time', env('MAX_EXECUTION_TIME'));
        $valida = Validator::make($request->all(), [
            'file_act_producto' => 'required',
        ]);
        $msg = '';
        $success = true;
        $array_ids_prod = [];
        if (!$valida->fails()) {

            $document = PHPExcel_IOFactory::load($request->file_act_producto);
            $activeSheetData = $document->getActiveSheet()->toArray(null, true, true, true);

            $titles = $activeSheetData[1];
            $finca = getFincaActiva();
            foreach ($activeSheetData as $pos_row => $row) {
                if ($pos_row > 1) {
                    if ($row['A'] != '') {
                        $nombre = str_limit(mb_strtoupper(espacios($row['B'])), 250);
                        $producto = Producto::All()
                            ->where('nombre', $nombre)
                            ->where('id_empresa', $finca)
                            ->first();

                        if ($producto != '') {
                            $model = ActividadProducto::All()
                                ->where('id_actividad', $request->id_actividad)
                                ->where('id_producto', $producto->id_producto)
                                ->first();
                            if ($model == '') {
                                $model = new ActividadProducto();
                                $model->id_actividad = $request->id_actividad;
                                $model->id_producto = $producto->id_producto;
                                $model->fecha_registro = date('Y-m-d H:i:s');

                                if ($model->save()) {
                                    $model = ActividadProducto::All()->last();
                                    $success = true;
                                    bitacora('actividad_producto', $model->actividad_producto, 'I', 'Inserción satisfactoria de un nuevo vínculo actividad_producto');
                                } else {
                                    $success = false;
                                }
                            } else {
                                $model->estado = 1;
                                $success = true;

                                $model->save();
                                bitacora('producto', $model->id_producto, 'U', 'Modificación satisfactoria del estado de un producto');
                            }
                            array_push($array_ids_prod, $producto->id_producto);
                            $msg .= '<li class="bg-green">Se ha vinculado el producto: "' . $nombre . '."</li>';
                        }
                    }
                }
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
            'ids' => $array_ids_prod,
        ];
    }

    public function delete_actividad(Request $request)
    {
        $model = Actividad::find($request->id_actividad);
        $model->estado = $model->estado == 1 ? 0 : 1;
        $model->save();
        bitacora('actividad', $model->id_actividad, 'U', 'Modificacion satisfactoria del estado de una actividad');

        return [
            'success' => true,
            'mensaje' => '',
        ];
    }

    public function delete_producto(Request $request)
    {
        $model = Producto::find($request->id_producto);
        $model->estado = $model->estado == 1 ? 0 : 1;
        $model->save();
        bitacora('producto', $model->id_producto, 'U', 'Modificacion satisfactoria del estado de un producto');

        return [
            'success' => true,
            'mensaje' => '',
        ];
    }

    public function buscar_insumosByActividad(Request $request)
    {
        $act_insumos = [];
        $actividad = Actividad::find($request->actividad);
        if ($actividad != '')
            $act_insumos = ActividadProducto::join('producto as p', 'p.id_producto', 'actividad_producto.id_producto')
                ->where('actividad_producto.id_actividad', $request->actividad)
                ->where('p.estado', 1)
                ->orderBy('p.nombre')
                ->get();
        return view('adminlte.gestion.costos.insumo.partials.select_edit_insumo', [
            'act_insumos' => $act_insumos,
            'form' => $request->form,
        ]);
    }

    public function buscar_moByActividad(Request $request)
    {
        $act_mo = [];
        $actividad = Actividad::find($request->actividad);
        if ($actividad != '')
            $act_mo = $actividad->manos_obra;
        return view('adminlte.gestion.costos.mano_obra.partials.select_edit_mo', [
            'act_mo' => $act_mo,
            'form' => $request->form,
        ]);
    }

    public function buscar_valorByActividadInsumoSemana(Request $request)
    {
        $finca_actual = getFincaActiva();
        $valor = 0;
        $existe = false;
        $act_ins = ActividadProducto::All()
            ->where('estado', 1)
            ->where('id_actividad', $request->actividad)
            ->where('id_producto', $request->insumo)
            ->first();
        if ($act_ins != '') {
            $costo_sem = CostosSemana::All()
                ->where('id_actividad_producto', $act_ins->id_actividad_producto)
                ->where('codigo_semana', $request->semana)
                ->where('id_empresa', $finca_actual)
                ->first();
            if ($costo_sem != '') {
                $valor = $costo_sem->valor;
                $existe = true;
            }
        }
        return [
            'valor' => $valor,
            'existe' => $existe,
        ];
    }

    public function buscar_valorByActividadMOSemana(Request $request)
    {
        $finca_actual = getFincaActiva();
        $valor = 0;
        $existe = false;
        $act_mo = ActividadManoObra::All()
            ->where('estado', 1)
            ->where('id_actividad', $request->actividad)
            ->where('id_mano_obra', $request->mo)
            ->first();
        if ($act_mo != '') {
            $costo_sem = CostosSemanaManoObra::All()
                ->where('id_actividad_mano_obra', $act_mo->id_actividad_mano_obra)
                ->where('codigo_semana', $request->semana)
                ->where('id_empresa', $finca_actual)
                ->first();
            if ($costo_sem != '') {
                $valor = $costo_sem->valor;
                $existe = true;
            }
        }
        return [
            'valor' => $valor,
            'existe' => $existe,
        ];
    }

    public function save_costoInsumo(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'actividad' => 'required',
            'semana' => 'required',
            'valor' => 'required',
            'insumo' => 'required',
        ], [
            'actividad.required' => 'La actividad es obligatoria',
            'semana.required' => 'La semana es obligatoria',
            'insumo.required' => 'El insumo es obligatorio',
            'valor.required' => 'El valor es obligatorio',
        ]);
        if (!$valida->fails()) {
            $finca_actual = getFincaActiva();
            $act_ins = ActividadProducto::All()
                ->where('estado', 1)
                ->where('id_actividad', $request->actividad)
                ->where('id_producto', $request->insumo)
                ->first();
            if ($act_ins != '') {
                $costo_sem = CostosSemana::All()
                    ->where('id_actividad_producto', $act_ins->id_actividad_producto)
                    ->where('codigo_semana', $request->semana)
                    ->where('id_empresa', $finca_actual)
                    ->first();
                $new = false;
                if ($costo_sem == '') {
                    $costo_sem = new CostosSemana();
                    $costo_sem->id_actividad_producto = $act_ins->id_actividad_producto;
                    $costo_sem->codigo_semana = $request->semana;
                    $costo_sem->valor = $costo_sem->cantidad = 0;
                    $new = true;
                }
                $costo_sem->valor = $request->valor;
                $costo_sem->id_empresa = $finca_actual;

                if ($costo_sem->save()) {
                    $success = true;
                    if ($new)
                        $id = CostosSemana::All()->last()->id_costos_semana;
                    else
                        $id = $costo_sem->id_costos_semana;
                    bitacora('costos_semana', $id, 'I', 'Inserción satisfactoria de un costo por semana');
                } else {
                    $success = false;
                }
            } else
                $success = false;
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
            'success' => $success
        ];
    }

    public function save_costoMO(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'actividad' => 'required',
            'semana' => 'required',
            'valor' => 'required',
            'mo' => 'required',
        ], [
            'actividad.required' => 'La actividad es obligatoria',
            'semana.required' => 'La semana es obligatoria',
            'mo.required' => 'La mano de obra es obligatoria',
            'valor.required' => 'El valor es obligatorio',
        ]);
        if (!$valida->fails()) {
            $finca_actual = getFincaActiva();
            $act_mo = ActividadManoObra::All()
                ->where('estado', 1)
                ->where('id_actividad', $request->actividad)
                ->where('id_mano_obra', $request->mo)
                ->first();
            if ($act_mo != '') {
                $costo_sem = CostosSemanaManoObra::All()
                    ->where('id_actividad_mano_obra', $act_mo->id_actividad_mano_obra)
                    ->where('codigo_semana', $request->semana)
                    ->where('id_empresa', $finca_actual)
                    ->first();
                $new = false;
                if ($costo_sem == '') {
                    $costo_sem = new CostosSemanaManoObra();
                    $costo_sem->id_actividad_mano_obra = $act_mo->id_actividad_mano_obra;
                    $costo_sem->codigo_semana = $request->semana;
                    $costo_sem->valor = $costo_sem->cantidad = 0;
                    $new = true;
                }
                $costo_sem->valor = $request->valor;
                $costo_sem->id_empresa = $finca_actual;

                if ($costo_sem->save()) {
                    $success = true;
                    if ($new)
                        $id = CostosSemanaManoObra::All()->last()->id_costos_semana_mano_obra;
                    else
                        $id = $costo_sem->id_costos_semana_mano_obra;
                    bitacora('costos_semana_mano_obra', $id, 'I', 'Inserción satisfactoria de un costo por semana');
                } else {
                    $success = false;
                }
            } else
                $success = false;
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
            'success' => $success
        ];
    }

    /* ==================================== EXPORTAR ===================================== */

    public function exportar_reporte_insumos(Request $request)
    {
        $spread = new Spreadsheet();
        $this->excel_reporte_insumos($spread, $request);
        $spread->getProperties()
            ->setCreator("Benchflow")
            ->setTitle('Insumos')
            ->setSubject('Insumos');

        $fileName = "Insumos.xlsx";
        $writer = new Xlsx($spread);

        //--------------------------- GUARDAR EL EXCEL -----------------------

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        $writer->save('php://output');
    }

    public function excel_reporte_insumos($spread, $request)
    {
        $finca_actual = getFincaActiva();
        $semanas = DB::table('costos_semana')
            ->select('codigo_semana')->distinct()
            ->where('id_empresa', $finca_actual)
            ->where('codigo_semana', '>=', $request->desde)
            ->where('codigo_semana', '<=', $request->hasta)
            ->where('id_empresa', $finca_actual)
            ->orderBy('codigo_semana')
            ->get();
        $area = Area::find($request->area);
        $actividad = Actividad::find($request->actividad);

        $ids = DB::table('costos_semana as c')
            ->join('actividad_producto as ap', 'c.id_actividad_producto', '=', 'ap.id_actividad_producto')
            ->join('producto as p', 'p.id_producto', '=', 'ap.id_producto')
            ->select('c.id_actividad_producto')->distinct();
        if ($actividad != '')   // una actividad en especifico
            $ids = $ids
                ->where('ap.id_actividad', $actividad->id_actividad);
        else if ($area != '') {
            $ids = $ids
                ->join('actividad as a', 'ap.id_actividad', '=', 'a.id_actividad')
                ->where('a.id_area', $area->id_area);
        }
        if ($request->criterio == 'V')  // dinero
            $ids = $ids->where('c.valor', '>', 0);
        else    // cantidad
            $ids = $ids->where('c.cantidad', '>', 0);
        if ($finca_actual != 'T') {
            $ids = $ids->where('c.id_empresa', $finca_actual);
        }
        $ids = $ids
            ->where('c.codigo_semana', '>=', $request->desde)
            ->where('c.codigo_semana', '<=', $request->hasta)
            ->orderBy('p.nombre')
            ->get();

        $list_ids = [];
        foreach ($ids as $item)
            array_push($list_ids, $item->id_actividad_producto);

        $productos = DB::table('costos_semana as c')
            ->join('actividad_producto as ap', 'ap.id_actividad_producto', '=', 'c.id_actividad_producto')
            ->join('producto as p', 'p.id_producto', '=', 'ap.id_producto')
            ->select('p.nombre', DB::raw('sum(c.valor) as valor'))
            ->where('c.codigo_semana', '>=', $request->desde)
            ->where('c.codigo_semana', '<=', $request->hasta)
            ->whereIn('c.id_actividad_producto', $list_ids)
            ->groupBy('p.nombre')
            ->orderBy('p.nombre')
            ->get();

        $matriz = [];
        foreach ($productos as $p) {
            $valores = [];
            foreach ($semanas as $sem) {
                $val = DB::table('costos_semana as c')
                    ->join('actividad_producto as ap', 'ap.id_actividad_producto', '=', 'c.id_actividad_producto')
                    ->join('producto as p', 'p.id_producto', '=', 'ap.id_producto')
                    ->select(DB::raw('sum(c.valor) as valor'))
                    ->where('c.codigo_semana', $sem->codigo_semana)
                    ->whereIn('c.id_actividad_producto', $list_ids)
                    ->where('p.nombre', $p->nombre)
                    ->get()[0]->valor;
                $valores[] = $val;
            }
            $matriz[] = [
                'producto' => $p,
                'valores' => $valores,
            ];
        }

        $totales = DB::table('costos_semana')
            ->select(DB::raw('sum(valor) as cant'), 'codigo_semana as semana')
            ->where('codigo_semana', '>=', $request->desde)
            ->where('codigo_semana', '<=', $request->hasta)
            ->whereIn('id_actividad_producto', $list_ids)
            ->where('id_empresa', $finca_actual)
            ->groupBy('codigo_semana')
            ->get();

        /* ----------------------- CREAR HOJA DE EXCEL ------------------------ */
        $objSheet = $spread->getActiveSheet()->setTitle('Insumos');
        $columnas = getColumnasExcel();

        /* --------------- SEMANAS ------------------ */
        setValueToCeldaExcel($objSheet, 'A1', 'Semanas');

        foreach ($semanas as $col => $sem)
            setValueToCeldaExcel($objSheet, $columnas[$col + 1] . '1', $sem->codigo_semana);

        setValueToCeldaExcel($objSheet, $columnas[$col + 2] . '1', 'Total');
        setValueToCeldaExcel($objSheet, $columnas[$col + 3] . '1', '%');
        setValueToCeldaExcel($objSheet, $columnas[$col + 4] . '1', 'Acum.');

        /* --------------- TOTALES ------------------ */
        setValueToCeldaExcel($objSheet, 'A2', 'Totales');

        $total = 0;
        foreach ($semanas as $pos_sem => $sem) {
            $valor = 0;
            foreach ($totales as $pos_t => $item)
                if ($sem->codigo_semana == $item->semana)
                    $valor = $item->cant;
            setValueToCeldaExcel($objSheet, $columnas[$pos_sem + 1] . '2', round($valor, 2));
            $total += round($valor, 2);
        }

        setValueToCeldaExcel($objSheet, $columnas[$pos_sem + 2] . '2', $total);
        setValueToCeldaExcel($objSheet, $columnas[$pos_sem + 3] . '2', '100%');
        setValueToCeldaExcel($objSheet, $columnas[$pos_sem + 4] . '2', 'Acum.');
        setBgToCeldaExcel($objSheet, 'A1:' . $columnas[$pos_sem + 4] . '2', 'e9ecef');   // gris

        $row = 3;
        $acumulado = 0;
        foreach ($matriz as $pos_act => $p) {
            setValueToCeldaExcel($objSheet, 'A' . $row, $p['producto']->nombre);
            setBgToCeldaExcel($objSheet, 'A' . $row, 'e9ecef');   // gris
            $total_prod = 0;
            foreach ($p['valores'] as $pos_sem => $v) {
                setValueToCeldaExcel($objSheet, $columnas[$pos_sem + 1] . $row, round($v, 2));
                $total_prod += round($v, 2);
            }
            setValueToCeldaExcel($objSheet, $columnas[$pos_sem + 2] . $row, round($total_prod, 2));
            setValueToCeldaExcel($objSheet, $columnas[$pos_sem + 3] . $row, porcentaje($total_prod, $total, 1));
            $acumulado += porcentaje($total_prod, $total, 1);
            setValueToCeldaExcel($objSheet, $columnas[$pos_sem + 4] . $row, round($acumulado, 2));
            setBgToCeldaExcel($objSheet, $columnas[$pos_sem + 2] . $row . ':' . $columnas[$pos_sem + 4] . $row, 'e9ecef');  // gris
            $row++;
        }

        setBorderToCeldaExcel($objSheet, 'A1:' . $columnas[$col + 4] . ($row - 1));
        for ($i = 0; $i <= $col + 5; $i++)
            $objSheet->getColumnDimension($columnas[$i])->setAutoSize(true);
    }

    public function exportar_reporte_mano_obra(Request $request)
    {
        $spread = new Spreadsheet();
        $this->excel_reporte_mano_obra($spread, $request);
        $spread->getProperties()
            ->setCreator("Benchflow")
            ->setTitle('Mano de Obra')
            ->setSubject('Mano de Obra');

        $fileName = "Mano_de_Obra.xlsx";
        $writer = new Xlsx($spread);

        //--------------------------- GUARDAR EL EXCEL -----------------------

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        $writer->save('php://output');
    }

    public function excel_reporte_mano_obra($spread, $request)
    {
        $finca_actual = getFincaActiva();
        $semanas = DB::table('costos_semana_mano_obra')
            ->select('codigo_semana')->distinct()
            ->where('codigo_semana', '>=', $request->desde)
            ->where('codigo_semana', '<=', $request->hasta)
            ->where('id_empresa', $finca_actual)
            ->orderBy('codigo_semana')
            ->get();
        $area = Area::find($request->area);
        $actividad = Actividad::find($request->actividad);

        $ids = DB::table('costos_semana_mano_obra as c')
            ->select('c.id_actividad_mano_obra', 'mo.nombre')->distinct()
            ->join('actividad_mano_obra as ap', 'c.id_actividad_mano_obra', '=', 'ap.id_actividad_mano_obra')
            ->join('mano_obra as mo', 'mo.id_mano_obra', '=', 'ap.id_mano_obra');
        if ($actividad != '')   // una actividad en especifico
            $ids = $ids
                ->where('ap.id_actividad', $actividad->id_actividad);
        else if ($area != '') {
            $ids = $ids
                ->join('actividad as a', 'ap.id_actividad', '=', 'a.id_actividad')
                ->where('a.id_area', $area->id_area);
        }
        if ($request->criterio == 'V')  // dinero
            $ids = $ids->where('c.valor', '>', 0);
        else    // cantidad
            $ids = $ids->where('c.cantidad', '>', 0);
        $ids = $ids
            ->where('c.codigo_semana', '>=', $request->desde)
            ->where('c.codigo_semana', '<=', $request->hasta);
        if ($finca_actual != 'T')
            $ids = $ids->where('c.id_empresa', $finca_actual);
        $ids = $ids->orderBy('mo.nombre')->get();

        $list_ids = [];
        $matriz = [];
        foreach ($ids as $item) {
            $query = CostosSemanaManoObra::where('codigo_semana', '>=', $request->desde)
                ->where('codigo_semana', '<=', $request->hasta)
                ->where('id_actividad_mano_obra', $item->id_actividad_mano_obra);
            if ($finca_actual != 'T')
                $query = $query->where('id_empresa', $finca_actual);
            $query = $query->orderBy('codigo_semana')->get();

            array_push($matriz, $query);
            array_push($list_ids, $item->id_actividad_mano_obra);
        }

        $totales = DB::table('costos_semana_mano_obra')
            ->select(DB::raw('sum(valor) as cant'), 'codigo_semana as semana')
            ->where('codigo_semana', '>=', $request->desde)
            ->where('codigo_semana', '<=', $request->hasta)
            ->whereIn('id_actividad_mano_obra', $list_ids)
            ->where('id_empresa', $finca_actual)
            ->groupBy('codigo_semana')
            ->get();

        /* ----------------------- CREAR HOJA DE EXCEL ------------------------ */
        $objSheet = $spread->getActiveSheet()->setTitle('Insumos');
        $columnas = getColumnasExcel();

        /* --------------- SEMANAS ------------------ */
        setValueToCeldaExcel($objSheet, 'A1', 'Semanas');

        foreach ($semanas as $col => $sem)
            setValueToCeldaExcel($objSheet, $columnas[$col + 1] . '1', $sem->codigo_semana);

        setValueToCeldaExcel($objSheet, $columnas[$col + 2] . '1', 'Total');
        setValueToCeldaExcel($objSheet, $columnas[$col + 3] . '1', '%');
        setValueToCeldaExcel($objSheet, $columnas[$col + 4] . '1', '');

        /* --------------- TOTALES ------------------ */
        setValueToCeldaExcel($objSheet, 'A2', 'Totales');
        $total = 0;
        foreach ($totales as $pos => $item) {
            setValueToCeldaExcel($objSheet, $columnas[$pos + 1] . '2', round($item->cant, 2));
            $total += round($item->cant, 2);
        }
        setValueToCeldaExcel($objSheet, $columnas[$col + 2] . '2', round($total, 2));
        setValueToCeldaExcel($objSheet, $columnas[$col + 3] . '2', '100%');
        setValueToCeldaExcel($objSheet, $columnas[$col + 4] . '2', 'Acum.');

        setBgToCeldaExcel($objSheet, 'A1:' . $columnas[$col + 4] . '2', 'e9ecef');  // gris

        $acumulado = 0;
        $row = 3;
        foreach ($matriz as $pos_act => $act) {
            $total_prod = 0;
            foreach ($act as $pos_item => $item) {
                if ($pos_item == 0) {
                    setValueToCeldaExcel($objSheet, 'A' . $row, $item->actividad_mano_obra->mano_obra->nombre);
                    setBgToCeldaExcel($objSheet, 'A' . $row, 'e9ecef'); // gris
                }
                setValueToCeldaExcel($objSheet, $columnas[$pos_item + 1] . $row, round($item->valor, 2));
                $total_prod += round($item->valor, 2);
            }
            setValueToCeldaExcel($objSheet, $columnas[$pos_item + 2] . $row, round($total_prod, 2));
            setValueToCeldaExcel($objSheet, $columnas[$pos_item + 3] . $row, porcentaje($total_prod, $total, 1));
            $acumulado += porcentaje($total_prod, $total, 1);
            setValueToCeldaExcel($objSheet, $columnas[$pos_item + 4] . $row, round($acumulado, 2));

            setBgToCeldaExcel($objSheet, $columnas[$pos_item + 2] . $row . ':' . $columnas[$pos + 4] . $row, 'e9ecef');  // gris
            $row++;
        }

        setBorderToCeldaExcel($objSheet, 'A1:' . $columnas[$col + 4] . ($row - 1));
        for ($i = 0; $i <= $col + 5; $i++)
            $objSheet->getColumnDimension($columnas[$i])->setAutoSize(true);
    }

    public function exportar_reporte_costos_generales(Request $request)
    {
        $spread = new Spreadsheet();
        $this->excel_reporte_costos_generales($spread, $request);
        $spread->getProperties()
            ->setCreator("Benchflow")
            ->setTitle('P y G')
            ->setSubject('P y G');

        $fileName = "P_y_G.xlsx";
        $writer = new Xlsx($spread);

        //--------------------------- GUARDAR EL EXCEL -----------------------

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        $writer->save('php://output');
    }

    public function excel_reporte_costos_generales($spread, $request)
    {
        $finca = getFincaActiva();
        $semanas = DB::table('semana')
            ->select('codigo', 'fecha_inicial', 'fecha_final')->distinct()
            ->where('codigo', '>=', $request->desde)
            ->where('codigo', '<=', $request->hasta)
            ->orderBy('codigo')
            ->get();
        $resumen_semanal = DB::table('resumen_total_semanal_exportcalas')
            ->select('semana',
                DB::raw('sum(tallos_cosechados) as tallos_cosechados'),
                DB::raw('sum(tallos_exportables) as tallos_exportables'),
                DB::raw('sum(bouquetera) as bouquetera'),
                DB::raw('sum(venta) as venta'),
                DB::raw('sum(nacional) as nacionales'),
                DB::raw('sum(bajas) as bajas'),
                DB::raw('sum(tallos_vendidos) as tallos_vendidos'),
                DB::raw('sum(venta_bouquetera) as venta_bouquetera'))
            ->where('id_empresa', $finca)
            ->where('semana', '>=', $request->desde)
            ->where('semana', '<=', $request->hasta)
            ->groupBy('semana')
            ->orderBy('semana')
            ->get();
        $resumen_costos = DB::table('resumen_costos_semanal')
            ->where('id_empresa', $finca)
            ->where('codigo_semana', '>=', $request->desde)
            ->where('codigo_semana', '<=', $request->hasta)
            ->orderBy('codigo_semana')
            ->get();
        $fincas = [$finca];
        if ($finca == 2)
            array_push($fincas, -1);
        $compra_flor = [];
        $tallos_bqt_total = [];
        foreach ($semanas as $sem) {
            $cant = DB::table('bouquetera')
                ->select(DB::raw('sum(precio * (tallos)) as tallos'),
                    DB::raw('sum(precio * (exportada)) as exportada'),
                    DB::raw('sum(tallos) as tallos_bqt'),
                    DB::raw('sum(exportada) as tallos_exportada'))
                ->where('fecha', '>=', $sem->fecha_inicial)
                ->where('fecha', '<=', $sem->fecha_final)
                ->whereIn('id_empresa', $fincas)
                ->get()[0];
            array_push($compra_flor, $cant);
        }

        $resumen_area = [];
        foreach ($semanas as $sem) {
            $cant = DB::table('ciclo')
                ->select(DB::raw('sum(area) as area'))
                ->where('estado', '=', 1)
                ->where('id_empresa', $finca)
                ->Where(function ($q) use ($sem) {
                    $q->where('fecha_fin', '>=', $sem->fecha_inicial)
                        ->where('fecha_fin', '<=', $sem->fecha_final)
                        ->orWhere(function ($q) use ($sem) {
                            $q->where('fecha_inicio', '>=', $sem->fecha_inicial)
                                ->where('fecha_inicio', '<=', $sem->fecha_final);
                        })
                        ->orWhere(function ($q) use ($sem) {
                            $q->where('fecha_inicio', '<', $sem->fecha_inicial)
                                ->where('fecha_fin', '>', $sem->fecha_final);
                        });
                })
                ->get()[0]->area;
            array_push($resumen_area, $cant);
        }

        $areas = Area::where('estado', 1)->where('id_empresa', $finca)->get();
        $centros_costos = [];
        foreach ($areas as $a) {
            array_push($centros_costos, [
                'area' => $a,
                'insumos' => DB::table('costos_semana as cs')
                    ->join('actividad_producto as ap', 'ap.id_actividad_producto', '=', 'cs.id_actividad_producto')
                    ->join('actividad as a', 'a.id_actividad', '=', 'ap.id_actividad')
                    ->select(DB::raw('sum(cs.valor) as valor'), 'cs.codigo_semana')
                    ->where('cs.id_empresa', $finca)
                    ->where('a.id_area', $a->id_area)
                    ->where('cs.codigo_semana', '>=', $request->desde)
                    ->where('cs.codigo_semana', '<=', $request->hasta)
                    ->groupBy('cs.codigo_semana')
                    ->orderBy('cs.codigo_semana')
                    ->get(),
                'mano_obra' => DB::table('costos_semana_mano_obra as cs')
                    ->join('actividad_mano_obra as amo', 'amo.id_actividad_mano_obra', '=', 'cs.id_actividad_mano_obra')
                    ->join('actividad as a', 'a.id_actividad', '=', 'amo.id_actividad')
                    ->select(DB::raw('sum(cs.valor) as valor'), 'cs.codigo_semana')
                    ->where('cs.id_empresa', $finca)
                    ->where('a.id_area', $a->id_area)
                    ->where('cs.codigo_semana', '>=', $request->desde)
                    ->where('cs.codigo_semana', '<=', $request->hasta)
                    ->groupBy('cs.codigo_semana')
                    ->orderBy('cs.codigo_semana')
                    ->get(),
                'otros_gastos' => DB::table('otros_gastos')
                    ->where('id_empresa', $finca)
                    ->where('id_area', $a->id_area)
                    ->where('codigo_semana', '>=', $request->desde)
                    ->where('codigo_semana', '<=', $request->hasta)
                    ->orderBy('codigo_semana')
                    ->get(),
            ]);
        }

        $indicadores_4_semanas = DB::table('indicadores_4_semanas')
            ->where('semana', '>=', $request->desde)
            ->where('semana', '<=', $request->hasta)
            ->where('id_empresa', $finca)
            ->orderBy('semana')
            ->get();

        /* ----------------------- CREAR HOJA DE EXCEL ------------------------ */
        $objSheet = $spread->getActiveSheet()->setTitle('P_y_G');
        $columnas = getColumnasExcel();

        foreach ($semanas as $col => $sem) {
            setValueToCeldaExcel($objSheet, $columnas[$col + 1] . '1', $sem->codigo);
        }
        setValueToCeldaExcel($objSheet, $columnas[$col + 2] . '1', 'Total');
        setColorTextToCeldaExcel($objSheet, 'A1:' . $columnas[$col + 2] . '1', 'FFFFFF');    // blanco
        setBgToCeldaExcel($objSheet, 'A1:' . $columnas[$col + 2] . '1', '00b388');    // verde

        /* AERA */
        $row = 2;
        setValueToCeldaExcel($objSheet, 'A' . $row, 'ÁREA m2');
        setBgToCeldaExcel($objSheet, 'A' . $row, 'e9ecef');   // gris
        $total_area_m2 = 0;
        foreach ($resumen_area as $pos => $item) {
            setValueToCeldaExcel($objSheet, $columnas[$pos + 1] . $row, round($item, 2));
            $total_area_m2 += $item;
        }
        setValueToCeldaExcel($objSheet, $columnas[$pos + 2] . $row, round($total_area_m2, 2));
        setBgToCeldaExcel($objSheet, $columnas[$pos + 2] . $row, 'e9ecef');   // gris

        /* TALLOS COSECHADOS */
        $row = 3;
        setValueToCeldaExcel($objSheet, 'A' . $row, 'TALLOS COSECHADOS');
        setBgToCeldaExcel($objSheet, 'A' . $row, 'e9ecef');   // gris
        $total_tallos_cosechados = 0;
        foreach ($resumen_semanal as $pos => $item) {
            setValueToCeldaExcel($objSheet, $columnas[$pos + 1] . $row, round($item->tallos_cosechados, 2));
            $total_tallos_cosechados += $item->tallos_cosechados;
        }
        setValueToCeldaExcel($objSheet, $columnas[$pos + 2] . $row, round($total_tallos_cosechados, 2));
        setBgToCeldaExcel($objSheet, $columnas[$pos + 2] . $row, 'e9ecef');   // gris

        /* TALLOS PRODUCIDOS */
        $row = 4;
        setValueToCeldaExcel($objSheet, 'A' . $row, 'TALLOS PRODUCIDOS');
        setBgToCeldaExcel($objSheet, 'A' . $row, 'e9ecef');   // gris
        $total_tallos_producidos = 0;
        foreach ($resumen_semanal as $pos => $item) {
            setValueToCeldaExcel($objSheet, $columnas[$pos + 1] . $row, round($item->tallos_exportables + $compra_flor[$pos]->tallos_bqt, 2));
            $total_tallos_producidos += $item->tallos_exportables + $compra_flor[$pos]->tallos_bqt;
        }
        setValueToCeldaExcel($objSheet, $columnas[$pos + 2] . $row, round($total_tallos_producidos, 2));
        setBgToCeldaExcel($objSheet, $columnas[$pos + 2] . $row, 'e9ecef');   // gris

        /* EXPORTABLES */
        $row = 5;
        setValueToCeldaExcel($objSheet, 'A' . $row, 'EXPORTABLES');
        setBgToCeldaExcel($objSheet, 'A' . $row, 'afffec');   // verde clarito
        $total_tallos_exportables = 0;
        foreach ($resumen_semanal as $pos => $item) {
            setValueToCeldaExcel($objSheet, $columnas[$pos + 1] . $row, round($item->tallos_exportables, 2));
            $total_tallos_exportables += $item->tallos_exportables;
        }
        setValueToCeldaExcel($objSheet, $columnas[$pos + 2] . $row, round($total_tallos_exportables, 2));
        setBgToCeldaExcel($objSheet, $columnas[$pos + 2] . $row, 'afffec');   // verde clarito

        /* BOUQUETERA */
        $row = 6;
        setValueToCeldaExcel($objSheet, 'A' . $row, 'BOUQUETERA');
        setBgToCeldaExcel($objSheet, 'A' . $row, 'afffec');   // verde clarito
        $total_tallos_bouquetera = 0;
        foreach ($compra_flor as $pos => $item) {
            setValueToCeldaExcel($objSheet, $columnas[$pos + 1] . $row, round($item->tallos_bqt, 2));
            $total_tallos_bouquetera += $item->tallos_bqt;
        }
        setValueToCeldaExcel($objSheet, $columnas[$pos + 2] . $row, round($total_tallos_bouquetera, 2));
        setBgToCeldaExcel($objSheet, $columnas[$pos + 2] . $row, 'afffec');   // verde clarito

        /* VENTA TOTAL */
        $row = 7;
        setValueToCeldaExcel($objSheet, 'A' . $row, 'VENTA TOTAL');
        setBgToCeldaExcel($objSheet, 'A' . $row, 'e9ecef');   // gris
        $total_valor_venta = 0;
        foreach ($resumen_semanal as $pos => $item) {
            setValueToCeldaExcel($objSheet, $columnas[$pos + 1] . $row, round($item->venta + $item->venta_bouquetera, 2));
            $total_valor_venta += $item->venta + $item->venta_bouquetera;
        }
        setValueToCeldaExcel($objSheet, $columnas[$pos + 2] . $row, round($total_valor_venta, 2));
        setBgToCeldaExcel($objSheet, $columnas[$pos + 2] . $row, 'e9ecef');   // gris

        /* VENTA */
        $row = 8;
        setValueToCeldaExcel($objSheet, 'A' . $row, 'VENTA');
        setBgToCeldaExcel($objSheet, 'A' . $row, 'afffec');   // verde clarito
        $total_venta = 0;
        foreach ($resumen_semanal as $pos => $item) {
            setValueToCeldaExcel($objSheet, $columnas[$pos + 1] . $row, round($item->venta, 2));
            $total_venta += $item->venta;
        }
        setValueToCeldaExcel($objSheet, $columnas[$pos + 2] . $row, round($total_venta, 2));
        setBgToCeldaExcel($objSheet, $columnas[$pos + 2] . $row, 'afffec');   // verde clarito

        /* VENTA BOUQUETERA */
        $row = 9;
        setValueToCeldaExcel($objSheet, 'A' . $row, 'VENTA BOUQUETERA');
        setBgToCeldaExcel($objSheet, 'A' . $row, 'afffec');   // verde clarito
        $total_valor_bqt = 0;
        foreach ($resumen_semanal as $pos => $item) {
            setValueToCeldaExcel($objSheet, $columnas[$pos + 1] . $row, round($item->venta_bouquetera, 2));
            $total_valor_bqt += $item->venta_bouquetera;
        }
        setValueToCeldaExcel($objSheet, $columnas[$pos + 2] . $row, round($total_valor_bqt, 2));
        setBgToCeldaExcel($objSheet, $columnas[$pos + 2] . $row, 'afffec');   // verde clarito

        /* TOTAL COSTOS */
        $row = 10;
        setValueToCeldaExcel($objSheet, 'A' . $row, 'TOTAL COSTOS');
        setBgToCeldaExcel($objSheet, 'A' . $row, 'e9ecef');   // gris
        $total_costos_operativos = 0;
        foreach ($resumen_costos as $pos => $item) {
            $costos_operativos = $item->mano_obra + $item->insumos + $item->fijos + $item->regalias + ($compra_flor[$pos]->tallos + $compra_flor[$pos]->exportada);
            setValueToCeldaExcel($objSheet, $columnas[$pos + 1] . $row, round($costos_operativos, 2));
            $total_costos_operativos += $costos_operativos;
        }
        setValueToCeldaExcel($objSheet, $columnas[$pos + 2] . $row, round($total_costos_operativos, 2));
        setBgToCeldaExcel($objSheet, $columnas[$pos + 2] . $row, 'e9ecef');   // gris

        /* COSTOS MO */
        $row = 11;
        setValueToCeldaExcel($objSheet, 'A' . $row, 'MANO OBRA');
        setBgToCeldaExcel($objSheet, 'A' . $row, 'afffec');   // verde clarito
        $total_costos_mo = 0;
        foreach ($resumen_costos as $pos => $item) {
            setValueToCeldaExcel($objSheet, $columnas[$pos + 1] . $row, round($item->mano_obra, 2));
            $total_costos_mo += $item->mano_obra;
        }
        setValueToCeldaExcel($objSheet, $columnas[$pos + 2] . $row, round($total_costos_mo, 2));
        setBgToCeldaExcel($objSheet, $columnas[$pos + 2] . $row, 'afffec');   // verde clarito

        /* COSTOS INSUMOS */
        $row = 12;
        setValueToCeldaExcel($objSheet, 'A' . $row, 'INSUMOS');
        setBgToCeldaExcel($objSheet, 'A' . $row, 'afffec');   // verde clarito
        $total_costos_insumos = 0;
        foreach ($resumen_costos as $pos => $item) {
            setValueToCeldaExcel($objSheet, $columnas[$pos + 1] . $row, round($item->insumos, 2));
            $total_costos_insumos += $item->insumos;
        }
        setValueToCeldaExcel($objSheet, $columnas[$pos + 2] . $row, round($total_costos_insumos, 2));
        setBgToCeldaExcel($objSheet, $columnas[$pos + 2] . $row, 'afffec');   // verde clarito

        /* COSTOS FIJOS */
        $row = 13;
        setValueToCeldaExcel($objSheet, 'A' . $row, 'FIJOS');
        setBgToCeldaExcel($objSheet, 'A' . $row, 'afffec');   // verde clarito
        $total_costos_fijos = 0;
        foreach ($resumen_costos as $pos => $item) {
            setValueToCeldaExcel($objSheet, $columnas[$pos + 1] . $row, round($item->fijos, 2));
            $total_costos_fijos += $item->fijos;
        }
        setValueToCeldaExcel($objSheet, $columnas[$pos + 2] . $row, round($total_costos_fijos, 2));
        setBgToCeldaExcel($objSheet, $columnas[$pos + 2] . $row, 'afffec');   // verde clarito

        /* COSTOS REGALIAS */
        $row = 14;
        setValueToCeldaExcel($objSheet, 'A' . $row, 'REGALÍAS');
        setBgToCeldaExcel($objSheet, 'A' . $row, 'afffec');   // verde clarito
        $total_costos_regalias = 0;
        foreach ($resumen_costos as $pos => $item) {
            setValueToCeldaExcel($objSheet, $columnas[$pos + 1] . $row, round($item->regalias, 2));
            $total_costos_regalias += $item->regalias;
        }
        setValueToCeldaExcel($objSheet, $columnas[$pos + 2] . $row, round($total_costos_regalias, 2));
        setBgToCeldaExcel($objSheet, $columnas[$pos + 2] . $row, 'afffec');   // verde clarito

        /* COSTOS COMPRA de FLOR */
        $row = 15;
        setValueToCeldaExcel($objSheet, 'A' . $row, 'COMPRA de FLOR');
        setBgToCeldaExcel($objSheet, 'A' . $row, 'afffec');   // verde clarito
        $total_costos_compra_flor = 0;
        foreach ($compra_flor as $pos => $item) {
            setValueToCeldaExcel($objSheet, $columnas[$pos + 1] . $row, round($item->tallos + $item->exportada, 2));
            $total_costos_compra_flor += $item->tallos + $item->exportada;
        }
        setValueToCeldaExcel($objSheet, $columnas[$pos + 2] . $row, round($total_costos_compra_flor, 2));
        setBgToCeldaExcel($objSheet, $columnas[$pos + 2] . $row, 'afffec');   // verde clarito

        /* EBITDA */
        $row = 16;
        setValueToCeldaExcel($objSheet, 'A' . $row, 'EBITDA');
        setBgToCeldaExcel($objSheet, 'A' . $row, 'e9ecef');   // gris
        foreach ($semanas as $pos => $item) {
            $ventas = $resumen_semanal[$pos]->venta + $resumen_semanal[$pos]->venta_bouquetera;
            $costos = $resumen_costos[$pos]->mano_obra + $resumen_costos[$pos]->insumos + $resumen_costos[$pos]->fijos + $resumen_costos[$pos]->regalias + ($compra_flor[$pos]->tallos + $compra_flor[$pos]->exportada);
            $ebitda = $ventas - $costos;
            setValueToCeldaExcel($objSheet, $columnas[$pos + 1] . $row, round($ebitda, 2));
            setColorTextToCeldaExcel($objSheet, $columnas[$pos + 1] . $row, $ebitda < 0 ? 'd01c62' : '00b388');
        }
        $ebitda = $total_valor_venta - ($total_costos_operativos);
        setValueToCeldaExcel($objSheet, $columnas[$pos + 2] . $row, round($ebitda, 2));
        setBgToCeldaExcel($objSheet, $columnas[$pos + 2] . $row, 'e9ecef');   // gris
        setColorTextToCeldaExcel($objSheet, $columnas[$pos + 2] . $row, $ebitda < 0 ? 'd01c62' : '00b388');

        /* UNOSOFT */
        $row = 17;
        $objSheet->mergeCells('A' . $row . ':' . $columnas[$pos + 2] . $row);
        setValueToCeldaExcel($objSheet, 'A' . $row, 'UNOSOFT');
        setBgToCeldaExcel($objSheet, 'A' . $row, 'c4c4ff');   // gris fuerte

        /* Nacional */
        $row = 18;
        setValueToCeldaExcel($objSheet, 'A' . $row, 'Nacional');
        setBgToCeldaExcel($objSheet, 'A' . $row, 'e9ecef');   // gris
        $total_nacionales = 0;
        foreach ($resumen_semanal as $pos => $item) {
            setValueToCeldaExcel($objSheet, $columnas[$pos + 1] . $row, round($item->nacionales, 2));
            $total_nacionales += $item->nacionales;
        }
        setValueToCeldaExcel($objSheet, $columnas[$pos + 2] . $row, round($total_nacionales, 2));
        setBgToCeldaExcel($objSheet, $columnas[$pos + 2] . $row, 'e9ecef');   // gris

        /* % Nacional */
        $row = 19;
        setValueToCeldaExcel($objSheet, 'A' . $row, '% Nacional');
        setBgToCeldaExcel($objSheet, 'A' . $row, 'e9ecef');   // gris
        foreach ($resumen_semanal as $pos => $item) {
            $value = porcentaje($item->nacionales, ($item->tallos_exportables + $compra_flor[$pos]->tallos_bqt), 1);
            setValueToCeldaExcel($objSheet, $columnas[$pos + 1] . $row, round($value, 2));
        }
        setValueToCeldaExcel($objSheet, $columnas[$pos + 2] . $row, round(porcentaje($total_nacionales, $total_tallos_producidos, 1), 2));
        setBgToCeldaExcel($objSheet, $columnas[$pos + 2] . $row, 'e9ecef');   // gris

        /* Bajas */
        $row = 20;
        setValueToCeldaExcel($objSheet, 'A' . $row, 'Bajas');
        setBgToCeldaExcel($objSheet, 'A' . $row, 'e9ecef');   // gris
        $total_bajas = 0;
        foreach ($resumen_semanal as $pos => $item) {
            setValueToCeldaExcel($objSheet, $columnas[$pos + 1] . $row, round($item->bajas, 2));
            $total_bajas += $item->bajas;
        }
        setValueToCeldaExcel($objSheet, $columnas[$pos + 2] . $row, round($total_bajas, 2));
        setBgToCeldaExcel($objSheet, $columnas[$pos + 2] . $row, 'e9ecef');   // gris

        /* Compra Flor Bqt */
        $row = 21;
        setValueToCeldaExcel($objSheet, 'A' . $row, 'Compra Flor Bqt');
        setBgToCeldaExcel($objSheet, 'A' . $row, 'e9ecef');   // gris
        $total_compra_flor_bqt = 0;
        foreach ($compra_flor as $pos => $item) {
            setValueToCeldaExcel($objSheet, $columnas[$pos + 1] . $row, round($item->tallos, 2));
            $total_compra_flor_bqt += $item->tallos;
        }
        setValueToCeldaExcel($objSheet, $columnas[$pos + 2] . $row, round($total_compra_flor_bqt, 2));
        setBgToCeldaExcel($objSheet, $columnas[$pos + 2] . $row, 'e9ecef');   // gris

        /* Compras Flor Export */
        $row = 22;
        setValueToCeldaExcel($objSheet, 'A' . $row, 'Compras Flor Export');
        setBgToCeldaExcel($objSheet, 'A' . $row, 'e9ecef');   // gris
        $total_compra_flor_export = 0;
        foreach ($compra_flor as $pos => $item) {
            setValueToCeldaExcel($objSheet, $columnas[$pos + 1] . $row, round($item->exportada, 2));
            $total_compra_flor_export += $item->exportada;
        }
        setValueToCeldaExcel($objSheet, $columnas[$pos + 2] . $row, round($total_compra_flor_export, 2));
        setBgToCeldaExcel($objSheet, $columnas[$pos + 2] . $row, 'e9ecef');   // gris

        /* Tallos Vendidos */
        $row = 23;
        setValueToCeldaExcel($objSheet, 'A' . $row, 'Tallos Vendidos');
        setBgToCeldaExcel($objSheet, 'A' . $row, 'e9ecef');   // gris
        $total_tallos_vendidos = 0;
        foreach ($resumen_semanal as $pos => $item) {
            setValueToCeldaExcel($objSheet, $columnas[$pos + 1] . $row, round($item->tallos_vendidos, 2));
            $total_tallos_vendidos += $item->tallos_vendidos;
        }
        setValueToCeldaExcel($objSheet, $columnas[$pos + 2] . $row, round($total_tallos_vendidos, 2));
        setBgToCeldaExcel($objSheet, $columnas[$pos + 2] . $row, 'e9ecef');   // gris

        setBorderToCeldaExcel($objSheet, 'A1:' . $columnas[$col + 2] . $row);
        for ($i = 0; $i <= $col + 5; $i++)
            $objSheet->getColumnDimension($columnas[$i])->setAutoSize(true);
    }

    /* ==================================== IMPORTAR ===================================== */
    public function costos_importar(Request $request)
    {
        return view('adminlte.gestion.costos.costos_importar', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
        ]);
    }

    public function importar_file_costos(Request $request)
    {
        ini_set('max_execution_time', env('MAX_EXECUTION_TIME'));
        $finca_actual = $request->finca_actual != 'T' ? $request->finca_actual : getFincasPropias()[0]->id_empresa;
        $valida = Validator::make($request->all(), [
            'file_costos' => 'required',
        ]);
        $msg = '<div class="alert alert-info text-center">Se ha importado el archivo, en menos de una hora se reflejarán los datos en el sistema</div>';
        $success = true;
        if (!$valida->fails()) {
            $archivo = $request->file_costos;
            $extension = $archivo->getClientOriginalExtension();
            $nombre_archivo = "finca_" . $finca_actual . "-costos_" . $request->concepto_importar . "." . $extension;
            $r1 = Almacenamiento::disk('pdf_loads')->put($nombre_archivo, \File::get($archivo));

            $url = public_path('storage\pdf_loads\\' . $nombre_archivo);
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

    public function importar_file_costos_details(Request $request)
    {
        ini_set('max_execution_time', env('MAX_EXECUTION_TIME'));
        $finca_actual = getFincaActiva();
        $valida = Validator::make($request->all(), [
            'file_costos_details' => 'required',
        ]);
        $msg = '<div class="alert alert-info text-center">Se ha importado el archivo, en menos de una hora se reflejarán los datos en el sistema</div>';
        $success = true;
        if (!$valida->fails()) {
            try {
                $archivo = $request->file_costos_details;
                $extension = $archivo->getClientOriginalExtension();
                $nombre_archivo = "finca_" . $finca_actual . "-costos_" . $request->concepto_importar_details . "_" . $request->sobreescribir_importar_details . "_details." . $extension;
                $r1 = Almacenamiento::disk('pdf_loads')->put($nombre_archivo, \File::get($archivo));

                //$url = public_path('storage/pdf_loads/' . $nombre_archivo);

                //$document = \PHPExcel_IOFactory::load($url);
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

    public function descargar_plantilla(Request $request)
    {
        if ($request->c == 'I')
            $fileName = basename('plantilla_costos_insumos_exportcalas.xlsx');
        else
            $fileName = basename('plantilla_costos_mano_obra_exportcalas.xlsx');
        $filePath = public_path('storage/' . $fileName);
        if (!empty($fileName) && file_exists($filePath)) {
            // Define headers
            header("Cache-Control: public");
            header("Content-Description: File Transfer");
            header("Content-Disposition: attachment; filename=$fileName");
            header("Content-Type: application/zip");
            header("Content-Transfer-Encoding: binary");

            // Read the file
            readfile($filePath);
            exit;
        }
    }

    /* =================================== MANO OBRA ======================================= */
    public function gestion_mano_obra(Request $request)
    {
        $finca = getFincaActiva();
        return view('adminlte.gestion.costos.mano_obra.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'areas' => Area::All()->where('id_empresa', $finca)->sortBy('nombre'),
            'actividades' => Actividad::All()->where('id_empresa', $finca)->sortBy('nombre'),
            'manos_obra' => ManoObra::All()->where('id_empresa', $finca)->sortBy('nombre'),
        ]);
    }

    public function store_mano_obra(Request $request)
    {
        $finca = getFincaActiva();
        $valida = Validator::make($request->all(), [
            'nombre' => 'required|max:250',
        ], [
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.max' => 'El nombre es muy grande',
        ]);
        $msg = '';
        if (!$valida->fails()) {
            $model = new ManoObra();
            $model->nombre = str_limit(mb_strtoupper(espacios($request->nombre)), 250);
            $model->fecha_registro = date('Y-m-d H:i:s');
            $model->id_empresa = $finca;

            if ($model->save()) {
                $model = ManoObra::All()->last();
                $success = true;
                bitacora('mano_obra', $model->id_mano_obra, 'I', 'Inserción satisfactoria de una nueva mano de obra');
            } else {
                $success = false;
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
            'success' => $success,
            'mensaje' => $msg,
        ];
    }

    public function importar_mano_obra(Request $request)
    {
        return view('adminlte.gestion.costos.mano_obra.forms.importar_producto', [
        ]);
    }

    public function importar_file_mano_obra(Request $request)
    {
        ini_set('max_execution_time', env('MAX_EXECUTION_TIME'));
        $valida = Validator::make($request->all(), [
            'file_mano_obra' => 'required',
        ]);
        $msg = '';
        $success = true;
        if (!$valida->fails()) {

            $document = PHPExcel_IOFactory::load($request->file_mano_obra);
            $activeSheetData = $document->getActiveSheet()->toArray(null, true, true, true);

            $titles = $activeSheetData[1];
            $finca = getFincaActiva();
            foreach ($activeSheetData as $pos_row => $row) {
                if ($pos_row > 1) {
                    if ($row['A'] != '') {
                        $nombre = str_limit(mb_strtoupper(espacios($row['A'])), 250);
                        if (count(ManoObra::All()->where('nombre', $nombre)) == 0) {
                            $model = new ManoObra();
                            $model->nombre = $nombre;
                            $model->fecha_registro = date('Y-m-d');
                            $model->id_empresa = $finca;

                            $model->save();
                            $model = ManoObra::All()->last();
                            bitacora('mano_obra', $model->id_mano_obra, 'I', 'Inserción satisfactoria de una nueva mano de obra');
                            $msg .= '<li class="bg-green">Se ha importado la mano de obra: "' . $nombre . '."</li>';
                        }
                    }
                }
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

    public function update_mano_obra(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'nombre' => 'required|max:250',
            'id_mano_obra' => 'required|',
        ], [
            'nombre.required' => 'El nombre es obligatorio',
            'id_mano_obra.required' => 'La mano de obra es obligatoria',
            'nombre.max' => 'El nombre es muy grande',
        ]);
        $msg = '';
        if (!$valida->fails()) {
            if (count(ManoObra::All()
                    ->where('nombre', '=', str_limit(mb_strtoupper(espacios($request->nombre)), 250))
                    ->where('id_empresa', getFincaActiva())
                    ->where('id_mano_obra', '!=', $request->id_mano_obra)) == 0) {
                $model = ManoObra::find($request->id_mano_obra);
                $model->nombre = str_limit(mb_strtoupper(espacios($request->nombre)), 250);

                if ($model->save()) {
                    $success = true;
                    bitacora('mano_obra', $model->id_mano_obra, 'U', 'Actualización satisfactoria de una mano de obra');
                } else {
                    $success = false;
                }
            } else {
                $success = false;
                $msg = '<div class="alert alert-warning text-center">' .
                    '<p> La mano de obra "' . espacios($request->nombre) . '" ya se encuentra en el sistema</p>'
                    . '</div>';
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

    public function vincular_actividad_mano_obra(Request $request)
    {
        $finca = getFincaActiva();
        $actividad = Actividad::find($request->id);
        $manos_obra_vinc = [];
        foreach ($actividad->manos_obra->where('estado', 1) as $p) {
            array_push($manos_obra_vinc, $p->id_mano_obra);
        }

        return view('adminlte.gestion.costos.mano_obra.forms.vincular_actividad_mano_obra', [
            'actividad' => $actividad,
            'manos_obra_vinc' => $manos_obra_vinc,
            'manos_obra' => ManoObra::All()
                ->where('id_empresa', $finca)
                ->where('estado', 1)
                ->sortBy('nombre'),
        ]);
    }

    public function store_actividad_mano_obra(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'actividad' => 'required',
            'mano_obra' => 'required',
        ], [
            'actividad.required' => 'La actividad es obligatoria',
            'mano_obra.required' => 'La mano de obra es obligatorio',
        ]);
        $msg = '';
        $estado = 1;
        if (!$valida->fails()) {
            $model = ActividadManoObra::All()
                ->where('id_actividad', $request->actividad)
                ->where('id_mano_obra', $request->mano_obra)
                ->first();
            if ($model == '') {
                $model = new ActividadManoObra();
                $model->id_actividad = $request->actividad;
                $model->id_mano_obra = $request->mano_obra;
                $model->fecha_registro = date('Y-m-d H:i:s');

                if ($model->save()) {
                    $model = ActividadManoObra::All()->last();
                    $success = true;
                    bitacora('actividad_mano_obra', $model->actividad_mano_obra, 'I', 'Inserción satisfactoria de un nuevo vínculo actividad_mano_obra');
                } else {
                    $success = false;
                }
            } else {
                $model->estado = $model->estado == 1 ? 0 : 1;
                $estado = $model->estado;
                $success = true;

                $model->save();
                bitacora('mano_obra', $model->id_mano_obra, 'U', 'Modificacion satisfactoria del estado de una mano de obra');
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
            'success' => $success,
            'mensaje' => $msg,
            'estado' => $estado,
        ];
    }

    public function importar_file_act_mano_obra(Request $request)
    {
        ini_set('max_execution_time', env('MAX_EXECUTION_TIME'));
        $valida = Validator::make($request->all(), [
            'file_act_mano_obra' => 'required',
        ]);
        $msg = '';
        $success = true;
        $array_ids_mo = [];
        if (!$valida->fails()) {

            $document = PHPExcel_IOFactory::load($request->file_act_mano_obra);
            $activeSheetData = $document->getActiveSheet()->toArray(null, true, true, true);

            $titles = $activeSheetData[1];
            $finca = getFincaActiva();
            foreach ($activeSheetData as $pos_row => $row) {
                if ($pos_row > 1) {
                    if ($row['A'] != '') {
                        $nombre = str_limit(mb_strtoupper(espacios($row['B'])), 250);
                        $mano_obra = ManoObra::All()
                            ->where('id_empresa', $finca)
                            ->where('nombre', $nombre)
                            ->first();

                        if ($mano_obra != '') {
                            $model = ActividadManoObra::All()
                                ->where('id_actividad', $request->id_actividad)
                                ->where('id_mano_obra', $mano_obra->id_mano_obra)
                                ->first();
                            if ($model == '') {
                                $model = new ActividadManoObra();
                                $model->id_actividad = $request->id_actividad;
                                $model->id_mano_obra = $mano_obra->id_mano_obra;
                                $model->fecha_registro = date('Y-m-d H:i:s');

                                if ($model->save()) {
                                    $model = ActividadManoObra::All()->last();
                                    $success = true;
                                    bitacora('actividad_mano_obra', $model->actividad_mano_obra, 'I', 'Inserción satisfactoria de un nuevo vínculo actividad_mano_obra');
                                } else {
                                    $success = false;
                                }
                            } else {
                                $model->estado = 1;
                                $success = true;

                                $model->save();
                                bitacora('mano_obra', $model->id_mano_obra, 'U', 'Modificación satisfactoria del estado de una mano de obra');
                            }
                            array_push($array_ids_mo, $mano_obra->id_mano_obra);
                            $msg .= '<li class="bg-green">Se ha vinculado la mano de obra: "' . $nombre . '."</li>';
                        }
                    }
                }
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
            'ids' => $array_ids_mo,
        ];
    }

    public function delete_mano_obra(Request $request)
    {
        $model = ManoObra::find($request->id_mano_obra);
        $model->estado = $model->estado == 1 ? 0 : 1;
        $model->save();
        bitacora('mano_obra', $model->id_mano_obra, 'U', 'Modificacion satisfactoria del estado de una mano de obra');

        return [
            'success' => true,
            'mensaje' => '',
        ];
    }

    /* ----------------------------------- REPORTE -------------------------------------------- */
    public function reporte_mano_obra(Request $request)
    {
        $finca = getFincaActiva();
        $semana_actual = getSemanaByDate(opDiasFecha('-', 7, date('Y-m-d')));
        $semana_desde = getSemanaByDate(opDiasFecha('-', 42, date('Y-m-d')));
        return view('adminlte.gestion.costos.mano_obra.reporte.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'areas' => Area::where('estado', 1)->where('id_empresa', $finca)->get(),
            'semana_actual' => $semana_actual,
            'semana_desde' => $semana_desde
        ]);
    }

    public function listar_reporte_mano_obra(Request $request)
    {
        $finca_actual = getFincaActiva();
        $semanas = DB::table('costos_semana_mano_obra')
            ->select('codigo_semana')->distinct()
            ->where('codigo_semana', '>=', $request->desde)
            ->where('codigo_semana', '<=', $request->hasta)
            ->where('id_empresa', $finca_actual)
            ->orderBy('codigo_semana')
            ->get();
        $area = Area::find($request->area);
        $actividad = Actividad::find($request->actividad);

        $ids = DB::table('costos_semana_mano_obra as c')
            ->select('c.id_actividad_mano_obra', 'mo.nombre')->distinct()
            ->join('actividad_mano_obra as ap', 'c.id_actividad_mano_obra', '=', 'ap.id_actividad_mano_obra')
            ->join('mano_obra as mo', 'mo.id_mano_obra', '=', 'ap.id_mano_obra');
        if ($actividad != '')   // una actividad en especifico
            $ids = $ids
                ->where('ap.id_actividad', $actividad->id_actividad);
        else if ($area != '') {
            $ids = $ids
                ->join('actividad as a', 'ap.id_actividad', '=', 'a.id_actividad')
                ->where('a.id_area', $area->id_area);
        }
        if ($request->criterio == 'V')  // dinero
            $ids = $ids->where('c.valor', '>', 0);
        else    // cantidad
            $ids = $ids->where('c.cantidad', '>', 0);
        $ids = $ids
            ->where('c.codigo_semana', '>=', $request->desde)
            ->where('c.codigo_semana', '<=', $request->hasta);
        if ($finca_actual != 'T')
            $ids = $ids->where('c.id_empresa', $finca_actual);
        $ids = $ids->orderBy('mo.nombre')->get();

        $list_ids = [];
        $matriz = [];
        foreach ($ids as $item) {
            $query = CostosSemanaManoObra::where('codigo_semana', '>=', $request->desde)
                ->where('codigo_semana', '<=', $request->hasta)
                ->where('id_actividad_mano_obra', $item->id_actividad_mano_obra);
            if ($finca_actual != 'T')
                $query = $query->where('id_empresa', $finca_actual);
            $query = $query->orderBy('codigo_semana')->get();

            array_push($matriz, $query);
            array_push($list_ids, $item->id_actividad_mano_obra);
        }

        $totales = DB::table('costos_semana_mano_obra')
            ->select(DB::raw('sum(valor) as cant'), 'codigo_semana as semana')
            ->where('codigo_semana', '>=', $request->desde)
            ->where('codigo_semana', '<=', $request->hasta)
            ->whereIn('id_actividad_mano_obra', $list_ids)
            ->where('id_empresa', $finca_actual)
            ->groupBy('codigo_semana')
            ->get();

        return view('adminlte.gestion.costos.mano_obra.reporte.partials.listado', [
            'semanas' => $semanas,
            'area' => $area,
            'actividad' => $actividad,
            'criterio' => $request->criterio,
            'matriz' => $matriz,
            'totales' => $totales,
        ]);
    }

    public function reporte_insumos(Request $request)
    {
        $finca = getFincaActiva();
        $semana_actual = getSemanaByDate(opDiasFecha('-', 7, date('Y-m-d')));
        $semana_desde = getSemanaByDate(opDiasFecha('-', 42, date('Y-m-d')));
        return view('adminlte.gestion.costos.insumo.reporte.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'areas' => Area::where('estado', 1)->where('id_empresa', $finca)->get(),
            'semana_actual' => $semana_actual,
            'semana_desde' => $semana_desde
        ]);
    }

    public function listar_reporte_insumos(Request $request)
    {
        $finca_actual = getFincaActiva();
        $semanas = DB::table('costos_semana')
            ->select('codigo_semana')->distinct()
            ->where('id_empresa', $finca_actual)
            ->where('codigo_semana', '>=', $request->desde)
            ->where('codigo_semana', '<=', $request->hasta)
            ->where('id_empresa', $finca_actual)
            ->orderBy('codigo_semana')
            ->get();
        $area = Area::find($request->area);
        $actividad = Actividad::find($request->actividad);

        $ids = DB::table('costos_semana as c')
            ->join('actividad_producto as ap', 'c.id_actividad_producto', '=', 'ap.id_actividad_producto')
            ->join('producto as p', 'p.id_producto', '=', 'ap.id_producto')
            ->select('c.id_actividad_producto')->distinct();
        if ($actividad != '')   // una actividad en especifico
            $ids = $ids
                ->where('ap.id_actividad', $actividad->id_actividad);
        else if ($area != '') {
            $ids = $ids
                ->join('actividad as a', 'ap.id_actividad', '=', 'a.id_actividad')
                ->where('a.id_area', $area->id_area);
        }
        if ($request->criterio == 'V')  // dinero
            $ids = $ids->where('c.valor', '>', 0);
        else    // cantidad
            $ids = $ids->where('c.cantidad', '>', 0);
        if ($finca_actual != 'T') {
            $ids = $ids->where('c.id_empresa', $finca_actual);
        }
        $ids = $ids
            ->where('c.codigo_semana', '>=', $request->desde)
            ->where('c.codigo_semana', '<=', $request->hasta)
            ->orderBy('p.nombre')
            ->get();

        $list_ids = [];
        foreach ($ids as $item)
            array_push($list_ids, $item->id_actividad_producto);

        $productos = DB::table('costos_semana as c')
            ->join('actividad_producto as ap', 'ap.id_actividad_producto', '=', 'c.id_actividad_producto')
            ->join('producto as p', 'p.id_producto', '=', 'ap.id_producto')
            ->select('p.nombre', DB::raw('sum(c.valor) as valor'))
            ->where('c.codigo_semana', '>=', $request->desde)
            ->where('c.codigo_semana', '<=', $request->hasta)
            ->whereIn('c.id_actividad_producto', $list_ids)
            ->groupBy('p.nombre')
            ->orderBy('p.nombre')
            ->get();

        $matriz = [];
        foreach ($productos as $p) {
            $valores = [];
            foreach ($semanas as $sem) {
                $val = DB::table('costos_semana as c')
                    ->join('actividad_producto as ap', 'ap.id_actividad_producto', '=', 'c.id_actividad_producto')
                    ->join('producto as p', 'p.id_producto', '=', 'ap.id_producto')
                    ->select(DB::raw('sum(c.valor) as valor'))
                    ->where('c.codigo_semana', $sem->codigo_semana)
                    ->whereIn('c.id_actividad_producto', $list_ids)
                    ->where('p.nombre', $p->nombre)
                    ->get()[0]->valor;
                $valores[] = $val;
            }
            $matriz[] = [
                'producto' => $p,
                'valores' => $valores,
            ];
        }

        $totales = DB::table('costos_semana')
            ->select(DB::raw('sum(valor) as cant'), 'codigo_semana as semana')
            ->where('codigo_semana', '>=', $request->desde)
            ->where('codigo_semana', '<=', $request->hasta)
            ->whereIn('id_actividad_producto', $list_ids)
            ->where('id_empresa', $finca_actual)
            ->groupBy('codigo_semana')
            ->get();

        return view('adminlte.gestion.costos.insumo.reporte.partials.listado', [
            'semanas' => $semanas,
            'area' => $area,
            'actividad' => $actividad,
            'criterio' => $request->criterio,
            'matriz' => $matriz,
            'totales' => $totales,
        ]);
    }

    public function costos_generales(Request $request)
    {
        $semana_actual = getSemanaByDate(opDiasFecha('-', 7, date('Y-m-d')));
        $semana_desde = getSemanaByDate(opDiasFecha('-', 42, date('Y-m-d')));
        return view('adminlte.gestion.costos.generales.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'semana_actual' => $semana_actual,
            'semana_desde' => $semana_desde
        ]);
    }

    public function listar_reporte_general(Request $request)
    {
        $finca = getFincaActiva();
        $semanas = DB::table('semana')
            ->select('codigo', 'fecha_inicial', 'fecha_final')->distinct()
            ->where('codigo', '>=', $request->desde)
            ->where('codigo', '<=', $request->hasta)
            ->orderBy('codigo')
            ->get();
        $resumen_semanal = DB::table('resumen_total_semanal_exportcalas')
            ->select('semana',
                DB::raw('sum(tallos_cosechados) as tallos_cosechados'),
                DB::raw('sum(tallos_exportables) as tallos_exportables'),
                DB::raw('sum(bouquetera) as bouquetera'),
                DB::raw('sum(venta) as venta'),
                DB::raw('sum(nacional) as nacionales'),
                DB::raw('sum(bajas) as bajas'),
                DB::raw('sum(tallos_vendidos) as tallos_vendidos'),
                DB::raw('sum(venta_bouquetera) as venta_bouquetera'))
            ->where('id_empresa', $finca)
            ->where('semana', '>=', $request->desde)
            ->where('semana', '<=', $request->hasta)
            ->groupBy('semana')
            ->orderBy('semana')
            ->get();
        /*$venta_bqt_total = DB::table('resumen_total_semanal_exportcalas')
            ->select('semana', DB::raw('sum(venta_bouquetera) as venta_bouquetera'))
            ->where('semana', '>=', $request->desde)
            ->where('semana', '<=', $request->hasta)
            ->groupBy('semana')
            ->orderBy('semana')
            ->get();*/
        $resumen_costos = DB::table('resumen_costos_semanal')
            ->where('id_empresa', $finca)
            ->where('codigo_semana', '>=', $request->desde)
            ->where('codigo_semana', '<=', $request->hasta)
            ->orderBy('codigo_semana')
            ->get();
        $fincas = [$finca];
        if ($finca == 2)
            array_push($fincas, -1);
        $compra_flor = [];
        $tallos_bqt_total = [];
        foreach ($semanas as $sem) {
            $cant = DB::table('bouquetera')
                ->select(DB::raw('sum(precio * (tallos)) as tallos'),
                    DB::raw('sum(precio * (exportada)) as exportada'),
                    DB::raw('sum(tallos) as tallos_bqt'),
                    DB::raw('sum(exportada) as tallos_exportada'))
                ->where('fecha', '>=', $sem->fecha_inicial)
                ->where('fecha', '<=', $sem->fecha_final)
                ->whereIn('id_empresa', $fincas)
                ->get()[0];
            array_push($compra_flor, $cant);

            /*$cant = DB::table('bouquetera')
                ->select(DB::raw('sum(tallos) as tallos_bqt'),
                    DB::raw('sum(exportada) as tallos_exportada'))
                ->where('fecha', '>=', $sem->fecha_inicial)
                ->where('fecha', '<=', $sem->fecha_final)
                ->get()[0];
            array_push($tallos_bqt_total, $cant);*/
        }

        $resumen_area = [];
        $totales_costos_mo = [];
        foreach ($semanas as $sem) {
            $area_a = DB::table('ciclo as c')
                ->join('variedad as v', 'v.id_variedad', '=', 'c.id_variedad')
                ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                ->select(DB::raw('sum(c.area) as area'))
                ->where('v.estado', 1)
                ->where('p.estado', 1)
                ->where('c.estado', '=', 1)
                ->where('c.id_empresa', $finca)
                ->Where(function ($q) use ($sem) {
                    $q->where('c.fecha_fin', '>=', $sem->fecha_inicial)
                        ->where('c.fecha_fin', '<=', $sem->fecha_final)
                        ->orWhere(function ($q) use ($sem) {
                            $q->where('c.fecha_inicio', '>=', $sem->fecha_inicial)
                                ->where('c.fecha_inicio', '<=', $sem->fecha_final);
                        })
                        ->orWhere(function ($q) use ($sem) {
                            $q->where('c.fecha_inicio', '<', $sem->fecha_inicial)
                                ->where('c.fecha_fin', '>', $sem->fecha_final);
                        });
                })
                ->Where(function ($q) use ($sem) {
                    $q->where('p.tipo', 'P')
                        ->orWhere('p.tiene_ciclos', 1);
                })
                ->get()[0]->area;
            $area_b = DB::table('proy_no_perennes as proy')
                ->join('semana as s', 's.id_semana', '=', 'proy.id_semana')
                ->join('variedad as v', 'v.id_variedad', '=', 's.id_variedad')
                ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                ->select(DB::raw('sum(proy.area_produccion) as area_produccion'),
                    DB::raw('sum(proy.area_semana) as area_semana'))
                ->where('s.codigo', $sem->codigo)
                ->where('p.estado', 1)
                ->where('v.estado', 1)
                ->where('p.tiene_ciclos', 0)
                ->where('p.tipo', 'N')
                ->where('proy.id_empresa', $finca)
                ->get()[0]->area_produccion;
            array_push($resumen_area, $area_a + $area_b);

            $costos_mo = DB::table('costos_semana_mano_obra')
                ->select(DB::raw('sum(valor) as valor'),
                    DB::raw('sum(valor_50) as valor_50'),
                    DB::raw('sum(valor_100) as valor_100'))
                ->where('codigo_semana', $sem->codigo)
                ->where('id_empresa', $finca)
                ->get()[0];
            $totales_costos_mo[] = $costos_mo;
        }

        $areas = Area::where('estado', 1)->where('id_empresa', $finca)->get();
        $centros_costos = [];
        foreach ($areas as $a) {
            array_push($centros_costos, [
                'area' => $a,
                'insumos' => DB::table('costos_semana as cs')
                    ->join('actividad_producto as ap', 'ap.id_actividad_producto', '=', 'cs.id_actividad_producto')
                    ->join('actividad as a', 'a.id_actividad', '=', 'ap.id_actividad')
                    ->select(DB::raw('sum(cs.valor) as valor'), 'cs.codigo_semana')
                    ->where('cs.id_empresa', $finca)
                    ->where('a.id_area', $a->id_area)
                    ->where('cs.codigo_semana', '>=', $request->desde)
                    ->where('cs.codigo_semana', '<=', $request->hasta)
                    ->groupBy('cs.codigo_semana')
                    ->orderBy('cs.codigo_semana')
                    ->get(),
                'mano_obra' => DB::table('costos_semana_mano_obra as cs')
                    ->join('actividad_mano_obra as amo', 'amo.id_actividad_mano_obra', '=', 'cs.id_actividad_mano_obra')
                    ->join('actividad as a', 'a.id_actividad', '=', 'amo.id_actividad')
                    ->select('cs.codigo_semana',
                        DB::raw('sum(cs.valor) as valor'),
                        DB::raw('sum(cs.cantidad) as cantidad'),
                        DB::raw('sum(cs.valor_50) as valor_50'),
                        DB::raw('sum(cs.valor_100) as valor_100'))
                    ->where('cs.id_empresa', $finca)
                    ->where('a.id_area', $a->id_area)
                    ->where('cs.codigo_semana', '>=', $request->desde)
                    ->where('cs.codigo_semana', '<=', $request->hasta)
                    ->groupBy('cs.codigo_semana')
                    ->orderBy('cs.codigo_semana')
                    ->get(),
                'otros_gastos' => DB::table('otros_gastos')
                    ->where('id_empresa', $finca)
                    ->where('id_area', $a->id_area)
                    ->where('codigo_semana', '>=', $request->desde)
                    ->where('codigo_semana', '<=', $request->hasta)
                    ->orderBy('codigo_semana')
                    ->get(),
            ]);
        }

        $indicadores_4_semanas = DB::table('indicadores_4_semanas')
            ->where('semana', '>=', $request->desde)
            ->where('semana', '<=', $request->hasta)
            ->where('id_empresa', $finca)
            ->orderBy('semana')
            ->get();

        return view('adminlte.gestion.costos.generales.partials.listado', [
            'semanas' => $semanas,
            'resumen_semanal' => $resumen_semanal,
            //'venta_bqt_total' => $venta_bqt_total,
            'resumen_costos' => $resumen_costos,
            'resumen_area' => $resumen_area,
            'centros_costos' => $centros_costos,
            'compra_flor' => $compra_flor,
            'tallos_bqt_total' => $tallos_bqt_total,
            'indicadores_4_semanas' => $indicadores_4_semanas,
            'totales_costos_mo' => $totales_costos_mo,
        ]);
    }

    public function corregir_costos_mano_obra(Request $request)
    {
        $finca = getFincaActiva();
        $semanas = DB::table('semana')
            ->select('codigo', 'fecha_inicial', 'fecha_final')->distinct()
            ->where('estado', 1)
            ->where('codigo', '>=', $request->desde)
            ->where('codigo', '<=', $request->hasta)
            ->get();
        foreach ($semanas as $pos_sem => $sem) {
            $model = CostosSemanaManoObra::All()
                ->where('codigo_semana', $sem->codigo)
                ->where('id_actividad_mano_obra', $request->act_mo)
                ->where('id_empresa', $finca)
                ->first();
            if ($model == '') {
                $model = new CostosSemanaManoObra();
                $model->id_actividad_mano_obra = $request->act_mo;
                $model->codigo_semana = $sem->codigo;
                $model->id_empresa = $finca;
                $model->valor = 0;
                $model->cantidad = 0;
                $model->save();
            }
        }

        return [
            'success' => true,
            'mensaje' => '',
        ];
    }

    public function corregir_costos_insumos(Request $request)
    {
        $finca = getFincaActiva();
        $semanas = DB::table('semana')
            ->select('codigo', 'fecha_inicial', 'fecha_final')->distinct()
            ->where('estado', 1)
            ->where('codigo', '>=', $request->desde)
            ->where('codigo', '<=', $request->hasta)
            ->get();
        foreach ($semanas as $pos_sem => $sem) {
            $model = CostosSemana::All()
                ->where('codigo_semana', $sem->codigo)
                ->where('id_actividad_producto', $request->act_prod)
                ->where('id_empresa', $finca)
                ->first();
            if ($model == '') {
                $model = new CostosSemana();
                $model->id_actividad_producto = $request->act_prod;
                $model->codigo_semana = $sem->codigo;
                $model->id_empresa = $finca;
                $model->valor = 0;
                $model->cantidad = 0;
                $model->save();
            }
        }

        return [
            'success' => true,
            'mensaje' => '',
        ];
    }

    /* =================================== OTROS GASTOS ======================================= */
    public function otros_gastos(Request $request)
    {
        $finca_actual = getFincaActiva();
        $area = Area::find($request->area);
        $semana_actual = getSemanaByDate(date('Y-m-d'));
        return view('adminlte.gestion.costos.mano_obra.forms.otros_gastos', [
            'area' => $area,
            'otros_gastos' => $area->otrosGastosBySemana($semana_actual->codigo, $finca_actual),
            'semana_actual' => $semana_actual,
        ]);
    }

    public function store_otros_gastos(Request $request)
    {
        $finca_actual = getFincaActiva();
        $valida = Validator::make($request->all(), [
            'id_area' => 'required',
            'semana' => 'required',
            'gip' => 'required',
            'ga' => 'required',
        ], [
            'semana.required' => 'La semana es obligatoria',
            'id_area.required' => 'El área es obligatoria',
            'gip.required' => 'El gip es obligatoria',
            'ga.required' => 'El ga es obligatoria',
        ]);
        if (!$valida->fails()) {
            $semana_actual = getSemanaByDate(date('Y-m-d'));
            $semanas = DB::table('semana')
                ->select('codigo')->distinct()
                ->where('estado', 1)
                ->where('codigo', '>=', $request->semana)
                ->where('codigo', '<=', $semana_actual->codigo)
                ->get();
            foreach ($semanas as $sem) {
                $model = OtrosGastos::All()
                    ->where('id_area', $request->id_area)
                    ->where('codigo_semana', $sem->codigo)
                    ->where('id_empresa', $finca_actual)
                    ->first();
                if ($model == '') {
                    $model = new OtrosGastos();
                    $model->id_area = $request->id_area;
                    $model->codigo_semana = $sem->codigo;
                    $model->id_empresa = $finca_actual;
                }
                $model->gip = $request->gip;
                $model->ga = $request->ga;
                $model->regalias = $request->regalias;

                if ($model->save()) {
                    $success = true;
                    $msg = '<div class="alert alert-success text-center">' .
                        '<p> Se han guardado los otros gastos satisfactoriamente</p>'
                        . '</div>';
                } else {
                    $success = false;
                    $msg = '<div class="alert alert-warning text-center">' .
                        '<p> Ha ocurrido un problema al guardar la información al sistema</p>'
                        . '</div>';
                    return [
                        'mensaje' => $msg,
                        'success' => $success
                    ];
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

    public function buscar_otros_gastos(Request $request)
    {
        $finca_actual = getFincaActiva();
        $finca_actual = $finca_actual != 'T' ? $finca_actual : getUsuario(Session::get('id_usuario'))->finca_activa;
        $area = Area::find($request->id_area);
        $costos = $area->otrosGastosBySemana($request->semana, $finca_actual);
        //dd($finca_actual, $area->id_area, $request->semana, $costos);
        return [
            'gip' => $costos != '' ? $costos->gip : 0,
            'ga' => $costos != '' ? $costos->ga : 0,
            'regalias' => $costos != '' ? $costos->regalias : 0,
        ];
    }
}
