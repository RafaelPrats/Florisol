<div style="overflow-x: scroll; overflow-y: scroll; max-height: 500px">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d" id="table_resumen_recepcion">
        <thead>
            <tr class="tr_fija_top_0">
                <th class="text-center th_yura_green padding_lateral_5" rowspan="2">
                    Plantas
                </th>
                <th class="text-center th_yura_green padding_lateral_5 col_fija_left_0" rowspan="2"
                    style="z-index: 9 !important">
                    <div style="width: 150px">
                        Variedades
                    </div>
                </th>
                @php
                    $totales = [];
                @endphp
                @foreach ($fechas as $f)
                    <th class="text-center bg-yura_dark" colspan="2">
                        <div style="width: 180px">
                            {{ convertDateToText($f) }}
                        </div>
                    </th>
                    @php
                        $totales[] = [
                            'recepcion' => 0,
                            'salidas' => 0,
                        ];
                    @endphp
                @endforeach
            </tr>
            <tr class="tr_fija_top_1">
                @foreach ($fechas as $f)
                    <th class="text-center bg-yura_dark">
                        Ingresos
                    </th>
                    <th class="text-center bg-yura_dark">
                        Salidas
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($listado as $item)
                <tr onmouseover="$(this).addClass('bg-yura_dark')" onmouseleave="$(this).removeClass('bg-yura_dark')">
                    <th class="text-center" style="border-color: #9d9d9d; background-color: #dddddd">
                        {{ $item['planta']->planta_nombre }}
                    </th>
                    <th class="text-center col_fija_left_0" style="border-color: #9d9d9d; background-color: #dddddd">
                        {{ $item['planta']->variedad_nombre }}
                    </th>
                    @foreach ($item['valores'] as $pos_f => $v)
                        <td class="text-center" style="border-color: #9d9d9d; border-left: 2px solid">
                            {{ number_format($v['recepcion']->cantidad) }}
                        </td>
                        <td class="text-center" style="border-color: #9d9d9d">
                            {{ number_format($v['salidas']) }}
                        </td>
                        @php
                            $totales[$pos_f]['recepcion'] += $v['recepcion']->cantidad;
                            $totales[$pos_f]['salidas'] += $v['salidas'];
                        @endphp
                    @endforeach
                </tr>
            @endforeach
        </tbody>
        <tr class="tr_fija_bottom_0">
            <th class="text-center th_yura_green">
            </th>
            <th class="text-center th_yura_green col_fija_left_0">
                Totales
            </th>
            @foreach ($totales as $v)
                <th class="text-center bg-yura_dark">
                    {{ number_format($v['recepcion']) }}
                </th>
                <th class="text-center bg-yura_dark">
                    {{ number_format($v['salidas']) }}
                </th>
            @endforeach
        </tr>
    </table>
</div>
