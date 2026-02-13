<?php

namespace yura\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use yura\Modelos\Variedad;
use yura\Modelos\ResumenAgrogana;
use DB;

class cronUpdateResumen extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resumen:agrogana {fecha=0} {variedad=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comando para actualizar la tabla resumen_agrogana';

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
        $fecha = $this->argument('fecha');
        $variedad = $this->argument('variedad');

        if($fecha == 0 || $fecha >= '2025-04-08'){  // funciona a partir de la actualizacion del 8 de abril del 2025
            if($fecha == 0){
                $fechas = DB::table('postco')
                    ->select('fecha')->distinct()
                    //->where('fecha', '>=', '2025-04-08')
                    ->where('fecha', '>=', hoy())
                    ->orderBy('fecha', 'asc')
                    ->get()->pluck('fecha')->toArray();
            } else {
                $fechas = [$fecha];
            }

            foreach($fechas as $pos_f => $fecha){
                $variedades = DB::table('postco as h')
                    ->join('distribucion_postco as r', 'r.id_postco', '=', 'h.id_postco')
                    ->join('variedad as v', 'v.id_variedad', '=', 'r.id_item')
                    ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                    ->select(
                        'v.id_planta',
                        'r.id_item as id_variedad',
                        'v.nombre as variedad_nombre',
                        'p.nombre as planta_nombre',
                    )->distinct()
                    ->where('h.fecha', $fecha);
                if($variedad != 0)
                    $variedades = $variedades->where('r.id_item', $variedad);
                $variedades = $variedades->orderBy('p.nombre')
                    ->orderBy('v.nombre')
                    ->get();
                $ids_variedad = $variedades->pluck('id_variedad')->toArray();
                $variedades_ot = DB::table('postco as h')
                    ->join('ot_postco as o', 'o.id_postco', '=', 'h.id_postco')
                    ->join('detalle_ot_postco as r', 'r.id_ot_postco', '=', 'o.id_ot_postco')
                    ->join('variedad as v', 'v.id_variedad', '=', 'r.id_item')
                    ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                    ->select(
                        'v.id_planta',
                        'r.id_item as id_variedad',
                        'v.nombre as variedad_nombre',
                        'p.nombre as planta_nombre',
                    )->distinct()
                    ->where('h.fecha', $fecha)
                    ->whereNotIn('r.id_item', $ids_variedad);
                if($variedad != 0)
                    $variedades_ot = $variedades_ot->where('r.id_item', $variedad);
                $variedades_ot = $variedades_ot->orderBy('p.nombre')
                    ->orderBy('v.nombre')
                    ->get();
                $ids_variedad = $variedades_ot->pluck('id_variedad')->toArray();
                $variedades = $variedades->merge($variedades_ot);

                if($variedad == 0){ // todas las variedades
                    DB::select('update resumen_agrogana set tallos_venta = 0 where fecha = "' . $fecha . '"');
                    DB::select('update resumen_agrogana set tallos_armados = 0 where fecha = "' . $fecha . '"');
                }
                foreach($variedades as $pos_var => $var){
                    DB::select('update resumen_agrogana set tallos_venta = 0 where fecha = "' . $fecha . '" and id_variedad = '.$var->id_variedad);
                    DB::select('update resumen_agrogana set tallos_armados = 0 where fecha = "' . $fecha . '" and id_variedad = '.$var->id_variedad);

                    $clientes_postco = DB::table('postco_clientes as pc')
                        ->join('postco as p', 'p.id_postco', '=', 'pc.id_postco')
                        ->join('distribucion_postco as r', 'r.id_postco', '=', 'p.id_postco')
                        ->select('pc.id_cliente')->distinct()
                        ->where('p.fecha', $fecha)
                        ->where('r.id_item', $var->id_variedad)
                        ->get();
                    $ids_clientes_postco = $clientes_postco->pluck('id_cliente')->toArray();
                    $clientes_ot = DB::table('postco as p')
                        ->join('ot_postco as o', 'o.id_postco', '=', 'p.id_postco')
                        ->join('detalle_ot_postco as r', 'r.id_ot_postco', '=', 'o.id_ot_postco')
                        ->select('o.id_cliente')->distinct()
                        ->where('p.fecha', $fecha)
                        ->where('r.id_item', $var->id_variedad)
                        ->whereNotIn('o.id_cliente', $ids_clientes_postco)
                        ->get();
                    $clientes = $clientes_postco->merge($clientes_ot);
                    foreach($clientes as $pos_cli => $cli){
                        dump('fecha: '.$pos_f.'/'.count($fechas).'; var: '.$pos_var.'/'.count($variedades).'; cli: '.$pos_cli.'/'.count($clientes));
                        $tallos_venta = getTallosVentaByVariedadCliente($var->id_variedad, $fecha, $cli->id_cliente);

                        $armados = DB::table('ot_postco as o')
                            ->join('postco as p', 'p.id_postco', '=', 'o.id_postco')
                            ->join('detalle_ot_postco as do', 'do.id_ot_postco', '=', 'o.id_ot_postco')
                            ->select(
                                DB::raw('sum(do.unidades * o.ramos) as cantidad'),
                            )
                            ->where('do.id_item', $var->id_variedad)
                            ->where('o.id_cliente', $cli->id_cliente)
                            ->where('o.estado', 'A')
                            ->where('p.fecha', $fecha)
                            ->get()[0]->cantidad;
                        $armados += DB::table('postco as p')
                            ->join('armado_postco as a', 'a.id_postco', '=', 'p.id_postco')
                            ->join('detalle_armado_postco as r', 'r.id_armado_postco', '=', 'a.id_armado_postco')
                            ->select(
                                DB::raw('sum(a.ramos * r.unidades) as cantidad'),
                            )
                            ->where('a.id_cliente', $cli->id_cliente)
                            ->where('r.id_item', $var->id_variedad)
                            ->where('p.fecha', $fecha)
                            ->get()[0]->cantidad;

                        /* TABLA RESUMEN */
                        $model = ResumenAgrogana::where('id_variedad', $var->id_variedad)
                            ->where('fecha', $fecha)
                            ->where('id_cliente', $cli->id_cliente)
                            ->get()
                            ->first();
                        if ($model == '') {
                            $model = new ResumenAgrogana();
                            $model->id_variedad = $var->id_variedad;
                            $model->fecha = $fecha;
                            $model->id_cliente = $cli->id_cliente;
                            $model->semana = getSemanaByDate($fecha)->codigo;
                            $model->mes = substr($fecha, 5, 2);
                            $model->anno = substr($fecha, 0, 4);
                            $model->tallos_venta = $tallos_venta;
                            $model->tallos_armados = $armados;
                            $model->save();
                        } else {
                            $model->tallos_venta = $tallos_venta;
                            $model->tallos_armados = $armados;
                            $model->last_update = date('Y-m-d H:i:s');
                            $model->save();
                        }
                    }
                }
            }
        }

        $time_duration = difFechas(date('Y-m-d H:i:s'), $ini)->h . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->m . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->s;
        dump('<*> DURACION: ' . $time_duration . '  <*>');
        dump('<<<<< * >>>>> Fin satisfactorio del comando "resumen:agrogana" <<<<< * >>>>>');
        Log::info('<*> DURACION: ' . $time_duration . '  <*>');
        Log::info('<<<<< * >>>>> Fin satisfactorio del comando "resumen:agrogana" <<<<< * >>>>>');
    }
}
