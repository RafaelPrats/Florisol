<?php

namespace yura\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use yura\Jobs\jobActualizarProyeccion;
use yura\Jobs\jobActualizarSemProyPerenne;
use yura\Jobs\ProyeccionUpdateSemanal;
use yura\Modelos\Ciclo;
use yura\Modelos\ConfiguracionEmpresa;
use yura\Modelos\Cosecha;
use yura\Modelos\CosechaDiaria;
use yura\Modelos\CostosSemana;
use yura\Modelos\CostosSemanaManoObra;
use yura\Modelos\DesgloseRecepcion;
use yura\Modelos\Modulo;
use yura\Modelos\Producto;
use yura\Modelos\ProyeccionModulo;
use yura\Modelos\ProyeccionModuloSemana;
use yura\Modelos\ResumenTotalSemanalExportcalas;
use yura\Modelos\Semana;
use yura\Modelos\SemanaProyPerenne;
use yura\Modelos\Variedad;
use Storage as Almacenamiento;
//use PHPExcel_IOFactory;
use \PhpOffice\PhpSpreadsheet\IOFactory as IOFactory;
use yura\Modelos\Cliente;
use yura\Modelos\DetalleCliente;
use yura\Modelos\DetalleReceta;
use yura\Modelos\Planta;
use yura\Modelos\Sector;
use yura\Modelos\VariedadProveedor;
use yura\Modelos\Postco;
use yura\Modelos\OtPostco;
use yura\Modelos\ArmadoPostco;
use yura\Modelos\DetalleArmadoPostco;

