<?php

namespace yura\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use yura\Jobs\IndicadorSemanal;
use yura\Jobs\ProyeccionUpdateSemanal;
use yura\Jobs\ProyeccionVentaSemanalUpdate;
use yura\Jobs\ResumenAreaSemanal;
use yura\Jobs\ResumenCostosSemanal;
use yura\Jobs\ResumenSemanaCosecha;
use yura\Jobs\UpdateIndicador;
use yura\Jobs\UpdateOtrosGastos;
use yura\Jobs\UpdateRegalias;
use yura\Jobs\UpdateTallosCosechadosProyeccion;
use yura\Modelos\Color;
use yura\Modelos\ConfiguracionEmpresa;
use yura\Modelos\Indicador;
use yura\Modelos\IntervaloIndicador;
use yura\Modelos\Job;
use yura\Modelos\ProyeccionModuloSemana;
use yura\Modelos\Submenu;
use Validator;

class dbController extends Controller
{
    public function jobs(Request $request)
    {
        return view('adminlte.gestion.db.jobs', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'variedades' => getVariedades(),
            'modulos' => getModulos()->where('estado', 1),
            'clientes' => getClientes(),
            'semana_actual' => getSemanaByDate(date('Y-m-d')),
            'indicadores' => getIndicadores()->where('estado', 1),
        ]);
    }

    public function listar_indicadores(Request $request)
    {
        $finca_actual = $request->has('finca_actual') ? $request->finca_actual : getUsuario(Session::get('id_usuario'))->finca_activa;
        $indicadores = getIndicadores()->where('estado', 1);
        if ($finca_actual != 'T')
            $indicadores = $indicadores->where('id_empresa', $finca_actual);
        return view('adminlte.gestion.db.partials.listado_indicadores', [
            'indicadores' => $indicadores,
        ]);
    }

    public function actualizar_jobs(Request $request)
    {
        return view('adminlte.gestion.db.partials._jobs', [
            'tabla' => DB::table('jobs')->get()
        ]);
    }

    public function delete_job(Request $request)
    {
        $model = Job::find($request->id);
        $model->delete();

        return [
            'success' => true,
            'mensaje' => '<div class="alert alert-success text-center">Se ha eliminado el Job satisfactoriamente</div>',
        ];
    }

    public function send_queue_job(Request $request)
    {
        if ($request->comando == 1) {   // comando ProyeccionUpdateSemanal
            $restriccion = $request->restriccion == 'true' ? 1 : 0;
            ProyeccionUpdateSemanal::dispatch($request->desde, $request->hasta, $request->variedad, $request->modulo, $restriccion)
                ->onQueue('job');
        }
        if ($request->comando == 2) {   // comando ResumenSemanaCosecha
            ResumenSemanaCosecha::dispatch($request->desde, $request->hasta, $request->variedad)
                ->onQueue('job');
        }
        if ($request->comando == 3) {   // comando VentaSemanalReal
            ProyeccionVentaSemanalUpdate::dispatch($request->desde, $request->hasta, $request->cliente, $request->variedad)
                ->onQueue('job');
        }
        if ($request->comando == 4) {   // comando UpdateIndicador
            if ($request->cola == 1) {    // en cola
                UpdateIndicador::dispatch($request->indicador)
                    ->onQueue('job');
            } else {
                Artisan::call('indicador:update', [
                    'indicador' => $request->indicador
                ]);
            }
        }
        if ($request->comando == 5) {   // comando ResumenAreaSemanal
            ResumenAreaSemanal::dispatch($request->desde, $request->hasta, $request->variedad)
                ->onQueue('job');
        }
        if ($request->comando == 6) {   // comando UpdateTallosCosechadosProyeccion
            UpdateTallosCosechadosProyeccion::dispatch($request->semana, $request->variedad, $request->modulo)
                ->onQueue('job');
        }
        if ($request->comando == 7) {   // comando ResumenAreaSemanal
            UpdateOtrosGastos::dispatch($request->desde, $request->hasta)
                ->onQueue('job');
        }
        if ($request->comando == 8) {   // comando ResumenAreaSemanal
            UpdateRegalias::dispatch($request->desde, $request->hasta)
                ->onQueue('job');
        }
        if ($request->comando == 9) {   // comando ResumenAreaSemanal
            ResumenCostosSemanal::dispatch($request->desde, $request->hasta)
                ->onQueue('job');
        }
        if ($request->comando == 10) {   // comando IndicadorSemanal
            IndicadorSemanal::dispatch($request->desde, $request->hasta)
                ->onQueue('job');
        }

        return ['success' => true];
    }

    /* ========================= INDICADORES ========================== */
    public function indicadores(Request $request)
    {
        return view('adminlte.gestion.db.indicadores', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'indicadores' => getIndicadores(),
        ]);
    }

    public function store_indicador(Request $request)
    {
        $finca_actual = $request->has('finca_actual') ? $request->finca_actual : getUsuario(Session::get('id_usuario'))->finca_activa;
        $valida = Validator::make($request->all(), [
            'nombre' => 'required|max:25|unique:indicador',
            'descripcion' => 'required|max:250',
            'valor' => 'required',
        ], [
            'nombre.unique' => 'El nombre ya existe',
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.max' => 'El nombre es muy grande',
            'descripcion.required' => 'La descripcón es obligatoria',
            'descripcion.max' => 'La descripcón es muy grande',
            'valor.required' => 'El valor es obligatorio',
        ]);
        if (!$valida->fails()) {
            $msg = '';
            if ($finca_actual != 'T') { // para una sola finca
                $model = new Indicador();
                $model->nombre = str_limit(mb_strtoupper(espacios($request->nombre)), 25) . '-' . $finca_actual;
                $model->descripcion = str_limit(espacios($request->descripcion), 250);
                $model->valor = $request->valor;
                $model->estado = $request->estado == 'true' ? 1 : 0;
                $model->fecha_registro = date('Y-m-d H:i:s');
                $model->id_empresa = $finca_actual;

                if ($model->save()) {
                    $model = Indicador::All()->last();
                    $success = true;
                    $msg = '<div class="alert alert-success text-center">' .
                        '<p> Se ha guardado un nuevo indicador satisfactoriamente</p>'
                        . '</div>';
                    bitacora('indicador', $model->id_indicador, 'I', 'Inserción satisfactoria de un nuevo indicador');
                } else {
                    $success = false;
                    $msg = '<div class="alert alert-warning text-center">' .
                        '<p> Ha ocurrido un problema al guardar la información al sistema</p>'
                        . '</div>';
                }
            } else {    // para todas las fincas
                foreach (ConfiguracionEmpresa::All() as $f) {
                    $model = new Indicador();
                    $model->nombre = str_limit(mb_strtoupper(espacios($request->nombre)), 3) . '-' . $f->id_configuracion_empresa;
                    $model->descripcion = str_limit(espacios($request->descripcion), 250);
                    $model->valor = $request->valor;
                    $model->estado = $request->estado == 'true' ? 1 : 0;
                    $model->fecha_registro = date('Y-m-d H:i:s');
                    $model->id_empresa = $f->id_configuracion_empresa;

                    if ($model->save()) {
                        $model = Indicador::All()->last();
                        $success = true;
                        $msg .= '<div class="alert alert-success text-center">' .
                            '<p> Se ha guardado un nuevo indicador satisfactoriamente para la finca: "' . $f->nombre . '"</p>'
                            . '</div>';
                        bitacora('indicador', $model->id_indicador, 'I', 'Inserción satisfactoria de un nuevo indicador');
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

    public function update_indicador(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'nombre' => 'required|max:25',
            'descripcion' => 'required|max:250',
            'valor' => 'required',
            'id' => 'required|',
        ], [
            'nombre.required' => 'El nombre es obligatorio',
            'descripcion.required' => 'La descripción es obligatoria',
            'descripcion.max' => 'La descripción es muy grande',
            'valor.required' => 'El valor es obligatorio',
            'id.required' => 'El indicador es obligatorio',
            'nombre.max' => 'El nombre es muy grande',
        ]);
        if (!$valida->fails()) {
            if (count(Indicador::All()->where('nombre', '=', str_limit(mb_strtoupper(espacios($request->nombre)), 4))
                ->where('id_indicador', '!=', $request->id)) == 0) {
                $model = Indicador::find($request->id);
                $model->nombre = str_limit(mb_strtoupper(espacios($request->nombre)), 25);
                $model->descripcion = str_limit(espacios($request->descripcion), 250);
                $model->valor = $request->valor;
                $model->estado = $request->estado == 'true' ? 1 : 0;

                if ($model->save()) {
                    $success = true;
                    $msg = '<div class="alert alert-success text-center">' .
                        '<p> Se ha actualizado el indicador satisfactoriamente</p>'
                        . '</div>';
                    bitacora('indicador', $model->id_indicador, 'U', 'Actualización satisfactoria de un indicador');
                } else {
                    $success = false;
                    $msg = '<div class="alert alert-warning text-center">' .
                        '<p> Ha ocurrido un problema al guardar la información al sistema</p>'
                        . '</div>';
                }
            } else {
                $success = false;
                $msg = '<div class="alert alert-warning text-center">' .
                    '<p> El indicador "' . espacios($request->nombre) . '" ya se encuentra en el sistema</p>'
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

    public function copiar_indicador(Request $request)
    {
        $ind_copy = Indicador::find($request->id);
        $success = true;
        $msg = '<div class="alert alert-success text-center">Se ha guardado la información satisfactoriamente</div>';
        foreach (ConfiguracionEmpresa::All() as $f) {
            if (Indicador::All()->where('nombre', explode('-', $ind_copy->nombre)[0] . '-' . $f->id_configuracion_empresa)->first() == '') {
                $ind_paste = new Indicador();
                $ind_paste->nombre = explode('-', $ind_copy->nombre)[0] . '-' . $f->id_configuracion_empresa;
                $ind_paste->descripcion = $ind_copy->descripcion;
                $ind_paste->valor = $ind_copy->valor;
                $ind_paste->id_empresa = $f->id_configuracion_empresa;

                if (!$ind_paste->save()) {
                    $success = false;
                    $msg = '<div class="alert alert-danger text-center">Ha ocurrido un problema al guardar el indicador "' . $ind_paste->nombre . '"</div>';
                } else {
                    $ind_paste = Indicador::All()->last();
                    /* ========== INTERVALOS ========== */
                    foreach ($ind_copy->intervalos as $int_copy) {
                        $int_paste = new IntervaloIndicador();
                        $int_paste->id_indicador = $ind_paste->id_indicador;
                        $int_paste->color = $int_copy->color;
                        $int_paste->desde = $int_copy->desde;
                        $int_paste->hasta = $int_copy->hasta;
                        $int_paste->condicional = $int_copy->condicional;
                        $int_paste->tipo = $int_copy->tipo;

                        $int_paste->save();
                    }
                }
            }
        }
        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }

    /* ========================= INTERVALOS INDICADORES ========================== */
    public function intervaloIndicador(Request $request)
    {
        $empresa = ConfiguracionEmpresa::find(getFincaActiva());
        return view('adminlte.gestion.db.intervalos_indicadores.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'text' => ['titulo' => 'Semaforización', 'subtitulo' => 'módulo de indicadores'],
            'indicadores' => getIndicadores()->where('estado', 1),
            'empresa' => $empresa,
        ]);
    }

    public function addIntervaloIndicador(Request $request)
    {
        return view('adminlte.gestion.db.intervalos_indicadores.partials.add_intervalo', [
            'indicador' => $request->id_indicador,
            'intervalos_indicadores' => IntervaloIndicador::where('id_indicador', $request->id_indicador)->get(),

        ]);
    }

    public function addRowIntervaloIndicador(Request $request)
    {

        if ($request->inputs === "rango")
            $view = 'adminlte.gestion.db.intervalos_indicadores.partials.inputs_rango';
        if ($request->inputs === "condicion")
            $view = 'adminlte.gestion.db.intervalos_indicadores.partials.inputs_condicion';

        return view($view, [
            'x' => $request->cant,
            'colores' => Color::where('estado', 1)->get()
        ]);
    }

    public function storeIntervaloIndicador(Request $request)
    {
        //dd($request->all());
        $valida = Validator::make($request->all(), [
            'color.*' => 'required',
            'desde.*' => 'required'
        ], [
            'color.*.required' => 'Hace falta seleccionar colores',
            'desde.*.required' => 'Debe color el número en el campo cantidad o en el campo desde'
        ]);

        if (!$valida->fails()) {
            $dataOld = IntervaloIndicador::where('id_indicador', $request->id_indicador)->select('id_intervalo_indicador')->get();

            foreach ($request->datos as $dato) {
                try {
                    $objIntervaloIndicador = new IntervaloIndicador;
                    $objIntervaloIndicador->id_indicador = $request->id_indicador;
                    $objIntervaloIndicador->tipo = $dato['tipo'];
                    $objIntervaloIndicador->color = $dato['color'];
                    $objIntervaloIndicador->hasta = $dato['hasta'];
                    if ($dato['tipo'] == "I") {
                        $objIntervaloIndicador->desde = $dato['desde'];
                    } else {
                        $objIntervaloIndicador->condicional = $dato['condicional'];
                    }
                    $objIntervaloIndicador->save();
                    $success = true;
                    $msg = '<div class="alert alert-success text-center">' .
                        '<p> Se ha guardado la información con éxito </p>'
                        . '</div>';
                } catch (\Exception $e) {
                    $success = false;
                    $msg = '<div class="alert alert-danger text-center">' .
                        '<p>  Ha ocurrido el siguiente error al intentar guardar la información <br />"' . $e->getMessage() . '"<br /> Comuníquelo al área de sistemas</p>'
                        . '</div>';
                }

                foreach ($dataOld as $data)
                    IntervaloIndicador::destroy($data->id_intervalo_indicador);
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

    public function update_objetivos(Request $request)
    {
        $empresa = ConfiguracionEmpresa::find(getFincaActiva());
        $empresa->objetivo_precio = $request->objetivo_precio;
        $empresa->objetivo_tallos = $request->objetivo_tallos;
        $empresa->objetivo_costos_fijos = $request->objetivo_costos_fijos;
        $empresa->objetivo_costos_variables = $request->objetivo_costos_variables;
        $empresa->objetivo_flor_comprada = $request->objetivo_flor_comprada;
        $empresa->save();

        return [
            'success' => true,
            'mensaje' => 'Se han <strong>ACTUALIZADO</strong> los objetivos correctamente',
        ];
    }
}
