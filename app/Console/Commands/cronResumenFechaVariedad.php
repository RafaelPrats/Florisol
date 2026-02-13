<?php

namespace yura\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use yura\Modelos\DetalleImportPedido;
use yura\Modelos\ResumenFechas;

class cronResumenFechaVariedad extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resumen:fecha {fecha=0} {variedad=0} {dev=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comando para calcular los valores resumidos de Agrogana por fecha/variedad';

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
        $dev = $this->argument('dev');
        if ($dev == 0) {
            dump('<<<<< ! >>>>> Ejecutando comando "resumen:fecha" <<<<< ! >>>>>');
            Log::info('<<<<< ! >>>>> Ejecutando comando "resumen:fecha" <<<<< ! >>>>>');
        }
        $fecha = $this->argument('fecha');
        $variedad = $this->argument('variedad');

        $comprados = DB::table('desglose_compra_flor')
            ->select(
                DB::raw('sum(tallos_x_malla * cantidad_mallas) as cantidad'),
            )
            ->where('id_variedad', $variedad)
            ->where('fecha', $fecha)
            ->where('estado', 1)
            ->get()[0]->cantidad;

        $desechados = DB::table('salidas_recepcion')
            ->select(
                DB::raw('sum(cantidad) as cantidad'),
            )
            ->where('id_variedad', $variedad)
            ->where('fecha', $fecha)
            ->where('basura', 1)
            ->get()[0]->cantidad;

        $recibidos = DB::table('desglose_recepcion')
            ->select(
                DB::raw('sum(tallos_x_malla * cantidad_mallas) as cantidad'),
            )
            ->where('id_variedad', $variedad)
            ->where('fecha', $fecha)
            ->where('estado', 1)
            ->get()[0]->cantidad;

        /* TALLOS_VENTA y TALLOS_ARMADOS */
        $detalle_pedidos = DB::table('detalle_import_pedido as d')
            ->join('import_pedido as ped', 'ped.id_import_pedido', '=', 'd.id_import_pedido')
            ->join('distribucion_receta as r', 'r.id_detalle_import_pedido', '=', 'd.id_detalle_import_pedido')
            ->select(
                'd.id_detalle_import_pedido',
            )->distinct()
            ->where('ped.estado', 1)
            ->where('ped.fecha', $fecha)
            ->where('r.id_variedad', $variedad)
            ->get();
        $ids_detalle_pedidos = $detalle_pedidos->pluck('id_detalle_import_pedido')->toArray();
        $detalle_pedidos_ot = DB::table('detalle_import_pedido as d')
            ->join('import_pedido as ped', 'ped.id_import_pedido', '=', 'd.id_import_pedido')
            ->join('orden_trabajo as o', 'o.id_detalle_import_pedido', '=', 'd.id_detalle_import_pedido')
            ->join('detalle_orden_trabajo as do', 'do.id_orden_trabajo', '=', 'o.id_orden_trabajo')
            ->select(
                'd.id_detalle_import_pedido',
            )->distinct()
            ->where('ped.estado', 1)
            ->where('ped.fecha', $fecha)
            ->where('do.id_variedad', $variedad)
            ->whereNotIn('d.id_detalle_import_pedido', $ids_detalle_pedidos)
            ->get();
        $ids_detalle_pedidos_ot = $detalle_pedidos_ot->pluck('id_detalle_import_pedido')->toArray();
        $detalle_pedidos_pre_ot = DB::table('detalle_import_pedido as d')
            ->join('import_pedido as ped', 'ped.id_import_pedido', '=', 'd.id_import_pedido')
            ->join('pre_orden_trabajo as po', 'po.id_detalle_import_pedido', '=', 'd.id_detalle_import_pedido')
            ->join('detalle_pre_orden_trabajo as pdo', 'pdo.id_pre_orden_trabajo', '=', 'po.id_pre_orden_trabajo')
            ->select(
                'd.id_detalle_import_pedido',
            )->distinct()
            ->where('po.estado', 1)
            ->where('ped.estado', 1)
            ->where('ped.fecha', $fecha)
            ->where('pdo.id_variedad', $variedad)
            ->whereNotIn('d.id_detalle_import_pedido', $ids_detalle_pedidos)
            ->whereNotIn('d.id_detalle_import_pedido', $ids_detalle_pedidos_ot)
            ->get();

        $detalle_pedidos = $detalle_pedidos->merge($detalle_pedidos_ot)->merge($detalle_pedidos_pre_ot);

