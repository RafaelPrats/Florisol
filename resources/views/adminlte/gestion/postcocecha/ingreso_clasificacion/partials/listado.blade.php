<div style="overflow-y: scroll; max-height: 700px">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
        <tr class="tr_fija_top_0">
            <th class="text-center th_yura_green">
                <div style="width: 120px">
                    Siglas
                </div>
            </th>
            <th class="text-center th_yura_green">
                <div style="width: 120px">
                    Nombre
                </div>
            </th>
            <th class="text-center th_yura_green padding_lateral_5">
                Longitud
            </th>
            @php
                $totales_venta_fecha = [];
                $totales_por_armar_fecha = [];
                $totales_ramos_dispo_fecha = [];
                $totales_ramos_despachados = [];
            @endphp
            @foreach ($fechas as $pos_f => $f)
                <th class="text-center bg-yura_dark">
                    <div style="width: 130px">
                        {{ getDias(TP_ABREVIADO, FR_ARREGLO)[transformDiaPhp(date('w', strtotime($f)))] }}<br>
                        <small>{{ convertDateToText($f) }}</small>
                    </div>
                </th>
                <th class="text-center bg-yura_dark">
                    <button type="button" class="btn btn-xs btn-yura_default"
                        onclick="exportar_excel_fecha('{{ $f }}', '{{ $pos_f }}')">
                        <i class="fa fa-fw fa-file-excel-o"></i>
                    </button>
                </th>
                @php
                    $totales_venta_fecha[] = 0;
                    $totales_por_armar_fecha[] = 0;
                    $totales_ramos_dispo_fecha[] = 0;
                    $totales_ramos_despachados[] = 0;
                @endphp
            @endforeach
            <th class="text-center bg-yura_dark padding_lateral_5">
                Despachado
            </th>
            <th class="text-center bg-yura_dark padding_lateral_5">
                Armados
            </th>
        </tr>
        @php
            $total_en_proceso = 0;
            $total_armados = 0;
            $pedidos_completos = true;
        @endphp
        @foreach ($listado as $pos_i => $item)
            <tr onmouseover="$(this).addClass('bg-aqua')" onmouseleave="$(this).removeClass('bg-aqua')">
                <th class="text-center" style="border-color: #9d9d9d">
                    {{ $item['item']->siglas }}
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    {{ $item['item']->nombre }}
                    <input type="hidden" class="pos_combinaciones" value="{{ $pos_i }}">
                    <input type="hidden" id="pos_id_variedad_{{ $pos_i }}"
                        value="{{ $item['item']->id_variedad }}">
                    <input type="hidden" id="pos_longitud_{{ $pos_i }}" value="{{ $item['item']->longitud }}">
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    {{ $item['item']->longitud }}<sup>cm</sup>
                </th>
                @php
                    $total_en_proceso += $item['ramos_orden'];
                    $armados_combinacion = 0;
                @endphp
                @if (count($item['valores']) > 0)
                    @foreach ($item['valores'] as $pos_f => $v)
                        @php
                            $armados_combinacion += $v['armados'];
                            $total_armados += $v['armados'];
                            $por_armar = $v['venta'] - $v['armados'];
                            if ($pos_f == 0 && $por_armar > 0) {
                                $pedidos_completos = false;
                            }
                        @endphp
                        <th class="text-center" style="border-color: #9d9d9d; background-color: white" colspan="2">
                            @if ($v['venta'] > 0)
                                <div class="btn-group">
                                    <button type="button" class="btn btn-xs btn-yura_dark" title="Ramos Pedidos">
                                        {{ number_format($v['venta']) }}
                                    </button>
                                    <button type="button"
                                        class="btn btn-xs btn-yura_{{ $por_armar > 0 ? 'danger' : 'default' }}"
                                        title="Por Armar"
                                        onclick="armar_combinacion('{{ $item['item']->id_variedad }}', '{{ $item['item']->longitud }}', '{{ $fechas[$pos_f] }}')">
                                        {{ number_format($por_armar > 0 ? $por_armar : 0) }}
                                    </button>
                                    <button type="button" class="btn btn-xs btn-yura_info" title="Ramos Disponibles">
                                        {{ number_format($por_armar > 0 ? $v['ramos_disponibles'] : 0) }}
                                    </button>
                                    @if ($v['ot_despachos'] > 0 && $por_armar > 0)
                                        <button type="button" class="btn btn-xs btn-yura_warning" title="Despachados">
                                            {{ number_format($v['ot_despachos']) }}
                                        </button>
                                    @endif
                                </div>
                                <input type="hidden" id="ramos_venta_{{ $pos_f }}_{{ $pos_i }}"
                                    value="{{ $v['venta'] }}">
                                <input type="hidden" id="ramos_por_armar_{{ $pos_f }}_{{ $pos_i }}"
                                    value="{{ $por_armar }}">
                                <input type="hidden" id="ramos_disponibles_{{ $pos_f }}_{{ $pos_i }}"
                                    value="{{ $v['ramos_disponibles'] }}">
                            @endif
                        </th>
                        @php
                            $totales_venta_fecha[$pos_f] += $v['venta'];
                            $totales_por_armar_fecha[$pos_f] += $por_armar > 0 ? $por_armar : 0;
                            $totales_ramos_dispo_fecha[$pos_f] += $v['ramos_disponibles'];
                            $totales_ramos_despachados[$pos_f] +=
                                $por_armar > 0 && $v['ot_despachos'] > 0 ? $v['ot_despachos'] : 0;
                        @endphp
                    @endforeach
                    <th class="text-center" style="border-color: #9d9d9d">
                        {{ $item['ramos_orden'] }}
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        {{ $armados_combinacion > 0 ? $armados_combinacion : '' }}
                    </th>
                @else
                    @foreach ($fechas as $pos_f => $f)
                        <th class="text-center" style="border-color: #9d9d9d; background-color: white; color: black"
                            colspan="2">
                            ?
                        </th>
                    @endforeach
                    <th class="text-center" style="border-color: #9d9d9d">
                        {{ $item['ramos_orden'] }}
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        0
                        <input type="hidden" class="items_restantes" data-variedad="{{ $item['item']->id_variedad }}"
                            data-longitud="{{ $item['item']->longitud }}">
                    </th>
                @endif
            </tr>
        @endforeach
        <tr class="tr_fija_bottom_0">
            <th class="text-center th_yura_green" colspan="3">
                Totales
            </th>
            @php
                $total_armados_temp = $total_armados;
            @endphp
            @foreach ($totales_venta_fecha as $pos_f => $v)
                @php
                    $por_armar = $totales_por_armar_fecha[$pos_f];
                @endphp
                <th class="text-center" style="border-color: #9d9d9d; background-color: #dddddd" colspan="2">
                    <div class="btn-group">
                        <button type="button" class="btn btn-xs btn-yura_dark" title="Ramos Pedidos">
                            {{ number_format($v) }}
                        </button>
                        <button type="button" class="btn btn-xs btn-yura_{{ $por_armar > 0 ? 'danger' : 'default' }}"
                            title="Por Armar">
                            {{ number_format($por_armar > 0 ? $por_armar : 0) }}
                        </button>
                        <button type="button" class="btn btn-xs btn-yura_info" title="Ramos Disponibles">
                            {{ number_format($por_armar > 0 ? $totales_ramos_dispo_fecha[$pos_f] : 0) }}
                        </button>
                        @if ($por_armar > 0 && $totales_ramos_despachados[$pos_f] > 0)
                            <button type="button" class="btn btn-xs btn-yura_warning" title="Ramos Despachados">
                                {{ number_format($por_armar > 0 ? $totales_ramos_despachados[$pos_f] : 0) }}
                            </button>
                        @endif
                    </div>
                    @if ($pedidos_completos && 0)
                        <br>
                        <button type="button" class="btn btn-blobk btn-xs btn-yura_primary"
                            onclick="confirmar_pedido('{{ $fechas[$pos_f] }}')">
                            <i class="fa fa-fw fa-check"></i> Confirmar Pedidos
                        </button>
                    @endif
                </th>
            @endforeach
            <th class="text-center th_yura_green">
                {{ $total_en_proceso }}
            </th>
            <th class="text-center th_yura_green">
                {{ $total_armados }}
            </th>
        </tr>
    </table>
