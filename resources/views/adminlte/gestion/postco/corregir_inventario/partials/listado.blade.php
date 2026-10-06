@php
    $total_revision = 0;
    foreach ($listado as $item) {
        if ($item->disponibles != $item->saldo) {
            $total_revision++;
        }
    }
@endphp

<div class="ci-transaction-card">
    <div class="ci-transaction-title">
        <span class="ci-icon"><i class="fa fa-file-text-o"></i></span>
        <span>Datos del ajuste</span>
    </div>
    <div class="ci-transaction-fields">
        <div class="ci-transaction-field">
            <label for="new_fecha">Fecha de corrección</label>
            <input type="date" name="new_fecha" id="new_fecha" class="form-control input-yura_default"
                value="{{ hoy() }}">
        </div>
        <div class="ci-transaction-field">
            <label for="new_orden">N° de orden</label>
            <input type="text" name="new_orden" id="new_orden" class="form-control input-yura_default"
                value="{{ $last_orden }}">
        </div>
    </div>
</div>

<div class="ci-results-card">
    <div class="ci-card-heading">
        <div class="ci-result-title">
            <span class="ci-icon"><i class="fa fa-cubes"></i></span>
            <div>
                <h3>Comparación de inventario</h3>
                <p>Contrasta el kardex con el inventario actual antes de registrar la corrección.</p>
            </div>
        </div>
        <div class="ci-result-badges">
            <span class="ci-badge">{{ count($listado) }} variedades</span>
            <span class="ci-badge {{ $total_revision > 0 ? 'ci-badge-warning' : '' }}">
                {{ $total_revision > 0 ? $total_revision . ' por revisar' : 'Sin diferencias' }}
            </span>
        </div>
    </div>

    <div class="ci-table-wrap">
        <table class="ci-table" id="table_listado">
            <colgroup>
                <col style="width: 22%">
                <col style="width: 29%">
                <col style="width: 14%">
                <col style="width: 17%">
                <col style="width: 18%">
            </colgroup>
            <thead>
                <tr>
                    <th>
                        <button type="button" class="ci-sort-button"
                            onclick="ordenar_corregir_inventario(this, 0, 'text')">
                            Planta <i class="fa fa-fw fa-sort" aria-hidden="true"></i>
                        </button>
                    </th>
                    <th>
                        <button type="button" class="ci-sort-button"
                            onclick="ordenar_corregir_inventario(this, 1, 'text')">
                            Variedad <i class="fa fa-fw fa-sort" aria-hidden="true"></i>
                        </button>
                    </th>
                    <th class="text-right">
                        <button type="button" class="ci-sort-button"
                            onclick="ordenar_corregir_inventario(this, 2, 'number')">
                            Kardex <i class="fa fa-fw fa-sort" aria-hidden="true"></i>
                        </button>
                    </th>
                    <th class="text-right">
                        <button type="button" class="ci-sort-button"
                            onclick="ordenar_corregir_inventario(this, 3, 'number')">
                            Inventario actual <i class="fa fa-fw fa-sort" aria-hidden="true"></i>
                        </button>
                    </th>
                    <th class="text-center">Corrección</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($listado as $item)
                    @php
                        $diferencia = $item->disponibles != $item->saldo;
                    @endphp
                    <tr>
                        <td class="ci-name">{{ $item->pta_nombre }}</td>
                        <td class="ci-name">{{ $item->var_nombre }}</td>
                        <td class="ci-number">{{ number_format($item->saldo) }}</td>
                        <td class="ci-number {{ $diferencia ? 'ci-inventory-alert' : '' }}">
                            {{ number_format($item->disponibles) }}
                        </td>
                        <td class="ci-input-cell text-center">
                            @if (!$diferencia)
                                <input type="number" class="ci-correction-input input_corregir" min="0"
                                    aria-label="Nuevo inventario para {{ $item->var_nombre }}"
                                    id="tallos_corregir_{{ $item->id_variedad }}"
                                    data-id_variedad="{{ $item->id_variedad }}"
                                    data-disponibles="{{ $item->disponibles }}">
                            @else
                                <span class="ci-status ci-status-review">
                                    <i class="fa fa-fw fa-exclamation-circle"></i> Revisar
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr class="ci-empty-row">
                        <td class="ci-empty" colspan="5">
                            <i class="fa fa-inbox fa-2x"></i>
                            <div style="margin-top: 6px">No hay variedades con inventario para estos filtros.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="ci-results-footer">
        <small><i class="fa fa-info-circle"></i> Ingresa el saldo físico deseado solo en las filas habilitadas.</small>
        <button type="button" class="btn btn-yura_primary ci-save-button" onclick="store_correccion()">
            <i class="fa fa-fw fa-save"></i> Grabar corrección
        </button>
    </div>
