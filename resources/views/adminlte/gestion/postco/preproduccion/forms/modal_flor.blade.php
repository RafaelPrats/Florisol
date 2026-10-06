<style>
    .modal-flor {
        --flor-primary: #00B388;
        --flor-dark: #5A7177;
        --flor-ink: #003729;
        --flor-border: rgba(90, 113, 119, .2);
        color: var(--flor-ink);
    }

    .modal-flor .flor-summary {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 12px;
        padding: 13px 15px;
        border: 1px solid rgba(0, 179, 136, .25);
        border-radius: 10px;
        background: linear-gradient(115deg, rgba(0, 179, 136, .09), rgba(90, 113, 119, .045));
    }

    .modal-flor .flor-title {
        display: flex;
        min-width: 0;
        align-items: center;
        gap: 11px;
    }

    .modal-flor .flor-title-icon {
        display: inline-flex;
        width: 38px;
        height: 38px;
        flex: 0 0 38px;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        background: #00B388;
        color: #fff;
        font-size: 17px;
    }

    .modal-flor .flor-title h4 {
        margin: 0;
        color: #003729;
        font-size: 15px;
        font-weight: 700;
    }

    .modal-flor .flor-title small {
        display: block;
        margin-top: 3px;
        color: #5A7177;
        font-size: 10px;
    }

    .modal-flor .flor-inventory-chip {
        display: flex;
        flex: 0 0 auto;
        align-items: center;
        gap: 8px;
        padding: 7px 11px;
        border: 1px solid rgba(90, 113, 119, .2);
        border-radius: 8px;
        background: #fff;
        color: #5A7177;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .modal-flor .flor-inventory-chip strong {
        color: #003729;
        font-size: 15px;
        font-variant-numeric: tabular-nums;
    }

    .modal-flor .flor-table-card {
        overflow: hidden;
        border: 1px solid var(--flor-border);
        border-radius: 9px;
        background: #fff;
    }

    .modal-flor .flor-table-scroll {
        max-height: 62vh;
        overflow: auto;
    }

    .modal-flor .flor-orders-table {
        width: 100%;
        min-width: 990px;
        margin: 0;
        border: 0 !important;
        border-collapse: separate;
        border-spacing: 0;
        color: var(--flor-ink);
        font-size: 11px;
    }

    .modal-flor .flor-orders-table th,
    .modal-flor .flor-orders-table td {
        padding: 8px 7px !important;
        border: 0 !important;
        border-right: 1px solid rgba(90, 113, 119, .16) !important;
        border-bottom: 1px solid rgba(90, 113, 119, .16) !important;
        vertical-align: middle !important;
    }

    .modal-flor .flor-orders-table thead th {
        position: sticky;
        top: 0;
        z-index: 3;
        background: #00B388 !important;
        color: #fff !important;
        font-size: 9px;
        font-weight: 700;
        letter-spacing: .35px;
        white-space: nowrap;
    }

    .modal-flor .flor-orders-table thead th:first-child {
        left: 0;
        z-index: 5;
        min-width: 190px;
    }

    .modal-flor .flor-orders-table thead th.bg-yura_warning {
        background: #ef6e11 !important;
    }

    .modal-flor .flor-orders-table tbody tr:nth-child(even) {
        background: rgba(90, 113, 119, .045);
    }

    .modal-flor .flor-orders-table tbody tr:hover {
        background: rgba(0, 179, 136, .09) !important;
    }

    .modal-flor .flor-orders-table .flor-order-info {
        position: sticky;
        left: 0;
        z-index: 2;
        min-width: 190px;
        background: #fff;
        text-align: left;
    }

    .modal-flor .flor-order-date {
        display: block;
        color: #003729;
        font-weight: 700;
    }

    .modal-flor .flor-order-customer {
        display: block;
        margin-top: 3px;
        color: #5A7177;
        font-size: 10px;
    }

    .modal-flor .flor-order-packing {
        display: inline-block;
        margin-top: 5px;
        padding: 2px 6px;
        border-radius: 10px;
        background: rgba(0, 179, 136, .1);
        color: #006b50;
        font-size: 9px;
        font-weight: 700;
    }

    .modal-flor .flor-number {
        color: #003729;
        font-variant-numeric: tabular-nums;
        font-weight: 600;
        text-align: center;
    }

    .modal-flor .flor-orders-table .flor-available {
        background: rgba(0, 179, 136, .07);
    }

    .modal-flor .flor-orders-table .form-control,
    .modal-flor .flor-orders-table select {
        height: 32px;
        min-height: 32px;
        padding: 4px 7px;
        border: 1px solid #707070;
        border-radius: 18px;
        background: #F7F8F8;
        color: #003729;
        box-shadow: none;
        font-size: 11px;
    }

    .modal-flor .flor-orders-table .form-control:focus,
    .modal-flor .flor-orders-table select:focus {
        border-color: #00B388;
        outline: 0;
        box-shadow: 0 0 0 2px rgba(0, 179, 136, .12);
    }

    .modal-flor .flor-orders-table .input-group-btn .btn {
        min-width: 33px;
        height: 32px;
        border-radius: 18px;
    }

    .modal-flor .flor-orders-table .flor-action-cell {
        min-width: 120px;
    }

    .modal-flor .flor-orders-table .flor-export-cell {
        min-width: 190px;
    }

    .modal-flor .flor-orders-table tfoot th {
        position: sticky;
        bottom: 0;
        z-index: 4;
        background: #00B388 !important;
        color: #fff !important;
        font-weight: 700;
        white-space: nowrap;
    }

    .modal-flor .flor-orders-table tfoot th:first-child {
        left: 0;
        z-index: 6;
    }

    .modal-flor .flor-legend {
        display: flex;
        flex-wrap: wrap;
        gap: 7px;
        margin-top: 10px;
    }

    .modal-flor .flor-legend-item {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 8px;
        border: 1px solid rgba(90, 113, 119, .16);
        border-radius: 16px;
        background: #fff;
        color: #5A7177;
        font-size: 9px;
    }

    .modal-flor .flor-legend-item i {
        color: #00B388;
    }

    .modal-flor .flor-legend-item.flor-legend-warning i {
        color: #ef6e11;
    }

    .modal-flor-confirm {
        padding: 4px;
    }

    .modal-flor-confirm .flor-confirm-message {
        display: flex;
        align-items: center;
        gap: 11px;
        margin: 0 0 12px;
        padding: 13px;
        border: 1px solid rgba(239, 110, 17, .25);
        border-radius: 9px;
        background: rgba(239, 110, 17, .08);
        color: #5A7177;
        font-size: 13px;
    }

    .modal-flor-confirm .flor-confirm-message i {
        color: #ef6e11;
        font-size: 20px;
    }

    .modal-flor-confirm .flor-auth-field {
        overflow: hidden;
        border: 1px solid rgba(90, 113, 119, .22);
        border-radius: 9px;
    }

    .modal-flor-confirm .flor-auth-label {
        padding: 9px 11px;
        background: #5A7177;
        color: #fff;
        font-size: 11px;
        font-weight: 700;
    }

    .modal-flor-confirm .flor-auth-input {
        width: 100%;
        height: 38px;
        padding: 7px 10px;
        border: 0;
        color: #003729;
        font-size: 13px;
    }

    @media (max-width: 600px) {
        .modal-flor .flor-summary {
            align-items: flex-start;
            flex-direction: column;
        }

        .modal-flor .flor-table-scroll {
            max-height: 58vh;
        }

        .modal-flor .flor-legend {
            gap: 5px;
        }
    }
