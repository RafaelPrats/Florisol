<div class="preproduccion-table-wrap">
    <table class="table-bordered preproduccion-table" id="table_listado">
        <thead>
            <tr class="tr_fija_top_0">
                <th class="padding_lateral_5 th_yura_green">
                    <div style="width: 230px">
                        FLOR
                    </div>
                </th>
                @php
                    $totales = [];
                    $total_inventario_ventas = 0;
                    $total_inventario_produccion = 0;
                @endphp
                @foreach ($fechas as $f)
                    <th class="text-center th_yura_green th_fechas" data-fecha="{{ $f }}" style="width: 250px;">
                        {{ getDias(TP_ABREVIADO, FR_ARREGLO)[transformDiaPhp(date('w', strtotime($f)))] }}
                        <br>
                        <small>{{ convertDateToText($f) }}</small>
                    </th>
                    @php
                        $totales[] = [
                            'pedidos_ramos' => 0,
                            'pedidos_tallos' => 0,
                            'armados' => 0,
                            'disponibles' => 0,
                        ];
                    @endphp
                @endforeach
                <th class="text-center bg-yura_dark" style="width: 60px">
                    INV. VENTAS
                </th>
                <th class="text-center bg-yura_dark" style="width: 60px">
                    INV. PROD.
                </th>
                <th class="text-center bg-yura_dark" style="width: 70px">
                    ARMADOS
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach ($listado as $item)
                @php
                    $getTotalInventarioByVariedadBodega = getTotalInventarioByVariedadBodega(
                        $item['flor']->id_variedad,
                    );
                    $inventario_ventas = 0;
                    $inventario_produccion = 0;
                    foreach ($getTotalInventarioByVariedadBodega as $v) {
                        if ($v->bodega == 'V') {
                            $inventario_ventas += $v->cantidad;
                        } else {
                            $inventario_produccion = $v->cantidad;
                        }
                    }
                    $total_inventario_flor = $inventario_ventas + $inventario_produccion;
                    $total_inventario_ventas += $inventario_ventas;
                    $total_inventario_produccion += $inventario_produccion;
                    $total_armados_flor = 0;
                @endphp
                <tr>
                    <th class="padding_lateral_5 mouse-hand" title="Ver detalle de flor"
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
                        <td class="text-center preproduccion-cell-date {{ $pos_f % 2 == 0 ? 'preproduccion-date-even' : '' }}">
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
                                    $totales[$pos_f]['disponibles'] += $disponibles;
                                @endphp
                                <div class="btn-group">
                                    <button class="btn btn-xs btn-yura_dark" title="Ramos">
                                        {{ $valor->ramos }}
                                    </button>
                                    <button class="btn btn-xs btn-yura_warning" title="Tallos">
                                        {{ $valor->tallos }}
                                    </button>
                                    <button
                                        class="btn btn-xs btn-yura_{{ $valor->armados < $valor->tallos ? 'danger' : 'default' }}"
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
                    <th class="text-center preproduccion-inventory">
                        {{ $inventario_ventas > 0 ? $inventario_ventas : 0 }}
                    </th>
                    <th class="text-center preproduccion-inventory">
                        {{ $inventario_produccion > 0 ? $inventario_produccion : 0 }}
                    </th>
                    <th class="text-center preproduccion-inventory">
                        {{ $total_armados_flor }}
                    </th>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
        <tr class="tr_fija_bottom_0 preproduccion-row-total">
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
                @endphp
                <th class="text-center preproduccion-cell-date">
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
                        @if ($v['disponibles'] > 0)
                            <button class="btn btn-xs btn-yura_info" title="Disponibles">
                                {{ $v['disponibles'] }}
                            </button>
                        @endif
                    </div>
                </th>
            @endforeach
            <th class="text-center th_yura_green">
                {{ $total_inventario_ventas }}
            </th>
            <th class="text-center th_yura_green">
                {{ $total_inventario_produccion }}
            </th>
            <th class="text-center th_yura_green">
                {{ $total_armados }}
            </th>
        </tr>
        </tfoot>
    </table>
</div>

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
            cliente: $('#cliente_filtro').val(),
        }
        get_jquery('{{ url('preproduccion/modal_flor') }}', datos, function(retorno) {
            modal_view('modal_modal_flor', retorno, '<i class="fa fa-fw fa-leaf"></i> Pedidos de la Flor',
                true, false, '{{ isPC() ? '95%' : '' }}',
                function() {});
        })
    }
</script>
