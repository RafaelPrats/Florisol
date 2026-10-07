<div class="clientes-import-preview">
    <div class="clientes-import-preview-scroll">
        <table id="tabla_preview_clientes">
            <thead>
                <tr>
                    <th>
                Codigo
                    </th>
                    <th>
                Nombre
                    </th>
                    <th>
                Identificacion
                    </th>
                </tr>
            </thead>
            <tbody>
        @foreach ($listado as $pos => $item)
            @php
                $cambio = 0;
                if ($item['cliente'] == '') {
                    $cambio = 1;
                } elseif ($item['cliente']->codigo != $item['row']['A'] || $item['cliente']->nombre != espacios(mb_strtoupper($item['row']['D'])) || $item['cliente']->ruc != espacios(mb_strtoupper($item['row']['C']))) {
                    $cambio = 1;
                }
            @endphp
            <tr class="{{ $cambio ? 'is-changed' : '' }}">
                <td>
                    <input type="text" style="width: 100%" value="{{ $item['row']['A'] }}"
                        id="codigo_cliente_{{ $pos }}"
                        class="text-center {{ $item['cliente'] == '' ? 'error' : '' }}">
                    <input type="hidden" class="pos_cliente" value="{{ $pos }}">
                    <input type="hidden" id="id_cliente_{{ $pos }}"
                        value="{{ $item['cliente'] != '' ? $item['cliente']->id_cliente : '' }}">
                    <input type="hidden" id="cambio_cliente_{{ $pos }}" value="{{ $cambio }}">
                </td>
                <td>
                    <input type="text" style="width: 100%; color: {{ $cambio == 1 ? 'red' : '' }}"
                        value="{{ espacios(mb_strtoupper($item['row']['D'])) }}"
                        id="nombre_cliente_{{ $pos }}" class="text-center">
                </td>
                <td>
                    <input type="text" style="width: 100%; color: {{ $cambio == 1 ? 'red' : '' }}"
                        value="{{ espacios(mb_strtoupper($item['row']['C'])) }}" id="ruc_cliente_{{ $pos }}"
                        class="text-center">
                </td>
            </tr>
        @endforeach
            </tbody>
        </table>
    </div>

    <div class="clientes-import-preview-footer">
        <span><i class="fa fa-info-circle"></i> Las filas resaltadas contienen datos nuevos o modificados.</span>
        <button type="button" class="btn btn-yura_primary" onclick="store_importar_clientes()">
            <i class="fa fa-save"></i> Guardar cambios
        </button>
    </div>
</div>

<script>
    function store_importar_clientes() {
        pos_cliente = $('.pos_cliente');
        data = [];
        for (i = 0; i < pos_cliente.length; i++) {
            pos = pos_cliente[i].value;
            cambio = $('#cambio_cliente_' + pos).val();
            if (cambio == 1) {
                data.push({
                    id: $('#id_cliente_' + pos).val(),
                    nombre: $('#nombre_cliente_' + pos).val(),
                    codigo: $('#codigo_cliente_' + pos).val(),
                    ruc: $('#ruc_cliente_' + pos).val(),
                });
            }
        }
        if (data.length > 0) {
            datos = {
                _token: '{{ csrf_token() }}',
                data: JSON.stringify(data),
            }
            post_jquery_m('{{ url('clientes/store_importar_clientes') }}', datos, function() {
                cerrar_modals();
                buscar_listado();
            });
        } else {
            alerta('<div class="alert alert-info text-center">No se han encontrado CAMBIOS para actualizar</div>')
        }
    }
</script>

<style>
    .clientes-import-preview {
        --clientes-green: #00B388;
        --clientes-ink: #003729;
        --clientes-muted: #5A7177;
        --clientes-line: #dce9e4;
        margin-top: 11px;
        color: var(--clientes-ink);
    }

    .clientes-import-preview-scroll {
        max-height: min(54vh, 500px);
        overflow: auto;
        border: 1px solid var(--clientes-line);
        border-radius: 7px;
    }

    .clientes-import-preview table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .clientes-import-preview th {
        position: sticky;
        z-index: 2;
        top: 0;
        padding: 8px 9px;
        background: #008f6c;
        color: #fff;
        font-size: 11px;
        text-align: center;
    }

    .clientes-import-preview td {
        padding: 6px;
        border-bottom: 1px solid #e8efec;
        background: #fff;
    }

    .clientes-import-preview tbody tr:nth-child(even) td {
        background: #f7faf8;
    }

    .clientes-import-preview tbody tr.is-changed td {
        background: #fff8e7;
    }

    .clientes-import-preview td .form-control,
    .clientes-import-preview td input[type="text"] {
        min-height: 33px;
        padding: 5px 7px;
        border: 1px solid var(--clientes-line);
        border-radius: 4px;
        font-size: 11px;
    }

    .clientes-import-preview-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 9px;
        margin-top: 8px;
    }

    .clientes-import-preview-footer > span {
        color: var(--clientes-muted);
        font-size: 10px;
    }

    .clientes-import-preview-footer > span i {
        margin-right: 3px;
        color: #bb8b19;
    }

    .clientes-import-preview-footer .btn {
        border-radius: 5px;
        font-size: 11px;
        font-weight: 700;
    }

    @media (max-width: 520px) {
        .clientes-import-preview-footer {
            align-items: stretch;
            flex-direction: column;
        }
    }
</style>
