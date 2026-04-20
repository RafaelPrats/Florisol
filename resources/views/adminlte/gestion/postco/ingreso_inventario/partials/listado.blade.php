<div style="overflow-y: scroll; overflow-x: scroll; max-height: 700px">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d" id="table_listado">
        <thead>
            <tr class="tr_fija_top_0">
                <th class="padding_lateral_5 th_yura_green">
                    Fecha
                </th>
                <th class="padding_lateral_5 th_yura_green">
                    Variedad
                </th>
                <th class="padding_lateral_5 bg-yura_dark" style="width: 90px">
                    TxR
                </th>
                <th class="padding_lateral_5 bg-yura_dark" style="width: 90px">
                    Longitud
                </th>
                <th class="padding_lateral_5" style="width: 90px; background-color: cyan">
                    Pendiente
                </th>
                <th class="padding_lateral_5 bg-yura_dark" style="width: 90px">
                    Ramos
                </th>
                <th class="padding_lateral_5 bg-yura_dark" style="width: 90px">
                    Tallos
                </th>
                <th class="text-center bg-yura_dark" style="width: 110px">
                    <button type="button" class="btn btn-xs btn-yura_default" onclick="modal_add()">
                        <i class="fa fa-fw fa-plus"></i> Agregar
                    </button>
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach ($listado as $item)
                @php
                    $ramos_pta = 0;
                    $tallos_pta = 0;
                    $pendiente_pta = 0;
                    $existe_pta = false;
                    foreach ($item['variedades'] as $var) {
                        $ramos_pta += $var->ramos;
                        $tallos_pta += $var->disponibles;
                        $getDetalleApiStoreCajasByVariedad = getDetalleApiStoreCajasByVariedad(
                            $var->id_variedad,
                            $var->tallos_x_ramo,
                            $var->longitud,
                            $var->id_empresa,
                        );
                        $ramos_pendientes = 0;
                        foreach ($getDetalleApiStoreCajasByVariedad as $detApi) {
                            if ($detApi->id_api_store_cajas == $documento || $documento == '') {
                                $ramos_pendientes += $detApi->ramos;
                                $existe_pta = true;
                            }
                        }
                        $pendiente_pta += $ramos_pendientes;
                    }
                @endphp
                @if ($existe_pta || $documento == '')
                    <tr style="background-color: #dddddd" class="mouse-hand"
                        onclick="$('.tr_planta_{{ $item['planta']->id_planta }}').toggleClass('hidden'); $('.tr_all_detApi_{{ $item['planta']->id_planta }}').addClass('hidden')">
                        <th class="padding_lateral_5" style="border-color: #9d9d9d" colspan="4">
                            {{ $item['planta']->nombre }} <i class="fa fa-fw fa-caret-down"></i>
                        </th>
                        <th class="text-center"
                            style="border-color: #9d9d9d; background-color: {{ $pendiente_pta > 0 ? '#b0ffff' : '' }}">
                            @if ($pendiente_pta > 0)
                                {{ number_format($pendiente_pta) }}
                            @endif
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d">
                            {{ number_format($ramos_pta) }}
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d">
                            {{ number_format($tallos_pta) }}
                        </th>
                        <th class="padding_lateral_5" style="border-color: #9d9d9d">
                        </th>
                    </tr>
                    @foreach ($item['variedades'] as $pos_v => $var)
                        @php
                            $ramos_pendientes = 0;
                            $getDetalleApiStoreCajasByVariedad = getDetalleApiStoreCajasByVariedad(
                                $var->id_variedad,
                                $var->tallos_x_ramo,
                                $var->longitud,
                                $var->id_empresa,
                            );
                            $existe_var = false;
                            foreach ($getDetalleApiStoreCajasByVariedad as $detApi) {
                                if ($detApi->id_api_store_cajas == $documento || $documento == '') {
                                    $ramos_pendientes += $detApi->ramos;
                                    $existe_var = true;
                                }
                            }
                        @endphp
                        @if ($existe_var || $documento == '')
                            <tr onmouseover="$(this).css('background-color', 'cyan')"
                                onmouseleave="$(this).css('background-color', '')"
                                class="tr_planta_{{ $item['planta']->id_planta }} hidden">
                                <th class="padding_lateral_5" style="border-color: #9d9d9d">
                                    {{ $var->fecha }}
                                </th>
                                <th class="padding_lateral_5 mouse-hand" style="border-color: #9d9d9d"
                                    onclick="$('.tr_detApi_{{ $var->id_variedad }}_{{ $pos_v }}').toggleClass('hidden')">
                                    {{ $var->nombre }}
                                    @if ($ramos_pendientes > 0)
                                        <i class="fa fa-fw fa-caret-down"></i>
                                    @endif
                                </th>
                                <th class="padding_lateral_5" style="border-color: #9d9d9d">
                                    {{ $var->tallos_x_ramo }}
                                </th>
                                <th class="padding_lateral_5" style="border-color: #9d9d9d">
                                    {{ $var->longitud }}
                                </th>
                                <th style="border-color: #9d9d9d">
                                    @if ($ramos_pendientes > 0)
                                        <input type="number" style="width: 100%; background-color: #b0ffff"
                                            class="text-center"
                                            id="ramos_pendiente_{{ $var->id_inventario_recepcion }}"
                                            value="{{ $ramos_pendientes }}">
                                    @endif
                                </th>
                                <th style="border-color: #9d9d9d">
                                    <input type="number" style="width: 100%" class="text-center"
                                        value="{{ $var->ramos }}">
                                </th>
                                <th style="border-color: #9d9d9d">
                                    <input type="number" style="width: 100%" class="text-center"
                                        value="{{ $var->disponibles }}">
                                </th>
                                <th class="text-center" style="border-color: #9d9d9d">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-xs btn-yura_warning">
                                            <i class="fa fa-fw fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-xs btn-yura_danger">
                                            <i class="fa fa-fw fa-trash"></i>
                                        </button>
                                    </div>
                                </th>
                            </tr>
                            @foreach ($getDetalleApiStoreCajasByVariedad as $detApi)
                                @if ($detApi->id_api_store_cajas == $documento || $documento == '')
                                    <tr
                                        class="tr_detApi_{{ $var->id_variedad }}_{{ $pos_v }} tr_all_detApi_{{ $item['planta']->id_planta }} hidden">
                                        <td class="text-right padding_lateral_5"
                                            style="border-color: #9d9d9d; background-color: #eeeeee" colspan="4">
                                            <input type="checkbox"
                                                id="check_detApi_{{ $detApi->id_detalle_api_store_cajas }}"
                                                data-id_inventario_recepcion="{{ $var->id_inventario_recepcion }}"
                                                class="ramos_pendiente check_all_ramos_pendiente"
                                                data-id_detalle_api_store_cajas="{{ $detApi->id_detalle_api_store_cajas }}">
                                            {{ $detApi->documento }}
                                        </td>
                                        <th style="border-color: #9d9d9d">
                                            <input type="number" style="width: 100%; background-color: #b0ffff"
                                                class="text-center"
                                                id="detApi_ramos_pendiente_{{ $detApi->id_detalle_api_store_cajas }}"
                                                value="{{ $detApi->ramos }}"
                                                onchange="$('#check_detApi_{{ $detApi->id_detalle_api_store_cajas }}').prop('checked', true)">
                                        </th>
                                        <th style="border-color: #9d9d9d" colspan="3">
                                        </th>
                                    </tr>
                                @endif
                            @endforeach
                        @endif
                    @endforeach
                @endif
            @endforeach
        </tbody>
        <tr>
            <th class="th_yura_green" colspan="2"></th>
            <th class="text-center" style="border-color: #9d9d9d" colspan="2">
                <label for="check_all" class="mouse-hand">
                    Seleccionar todos
                </label>
                <input type="checkbox" id="check_all"
                    onchange="$('.check_all_ramos_pendiente').prop('checked', $(this).prop('checked'))">
            </th>
            <th style="border-color: #9d9d9d">
                <button type="button" class="btn btn-xs btn-yura_dark" onclick="recibir_all_pendientes()">
                    <i class="fa fa-fw fa-check"></i> Recibir todo
                </button>
            </th>
            <th class="th_yura_green" colspan="3"></th>
        </tr>
    </table>
