<div class="baja-motives">
    <div class="baja-motives-intro">
        <span class="baja-motives-icon"><i class="fa fa-list-alt"></i></span>
        <div>
            <strong>Catálogo de motivos</strong>
            <p>Activa los motivos disponibles al crear una nueva orden. Los motivos inactivos se conservan en el historial.</p>
        </div>
    </div>

    <div class="baja-motives-toolbar">
        <span><strong>{{ count($motivos) }}</strong> motivos registrados</span>
        <button type="button" class="btn btn-yura_default" onclick="add_motivo()">
            <i class="fa fa-plus"></i> Agregar motivo
        </button>
    </div>

    <div class="baja-motives-table-wrap">
        <table class="baja-motives-table">
            <thead>
                <tr>
                    <th>Motivo</th>
                    <th style="width: 115px">Estado</th>
                    <th class="text-center" style="width: 105px">Acciones</th>
                </tr>
            </thead>
            <tbody id="baja_motives_body">
                <tr id="tr_grabar_nuevos_motivos" class="hidden">
                    <td colspan="3" class="text-right">
                        <button type="button" class="btn btn-yura_primary btn-sm" onclick="store_motivos()">
                            <i class="fa fa-save"></i> Guardar nuevos motivos
                        </button>
                    </td>
                </tr>
                @forelse ($motivos as $m)
                    <tr>
                        <td>
                            <input type="text" value="{{ $m->nombre }}" id="nombre_motivo_{{ $m->id_motivo_baja }}"
                                class="form-control {{ !$m->estado ? 'baja-motive-disabled' : '' }}"
                                aria-label="Nombre del motivo">
                        </td>
                        <td>
                            @if ($m->estado)
                                <span class="baja-motive-status baja-motive-active"><i class="fa fa-check-circle"></i>
                                    Activo</span>
                            @else
                                <span class="baja-motive-status baja-motive-inactive"><i class="fa fa-ban"></i>
                                    Inactivo</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="baja-motive-actions">
                                <button type="button" class="btn btn-yura_warning" title="Guardar nombre"
                                    aria-label="Guardar nombre" onclick="update_motivo('{{ $m->id_motivo_baja }}')">
                                    <i class="fa fa-save"></i>
                                </button>
                                <button type="button" class="btn btn-yura_{{ $m->estado ? 'danger' : 'primary' }}"
                                    title="{{ $m->estado ? 'Desactivar motivo' : 'Activar motivo' }}"
                                    aria-label="{{ $m->estado ? 'Desactivar motivo' : 'Activar motivo' }}"
                                    onclick="cambiar_estado_motivo('{{ $m->id_motivo_baja }}')">
                                    <i class="fa fa-{{ $m->estado ? 'ban' : 'check' }}"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr class="baja-no-motives">
                        <td colspan="3"><i class="fa fa-info-circle"></i> Aún no hay motivos. Agrega el primero para habilitar
                            la creación de órdenes.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<style>
    .baja-motives {
        color: #003729;
        font-size: 14px;
    }

    .baja-motives-intro {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 12px;
        padding: 11px;
        border: 1px solid #dce9e4;
        border-radius: 8px;
        background: #f4faf7;
    }

    .baja-motives-icon {
        display: grid;
        width: 36px;
        height: 36px;
        flex: 0 0 36px;
        place-items: center;
        border-radius: 9px;
        background: #e4f5ee;
        color: #00B388;
    }

    .baja-motives-intro strong {
        font-size: 14px;
    }

    .baja-motives-intro p {
        margin: 3px 0 0;
        color: #5A7177;
        font-size: 12px;
    }

    .baja-motives-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 9px;
        margin-bottom: 8px;
        color: #5A7177;
        font-size: 12px;
    }

    .baja-motives-toolbar strong {
        color: #003729;
        font-size: 14px;
    }

    .baja-motives-toolbar .btn {
        padding: 6px 9px;
        border-radius: 5px;
        font-size: 12px;
    }

    .baja-motives-table-wrap {
        max-height: min(60vh, 500px);
        overflow: auto;
        border: 1px solid #dce9e4;
        border-radius: 7px;
    }

    .baja-motives-table {
        width: 100%;
        min-width: 380px;
        margin: 0;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 13px;
    }

    .baja-motives-table th,
    .baja-motives-table td {
        padding: 6px 8px;
        border-right: 1px solid #e4eee9;
        border-bottom: 1px solid #e4eee9;
        vertical-align: middle;
    }

    .baja-motives-table thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        background: #003729;
        color: #fff;
        font-size: 12px;
    }

    .baja-motives-table tbody tr:nth-child(even) {
        background: #f5faf7;
    }

    .baja-motives-table .form-control {
        height: 32px;
        border-color: #dce9e4;
        font-size: 13px;
    }

    .baja-motive-disabled {
        color: #7b898d;
        background: #f0f2f1 !important;
    }

    .baja-motive-status {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }

    .baja-motive-active {
        color: #087a5f;
    }

    .baja-motive-inactive {
        color: #7b898d;
    }

    .baja-motive-actions {
        display: flex;
        justify-content: center;
        gap: 4px;
    }

    .baja-motive-actions .btn {
        width: 29px;
        height: 29px;
        padding: 5px;
        border-radius: 5px;
    }

    .baja-no-motives td {
        padding: 20px;
        color: #5A7177;
        text-align: center;
    }

    @media (max-width: 480px) {
        .baja-motives-intro {
            align-items: flex-start;
        }

        .baja-motives-toolbar {
            align-items: flex-start;
            flex-direction: column;
        }
    }
