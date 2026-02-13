<?php

namespace yura\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use yura\Modelos\AgenciaCarga;
use yura\Modelos\Caja;
use yura\Modelos\DetallePedido;
use yura\Modelos\Exportador;
use yura\Modelos\InventarioFrio;
use yura\Modelos\Pedido;
use yura\Modelos\Submenu;

class PedidoController extends Controller
{
    public function inicio(Request $request)
    {
        return view('adminlte.gestion.pedidos.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'text' => ['titulo' => 'Pedidos', 'subtitulo' => 'módulo de pedidos'],
            'clientes' => DB::table('cliente as c')
                ->join('detalle_cliente as dc', 'c.id_cliente', '=', 'dc.id_cliente')
                ->orderBy('nombre','asc')
                ->where('dc.estado', 1)->get(),
            'annos' => DB::table('pedido as p')->select(DB::raw('YEAR(p.fecha_pedido) as anno'))
                ->distinct()->get(),
            'empresas' => getConfiguracionEmpresa(null,true)
        ]);
    }

    public function crearPedido(Request $request)
    {
        $clientes = DB::table('cliente as c')
        ->join('detalle_cliente as dc', 'c.id_cliente', '=', 'dc.id_cliente')
        ->orderBy('nombre','asc') ->where('dc.estado', 1)->get();

        $exportadores = Exportador::where('estado',true)->get();

        return view('adminlte.gestion.pedidos.partials.crear_pedido',[
            'clientes' => $clientes,
            'exportadores' => $exportadores,
            'empresas' => getConfiguracionEmpresa(null,true)
        ]);
    }

    public function obetenerInventarioPLanta(Request $request)
    {
        $invFrio = InventarioFrio::join('variedad as v',function($j){
            $j->on('inventario_frio.id_variedad','v.id_variedad')
            ->whereIn('v.id_variedad', [DB::raw("SELECT DISTINCT id_variedad FROM variedad WHERE estado = true")]);
        })->join('planta as p','v.id_planta','p.id_planta')
        ->where([
            ['inventario_frio.id_empresa', $request->id_configuracion_empresa],
            ['basura', 0],
            ['disponibilidad', 1]
        ])->select(
            DB::raw("MAX(p.nombre) as planta"),
            DB::raw("SUM(inventario_frio.disponibles) as disponibles"),
            'v.id_planta'
        )->orderBy(DB::raw("MAX(p.nombre)"),'asc')->groupBy('v.id_planta')->get();

        return view('adminlte.gestion.pedidos.partials.inventario_frio_planta',[
            'invFrio' => $invFrio
        ]);
    }

    public function obetenerInventarioPlantaVariedad(Request $request)
    {
        $variedades = DB::table('variedad')->where([
            ['id_planta',$request->id_planta],
            ['estado',true]
        ])->get();

        $invFrio = InventarioFrio::join('variedad as v',function($j) use ($variedades) {
            $j->on('inventario_frio.id_variedad','v.id_variedad')
            ->whereIn('v.id_variedad', $variedades->pluck('id_variedad'));
        })->join('clasificacion_ramo as cr','inventario_frio.id_clasificacion_ramo','cr.id_clasificacion_ramo')
        ->join('unidad_medida as um','cr.id_unidad_medida','um.id_unidad_medida')
        ->join('empaque as emp', 'inventario_frio.id_empaque', 'emp.id_empaque')
        ->join('unidad_medida as um2','inventario_frio.id_unidad_medida','um2.id_unidad_medida')
        ->where([
            ['inventario_frio.id_empresa', $request->id_configuracion_empresa],
            ['basura', 0],
            ['disponibilidad', 1]
        ])->select(
            'v.nombre as variedad','emp.nombre as presentacion','v.siglas',
            'cr.id_clasificacion_ramo','v.id_variedad','emp.id_empaque',
            'tallos_x_ramo','disponibles','inventario_frio.id_inventario_frio',
            DB::raw("CONCAT(inventario_frio.longitud_ramo,' ',um2.siglas) as longitud"),
            DB::raw("CONCAT(cr.nombre,' ',um.siglas) as peso"),
            DB::raw("CONCAT(DATEDIFF(NOW(),inventario_frio.fecha),' ','días') as edad")
        )->orderBy('fecha', 'asc')->get();

        return view('adminlte.gestion.pedidos.partials.inventario_frio_planta_variedad',[
            'invFrio' => $invFrio
        ]);
    }

    public function obtenerDataPedido(Request $request)
    {
        return [
            'cajas' => Caja::where('estado',true)->get(),
            'agencias_carga' => AgenciaCarga::where('estado',true)->get(),
        ];
    }

    public function storePedido(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'id_cliente' => 'required|exists:cliente,id_cliente',
            'id_exportador' => 'required|exists:exportador,id_exportador',
            'fecha' => 'required|date',
            'data_pedido' => ['required','array',function($attribute,$value,$onFailure){

                foreach($value as $i => $v){

                    if(!isset($v['id_variedad'])){
                        $onFailure('No se obtuvo la variedad de la fila '.($i+1). ' del pedido');
                    }else if(!isset($v['id_clasificacion_ramo'])){
                        $onFailure('No se obtuvo el peso de la fila '.($i+1). ' del pedido');
                    }else if(!isset($v['id_empaque'])){
                        $onFailure('No se obtuvo la presentación de la fila '.($i+1). ' del pedido');
                    }else if(!isset($v['tallos_x_ramo'])){
                        $onFailure('No se obtuvieron los tallos por ramo de la fila '.($i+1). ' del pedido');
                    }else if(!isset($v['longitud'])){
                        $onFailure('No se obtuvo la longitud de la fila '.($i+1). ' del pedido');
                    }else if(!isset($v['cantidad'])){
                        $onFailure('No se obtuvo la cantida de la fila '.($i+1). ' del pedido');
                    }else if(!isset($v['id_caja'])){
                        $onFailure('No se obtuvo la caja de la fila '.($i+1). ' del pedido');
                    }else if(!isset($v['ramos_x_caja'])){
                        $onFailure('No se obtuvo los ramos por caja de la fila '.($i+1). ' del pedido');
                    }else if(!isset($v['precio'])){
                        $onFailure('No se obtuvo el precio de la fila '.($i+1). ' del pedido');
                    }else if(!isset($v['id_agencia_carga'])){
                        $onFailure('No se obtuvo la agencia de carga de la fila '.($i+1). ' del pedido');
                    }else if(!isset($v['id_inventario_frio'])){
                        $onFailure('No se obtuvo el inventario de la fila '.($i+1). ' del pedido');
                    }else{

                        $inventario = InventarioFrio::find($v['id_inventario_frio']);

                        if($inventario->disponibles < $v['cantidad'])
                            $onFailure('No hay suficientes productos en el inventario para depachar la fila '.($i+1). ' del pedido');

                    }

                }

            }],
        ],[
            'id_cliente.required' => 'Debe seleccionar un cliente',
            'id_cliente.exists' => 'El cliente seleccionado no existe',
            'id_exportador.required' => 'Debe seleccionar un exportador',
            'id_exportador.exists' => 'El exportador seleccionado no existe',
            'fecha.required' => 'Debe seleccionar una fecha',
            'fecha.date' => 'La fecha seleccionada no es válida',
            'data_pedido.required' => 'Debe seleccionar al menos un item del inventario',
            'data_pedido.array' => 'El inventario seleccionado debe ser una coleción de datos'
        ]);

        DB::beginTransaction();

        if (!$valida->fails()) {
            //dd($request->all());

            try{

                Pedido::updateOrCreate(
                    [
                        'id_cliente' => $request->id_cliente,
                        'id_exportador' => $request->id_exportador,
                        'fecha_pedido' => $request->fecha,
                        'id_configuracion_empresa' => getFincaActiva(),
                    ],
                    ['id_pedido' => isset($request->id_pedido) ? $request->id_pedido : 0]
                );

                if(isset($request->id_pedido)){

                    $idPedido = $request->id_pedido;
                    $idsOldDetallePedido = DetallePedido::where('id_pedido',$idPedido)->get()->pluck('id_detalle_pedido')->toArray();
                    DetallePedido::whereIn('id_detalle_pedido',$idsOldDetallePedido)->delete();

                }else{

                    $idPedido= Pedido::orderBy('id_pedido','desc')->first()->id_pedido;

                }

                foreach($request->data_pedido as $dp){

                    DetallePedido::create([
                        'id_pedido' => $idPedido,
                        'id_agencia_carga' => $dp['id_agencia_carga'],
                        'cantidad' => $dp['cantidad'],
                        'precio' => $dp['precio'],
                        'id_variedad' => $dp['id_variedad'],
                        'id_clasificacion_ramo' => $dp['id_clasificacion_ramo'],
                        'tallos_x_ramo' => $dp['tallos_x_ramo'],
                        'longitud' => $dp['longitud'],
                        'id_empaque' => $dp['id_empaque'],
                        'ramos_x_caja' => $dp['ramos_x_caja'],
                        'id_caja' => $dp['id_caja'],
                        'id_inventario_frio' => $dp['id_inventario_frio']
                    ]);

                    $invfrio = InventarioFrio::find($dp['id_inventario_frio']);
                    $invfrio->disponibles-= $dp['cantidad'];
                    $invfrio->disponibles <= 0 && $invfrio->disponibilidad=false;
                    $invfrio->save();

                }

                DB::commit();
                $success = true;
                $msg = '<div class="alert alert-success text-center">' .
                            '<p> Se ha guardado el registro correctamente </p>
                        </div>';

            }catch(\Exception $e){

                DB::rollBack();
                $success = false;
                $msg = '<div class="alert alert-warning text-center">' .
                            '<p> Ha ocurrido un problema al guardar la información al sistema </p>
                            <p><strong>Error:</strong> ' . $e->getMessage() . 'en la línea '.$e->getLine().' del archivo '.$e->getFile().'</p>'
                        . '</div>';
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


}