</div>

<script>
    function ordenar_corregir_inventario(button, columna, tipo) {
        var $button = $(button);
        var $table = $button.closest('table');
        var $headers = $table.find('thead th');
        var $header = $button.closest('th');
        var direccion = $header.attr('data-sort-direction') === 'asc' ? 'desc' : 'asc';
        var filas = $table.children('tbody').children('tr').not('.ci-empty-row').get();

        filas.sort(function(a, b) {
            var valorA = $(a).children('td').eq(columna).text().trim();
            var valorB = $(b).children('td').eq(columna).text().trim();
            var comparacion;

            if (tipo === 'number') {
                comparacion = (parseFloat(valorA.replace(/[^\d.-]/g, '')) || 0) -
                    (parseFloat(valorB.replace(/[^\d.-]/g, '')) || 0);
            } else {
                comparacion = valorA.localeCompare(valorB, undefined, {
                    sensitivity: 'base',
                    numeric: true
                });
            }

            return direccion === 'asc' ? comparacion : -comparacion;
        });

        $table.children('tbody').append(filas);
        $headers.removeAttr('data-sort-direction aria-sort');
        $headers.find('.ci-sort-button i')
            .removeClass('fa-sort-up fa-sort-down')
            .addClass('fa-sort');
        $header.attr('data-sort-direction', direccion)
            .attr('aria-sort', direccion === 'asc' ? 'ascending' : 'descending');
        $button.find('i')
            .removeClass('fa-sort')
            .addClass(direccion === 'asc' ? 'fa-sort-up' : 'fa-sort-down');
    }

    function store_correccion() {
        data = [];
        input_corregir = $('.input_corregir');
        for (i = 0; i < input_corregir.length; i++) {
            id = input_corregir[i].id;
            id_variedad = $('#' + id).data('id_variedad');
            anterior = parseInt($('#' + id).data('disponibles'));
            actual = parseInt($('#' + id).val());
            diferencia = actual - anterior;
            if (actual >= 0 && diferencia != 0) {
                data.push({
                    id_variedad: id_variedad,
                    anterior: anterior,
                    actual: actual,
                    diferencia: diferencia,
                });
            }
        }
        if (data.length > 0) {
            texto =
                '<div class="alert alert-warning text-center"><i class="fa fa-fw fa-exclamation-triangle"></i> ¿Está seguro de <b>grabar la corrección</b> de ' +
                data.length + ' variedad(es)?</div>' +
                '<div class="input-group">' +
                '<div class="input-group-addon span-input-group-yura-fixed bg-yura_dark">' +
                'Código de autorización' +
                '</div>' +
                '<input type="password" id="codigo_autorizacion" style="width: 100%" class="text-center form-control">' +
                '</div>';

            modal_quest('modal_store_correccion', texto, 'Confirmar corrección de inventario', true, false, '40%',
                function() {
                    datos = {
                        _token: '{{ csrf_token() }}',
                        fecha: $('#new_fecha').val(),
                        orden: $('#new_orden').val(),
                        bodega: $('#bodega_filtro').val(),
                        data: JSON.stringify(data),
                        codigo: $('#codigo_autorizacion').val(),
                    }
                    post_jquery_m('{{ url('corregir_inventario/store_correccion') }}', datos, function() {
                        cerrar_modals();
                        listar_reporte();
                    });
                })
        }
    }
</script>
