@php
    $total = 0;
    $colores_array = ['#00b388', '#30bbbb', '#ef6e11', '#d01c62'];
    foreach ($query as $q) {
        if ($criterio == 'A') {
            $total += $q->armados;
        }
        if ($criterio == 'C') {
            $total += $q->comprados;
        }
        if ($criterio == 'D') {
            $total += $q->desechados;
        }
        if ($criterio == 'R') {
            $total += $q->recibidos;
        }
    }
@endphp

@foreach ($query as $pos => $item)
    @php
        if ($criterio == 'A') {
            $valor = $item->armados;
        }
        if ($criterio == 'C') {
            $valor = $item->comprados;
        }
        if ($criterio == 'D') {
            $valor = $item->desechados;
        }
        if ($criterio == 'R') {
            $valor = $item->recibidos;
        }
    @endphp
    <div class="progress-group">
        <table style="width: 100%">
            <tr>
                <th>
                    {{ $item->nombre }} <sup>{{ porcentaje($valor, $total, 1) }}%</sup>
                </th>
                <td class="text-right">
                    {{ number_format($valor) }}
                </td>
            </tr>
        </table>

        <div class="progress progress-sm">
            <div class="progress-bar"
                style="width: {{ porcentaje($valor, $total, 1) }}%; background-color: {{ $colores_array[$pos] }}"></div>
        </div>
    </div>
@endforeach
