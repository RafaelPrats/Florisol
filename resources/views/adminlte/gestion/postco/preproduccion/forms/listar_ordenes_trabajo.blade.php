<div style="overflow-y: scroll; max-height: 500px">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d" id="table_inventario_cosecha">
        <thead>
            <tr class="tr_fija_top_0">
                <th class="text-center th_yura_green padding_lateral_5">
                    OT
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
                    $estado = $item->getEstado();
                @endphp
                @foreach ($item->detalles as $pos_d => $det)
                    <tr onmouseover="$(this).addClass('bg-aqua')" onmouseleave="$(this).removeClass('bg-aqua')"
                        class="tr_ot_{{ $item->id_ot_postco }}">
                        @if ($pos_d == 0)
                            <th class="text-center padding_lateral_5" style="border-color: #9d9d9d"
                                rowspan="{{ count($item->detalles) }}">
                                #{{ $item->id_ot_postco }}
                            </th>
                            <th class="text-center padding_lateral_5" style="border-color: #9d9d9d"
                                rowspan="{{ count($item->detalles) }}">
                                {{ $postco->variedad->nombre }}
                                <br>
                                <small><em>{{$item->cliente->detalle()->nombre}}</em></small>
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
                            {{ $det->item->nombre }}
                        </th>
                        <th class="text-center padding_lateral_5" style="border-color: #9d9d9d">
                            {{ $det->unidades }}
                        </th>
                        <th class="text-center padding_lateral_5" style="border-color: #9d9d9d">
                            {{ $det->unidades * $item->ramos }}
                        </th>
                        @if ($pos_d == 0)
                            <th class="text-center" style="border-color: #9d9d9d; color: black"
                                rowspan="{{ count($item->detalles) }}">
                                <select id="id_despachador_{{ $item->id_ot_postco }}" style="width: 100%"
                                    onchange="update_despachador('{{ $item->id_ot_postco }}')">
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
                                            onclick="eliminar_orden_trabajo('{{ $item->id_ot_postco }}')">
                                            <i class="fa fa-fw fa-trash"></i> Eliminar
                                        </button>
                                    @endif
                                    <button type="button" class="btn btn-xs btn-yura_default" style="margin-top: 5px"
                                        onclick="exportar_orden_trabajo('{{ $item->id_ot_postco }}')">
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
                post_jquery_m('{{ url('preproduccion/eliminar_orden_trabajo') }}', datos, function() {
                    cerrar_modals();
                    modal_receta('{{$postco->id_variedad}}', '{{$postco->longitud}}');
                    listar_reporte();
                });
            })
    }

    function exportar_orden_trabajo(id) {
        $.LoadingOverlay('show');
        window.open('{{ url('preproduccion/exportar_orden_trabajo') }}?id=' + id, '_blank');
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
                post_jquery_m('{{ url('preproduccion/update_despachador') }}', datos, function() {
                });
            })
    }
</script>
