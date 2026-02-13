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
            INV. <br>DISP.
        </th>
        <th class="text-center th_yura_green" rowspan="2" style="width: 60px">
            OT / OA
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
    @foreach ($listado as $postco)
        @php
            $distribuciones = $postco->distribuciones;
            $tallos_x_ramo = 0;
            $disponibles = $postco->ramos;
        @endphp
        @foreach ($distribuciones as $pos_d => $dist)
            @php
                $variedad = $dist->item;
                $tallos_x_ramo += $dist->unidades;
                $inventario = getTotalInventarioByVariedad($dist->id_item);
                $inventarioDisponible = getInventarioDisponibleByVariedadFecha($variedad, $postco->fecha);
                $tallos_variedad = $dist->unidades * $postco->ramos;
                $posibles = intval($inventarioDisponible / $dist->unidades);
                if ($posibles < $disponibles) {
                    $disponibles = $posibles;
                }
                $getRamosOt = $postco->getRamosOt();
                $getRamosOa = $postco->getRamosOa();

                $pos_en_resumen = -1;
                foreach ($resumen_variedades as $pos => $r) {
                    if ($r['variedad']->id_variedad == $dist->id_item) {
                        $pos_en_resumen = $pos;
                    }
                }
                if ($pos_en_resumen != -1) {
                    $resumen_variedades[$pos_en_resumen]['tallos'] += $dist->unidades * $postco->ramos;
                } else {
                    $resumen_variedades[] = [
                        'variedad' => $variedad,
                        'tallos' => $dist->unidades * $postco->ramos,
                        'inventario' => $inventarioDisponible,
                    ];
                }
            @endphp
            <tr onmouseover="$('.row_{{ $postco->id_postco }}').css('background-color', '#dddddd')"
                onmouseleave="$('.row_{{ $postco->id_postco }}').css('background-color', '')"
                class="row_{{ $postco->id_postco }}">
                @if ($pos_d == 0)
                    <th class="text-center" style="border-color: #9d9d9d;" rowspan="{{ count($distribuciones) }}">
                        {{ convertDateToText($postco->fecha) }}
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d;" rowspan="{{ count($distribuciones) }}">
                        {{ $postco->ramos }}
                        <input type="hidden" class="postco_ramos_{{ $postco->id_postco }}"
                            value="{{ $postco->ramos }}">
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
                    <input type="hidden" class="unidades_item_{{ $postco->id_postco }}"
                        data-id_item="{{ $dist->id_item }}" value="{{ $dist->unidades }}"
                        data-inventario="{{ $inventarioDisponible }}">
                </th>
                @if ($pos_d == 0)
                    <th class="text-center" style="border-color: #9d9d9d;" rowspan="{{ count($distribuciones) }}"
                        id="celda_tallos_x_ramo_{{ $postco->id_postco }}">
                    </th>
                @endif
                <th class="text-center" style="border-color: #9d9d9d">
                    {{ $dist->unidades * $postco->ramos }}
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
                                onclick="listar_ordenes_trabajo('{{ $postco->id_postco }}')">
                                {{ $getRamosOt }}
                            </button>
                            <button type="button" class="btn btn-xs btn-yura_default"
                                title="Ver Ordenes de Alistamiento"
                                onclick="listar_ordenes_alistamiento('{{ $postco->id_postco }}')">
                                {{ $getRamosOa }}
                            </button>
                        </div>
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d;" rowspan="{{ count($distribuciones) }}">
                        {{ $postco->armados }}
                        @if ($postco->armados < $postco->ramos)
                            <input type="number" style="width: 100%; color: black" class="text-center"
                                placeholder="Armar" min="1" id="armar_ramos_{{ $postco->id_postco }}">
                            <button type="button" class="btn btn-xs btn-block btn-yura_dark"
                                onclick="armar_ramos('{{ $postco->id_postco }}')" style="height: 26px">
                                Grabar
                            </button>
                        @endif
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d;" rowspan="{{ count($distribuciones) }}">
                        <span class="btn-xs btn-yura_info" id="celda_disponibles_{{ $postco->id_postco }}"></span>
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d;" rowspan="{{ count($distribuciones) }}">
                        <input type="number" style="width: 100%; color: black" class="text-center" placeholder="OT"
                            min="1" id="procesar_ramos_{{ $postco->id_postco }}"
                            onchange="calcular_uso('{{ $postco->id_postco }}')">
                        @if ($postco->armados < $postco->ramos)
                            <div class="input-group-btn">
                                <button type="button"
                                    class="btn btn-block btn-yura_primary btn-xs dropdown-toggle btn_procesar_{{ $postco->id_postco }}"
                                    data-toggle="dropdown" aria-expanded="false">
                                    Procesar <span class="fa fa-caret-down"></span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-right sombra_pequeña"
                                    style="background-color: #c8c8c8">
                                    @foreach ($postco->clientes as $cli)
                                        <li>
                                            <a href="javascript:void(0)" style="color: black"
                                                onclick="store_ot('{{ $postco->id_postco }}', '{{ $cli->id_cliente }}')">
                                                {{ $cli->cliente->detalle()->nombre }}
                                                <sup>
                                                    {{ $cli->cantidad }}
                                                </sup>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                            @if ($postco->bloqueado == 0)
                                <button type="button"
                                    class="btn btn-xs btn-block btn-yura_default btn_procesar_{{ $postco->id_postco }}"
                                    style="height: 21px; margin-top: 0;"
                                    onclick="admin_receta('{{ $postco->id_postco }}')">
                                    Distribucion
                                </button>
                            @endif
                            <button type="button"
                                class="btn btn-xs btn-block btn-yura_dark btn_procesar_{{ $postco->id_postco }}"
                                style="height: 21px; margin-top: 0;"
                                onclick="copiar_receta('{{ $postco->id_postco }}')">
                                <i class="fa fa-fw fa-copy"></i> Copiar
                            </button>
                            <button type="button"
                                class="btn btn-xs btn-block btn-yura_{{ $postco->bloqueado == 0 ? 'warning' : 'danger' }} btn_procesar_{{ $postco->id_postco }}"
                                style="height: 21px; margin-top: 0;"
                                onclick="bloquear_postco('{{ $postco->id_postco }}', '{{ $postco->bloqueado }}')">
                                <i class="fa fa-fw fa-{{ $postco->bloqueado == 0 ? 'lock' : 'unlock' }}"></i>
                                {{ $postco->bloqueado == 0 ? 'Bloquear' : 'Desbloquear' }}
                            </button>

                            <div class="input-group-btn">
                                <button type="button"
                                    class="btn btn-block btn-yura_primary btn-xs dropdown-toggle hidden"
                                    data-toggle="dropdown" aria-expanded="false"
                                    id="btn_procesar_oa_{{ $postco->id_postco }}">
                                    Procesar OA <span class="fa fa-caret-down"></span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-right sombra_pequeña"
                                    style="background-color: #c8c8c8">
                                    @foreach ($postco->clientes as $cli)
                                        <li>
                                            <a href="javascript:void(0)" style="color: black"
                                                onclick="store_oa('{{ $postco->id_postco }}', '{{ $cli->id_cliente }}')">
                                                {{ $cli->cliente->detalle()->nombre }}
                                                <sup>
                                                    {{ $cli->cantidad }}
                                                </sup>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </th>
                @endif
                <th class="text-center" style="border-color: #9d9d9d">
                    <input type="number" style="width: 100%; color: black" class="text-center" min="1"
                        id="usar_tallos_{{ $postco->id_postco }}_{{ $dist->id_item }}" max="1">
                </th>
            </tr>
        @endforeach
        <script type="text/javascript">
            $('#celda_tallos_x_ramo_{{ $postco->id_postco }}').html('{{ $tallos_x_ramo }}')
            $('#celda_disponibles_{{ $postco->id_postco }}').html('{{ $disponibles }}')
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
                            <td class="text-center" style="border-color: #9d9d9d">
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
        datos = {
            _token: '{{ csrf_token() }}',
            id: id,
            cantidad: $('#armar_ramos_' + id).val()
        }
        post_jquery_m('{{ url('preproduccion/armar_ramos') }}', datos, function() {
            cerrar_modals();
            modal_receta('{{ $receta->id_variedad }}', '{{ $longitud }}');
            listar_reporte();
        })
    }

    function store_ot(id, cliente = '') {
        datos = {
            _token: '{{ csrf_token() }}',
            id: id,
            cliente: cliente,
            cantidad: parseInt($('#procesar_ramos_' + id).val()),
            fecha: $('#fecha_filtro').val(),
            longitud: '{{ $longitud }}',
        }
        if (datos['cantidad'] > 0 && cliente != '')
            post_jquery_m('{{ url('preproduccion/store_ot') }}', datos, function() {
                cerrar_modals();
                modal_receta('{{ $receta->id_variedad }}', '{{ $longitud }}');
                listar_reporte();
            })
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

    function calcular_uso(postco) {
        procesar_ramos = parseInt($('#procesar_ramos_' + postco).val());
        unidades_item = $('.unidades_item_' + postco);
        fallos = false;
        for (i = 0; i < unidades_item.length; i++) {
            unidades = parseInt(unidades_item[i].value);
            id_item = unidades_item[i].getAttribute('data-id_item');
            if (unidades_item[i].getAttribute('data-inventario') != '')
                inventario = parseInt(unidades_item[i].getAttribute('data-inventario'));
            else
                inventario = 0;

            uso = unidades * procesar_ramos;
            $('#usar_tallos_' + postco + '_' + id_item).val(uso);
            if (uso > inventario) {
                $('#usar_tallos_' + postco + '_' + id_item).css('background-color', '#ffb2b2');
                fallos = true;
            } else {
                $('#usar_tallos_' + postco + '_' + id_item).css('background-color', '');
            }
        }
        if (fallos) {
            $('.btn_procesar_' + postco).addClass('hidden');
            $('#btn_procesar_oa_' + postco).removeClass('hidden');
        } else {
            $('.btn_procesar_' + postco).removeClass('hidden');
            $('#btn_procesar_oa_' + postco).addClass('hidden');
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

    function listar_ordenes_trabajo(postco) {
        datos = {
            postco: postco,
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
