@if(count($detalles) > 0)
    <table class="table-bordered" style="width: 100%; border-radius: 18px 18px 0 0">
        <tr>
            <th class="text-center th_yura_green" style="border-radius: 18px 0 0 0">Variedad</th>
            <th class="text-center th_yura_green">Semana inicial</th>
            <th class="text-center th_yura_green" style="width: 80px">Semanas</th>
            <th class="text-center th_yura_green">Disponibilidad</th>
            <th class="text-center th_yura_green">Bandeja</th>
            <th class="text-center th_yura_green" style="width: 100px;">Cantidad</th>
            <th class="text-center th_yura_green" style="border-radius: 0 18px 0 0; width: 80px">Opciones</th>
        </tr>
        @php
            $enrz = $detalles[0]->enraizamiento_semanal;
            $variedad = $enrz->variedad;
            $total_dia = 0;
            $total = 0;
        @endphp
        @foreach($detalles as $pos => $det)
            @php
                $total_dia += $det->cantidad_siembra;
                $total += $det->cantidad_siembra;
            @endphp
            <tr>
                <td class="text-left td_yura_default" style="border-color: #9d9d9d; padding-left: 15px" colspan="4">{{$variedad->nombre}}</td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <select id="edit_id_contenedor_propag_{{$det->id_detalle_enraizamiento_semanal}}" style="width: 100%">
                        @foreach($contenedores as $cont)
                            <option value="{{$cont->id_contenedor_propag}}"
                                    {{$cont->id_contenedor_propag == $det->id_contenedor_propag ? 'selected' : ''}}>{{$cont->nombre}}</option>
                        @endforeach
                    </select>
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <input type="number" id="edit_cantidad_siembra_{{$det->id_detalle_enraizamiento_semanal}}" style="width: 100%"
                           value="{{$det->cantidad_siembra}}" class="text-center">
                </td>
                <td class="text-center" style="border-color: #9d9d9d" id="td_opciones_{{$det->id_detalle_enraizamiento_semanal}}">
                    <div class="btn-group">
                        <button type="button" class="btn btn-xs btn-yura_primary"
                                onclick="update_detalle_enraizamiento('{{$det->id_detalle_enraizamiento_semanal}}')">
                            <i class="fa fa-fw fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-xs btn-yura_danger"
                                onclick="delete_detalle_enraizamiento('{{$det->id_detalle_enraizamiento_semanal}}')">
                            <i class="fa fa-fw fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
            @if(($pos+1 <= count($detalles) - 1 && $detalles[$pos+1]->id_enraizamiento_semanal != $det->id_enraizamiento_semanal) || $pos == count($detalles) - 1)
                <tr>
                    <th class="text-left" style="background-color: #e9ecef; border-color: #9d9d9d; padding-left: 5px" colspan="5">
                        Total variedad en el día
                    </th>
                    <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d; padding-left: 5px">
                        {{number_format($total_dia)}}
                    </th>
                </tr>
                <tr>
                    <th class="text-left" style="background-color: #00b3887a; border-color: #9d9d9d; padding-left: 5px">
                        Total semana
                    </th>
                    <td class="text-center"
                        style="background-color: #00b3887a; border-color: #9d9d9d; padding-left: 5px">{{$enrz->semana_ini}}</td>
                    <td class="text-center"
                        style="background-color: #00b3887a; border-color: #9d9d9d">
                        <input type="number" id="edit_cantidad_semanas_{{$enrz->id_enraizamiento_semanal}}"
                               style="width: 100%; background-color: #00b3887a" value="{{$enrz->cantidad_semanas}}" class="text-center">
                    </td>
                    <td class="text-center"
                        style="background-color: #00b3887a; border-color: #9d9d9d; padding-left: 5px">{{$enrz->semana_fin}}</td>
                    <th class="text-center" style="background-color: #00b3887a; border-color: #9d9d9d;">
                    </th>
                    <th class="text-center" style="background-color: #00b3887a; border-color: #9d9d9d; padding-left: 5px">
                        {{number_format($enrz->cantidad_siembra)}}
                    </th>
                    <td class="text-center" style="border-color: #9d9d9d" id="td_opciones_enrz_{{$enrz->id_enraizamiento_semanal}}">
                        <div class="btn-group">
                            <button type="button" class="btn btn-xs btn-yura_primary"
                                    onclick="update_enraizamiento('{{$enrz->id_enraizamiento_semanal}}')">
                                <i class="fa fa-fw fa-edit"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @php
                    $enrz = $pos == count($detalles) - 1 ? $det->enraizamiento_semanal : $detalles[$pos+1]->enraizamiento_semanal;
                    $variedad = $enrz->variedad;
                    $total_dia = 0;
                @endphp
            @endif
        @endforeach
        <tr>
            <th class="text-left th_yura_green" style="padding-left: 5px; border-radius: 0 0 0 18px" colspan="4">
                TOTAL
            </th>
            <th class="text-center th_yura_green" style="padding-left: 5px; border-radius: 0 0 18px 0">
                {{number_format($total)}}
            </th>
        </tr>
    </table>
@else
    <div class="alert alert-info text-center">No se han encontrado resultados que mostrar</div>
@endif

<script>
    function update_enraizamiento(id) {
        datos = {
            _token: '{{csrf_token()}}',
            id: id,
            cantidad: $('#edit_cantidad_semanas_' + id).val(),
        };
        post_jquery('{{url('enraizamiento/update_enraizamiento')}}', datos, function () {
            listar_enraizamientos();
        });
    }

    function update_detalle_enraizamiento(id) {
        datos = {
            _token: '{{csrf_token()}}',
            id: id,
            cantidad: $('#edit_cantidad_siembra_' + id).val(),
            contenedor: $('#edit_id_contenedor_propag_' + id).val(),
        };
        post_jquery('{{url('enraizamiento/update_detalle_enraizamiento')}}', datos, function () {
            listar_enraizamientos();
        });
    }

    function delete_detalle_enraizamiento(id) {
        datos = {
            _token: '{{csrf_token()}}',
            id: id,
        };
        post_jquery('{{url('enraizamiento/delete_detalle_enraizamiento')}}', datos, function () {
            listar_enraizamientos();
        });
    }
</script>