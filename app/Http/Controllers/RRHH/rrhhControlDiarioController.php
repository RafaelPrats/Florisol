<?php

namespace yura\Http\Controllers\RRHH;

use AWS;
use Carbon\Carbon;
use DB;
use File;
use Illuminate\Http\Request;
use Session;
use Validator;
use yura\Http\Controllers\Controller;
use yura\Modelos\Area;
use yura\Modelos\Actividad;
use yura\Modelos\ConfiguracionEmpresa;
use yura\Modelos\ControlPersonal;
use yura\Modelos\ManoObra;
use yura\Modelos\PersonalDetalle;
use yura\Modelos\Submenu;
use Illuminate\Support\Str;
use Storage;
use yura\Modelos\Personal;

class rrhhControlDiarioController extends Controller
{
    public function inicio(Request $request)
    {
        $usuario = getUsuario(Session::get('id_usuario'));
        $usuario->finca_activa;
        $area = Area::where([
            ['id_empresa', $usuario->finca_activa],
            ['estado', 1]
        ])->get();

        return view('adminlte.gestion.rrhh.control_diario.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'area' => $area
        ]);
    }

    public function obtener_actividad_mano_obra(Request $request)
    {
        if($request->tipo === 'actividad'){

            return response()->json(Actividad::where('id_area', $request->id_search_area)->get(),200);

        }else if($request->tipo === 'mano_obra'){

            $actividad = Actividad::find($request->id_search_actividad);
            return response()->json($actividad->manos_obra,200);

        }

    }

    public function buscar_control_diario(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'fecha' => 'required|date',
            'id_area' => 'required|numeric',
        ],[
            'fecha.required' => 'Debe ingresar una fecha',
            'fecha.date' => 'Debe ingresar una fecha válida',
            'id_area.required' => 'Debe seleccionar al menos un área',
            'id_area.numeric' => 'Debe seleccionar al menos un área válida',
        ]);

        if (!$valida->fails()) {

            $personal= PersonalDetalle::join('personal as p','personal_detalle.id_personal','p.id_personal')
            ->where('estado',true)
            ->where(function($w) use ($request){

                if($request->id_mano_obra != ''){

                    $w->where('personal_detalle.id_mano_obra',$request->id_mano_obra);

                }else if($request->id_actividad != ''){

                    $actividad = Actividad::find($request->id_actividad);
                    $w->whereIn('personal_detalle.id_mano_obra', $actividad->manos_obra->pluck('id_mano_obra')->toArray());

                }else if($request->id_area != ''){

                    $area = Area::where([
                        ['area.id_area', $request->id_area],
                        ['amo.estado',true]
                    ])->join('actividad as act','area.id_area','act.id_area')
                    ->join('actividad_mano_obra as amo','act.id_actividad','amo.id_actividad')
                    ->select('amo.id_mano_obra')->distinct()->get();

                    $w->whereIn('personal_detalle.id_mano_obra', $area->pluck('id_mano_obra')->toArray());

                }

            })->LeftJoin('control_personal as cp',function($j) use($request){

                $j->on('personal_detalle.id_personal_detalle','cp.id_personal_detalle')
                ->where('cp.fecha',$request->fecha);

            })->orderBy('p.nombre','asc')->orderBy('p.apellido','asc')
            ->select(
                'p.nombre',
                'p.apellido',
                'p.cedula_identidad',
                'cp.desde',
                'cp.hasta',
                'cp.id_control_personal',
                'cp.id_personal_detalle',
                'cp.id_mano_obra',
                'personal_detalle.id_personal_detalle',

            )->get();

            $manoObras = ManoObra::where([
                ['estado',true],
                ['id_empresa',getUsuario(Session::get('id_usuario'))->finca_activa]
            ])->get();

            return view('adminlte.gestion.rrhh.control_diario.partials.historico_control_diario',[
                'personal' => $personal,
                'ManoObras' => $manoObras,
                'asignacionMasivaHoras' => Carbon::parse($request->fecha)->diffInDays(now())
            ]);

        }else {
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
            return response()->json([$msg]);
        }

    }

    public function add_control_personal(Request $request)
    {
        $personalDetalle = PersonalDetalle::join('personal as p','p.id_personal','personal_detalle.id_personal')
        ->where('estado',true)
        ->select('personal_detalle.id_personal_detalle','p.nombre','p.apellido','cedula_identidad','p.id_personal')
        ->orderBy('p.nombre','asc')->orderBy('apellido','asc')->get();

        $personalEncontrado = Personal::join('personal_detalle as pd','personal.id_personal','pd.id_personal')
        ->where('cedula_identidad',$request->identificacion)
        ->select('personal.*','pd.id_mano_obra')->first();

        return view('adminlte.gestion.rrhh.control_diario.partials.fila_tabla_control_personal',[
            'personal' => $personalDetalle,
            'manoObra' => ManoObra::where([
                ['estado',true],
                ['id_empresa',getUsuario(Session::get('id_usuario'))->finca_activa]
            ])->orderBy('nombre','asc')->get(),
            'asignacionMasivaHoras' => Carbon::parse($request->fecha)->diffInDays(now()),
            'personalEncontrado' => $personalEncontrado,
            'desde' => $request->hora_desde,
            'hasta' => $request->hora_hasta,
        ]);
    }

    public function store_control_personal(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'fecha'=> 'required|date',
            'datos' => 'required|array'
            /*'datos' => ['required','array',function($attribute, $value, $onFailure) {

                foreach($value as $key => $asistencia){

                    if(!isset($asistencia['id_personal_detalle']) && $asistencia['id_personal_detalle'] ==''){

                        $onFailure('Debe seleccionar un personal en la fila '.($key+1));

                    }else if(!isset($asistencia['desde']) && $asistencia['desde'] ==''){

                        $onFailure('Debe colocar la hora desde en la fila '.($key+1));

                    }else if(!isset($asistencia['hasta']) && $asistencia['hasta'] ==''){

                        $onFailure('Debe colocar la hora hasta en la fila '.($key+1));

                    }else if(!isset($asistencia['id_mano_obra']) && $asistencia['id_mano_obra'] ==''){

                        $onFailure('Debe seleccionar una labor en la fila '.($key+1));

                    }

                }

            }],*/

        ], [
            'datos.required' => 'Debe ingresar al menos un registro de asistencia de personal',
            'datos.array' => 'Los registros de asistencia de personal deben ser una colección de datos',
            'fecha.required' => 'Debe ingresar una fecha',
            'fecha.date' => 'La fecha debe ser una fecha válida',
        ]);

        if (!$valida->fails()) {

            DB::beginTransaction();

            try{

                foreach($request->datos as $asistencia){

                    if(isset($asistencia['desde']) && $asistencia['desde'] !== '' && $asistencia['id_personal_detalle'] !== '' && isset($asistencia['id_personal_detalle'])){

                        $objControlPersonal = isset($asistencia['id_control_personal'])
                        ?  ControlPersonal::find($asistencia['id_control_personal'])
                        : new ControlPersonal;

                        $objControlPersonal->id_personal_detalle = $asistencia['id_personal_detalle'];
                        $objControlPersonal->id_mano_obra = $asistencia['id_mano_obra'];
                        $objControlPersonal->fecha = now()->toDateString();
                        $objControlPersonal->desde = $asistencia['desde'];
                        $objControlPersonal->hasta = $asistencia['hasta'];
                        $objControlPersonal->save();

                    }

                }

                $success= true;
                $msg=   "<div class='alert alert-success text-center'> Se ha guardado la información con éxito </div>";

                DB::commit();

            }catch (\Exception $e){

                DB::rollback();
                $success=false;
                $msg="<div class='alert alert-danger text-center'>".$e->getMessage()."</div>";

            }

        }else{

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

    public function delete_control_personal(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'id_control_personal' => 'required|exists:control_personal,id_control_personal'
        ], [
            'id_control_personal.required' => 'Debe seleccionar una asistencia para eliminar',
            'id_control_personal.exists' => 'La asistencia a eliminar no existe',
        ]);

        if (!$valida->fails()) {

            DB::beginTransaction();

            try{

                ControlPersonal::destroy($request->id_control_personal);

                $success= true;
                $msg=   "<div class='alert alert-success text-center'> Se ha eliminado la información con éxito </div>";

                DB::commit();

            }catch (\Exception $e){

                DB::rollback();
                $success=false;
                $msg="<div class='alert alert-danger text-center'>".$e->getMessage()."</div>";

            }

        }else{

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

    public function compare_photo(Request $request)
    {
        try{

            $fileData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $request->photo));

            $tmpFilePath = sys_get_temp_dir() . '/' . Str::uuid()->toString();
            file_put_contents($tmpFilePath, $fileData);

            $usuario = getUsuario(Session::get('id_usuario'));
            $ce = ConfiguracionEmpresa::find($usuario->finca_activa);

            $aws = AWS::createClient('rekognition');

            $result = $aws->SearchFacesByImage([
                'CollectionId' => $ce->coleccion_aws,
                'FaceMatchThreshold' => 80,
                'MaxFaces'=> 1,
                'Image' => [
                    'Bytes' => file_get_contents($tmpFilePath)
                ]
            ]);

            if($result->get('FaceMatches')){

                $arrFace = $result->get('FaceMatches')[0]['Face'];

                $personal = Personal::join('personal_detalle as pd','personal.id_personal','pd.id_personal')
                ->where('cedula_identidad',$arrFace['ExternalImageId'])
                ->select('personal.*','pd.id_personal_detalle','pd.id_mano_obra')->first();

                return[
                    'mensaje' => "<div class='alert alert-success text-center'> Personal encontrado ".$personal->nombre. " ".$personal->apellido." </div>",
                    'personal' => $personal,
                    'success' => true,
                    'identificacion' => $arrFace['ExternalImageId'],
                    'matches' => $result->get('FaceMatches')
                ];

            }else{

                return[
                    'mensaje' => "<div class='alert alert-danger text-center'> No se econtró la persona, intente nuevamente</div>",
                    'success' => false,
                ];

            }


        }catch(\Exception $e){
            dd($e->getMessage());
        }

    }

    public function modal_foto()
    {
        return view('adminlte.gestion.rrhh.control_diario.partials.form_camara');
    }

}
