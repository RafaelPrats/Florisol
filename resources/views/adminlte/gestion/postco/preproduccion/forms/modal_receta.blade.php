<legend class="text-center" style="margin-bottom: 5px; font-size: 1.1em;">
    Pedidos de la RECETA "<b>{{ $receta->nombre }}</b>" de "<b>{{ $longitud }}cm</b>"
</legend>
<table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
    <tr>
        <th class="text-center th_yura_green" rowspan="2" style="width: 130px">
            FECHA
        </th>
        <th class="text-center th_yura_green" rowspan="2" style="width: 60px">
            RAMOS
        </th>
        <th class="text-center th_yura_green" colspan="4">
            DISTRIBUCION RECETA
        </th>
        <th class="text-center th_yura_green" rowspan="2" style="width: 60px">
            TALLOS
        </th>
        <th class="text-center th_yura_green" rowspan="2" style="width: 60px">
            INV. TOTAL
        </th>
        <th class="text-center th_yura_green" rowspan="2" style="width: 60px">
            INV. DISP.
        </th>
        <th class="text-center th_yura_green" rowspan="2" style="width: 60px">
            OT
        </th>
        <th class="text-center th_yura_green" rowspan="2" style="width: 60px">
            ARMADOS
        </th>
        <th class="text-center th_yura_green" rowspan="2" style="width: 60px">
            Disp.
        </th>
        <th class="text-center th_yura_green" rowspan="2" style="width: 80px">
            ARMAR
        </th>
        <th class="text-center th_yura_green" rowspan="2" style="width: 60px">
            USAR
        </th>
    </tr>
    <tr>
        <th class="text-center bg-yura_dark">
            PLANTA
        </th>
        <th class="text-center bg-yura_dark">
            VARIEDAD
        </th>
        <th class="text-center bg-yura_dark" style="width: 60px;">
            UNIDADES
        </th>
        <th class="text-center bg-yura_dark" style="width: 30px;">
            TxR
        </th>
    </tr>
    @php
        $resumen_variedades = [];
    @endphp
    @foreach ($listado as $detalle)
        @php
            $distribuciones = $detalle->distribuciones;
            $tallos_x_ramo = 0;
            $disponibles = $detalle->ramos;
        @endphp
        @foreach ($distribuciones as $pos_d => $dist)
            @php
                $variedad = $dist->variedad;
                $tallos_x_ramo += $dist->unidades;
                $inventario = getTotalInventarioByVariedad($dist->id_variedad);
                $inventarioDisponible = getInventarioDisponibleByVariedadFecha($variedad, $detalle->fecha);
                $tallos_variedad = $dist->unidades * $detalle->ramos;
                $posibles = intval($inventarioDisponible / $dist->unidades);
                if ($posibles < $disponibles) {
                    $disponibles = $posibles;
                }
                $getRamosOt = $detalle->getRamosOt();

                $pos_en_resumen = -1;
                foreach ($resumen_variedades as $pos => $r) {
                    if ($r['variedad']->id_variedad == $dist->id_variedad) {
                        $pos_en_resumen = $pos;
                    }
                }
                if ($pos_en_resumen != -1) {
                    $resumen_variedades[$pos_en_resumen]['tallos'] += $dist->unidades * $detalle->ramos;
                } else {
                    $resumen_variedades[] = [
                        'variedad' => $variedad,
                        'tallos' => $dist->unidades * $detalle->ramos,
                        'inventario' => $inventarioDisponible,
                    ];
                }
            @endphp
            <tr onmouseover="$('.row_{{ $detalle->id_detalle_caja_proyecto }}').css('background-color', '#dddddd')"
                onmouseleave="$('.row_{{ $detalle->id_detalle_caja_proyecto }}').css('background-color', '')"
                class="row_{{ $detalle->id_detalle_caja_proyecto }}">
                @if ($pos_d == 0)
                    <th class="text-center" style="border-color: #9d9d9d;" rowspan="{{ count($distribuciones) }}">
                        {{ convertDateToText($detalle->fecha) }}
                        <br>
                        <small><em>{{ $detalle->cliente_nombre }}</em></small>
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d;" rowspan="{{ count($distribuciones) }}">
                        {{ $detalle->ramos }}
                        <input type="hidden" class="postco_ramos_{{ $detalle->id_detalle_caja_proyecto }}"
                            value="{{ $detalle->ramos }}">
                    </th>
                @endif
                <th class="text-center" style="border-color: #9d9d9d">
                    {{ $variedad->planta->nombre }}
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    {{ $variedad->nombre }}
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    {{ $dist->unidades }}
                    <input type="hidden" class="unidades_item_{{ $detalle->id_detalle_caja_proyecto }}"
                        data-id_item="{{ $dist->id_variedad }}" value="{{ $dist->unidades }}"
                        data-inventario="{{ $inventarioDisponible }}">
                </th>
                @if ($pos_d == 0)
                    <th class="text-center" style="border-color: #9d9d9d;" rowspan="{{ count($distribuciones) }}"
                        id="celda_tallos_x_ramo_{{ $detalle->id_detalle_caja_proyecto }}">
                    </th>
                @endif
                <th class="text-center" style="border-color: #9d9d9d">
                    {{ $dist->unidades * $detalle->ramos }}
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    {{ $inventario }}
                </th>
                <th class="text-center" style="border-color: #9d9d9d; background-color: #eeeeee">
                    {{ $inventarioDisponible }}
                </th>
                @if ($pos_d == 0)
                    <th class="text-center" style="border-color: #9d9d9d;" rowspan="{{ count($distribuciones) }}">
                        <div class="btn-group">
                            <button type="button" class="btn btn-xs btn-yura_dark" title="Ver Ordenes de Trabajo"
                                onclick="listar_ordenes_trabajo('{{ $detalle->id_detalle_caja_proyecto }}')">
                                {{ $getRamosOt }}
                            </button>
                        </div>
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d;" rowspan="{{ count($distribuciones) }}">
                        {{ $detalle->armados }}
                        @if ($detalle->armados < $detalle->ramos)
                            <input type="number" style="width: 100%; color: black" class="text-center"
                                placeholder="Armar" min="1"
                                id="armar_ramos_{{ $detalle->id_detalle_caja_proyecto }}">
                            <button type="button" class="btn btn-xs btn-block btn-yura_dark"
                                onclick="armar_ramos('{{ $detalle->id_detalle_caja_proyecto }}')" style="height: 26px">
                                Grabar
                            </button>
                        @endif
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d;" rowspan="{{ count($distribuciones) }}">
                        <span class="btn-xs btn-yura_info"
                            id="celda_disponibles_{{ $detalle->id_detalle_caja_proyecto }}"></span>
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d;" rowspan="{{ count($distribuciones) }}">
                        <input type="number" style="width: 100%; color: black" class="text-center" placeholder="OT"
                            min="1" id="procesar_ramos_{{ $detalle->id_detalle_caja_proyecto }}"
                            onchange="calcular_uso('{{ $detalle->id_detalle_caja_proyecto }}')">
                        @if ($detalle->armados < $detalle->ramos)
                            <button type="button" onclick="store_ot('{{ $detalle->id_detalle_caja_proyecto }}')"
                                class="btn btn-block btn-yura_primary btn-xs btn_procesar_{{ $detalle->id_detalle_caja_proyecto }}">
                                Procesar
                            </button>
                            <button type="button"
                                class="btn btn-xs btn-block btn-yura_default btn_procesar_{{ $detalle->id_detalle_caja_proyecto }}"
                                style="height: 21px; margin-top: 0;"
                                onclick="admin_receta('{{ $detalle->id_detalle_caja_proyecto }}')">
                                Distribucion
                            </button>
                            <button type="button"
                                class="btn btn-xs btn-block btn-yura_dark btn_procesar_{{ $detalle->id_detalle_caja_proyecto }}"
                                style="height: 21px; margin-top: 0;"
                                onclick="copiar_receta('{{ $detalle->id_detalle_caja_proyecto }}')">
                                <i class="fa fa-fw fa-copy"></i> Copiar
                            </button>
                        @endif
                    </th>
                @endif
                <th class="text-center" style="border-color: #9d9d9d">
                    <input type="number" style="width: 100%; color: black" class="text-center" min="1"
                        id="usar_tallos_{{ $detalle->id_detalle_caja_proyecto }}_{{ $dist->id_variedad }}"
                        max="1">
                </th>
            </tr>
        @endforeach
        <script type="text/javascript">
            $('#celda_tallos_x_ramo_{{ $detalle->id_detalle_caja_proyecto }}').html('{{ $tallos_x_ramo }}')
            $('#celda_disponibles_{{ $detalle->id_detalle_caja_proyecto }}').html('{{ $disponibles }}')
        </script>
    @endforeach
