<div class="baja-modal">
    <div class="baja-order-meta">
        <label class="baja-modal-field">
            <span><i class="fa fa-calendar"></i> Fecha de la orden</span>
            <input type="date" name="new_fecha" id="new_fecha" class="form-control" min="2026-09-17"
                value="{{ hoy() >= '2026-09-17' ? hoy() : '2026-09-17' }}">
        </label>
        <label class="baja-modal-field">
            <span><i class="fa fa-hashtag"></i> N° de orden</span>
            <input type="number" min="1" name="new_orden_basura" id="new_orden_basura" class="form-control"
                value="{{ $last_orden + 1 }}">
        </label>
        <div class="baja-modal-help">
            <i class="fa fa-info-circle"></i> Ingresa la cantidad a desechar y selecciona un motivo en cada lote elegido.
        </div>
    </div>

    @if (count($motivos) === 0)
        <div class="alert alert-warning baja-modal-alert">
            <i class="fa fa-exclamation-triangle"></i> No hay motivos activos. Configura al menos uno antes de crear la orden.
            <button type="button" class="btn btn-yura_default btn-sm" onclick="cerrar_modals();admin_motivos()">
                <i class="fa fa-list-alt"></i> Administrar motivos
            </button>
        </div>
    @endif

    <div class="baja-modal-toolbar">
        <label for="baja_modal_search"><i class="fa fa-search"></i> Buscar planta o variedad</label>
        <input type="search" id="baja_modal_search" class="form-control" placeholder="Escribe para filtrar los lotes...">
        <span class="baja-selection-count"><i class="fa fa-check-circle"></i> <strong id="baja_selection_count">0</strong>
            lotes seleccionados</span>
    </div>

    @if (count($listado))
        <div class="baja-modal-table-wrap">
            <table class="baja-modal-table" id="table_new_orden">
                <thead>
                    <tr>
                        <th>Planta</th>
                        <th>Variedad</th>
                        <th style="width: 112px">Fecha</th>
                        <th class="text-center" style="width: 72px">Longitud</th>
                        <th class="text-center" style="width: 52px">TxR</th>
                        <th class="text-right" style="width: 118px">Disponibles</th>
                        <th class="text-center" style="width: 116px">A desechar</th>
                        <th style="width: 190px">Motivo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($listado as $item)
                        <tr data-search="{{ strtolower($item->pta_nombre . ' ' . $item->var_nombre) }}">
                            <td>{{ $item->pta_nombre }}</td>
                            <td>{{ $item->var_nombre }}</td>
                            <td>{{ $item->fecha }}</td>
                            <td class="text-center">{{ $item->longitud }}</td>
                            <td class="text-center">{{ $item->tallos_x_ramo }}</td>
                            <td class="text-right">
                                <span class="baja-modal-available">{{ number_format($item->disponibles) }}</span>
                            </td>
                            <td>
                                <input type="number" min="0" step="1" inputmode="numeric"
                                    class="form-control input_botar baja-quantity"
                                    data-id_inv="{{ $item->id_inventario_recepcion }}"
                                    id="botar_{{ $item->id_inventario_recepcion }}"
                                    max="{{ $item->disponibles }}"
                                    oninput="keyup_botar('{{ $item->id_inventario_recepcion }}')"
                                    aria-label="Cantidad a desechar">
                            </td>
                            <td>
                                <select id="motivo_{{ $item->id_inventario_recepcion }}"
                                    class="form-control baja-reason"
                                    onchange="keyup_botar('{{ $item->id_inventario_recepcion }}')"
                                    aria-label="Motivo de baja">
                                    <option value="">Seleccionar motivo</option>
                                    @foreach ($motivos as $mot)
                                        <option value="{{ $mot->id_motivo_baja }}">{{ $mot->nombre }}</option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="baja-empty"><i class="fa fa-inbox"></i> No hay tallos disponibles para crear una orden.</div>
    @endif

    <div class="baja-modal-footer">
        <span>Los tallos seleccionados se registrarán como una orden pendiente.</span>
        <button type="button" class="btn btn-yura_primary" onclick="store_orden_basura()"
            {{ count($motivos) === 0 || count($listado) === 0 ? 'disabled' : '' }}>
            <i class="fa fa-save"></i> Crear orden
        </button>
    </div>
