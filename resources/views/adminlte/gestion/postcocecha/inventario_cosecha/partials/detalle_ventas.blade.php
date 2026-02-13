<legend style="font-size: 1em; margin-bottom: 2px" class="text-center">
    Pedidos de la variedad "<b>{{ $variedad->nombre }}</b>"
</legend>
<div style="overflow-y: scroll; max-height: 500px">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
        <tr class="tr_fija_top_0">
            <th class="text-center th_yura_green">
                PEDIDO
            </th>
            <th class="text-center th_yura_green">
                RECETA
            </th>
            @php
                $totales_longitud = [];
            @endphp
            @foreach ($longitudes as $long)
                @php
                    $totales_longitud[] = 0;
                @endphp
                <th class="text-center bg-yura_dark">
                    {{ $long }}<sup>cm</sup>
                </th>
            @endforeach
            <th class="text-center th_yura_green" colspan="2">
                TOTAL
            </th>
        </tr>
        @foreach ($listado as $pos_p => $item)
            @foreach ($item['valores_detalle'] as $pos_d => $det)
                <tr onmouseover="$('.tr_pedido_{{ $item['pedido']->codigo }}').addClass('bg-yura_dark')"
                    onmouseleave="$('.tr_pedido_{{ $item['pedido']->codigo }}').removeClass('bg-yura_dark')"
                    class="tr_pedido_{{ $item['pedido']->codigo }}">
                    @if ($pos_d == 0)
                        <th class="text-center" style="border-color: #9d9d9d"
                            rowspan="{{ count($item['valores_detalle']) }}">
                            #{{ $item['pedido']->codigo }}
                            <br>
                            {{ $item['pedido']->nombre_cliente }}
                        </th>
                    @endif
                    <th class="text-center" style="border-color: #9d9d9d">
                        {{ $det['detalle']->nombre_variedad }}
                    </th>
                    @php
                        $total_receta = 0;
                    @endphp
                    @foreach ($longitudes as $pos_l => $long)
                        @php
                            $totales_longitud[$pos_l] += $det['valores_longitud'][$pos_l];
                            $total_receta += $det['valores_longitud'][$pos_l];
                        @endphp
                        <th class="text-center" style="border-color: #9d9d9d; background-color: #dddddd; color: black">
                            {{ $det['valores_longitud'][$pos_l] }}
                        </th>
                    @endforeach
                    <th class="text-center" style="border-color: #9d9d9d">
                        {{ $total_receta }}
                    </th>
                    @if ($pos_d == 0)
                        <th class="text-center" style="border-color: #9d9d9d"
                            rowspan="{{ count($item['valores_detalle']) }}">
                            {{ $item['total_pedido'] }}
                        </th>
                    @endif
                </tr>
            @endforeach
        @endforeach
        <tr class="tr_fija_bottom_0">
            <th class="text-center th_yura_green" colspan="2">
                TOTALES
            </th>
            @php
                $total = 0;
            @endphp
            @foreach ($totales_longitud as $v)
                @php
                    $total += $v;
                @endphp
                <th class="text-center bg-yura_dark">
                    {{ $v }}
                </th>
            @endforeach
            <th class="text-center th_yura_green" colspan="2">
                {{ $total }}
            </th>
        </tr>
    </table>
</div>
