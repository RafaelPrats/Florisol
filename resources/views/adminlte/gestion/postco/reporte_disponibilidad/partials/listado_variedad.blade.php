<legend class="text-center" style="font-size: 1.3em; margin-bottom: 5px">
    <b>{{ $variedad->nombre }}</b> <sup><b>{{ $variedad->dias_rotacion_recepcion }}</b> días de rotacion</sup>
</legend>

<div class="row">
    <div class="col-md-6">
        <table class="table-bordered" style="border: 1px solid #9d9d9d; width: 100%">
            <tr>
                <th class="text-center th_yura_green" colspan="4">
                    Recepcion actual
                </th>
            </tr>
            @foreach ($recepciones as $pos => $r)
                @php
                    $fecha_exp = opDiasFecha('+', $variedad->dias_rotacion_recepcion, $r->fecha);
                @endphp
                <tr style="background-color: {{ $pos % 2 == 0 ? '#dddddd' : '' }}"
                    class="{{ $fecha_exp < $desde ? 'error' : '' }}"
                    title="{{ $fecha_exp < $desde ? 'Flor vencida' : '' }}">
                    <th class="text-center" style="width: 60px; border-color: #9d9d9d">
                        R{{ $pos + 1 }}
                    </th>
                    <th class="text-center" style="width: 60px; border-color: #9d9d9d">
                        {{ $r->cantidad }}
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        Ing: {{ $r->fecha }}
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        Exp: {{ $fecha_exp }}
                    </th>
                </tr>
            @endforeach
        </table>
    </div>
    <div class="col-md-6">
        <table class="table-bordered" style="border: 1px solid #9d9d9d; width: 100%">
            <tr>
                <th class="text-center th_yura_green" colspan="4">
                    Compras futuras
                </th>
            </tr>
            @foreach ($compras as $pos => $c)
                <tr style="background-color: {{ $pos % 2 == 0 ? '#dddddd' : '' }}">
                    <th class="text-center" style="width: 60px; border-color: #9d9d9d">
                        C{{ $pos + 1 }}
                    </th>
                    <th class="text-center" style="width: 60px; border-color: #9d9d9d">
                        {{ $c->cantidad }}
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        Ing: {{ $c->fecha }}
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        Exp: {{ opDiasFecha('+', $variedad->dias_rotacion_recepcion, $c->fecha) }}
                    </th>
                </tr>
            @endforeach
        </table>
    </div>
</div>

