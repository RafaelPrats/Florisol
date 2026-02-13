<div style="overflow-x: scroll">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d" id="table_inventario_cosecha">
        <thead>
            <tr class="tr_fija_top_0">
                <th class="text-center th_yura_green padding_lateral_5" rowspan="2" style="min-width: 120px">
                    Proveedor
                </th>
                <th class="text-center th_yura_green padding_lateral_5" rowspan="2" style="min-width: 120px">
                    Planta
                </th>
                <th class="text-center th_yura_green padding_lateral_5" rowspan="2" style="min-width: 120px">
                    Variedad
                </th>
                <th class="text-center th_yura_green padding_lateral_5" rowspan="2" style="min-width: 90px">
                    Longitud
                </th>
                <th class="text-center bg-yura_dark padding_lateral_5" rowspan="2" style="width: 220px"
                    colspan="2">
                    Hoy
                </th>
                <th class="text-center th_yura_green" colspan="{{ count($fechas) }}" style="width: 220px">
                    Dias
                </th>
                <th class="text-center th_yura_green padding_lateral_5" rowspan="2">
                    Saldo
                    <br>
                    <button type="button" class="btn btn-xs btn-yura_default" title="Exportar"
                        onclick="exportar_listado_compra_flor()">
                        <i class="fa fa-fw fa-file-excel-o"></i>
                    </button>
                </th>
            </tr>
            <tr class="tr_fija_top_1">
                @php
                    $totales_fechas = [];
                @endphp
                @foreach ($fechas as $pos_f => $f)
                    <th class="text-center bg-yura_dark" title="{{ convertDateToText($f) }}" style="width: 80px">
                        <div style="width: 60px">
                            -{{ difFechas(hoy(), $f)->d }} @if ($pos_f == count($fechas) - 1)
                                ...
                            @endif
                        </div>
                    </th>
                    @php
                        $totales_fechas[] = 0;
                    @endphp
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($listado as $pos => $item)
                <tr onmouseover="$(this).addClass('bg-aqua')" onmouseleave="$(this).removeClass('bg-aqua')">
                    <th class="text-center padding_lateral_5" style="border-color: #9d9d9d">
                        {{ $item['combinacion']->proveedor_nombre }}
                    </th>
                    <th class="text-center padding_lateral_5" style="border-color: #9d9d9d">
                        {{ $item['combinacion']->planta_nombre }}
                    </th>
                    <th class="text-center padding_lateral_5" style="border-color: #9d9d9d">
                        {{ $item['combinacion']->variedad_nombre }}
                    </th>
                    <th class="text-center padding_lateral_5" style="border-color: #9d9d9d">
                        {{ $item['combinacion']->longitud }}<sup>cm</sup>
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        <div style="width: 200px">
                            <input type="hidden" id="id_variedad_compra_{{ $pos }}"
                                value="{{ $item['combinacion']->id_variedad }}">
                            <input type="hidden" id="id_proveedor_compra_{{ $pos }}"
                                value="{{ $item['combinacion']->id_proveedor }}">
                            <input type="hidden" id="longitud_compra_{{ $pos }}"
                                value="{{ $item['combinacion']->longitud }}">
                            <div class="input-group">
                                @if ($item['inventario_hoy'] > 0)
                                    <input type="number" style="width: 100%; color: black !important"
                                        class="text-center"
                                        value="{{ $item['inventario_hoy'] - $item['compra_parcial'] }}" min="1"
                                        id="confirmar_compra_{{ $pos }}">
                                @else
                                    <input type="number" style="width: 100%; color: black !important"
                                        class="text-center" value="" min="1"
                                        id="confirmar_compra_{{ $pos }}">
                                @endif
                                <div class="input-group-btn">
                                    <button type="button" class="btn btn-xs btn-yura_dark"
                                        style="height: 26px; border-radius: 0"
                                        onclick="confirmar_compra('{{ $pos }}')">
                                        <i class="fa fa-fw fa-check"></i> Recibir
                                    </button>
                                    <button type="button" class="btn btn-xs btn-yura_default"
                                        style="height: 26px; border-radius: 0"
                                        onclick="prorrogar_compra('{{ $pos }}')">
                                        <i class="fa fa-fw fa-plus"></i> Nuevo
                                    </button>
                                </div>
                            </div>
                        </div>
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        <div style="width: 180px">
                            @if ($item['inventario_hoy'] > 0)
                                <div class="input-group">
                                    <input type="number" style="width: 100%; color: black !important"
                                        class="text-center" min="1" id="compra_parcial_{{ $pos }}">
                                    <div class="input-group-btn">
                                        <button type="button" class="btn btn-xs btn-yura_dark"
                                            style="height: 26px; border-radius: 0"
                                            onclick="store_compra_parcial('{{ $pos }}')">
                                            Parcial:
                                            <b>{{ $item['compra_parcial'] > 0 ? $item['compra_parcial'] : 0 }}/{{ $item['inventario_hoy'] > 0 ? $item['inventario_hoy'] : 0 }}</b>
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </th>
                    @php
                        $total_combinacion = 0;
                    @endphp
                    @foreach ($item['valores'] as $pos_v => $v)
                        @php
                            $total_combinacion += $v;
                            $totales_fechas[$pos_v] += $v;
                        @endphp
                        <td class="text-center"
                            style="background-color: #dddddd !important; color: black !important; border-color: #9d9d9d"
                            title="{{ convertDateToText($fechas[$pos_v]) }}">
                            @if ($v > 0)
                                <input type="number" style="width: 100%" value="{{ $v }}"
                                    class="text-center"
                                    onchange="update_compra('{{ $pos }}', '{{ $fechas[$pos_v] }}', '{{ $pos_v }}')"
                                    id="update_compra_{{ $pos }}_{{ $pos_v }}">
                            @endif
                        </td>
                    @endforeach
                    <th class="text-center padding_lateral_5" style="border-color: #9d9d9d">
                        {{ number_format($total_combinacion) }}
                    </th>
                </tr>
            @endforeach
        </tbody>
        <tr class="tr_fija_bottom_0">
            <th class="text-center th_yura_green" colspan="6">
                Totales
            </th>
            @php
                $total = 0;
            @endphp
            @foreach ($totales_fechas as $pos_v => $v)
                @php
                    $total += $v;
                @endphp
                <th class="text-center bg-yura_dark" title="{{ convertDateToText($fechas[$pos_v]) }}">
                    @if ($v > 0)
                        {{ number_format($v) }}
                    @endif
                </th>
            @endforeach
            <th class="text-center th_yura_green">
                {{ number_format($total) }}
            </th>
        </tr>
    </table>
