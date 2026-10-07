<div id="table_usuarios" class="usuarios-listado">
    @if (sizeof($listado) > 0)
        <div class="usuarios-listado-scroll">
            <table id="table_content_usuarios" class="usuarios-table">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Correo</th>
                        <th>Cuenta</th>
                        <th>Rol</th>
                        <th class="usuarios-actions-col">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($listado as $item)
                        <tr class="{{ $item->estado == 'A' ? '' : 'is-inactive' }}" id="row_usuarios_{{ $item->id_usuario }}">
                            <td data-label="Usuario">
                                <div class="usuarios-name-cell">
                                    <span class="usuarios-avatar"><i class="fa fa-user"></i></span>
                                    <div>
                                        <strong>{{ $item->nombre_completo }}</strong>
                                        @if ($item->estado == 'A')
                                            <small class="usuarios-badge is-active"><i class="fa fa-check-circle"></i> Activo</small>
                                        @else
                                            <small class="usuarios-badge is-inactive"><i class="fa fa-ban"></i> Inactivo</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td data-label="Correo">
                                <span class="usuarios-mail">{{ $item->correo }}</span>
                            </td>
                            <td data-label="Cuenta">
                                <span class="usuarios-username">{{ $item->username }}</span>
                            </td>
                            <td data-label="Rol">
                                <span class="usuarios-role">{{ $item->rol }}</span>
                            </td>
                            <td data-label="Acciones" class="usuarios-actions-col">
                                <div class="usuarios-actions">
                                    <button type="button" class="btn btn-usuarios-action is-view" title="Detalles"
                                        onclick="ver_usuario('{{ $item->id_usuario }}')"
                                        id="btn_view_usuario_{{ $item->id_usuario }}">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                    @if (getUsuario($item->id_usuario)->rol()->tipo == 'S')
                                        <button type="button"
                                            class="btn btn-usuarios-action {{ $item->estado == 'A' ? 'is-lock' : 'is-unlock' }}"
                                            title="{{ $item->estado == 'A' ? 'Desactivar' : 'Activar' }}"
                                            onclick="eliminar_usuario('{{ $item->id_usuario }}', '{{ $item->estado }}')"
                                            id="btn_usuarios_{{ $item->id_usuario }}">
                                            <i class="fa {{ $item->estado == 'A' ? 'fa-lock' : 'fa-unlock' }}"
                                                id="icon_usuarios_{{ $item->id_usuario }}"></i>
                                        </button>
                                    @endif
                                    <button type="button" class="btn btn-usuarios-action is-finca" title="Fincas"
                                        onclick="config_user_finca('{{ $item->id_usuario }}')">
                                        <i class="fa fa-leaf"></i>
                                    </button>
                                    <button type="button" class="btn btn-usuarios-action is-proveedor" title="Fincas proveedoras"
                                        onclick="config_user_proveedor('{{ $item->id_usuario }}')">
                                        <i class="fa fa-tree"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="usuarios-listado-note">
            <i class="fa fa-info-circle"></i> Usa los botones de cada fila para administrar accesos de finca y seguridad.
        </div>
    @else
        <div class="usuarios-empty">
            <span><i class="fa fa-user-o"></i></span>
            <strong>No se encontraron usuarios</strong>
            <small>Prueba con otra búsqueda o registra un usuario nuevo.</small>
        </div>
    @endif
</div>

