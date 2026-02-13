<table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d" id="table_inventario_cosecha">
    <thead>
        <tr class="tr_fija_top_0">
            <th class="text-center th_yura_green padding_lateral_5" rowspan="2">
                Proveedor
            </th>
            <th class="text-center th_yura_green padding_lateral_5" rowspan="2">
                Planta
            </th>
            <th class="text-center th_yura_green padding_lateral_5" rowspan="2">
                Variedad
            </th>
            <th class="text-center th_yura_green padding_lateral_5" rowspan="2">
                Longitud
            </th>
            <th class="text-center th_yura_green" colspan="{{ count($fechas) }}">
                Dias
            </th>
            <th class="text-center th_yura_green padding_lateral_5" rowspan="2">
                Saldo
            </th>
        </tr>
        <tr class="tr_fija_top_1">
            @php
                $totales_fechas = [];
            @endphp
            @foreach ($fechas as $pos_f => $f)
                <th class="text-center padding_lateral_5 bg-yura_dark" title="{{ convertDateToText($f) }}">
                    {{ difFechas(hoy(), $f)->d }} @if ($pos_f == count($fechas) - 1)
                        ...
                    @endif
                </th>
                @php
                    $totales_fechas[] = 0;
                @endphp
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach ($listado as $item)
            <tr onmouseover="$(this).addClass('bg-aqua')" onmouseleave="$(this).removeClass('bg-aqua')">
                <th class="text-center padding_lateral_5" style="border-color: #9d9d9d">
                    {{ $item['combinacion']->proveedor_nombre }}
                </th>
                <th class="text-center padding_lateral_5" style="border-color: #9d9d9d">
                    {{ $item['combinacion']->planta_nombre }}
                </th>
                <th class="text-center padding_lateral_5" style="border-color: #9d9d9d">
                    {{ $item['combinacion']->variedad_nombre }}
                </th>
                <th class="text-center padding_lateral_5" style="border-color: #9d9d9d">
                    {{ $item['combinacion']->longitud }}<sup>cm</sup>
                </th>
                @php
                    $total_combinacion = 0;
                @endphp
                @foreach ($item['valores'] as $pos_v => $v)
                    @php
                        $total_combinacion += $v;
                        $totales_fechas[$pos_v] += $v;
                    @endphp
                    <td class="text-center padding_lateral_5"
                        style="background-color: #dddddd !important; color: black !important; border-color: #9d9d9d"
                        title="{{ convertDateToText($fechas[$pos_v]) }}">
                        @if ($v > 0)
                            {{ number_format($v) }}
                        @endif
                    </td>
                @endforeach
                <th class="text-center padding_lateral_5" style="border-color: #9d9d9d">
                    {{ number_format($total_combinacion) }}
                </th>
            </tr>
        @endforeach
    </tbody>
    <tr class="tr_fija_bottom_0">
        <th class="text-center th_yura_green" colspan="4">
            Totales
        </th>
        @php
            $total = 0;
        @endphp
        @foreach ($totales_fechas as $pos_v => $v)
            @php
                $total += $v;
            @endphp
            <th class="text-center bg-yura_dark" title="{{ convertDateToText($fechas[$pos_v]) }}">
                @if ($v > 0)
                    {{ number_format($v) }}
                @endif
            </th>
        @endforeach
        <th class="text-center th_yura_green">
            {{ number_format($total) }}
        </th>
    </tr>
</table>
