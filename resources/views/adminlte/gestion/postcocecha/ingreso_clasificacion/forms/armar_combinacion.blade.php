<legend style="font-size: 1em; margin-bottom: 2px" class="text-center">
    Pedidos de la RECETA "<b>{{ $variedad->nombre }}</b>" de "<b>{{ $longitud }}<sup>cm</sup></b>", del dia
    "<b>{{ convertDateToText($fecha) }}</b>"
    <button type="button" class="btn btn-xs btn-yura_primary"
        onclick="exportar_receta('{{ $variedad->id_variedad }}', '{{ $longitud }}', '{{ $fecha }}')">
        <i class="fa fa-fw fa-file-excel-o"></i> Exportar
    </button>
</legend>
<input type="hidden" id="id_receta_armar" value="{{ $variedad->id_variedad }}">
<input type="hidden" id="longitud_receta_armar" value="{{ $longitud }}">
<input type="hidden" id="fecha_receta_armar" value="{{ $fecha }}">

<div style="overflow-y: scroll; max-height: 500px">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
        <tr class="tr_fija_top_0">
            <th class="text-center th_yura_green" rowspan="2">
                PEDIDO
            </th>
            <th class="text-center th_yura_green" rowspan="2">
                CLIENTE
            </th>
            <th class="text-center th_yura_green" rowspan="2">
                RAMOS
            </th>
            <th class="text-center th_yura_green" colspan="4">
                DISTRIBUCION RECETA
            </th>
            <th class="text-center th_yura_green" rowspan="2">
                TALLOS
            </th>
            <th class="text-center th_yura_green" rowspan="2">
                INVENTARIO
            </th>
            <th class="text-center th_yura_green" rowspan="2">
                PRE-OT
            </th>
            <th class="text-center th_yura_green" rowspan="2">
                OT
            </th>
            <th class="text-center th_yura_green" rowspan="2">
                ARMADOS
            </th>
            <th class="text-center th_yura_green" rowspan="2">
                Disp.
            </th>
            <th class="text-center th_yura_green" rowspan="2" style="width: 80px">
                ARMAR
            </th>
            <th class="text-center th_yura_green" rowspan="2" style="width: 80px">
                USAR
            </th>
        </tr>
        <tr class="tr_fija_top_1" style="position: sticky; top: 21px; z-index: 9">
            <th class="text-center bg-yura_dark">
                PLANTA
            </th>
            <th class="text-center bg-yura_dark">
                VARIEDAD
            </th>
            <th class="text-center bg-yura_dark">
                UNIDADES
            </th>
            <th class="text-center bg-yura_dark" style="width: 40px">
                TxR
            </th>
        </tr>
        @php
            $total_ramos = 0;
            $total_ramos_armados = 0;
            $total_tallos = 0;
            $resumen_variedades = [];
        @endphp
        @foreach ($listado as $pos_p => $item)
            @php
                $total_ramos += $item['ramos_venta'];
                $total_ramos_armados += $item['ramos_armados_orden'] + $item['pedido']->ramos_armados;
                $ramos_disponibles = $item['ramos_venta'];

                $tallos_x_ramo = 0;
                foreach ($item['distribucion'] as $pos_d => $dist) {
                    $tallos_x_ramo += $dist->unidades;
                }
            @endphp
            @foreach ($item['distribucion'] as $pos_d => $dist)
                @php
                    $total_tallos += $dist->unidades * $item['ramos_venta'];
                    $inventario = getTotalInventarioByVariedad($dist->id_variedad);
                    if ($ramos_disponibles > intVal($inventario / $dist->unidades)) {
                        $ramos_disponibles = intVal($inventario / $dist->unidades);
                    }

                    $pos_en_resumen = -1;
                    foreach ($resumen_variedades as $pos => $r) {
                        if ($r['variedad']->id_variedad == $dist->id_variedad) {
                            $pos_en_resumen = $pos;
                        }
                    }
                    if ($pos_en_resumen != -1) {
                        $resumen_variedades[$pos_en_resumen]['tallos'] += $dist->unidades * $item['ramos_venta'];
                    } else {
                        $resumen_variedades[] = [
                            'variedad' => $dist,
                            'tallos' => $dist->unidades * $item['ramos_venta'],
                        ];
                    }
                @endphp
                <tr onmouseover="$(this).addClass('bg-aqua')" onmouseleave="$(this).removeClass('bg-aqua')">
                    @if ($pos_d == 0)
                        <th class="text-center" style="border-color: #9d9d9d"
                            rowspan="{{ count($item['distribucion']) }}">
                            #{{ $item['pedido']->codigo }}
                            :<em>{{ $item['pedido']->codigo_ref }}</em>
                            <input type="hidden" class="id_pedido_armar"
                                value="{{ $item['pedido']->id_detalle_import_pedido }}">
                            <input type="hidden" class="ids_detalle_pedido_armar"
                                id="id_detalle_pedido_armar_{{ $item['pedido']->id_detalle_import_pedido }}"
                                value="{{ $item['pedido']->id_detalle_import_pedido }}">
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d"
                            rowspan="{{ count($item['distribucion']) }}">
                            {{ $item['pedido']->nombre_cliente }}
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d"
                            rowspan="{{ count($item['distribucion']) }}">
                            {{ $item['ramos_venta'] }}
                            <input type="hidden" id="ramos_armar_{{ $item['pedido']->id_detalle_import_pedido }}"
                                value="{{ $item['ramos_venta'] }}">
                        </th>
                    @endif
                    <th class="text-center" style="border-color: #9d9d9d">
                        {{ $dist->nombre_planta }}
                        <input type="hidden" class="id_item_armar_{{ $item['pedido']->id_detalle_import_pedido }}"
                            value="{{ $dist->id_variedad }}">
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        {{ $dist->nombre_variedad }}
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        {{ $dist->unidades }}
                        <input type="hidden"
                            id="id_unidades_armar_{{ $dist->id_variedad }}_{{ $item['pedido']->id_detalle_import_pedido }}"
                            value="{{ $dist->unidades }}">
                    </th>
                    @if ($pos_d == 0)
                        <th class="text-center" style="border-color: #9d9d9d"
                            rowspan="{{ count($item['distribucion']) }}">
                            {{ $tallos_x_ramo }}
                        </th>
                    @endif
                    <th class="text-center" style="border-color: #9d9d9d">
                        {{ $dist->unidades * $item['ramos_venta'] }}
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        {{ $inventario }}
                    </th>
                    @if ($pos_d == 0)
                        <th class="text-center" style="border-color: #9d9d9d"
                            rowspan="{{ count($item['distribucion']) }}">
                            @if ($item['ramos_pre_ot'] > 0)
                                <button type="button" class="btn btn-xs btn-yura_dark"
                                    title="Ver Pre-Ordenes de Trabajo"
                                    onclick="listar_pre_ordenes_trabajo('{{ $item['pedido']->id_detalle_import_pedido }}')">
                                    {{ $item['ramos_pre_ot'] }}
                                </button>
                            @endif
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d"
                            rowspan="{{ count($item['distribucion']) }}">
                            @if ($item['ramos_orden'] > 0)
                                <button type="button" class="btn btn-xs btn-yura_dark"
                                    title="Ver Ordenes de Trabajo"
                                    onclick="listar_ordenes_trabajo('{{ $item['pedido']->id_detalle_import_pedido }}')">
                                    {{ $item['ramos_orden'] }}
                                </button>
                            @endif
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d"
                            rowspan="{{ count($item['distribucion']) }}">
                            @if ($item['ramos_armados_orden'] + $item['pedido']->ramos_armados > 0)
                                {{ $item['ramos_armados_orden'] + $item['pedido']->ramos_armados }}
                            @endif
                            @if (
                                (in_array(session('id_usuario'), $usuarios) || session('id_usuario') == 1) &&
                                    $item['ramos_venta'] > $item['ramos_armados_orden'] + $item['pedido']->ramos_armados)
                                <br>
                                <div class="input-group inputs_armar">
                                    <input type="number" style="width: 100%; color: black" class="text-center"
                                        placeholder="Armar" min="1"
                                        id="confirmar_ramos_{{ $item['pedido']->id_detalle_import_pedido }}"
                                        max="{{ $item['ramos_venta'] - ($item['ramos_armados_orden'] + $item['pedido']->ramos_armados) }}">
                                    <div class="input-group-btn">
                                        <button type="button" class="btn btn-xs btn-yura_dark"
                                            onclick="confirmar_ramos('{{ $item['pedido']->id_detalle_import_pedido }}')"
                                            style="height: 26px">
                                            <i class="fa fa-fw fa-save"></i> Grabar
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d"
                            rowspan="{{ count($item['distribucion']) }}"
                            id="td_ramos_disponibles_{{ $item['pedido']->id_detalle_import_pedido }}">
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d"
                            rowspan="{{ count($item['distribucion']) }}">
                            @if ($item['ramos_venta'] - $item['ramos_orden'] - $item['pedido']->ramos_armados > 0)
                                <input type="number"
                                    id="input_armar_{{ $item['pedido']->id_detalle_import_pedido }}"
                                    class="text-center inputs_armar" style="width: 100%; color: black !important"
                                    min="0"
                                    onchange="calcular_usar_armar('{{ $item['pedido']->id_detalle_import_pedido }}')"
                                    onkeyup="calcular_usar_armar('{{ $item['pedido']->id_detalle_import_pedido }}')">
                                <button type="button" class="btn btn-xs btn-block btn-yura_primary inputs_armar"
                                    id="btn_armar_{{ $item['pedido']->id_detalle_import_pedido }}"
                                    onclick="store_armar_pedido('{{ $item['pedido']->id_detalle_import_pedido }}', '{{ $longitud }}', '{{ $fecha }}')">
                                    <i class="fa fa-fw fa-check"></i> Procesar
                                </button>
                                <button type="button" class="btn btn-block btn-xs btn-yura_dark inputs_armar"
                                    style="margin-top: 0"
                                    onclick="admin_receta('{{ $item['pedido']->id_detalle_import_pedido }}')">
                                    <i class="fa fa-fw fa-gift"></i> Distribuir
                                </button>
                                <button type="button" class="btn btn-block btn-xs btn-yura_default inputs_armar"
                                    style="margin-top: 0"
                                    onclick="dividir_receta('{{ $item['pedido']->id_detalle_import_pedido }}')">
                                    <i class="fa fa-fw fa-exchange"></i> Dividir
                                </button>
                                <div class="btn-group inputs_armar" style="margin-top: 0;">
                                    <button type="button"
                                        class="btn btn-xs btn-yura_{{ $item['pedido']->bloquear_distribucion == 1 ? 'danger' : 'warning' }}"
                                        title="{{ $item['pedido']->bloquear_distribucion == 1 ? 'Desbloquear' : 'Bloquear' }} Distribucion"
                                        onclick="bloquear_distribucion('{{ $item['pedido']->id_detalle_import_pedido }}', '{{ $item['pedido']->bloquear_distribucion }}')">
                                        <i
                                            class="fa fa-fw fa-{{ $item['pedido']->bloquear_distribucion == 1 ? 'lock' : 'unlock' }}"></i>
                                    </button>
                                    <button type="button" class="btn btn-xs btn-yura_default"
                                        title="Copiar Distribucion"
                                        onclick="copiar_distribucion('{{ $item['pedido']->id_detalle_import_pedido }}')">
                                        <i class="fa fa-fw fa-copy"></i>
                                    </button>
                                </div>
                            @else
                                <span class="badge bg-yura_primary">
                                    <i class="fa fa-fw fa-check"></i> <br> Pedido <br> Procesado
                                </span>
                            @endif
                        </th>
                    @endif
                    <th class="text-center" style="border-color: #9d9d9d">
                        <input type="number" readonly style="width: 100%; color: black !important"
                            class="text-center"
                            id="input_usar_armar_{{ $dist->id_variedad }}_{{ $item['pedido']->id_detalle_import_pedido }}">
                    </th>
                </tr>
            @endforeach
            <script>
                $('#td_ramos_disponibles_{{ $item['pedido']->id_detalle_import_pedido }}').html(
                    {{ $ramos_disponibles - $item['ramos_orden'] > 0 ? $ramos_disponibles - $item['ramos_orden'] : 0 }});
                $('#input_armar_{{ $item['pedido']->id_detalle_import_pedido }}').prop('max',
                    {{ $ramos_disponibles - $item['ramos_orden'] }});
            </script>
        @endforeach
        <tr class="tr_fija_bottom_0">
            <th class="text-center th_yura_green" colspan="2">
                TOTALES
            </th>
            <th class="text-center th_yura_green">
                {{ number_format($total_ramos) }}
            </th>
            <th class="text-center th_yura_green" colspan="4">
            </th>
            <th class="text-center th_yura_green">
                {{ number_format($total_tallos) }}
            </th>
            <th class="text-center th_yura_green" colspan="3">
            </th>
            <th class="text-center th_yura_green">
                {{ number_format($total_ramos_armados) }}
            </th>
            <th class="text-center th_yura_green" colspan="3">
            </th>
        </tr>
    </table>
