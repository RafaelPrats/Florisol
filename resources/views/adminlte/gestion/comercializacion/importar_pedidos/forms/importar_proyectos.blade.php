<div style="overflow-x: scroll; overflow-y: scroll; max-height: 500px">
    <table class="table-bordered" style="border: 1px solid #9d9d9d; width: 100%">
        <tr>
            <th class="text-center th_yura_green">
            </th>
            <th class="text-center th_yura_green">
                Fecha
                <input type="date" value="" class="text-center" id="fecha_all_pedido" value=""
                    onchange="$('.fecha_all_pedido').val($(this).val())" style="width: 100%; color: black !important"
                    required>
            </th>
            <th class="text-center th_yura_green">
                Cliente
            </th>
            <th class="text-center th_yura_green">
                #Pedido Ref.
            </th>
            <th class="text-center th_yura_green hidden" style="width: 80px">
                Cajas
            </th>
            <th class="text-center th_yura_green">
                Variedad
            </th>
            <th class="text-center th_yura_green">
                Código Rainbow
            </th>
            <th class="text-center bg-yura_dark" style="width: 80px">
                Longitud
            </th>
            <th class="text-center th_yura_green" style="width: 80px">
                Ramos
            </th>
        </tr>
        @php
            $cajas_anterior = $listado[0]['cajas'];
            $error_orden = false;
        @endphp
        @foreach ($listado as $pos => $item)
        @php
            $bg_celda = $item['row']['A'] == 1 ? '#b9ffb9' : '#50c7ff';
            if($item['error_orden']){
                $bg_celda = '#fb9696';
                $error_orden = true;
            }
        @endphp
            <tr>
                <td class="text-center"
                    style="border-color: #9d9d9d; background-color: {{ $bg_celda }}">
                    <small>{{ $pos + 8 }}</small>
                </td>
                <th class="text-center" style="border-color: #9d9d9d">
                    <input type="date" value="{{ $item['fecha'] }}" class="text-center fecha_all_pedido"
                        id="fecha_pedido_{{ $pos }}" style="width: 100%" required>
                    <input type="hidden" class="pos_pedidos" value="{{ $pos }}">
                    <input type="hidden" id="es_orden_fija_{{ $pos }}" value="{{ $item['row']['A'] }}">
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    @if ($item['cliente'] != '')
                        {{ $item['cliente']->nombre }}
                        <input type="hidden" value="{{ $item['cliente']->id_cliente }}"
                            id="cliente_pedido_{{ $pos }}">
                    @else
                        <span class="error">{{ $item['row']['D'] }}</span>
                    @endif
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    <input type="number" value="{{ $item['codigo_ref'] }}" class="text-center"
                        id="codigo_ref_{{ $pos }}"
                        style="width: 100%; background-color: {{ $item['codigo_ref'] != '' ? '#dddddd' : '#d01c62' }}"
                        readonly>
                </th>
                <th class="text-center hidden" style="border-color: #9d9d9d">
                    <input type="number" value="{{ $item['cajas'] != '' ? $item['cajas'] : $cajas_anterior }}"
                        class="text-center" id="cajas_pedido_{{ $pos }}"
                        style="width: 100%; background-color: #dddddd" readonly>
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    @if ($item['variedad'] != '')
                        {{ $item['variedad']->nombre }}
                        <input type="hidden" value="{{ $item['variedad']->id_variedad }}"
                            id="variedad_pedido_{{ $pos }}">
                    @else
                        <span class="error">{{ $item['row']['L'] }}</span>
                    @endif
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    @if ($item['variedad'] != '')
                        {{ $item['variedad']->siglas }}
                    @else
                        <span class="error">{{ $item['row']['M'] }}</span>
                    @endif
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    <input type="number" value="{{ $item['longitud'] }}" class="text-center"
                        id="longitud_pedido_{{ $pos }}" style="width: 100%; background-color: #dddddd"
                        readonly>
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    <input type="number" value="{{ $item['ramos'] }}" class="text-center"
                        id="ramos_pedido_{{ $pos }}" style="width: 100%; background-color: #dddddd" readonly>
                </th>
            </tr>
            @php
                $cajas_anterior = $item['cajas'] != '' ? $item['cajas'] : $cajas_anterior;
            @endphp
        @endforeach
    </table>
</div>

<div class="text-center" style="margin-top: 5px">
    @if (!$fallos && !$error_orden)
        <button type="button" class="btn btn-yura_primary" onclick="store_importar_pedidos()">
            <i class="fa fa-fw fa-save"></i> Grabar Pedidos
        </button>
    @else
        <button type="button" class="btn btn-yura_danger">
            <i class="fa fa-fw fa-ban"></i> Hay fallos <sup>ROJO</sup> en el archivo. Debe corregirlos para poder
            grabar
        </button>
    @endif
</div>

<script>
    function store_importar_pedidos() {
        pos_pedidos = $('.pos_pedidos');
        data = [];
        for (i = 0; i < pos_pedidos.length; i++) {
            pos = parseInt(pos_pedidos[i].value);
            es_orden_fija = $('#es_orden_fija_' + pos).val();
            codigo_ref = $('#codigo_ref_' + pos).val();
            fecha = $('#fecha_pedido_' + pos).val();
            if (fecha == '') {
                alerta('<div class="alert alert-warning text-center">Falta la <b>FECHA</b> en la fila #' + (pos + 8) +
                    '</div>');
                return false;
            }
            cliente = $('#cliente_pedido_' + pos).val();
            variedad = $('#variedad_pedido_' + pos).val();
            cajas = $('#cajas_pedido_' + pos).val();
            longitud = $('#longitud_pedido_' + pos).val();
            ramos = parseInt($('#ramos_pedido_' + pos).val());
            // BUSCAR SI EXISTE "numero-variedad-longitud" EN "data" PARA AGRUPAR LOS RAMOS
            pos_in_data = -1;
            for (x = 0; x < data.length; x++) {
                if (data[x]['codigo_ref'] == codigo_ref &&
                    data[x]['variedad'] == variedad &&
                    data[x]['longitud'] == longitud)
                    pos_in_data = x;
            }
            if (pos_in_data == -1) // NO EXISTE, ES NUEVO
                data.push({
                    es_orden_fija: es_orden_fija,
                    codigo_ref: codigo_ref,
                    fecha: fecha,
                    cliente: cliente,
                    variedad: variedad,
                    cajas: cajas,
                    longitud: longitud,
                    ramos: ramos,
                });
            else // SI EXISTE, AGRUPAR RAMOS
                data[pos_in_data]['ramos'] += ramos;
        }

        datos = {
            _token: '{{ csrf_token() }}',
            data: JSON.stringify(data),
        }
        post_jquery_m('{{ url('importar_pedidos/store_importar_pedidos') }}', datos, function() {
            cerrar_modals();
            listar_reporte();
        });
    }
</script>