class ComandoDev extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'comando:dev {comando} {desde=0} {hasta=0} {empresa=0} {variedad=0} {modulo=0} {opcion=0} {planta=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comando con fines de desarrollo';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $ini = date('Y-m-d H:i:s');

        $comando = $this->argument('comando');
        $opcion = $this->argument('opcion');
        if ($comando == 'cosecha_diaria') {
            $this->cosecha_diaria();
        }
        if ($comando == 'upload_insumos') {
            $this->upload_insumos();
        }
        if ($comando == 'corregir_costos_insumos') {
            $this->corregir_costos_insumos();
        }
        if ($comando == 'corregir_costos_mano_obra') {
            $this->corregir_costos_mano_obra();
        }
        if ($comando == 'corregir_proyeccion_cosecha') {
            $this->corregir_proyeccion_cosecha();
        }
        if ($comando == 'corregir_proyeccion_modulo') {
            $this->corregir_proyeccion_modulo();
        }
        if ($comando == 'corregir_ciclos_activos') {
            $this->corregir_ciclos_activos();
        }
        if ($comando == 'calcular_resumen_total_semanal_bqt') {
            $this->calcular_resumen_total_semanal_bqt();
        }
        if ($comando == 'eliminar_costos_insumos_duplicados') {
            $this->eliminar_costos_insumos_duplicados();
        }
        if ($comando == 'corregir_proy_sem_perenne') {
            $this->corregir_proy_sem_perenne();
        }
        if ($comando == 'calcular_semanas_guias') {
            $this->calcular_semanas_guias();
        }
        if ($comando == 'procesar_semanas') {
            $this->procesar_semanas();
        }
        if ($comando == 'copiar_semanas') {
            $this->copiar_semanas();
        }
        if ($comando == 'copiar_anno') {
            $this->copiar_anno();
        }
        if ($comando == 'importar_recetas') {
            $this->importar_recetas();
        }
        if ($comando == 'importar_variedades') {
            $this->importar_variedades();
        }
        if ($comando == 'importar_clientes') {
            $this->importar_clientes();
        }
        if ($comando == 'importar_productos') {
            $this->importar_productos();
        }
        if ($comando == 'importar_proveedor_productos') {
            $this->importar_proveedor_productos();
        }
        if ($comando == 'resumen_fechas') {
            $this->resumen_fechas();
        }
        if ($comando == 'caca') {
            $this->caca();
        }

        $time_duration = difFechas(date('Y-m-d H:i:s'), $ini)->h . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->m . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->s;
        if ($opcion == 0) {
            dump('<*> DURACION: ' . $time_duration . '  <*>');
            dump('<<<<< * >>>>> Fin satisfactorio del comando "comando:dev" <<<<< * >>>>>');
        }
    }

    function cosecha_diaria()
    {
        dump('<<<<< ! >>>>> Ejecutando comando:dev "cosecha_diaria" <<<<< ! >>>>>');
        $desde = $this->argument('desde');
        $hasta = $this->argument('hasta');
        if ($desde <= $hasta) {
            $item = $desde;
            $fechas = [];
            while ($item <= $hasta) {
                array_push($fechas, $item);
                $item = opDiasFecha('+', 1, $item);
            }

            $fincas = ConfiguracionEmpresa::All();
            foreach ($fechas as $pos_fecha => $fecha) {
                foreach ($fincas as $pos_finca => $finca) {
                    $finca = $finca->id_configuracion_empresa;
                    $variedades = DB::table('ciclo as c')
                        ->join('variedad as v', 'v.id_variedad', '=', 'c.id_variedad')
                        ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                        ->select('c.id_variedad', 'v.id_planta', 'v.nombre as variedad_nombre', 'p.nombre as planta_nombre')->distinct()
                        ->where('c.id_empresa', $finca)
                        ->where('c.estado', 1)
                        ->orderBy('p.nombre')
                        ->orderBy('v.nombre')
                        ->get();
                    foreach ($variedades as $pos_var => $var) {
                        $model = CosechaDiaria::All()
                            ->where('fecha', $fecha)
                            ->where('id_empresa', $finca)
                            ->where('id_variedad', $var->id_variedad)
                            ->first();
                        if ($model == '') {
                            $model = new CosechaDiaria();
                            $model->fecha = $fecha;
                            $model->id_empresa = $finca;
                            $model->id_variedad = $var->id_variedad;
                            $model->id_planta = $var->id_planta;
                            $model->variedad_nombre = $var->variedad_nombre;
                            $model->planta_nombre = $var->planta_nombre;
                        }
                        $cosechados = DB::table('desglose_recepcion as dr')
                            ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
                            ->select(DB::raw('sum(dr.cantidad_mallas * dr.tallos_x_malla) as cantidad'))
                            ->where('r.estado', 1)
                            ->where('dr.estado', 1)
                            ->where('dr.id_variedad', $var->id_variedad)
                            ->where('dr.id_empresa', $finca)
                            ->where('r.fecha_ingreso', 'like', $fecha . '%')
                            ->get()[0]->cantidad;
                        $model->cosechados = $cosechados > 0 ? $cosechados : 0;
                        $model->save();
                        dump('Fecha(' . $fecha . '): ' . ($pos_fecha + 1) . '/' . count($fechas)
                            . ' - Finca: ' . ($pos_finca + 1) . '/' . count($fincas)
                            . ' - Var(' . $var->id_variedad . '): ' . ($pos_var + 1) . '/' . count($variedades) . ' - cos: ' . $cosechados);
                    }
                }
            }
        }
    }

    function upload_insumos()
    {
        dump('<<<<< ! >>>>> Ejecutando comando:dev "upload_insumos" <<<<< ! >>>>>');
        $files_search = [];
        foreach (ConfiguracionEmpresa::All() as $f) {
            array_push($files_search, 'upload_insumos_' . $f->id_configuracion_empresa . '.xlsx');
            array_push($files_search, 'upload_insumos_' . $f->id_configuracion_empresa . '.xlsx');
        }
        $files = Almacenamiento::disk('pdf_loads')->files('');
        foreach ($files as $nombre_archivo) {
            if (in_array(substr($nombre_archivo, 8), $files_search)) {
                $empresa = explode('.', explode('_', substr($nombre_archivo, 8))[2])[0];
                try {
                    $url = public_path('storage/pdf_loads/' . $nombre_archivo);
                    $document = \PHPExcel_IOFactory::load($url);
                    $activeSheetData = $document->getActiveSheet()->toArray(null, true, true, true);

                    $cant_new = 0;
                    foreach ($activeSheetData as $pos_row => $row) {
                        if ($pos_row > 1) {
                            $insumo = Producto::All()
                                ->where('nombre', espacios(mb_strtoupper($row['A'])))
                                ->where('id_empresa', $empresa)
                                ->first();
                            if ($insumo == '') {
                                $cant_new++;
                                dump('pos: ' . $pos_row . ' - ' . porcentaje($pos_row, count($activeSheetData), 1) . '%' . ' - Ingresar Insumo: "' . espacios(mb_strtoupper($row['A'])) . '"');
                                $model = new Producto();
                                $model->nombre = espacios(mb_strtoupper($row['A']));
                                $model->id_empresa = $empresa;
                                $model->save();
                            } else {
                                dump($pos_row . '/' . count($activeSheetData) . ' - ' . porcentaje($pos_row, count($activeSheetData), 1) . '%');
                            }
                        }
                    }
                    dump('SE REGISTRARON: ' . $cant_new . ' INSUMOS NUEVOS');
                    unlink($url);
                } catch (\Exception $e) {
                    dump('************************* ERROR ****************************');
                    dump($e->getMessage());
                }
            }
        }
    }

    function corregir_costos_insumos()
    {
        dump('<<<<< ! >>>>> Ejecutando comando:dev "corregir_costos_insumos" <<<<< ! >>>>>');
        $desde = $this->argument('desde');
        $hasta = $this->argument('hasta');
        $empresa = $this->argument('empresa');

        $semanas = DB::table('semana')
            ->select('codigo', 'fecha_inicial', 'fecha_final')->distinct()
            ->where('estado', 1)
            ->where('codigo', '>=', $desde)
            ->where('codigo', '<=', $hasta)
            ->get();
        $empresas = ConfiguracionEmpresa::All();
        if ($empresa != 0) {
            $empresas = $empresas->where('id_configuracion_empresa', $empresa);
        }

        foreach ($empresas as $pos_emp => $empresa) {
            $finca = $empresa->id_configuracion_empresa;
            $list_act_prod = DB::table('costos_semana')
                ->select('id_actividad_producto', DB::raw('count(*)'))->distinct()
                ->where('codigo_semana', '>=', $desde)
                ->where('codigo_semana', '<=', $hasta)
                ->where('id_empresa', $finca)
                ->groupBy('id_actividad_producto')
                ->having(DB::raw('count(*)'), '<', count($semanas))
                ->get();
            foreach ($list_act_prod as $pos_act_prod => $act_prod) {
                foreach ($semanas as $pos_sem => $sem) {
                    $model = CostosSemana::All()
                        ->where('codigo_semana', $sem->codigo)
                        ->where('id_actividad_producto', $act_prod->id_actividad_producto)
                        ->where('id_empresa', $finca)
                        ->first();
                    if ($model == '') {
                        $model = new CostosSemana();
                        $model->id_actividad_producto = $act_prod->id_actividad_producto;
                        $model->codigo_semana = $sem->codigo;
                        $model->id_empresa = $finca;
                        $model->valor = 0;
                        $model->cantidad = 0;
                        $model->save();
                        dump('finca: ' . ($pos_emp + 1) . '/' . count($empresas) . ' - ap: ' . ($pos_act_prod + 1) . '/' . count($list_act_prod) . ' - sem: ' . ($pos_sem + 1) . '/' . count($semanas) . ' *NEW*');
                    } else
                        dump('finca: ' . ($pos_emp + 1) . '/' . count($empresas) . ' - ap: ' . ($pos_act_prod + 1) . '/' . count($list_act_prod) . ' - sem: ' . ($pos_sem + 1) . '/' . count($semanas));
                }
            }
        }
    }

    function corregir_costos_mano_obra()
    {
        dump('<<<<< ! >>>>> Ejecutando comando:dev "corregir_costos_mano_obra" <<<<< ! >>>>>');
        $desde = $this->argument('desde');
        $hasta = $this->argument('hasta');
        $empresa = $this->argument('empresa');

        $semanas = DB::table('semana')
            ->select('codigo', 'fecha_inicial', 'fecha_final')->distinct()
            ->where('estado', 1)
            ->where('codigo', '>=', $desde)
            ->where('codigo', '<=', $hasta)
            ->get();
        $empresas = ConfiguracionEmpresa::All();
        if ($empresa != 0) {
            $empresas = $empresas->where('id_configuracion_empresa', $empresa);
        }

        foreach ($empresas as $pos_emp => $empresa) {
            $finca = $empresa->id_configuracion_empresa;
            $list_act_mano_obra = DB::table('costos_semana_mano_obra')
                ->select('id_actividad_mano_obra', DB::raw('count(*)'))->distinct()
                ->where('codigo_semana', '>=', $desde)
                ->where('codigo_semana', '<=', $hasta)
                ->where('id_empresa', $finca)
                ->groupBy('id_actividad_mano_obra')
                ->having(DB::raw('count(*)'), '<', count($semanas))
                ->get();
            foreach ($list_act_mano_obra as $pos_act_mano_obra => $act_mano_obra) {
                foreach ($semanas as $pos_sem => $sem) {
                    $model = CostosSemanaManoObra::All()
                        ->where('codigo_semana', $sem->codigo)
                        ->where('id_actividad_mano_obra', $act_mano_obra->id_actividad_mano_obra)
                        ->where('id_empresa', $finca)
                        ->first();
                    if ($model == '') {
                        $model = new CostosSemanaManoObra();
                        $model->id_actividad_mano_obra = $act_mano_obra->id_actividad_mano_obra;
                        $model->codigo_semana = $sem->codigo;
                        $model->id_empresa = $finca;
                        $model->valor = 0;
                        $model->cantidad = 0;
                        $model->save();
                        dump('finca: ' . ($pos_emp + 1) . '/' . count($empresas) . ' - ap: ' . ($pos_act_mano_obra + 1) . '/' . count($list_act_mano_obra) . ' - sem: ' . ($pos_sem + 1) . '/' . count($semanas) . ' *NEW*');
                    } else
                        dump('finca: ' . ($pos_emp + 1) . '/' . count($empresas) . ' - ap: ' . ($pos_act_mano_obra + 1) . '/' . count($list_act_mano_obra) . ' - sem: ' . ($pos_sem + 1) . '/' . count($semanas));
                }
            }
        }
    }

    function corregir_proyeccion_cosecha()
    {
        dump('<<<<< ! >>>>> Ejecutando comando:dev "corregir_proyeccion_cosecha" <<<<< ! >>>>>');
        $empresa = $this->argument('empresa');
        $desde = getSemanaByDate($this->argument('desde'));
        $hasta = $this->argument('hasta');
        $variedad = $this->argument('variedad');
        $planta = $this->argument('planta');
        $modulo = $this->argument('modulo');

        $variedades = Variedad::join('planta as p', 'p.id_planta', '=', 'variedad.id_planta')
            ->select('variedad.*')->distinct()
            ->where('variedad.estado', 1)
            ->where('p.estado', 1)
            ->where('p.tipo', 'N');
        if ($variedad != 0)
            $variedades = $variedades->where('variedad.id_variedad', $variedad);
        if ($planta != 0)
            $variedades = $variedades->where('variedad.id_planta', $planta);
        $variedades = $variedades->orderBy('variedad.nombre')->get();

        $cant_var = 0;
        $cant_mod = 0;
        foreach ($variedades as $pos_var => $var) {
            $fecha_ini = DB::table('ciclo')
                ->select(DB::raw('min(fecha_inicio) as fecha_inicio'))
                ->where('estado', '=', 1)
                ->where('activo', '=', 1)
                ->where('id_variedad', '=', $var->id_variedad)
                ->where('fecha_fin', '>=', $desde->fecha_inicial)
                ->where('id_empresa', '=', $empresa)
                ->orderBy('fecha_inicio')
                ->get()[0]->fecha_inicio;

            if ($fecha_ini) {
                $semana_ini = getSemanaByDate($fecha_ini);
                $cant_var++;

                $query_modulos = DB::table('ciclo as c')
                    ->join('modulo as m', 'm.id_modulo', '=', 'c.id_modulo')
                    ->select('c.id_modulo')->distinct()
                    ->where('c.estado', '=', 1)
                    ->where('m.estado', '=', 1)
                    ->where('c.id_variedad', '=', $var->id_variedad)
                    ->where('c.fecha_fin', '>=', $desde->fecha_inicial)
                    ->where('c.id_empresa', '=', $empresa);
                if ($modulo != 0)
                    $query_modulos = $query_modulos->where('c.id_modulo', $modulo);
                $query_modulos = $query_modulos->orderBy('c.activo', 'desc')
                    ->orderBy('c.fecha_inicio', 'asc')->get();

                if ($modulo == 0) { // todos los modulos entonces añadir los modulos inactivos
                    $ids_modulos = [];
                    foreach ($query_modulos as $item)
                        array_push($ids_modulos, $item->id_modulo);

                    $modulos_inactivos = DB::table('proyeccion_modulo as p')
                        ->join('modulo as m', 'm.id_modulo', '=', 'p.id_modulo')
                        ->select('p.id_modulo')->distinct()
                        ->where('p.estado', '=', 1)
                        ->where('m.estado', '=', 1)
                        ->where('p.id_variedad', '=', $var->id_variedad)
                        ->where('p.fecha_inicio', '>=', $desde->fecha_inicial)
                        ->whereNotIn('p.id_modulo', $ids_modulos)
                        ->where('p.id_empresa', $empresa)
                        ->orderBy('p.fecha_inicio', 'asc')
                        ->get();

                    $query_modulos = $query_modulos->merge($modulos_inactivos);
                }

                foreach ($query_modulos as $pos_mod => $mod) {
                    $cant_mod++;
                    dump('var(' . $var->nombre . '): ' . ($pos_var + 1) . '/' . count($variedades) . '=>' . $cant_var . ' - mod:(' . $mod->id_modulo . '): ' . ($pos_mod + 1) . '/' . count($query_modulos) . '=>' . $cant_mod);
                    $this->updateProyeccionModuloSemana($mod->id_modulo, $semana_ini, $hasta, $var->id_variedad);
                }
            }
        }
    }

    function updateProyeccionModuloSemana($modulo, $semana_ini, $sem_hasta, $variedad)
    {
        $modulo = getModuloById($modulo);
        $ciclo = $modulo->cicloActual();
        if ($ciclo == '') {
            $ciclo = $modulo->getLastCiclo();
        }
        $semana_ciclo = getSemanaByDateVariedad($ciclo->fecha_inicio, $ciclo->id_variedad);
        if ($semana_ciclo->codigo <= $semana_ini->codigo) { // la semana del ciclo es anterior a la semana de la fecha_ini
            $semana_ini = $semana_ciclo;
        } else if ($this->argument('modulo') != 0) {    //  la semana del ciclo posterior a la semana de la fecha_ini
            ProyeccionUpdateSemanal::dispatch($semana_ini->codigo, $semana_ciclo->codigo, $variedad, $modulo->id_modulo, 0)
                ->onQueue('actualizar_proyecciones_job');
        }
        $semanas = DB::table('semana as s')
            ->select('s.codigo', 's.fecha_inicial', 's.fecha_final')->distinct()
            ->where('s.codigo', '>=', $semana_ini->codigo)
            ->where('s.codigo', '<=', $sem_hasta)
            ->where('s.estado', 1)
            ->get();

        $semana_cosecha = $ciclo->semana_poda_siembra;
        $tallos_planta = $ciclo->conteo;
        $tallos_ramo = $ciclo->poda_siembra == 'P' ? $semana_ciclo->tallos_ramo_poda : $semana_ciclo->tallos_ramo_siembra;
        $semanas_curva = count(explode('-', $ciclo->curva));
        if ($ciclo->num_poda_siembra != '')
            $getPodaSiembraByCiclo = $ciclo->num_poda_siembra;
        else
            $getPodaSiembraByCiclo = $modulo->getPodaSiembraByCiclo($ciclo->id_ciclo);
        $proyecciones_ciclo = ProyeccionModuloSemana::where('id_modulo', $ciclo->id_modulo)
            ->where('id_variedad', $ciclo->id_variedad)
            ->where('semana', '>=', $semana_ini->codigo)
            ->where('semana', '<=', $sem_hasta)
            ->delete();
        $next_proy = ProyeccionModulo::All()
            ->where('id_modulo', $ciclo->id_modulo)
            ->where('id_variedad', $ciclo->id_variedad)
            ->first();
        $array_semanas_prog = [];
        $pos_cosecha = 0;

        $sem_actual = 0;
        foreach ($semanas as $pos_sem => $sem) {
            $proy = new ProyeccionModuloSemana();
            $proy->id_modulo = $modulo->id_modulo;
            $proy->id_variedad = $variedad;
            $proy->semana = $sem->codigo;
            $proy->cosechados = DB::table('desglose_recepcion as dr')
                ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
                ->select(DB::raw('sum(dr.cantidad_mallas * dr.tallos_x_malla) as cantidad'))
                ->where('dr.id_variedad', $variedad)
                ->where('dr.id_modulo', $modulo->id_modulo)
                ->where('r.fecha_ingreso', '>=', $sem->fecha_inicial)
                ->where('r.fecha_ingreso', '<=', $sem->fecha_final)
                ->get()[0]->cantidad;

            if ($sem->codigo >= $semana_ciclo->codigo) {    // es una semana del ciclo actual
                $sem_actual++;

                $proy->plantas_iniciales = $ciclo->plantas_iniciales;
                $proy->plantas_actuales = $ciclo->plantas_actuales();
                $proy->fecha_inicio = $ciclo->fecha_inicio;
                $proy->activo = $ciclo->activo;
                $proy->area = $ciclo->area;
                $proy->tallos_planta = $tallos_planta;
                $proy->tallos_ramo = $tallos_ramo;
                $proy->curva = $ciclo->curva;
                $proy->poda_siembra = $ciclo->poda_siembra;
                $proy->semana_poda_siembra = $semana_cosecha;
                $proy->desecho = $semana_ciclo->desecho;
                $proy->tabla = 'C';
                $proy->modelo = $ciclo->id_ciclo;

                if ($sem_actual == 1) {    // primera semana de ciclo
                    dump('___sem: ' . $sem->codigo . '__ciclo');
                    $proy->tipo = $ciclo->poda_siembra;
                    $proy->info = $ciclo->poda_siembra . '-' . $getPodaSiembraByCiclo;
                    $proy->save();
                } elseif ($sem_actual < $semana_cosecha) {   // semana info antes de inicio de cosecha
                    dump('___sem: ' . $sem->codigo . '__ciclo');
                    $proy->tipo = 'I';
                    $proy->info = $sem_actual . "'";
                    $proy->save();
                } elseif ($sem_actual >= $semana_cosecha && $sem_actual <= $semana_cosecha + $semanas_curva - 1) {  // semanas de cosecha-curva
                    dump('___sem: ' . $sem->codigo . '__ciclo');
                    $proy->tipo = 'T';
                    $proy->info = $sem_actual . "'";
                    $total = $proy->plantas_actuales * $tallos_planta;
                    $total = $total * ((100 - $proy->desecho) / 100);
                    $proy->proyectados = round($total * (explode('-', $proy->curva)[$pos_cosecha] / 100), 2);
                    $pos_cosecha++;
                    $proy->save();
                } else {    // semanas despues del ciclo
                    if ($next_proy != '' && $sem->codigo < $next_proy->semana->codigo) { // semanas antes de la programacion
                        dump('___sem: ' . $sem->codigo . '__empty');
                        $proy->tipo = 'F';
                        $proy->info = '-';
                        $proy->save();
                    } else {    // semanas de la programacion
                        array_push($array_semanas_prog, $sem);
                    }
                }
            } else {    // es una semana antes del ciclo actual
                dump('___sem: ' . $sem->codigo . '__antes');
                $proy->tipo = 'F';
                $proy->info = '-';
                $proy->save();
            }
        }

        /* ------------ Programacion siguiente --------------- */
        $pos_cosecha = 0;
        foreach ($array_semanas_prog as $pos_sem => $sem) {
            $proy = new ProyeccionModuloSemana();
            $proy->id_modulo = $modulo->id_modulo;
            $proy->id_variedad = $variedad;
            $proy->semana = $sem->codigo;
            $proy->cosechados = DB::table('desglose_recepcion as dr')
                ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
                ->select(DB::raw('sum(dr.cantidad_mallas * dr.tallos_x_malla) as cantidad'))
                ->where('dr.id_variedad', $variedad)
                ->where('dr.id_modulo', $modulo->id_modulo)
                ->where('r.fecha_ingreso', '>=', $sem->fecha_inicial)
                ->where('r.fecha_ingreso', '<=', $sem->fecha_final)
                ->get()[0]->cantidad;

            if ($next_proy != '') { // tiene siguiente proyeccion
                $sem_actual = $pos_sem + 1;

                $proy->plantas_iniciales = $ciclo->plantas_iniciales;
                $proy->plantas_actuales = $ciclo->plantas_iniciales;
                $proy->fecha_inicio = $ciclo->fecha_inicio;
                $proy->activo = 1;
                $proy->area = $ciclo->area;
                $proy->tallos_planta = $tallos_planta;
                $proy->tallos_ramo = $tallos_ramo;
                $proy->curva = $ciclo->curva;
                $proy->poda_siembra = $next_proy->poda_siembra;
                $proy->semana_poda_siembra = $semana_cosecha;
                $proy->desecho = $semana_ciclo->desecho;
                $proy->tabla = 'P';
                $proy->modelo = $next_proy->id_proyeccion_modulo;

                if ($sem_actual == 1) {    // primera semana de programacion
                    dump('___sem: ' . $sem->codigo . '__proy');
                    $proy->tipo = 'Y';
                    $proy->info = $next_proy->tipo;
                    $proy->save();
                } elseif ($sem_actual < $semana_cosecha) {   // semana info antes de inicio de cosecha
                    dump('___sem: ' . $sem->codigo . '__proy');
                    $proy->tipo = 'I';
                    $proy->info = $sem_actual . "'";
                    $proy->save();
                } elseif ($sem_actual >= $semana_cosecha && $sem_actual <= $semana_cosecha + $semanas_curva - 1) {  // semanas de cosecha-curva
                    dump('___sem: ' . $sem->codigo . '__proy');
                    $proy->tipo = 'T';
                    $proy->info = $sem_actual . "'";
                    $total = $proy->plantas_actuales * $tallos_planta;
                    $total = $total * ((100 - $proy->desecho) / 100);
                    $proy->proyectados = round($total * (explode('-', $proy->curva)[$pos_cosecha] / 100), 2);
                    $pos_cosecha++;
                    $proy->save();
                } else {    // semanas despues de la programacion
                    dump('___sem: ' . $sem->codigo . '__end');
                    $proy->tipo = 'F';
                    $proy->info = '-';
                    $proy->save();
                }
            } else {    // no hay mas programacion hacia adelante
                dump('___sem: ' . $sem->codigo . '__end');
                $proy->tipo = 'F';
                $proy->info = '-';
                $proy->save();
            }
        }
    }

    function corregir_proyeccion_modulo()
    {
        dump('<<<<< ! >>>>> Ejecutando comando:dev "corregir_proyeccion_modulo" <<<<< ! >>>>>');
        $desde = getSemanaByDate($this->argument('desde'));
        $variedad = $this->argument('variedad');
        $modulo = $this->argument('modulo');

        $variedades = Variedad::join('planta as p', 'p.id_planta', '=', 'variedad.id_planta')
            ->select('variedad.*')->distinct()
            ->where('variedad.estado', 1)
            ->where('p.estado', 1)
            ->where('p.tipo', 'N');
        if ($variedad != 0)
            $variedades = $variedades->where('variedad.id_variedad', $variedad);
        $variedades = $variedades->orderBy('variedad.nombre')->get();

        $fincas = ConfiguracionEmpresa::All();
        foreach ($fincas as $pos_finca => $f) {
            /* ------- BORRAR PROYs DUPLICADAS --------- */
            dump('------- BORRAR PROYs DUPLICADAS FINCA: ' . $f->nombre);
            $finca = $f->id_configuracion_empresa;
            $modulos = DB::table('proyeccion_modulo')
                ->select('id_modulo', DB::raw('count(*) as count'))
                ->where('estado', 1)
                ->where('id_empresa', $finca)
                ->groupBy('id_modulo')
                ->having(DB::raw('count(*)'), '>', 1)
                ->get();
            foreach ($modulos as $pos_mod => $mod) {
                dump('finca: ' . ($pos_finca + 1) . '/' . count($fincas) . ' - mod: ' . ($pos_mod + 1) . '/' . count($modulos) . ' - count: ' . $mod->count);
                $proys = ProyeccionModulo::where('id_modulo', $mod->id_modulo)
                    ->where('estado', 1)
                    ->where('id_empresa', $finca)
                    ->orderBy('fecha_inicio', 'desc')
                    ->get();
                foreach ($proys as $pos_proy => $p)
                    if ($pos_proy > 0)
                        $p->delete();
            }

            /* ------- CREAR PROYs NUEVAS --------- */
            $opcion = $this->argument('opcion');
            if ($opcion == 1) {
                dump('======= CREAR PROYs NUEVAS FINCA: ' . $f->nombre);
                foreach ($variedades as $pos_var => $var) {
                    $modulos = DB::table('ciclo as c')
                        ->join('modulo as m', 'm.id_modulo', '=', 'c.id_modulo')
                        ->select('c.id_modulo')->distinct()
                        ->where('c.estado', '=', 1)
                        ->where('m.estado', '=', 1)
                        ->where('c.id_variedad', '=', $var->id_variedad)
                        ->where('c.fecha_fin', '>=', $desde->fecha_inicial)
                        ->where('c.id_empresa', '=', $finca);
                    if ($modulo != 0)
                        $modulos = $modulos->where('c.id_modulo', $modulo);
                    $modulos = $modulos->orderBy('c.activo', 'desc')
                        ->orderBy('c.fecha_inicio', 'asc')->get();
                    foreach ($modulos as $pos_mod => $mod) {
                    }
                }
            }
        }
    }

    function corregir_ciclos_activos()
    {
        dump('<<<<< ! >>>>> Ejecutando comando:dev "corregir_ciclos_activos" <<<<< ! >>>>>');

        $fincas = ConfiguracionEmpresa::All();
        foreach ($fincas as $pos_finca => $f) {
            /* ------- BORRAR PROYs DUPLICADAS --------- */
            dump('------- BORRAR CICLOS DUPLICADOS FINCA: ' . $f->nombre);
            $finca = $f->id_configuracion_empresa;
            $modulos = DB::table('ciclo')
                ->select('id_modulo', DB::raw('count(*) as count'))
                ->where('estado', 1)
                ->where('activo', 1)
                ->where('id_empresa', $finca)
                ->groupBy('id_modulo')
                ->having(DB::raw('count(*)'), '>', 1)
                ->get();
            foreach ($modulos as $pos_mod => $mod) {
                dump('finca: ' . ($pos_finca + 1) . '/' . count($fincas) . ' - mod: ' . ($pos_mod + 1) . '/' . count($modulos) . ' - count: ' . $mod->count);
                $ciclos = Ciclo::where('id_modulo', $mod->id_modulo)
                    ->where('estado', 1)
                    ->where('activo', 1)
                    ->where('id_empresa', $finca)
                    ->orderBy('fecha_inicio', 'desc')
                    ->get();
                foreach ($ciclos as $pos_c => $c)
                    if ($pos_c > 0)
                        $c->delete();
            }
        }
    }

    function calcular_resumen_total_semanal_bqt()
    {
        dump('<<<<< ! >>>>> Ejecutando comando:dev "calcular_resumen_total_semanal_bqt" <<<<< ! >>>>>');
        $desde = $this->argument('desde');
        $hasta = $this->argument('hasta');

        $sem_desde = getSemanaByDate($desde);
        $sem_hasta = getSemanaByDate($hasta);

        $semanas = DB::table('semana')
            ->select('codigo', 'fecha_inicial', 'fecha_final')->distinct()
            ->where('estado', 1)
            ->where('codigo', '>=', $sem_desde->codigo)
            ->where('codigo', '<=', $sem_hasta->codigo)
            ->get();
        $empresas = ConfiguracionEmpresa::All();
        foreach ($empresas as $pos_e => $finca) {
            $id_finca = $finca->id_configuracion_empresa;
            $variedades = DB::table('bouquetera')
                ->select('id_variedad')->distinct()
                ->where('id_empresa', $id_finca)
                ->where('fecha', '>=', $desde)
                ->where('fecha', '<=', $hasta)
                ->get();
            foreach ($variedades as $pos_v => $var) {
                foreach ($semanas as $pos_s => $sem) {
                    dump('finca: ' . ($pos_e + 1) . '/' . count($empresas) . ' - var: ' . ($pos_v + 1) . '/' . count($variedades) . ' - sem: ' . ($pos_s + 1) . '/' . count($semanas));
                    $model = ResumenTotalSemanalExportcalas::All()
                        ->where('id_variedad', $var->id_variedad)
                        ->where('semana', $sem->codigo)
                        ->where('id_empresa', $id_finca)
                        ->first();
                    if ($model == '') {
                        $model = new ResumenTotalSemanalExportcalas();
                        $model->semana = $sem->codigo;
                        $model->id_variedad = $var->id_variedad;
                        $model->id_empresa = $id_finca;
                    }
                    $tallos_bqt = DB::table('bouquetera')
                        ->select(DB::raw('sum(tallos) as cant'))
                        ->where('id_empresa', $id_finca)
                        ->where('id_variedad', $var->id_variedad)
                        ->where('fecha', '>=', $sem->fecha_inicial)
                        ->where('fecha', '<=', $sem->fecha_final)
                        ->get()[0]->cant;
                    $model->bouquetera = $tallos_bqt != '' ? $tallos_bqt : 0;
                    $model->save();
                }
            }
        }
    }

    function eliminar_costos_insumos_duplicados()
    {
        dump('<<<<< ! >>>>> Ejecutando comando:dev "eliminar_costos_insumos_duplicados" <<<<< ! >>>>>');
        $fincas = ConfiguracionEmpresa::All();
        foreach ($fincas as $pos_finca => $f) {
            $finca = $f->id_configuracion_empresa;
            $act_prods = DB::table('costos_semana')
                ->select('id_actividad_producto', 'codigo_semana', DB::raw('count(*) as count'))
                ->where('id_empresa', $finca)
                ->groupBy('id_actividad_producto', 'codigo_semana')
                ->having(DB::raw('count(*)'), '>', 1)
                ->get();
            foreach ($act_prods as $pos_ap => $ap) {
                dump('finca: ' . ($pos_finca + 1) . '/' . count($fincas) . ' - mod: ' . ($pos_ap + 1) . '/' . count($act_prods) . ' - count: ' . $ap->count);
                $costos = CostosSemana::where('id_actividad_producto', $ap->id_actividad_producto)
                    ->where('codigo_semana', $ap->codigo_semana)
                    ->where('id_empresa', $finca)
                    ->orderBy('id_costos_semana', 'asc')
                    ->get();
                foreach ($costos as $pos_c => $c)
                    if ($pos_c > 0)
                        $c->delete();
            }
        }
    }

    function corregir_proy_sem_perenne()
    {
        $opcion = $this->argument('opcion');
        if ($opcion == 0)
            dump('<<<<< ! >>>>> Ejecutando comando:dev "corregir_proy_sem_perenne" <<<<< ! >>>>>');
        $desde = $this->argument('desde');
        $finca = $this->argument('empresa');
        $variedad = $this->argument('variedad');
        $semanas_proy = DB::table('semana as sem')
            ->leftJoin('semana_proy_perenne as p', 'p.id_semana', '=', 'sem.id_semana')
            ->select('sem.id_semana')->distinct()
            ->where('sem.estado', 1)
            ->where('sem.id_variedad', $variedad)
            ->where('sem.anno', $desde)
            ->where('p.id_empresa', $finca)
            ->orderBy('sem.codigo')
            ->get();
        $ids_semanas = [];
        foreach ($semanas_proy as $s)
            array_push($ids_semanas, $s->id_semana);
        $semanas = Semana::where('estado', 1)
            ->where('anno', $desde)
            ->where('id_variedad', $variedad)
            ->whereNotIn('id_semana', $ids_semanas)
            ->get();
        foreach ($semanas as $pos_s => $semana) {
            if ($opcion == 0)
                dump('sem:' . ($pos_s + 1) . '/' . count($semanas));
            $id_semana = $semana->id_semana;
            $model = SemanaProyPerenne::All()
                ->where('id_semana', $id_semana)
                ->where('id_empresa', $finca)
                ->first();
            if ($model == '') {
                $model = new SemanaProyPerenne();
                $model->id_semana = $id_semana;
                $model->id_empresa = $finca;
            }
            $model->curva = 0;
            $model->proyectados = 0;
            $cosechados = DB::table('resumen_total_semanal_exportcalas')
                ->select(DB::raw('sum(tallos_cosechados) as cantidad'))
                ->where('id_variedad', $variedad)
                ->where('id_empresa', $finca)
                ->where('semana', $semana->codigo)
                ->get()[0]->cantidad;
            $cosechados = $cosechados > 0 ? $cosechados : 0;
            $model->cosechados = $cosechados;
            $model->save();

            /* ---------------- ACTUALIZR PROYECCIONES ------------------- */
            jobActualizarProyeccion::dispatch($variedad, '', $semana, $finca)->onQueue('proy_cosecha')
                ->onConnection('sync');
        }
    }

    function calcular_semanas_guias()
    {
        dump('<<<<< ! >>>>> Ejecutando comando:dev "calcular_semanas_guias" <<<<< ! >>>>>');
        $semanas = Semana::where('semana_guia', 1)->orderBy('codigo')->get();
        foreach ($semanas as $pos_s => $sem) {
            dump('sem: ' . ($pos_s + 1) . '/' . count($semanas));
            $sem_last_4 = getSemanaByDate(opDiasFecha('-', 21, $sem->fecha_inicial));
            $sem->last_4_semana = $sem_last_4 != '' ? $sem_last_4->codigo : null;
            $sem_last_13 = getSemanaByDate(opDiasFecha('-', 84, $sem->fecha_inicial));
            $sem->last_13_semana = $sem_last_13 != '' ? $sem_last_13->codigo : null;
            $sem_last_52 = getSemanaByDate(opDiasFecha('-', 357, $sem->fecha_inicial));
            $sem->last_52_semana = $sem_last_52 != '' ? $sem_last_52->codigo : null;
            $sem->save();
        }
    }

    function procesar_semanas()
    {
        dump('<<<<< ! >>>>> Ejecutando comando:dev "procesar_semanas" <<<<< ! >>>>>');
        $desde = $this->argument('desde');
        $hasta = $this->argument('hasta');
        $anno = $this->argument('empresa');
        $variedad = $this->argument('variedad');

        $existe = Semana::All()->where('anno', '=', $anno)
            ->where('id_variedad', '=', $variedad)
            ->first();
        if ($existe == '') {
            if ($desde < $hasta) {
                /* =========================== OBTENER LAS SEMANAS =======================*/
                $arreglo = [];
                $inicio = $desde;
                $fin = strtotime('+6 day', strtotime($inicio));
                $fin = date('Y-m-d', $fin);

                array_push($arreglo, [
                    'inicio' => $inicio,
                    'fin' => $fin
                ]);

                $inicio = strtotime('+1 day', strtotime($fin));
                $inicio = date('Y-m-j', $inicio);

                while ($inicio < $hasta) {
                    if (existInSemana($inicio, $variedad, $anno) && existInSemana($fin, $variedad, $anno)) {
                        $fin = strtotime('+6 day', strtotime($inicio));
                        $fin = date('Y-m-d', $fin);

                        array_push($arreglo, [
                            'inicio' => $inicio,
                            'fin' => $fin
                        ]);

                        $inicio = strtotime('+1 day', strtotime($fin));
                        $inicio = date('Y-m-d', $inicio);
                    } else {
                        dd('El rango indicado incluye al menos una fecha que ya está registrada');
                        break;
                    }
                }
                /* =========================== VERIFICAR LA CANTIDAD DE SEMANAS EN UN AÑO =======================*/
                if (count($arreglo) >= 52 && count($arreglo) <= 53) {
                    /* =========================== GRABAR EN LA BASE LAS SEMANAS =======================*/
                    for ($i = 0; $i < count($arreglo); $i++) {
                        dump('sem: ' . ($i + 1) . '/' . count($arreglo));
                        $model = new Semana();
                        $model->id_variedad = $variedad;
                        $model->anno = $anno;
                        $pref = ($i + 1) < 10 ? '0' : '';
                        $model->codigo = substr($anno, 2) . $pref . ($i + 1);
                        $model->fecha_inicial = $arreglo[$i]['inicio'];
                        $model->fecha_final = $arreglo[$i]['fin'];
                        $model->save();
                    }
                } else {
                    dd('No se ha cumplido el rango de 52-53 semanas de un año en el rango indicado');
                }
            } else {
                dd('<div class="text-center alert alert-danger">La fecha inicial debe ser menor que la final');
            }
        } else {
            dd('Ya existe una programación para esta variedad en el año ' . $anno);
        }
    }

    function copiar_semanas()
    {
        $opcion = $this->argument('opcion');
        if ($opcion == 0)
            dump('<<<<< ! >>>>> Ejecutando comando:dev "copiar_semanas" <<<<< ! >>>>>');
        $anno = $this->argument('desde');
        $variedad = $this->argument('variedad');
        $planta = $this->argument('planta');
        $semanas = Semana::where('estado', 1)
            ->where('id_variedad', $variedad)
            ->where('anno', $anno)
            ->get();
        if (count($semanas) > 0) {
            $variedad_par = Variedad::find($variedad);
            $variedades = getVariedades();
            foreach ($variedades as $pos_v => $var) {
                $sem_var = Semana::where('estado', 1)
                    ->where('id_variedad', $var->id_variedad)
                    ->where('anno', $anno)
                    ->first();
                if ($sem_var == '')
                    if ($var->id_planta == $variedad_par->id_planta || $planta == 'x')
                        foreach ($semanas as $pos_s => $sem) {
                            if ($opcion == 0)
                                dump('var: ' . ($pos_v + 1) . '/' . count($variedades) . ' - sem: ' . ($pos_s + 1) . '/' . count($semanas));
                            $new = new Semana();
                            $new->id_variedad = $var->id_variedad;
                            $new->anno = $sem->anno;
                            $new->codigo = $sem->codigo;
                            $new->fecha_inicial = $sem->fecha_inicial;
                            $new->fecha_final = $sem->fecha_final;
                            $new->curva = $sem->curva;
                            $new->desecho = $sem->desecho;
                            $new->semana_poda = $sem->semana_poda;
                            $new->semana_siembra = $sem->semana_siembra;
                            $new->tallos_planta_siembra = $sem->tallos_planta_siembra;
                            $new->tallos_planta_poda = $sem->tallos_planta_poda;
                            $new->tallos_ramo_siembra = $sem->tallos_ramo_siembra;
                            $new->tallos_ramo_poda = $sem->tallos_ramo_poda;
                            $new->mes = $sem->mes;
                            $new->save();
                        }
            }
            if ($opcion == 0)
                dd('Se han copiado las semanas satisfactoriamente');
        } else {
            if ($opcion == 0)
                dd('La variedad no tiene semanas ingresadas para el año indicado');
        }
    }

    function copiar_anno()
    {
        dump('<<<<< ! >>>>> Ejecutando comando:dev "copiar_anno" <<<<< ! >>>>>');
        $copy = $this->argument('desde');
        $paste = $this->argument('hasta');
        $variedades = getVariedades();
        foreach ($variedades as $pos_v => $var) {
            $semanas = Semana::where('estado', 1)
                ->where('id_variedad', $var->id_variedad)
                ->where('anno', $copy)
                ->get();
            foreach ($semanas as $pos_s => $sem) {
                dump('var: ' . ($pos_v + 1) . '/' . count($variedades) . ' - sem: ' . ($pos_s + 1) . '/' . count($semanas) . ' - codigo: ' . substr($paste, 2, 2) . substr($sem->codigo, 2, 2));
                $sem_paste = getObjSemanaVariedad(substr($paste, 2, 2) . substr($sem->codigo, 2, 2), $var->id_variedad);
                if ($sem_paste != '') {
                    $sem_paste->curva = $sem->curva;
                    $sem_paste->desecho = $sem->desecho;
                    $sem_paste->semana_poda = $sem->semana_poda;
                    $sem_paste->semana_siembra = $sem->semana_siembra;
                    $sem_paste->tallos_planta_siembra = $sem->tallos_planta_siembra;
                    $sem_paste->tallos_planta_poda = $sem->tallos_planta_poda;
                    $sem_paste->tallos_ramo_siembra = $sem->tallos_ramo_siembra;
                    $sem_paste->tallos_ramo_poda = $sem->tallos_ramo_poda;
                    $sem_paste->plantas_iniciales = $sem->plantas_iniciales;
                    $sem_paste->densidad = $sem->densidad;
                    $sem_paste->mes = $sem->mes;
                    $sem_paste->save();
                }
            }
        }
        dd('Se han copiado las semanas satisfactoriamente');
    }

    function importar_recetas()
    {
        dump('<<<<< ! >>>>> Ejecutando comando:dev "importar_recetas" <<<<< ! >>>>>');
        DB::beginTransaction();
        try {
            $url = public_path('storage/pdf_loads/recetas.xlsx');
            $document = IOFactory::load($url);
            $activeSheetData = $document->getActiveSheet()->toArray(null, true, true, true);

            $planta = Planta::where('nombre', 'BOUQUETS')
                ->first();
            $errores = [];
            foreach ($activeSheetData as $pos_row => $row) {
                if ($pos_row > 2 && $row['E'] != '') {
                    dump($pos_row . '/' . count($activeSheetData));
                    if ($row['B'] != '') {    // nueva receta
                        $receta = new Variedad();
                        $receta->id_planta = $planta->id_planta;
                        $receta->nombre = espacios(mb_strtoupper($row['B']));
                        $receta->siglas = espacios(mb_strtoupper($row['A']));
                        $receta->color = 'RECETA';
                        $receta->receta = 1;
                        $receta->tallos_x_malla = 30;
                        $receta->tipo = 'L';
                        $receta->save();
                        $receta = Variedad::All()->last();
                    }
                    $item = Variedad::where('nombre', espacios(mb_strtoupper($row['C'])))
                        ->first();
                    if ($item != '') {
                        $detalle = new DetalleReceta();
                        $detalle->id_variedad = $receta->id_variedad;
                        $detalle->id_item = $item->id_variedad;
                        $detalle->unidades = $row['D'];
                        $detalle->longitud = 60;
                        $detalle->precio = $row['E'];
                        $detalle->save();
                    } else {
                        $errores[] = 'No se encontro la variedad: ' . espacios(mb_strtoupper($row['E']));
                    }
                }
            }
            if (count($errores) > 0)
                dump($errores);
            DB::commit();
            //unlink($url);
        } catch (\Exception $e) {
            DB::rollBack();
            dump('pos: ' . $pos_row . '************************* ERROR ****************************');
            dump($e->getMessage());
        }
    }

    function importar_productos()
    {
        dump('<<<<< ! >>>>> Ejecutando comando:dev "importar_productos" <<<<< ! >>>>>');
        DB::beginTransaction();
        try {
            $url = public_path('storage/pdf_loads/productos.xlsx');
            $document = IOFactory::load($url);
            $activeSheetData = $document->getActiveSheet()->toArray(null, true, true, true);

            foreach ($activeSheetData as $pos_row => $row) {
                if ($pos_row > 10 && $row['E'] != '') {
                    dump($pos_row . '/' . count($activeSheetData));
                    if ($row['B'] != '') {
                        $producto = new Producto();
                        $producto->id_empresa = 1;
                        $producto->nombre = espacios(mb_strtoupper($row['C']));
                        $producto->codigo = espacios(mb_strtoupper($row['B']));
                        $producto->unidad_medida = espacios(mb_strtoupper($row['E']));
                        $producto->disponibles = str_replace(',', '', explode('.0000', $row['F'])[0]);
                        $producto->conversion = 1;
                        $producto->stock_minimo = 0;
                        $producto->save();
                    }
                }
            }
            DB::commit();
            //unlink($url);
        } catch (\Exception $e) {
            DB::rollBack();
            dump('************************* ERROR ****************************');
            dump($e->getMessage());
        }
    }

    function importar_proveedor_productos()
    {
        dump('<<<<< ! >>>>> Ejecutando comando:dev "importar_proveedor_productos" <<<<< ! >>>>>');
        DB::beginTransaction();
        try {
            $url = public_path('storage/pdf_loads/proveedores_productos.xlsx');
            $document = IOFactory::load($url);
            $activeSheetData = $document->getActiveSheet()->toArray(null, true, true, true);

            foreach ($activeSheetData as $pos_row => $row) {
                if ($pos_row > 1 && $row['A'] != '' && $row['B'] != '') {
                    dump($pos_row . '/' . count($activeSheetData));
                    $proveedor = ConfiguracionEmpresa::All()
                        ->where('nombre', str_limit(espacios($row['A']), 250))
                        ->where('proveedor', 1)
                        ->first();
                    if ($proveedor == '') {
                        $proveedor = new ConfiguracionEmpresa();
                        $proveedor->nombre = str_limit(espacios($row['A']), 250);
                        $proveedor->proveedor = 1;
                        $proveedor->fecha_registro = date('Y-m-d H:i:s');
                        $proveedor->save();
                        $proveedor = ConfiguracionEmpresa::All()->last();
                    }

                    $variedad = Variedad::All()
                        ->where('nombre', espacios($row['B']))
                        ->first();
                    if ($variedad != '') {
                        $model = VariedadProveedor::All()
                            ->where('id_variedad', $variedad->id_variedad)
                            ->where('id_proveedor', $proveedor->id_configuracion_empresa)
                            ->first();
                        if ($model == '') {
                            $model = new VariedadProveedor();
                            $model->id_variedad = $variedad->id_variedad;
                            $model->id_proveedor = $proveedor->id_configuracion_empresa;
                            $model->save();
                        }
                    }
                }
            }
            DB::commit();
            //unlink($url);
        } catch (\Exception $e) {
            DB::rollBack();
            dump('************************* ERROR ****************************');
            dump($e->getMessage());
        }
    }

    function importar_variedades()
    {
        dump('<<<<< ! >>>>> Ejecutando comando:dev "importar_variedades" <<<<< ! >>>>>');
        try {
            $url = public_path('storage/pdf_loads/variedades.xls');
            $document = IOFactory::load($url);
            $activeSheetData = $document->getActiveSheet()->toArray(null, true, true, true);

            foreach ($activeSheetData as $pos_row => $row) {
                $planta = Planta::All()
                    ->where('nombre', espacios(mb_strtoupper($row['C'])))
                    ->first();
                if ($planta == '') {
                    $planta = new Planta();
                    $planta->nombre = espacios(mb_strtoupper($row['C']));
                    $planta->siglas = substr(espacios(mb_strtoupper($row['C'])), 0, 3);
                    $planta->save();
                    $planta = Planta::All()->last();
                }
                if ($planta != '') {
                    $variedad = Variedad::All()
                        ->where('nombre', espacios(mb_strtoupper($row['B'])))
                        ->where('id_planta', $planta->id_planta)
                        ->first();
                    if ($variedad == '') {
                        dump($pos_row . '/' . (count($activeSheetData) - 1));
                        $variedad = new Variedad();
                        $variedad->id_planta = $planta->id_planta;
                        $variedad->nombre = espacios(mb_strtoupper($row['B']));
                        $variedad->siglas = espacios(mb_strtoupper($row['A']));
                        $variedad->color = espacios(mb_strtoupper($row['D']));
                        $variedad->tallos_x_malla = 30;

                        $variedad->save();
                    }
                } else {
                    dd('Falló en la fila: ' . $pos_row . '; Planta errónea', $row, espacios(mb_strtoupper($row['B'])));
                }
            }
            //unlink($url);
        } catch (\Exception $e) {
            dump('************************* ERROR ****************************');
            dump($e->getMessage());
        }
    }

    function importar_clientes()
    {
        dump('<<<<< ! >>>>> Ejecutando comando:dev "importar_clientes" <<<<< ! >>>>>');
        try {
            $url = public_path('storage/pdf_loads/clientes.xls');
            $document = IOFactory::load($url);
            $activeSheetData = $document->getActiveSheet()->toArray(null, true, true, true);
            $pos_row = 0;
            foreach ($activeSheetData as $pos_row => $row) {
                if ($pos_row > 1) {
                    dump($pos_row . '/' . count($activeSheetData));
                    $cliente = Cliente::All()
                        ->where('codigo', $row['A'])
                        ->first();
                    if ($cliente == '') {
                        $cliente = new Cliente();
                        $cliente->codigo = $row['A'];
                        $cliente->save();
                        $cliente = Cliente::All()->last();

                        $objDetalleCliente = new DetalleCliente();
                        $objDetalleCliente->id_cliente = $cliente->id_cliente;
                        $objDetalleCliente->nombre = espacios(mb_strtoupper($row['E']));
                        $objDetalleCliente->ruc = espacios(mb_strtoupper($row['C']));
                        $objDetalleCliente->codigo_porcentaje_impuesto = 0;
                        $objDetalleCliente->codigo_identificacion = 0;
                        $objDetalleCliente->codigo_impuesto = 2;
                        $objDetalleCliente->save();
                    } else {
                        $detalle = $cliente->detalle();
                        if ($detalle != '') {
                            $detalle->estado = 0;
                            $detalle->save;
                        }

                        $objDetalleCliente = new DetalleCliente();
                        $objDetalleCliente->id_cliente = $cliente->id_cliente;
                        $objDetalleCliente->nombre = espacios(mb_strtoupper($row['E']));
                        $objDetalleCliente->ruc = espacios(mb_strtoupper($row['C']));
                        $objDetalleCliente->codigo_porcentaje_impuesto = 0;
                        $objDetalleCliente->codigo_identificacion = 0;
                        $objDetalleCliente->codigo_impuesto = 2;
                        $objDetalleCliente->save();
                    }
                }
            }
            //unlink($url);
        } catch (\Exception $e) {
            dump('************************* ERROR ****************************' . $pos_row);
            dump($e->getMessage());
        }
    }

    function resumen_fechas()
    {
        $desde = $this->argument('desde');
        $fechas = DB::table('resumen_fechas')
            ->select('fecha')->distinct()
            ->where('fecha', '>=', $desde)
            ->orderBy('fecha')
            ->get()->pluck('fecha')->toArray();
        foreach ($fechas as $pos_f => $fecha) {
            $combinaciones_compra_flor = DB::table('desglose_compra_flor as i')
                ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
                ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                ->select(
                    'i.id_variedad',
                    'v.nombre as variedad_nombre',
                    'p.nombre as planta_nombre',
                    'v.dias_rotacion_recepcion',
                )->distinct()
                ->where('i.fecha', $fecha)
                ->orderBy('p.nombre')
                ->orderBy('v.nombre')
                ->get();
            $ids_variedad_compra_flor = $combinaciones_compra_flor->pluck('id_variedad')->toArray();
            $combinaciones_recepcion = DB::table('desglose_recepcion as i')
                ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
                ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                ->select(
                    'i.id_variedad',
                    'v.nombre as variedad_nombre',
                    'p.nombre as planta_nombre',
                    'v.dias_rotacion_recepcion',
                )->distinct()
                ->where('i.fecha', $fecha)
                ->where('i.disponibles', '>', 0)
                ->where('i.estado', 1)
                ->whereNotIn('i.id_variedad', $ids_variedad_compra_flor)
                ->orderBy('p.nombre')
                ->orderBy('v.nombre')
                ->get();
            $ids_variedad_recepcion = $combinaciones_recepcion->pluck('id_variedad')->toArray();
            $combinaciones_resumen = DB::table('resumen_fechas as h')
                ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                ->select(
                    'h.id_variedad',
                    'v.nombre as variedad_nombre',
                    'p.nombre as planta_nombre',
                )->distinct()
                ->where('h.fecha', $fecha)
                ->where('h.tallos_venta', '>', 0)
                ->whereNotIn('h.id_variedad', $ids_variedad_compra_flor)
                ->whereNotIn('h.id_variedad', $ids_variedad_recepcion)
                ->orderBy('p.nombre')
                ->orderBy('v.nombre')
                ->get();
            $ids_variedad_resumen = $combinaciones_resumen->pluck('id_variedad')->toArray();
            $combinaciones_pedidos = DB::table('distribucion_receta as h')
                ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                ->join('detalle_import_pedido as det', 'det.id_detalle_import_pedido', '=', 'h.id_detalle_import_pedido')
                ->join('import_pedido as ped', 'ped.id_import_pedido', '=', 'det.id_import_pedido')
                ->select(
                    'h.id_variedad',
                    'v.nombre as variedad_nombre',
                    'p.nombre as planta_nombre',
                )->distinct()
                ->where('ped.fecha', $fecha)
                ->where('h.unidades', '>', 0)
                ->whereNotIn('h.id_variedad', $ids_variedad_compra_flor)
                ->whereNotIn('h.id_variedad', $ids_variedad_recepcion)
                ->whereNotIn('h.id_variedad', $ids_variedad_resumen)
                ->orderBy('p.nombre')
                ->orderBy('v.nombre')
                ->get();
            $ids_variedad_pedidos = $combinaciones_pedidos->pluck('id_variedad')->toArray();
            $combinaciones_ot = DB::table('detalle_orden_trabajo as h')
                ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                ->join('orden_trabajo as ot', 'ot.id_orden_trabajo', '=', 'h.id_orden_trabajo')
                ->join('detalle_import_pedido as det', 'det.id_detalle_import_pedido', '=', 'ot.id_detalle_import_pedido')
                ->join('import_pedido as ped', 'ped.id_import_pedido', '=', 'det.id_import_pedido')
                ->select(
                    'h.id_variedad',
                    'v.nombre as variedad_nombre',
                    'p.nombre as planta_nombre',
                )->distinct()
                ->Where(function ($q) use ($fecha) {
                    $q->Where('ped.fecha', $fecha)
                        ->orWhere('ot.fecha', $fecha);
                })
                ->where('h.tallos', '>', 0)
                ->whereNotIn('h.id_variedad', $ids_variedad_compra_flor)
                ->whereNotIn('h.id_variedad', $ids_variedad_recepcion)
                ->whereNotIn('h.id_variedad', $ids_variedad_resumen)
                ->whereNotIn('h.id_variedad', $ids_variedad_pedidos)
                ->orderBy('p.nombre')
                ->orderBy('v.nombre')
                ->get();
            $ids_variedad_ot = $combinaciones_ot->pluck('id_variedad')->toArray();
            $combinaciones_pre_ot = DB::table('detalle_pre_orden_trabajo as h')
                ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                ->join('pre_orden_trabajo as ot', 'ot.id_pre_orden_trabajo', '=', 'h.id_pre_orden_trabajo')
                ->join('detalle_import_pedido as det', 'det.id_detalle_import_pedido', '=', 'ot.id_detalle_import_pedido')
                ->join('import_pedido as ped', 'ped.id_import_pedido', '=', 'det.id_import_pedido')
                ->select(
                    'h.id_variedad',
                    'v.nombre as variedad_nombre',
                    'p.nombre as planta_nombre',
                )->distinct()
                ->Where(function ($q) use ($fecha) {
                    $q->Where('ped.fecha', $fecha)
                        ->orWhere('ot.fecha', $fecha);
                })
                ->where('h.tallos', '>', 0)
                ->whereNotIn('h.id_variedad', $ids_variedad_compra_flor)
                ->whereNotIn('h.id_variedad', $ids_variedad_recepcion)
                ->whereNotIn('h.id_variedad', $ids_variedad_resumen)
                ->whereNotIn('h.id_variedad', $ids_variedad_pedidos)
                ->whereNotIn('h.id_variedad', $ids_variedad_ot)
                ->orderBy('p.nombre')
                ->orderBy('v.nombre')
                ->get();
            $ids_variedad_pre_ot = $combinaciones_pre_ot->pluck('id_variedad')->toArray();
            $variedades = $combinaciones_compra_flor->merge($combinaciones_resumen)
                ->merge($combinaciones_recepcion)
                ->merge($combinaciones_pedidos)
                ->merge($combinaciones_ot)
                ->merge($combinaciones_pre_ot)
                ->pluck('id_variedad')->toArray();
            foreach ($variedades as $pos_v => $variedad) {
                dump('fecha: ' . ($pos_f + 1) . '/' . count($fechas) . '; var: ' . ($pos_v + 1) . '/' . count($variedades));
                Artisan::call('resumen:fecha', [
                    'fecha' => $fecha,
                    'variedad' => $variedad,
                    'dev' => 0,
                ]);
            }
        }
    }

    function caca()
    {
        $postcos = Postco::where('armados', '>', 0)
            ->orderBy('fecha')->get();
        foreach($postcos as $postco){
            $ramos_armados = $postco->armados;
            $ramos_ot = DB::table('ot_postco')
                ->select(DB::raw('sum(ramos) as cantidad'))
                ->where('id_postco', $postco->id_postco)
                ->get()[0]->cantidad;
            if($ramos_armados > $ramos_ot){
                $diferencia = $ramos_armados - $ramos_ot;
                $model_armado = new ArmadoPostco();
                $model_armado->id_postco = $postco->id_postco;
                $model_armado->ramos = $diferencia;
                $model_armado->id_cliente = $postco->clientes[0]->id_cliente;
                $model_armado->save();
                $model_armado->id_armado_postco = DB::table('armado_postco')
                    ->select(DB::raw('max(id_armado_postco) as id'))
                    ->get()[0]->id;
                dump('POSTCO: '.$postco->variedad->nombre.' '.$postco->fecha.' = '.$diferencia);

                foreach ($postco->distribuciones as $key => $dist) {
                    $det_armado = new DetalleArmadoPostco();
                    $det_armado->id_armado_postco = $model_armado->id_armado_postco;
                    $det_armado->id_item = $dist->id_item;
                    $det_armado->unidades = $dist->unidades;
                    $det_armado->save();
                }
            }
        }
    }
}
