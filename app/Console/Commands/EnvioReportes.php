<?php

namespace yura\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use yura\Mail\EnvioReportes as MailReportes;
use yura\Modelos\Area;
use yura\Modelos\ConfiguracionEmpresa;
use yura\Modelos\EnvioReporte;
use yura\Modelos\EnvioReporteDone;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PHPExcel_Style_Alignment;
use PHPExcel_Style_Border;
use PHPExcel_Style_Color;
use PHPExcel_Style_Fill;

class EnvioReportes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'envio:reportes {opcion=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comando para enviar por correo los reportes';

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
        dump('<<<<< ! >>>>> Ejecutando comando "envio:reportes" <<<<< ! >>>>>');
        $opcion = $this->argument('opcion');

        try {
            $reportes = EnvioReporte::orderBy('nombre_reporte')->get();
            foreach ($reportes as $r) {
                $done = EnvioReporteDone::All()
                    ->where('id_envio_reporte', $r->id_envio_reporte)
                    ->where('fecha', hoy())
                    ->first();
                //dd($done. '; '.$r->dia_semana. '; '.date('w'). '; '.$r->hora.'; '.date('H:i:s'));
                if ($done == '' && $r->dia_semana == date('w') && $r->hora <= date('H:i:s') || $opcion == 1) {   // hacer el envio del reporte r
                    $funcion = $r->nombre_funcion;
                    $nombre_archivo = $this->$funcion($r);
                    $to = $r->usuarios[0]->usuario->correo;
                    $to = 'rafael.pratsrecasen@gmail.com';
                    $cc = [];
                    foreach ($r->usuarios as $pos => $u) {
                        if ($pos > 0)
                            $cc[] = $u->usuario->correo;
                    }
                    Mail::to($to)
                        ->cc($cc)
                        ->send(new MailReportes($nombre_archivo, $r->nombre_reporte));

                    $done = new EnvioReporteDone();
                    $done->id_envio_reporte = $r->id_envio_reporte;
                    $done->fecha = hoy();
                    //$done->save();
                }
            }
        } catch (\Exception $e) {
            // guardar el cron con estado 2 y el campo observacion = $e->getMessage();
            dd('ERROR: ' . $e->getMessage());
        }

        $time_duration = difFechas(date('Y-m-d H:i:s'), $ini)->h . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->m . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->s;
        dump('<*> DURACION: ' . $time_duration . '  <*>');
        dump('<<<<< * >>>>> Fin satisfactorio del comando "envio:reportes" <<<<< * >>>>>');
    }

    function esquejes_cosechados($r)
    {
        dump('***************** Enviar reporte "' . $r->nombre_reporte . '" **********************');
        $semana_desde = getSemanaByDate(opDiasFecha('-', 42, date('Y-m-d')));
        $semana_hasta = getSemanaByDate(opDiasFecha('-', 7, date('Y-m-d')));
        $semanas = DB::table('semana')
            ->select('codigo', 'fecha_inicial', 'fecha_final')->distinct()
            ->where('codigo', '>=', $semana_desde->codigo)
            ->where('codigo', '<=', $semana_hasta->codigo)
            ->orderBy('codigo')
            ->get();

        $listado = [];
        $finca = 2; // el Llano

        $plantas = DB::table('cosecha_plantas_madres as cos')
            ->join('variedad as v', 'v.id_variedad', '=', 'cos.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->select('v.id_planta', 'p.nombre')->distinct()
            ->where('cos.fecha', '>=', $semana_desde->fecha_inicial)
            ->where('cos.fecha', '<=', $semana_hasta->fecha_final)
            ->where('cos.id_empresa', $finca)
            ->orderBy('p.nombre')
            ->get();

        foreach ($plantas as $pos => $pta) {
            $valores = [];
            foreach ($semanas as $sem) {
                $cant = DB::table('cosecha_plantas_madres as cos')
                    ->join('variedad as v', 'v.id_variedad', '=', 'cos.id_variedad')
                    ->select(DB::raw('sum(cantidad) as cantidad'))
                    ->where('cos.fecha', '>=', $sem->fecha_inicial)
                    ->where('cos.fecha', '<=', $sem->fecha_final)
                    ->where('cos.id_empresa', $finca)
                    ->where('v.id_planta', $pta->id_planta)
                    ->get()[0]->cantidad;
                $valores[] = $cant;
            }

            array_push($listado, [
                'planta' => $pta,
                'valores' => $valores,
            ]);
        }

        /* ---------------- construir el excel ----------------- */
        $spread = new Spreadsheet();
        $objSheet = $spread->getActiveSheet()->setTitle('Esquejes cosechados');
        $columnas = getColumnasExcel();

        /* --------------- SEMANAS ------------------ */
        setValueToCeldaExcel($objSheet, 'A1', 'Semanas');
        setBgToCeldaExcel($objSheet, 'A1', '00b388');   // verde

        $totales = [];
        $col = 1;
        foreach ($semanas as $por_s => $sem) {
            setValueToCeldaExcel($objSheet, $columnas[$col] . '1', $sem->codigo);
            setBgToCeldaExcel($objSheet, $columnas[$col] . '1', '5a7177');  // dark

            array_push($totales, 0);
            $col++;
        }
        setColorTextToCeldaExcel($objSheet, 'A1:' . $columnas[$col - 1] . '1', 'FFFFFF');   // blanco

        $row = 2;
        foreach ($listado as $pos_l => $item) {
            setValueToCeldaExcel($objSheet, 'A' . $row, $item['planta']->nombre);
            setBgToCeldaExcel($objSheet, 'A' . $row, '8fdbc9');   // verde claro

            foreach ($item['valores'] as $pos_c => $val) {
                $val = $val > 0 ? $val : 0;
                setValueToCeldaExcel($objSheet, $columnas[$pos_c + 1] . $row, $val);
                setBgToCeldaExcel($objSheet, $columnas[$pos_c + 1] . $row, '8fdbc9');   // verde claro
                $totales[$pos_c] += $val;
            }

            $row++;
        }
        /* --------------- TOTALES ------------------ */
        setValueToCeldaExcel($objSheet, 'A' . $row, 'Totales');
        setBgToCeldaExcel($objSheet, 'A' . $row, '00b388');   // verde

        foreach ($totales as $pos_t => $val) {
            setValueToCeldaExcel($objSheet, $columnas[$pos_t + 1] . $row, $val);
            setBgToCeldaExcel($objSheet, $columnas[$pos_t + 1] . $row, '5a7177');   // dark
        }
        setColorTextToCeldaExcel($objSheet, 'A' . $row . ':' . $columnas[$col - 1] . $row, 'FFFFFF');   // blanco

        /* estilos generales */
        setTextCenterToCeldaExcel($objSheet, 'B1:' . $columnas[$col - 1] . ($row));
        setBorderToCeldaExcel($objSheet, 'A1:' . $columnas[$col - 1] . ($row));
        for ($i = 0; $i <= $col; $i++)
            $objSheet->getColumnDimension($columnas[$i])->setAutoSize(true);

        /* --------------------------- FIN ---------------------------- */
        $spread->getProperties()
            ->setCreator("Benchflow")
            //->setLastModifiedBy('BaulPHP')
            ->setTitle('Reporte Esquejes Cosechados')
            ->setSubject('Reporte del sistema')
            ->setDescription('Reporte generado desde el sistema BenchflowSystem');
        //->setKeywords('PHPSpreadsheet')
        //->setCategory('Categoría Excel');

        $fileName = "Esquejes_cosechados_" . hoy() . ".xlsx";
        $writer = new Xlsx($spread);

        //--------------------------- GUARDAR EL EXCEL -----------------------

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        $writer->save(public_path() . '/storage/files_mail/' . $fileName);

        return $fileName;
    }

    function proyeccion_semanal($r)
    {

    }

    function p_y_g_semanal($r)
    {
        dump('***************** Enviar reporte "' . $r->nombre_reporte . '" **********************');

        $empresas = ConfiguracionEmpresa::All();
        $sem_hasta = getSemanaByDate(opDiasFecha('-', 7, date('Y-m-d')));
        $sem_desde = getSemanaByDate(opDiasFecha('-', 42, date('Y-m-d')));
        $semanas = DB::table('semana')
            ->select('codigo', 'fecha_inicial', 'fecha_final')->distinct()
            ->where('codigo', '>=', $sem_desde->codigo)
            ->where('codigo', '<=', $sem_hasta->codigo)
            ->orderBy('codigo')
            ->get();
        /* ---------------- construir el excel ----------------- */
        $spread = new Spreadsheet();
        foreach ($empresas as $p => $f) {
            $finca = $f->id_configuracion_empresa;
            if ($p == 0)
                $objSheet = $spread->getActiveSheet()->setTitle($f->nombre);
            else
                $objSheet = $spread->createSheet()->setTitle($f->nombre);

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
                ->where('semana', '>=', $sem_desde->codigo)
                ->where('semana', '<=', $sem_hasta->codigo)
                ->groupBy('semana')
                ->orderBy('semana')
                ->get();
            $resumen_costos = DB::table('resumen_costos_semanal')
                ->where('id_empresa', $finca)
                ->where('codigo_semana', '>=', $sem_desde->codigo)
                ->where('codigo_semana', '<=', $sem_hasta->codigo)
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
                        ->where('cs.codigo_semana', '>=', $sem_desde->codigo)
                        ->where('cs.codigo_semana', '<=', $sem_hasta->codigo)
                        ->groupBy('cs.codigo_semana')
                        ->orderBy('cs.codigo_semana')
                        ->get(),
                    'mano_obra' => DB::table('costos_semana_mano_obra as cs')
                        ->join('actividad_mano_obra as amo', 'amo.id_actividad_mano_obra', '=', 'cs.id_actividad_mano_obra')
                        ->join('actividad as a', 'a.id_actividad', '=', 'amo.id_actividad')
                        ->select(DB::raw('sum(cs.valor) as valor'), 'cs.codigo_semana')
                        ->where('cs.id_empresa', $finca)
                        ->where('a.id_area', $a->id_area)
                        ->where('cs.codigo_semana', '>=', $sem_desde->codigo)
                        ->where('cs.codigo_semana', '<=', $sem_hasta->codigo)
                        ->groupBy('cs.codigo_semana')
                        ->orderBy('cs.codigo_semana')
                        ->get(),
                    'otros_gastos' => DB::table('otros_gastos')
                        ->where('id_empresa', $finca)
                        ->where('id_area', $a->id_area)
                        ->where('codigo_semana', '>=', $sem_desde->codigo)
                        ->where('codigo_semana', '<=', $sem_hasta->codigo)
                        ->orderBy('codigo_semana')
                        ->get(),
                ]);
            }

            /*$indicadores_4_semanas = DB::table('indicadores_4_semanas')
                ->where('semana', '>=', $sem_desde->codigo)
                ->where('semana', '<=', $sem_hasta->codigo)
                ->where('id_empresa', $finca)
                ->orderBy('semana')
                ->get();*/

            /* ----------------------- CREAR HOJA DE EXCEL ------------------------ */
            $columnas = getColumnasExcel();

            foreach ($semanas as $col => $sem) {
                setValueToCeldaExcel($objSheet, $columnas[$col + 1] . '1', $sem->codigo);
            }
            setValueToCeldaExcel($objSheet, $columnas[$col + 2] . '1', 'Total');
            setColorTextToCeldaExcel($objSheet, 'A1:' . $columnas[$col + 2] . '1', 'FFFFFF');    // verde
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

        $spread->getProperties()
            ->setCreator("Benchflow")
            ->setTitle('Reporte P y G semanal')
            ->setSubject('P y G semanal')
            ->setDescription('Reporte generado desde el sistema BenchflowSystem');

        $fileName = "P_y_G_" . hoy() . ".xlsx";
        $writer = new Xlsx($spread);

        //--------------------------- GUARDAR EL EXCEL -----------------------
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        $writer->save(public_path() . '/storage/files_mail/' . $fileName);

        return $fileName;
    }
}