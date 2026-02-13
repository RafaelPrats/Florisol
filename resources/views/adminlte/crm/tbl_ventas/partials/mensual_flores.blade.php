<div style="overflow-x: scroll; overflow-y: scroll; max-height: 700px">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d" id="table_global">
        <thead>
            <tr id="tr_fija_top_0">
                <th class="text-center th_yura_green" rowspan="2">
                    <div style="width: 250px" class="text-center">
                        Flores / Años
                    </div>
                </th>
                @foreach ($listado_annos as $a)
                    <th class="text-center th_yura_green" colspan="{{ (count($a['meses']) + 1) * 2 }}">
                        {{ $a['anno'] }}
                    </th>
                @endforeach
            </tr>
            <tr id="tr_fija_top_1">
                @php
                    $totales_annos = [];
                @endphp
                @foreach ($listado_annos as $a)
                    @php
                        $totales_meses = [];
                    @endphp
                    @foreach ($a['meses'] as $mes)
                        @php
                            $totales_meses[] = [
                                'suma' => 0,
                            ];
                        @endphp
                        <th class="text-center bg-yura_dark">
                            <div style="width: 80px" class="text-center">
                                {{ getMeses()[$mes - 1] }}
                            </div>
                        </th>
                        <th class="text-center bg-yura_dark">
                            %
                        </th>
                    @endforeach
                    @php
                        $totales_annos[] = $totales_meses;
                    @endphp
                    <th class="text-center bg-yura_dark">
                        <div style="width: 90px" class="text-center">
                            TOTAL <sup>{{ $a['anno'] }}</sup>
                        </div>
                    </th>
                    <th class="text-center bg-yura_dark">
                        %
                    </th>
                @endforeach
            </tr>
        </thead>
        @php
            foreach ($listado as $item) {
                foreach ($item['valores_anno'] as $pos_a => $a) {
                    foreach ($a['valores_meses'] as $pos_mes => $mes) {
                        $totales_annos[$pos_a][$pos_mes]['suma'] += $mes['valor'];
                    }
                }
            }
        @endphp
        <tbody>
            @foreach ($listado as $item)
                <tr>
                    <th class="padding_lateral_5 bg-yura_dark">
                        <a href="javascript:void(0)" style="color: white"
                            onclick="select_planta_mensual('{{ $item['planta']->id_planta }}')">
                            {{ $item['planta']->nombre }} <i class="fa fa-fw fa-caret-right"></i>
                        </a>
                    </th>
                    @foreach ($item['valores_anno'] as $pos_a => $a)
                        @php
                            $total_anno_item = 0;
                            $total_anno = 0;
                            foreach ($totales_annos[$pos_a] as $pos_mes => $mes) {
                                $total_anno += $mes['suma'];
                            }
                        @endphp
                        @foreach ($a['valores_meses'] as $pos_mes => $mes)
                            @php
                                $total_anno_item += $mes['valor'];
                            @endphp
                            <th class="text-center" style="border-color: #9d9d9d">
                                {{ number_format($mes['valor']) }}
                            </th>
                            <th class="text-center" style="border-color: #9d9d9d">
                                {{ porcentaje($mes['valor'], $totales_annos[$pos_a][$pos_mes]['suma'], 1) }}%
                            </th>
                        @endforeach
                        <th class="text-center" style="border-color: #9d9d9d; background-color: #dddddd">
                            {{ number_format($total_anno_item) }}
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d; background-color: #dddddd">
                            {{ porcentaje($total_anno_item, $total_anno, 1) }}%
                        </th>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
        <tr id="tr_fija_bottom_0">
            <th class="padding_lateral_5 th_yura_green">
                TOTALES
            </th>
            @foreach ($totales_annos as $t)
                @php
                    $total_anno = 0;
                    foreach ($t as $val) {
                        $total_anno += $val['suma'];
                    }
                @endphp
                @foreach ($t as $val)
                    <th class="text-center bg-yura_dark">
                        {{ number_format($val['suma']) }}
                    </th>
                    <th class="text-center bg-yura_dark">
                        {{ porcentaje($val['suma'], $total_anno, 1) }}%
                    </th>
                @endforeach
                <th class="text-center bg-yura_dark">
                    {{ number_format($total_anno) }}
                </th>
                <th class="text-center bg-yura_dark">
                    100%
                </th>
            @endforeach
        </tr>
    </table>
</div>

<style>
    #tr_fija_bottom_0 th {
        position: sticky;
        bottom: 0;
        z-index: 9;
    }

    #tr_fija_top_0 th {
        position: sticky;
        top: 0;
        z-index: 9;
    }

    #tr_fija_top_1 th {
        position: sticky;
        top: 21px;
        z-index: 9;
    }
</style>

<script>
    estructura_tabla('table_global');
    $('#table_global_filter>label>input').addClass('input-yura_default');

    function select_planta_mensual(planta) {
        datos = {
            planta: planta,
            desde_mensual: $('#desde_mensual').val(),
            hasta_mensual: $('#hasta_mensual').val(),
            annos: $('#annos').val(),
            cliente: $('#cliente').val(),
            criterio: $('#criterio').val(),
        }
        if (datos['desde_mensual'] <= datos['hasta_mensual']) {
            get_jquery('{{ url('tbl_ventas/select_planta_mensual') }}', datos, function(retorno) {
                modal_view('modal_select_planta_mensual', retorno,
                    '<i class="fa fa-fw fa-plus"></i> Desglose Flor Mensual',
                    true, false, '{{ isPC() ? '95%' : '' }}',
                    function() {});
            });
        }
    }

    function exportar_planta_mensual(planta) {
        datos = {
            planta: planta,
            desde_mensual: $('#desde_mensual').val(),
            hasta_mensual: $('#hasta_mensual').val(),
            annos: $('#annos').val(),
            cliente: $('#cliente').val(),
            criterio: $('#criterio').val(),
        }
        if (datos['desde_mensual'] <= datos['hasta_mensual']) {
            datos = JSON.stringify(datos);
            $.LoadingOverlay('show');
            window.open('{{ url('tbl_ventas/exportar_planta_mensual') }}' + '?datos=' + datos, '_blank');
            $.LoadingOverlay('hide');
        }
    }
</script>