</div>

<div class="row hidden">
    <div class="col-md-offset-2 col-md-10">
        <legend class="text-center" style="font-size: 1em; margin-bottom: 1px">
            <b>RESUMEN de salidas en Recepcion</b>
        </legend>
        <div style="overflow-y: scroll; max-height: 350px">
            <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d"
                id="table_salidas_recepcion">
                <thead>
                    <tr>
                        <th class="text-center th_yura_green" rowspan="2">
                            PLANTA
                        </th>
                        <th class="text-center th_yura_green" rowspan="2">
                            VARIEDAD
                        </th>
                        <th class="text-center th_yura_green" colspan="3">
                            TALLOS
                        </th>
                    </tr>
                    <tr>
                        <th class="text-center bg-yura_dark" style="width: 90px">
                            PEDIDOS
                        </th>
                        <th class="text-center bg-yura_dark" style="width: 90px">
                            SALIDAS
                        </th>
                        <th class="text-center bg-yura_dark" style="width: 90px">
                            DISPONIBLES
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $total_ventas = 0;
                        $total_salidas = 0;
                        $total_disponibles = 0;
                    @endphp
                    @foreach ($listado_resumen_salidas as $item)
                        @php
                            $total_ventas += $item['venta'];
                            $total_salidas += $item['item']->cantidad;
                            $total_disponibles += $item['item']->disponibles;
                        @endphp
                        <tr onmouseover="$(this).addClass('bg-yura_dark')"
                            onmouseleave="$(this).removeClass('bg-yura_dark')">
                            <th class="padding_lateral_5" style="border-color: #9d9d9d">
                                {{ $item['item']->planta_nombre }}
                            </th>
                            <th class="padding_lateral_5" style="border-color: #9d9d9d">
                                {{ $item['item']->variedad_nombre }}
                            </th>
                            <td class="text-center" style="border-color: #9d9d9d">
                                {{ $item['venta'] }}
                            </td>
                            <td class="text-center" style="border-color: #9d9d9d">
                                {{ $item['item']->cantidad }}
                            </td>
                            <td class="text-center" style="border-color: #9d9d9d">
                                {{ $item['item']->disponibles }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tr>
                    <th class="text-center th_yura_green" colspan="2">
                        TOTAL
                    </th>
                    <th class="text-center bg-yura_dark">
                        {{ $total_ventas }}
                    </th>
                    <th class="text-center bg-yura_dark">
                        {{ $total_salidas }}
                    </th>
                    <th class="text-center bg-yura_dark">
                        {{ $total_disponibles }}
                    </th>
                </tr>
            </table>
        </div>
    </div>
</div>

<script>
    estructura_tabla('table_salidas_recepcion');
    $('#table_salidas_recepcion_filter').addClass('hidden');

    function armar_combinacion(variedad, longitud, fecha) {
        datos = {
            variedad: variedad,
            longitud: longitud,
            fecha: fecha,
            fecha_trabajo: $('#fecha_filtro').val(),
        }
        get_jquery('{{ url('ingreso_clasificacion/armar_combinacion') }}', datos, function(retorno) {
            modal_view('modal_armar_combinacion', retorno,
                '<i class="fa fa-fw fa-plus"></i> Armar ramos de la Receta',
                true, false, '{{ isPC() ? '98%' : '' }}',
                function() {});
        });
    }

    function store_armar_pedido(detalle_pedido, longitud, fecha) {
        texto =
            "<div class='alert alert-warning text-center'>¿Esta seguro de <b>PROCESAR</b> la orden de trabajo?</div>";

        modal_quest('modal_eliminar_orden_trabajo', texto, 'Eliminar la Orden de Trabajo', true, false, '40%',
            function() {
                ramos_venta = $('#ramos_armar_' + detalle_pedido).val();
                ramos_venta = parseInt(ramos_venta);
                armar = $('#input_armar_' + detalle_pedido).val();
                armar = armar != '' ? parseInt(armar) : 0;
                if (armar <= ramos_venta) {
                    //detalle_pedido = $('#id_detalle_pedido_armar_' + detalle_pedido).val();
                    ramos_pedido = $('#id_ramos_armar_' + detalle_pedido).val();
                    ramos_pedido = parseInt(ramos_pedido);
                    if (armar > 0) {
                        ids_item_armar = $('.id_item_armar_' + detalle_pedido);
                        data = [];
                        for (i = 0; i < ids_item_armar.length; i++) {
                            variedad = ids_item_armar[i].value;
                            usar = $('#input_usar_armar_' + variedad + '_' + detalle_pedido).val();
                            usar = parseInt(usar);
                            data.push({
                                variedad: variedad,
                                usar: usar,
                            });
                        }

                        datos = {
                            _token: '{{ csrf_token() }}',
                            variedad: $('#id_receta_armar').val(),
                            fecha_trabajo: $('#fecha_filtro').val(),
                            detalle_pedido: detalle_pedido,
                            longitud: longitud,
                            fecha_pedido: fecha,
                            ramos_pedido: ramos_pedido,
                            ramos_venta: ramos_venta,
                            armar: armar,
                            data: JSON.stringify(data),
                        }
                        post_jquery_m('{{ url('ingreso_clasificacion/store_armar_pedido') }}', datos, function(
                            retorno) {
                            cerrar_modals();
                            armar_combinacion(datos['variedad'], longitud, fecha);
                            listar_reporte();
                        });
                    }
                } else {
                    alerta(
                        '<div class="text-center alert alert-warning">La cantidad de ramos a <b>ARMAR</b> (<b>' +
                        armar + '</b>) no puede superar a la cantidad <b>RAMOS PEDIDOS</b>: (<b>' + por_armar +
                        '</b>)</div>');
                }
            })
    }

    function calcular_usar_armar(ped) {
        armar = $('#input_armar_' + ped).val();
        armar = parseInt(armar);
        ids_item_armar = $('.id_item_armar_' + ped);
        $('#btn_armar_' + ped).prop('disabled', false);
        $('#btn_armar_' + ped).html('<i class="fa fa-fw fa-check"></i> Procesar');
        $('#btn_armar_' + ped).css('color', '');
        for (i = 0; i < ids_item_armar.length; i++) {
            variedad = ids_item_armar[i].value;
            unidades = $('#id_unidades_armar_' + variedad + '_' + ped).val();
            unidades = parseInt(unidades);
            usar = unidades * armar;
            $('#input_usar_armar_' + variedad + '_' + ped).val(usar);
            inventario = $('#inventario_variedad_armar_' + variedad).val();
            inventario = parseInt(inventario);
            if (inventario < usar) {
                $('#input_usar_armar_' + variedad + '_' + ped).css('background-color', '#ff9a9a');
                $('#btn_armar_' + ped).css('color', 'red');
                $('#btn_armar_' + ped).prop('disabled', true)
                $('#btn_armar_' + ped).html('No hay flor <br/> en el Inventario');
            } else {
                $('#input_usar_armar_' + variedad + '_' + ped).css('background-color', '');
            }
        }
    }

    function exportar_excel_fecha(fecha) {
        pos_combinaciones = $('.pos_combinaciones');
        data = [];
        /*for (i = 0; i < pos_combinaciones.length; i++) {
            pos_i = pos_combinaciones[i].value;
            variedad = $('#pos_id_variedad_' + pos_i).val();
            longitud = $('#pos_longitud_' + pos_i).val();
            ramos_venta = $('#ramos_venta_' + pos_i).val();
            ramos_por_armar = $('#ramos_por_armar_' + pos_i).val();
            ramos_disponibles = $('#ramos_disponibles_' + pos_i).val();
            if (ramos_venta != '') {
                data.push({
                    variedad: variedad,
                    longitud: longitud,
                    ramos_venta: ramos_venta,
                    ramos_por_armar: ramos_por_armar,
                    ramos_disponibles: ramos_disponibles,
                })
            }
        }*/

        $.LoadingOverlay('show');
        window.open('{{ url('ingreso_clasificacion/exportar_excel_fecha') }}?fecha=' + fecha +
            '&data=' + JSON.stringify(data), '_blank');
        $.LoadingOverlay('hide');
    }

    function store_armar_ramos() {
        pos_combinaciones = $('.pos_combinaciones');
        data = [];
        for (i = 0; i < pos_combinaciones.length; i++) {
            pos_i = pos_combinaciones[i].value;
            variedad = $('#pos_id_variedad_' + pos_i).val();
            longitud = $('#pos_longitud_' + pos_i).val();
            armar = $('#pos_armar_' + pos_i).val();
            if (armar > 0) {
                data.push({
                    variedad: variedad,
                    longitud: longitud,
                    armar: armar,
                })
            }
        }
        if (data.length > 0) {
            datos = {
                _token: '{{ csrf_token() }}',
                fecha: $('#fecha_filtro').val(),
                data: JSON.stringify(data),
            }
            post_jquery_m('{{ url('ingreso_clasificacion/store_armar_ramos') }}', datos, function() {
                listar_reporte();
            })
        }
    }

    function store_armar_ramos() {
        pos_combinaciones = $('.pos_combinaciones');
        data = [];
        for (i = 0; i < pos_combinaciones.length; i++) {
            pos_i = pos_combinaciones[i].value;
            variedad = $('#pos_id_variedad_' + pos_i).val();
            longitud = $('#pos_longitud_' + pos_i).val();
            armar = $('#pos_armar_' + pos_i).val();
            if (armar > 0) {
                data.push({
                    variedad: variedad,
                    longitud: longitud,
                    armar: armar,
                })
            }
        }
        if (data.length > 0) {
            datos = {
                _token: '{{ csrf_token() }}',
                fecha: $('#fecha_filtro').val(),
                data: JSON.stringify(data),
            }
            post_jquery_m('{{ url('ingreso_clasificacion/store_armar_ramos') }}', datos, function() {
                listar_reporte();
            })
        }
    }

    function confirmar_pedido(fecha) {
        datos = {
            _token: '{{ csrf_token() }}',
            variedad: $('#variedad_filtro').val(),
            fecha: fecha,
        }
        post_jquery_m('{{ url('ingreso_clasificacion/confirmar_pedido') }}', datos, function() {
            listar_reporte();
        })
    }
</script>
