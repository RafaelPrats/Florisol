<div class="receta-table-scroll">
    <table class="table-striped table-bordered receta-table" style="width: 100%; border: 1px solid #9d9d9d" id="table_catalogo">
        <thead>
            <tr class="tr_fija_top_0">
                <th class="text-center th_yura_green" style="width: 25%">
                    <button type="button" class="receta-sort-button"
                        onclick="ordenar_catalogo_receta(this, 0, 'text')">
                        VARIEDAD <i class="fa fa-fw fa-sort" aria-hidden="true"></i>
                    </button>
                </th>
                <th class="text-center th_yura_green" style="width: 15%">
                    <button type="button" class="receta-sort-button"
                        onclick="ordenar_catalogo_receta(this, 1, 'text')">
                        COLOR <i class="fa fa-fw fa-sort" aria-hidden="true"></i>
                    </button>
                </th>
                <th class="text-center th_yura_green padding_lateral_5">
                    <button type="button" class="receta-sort-button"
                        onclick="ordenar_catalogo_receta(this, 2, 'number')">
                        LONGITUD <i class="fa fa-fw fa-sort" aria-hidden="true"></i>
                    </button>
                </th>
                <th class="text-center th_yura_green padding_lateral_5">
                    <button type="button" class="receta-sort-button"
                        onclick="ordenar_catalogo_receta(this, 3, 'number')">
                        UNIDADES <i class="fa fa-fw fa-sort" aria-hidden="true"></i>
                    </button>
                </th>
                <th class="text-center th_yura_green padding_lateral_5">
                    <button type="button" class="receta-sort-button"
                        onclick="ordenar_catalogo_receta(this, 4, 'number')">
                        INV. DISP. <i class="fa fa-fw fa-sort" aria-hidden="true"></i>
                    </button>
                </th>
                <th class="text-center th_yura_green padding_lateral_5">
                    <button type="button" class="receta-sort-button"
                        onclick="ordenar_catalogo_receta(this, 5, 'number')">
                        VENTA <i class="fa fa-fw fa-sort" aria-hidden="true"></i>
                    </button>
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach ($listado as $pos => $item)
                <tr id="tr_variedad_{{ $item->id_variedad }}" class="{{ $item->estado == 0 ? 'error' : '' }}">
                    <th class="text-center text-sm" style="border-color: #9d9d9d">
                        <input type="hidden" class="variedades_listados" value="{{ $item->id_variedad }}">
                        <input type="hidden" id="nombre_planta_{{ $item->id_variedad }}"
                            value="{{ $item->planta->nombre }}">
                        <input type="hidden" readonly id="nombre_variedad_{{ $item->id_variedad }}" style="width: 100%"
                            class="text-center" value="{{ $item->nombre }}">
                        {{ $item->nombre }}
                    </th>
                    <th class="text-center text-sm" style="border-color: #9d9d9d">
                        {{ $item->color }}
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        <input type="number" style="width: 100%" class="text-center" required min="0"
                            id="longitud_{{ $item->id_variedad }}" value="{{ $longitud_pedido }}">
                        <span class="hidden">{{ $longitud_pedido }}</span>
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        <input type="number" style="width: 100%" class="text-center" required min="0"
                            id="cantidad_{{ $item->id_variedad }}"
                            onkeyup="calcular_venta_busqueda('{{ $item->id_variedad }}')"
                            onchange="calcular_venta_busqueda('{{ $item->id_variedad }}')">
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d; background-color: rgba(0, 179, 136, .08)"
                        id="td_inventario_busqueda_{{ $item->id_variedad }}">
                        {{ getInventarioDisponibleByVariedadFechaSegmento($item, $postco_fecha, $segmento) }}
                    </th>
                    <td class="text-center" style="border-color: #9d9d9d"
                        id="td_venta_busqueda_{{ $item->id_variedad }}">
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<script>
    function ordenar_catalogo_receta(button, columna, tipo) {
        var $button = $(button);
        var $table = $button.closest('table');
        var $headers = $table.find('thead th');
        var $header = $button.closest('th');
        var direccion = $header.attr('data-sort-direction') === 'asc' ? 'desc' : 'asc';
        var filas = $table.find('tbody tr').get();

        filas.sort(function(a, b) {
            var $celdaA = $(a).children('td, th').eq(columna);
            var $celdaB = $(b).children('td, th').eq(columna);
            var valorA;
            var valorB;
            var comparacion;

            if ($celdaA.find('input[type="number"]').length) {
                valorA = $celdaA.find('input[type="number"]').first().val();
                valorB = $celdaB.find('input[type="number"]').first().val();
            } else {
                valorA = $celdaA.text().trim();
                valorB = $celdaB.text().trim();
            }

            if (tipo === 'number') {
                valorA = parseFloat(String(valorA).replace(/[^\d.-]/g, '')) || 0;
                valorB = parseFloat(String(valorB).replace(/[^\d.-]/g, '')) || 0;
                comparacion = valorA - valorB;
            } else {
                comparacion = String(valorA).localeCompare(String(valorB), undefined, {
                    sensitivity: 'base',
                    numeric: true
                });
            }

            return direccion === 'asc' ? comparacion : -comparacion;
        });

        $table.children('tbody').append(filas);
        $headers.removeAttr('data-sort-direction aria-sort');
        $headers.find('.receta-sort-button i')
            .removeClass('fa-sort-up fa-sort-down')
            .addClass('fa-sort');
        $header.attr('data-sort-direction', direccion)
            .attr('aria-sort', direccion === 'asc' ? 'ascending' : 'descending');
        $button.find('i')
            .removeClass('fa-sort')
            .addClass(direccion === 'asc' ? 'fa-sort-up' : 'fa-sort-down');
    }

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