</style>

<div class="modal-flor">
    <section class="flor-summary">
        <div class="flor-title">
            <span class="flor-title-icon"><i class="fa fa-leaf"></i></span>
            <div>
                <h4>{{ $variedad->nombre }}</h4>
                <small>Pedidos, inventario y operaciones de armado</small>
            </div>
        </div>
        <div class="flor-inventory-chip">
            <i class="fa fa-cubes"></i>
            Inventario disponible
            <strong>{{ number_format($inventario) }}</strong>
            <span>tallos</span>
        </div>
    </section>

    <div class="flor-table-card">
        <div class="flor-table-scroll">
            <table class="table-bordered flor-orders-table">
                <thead>
                    <tr class="tr_fija_top_0">
                        <th class="text-center th_yura_green">FECHA / PEDIDO</th>
                        <th class="text-center th_yura_green">RAMOS</th>
                        <th class="text-center th_yura_green">TxR</th>
                        <th class="text-center th_yura_green">INV. DISP.</th>
                        <th class="text-center th_yura_green">ARMADOS</th>
                        <th class="text-center th_yura_green">RAMOS DISP.</th>
                        <th class="text-center th_yura_green">ARMAR</th>
                        <th class="text-center bg-yura_warning">DEVOLVER</th>
                        <th class="text-center th_yura_green">EXPORTAR</th>
                    </tr>
                </thead>
                <tbody>
    @php
        $total_ramos = 0;
        $total_tallos = 0;
        $total_armados = 0;
        $total_disponibles = 0;
    @endphp
    @foreach ($listado as $item)
        @php
            $getSalidasRecepcion = $item->getSalidasRecepcion();
            $inventarioDisponible = getInventarioDisponibleByVariedadFechaSegmento(
                $variedad,
                $item->fecha,
                $item->segmento,
            );
            $por_armar = $item->ramos - $item->armados;
            $disponibles = 0;
            if ($por_armar > 0) {
                $invRamosDisponible = intVal($inventarioDisponible / $item->tallos_x_ramo);
                if ($invRamosDisponible >= $por_armar) {
                    $disponibles = $por_armar;
                } else {
                    $disponibles = $invRamosDisponible;
                }
            }
            $total_ramos += $item->ramos;
            $total_tallos += $item->ramos * $item->tallos_x_ramo;
            $total_armados += $item->armados;
            $total_disponibles += $disponibles;
        @endphp
        <tr>
            <th class="flor-order-info">
                <span class="flor-order-date">{{ convertDateToText($item->fecha) }}</span>
                <span class="flor-order-customer">
                    {{ $item->segmento }} · {{ $item->cliente_nombre }}
                </span>
                @if ($item->packing != '')
                    <span class="flor-order-packing">#{{ $item->packing }}</span>
                @endif
            </th>
            <td class="flor-number">
                {{ $item->ramos }}
            </td>
            <td class="flor-number">
                {{ $item->tallos_x_ramo }}
            </td>
            <td class="flor-number">
                {{ number_format($inventarioDisponible) }}
            </td>
            <td class="flor-number">
                @if ($item->armados > 0)
                    {{ $item->armados }}
                @endif
            </td>
            <td class="flor-number flor-available">
                <span class="badge bg-yura_primary" title="Ramos disponibles">
                    {{ $disponibles }}
                </span>
            </td>
            <td class="flor-action-cell">
                <div class="input-group input-group-sm">
                    <div class="input-group-btn">
                        <button type="button" class="btn btn-yura_primary"
                            title="Confirmar armado"
                            onclick="store_armar_flor('{{ $item->id_detalle_caja_proyecto }}')">
                            <i class="fa fa-fw fa-check"></i>
                        </button>
                    </div>
                    <input type="number" max="{{ $disponibles }}" min="0" step="1"
                        class="form-control text-center" placeholder="0"
                        id="ramos_armar_{{ $item->id_detalle_caja_proyecto }}"
                        onkeyup="verificar_disponibles($(this))">
                </div>
            </td>
            <td class="flor-action-cell">
                @if ($item->fecha >= '2026-09-17')
                    <div class="input-group input-group-sm">
                        <input type="number" id="devolver_ramos_{{ $item->id_detalle_caja_proyecto }}"
                            class="form-control text-center" max="{{ $item->armados }}" min="0" step="1"
                            value="{{ $item->armados }}" onkeyup="verificar_disponibles($(this))">
                        <div class="input-group-btn">
                            <button class="btn btn-xs btn-yura_warning"
                                title="Confirmar devolución"
                                onclick="store_devolver('{{ $item->id_detalle_caja_proyecto }}')">
                                <i class="fa fa-fw fa-download"></i>
                            </button>
                        </div>
                    </div>
                @endif
            </td>
            <td class="flor-export-cell">
                @if ($item->armados > 0)
                    <div class="input-group input-group-sm">
                        <select id="exportar_{{ $item->id_detalle_caja_proyecto }}"
                            onchange="export_armados('{{ $item->id_detalle_caja_proyecto }}', $(this).val())">
                            @if (count($getSalidasRecepcion) > 1)
                                <option value="">Todos los despachos</option>
                            @endif
                            @foreach ($getSalidasRecepcion as $orden)
                                <option value="{{ $orden->orden_flor_solida }}">
                                    {{ 'Orden #' . $orden->orden_flor_solida . ': ' . $orden->tallos / $item->tallos_x_ramo . ' ramos' }}
                                </option>
                            @endforeach
                        </select>
                        <div class="input-group-btn">
                            <button type="button" class="btn btn-yura_default" title="Exportar despacho"
                                onclick="export_armados('{{ $item->id_detalle_caja_proyecto }}', $('#exportar_{{ $item->id_detalle_caja_proyecto }}').val())">
                                <i class="fa fa-fw fa-file-excel-o"></i>
                            </button>
                        </div>
                    </div>
                @endif
            </td>
        </tr>
    @endforeach
    </tbody>
    <tfoot>
    <tr>
        <th class="text-center th_yura_green">
            TOTALES
        </th>
        <th class="text-center th_yura_green">
            {{ number_format($total_ramos) }}
        </th>
        <th class="text-center th_yura_green">
            {{ number_format($total_tallos) }}
        </th>
        <th class="text-center th_yura_green">
            {{ number_format($inventario) }}
        </th>
        <th class="text-center th_yura_green">
            {{ number_format($total_armados) }}
        </th>
        <th class="text-center th_yura_green">
            {{ number_format($total_disponibles) }}
        </th>
        <th class="text-center th_yura_green" colspan="3">
        </th>
    </tr>
    </tfoot>
            </table>
        </div>
    </div>

    <div class="flor-legend">
        <span class="flor-legend-item"><i class="fa fa-cube"></i> Ramos solicitados</span>
        <span class="flor-legend-item"><i class="fa fa-check-circle"></i> Ramos disponibles</span>
        <span class="flor-legend-item flor-legend-warning"><i class="fa fa-exclamation-circle"></i> Devolución de flor</span>
    </div>
