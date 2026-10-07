@php
    $fincas_activas = 0;
    foreach ($listado as $finca) {
        if ($finca->estado) {
            $fincas_activas++;
        }
    }
    $fincas_inactivas = count($listado) - $fincas_activas;
@endphp

<div class="mis-fincas">
    <div class="mis-fincas-summary">
        <div class="mis-fincas-summary-card">
            <span class="mis-fincas-summary-icon"><i class="fa fa-map-marker"></i></span>
            <span>
                <strong>{{ count($listado) }}</strong>
                <small>Fincas registradas</small>
            </span>
        </div>
        <div class="mis-fincas-summary-card">
            <span class="mis-fincas-summary-icon is-active"><i class="fa fa-check-circle"></i></span>
            <span>
                <strong>{{ $fincas_activas }}</strong>
                <small>Activas</small>
            </span>
        </div>
        <div class="mis-fincas-summary-card">
            <span class="mis-fincas-summary-icon is-inactive"><i class="fa fa-ban"></i></span>
            <span>
                <strong>{{ $fincas_inactivas }}</strong>
                <small>Inactivas</small>
            </span>
        </div>
    </div>

    <div class="mis-fincas-card">
        <div class="mis-fincas-card-heading">
            <div>
                <h2><i class="fa fa-leaf"></i> Fincas proveedoras</h2>
                <p>Administra contactos, márgenes y plantas asociadas a cada finca.</p>
            </div>
            <button type="button" class="btn btn-yura_primary mis-fincas-add-button"
                onclick="toggle_formulario_finca()">
                <i class="fa fa-plus"></i> Nueva finca
            </button>
        </div>

        <div class="mis-fincas-table-wrap">
            <table class="mis-fincas-table" id="table_listado">
                <thead>
                    <tr>
                        <th>Finca</th>
                        <th class="text-center" style="width: 105px">Margen</th>
                        <th style="min-width: 260px">Teléfonos de contacto</th>
                        <th class="text-center" style="width: 110px">Estado</th>
                        <th class="text-center" style="width: 140px">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="mis-fincas-new-row hidden" id="new_finca_row">
                        <td>
                            <label class="mis-fincas-cell-label" for="new_nombre">Nombre de la finca</label>
                            <input type="text" class="form-control" id="new_nombre" placeholder="Ej. Finca El Rosal">
                        </td>
                        <td>
                            <label class="mis-fincas-cell-label" for="new_margen">Margen %</label>
                            <div class="input-group mis-fincas-margin">
                                <input type="number" step="0.01" class="form-control" id="new_margen" placeholder="0">
                                <span class="input-group-addon">%</span>
                            </div>
                        </td>
                        <td id="contenedor_telefonos">
                            <label class="mis-fincas-cell-label">Celulares Ecuador</label>
                            <div class="mis-fincas-phone-item telefono_item">
                                <input type="tel" class="form-control telefono_input" placeholder="0991234567"
                                    maxlength="13" inputmode="tel">
                                <button type="button" class="btn btn-yura_danger" title="Quitar teléfono"
                                    aria-label="Quitar teléfono" onclick="$(this).closest('.telefono_item').remove()">
                                    <i class="fa fa-times"></i>
                                </button>
                            </div>
                            <button type="button" class="btn btn-link mis-fincas-add-phone tr_new hidden"
                                onclick="agregarTelefono()">
                                <i class="fa fa-plus"></i> Añadir otro teléfono
                            </button>
                        </td>
                        <td class="text-center">
                            <span class="mis-fincas-status is-active"><i class="fa fa-check-circle"></i> Activa</span>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-yura_primary mis-fincas-save"
                                onclick="store_finca()">
                                <i class="fa fa-save"></i> Guardar finca
                            </button>
                        </td>
                    </tr>

                    @forelse ($listado as $pos => $item)
                        @php
                            $telefonos_finca = array_filter(explode('|', $item->telefonos));
                        @endphp
                        <tr class="mis-fincas-data-row {{ !$item->estado ? 'is-disabled' : '' }}">
                            <td>
                                <label class="mis-fincas-cell-label" for="nombre_{{ $item->id_finca_proveedor }}">
                                    Nombre de finca
                                </label>
                                <input type="text" class="form-control mis-fincas-editable"
                                    value="{{ $item->nombre }}"
                                    onchange="update_finca('{{ $item->id_finca_proveedor }}')"
                                    id="nombre_{{ $item->id_finca_proveedor }}">
                            </td>
                            <td>
                                <label class="mis-fincas-cell-label" for="margen_{{ $item->id_finca_proveedor }}">
                                    Margen %
                                </label>
                                <div class="input-group mis-fincas-margin">
                                    <input type="number" step="0.01" class="form-control mis-fincas-editable"
                                        value="{{ $item->margen }}"
                                        onchange="update_finca('{{ $item->id_finca_proveedor }}')"
                                        id="margen_{{ $item->id_finca_proveedor }}">
                                    <span class="input-group-addon">%</span>
                                </div>
                            </td>
                            <td>
                                <label class="mis-fincas-cell-label">Teléfonos</label>
                                <div class="mis-fincas-phone-list">
                                    @foreach ($telefonos_finca as $pos_t => $telefono)
                                        <div class="mis-fincas-phone-item">
                                            <span class="mis-fincas-phone-icon"><i class="fa fa-phone"></i></span>
                                            <input type="tel"
                                                class="form-control mis-fincas-editable telefono_{{ $item->id_finca_proveedor }}"
                                                value="{{ $telefono }}"
                                                onchange="update_finca('{{ $item->id_finca_proveedor }}')"
                                                aria-label="Teléfono {{ $pos_t + 1 }} de {{ $item->nombre }}">
                                        </div>
                                    @endforeach
                                    @if ($item->estado)
                                        <div class="mis-fincas-phone-item">
                                            <span class="mis-fincas-phone-icon is-new"><i class="fa fa-plus"></i></span>
                                            <input type="tel"
                                                class="form-control mis-fincas-editable telefono_{{ $item->id_finca_proveedor }}"
                                                placeholder="Añadir celular (09XXXXXXXX)"
                                                onchange="update_finca('{{ $item->id_finca_proveedor }}')"
                                                aria-label="Añadir teléfono para {{ $item->nombre }}">
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="text-center">
                                @if ($item->estado)
                                    <span class="mis-fincas-status is-active"><i class="fa fa-check-circle"></i>
                                        Activa</span>
                                @else
                                    <span class="mis-fincas-status is-inactive"><i class="fa fa-ban"></i> Inactiva</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="mis-fincas-actions">
                                    <button type="button" class="btn btn-yura_default" title="Asignar plantas"
                                        aria-label="Asignar plantas a {{ $item->nombre }}"
                                        onclick="modal_plantas('{{ $item->id_finca_proveedor }}')">
                                        <i class="fa fa-leaf"></i>
                                    </button>
                                    <button type="button"
                                        class="btn {{ $item->estado ? 'btn-yura_warning' : 'btn-yura_primary' }}"
                                        title="{{ $item->estado ? 'Desactivar finca' : 'Activar finca' }}"
                                        aria-label="{{ $item->estado ? 'Desactivar finca' : 'Activar finca' }}"
                                        onclick="cambiar_estado('{{ $item->id_finca_proveedor }}')">
                                        <i class="fa {{ $item->estado ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="mis-fincas-empty-row">
                            <td colspan="5">
                                <div class="mis-fincas-empty">
                                    <span><i class="fa fa-map-o"></i></span>
                                    <strong>Aún no hay fincas registradas</strong>
                                    <small>Agrega una finca para empezar a gestionar sus contactos y plantas.</small>
                                    <button type="button" class="btn btn-yura_primary"
                                        onclick="toggle_formulario_finca()">
                                        <i class="fa fa-plus"></i> Registrar primera finca
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mis-fincas-card-footer">
            <span><i class="fa fa-info-circle"></i> Los cambios de nombre, margen y teléfono se guardan al salir del
                campo.</span>
            <span>{{ count($listado) }} {{ count($listado) === 1 ? 'finca' : 'fincas' }}</span>
        </div>
    </div>
