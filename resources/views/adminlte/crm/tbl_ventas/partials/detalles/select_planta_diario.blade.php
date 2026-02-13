<div style="overflow-x: scroll">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d" id="table_select_planta_diario">
        <thead>
            <tr>
                <th class="text-center th_yura_green col_fija_left_0"
                    style="left:0 !important; position: sticky !important;">
                    <div style="width: 180px">
                        Colores {{ $planta->nombre }}
                    </div>
                </th>
                @php
                    $array_totales_vacio = [];
                @endphp
                @foreach ($fechas as $f)
                    @php
                        $array_totales_vacio[] = [
                            'suma' => 0,
                        ];
                    @endphp
                    <th class="text-center bg-yura_dark">
                        <div style="width: 80px" class="text-center">
                            {{ getDias(TP_ABREVIADO, FR_ARREGLO)[transformDiaPhp(date('w', strtotime($f)))] }}<br>
                            {{ $f }}
                        </div>
                    </th>
                @endforeach
                <th class="text-center bg-yura_dark">
                    <div style="width: 90px" class="text-center">
                        TOTAL
                    </div>
                </th>
            </tr>
        </thead>
        <tbody>
            @php
                $totales = $array_totales_vacio;
            @endphp
            @foreach ($listado as $var)
                @php
                    $totales_long = $array_totales_vacio;
                @endphp
                <tr>
                    <th class="padding_lateral_5 col_fija_left_0"
                        style="background-color: #dddddd; border-color: #9d9d9d">
                        {{ $var['variedad']->nombre }}
                    </th>
                    @php
                        $total_item = 0;
                    @endphp
                    @foreach ($var['valores_fechas'] as $pos_dia => $dia)
                        @php
                            $total_item += $dia['valor'];
                            $totales_long[$pos_dia]['suma'] += $dia['valor'];
                            $totales[$pos_dia]['suma'] += $dia['valor'];
                        @endphp
                        <th class="text-center" style="border-color: #9d9d9d">
                            {{ number_format($dia['valor']) }}
                        </th>
                    @endforeach
                    <th class="text-center" style="border-color: #9d9d9d; background-color: #dddddd">
                        {{ number_format($total_item) }}
                    </th>
                </tr>
            @endforeach
        </tbody>
        <tr>
            <th class="padding_lateral_5 th_yura_green col_fija_left_0">
                TOTALES
            </th>
            @php
                $total = 0;
            @endphp
            @foreach ($totales as $val)
                @php
                    $total += $val['suma'];
                @endphp
                <th class="text-center th_yura_green">
                    {{ number_format($val['suma']) }}
                </th>
            @endforeach
            <th class="text-center th_yura_green">
                {{ number_format($total) }}
            </th>
        </tr>
    </table>
</div>

<script>
    estructura_tabla('table_select_planta_diario')
    $('#table_select_planta_diario_filter>label>input').addClass('input-yura_default');
</script>
