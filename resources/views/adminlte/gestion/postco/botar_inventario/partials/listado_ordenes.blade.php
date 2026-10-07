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
            '<div class="alert alert-warning text-center"><h4>Confirma el desecho de los tallos de esta orden.</h4></div>' +
            '<label for="codigo_autorizacion">Código de autorización</label>' +
            '<input type="password" id="codigo_autorizacion" class="form-control" autocomplete="current-password">';

        modal_quest('modal_completar_orden', texto, 'Desechar la flor', true, false, '40%',
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
    }
</script>
