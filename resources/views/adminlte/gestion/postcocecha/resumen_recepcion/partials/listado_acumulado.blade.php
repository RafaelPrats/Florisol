<table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d" id="table_inventario_cosecha">
    <thead>
        <tr class="tr_fija_top_0">
            <th class="text-center th_yura_green padding_lateral_5" rowspan="2">
                Planta
            </th>
            <th class="text-center th_yura_green padding_lateral_5" rowspan="2">
                Variedad
            </th>
            <th class="text-center th_yura_green" colspan="{{ count($fechas) }}">
                Dias
            </th>
            <th class="text-center th_yura_green padding_lateral_5" rowspan="2">
                Ingreso
            </th>
            <th class="text-center th_yura_green padding_lateral_5" rowspan="2">
                Inventario
            </th>
            <th class="text-center th_yura_green padding_lateral_5" rowspan="2">
                Ventas
            </th>
            <th class="text-center th_yura_green padding_lateral_5" rowspan="2">
                Necesidad
            </th>
            <th class="text-center th_yura_green padding_lateral_5 campo_devoluciones" colspan="2">
                Despachos
                <button type="button" class="btn btn-xs btn-yura_default pull-right" title="Cambiar a Devoluciones"
                    onclick="$('.campo_devoluciones').toggleClass('hidden')">
                    <i class="fa fa-fw fa-refresh"></i>
                </button>
            </th>
            <th class="text-center th_yura_green padding_lateral_5 campo_devoluciones hidden" colspan="2">
                Devoluciones
                <button type="button" class="btn btn-xs btn-yura_default pull-right" title="Cambiar a Despachos"
                    onclick="$('.campo_devoluciones').toggleClass('hidden')">
                    <i class="fa fa-fw fa-refresh"></i>
                </button>
            </th>
            <th class="text-center th_yura_green padding_lateral_5 campo_devoluciones" rowspan="2">
                Sacar
            </th>
            <th class="text-center th_yura_green padding_lateral_5 campo_devoluciones" rowspan="2">
                Basura
            </th>
            <th class="text-center th_yura_green padding_lateral_5" rowspan="2">
            </th>
        </tr>
        <tr class="tr_fija_top_1">
            @php
                $totales_fechas = [];
            @endphp
            @foreach ($fechas as $pos_f => $f)
                <th class="text-center padding_lateral_5 bg-yura_dark" title="{{ convertDateToText($f) }}">
                    {{ difFechas(hoy(), $f)->d }} @if ($pos_f == count($fechas) - 1)
                        ...
                    @endif
                </th>
                @php
                    $totales_fechas[] = 0;
                @endphp
            @endforeach
            <th class="text-center padding_lateral_5 bg-yura_dark campo_devoluciones">
                Salidas
            </th>
            <th class="text-center padding_lateral_5 bg-yura_dark campo_devoluciones">
                Basura
            </th>
            <th class="text-center padding_lateral_5 bg-yura_dark campo_devoluciones hidden">
                Salidas
            </th>
            <th class="text-center padding_lateral_5 bg-yura_dark campo_devoluciones hidden">
                Basura
            </th>
        </tr>
    </thead>
    <tbody>
        @php
            $total_ingresos = 0;
            $total_ventas = 0;
            $total_salidas = 0;
            $total_basura = 0;
        @endphp
        @foreach ($listado as $item)
            <tr onmouseover="$(this).addClass('bg-aqua')" onmouseleave="$(this).removeClass('bg-aqua')">
                <th class="text-center padding_lateral_5" style="border-color: #9d9d9d"
                    id="tr_acumulado_{{ $item['combinacion']->id_variedad }}">
                    {{ $item['combinacion']->planta_nombre }}
                    <input type="hidden" class="ids_variedad" value="{{ $item['combinacion']->id_variedad }}">
                </th>
                <th class="text-center padding_lateral_5" style="border-color: #9d9d9d">
                    {{ $item['combinacion']->variedad_nombre }}
                </th>
                @php
                    $total_combinacion = 0;
                    $total_ingreso_combinacion = 0;
                @endphp
                @foreach ($item['valores'] as $pos_v => $v)
                    @php
                        $total_ingreso_combinacion += $v->cantidad;
                        $total_combinacion += $v->disponibles;
                        $totales_fechas[$pos_v] += $v->disponibles;
                    @endphp
                    <td class="text-center padding_lateral_5"
                        style="background-color: #dddddd !important; color: black !important; border-color: #9d9d9d"
                        title="{{ convertDateToText($fechas[$pos_v]) }}">
                        @if ($v->disponibles > 0)
                            {{ number_format($v->disponibles) }}
                        @endif
                    </td>
                @endforeach
                @php
                    $necesidad = $total_combinacion - $item['venta'] + $item['salidas'] + $item['basura'];
                    $total_ingresos += $total_ingreso_combinacion;
                    $total_ventas += $item['venta'];
                    $total_salidas += $item['salidas'];
                    $total_basura += $item['basura'];
                @endphp
                <th class="text-center padding_lateral_5" style="border-color: #9d9d9d">
                    {{ number_format($total_ingreso_combinacion) }}
                </th>
                <th class="text-center padding_lateral_5" style="border-color: #9d9d9d">
                    {{ number_format($total_combinacion) }}
                </th>
                <th class="text-center padding_lateral_5" style="border-color: #9d9d9d">
                    @if ($item['venta'] > 0)
                        <button type="button" class="btn btn-xs btn-yura_dark" title="Ver Pedidos"
                            onclick="detalle_ventas('{{ $item['combinacion']->id_variedad }}')">
                            {{ number_format($item['venta']) }}
                        </button>
                    @endif
                </th>
                <th class="text-center padding_lateral_5 {{ $necesidad < 0 ? 'error' : '' }}"
                    style="border-color: #9d9d9d">
                    {{ number_format($necesidad) }}
                </th>
                <th class="text-center padding_lateral_5 campo_devoluciones"
                    style="border-color: #9d9d9d; background-color: #dddddd; color: black !important">
                    {{ number_format($item['salidas']) }}
                </th>
                <th class="text-center padding_lateral_5 campo_devoluciones"
                    style="border-color: #9d9d9d; background-color: #dddddd; color: black !important">
                    {{ number_format($item['basura']) }}
                </th>
                <th class="text-center campo_devoluciones hidden" style="border-color: #9d9d9d">
                    <input type="number" style="width: 100%; color: black !important" class="text-center"
                        max="{{ $item['salidas'] != '' ? $item['salidas'] : 0 }}" min="0" placeholder="0"
                        id="devolucion_salidas_{{ $item['combinacion']->id_variedad }}">
                </th>
                <th class="text-center campo_devoluciones hidden"
                    style="border-color: #9d9d9d; background-color: #dddddd; color: black !important">
                    <input type="number" style="width: 100%; color: black !important" class="text-center"
                        max="{{ $item['basura'] != '' ? $item['basura'] : 0 }}" min="0" placeholder="0"
                        id="devolucion_basura_{{ $item['combinacion']->id_variedad }}">
                </th>
                <th class="text-center campo_devoluciones" style="border-color: #9d9d9d">
                    <input type="number" style="width: 100%; color: black !important" class="text-center"
                        max="{{ $total_combinacion }}" min="0" placeholder="0"
                        id="sacar_acumulado_{{ $item['combinacion']->id_variedad }}">
                </th>
                <th class="text-center campo_devoluciones" style="border-color: #9d9d9d">
                    <input type="number" style="width: 100%; color: black !important" class="text-center"
                        max="{{ $total_combinacion }}" min="0" placeholder="0"
                        id="basura_acumulado_{{ $item['combinacion']->id_variedad }}">
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    <button type="button" class="btn btn-xs btn-yura_primary campo_devoluciones"
                        onclick="sacar_inventario('{{ $item['combinacion']->id_variedad }}')">
                        <i class="fa fa-fw fa-check"></i>
                    </button>
                    <button type="button" class="btn btn-xs btn-yura_dark campo_devoluciones hidden"
                        onclick="store_devolucion('{{ $item['combinacion']->id_variedad }}')">
                        <i class="fa fa-fw fa-check"></i>
                    </button>
                </th>
            </tr>
        @endforeach
    </tbody>
    <tr class="tr_fija_bottom_0">
        <th class="text-center th_yura_green" colspan="2">
            Totales
        </th>
        @php
            $total_inventario = 0;
        @endphp
        @foreach ($totales_fechas as $pos_v => $v)
            @php
                $total_inventario += $v;
            @endphp
            <th class="text-center bg-yura_dark" title="{{ convertDateToText($fechas[$pos_v]) }}">
                @if ($v > 0)
                    {{ number_format($v) }}
                @endif
            </th>
        @endforeach
        @php
            $total_necesidad = $total_inventario - $total_ventas + $total_salidas + $total_basura;
        @endphp
        <th class="text-center th_yura_green">
            {{ number_format($total_ingresos) }}
        </th>
        <th class="text-center th_yura_green">
            {{ number_format($total_inventario) }}
        </th>
        <th class="text-center th_yura_green">
            {{ number_format($total_ventas) }}
        </th>
        <th class="text-center th_yura_green" style="color: {{ $total_necesidad < 0 ? 'red' : '' }}">
            {{ number_format($total_necesidad) }}
        </th>
        <th class="text-center bg-yura_dark campo_devoluciones">
            {{ number_format($total_salidas) }}
        </th>
        <th class="text-center bg-yura_dark campo_devoluciones">
            {{ number_format($total_basura) }}
        </th>
        <th class="text-center th_yura_green" colspan="3">
            <button type="button" class="btn btn-xs btn-block btn-yura_default campo_devoluciones"
                onclick="sacar_all_inventario()">
                <i class="fa fa-fw fa-save"></i> Grabar TODO
            </button>
        </th>
    </tr>
