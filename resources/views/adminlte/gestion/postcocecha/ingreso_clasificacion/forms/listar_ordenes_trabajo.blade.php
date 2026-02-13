<div style="overflow-y: scroll; max-height: 500px">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d" id="table_inventario_cosecha">
        <thead>
            <tr class="tr_fija_top_0">
                <th class="text-center th_yura_green padding_lateral_5">
                    OT
                </th>
                <th class="text-center th_yura_green padding_lateral_5">
                    Pedido
                </th>
                <th class="text-center th_yura_green padding_lateral_5">
                    Receta
                </th>
                <th class="text-center th_yura_green padding_lateral_5">
                    Longitud
                </th>
                <th class="text-center th_yura_green padding_lateral_5">
                    Ramos
                </th>
                <th class="text-center th_yura_green padding_lateral_5">
                    Variedad
                </th>
                <th class="text-center th_yura_green padding_lateral_5">
                    Unidades
                </th>
                <th class="text-center th_yura_green padding_lateral_5">
                    Tallos
                </th>
                <th class="text-center th_yura_green padding_lateral_5">
                    Linea Produccion
                </th>
                <th class="text-center th_yura_green padding_lateral_5">
                    Estado
                </th>
                <th class="text-center th_yura_green padding_lateral_5">
                    Opciones
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach ($listado as $pos_o => $item)
                @php
                    $detalle_pedido = $item->detalle_import_pedido;
                    $pedido = $detalle_pedido->pedido;
                    $estado = $item->getEstado();
                @endphp
                @foreach ($item->detalles as $pos_d => $det)
                    <tr onmouseover="$(this).addClass('bg-aqua')" onmouseleave="$(this).removeClass('bg-aqua')"
                        class="tr_ot_{{ $item->id_orden_trabajo }}">
                        @if ($pos_d == 0)
                            <th class="text-center padding_lateral_5" style="border-color: #9d9d9d"
                                rowspan="{{ count($item->detalles) }}">
                                #{{ $item->id_orden_trabajo }}
                            </th>
                            <th class="text-center padding_lateral_5" style="border-color: #9d9d9d"
                                rowspan="{{ count($item->detalles) }}">
                                #{{ $pedido->codigo }}
                                <br>
                                {{ $pedido->cliente->detalle()->nombre }}
                                <br>
                                {{ convertDateToText($item->fecha) }}
                            </th>
                            <th class="text-center padding_lateral_5" style="border-color: #9d9d9d"
                                rowspan="{{ count($item->detalles) }}">
                                {{ $detalle_pedido->variedad->nombre }}
                            </th>
                            <th class="text-center padding_lateral_5" style="border-color: #9d9d9d"
                                rowspan="{{ count($item->detalles) }}">
                                {{ $item->longitud }} <sup>cm</sup>
                            </th>
                            <th class="text-center padding_lateral_5" style="border-color: #9d9d9d"
                                rowspan="{{ count($item->detalles) }}">
                                {{ $item->ramos }}
                            </th>
                        @endif
                        <th class="text-center padding_lateral_5" style="border-color: #9d9d9d">
                            {{ $det->variedad->nombre }}
                        </th>
                        <th class="text-center padding_lateral_5" style="border-color: #9d9d9d">
                            {{ $det->tallos / $item->ramos }}
                        </th>
                        <th class="text-center padding_lateral_5" style="border-color: #9d9d9d">
                            {{ $det->tallos }}
                        </th>
                        @if ($pos_d == 0)
                            <th class="text-center" style="border-color: #9d9d9d; color: black"
                                rowspan="{{ count($item->detalles) }}">
                                <select id="id_despachador_{{ $item->id_orden_trabajo }}" style="width: 100%"
                                    onchange="update_despachador('{{ $item->id_orden_trabajo }}')">
                                    <option value="">Seleccione</option>
                                    @foreach ($despachadores as $desp)
                                        <option value="{{ $desp->id_despachador }}"
                                            {{ $desp->id_despachador == $item->id_despachador ? 'selected' : '' }}>
                                            {{ $desp->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </th>
                            <th class="text-center padding_lateral_5" style="border-color: #9d9d9d"
                                rowspan="{{ count($item->detalles) }}">
                                {!! $estado['html'] !!}
                            </th>
                            <th class="text-center padding_lateral_5" style="border-color: #9d9d9d"
                                rowspan="{{ count($item->detalles) }}">
                                <div class="btn-group">
                                    @if ($estado['estado'] == 'Pendiente')
                                        <button type="button" class="btn btn-xs btn-yura_danger"
                                            style="margin-top: 5px"
                                            onclick="eliminar_orden_trabajo('{{ $item->id_orden_trabajo }}')">
                                            <i class="fa fa-fw fa-trash"></i> Eliminar
                                        </button>
                                    @endif
                                    <button type="button" class="btn btn-xs btn-yura_default" style="margin-top: 5px"
                                        onclick="exportar_orden_trabajo('{{ $item->id_orden_trabajo }}')">
                                        <i class="fa fa-fw fa-file-excel-o"></i> Exportar
                                    </button>
                                </div>
                            </th>
                        @endif
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>
</div>

<script>
    function eliminar_orden_trabajo(id) {
        texto =
            "<div class='alert alert-warning text-center'>¿Esta seguro de <b>ELIMINAR</b> la orden de trabajo?</div>";

        modal_quest('modal_eliminar_orden_trabajo', texto, 'Eliminar la Orden de Trabajo', true, false, '40%',
            function() {
                datos = {
                    _token: '{{ csrf_token() }}',
                    id: id,
                }
                post_jquery_m('{{ url('ingreso_clasificacion/eliminar_orden_trabajo') }}', datos, function() {
                    cerrar_modals();
                    armar_combinacion($('#id_receta_armar').val(),
                        $('#longitud_receta_armar').val(),
                        $('#fecha_receta_armar').val());
                    listar_reporte();
                });
            })
    }

    function exportar_orden_trabajo(id) {
        $.LoadingOverlay('show');
        window.open('{{ url('ingreso_clasificacion/exportar_orden_trabajo') }}?id=' + id, '_blank');
        $.LoadingOverlay('hide');
    }

    function update_despachador(id_ot) {
        texto =
            "<div class='alert alert-warning text-center'>¿Esta seguro de <b>ASIGNAR</b> este responsable?</div>";

        modal_quest('modal_update_despachador', texto, 'Asignar responsable', true, false, '40%',
            function() {
                datos = {
                    _token: '{{ csrf_token() }}',
                    id_ot: id_ot,
                    despachador: $('#id_despachador_' + id_ot).val(),
                }
                post_jquery_m('{{ url('ingreso_clasificacion/update_despachador') }}', datos, function() {
                    
                });
            })
    }
</script>
