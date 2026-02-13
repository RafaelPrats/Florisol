<?php

namespace yura\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use yura\Modelos\Ciclo;
use yura\Modelos\Planta;
use yura\Modelos\ResumenFenogramaEjecucion;
use yura\Modelos\Submenu;
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Style_Alignment;
use PHPExcel_Style_Border;
use PHPExcel_Style_Color;
use PHPExcel_Style_Fill;
use PHPExcel_Worksheet;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use yura\Modelos\Sector;

class FenogramaEjecucionController extends Controller
{
    public function inicio(Request $request)
    {
        return view('adminlte.crm.fenograma_ejecucion.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'sectores' => Sector::where('estado', 1)
                ->orderBy('nombre')
                ->get(),
            'plantas' => Planta::join('variedad as v', 'v.id_planta', '=', 'planta.id_planta')
                ->select('planta.*')->distinct()
                ->where('v.estado', 1)
                ->where('planta.estado', 1)
                ->where('planta.tiene_ciclos', 1)
                ->where('v.compra_flor', 0)
                ->where('planta.tipo', 'N')
                ->orderBy('planta.nombre')
                ->get(),
        ]);
    }

    public function filtrar_ciclos(Request $request)
    {
        $finca_actual = getFincaActiva();
        $ciclos = DB::table('ciclo as c')
            ->join('variedad as v', 'v.id_variedad', '=', 'c.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->join('modulo as m', 'm.id_modulo', '=', 'c.id_modulo')
            ->select('c.id_ciclo')->distinct()
            ->where('c.estado', 1)
            ->where('m.estado', 1)
            ->where('p.tiene_ciclos', 1)
            ->where('p.tipo', 'N');
        if ($request->activo == '')
            $ciclos = $ciclos->where('c.fecha_inicio', '<=', $request->fecha)
                ->where('c.fecha_fin', '>=', $request->fecha);
        else {
            $desde = $request->fecha;
            $hasta = $request->hasta;
            $ciclos = $ciclos->where('activo', $request->activo)
                ->Where(function ($q) use ($desde, $hasta) {
                    $q->where('c.fecha_fin', '>=', $desde)
                        ->where('c.fecha_fin', '<=', $hasta)
                        ->orWhere(function ($q) use ($desde, $hasta) {
                            $q->where('c.fecha_inicio', '>=', $desde)
                                ->where('c.fecha_inicio', '<=', $hasta);
                        })
                        ->orWhere(function ($q) use ($desde, $hasta) {
                            $q->where('c.fecha_inicio', '<', $desde)
                                ->where('c.fecha_fin', '>', $hasta);
                        });
                });
        }
        if ($request->variedad != 'T')
            $ciclos = $ciclos->where('c.id_variedad', $request->variedad);
        elseif ($request->planta != '')
            $ciclos = $ciclos->where('v.id_planta', $request->planta);
        if ($finca_actual != 'T')
            $ciclos = $ciclos->where('c.id_empresa', $finca_actual);
        if ($request->sector != 'T')
            $ciclos = $ciclos->where('m.id_sector', $request->sector);
        if ($request->poda_siembra != '')
            $ciclos = $ciclos->where('c.poda_siembra', $request->poda_siembra);
        $ciclos = $ciclos->orderBy('c.fecha_inicio')->get();

        $ids_ciclos = [];
        foreach ($ciclos as $c)
            array_push($ids_ciclos, $c->id_ciclo);

        $query = DB::table('resumen_fenograma_ejecucion as r')
            ->join('ciclo as c', 'c.id_ciclo', '=', 'r.id_ciclo')
            ->join('variedad as v', 'v.id_variedad', '=', 'c.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->join('modulo as m', 'm.id_modulo', '=', 'r.id_modulo')
            ->join('sector as s', 's.id_sector', '=', 'm.id_sector')
            ->select(
                'r.*',
                'c.fecha_inicio as fecha_inicio_ciclo',
                'c.fecha_fin as fecha_fin_ciclo',
                'v.nombre as var_nombre',
                'p.nombre as pta_nombre',
                's.nombre as sector_modulo'
            )
            ->whereIn('r.id_ciclo', $ids_ciclos)
            ->get();

        return view('adminlte.crm.fenograma_ejecucion.partials.filtrar_ciclos', [
            'ciclos' => $query,
            'estado' => $request->activo
        ]);
    }

    public function exportar_reporte(Request $request)
    {
        $spread = new Spreadsheet();
        $this->excel_reporte($spread, $request);
        $spread->getProperties()
            ->setCreator("Benchflow")
            ->setTitle('Fenograma de ejecucion')
            ->setSubject('Fenograma de ejecucion');

        $fileName = "Fenograma_ejecucion.xlsx";
        $writer = new Xlsx($spread);

        //--------------------------- GUARDAR EL EXCEL -----------------------

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        $writer->save('php://output');
    }

    public function excel_reporte($spread, $request)
    {
        $finca_actual = getFincaActiva();
        $ciclos = DB::table('ciclo as c')
            ->select('c.id_ciclo')->distinct()
            ->where('c.estado', 1)
            ->join('variedad as v', 'v.id_variedad', '=', 'c.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->where('p.tipo', 'N');
        if ($request->activo == '')
            $ciclos = $ciclos->where('c.fecha_inicio', '<=', $request->fecha)
                ->where('c.fecha_fin', '>=', $request->fecha);
        else {
            $desde = $request->fecha;
            $hasta = $request->hasta;
            $ciclos = $ciclos->where('activo', $request->activo)
                ->Where(function ($q) use ($desde, $hasta) {
                    $q->where('c.fecha_fin', '>=', $desde)
                        ->where('c.fecha_fin', '<=', $hasta)
                        ->orWhere(function ($q) use ($desde, $hasta) {
                            $q->where('c.fecha_inicio', '>=', $desde)
                                ->where('c.fecha_inicio', '<=', $hasta);
                        })
                        ->orWhere(function ($q) use ($desde, $hasta) {
                            $q->where('c.fecha_inicio', '<', $desde)
                                ->where('c.fecha_fin', '>', $hasta);
                        });
                });
        }
        if ($request->var != 'T')
            $ciclos = $ciclos->where('c.id_variedad', $request->var);
        elseif ($request->planta != '')
            $ciclos = $ciclos->where('v.id_planta', $request->planta);
        if ($finca_actual != 'T')
            $ciclos = $ciclos->where('c.id_empresa', $finca_actual);
        $ciclos = $ciclos->orderBy('c.fecha_inicio')->get();

        $ids_ciclos = [];
        foreach ($ciclos as $c)
            array_push($ids_ciclos, $c->id_ciclo);

        $query = DB::table('resumen_fenograma_ejecucion as r')
            ->join('ciclo as c', 'c.id_ciclo', '=', 'r.id_ciclo')
            ->select('r.*', 'c.fecha_inicio as fecha_inicio_ciclo', 'c.fecha_fin as fecha_fin_ciclo')
            ->whereIn('r.id_ciclo', $ids_ciclos)
            ->orderBy('c.fecha_inicio')
            ->get();

        if (count($query) > 0) {
            $objSheet = $spread->getActiveSheet()->setTitle('Fenograma de ejecucion');

            $objSheet->mergeCells('A1:R1');
            $objSheet->getStyle('A1:R1')->getFont()->setBold(true)->setSize(12);
            $objSheet->getStyle('A1:R1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objSheet->getStyle('A1:R1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('CCFFCC');

            $objSheet->getCell('A1')->setValue('Reporte Fenograma de Ejecucion');

            $objSheet->getCell('A2')->setValue('MÓDULO');
            $objSheet->getCell('B2')->setValue('FECHA');
            $objSheet->getCell('C2')->setValue('SEMANA');
            $objSheet->getCell('D2')->setValue('P/S');
            $objSheet->getCell('E2')->setValue('DÍAS');
            $objSheet->getCell('F2')->setValue('ÁREA m2');
            $objSheet->getCell('G2')->setValue('TOTAL x SEMANA m2');
            $objSheet->getCell('H2')->setValue('1ra FLOR');
            $objSheet->getCell('I2')->setValue('%M');
            $objSheet->getCell('J2')->setValue('TALLOS COSECHADOS');
            $objSheet->getCell('K2')->setValue('REAL TALLOS/m2');
            $objSheet->getCell('L2')->setValue('COSECHADO %');
            $objSheet->getCell('M2')->setValue('PROY TALLOS/m2');
            $objSheet->getCell('N2')->setValue('Ptas INICIALES');
            $objSheet->getCell('O2')->setValue('Ptas ACTUALES');
            $objSheet->getCell('P2')->setValue('DEND. P.INI/m2');
            $objSheet->getCell('Q2')->setValue('CONTEO T/P');

            $objSheet->getStyle('A2:R2')->getFont()->setBold(true)->setSize(12);

            $objSheet->getStyle('A2:R2')
                ->getBorders()
                ->getAllBorders()
                ->setBorderStyle(PHPExcel_Style_Border::BORDER_DOUBLE)
                ->getColor()
                ->setRGB(PHPExcel_Style_Color::COLOR_BLACK);

            $objSheet->getStyle('A2:R2')
                ->getFill()
                ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                ->getStartColor()
                ->setRGB('CCFFCC');

            //--------------------------- LLENAR LA TABLA ---------------------------------------------
            $total_area = 0;
            $ciclo = 0;
            $total_tallos = 0;
            $total_tallos_m2 = 0;
            $positivos_tallos_m2 = 0;
            $total_iniciales = 0;
            $total_actuales = 0;
            $total_mortalidad = [
                'valor' => 0,
                'positivos' => 0,
            ];
            $total_densidad = [
                'valor' => 0,
                'positivos' => 0,
            ];
            $total_tallos_m2_proy = [
                'valor' => 0,
                'positivos' => 0,
            ];
            $codigo_semana = $query[0]->semana;
            $area = 0;
            foreach ($query as $pos => $item) {
                $semana = $item->semana;
                $poda_siembra = $item->poda_siembra;
                $tallos_cosechados = $item->tallos_cosechados;
                $desecho = $item->desecho;
                $conteo = $item->conteo;
                $plantas_actuales = $item->plantas_actuales;
                $getDensidadIniciales = $item->densidad_plantas_ini_m2;
                $tallos_m2_cos = $item->real_tallos_m2;
                $tallos_m2_proy = $item->proy_tallos_m2;

                $objSheet->getCell('A' . ($pos + 3))->setValue($item->nombre_modulo);
                $fecha_text = $item->fecha_inicio_ciclo;
                if ($request->activo == 0)
                    $fecha_text .= ' - ' . $item->fecha_fin_ciclo;
                $objSheet->getCell('B' . ($pos + 3))->setValue($fecha_text);
                $objSheet->getCell('C' . ($pos + 3))->setValue($item->semana);
                $objSheet->getCell('D' . ($pos + 3))->setValue($item->poda_siembra);
                $objSheet->getCell('E' . ($pos + 3))->setValue(difFechas($item->fecha_fin_ciclo, $item->fecha_inicio_ciclo)->days);
                $objSheet->getCell('F' . ($pos + 3))->setValue($item->area_m2);
                if ($codigo_semana == $semana) {
                    $area += $item->area_m2;
                } else {
                    $area = $item->area_m2;
                    $codigo_semana = $semana;
                }
                if ($pos + 1 < count($query)) {
                    if ($query[$pos + 1]->semana != $codigo_semana) {
                        $objSheet->getCell('G' . ($pos + 3))->setValue($area);
                    }
                } else {
                    $objSheet->getCell('G' . ($pos + 3))->setValue($area);
                }
                $objSheet->getCell('H' . ($pos + 3))->setValue($item->primera_flor);
                $mortalidad = $item->porciento_mortalidad;
                $color = 'EF6E11';
                if ($mortalidad < 10)
                    $color = 'D01C62';
                if ($mortalidad > 20)
                    $color = '00B388';
                $objSheet->getCell('I' . ($pos + 3))->setValue($mortalidad);
                $objSheet->getStyle('I' . ($pos + 3))
                    ->getFont()
                    ->getColor()
                    ->setRGB($color);
                $objSheet->getCell('J' . ($pos + 3))->setValue($tallos_cosechados);
                $objSheet->getCell('K' . ($pos + 3))->setValue($tallos_m2_cos);
                $objSheet->getCell('L' . ($pos + 3))->setValue($item->porciento_cosechado . '%');
                $color = 'EF6E11';
                if ($tallos_m2_proy < 35)
                    $color = 'D01C62';
                if ($tallos_m2_proy > 45)
                    $color = '00B388';
                $objSheet->getCell('M' . ($pos + 3))->setValue($tallos_m2_proy);
                $objSheet->getStyle('M' . ($pos + 3))
                    ->getFont()
                    ->getColor()
                    ->setRGB($color);
                $objSheet->getCell('N' . ($pos + 3))->setValue($item->plantas_iniciales);
                $objSheet->getCell('O' . ($pos + 3))->setValue($plantas_actuales);
                $objSheet->getCell('P' . ($pos + 3))->setValue($getDensidadIniciales);
                $objSheet->getCell('Q' . ($pos + 3))->setValue($conteo);

                $total_area += $item->area_m2;
                $total_iniciales += $item->plantas_iniciales;
                $total_actuales += $plantas_actuales;
                if ($item->plantas_iniciales > 0 && $plantas_actuales > 0) {
                    $total_mortalidad['valor'] += $mortalidad;
                    $total_mortalidad['positivos']++;
                }
                if ($item->plantas_iniciales > 0 && $item->area_m2 > 0) {
                    $total_densidad['valor'] += $getDensidadIniciales;
                    $total_densidad['positivos']++;
                }
                if ($item->area_m2 > 0 && $tallos_m2_proy > 0) {
                    $total_tallos_m2_proy['valor'] += $tallos_m2_proy;
                    $total_tallos_m2_proy['positivos']++;
                }
                $ciclo += difFechas($item->fecha_fin_ciclo, $item->fecha_inicio_ciclo)->days;
                $total_tallos += $tallos_cosechados;
                $total_tallos_m2 += $tallos_m2_cos;
                if ($tallos_cosechados > 0) {
                    $positivos_tallos_m2++;
                }
            }
            $objSheet->getCell('A' . ($pos + 4))->setValue('TOTALES');
            $objSheet->mergeCells('A' . ($pos + 4) . ':D' . ($pos + 4));
            $objSheet->getCell('E' . ($pos + 4))->setValue(count($query) > 0 ? round($ciclo / count($query), 2) : 0);
            $objSheet->getCell('F' . ($pos + 4))->setValue(round($total_area / 10000, 2));
            $objSheet->mergeCells('G' . ($pos + 4) . ':H' . ($pos + 4));
            $objSheet->getCell('I' . ($pos + 4))->setValue($total_mortalidad['positivos'] > 0 ? round($total_mortalidad['valor'] / $total_mortalidad['positivos'], 2) : 0);
            $objSheet->getCell('J' . ($pos + 4))->setValue($total_tallos);
            if ($positivos_tallos_m2 > 0)
                $real_tallos_total = count($ciclos) > 0 ? round($total_tallos_m2 / $positivos_tallos_m2, 2) : 0;
            else
                $real_tallos_total = 0;
            $objSheet->getCell('K' . ($pos + 4))->setValue($real_tallos_total);
            $objSheet->getCell('M' . ($pos + 4))->setValue($total_tallos_m2_proy['positivos'] > 0 ? round($total_tallos_m2_proy['valor'] / $total_tallos_m2_proy['positivos'], 2) : 0);
            $objSheet->getCell('N' . ($pos + 4))->setValue($total_iniciales);
            $objSheet->getCell('O' . ($pos + 4))->setValue($total_actuales);
            $objSheet->getCell('P' . ($pos + 4))->setValue($total_densidad['positivos'] > 0 ? round($total_densidad['valor'] / $total_densidad['positivos'], 2) : 0);

            $objSheet->getStyle('A' . ($pos + 4) . ':Q' . ($pos + 4))
                ->getBorders()
                ->getAllBorders()
                ->setBorderStyle(PHPExcel_Style_Border::BORDER_DOUBLE)
                ->getColor()
                ->setRGB(PHPExcel_Style_Color::COLOR_BLACK);

            $objSheet->getStyle('A' . ($pos + 4) . ':Q' . ($pos + 4))
                ->getFill()
                ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                ->getStartColor()
                ->setRGB('CCFFCC');


            $objSheet->getColumnDimension('A')->setAutoSize(true);
            $objSheet->getColumnDimension('B')->setAutoSize(true);
            $objSheet->getColumnDimension('C')->setAutoSize(true);
            $objSheet->getColumnDimension('D')->setAutoSize(true);
            $objSheet->getColumnDimension('E')->setAutoSize(true);
            $objSheet->getColumnDimension('F')->setAutoSize(true);
            $objSheet->getColumnDimension('G')->setAutoSize(true);
            $objSheet->getColumnDimension('H')->setAutoSize(true);
            $objSheet->getColumnDimension('I')->setAutoSize(true);
            $objSheet->getColumnDimension('J')->setAutoSize(true);
            $objSheet->getColumnDimension('K')->setAutoSize(true);
            $objSheet->getColumnDimension('L')->setAutoSize(true);
            $objSheet->getColumnDimension('M')->setAutoSize(true);
            $objSheet->getColumnDimension('N')->setAutoSize(true);
            $objSheet->getColumnDimension('O')->setAutoSize(true);
            $objSheet->getColumnDimension('P')->setAutoSize(true);
            $objSheet->getColumnDimension('Q')->setAutoSize(true);
        } else {
            dd('No se han encontrado coincidencias');
        }
    }
}
