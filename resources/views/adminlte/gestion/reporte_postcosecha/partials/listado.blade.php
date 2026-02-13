<div style="overflow-x: scroll; overflow-y: scroll; height: 500px;">
    <table class="table-bordered" style="border: 1px solid #9d9d9d; width: 100%">
        <tr>
            <th class="text-center th_yura_green">
                Variedad <sup>MEDIDA</sup>
            </th>
            @php
                $totales_longitudes = [];
            @endphp
            @foreach ($longitudes as $l)
                <th class="text-center bg-yura_dark">
                    {{ $l }}
                </th>
                @php
                    $totales_longitudes[] = 0;
                @endphp
            @endforeach
            <th class="text-center th_yura_green">
                TOTAL
            </th>
        </tr>
        @foreach ($listado as $item)
            @foreach ($item['valores_var'] as $var)
                <tr>
                    <th class="text-center bg-yura_dark">
                        {{ $var['variedad']->nombre }}
                    </th>
                    @php
                        $total_var = 0;
                    @endphp
                    @foreach ($var['valores_long'] as $pos => $val)
                        @php
                            $total_var += $val;
                            $totales_longitudes[$pos] += $val;
                        @endphp
                        <td class="text-center" style="border-color: #9d9d9d">
                            {{ number_format($val) }}
                        </td>
                    @endforeach
                    <th class="text-center bg-yura_dark">
                        {{ number_format($total_var) }}
                    </th>
                </tr>
            @endforeach
        @endforeach
        <tr>
            <th class="text-center th_yura_green">
                TOTALES
            </th>
            @php
                $total = 0;
            @endphp
            @foreach ($totales_longitudes as $v)
                <th class="text-center bg-yura_dark">
                    {{ number_format($v) }}
                </th>
                @php
                    $total += $v;
                @endphp
            @endforeach
            <th class="text-center th_yura_green">
                {{ number_format($total) }}
            </th>
        </tr>
    </table>
</div>
