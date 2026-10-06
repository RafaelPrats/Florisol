@php
    $total_ingresos = 0;
    $total_salidas = 0;
    $saldo_final = $saldo;
    foreach ($listado as $movimiento) {
        if ($movimiento->tipo == 'I') {
            $total_ingresos += $movimiento->cantidad;
            $saldo_final += $movimiento->cantidad;
        } else {
            $total_salidas += $movimiento->cantidad;
            $saldo_final -= $movimiento->cantidad;
        }
    }
@endphp

<div class="bitacora-results-card">
    <div class="bitacora-result-heading">
        <div class="bitacora-result-title">
            <span class="bitacora-icon"><i class="fa fa-history"></i></span>
            <div>
                <h3>Bitácora · {{ $variedad->nombre }}</h3>
                <p>Movimientos registrados desde {{ explode(' del ', convertDateToText($desde))[0] }}
                    hasta {{ explode(' del ', convertDateToText($hasta))[0] }}</p>
            </div>
        </div>
        <div class="bitacora-result-meta">
            <span class="bitacora-chip"><i class="fa fa-archive"></i> Bodega <strong>{{ $bodega }}</strong></span>
            <span class="bitacora-chip"><i class="fa fa-list"></i> <strong>{{ count($listado) }}</strong>
                movimientos</span>
        </div>
    </div>

    <div class="bitacora-summary">
        <div class="bitacora-summary-item">
            <span>Saldo inicial</span>
            <strong>{{ number_format($saldo) }}</strong>
        </div>
        <div class="bitacora-summary-item bitacora-in">
            <span>Entradas del periodo</span>
            <strong>+{{ number_format($total_ingresos) }}</strong>
        </div>
        <div class="bitacora-summary-item bitacora-out">
            <span>Salidas del periodo</span>
            <strong>-{{ number_format($total_salidas) }}</strong>
        </div>
        <div class="bitacora-summary-item">
            <span>Saldo al cierre</span>
            <strong>{{ number_format($saldo_final) }}</strong>
        </div>
    </div>

    @if (count($listado) > 0)
        <div class="bitacora-table-wrap">
            <table class="bitacora-table" id="table_listado">
                <colgroup>
                    <col style="width: 14%">
                    <col style="width: 9%">
                    <col style="width: 12%">
                    <col style="width: 14%">
                    <col style="width: 11%">
                    <col style="width: 19%">
                    <col style="width: 7%">
                    <col style="width: 7%">
                    <col style="width: 7%">
                </colgroup>
                <thead>
                    <tr>
                        <th>Fecha registro</th>
                        <th>Tipo</th>
                        <th>Fecha</th>
                        <th>Documento</th>
                        <th>Usuario</th>
                        <th>Detalle</th>
                        <th class="text-right bitacora-in-head">Entrada</th>
                        <th class="text-right bitacora-out-head">Salida</th>
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
                            $fecha_registro = substr($item->fecha_registro, 0, 10);
                            $fecha_inicio = $fecha_anterior != $fecha_registro;
                            if ($fecha_inicio && $fecha_anterior !== null) {
                                $grupo_fecha = !$grupo_fecha;
                            }
                            $fecha_anterior = $fecha_registro;
                        @endphp
                        <tr class="bitacora-day-{{ $grupo_fecha }} {{ $fecha_inicio ? 'bitacora-date-start' : '' }}">
                            <td>{{ $item->fecha_registro }}</td>
                            <td class="text-center">
                                <span class="bitacora-type {{ $item->tipo == 'I' ? 'bitacora-type-in' : 'bitacora-type-out' }}">
                                    {{ $item->tipo == 'I' ? 'INGRESO' : 'SALIDA' }}
                                </span>
                            </td>
                            <td>{{ explode(' del ', convertDateToText($item->fecha))[0] }}</td>
                            <td>
                                <strong>{{ $item->concepto }}</strong>
                                <span class="text-muted">#{{ $item->numero }}</span>
                            </td>
                            <td>{{ $item->usuario->username }}</td>
                            <td class="bitacora-detail">{{ $item->descripcion ?: '—' }}</td>
                            <td class="bitacora-number">
                                @if ($item->tipo == 'I')
                                    <span class="text-color_yura">+{{ number_format($item->cantidad) }}</span>
                                    @php
                                        $saldo += $item->cantidad;
                                    @endphp
                                @endif
                            </td>
                            <td class="bitacora-number">
                                @if ($item->tipo == 'S')
                                    <span class="color_text-yura_danger">-{{ number_format($item->cantidad) }}</span>
                                    @php
                                        $saldo -= $item->cantidad;
                                    @endphp
                                @endif
                            </td>
                            <td class="bitacora-number bitacora-balance">{{ number_format($saldo) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="bitacora-empty-state" style="border: 0; border-radius: 0">
            <span class="bitacora-empty-icon"><i class="fa fa-inbox"></i></span>
            <strong>Sin movimientos registrados en este periodo</strong>
            <span>El saldo inicial se mantiene hasta que se registre un movimiento.</span>
        </div>
    @endif
</div>