</table>

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
                            Saldo
                        </th>
                    </tr>
                </thead>
                @php
                    $total_tallos = 0;
                    $total_inventario = 0;
                @endphp
                <tbody>
                    @foreach ($resumen_variedades as $r)
                        @php
                            $inventario = $r['inventario'];
                            $saldo = $inventario - $r['tallos'] + 0;
                            $total_tallos += $r['tallos'];
                            $total_inventario += $inventario;
                        @endphp
                        <tr onmouseover="$(this).addClass('bg-yura_dark')"
                            onmouseleave="$(this).removeClass('bg-yura_dark')">
                            <th class="text-center" style="border-color: #9d9d9d">
                                {{ $r['variedad']->planta->nombre }}
                            </th>
                            <th class="text-center" style="border-color: #9d9d9d">
                                {{ $r['variedad']->nombre }}
                            </th>
                            <td class="text-center" style="border-color: #9d9d9d">
                                {{ number_format($r['tallos']) }}
                            </td>
                            <td class="text-center"
                                style="border-color: #9d9d9d; background-color: #eeeeee; color: black">
                                {{ number_format($inventario) }}
                                <input type="hidden"
                                    id="inventario_variedad_armar_{{ $r['variedad']->id_variedad }}"
                                    value="{{ $inventario != '' ? $inventario : 0 }}">
                            </td>
                            <th class="text-center"
                                style="border-color: #9d9d9d; color: {{ $saldo < 0 ? 'red' : '' }}">
                                {{ number_format($saldo) }}
                            </th>
                        </tr>
                    @endforeach
                </tbody>
                @php
                    $saldo_total = $total_inventario - $total_tallos;
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
                    <th class="text-center th_yura_green" style="color: {{ $saldo_total < 0 ? 'red' : '' }}">
                        {{ number_format($saldo_total) }}
                    </th>
                </tr>
            </table>
        </div>
    </div>
