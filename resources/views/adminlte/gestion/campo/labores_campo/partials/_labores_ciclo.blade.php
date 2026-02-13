@php
    $modulo = $ciclo->modulo;
@endphp
<legend class="text-center" style="font-size: 1em">{{$aplicacion->nombre}} aplicados al módulo: <strong>{{$modulo->nombre}}</strong></legend>

<div style="overflow-x: scroll; overflow-y: scroll; max-height: 450px">
    <table class="table-striped table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
        <tr>
            <th class="text-center th_yura_green">
                <div style="width: 50px">
                    Días
                </div>
            </th>
            <th class="text-center th_yura_green">
                <div style="width: 80px">
                    Fecha
                </div>
            </th>
            <th class="text-center th_yura_green">
                <div style="width: 70px">
                    Rep.
                </div>
            </th>
            <th class="text-center th_yura_green">
                <div style="width: 70px">
                    Camas
                </div>
            </th>
            <th class="text-center th_yura_green">
                <div style="width: 70px">
                    Ltrs. x Cama
                </div>
            </th>
            @foreach($productos as $producto)
                <th class="text-center bg-yura_dark">
                    <div style="width: 140px">
                        {{$producto->nombre}}
                    </div>
                </th>
            @endforeach
            @foreach($mano_obras as $mo)
                <th class="text-center bg-yura_dark">
                    <div style="width: 140px">
                        {{$mo->nombre}}
                    </div>
                </th>
            @endforeach
            <th class="text-center th_yura_green columna_fija_right_0">
                <div style="width: 70px">
                </div>
            </th>
        </tr>
        @foreach($labores as $labor)
            <tr id="tr_labor_{{$labor->id_aplicacion_campo}}">
                <td class="text-center" style="border-color: #9d9d9d">
                    {{difFechas($labor->fecha, $ciclo->fecha_inicio)->days}}
                </td>
                <th class="text-center" style="border-color: #9d9d9d">
                    <input type="date" id="app_fecha_{{$labor->id_aplicacion_campo}}" style="width: 100%" class="text-center"
                           value="{{$labor->fecha}}">
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    <input type="number" id="app_repeticion_{{$labor->id_aplicacion_campo}}" style="width: 100%" class="text-center"
                           value="{{$labor->repeticion}}">
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    <input type="number" id="app_camas_{{$labor->id_aplicacion_campo}}" style="width: 100%" class="text-center"
                           value="{{$labor->camas}}">
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    <input type="number" id="app_litro_x_cama_{{$labor->id_aplicacion_campo}}" style="width: 100%" class="text-center"
                           value="{{$labor->litro_x_cama}}">
                </th>
                @foreach($productos as $producto)
                    @php
                        $detalle = $labor->getDetalleByProducto($producto->id_producto);
                    @endphp
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{$detalle != '' && $detalle->id_unidad_medida != '' ? $detalle->dosis . ' '. $detalle->unidad_medida->siglas : ''}}
                    </td>
                @endforeach
                @foreach($mano_obras as $mo)
                    @php
                        $detalle = $labor->getDetalleByManoObra($mo->id_mano_obra);
                    @endphp
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{$detalle != '' && $detalle->id_unidad_medida != '' ? $detalle->dosis . ' '. $detalle->unidad_medida->siglas : ''}}
                    </td>
                @endforeach
                <th class="text-center columna_fija_right_0" style="border-color: #9d9d9d">
                    <div class="btn-group">
                        <button type="button" class="btn btn-xs btn-yura_primary" onclick="update_aplicacion('{{$labor->id_aplicacion_campo}}')">
                            <i class="fa fa-fw fa-pencil"></i>
                        </button>
                        <button type="button" class="btn btn-xs btn-yura_danger" onclick="delete_aplicacion('{{$labor->id_aplicacion_campo}}')">
                            <i class="fa fa-fw fa-trash"></i>
                        </button>
                    </div>
                </th>
            </tr>
        @endforeach
    </table>
</div>

<style>
    .columna_fija_right_0 {
        position: sticky;
        right: 0;
        z-index: 9;
        background-color: #e9ecef;
    }
</style>

<script>
    function update_aplicacion(id) {
        datos = {
            _token: '{{csrf_token()}}',
            id: id,
            fecha: $('#app_fecha_' + id).val(),
            repeticion: $('#app_repeticion_' + id).val(),
            camas: $('#app_camas_' + id).val(),
            litro_x_cama: $('#app_litro_x_cama_' + id).val(),
        };
        post_jquery_m('{{url('ingreso_labores/update_aplicacion')}}', datos, function () {
        }, 'tr_labor_' + id);
    }

    function delete_aplicacion(id) {
        datos = {
            _token: '{{csrf_token()}}',
            id: id,
        };
        post_jquery_m('{{url('ingreso_labores/delete_aplicacion')}}', datos, function () {
            $('#tr_labor_' + id).remove();
        });
    }
</script>