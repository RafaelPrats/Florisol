@php
    $ordenes_pendientes = $listado->where('estado_orden_basura', 0)->count();
    $ordenes_completadas = $listado->where('estado_orden_basura', 1)->count();
    $total_tallos = $listado->sum(function ($orden) {
        return $orden['detalles']->sum('basura');
    });
@endphp

<div class="baja-summary">
    <div class="baja-summary-card">
        <span class="baja-summary-icon"><i class="fa fa-file-text-o"></i></span>
        <span><strong class="baja-summary-value">{{ $listado->count() }}</strong><small class="baja-summary-label">Órdenes
                del día</small></span>
    </div>
    <div class="baja-summary-card">
        <span class="baja-summary-icon"><i class="fa fa-clock-o"></i></span>
        <span><strong class="baja-summary-value">{{ $ordenes_pendientes }}</strong><small
                class="baja-summary-label">Pendientes</small></span>
    </div>
    <div class="baja-summary-card">
        <span class="baja-summary-icon"><i class="fa fa-cubes"></i></span>
        <span><strong class="baja-summary-value">{{ number_format($total_tallos) }}</strong><small
                class="baja-summary-label">Tallos en órdenes ({{ $ordenes_completadas }} completadas)</small></span>
    </div>
</div>

<div class="baja-table-card baja-orders">
    <div class="baja-table-heading">
        <h3><i class="fa fa-list-alt"></i> Órdenes de flor de baja</h3>
        <span class="baja-table-note">Las órdenes pendientes se pueden ajustar antes de completarlas.</span>
    </div>

    @if ($listado->count())
        <div class="baja-table-scroll">
            <table class="baja-table">
                <thead>
                    <tr>
                        <th style="width: 72px">N° orden</th>
                        <th style="width: 105px">Fecha</th>
                        <th style="width: 105px">Estado</th>
                        <th>Planta</th>
                        <th>Variedad</th>
                        <th class="text-center" style="width: 78px">Longitud</th>
                        <th class="text-center" style="width: 62px">TxR</th>
                        <th>Motivo</th>
                        <th class="text-right" style="width: 125px">Tallos a botar</th>
                        <th class="text-center" style="width: 125px">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($listado as $item)
                        @foreach ($item['detalles'] as $pos_d => $det)
                            <tr class="baja-order-row">
                                @if ($pos_d == 0)
                                    <th rowspan="{{ count($item['detalles']) }}">#{{ $item['orden_basura'] }}</th>
                                    <th rowspan="{{ count($item['detalles']) }}">{{ $item['fecha'] }}</th>
                                    <th rowspan="{{ count($item['detalles']) }}">
                                        @if ($item['estado_orden_basura'])
                                            <span class="baja-status baja-status-done"><i class="fa fa-check-circle"></i>
                                                Completada</span>
                                        @else
                                            <span class="baja-status baja-status-pending"><i class="fa fa-clock-o"></i>
                                                Pendiente</span>
                                        @endif
                                    </th>
                                @endif
                                <th>{{ $det->pta_nombre }}</th>
                                <th>{{ $det->var_nombre }}</th>
                                <th class="text-center">{{ $det->longitud }}</th>
                                <th class="text-center">{{ $det->tallos_x_ramo }}</th>
                                <th>{{ $det->motivo_nombre }}</th>
                                <th>
                                    <input type="number" min="0" step="1"
                                        class="baja-order-quantity input_orden_{{ $item['orden_basura'] }}"
                                        {{ !$item['estado_orden_basura'] ? '' : 'disabled' }}
                                        data-id="{{ $det->id_salidas_recepcion }}" value="{{ $det->basura }}"
                                        aria-label="Tallos a botar">
                                </th>
                                @if ($pos_d == 0)
                                    <th rowspan="{{ count($item['detalles']) }}" class="text-center">
                                        @if ($item['estado_orden_basura'] == 0)
                                            <div class="baja-order-actions">
                                                <button type="button" class="btn btn-yura_primary"
                                                    title="Guardar cambios" aria-label="Guardar cambios"
                                                    onclick="update_orden('{{ $item['orden_basura'] }}')">
                                                    <i class="fa fa-save"></i>
                                                </button>
                                                <button type="button" class="btn btn-yura_warning"
                                                    title="Completar orden" aria-label="Completar orden"
                                                    onclick="completar_orden('{{ $item['orden_basura'] }}')">
                                                    <i class="fa fa-check"></i>
                                                </button>
                                                <button type="button" class="btn btn-yura_danger"
                                                    title="Eliminar orden" aria-label="Eliminar orden"
                                                    onclick="delete_orden_basura('{{ $item['orden_basura'] }}')">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </div>
                                        @else
                                            <span class="text-muted">Sin acciones</span>
                                        @endif
                                    </th>
                                @endif
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="baja-empty"><i class="fa fa-calendar-times-o"></i> No hay órdenes para la fecha y filtros
            seleccionados.</div>
    @endif
