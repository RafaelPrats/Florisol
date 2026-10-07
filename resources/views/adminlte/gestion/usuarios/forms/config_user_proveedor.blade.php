<div class="usuarios-config-proveedor">
    <div class="usuarios-config-heading">
        <span><i class="fa fa-tree"></i></span>
        <div>
            <strong>Fincas proveedoras</strong>
            <small>Selecciona qué fincas proveedoras podrá gestionar el usuario.</small>
        </div>
    </div>

    <div class="usuarios-config-scroll">
        <table class="usuarios-config-table">
            <thead>
                <tr>
                    <th>Finca proveedora</th>
                    <th class="text-center">Acceso</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($fincas as $f)
                    <tr>
                        <td>{{ $f->nombre }}</td>
                        <td class="text-center">
                            <input type="checkbox" id="check_finca_proveedor_{{ $f->id_finca_proveedor }}"
                                class="mouse-hand checkbox_config_finca_proveedor {{ $f->id_finca_proveedor }}"
                                {{ in_array($f->id_finca_proveedor, $mis_fincas) ? 'checked' : '' }}>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="usuarios-config-actions">
        <button type="button" class="btn btn-usuarios-config-save"
            onclick="store_user_proveedor('{{ $usuario }}')">
            <i class="fa fa-save"></i> Guardar configuración
        </button>
    </div>
</div>

<style>
    .usuarios-config-proveedor {
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
    function store_user_proveedor(id_usuario) {
        data = [];
        checkboxes = $('.checkbox_config_finca_proveedor');
        for (i = 0; i < checkboxes.length; i++) {
            if ($('#' + checkboxes[i].id).prop('checked') == true) {
                var id_emp = document.getElementById(checkboxes[i].id).classList[2];
                data.push(id_emp);
            }
        }
        datos = {
            _token: '{{ csrf_token() }}',
            user: id_usuario,
            data: JSON.stringify(data)
        };
        post_jquery_m('{{ url('usuarios/store_user_proveedor') }}', datos, function(retorno) {
        });
    }
</script>
