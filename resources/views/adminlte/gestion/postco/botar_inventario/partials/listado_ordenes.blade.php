<div style="overflow-y: scroll; max-height: 700px">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
        <tr class="tr_fija_top_0">
            <th class="padding_lateral_5 th_yura_green" style="width: 45px">
                N°
            </th>
            <th class="padding_lateral_5 th_yura_green" style="width: 90px">
                Fecha
            </th>
            <th class="padding_lateral_5 th_yura_green" style="width: 90px">
                Estado
            </th>
            <th class="padding_lateral_5 th_yura_green">
                Planta
            </th>
            <th class="padding_lateral_5 th_yura_green">
                Variedad
            </th>
            <th class="padding_lateral_5 th_yura_green" style="width: 60px">
                Longitud
            </th>
            <th class="padding_lateral_5 th_yura_green" style="width: 90px">
                TxR
            </th>
            <th class="padding_lateral_5 th_yura_green" style="width: 90px">
                Motivo
            </th>
            <th class="padding_lateral_5 th_yura_green" style="width: 90px">
                Tallos a Botar
            </th>
            <th class="padding_lateral_5 th_yura_green" style="width: 90px">
                Opciones
            </th>
        </tr>
        @foreach ($listado as $item)
            @foreach ($item['detalles'] as $pos_d => $det)
                <tr onmouseover="$('.tr_orden_{{ $item['orden_basura'] }}').css('background-color', 'cyan')"
                    onmouseleave="$('.tr_orden_{{ $item['orden_basura'] }}').css('background-color', '')"
                    class="tr_orden_{{ $item['orden_basura'] }}">
                    @if ($pos_d == 0)
                        <th class="padding_lateral_5" style="border-color: #9d9d9d"
                            rowspan="{{ count($item['detalles']) }}">
                            {{ $item['orden_basura'] }}
                        </th>
                        <th class="padding_lateral_5" style="border-color: #9d9d9d"
                            rowspan="{{ count($item['detalles']) }}">
                            {{ $item['fecha'] }}
                        </th>
                        <th class="padding_lateral_5" style="border-color: #9d9d9d"
                            rowspan="{{ count($item['detalles']) }}">
                            @if ($item['estado_orden_basura'])
                                <span class="badge bg-yura_primary">COMPLETADA</span>
                            @else
                                <span class="badge bg-yura_warning">PENDIENTE</span>
                            @endif
                        </th>
                    @endif
                    <th class="padding_lateral_5" style="border-color: #9d9d9d">
                        {{ $det->pta_nombre }}
                    </th>
                    <th class="padding_lateral_5" style="border-color: #9d9d9d">
                        {{ $det->var_nombre }}
                    </th>
                    <th class="padding_lateral_5" style="border-color: #9d9d9d">
                        {{ $det->longitud }}
                    </th>
                    <th class="padding_lateral_5" style="border-color: #9d9d9d">
                        {{ $det->tallos_x_ramo }}
                    </th>
                    <th class="padding_lateral_5" style="border-color: #9d9d9d">
                        {{ $det->motivo_nombre }}
                    </th>
                    <th class="padding_lateral_5" style="border-color: #9d9d9d">
                        {{ $det->basura }}
                    </th>
                    @if ($pos_d == 0)
                        <th class="text-center" style="border-color: #9d9d9d" rowspan="{{ count($item['detalles']) }}">
                            @if ($item['estado_orden_basura'] == 0)
                                <div class="btn-group">
                                    <button type="button" class="btn btn-xs btn-yura_warning" title="Confirmar"
                                        onclick="completar_orden('{{ $item['orden_basura'] }}')">
                                        <i class="fa fa-fw fa-check"></i>
                                    </button>
                                    <button type="button" class="btn btn-xs btn-yura_danger" title="Eliminar"
                                        onclick="delete_orden_basura('{{ $item['orden_basura'] }}')">
                                        <i class="fa fa-fw fa-trash"></i>
                                    </button>
                                </div>
                            @endif
                        </th>
                    @endif
                </tr>
            @endforeach
        @endforeach
    </table>
</div>

<script>
    function delete_orden_basura(orden) {
        texto =
            '<div class="alert alert-warning text-center"><h3>¿Esta seguro de <b>ELIMINAR</b> la orden?</h3></div>';

        modal_quest('modal_delete_orden_basura', texto, 'Eliminar la orden', true, false, '40%',
            function() {
                datos = {
                    _token: '{{ csrf_token() }}',
                    orden: orden,
                }
                post_jquery_m('{{ url('botar_inventario/delete_orden_basura') }}', datos, function() {
                    cerrar_modals();
                    listar_ordenes();
                });
            })
    }

    function completar_orden(orden) {
        texto =
            '<div class="alert alert-warning text-center"><h3>¿Esta seguro de <b>DESECHAR</b> la flor?</h3></div>' +
            '<div class="input-group">' +
            '<div class="input-group-addon span-input-group-yura-fixed bg-yura_dark">' +
            'Codigo de autorizacion' +
            '</div>' +
            '<input type="password" id="codigo_autorizacion" style="width: 100%" class="text-center form-control">' +
            '</div>';

        modal_quest('modal_completar_orden', texto, 'Desechar la flor', true, false, '40%',
            function() {
                datos = {
                    _token: '{{ csrf_token() }}',
                    orden: orden,
                    codigo: $('#codigo_autorizacion').val(),
                }
                post_jquery_m('{{ url('botar_inventario/completar_orden') }}', datos, function() {
                    cerrar_modals();
                    listar_ordenes();
                });
            })
    }
</script>
