@foreach($centros_costos as $i => $item)
    @if(count($item['mano_obra']) > 0)
        {{-- PERSONAL --}}
        <tr class="tr_rrhh hidden">
            <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
            <span style="margin: auto 5px; color: #585858; font-size: 0.85em; font-weight: bold">
                {{$item['area']->nombre}}
            </span>
            </th>
            @foreach($semanas as $pos_sem => $sem)
                @php
                    $valor = 0;
                @endphp
                @foreach($item['mano_obra'] as $v)
                    @php
                        if($sem->codigo == $v->codigo_semana)
                            $valor = $v->cantidad;
                    @endphp
                @endforeach
                <td class="text-center" style="border-color: #9d9d9d; background-color: #ff8d3c3b" title="Personal">
                    {{number_format($valor, 2)}}
                </td>
                @php
                    $totales_personal[$pos_sem] += $valor;
                @endphp
            @endforeach
            <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d" title="Personal">
            </th>
        </tr>
        {{-- Personal / ha --}}
        <tr class="tr_rrhh hidden">
            <td class="text-right th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
                    <span style="margin: auto 5px; color: black; font-size: 0.85em">
                        PERSONAL/Ha
                    </span>
            </td>
            @foreach($semanas as $pos_sem => $sem)
                @php
                    $valor = 0;
                @endphp
                @foreach($item['mano_obra'] as $v)
                    @php
                        if($sem->codigo == $v->codigo_semana)
                            $valor = $v->cantidad / ($resumen_area[$pos_sem] / 10000);
                    @endphp
                @endforeach
                <td class="text-center" style="border-color: #9d9d9d">
                    {{number_format($valor, 2)}}
                </td>
            @endforeach
            <td class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
            </td>
        </tr>
    @endif
@endforeach
{{-- TOTAL RRHH --}}
<tr class="tr_rrhh hidden">
    <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
        <span style="margin: auto 5px; color: black; font-size: 0.85em">
            PERSONAL TOTAL
        </span>
    </th>
    @foreach($totales_personal as $pos => $v)
        <th class="text-center" style="border-color: #9d9d9d">
            {{number_format($v, 2)}}
        </th>
    @endforeach
    <td class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
    </td>
</tr>
{{-- TOTAL PERSONAL / Ha --}}
<tr class="tr_rrhh hidden">
    <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
        <span style="margin: auto 5px; color: black; font-size: 0.85em">
            TOTAL PERSONAL/Ha
        </span>
    </th>
    @foreach($totales_personal as $pos => $v)
        <th class="text-center" style="border-color: #9d9d9d">
            {{number_format($v / ($resumen_area[$pos] / 10000), 2)}}
        </th>
    @endforeach
    <td class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
    </td>
</tr>