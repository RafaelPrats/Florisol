<?php

namespace yura\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
//use PHPExcel_IOFactory;
use \PhpOffice\PhpSpreadsheet\IOFactory as IOFactory;
use yura\Modelos\Actividad;
use yura\Modelos\ActividadManoObra;
use yura\Modelos\ActividadProducto;
use yura\Modelos\ConfiguracionEmpresa;
use yura\Modelos\CostosSemana;
use yura\Modelos\CostosSemanaManoObra;
use yura\Modelos\ManoObra;
use yura\Modelos\Producto;

class UploadCostosMasivoDetails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'costos:importar_file_details {url=0} {concepto=0} {criterio=0} {sobreescribir=0} {finca=0}';

    /**
     * url = nombre completo del archivo
     * concepto => I, insumos _ M, mano de obra
     * criterio => V, dinero _ C, cantidad
     * sobreescribir => S, si _ I, sumar a lo anterior
     * finca => id de la empresa a la que pertenecen los costos
     *
     * @var string
     */
    protected $description = 'Comando para subir los costos mediante un excel con los detalles por fecha';

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
        Log::info('<<<<< ! >>>>> Ejecutando comando "costos:importar_file_details" <<<<< ! >>>>>');

        $url = $this->argument('url');
        $concepto_importar = $this->argument('concepto');
        $criterio_importar = $this->argument('criterio');
        $sobreescribir = $this->argument('sobreescribir');
        $finca = $this->argument('finca');

        $document = IOFactory::load($url);
        $activeSheetData = $document->getActiveSheet()->toArray(null, true, true, true);
        if ($concepto_importar == 'I') {
            $this->importar_insumos($activeSheetData, $concepto_importar, $criterio_importar, $sobreescribir, $finca);
        } else {
            $this->importar_mano_obra($activeSheetData, $concepto_importar, $criterio_importar, $sobreescribir, $finca);
        }

        $time_duration = difFechas(date('Y-m-d H:i:s'), $ini)->h . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->m . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->s;
        Log::info('<*> DURACION: ' . $time_duration . '  <*>');
        Log::info('<<<<< * >>>>> Fin satisfactorio del comando "costos:importar_file_details" <<<<< * >>>>>');
    }

    public function importar_insumos($activeSheetData, $concepto_importar, $criterio_importar, $sobreescribir = false, $finca)
    {
        $titles = $activeSheetData[1];
        $lista = [];
        $faltantes = [];
        foreach ($activeSheetData as $pos_row => $row) {
            if ($pos_row > 1) {
                $finca = ConfiguracionEmpresa::All()->where('nombre', $row['A'])->first();
                if ($finca != '') {
                    dump($row);
                    $anno = explode('.', $row['B'])[2];
                    $mes = explode('.', $row['B'])[1];
                    $mes = strlen($mes) == 1 ? '0' . $mes : $mes;
                    $dia = explode('.', $row['B'])[0];
                    $dia = strlen($dia) == 1 ? '0' . $dia : $dia;
                    $fecha = $anno . '-' . $mes . '-' . $dia;
                    $semana = getSemanaByDate($fecha);
                    $actividad = Actividad::All()
                        ->where('nombre', espacios(mb_strtoupper($row['C'])))
                        ->where('id_empresa', $finca->id_configuracion_empresa)
                        ->first();  //query
                    if ($semana != '') {
                        if ($actividad != '') {
                            $producto = Producto::All()
                                ->where('nombre', espacios(mb_strtoupper($row['D'])))
                                ->where('id_empresa', $finca->id_configuracion_empresa)
                                ->first();  //query
                            if ($producto != '') {
                                dump('pos: ' . $pos_row . '/' . count($activeSheetData) . '-' . porcentaje($pos_row, count($activeSheetData), 1) . '% - fecha: ' . $fecha . ' - sem: ' . $semana->codigo . ' - act: ' . $actividad->nombre . ' - prod: ' . $producto->nombre);
                                $existe = false;
                                for ($i = 0; $i < count($lista); $i++) {
                                    if ($lista[$i]['semana'] == $semana->codigo && $lista[$i]['actividad']->id_actividad == $actividad->id_actividad && $lista[$i]['producto']->id_producto == $producto->id_producto && $lista[$i]['finca'] == $finca->id_configuracion_empresa) {
                                        $lista[$i]['cantidad'] += floatval(str_replace(',', '', $row['F']));
                                        $lista[$i]['valor'] += floatval(str_replace(',', '', $row['E']));
                                        $existe = true;
                                    }
                                }
                                if (!$existe) {
                                    array_push($lista, [
                                        'finca' => $finca->id_configuracion_empresa,
                                        'semana' => $semana->codigo,
                                        'actividad' => $actividad,
                                        'producto' => $producto,
                                        'cantidad' => floatval(str_replace(',', '', $row['F'])),
                                        'valor' => floatval(str_replace(',', '', $row['E'])),
                                    ]);
                                }

                                /* guardar en la tabla COSTOS_DIARIO_PRODUCTO */
                                //$model = Cos
                            } else {
                                dump('pos: ' . $pos_row . '****************** ERROR *******************');
                                dump('En la Finca: "' . $finca->nombre . '", no se ha encontrado el PRODUCTO: "' . espacios(mb_strtoupper($row['D'])) . '"');
                                if (!in_array('En la finca: ' . $finca->nombre . ', no se ha encontrado el PRODUCTO: ' . espacios(mb_strtoupper($row['D'])), $faltantes))
                                    array_push($faltantes, 'En la finca: ' . $finca->nombre . ', no se ha encontrado el PRODUCTO: ' . espacios(mb_strtoupper($row['D'])));
                            }
                        } else {
                            dump('pos: ' . $pos_row . '****************** ERROR *******************');
                            dump('En la finca: "' . $finca->nombre . '", no se ha encontrado la ACTIVIDAD: "' . espacios(mb_strtoupper($row['C'])) . '"');
                            if (!in_array('En la finca: ' . $finca->nombre . ', no se ha encontrado la ACTIVIDAD: ' . espacios(mb_strtoupper($row['C'])), $faltantes))
                                array_push($faltantes, 'En la finca: ' . $finca->nombre . ', no se ha encontrado la ACTIVIDAD: ' . espacios(mb_strtoupper($row['C'])));
                        }
                    } else {
                        dump('pos: ' . $pos_row . '****************** ERROR *******************');
                        dump('No se ha encontrado la SEMANA de la FECHA: "' . $row['B'] . '"');
                    }
                } else {
                    dump('pos: ' . $pos_row . '****************** ERROR *******************');
                    dump('No se ha encontrado la FINCA: "' . $row['A'] . '"');
                    if (!in_array('No se ha encontrado la FINCA: ' . $row['A'], $faltantes))
                        array_push($faltantes, 'No se ha encontrado la FINCA: ' . $row['A']);
                }
            }
        }
        dump('=========== GUARDAR DATOS ===========');
        foreach ($lista as $pos_item => $item) {
            $act_prod = ActividadProducto::All()
                ->where('id_actividad', $item['actividad']->id_actividad)
                ->where('id_producto', $item['producto']->id_producto)
                ->first();
            if ($act_prod == '') {
                $act_prod = new ActividadProducto();
                $act_prod->id_actividad = $item['actividad']->id_actividad;
                $act_prod->id_producto = $item['producto']->id_producto;
                $act_prod->save();
                $act_prod = ActividadProducto::All()->last();
            }
            $costo_semana = CostosSemana::All()
                ->where('id_empresa', $item['finca'])
                ->where('codigo_semana', $item['semana'])
                ->where('id_actividad_producto', $act_prod->id_actividad_producto)
                ->first();
            if ($costo_semana == '') {
                $costo_semana = new CostosSemana();
                $costo_semana->codigo_semana = $item['semana'];
                $costo_semana->id_actividad_producto = $act_prod->id_actividad_producto;
                $costo_semana->valor = 0;
                $costo_semana->cantidad = 0;
                $costo_semana->id_empresa = $item['finca'];
            }
            if ($sobreescribir == 'S') {
                $costo_semana->valor = $item['valor'];
            } else {
                $costo_semana->valor += $item['valor'];
            }
            dump($pos_item . '/' . count($lista) . '-' . porcentaje($pos_item, count($lista), 1) . '% - act_prod: ' . $act_prod->id_actividad_producto . ' - act: ' . $item['actividad']->nombre . ' - prod: ' . $item['producto']->nombre . ' -sem: ' . $item['semana'] . ' - valor: ' . $item['valor']);
            $costo_semana->save();
        }
        if (count($faltantes) > 0) {
            dump('=========== FALTANTES ===========');
            dump($faltantes);
            /* ------------ ACTUALIZR NOTIFICACION fallos_upload_insumos --------------- */
            NotificacionesSistema::fallos_upload_insumos($faltantes);
        }
    }

    public function importar_mano_obra($activeSheetData, $concepto_importar, $criterio_importar, $sobreescribir = false, $finca)
    {
        $titles = $activeSheetData[1];
        $lista = [];
        $faltantes = [];
        foreach ($activeSheetData as $pos_row => $row) {
            if ($pos_row > 1) {
                $finca = ConfiguracionEmpresa::All()->where('nombre', $row['A'])->first();
                if ($finca != '') {
                    dump($row);
                    $anno = explode('/', $row['J'])[2];
                    $mes = explode('/', $row['J'])[0];
                    $mes = strlen($mes) == 1 ? '0' . $mes : $mes;
                    $dia = explode('/', $row['J'])[1];
                    $dia = strlen($dia) == 1 ? '0' . $dia : $dia;
                    $fecha = $anno . '-' . $mes . '-' . $dia;
                    $semana = getSemanaByDate($fecha);
                    if ($semana != '') {
                        $actividad = Actividad::All()
                            ->where('nombre', espacios(mb_strtoupper($row['E'])))
                            ->where('id_empresa', $finca->id_configuracion_empresa)
                            ->first();  //query
                        if ($actividad != '') {
                            $mo = ManoObra::All()
                                ->where('nombre', espacios(mb_strtoupper($row['F'])))
                                ->where('id_empresa', $finca->id_configuracion_empresa)
                                ->first();  //query
                            if ($mo != '') {
                                /* CALCULAR TOTAL */
                                $dias_lab_mes = count(bussiness_days($anno . '-' . $mes . '-01', $anno . '-' . $mes . '-' . getUltimoDiaMes($anno, $mes))[$anno . '-' . $mes]);
                                $hor_efect_mes = $dias_lab_mes * 8; // CantDiasLaborables/Mes *8(hrs/dia)
                                $cost_hr_ord = $row['G'] / $hor_efect_mes;  // Sueldo / HorasEfectivasMes
                                $cost_hr_supl = $row['G'] / 240;    // Sueldo / 240(30dias * 8)
                                $cost_dia_pers = $row['L'] * $cost_hr_ord;   // HoraNormal * CostHrsOrdinaria
                                $cost_dia_50_pers = $row['M'] * (1.5 * $cost_hr_supl); // Hrs50  * (50% de CostHrOrdinaria)
                                $cost_dia_100_pers = $row['N'] * (2 * $cost_hr_supl); // Hrs100  * (100% de CostHrOrdinaria)
                                $cost_dia_aus_pers = $row['O'] * $cost_hr_ord;    // HrsAusentismo * CostHrsOrdinaria
                                $cost_total_dia_pers = $cost_dia_pers + $cost_dia_50_pers + $cost_dia_100_pers + $cost_dia_aus_pers;    // CostoTotalDiaPersona
                                if (count(explode('/', $row['H'])) == 3) {
                                    $anno_ingreso = explode('/', $row['H'])[2];
                                    $mes_ingreso = explode('/', $row['H'])[0];
                                    $mes_ingreso = strlen($mes) == 1 ? '0' . $mes_ingreso : $mes_ingreso;
                                    $dia_ingreso = explode('/', $row['H'])[1];
                                    $dia_ingreso = strlen($dia) == 1 ? '0' . $dia_ingreso : $dia_ingreso;
                                    $fecha_ingreso = $anno_ingreso . '-' . $mes_ingreso . '-' . $dia_ingreso;
                                    dump('fecha: ' . $fecha . ' || fecha_ingreso: ' . $fecha_ingreso);
                                    $cant_anno_activo = difFechas($fecha, $fecha_ingreso)->days / 365;  // CantidadAnnosActivo
                                    $prov_13th = $cost_total_dia_pers / 12;  //  Provision 13º
                                    $prov_14th = 401.41 / 261;  //  Sueldo Basico en el pais / ...
                                    $fondos_reserva = $cant_anno_activo > 1 ? $prov_13th : 0;
                                    $aporte_patronal = (12.15 * $cost_total_dia_pers) / 100;   //  12.15% de CostoTotalDiaPersona
                                    $total = $cost_total_dia_pers + $prov_13th + $prov_14th + $fondos_reserva + $aporte_patronal;   //  TOTAL

                                    /*dump('$dias_lab_mes = ' . $dias_lab_mes);
                                    dump('$hor_efect_mes = ' . $hor_efect_mes);
                                    dump('$cost_hr_ord = ' . $cost_hr_ord);
                                    dump('$cost_hr_supl = ' . $cost_hr_supl);
                                    dump('$cost_dia_pers = ' . $cost_dia_pers);
                                    dump('$cost_dia_50_pers = ' . $cost_dia_50_pers);
                                    dump('$cost_dia_100_pers = ' . $cost_dia_100_pers);
                                    dump('$cost_dia_aus_pers = ' . $cost_dia_aus_pers);
                                    dump('$cost_total_dia_pers = ' . $cost_total_dia_pers);
                                    dump('$prov_13th = ' . $prov_13th);
                                    dump('$prov_14th = ' . $prov_14th);
                                    dump('$fondos_reserva = ' . $fondos_reserva);
                                    dump('fecha_ingreso: ' . $fecha_ingreso . ' || fecha: ' . $fecha . ' = dias: ' . difFechas($fecha, $fecha_ingreso)->days . ' || annos: ' . $cant_anno_activo);
                                    dump('$aporte_patronal = ' . $aporte_patronal);*/
                                    dump('pos: ' . $pos_row . '/' . count($activeSheetData) . '-' . porcentaje($pos_row, count($activeSheetData), 1) . '% - fecha: ' . $fecha . ' - sem: ' . $semana->codigo . ' - act: ' . $actividad->nombre . ' - mo: ' . $mo->nombre . ' - TOTAL: ' . $total);
                                    //dump('$total: '. $total, '$cost_total_dia_pers: '.$cost_total_dia_pers, '$prov_13th: '.$prov_13th, '$prov_14th: '.$prov_14th, '$fondos_reserva: '.$fondos_reserva, '$aporte_patronal: '.$aporte_patronal);
                                    $existe = false;
                                    for ($i = 0; $i < count($lista); $i++) {
                                        if ($lista[$i]['semana'] == $semana->codigo && $lista[$i]['actividad']->id_actividad == $actividad->id_actividad && $lista[$i]['mano_obra']->id_mano_obra == $mo->id_mano_obra && $lista[$i]['finca'] == $finca->id_configuracion_empresa) {
                                            $lista[$i]['valor'] += $total;
                                            $lista[$i]['valor_50'] += $cost_dia_50_pers;
                                            $lista[$i]['valor_100'] += $cost_dia_100_pers;
                                            $lista[$i]['cantidad'] += 1;
                                            $existe = true;
                                        }
                                    }
                                    if (!$existe) {
                                        array_push($lista, [
                                            'finca' => $finca->id_configuracion_empresa,
                                            'semana' => $semana->codigo,
                                            'actividad' => $actividad,
                                            'mano_obra' => $mo,
                                            'valor' => $total,
                                            'valor_50' => $cost_dia_50_pers,
                                            'valor_100' => $cost_dia_100_pers,
                                            'cantidad' => 1,
                                        ]);
                                    }
                                } else {
                                    dump('pos: ' . $pos_row . '****************** ERROR *******************');
                                    dump('En la fila: "' . $pos_row . '", la fecha de ingreso no es correcta');
                                    if (!in_array('En la fila: "' . $pos_row . '", la fecha de ingreso no es correcta', $faltantes))
                                        array_push($faltantes, 'En la fila: ' . $pos_row . ', la fecha de ingreso no es correcta');
                                }
                            } else {
                                dump('pos: ' . $pos_row . '****************** ERROR *******************');
                                dump('En la Finca: "' . $finca->nombre . '", no se ha encontrado la MANO de OBRA: "' . espacios(mb_strtoupper($row['F'])) . '"');
                                if (!in_array('En la finca: ' . $finca->nombre . ', no se ha encontrado la MANO de OBRA: ' . espacios(mb_strtoupper($row['F'])), $faltantes))
                                    array_push($faltantes, 'En la finca: ' . $finca->nombre . ', no se ha encontrado la MANO de OBRA: ' . espacios(mb_strtoupper($row['F'])));
                            }
                        } else {
                            dump('pos: ' . $pos_row . '****************** ERROR *******************');
                            dump('En la finca: "' . $finca->nombre . '", no se ha encontrado la ACTIVIDAD: "' . espacios(mb_strtoupper($row['E'])) . '"');
                            if (!in_array('En la finca: ' . $finca->nombre . ', no se ha encontrado la ACTIVIDAD: ' . espacios(mb_strtoupper($row['E'])), $faltantes))
                                array_push($faltantes, 'En la finca: ' . $finca->nombre . ', no se ha encontrado la ACTIVIDAD: ' . espacios(mb_strtoupper($row['E'])));
                        }
                    } else {
                        dump('pos: ' . $pos_row . '****************** ERROR *******************');
                        dump('No se ha encontrado la SEMANA de la FECHA: "' . $row['B'] . '"');
                    }
                } else {
                    dump('pos: ' . $pos_row . '****************** ERROR *******************');
                    dump('No se ha encontrado la FINCA: "' . $row['A'] . '"');
                    if (!in_array('No se ha encontrado la FINCA: ' . $row['A'], $faltantes))
                        array_push($faltantes, 'No se ha encontrado la FINCA: ' . $row['A']);
                }
            }
        }
        dump('=========== GUARDAR DATOS ===========');
        foreach ($lista as $pos_item => $item) {
            $act_mo = ActividadManoObra::All()
                ->where('id_actividad', $item['actividad']->id_actividad)
                ->where('id_mano_obra', $item['mano_obra']->id_mano_obra)
                ->first();
            if ($act_mo == '') {
                $act_mo = new ActividadManoObra();
                $act_mo->id_actividad = $item['actividad']->id_actividad;
                $act_mo->id_mano_obra = $item['mano_obra']->id_mano_obra;
                $act_mo->save();
                $act_mo = ActividadManoObra::All()->last();
            }
            $costo_semana = CostosSemanaManoObra::All()
                ->where('id_empresa', $item['finca'])
                ->where('codigo_semana', $item['semana'])
                ->where('id_actividad_mano_obra', $act_mo->id_actividad_mano_obra)
                ->first();
            if ($costo_semana == '') {
                $costo_semana = new CostosSemanaManoObra();
                $costo_semana->codigo_semana = $item['semana'];
                $costo_semana->id_actividad_mano_obra = $act_mo->id_actividad_mano_obra;
                $costo_semana->valor = 0;
                $costo_semana->cantidad = $item['cantidad'];
                $costo_semana->id_empresa = $item['finca'];
            }
            if ($sobreescribir == 'S') {
                $costo_semana->cantidad = $item['cantidad'];
                $costo_semana->valor = $item['valor'];
                $costo_semana->valor_50 = $item['valor_50'];
                $costo_semana->valor_100 = $item['valor_100'];
            } else {
                $costo_semana->cantidad += $item['cantidad'];
                $costo_semana->valor += $item['valor'];
                $costo_semana->valor_50 += $item['valor_50'];
                $costo_semana->valor_100 += $item['valor_100'];
            }
            dump(($pos_item + 1) . '/' . count($lista) . '-' . porcentaje($pos_item, count($lista), 1) . '% - act_mo: ' . $act_mo->id_actividad_mano_obra . ' - act: ' . $item['actividad']->nombre . ' - prod: ' . $item['mano_obra']->nombre . ' -sem: ' . $item['semana'] . ' - valor: ' . $item['valor']);
            $costo_semana->save();
        }
        if (count($faltantes) > 0) {
            dump('=========== FALTANTES ===========');
            dump($faltantes);
            /* ------------ ACTUALIZR NOTIFICACION fallos_upload_insumos --------------- */
            NotificacionesSistema::fallos_upload_mano_obra($faltantes);
        }
    }
}