</div>

<style>
    .baja-orders .baja-table {
        min-width: 920px;
    }

    .baja-order-row th {
        vertical-align: middle !important;
    }

    .baja-order-row th[rowspan] {
        background: #f4faf7;
    }

    .baja-order-quantity {
        width: 100%;
        min-width: 78px;
        height: 30px;
        padding: 3px 7px;
        border: 1px solid #dce9e4;
        border-radius: 5px;
        background: #fff;
        color: #003729;
        text-align: right;
        font-size: 14px;
        font-weight: 700;
    }

    .baja-order-quantity:disabled {
        background: #f4faf7;
        color: #5A7177;
    }

    .baja-status {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 7px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
    }

    .baja-status-done {
        background: #e4f5ee;
        color: #003729;
    }

    .baja-status-pending {
        background: #fff3d9;
        color: #795600;
    }

    .baja-order-actions {
        display: flex;
        justify-content: center;
        gap: 4px;
    }

    .baja-order-actions .btn {
        width: 29px;
        height: 29px;
        padding: 5px;
        border-radius: 5px;
    }

    .baja-auth-modal {
        padding: 3px 2px 0;
        color: #003729;
    }

    .baja-auth-warning {
        display: flex;
        align-items: flex-start;
        gap: 11px;
        margin-bottom: 16px;
        padding: 13px;
        border: 1px solid #f0d89a;
        border-radius: 8px;
        background: #fff9e9;
    }

    .baja-auth-warning-icon {
        display: grid;
        width: 36px;
        height: 36px;
        flex: 0 0 36px;
        place-items: center;
        border-radius: 10px;
        background: #ffefc5;
        color: #936400;
        font-size: 16px;
    }

    .baja-auth-warning strong {
        display: block;
        margin: 0 0 3px;
        font-size: 14px;
    }

    .baja-auth-warning p {
        margin: 0;
        color: #6c6046;
        font-size: 12px;
        line-height: 1.45;
    }

    .baja-auth-label {
        display: block;
        margin-bottom: 6px;
        color: #003729;
        font-size: 13px;
        font-weight: 700;
    }

    .baja-auth-input-group {
        display: flex;
        width: 100%;
        align-items: stretch;
    }

    .baja-auth-input-icon,
    .baja-auth-toggle {
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #dce9e4;
        background: #f4faf7;
        color: #5A7177;
    }

    .baja-auth-input-icon {
        width: 40px;
        flex: 0 0 40px;
        border-right: 0;
        border-radius: 6px 0 0 6px;
    }

    .baja-auth-input {
        min-width: 0;
        height: 42px;
        flex: 1 1 auto;
        border: 1px solid #dce9e4;
        border-radius: 0;
        background: #fff;
        color: #003729;
        font-size: 17px;
        letter-spacing: .12em;
        text-align: center;
        box-shadow: none;
    }

    .baja-auth-input:focus {
        z-index: 1;
        border-color: #00B388;
        box-shadow: 0 0 0 2px rgba(0, 179, 136, .12);
    }

    .baja-auth-toggle {
        min-width: 44px;
        flex: 0 0 44px;
        border-left: 0;
        border-radius: 0 6px 6px 0;
        cursor: pointer;
    }

    .baja-auth-toggle:hover,
    .baja-auth-toggle:focus {
        background: #e4f5ee;
        color: #003729;
        outline: 0;
    }

    .baja-auth-hint {
        display: block;
        margin-top: 6px;
        color: #5A7177;
        font-size: 11px;
    }

    @media (max-width: 480px) {
        .baja-auth-warning {
            padding: 10px;
        }
    }
