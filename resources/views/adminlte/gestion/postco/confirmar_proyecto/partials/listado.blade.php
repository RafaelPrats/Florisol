@php
    $total_pedido = 0;
    $total_confirmado = 0;
@endphp

<div class="confirmar-detalle">
    <div class="confirmar-order-heading">
        <span class="confirmar-order-icon"><i class="fa fa-file-text-o"></i></span>
        <div class="confirmar-order-title">
            <small>Pedido seleccionado</small>
            <h2>Pedido #{{ $proyecto->id_proyecto }}</h2>
            <p>{{ $proyecto->cliente->detalle()->nombre }} <span>·</span> {{ convertDateToText($proyecto->fecha) }}</p>
        </div>
        @if ($confirmacion == '')
            <span class="confirmar-document-status is-pending"><i class="fa fa-clock-o"></i> Pendiente de confirmar</span>
        @elseif($confirmacion->estado == 'P')
            <span class="confirmar-document-status is-sent"><i class="fa fa-paper-plane"></i> Enviado · #{{ $confirmacion->id_proyecto_confirmacion }}</span>
        @elseif($confirmacion->estado == 'C')
            <span class="confirmar-document-status is-confirmed"><i class="fa fa-check-circle"></i> Confirmado · #{{ $confirmacion->id_proyecto_confirmacion }}</span>
        @elseif($confirmacion->estado == 'R')
            <span class="confirmar-document-status is-received"><i class="fa fa-inbox"></i> Recibido · #{{ $confirmacion->id_proyecto_confirmacion }}</span>
        @endif
    </div>

    @if (count($listado) > 0)
        <div class="confirmar-summary">
            <div class="confirmar-summary-card">
                <span class="confirmar-summary-icon"><i class="fa fa-leaf"></i></span>
                <span><strong>{{ count($listado) }}</strong><small>Variedades</small></span>
            </div>
            <div class="confirmar-summary-card">
                <span class="confirmar-summary-icon is-ordered"><i class="fa fa-list-ol"></i></span>
                <span>
                    <strong id="total_pedido_live">
                        @foreach ($listado as $item)
                            @php
                                $item->tallos += intval(porcentaje($finca_proveedor->margen, $item->tallos, 2));
                                $total_pedido += $item->tallos;
                                $total_confirmado += $item->confirmados;
                            @endphp
                        @endforeach
                        {{ number_format($total_pedido) }}
                    </strong>
                    <small>Tallos del pedido</small>
                </span>
            </div>
            <div class="confirmar-summary-card">
                <span class="confirmar-summary-icon is-confirmed"><i class="fa fa-check"></i></span>
                <span><strong id="total_confirmado_live">{{ number_format($total_confirmado) }}</strong><small>Tallos a confirmar</small></span>
            </div>
            <div class="confirmar-summary-card">
                <span class="confirmar-summary-icon is-difference"><i class="fa fa-adjust"></i></span>
                <span><strong id="total_diferencia_live">{{ number_format($total_pedido - $total_confirmado) }}</strong><small>Por confirmar</small></span>
            </div>
        </div>

        <div class="confirmar-table-card">
            <div class="confirmar-table-heading">
                <div>
                    <h3><i class="fa fa-list-ul"></i> Detalle por variedad</h3>
                    <p>Ingresa cuántos tallos confirma la finca para cada variedad.</p>
                </div>
                <span>{{ count($listado) }} {{ count($listado) === 1 ? 'variedad' : 'variedades' }}</span>
            </div>
            <div class="confirmar-table-scroll">
                <table id="table_listado" class="confirmar-table">
                    <thead>
                        <tr>
                            <th class="confirmar-number-column">N.º</th>
                            <th>Planta y variedad</th>
                            <th class="confirmar-numeric-column">Pedido</th>
                            <th class="confirmar-input-column">Confirmar tallos</th>
                            <th class="confirmar-numeric-column">Pendientes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($listado as $pos => $item)
                            @php
                                $tallos_diferencia = $item->tallos - $item->confirmados;
                            @endphp
                            <tr class="{{ $item->tipo_variedad == 'CONFIRMACION' ? 'is-removed-variety' : '' }}"
                                title="{{ $item->tipo_variedad == 'CONFIRMACION' ? 'Esta variedad ya no se encuentra en el pedido' : '' }}">
                                <td class="confirmar-number-column" data-label="N.º">
                                    <span class="confirmar-row-number">{{ $pos + 1 }}</span>
                                </td>
                                <td data-label="Planta y variedad">
                                    <div class="confirmar-variety">
                                        <strong>{{ $item->var_nombre }}</strong>
                                        <small>{{ $item->pta_nombre }}</small>
                                        @if ($item->tipo_variedad == 'CONFIRMACION')
                                            <span class="confirmar-removed-tag"><i class="fa fa-exclamation-circle"></i> Ya no está en el pedido</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="confirmar-numeric-column" data-label="Pedido">
                                    <input type="number" class="confirmar-readonly-input"
                                        readonly disabled id="tallos_{{ $item->id_variedad }}"
                                        value="{{ $item->tallos }}">
                                </td>
                                <td class="confirmar-input-column" data-label="Confirmar tallos">
                                    <input type="number" class="confirmar-entry input_confirmar"
                                        min="0" max="{{ $item->tallos }}" step="1" inputmode="numeric"
                                        placeholder="0"
                                        value="{{ $item->confirmados > 0 ? $item->confirmados : '' }}"
                                        data-id_variedad="{{ $item->id_variedad }}"
                                        id="confirmar_{{ $item->id_variedad }}"
                                    {{ $confirmacion != '' && in_array($confirmacion->estado, ['C', 'R']) ? 'disabled' : '' }}
                                    oninput="calcular_totales(this)" onchange="calcular_totales(this)"
                                    aria-label="Tallos confirmados para {{ $item->var_nombre }}">
                                </td>
                                <td class="confirmar-numeric-column" data-label="Pendientes">
                                    <input type="number" class="confirmar-readonly-input is-difference"
                                        readonly disabled id="diferencia_{{ $item->id_variedad }}"
                                        value="{{ $tallos_diferencia }}">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="2">Totales</th>
                            <th class="confirmar-numeric-column">{{ number_format($total_pedido) }}</th>
                            <th class="confirmar-numeric-column" id="total_confirmado_footer">{{ number_format($total_confirmado) }}</th>
                            <th class="confirmar-numeric-column" id="total_diferencia_footer">{{ number_format($total_pedido - $total_confirmado) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="confirmar-table-note">
                <i class="fa fa-info-circle"></i> Las variedades señaladas en ámbar fueron confirmadas anteriormente y ya no aparecen en el pedido actual.
            </div>
        </div>

        <div class="confirmar-submit">
            <div class="confirmar-submit-hint">
                <i class="fa fa-check-circle"></i>
                @if ($confirmacion == '')
                    Revisa las cantidades antes de guardar la confirmación.
                @elseif($confirmacion->estado == 'P')
                    Puedes actualizar las cantidades mientras el documento está enviado.
                @elseif($confirmacion->estado == 'C')
                    Este documento ya fue confirmado y no admite cambios.
                @elseif($confirmacion->estado == 'R')
                    Este documento ya fue recibido y no admite cambios.
                @endif
            </div>
            @if ($confirmacion == '')
                <button type="button" class="btn btn-confirmar-submit"
                    onclick="store_confirmar_pedido('{{ $proyecto->id_proyecto }}', '{{ $finca_proveedor->id_finca_proveedor }}')">
                    <i class="fa fa-save"></i> Confirmar pedido
                </button>
            @elseif($confirmacion->estado == 'P')
                <button type="button" class="btn btn-confirmar-submit"
                    onclick="store_confirmar_pedido('{{ $proyecto->id_proyecto }}', '{{ $finca_proveedor->id_finca_proveedor }}')">
                    <i class="fa fa-refresh"></i> Actualizar confirmación
                </button>
            @elseif($confirmacion->estado == 'C')
                <button type="button" class="btn btn-confirmar-submit" disabled>
                    <i class="fa fa-check-circle"></i> Pedido confirmado
                </button>
            @elseif($confirmacion->estado == 'R')
                <button type="button" class="btn btn-confirmar-submit" disabled>
                    <i class="fa fa-inbox"></i> Pedido recibido
                </button>
            @endif
        </div>
    @else
        <div class="confirmar-empty">
            <span><i class="fa fa-leaf"></i></span>
            <strong>No hay variedades de esta finca en el pedido</strong>
            <small>Verifica el código del pedido o la finca seleccionada.</small>
        </div>
    @endif
</div>

<style>
    .confirmar-detalle {
        --confirm-green: #00B388;
        --confirm-ink: #003729;
        --confirm-muted: #5A7177;
        --confirm-line: #dce9e4;
        margin-bottom: 15px;
        color: var(--confirm-ink, #003729);
        font-size: 13px;
    }

    .confirmar-order-heading {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 10px;
        padding: 11px 13px;
        border: 1px solid var(--confirm-line);
        border-radius: 9px;
        background: linear-gradient(110deg, #f4faf7, #fff 72%);
    }

    .confirmar-order-icon {
        display: grid;
        width: 38px;
        height: 38px;
        flex: 0 0 38px;
        place-items: center;
        border-radius: 10px;
        background: #e4f5ee;
        color: var(--confirm-green);
        font-size: 15px;
    }

    .confirmar-order-title {
        min-width: 0;
        flex: 1 1 auto;
    }

    .confirmar-order-title > small {
        color: var(--confirm-muted, #5A7177);
        font-size: 10px;
    }

    .confirmar-order-title h2 {
        margin: 1px 0;
        color: var(--confirm-ink);
        font-size: 15px;
        font-weight: 700;
    }

    .confirmar-order-title p {
        margin: 0;
        overflow: hidden;
        color: var(--confirm-muted);
        font-size: 11px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .confirmar-order-title p span {
        margin: 0 3px;
        color: var(--confirm-green);
    }

    .confirmar-document-status,
    .confirmar-removed-tag {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 5px 8px;
        border-radius: 18px;
        font-size: 10px;
        font-weight: 700;
        white-space: nowrap;
    }

    .confirmar-document-status.is-pending {
        background: #fff4d8;
        color: #886000;
    }

    .confirmar-document-status.is-sent {
        background: #e9f2ff;
        color: #315e9b;
    }

    .confirmar-document-status.is-confirmed,
    .confirmar-document-status.is-received {
        background: #e5f6ee;
        color: #147452;
    }

    .confirmar-summary {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 8px;
        margin-bottom: 10px;
    }

    .confirmar-summary-card {
        display: flex;
        min-width: 0;
        min-height: 57px;
        align-items: center;
        gap: 8px;
        padding: 8px 10px;
        border: 1px solid var(--confirm-line);
        border-radius: 8px;
        background: #fff;
    }

    .confirmar-summary-icon {
        display: grid;
        width: 31px;
        height: 31px;
        flex: 0 0 31px;
        place-items: center;
        border-radius: 9px;
        background: #eaf6f0;
        color: var(--confirm-green);
        font-size: 12px;
    }

    .confirmar-summary-icon.is-ordered {
        background: #eef3f8;
        color: #53738c;
    }

    .confirmar-summary-icon.is-confirmed {
        background: #e5f6ee;
        color: #16835f;
    }

    .confirmar-summary-icon.is-difference {
        background: #fff4d8;
        color: #a47713;
    }

    .confirmar-summary-card strong,
    .confirmar-summary-card small {
        display: block;
    }

    .confirmar-summary-card strong {
        overflow: hidden;
        color: var(--confirm-ink);
        font-size: 16px;
        line-height: 1.1;
        text-overflow: ellipsis;
    }

    .confirmar-summary-card small {
        margin-top: 3px;
        color: var(--confirm-muted);
        font-size: 10px;
        white-space: nowrap;
    }

    .confirmar-table-card {
        overflow: hidden;
        border: 1px solid var(--confirm-line);
        border-radius: 9px;
        background: #fff;
    }

    .confirmar-table-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        padding: 10px 12px;
        border-bottom: 1px solid var(--confirm-line);
    }

    .confirmar-table-heading h3 {
        margin: 0;
        color: var(--confirm-ink);
        font-size: 13px;
        font-weight: 700;
    }

    .confirmar-table-heading h3 i {
        margin-right: 4px;
        color: var(--confirm-green);
    }

    .confirmar-table-heading p {
        margin: 3px 0 0;
        color: var(--confirm-muted);
        font-size: 10px;
    }

    .confirmar-table-heading > span {
        flex: 0 0 auto;
        padding: 4px 7px;
        border-radius: 16px;
        background: #edf5f1;
        color: #42675b;
        font-size: 10px;
        font-weight: 700;
    }

    .confirmar-table-scroll {
        max-height: min(58vh, 600px);
        overflow: auto;
    }

    .confirmar-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .confirmar-table thead th {
        position: sticky;
        z-index: 2;
        top: 0;
        padding: 8px 10px;
        border-bottom: 1px solid #00674f;
        background: #008f6c;
        color: #fff;
        font-size: 10px;
        font-weight: 700;
        text-align: left;
        white-space: nowrap;
    }

    .confirmar-table tbody td {
        padding: 6px 10px;
        border-bottom: 1px solid #e8efec;
        background: #fff;
        vertical-align: middle;
        font-size: 12px;
    }

    .confirmar-table tbody tr:nth-child(even) td {
        background: #f7faf8;
    }

    .confirmar-table tbody tr:hover td {
        background: #edf8f3;
    }

    .confirmar-table tbody tr.is-removed-variety td,
    .confirmar-table tbody tr.is-removed-variety:nth-child(even) td {
        background: #fff9ec;
    }

    .confirmar-number-column {
        width: 45px;
        text-align: center !important;
    }

    .confirmar-row-number {
        display: inline-grid;
        width: 23px;
        height: 23px;
        place-items: center;
        border-radius: 7px;
        background: #edf5f1;
        color: #527266;
        font-size: 10px;
        font-weight: 700;
    }

    .confirmar-variety strong,
    .confirmar-variety small {
        display: block;
    }

    .confirmar-variety strong {
        color: var(--confirm-ink);
        font-size: 12px;
        font-weight: 700;
    }

    .confirmar-variety small {
        margin-top: 2px;
        color: var(--confirm-muted);
        font-size: 10px;
    }

    .confirmar-removed-tag {
        margin-top: 4px;
        padding: 3px 6px;
        background: #fff0ce;
        color: #8b6411;
        font-size: 9px;
    }

    .confirmar-numeric-column {
        width: 120px;
        text-align: center !important;
    }

    .confirmar-input-column {
        width: 155px;
        text-align: center !important;
    }

    .confirmar-readonly-input,
    .confirmar-entry {
        width: 100%;
        max-width: 112px;
        height: 32px;
        padding: 4px 7px;
        border: 1px solid var(--confirm-line);
        border-radius: 5px;
        background: #f7faf8;
        color: var(--confirm-ink);
        font-size: 12px;
        font-weight: 600;
        text-align: center;
        font-variant-numeric: tabular-nums;
    }

    .confirmar-readonly-input:disabled {
        opacity: 1;
        -webkit-text-fill-color: var(--confirm-ink);
    }

    .confirmar-readonly-input.is-difference {
        border-color: #f0dfb8;
        background: #fff9ec;
        color: #8b6411;
        -webkit-text-fill-color: #8b6411;
    }

    .confirmar-entry {
        border: 1px solid #a8d9c8;
        background: #fff;
        color: #087a5f;
        font-size: 14px;
    }

    .confirmar-entry:focus {
        border-color: var(--confirm-green);
        outline: 0;
        box-shadow: 0 0 0 2px rgba(0, 179, 136, .15);
    }

    .confirmar-table tfoot th {
        position: sticky;
        z-index: 1;
        bottom: 0;
        padding: 8px 10px;
        border-top: 1px solid #00674f;
        background: #eaf6f0;
        color: var(--confirm-ink);
        font-size: 11px;
        font-weight: 700;
    }

    .confirmar-table-note {
        padding: 7px 11px;
        border-top: 1px solid var(--confirm-line);
        color: var(--confirm-muted);
        font-size: 10px;
    }

    .confirmar-table-note i {
        margin-right: 3px;
        color: #b4831d;
    }

    .confirmar-submit {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-top: 10px;
        padding: 10px 12px;
        border: 1px solid var(--confirm-line);
        border-radius: 8px;
        background: #fff;
    }

    .confirmar-submit-hint {
        color: var(--confirm-muted);
        font-size: 11px;
    }

    .confirmar-submit-hint i {
        margin-right: 4px;
        color: var(--confirm-green);
    }

    .btn-confirmar-submit {
        min-height: 36px;
        padding: 7px 12px;
        border: 1px solid var(--confirm-green);
        border-radius: 5px;
        background: var(--confirm-green);
        color: #fff;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
    }

    .btn-confirmar-submit:hover,
    .btn-confirmar-submit:focus {
        border-color: #008f6c;
        background: #008f6c;
        color: #fff;
    }

    .btn-confirmar-submit:disabled {
        border-color: #d7e2dd;
        background: #eef3f0;
        color: #72847c;
        opacity: 1;
    }

    .btn-confirmar-submit i {
        margin-right: 3px;
    }

    .confirmar-empty {
        display: flex;
        min-height: 170px;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        gap: 5px;
        padding: 20px;
        border: 1px solid var(--confirm-line);
        border-radius: 9px;
        background: #fff;
        color: var(--confirm-muted);
        text-align: center;
    }

    .confirmar-empty > span {
        display: grid;
        width: 40px;
        height: 40px;
        margin-bottom: 3px;
        place-items: center;
        border-radius: 11px;
        background: #e4f5ee;
        color: var(--confirm-green);
        font-size: 16px;
    }

    .confirmar-empty strong {
        color: var(--confirm-ink);
        font-size: 13px;
    }

    .confirmar-empty small {
        font-size: 11px;
    }

    .confirmar-save-warning {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 12px;
        border: 1px solid #f0dfb8;
        border-radius: 8px;
        background: #fff9ec;
        color: var(--confirm-ink, #003729);
        text-align: left;
    }

    .confirmar-save-warning > span {
        display: grid;
        width: 34px;
        height: 34px;
        flex: 0 0 34px;
        place-items: center;
        border-radius: 9px;
        background: #fff0ce;
        color: #a47713;
    }

    .confirmar-save-warning strong {
        display: block;
        margin-bottom: 3px;
        font-size: 13px;
    }

    .confirmar-save-warning p {
        margin: 0;
        color: var(--confirm-muted, #5A7177);
        font-size: 11px;
        line-height: 1.45;
    }

    @media (max-width: 760px) {
        .confirmar-summary {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 600px) {
        .confirmar-order-heading {
            align-items: flex-start;
            flex-wrap: wrap;
        }

        .confirmar-document-status {
            margin-left: 48px;
        }

        .confirmar-table-scroll {
            max-height: none;
            overflow: visible;
        }

        .confirmar-table,
        .confirmar-table tbody,
        .confirmar-table tfoot {
            display: block;
            width: 100%;
        }

        .confirmar-table thead {
            display: none;
        }

        .confirmar-table tbody tr {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 7px;
            padding: 9px 10px;
            border-bottom: 1px solid var(--confirm-line);
        }

        .confirmar-table tbody td {
            display: block;
            width: auto;
            padding: 0;
            border: 0;
            background: transparent !important;
            text-align: left !important;
        }

        .confirmar-table tbody td.confirmar-number-column {
            display: none;
        }

        .confirmar-table tbody td[data-label="Planta y variedad"] {
            grid-column: 1 / -1;
        }

        .confirmar-table tbody td[data-label="Pedido"]::before,
        .confirmar-table tbody td[data-label="Confirmar tallos"]::before,
        .confirmar-table tbody td[data-label="Pendientes"]::before {
            display: block;
            margin-bottom: 4px;
            color: var(--confirm-muted);
            content: attr(data-label);
            font-size: 9px;
            font-weight: 600;
        }

        .confirmar-table tbody .confirmar-readonly-input,
        .confirmar-table tbody .confirmar-entry {
            max-width: none;
            height: 34px;
            font-size: 13px;
        }

        .confirmar-table tfoot tr {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 5px;
            padding: 8px 10px;
            background: #eaf6f0;
        }

        .confirmar-table tfoot th {
            position: static;
            padding: 0;
            border: 0;
            background: transparent;
            font-size: 11px;
        }

        .confirmar-table tfoot th:first-child {
            grid-column: 1 / -1;
        }
    }

    @media (max-width: 420px) {
        .confirmar-summary-card {
            gap: 6px;
            padding: 7px;
        }

        .confirmar-summary-icon {
            width: 27px;
            height: 27px;
            flex-basis: 27px;
        }

        .confirmar-summary-card strong {
            font-size: 14px;
        }

        .confirmar-submit {
            align-items: stretch;
            flex-direction: column;
        }
    }
</style>

<script>
    function actualizar_totales_confirmacion() {
        var totalPedido = 0;
        var totalConfirmado = 0;

        $('.input_confirmar').each(function() {
            var idVariedad = $(this).data('id_variedad');
            var tallos = parseInt($('#tallos_' + idVariedad).val(), 10) || 0;
            var confirmados = parseInt($(this).val(), 10) || 0;
            totalPedido += tallos;
            totalConfirmado += confirmados;
        });

        var diferencia = totalPedido - totalConfirmado;
        $('#total_pedido_live').text(totalPedido.toLocaleString());
        $('#total_confirmado_live, #total_confirmado_footer').text(totalConfirmado.toLocaleString());
        $('#total_diferencia_live, #total_diferencia_footer').text(diferencia.toLocaleString());
    }

    function calcular_totales(input) {
        var idVariedad = $(input).data('id_variedad');
        var tallos = parseInt($('#tallos_' + idVariedad).val(), 10) || 0;
        var confirmar = parseInt($(input).val(), 10) || 0;

        if (confirmar < 0) {
            confirmar = 0;
            $(input).val(0);
        }
        if (confirmar > tallos) {
            confirmar = tallos;
            $(input).val(tallos);
        }

        $('#diferencia_' + idVariedad).val(tallos - confirmar);
        actualizar_totales_confirmacion();
    }

    function store_confirmar_pedido(proy, finca) {
        var data = [];
        var faltantes = false;
        var inputConfirmar = $('.input_confirmar');

        for (var i = 0; i < inputConfirmar.length; i++) {
            var input = $(inputConfirmar[i]);
            var variedad = input.data('id_variedad');
            var tallos = $('#tallos_' + variedad).val();
            var confirmar = input.val();
            var diferencia = parseInt($('#diferencia_' + variedad).val(), 10) || 0;
            if (diferencia > 0) {
                faltantes = true;
            }
            data.push({
                variedad: variedad,
                confirmar: confirmar,
                tallos: tallos,
                diferencia: diferencia
            });
        }

        if (data.length > 0) {
            var texto = '<div class="confirmar-save-warning">' +
                '<span><i class="fa fa-check-circle"></i></span>' +
                '<div><strong>¿Confirmar las cantidades del pedido?</strong>' +
                '<p>Se guardarán los tallos ingresados para cada variedad. ' +
                (faltantes ? 'Quedarán variedades con tallos pendientes de confirmar.' : 'El pedido quedará confirmado con las cantidades indicadas.') +
                '</p></div></div>';

            modal_quest('modal_store_confirmar_pedido', texto, 'Confirmar pedido', true, false,
                'min(440px, 94vw)', function() {
                    var datos = {
                        _token: '{{ csrf_token() }}',
                        proy: proy,
                        finca: finca,
                        faltantes: faltantes,
                        data: JSON.stringify(data)
                    };
                    post_jquery_m('{{ url('confirmar_proyecto/store_confirmar_pedido') }}', datos, function() {
                        cerrar_modals();
                        listar_reporte();
                    });
                });
        }
    }
</script>
