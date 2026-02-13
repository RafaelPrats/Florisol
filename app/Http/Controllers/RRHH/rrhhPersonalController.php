<?php

namespace yura\Http\Controllers\RRHH;

use Excel;
use Illuminate\Http\Request;
use yura\Http\Controllers\Controller;
use Validator;
use yura\Modelos\Personal;
use yura\Modelos\PersonalDetalle;
use yura\Modelos\Banco;
use yura\Modelos\Cargo;
use yura\Modelos\CausaDesvinculacion;
use yura\Modelos\EstadoCivil;
use yura\Modelos\Sexo;
use yura\Modelos\Nacionalidad;
use yura\Modelos\Tipo_rol;
use yura\Modelos\TipoPago;
use yura\Modelos\TipoContrato;
use yura\Modelos\Grupo;
use yura\Modelos\Departamento;
use yura\Modelos\Discapacidad;
use yura\Modelos\Sucursal;
use yura\Modelos\GrupoInterno;
use yura\Modelos\GradoInstruccion;
use yura\Modelos\Area;
use yura\Modelos\Actividad;
use yura\Modelos\ManoObra;
use yura\Modelos\TipoCuenta;
use yura\Modelos\Plantilla;
use yura\Modelos\DetalleContrato;
use yura\Modelos\RelacionLaboral;
use yura\Modelos\Seguro;
use yura\Modelos\Submenu;
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Worksheet;
use PHPExcel_Style_Fill;
use PHPExcel_Style_Border;
use PHPExcel_Style_Color;
use PHPExcel_Style_Alignment;
use DB;
use Session;
use yura\Modelos\ConfiguracionEmpresa;
use AWS;

