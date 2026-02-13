<div style="overflow-x: scroll; overflow-y: scroll; max-height: 700px">
    <table class="table-bordered" style="border: 1px solid #9d9d9d; width: 100%" id="table_listado">
        <thead>
            <tr class="tr_fija_top_0">
                <th class="padding_lateral_5 th_yura_green" rowspan="2">
                    <div style="min-width: 130px">
                        Planta
                    </div>
                </th>
                <th class="padding_lateral_5 th_yura_green" rowspan="2">
                    <div style="min-width: 170px">
                        Variedad
                    </div>
                </th>
                @php
                    $totales_fecha = [];
                @endphp
                @foreach ($fechas as $f)
                    <th class="text-center th_yura_green" colspan="2">
                        {{ $f }}
                    </th>
                    @php
                        $totales_fecha[] = [
                            'compra' => 0,
                            'finca' => 0,
                        ];
                    @endphp
                @endforeach
                <th class="text-center th_yura_green" rowspan="2">
                    <div style="min-width: 80px">
                        TOTAL COMPRA
                    </div>
                </th>
                <th class="text-center th_yura_green" rowspan="2">
                    <div style="min-width: 80px">
                        TOTAL FINCA
                    </div>
                </th>
            </tr>
            <tr class="tr_fija_top_1">
                @foreach ($fechas as $f)
                    <th class="text-center text-center bg-yura_dark">
                        COMPRA
                    </th>
                    <th class="text-center text-center bg-yura_dark">
                        FINCA
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($listado as $pos_i => $item)
                @php
                    $compra_var = 0;
                    $finca_var = 0;
                @endphp
                <tr class="tr_listado">
                    <th class="padding_lateral_5" style="border-color: #9d9d9d">
                        {{ $item['planta']->nombre }}
                    </th>
                    <th class="padding_lateral_5" style="border-color: #9d9d9d; border-right: 2px solid black">
                        {{ $item['var']->var_nombre }}
                    </th>
                    @foreach ($fechas as $pos_f => $f)
                        @php
                            $compra = 0;
                            foreach ($item['valores_comprados'] as $c) {
                                if ($c->fecha == $f) {
                                    $compra = $c->cantidad;
                                }
                            }
                            $finca = 0;
                            foreach ($item['valores_finca'] as $c) {
                                if ($c->fecha == $f) {
                                    $finca = $c->cantidad;
                                }
                            }
                            $compra_var += $compra;
                            $finca_var += $finca;
                            $totales_fecha[$pos_f]['compra'] += $compra;
                            $totales_fecha[$pos_f]['finca'] += $finca;
                        @endphp
                        <th class="text-center"
                            style="border-color: #9d9d9d; background-color: {{ $pos_f % 2 == 0 ? '#eeeeee' : '' }};">
                            {{ $compra }}
                        </th>
                        <th class="text-center"
                            style="border-color: #9d9d9d; border-right: 2px solid black; background-color: {{ $pos_f % 2 == 0 ? '#eeeeee' : '' }};">
                            {{ $finca }}
                        </th>
                    @endforeach
                    <th class="text-center" style="border-color: #9d9d9d; background-color: #dddddd">
                        {{ $compra_var }}
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d; background-color: #dddddd">
                        {{ $finca_var }}
                    </th>
                </tr>
            @endforeach
        </tbody>
        <tr class="tr_fija_bottom_0">
            <th class="padding_lateral_5 th_yura_green" colspan="2">
                TOTALES
            </th>
            @php
                $total_compra = 0;
                $total_finca = 0;
            @endphp
            @foreach ($totales_fecha as $val)
                <th class="text-center bg-yura_dark">
                    {{ number_format($val['compra']) }}
                </th>
                <th class="text-center bg-yura_dark">
                    {{ number_format($val['finca']) }}
                </th>
                @php
                    $total_compra += $val['compra'];
                    $total_finca += $val['finca'];
                @endphp
            @endforeach
            <th class="text-center th_yura_green">
                {{ number_format($total_compra) }}
            </th>
            <th class="text-center th_yura_green">
                {{ number_format($total_finca) }}
            </th>
        </tr>
    </table>
</div>

<script type="text/javascript">
    estructura_tabla('table_listado');
</script>
