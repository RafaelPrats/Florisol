@php
    $total_ingresos = 0;
    $total_salidas = 0;
    $saldo_final = $saldo;
    foreach ($listado as $movimiento) {
        if ($movimiento->tipo == 'INGRESO') {
            $total_ingresos += $movimiento->cantidad;
            $saldo_final += $movimiento->cantidad;
        } else {
            $total_salidas += $movimiento->cantidad;
            $saldo_final -= $movimiento->cantidad;
        }
    }
@endphp

<div class="kardex-results-card">
    <div class="kardex-result-heading">
        <div class="kardex-result-title">
            <span class="kardex-icon"><i class="fa fa-line-chart"></i></span>
            <div>
                <h3>Kardex · {{ $variedad->nombre }}</h3>
                <p>Movimientos desde {{ explode(' del ', convertDateToText($desde))[0] }}
                    hasta {{ explode(' del ', convertDateToText($hasta))[0] }}</p>
            </div>
        </div>
        <div class="kardex-result-meta">
            <span class="kardex-chip"><i class="fa fa-archive"></i> Bodega <strong>{{ $bodega }}</strong></span>
            <span class="kardex-chip"><i class="fa fa-list"></i> <strong>{{ count($listado) }}</strong>
                movimientos</span>
        </div>
    </div>

    <div class="kardex-summary">
        <div class="kardex-summary-item">
            <span>Saldo inicial</span>
            <strong>{{ number_format($saldo) }}</strong>
        </div>
        <div class="kardex-summary-item kardex-in">
            <span>Entradas del periodo</span>
            <strong>+{{ number_format($total_ingresos) }}</strong>
        </div>
        <div class="kardex-summary-item kardex-out">
            <span>Salidas del periodo</span>
            <strong>-{{ number_format($total_salidas) }}</strong>
        </div>
        <div class="kardex-summary-item">
            <span>Saldo al cierre</span>
            <strong>{{ number_format($saldo_final) }}</strong>
        </div>
    </div>

    @if (count($listado) > 0)
        <div class="kardex-table-wrap">
            <table class="kardex-table" id="table_listado">
                <colgroup>
                    <col style="width: 13%">
                    <col style="width: 11%">
                    <col style="width: 18%">
                    <col style="width: 28%">
                    <col style="width: 10%">
                    <col style="width: 10%">
                    <col style="width: 10%">
                </colgroup>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Documento</th>
                        <th>Detalle</th>
                        <th class="text-right kardex-in-head">Entrada</th>
                        <th class="text-right kardex-out-head">Salida</th>
                        <th class="text-right">Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $fecha_anterior = null;
                        $grupo_fecha = 0;
                    @endphp
                    @foreach ($listado as $item)
                        @php
                            $fecha_inicio = $fecha_anterior != $item->fecha;
                            if ($fecha_inicio && $fecha_anterior !== null) {
                                $grupo_fecha = !$grupo_fecha;
                            }
                            $fecha_anterior = $item->fecha;
                        @endphp
                        <tr class="kardex-day-{{ $grupo_fecha }} {{ $fecha_inicio ? 'kardex-date-start' : '' }}">
                            <td>
                                {{ explode(' del ', convertDateToText($item->fecha))[0] }}
                            </td>
                            <td class="text-center">
                                <span class="kardex-type {{ $item->tipo == 'INGRESO' ? 'kardex-type-in' : 'kardex-type-out' }}">
                                    {{ $item->tipo }}
                                </span>
                            </td>
                            <td>
                                <strong>{{ $item->concepto }}</strong>
                                <span class="text-muted">#{{ $item->documento }}</span>
                            </td>
                            <td class="kardex-detail">{{ $item->detalle ?: '—' }}</td>
                            <td class="kardex-number">
                                @if ($item->tipo == 'INGRESO')
                                    <span class="text-color_yura">+{{ number_format($item->cantidad) }}</span>
                                    @php
                                        $saldo += $item->cantidad;
                                    @endphp
                                @endif
                            </td>
                            <td class="kardex-number">
                                @if ($item->tipo == 'SALIDA')
                                    <span class="color_text-yura_danger">-{{ number_format($item->cantidad) }}</span>
                                    @php
                                        $saldo -= $item->cantidad;
                                    @endphp
                                @endif
                            </td>
                            <td class="kardex-number kardex-balance">{{ number_format($saldo) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="kardex-empty-state" style="border: 0; border-radius: 0">
            <span class="kardex-empty-icon"><i class="fa fa-inbox"></i></span>
            <strong>Sin movimientos en este periodo</strong>
            <span>El saldo inicial se mantiene hasta que se registre un movimiento.</span>
        </div>
    @endif
</div>
