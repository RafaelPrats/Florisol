<table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d; margin-top: 5px">
    <tr class="tr_fija_top_0">
        <th class="padding_lateral_5 bg-yura_dark">
            Fecha
        </th>
        <th class="padding_lateral_5 bg-yura_dark">
            N°
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
        <th class="padding_lateral_5 bg-yura_warning" style="width: 90px">
            Tallos Anteriores
        </th>
        <th class="padding_lateral_5 bg-yura_warning" style="width: 90px">
            Tallos Corregidos
        </th>
        <th class="padding_lateral_5 bg-yura_dark" style="width: 90px">
            Tallos Ingresados
        </th>
    </tr>
    @foreach ($listado_corregir as $correccion)
        @foreach ($correccion['detalles'] as $pos_i => $item)
            <tr class="correccion_{{ $correccion['orden'] }}"
                onmouseover="$('.correccion_{{ $correccion['orden'] }}').css('background-color', 'cyan')"
                onmouseleave="$('.correccion_{{ $correccion['orden'] }}').css('background-color', '')">
                @if ($pos_i == 0)
                    <th class="padding_lateral_5" style="border-color: #9d9d9d"
                        rowspan="{{ count($correccion['detalles']) }}">
                        {{ $correccion['fecha'] }}
                    </th>
                    <th class="padding_lateral_5" style="border-color: #9d9d9d"
                        rowspan="{{ count($correccion['detalles']) }}">
                        {{ $correccion['orden'] }}
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
                <th class="padding_lateral_5" style="border-color: #9d9d9d; background-color: #ffdfa2">
                    {{ $item->anterior }}
                </th>
                <th class="padding_lateral_5" style="border-color: #9d9d9d; background-color: #ffdfa2">
                    {{ $item->actual }}
                </th>
                <th class="padding_lateral_5" style="border-color: #9d9d9d">
                    {{ $item->tallos }}
                </th>
            </tr>
        @endforeach
    @endforeach
</table>
