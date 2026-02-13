<tr class="tr_costos_50_100 hidden">
    <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
        <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em">
            <em>Total 50%</em>
        </span>
    </th>
    @php
        $total_50 = 0;
    @endphp
    @foreach($totales_costos_mo as $pos_v => $v)
        <th class="text-center" style="border-color: #9d9d9d; background-color: #ffd1d1">
            <div style="width: 100px">
                ${{number_format($v->valor_50, 2)}} <sup>{{porcentaje($v->valor_50, $v->valor, 1)}}%</sup>
            </div>
        </th>
        @php
            $total_50 += $v->valor_50;
        @endphp
    @endforeach
    <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
        <div style="width: 110px;">
            ${{number_format($total_50, 2)}} <sup>{{porcentaje($total_50, $total_mo, 1)}}%</sup>
        </div>
    </th>
</tr>

<tr class="tr_costos_50_100 hidden">
    <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
        <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em">
            <em>Total 100%</em>
        </span>
    </th>
    @php
        $total_100 = 0;
    @endphp
    @foreach($totales_costos_mo as $pos_v => $v)
        <th class="text-center" style="border-color: #9d9d9d; background-color: #ffd1d1">
            <div style="width: 100px">
                ${{number_format($v->valor_100, 2)}} <sup>{{porcentaje($v->valor_100, $v->valor, 1)}}%</sup>
            </div>
        </th>
        @php
            $total_100 += $v->valor_100;
        @endphp
    @endforeach
    <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
        <div style="width: 110px;">
            ${{number_format($total_100, 2)}} <sup>{{porcentaje($total_100, $total_mo, 1)}}%</sup>
        </div>
    </th>
</tr>