</div>

<div class="row">
    <div class="col-md-7 col-md-offset-5">
        <legend class="text-center" style="font-size: 1em; margin-bottom: 1px">
            <b>RESUMEN</b>
        </legend>
        <div style="overflow-y: scroll; max-height: 250px; margin-top: 5px;">
            <table class="table-bordered pull-right" style="width: 100%; border: 1px solid #9d9d9d"
                id="table_resumen_variedades">
                <thead>
                    <tr class="tr_fija_top_0">
                        <th class="text-center th_yura_green">
                            Planta
                        </th>
                        <th class="text-center th_yura_green">
                            Variedad
                        </th>
                        <th class="text-center th_yura_green padding_lateral_5">
                            Pedidos
                        </th>
                        <th class="text-center th_yura_green padding_lateral_5">
                            Inventario
                        </th>
                        <th class="text-center th_yura_green padding_lateral_5">
                            Usados
                        </th>
                        <th class="text-center th_yura_green padding_lateral_5">
                            Saldo
                        </th>
                    </tr>
                </thead>
                @php
                    $total_tallos = 0;
                    $total_orden = 0;
                    $total_inventario = 0;
                @endphp
                <tbody>
                    @foreach ($resumen_variedades as $r)
                        @php
                            $inventario = getTotalInventarioByVariedad($r['variedad']->id_variedad);
                            $saldo = $inventario - $r['tallos'] + 0;
                            $total_tallos += $r['tallos'];
                            $total_orden += 0;
                            $total_inventario += $inventario;
                        @endphp
                        <tr onmouseover="$(this).addClass('bg-yura_dark')"
                            onmouseleave="$(this).removeClass('bg-yura_dark')">
                            <th class="text-center" style="border-color: #9d9d9d">
                                {{ $r['variedad']->nombre_planta }}
                            </th>
                            <th class="text-center" style="border-color: #9d9d9d">
                                {{ $r['variedad']->nombre_variedad }}
                            </th>
                            <td class="text-center" style="border-color: #9d9d9d">
                                {{ number_format($r['tallos']) }}
                            </td>
                            <td class="text-center" style="border-color: #9d9d9d">
                                {{ number_format($inventario) }}
                                <input type="hidden"
                                    id="inventario_variedad_armar_{{ $r['variedad']->id_variedad }}"
                                    value="{{ $inventario != '' ? $inventario : 0 }}">
                            </td>
                            <td class="text-center" style="border-color: #9d9d9d">
                                {{ number_format(0) }}
                            </td>
                            <th class="text-center"
                                style="border-color: #9d9d9d; color: {{ $saldo < 0 ? 'red' : '' }}">
                                {{ number_format($saldo) }}
                            </th>
                        </tr>
                    @endforeach
                </tbody>
                @php
                    $saldo_total = $total_inventario - $total_tallos + $total_orden;
                @endphp
                <tr class="tr_fija_bottom_0">
                    <th class="text-center th_yura_green" colspan="2">
                        TOTALES
                    </th>
                    <th class="text-center th_yura_green">
                        {{ number_format($total_tallos) }}
                    </th>
                    <th class="text-center th_yura_green">
                        {{ number_format($total_inventario) }}
                    </th>
                    <th class="text-center th_yura_green">
                        {{ number_format($total_orden) }}
                    </th>
                    <th class="text-center th_yura_green" style="color: {{ $saldo_total < 0 ? 'red' : '' }}">
                        {{ number_format($saldo_total) }}
                    </th>
                </tr>
            </table>
        </div>
    </div>
