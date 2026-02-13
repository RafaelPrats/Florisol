<div style="overflow-y: scroll; overflow-x: scroll; max-height: 500px;">
    <table class="table-striped table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
        <tr class="tr_fija_top_0">
            <th class="text-center th_yura_green" style="width: 60%">
                VARIEDAD
            </th>
            <th class="text-center th_yura_green padding_lateral_5">
                LONGITUD
            </th>
            <th class="text-center th_yura_green padding_lateral_5">
                UNIDADES
            </th>
            <th class="text-center th_yura_green padding_lateral_5">
                INVENT.
            </th>
            <th class="text-center th_yura_green padding_lateral_5">
                VENTA
            </th>
        </tr>
        @foreach ($listado as $pos => $item)
            <tr id="tr_variedad_{{ $item->id_variedad }}" class="{{ $item->estado == 0 ? 'error' : '' }}">
                <th class="text-center" style="border-color: #9d9d9d">
                    <input type="hidden" class="variedades_listados" value="{{ $item->id_variedad }}">
                    <input type="hidden" id="nombre_planta_{{ $item->id_variedad }}"
                        value="{{ $item->planta->nombre }}">
                    <input type="text" readonly id="nombre_variedad_{{ $item->id_variedad }}" style="width: 100%"
                        class="text-center" value="{{ $item->nombre }}">
                    <span class="hidden">{{ $item->nombre }}</span>
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    <input type="number" style="width: 100%" class="text-center" required min="0"
                        id="longitud_{{ $item->id_variedad }}" value="{{ $longitud_pedido }}">
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    <input type="number" style="width: 100%" class="text-center" required min="0"
                        id="cantidad_{{ $item->id_variedad }}"
                        onkeyup="calcular_venta_busqueda('{{ $item->id_variedad }}')"
                        onchange="calcular_venta_busqueda('{{ $item->id_variedad }}')">
                </th>
                <td class="text-center" style="border-color: #9d9d9d"
                    id="td_inventario_busqueda_{{ $item->id_variedad }}">
                    {{ getTotalInventarioByVariedad($item->id_variedad) }}
                </td>
                <td class="text-center" style="border-color: #9d9d9d" id="td_venta_busqueda_{{ $item->id_variedad }}">
                </td>
            </tr>
        @endforeach
    </table>
</div>

<script>
    function calcular_venta_busqueda(variedad) {
        ramos_pedido = parseInt($('#ramos_pedido').val());
        unidades = parseInt($('#cantidad_' + variedad).val());
        venta = ramos_pedido * unidades;
        $('#td_venta_busqueda_' + variedad).html(venta);
        inventario = parseInt($('#td_inventario_busqueda_' + variedad).html());
        if (inventario < venta)
            $('#td_venta_busqueda_' + variedad).addClass('error');
        else
            $('#td_venta_busqueda_' + variedad).removeClass('error');

    }
</script>