</div>

<script type="text/javascript">
    function armar_ramos(id) {
        texto =
            "<div class='alert alert-warning text-center'>¿Esta seguro de <b>ARMAR</b> los ramos manualmente?</div>";

        modal_quest('modal_armar_ramos', texto, 'Mensaje de confirmacion', true, false, '40%',
            function() {
                datos = {
                    _token: '{{ csrf_token() }}',
                    id: id,
                    cantidad: $('#armar_ramos_' + id).val()
                }
                post_jquery_m('{{ url('preproduccion/armar_ramos') }}', datos, function() {
                    cerrar_modals();
                    modal_receta('{{ $receta->id_variedad }}', '{{ $longitud }}');
                    listar_reporte();
                });
            });
    }

    function store_ot(id) {
        texto =
            "<div class='alert alert-warning text-center'>¿Esta seguro de <b>PROCESAR</b> la orden de trabajo?</div>";

        modal_quest('modal_store_ot', texto, 'Mensaje de confirmacion', true, false, '40%',
            function() {
                datos = {
                    _token: '{{ csrf_token() }}',
                    id: id,
                    cantidad: parseInt($('#procesar_ramos_' + id).val()),
                    fecha: $('#fecha_filtro').val(),
                    longitud: '{{ $longitud }}',
                }
                if (datos['cantidad'] > 0)
                    post_jquery_m('{{ url('preproduccion/store_ot') }}', datos, function() {
                        cerrar_modals();
                        modal_receta('{{ $receta->id_variedad }}', '{{ $longitud }}');
                        listar_reporte();
                    });
            });
    }

    function store_oa(id, cliente = '') {
        datos = {
            _token: '{{ csrf_token() }}',
            id: id,
            cliente: cliente,
            cantidad: parseInt($('#procesar_ramos_' + id).val()),
            fecha: $('#fecha_filtro').val(),
            longitud: '{{ $longitud }}',
        }
        if (datos['cantidad'] > 0 && cliente != '')
            post_jquery_m('{{ url('preproduccion/store_oa') }}', datos, function() {
                cerrar_modals();
                modal_receta('{{ $receta->id_variedad }}', '{{ $longitud }}');
                listar_reporte();
            })
    }

    function calcular_uso(id_detalle) {
        procesar_ramos = parseInt($('#procesar_ramos_' + id_detalle).val());
        unidades_item = $('.unidades_item_' + id_detalle);
        fallos = false;
        for (i = 0; i < unidades_item.length; i++) {
            unidades = parseInt(unidades_item[i].value);
            id_item = unidades_item[i].getAttribute('data-id_item');
            if (unidades_item[i].getAttribute('data-inventario') != '')
                inventario = parseInt(unidades_item[i].getAttribute('data-inventario'));
            else
                inventario = 0;

            uso = unidades * procesar_ramos;
            $('#usar_tallos_' + id_detalle + '_' + id_item).val(uso);
            if (uso > inventario) {
                $('#usar_tallos_' + id_detalle + '_' + id_item).css('background-color', '#ffb2b2');
                fallos = true;
            } else {
                $('#usar_tallos_' + id_detalle + '_' + id_item).css('background-color', '');
            }
        }
        if (fallos) {
            $('.btn_procesar_' + id_detalle).addClass('hidden');
            $('#btn_procesar_oa_' + id_detalle).removeClass('hidden');
        } else {
            $('.btn_procesar_' + id_detalle).removeClass('hidden');
            $('#btn_procesar_oa_' + id_detalle).addClass('hidden');
        }
    }

    function admin_receta(id) {
        datos = {
            id: id,
        };
        get_jquery('{{ url('preproduccion/admin_receta') }}', datos, function(retorno) {
            modal_view('modal_admin_receta', retorno, '<i class="fa fa-fw fa-plus"></i> Administrar receta',
                true, false, '{{ isPC() ? '95%' : '' }}');
        });
    }

    function listar_ordenes_trabajo(id) {
        datos = {
            id: id,
        };
        get_jquery('{{ url('preproduccion/listar_ordenes_trabajo') }}', datos, function(retorno) {
            modal_view('modal_listar_ordenes_trabajo', retorno,
                '<i class="fa fa-fw fa-plus"></i> Listado de Ordenes de Trabajo',
                true, false, '{{ isPC() ? '95%' : '' }}');
        });
    }

    function listar_ordenes_alistamiento(postco) {
        datos = {
            postco: postco,
        };
        get_jquery('{{ url('preproduccion/listar_ordenes_alistamiento') }}', datos, function(retorno) {
            modal_view('modal_listar_ordenes_alistamiento', retorno,
                '<i class="fa fa-fw fa-plus"></i> Listado de Ordenes de Alistamiento',
                true, false, '{{ isPC() ? '95%' : '' }}');
        });
    }

    function copiar_receta(id) {
        datos = {
            _token: '{{ csrf_token() }}',
            id: id,
            desde: $('#desde_filtro').val(),
            hasta: $('#hasta_filtro').val(),
        }
        post_jquery_m('{{ url('preproduccion/copiar_receta') }}', datos, function() {
            cerrar_modals();
            modal_receta('{{ $receta->id_variedad }}', '{{ $longitud }}');
            listar_reporte();
        });
    }

    function bloquear_postco(id, bloqueado) {
        texto = bloqueado == 1 ? 'DESBLOQUEAR' : 'BLOQUEAR';
        mensaje = {
            title: '<i class="fa fa-fw fa-trash"></i> Bloquear Receta',
            mensaje: '<div class="alert alert-warning text-center"><i class="fa fa-fw fa-exclamation-triangle"></i> ¿Está seguro de <b>' +
                texto + '</b> esta receta?</div>' +
                "<input type='password' id='codigo_autorizacion_despachar' stylw='width: 100%' class='text-center form-control' placeholder='CODIGO de AUTORIZACION'>",
        };
        modal_quest('modal-quest_bloquear_postco', mensaje['mensaje'], mensaje['title'], true, false, '50%',
            function() {
                datos = {
                    _token: '{{ csrf_token() }}',
                    id: id,
                    codigo: $('#codigo_autorizacion_despachar').val(),
                }
                post_jquery_m('{{ url('preproduccion/bloquear_postco') }}', datos, function() {
                    cerrar_modals();
                    modal_receta('{{ $receta->id_variedad }}', '{{ $longitud }}');
                });
            });
    }
</script>
