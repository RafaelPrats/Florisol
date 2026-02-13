<div style="overflow-y: scroll; max-height: 500px">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d" id="table_inventario_cosecha">
        <thead>
            <tr class="tr_fija_top_0">
                <th class="text-center th_yura_green padding_lateral_5">
                    OA
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
                    Inventario
                </th>
                <th class="text-center th_yura_green padding_lateral_5">
                    Linea Produccion
                </th>
                <th class="text-center th_yura_green padding_lateral_5">
                    Estado
                </th>
                <th class="text-center th_yura_green padding_lateral_5" style="width: 90px">
                    Opciones
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach ($listado as $pos_o => $item)
                @php
                    $estado = $item->getEstado();
                    $ready = true;
                @endphp
                @foreach ($item->detalles as $pos_d => $det)
                    @php
                        $inventario = getTotalInventarioByVariedad($det->id_item);
                        $tallos = $det->unidades * $item->ramos;
                        if ($inventario < $tallos) {
                            $ready = false;
                        }
                    @endphp
                    <tr onmouseover="$(this).addClass('bg-aqua')" onmouseleave="$(this).removeClass('bg-aqua')"
                        class="tr_ot_{{ $item->id_oa_postco }}">
                        @if ($pos_d == 0)
                            <th class="text-center padding_lateral_5" style="border-color: #9d9d9d"
                                rowspan="{{ count($item->detalles) }}">
                                #{{ $item->id_oa_postco }}
                            </th>
                            <th class="text-center padding_lateral_5" style="border-color: #9d9d9d"
                                rowspan="{{ count($item->detalles) }}">
                                {{ $postco->variedad->nombre }}
                                <br>
                                <small><em>{{ $item->cliente->detalle()->nombre }}</em></small>
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
                            {{ $tallos }}
                        </th>
                        <th class="text-center padding_lateral_5"
                            style="border-color: #9d9d9d; background-color: {{ $inventario < $tallos ? '#ffb2b2' : '' }}">
                            {{ $inventario }}
                        </th>
                        @if ($pos_d == 0)
                            <th class="text-center" style="border-color: #9d9d9d; color: black"
                                rowspan="{{ count($item->detalles) }}">
                                <select id="id_despachador_{{ $item->id_oa_postco }}" style="width: 100%"
                                    onchange="update_despachador_oa('{{ $item->id_oa_postco }}')">
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
                            <th class="text-center" style="border-color: #9d9d9d"
                                rowspan="{{ count($item->detalles) }}">
                                {{-- <button type="button" class="btn btn-xs btn-block btn-yura_default"
                                    style="height: 21px; margin-top: 0;"
                                    onclick="exportar_orden_trabajo('{{ $item->id_oa_postco }}')">
                                    <i class="fa fa-fw fa-file-excel-o"></i> Exportar
                                </button> --}}
                                <button type="button" class="btn btn-xs btn-block btn-yura_primary hidden"
                                    style="height: 21px; margin-top: 0;" id="btn_convertir_{{ $item->id_oa_postco }}"
                                    onclick="convertir_ot('{{ $item->id_oa_postco }}')">
                                    <i class="fa fa-fw fa-gift"></i> Convertir
                                </button>
                                @if ($estado['estado'] == 'Pendiente')
                                    <button type="button" class="btn btn-xs btn-block btn-yura_danger"
                                        style="height: 21px; margin-top: 0;"
                                        onclick="eliminar_orden_alistamiento('{{ $item->id_oa_postco }}')">
                                        <i class="fa fa-fw fa-trash"></i> Eliminar
                                    </button>
                                @endif
                            </th>
                        @endif
                    </tr>
                @endforeach
                <script>
                    @if ($ready && $item->estado == 'P')
                        $('#btn_convertir_{{ $item->id_oa_postco }}').removeClass('hidden');
                    @endif
                </script>
            @endforeach
        </tbody>
    </table>
</div>

<script>
    function eliminar_orden_alistamiento(id) {
        texto =
            "<div class='alert alert-warning text-center'>¿Esta seguro de <b>ELIMINAR</b> la orden de alistamiento?</div>";

        modal_quest('modal_eliminar_orden_alistamiento', texto, 'Eliminar la Orden de alistamiento', true, false, '40%',
            function() {
                datos = {
                    _token: '{{ csrf_token() }}',
                    id: id,
                }
                post_jquery_m('{{ url('preproduccion/eliminar_orden_alistamiento') }}', datos, function() {
                    cerrar_modals();
                    modal_receta('{{ $postco->id_variedad }}', '{{ $postco->longitud }}');
                    listar_reporte();
                });
            })
    }

    function exportar_orden_trabajo(id) {
        $.LoadingOverlay('show');
        window.open('{{ url('preproduccion/exportar_orden_trabajo') }}?id=' + id, '_blank');
        $.LoadingOverlay('hide');
    }

    function update_despachador_oa(id_oa) {
        texto =
            "<div class='alert alert-warning text-center'>¿Esta seguro de <b>ASIGNAR</b> este responsable?</div>";

        modal_quest('modal_update_despachador_oa', texto, 'Asignar responsable', true, false, '40%',
            function() {
                datos = {
                    _token: '{{ csrf_token() }}',
                    id_oa: id_oa,
                    despachador: $('#id_despachador_' + id_oa).val(),
                }
                post_jquery_m('{{ url('preproduccion/update_despachador_oa') }}', datos, function() {});
            })
    }

    function convertir_ot(id_oa) {
        texto =
            "<div class='alert alert-warning text-center'>¿Esta seguro de <b>CONVERTIR a OT</b> esta orden de alistamiento?</div>";

        modal_quest('modal_convertir_ot', texto, 'Convertir a OT', true, false, '40%',
            function() {
                datos = {
                    _token: '{{ csrf_token() }}',
                    id_oa: id_oa,
                }
                post_jquery_m('{{ url('preproduccion/convertir_ot') }}', datos, function() {
                    cerrar_modals();
                    modal_receta('{{ $postco->id_variedad }}', '{{ $postco->longitud }}');
                });
            })
    }
</script>