</div>

<style>
    .mis-fincas {
        --fincas-green: #00B388;
        --fincas-ink: #003729;
        --fincas-muted: #5A7177;
        --fincas-line: #dce9e4;
        --fincas-soft: #f4faf7;
        color: var(--fincas-ink);
        font-size: 13px;
    }

    .mis-fincas-summary {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
        margin-bottom: 11px;
    }

    .mis-fincas-summary-card {
        display: flex;
        min-height: 68px;
        align-items: center;
        gap: 10px;
        padding: 10px 13px;
        border: 1px solid var(--fincas-line);
        border-radius: 9px;
        background: #fff;
        box-shadow: 0 3px 10px rgba(0, 55, 41, .045);
    }

    .mis-fincas-summary-icon {
        display: grid;
        width: 36px;
        height: 36px;
        flex: 0 0 36px;
        place-items: center;
        border-radius: 10px;
        background: #e4f5ee;
        color: var(--fincas-green);
    }

    .mis-fincas-summary-icon.is-active {
        background: #e7f6ee;
        color: #16835f;
    }

    .mis-fincas-summary-icon.is-inactive {
        background: #f1f4f3;
        color: #788681;
    }

    .mis-fincas-summary-card strong,
    .mis-fincas-summary-card small {
        display: block;
    }

    .mis-fincas-summary-card strong {
        font-size: 18px;
        line-height: 1.1;
    }

    .mis-fincas-summary-card small {
        margin-top: 3px;
        color: var(--fincas-muted);
        font-size: 11px;
    }

    .mis-fincas-card {
        overflow: hidden;
        border: 1px solid var(--fincas-line);
        border-radius: 10px;
        background: #fff;
        box-shadow: 0 4px 14px rgba(0, 55, 41, .05);
    }

    .mis-fincas-card-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 13px 15px;
        border-bottom: 1px solid var(--fincas-line);
    }

    .mis-fincas-card-heading h2 {
        margin: 0 0 3px;
        font-size: 15px;
        font-weight: 700;
    }

    .mis-fincas-card-heading h2 i {
        margin-right: 5px;
        color: var(--fincas-green);
    }

    .mis-fincas-card-heading p {
        margin: 0;
        color: var(--fincas-muted);
        font-size: 11px;
    }

    .mis-fincas-add-button,
    .mis-fincas-save {
        flex: 0 0 auto;
        padding: 7px 10px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 700;
    }

    .mis-fincas-table-wrap {
        max-height: min(68vh, 660px);
        overflow: auto;
    }

    .mis-fincas-table {
        width: 100%;
        min-width: 780px;
        margin: 0;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 13px;
    }

    .mis-fincas-table th,
    .mis-fincas-table td {
        padding: 7px 9px;
        border-right: 1px solid #e4eee9;
        border-bottom: 1px solid #e4eee9;
        vertical-align: middle;
    }

    .mis-fincas-table thead th {
        position: sticky;
        top: 0;
        z-index: 3;
        padding-top: 8px;
        padding-bottom: 8px;
        background: var(--fincas-ink);
        color: #fff;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
    }

    .mis-fincas-table tbody tr:nth-child(even):not(.mis-fincas-new-row):not(.mis-fincas-empty-row) {
        background: #f7fbf9;
    }

    .mis-fincas-table .mis-fincas-cell-label {
        display: none;
        margin-bottom: 4px;
        color: var(--fincas-muted);
        font-size: 10px;
        font-weight: 700;
    }

    .mis-fincas-table .form-control {
        height: 33px;
        padding: 5px 8px;
        border-color: var(--fincas-line);
        border-radius: 5px;
        box-shadow: none;
        color: var(--fincas-ink);
        font-size: 12px;
    }

    .mis-fincas-table .form-control:focus {
        border-color: var(--fincas-green);
        box-shadow: 0 0 0 2px rgba(0, 179, 136, .1);
    }

    .mis-fincas-new-row {
        background: #f3faf6;
    }

    .mis-fincas-new-row td {
        vertical-align: top;
    }

    .mis-fincas-margin .input-group-addon {
        border-color: var(--fincas-line);
        background: #edf6f1;
        color: var(--fincas-muted);
        font-size: 11px;
    }

    .mis-fincas-phone-list {
        display: grid;
        gap: 5px;
    }

    .mis-fincas-phone-item {
        display: flex;
        min-width: 0;
        align-items: center;
        gap: 5px;
    }

    .mis-fincas-phone-icon {
        display: grid;
        width: 28px;
        height: 28px;
        flex: 0 0 28px;
        place-items: center;
        border-radius: 7px;
        background: #eaf6f0;
        color: var(--fincas-green);
        font-size: 11px;
    }

    .mis-fincas-phone-icon.is-new {
        background: #f3f6f4;
        color: var(--fincas-muted);
    }

    .mis-fincas-status {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 8px;
        border-radius: 16px;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
    }

    .mis-fincas-status.is-active {
        background: #e5f6ee;
        color: #147452;
    }

    .mis-fincas-status.is-inactive {
        background: #f0f3f2;
        color: #71807a;
    }

    .mis-fincas-data-row.is-disabled .form-control {
        background: #f1f3f2;
        color: #77837e;
    }

    .mis-fincas-actions {
        display: flex;
        justify-content: center;
        gap: 5px;
    }

    .mis-fincas-actions .btn {
        width: 31px;
        height: 31px;
        padding: 5px;
        border-radius: 6px;
    }

    .mis-fincas-add-phone {
        padding: 3px 0;
        color: #087a5f;
        font-size: 11px;
        text-decoration: none;
    }

    .mis-fincas-add-phone:hover,
    .mis-fincas-add-phone:focus {
        color: var(--fincas-ink);
        text-decoration: underline;
    }

    .mis-fincas-empty-row td {
        padding: 0;
        border: 0;
    }

    .mis-fincas-empty {
        display: flex;
        min-height: 200px;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        gap: 6px;
        padding: 20px;
        color: var(--fincas-muted);
        text-align: center;
    }

    .mis-fincas-empty > span {
        display: grid;
        width: 44px;
        height: 44px;
        margin-bottom: 4px;
        place-items: center;
        border-radius: 13px;
        background: #e4f5ee;
        color: var(--fincas-green);
        font-size: 19px;
    }

    .mis-fincas-empty strong {
        color: var(--fincas-ink);
        font-size: 14px;
    }

    .mis-fincas-empty small {
        font-size: 11px;
    }

    .mis-fincas-empty .btn {
        margin-top: 6px;
        border-radius: 6px;
        font-size: 12px;
    }

    .mis-fincas-card-footer {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        padding: 9px 13px;
        color: var(--fincas-muted);
        font-size: 10px;
    }

    .mis-fincas-card-footer i {
        margin-right: 3px;
        color: var(--fincas-green);
    }

    @media (max-width: 620px) {
        .mis-fincas-summary {
            gap: 6px;
        }

        .mis-fincas-summary-card {
            min-height: 60px;
            gap: 7px;
            padding: 8px;
        }

        .mis-fincas-summary-icon {
            width: 30px;
            height: 30px;
            flex-basis: 30px;
        }

        .mis-fincas-summary-card strong {
            font-size: 16px;
        }

        .mis-fincas-summary-card small {
            font-size: 9px;
        }

        .mis-fincas-card-heading {
            align-items: flex-start;
            flex-direction: column;
        }

        .mis-fincas-add-button {
            width: 100%;
        }
    }

    @media (max-width: 480px) {
        .mis-fincas-table {
            min-width: 0;
            border: 0;
        }

        .mis-fincas-table thead {
            display: none;
        }

        .mis-fincas-table,
        .mis-fincas-table tbody,
        .mis-fincas-table tr,
        .mis-fincas-table td {
            display: block;
            width: 100%;
        }

        .mis-fincas-table .mis-fincas-data-row {
            padding: 8px 10px;
            border-bottom: 1px solid var(--fincas-line);
        }

        .mis-fincas-table .mis-fincas-data-row td {
            display: block;
            padding: 5px 0;
            border: 0;
        }

        .mis-fincas-table .mis-fincas-cell-label {
            display: block;
        }

        .mis-fincas-data-row td:nth-child(4) {
            display: inline-block;
            width: auto;
            padding-right: 10px;
        }

        .mis-fincas-data-row td:nth-child(5) {
            display: inline-block;
            width: auto;
            float: right;
        }

        .mis-fincas-new-row td {
            padding: 6px 0;
        }

        .mis-fincas-card-footer {
            flex-direction: column;
            gap: 4px;
        }
    }
