<table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d; margin-top: 5px">
    <tr class="tr_fija_top_0">
        <th class="padding_lateral_5 bg-yura_dark">
            OT
        </th>
        <th class="padding_lateral_5 bg-yura_dark">
            Fecha
        </th>
        <th class="padding_lateral_5 bg-yura_dark">
            Cliente
        </th>
        <th class="padding_lateral_5 bg-yura_dark">
            Ramos
        </th>
        <th class="padding_lateral_5 bg-yura_dark">
            Fecha Inventario
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
            Tallos
        </th>
    </tr>
    @foreach ($listado_ot as $ot)
        @foreach ($ot['detalles'] as $pos_i => $item)
            <tr class="ot_{{ $ot['ot'] }}"
                onmouseover="$('.ot_{{ $ot['ot'] }}').css('background-color', 'cyan')"
                onmouseleave="$('.ot_{{ $ot['ot'] }}').css('background-color', '')">
                @if ($pos_i == 0)
                    <th class="padding_lateral_5" style="border-color: #9d9d9d" rowspan="{{ count($ot['detalles']) }}">
                        #{{ $ot['ot'] }}
                    </th>
                    <th class="padding_lateral_5" style="border-color: #9d9d9d" rowspan="{{ count($ot['detalles']) }}">
                        {{ $ot['fecha'] }}
                    </th>
                    <th class="padding_lateral_5" style="border-color: #9d9d9d" rowspan="{{ count($ot['detalles']) }}">
                        {{ $ot['cli_nombre'] }}
                    </th>
                    <th class="padding_lateral_5" style="border-color: #9d9d9d" rowspan="{{ count($ot['detalles']) }}">
                        {{ $ot['ramos'] }}
                    </th>
                @endif
                <th class="padding_lateral_5" style="border-color: #9d9d9d">
                    {{ $item->fecha_inventario }}
                </th>
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
                    {{ $item->cantidad }}
                </th>
            </tr>
        @endforeach
    @endforeach
</table>
