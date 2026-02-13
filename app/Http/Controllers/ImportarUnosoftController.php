<?php

namespace yura\Http\Controllers;

use Illuminate\Http\Request;
use yura\Modelos\Submenu;
use Validator;
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Worksheet;
use Storage as Almacenamiento;

class ImportarUnosoftController extends Controller
{
    public function inicio(Request $request)
    {
        return view('adminlte.gestion.importar_unosoft.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
        ]);
    }

    public function importar(Request $request)
    {
        ini_set('max_execution_time', env('MAX_EXECUTION_TIME'));
        $valida = Validator::make($request->all(), [
            'file_importar' => 'required',
        ]);
        $msg = '<div class="alert alert-info text-center">Se ha importado el archivo, en menos de una hora se reflejarán los datos en el sistema</div>';
        $success = true;
        if (!$valida->fails()) {
            $archivo = $request->file_importar;
            $extension = $archivo->getClientOriginalExtension();
            if ($request->bouquetera == 0)
                $nombre_archivo = "unosoft" . "." . $extension;
            else
                $nombre_archivo = "unosoft_bouquetera" . "." . $extension;
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

    public function descargar_plantilla(Request $request)
    {
        $fileName = basename('plantilla_unosoft.xlsx');
        $filePath = public_path('storage/' . $fileName);;
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
}