</div>

<script>
    function confirmar_compra(pos) {
        texto =
            "<div class='alert alert-warning text-center'>Esta seguro de <b>CONFIRMAR</b> la compra?</div>";

        modal_quest('modal_confirmar_compra', texto, 'Confirmar compra', true, false, '40%', function() {
            proveedor = $('#id_proveedor_compra_' + pos).val();
            variedad = $('#id_variedad_compra_' + pos).val();
            longitud = $('#longitud_compra_' + pos).val();
            datos = {
                _token: '{{ csrf_token() }}',
                proveedor: proveedor,
                variedad: variedad,
                longitud: longitud,
                cantidad: parseInt($('#confirmar_compra_' + pos).val()),
            }
            if (datos['cantidad'] >= $('#confirmar_compra_' + pos).prop('min'))
                post_jquery_m('{{ url('inventario_compra_flor/confirmar_compra') }}', datos, function() {
                    cerrar_modals();
                    listar_inventario_compra_flor();
                });
        })
    }

    function store_compra_parcial(pos) {
        texto =
            "<div class='alert alert-warning text-center'>Esta seguro de <b>CONFIRMAR</b> la compra?</div>";

        modal_quest('modal_store_compra_parcial', texto, 'Confirmar compra', true, false, '40%', function() {
            proveedor = $('#id_proveedor_compra_' + pos).val();
            variedad = $('#id_variedad_compra_' + pos).val();
            longitud = $('#longitud_compra_' + pos).val();
            datos = {
                _token: '{{ csrf_token() }}',
                proveedor: proveedor,
                variedad: variedad,
                longitud: longitud,
                cantidad: parseInt($('#compra_parcial_' + pos).val()),
            }
            if (datos['cantidad'] >= $('#confirmar_compra_' + pos).prop('min'))
                post_jquery_m('{{ url('inventario_compra_flor/store_compra_parcial') }}', datos, function() {
                    cerrar_modals();
                    listar_inventario_compra_flor();
                });
        })
    }

    function prorrogar_compra(pos) {
        proveedor = $('#id_proveedor_compra_' + pos).val();
        variedad = $('#id_variedad_compra_' + pos).val();
        longitud = $('#longitud_compra_' + pos).val();
        datos = {
            proveedor: proveedor,
            variedad: variedad,
            longitud: longitud,
        }
        get_jquery('{{ url('inventario_compra_flor/prorrogar_compra') }}', datos, function(retorno) {
            modal_view('modal_prorrogar_compra', retorno,
                '<i class="fa fa-fw fa-plus"></i> Prorrogar la Compra',
                true, false, '{{ isPC() ? '50%' : '' }}',
                function() {});
        });
    }

    function update_compra(pos, fecha, pos_f) {
        texto =
            "<div class='alert alert-warning text-center'>¿Esta seguro de <b>MODIFICAR</b> la compra?</div>";

        modal_quest('modal_update_compra', texto, 'Modificar la compra', true, false, '40%', function() {
            proveedor = $('#id_proveedor_compra_' + pos).val();
            variedad = $('#id_variedad_compra_' + pos).val();
            longitud = $('#longitud_compra_' + pos).val();
            datos = {
                _token: '{{ csrf_token() }}',
                proveedor: proveedor,
                variedad: variedad,
                longitud: longitud,
                fecha: fecha,
                cantidad: parseInt($('#update_compra_' + pos + '_' + pos_f).val()),
            }
            if (datos['cantidad'] >= $('#update_compra_' + pos + '_' + pos_f).prop('min'))
                post_jquery_m('{{ url('inventario_compra_flor/update_compra') }}', datos, function() {
                    cerrar_modals();
                    listar_inventario_compra_flor();
                });
        })
    }

    function exportar_listado_compra_flor() {
        $.LoadingOverlay('show');
        window.open('{{ url('inventario_compra_flor/exportar_listado_compra_flor') }}?proveedor=' + $(
                "#proveedor_filtro")
            .val() +
            '&planta=' + $("#planta_filtro").val() +
            '&variedad=' + $("#variedad_filtro").val(), '_blank');
        $.LoadingOverlay('hide');
    }
</script>
