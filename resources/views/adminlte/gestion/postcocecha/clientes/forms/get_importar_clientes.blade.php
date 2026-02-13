<div style="overflow-y: scroll; max-height: 500px">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
        <tr class="tr_fija_top_0">
            <th class="text-center th_yura_green">
                Codigo
            </th>
            <th class="text-center th_yura_green">
                Nombre
            </th>
            <th class="text-center th_yura_green">
                Identificacion
            </th>
        </tr>
        @foreach ($listado as $pos => $item)
            @php
                $cambio = 0;
                if ($item['cliente'] == '') {
                    $cambio = 1;
                } elseif ($item['cliente']->codigo != $item['row']['A'] || $item['cliente']->nombre != espacios(mb_strtoupper($item['row']['D'])) || $item['cliente']->ruc != espacios(mb_strtoupper($item['row']['C']))) {
                    $cambio = 1;
                }
            @endphp
            <tr>
                <th class="text-center" style="border-color: #9d9d9d">
                    <input type="text" style="width: 100%" value="{{ $item['row']['A'] }}"
                        id="codigo_cliente_{{ $pos }}"
                        class="text-center {{ $item['cliente'] == '' ? 'error' : '' }}">
                    <input type="hidden" class="pos_cliente" value="{{ $pos }}">
                    <input type="hidden" id="id_cliente_{{ $pos }}"
                        value="{{ $item['cliente'] != '' ? $item['cliente']->id_cliente : '' }}">
                    <input type="hidden" id="cambio_cliente_{{ $pos }}" value="{{ $cambio }}">
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    <input type="text" style="width: 100%; color: {{ $cambio == 1 ? 'red' : '' }}"
                        value="{{ espacios(mb_strtoupper($item['row']['D'])) }}"
                        id="nombre_cliente_{{ $pos }}" class="text-center">
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    <input type="text" style="width: 100%; color: {{ $cambio == 1 ? 'red' : '' }}"
                        value="{{ espacios(mb_strtoupper($item['row']['C'])) }}" id="ruc_cliente_{{ $pos }}"
                        class="text-center">
                </th>
            </tr>
        @endforeach
    </table>
</div>

<div class="text-center" style="margin-top: 5px">
    <button type="button" class="btn btn-yura_primary" onclick="store_importar_clientes()">
        <i class="fa fa-fw fa-save"></i> GRABAR ARCHIVO
    </button>
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