class rrhhPersonalController extends Controller
{
    public function inicio(Request $request)
    {
        return view('adminlte.gestion.rrhh.personal.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
        ]);
    }

    public function listar_personalmnjj(Request $request)
    {
        //dd($request->all());
        $resultado = [];
        $personal = Personal::All()
            ->where('cedula', $request->cedula)
            ->orwhere('nombre', $request->nombre)
            ->orwhere('apellido', $request->apellido)
            ->first();
        foreach ($personal_detalle as $detalle)
            if ($personal_detalle->estado == '1') {
            }
    }

    public function listar_personal(Request $request)
    {
        // dd($request->all());
        $resultado = [];
        $personal = Personal::All()
            ->where('cedula', $request->cedula)
            ->orwhere('nombre', $request->nombre)
            ->orwhere('apellido', $request->apellido)
            ->first();
        foreach ($personal_detalle as $detalle)
            if ($personal_detalle->estado == '1') {

                $msg = '<div class="alert alert-success text-center">Se ha guardado el horario satisfactoriamente</div>';
                $success = true;
            } else {
                $msg = '<div class="alert alert-danger text-center">Ha ocurrido un problema al guardar la información</div>';
                $success = false;
            }
        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }

    public function actualizarEstadoPersonal(Request $request)
    {
        $model = PersonalDetalle::find($request->id_personal);
        if ($model != '') {
            $model->estado = $model->estado == 1 ? 0 : 1;
            if ($model->save()) {
                bitacora('personal_detalle', $model->id_personal_, 'U', 'Actualización satisfactoria del estado del personal' . $model->nombre);

                return [
                    'success' => true,
                    'estado' => $model->estado == 1 ? true : false,
                    'mensaje' => '',
                ];
            } else {
                return [
                    'success' => false,
                    'estado' => '',
                    'mensaje' => '<div class="alert alert-info text-center">Ha ocurrido un problema al guardar en el sistema</div>',
                ];
            }
        } else {
            return [
                'success' => false,
                'estado' => '',
                'mensaje' => '<div class="alert alert-info text-center">No se ha encontrado en el sistema el parámetro</div>',
            ];
        }
    }

    public function add_personal(Request $request)
    {

        $grupo = Grupo::where('estado', 1)->get();
        //$banco = Banco::where('estado', 1)->get();
        //$departamento = Departamento::where('estado', 1)->get();
        //$estado_civil = EstadoCivil::where('estado', 1)->get();
        //$nacionalidad = Nacionalidad::where('estado', 1)->get();
       // $cargo = Cargo::where('estado', 1)->get();
        $tipo_rol = Tipo_rol::where('estado', 1)->get();
        //$tipo_pago = TipoPago::where('estado', 1)->get();
        //$tipo_contrato = TipoContrato::where('estado', 1)->get();
        //$sexo = Sexo::where('estado', 1)->get();
        $sucursal = Sucursal::where('estado', 1)->get();
        $grupo_interno = GrupoInterno::where('estado', 1)->get();
        //$grado_instruccion = GradoInstruccion::where('estado', 1)->get();
        $grupo = Grupo::where('estado', 1)->get();
        $area = Area::where('estado', 1)->get();
       // $plantilla = Plantilla::where('estado', 1)->get();
        //$tipo_cuenta = TipoCuenta::where('estado', 1)->get();
        //$detalle_contrato = DetalleContrato::where('estado', 1)->get();
        //$relacion_laboral = RelacionLaboral::where('estado', 1)->get();
        //$seguro = Seguro::where('estado', 1)->get();
        return view('adminlte.gestion.rrhh.personal.forms.add_personal', [
            'grupo' => $grupo,
            //'departamento' => $departamento,
            //'banco' => $banco,
            //'estado_civil' => $estado_civil,
            //'nacionalidad' => $nacionalidad,
           // 'cargo' => $cargo,
            'tipo_rol' => $tipo_rol,
            //'tipo_pago' => $tipo_pago,
            //'tipo_contrato' => $tipo_contrato,
            'sucursal' => $sucursal,
            'grupo_interno' => $grupo_interno,
            'grupo' => $grupo,
            'area' => $area,
            //'plantilla' => $plantilla,
            //'tipo_cuenta' => $tipo_cuenta,
            //'detalle_contrato' => $detalle_contrato,
            //'relacion_laboral' => $relacion_laboral,
            //'seguro' => $seguro,
            /*'sexo' => $sexo,
            'grado_instruccion' => $grado_instruccion,*/
        ]);
    }

    public function trabajador(Request $request)
    {
        //dd($request);
        $busqueda_personal = $request->busqueda_personal;
        $estado = $request->estado;
        $listado = Personal::where('nombre', 'like', "%$busqueda_personal%")
            ->orWhere('apellido', 'like', "%$busqueda_personal%")
            ->orWhere('cedula_identidad', 'like', "%$busqueda_personal%")
            ->orderBy('apellido', 'asc')->get();
        //  dd($listado);

        $resultados = [];
        foreach ($listado as $per) {
            if ($estado == 1) {
                if ($per->getDetalleActivoDesin() != '')
                    array_push($resultados, $per);
            } else
                if ($per->getDetalleActivoDesin() == '')
                array_push($resultados, $per);
        }

        return view('adminlte.gestion.rrhh.personal.partials.listado', [
            'person' => $resultados,
            'estado' => $estado,

        ]);
    }

    public function trabajadores(Request $request)
    {
        //dd($request->all());
        $estado = $request->estado;
        $busqueda_personal = $request->busqueda_personal;

        $listado = Personal::buscarpor($estado, $busqueda_personal);
        $resultados = [];
        foreach ($listado as $per) {
            if ($estado == 1) {
                if ($per->getDetalleActivo() != '')
                    array_push($resultados, $per);
            } else
                if ($per->getDetalleActivo() == '')
                array_push($resultados, $per);
        }

        return view('adminlte.gestion.rrhh.personal.partials.listado', [
            'person' => $resultados,
            'estado' => $estado,
        ]);
    }

    public function ver_historico(Request $request)
    {
        $dataPersonal = Personal::find($request->id_personal);
        return view('adminlte.gestion.rrhh.personal.forms.historico', [
            'dataPersonal' => $dataPersonal,
            'detalles' => $dataPersonal->detalles->sortBy('estado'),
        ]);
    }

    public function excel_personal(Request $request)
    {
        //---------------------- EXCEL --------------------------------------
        $objPHPExcel = new PHPExcel;
        $objPHPExcel->getDefaultStyle()->getFont()->setName('Calibri');
        $objPHPExcel->getDefaultStyle()->getFont()->setSize(12);
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel2007");
        $currencyFormat = '#,#0.## \€;[Red]-#,#0.## \€';
        $numberFormat = '#,#0.##;[Red]-#,#0.##';
        $objPHPExcel->removeSheetByIndex(0); //Eliminar la hoja inicial por defecto

        $this->excelPersonal($objPHPExcel, $request);

        //--------------------------- GUARDAR EL EXCEL -----------------------

        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition:inline;filename="Reporte del personal.xlsx"');
        header("Content-Transfer-Encoding: binary");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        $objWriter->save('php://output');
    }

    public function excelPersonal($objPHPExcel, $request)
    {
        //dd($request->all());
        $estado = $request->estado;
        $busqueda = $request->has('busqueda') ? espacios($request->busqueda) : '';
        $bus = str_replace(' ', '%%', $busqueda);

        $listado = DB::table('personal as a');
        //dd($listado);

        if ($request->busqueda != null) $listado = $listado->Where(function ($q) use ($bus,$estado) {

            $listado = Personal::where('nombre', 'like', '%' . $bus . '%')
                ->orWhere('apellido', 'like', '%' . $bus . '%')
                ->orWhere('cedula_identidad', 'like', '%' . $bus . '%')
                ->orderBy('apellido', 'asc')->get();
            //  dd($listado);
            $listado->getDetalleActivo()->last();

            $resultados = [];
            foreach ($listado as $per) {
                if ($estado == 1) {
                    if ($per->getDetalleActivoDesin() != '')
                        array_push($resultados, $per);
                } else
                    if ($per->getDetalleActivoDesin() == '')
                    array_push($resultados, $per);
            }
        });

        $listado = $listado->orderBy('a.nombre', 'asc')->paginate(20);

        if (count($listado) > 0) {
            $objSheet = new PHPExcel_Worksheet($objPHPExcel, 'Personal');
            $objPHPExcel->addSheet($objSheet, 0);

            $objSheet->mergeCells('A1:B1');
            $objSheet->getStyle('A1:B1')->getFont()->setBold(true)->setSize(12);
            $objSheet->getStyle('A1:B1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objSheet->getStyle('A1:B1')
                ->getFill()
                ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                ->getStartColor()
                ->setRGB('CCFFCC');

            $objSheet->getCell('A1')->setValue('Listado de Personal');

            $objSheet->getCell('A3')->setValue('ID');
            $objSheet->getCell('B3')->setValue('Nombre');
            $objSheet->getCell('C3')->setValue('Apellido');
            $objSheet->getCell('D3')->setValue('Cédula');
            $objSheet->getCell('E3')->setValue('Fecha de Ingreso');

            $objSheet->getStyle('A3:B3')->getFont()->setBold(true)->setSize(12);

            $objSheet->getStyle('A3:B3')
                ->getBorders()
                ->getAllBorders()
                ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN)
                ->getColor()
                ->setRGB(PHPExcel_Style_Color::COLOR_BLACK);

            $objSheet->getStyle('A3:B3')
                ->getFill()
                ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                ->getStartColor()
                ->setRGB('CCFFCC');

            $objSheet->getStyle('A3:C3')->getFont()->setBold(true)->setSize(12);

            $objSheet->getStyle('A3:C3')
                ->getFill()
                ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                ->getStartColor()
                ->setRGB('CCFFCC');

            //--------------------------- LLENAR LA TABLA ---------------------------------------------
            for ($i = 0; $i < sizeof($listado); $i++) {

                $objSheet->getStyle('A' . ($i + 4))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                $objSheet->getStyle('B' . ($i + 4))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

                $objSheet->getCell('A' . ($i + 4))->setValue($listado[$i]->id_personal);
                $objSheet->getCell('B' . ($i + 4))->setValue($listado[$i]->nombre);
                $objSheet->getCell('C' . ($i + 4))->setValue($listado[$i]->apellido);
                $objSheet->getCell('D' . ($i + 4))->setValue($listado[$i]->cedula_identidad);
                $pd = PersonalDetalle::where('id_personal', $listado[$i]->id_personal)->orderBy('id_personal_detalle','desc')->first();
                $objSheet->getCell('E' . ($i + 4))->setValue(isset($pd) ? $pd->fecha_ingreso: '');

            }

            $objSheet->getColumnDimension('A')->setAutoSize(true);
            $objSheet->getColumnDimension('B')->setAutoSize(true);
        } else {
            return '<div>No se han encontrado coincidencias para exportar</div>';
        }
    }

    public function eliminar_trabajador(Request $request)
    {
        $dataPersonal = Personal::find($request->id_personal);
        $dataPersonal->eliminarPersonal();
        if ($dataPersonal->delete()) {
            $msg = '<div class="alert alert-success text-center">Se ha eliminado el personal satisfactoriamente</div>';
            $success = true;
        } else {
            $msg = '<div class="alert alert-danger text-center">Ha ocurrido un error al eliminar el personal</div>';
            $success = false;
        }
        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }

    public function ver_desincorporar_personal(Request $request)
    {
        $dataPersonal = Personal::find($request->id_personal);
        $causa_desvinculacion = CausaDesvinculacion::where('estado', 1)->get();

        return view('adminlte.gestion.rrhh.personal.forms.desincorporar_personal', [
            'dataPersonal' => $dataPersonal,
            'detalle' => $dataPersonal->getDetalleActivoDesin(),
            'causa_desvinculacion' => $causa_desvinculacion,

        ]);
    }

    public function ver_incorporar_personal(Request $request)
    {
        $dataPersonal = Personal::find($request->id_personal);

        return view('adminlte.gestion.rrhh.personal.forms.incorporar_personal', [
            'dataPersonal' => $dataPersonal,
            'detalle' => $dataPersonal->getDetalleInactivo()->last(),
            'manoObra' => ManoObra::where('estado', 1)->orderBy('nombre','asc')->get()
        ]);
    }

    public function ficha_personal(Request $request)
    {
        //dd($request->all());
        $dataPersonal = Personal::find($request->id_personal);
        $tipo_rol = Tipo_rol::ALL()->where('estado', 1 || 0);
        $area = Area::ALL()->where('estado', 1 || 0);
        $sexo = Sexo::ALL()->where('estado', 1 || 0);
        $tipo_rol = Tipo_rol::ALL()->where('estado', 1 || 0);
        $estado_civil = EstadoCivil::ALL()->where('estado', 1 || 0);
        $nacionalidad = Nacionalidad::ALL()->where('estado', 1 || 0);
        $tipo_contrato = TipoContrato::ALL()->where('estado', 1 || 0);
        $cargo = Cargo::ALL()->where('estado', 1 || 0);
        $tipo_pago = TipoPago::ALL()->where('estado', 1 || 0);
        $banco = Banco::ALL()->where('estado', 1 || 0);
        $tipo_cuenta = TipoCuenta::ALL()->where('estado', 1 || 0);
        $sucursal = Sucursal::ALL()->where('estado', 1 || 0);
        $departamento = Departamento::ALL()->where('estado', 1 || 0);
        $actividad = Actividad::ALL()->where('estado', 1 || 0);
        $mano_obra = ManoObra::ALL()->where('estado', 1 || 0);
        $grupo_interno = GrupoInterno::ALL()->where('estado', 1 || 0);
        $grupo = Grupo::ALL()->where('estado', 1 || 0);
        $plantilla = Plantilla::ALL()->where('estado', 1 || 0);
        $causa_desvinculacion = CausaDesvinculacion::ALL()->where('estado', 1 || 0);
        $grado_instruccion = GradoInstruccion::ALL()->where('estado', 1 || 0);
        $detalle_contrato = DetalleContrato::ALL()->where('estado', 1);
        $relacion_laboral = RelacionLaboral::ALL()->where('estado', 1);
        $seguro = Seguro::ALL()->where('estado', 1);

        return view('adminlte.gestion.rrhh.personal.forms.ficha_personal', [
            'dataPersonal' => $dataPersonal,
            'sexo' => $sexo,
            'detalle' => $dataPersonal->getDetalleInactivo()->last(),
            'tipo_rol' => $tipo_rol,
            'area' => $area,
            'tipo_rol' => $tipo_rol,
            'estado_civil' => $estado_civil,
            'nacionalidad' => $nacionalidad,
            'tipo_contrato' => $tipo_contrato,
            'cargo' => $cargo,
            'tipo_pago' => $tipo_pago,
            'banco' => $banco,
            'tipo_cuenta' => $tipo_cuenta,
            'sucursal' => $sucursal,
            'departamento' => $departamento,
            'actividad' => $actividad,
            'mano_obra' => $mano_obra,
            'grupo_interno' => $grupo_interno,
            'grupo' => $grupo,
            'plantilla' => $plantilla,
            'causa_desvinculacion' => $causa_desvinculacion,
            'grado_instruccion' => $grado_instruccion,
            'detalle_contrato' => $detalle_contrato,
            'relacion_laboral' => $relacion_laboral,
            'seguro' => $seguro,
        ]);
    }

    public function ver_personal(Request $request)
    {
        $dataPersonal = Personal::find($request->id_personal);
        $sucursal = Sucursal::where('estado', 1 || 0)->get();
        $area = Area::where('estado', 1 || 0)->get();
        $actividad = Actividad::where('estado', 1 || 0)->get();
        $mano_obra = ManoObra::where('estado', 1 || 0)->get();
       // $causa_desvinculacion = CausaDesvinculacion::where('estado', 1 || 0)->get();
        $tipo_rol = Tipo_rol::where('estado', 1)->get();
        $grupo = Grupo::ALL()->where('estado', 1 || 0);

        return view('adminlte.gestion.rrhh.personal.forms.update_personal', [
            'dataPersonal' => $dataPersonal,
            'detalle' => $dataPersonal->getDetalleActivo()->last(),
            'area' => $area,
            'sucursal' => $sucursal,
            'actividad' => $actividad,
            'mano_obra' => $mano_obra,
           // 'causa_desvinculacion' => $causa_desvinculacion,
            'tipo_rol' => $tipo_rol,
            'grupo' => $grupo,
        ]);
    }

    public function desincorporar_persona(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'id_personal_detalle' => 'required|exists:personal_detalle,id_personal_detalle',
            'fecha_desvinculacion' => 'required|date',
            'id_causa_desvinculacion' => 'required|exists:causa_desvinculacion,id_causa_desvinculacion',
        ], [
            'id_personal_detalle.required' => 'El campo personal es obligatorio',
            'id_personal_detalle.exists' => 'El personal no existe',
            'fecha_desvinculacion.required' => 'La fecha desvinculacion es obligatorio',
            'fecha_desvinculacion.date' => 'LA fecha desvinculacion debe ser una fecha',
            'id_causa_desvinculacion.required' => 'La causa desvinculacion es obligatorio',
            'id_causa_desvinculacion.exists' => 'La causa desvinculacion no existe',
        ]);

        if (!$valida->fails()) {

            $detalle = PersonalDetalle::find($request->id_personal_detalle);
            if ($detalle != '') {
                $detalle->fecha_desvinculacion = $request->fecha_desvinculacion;
                $detalle->id_causa_desvinculacion = $request->id_causa_desvinculacion;
                $detalle->estado = 0;
                if ($detalle->save()) {
                    $msg = '<div class="alert alert-success text-center">Se ha desincorporado el personal satisfactoriamente</div>';
                    $success = true;
                }
            } else {
                $msg = '<div class="alert alert-danger text-center">Ha ocurrido un error al guardar la informacion</div>';
                $success = false;
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

    public function incorporarh_persona(Request $request)
    {
        // dd($request->all());
        $detalle = PersonalDetalle::find($request->id_personal_detalle);

        if ($detalle != '') {
            $detalle->fecha_reingreso = $request->fecha_reingreso;

            if ($detalle->save()) {
                $msg = '<div class="alert alert-success text-center">Se ha desincorporado el personal satisfactoriamente</div>';
                $success = true;
            }
        } else {
            $msg = '<div class="alert alert-danger text-center">Ha ocurrido un error al guardar la informacion</div>';
            $success = false;
        }
        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }

    public function incorporar_personal(Request $request)
    {


        // dd($request->all());
        $personal = PersonalDetalle::where('id_personal_detalle', $request->id_personal_detalle)->first();
    }

    public function actualiza_personal(Request $request)
    {

        $valida = Validator::make($request->all(), [
            'cedula_identidad' => 'required|max:10',
            'nombre' => 'required|max:50',
            'apellido' => 'required|max:50',
            'id_sucursal' => 'required|exists:sucursal,id_sucursal',
            'id_mano_obra' => 'required|exists:mano_obra,id_mano_obra',
            'file_personal' => 'nullable|max:500',

        ], [
            'cedula_identidad.required' => 'El número de cédula es obligatorio',
            'cedula_identidad.max' => 'El número de cédula es muy grande',
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.max' => 'El nombre es muy grande',
            'apellido.required' => 'El apellido es obligatorio',
            'apellido.max' => 'El apellido es muy grande',
            'id_sucursal.required' => 'La sucursal es obligatoria',
            'id_sucursal.exists' => 'La sucursal no existe',
            'file_personal.max' => 'El archivo debe ser menor a 500Kb',
        ]);
        if (!$valida->fails()) {


            try{

                $success = true;
                $personal = Personal::find($request->id_personal);
                $personal->nombre = $request->nombre;
                $personal->apellido = $request->apellido;
                $personal->cedula_identidad = $request->cedula_identidad;
                $personal->id_sexo = $request->id_sexo;
                $personal->id_nacionalidad = $request->id_nacionalidad;
                $personal->fecha_nacimiento = $request->fecha_nacimiento;
                $personal->save();

                $detalle = PersonalDetalle::find($request->id_personal_detalle);


                $detalle->estado = 0;
                $detalle->save();

                $new_detalle = new PersonalDetalle();
                $new_detalle->id_personal = $personal->id_personal;
                $new_detalle->fecha_ingreso = $request->fecha_ingreso;
                $new_detalle->id_departamento = $request->id_departamento;
                $new_detalle->id_estado_civil = $request->id_estado_civil;
                $new_detalle->discapacidad = $request->discapacidad;
                $new_detalle->porcentaje_discapacidad = $request->porcentaje_discapacidad;
                $new_detalle->id_cargo = $request->id_cargo;
                $new_detalle->telef = $request->telef;
                $new_detalle->cargas_familiares = $request->cargas_familiares;
                $new_detalle->id_tipo_contrato = $request->id_tipo_contrato;
                $new_detalle->lugar_residencia = $request->lugar_residencia;
                $new_detalle->direccion = $request->direccion;
                $new_detalle->correo = $request->correo;
                $new_detalle->sueldo = $request->sueldo;
                $new_detalle->id_banco = $request->id_banco;
                $new_detalle->id_tipo_rol = $request->tipo_rol;
                $new_detalle->id_tipo_pago = $request->id_tipo_pago;
                $new_detalle->numero_cuenta = $request->numero_cuenta;
                $new_detalle->id_grado_instruccion = $request->id_grado_instruccion;
                $new_detalle->id_sucursal = $request->id_sucursal;
                $new_detalle->id_grupo = $request->grupo;
                $new_detalle->id_grupo_interno = $request->id_grupo_interno;
                $new_detalle->id_area = $request->id_area;
                $new_detalle->id_actividad = $request->id_actividad;
                $new_detalle->id_mano_obra = $request->id_mano_obra;
                $new_detalle->id_plantilla = $request->id_plantilla;
                $new_detalle->id_tipo_cuenta = $request->id_tipo_cuenta;
                $new_detalle->id_detalle_contrato = $request->id_detalle_contrato;
                $new_detalle->id_relacion_laboral = $request->id_relacion_laboral;
                $new_detalle->id_seguro = $request->id_seguro;
                $new_detalle->n_afiliacion = $request->n_afiliacion;

                $new_detalle->save();

                $usuario = getUsuario(Session::get('id_usuario'));

                $ce = ConfiguracionEmpresa::find($usuario->finca_activa);

                if($request->has('file_personal')){

                    $aws = AWS::createClient('rekognition');

                    $indexImg = $aws->IndexFaces([
                        'CollectionId' => $ce->coleccion_aws,
                        'ExternalImageId' => $request->cedula_identidad,
                        'Image' => [
                            'Bytes' => file_get_contents($request->file('file_personal')->getRealPath())
                        ]
                    ]);

                    if($indexImg->get('@metadata')['statusCode'] !== 200){
                        $msg = '<div class="alert alert-danger text-center">Ha ocurrido un error al guardar la foto del personal, intente nuevamente</div>';
                        $success = false;
                    }

                }

                if($success)
                    $msg = '<div class="alert alert-success text-center">Se ha actualizado el personal satisfactoriamente</div>';


            }catch(\Exception $e){

                $success = false;
                $msg = $e->getMessage();
            }

        }else {
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

    public function reincorporar_personal(Request $request)
    {
        //dd($request->all());
        $valida = Validator::make($request->all(), [
            'id_personal' => 'required|exists:personal,id_personal',
            'cedula_identidad' => 'required|max:10',
            'nombre' => 'required|max:50',
            'apellido' => 'required|max:50',
            'id_mano_obra' => 'required|exists:mano_obra,id_mano_obra',
            'fecha_ingreso' => 'required|date',
            'sueldo' => 'required|numeric|min:0',
        ], [
            'id_personal.required' => 'El campo personal es obligatorio',
            'id_personal.exists' => 'El personal no existe',
            'cedula_identidad.unique' => 'El número de cédula ya existe',
            'cedula_identidad.required' => 'El número de cédula es obligatorio',
            'cedula_identidad.max' => 'El número de cédula es muy grande',
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.max' => 'El nombre es muy grande',
            'apellido.required' => 'El apellido es obligatorio',
            'apellido.max' => 'El apellido es muy grande',
            'id_mano_obra.required' => 'La mano de obra es obligatoria',
            'id_mano_obra.exists' => 'La mano de obra no existe',
            'fecha_ingreso.required' => 'La fecha de ingreso es obligatoria',
            'fecha_ingreso.date' => 'La fecha de ingreso no es valida',
            'sueldo.required' => 'El sueldo es obligatorio',
            'sueldo.numeric' => 'El sueldo debe ser numérico',
            'sueldo.min' => 'El sueldo debe ser mayor a 0',
        ]);

        if (!$valida->fails()) {

            //dd($request->all());
            $personal = Personal::find($request->id_personal);
            $personal->nombre = $request->nombre;
            $personal->apellido = $request->apellido;
            $personal->cedula_identidad = $request->cedula_identidad;
            $personal->id_sexo = $request->id_sexo;
            $personal->id_nacionalidad = $request->id_nacionalidad;
            $personal->fecha_nacimiento = $request->fecha_nacimiento;
            //dd($request->all());
            $detalle = PersonalDetalle::find($request->id_personal_detalle);

            if ($personal->save()) {

                $new_detalle = new PersonalDetalle();
                $new_detalle->id_personal = $personal->id_personal;
                $new_detalle->fecha_ingreso = $request->fecha_ingreso;
                $new_detalle->id_departamento = $request->id_departamento;
                $new_detalle->id_estado_civil = $request->id_estado_civil;
                $new_detalle->discapacidad = $request->discapacidad;
                $new_detalle->porcentaje_discapacidad = $request->porcentaje_discapacidad;
                $new_detalle->id_cargo = $request->id_cargo;
                $new_detalle->telef = $request->telef;
                $new_detalle->cargas_familiares = $request->cargas_familiares;
                $new_detalle->id_tipo_contrato = $request->id_tipo_contrato;
                $new_detalle->lugar_residencia = $request->lugar_residencia;
                $new_detalle->direccion = $request->direccion;
                $new_detalle->correo = $request->correo;
                $new_detalle->sueldo = $request->sueldo;
                $new_detalle->id_banco = $request->id_banco;
                $new_detalle->id_tipo_rol = $request->id_tipo_rol;
                $new_detalle->id_tipo_pago = $request->id_tipo_pago;
                $new_detalle->numero_cuenta = $request->numero_cuenta;
                $new_detalle->id_grado_instruccion = $request->id_grado_instruccion;
                $new_detalle->id_sucursal = $request->id_sucursal;
                $new_detalle->id_grupo = $request->id_grupo;
                $new_detalle->id_grupo_interno = $request->id_grupo_interno;
                $new_detalle->id_area = $request->id_area;
                $new_detalle->id_actividad = $request->id_actividad;
                $new_detalle->id_mano_obra = $request->id_mano_obra;
                $new_detalle->id_plantilla = $request->id_plantilla;
                $new_detalle->id_tipo_cuenta = $request->id_tipo_cuenta;
                $new_detalle->id_detalle_contrato = $request->id_detalle_contrato;
                $new_detalle->id_relacion_laboral = $request->id_relacion_laboral;
                $new_detalle->id_seguro = $request->id_seguro;
                $new_detalle->n_afiliacion = $request->n_afiliacion;
                $new_detalle->save();

                $msg = '<div class="alert alert-success text-center">Se ha reincorporado el personal satisfactoriamente</div>';
                $success = true;
            } else {
                $msg = '<div class="alert alert-danger text-center">Ha ocurrido un error al guardar la informacion</div>';
                $success = false;
            }


        }else {
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

    public function store_personal(Request $request)
    {//dd($request->all());
        $valida = Validator::make($request->all(), [
            'cedula_identidad' => 'required|max:10|unique:personal',
            'nombre' => 'required|max:50',
            'apellido' => 'required|max:50',
            'id_sucursal' => 'required|exists:sucursal,id_sucursal',
            'id_mano_obra' => 'required|exists:mano_obra,id_mano_obra',
            'file_personal' => 'nullable|max:500',

        ], [
            'cedula_identidad.unique' => 'El número de cédula ya existe',
            'cedula_identidad.required' => 'El número de cédula es obligatorio',
            'cedula_identidad.max' => 'El número de cédula es muy grande',
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.max' => 'El nombre es muy grande',
            'apellido.required' => 'El apellido es obligatorio',
            'apellido.max' => 'El apellido es muy grande',
            'id_sucursal.required' => 'La sucursal es obligatoria',
            'id_sucursal.exists' => 'La sucursal no existe',
            'file_personal.max' => 'El archivo debe ser menor a 500Kb',
        ]);
        if (!$valida->fails()) {
            //dd($request->all());
            $success = true;
            $usuario = getUsuario(Session::get('id_usuario'));

            $ce = ConfiguracionEmpresa::find($usuario->finca_activa);

            $personal = new Personal();
            $personal->nombre = $request->nombre;
            $personal->apellido = $request->apellido;
            $personal->cedula_identidad = $request->cedula_identidad;
            $personal->id_sexo = $request->id_sexo;
            $personal->id_nacionalidad = $request->id_nacionalidad;
            $personal->fecha_nacimiento = $request->fecha_nacimiento;

            // $model->fecha_desvinculacion = $request->fecha_desvinculacion;
            //   $model->id_causa_desvinculacion = $request->id_causa_desvinculacion;

            if ($personal->save()) {
                $personal = Personal::All()->last();
                $detalle = new PersonalDetalle();
                $detalle->id_personal = $personal->id_personal;
                $detalle->fecha_ingreso = $request->fecha_ingreso;
                $detalle->id_departamento = $request->id_departamento;
                $detalle->id_estado_civil = $request->id_estado_civil;
                $detalle->discapacidad = $request->discapacidad;
                $detalle->porcentaje_discapacidad = $request->porcentaje_discapacidad;
                $detalle->id_cargo = $request->id_cargo;
                $detalle->telef = $request->telef;
                $detalle->cargas_familiares = $request->cargas_familiares;
                $detalle->id_tipo_contrato = $request->id_tipo_contrato;
                $detalle->lugar_residencia = $request->lugar_residencia;
                $detalle->direccion = $request->direccion;
                $detalle->correo = $request->correo;
                $detalle->sueldo = $request->sueldo;
                $detalle->id_banco = $request->id_banco;
                $detalle->id_tipo_rol = $request->id_tipo_rol;
                $detalle->id_tipo_pago = $request->id_tipo_pago;
                $detalle->numero_cuenta = $request->numero_cuenta;
                $detalle->id_grado_instruccion = $request->id_grado_instruccion;
                $detalle->id_sucursal = $request->id_sucursal;
                $detalle->id_grupo = $request->id_grupo;
                $detalle->id_grupo_interno = $request->id_grupo_interno;
                $detalle->id_area = $request->id_area;
                $detalle->id_actividad = $request->id_actividad;
                $detalle->id_mano_obra = $request->id_mano_obra;
                $detalle->id_plantilla = $request->id_plantilla;
                $detalle->id_tipo_cuenta = $request->id_tipo_cuenta;
                $detalle->id_relacion_laboral = $request->id_relacion_laboral;
                $detalle->id_detalle_contrato = $request->id_detalle_contrato;
                $detalle->id_seguro = $request->id_seguro;
                $detalle->n_afiliacion = $request->n_afiliacion;
                $detalle->save();

                if(isset($ce->coleccion_aws) && $request->has('file_personal')){

                    $aws = AWS::createClient('rekognition');
                    $indexImg = $aws->IndexFaces([
                        'CollectionId' => $ce->coleccion_aws,
                        'ExternalImageId' => $request->cedula_identidad,
                        'Image' => [
                            'Bytes' => file_get_contents($request->file('file_personal')->getRealPath())
                        ]
                    ]);

                    if($indexImg->get('@metadata')['statusCode'] !== 200){
                        $msg = '<div class="alert alert-danger text-center">Ha ocurrido un error al guardar la foto del personal, intente nuevamente</div>';
                        $success = false;
                    }

                }

                if($success)
                    $msg = '<div class="alert alert-success text-center">Se ha guardado el personal satisfactoriamente</div>';

            } else {
                $msg = '<div class="alert alert-danger text-center">Ha ocurrido un error al guardar la informacion</div>';
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
            'mensaje' => $msg,
            'success' => $success
        ];
    }

    public function buscar_personal(Request $request)
    {

        return view('adminlte.gestion.rrhh.personal.partials.listado', [
            'listado' => Personal::All()->sortBy('id'),

        ]);
    }

    public function seleccionar_area_actividad(Request $request)
    {
        $area = Area::ALL()->where('estado', 1);
        $actividades = Actividad::where('id_area' == $area)->get();
        return view('adminlte.gestion.rrhh.personal.forms._actividades', [
            'area' => $area,
            'actividades' => $actividades
        ]);
    }

    public function seleccionar_area(Request $request)
    {
        $actividades = Actividad::where('id_area', $request->id_area)->get();
        return view('adminlte.gestion.rrhh.personal.forms._actividades', [
            'actividades' => $actividades
        ]);
    }

    public function seleccionar_actividad(Request $request)
    {
        $actividad = Actividad::find($request->id_actividad);
        $manos_obra = $actividad->manos_obra;
        return view('adminlte.gestion.rrhh.personal.forms._manos_obra', [
            'manos_obra' => $manos_obra
        ]);
    }

}