<style>
    .usuarios-listado {
        --usuarios-green: #00B388;
        --usuarios-ink: #003729;
        --usuarios-muted: #5A7177;
        --usuarios-line: #dce9e4;
        color: var(--usuarios-ink);
        font-size: 13px;
    }

    .usuarios-listado-scroll {
        max-height: min(68vh, 700px);
        overflow: auto;
    }

    .usuarios-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .usuarios-table th {
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

    .usuarios-table td {
        padding: 7px 10px;
        border-bottom: 1px solid #e8efec;
        background: #fff;
        font-size: 12px;
        vertical-align: middle;
    }

    .usuarios-table tbody tr:nth-child(even) td {
        background: #f7faf8;
    }

    .usuarios-table tbody tr:hover td {
        background: #edf8f3;
    }

    .usuarios-table tbody tr.is-inactive td,
    .usuarios-table tbody tr.is-inactive:nth-child(even) td {
        background: #fbf6f5;
    }

    .usuarios-name-cell {
        display: flex;
        min-width: 220px;
        align-items: center;
        gap: 8px;
    }

    .usuarios-avatar {
        display: grid;
        width: 30px;
        height: 30px;
        flex: 0 0 30px;
        place-items: center;
        border-radius: 9px;
        background: #e6f5ee;
        color: #15936e;
        font-size: 12px;
    }

    .usuarios-name-cell strong,
    .usuarios-name-cell small {
        display: block;
    }

    .usuarios-name-cell strong {
        max-width: 290px;
        overflow: hidden;
        color: var(--usuarios-ink);
        font-size: 12px;
        font-weight: 700;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .usuarios-badge {
        margin-top: 2px;
        font-size: 10px;
        font-weight: 600;
    }

    .usuarios-badge i {
        margin-right: 2px;
    }

    .usuarios-badge.is-active {
        color: #147452;
    }

    .usuarios-badge.is-inactive {
        color: #89645d;
    }

    .usuarios-mail,
    .usuarios-username {
        color: #35574b;
        font-size: 11px;
    }

    .usuarios-role {
        display: inline-block;
        max-width: 190px;
        overflow: hidden;
        padding: 4px 7px;
        border-radius: 15px;
        background: #edf5f1;
        color: #42675b;
        font-size: 10px;
        font-weight: 700;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .usuarios-actions-col {
        width: 170px;
        text-align: center !important;
    }

    .usuarios-actions {
        display: flex;
        justify-content: center;
        gap: 4px;
    }

    .btn-usuarios-action {
        width: 29px;
        height: 29px;
        padding: 4px;
        border: 1px solid #d7e6df;
        border-radius: 5px;
        background: #fff;
        font-size: 12px;
    }

    .btn-usuarios-action.is-view {
        color: #4f6f63;
    }

    .btn-usuarios-action.is-lock {
        color: #a24c4c;
    }

    .btn-usuarios-action.is-unlock {
        color: #6f7f3a;
    }

    .btn-usuarios-action.is-finca {
        color: #087a5f;
    }

    .btn-usuarios-action.is-proveedor {
        color: #315e9b;
    }

    .btn-usuarios-action:hover,
    .btn-usuarios-action:focus {
        border-color: var(--usuarios-green);
        background: #f4faf7;
    }

    .usuarios-listado-note {
        padding: 7px 10px;
        border-top: 1px solid var(--usuarios-line);
        color: var(--usuarios-muted);
        font-size: 10px;
    }

    .usuarios-listado-note i {
        margin-right: 3px;
        color: var(--usuarios-green);
    }

    .usuarios-empty {
        display: flex;
        min-height: 180px;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        gap: 5px;
        padding: 20px;
        color: var(--usuarios-muted);
        text-align: center;
    }

    .usuarios-empty > span {
        display: grid;
        width: 40px;
        height: 40px;
        margin-bottom: 3px;
        place-items: center;
        border-radius: 11px;
        background: #e6f5ee;
        color: var(--usuarios-green);
        font-size: 16px;
    }

    .usuarios-empty strong {
        color: var(--usuarios-ink);
        font-size: 13px;
    }

    .usuarios-empty small {
        font-size: 11px;
    }

    @media (max-width: 680px) {
        .usuarios-listado-scroll {
            max-height: none;
            overflow: visible;
        }

        .usuarios-table,
        .usuarios-table tbody {
            display: block;
            width: 100%;
        }

        .usuarios-table thead {
            display: none;
        }

        .usuarios-table tbody tr {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 0 9px;
            padding: 8px 10px;
            border-bottom: 1px solid var(--usuarios-line);
        }

        .usuarios-table td {
            display: block;
            padding: 5px 0;
            border: 0;
            background: transparent !important;
        }

        .usuarios-table td:first-child {
            grid-column: 1 / -1;
        }

        .usuarios-table td:nth-child(2),
        .usuarios-table td:nth-child(3),
        .usuarios-table td:nth-child(4) {
            grid-column: 1;
        }

        .usuarios-table td:nth-child(2)::before,
        .usuarios-table td:nth-child(3)::before,
        .usuarios-table td:nth-child(4)::before {
            display: inline-block;
            min-width: 55px;
            color: var(--usuarios-muted);
            content: attr(data-label) ":";
            font-size: 10px;
        }

        .usuarios-table td.usuarios-actions-col {
            grid-column: 2;
            grid-row: 2 / span 3;
            align-self: center;
        }

        .usuarios-actions {
            flex-direction: column;
        }
    }
</style>

<script>
    function config_user_finca(user) {
        var datos = {
            user: user
        };
        $.LoadingOverlay('show');
        get_jquery('{{ url('usuarios/config_user_finca') }}', datos, function(retorno) {
            modal_view('modal-view_config_user_finca', retorno,
                '<i class="fa fa-leaf"></i> Configurar fincas del usuario', true, false,
                '{{ isPC() ? 'min(560px, 94vw)' : '100%' }}');
        });
        $.LoadingOverlay('hide');
    }

    function config_user_proveedor(user) {
        var datos = {
            user: user
        };
        $.LoadingOverlay('show');
        get_jquery('{{ url('usuarios/config_user_proveedor') }}', datos, function(retorno) {
            modal_view('modal-view_config_user_proveedor', retorno,
                '<i class="fa fa-tree"></i> Configurar fincas proveedoras', true, false,
                '{{ isPC() ? 'min(560px, 94vw)' : '100%' }}');
        });
        $.LoadingOverlay('hide');
    }
</script>
