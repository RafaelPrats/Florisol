<div style="overflow-x: scroll">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d" id="table_select_planta_diario">
        <thead>
            <tr>
                <th class="text-center th_yura_green col_fija_left_0" rowspan="2"
                    style="left:0 !important; position: sticky !important;">
                    <div style="width: 180px">
                        Fechas SEMANA {{ $semana->codigo }}
                    </div>
                </th>
                @foreach ($fechas as $f)
                    <th class="text-center bg-yura_dark" colspan="2">
                        {{ getDias(TP_ABREVIADO, FR_ARREGLO)[transformDiaPhp(date('w', strtotime($f)))] }}<br>
                        {{ $f }}
                    </th>
                @endforeach
                <th class="text-center bg-yura_dark" colspan="2">
                    <div style="width: 90px" class="text-center">
                        TOTAL
                    </div>
                </th>
            </tr>
            <tr>
                @foreach ($fechas as $f)
                    <th class="text-center bg-yura_dark">
                        RAMOS
                    </th>
                    <th class="text-center bg-yura_dark">
                        VENTAS
                    </th>
                @endforeach
                <th class="text-center bg-yura_dark">
                    RAMOS
                </th>
                <th class="text-center bg-yura_dark">
                    VENTAS
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <th class="padding_lateral_5 col_fija_left_0" style="background-color: #dddddd; border-color: #9d9d9d">
                    {{ $receta->siglas }}: {{ $receta->nombre }}
                </th>
                @php
                    $total_item = 0;
                    $total_precio = 0;
                @endphp
                @foreach ($listado as $pos_dia => $dia)
                    @php
                        $total_item += $dia['valor'];
                        $total_precio += round($dia['valor'] * $dia['precio'], 2);
                    @endphp
                    <th class="text-center" style="border-color: #9d9d9d">
                        {{ number_format($dia['valor']) }}
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        ${{ number_format($dia['valor'] * $dia['precio'], 2) }}
                    </th>
                @endforeach
                <th class="text-center" style="border-color: #9d9d9d; background-color: #dddddd">
                    {{ number_format($total_item) }}
                </th>
                <th class="text-center" style="border-color: #9d9d9d; background-color: #dddddd">
                    ${{ number_format($total_precio, 2) }}
                </th>
            </tr>
        </tbody>
    </table>
</div>

<script>
    estructura_tabla('table_select_planta_diario')
    $('#table_select_planta_diario_filter>label>input').addClass('input-yura_default');
</script>
