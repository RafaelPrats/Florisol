<div style="overflow-x: scroll; overflow-y: scroll; width: 100%; max-height: 700px;">
    <table class="table-bordered" style="border: 1px solid #9d9d9d; width: 100%;" id="table_listado">
        <thead>
            <tr class="tr_fija_top_0">
                <th class="padding_lateral_5 th_yura_green">
                    FLOR
                </th>
                @php
                    $totales = [];
                    $total_inventario = 0;
                @endphp
                @foreach ($fechas as $f)
                    <th class="text-center th_yura_green th_fechas" data-fecha="{{ $f }}" style="width: 130px;">
                        {{ getDias(TP_ABREVIADO, FR_ARREGLO)[transformDiaPhp(date('w', strtotime($f)))] }}
                        <br>
                        <small>{{ convertDateToText($f) }}</small>
                    </th>
                    @php
                        $totales[] = [
                            'pedidos_ramos' => 0,
                            'pedidos_tallos' => 0,
                            'armados' => 0,
                        ];
                    @endphp
                @endforeach
                <th class="text-center bg-yura_dark" style="width: 60px">
                    INV.
                </th>
                <th class="text-center bg-yura_dark" style="width: 70px">
                    ARMADOS
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach ($listado as $item)
                @php
                    $total_inventario_flor = getTotalInventarioByVariedad($item['flor']->id_variedad);
                    $total_inventario += $total_inventario_flor;
                    $total_armados_flor = 0;
                @endphp
                <tr onmouseover="$(this).addClass('bg-yura_dark')" onmouseleave="$(this).removeClass('bg-yura_dark')">
                    <th class="padding_lateral_5 mouse-hand" style="border-color: #9d9d9d;"
                        onclick="modal_flor('{{ $item['flor']->id_variedad }}')">
                        {{ $item['flor']->nombre }}
                    </th>
                    @php
                        $acumulado_inventario = $total_inventario_flor > 0 ? $total_inventario_flor : 0;
                    @endphp
                    @foreach ($fechas as $pos_f => $fecha)
                        @php
                            $valor = '';
                            foreach ($item['valores'] as $val) {
                                if ($val->fecha == $fecha) {
                                    $valor = $val;
                                }
                            }
                            if ($valor != '') {
                                $total_armados_flor += $valor != '' ? $valor->armados : 0;
                                $totales[$pos_f]['pedidos_ramos'] += $valor->ramos;
                                $totales[$pos_f]['pedidos_tallos'] += $valor->tallos;
                                $totales[$pos_f]['armados'] += $valor->armados;
                            }
                        @endphp
                        <td class="text-center"
                            style="border-color: #9d9d9d; background-color: {{ $pos_f % 2 == 0 ? '#dddddd' : '' }};">
                            @if ($valor != '')
                                @php
                                    $por_armar = $valor->tallos - $valor->armados;

                                    $disponibles = 0;
                                    if ($acumulado_inventario >= $por_armar) {
                                        $disponibles = $por_armar;
                                        $acumulado_inventario -= $por_armar;
                                    } else {
                                        $disponibles = $acumulado_inventario;
                                        $acumulado_inventario = 0;
                                    }
                                @endphp
                                <div class="btn-group">
                                    <button class="btn btn-xs btn-yura_dark" title="Ramos">
                                        {{ $valor->ramos }}
                                    </button>
                                    <button class="btn btn-xs btn-yura_warning" title="Tallos">
                                        {{ $valor->tallos }}
                                    </button>
                                    <button
                                        class="btn btn-xs btn-yura_{{ $valor->armados < $valor->ramos ? 'danger' : 'default' }}"
                                        title="Tallos por armar">
                                        {{ $por_armar }}
                                    </button>
                                    @if ($disponibles > 0)
                                        <button class="btn btn-xs btn-yura_info" title="Disponibles">
                                            {{ $disponibles }}
                                        </button>
                                    @endif
                                </div>
                            @endif
                        </td>
                    @endforeach
                    <th class="text-center" style="border-color: #9d9d9d;">
                        {{ $total_inventario_flor > 0 ? $total_inventario_flor : 0 }}
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d;">
                        {{ $total_armados_flor }}
                    </th>
                </tr>
            @endforeach
        </tbody>
        <tr class="tr_fija_bottom_0">
            <th class="padding_lateral_5 th_yura_green">
                Totales
            </th>
            @php
                $total_armados = 0;
            @endphp
            @foreach ($totales as $v)
                @php
                    $total_armados += $v['armados'];
                    $por_armar = $v['pedidos_tallos'] - $v['armados'];
                    $disponibles = 0;
                @endphp
                <th class="text-center" style="background-color: #eeeeee; border-color: #9d9d9d;">
                    <div class="btn-group">
                        <button class="btn btn-xs btn-yura_dark" title="Ramos">
                            {{ $v['pedidos_ramos'] }}
                        </button>
                        <button class="btn btn-xs btn-yura_warning" title="Tallos">
                            {{ $v['pedidos_tallos'] }}
                        </button>
                        <button
                            class="btn btn-xs btn-yura_{{ $v['armados'] < $v['pedidos_tallos'] ? 'danger' : 'default' }}"
                            title="Tallos por armar">
                            {{ $por_armar }}
                        </button>
                        <button class="btn btn-xs btn-yura_info" title="Disponibles">
                            {{ $disponibles }}
                        </button>
                    </div>
                </th>
            @endforeach
            <th class="text-center th_yura_green">
                {{ $total_inventario }}
            </th>
            <th class="text-center th_yura_green">
                {{ $total_armados }}
            </th>
        </tr>
    </table>
</div>

<style type="text/css">
    .tr_fija_top_1 {
        position: sticky;
        top: 23px;
        z-index: 8;
    }

    .tr_fija_bottom_0 {
        position: sticky;
        bottom: 0;
        z-index: 9;
    }
</style>

<script>
    function modal_flor(variedad) {
        fechas = [];
        th_fechas = $('.th_fechas');
        for (i = 0; i < th_fechas.length; i++) {
            fechas.push(th_fechas[i].getAttribute('data-fecha'));
        }
        datos = {
            fechas: JSON.stringify(fechas),
            variedad: variedad,
        }
        get_jquery('{{ url('preproduccion/modal_flor') }}', datos, function(retorno) {
            modal_view('modal_modal_flor', retorno, '<i class="fa fa-fw fa-plus"></i> Pedidos de la Flor',
                true, false, '{{ isPC() ? '75%' : '' }}',
                function() {});
        })
    }
</script>
