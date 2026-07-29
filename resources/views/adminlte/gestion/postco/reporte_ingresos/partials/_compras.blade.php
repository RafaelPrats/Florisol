<table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d; margin-top: 5px">
    <tr class="tr_fija_top_0">
        <th class="padding_lateral_5 bg-yura_dark">
            Fecha
        </th>
        <th class="padding_lateral_5 bg-yura_dark">
            Packing
        </th>
        <th class="padding_lateral_5 bg-yura_dark">
            Factura
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
    </tr>
    @foreach ($listado_compras as $compra)
        @foreach ($compra['detalles'] as $pos_i => $item)
            <tr class="compra_{{ $compra['packing'] }}"
                onmouseover="$('.compra_{{ $compra['packing'] }}').css('background-color', 'cyan')"
                onmouseleave="$('.compra_{{ $compra['packing'] }}').css('background-color', '')">
                @if ($pos_i == 0)
                    <th class="padding_lateral_5" style="border-color: #9d9d9d"
                        rowspan="{{ count($compra['detalles']) }}">
                        {{ $compra['fecha'] }}
                    </th>
                    <th class="padding_lateral_5" style="border-color: #9d9d9d"
                        rowspan="{{ count($compra['detalles']) }}">
                        {{ $compra['packing'] }}
                    </th>
                    <th class="padding_lateral_5" style="border-color: #9d9d9d"
                        rowspan="{{ count($compra['detalles']) }}">
                        {{ $compra['factura'] }}
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
            </tr>
        @endforeach
    @endforeach
</table>
