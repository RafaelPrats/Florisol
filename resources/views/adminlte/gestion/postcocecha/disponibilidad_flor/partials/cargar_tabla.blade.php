@foreach ($listado as $item)
    @php
        $num_filas++;
    @endphp
    <tr
        class="tr_listado {{ $item['total_negativos'] < 0 ? 'tr_negativo' : '' }} {{ $item['total_perdidas'] > 0 ? 'tr_perdida' : '' }}">
        <th class="padding_lateral_5" style="border-color: #9d9d9d">
            {{ $num_filas }}
        </th>
        <th class="padding_lateral_5" style="border-color: #9d9d9d">
            {{ $item['variedad']->planta->nombre }}
        </th>
        <th class="padding_lateral_5" style="border-color: #9d9d9d">
            {{ $item['variedad']->nombre }}
        </th>
        <th class="padding_lateral_5 bg-yura_dark" style="border-right: 2px solid black">
            {{ number_format($item['total_ventas']) }}
        </th>
        @foreach ($item['list_saldos'] as $pos_s => $s)
            <th class="padding_lateral_5 text-center"
                style="border-color: #9d9d9d; background-color: {{ $pos_s % 2 == 0 ? '#eeeeee' : '' }}; color: {{ $s < 0 ? 'red' : 'black' }}">
                {{ $s }}
            </th>
            <th class="padding_lateral_5 text-center"
                style="border-color: #9d9d9d; border-right: 2px solid black; background-color: {{ $pos_s % 2 == 0 ? '#eeeeee' : '' }}; color: black">
                {{ $item['list_perdidas'][$pos_s] }}
            </th>
        @endforeach
        <th class="padding_lateral_5 bg-yura_dark">
            @if ($item['total_negativos'] < 0)
                {{ number_format($item['total_negativos']) }}
            @endif
        </th>
        <th class="padding_lateral_5" style="background-color: #eeeeee; color: {{ $item['total_perdidas'] > 0 ? 'red' : '' }}; border-color: #9d9d9d">
            @if ($item['total_perdidas'] > 0)
                {{ number_format($item['total_perdidas']) }}
            @endif
        </th>
    </tr>
@endforeach

<script>
    pos_listado = parseInt($('#pos_listado').val());
    count_listado = parseInt($('#count_listado').val());
    if (pos_listado < count_listado) {
        setTimeout(() => {
            cargar_tabla();
        }, 500);
    } else {
        $('.btn_listar_reporte').prop('disabled', false);
    }
    $('#num_filas').val({{ $num_filas }});
</script>
