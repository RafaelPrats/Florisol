<th class="text-center padding_lateral_5 columna_fija_left_0"
    style="border-color: #9d9d9d; background-color: white !important; color: black !important"
    id="tr_acumulado_{{ $model_variedad->id_variedad }}">
    {{ $model_variedad->planta->nombre }}
    <input type="hidden" class="ids_variedad" value="{{ $model_variedad->id_variedad }}">
</th>
<th class="text-center padding_lateral_5 columna_fija_left_1"
    style="border-color: #9d9d9d; background-color: white !important; color: black !important">
    {{ $model_variedad->nombre }}
</th>
@php
    $total_combinacion = 0;
    $total_compra_flor = 0;
    $total_recepcion = 0;
@endphp
@foreach ($valores_compra_flor as $pos_v => $v)
    @php
        $total_combinacion += $v;
        $total_compra_flor += $v;
    @endphp
    <td class="text-center padding_lateral_5"
        style="background-color: #dddddd !important; color: black !important; border-color: #9d9d9d"
        title="{{ convertDateToText($fechas_compra_flor[$pos_v]) }}">
        @if ($v > 0)
            {{ number_format($v) }}
        @endif
    </td>
@endforeach
@foreach ($valores_recepcion as $pos_v => $v)
    @php
        $total_combinacion += $model_variedad->dias_rotacion_recepcion != '' && $model_variedad->dias_rotacion_recepcion <= $pos_v ? 0 : $v;
        $total_recepcion += $model_variedad->dias_rotacion_recepcion != '' && $model_variedad->dias_rotacion_recepcion <= $pos_v ? 0 : $v;
        $color_text = 'black';
        if ($model_variedad->dias_rotacion_recepcion != '') {
            if ($model_variedad->dias_rotacion_recepcion <= $pos_v) {
                $color_text = 'red';
            } elseif ($model_variedad->dias_rotacion_recepcion - 1 == $pos_v) {
                $color_text = 'blue';
            }
        }
    @endphp
    <td class="text-center padding_lateral_5"
        style="background-color: #dddddd !important; color: black !important; border-color: #9d9d9d"
        title="{{ convertDateToText($fechas_recepcion[$pos_v]) }}">
        @if ($v > 0)
            <b style="color: {{ $color_text }}">
                {{ number_format($v) }}
            </b>
        @endif
    </td>
@endforeach
@php
    $necesidad = $total_combinacion - $venta + $armados;
@endphp
<th class="text-center padding_lateral_5" style="border-color: #9d9d9d">
    {{ number_format($total_compra_flor) }}
</th>
<th class="text-center padding_lateral_5" style="border-color: #9d9d9d">
    {{ number_format($total_recepcion) }}
</th>

@if (count($fechas_ventas) > 1)
    @php
        $ventas_acum = 0;
        $armados_acum = 0;
        $compras_acum = 0;
        $necesidad_anterior = 0;
        $valores_necesidades = [];
    @endphp
    @foreach ($valores_ventas as $pos_v => $v)
        @php
            $ventas_acum += $v;
            $armados_acum += $valores_armados[$pos_v];
            $compras_acum += $valores_compras[$pos_v];
        @endphp
        <td class="text-center padding_lateral_5 celda_pedidos_actuales_{{ $pos_v }}"
            id="celda_ventas_{{ $model_variedad->id_variedad }}_{{ $pos_v }}"
            style="border-color: #9d9d9d; background-color: #dddddd !important; color: black !important">
            <div class="btn-group">
                @if ($v > 0)
                    <button type="button" class="btn btn-xs btn-yura_dark btn_pedidos_actuales_{{ $pos_v }}"
                        title="Pedidos actuales" data-variedad="{{ $model_variedad->id_variedad }}"
                        id="btn_pedidos_actuales_{{ $model_variedad->id_variedad }}_{{ $pos_v }}">
                        {{ number_format($v) }}
                    </button>
                @endif
                @if (session('id_usuario') == 1)
                    <button type="button" class="btn btn-xs btn-yura_warning" title="Refrescar"
                        onclick="refrescar_ventas('{{ $model_variedad->id_variedad }}', '{{ $pos_v }}')">
                        <i class="fa fa-fw fa-refresh"></i>
                    </button>
                @endif
            </div>
        </td>
        <td class="text-center padding_lateral_5"
            style="border-color: #9d9d9d; background-color: #dddddd !important; color: black !important">
            @if ($valores_armados[$pos_v] > 0)
                <b>{{ number_format($valores_armados[$pos_v]) }}</b>
            @endif
        </td>
        <td class="text-center padding_lateral_5"
            style="border-color: #9d9d9d; background-color: #dddddd !important; color: black !important; border-right-width: 2px">
            @php
                $recepcion_disponible = 0;
                foreach ($valores_recepcion as $pos_r => $r) {
                    if ($r > 0) {
                        $dias_disponible = $model_variedad->dias_rotacion_recepcion - $pos_r;
                        $fecha_disponible = opDiasFecha('+', $dias_disponible, hoy());
                        if ($fecha_disponible > $fechas_ventas[$pos_v]) {
                            $recepcion_disponible += $r;
                        }
                    }
                }
                $necesidad_fecha = $recepcion_disponible + $compras_acum - $ventas_acum + $armados_acum;
                if ($necesidad_anterior < 0) {
                    $diferencia = $necesidad_fecha - $necesidad_anterior;
                } else {
                    $diferencia = $necesidad_fecha;
                }
                $necesidad_anterior = $necesidad_fecha;
                $valores_necesidades[] = $diferencia;
            @endphp
            @if ($v > 0)
                <span class="{{ $diferencia < 0 ? 'error' : '' }}">
                    {{ $diferencia }}
                </span>
            @endif
        </td>
    @endforeach
@endif

<th class="text-center padding_lateral_5" style="border-color: #9d9d9d">
    @if ($venta > 0)
        <button type="button" class="btn btn-xs btn-yura_dark" title="Ver Pedidos"
            onclick="detalle_ventas('{{ $model_variedad->id_variedad }}', '{{ $venta }}')">
            {{ number_format($venta) }}
        </button>
    @endif
</th>
<th class="text-center padding_lateral_5" style="border-color: #9d9d9d">
    <div class="btn-group">
        <button class="btn btn-xs btn-yura_dark" title="Armados">
            {{ number_format($armados) }}
        </button>
        @php
            $por_armar = $venta - $armados;
        @endphp
        @if ($por_armar > 0)
            <button class="btn btn-xs btn-yura_danger" title="Faltante">
                {{ number_format($por_armar) }}
            </button>
        @elseif($por_armar < 0)
            <button class="btn btn-xs btn-yura_primary" title="Sobrante">
                {{ number_format(abs($por_armar)) }}
            </button>
        @else
            <button class="btn btn-xs btn-yura_default">
                {{ number_format(abs($por_armar)) }}
            </button>
        @endif
    </div>
</th>
@php
    $necesidad_global = 0;
    for ($i = count($valores_necesidades) - 1; $i >= 0; $i--) {
        if ($valores_necesidades[$i] <= 0) {
            $necesidad_global += $valores_necesidades[$i];
        } else {
            break;
        }
    }
@endphp
<th class="text-center padding_lateral_5 {{ $necesidad_global < 0 ? 'error' : '' }}" style="border-color: #9d9d9d">
    @if ($necesidad_global < 0)
        {{ number_format($necesidad_global) }}
    @endif
</th>
