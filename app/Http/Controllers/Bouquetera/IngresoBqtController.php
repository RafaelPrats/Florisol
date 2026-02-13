<?php

namespace yura\Http\Controllers\Bouquetera;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use yura\Http\Controllers\Controller;
use yura\Jobs\jobActualizarBouquetera;
use yura\Modelos\Bouquetera;
use yura\Modelos\ConfiguracionEmpresa;
use yura\Modelos\Submenu;
use Validator;
use Storage as Almacenamiento;

class IngresoBqtController extends Controller
{
    public function inicio(Request $request)
    {
        return view('adminlte.gestion.bouquetera.ingreso.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'plantas' => getPlantas(),
            'fincas' => ConfiguracionEmpresa::All(),
        ]);
    }

    public function listar_ingresos_bqt(Request $request)
    {
        $listado = DB::table('bouquetera as b')
            ->join('variedad as v', 'v.id_variedad', '=', 'b.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->leftJoin('configuracion_empresa as e', 'e.id_configuracion_empresa', '=', 'b.id_empresa')
            ->select('b.id_empresa', 'v.nombre as nombre_variedad', 'p.nombre as nombre_planta', 'e.nombre as nombre_finca',
                'b.id_bouquetera', 'b.tallos', 'b.precio', 'b.exportada')->distinct()
            ->where('b.fecha', $request->fecha);
        if ($request->planta != 'T')
            $listado = $listado->where('v.id_planta', $request->planta);
        if ($request->variedad != 'T')
            $listado = $listado->where('b.id_variedad', $request->variedad);
        if ($request->finca != 'T')
            $listado = $listado->where('b.id_empresa', $request->finca);
        $listado = $listado->orderBy('e.nombre')
            ->orderBy('v.nombre')
            ->orderBy('b.tallos')
            ->get();
        return view('adminlte.gestion.bouquetera.ingreso.partials.listado', [
            'listado' => $listado
        ]);
    }

    public function store_bqt(Request $request)
    {
        $semana = getSemanaByDate($request->fecha);
        foreach ($request->data as $d) {
            $model = new Bouquetera();
            $model->id_planta = $d['planta'];
            $model->id_variedad = $d['variedad'];
            $model->id_empresa = $d['finca'];
            $model->tallos = $d['tallos'];
            $model->precio = $d['precio'];
            $model->exportada = $d['exportada'];
            $model->fecha = $request->fecha;
            if ($model->save()) {
                $model = Bouquetera::All()->last();
                bitacora('bouquetera', $model->id_bouquetera, 'I', 'Insert nueva bouquetera');

                /* ----------------- ACTUALIZAR RESUMEN_TOTAL_SEMANAL_EXPORTCALAS --------------- */
                if ($d['finca'] > 0)
                    jobActualizarBouquetera::dispatch($d['variedad'], $semana, $d['finca'])->onQueue('bouquetera');
            } else {
                return [
                    'success' => false,
                    'mensaje' => '<div class="alert alert-danger text-center">No se ha podido guardar toda la información</div>',
                ];
            }
        }
        return [
            'success' => true,
            'mensaje' => '<div class="alert alert-success text-center">Se ha guardado la información satisfactoriamente</div>',
        ];
    }

    public function update_bqt(Request $request)
    {
        $model = Bouquetera::find($request->id);
        if ($model != '') {
            $model->tallos = $request->tallos;
            $model->precio = $request->precio;
            $model->exportada = $request->exportada;
            if ($model->save()) {
                bitacora('bouquetera', $model->id_bouquetera, 'U', 'Update bouquetera');
                $success = true;
                $msg = '<div class="alert alert-success text-center">Se ha actualizado la información satisfactoriamente</div>';

                /* ----------------- ACTUALIZAR RESUMEN_TOTAL_SEMANAL_EXPORTCALAS --------------- */
                if ($model->id_empresa > 0) {
                    $semana = getSemanaByDate($model->fecha);
                    jobActualizarBouquetera::dispatch($model->id_variedad, $semana, $model->id_empresa)->onQueue('bouquetera');
                }
            } else {
                $success = false;
                $msg = '<div class="alert alert-danger text-center">No se ha podido guardar la información</div>';
            }
        } else {
            $success = false;
            $msg = '<div class="alert alert-danger text-center">No se ha podido encontrado el registro de bouquetera</div>';
        }
        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }

    public function delete_bqt(Request $request)
    {
        $model = Bouquetera::find($request->id);
        $empresa = $model->id_empresa;
        $variedad = $model->id_variedad;
        $semana = getSemanaByDate($model->fecha);
        if ($model->delete()) {
            $success = true;
            $msg = '<div class="alert alert-success text-center">Se ha actualizado la información satisfactoriamente</div>';

            /* ----------------- ACTUALIZAR RESUMEN_TOTAL_SEMANAL_EXPORTCALAS --------------- */
            if ($empresa > 0) {
                jobActualizarBouquetera::dispatch($variedad, $semana, $empresa)->onQueue('bouquetera');
            }
        } else {
            $success = false;
            $msg = '<div class="alert alert-danger text-center">No se ha podido guardar la información</div>';
        }
        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }

    public function importar_file_bqt(Request $request)
    {
        return view('adminlte.gestion.bouquetera.ingreso.forms.importar', [
        ]);
    }

    public function descargar_plantilla(Request $request)
    {
        $fileName = basename('plantilla_ingreso_bouquetera.xlsx');
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

    public function upload_file_bqt(Request $request)
    {
        ini_set('max_execution_time', env('MAX_EXECUTION_TIME'));
        $finca_actual = getFincaActiva();
        $valida = Validator::make($request->all(), [
            'file_bqt' => 'required',
        ]);
        $msg = '<div class="alert alert-info text-center">Se ha importado el archivo, en menos de una hora se reflejarán los datos en el sistema</div>';
        $success = true;
        if (!$valida->fails()) {
            try {
                $archivo = $request->file_bqt;
                $extension = $archivo->getClientOriginalExtension();
                $nombre_archivo = $request->tallos_bqt . "-" . $request->tallos_exportables . "-" . $request->precio . "-upload_ingreso_bqt." . $extension;
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

    public function delete_registros(Request $request)
    {
        $modelos = Bouquetera::where('fecha', '>=', $request->desde)
            ->where('fecha', '<=', $request->hasta)
            ->delete();
        return [
            'success' => true,
            'mensaje' => '<div class="alert alert-info text-center">Se han eliminado los registros satisfactoriamente</div>',
        ];
    }
}