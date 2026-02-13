<?php

namespace yura\Http\Controllers\Campo;

use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use yura\Http\Controllers\Controller;
use yura\Modelos\Aplicacion;
use yura\Modelos\AplicacionMatriz;
use yura\Modelos\AplicacionMezcla;
use yura\Modelos\CicloLuz;
use yura\Modelos\DetalleAplicacion;
use yura\Modelos\DetalleAplicacionCampo;
use yura\Modelos\ManoObra;
use yura\Modelos\Planta;
use yura\Modelos\Producto;
use yura\Modelos\ProyeccionModulo;
use yura\Modelos\Semana;
use yura\Modelos\Submenu;
use yura\Modelos\Ciclo;
use yura\Modelos\AplicacionCampo;
use Validator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use yura\Modelos\ResumenFenogramaEjecucion;

class LaboresCampoController extends Controller
{
    public function inicio(Request $request)
    {
        $semanas = DB::table('semana')
            ->select('codigo', 'fecha_inicial', 'fecha_final')->distinct()
            ->where('estado', 1)
            ->where('fecha_final', '>=', opDiasFecha('-', 7, hoy()))
            ->orderBy('codigo')
            ->get();
        $semana_actual = getSemanaByDate(hoy());
        $sectores = DB::table('sector')
            ->where('estado', 1)
            ->where('id_empresa', getFincaActiva())
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.campo.labores_campo.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'semanas' => $semanas,
            'semana_actual' => $semana_actual,
            'sectores' => $sectores,
        ]);
    }

    public function seleccionar_tipo_labor(Request $request)
    {
        $labores = AplicacionMatriz::where('tipo', $request->tipo)->orderBy('nombre')->get();
        return [
            'labores' => $labores
        ];
    }

    /* ----------------------- LISTAR -------------------------- */
    public function listar_labores(Request $request)
    {
        $app_matriz = AplicacionMatriz::find($request->labor);
        if ($app_matriz->nombre == 'ACIDO GIBERELICO')
            return view(
                'adminlte.gestion.campo.labores_campo.partials.listado_giberelico',
                self::listar_acido_giberelico($request, $app_matriz)
            );
        if ($app_matriz->nombre == 'DESBROTE')
            return view(
                'adminlte.gestion.campo.labores_campo.partials.listado_desbrote',
                self::listar_desbrote($request, $app_matriz)
            );
        if ($app_matriz->nombre == 'SIEMRBA')
            return view(
                'adminlte.gestion.campo.labores_campo.partials.listado_siembra',
                self::listar_siembra($request, $app_matriz)
            );
    }

    static function listar_acido_giberelico($request, $app_matriz)
    {
        $finca = getFincaActiva();
        $semana_actual = getSemanaByDate(hoy());
        $semana_req = getObjSemana($request->semana);
        $aplicaciones = $app_matriz->aplicaciones->where('estado', 1);
        $listado = [];
        $ids_ciclos = [];
        $productos = [];
        $mano_obras = [];
        foreach ($aplicaciones as $aplicacion) {
            $ciclos = Ciclo::join('modulo as m', 'm.id_modulo', '=', 'ciclo.id_modulo')
                ->where('ciclo.estado', 1)
                ->where('m.id_sector', $request->sector);
            if ($semana_req->codigo >= $semana_actual->codigo)
                $ciclos = $ciclos->where('ciclo.activo', 1);
            else
                $ciclos = $ciclos->where('ciclo.fecha_fin', '>=', $semana_req->fecha_inicial);
            if ($aplicacion->poda_siembra != 'T') {
                $ciclos = $ciclos->where('ciclo.poda_siembra', $aplicacion->poda_siembra);
            }
            $ciclos = $ciclos->where('ciclo.id_empresa', $finca)
                ->orderBy('ciclo.fecha_inicio', 'desc')
                ->get();
            $dia_ini = $aplicacion->semana_ini * 7;
            $dia_fin = (($aplicacion->semana_ini + $aplicacion->repeticiones) * 7) - 1;
            foreach ($ciclos as $c) {
                if (!in_array($c->id_ciclo, $ids_ciclos)) {
                    $fenograma = ResumenFenogramaEjecucion::All()
                        ->where('id_ciclo', $c->id_ciclo)
                        ->first();
                    $labores_campo = AplicacionCampo::where('id_ciclo', $c->id_ciclo)
                        ->where('id_aplicacion', $aplicacion->id_aplicacion)
                        ->where('fecha', '>=', $semana_req->fecha_inicial)
                        ->where('fecha', '<=', $semana_req->fecha_final)
                        ->where('id_empresa', $finca)
                        ->orderBy('fecha')
                        ->get();
                    if (count($labores_campo) > 0) {    // tiene labores en la semana
                        array_push($ids_ciclos, $c->id_ciclo);
                        array_push($listado, [
                            'ciclo' => $c,
                            'fenograma' => $fenograma,
                            'labores' => $labores_campo,
                            'aplicacion' => $aplicacion,
                        ]);
                        foreach ($labores_campo as $app_campo)
                            foreach ($app_campo->detalles as $det) {
                                if ($det->id_producto != '' && !in_array($det->producto, $productos))
                                    array_push($productos, $det->producto);
                                if ($det->id_mano_obra != '' && !in_array($det->mano_obra, $mano_obras))
                                    array_push($mano_obras, $det->mano_obra);
                            }
                    } else {
                        $dias_ciclo_ini_sem = difFechas($semana_req->fecha_inicial, $c->fecha_inicio)->days;
                        $dias_ciclo_fin_sem = difFechas($semana_req->fecha_final, $c->fecha_inicio)->days;

                        if (($dias_ciclo_ini_sem >= $dia_ini && $dias_ciclo_ini_sem <= $dia_fin) ||
                            ($dias_ciclo_fin_sem >= $dia_ini && $dias_ciclo_fin_sem <= $dia_fin)
                        ) {   // es un ciclo q le toca en la semana
                            array_push($ids_ciclos, $c->id_ciclo);
                            array_push($listado, [
                                'ciclo' => $c,
                                'fenograma' => $fenograma,
                                'labores' => [],
                                'aplicacion' => $aplicacion,
                            ]);
                        }
                    }
                }
            }
        }
        /* order by fecha_inicio */
        if (count($listado) > 0) {
            for ($i = 0; $i < count($listado) - 1; $i++) {
                for ($y = $i + 1; $y < count($listado); $y++) {
                    if ($listado[$i]['ciclo']->fecha_inicio < $listado[$y]['ciclo']->fecha_inicio) {
                        $temp = $listado[$i];
                        $listado[$i] = $listado[$y];
                        $listado[$y] = $temp;
                    }
                }
            }
        }
        return [
            'listado' => $listado,
            'semana_actual' => $semana_actual,
            'semana_req' => $semana_req,
            'app_matriz' => $app_matriz,
            'productos' => $productos,
            'mano_obras' => $mano_obras,
        ];
    }

    static function listar_desbrote($request, $app_matriz)
    {
        $mezclas = $app_matriz->mezclas;
        $semana_actual = getSemanaByDate(hoy());
        $semana_req = getObjSemana($request->semana);
        $aplicaciones = $app_matriz->aplicaciones;
        $listado = [];
        $ids_ciclos = [];
        $mano_obras = [];
        foreach ($mezclas as $mezcla)
            foreach ($mezcla->detalles as $det) {
                if ($det->id_mano_obra != '' && !in_array($det->mano_obra, $mano_obras))
                    array_push($mano_obras, $det->mano_obra);
            }
        foreach ($aplicaciones as $aplicacion) {
            $ciclos = Ciclo::where('estado', 1);
            if ($semana_req->codigo >= $semana_actual->codigo)
                $ciclos = $ciclos->where('activo', 1);
            else
                $ciclos = $ciclos->where('fecha_fin', '>=', $semana_req->fecha_inicial);
            if ($aplicacion->poda_siembra != 'T') {
                $ciclos = $ciclos->where('poda_siembra', $aplicacion->poda_siembra);
            }
            $ciclos = $ciclos->orderBy('fecha_inicio', 'desc')->get();
            $rangos = [];
            for ($i = 0; $i < $aplicacion->repeticiones; $i++) {
                if ($aplicacion->frecuencia > 0)
                    $rangos[] = $aplicacion->dia_ini + ($aplicacion->frecuencia * $i);
                else
                    $rangos[] = $aplicacion->dia_ini + (7 * $i);
            }
            foreach ($ciclos as $c) {
                if (!in_array($c->id_ciclo, $ids_ciclos)) {
                    $labores_campo = AplicacionCampo::where('id_ciclo', $c->id_ciclo)
                        ->where('id_aplicacion', $aplicacion->id_aplicacion)
                        ->where('fecha', '>=', $semana_req->fecha_inicial)
                        ->where('fecha', '<=', $semana_req->fecha_final)
                        ->orderBy('fecha')
                        ->get();
                    if (count($labores_campo) > 0) {    // tiene labores en la semana
                        array_push($ids_ciclos, $c->id_ciclo);
                        array_push($listado, [
                            'ciclo' => $c,
                            'labores' => $labores_campo,
                            'aplicacion' => $aplicacion,
                        ]);
                        foreach ($labores_campo as $app_campo)
                            foreach ($app_campo->detalles as $det) {
                                if ($det->id_mano_obra != '' && !in_array($det->mano_obra, $mano_obras))
                                    array_push($mano_obras, $det->mano_obra);
                            }
                    } else {
                        $dias_ciclo_ini_sem = difFechas($semana_req->fecha_inicial, $c->fecha_inicio)->days;
                        $dias_ciclo_fin_sem = difFechas($semana_req->fecha_final, $c->fecha_inicio)->days;

                        foreach ($rangos as $pos_r => $r) {
                            if ($r >= $dias_ciclo_ini_sem && $r <= $dias_ciclo_fin_sem) {   // es un ciclo q le toca en la semana
                                array_push($ids_ciclos, $c->id_ciclo);
                                array_push($listado, [
                                    'ciclo' => $c,
                                    'labores' => [],
                                    'aplicacion' => $aplicacion,
                                    'fecha' => opDiasFecha('+', $r, $c->fecha_inicio),
                                    'repeticion' => $pos_r + 1,
                                ]);
                                break;
                            }
                        }
                    }
                }
            }
        }
        /* order by fecha_inicio */
        if (count($listado) > 0) {
            for ($i = 0; $i < count($listado) - 1; $i++) {
                for ($y = $i + 1; $y < count($listado); $y++) {
                    if ($listado[$i]['ciclo']->fecha_inicio < $listado[$y]['ciclo']->fecha_inicio) {
                        $temp = $listado[$i];
                        $listado[$i] = $listado[$y];
                        $listado[$y] = $temp;
                    }
                }
            }
        }

        $mezcla = count($mezclas) > 0 ? $mezclas[0] : '';
        $detalles_mezcla = $mezcla != '' ? $mezcla->detalles : [];
        return [
            'listado' => $listado,
            'semana_actual' => $semana_actual,
            'semana_req' => $semana_req,
            'app_matriz' => $app_matriz,
            'mezcla' => $mezcla,
            'detalles_mezcla' => $detalles_mezcla,
            'mano_obras' => $mano_obras,
        ];
    }

    static function listar_siembra($request, $app_matriz)
    {
        en_desarrollo();
        $mezclas = $app_matriz->mezclas;
        $semana_actual = getSemanaByDate(hoy());
        $semana_req = getObjSemana($request->semana);
        $aplicaciones = $app_matriz->aplicaciones;
        $listado = [];
        $ids_modulos = [];
        $mano_obras = [];
        foreach ($mezclas as $mezcla)
            foreach ($mezcla->detalles as $det) {
                if ($det->id_mano_obra != '' && !in_array($det->mano_obra, $mano_obras))
                    array_push($mano_obras, $det->mano_obra);
            }
        foreach ($aplicaciones as $aplicacion) {
            $proys = ProyeccionModulo::where('estado', 1)
                ->where('estado', 1)
                ->where('fecha_inicio', '>=', $semana_req->fecha_inicial)
                ->where('fecha_inicio', '<=', $semana_req->fecha_final)
                ->where('poda_siembra', 0)
                ->orderBy('fecha_inicio')
                ->get();
            $rangos = [];
            for ($i = 0; $i < $aplicacion->repeticiones; $i++) {
                if ($aplicacion->frecuencia > 0)
                    $rangos[] = $aplicacion->dia_ini + ($aplicacion->frecuencia * $i);
                else
                    $rangos[] = $aplicacion->dia_ini + (7 * $i);
            }
            foreach ($proys as $p) {
                if (!in_array($p->id_modulo, $ids_modulos)) {
                    $labores_campo = AplicacionCampo::where('id_modulo', $p->id_modulo)
                        ->where('id_aplicacion', $aplicacion->id_aplicacion)
                        ->where('fecha', '>=', $semana_req->fecha_inicial)
                        ->where('fecha', '<=', $semana_req->fecha_final)
                        ->orderBy('fecha')
                        ->get();
                    if (count($labores_campo) > 0) {    // tiene labores en la semana
                        array_push($ids_modulos, $p->id_modulo);
                        array_push($listado, [
                            'proy' => $p,
                            'labores' => $labores_campo,
                            'aplicacion' => $aplicacion,
                        ]);
                        foreach ($labores_campo as $app_campo)
                            foreach ($app_campo->detalles as $det) {
                                if ($det->id_mano_obra != '' && !in_array($det->mano_obra, $mano_obras))
                                    array_push($mano_obras, $det->mano_obra);
                            }
                    } else {
                        $dias_ciclo_ini_sem = difFechas($semana_req->fecha_inicial, $p->fecha_inicio)->days;
                        $dias_ciclo_fin_sem = difFechas($semana_req->fecha_final, $p->fecha_inicio)->days;

                        foreach ($rangos as $pos_r => $r) {
                            if ($r >= $dias_ciclo_ini_sem && $r <= $dias_ciclo_fin_sem) {   // es un ciclo q le toca en la semana
                                array_push($ids_modulos, $p->id_modulo);
                                array_push($listado, [
                                    'proy' => $p,
                                    'labores' => [],
                                    'aplicacion' => $aplicacion,
                                    'fecha' => opDiasFecha('+', $r, $p->fecha_inicio),
                                    'repeticion' => $pos_r + 1,
                                ]);
                                break;
                            }
                        }
                    }
                }
            }
        }
        /* order by fecha_inicio */
        if (count($listado) > 0) {
            for ($i = 0; $i < count($listado) - 1; $i++) {
                for ($y = $i + 1; $y < count($listado); $y++) {
                    if ($listado[$i]['proy']->fecha_inicio < $listado[$y]['proy']->fecha_inicio) {
                        $temp = $listado[$i];
                        $listado[$i] = $listado[$y];
                        $listado[$y] = $temp;
                    }
                }
            }
        }

        $mezcla = count($mezclas) > 0 ? $mezclas[0] : '';
        $detalles_mezcla = $mezcla != '' ? $mezcla->detalles : [];
        dd($listado);
        return [
            'listado' => $listado,
            'semana_actual' => $semana_actual,
            'semana_req' => $semana_req,
            'app_matriz' => $app_matriz,
            'mezcla' => $mezcla,
            'detalles_mezcla' => $detalles_mezcla,
            'mano_obras' => $mano_obras,
        ];
    }

    /* ------------------------ STORE -------------------------- */
    public function store_labor(Request $request)
    {
        $aplicacion = Aplicacion::find($request->aplicacion);
        $app_matriz = $aplicacion->aplicacion_matriz;
        if ($app_matriz->nombre == 'ACIDO GIBERELICO')
            return self::store_labor_giberelico($request);
        if ($app_matriz->nombre == 'DESBROTE')
            return self::store_labor_desbrote($request, $app_matriz);
    }

    static function store_labor_giberelico($request)
    {
        $valida = Validator::make($request->all(), [
            'ciclo' => 'required',
            'aplicacion' => 'required',
            'fecha' => 'required',
            'repeticion' => 'required',
            'camas' => 'required',
            'litros_x_cama' => 'required',
            'cc_x_planta' => 'required',
        ], [
            'ciclo.required' => 'El ciclo es obligatorio',
            'aplicacion.required' => 'La aplicación es obligatoria',
            'fecha.required' => 'La fecha es obligatoria',
            'repeticion.required' => 'La repetición es obligatoria',
            'camas.required' => 'Las camas son obligatorias',
            'litros_x_cama.required' => 'Los litros por cama son obligatorios',
            'cc_x_planta.required' => 'Los cc por planta son obligatorios',
        ]);
        if (!$valida->fails()) {
            $finca = getFincaActiva();
            $existe = AplicacionCampo::All()
                ->where('id_ciclo', $request->ciclo)
                ->where('id_aplicacion', $request->aplicacion)
                ->where('repeticion', $request->repeticion)
                ->where('fecha', $request->fecha)
                ->where('id_empresa', $finca)
                ->first();
            if ($existe == '') {
                $model = new AplicacionCampo();
                $model->id_ciclo = $request->ciclo;
                $model->id_aplicacion = $request->aplicacion;
                $model->fecha = $request->fecha;
                $model->repeticion = $request->repeticion;
                $model->camas = $request->camas;
                $model->litro_x_cama = $request->litros_x_cama;
                $model->cc_x_planta = $request->cc_x_planta;
                $model->id_empresa = $finca;

                if ($model->save()) {
                    $model = AplicacionCampo::All()->last();
                    $success = true;
                    $msg = 'Se ha <strong>creado</strong> el registro de ' . $model->aplicacion->nombre . ' satisfactoriamente';
                    bitacora('aplicacion_campo', $model->id_aplicacion_campo, 'I', 'Inserción satisfactoria de un nuevo aplicacion_campo');

                    if ($model->repeticion == 1) {
                        $fecha_desde = $model->fecha >= hoy() ? hoy() : $model->fecha;
                        $posteriores = CicloLuz::All()
                            ->where('id_ciclo', $model->id_ciclo)
                            ->where('fecha', '>=', $fecha_desde)
                            ->where('fecha', '<=', hoy());
                        foreach ($posteriores as $p) {
                            $p->inicio_luz = difFechas($model->fecha, $model->ciclo->fecha_inicio)->days;
                            $p->save();
                        }
                    }
                } else {
                    $success = false;
                    $msg = 'Ha ocurrido un problema al guardar la información al sistema';
                }
            } else {
                $success = false;
                $msg = 'Ya existe un registro de ' . $existe->aplicacion->nombre . ' en el día indicado para este módulo';
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
            $msg = '<p class="text-center">¡Por favor corrija los siguientes errores!</p>' .
                '<ul>' .
                $errores .
                '</ul>';
        }
        return [
            'mensaje' => $msg,
            'success' => $success
        ];
    }

    static function store_labor_desbrote($request, $app_matriz)
    {
        $valida = Validator::make($request->all(), [
            'ciclo' => 'required',
            'aplicacion' => 'required',
            'fecha' => 'required',
            'repeticion' => 'required',
            'horas_dia' => 'required',
            'plantas' => 'required',
            'hombres_dia' => 'required',
            'horas_necesarias' => 'required',
        ], [
            'ciclo.required' => 'El ciclo es obligatorio',
            'aplicacion.required' => 'La aplicación es obligatoria',
            'fecha.required' => 'La fecha es obligatoria',
            'repeticion.required' => 'La repetición es obligatoria',
            'horas_dia.required' => 'Las horas del día son obligatorias',
            'plantas.required' => 'Las plantas son obligatorias',
            'hombres_dia.required' => 'Los hombres del dia son obligatorios',
            'horas_necesarias.required' => 'Las horas necesarias son obligatorias',
        ]);
        if (!$valida->fails()) {
            $existe = AplicacionCampo::All()
                ->where('id_ciclo', $request->ciclo)
                ->where('id_aplicacion', $request->aplicacion)
                ->where('fecha', $request->fecha)
                ->first();
            if ($existe == '') {
                $model = new AplicacionCampo();
                $model->id_ciclo = $request->ciclo;
                $model->id_aplicacion = $request->aplicacion;
                $model->fecha = $request->fecha;
                $model->repeticion = $request->repeticion;
                $model->horas_dia = $request->horas_dia;
                $model->plantas = $request->plantas;
                $model->hombres_dia = $request->hombres_dia;
                $model->horas_necesarias = $request->horas_necesarias;

                if ($model->save()) {
                    $model = AplicacionCampo::All()->last();
                    $success = true;
                    $msg = 'Se ha <strong>creado</strong> el registro de ' . $model->aplicacion->nombre . ' satisfactoriamente';
                    bitacora('aplicacion_campo', $model->id_aplicacion_campo, 'I', 'Inserción satisfactoria de un nuevo aplicacion_campo');

                    /* ----------------- GUARDAR MEZCLA de (MO) ----------------- */
                    foreach ($request->data as $d) {
                        $det = new DetalleAplicacionCampo();
                        $det->id_aplicacion_campo = $model->id_aplicacion_campo;
                        $det->id_mano_obra = $d['mo'];
                        $det->dosis = $d['dosis'];
                        $det->save();
                    }
                } else {
                    $success = false;
                    $msg = 'Ha ocurrido un problema al guardar la información al sistema';
                }
            } else {
                $success = false;
                $msg = 'Ya existe un registro de ' . $existe->aplicacion->nombre . ' en el día indicado para este módulo';
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
            $msg = '<p class="text-center">¡Por favor corrija los siguientes errores!</p>' .
                '<ul>' .
                $errores .
                '</ul>';
        }
        return [
            'mensaje' => $msg,
            'success' => $success
        ];
    }

    static function duplicar_labor(Request $request)
    {
        $original = AplicacionCampo::find($request->app_campo);

        $model = new AplicacionCampo();
        $model->id_ciclo = $original->id_ciclo;
        $model->id_aplicacion = $original->id_aplicacion;
        $model->fecha = opDiasFecha('+', 1, $original->fecha);
        $model->repeticion = $original->repeticion;
        $model->horas_dia = $original->horas_dia;
        $model->hombres_dia = $original->hombres_dia;
        $model->id_empresa = $original->id_empresa;

        $parte_horas = porcentaje($original->horas_dia, $original->horas_necesarias, 1);
        $plantas_1 = porcentaje(100 - $parte_horas, $original->plantas, 2);
        $plantas_2 = porcentaje($parte_horas, $original->plantas, 2);
        $model->plantas = $plantas_2;
        $original->plantas = $plantas_1;

        $horas_necesarias = $original->horas_necesarias;
        $original->horas_necesarias = $original->horas_necesarias - $original->horas_dia;
        $model->horas_necesarias = $horas_necesarias - $original->horas_necesarias;

        if ($model->save() && $original->save()) {
            $model = AplicacionCampo::All()->last();
            $success = true;
            $msg = 'Se ha <strong>creado</strong> el registro de ' . $model->aplicacion->nombre . ' satisfactoriamente';
            bitacora('aplicacion_campo', $model->id_aplicacion_campo, 'I', 'Inserción satisfactoria de un nuevo aplicacion_campo');

            /* ----------------- GUARDAR MEZCLA de (MO) ----------------- */
            foreach ($original->detalles as $d) {
                $det = new DetalleAplicacionCampo();
                $det->id_aplicacion_campo = $model->id_aplicacion_campo;
                $det->id_mano_obra = $d->id_mano_obra;
                $det->dosis = $d->dosis;
                $det->save();
            }
        } else {
            $success = false;
            $msg = 'Ha ocurrido un problema al guardar la información al sistema';
        }
        return [
            'mensaje' => $msg,
            'success' => $success
        ];
    }

    static function store_all_labor(Request $request)
    {
        $app_matriz = AplicacionMatriz::find($request->labor);
        if ($app_matriz->nombre == 'DESBROTE') {
            $success = true;
            $msg = 'Se han <strong>grabado</strong> los registros satisfactoriamente';
            foreach ($request->data as $data) {
                $existe = AplicacionCampo::All()
                    ->where('id_ciclo', $data['ciclo'])
                    ->where('id_aplicacion', $data['aplicacion'])
                    ->where('fecha', $data['fecha'])
                    ->first();
                if ($existe == '') {
                    $model = new AplicacionCampo();
                    $model->id_ciclo = $data['ciclo'];
                    $model->id_aplicacion = $data['aplicacion'];
                }
                $model->fecha = $data['fecha'];
                $model->repeticion = $data['repeticion'];
                $model->hora_ini = $data['hora_ini'];
                $model->hora_fin = $data['hora_fin'];
                $model->horas_dia = $data['horas_dia'];
                $model->plantas = $data['plantas'];
                $model->hombres_dia = $data['hombres_dia'];
                $model->horas_necesarias = $data['horas_necesarias'];

                if ($model->save()) {
                    if ($existe == '')
                        $model = AplicacionCampo::All()->last();
                    bitacora('aplicacion_campo', $model->id_aplicacion_campo, 'I', 'Inserción satisfactoria de un nuevo aplicacion_campo');

                    /* ----------------- GUARDAR MEZCLA de (MO) ----------------- */
                    foreach ($model->detalles as $det)
                        $det->delete();
                    foreach ($data['detalles'] as $d) {
                        $det = new DetalleAplicacionCampo();
                        $det->id_aplicacion_campo = $model->id_aplicacion_campo;
                        $det->id_mano_obra = $d['mo'];
                        $det->dosis = $d['dosis'];
                        $det->save();
                    }
                } else {
                    $success = false;
                    $msg = 'Ha ocurrido un problema al guardar la información al sistema';
                }
            }
            return [
                'mensaje' => $msg,
                'success' => $success
            ];
        }
    }

    /* ------------------------ UPDATE -------------------------- */

    public function update_labor(Request $request)
    {
        $aplicacion = Aplicacion::find($request->aplicacion);
        $app_matriz = $aplicacion->aplicacion_matriz;
        if ($app_matriz->nombre == 'ACIDO GIBERELICO')
            return self::update_labor_giberelico($request);
        if ($app_matriz->nombre == 'DESBROTE')
            return self::update_labor_desbrote($request, $app_matriz);
    }

    static function update_labor_giberelico($request)
    {
        $valida = Validator::make($request->all(), [
            'ciclo' => 'required',
            'app_campo' => 'required',
            'aplicacion' => 'required',
            'fecha' => 'required',
            'repeticion' => 'required',
            'camas' => 'required',
            'litros_x_cama' => 'required',
            'cc_x_planta' => 'required',
        ], [
            'ciclo.required' => 'El ciclo es obligatorio',
            'app_campo.required' => 'La labor del día es obligatoria',
            'aplicacion.required' => 'La aplicación es obligatoria',
            'fecha.required' => 'La fecha es obligatoria',
            'repeticion.required' => 'La repetición es obligatoria',
            'camas.required' => 'Las camas son obligatorias',
            'litros_x_cama.required' => 'Los litros por cama son obligatorios',
            'cc_x_planta.required' => 'Los cc por planta son obligatorios',
        ]);
        if (!$valida->fails()) {
            $finca = getFincaActiva();
            $existe = AplicacionCampo::All()
                ->where('id_ciclo', $request->ciclo)
                ->where('id_aplicacion', $request->aplicacion)
                ->where('fecha', $request->fecha)
                ->where('repeticion', $request->repeticion)
                ->where('id_aplicacion_campo', '!=', $request->app_campo)
                ->where('id_empresa', $finca)
                ->first();
            if ($existe == '') {
                $model = AplicacionCampo::find($request->app_campo);
                $model->fecha = $request->fecha;
                $model->repeticion = $request->repeticion;
                $model->camas = $request->camas;
                $model->litro_x_cama = $request->litros_x_cama;
                $model->cc_x_planta = $request->cc_x_planta;

                if ($model->save()) {
                    $success = true;
                    $msg = 'Se ha <strong>actualizado</strong> el registro de ' . $model->aplicacion->nombre . ' satisfactoriamente';
                    bitacora('aplicacion_campo', $model->id_aplicacion_campo, 'U', 'Actualizacion satisfactoria de un nuevo aplicacion_campo');
                } else {
                    $success = false;
                    $msg = 'Ha ocurrido un problema al guardar la información al sistema';
                }
            } else {
                $success = false;
                $msg = 'Ya existe un registro de ' . $existe->aplicacion->nombre . ' en el día indicado para este módulo';
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
            $msg = '<p class="text-center">¡Por favor corrija los siguientes errores!</p>' .
                '<ul>' .
                $errores .
                '</ul>';
        }
        return [
            'mensaje' => $msg,
            'success' => $success
        ];
    }

    static function update_labor_desbrote($request)
    {
        $valida = Validator::make($request->all(), [
            'ciclo' => 'required',
            'app_campo' => 'required',
            'aplicacion' => 'required',
            'fecha' => 'required',
            'repeticion' => 'required',
            'horas_dia' => 'required',
            'plantas' => 'required',
            'hombres_dia' => 'required',
            'horas_necesarias' => 'required',
        ], [
            'ciclo.required' => 'El ciclo es obligatorio',
            'app_campo.required' => 'La labor del día es obligatoria',
            'aplicacion.required' => 'La aplicación es obligatoria',
            'fecha.required' => 'La fecha es obligatoria',
            'repeticion.required' => 'La repetición es obligatoria',
            'horas_dia.required' => 'Las horas del día son obligatorias',
            'plantas.required' => 'Las plantas son obligatorias',
            'hombres_dia.required' => 'Los hombres del dia son obligatorios',
            'horas_necesarias.required' => 'Las horas necesarias son obligatorias',
        ]);
        if (!$valida->fails()) {
            $existe = AplicacionCampo::All()
                ->where('id_ciclo', $request->ciclo)
                ->where('id_aplicacion', $request->aplicacion)
                ->where('fecha', $request->fecha)
                ->where('id_aplicacion_campo', '!=', $request->app_campo)
                ->first();
            if ($existe == '') {
                $model = AplicacionCampo::find($request->app_campo);
                $model->fecha = $request->fecha;
                $model->repeticion = $request->repeticion;
                $model->horas_dia = $request->horas_dia;
                $model->plantas = $request->plantas;
                $model->hombres_dia = $request->hombres_dia;
                $model->horas_necesarias = $request->horas_necesarias;

                if ($model->save()) {
                    $success = true;
                    $msg = 'Se ha <strong>actualizado</strong> el registro de ' . $model->aplicacion->nombre . ' satisfactoriamente';
                    bitacora('aplicacion_campo', $model->id_aplicacion_campo, 'U', 'Actualizacion satisfactoria de un nuevo aplicacion_campo');

                    /* ----------------- GUARDAR MEZCLA de (MO) ----------------- */
                    foreach ($model->detalles as $det)
                        $det->delete();
                    foreach ($request->data as $d) {
                        $det = new DetalleAplicacionCampo();
                        $det->id_aplicacion_campo = $model->id_aplicacion_campo;
                        $det->id_mano_obra = $d['mo'];
                        $det->dosis = $d['dosis'];
                        $det->save();
                    }
                } else {
                    $success = false;
                    $msg = 'Ha ocurrido un problema al guardar la información al sistema';
                }
            } else {
                $success = false;
                $msg = 'Ya existe un registro de ' . $existe->aplicacion->nombre . ' en el día indicado para este módulo';
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
            $msg = '<p class="text-center">¡Por favor corrija los siguientes errores!</p>' .
                '<ul>' .
                $errores .
                '</ul>';
        }
        return [
            'mensaje' => $msg,
            'success' => $success
        ];
    }

    public function delete_labor(Request $request)
    {
        $model = AplicacionCampo::find($request->app_campo);
        foreach ($model->detalles as $det)
            $det->delete();
        $model->delete();
        return [
            'success' => true,
            'mensaje' => 'Se ha <strong>ELIMINADO</strong> el registro satisfactoriamente',
        ];
    }

    public function add_adicional(Request $request)
    {
        $finca = getFincaActiva();
        $app_matriz = AplicacionMatriz::find($request->labor);
        $ids_variedades = [];
        foreach ($app_matriz->aplicaciones->where('estado', 1) as $app)
            foreach ($app->variedades as $v)
                array_push($ids_variedades, $v->id_variedad);
        $ciclos = Ciclo::join('modulo as m', 'm.id_modulo', '=', 'ciclo.id_modulo')
            ->where('ciclo.estado', 1)
            ->where('m.id_sector', $request->sector)
            ->where('ciclo.activo', 1)
            ->where('ciclo.id_empresa', $finca)
            ->whereIn('ciclo.id_variedad', $ids_variedades)
            ->orderBy('m.nombre')
            ->get();
        if ($app_matriz->nombre == 'ACIDO GIBERELICO') {
            $view = 'add_adicional_giberelico';
            $datos = [
                'ciclos' => $ciclos,
                'semana' => getObjSemana($request->semana),
            ];
        }
        if ($app_matriz->nombre == 'DESBROTE') {
            $view = 'add_adicional_desbrote';
            $mezclas = $app_matriz->mezclas;
            $mezcla = count($mezclas) > 0 ? $mezclas[0] : '';
            $detalles_mezcla = $mezcla != '' ? $mezcla->detalles : [];
            $mano_obras = [];
            foreach ($mezclas as $mezcla)
                foreach ($mezcla->detalles as $det) {
                    if ($det->id_mano_obra != '' && !in_array($det->mano_obra, $mano_obras))
                        array_push($mano_obras, $det->mano_obra);
                }
            $datos = [
                'ciclos' => $ciclos,
                'semana' => getObjSemana($request->semana),
                'app_matriz' => $app_matriz,
                'mezcla' => $mezcla,
                'detalles_mezcla' => $detalles_mezcla,
                'mano_obras' => $mano_obras,
            ];
        }
        return view('adminlte.gestion.campo.labores_campo.forms.' . $view, $datos);
    }

    public function seleccionar_modulo(Request $request)
    {
        $ciclo = Ciclo::find($request->ciclo);
        $labor = AplicacionMatriz::find($request->labor);
        $aplicaciones = [];
        foreach ($labor->aplicaciones->where('estado', 1) as $app) {
            if ($app->poda_siembra == 'T' || $app->poda_siembra == $ciclo->poda_siembra) {
                $ids_variedades = [];
                foreach ($app->variedades as $v)
                    array_push($ids_variedades, $v->id_variedad);
                if (in_array($ciclo->variedad->id_planta, $ids_variedades))
                    array_push($aplicaciones, $app);
            }
        }
        return [
            'planta' => $ciclo->variedad->planta->siglas,
            'variedad' => $ciclo->variedad->siglas,
            'poda_siembra' => $ciclo->modulo->getPodaSiembraByCiclo($ciclo->id_ciclo),
            'fecha_inicio' => convertDateToText($ciclo->fecha_inicio),
            'dias_ciclo' => difFechas(hoy(), $ciclo->fecha_inicio)->days,
            'aplicaciones' => $aplicaciones,
        ];
    }

    public function seleccionar_labor(Request $request)
    {
        $ciclo = Ciclo::find($request->ciclo);
        $fenograma = ResumenFenogramaEjecucion::All()
            ->where('id_ciclo', $request->ciclo)
            ->first();
        $aplicacion = Aplicacion::find($request->app);
        $app_matriz = $aplicacion->aplicacion_matriz;
        if ($app_matriz->nombre == 'ACIDO GIBERELICO') {
            $camas = calcularCamas($ciclo->area);
            $densidad = $fenograma != '' ? $fenograma->densidad_plantas_ini_m2 : 0;
            $cc_x_planta = $aplicacion->litro_x_cama;
            $litros_x_cama = $densidad * 45 * ($cc_x_planta / 1000);
            $lastLabor = $ciclo->getLastLaborByLabor($aplicacion->id_aplicacion);
            $repeticion = isset($lastLabor) ? $lastLabor->repeticion + 1 : 1;
            return [
                'repeticion' => $repeticion,
                'camas' => $camas,
                'densidad' => $densidad,
                'cc_x_planta' => $cc_x_planta,
                'litros_x_cama' => round($litros_x_cama, 2),
            ];
        }
        if ($app_matriz->nombre == 'DESBROTE') {
            $plantas = $ciclo->plantas_actuales();
            $lastLabor = $ciclo->getLastLaborByLabor($aplicacion->id_aplicacion);
            $repeticion = isset($lastLabor) ? $lastLabor->repeticion + 1 : 1;
            return [
                'repeticion' => $repeticion,
                'plantas' => $plantas,
            ];
        }
    }

    /* ------------------------ STORE ADICIONAL -------------------------- */

    public function store_adicional(Request $request)
    {
        $app_matriz = AplicacionMatriz::find($request->labor);
        if ($app_matriz->nombre == 'ACIDO GIBERELICO')
            return self::store_adicional_giberelico($request);
        if ($app_matriz->nombre == 'DESBROTE')
            return self::store_adicional_desbrote($request);
    }

    static function store_adicional_giberelico($request)
    {
        $success = true;
        $msg = 'Se ha <strong>adicionado</strong> la información satisfactoriamente';
        $finca = getFincaActiva();
        foreach ($request->data as $data) {
            $existe = AplicacionCampo::All()
                ->where('id_ciclo', $data['ciclo'])
                ->where('id_aplicacion', $data['aplicacion'])
                ->where('repeticion', $data['repeticion'])
                ->where('fecha', $data['fecha'])
                ->where('id_empresa', $finca)
                ->first();
            if ($existe == '') {
                $model = new AplicacionCampo();
                $model->id_ciclo = $data['ciclo'];
                $model->id_aplicacion = $data['aplicacion'];
                $model->fecha = $data['fecha'];
                $model->repeticion = $data['repeticion'];
                $model->camas = $data['camas'];
                $model->litro_x_cama = $data['litros_x_cama'];
                $model->cc_x_planta = $data['cc_x_planta'];
                $model->id_empresa = $finca;

                if ($model->save()) {
                    $model = AplicacionCampo::All()->last();
                    bitacora('aplicacion_campo', $model->id_aplicacion_campo, 'I', 'Inserción satisfactoria de un nuevo aplicacion_campo');
                }
            } else {
                $ciclo = Ciclo::find($data['ciclo']);
                $success = false;
                $msg = 'Ya existe un registro de ' . $existe->aplicacion->nombre . ' en el día indicado para el módulo: ' . $ciclo->modulo->nombre;
                break;
            }
        }
        return [
            'mensaje' => $msg,
            'success' => $success
        ];
    }

    static function store_adicional_desbrote($request)
    {
        $success = true;
        $msg = 'Se ha <strong>adicionado</strong> la información satisfactoriamente';
        foreach ($request->data as $data) {
            $existe = AplicacionCampo::All()
                ->where('id_ciclo', $data['ciclo'])
                ->where('id_aplicacion', $data['aplicacion'])
                ->where('fecha', $data['fecha'])
                ->first();
            if ($existe == '') {
                $model = new AplicacionCampo();
                $model->id_ciclo = $data['ciclo'];
                $model->id_aplicacion = $data['aplicacion'];
                $model->fecha = $data['fecha'];
                $model->repeticion = $data['repeticion'];
                $model->plantas = $data['plantas'];
                $model->horas_dia = $data['horas_dia'];
                $model->hombres_dia = $data['hombres_dia'];
                $model->horas_necesarias = $data['horas_necesarias'];

                if ($model->save()) {
                    $model = AplicacionCampo::All()->last();
                    bitacora('aplicacion_campo', $model->id_aplicacion_campo, 'I', 'Inserción satisfactoria de un nuevo aplicacion_campo');

                    /* ----------------- GUARDAR MEZCLA de (MO) ----------------- */
                    foreach ($data['detalles'] as $d) {
                        $det = new DetalleAplicacionCampo();
                        $det->id_aplicacion_campo = $model->id_aplicacion_campo;
                        $det->id_mano_obra = $d['mo'];
                        $det->dosis = $d['dosis'];
                        $det->save();
                    }
                }
            } else {
                $ciclo = Ciclo::find($data['ciclo']);
                $success = false;
                $msg = 'Ya existe un registro de ' . $existe->aplicacion->nombre . ' en el día indicado para el módulo: ' . $ciclo->modulo->nombre;
                break;
            }
        }
        return [
            'mensaje' => $msg,
            'success' => $success
        ];
    }

    public function aplicar_mezclas(Request $request)
    {
        $labor = AplicacionMatriz::find($request->labor);
        return view('adminlte.gestion.campo.labores_campo.forms.aplicar_mezclas', [
            'labor' => $labor
        ]);
    }

    public function seleccionar_mezcla(Request $request)
    {
        $finca = getFincaActiva();
        $ids_productos = [];
        $ids_mo = [];
        foreach ($request->data as $d) {
            if (isset($d['app_campo'])) {
                $app_campo = AplicacionCampo::find($d['app_campo']);
                foreach ($app_campo->detalles as $det) {
                    if ($det->id_producto != '' && !in_array($det->id_producto, $ids_productos))
                        array_push($ids_productos, $det->id_producto);
                    if ($det->id_mano_obra != '' && !in_array($det->id_mano_obra, $ids_mo))
                        array_push($ids_mo, $det->id_mano_obra);
                }
            }
        }
        $mezcla = AplicacionMezcla::find($request->mezcla);
        foreach ($mezcla->detalles as $det) {
            if ($det->id_producto != '' && !in_array($det->id_producto, $ids_productos))
                array_push($ids_productos, $det->id_producto);
            if ($det->id_mano_obra != '' && !in_array($det->id_mano_obra, $ids_mo))
                array_push($ids_mo, $det->id_mano_obra);
        }
        $productos = Producto::whereIn('id_producto', $ids_productos)
            ->where('id_empresa', $finca)
            ->orderBy('nombre')
            ->get();
        $mano_obras = ManoObra::whereIn('id_mano_obra', $ids_mo)
            ->where('id_empresa', $finca)
            ->orderBy('nombre')
            ->get();

        $other_productos = Producto::whereNotIn('id_producto', $ids_productos)
            ->where('estado', 1)
            ->where('id_empresa', $finca)
            ->orderBy('nombre')
            ->get();
        $other_mano_obras = ManoObra::whereNotIn('id_mano_obra', $ids_mo)
            ->where('estado', 1)
            ->where('id_empresa', $finca)
            ->orderBy('nombre')
            ->get();

        $listado = [];
        foreach ($request->data as $data) {
            $detalles = [];
            foreach ($mezcla->getModelDetalles() as $det) {
                $parametro = '';
                foreach ($det->parametros as $par) {
                    if ($par->id_variedad == $data['variedad']) {
                        $parametro = $par;
                    }
                }
                array_push($detalles, [
                    'detalle' => $det,
                    'parametro' => $parametro,
                ]);
            }
            $fenograma = ResumenFenogramaEjecucion::All()
                ->where('id_ciclo', $data['ciclo'])
                ->first();
            array_push($listado, [
                'ciclo' => Ciclo::find($data['ciclo']),
                'fenograma' => $fenograma,
                'data' => $data,
                'detalles' => $detalles,
            ]);
        }
        return view('adminlte.gestion.campo.labores_campo.forms._seleccionar_mezcla', [
            'mezcla' => $mezcla,
            'listado' => $listado,
            'productos' => $productos,
            'mano_obras' => $mano_obras,
            'unidad_medidas' => getUnidadesMedida(),
            'other_productos' => $other_productos,
            'other_mano_obras' => $other_mano_obras,
        ]);
    }

    public function store_mezclas(Request $request)
    {
        $finca = getFincaActiva();
        $success = true;
        $msg = 'Se ha <strong>APLICADO LA MEZCLA</strong> satisfactoriamente';
        foreach ($request->data as $data) {
            /* GRABAR LABOR */
            $app_campo = AplicacionCampo::find($data['app_campo']);
            $existe = AplicacionCampo::All()
                ->where('id_ciclo', $data['ciclo'])
                ->where('id_aplicacion', $data['aplicacion'])
                ->where('repeticion', $data['repeticion'])
                ->where('fecha', $data['fecha'])
                ->where('id_empresa', $finca)
                ->first();
            if (!isset($app_campo) && $existe == '') {
                $app_campo = new AplicacionCampo();
                $app_campo->id_ciclo = $data['ciclo'];
                $app_campo->id_aplicacion = $data['aplicacion'];
                $app_campo->fecha = $data['fecha'];
                $app_campo->repeticion = $data['repeticion'];
                $app_campo->camas = $data['camas'];
                $app_campo->litro_x_cama = $data['litros_x_cama'];
                $app_campo->cc_x_planta = $data['cc_x_planta'];
                $app_campo->id_empresa = $finca;
                $app_campo->save();
                $app_campo = AplicacionCampo::All()->last();
            }
            $app_campo->fecha = $data['fecha'];
            $app_campo->repeticion = $data['repeticion'];
            $app_campo->camas = $data['camas'];
            $app_campo->litro_x_cama = $data['litros_x_cama'];
            $app_campo->cc_x_planta = $data['cc_x_planta'];
            $app_campo->save();

            /* CHEQUEAR LUZ */
            if ($app_campo->repeticion == 1) {
                $fecha_desde = $app_campo->fecha >= hoy() ? hoy() : $app_campo->fecha;
                $posteriores = CicloLuz::All()
                    ->where('id_ciclo', $app_campo->id_ciclo)
                    ->where('fecha', '>=', $fecha_desde)
                    ->where('fecha', '<=', hoy());
                foreach ($posteriores as $p) {
                    $p->inicio_luz = difFechas($app_campo->fecha, $app_campo->ciclo->fecha_inicio)->days;
                    $p->save();
                }
            }

            /* DETALLES MEZCLA */
            foreach ($app_campo->detalles as $det)
                $det->delete();
            if ((isset($data['productos']) && count($data['productos']) > 0) || (isset($data['mano_obras']) && count($data['mano_obras']) > 0)) {
                if (isset($data['productos']))
                    foreach ($data['productos'] as $det) {
                        if (isset($det['dosis']) && $det['dosis'] > 0) {
                            $detalle = new DetalleAplicacionCampo();
                            $detalle->id_aplicacion_campo = $app_campo->id_aplicacion_campo;
                            $detalle->id_mano_obra = null;
                            $detalle->id_producto = $det['prod'];
                            $detalle->dosis = $det['dosis'];
                            $detalle->id_unidad_medida = isset($det['unidad_medida']) ? $det['unidad_medida'] : null;
                            $detalle->factor_conversion = isset($det['factor_conversion']) ? $det['factor_conversion'] : null;
                            $detalle->id_unidad_conversion = isset($det['unidad_conversion']) ? $det['unidad_conversion'] : null;
                            $detalle->save();
                        }
                    }
                if (isset($data['mano_obras']))
                    foreach ($data['mano_obras'] as $det) {
                        if (isset($det['dosis']) && $det['dosis'] > 0) {
                            $detalle = new DetalleAplicacionCampo();
                            $detalle->id_aplicacion_campo = $app_campo->id_aplicacion_campo;
                            $detalle->id_producto = null;
                            $detalle->id_mano_obra = $det['mo'];
                            $detalle->dosis = $det['dosis'];
                            $detalle->id_unidad_medida = isset($det['unidad_medida']) ? $det['unidad_medida'] : null;
                            $detalle->factor_conversion = isset($det['factor_conversion']) ? $det['factor_conversion'] : null;
                            $detalle->id_unidad_conversion = isset($det['unidad_conversion']) ? $det['unidad_conversion'] : null;
                            $detalle->save();
                        }
                    }
            }
        }

        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }

    public function exportar_reporte(Request $request)
    {
        $spread = new Spreadsheet();
        $app_matriz = AplicacionMatriz::find($request->labor);
        if ($app_matriz->nombre == 'ACIDO GIBERELICO') {
            $this->excel_reporte_giberelico($spread, $request);
        }
        $spread->getProperties()
            ->setTitle('Reporte_Labores');

        $fileName = "Reporte_Labores.xlsx";
        $writer = new Xlsx($spread);

        //--------------------------- GUARDAR EL EXCEL -----------------------

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        $writer->save('php://output');

        //$writer->save('/var/www/html/Dasalflor/storage/storage/excel/excel_prueba.xlsx');
    }

    public function excel_reporte_giberelico($spread, $request)
    {
        $finca = getFincaActiva();
        $semana_req = getObjSemana($request->semana);
        $app_matriz = AplicacionMatriz::find($request->labor);
        $ids_app = [];
        foreach ($app_matriz->aplicaciones as $app)
            array_push($ids_app, $app->id_aplicacion);
        $listado = AplicacionCampo::join('ciclo as c', 'c.id_ciclo', '=', 'aplicacion_campo.id_ciclo')
            ->join('modulo as m', 'm.id_modulo', '=', 'c.id_modulo')
            ->whereIn('aplicacion_campo.id_aplicacion', $ids_app)
            ->where('aplicacion_campo.fecha', '>=', $semana_req->fecha_inicial)
            ->where('aplicacion_campo.fecha', '<=', $semana_req->fecha_final)
            ->where('aplicacion_campo.id_empresa', $finca)
            ->where('m.id_sector', $request->sector)
            ->orderBy('aplicacion_campo.fecha')
            ->get();

        $productos = [];
        foreach ($listado as $app_campo)
            foreach ($app_campo->detalles as $det)
                if ($det->id_producto != '' && !in_array($det->producto, $productos))
                    array_push($productos, $det->producto);

        /* -------------------- CREAR HOJA EXCEL -------------------- */
        $columnas = getColumnasExcel();
        $objSheet = $spread->getActiveSheet()->setTitle('Reporte_Labores ' . $request->semana);

        $row = 1;
        setValueToCeldaExcel($objSheet, 'A' . $row, 'Variedad');
        setValueToCeldaExcel($objSheet, 'B' . $row, 'Módulo');
        setValueToCeldaExcel($objSheet, 'C' . $row, 'P/S');
        setValueToCeldaExcel($objSheet, 'D' . $row, 'Días');
        setValueToCeldaExcel($objSheet, 'E' . $row, 'Fecha');
        setValueToCeldaExcel($objSheet, 'F' . $row, 'Repetición');
        setValueToCeldaExcel($objSheet, 'G' . $row, 'Camas');
        setValueToCeldaExcel($objSheet, 'H' . $row, 'Litros x cama');
        setValueToCeldaExcel($objSheet, 'I' . $row, 'Volumen TOTAL');
        setValueToCeldaExcel($objSheet, 'J' . $row, 'PPM');
        setValueToCeldaExcel($objSheet, 'K' . $row, 'Gib.');
        setBgToCeldaExcel($objSheet, 'A' . $row . ':K' . $row, '00b388');  //verde
        $col = 10;
        $totales_prod = [];
        foreach ($productos as $p) {
            $col++;
            setValueToCeldaExcel($objSheet, $columnas[$col] . $row, $p->nombre);
            setBgToCeldaExcel($objSheet, $columnas[$col] . $row, '5a7177');  // dark
            $totales_prod[] = 0;
        }
        setColorTextToCeldaExcel($objSheet, 'A' . $row . ':' . $columnas[$col] . $row, 'FFFFFF');  // blanco

        $total_camas = 0;
        $total_litros = 0;
        $total_volumen = 0;
        $total_ppm = 0;
        $total_gib = 0;
        foreach ($listado as $labor) {
            $ciclo = $labor->ciclo;
            $modulo = $ciclo->modulo;
            $dias_ciclo = difFechas($labor->fecha, $ciclo->fecha_inicio)->days;

            $fecha = $labor->fecha;
            $repeticion = $labor->repeticion;
            $camas = $labor->camas;
            $total_camas += $camas;
            $litro_x_cama = $labor->litro_x_cama;
            $total_litros += $litro_x_cama;

            $row++;
            setValueToCeldaExcel($objSheet, 'A' . $row, $ciclo->variedad->nombre);
            setValueToCeldaExcel($objSheet, 'B' . $row, $modulo->nombre);
            setValueToCeldaExcel($objSheet, 'C' . $row, $modulo->getPodaSiembraByCiclo($ciclo->id_ciclo));
            setValueToCeldaExcel($objSheet, 'D' . $row, $dias_ciclo);
            setValueToCeldaExcel($objSheet, 'E' . $row, getDiaSemanaByFecha($fecha) . ' ' . convertDateToText($fecha));
            setValueToCeldaExcel($objSheet, 'F' . $row, $repeticion);
            setValueToCeldaExcel($objSheet, 'G' . $row, $camas);
            setValueToCeldaExcel($objSheet, 'H' . $row, $litro_x_cama);
            $volumen = round($camas * $litro_x_cama);
            $total_volumen += $volumen;
            setValueToCeldaExcel($objSheet, 'I' . $row, $volumen);
            $col = 10;
            $dosis_acido_giberelico = 0;
            foreach ($productos as $pos_p => $p) {
                $col++;
                $detalle = $labor != '' ? $labor->getDetalleByProducto($p->id_producto) : '';
                $dosis = '';
                if ($detalle != '') {
                    $dosis = $detalle->factor_conversion != '' ? round($detalle->dosis * $detalle->factor_conversion, 3) : $detalle->dosis;
                    $dosis = $dosis * $volumen;
                    if ($p->nombre == 'ACIDO GIBERELICO WOGIBB RODEL') {
                        $dosis_acido_giberelico = $dosis;
                        setValueToCeldaExcel($objSheet, 'J' . $row, $detalle->dosis);
                        setValueToCeldaExcel($objSheet, 'K' . $row, round($detalle->dosis * $detalle->factor_conversion, 3));
                        $total_ppm += $detalle->dosis;
                        $total_gib += $detalle->dosis * $detalle->factor_conversion;
                    }
                    $dosis .= $detalle->id_unidad_conversion != '' ? ' ' . $detalle->unidad_conversion->siglas : ' ' . $detalle->unidad_medida->siglas;
                }
                setValueToCeldaExcel($objSheet, $columnas[$col] . $row, round($dosis));
                $totales_prod[$pos_p] += round($dosis);
            }
        }

        /* TOTALES */
        $row++;
        $objSheet->mergeCells('A' . $row . ':F' . $row);
        setValueToCeldaExcel($objSheet, 'A' . $row, 'TOTALES');
        setValueToCeldaExcel($objSheet, 'G' . $row, number_format($total_camas, 2));
        setValueToCeldaExcel($objSheet, 'H' . $row, number_format($total_litros, 2));
        setValueToCeldaExcel($objSheet, 'I' . $row, number_format($total_volumen, 2));
        setValueToCeldaExcel($objSheet, 'J' . $row, number_format($total_ppm, 2));
        setValueToCeldaExcel($objSheet, 'K' . $row, number_format($total_gib, 2));
        setBgToCeldaExcel($objSheet, 'A' . $row . ':K' . $row, '00b388');  //verde
        setColorTextToCeldaExcel($objSheet, 'A' . $row . ':K' . $row, 'FFFFFF');  //blanco
        $col = 10;
        foreach ($totales_prod as $p) {
            $col++;
            setValueToCeldaExcel($objSheet, $columnas[$col] . $row, $p);
            setBgToCeldaExcel($objSheet, $columnas[$col] . $row, '5a7177');  // dark
            setColorTextToCeldaExcel($objSheet, $columnas[$col] . $row, 'FFFFFF');  // blanco
        }

        setTextCenterToCeldaExcel($objSheet, 'A1:' . $columnas[$col] . $row);
        setBorderToCeldaExcel($objSheet, 'A1:' . $columnas[$col] . $row);
        for ($i = 0; $i <= $col; $i++) {
            $objSheet->getColumnDimension($columnas[$i])->setAutoSize(true);
        }
    }

    public function ver_labores_by_ciclo(Request $request)
    {
        $labores = AplicacionCampo::where('id_ciclo', $request->ciclo)
            ->where('id_aplicacion', $request->aplicacion)
            ->orderBy('fecha')
            ->get();
        $ids_productos = [];
        $ids_mano_obras = [];
        foreach ($labores as $labor)
            foreach ($labor->detalles as $det) {
                if ($det->id_producto != '' && !in_array($det->id_producto, $ids_productos))
                    array_push($ids_productos, $det->id_producto);
                if ($det->id_mano_obra != '' && !in_array($det->id_mano_obra, $ids_mano_obras))
                    array_push($ids_mano_obras, $det->id_mano_obra);
            }
        $productos = Producto::whereIn('id_producto', $ids_productos)
            ->orderBy('nombre')
            ->get();
        $mano_obras = ManoObra::whereIn('id_mano_obra', $ids_mano_obras)
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.campo.labores_campo.partials._labores_ciclo', [
            'labores' => $labores,
            'productos' => $productos,
            'mano_obras' => $mano_obras,
            'ciclo' => Ciclo::find($request->ciclo),
            'aplicacion' => Aplicacion::find($request->aplicacion),
        ]);
    }

    public function update_aplicacion(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'id' => 'required',
            'fecha' => 'required',
            'repeticion' => 'required',
            'camas' => 'required',
            'litro_x_cama' => 'required',
        ], [
            'id.required' => 'La labor del día es obligatoria',
            'aplicacion.required' => 'La aplicación es obligatoria',
            'fecha.required' => 'La fecha es obligatoria',
            'repeticion.required' => 'La repetición es obligatoria',
            'camas.required' => 'Las camas son obligatorias',
            'litro_x_cama.required' => 'Los litros por cama son obligatorios',
        ]);
        if (!$valida->fails()) {
            $finca = getFincaActiva();
            $labor = AplicacionCampo::find($request->id);
            $existe = AplicacionCampo::All()
                ->where('id_ciclo', $request->ciclo)
                ->where('id_aplicacion', $labor->id_aplicacion)
                ->where('fecha', $request->fecha)
                ->where('repeticion', $request->repeticion)
                ->where('id_aplicacion_campo', '!=', $request->id)
                ->where('id_empresa', $finca)
                ->first();
            if ($existe == '') {
                $labor->fecha = $request->fecha;
                $labor->repeticion = $request->repeticion;
                $labor->camas = $request->camas;
                $labor->litro_x_cama = $request->litro_x_cama;
                $labor->id_empresa = $finca;

                if ($labor->save()) {
                    $success = true;
                    $msg = 'Se ha <strong>actualizado</strong> el registro satisfactoriamente';
                    bitacora('aplicacion_campo', $labor->id_aplicacion_campo, 'U', 'Actualizacion satisfactoria de una aplicacion_campo');
                } else {
                    $success = false;
                    $msg = 'Ha ocurrido un problema al guardar la información al sistema';
                }
            } else {
                $success = false;
                $msg = 'Ya existe un registro de ' . $existe->aplicacion->nombre . ' en el día indicado para este módulo';
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
            $msg = '<p class="text-center">¡Por favor corrija los siguientes errores!</p>' .
                '<ul>' .
                $errores .
                '</ul>';
        }
        return [
            'mensaje' => $msg,
            'success' => $success
        ];
    }

    public function delete_aplicacion(Request $request)
    {
        $model = AplicacionCampo::find($request->id);
        foreach ($model->detalles as $det)
            $det->delete();
        $model->delete();
        return [
            'success' => true,
            'mensaje' => 'Se ha <strong>ELIMINADO</strong> el registro satisfactoriamente',
        ];
    }
}
