<div style="overflow-y: scroll; overflow-x: scroll; max-height: 700px">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d" id="table_listado">
        <thead>
            <tr class="tr_fija_top_0">
                <th class="padding_lateral_5 th_yura_green" rowspan="2">
                    <div style="width: 150px">
                        Planta
                    </div>
                </th>
                <th class="padding_lateral_5 th_yura_green" rowspan="2">
                    <div style="width: 250px">
                        Variedad
                    </div>
                </th>
                @php
                    $total_fechas = [];
                @endphp
                @foreach ($fechas as $f)
                    <th class="text-center th_yura_green" colspan="2" style="border-left: 2px solid white">
                        {{ explode(' del ', convertDateToText($f))[0] }}
                    </th>
                    @php
                        $total_fechas[] = [
                            'salidas' => 0,
                            'ventas' => 0,
                        ];
                    @endphp
                @endforeach
                <th class="text-center th_yura_green" colspan="2" style="border-left: 2px solid white">
                    Totales
                </th>
            </tr>
            <tr class="tr_fija_top_1">
                @foreach ($fechas as $f)
                    <th class="padding_lateral_5 bg-yura_dark" style="border-left: 2px solid white">
                        <div style="width: 70px">
                            Despacho
                        </div>
                    </th>
                    <th class="padding_lateral_5 bg-yura_dark">
                        <div style="width: 70px">
                            Venta
                        </div>
                    </th>
                @endforeach
                <th class="padding_lateral_5 bg-yura_dark" style="border-left: 2px solid white">
                    <div style="width: 70px">
                        Despacho
                    </div>
                </th>
                <th class="padding_lateral_5 bg-yura_dark">
                    <div style="width: 70px">
                        Venta
                    </div>
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach ($listado as $pos => $item)
                <tr onmouseover="$(this).css('background-color', 'cyan')"
                    onmouseleave="$(this).css('background-color', '')">
                    <th class="padding_lateral_5" style="border-color: #9d9d9d; background-color: #dddddd">
                        {{ $item->pta_nombre }}
                    </th>
                    <th class="padding_lateral_5" style="border-color: #9d9d9d; background-color: #dddddd">
                        {{ $item->var_nombre }}
                    </th>
                    @php
                        $ventas_var = 0;
                        $salidas_var = 0;
                    @endphp
                    @foreach ($fechas as $pos_f => $f)
                        @php
                            $ventas = 0;
                            $salidas = 0;
                            foreach ($item->ventas as $val) {
                                if ($val->fecha == $f) {
                                    $ventas += $val->monto;
                                }
                            }
                            foreach ($item->salidas_ot as $val) {
                                if ($val->fecha == $f) {
                                    $salidas += $val->cantidad;
                                }
                            }
                            foreach ($item->salidas_ot_nacional as $val) {
                                if ($val->fecha == $f) {
                                    $salidas += $val->cantidad;
                                }
                            }
                            foreach ($item->salidas_solido as $val) {
                                if ($val->fecha == $f) {
                                    $salidas += $val->cantidad;
                                }
                            }
                            $ventas_var += $ventas;
                            $salidas_var += $salidas;
                            $total_fechas[$pos_f]['ventas'] += $ventas;
                            $total_fechas[$pos_f]['salidas'] += $salidas;
                        @endphp
                        <th class="padding_lateral_5" style="border-color: #9d9d9d; border-left: 2px solid #9d9d9d">
                            {{ $salidas > 0 ? number_format($salidas) : '' }}
                        </th>
                        <th class="padding_lateral_5" style="border-color: #9d9d9d">
                            @if ($ventas > 0)
                                ${{ number_format($ventas, 2) }}
                            @endif
                        </th>
                    @endforeach
                    <th class="padding_lateral_5"
                        style="background-color: #dddddd; border-color: #9d9d9d; border-left: 2px solid #9d9d9d">
                        {{ number_format($salidas_var) }}
                    </th>
                    <th class="padding_lateral_5" style="background-color: #dddddd; border-color: #9d9d9d">
                        ${{ number_format($ventas_var, 2) }}
                    </th>
                </tr>
            @endforeach
        </tbody>
        <tr class="tr_fija_bottom_0">
            <th class="padding_lateral_5 th_yura_green" colspan="2">
                TOTALES
            </th>
            @php
                $total_ventas = 0;
                $total_salidas = 0;
            @endphp
            @foreach ($total_fechas as $val)
                <th class="padding_lateral_5 bg-yura_dark">
                    {{ number_format($val['salidas']) }}
                </th>
                <th class="padding_lateral_5 bg-yura_dark">
                    ${{ number_format($val['ventas'], 2) }}
                </th>
                @php
                    $total_ventas += $val['salidas'];
                    $total_salidas += $val['ventas'];
                @endphp
            @endforeach
            <th class="padding_lateral_5 th_yura_green">
                {{ number_format($total_ventas) }}
            </th>
            <th class="padding_lateral_5 th_yura_green">
                ${{ number_format($total_salidas, 2) }}
            </th>
        </tr>
    </table>
</div>

<script>
    estructura_tabla('table_listado');
</script>