</div>

<style>
    .baja-modal {
        --baja-modal-green: #00B388;
        --baja-modal-ink: #003729;
        --baja-modal-muted: #5A7177;
        color: var(--baja-modal-ink);
        font-size: 14px;
    }

    .baja-order-meta {
        display: grid;
        grid-template-columns: minmax(150px, 220px) minmax(130px, 180px) minmax(180px, 1fr);
        align-items: end;
        gap: 12px;
        margin-bottom: 12px;
        padding: 12px;
        border: 1px solid #dce9e4;
        border-radius: 8px;
        background: #f4faf7;
    }

    .baja-modal-field {
        display: block;
        margin: 0;
        font-size: 13px;
        font-weight: 700;
    }

    .baja-modal-field span {
        display: block;
        margin-bottom: 5px;
    }

    .baja-modal-field span i {
        margin-right: 4px;
        color: var(--baja-modal-green);
    }

    .baja-modal .form-control {
        height: 34px;
        border-color: #dce9e4;
        border-radius: 5px;
        box-shadow: none;
        font-size: 14px;
    }

    .baja-modal .form-control:focus {
        border-color: var(--baja-modal-green);
        box-shadow: 0 0 0 2px rgba(0, 179, 136, .1);
    }

    .baja-modal-help {
        align-self: center;
        padding-top: 17px;
        color: var(--baja-modal-muted);
        font-size: 12px;
    }

    .baja-modal-help i {
        color: var(--baja-modal-green);
    }

    .baja-modal-alert {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        padding: 9px 12px;
        font-size: 13px;
    }

    .baja-modal-toolbar {
        display: grid;
        grid-template-columns: auto minmax(180px, 1fr) auto;
        align-items: center;
        gap: 10px;
        margin: 10px 0;
    }

    .baja-modal-toolbar label {
        margin: 0;
        color: var(--baja-modal-ink);
        font-size: 13px;
    }

    .baja-modal-toolbar label i,
    .baja-selection-count i {
        color: var(--baja-modal-green);
    }

    .baja-selection-count {
        color: var(--baja-modal-muted);
        font-size: 12px;
        white-space: nowrap;
    }

    .baja-selection-count strong {
        color: var(--baja-modal-ink);
        font-size: 14px;
    }

    .baja-modal-table-wrap {
        max-height: min(58vh, 560px);
        overflow: auto;
        border: 1px solid #dce9e4;
        border-radius: 7px;
    }

    .baja-modal-table {
        width: 100%;
        min-width: 850px;
        margin: 0;
        border-collapse: separate;
        border-spacing: 0;
        color: var(--baja-modal-ink);
        font-size: 13px;
    }

    .baja-modal-table th,
    .baja-modal-table td {
        padding: 5px 8px;
        border-right: 1px solid #e4eee9;
        border-bottom: 1px solid #e4eee9;
        vertical-align: middle;
    }

    .baja-modal-table thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        background: var(--baja-modal-ink);
        color: #fff;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }

    .baja-modal-table tbody tr:nth-child(even) {
        background: #f5faf7;
    }

    .baja-modal-table tbody tr:hover {
        background: #e6f5ee;
    }

    .baja-modal-table .form-control {
        min-width: 0;
        height: 30px;
        padding: 4px 6px;
        font-size: 13px;
    }

    .baja-modal-table .baja-quantity {
        min-width: 78px;
        text-align: right;
        font-weight: 700;
    }

    .baja-modal-table .baja-reason {
        min-width: 158px;
    }

    .baja-modal-available {
        font-variant-numeric: tabular-nums;
        font-weight: 700;
    }

    .baja-modal-table .baja-selected {
        border-color: var(--baja-modal-green);
        background: #e4f5ee;
    }

    .baja-modal-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-top: 11px;
        padding-top: 10px;
        border-top: 1px solid #dce9e4;
        color: var(--baja-modal-muted);
        font-size: 12px;
    }

    .baja-modal-footer .btn {
        flex: 0 0 auto;
        padding: 8px 14px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 700;
    }

    @media (max-width: 700px) {
        .baja-order-meta {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .baja-modal-help {
            grid-column: 1 / -1;
            padding-top: 0;
        }

        .baja-modal-toolbar {
            grid-template-columns: 1fr;
            gap: 6px;
        }

        .baja-modal-footer,
        .baja-modal-alert {
            align-items: flex-start;
            flex-direction: column;
        }
    }

    @media (max-width: 420px) {
        .baja-order-meta {
            grid-template-columns: 1fr;
        }
    }
</style>

<script>
    function keyup_botar(id) {
        var input = $('#botar_' + id);
        var quantity = input.val() === '' ? 0 : Number(input.val());
        var reason = $('#motivo_' + id);
        input.removeClass('baja-selected');
        reason.removeClass('baja-selected');

        if (quantity < 0 || quantity % 1 !== 0 || quantity > Number(input.attr('max'))) {
            input.val('');
            quantity = 0;
        }
        if (quantity > 0) {
            input.addClass('baja-selected');
        }
        if (reason.val() !== '') {
            reason.addClass('baja-selected');
        }
        actualizar_conteo_baja();
    }

    function actualizar_conteo_baja() {
        var count = 0;
        $('.input_botar').each(function() {
            if (Number($(this).val()) > 0) {
                count++;
            }
        });
        $('#baja_selection_count').text(count);
    }

    $('#baja_modal_search').on('input', function() {
        var term = $.trim($(this).val().toLowerCase());
        $('#table_new_orden tbody tr').each(function() {
            $(this).toggle($(this).data('search').indexOf(term) !== -1);
        });
    });

    function store_orden_basura() {
        var data = [];
        var missingReason = false;
        var invalidQuantity = false;

        $('.input_botar').each(function() {
            var quantity = Number($(this).val());
            if (quantity > 0) {
                var id = $(this).data('id_inv');
                var reason = $('#motivo_' + id).val();
                if (quantity % 1 !== 0 || quantity > Number($(this).attr('max'))) {
                    invalidQuantity = true;
                } else if (!reason) {
                    missingReason = true;
                } else {
                    data.push({
                        id_inv: id,
                        botar: quantity,
                        motivo: reason,
                    });
                }
            }
        });

        if (invalidQuantity) {
            alerta_errores("<div class='alert alert-warning text-center'>La cantidad a desechar supera los tallos disponibles de un lote.</div>");
            return false;
        }
        if (missingReason) {
            alerta_errores("<div class='alert alert-warning text-center'>Selecciona un motivo para cada lote con cantidad a desechar.</div>");
            return false;
        }
        if (!data.length) {
            alerta_errores("<div class='alert alert-warning text-center'>Ingresa una cantidad mayor que cero en al menos un lote.</div>");
            return false;
        }
        if (!$('#new_fecha').val() || Number($('#new_orden_basura').val()) < 1) {
            alerta_errores("<div class='alert alert-warning text-center'>Completa una fecha y un número de orden válido.</div>");
            return false;
        }

        var texto =
            "<div class='alert alert-info text-center'><h4>¿Confirmas la creación de la orden de flor de baja?</h4><p>" +
            data.length + " lotes seleccionados</p></div>";
        modal_quest('modal_store_orden_basura', texto, 'Crear orden', true, false, '40%', function() {
            var datos = {
                _token: '{{ csrf_token() }}',
                data: JSON.stringify(data),
                fecha: $('#new_fecha').val(),
                orden: $('#new_orden_basura').val(),
            };
            post_jquery_m('{{ url('botar_inventario/store_orden_basura') }}', datos, function() {
                cerrar_modals();
                listar_reporte();
            });
        });
    }
</script>