</div>

<input type="hidden" id="variedad_selected" value="{{ $variedad->id_variedad }}">
<input type="hidden" id="fechas_selected" value="{{ $fechas }}">

<script>
    function store_armar_flor(id) {
        armar = parseInt($('#ramos_armar_' + id).val());
        if (armar <= parseInt($('#ramos_armar_' + id).prop('max'))) {
            texto =
                "<div class='modal-flor-confirm'>" +
                "<div class='flor-confirm-message'><i class='fa fa-cubes'></i>" +
                "<span>Confirma el armado de <b>" + armar + " ramos</b> para este pedido.</span></div>" +
                "</div>";

            modal_quest('modal_store_armar_flor', texto, '<i class="fa fa-fw fa-cubes"></i> Confirmar armado',
                true, false, '40%', function() {
                datos = {
                    _token: '{{ csrf_token() }}',
                    id: id,
                    armar: armar,
                    fecha: $('#fecha_filtro').val()
                }
                $.post('{{ url('preproduccion/store_armar_flor') }}', datos, function(retorno) {
                    cerrar_modals();
                    listar_reporte();
                    export_armados(id, retorno.orden);
                    modal_flor($('#variedad_selected').val(), $('#fechas_selected').val());
                }, 'json').fail(function(retorno) {
                    console.log(retorno);
                    alerta_errores(retorno.responseText);
                });
            })
        } else {
            alerta(
                '<div class="alert alert-warning text-center">No hay flor disponible en el inventario para armar los ramos indicados</div>'
            );
        }
    }

    function store_devolver(id) {
        var cantidad_devolver = parseInt($('#devolver_ramos_' + id).val());
        texto =
            '<div class="modal-flor-confirm">' +
            '<div class="flor-confirm-message"><i class="fa fa-reply"></i>' +
            '<span>Confirma la devolución de <b>' + cantidad_devolver +
            ' ramos</b> al inventario.</span></div>' +
            '<div class="flor-auth-field">' +
            '<div class="flor-auth-label"><i class="fa fa-lock"></i> Código de autorización</div>' +
            '<input type="password" id="codigo_autorizacion" class="flor-auth-input" autocomplete="off">' +
            '</div></div>';

        modal_quest('modal_store_devolver', texto, '<i class="fa fa-fw fa-reply"></i> Confirmar devolución',
            true, false, '40%',
            function() {
                devolver = parseInt($('#devolver_ramos_' + id).val());
                if (devolver <= parseInt($('#devolver_ramos_' + id).prop('max'))) {
                    datos = {
                        _token: '{{ csrf_token() }}',
                        id: id,
                        devolver: devolver,
                        codigo: $('#codigo_autorizacion').val(),
                    }
                    post_jquery_m('{{ url('preproduccion/store_devolver') }}', datos, function() {
                        cerrar_modals();
                        listar_reporte();
                        modal_flor($('#variedad_selected').val(), $('#fechas_selected').val());
                    });
                } else {
                    alerta(
                        '<div class="alert alert-warning text-center">No hay flor disponible en el inventario para armar los ramos indicados</div>'
                    );
                }
            })

    }

    function export_armados(id, orden) {
        $.LoadingOverlay('show');
        window.open('{{ url('preproduccion/export_armados') }}?id=' + id + '&orden=' + orden,
            '_blank');
        $.LoadingOverlay('hide');
    }

    function verificar_disponibles(input) {
        maximo = parseInt(input.prop('max'));
        if (parseInt(input.val()) > maximo || parseInt(input.val()) < 0)
            input.val('');
    }
</script>