</style>

<script>
    function update_orden(orden) {
        var texto =
            '<div class="alert alert-warning text-center"><h4>¿Está seguro de modificar la orden?</h4></div>';

        modal_quest('modal_update_orden', texto, 'Modificar la orden', true, false, '40%',
            function() {
                var input_orden = $('.input_orden_' + orden);
                var data = [];
                for (var i = 0; i < input_orden.length; i++) {
                    data.push({
                        id: $(input_orden[i]).data('id'),
                        basura: $(input_orden[i]).val(),
                    });
                }
                var datos = {
                    _token: '{{ csrf_token() }}',
                    orden: orden,
                    data: JSON.stringify(data),
                };
                post_jquery_m('{{ url('botar_inventario/update_orden') }}', datos, function() {
                    cerrar_modals();
                    listar_ordenes();
                });
            });
    }

    function delete_orden_basura(orden) {
        var texto =
            '<div class="alert alert-warning text-center"><h4>¿Está seguro de eliminar la orden?</h4></div>';

        modal_quest('modal_delete_orden_basura', texto, 'Eliminar la orden', true, false, '40%',
            function() {
                var datos = {
                    _token: '{{ csrf_token() }}',
                    orden: orden,
                };
                post_jquery_m('{{ url('botar_inventario/delete_orden_basura') }}', datos, function() {
                    cerrar_modals();
                    listar_ordenes();
                });
            });
    }

    function completar_orden(orden) {
        var texto =
            '<div class="baja-auth-modal">' +
            '<div class="baja-auth-warning">' +
            '<span class="baja-auth-warning-icon"><i class="fa fa-exclamation-triangle"></i></span>' +
            '<div><strong>Confirma el desecho de los tallos</strong>' +
            '<p>Esta acción registrará el desecho de los tallos y actualizará el inventario. Para continuar, confirma tu autorización.</p></div>' +
            '</div>' +
            '<label for="codigo_autorizacion" class="baja-auth-label">Código de autorización</label>' +
            '<div class="baja-auth-input-group">' +
            '<span class="baja-auth-input-icon"><i class="fa fa-lock"></i></span>' +
            '<input type="password" id="codigo_autorizacion" class="baja-auth-input" ' +
            'placeholder="Ingresa el código" autocomplete="off" autocapitalize="off" spellcheck="false" ' +
            'aria-describedby="baja_auth_hint">' +
            '<button type="button" class="baja-auth-toggle" onclick="toggle_baja_auth_visibility(this)" ' +
            'aria-label="Mostrar código" title="Mostrar código"><i class="fa fa-eye"></i></button>' +
            '</div>' +
            '<small id="baja_auth_hint" class="baja-auth-hint"><i class="fa fa-info-circle"></i> El código se utiliza solo para autorizar esta operación.</small>' +
            '</div>';

        modal_quest('modal_completar_orden', texto, 'Desechar la flor', true, false, 'min(440px, 94vw)',
            function() {
                var datos = {
                    _token: '{{ csrf_token() }}',
                    orden: orden,
                    codigo: $('#codigo_autorizacion').val(),
                };
                post_jquery_m('{{ url('botar_inventario/completar_orden') }}', datos, function() {
                    cerrar_modals();
                    listar_ordenes();
                });
            });
        setTimeout(function() {
            $('#codigo_autorizacion').trigger('focus');
        }, 100);
    }

    function toggle_baja_auth_visibility(button) {
        var input = $('#codigo_autorizacion');
        var mostrar = input.attr('type') === 'password';
        input.attr('type', mostrar ? 'text' : 'password');
        $(button)
            .attr('aria-label', mostrar ? 'Ocultar código' : 'Mostrar código')
            .attr('title', mostrar ? 'Ocultar código' : 'Mostrar código')
            .find('i')
            .toggleClass('fa-eye', !mostrar)
            .toggleClass('fa-eye-slash', mostrar);
    }
</script>