</div>

<script>
    @if ($total_ramos <= $total_ramos_armados)
        $('.inputs_armar').addClass('hidden')
    @endif

    function admin_receta(det_ped) {
        datos = {
            det_ped: det_ped,
        };
        get_jquery('{{ url('ingreso_clasificacion/admin_receta') }}', datos, function(retorno) {
            modal_view('modal_admin_receta', retorno, '<i class="fa fa-fw fa-plus"></i> Administrar receta',
                true, false, '{{ isPC() ? '95%' : '' }}');
        });
    }

    function dividir_receta(det_ped) {
        datos = {
            det_ped: det_ped,
        };
        get_jquery('{{ url('ingreso_clasificacion/dividir_receta') }}', datos, function(retorno) {
            modal_view('modal_dividir_receta', retorno, '<i class="fa fa-fw fa-plus"></i> Dividir receta',
                true, false, '{{ isPC() ? '95%' : '' }}');
        });
    }

    function exportar_receta(variedad, longitud, fecha) {
        $.LoadingOverlay('show');
        window.open('{{ url('ingreso_clasificacion/exportar_receta') }}?fecha=' + fecha +
            '&variedad=' + variedad +
            '&longitud=' + longitud, '_blank');
        $.LoadingOverlay('hide');
    }

    function listar_ordenes_trabajo(det_ped) {
        datos = {
            det_ped: det_ped,
        };
        get_jquery('{{ url('ingreso_clasificacion/listar_ordenes_trabajo') }}', datos, function(retorno) {
            modal_view('modal_listar_ordenes_trabajo', retorno,
                '<i class="fa fa-fw fa-plus"></i> Listado de Ordenes de Trabajo',
                true, false, '{{ isPC() ? '95%' : '' }}');
        });
    }

    function listar_pre_ordenes_trabajo(det_ped) {
        datos = {
            det_ped: det_ped,
        };
        get_jquery('{{ url('ingreso_clasificacion/listar_pre_ordenes_trabajo') }}', datos, function(retorno) {
            modal_view('modal_listar_pre_ordenes_trabajo', retorno,
                '<i class="fa fa-fw fa-plus"></i> Listado de Pre-Ordenes de Trabajo',
                true, false, '{{ isPC() ? '95%' : '' }}');
        });
    }

    function bloquear_distribucion(detalle_pedido, bloqueo) {
        if (bloqueo == 0)
            texto =
            "<div class='alert alert-warning text-center'>¿Esta seguro de <b>BLOQUEAR</b> la distribucion para este pedido?</div>";
        else
            texto =
            "<div class='alert alert-warning text-center'>¿Esta seguro de <b>DESBLOQUEAR</b> la distribucion para este pedido?</div>";

        modal_quest('modal_bloquear_distribucion', texto, 'Bloquear Distribucion', true, false, '40%',
            function() {
                datos = {
                    _token: '{{ csrf_token() }}',
                    detalle_pedido: detalle_pedido,
                }
                variedad = $('#id_receta_armar').val();
                longitud = $('#longitud_receta_armar').val();
                fecha = $('#fecha_receta_armar').val();
                post_jquery_m('{{ url('ingreso_clasificacion/bloquear_distribucion') }}', datos, function(
                    retorno) {
                    cerrar_modals();
                    armar_combinacion(variedad, longitud, fecha);
                });
            })
    }

    function copiar_distribucion(detalle_pedido) {
        texto =
            "<div class='alert alert-warning text-center'>¿Esta seguro de <b>COPIAR</b> la distribucion para los demas pedidos?</div>";

        modal_quest('modal_bloquear_distribucion', texto, 'Bloquear Distribucion', true, false, '40%',
            function() {
                ids_detalle_pedido_armar = $('.ids_detalle_pedido_armar');
                data = [];
                for (i = 0; i < ids_detalle_pedido_armar.length; i++) {
                    id_det = ids_detalle_pedido_armar[i].value;
                    if (id_det != detalle_pedido)
                        data.push(id_det);
                }
                datos = {
                    _token: '{{ csrf_token() }}',
                    detalle_pedido: detalle_pedido,
                    data: data,
                }
                variedad = $('#id_receta_armar').val();
                longitud = $('#longitud_receta_armar').val();
                fecha = $('#fecha_receta_armar').val();
                post_jquery_m('{{ url('ingreso_clasificacion/copiar_distribucion') }}', datos, function(
                    retorno) {
                    cerrar_modals();
                    armar_combinacion(variedad, longitud, fecha);
                });
            })
    }

    function confirmar_ramos(id_det_ped) {
        texto =
            "<div class='alert alert-info text-center' style='font-size: 16px'>¿Esta seguro de <b>ARMAR</b> los ramos?</div>";

        modal_quest('modal_confirmar_ramos', texto, 'Despachar la Orden de Trabajo', true, false, '40%',
            function() {
                datos = {
                    _token: '{{ csrf_token() }}',
                    id_det_ped: id_det_ped,
                    armar: parseInt($('#confirmar_ramos_' + id_det_ped).val()),
                }
                if (datos['armar'] <= parseInt($('#confirmar_ramos_' + id_det_ped).prop('max'))) {
                    variedad = $('#id_receta_armar').val();
                    longitud = $('#longitud_receta_armar').val();
                    fecha = $('#fecha_receta_armar').val();
                    post_jquery_m('{{ url('ingreso_clasificacion/confirmar_ramos') }}', datos, function() {
                        cerrar_modals();
                        armar_combinacion(variedad, longitud, fecha);
                        listar_reporte();
                    });
                } else
                    alerta(
                        '<div class="alert alert-warning text-center" style="font-size: 16px">La cantidad de ramos a <b>ARMAR</b> excede la cantidad <b>FALTANTE</b></div>'
                    )
            })
    }
</script>
