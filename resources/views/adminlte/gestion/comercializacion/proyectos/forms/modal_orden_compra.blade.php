<legend class="text-center" style="font-size: 1.3em; margin-bottom: 5px">
    Orden de Compra <b>#{{ $compra->id_orden_compra }}</b>
    <button type="button" class="btn btn-xs btn-yura_default">
        <i class="fa fa-fw fa-file-excel-o"></i> Exportar
    </button>
</legend>
<div style="overflow-y: scroll; max-height: 650px; overflow-x: scroll">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
        <tr class="tr_fija_top_0">
            <th class="padding_lateral_5 th_yura_green">
                Planta
            </th>
            <th class="padding_lateral_5 th_yura_green">
                Variedad
            </th>
            <th class="padding_lateral_5 th_yura_green" style="width: 70px">
                Tallos
            </th>
            <th class="padding_lateral_5 bg-yura_dark" style="width: 70px">
                Comprar
            </th>
            <th class="padding_lateral_5 bg-yura_dark" style="width: ">
                Proveedor
            </th>
            @if ($compra->estado == 'P')
                <th class="padding_lateral_5 bg-yura_dark" style="width: 30px">
                </th>
            @endif
        </tr>
        @foreach ($detalles as $pos_d => $det)
            @php
                $variedad = $det->variedad;
                $proveedores = $det->proveedores;
            @endphp
            <tr style="background-color: {{ $pos_d % 2 == 0 ? '#dddddd' : '' }}" class="tr_detalle"
                id="tr_detalle_{{ $det->id_detalle_orden_compra }}" data-tallos="{{ $det->tallos }}"
                data-id_detalle="{{ $det->id_detalle_orden_compra }}" data-cant_prov="{{ count($proveedores) }}">
                <th class="padding_lateral_5" style="border-color: #9d9d9d">
                    {{ $variedad->planta->nombre }}
                </th>
                <th class="padding_lateral_5" style="border-color: #9d9d9d">
                    {{ $variedad->nombre }}
                </th>
                <th class="padding_lateral_5" style="border-color: #9d9d9d">
                    {{ $det->tallos }}
                </th>
                <th class="text-center" style="border-color: #9d9d9d"
                    id="td_input_comprar_{{ $det->id_detalle_orden_compra }}">
                    @if (count($proveedores) == 0)
                        <input type="text" style="width: 100%"
                            class="padding_lateral_5 input_comprar_{{ $det->id_detalle_orden_compra }}"
                            id="input_comprar_{{ $det->id_detalle_orden_compra }}_0">
                    @else
                        @foreach ($proveedores as $pos_prov => $p)
                            <input type="text" style="width: 100%" value="{{ $p->cantidad }}"
                                {{ $compra->estado != 'P' ? 'disabled' : '' }}
                                class="padding_lateral_5 input_comprar_{{ $det->id_detalle_orden_compra }}"
                                id="input_comprar_{{ $det->id_detalle_orden_compra }}_{{ $pos_prov }}">
                        @endforeach
                    @endif
                </th>
                <th class="text-center" style="border-color: #9d9d9d"
                    id="td_select_proveedor_{{ $det->id_detalle_orden_compra }}">
                    @if (count($proveedores) == 0)
                        <select id="select_proveedor_{{ $det->id_detalle_orden_compra }}_0"
                            style="width: 100%; height: 26px;"
                            class="select_proveedor_{{ $det->id_detalle_orden_compra }}">
                            @foreach ($det->all_proveedores as $prov)
                                <option value="{{ $prov->id_proveedor }}">
                                    {{ $prov->nombre }}
                                </option>
                            @endforeach
                        </select>
                    @else
                        @foreach ($proveedores as $pos_prov => $p)
                            <select id="select_proveedor_{{ $det->id_detalle_orden_compra }}_{{ $pos_prov }}"
                                style="width: 100%; height: 26px;"
                                class="select_proveedor_{{ $det->id_detalle_orden_compra }}"
                                {{ $compra->estado != 'P' ? 'disabled' : '' }}>
                                @foreach ($det->all_proveedores as $prov)
                                    <option value="{{ $prov->id_proveedor }}"
                                        {{ $prov->id_proveedor == $p->id_proveedor ? 'selected' : '' }}>
                                        {{ $prov->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        @endforeach
                    @endif
                </th>
                @if ($compra->estado == 'P')
                    <th class="text-center" style="border-color: #9d9d9d">
                        <button type="button" class="btn btn-xs btn-yura_dark" id="btn_agregar_proveedor"
                            onclick="agregar_proveedor('{{ $det->id_detalle_orden_compra }}')">
                            <i class="fa fa-fw fa-plus"></i>
                        </button>
                    </th>
                @endif
            </tr>
        @endforeach
    </table>
</div>
<div class="text-center" style="margin-top: 5px">
    @if ($compra->estado == 'P')
        <button type="button" class="btn btn-yura_primary"
            onclick="update_orden_compra('{{ $compra->id_orden_compra }}')">
            <i class="fa fa-fw fa-save"></i> ACTUALIZAR ORDEN
        </button>
    @elseif($compra->estado == 'F')
        <button type="button" class="btn btn-yura_primary" disabled>
            <i class="fa fa-fw fa-check"></i> COMPRA FINALIZADA
        </button>
    @endif
</div>

<script>
    function agregar_proveedor(det) {
        cant_prov = $('#tr_detalle_' + det).data('cant_prov');
        cant_prov++;
        $('#td_input_comprar_' + det).append(
            '<input type="text" style="width: 100%" class="padding_lateral_5 input_comprar_' + det +
            '" id="input_comprar_' + det + '_' + cant_prov + '">');
        select_proveedor = $('#select_proveedor_' + det + '_0').html();
        $('#td_select_proveedor_' + det).append(
            '<div style="margin-top: 0;">' +
            '<select id="select_proveedor_' + det + '_' + cant_prov +
            '" style="width: 100%; height: 26px;" class="select_proveedor_' + det + '">' +
            select_proveedor +
            '</select>' +
            '</div>'
        );
        $('#tr_detalle_' + det).data('cant_prov', cant_prov);
    }

    function update_orden_compra(id) {
        $('.tr_detalle').removeClass('error');
        data = [];
        tr_detalle = $('.tr_detalle');
        fallos = false;
        for (d = 0; d < tr_detalle.length; d++) {
            id_det = tr_detalle[d].getAttribute('data-id_detalle');
            tallos = parseInt(tr_detalle[d].getAttribute('data-tallos'));
            proveedores = [];
            input_comprar = $('.input_comprar_' + id_det);
            select_proveedor = $('.select_proveedor_' + id_det);
            total_comprar = 0;
            for (p = 0; p < input_comprar.length; p++) {
                comprar = parseInt(input_comprar[p].value);
                proveedor = select_proveedor[p].value;
                if (comprar > 0 && proveedor != '') {
                    proveedores.push({
                        comprar: comprar,
                        proveedor: proveedor,
                    });
                }
                total_comprar += comprar;
            }
            if (total_comprar != tallos) {
                $('#tr_detalle_' + id_det).addClass('error');
                //fallos = true;
            }
            if (proveedores.length > 0) {
                data.push({
                    id_det: id_det,
                    proveedores: proveedores,
                });
            }
        }
        if (data.length > 0 && !fallos) {
            mensaje = {
                title: '<i class="fa fa-fw fa-save"></i> Actualizar Orden de Compra',
                mensaje: '<div class="alert alert-warning text-center"><i class="fa fa-fw fa-exclamation-triangle"></i> ¿Está seguro de <b>Actualizar la Orden de Compra</b> para este pedido?</div>',
            };
            modal_quest('modal_update_orden_compra', mensaje['mensaje'], mensaje['title'], true, false,
                '{{ isPC() ? '35%' : '' }}',
                function() {
                    datos = {
                        _token: '{{ csrf_token() }}',
                        id: id,
                        data: JSON.stringify(data),
                    };
                    post_jquery_m('{{ url('proyectos/update_orden_compra') }}', datos, function() {
                        /*cerrar_modals();
                        modal_confirmaciones(id);*/
                    }, '', 10000);
                });
        } else {
            alert('Los tallos a comprar no coinciden');
        }
    }
</script>
