<?php

namespace yura\Http\Controllers\Postco;

use DB;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use yura\Http\Controllers\Controller;
use yura\Modelos\Planta;
use yura\Modelos\Submenu;

class MovimientosRecepcionController extends Controller
{
    public function inicio(Request $request)
    {
        $finca = getFincaActiva();
        $plantas = Planta::where('estado', 1)
            ->where('id_empresa', $finca)
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.postco.movimientos_recepcion.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'plantas' => $plantas,
        ]);
    }

    public function listar_reporte(Request $request)
    {
        $finca = getFincaActiva();
        $ingresos = DB::table('inventario_recepcion as i')
            ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->select(
                'i.id_variedad',
                'v.nombre as var_nombre',
                'v.id_planta',
                'p.nombre as pta_nombre',
                'i.fecha',
                DB::raw('sum(i.ramos * i.tallos_x_ramo) as tallos_ingresados'),
                DB::raw('sum(i.disponibles) as tallos_disponibles'),
            )
            ->where('i.id_empresa', $finca)
            ->where('i.bodega', $request->bodega)
            ->where('i.fecha', '>=', $request->desde)
            ->where('i.fecha', '<=', $request->hasta);
        if ($request->planta != '')
            $ingresos = $ingresos->where('v.id_planta', $request->planta);
        if ($request->variedad != '')
            $ingresos = $ingresos->where('i.id_variedad', $request->variedad);
        $ingresos = $ingresos->groupBy(
            'i.id_variedad',
            'v.nombre',
            'v.id_planta',
            'p.nombre',
            'i.fecha'
        )
            ->orderBy('i.fecha')
            ->orderBy('p.nombre')
            ->orderBy('v.nombre')
            ->get();

        $salidas = DB::table('salidas_recepcion as s')
            ->join('inventario_recepcion as i', 'i.id_inventario_recepcion', '=', 's.id_inventario_recepcion')
            ->join('variedad as v', 'v.id_variedad', '=', 's.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->leftJoin('orden_trabajo as o', function ($join) {
                $join->on('o.id_orden_trabajo', '=', 's.id_orden_trabajo')
                    ->join('detalle_cliente as cli', 'cli.id_cliente', '=', 'o.id_cliente')
                    ->join('detalle_caja_proyecto as dc', 'dc.id_detalle_caja_proyecto', '=', 'o.id_detalle_caja_proyecto')
                    ->join('caja_proyecto as cp', 'cp.id_caja_proyecto', '=', 'dc.id_caja_proyecto')
                    ->join('proyecto as pr', 'pr.id_proyecto', '=', 'cp.id_proyecto')
                    ->where('cli.estado', 1);
            })
            ->leftJoin('detalle_caja_proyecto as dcp', function ($join) {
                $join->on('dcp.id_detalle_caja_proyecto', '=', 's.id_detalle_caja_proyecto')
                    ->join('caja_proyecto as cpr', 'cpr.id_caja_proyecto', '=', 'dcp.id_caja_proyecto')
                    ->join('proyecto as proy', 'proy.id_proyecto', '=', 'cpr.id_proyecto')
                    ->join('detalle_cliente as cli_proy', 'cli_proy.id_cliente', '=', 'proy.id_cliente')
                    ->where('cli_proy.estado', 1);
            })
            ->leftJoin('motivo_baja as mb', function ($join) {
                $join->on('mb.id_motivo_baja', '=', 's.id_motivo_baja');
            })
            ->select(
                's.*',
                'v.nombre as var_nombre',
                'v.id_planta',
                'p.nombre as pta_nombre',
                'o.ramos as ramos_ot',
                'o.id_cliente',
                'cli.nombre as cli_nombre_ot',
                'pr.segmento as segmento_ot',
                'proy.segmento as segmento_proy',
                'cli_proy.nombre as cli_nombre_proy',
                'mb.nombre as motivo_nombre',
            )->distinct()
            ->where('i.id_empresa', $finca)
            ->where('i.bodega', $request->bodega)
            ->where('s.fecha', '>=', $request->desde)
            ->where('s.fecha', '<=', $request->hasta);
        if ($request->planta != '')
            $salidas = $salidas->where('v.id_planta', $request->planta);
        if ($request->variedad != '')
            $salidas = $salidas->where('s.id_variedad', $request->variedad);
        $salidas = $salidas->orderBy('s.fecha')
            ->orderBy('p.nombre')
            ->orderBy('v.nombre')
            ->get();

        $listado = collect();

        foreach ($ingresos as $ing) {
            $listado->push((object)[
                'tipo' => 'INGRESO',
                'fecha' => $ing->fecha,
                'id_variedad' => $ing->id_variedad,
                'var_nombre' => $ing->var_nombre,
                'id_planta' => $ing->id_planta,
                'pta_nombre' => $ing->pta_nombre,
                'data' => $ing,
            ]);
        }

        foreach ($salidas as $sal) {
            $listado->push((object)[
                'tipo' => 'SALIDA',
                'fecha' => $sal->fecha,
                'id_variedad' => $sal->id_variedad,
                'var_nombre' => $sal->var_nombre,
                'id_planta' => $sal->id_planta,
                'pta_nombre' => $sal->pta_nombre,
                'data' => $sal,
            ]);
        }

        $listado = $listado
            ->sortBy(function ($item) {
                return sprintf(
                    '%s|%s|%s',
                    $item->fecha,
                    $item->pta_nombre,
                    $item->var_nombre
                );
            })
            ->values();

        return view('adminlte.gestion.postco.movimientos_recepcion.partials.listado', [
            'listado' => $listado
        ]);
    }

    public function exportar_reporte(Request $request)
    {
        $spread = new Spreadsheet();
        $this->excel_reporte($spread, $request);
        $fileName = "Movimientos.xlsx";
        $writer = new Xlsx($spread);

        //--------------------------- GUARDAR EL EXCEL -----------------------

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        $writer->save('php://output');

        //$writer->save('/var/www/html/Dasalflor/storage/storage/excel/excel_prueba.xlsx');
    }

    public function excel_reporte($spread, $request)
    {
        $finca = getFincaActiva();
        $ingresos = DB::table('inventario_recepcion as i')
            ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->select(
                'i.id_variedad',
                'v.nombre as var_nombre',
                'v.id_planta',
                'p.nombre as pta_nombre',
                'i.fecha',
                DB::raw('sum(i.ramos * i.tallos_x_ramo) as tallos_ingresados'),
                DB::raw('sum(i.disponibles) as tallos_disponibles'),
            )
            ->where('i.id_empresa', $finca)
            ->where('i.bodega', $request->bodega)
            ->where('i.fecha', '>=', $request->desde)
            ->where('i.fecha', '<=', $request->hasta);
        if ($request->planta != '')
            $ingresos = $ingresos->where('v.id_planta', $request->planta);
        if ($request->variedad != '')
            $ingresos = $ingresos->where('i.id_variedad', $request->variedad);
        $ingresos = $ingresos->groupBy(
            'i.id_variedad',
            'v.nombre',
            'v.id_planta',
            'p.nombre',
            'i.fecha'
        )
            ->orderBy('i.fecha')
            ->orderBy('p.nombre')
            ->orderBy('v.nombre')
            ->get();

        $salidas = DB::table('salidas_recepcion as s')
            ->join('inventario_recepcion as i', 'i.id_inventario_recepcion', '=', 's.id_inventario_recepcion')
            ->join('variedad as v', 'v.id_variedad', '=', 's.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->leftJoin('orden_trabajo as o', function ($join) {
                $join->on('o.id_orden_trabajo', '=', 's.id_orden_trabajo')
                    ->join('detalle_cliente as cli', 'cli.id_cliente', '=', 'o.id_cliente')
                    ->join('detalle_caja_proyecto as dc', 'dc.id_detalle_caja_proyecto', '=', 'o.id_detalle_caja_proyecto')
                    ->join('caja_proyecto as cp', 'cp.id_caja_proyecto', '=', 'dc.id_caja_proyecto')
                    ->join('proyecto as pr', 'pr.id_proyecto', '=', 'cp.id_proyecto')
                    ->where('cli.estado', 1);
            })
            ->leftJoin('detalle_caja_proyecto as dcp', function ($join) {
                $join->on('dcp.id_detalle_caja_proyecto', '=', 's.id_detalle_caja_proyecto')
                    ->join('caja_proyecto as cpr', 'cpr.id_caja_proyecto', '=', 'dcp.id_caja_proyecto')
                    ->join('proyecto as proy', 'proy.id_proyecto', '=', 'cpr.id_proyecto')
                    ->join('detalle_cliente as cli_proy', 'cli_proy.id_cliente', '=', 'proy.id_cliente')
                    ->where('cli_proy.estado', 1);
            })
            ->leftJoin('motivo_baja as mb', function ($join) {
                $join->on('mb.id_motivo_baja', '=', 's.id_motivo_baja');
            })
            ->select(
                's.*',
                'v.nombre as var_nombre',
                'v.id_planta',
                'p.nombre as pta_nombre',
                'o.ramos as ramos_ot',
                'o.id_cliente',
                'cli.nombre as cli_nombre_ot',
                'pr.segmento as segmento_ot',
                'proy.segmento as segmento_proy',
                'cli_proy.nombre as cli_nombre_proy',
                'mb.nombre as motivo_nombre',
            )->distinct()
            ->where('i.id_empresa', $finca)
            ->where('i.bodega', $request->bodega)
            ->where('s.fecha', '>=', $request->desde)
            ->where('s.fecha', '<=', $request->hasta);
        if ($request->planta != '')
            $salidas = $salidas->where('v.id_planta', $request->planta);
        if ($request->variedad != '')
            $salidas = $salidas->where('s.id_variedad', $request->variedad);
        $salidas = $salidas->orderBy('s.fecha')
            ->orderBy('p.nombre')
            ->orderBy('v.nombre')
            ->get();

        $listado = collect();

        foreach ($ingresos as $ing) {
            $listado->push((object)[
                'tipo' => 'INGRESO',
                'fecha' => $ing->fecha,
                'id_variedad' => $ing->id_variedad,
                'var_nombre' => $ing->var_nombre,
                'id_planta' => $ing->id_planta,
                'pta_nombre' => $ing->pta_nombre,
                'data' => $ing,
            ]);
        }

        foreach ($salidas as $sal) {
            $listado->push((object)[
                'tipo' => 'SALIDA',
                'fecha' => $sal->fecha,
                'id_variedad' => $sal->id_variedad,
                'var_nombre' => $sal->var_nombre,
                'id_planta' => $sal->id_planta,
                'pta_nombre' => $sal->pta_nombre,
                'data' => $sal,
            ]);
        }

        $listado = $listado
            ->sortBy(function ($item) {
                return sprintf(
                    '%s|%s|%s',
                    $item->fecha,
                    $item->pta_nombre,
                    $item->var_nombre
                );
            })
            ->values();

        $columnas = getColumnasExcel();
        $sheet = $spread->getActiveSheet();
        $sheet->setTitle('MOVIMIENTOS');

        $row = 1;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TIPO');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'FECHA');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'PLANTA');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'VARIEDAD');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TALLOS');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'BASURA');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'OT (RECETA)');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'RAMOS SOLIDOS');
        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        foreach ($listado as $pos => $item) {
            $row++;
            $col = 0;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->tipo);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->fecha);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->pta_nombre);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->var_nombre);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->tipo == 'INGRESO' ? $item->data->tallos_ingresados : $item->data->cantidad);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->tipo == 'SALIDA' ? $item->data->basura : '');
            $col++;
            if ($item->tipo == 'SALIDA' && $item->data->id_orden_trabajo != '')
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, '#' . $item->data->id_orden_trabajo . ' ' . $item->data->cli_nombre_ot . ' ' . $item->data->segmento_ot);
            $col++;
            if ($item->tipo == 'SALIDA')
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item->data->cli_nombre_proy . ' ' . $item->data->segmento_proy);
        }

        setTextCenterToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);
        setBorderToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);

        for ($i = 0; $i <= $col; $i++)
            $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);
    }
}
