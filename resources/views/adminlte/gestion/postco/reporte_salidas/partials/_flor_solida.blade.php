<table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d; margin-top: 5px">
    <tr class="tr_fija_top_0">
        <th class="padding_lateral_5 bg-yura_dark">
            Fecha Despacho
        </th>
        <th class="padding_lateral_5 bg-yura_dark">
            Cliente
        </th>
        <th class="padding_lateral_5 bg-yura_dark">
            Piezas
        </th>
        <th class="padding_lateral_5 bg-yura_dark">
            Tipo Caja
        </th>
        <th class="padding_lateral_5 bg-yura_dark">
            Ramos x Caja
        </th>
        <th class="padding_lateral_5 bg-yura_dark">
            Planta
        </th>
        <th class="padding_lateral_5 bg-yura_dark">
            Variedad
        </th>
        <th class="padding_lateral_5 bg-yura_dark">
            Fecha Inventario
        </th>
        <th class="padding_lateral_5 bg-yura_dark">
            Bodega
        </th>
        <th class="padding_lateral_5 bg-yura_dark">
            Longitud
        </th>
        <th class="padding_lateral_5 bg-yura_dark">
            TxR
        </th>
        <th class="padding_lateral_5 bg-yura_dark">
            Tallos
        </th>
    </tr>
    @foreach ($listado_solido as $det_caja)
        @foreach ($det_caja['detalles'] as $pos_i => $item)
            <tr class="det_caja_{{ $det_caja['det_caja'] }}"
                onmouseover="$('.det_caja_{{ $det_caja['det_caja'] }}').css('background-color', 'cyan')"
                onmouseleave="$('.det_caja_{{ $det_caja['det_caja'] }}').css('background-color', '')">
                @if ($pos_i == 0)
                    <th class="padding_lateral_5" style="border-color: #9d9d9d"
                        rowspan="{{ count($det_caja['detalles']) }}">
                        {{ $det_caja['fecha'] }}
                    </th>
                    <th class="padding_lateral_5" style="border-color: #9d9d9d"
                        rowspan="{{ count($det_caja['detalles']) }}">
                        {{ $det_caja['cli_nombre'] }}
                    </th>
                    <th class="padding_lateral_5" style="border-color: #9d9d9d"
                        rowspan="{{ count($det_caja['detalles']) }}">
                        {{ $det_caja['piezas'] }}
                    </th>
                    <th class="padding_lateral_5" style="border-color: #9d9d9d"
                        rowspan="{{ count($det_caja['detalles']) }}">
                        {{ $det_caja['tipo_caja'] }}
                    </th>
                    <th class="padding_lateral_5" style="border-color: #9d9d9d"
                        rowspan="{{ count($det_caja['detalles']) }}">
                        {{ $det_caja['ramos_x_caja'] }}
                    </th>
                    <th class="padding_lateral_5" style="border-color: #9d9d9d"
                        rowspan="{{ count($det_caja['detalles']) }}">
                        {{ $det_caja['pta_nombre'] }}
                    </th>
                    <th class="padding_lateral_5" style="border-color: #9d9d9d"
                        rowspan="{{ count($det_caja['detalles']) }}">
                        {{ $det_caja['var_nombre'] }}
                    </th>
                @endif
                <th class="padding_lateral_5" style="border-color: #9d9d9d">
                    {{ $item->fecha_inventario }}
                </th>
                <th class="padding_lateral_5" style="border-color: #9d9d9d">
                    {{ $item->bodega == 'V' ? 'Ventas' : 'Produccion' }}
                </th>
                <th class="padding_lateral_5" style="border-color: #9d9d9d">
                    {{ $item->longitud }}
                </th>
                <th class="padding_lateral_5" style="border-color: #9d9d9d">
                    {{ $item->tallos_x_ramo }}
                </th>
                <th class="padding_lateral_5" style="border-color: #9d9d9d">
                    {{ $item->cantidad }}
                </th>
            </tr>
        @endforeach
    @endforeach
</table>