</style>

<script>
    var num_motivos = 0;

    function add_motivo() {
        num_motivos++;
        $('.baja-no-motives').remove();
        $('#tr_grabar_nuevos_motivos').before(
            '<tr class="baja-new-motive-row">' +
            '<td><input type="text" id="new_motivo_' + num_motivos +
            '" class="form-control" placeholder="Nombre del motivo" aria-label="Nombre del nuevo motivo"></td>' +
            '<td><span class="baja-motive-status baja-motive-active"><i class="fa fa-check-circle"></i> Activo al guardar</span></td>' +
            '<td></td></tr>'
        );
        $('#tr_grabar_nuevos_motivos').removeClass('hidden');
        $('#new_motivo_' + num_motivos).focus();
    }

    function store_motivos() {
        var data = [];
        $('.baja-new-motive-row input').each(function() {
            var nombre = $.trim($(this).val());
            if (nombre !== '') {
                data.push(nombre);
            }
        });
        if (!data.length) {
            alerta_errores("<div class='alert alert-warning text-center'>Escribe al menos un nombre de motivo.</div>");
            return;
        }
        var datos = {
            _token: '{{ csrf_token() }}',
            data: JSON.stringify(data),
        };
        post_jquery_m('{{ url('botar_inventario/store_motivos') }}', datos, function() {
            cerrar_modals();
            admin_motivos();
        });
    }

    function update_motivo(id) {
        var nombre = $.trim($('#nombre_motivo_' + id).val());
        if (nombre === '') {
            alerta_errores("<div class='alert alert-warning text-center'>El nombre del motivo no puede quedar vacío.</div>");
            return;
        }
        var datos = {
            _token: '{{ csrf_token() }}',
            id: id,
            nombre: nombre,
        };
        post_jquery_m('{{ url('botar_inventario/update_motivo') }}', datos, function() {
            cerrar_modals();
            admin_motivos();
        });
    }

    function cambiar_estado_motivo(id) {
        var datos = {
            _token: '{{ csrf_token() }}',
            id: id,
        };
        post_jquery_m('{{ url('botar_inventario/cambiar_estado_motivo') }}', datos, function() {
            cerrar_modals();
            admin_motivos();
        });
    }
</script>
