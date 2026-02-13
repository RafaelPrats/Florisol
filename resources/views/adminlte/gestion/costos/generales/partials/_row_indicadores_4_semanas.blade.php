<tr class="tr_indicadores_4_semanas hidden">
    <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
    <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em">
        Precio TOTAL x tallo
    </span>
    </th>
    @foreach($indicadores_4_semanas as $pos_s => $item)
        @php
            $value = $item->precio_x_tallo;
        @endphp
        <th class="text-center" style="border-color: #9d9d9d;">
            <div style="width: 100px">
                ${{number_format($value, 2)}}
            </div>
        </th>
    @endforeach
</tr>

<tr class="tr_indicadores_4_semanas hidden">
    <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
    <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em">
        Precio x tallo Bqt
    </span>
    </th>
    @foreach($indicadores_4_semanas as $pos_s => $item)
        @php
            $value = $item->precio_x_tallo_bqt;
        @endphp
        <th class="text-center" style="border-color: #9d9d9d;">
            <div style="width: 100px">
                ${{number_format($value, 2)}}
            </div>
        </th>
    @endforeach
</tr>

<tr class="tr_indicadores_4_semanas hidden">
    <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
    <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em">
        Venta/m<sup>2</sup>
    </span>
    </th>
    @foreach($indicadores_4_semanas as $pos_s => $item)
        @php
            $value = $item->venta_m2;
        @endphp
        <th class="text-center" style="border-color: #9d9d9d;">
            <div style="width: 100px">
                ${{number_format($value, 2)}}
            </div>
        </th>
    @endforeach
</tr>

<tr class="tr_indicadores_4_semanas hidden">
    <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
    <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em">
        Costos Totales/m<sup>2</sup>
    </span>
    </th>
    @foreach($indicadores_4_semanas as $pos_s => $item)
        @php
            $value = $item->costos_m2;
        @endphp
        <th class="text-center" style="border-color: #9d9d9d;">
            <div style="width: 100px">
                ${{number_format($value, 2)}}
            </div>
        </th>
    @endforeach
</tr>

<tr class="tr_indicadores_4_semanas hidden">
    <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
    <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em">
        EBITDA/m<sup>2</sup>
    </span>
    </th>
    @foreach($indicadores_4_semanas as $pos_s => $item)
        @php
            $value = $item->ebitda_m2;
        @endphp
        <th class="text-center" style="border-color: #9d9d9d;">
            <div style="width: 100px; color: {{$value < 0 ? '#D01C62' : '#00B388'}}">
                ${{number_format($value, 2)}}
            </div>
        </th>
    @endforeach
</tr>

<tr class="tr_indicadores_4_semanas hidden">
    <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
    <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em">
        Costos Finca/m<sup>2</sup>
    </span>
    </th>
    @foreach($indicadores_4_semanas as $pos_s => $item)
        @php
            $value = $item->costos_finca_m2;
        @endphp
        <th class="text-center" style="border-color: #9d9d9d;">
            <div style="width: 100px">
                ${{number_format($value, 2)}}
            </div>
        </th>
    @endforeach
</tr>

<tr class="tr_indicadores_4_semanas hidden">
    <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
    <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em">
        Propagación x tallo
    </span>
    </th>
    @foreach($indicadores_4_semanas as $pos_s => $item)
        @php
            $value = $item->propagacion_x_tallo;
        @endphp
        <th class="text-center" style="border-color: #9d9d9d;">
            <div style="width: 100px">
                ¢{{number_format($value, 2)}}
            </div>
        </th>
    @endforeach
</tr>

<tr class="tr_indicadores_4_semanas hidden">
    <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
    <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em">
        Cultivo x tallo
    </span>
    </th>
    @foreach($indicadores_4_semanas as $pos_s => $item)
        @php
            $value = $item->cultivo_x_tallo;
        @endphp
        <th class="text-center" style="border-color: #9d9d9d;">
            <div style="width: 100px">
                ¢{{number_format($value, 2)}}
            </div>
        </th>
    @endforeach
</tr>

<tr class="tr_indicadores_4_semanas hidden">
    <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
    <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em">
        Postcosecha x tallo
    </span>
    </th>
    @foreach($indicadores_4_semanas as $pos_s => $item)
        @php
            $value = $item->postcosecha_x_tallo;
        @endphp
        <th class="text-center" style="border-color: #9d9d9d;">
            <div style="width: 100px">
                ¢{{number_format($value, 2)}}
            </div>
        </th>
    @endforeach
</tr>

<tr class="tr_indicadores_4_semanas hidden">
    <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
    <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em">
        Costos Total x tallo
    </span>
    </th>
    @foreach($indicadores_4_semanas as $pos_s => $item)
        @php
            $value = $item->costos_total_x_tallo;
        @endphp
        <th class="text-center" style="border-color: #9d9d9d;">
            <div style="width: 100px">
                ¢{{number_format($value, 2)}}
            </div>
        </th>
    @endforeach
</tr>