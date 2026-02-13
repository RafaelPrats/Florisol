@foreach($centros_costos as $i => $item)
    {{-- TOTAL AREA --}}
    <tr class="tr_centro_costos hidden">
        <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
            <span style="margin: auto 5px; color: #585858; font-size: 0.85em; font-weight: bold">
                {{$item['area']->nombre}}
            </span>
        </th>
        @php
            $total_area = 0;
        @endphp
        @foreach($semanas as $i => $sem)
            @php
                $mano_obra = (count($item['mano_obra']) > $i) ? $item['mano_obra'][$i]->valor : 0;
                $insumos = count($item['insumos']) > $i ? $item['insumos'][$i]->valor : 0;
                $fijos = count($item['otros_gastos']) > $i ? ($item['otros_gastos'][$i]->gip + $item['otros_gastos'][$i]->ga) : 0;
                $total_area += $insumos + $mano_obra + $fijos;
            @endphp
            <th class="text-center" style="border-color: #9d9d9d; background-color: #ff8d3c3b">
                ${{number_format($insumos + $mano_obra + $fijos, 2)}}
            </th>
        @endforeach
        <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
            ${{number_format($total_area, 2)}}
        </th>
    </tr>
    @if(count($item['mano_obra']) > 0)
        {{-- MANO OBRA --}}
        <tr class="tr_area_{{$item['area']->id_area}} tr_centro_costos hidden">
            <td class="text-right th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
                    <span style="margin: auto 5px; color: black; font-size: 0.85em">
                        MANO OBRA
                    </span>
            </td>
            @php
                $total_mano_obra = 0;
            @endphp
            @foreach($semanas as $pos_sem => $sem)
                @php
                    $valor = 0;
                @endphp
                @foreach($item['mano_obra'] as $v)
                    @php
                        if($sem->codigo == $v->codigo_semana)
                            $valor = $v->valor;
                    @endphp
                @endforeach
                <td class="text-center" style="border-color: #9d9d9d">
                    ${{number_format($valor, 2)}}
                </td>
                @php
                    $total_mano_obra += $valor;
                @endphp
            @endforeach
            <td class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
                ${{number_format($total_mano_obra, 2)}}
            </td>
        </tr>
    @endif
    @if(count($item['insumos']) > 0)
        {{-- INSUMOS --}}
        <tr class="tr_area_{{$item['area']->id_area}} tr_centro_costos hidden">
            <td class="text-right th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
                <span style="margin: auto 5px; color: black; font-size: 0.85em">
                    INSUMOS
                </span>
            </td>
            @php
                $total_insumos = 0;
            @endphp
            @foreach($semanas as $pos_sem => $sem)
                @php
                    $valor = 0;
                @endphp
                @foreach($item['insumos'] as $v)
                    @php
                        if($sem->codigo == $v->codigo_semana)
                            $valor = $v->valor;
                    @endphp
                @endforeach
                <td class="text-center" style="border-color: #9d9d9d">
                    ${{number_format($valor, 2)}}
                </td>
                @php
                    $total_insumos += $valor;
                @endphp
            @endforeach
            <td class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
                ${{number_format($total_insumos, 2)}}
            </td>
        </tr>
    @endif
    {{-- FIJOS --}}

    <tr class="tr_area_{{$item['area']->id_area}} tr_centro_costos hidden">
        <td class="text-right th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
                <span style="margin: auto 5px; color: black; font-size: 0.85em">
                    FIJOS
                </span>
        </td>
        @php
            $total_fijos = 0;
        @endphp
        @foreach($item['otros_gastos'] as $v)
            <td class="text-center" style="border-color: #9d9d9d">
                ${{number_format($v->gip + $v->ga, 2)}}
            </td>
            @php
                $total_fijos += $v->gip + $v->ga;
            @endphp
        @endforeach
        <td class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
            ${{number_format($total_fijos, 2)}}
        </td>
    </tr>
    {{-- REGALIAS --}}
    <tr class="tr_area_{{$item['area']->id_area}} tr_centro_costos hidden">
        <td class="text-right th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
                <span style="margin: auto 5px; color: black; font-size: 0.85em">
                    REGALÍAS
                </span>
        </td>
        @php
            $total_regalias = 0;
        @endphp
        @foreach($item['otros_gastos'] as $v)
            <td class="text-center" style="border-color: #9d9d9d">
                ${{number_format($v->regalias, 2)}}
            </td>
            @php
                $total_regalias += $v->regalias;
            @endphp
        @endforeach
        <td class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
            ${{number_format($total_regalias, 2)}}
        </td>
    </tr>
@endforeach