</div>

<script>
    function modal_add() {
        datos = {}
        get_jquery('{{ url('ingreso_inventario/modal_add') }}', datos, function(retorno) {
            modal_view('modal_modal_add', retorno,
                '<i class="fa fa-fw fa-plus"></i> Ingreso manual',
                true, false, '{{ isPC() ? '75%' : '' }}',
                function() {});
        })
    }

    function update_flor_nacional(id) {
        datos = {
            _token: '{{ csrf_token() }}',
            id: id,
            fecha: $('#edit_fecha_' + id).val(),
            motivo: $('#edit_motivo_' + id).val(),
            finca_origen: $('#edit_finca_origen_' + id).val(),
            produccion: $('#edit_produccion_' + id).val(),
            porcentaje: parseInt($('#edit_porcentaje_' + id).val()),
            nacional: $('#edit_nacional_' + id).val(),
        }
        if (datos['fecha'] != '' && datos['motivo'] != '' && datos['finca_origen'] != '' && datos['produccion'] > 0 &&
            datos['porcentaje'] >= 0 && datos['nacional'] >= 0) {
            post_jquery_m('{{ url('ingreso_flor_nacional/update_flor_nacional') }}', datos, function() {});
        }
    }

    function delete_flor_nacional(id) {
        texto =
            "<div class='alert alert-warning text-center'><h3><i class='fa fa-fw fa-exclamation-triangle error'></i>¿Esta seguro de <b>ELIMINAR</b> el registro de flor nacional?</h3></div>";

        modal_quest('modal_delete_flor_nacional', texto, 'Grabar recetas', true, false, '40%', function() {
            datos = {
                _token: '{{ csrf_token() }}',
                id: id
            }
            post_jquery_m('{{ url('ingreso_flor_nacional/delete_flor_nacional') }}', datos, function() {
                cerrar_modals();
                listar_reporte();
            });
        })
    }

    function recibir_pendientes(id) {
        texto =
            "<div class='alert alert-warning text-center'><h3><i class='fa fa-fw fa-exclamation-triangle error'></i>¿Esta seguro de <b>RECIBIR los RAMOS PENDIENTES</b>?</h3></div>";

        modal_quest('modal_recibir_pendientes', texto, 'Grabar recetas', true, false, '40%', function() {
            datos = {
                _token: '{{ csrf_token() }}',
                id: id,
                ramos: $('#ramos_pendiente_' + id).val()
            }
            post_jquery_m('{{ url('ingreso_inventario/recibir_pendientes') }}', datos, function() {
                cerrar_modals();
                listar_reporte();
            });
        })
    }

    function recibir_all_pendientes() {
        texto =
            "<div class='alert alert-warning text-center'><h3><i class='fa fa-fw fa-exclamation-triangle error'></i>¿Esta seguro de <b>RECIBIR los RAMOS PENDIENTES</b>?</h3></div>";

        modal_quest('modal_recibir_pendientes', texto, 'Recibir ramos pendientes', true, false, '40%', function() {
            data = [];
            ramos_pendiente = $('.ramos_pendiente');
            for (i = 0; i < ramos_pendiente.length; i++) {
                id = ramos_pendiente[i].id;
                id_inv = $('#' + id).data('id_inventario_recepcion');
                id_detApi = $('#' + id).data('id_detalle_api_store_cajas');
                if ($('#check_detApi_' + id_detApi).prop('checked') == true) {
                    data.push({
                        id_inv: id_inv,
                        id_detApi: id_detApi,
                        ramos: $('#detApi_ramos_pendiente_' + id_detApi).val()
                    });
                }
            }
            if (data.length > 0) {
                datos = {
                    _token: '{{ csrf_token() }}',
                    id: id,
                    data: JSON.stringify(data)
                }
                post_jquery_m('{{ url('ingreso_inventario/recibir_all_pendientes') }}', datos, function() {
                    cerrar_modals();
                    listar_reporte();
                });
            }
        })
    }
</script>