<table class="table-bordered" style="border: 1px solid #9d9d9d; width: 100%; margin-top: 5px">
    <tr>
        <th class="padding_lateral_5 th_yura_green" style="width: 50%">
            Fecha
        </th>
        @foreach ($recepciones as $pos => $r)
            <th class="text-center bg-yura_dark" style="width: 60px">
                R{{ $pos + 1 }}
            </th>
        @endforeach
        @foreach ($compras as $pos => $c)
            <th class="text-center bg-yura_dark" style="width: 60px">
                C{{ $pos + 1 }}
            </th>
        @endforeach
        <th class="text-center th_yura_green" style="width: 60px">
            Dispo.
        </th>
        <th class="text-center th_yura_green" style="width: 60px">
            Venta
        </th>
        <th class="text-center th_yura_green" style="width: 60px">
            Armados
        </th>
        <th class="text-center th_yura_green" style="width: 60px">
            Faltantes
        </th>
        <th class="text-center bg-yura_dark" style="width: 60px">
            Saldo
        </th>
        <th class="text-center" style="background-color: #d01c62; border-color: white; color: white"
            style="width: 60px">
            PERDIDAS
        </th>
    </tr>
    @php
        $meta = 0;
        $total_negativos = 0;
        $total_perdidas = 0;
        $total_ventas = 0;
    @endphp
    @foreach ($valores_postco as $pos_v => $v)
        @php
            $inventario = 0;
            $perdida = 0;
            $pos_perdidas_recepciones = [];
            $pos_perdidas_compras = [];
            for ($pos_r = 0; $pos_r < count($valores_recepciones); $pos_r++) {
                $r = $valores_recepciones[$pos_r];
                $fecha_exp = opDiasFecha('+', $variedad->dias_rotacion_recepcion, $recepciones[$pos_r]->fecha);
                if ($fecha_exp >= $desde) {
                    if (
                        $v['fecha'] >= opDiasFecha('+', $variedad->dias_rotacion_recepcion, $recepciones[$pos_r]->fecha)
                    ) {
                        if ($valores_recepciones[$pos_r] > 0) {
                            $perdida += $valores_recepciones[$pos_r];
                            $pos_perdidas_recepciones[] = $pos_r;
                        }
                        $valores_recepciones[$pos_r] = 0;
                        $r = 0;
                    }
                    if ($meta > 0 && $r > 0) {
                        if ($r >= $meta) {
                            $valores_recepciones[$pos_r] = $r - $meta;
                            $meta = 0;
                        } else {
                            $meta -= $r;
                            $valores_recepciones[$pos_r] = 0;
                        }
                    }
                    $inventario += $valores_recepciones[$pos_r];
                }
            }
            for ($pos_c = 0; $pos_c < count($valores_compras); $pos_c++) {
                $c = $valores_compras[$pos_c];
                if ($v['fecha'] >= opDiasFecha('+', $variedad->dias_rotacion_recepcion, $compras[$pos_c]->fecha)) {
                    if ($valores_compras[$pos_c] > 0) {
                        $perdida += $valores_compras[$pos_c];
                        $pos_perdidas_compras[] = $pos_c;
                    }
                    $valores_compras[$pos_c] = 0;
                    $c = 0;
                }
                if (
                    $meta > 0 &&
                    $c > 0 &&
                    $compras[$pos_c]->fecha <= $v['fecha'] &&
                    $v['fecha'] <= opDiasFecha('+', $variedad->dias_rotacion_recepcion, $compras[$pos_c]->fecha)
                ) {
                    if ($c >= $meta) {
                        $valores_compras[$pos_c] = $c - $meta;
                        $meta = 0;
                    } else {
                        $meta -= $c;
                        $valores_compras[$pos_c] = 0;
                    }
                }
                if (
                    $compras[$pos_c]->fecha <= $v['fecha'] &&
                    $v['fecha'] < opDiasFecha('+', $variedad->dias_rotacion_recepcion, $compras[$pos_c]->fecha)
                ) {
                    $inventario += $valores_compras[$pos_c];
                }
            }
            $venta = $v['venta'];
            $armados = $v['armados'];
            $faltante = $venta - $armados;
            $saldo = $inventario - $faltante;
            $meta = $faltante >= 0 ? $faltante : 0;
            $total_negativos += $saldo < 0 ? $saldo : 0;
            $total_perdidas += $perdida;
            $total_ventas += $venta;
        @endphp
        <tr onmouseover="$(this).addClass('bg-yura_dark')" onmouseleave="$(this).removeClass('bg-yura_dark')">
            <th class="padding_lateral_5" style="border-color: #9d9d9d">
                {{ convertDateToText($v['fecha']) }}
            </th>
            @foreach ($valores_recepciones as $pos_r => $r)
                <td class="text-center"
                    style="border-color: #9d9d9d; background-color: {{ in_array($pos_r, $pos_perdidas_recepciones) ? '#d01c62' : '#eeeeee' }}; color: black">
                    @if ($v['fecha'] < opDiasFecha('+', $variedad->dias_rotacion_recepcion, $recepciones[$pos_r]->fecha))
                        {{ $r }}
                    @else
                        -
                    @endif
                </td>
            @endforeach
            @foreach ($valores_compras as $pos_c => $c)
                <td class="text-center"
                    style="border-color: #9d9d9d; background-color: {{ in_array($pos_c, $pos_perdidas_compras) ? '#d01c62' : '#eeeeee' }}; color: black">
                    @if (
                        $compras[$pos_c]->fecha <= $v['fecha'] &&
                            $v['fecha'] < opDiasFecha('+', $variedad->dias_rotacion_recepcion, $compras[$pos_c]->fecha))
                        {{ $c }}
                    @else
                        -
                    @endif
                </td>
            @endforeach
            <th class="text-center" style="border-color: #9d9d9d">
                {{ $inventario }}
            </th>
            <th class="text-center" style="border-color: #9d9d9d">
                {{ $venta }}
            </th>
            <th class="text-center" style="border-color: #9d9d9d">
                {{ $armados }}
            </th>
            <th class="text-center" style="border-color: #9d9d9d">
                {{ $faltante }}
            </th>
            <th class="text-center"
                style="background-color: antiquewhite; border-color: #9d9d9d; color: {{ $saldo >= 0 ? 'black' : 'red' }}">
                {{ $saldo }}
            </th>
            <th class="text-center" style="border-color: #9d9d9d">
                @if ($perdida > 0)
                    {{ $perdida }}
                @endif
            </th>
        </tr>
    @endforeach
    <tr>
        <th class="text-right padding_lateral_5 th_yura_green"
            colspan="{{ 2 + count($compras) + count($recepciones) }}">
            TOTALES VENTAS y NEGATIVOS
        </th>
        <th class="text-center bg-yura_dark" colspan="3">
            <button type="button" class="btn btn-xs btn-yura_default"
                onclick="detalle_ventas('{{ $variedad->id_variedad }}')">
                {{ number_format($total_ventas) }}
            </button>
        </th>
        <th class="text-center bg-yura_dark">
            {{ $total_negativos }}
        </th>
        <th class="text-center" style="background-color: #d01c62; border-color: white; color: white">
            {{ $total_perdidas }}
        </th>
    </tr>
</table>

<legend style="font-size: 1em; margin-bottom: 5px" class="text-right">
    <b>Leyenda</b>
</legend>
<div style="font-size: 1em" class="text-right">
    <span class="badge" style="background-color: #d01c62; color: white;">Perdida por Dias de Rotacion</span>
</div>

<script type="text/javascript">
    function detalle_ventas(variedad) {
        datos = {
            _token: '{{ csrf_token() }}',
            variedad: variedad,
            hasta: $('#hasta_filtro').val(),
        }
        get_jquery('{{ url('reporte_disponibilidad/detalle_ventas') }}', datos, function(retorno) {
            modal_view('modal_detalle_ventas', retorno,
                '<i class="fa fa-fw fa-plus"></i> Detalle de los Pedidos',
                true, false, '{{ isPC() ? '90%' : '' }}',
                function() {});
        });
    }
</script>