        $tallos_venta = 0;
        foreach ($detalle_pedidos as $det_ped) {
            $det_ped = DetalleImportPedido::find($det_ped->id_detalle_import_pedido);
            $ramos_venta = $det_ped->ramos; // ramos totales del pedido
            $ramos_procesados = 0;  // ramos procesados mediante OT y Pre-OT

            $list_ot = DB::table('orden_trabajo as o')
                ->join('detalle_orden_trabajo as do', 'do.id_orden_trabajo', '=', 'o.id_orden_trabajo')
                ->select(
                    'o.id_orden_trabajo',
                    'o.ramos',
                    DB::raw('sum(do.tallos) as tallos'),
                )
                ->where('do.id_variedad', $variedad)
                ->where('o.id_detalle_import_pedido', $det_ped->id_detalle_import_pedido)
                ->groupBy(
                    'o.id_orden_trabajo',
                    'o.ramos'
                )
                ->get();
            $query_ot = DB::table('orden_trabajo as o')
                ->select(
                    'o.ramos',
                )
                ->where('o.id_detalle_import_pedido', $det_ped->id_detalle_import_pedido)
                ->get();
            foreach ($list_ot as $ot) {
                $tallos_venta += $ot->tallos;
            }
            foreach ($query_ot as $ot) {
                $ramos_procesados += $ot->ramos;
            }

            $list_pre_ot = DB::table('pre_orden_trabajo as o')
                ->join('detalle_pre_orden_trabajo as do', 'do.id_pre_orden_trabajo', '=', 'o.id_pre_orden_trabajo')
                ->select(
                    'o.id_pre_orden_trabajo',
                    'o.ramos',
                    DB::raw('sum(o.ramos * do.tallos) as tallos'),
                )
                ->where('o.estado', 1)
                ->where('do.id_variedad', $variedad)
                ->where('o.id_detalle_import_pedido', $det_ped->id_detalle_import_pedido)
                ->groupBy(
                    'o.id_pre_orden_trabajo',
                    'o.ramos'
                )
                ->get();
            $query_pre_ot = DB::table('pre_orden_trabajo as o')
                ->select(
                    'o.ramos',
                )
                ->where('o.estado', 1)
                ->where('o.id_detalle_import_pedido', $det_ped->id_detalle_import_pedido)
                ->get();
            foreach ($list_pre_ot as $pre_ot) {
                $tallos_venta += $pre_ot->tallos;
            }
            foreach ($query_pre_ot as $pre_ot) {
                $ramos_procesados += $pre_ot->ramos;
            }

            if ($ramos_venta > $ramos_procesados) {   // aun quedan ramos del pedido por procesar
                $diferencia = $ramos_venta - $ramos_procesados;
                $unidades = DB::table('detalle_import_pedido as d')
                    ->join('distribucion_receta as r', 'r.id_detalle_import_pedido', '=', 'd.id_detalle_import_pedido')
                    ->select(DB::raw('sum(r.unidades) as cantidad'))
                    ->where('r.id_variedad', $variedad)
                    ->where('d.id_detalle_import_pedido', $det_ped->id_detalle_import_pedido)
                    ->get()[0]->cantidad;
                $tallos_venta += $diferencia * $unidades;
            }
        }

        $armados = DB::table('orden_trabajo as o')
            ->join('detalle_orden_trabajo as do', 'do.id_orden_trabajo', '=', 'o.id_orden_trabajo')
            ->join('detalle_import_pedido as d', 'd.id_detalle_import_pedido', '=', 'o.id_detalle_import_pedido')
            ->join('import_pedido as p', 'p.id_import_pedido', '=', 'd.id_import_pedido')
            ->select(
                DB::raw('sum(do.tallos) as cantidad'),
            )
            ->where('do.id_variedad', $variedad)
            ->where('o.armado', 1)
            ->where('p.fecha', $fecha)
            ->where('p.estado', 1)
            ->get()[0]->cantidad;
        $armados += DB::table('detalle_import_pedido as d')
            ->join('import_pedido as p', 'p.id_import_pedido', '=', 'd.id_import_pedido')
            ->join('distribucion_receta as r', 'r.id_detalle_import_pedido', '=', 'd.id_detalle_import_pedido')
            ->select(
                DB::raw('sum(d.ramos_armados * r.unidades) as cantidad'),
            )
            ->where('r.id_variedad', $variedad)
            ->where('p.fecha', $fecha)
            ->where('p.estado', 1)
            ->get()[0]->cantidad;

        $model = ResumenFechas::where('id_variedad', $variedad)
            ->where('fecha', $fecha)
            ->get()
            ->first();
        if ($model == '') {
            $model = new ResumenFechas();
            $model->id_variedad = $variedad;
            $model->fecha = $fecha;
            $model->semana = getSemanaByDate($fecha)->codigo;
            $model->mes = substr($fecha, 5, 2);
            $model->anno = substr($fecha, 0, 4);
            $model->tallos_comprados = $comprados;
            $model->tallos_desechados = $desechados;
            $model->tallos_recibidos = $recibidos;
            $model->tallos_venta = $tallos_venta;
            $model->tallos_armados = $armados;
            $model->save();
        } else {
            $model->tallos_comprados = $comprados;
            $model->tallos_desechados = $desechados;
            $model->tallos_recibidos = $recibidos;
            $model->tallos_venta = $tallos_venta;
            $model->tallos_armados = $armados;
            $model->last_update = date('Y-m-d H:i:s');
            $model->save();
        }

        $time_duration = difFechas(date('Y-m-d H:i:s'), $ini)->h . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->m . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->s;
        if ($dev == 0) {
            dump('<*> DURACION: ' . $time_duration . '  <*>');
            dump('<<<<< * >>>>> Fin satisfactorio del comando "resumen:fecha" <<<<< * >>>>>');
            Log::info('<*> DURACION: ' . $time_duration . '  <*>');
            Log::info('<<<<< * >>>>> Fin satisfactorio del comando "resumen:fecha" <<<<< * >>>>>');
        }
    }
}