</table>

<script>
    function sacar_inventario(variedad) {
        sacar = $('#sacar_acumulado_' + variedad).val();
        sacar = sacar == '' ? 0 : parseInt(sacar);
        basura = $('#basura_acumulado_' + variedad).val();
        basura = basura == '' ? 0 : parseInt(basura);
        datos = {
            _token: '{{ csrf_token() }}',
            variedad: variedad,
            sacar: sacar,
            basura: basura,
            fecha: $('#fecha_venta_filtro').val(),
        }
        if ((datos['sacar'] > 0 || datos['basura'] > 0) && (datos['sacar'] + datos['basura']) <= parseInt($(
                    '#sacar_acumulado_' + variedad)
                .prop('max')))
            post_jquery_m('{{ url('inventario_cosecha/sacar_inventario') }}', datos, function() {
                listar_inventario_cosecha_acumulado('T');
            });
    }

    function sacar_all_inventario() {
        ids_variedad = $('.ids_variedad');
        data = [];
        for (i = 0; i < ids_variedad.length; i++) {
            variedad = ids_variedad[i].value;
            sacar = $('#sacar_acumulado_' + variedad).val();
            sacar = sacar == '' ? 0 : parseInt(sacar);
            basura = $('#basura_acumulado_' + variedad).val();
            basura = basura == '' ? 0 : parseInt(basura);
            if ((sacar > 0 || basura > 0) && (sacar + basura) <= parseInt($('#sacar_acumulado_' + variedad).prop(
                    'max')))
                data.push({
                    variedad: variedad,
                    sacar: sacar,
                    basura: basura,
                });
        }
        datos = {
            _token: '{{ csrf_token() }}',
            data: JSON.stringify(data),
            fecha: $('#fecha_venta_filtro').val(),
        }
        if (data.length > 0)
            post_jquery_m('{{ url('inventario_cosecha/sacar_all_inventario') }}', datos, function() {
                listar_inventario_cosecha_acumulado('T');
            });
    }

    function detalle_ventas(variedad) {
        datos = {
            _token: '{{ csrf_token() }}',
            variedad: variedad,
            fecha: $('#fecha_venta_filtro').val(),
        }
        get_jquery('{{ url('inventario_cosecha/detalle_ventas') }}', datos, function(retorno) {
            modal_view('modal_detalle_ventas', retorno,
                '<i class="fa fa-fw fa-plus"></i> Detalle de los Pedidos',
                true, false, '{{ isPC() ? '70%' : '' }}',
                function() {});
        });
    }

    function store_devolucion(variedad) {
        sacar = $('#devolucion_salidas_' + variedad).val();
        sacar = sacar == '' ? 0 : parseInt(sacar);
        basura = $('#devolucion_basura_' + variedad).val();
        basura = basura == '' ? 0 : parseInt(basura);
        datos = {
            _token: '{{ csrf_token() }}',
            variedad: variedad,
            sacar: sacar,
            basura: basura,
            fecha: $('#fecha_venta_filtro').val(),
        }
        if ((datos['sacar'] > 0 || datos['basura'] > 0) && datos['sacar'] <= parseInt($('#devolucion_salidas_' +
                variedad).prop('max')) && datos['basura'] <= parseInt($('#devolucion_basura_' + variedad).prop('max')))
            post_jquery_m('{{ url('inventario_cosecha/store_devolucion') }}', datos, function() {
                listar_inventario_cosecha_acumulado('T');
            });
    }
</script>
