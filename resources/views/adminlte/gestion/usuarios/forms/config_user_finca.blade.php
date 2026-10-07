<div class="usuarios-config-finca">
    <div class="usuarios-config-heading">
        <span><i class="fa fa-leaf"></i></span>
        <div>
            <strong>Fincas habilitadas</strong>
            <small>Selecciona las fincas a las que el usuario tendrá acceso.</small>
        </div>
    </div>

    <div class="usuarios-config-scroll">
        <table class="usuarios-config-table">
            <thead>
                <tr>
                    <th>Finca</th>
                    <th class="text-center">Acceso</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($empresas as $emp)
                    @php
                        $getUsuarioFincaByFincas = getUsuarioFincaByFincas($fincas, $emp->id_configuracion_empresa);
                    @endphp
                    <tr>
                        <td>{{ $emp->nombre }}</td>
                        <td class="text-center">
                            <input type="checkbox" id="check_finca_user_{{ $emp->id_configuracion_empresa }}"
                                class="mouse-hand checkbox_config_finca_user {{ $emp->id_configuracion_empresa }}"
                                {{ $getUsuarioFincaByFincas != '' ? 'checked' : '' }}>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="usuarios-config-actions">
        <button type="button" class="btn btn-usuarios-config-save" onclick="store_finca_user()">
            <i class="fa fa-save"></i> Guardar configuración
        </button>
    </div>
</div>
<input type="hidden" id="id_usuario_config_finca" value="{{ $usuario->id_usuario }}">

<style>
    .usuarios-config-finca {
        --usuarios-green: #00B388;
        --usuarios-ink: #003729;
        --usuarios-muted: #5A7177;
        --usuarios-line: #dce9e4;
        color: var(--usuarios-ink);
    }

    .usuarios-config-heading {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 9px;
        padding: 9px 10px;
        border: 1px solid var(--usuarios-line);
        border-radius: 7px;
        background: #f4faf7;
    }

    .usuarios-config-heading > span {
        display: grid;
        width: 30px;
        height: 30px;
        flex: 0 0 30px;
        place-items: center;
        border-radius: 8px;
        background: #e4f5ee;
        color: var(--usuarios-green);
    }

    .usuarios-config-heading strong,
    .usuarios-config-heading small {
        display: block;
    }

    .usuarios-config-heading strong {
        font-size: 12px;
    }

    .usuarios-config-heading small {
        margin-top: 2px;
        color: var(--usuarios-muted);
        font-size: 10px;
    }

    .usuarios-config-scroll {
        max-height: min(52vh, 420px);
        overflow: auto;
        border: 1px solid var(--usuarios-line);
        border-radius: 7px;
    }

    .usuarios-config-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .usuarios-config-table th {
        position: sticky;
        top: 0;
        z-index: 1;
        padding: 7px 9px;
        border-bottom: 1px solid #00674f;
        background: #008f6c;
        color: #fff;
        font-size: 10px;
        font-weight: 700;
        text-align: left;
    }

    .usuarios-config-table td {
        padding: 7px 9px;
        border-bottom: 1px solid #e8efec;
        background: #fff;
        font-size: 12px;
    }

    .usuarios-config-table tbody tr:nth-child(even) td {
        background: #f7faf8;
    }

    .usuarios-config-table input[type="checkbox"] {
        width: 16px;
        height: 16px;
        accent-color: var(--usuarios-green);
        cursor: pointer;
    }

    .usuarios-config-actions {
        margin-top: 9px;
        text-align: right;
    }

    .btn-usuarios-config-save {
        min-height: 34px;
        padding: 7px 12px;
        border: 1px solid var(--usuarios-green);
        border-radius: 5px;
        background: var(--usuarios-green);
        color: #fff;
        font-size: 11px;
        font-weight: 700;
    }

    .btn-usuarios-config-save:hover,
    .btn-usuarios-config-save:focus {
        border-color: #008f6c;
        background: #008f6c;
        color: #fff;
    }
</style>

<script>
    function store_finca_user() {
        data = [];
        checkboxes = $('.checkbox_config_finca_user');
        for (i = 0; i < checkboxes.length; i++) {
            if ($('#' + checkboxes[i].id).prop('checked') == true) {
                var id_emp = document.getElementById(checkboxes[i].id).classList[2];
                data.push(id_emp);
            }
        }
        datos = {
            _token: '{{ csrf_token() }}',
            user: $('#id_usuario_config_finca').val(),
            data: data
        };
        post_jquery('{{ url('usuarios/store_finca_user') }}', datos, function(retorno) {
            cerrar_modals();
            location.reload();
        });
    }
</script>
