<table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d; margin-top: 5px">
    <tr class="tr_fija_top_0">
        <th class="padding_lateral_5 bg-yura_dark">
            Fecha
        </th>
        <th class="padding_lateral_5 bg-yura_dark">
            Documento
        </th>
        <th class="padding_lateral_5 bg-yura_dark">
            Bodega
        </th>
        <th class="padding_lateral_5 bg-yura_dark">
            Planta
        </th>
        <th class="padding_lateral_5 bg-yura_dark">
            Variedad
        </th>
        <th class="padding_lateral_5 bg-yura_dark">
            Longitud
        </th>
        <th class="padding_lateral_5 bg-yura_dark">
            TxR
        </th>
        <th class="padding_lateral_5 bg-yura_dark">
            Ramos
        </th>
        <th class="padding_lateral_5 bg-yura_dark">
            Tallos
        </th>
    </tr>
    @foreach ($listado_documentos as $documento)
        @foreach ($documento['detalles'] as $pos_i => $item)
            <tr class="documento_{{ $documento['id_api_store_cajas'] }}"
                onmouseover="$('.documento_{{ $documento['id_api_store_cajas'] }}').css('background-color', 'cyan')"
                onmouseleave="$('.documento_{{ $documento['id_api_store_cajas'] }}').css('background-color', '')">
                @if ($pos_i == 0)
                    <th class="padding_lateral_5" style="border-color: #9d9d9d"
                        rowspan="{{ count($documento['detalles']) }}">
                        {{ $documento['fecha'] }}
                    </th>
                    <th class="padding_lateral_5" style="border-color: #9d9d9d"
                        rowspan="{{ count($documento['detalles']) }}">
                        {{ $documento['documento'] }}
                    </th>
                @endif
                <th class="padding_lateral_5" style="border-color: #9d9d9d">
                    {{ $item->bodega == 'V' ? 'Ventas' : 'Produccion' }}
                </th>
                <th class="padding_lateral_5" style="border-color: #9d9d9d">
                    {{ $item->pta_nombre }}
                </th>
                <th class="padding_lateral_5" style="border-color: #9d9d9d">
                    {{ $item->var_nombre }}
                </th>
                <th class="padding_lateral_5" style="border-color: #9d9d9d">
                    {{ $item->longitud }}
                </th>
                <th class="padding_lateral_5" style="border-color: #9d9d9d">
                    {{ $item->tallos_x_ramo }}
                </th>
                <th class="padding_lateral_5" style="border-color: #9d9d9d">
                    {{ $item->ramos }}
                </th>
                <th class="padding_lateral_5" style="border-color: #9d9d9d">
                    {{ $item->tallos }}
                </th>
            </tr>
        @endforeach
    @endforeach
</table>