</style>

<script>
    function toggle_formulario_finca() {
        $('#new_finca_row').toggleClass('hidden');
        $('.mis-fincas-add-phone').toggleClass('hidden', $('#new_finca_row').hasClass('hidden'));
        if (!$('#new_finca_row').hasClass('hidden')) {
            $('#new_nombre').trigger('focus');
        }
    }

    function agregarTelefono() {
        $('#contenedor_telefonos').append(
            '<div class="mis-fincas-phone-item telefono_item">' +
            '<span class="mis-fincas-phone-icon"><i class="fa fa-phone"></i></span>' +
            '<input type="tel" class="form-control telefono_input" placeholder="0991234567" maxlength="13" inputmode="tel">' +
            '<button type="button" class="btn btn-yura_danger mis-fincas-remove-phone" title="Quitar teléfono" aria-label="Quitar teléfono">' +
            '<i class="fa fa-times"></i></button>' +
            '</div>'
        );
    }

    $(document).off('click.misFincasRemovePhone', '.mis-fincas-remove-phone')
        .on('click.misFincasRemovePhone', '.mis-fincas-remove-phone', function() {
            $(this).closest('.telefono_item').remove();
        });

    function obtenerTelefonos() {
        var telefonos = [];
        var telefonoInvalido = false;

        $('.telefono_input').each(function() {
            var telefonoOriginal = $(this).val().trim();
            var telefono = telefonoOriginal.replace(/\D/g, '');
            if (telefonoOriginal === '') {
                return;
            }

            if (telefono.length === 10 && telefono.substring(0, 2) === '09') {
                telefono = '+593' + telefono.substring(1);
            } else if (telefono.length === 12 && telefono.substring(0, 4) === '5939') {
                telefono = '+' + telefono;
            } else {
                alerta_errores('<div class="alert alert-warning text-center">El teléfono "' +
                    $('<div>').text(telefonoOriginal).html() +
                    '" no es un celular válido de Ecuador.</div>');
                telefonoInvalido = true;
                return false;
            }

            if (telefonos.indexOf(telefono) === -1) {
                telefonos.push(telefono);
            }
        });

        return telefonoInvalido ? false : telefonos.join('|');
    }

    function store_finca() {
        var nombre = $.trim($('#new_nombre').val());
        if (nombre === '') {
            alerta_errores('<div class="alert alert-warning text-center">Ingresa el nombre de la finca.</div>');
            return;
        }

        var telefonos = obtenerTelefonos();
        if (telefonos === false) {
            return;
        }
        if (telefonos === '') {
            alerta_errores('<div class="alert alert-warning text-center">Ingresa al menos un celular válido.</div>');
            return;
        }

        var texto =
            '<div class="mis-fincas-confirm"><span><i class="fa fa-map-marker"></i></span>' +
            '<div><strong>¿Registrar esta finca?</strong><p>' + $('<div>').text(nombre).html() +
            ' quedará disponible para asignar plantas y administrar sus contactos.</p></div></div>';
        modal_quest('modal_store_finca', texto, 'Nueva finca', true, false, 'min(440px, 94vw)',
            function() {
                var datos = {
                    _token: '{{ csrf_token() }}',
                    nombre: nombre,
                    margen: $('#new_margen').val(),
                    telefonos: telefonos,
                };
                post_jquery_m('{{ url('mis_fincas/store_finca') }}', datos, function() {
                    cerrar_modals();
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                });
            });
    }

    function update_finca(id) {
        var telefonos = [];
        $('.telefono_' + id).each(function() {
            var telefono = $.trim($(this).val());
            if (telefono !== '') {
                telefonos.push(telefono);
            }
        });

        if ($.trim($('#nombre_' + id).val()) === '' || telefonos.length === 0) {
            alerta_errores('<div class="alert alert-warning text-center">La finca debe tener nombre y al menos un teléfono.</div>');
            return;
        }

        var datos = {
            _token: '{{ csrf_token() }}',
            id: id,
            nombre: $('#nombre_' + id).val(),
            margen: $('#margen_' + id).val(),
            telefonos: telefonos.join('|'),
        };
        post_jquery_m('{{ url('mis_fincas/update_finca') }}', datos, function() {
            cerrar_modals();
            setTimeout(function() {
                location.reload();
            }, 1000);
        });
    }

    function cambiar_estado(id) {
        var texto =
            '<div class="mis-fincas-confirm is-warning"><span><i class="fa fa-exclamation-triangle"></i></span>' +
            '<div><strong>¿Cambiar el estado de la finca?</strong>' +
            '<p>La finca se activará o desactivará. Puedes volver a cambiar este estado después.</p></div></div>';
        modal_quest('modal_cambiar_estado', texto, 'Estado de finca', true, false, 'min(440px, 94vw)',
            function() {
                var datos = {
                    _token: '{{ csrf_token() }}',
                    id: id,
                };
                post_jquery_m('{{ url('mis_fincas/cambiar_estado') }}', datos, function() {
                    cerrar_modals();
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                });
            });
    }

    function modal_plantas(id) {
        var datos = {
            id: id
        };
        get_jquery('{{ url('mis_fincas/modal_plantas') }}', datos, function(retorno) {
            modal_view('modal_modal_plantas', retorno,
                '<i class="fa fa-leaf"></i> Asignar plantas a la finca',
                true, false, '{{ isPC() ? 'min(680px, 94vw)' : '100%' }}',
                function() {});
        });
    }
</script>

<style>
    .mis-fincas-confirm {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 12px;
        border: 1px solid #dce9e4;
        border-radius: 8px;
        background: #f4faf7;
        color: #003729;
    }

    .mis-fincas-confirm.is-warning {
        border-color: #f0d89a;
        background: #fff9e9;
    }

    .mis-fincas-confirm > span {
        display: grid;
        width: 35px;
        height: 35px;
        flex: 0 0 35px;
        place-items: center;
        border-radius: 9px;
        background: #e4f5ee;
        color: #00B388;
    }

    .mis-fincas-confirm.is-warning > span {
        background: #ffefc5;
        color: #936400;
    }

    .mis-fincas-confirm strong {
        display: block;
        margin-bottom: 3px;
        font-size: 13px;
    }

    .mis-fincas-confirm p {
        margin: 0;
        color: #5A7177;
        font-size: 11px;
        line-height: 1.45;
    }
</style>
