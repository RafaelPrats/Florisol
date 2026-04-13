<div style="overflow-x: scroll; overflow-y: scroll; max-height: 700px">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
        <tr style="font-size: 0.8em">
            <th class="text-center th_yura_green">
                <input type="checkbox" onchange="$('.check_proyecto').prop('checked', $(this).prop('checked'))">
            </th>
            <th class="text-center th_yura_green">
                PACKING
            </th>
            <th class="text-center th_yura_green">
                CLIENTE
            </th>
            <th class="text-center th_yura_green">
                TIPO
            </th>
            <th class="text-center th_yura_green">
                FECHA
            </th>
            <th class="text-center th_yura_green">
                CAJAS
            </th>
            <th class="text-center th_yura_green">
                BQT
            </th>
            <th class="text-center th_yura_green" colspan="2">
                BUNCHES
            </th>
            <th class="text-center th_yura_green" colspan="2">
                STEMS
            </th>
            <th class="text-center th_yura_green">
                PRECIO
            </th>
            <th class="text-center th_yura_green">
                OPCIONES
            </th>
        </tr>
        @php
            $resumen_variedad_longitud = [];
            $resumen_piezas = [];
        @endphp
        @foreach ($proyectos as $proyecto)
            @php
                $cant_rows = 0;
                $cajas = $proyecto->cajas;
                $ramos_proy = 0;
                $tallos_proy = 0;
                $monto_proy = 0;
                $cajas_proy = 0;
                foreach ($cajas as $c) {
                    $cajas_proy += $c->cantidad;
                    foreach ($c->detalles as $d) {
                        $cant_rows++;
                        $ramos_proy += $d->ramos_x_caja * $c->cantidad;
                        $tallos_proy += $d->tallos_x_ramo * $d->ramos_x_caja * $c->cantidad;
                        $monto_proy += $d->precio * $d->ramos_x_caja * $c->cantidad;
                    }
                }
            @endphp
            @foreach ($cajas as $pos_c => $caja)
                @php
                    $detalles = $caja->detalles;

                    //RESUMEN PIEZAS
                    $pos_en_resumen = -1;
                    foreach ($resumen_piezas as $pos => $r) {
                        if ($r['tipo_caja'] == $caja->tipo_caja) {
                            $pos_en_resumen = $pos;
                        }
                    }
                    if ($pos_en_resumen != -1) {
                        $resumen_piezas[$pos_en_resumen]['cantidad'] += $caja->cantidad;
                    } else {
                        $resumen_piezas[] = [
                            'tipo_caja' => $caja->tipo_caja,
                            'cantidad' => $caja->cantidad,
                        ];
                    }
                @endphp
                @foreach ($detalles as $pos_d => $detalle)
                    @php
                        $variedad = $detalle->variedad;
                        $tallos = $caja->cantidad * $detalle->ramos_x_caja * $detalle->tallos_x_ramo;
                        $ramos = $caja->cantidad * $detalle->ramos_x_caja;
                        $venta = $caja->cantidad * $detalle->ramos_x_caja * $detalle->precio;

                        //RESUMEN VARIEDAD/LONGITUD
                        $pos_en_resumen = -1;
                        foreach ($resumen_variedad_longitud as $pos => $r) {
                            if (
                                $r['id_variedad'] == $detalle->id_variedad &&
                                $r['longitud'] == $detalle->longitud_ramo
                            ) {
                                $pos_en_resumen = $pos;
                            }
                        }
                        if ($pos_en_resumen != -1) {
                            $resumen_variedad_longitud[$pos_en_resumen]['tallos'] += $tallos;
                            $resumen_variedad_longitud[$pos_en_resumen]['ramos'] += $ramos;
                            $resumen_variedad_longitud[$pos_en_resumen]['venta'] += $venta;
                        } else {
                            $resumen_variedad_longitud[] = [
                                'id_variedad' => $detalle->id_variedad,
                                'longitud' => $detalle->longitud_ramo,
                                'nombre_planta' => $variedad->planta->nombre,
                                'nombre_variedad' => $variedad->nombre,
                                'tallos' => $tallos,
                                'ramos' => $ramos,
                                'venta' => $venta,
                            ];
                        }
                    @endphp
                    <tr class="tr_pedido_{{ $proyecto->id_proyecto }}" style="font-size: 0.9em"
                        onmouseover="$('.tr_pedido_{{ $proyecto->id_proyecto }}').css('background-color', 'cyan')"
                        onmouseleave="$('.tr_pedido_{{ $proyecto->id_proyecto }}').css('background-color', '')">
                        @if ($pos_d == 0 && $pos_c == 0)
                            <th class="text-center" style="border-color: #9d9d9d" rowspan="{{ $cant_rows }}">
                                <input type="checkbox" id="check_proyecto_{{ $proyecto->id_proyecto }}"
                                    class="check_proyecto">
                            </th>
                            <th class="text-center" style="border-color: #9d9d9d" rowspan="{{ $cant_rows }}">
                                <small>{{ $proyecto->segmento }}</small>
                                @if ($proyecto->packing != '')
                                    <br>
                                    {{ $proyecto->packing }}
                                @endif
                            </th>
                            <th class="text-center" style="border-color: #9d9d9d" rowspan="{{ $cant_rows }}">
                                {{ $proyecto->cliente->detalle()->nombre }}
                                <br>
                                <small>Ventas: ${{ $monto_proy }}</small>
                                <br>
                                <small>Cajas: {{ $cajas_proy }}</small>
                            </th>
                            <th class="text-center" style="border-color: #9d9d9d" rowspan="{{ $cant_rows }}">
                                {{ $proyecto->tipo }}
                                @if ($proyecto->tipo == 'SO')
                                    <br>
                                    #{{ $proyecto->orden_fija }}
                                @endif
                            </th>
                            <th class="text-center" style="border-color: #9d9d9d" rowspan="{{ $cant_rows }}">
                                {{ $proyecto->fecha }}
                            </th>
                        @endif
                        @if ($pos_d == 0)
                            <th class="text-center" style="border-color: #9d9d9d" rowspan="{{ count($detalles) }}">
                                {{ $caja->cantidad }} <small>{{ $caja->tipo_caja }}</small>
                            </th>
                        @endif
                        <th class="text-center" style="border-color: #9d9d9d">
                            {{ $variedad->nombre }}
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d">
                            {{ $detalle->ramos_x_caja }}
                        </th>
                        @if ($pos_d == 0 && $pos_c == 0)
                            <th class="text-center" style="border-color: #9d9d9d" rowspan="{{ $cant_rows }}">
                                {{ $ramos_proy }}
                            </th>
                        @endif
                        <th class="text-center" style="border-color: #9d9d9d">
                            {{ $detalle->tallos_x_ramo }}
                        </th>
                        @if ($pos_d == 0 && $pos_c == 0)
                            <th class="text-center" style="border-color: #9d9d9d" rowspan="{{ $cant_rows }}">
                                {{ $tallos_proy }}
                            </th>
                        @endif
                        <th class="text-center" style="border-color: #9d9d9d">
                            ${{ $detalle->precio }}
                        </th>
                        @if ($pos_d == 0 && $pos_c == 0)
                            <th class="text-center" style="border-color: #9d9d9d" rowspan="{{ $cant_rows }}">
                                <div class="input-group-btn">
                                    <button type="button" class="btn btn-yura_default btn-xs dropdown-toggle btn-block"
                                        data-toggle="dropdown" aria-expanded="true">
                                        Acciones <span class="fa fa-caret-down"></span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-right sombra_pequeña"
                                        style="background-color: #c8c8c8;">
                                        <li>
                                            <a class="link_opciones_{{ $proyecto->id_proyecto }}"
                                                href="javascript:void(0)" style="color: black"
                                                onclick="editar_proyecto('{{ $proyecto->id_proyecto }}')">
                                                <i class="fa fa-fw fa-pencil"></i>
                                                Editar pedido
                                            </a>
                                        </li>
                                        <li>
                                            <a class="link_opciones_{{ $proyecto->id_proyecto }}"
                                                href="javascript:void(0)" style="color: black"
                                                onclick="copiar_pedido('{{ $proyecto->id_proyecto }}')">
                                                <i class="fa fa-files-o fa-fw" aria-hidden="true"></i>
                                                Copiar pedido
                                            </a>
                                        </li>
                                        <li>
                                            <a class="link_opciones_{{ $proyecto->id_proyecto }}"
                                                href="javascript:void(0)" id="edit_pedidos" style="color: black"
                                                onclick="delete_pedido('{{ $proyecto->id_proyecto }}')">
                                                <i class="fa fa-fw fa-trash" aria-hidden="true"></i>
                                                Cancelar pedido
                                            </a>
                                        </li>
                                        <li>
                                            <a target="_blank" style="color: black" href="javascript:void(0)"
                                                onclick="descargar_packing('{{ $proyecto->id_proyecto }}')">
                                                <i class="fa fa-cubes fa-fw"></i>
                                                Descargar packing list
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </th>
                        @endif
                    </tr>
                @endforeach
            @endforeach
        @endforeach
    </table>
</div>

<legend class="text-center" style="margin-bottom: 5px; font-size: 1.1em">
    RESUMEN
</legend>
<div style="overflow-x: scroll">
    <table style="width: 100%; font-size: 0.9em">
        <tbody>
            <tr>
                <td style="vertical-align: top; width: 85%; min-width: 420px" class="padding_lateral_5">
                    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
                        <tbody>
                            <tr>
                                <th class="padding_lateral_5 th_yura_green" colspan="2">
                                    VARIEDAD-LONGNITUD
                                </th>
                                <th class="padding_lateral_5 th_yura_green padding_lateral_5">
                                    TALLOS
                                </th>
                                <th class="padding_lateral_5 th_yura_green padding_lateral_5">
                                    RAMOS
                                </th>
                                <th class="padding_lateral_5 th_yura_green padding_lateral_5">
                                    MONTO
                                </th>
                            </tr>
                            @php
                                $total_tallos = 0;
                                $total_ramos = 0;
                                $total_monto = 0;
                            @endphp
                            @foreach ($resumen_variedad_longitud as $item)
                                @php
                                    $total_tallos += $item['tallos'];
                                    $total_ramos += $item['ramos'];
                                    $total_monto += $item['venta'];
                                @endphp
                                <tr onmouseover="$(this).addClass('bg-yura_dark')"
                                    onmouseleave="$(this).removeClass('bg-yura_dark')" class="">
                                    <th class="padding_lateral_5" style="border-color: #9d9d9d;">
                                        {{ $item['nombre_planta'] }}
                                    </th>
                                    <th class="padding_lateral_5" style="border-color: #9d9d9d; width: 25%">
                                        {{ $item['nombre_variedad'] }} {{ $item['longitud'] }}cm
                                    </th>
                                    <th class="padding_lateral_5" style="border-color: #9d9d9d">
                                        {{ $item['tallos'] }}
                                    </th>
                                    <th class="padding_lateral_5" style="border-color: #9d9d9d">
                                        {{ $item['ramos'] }}
                                    </th>
                                    <th class="padding_lateral_5" style="border-color: #9d9d9d">
                                        ${{ round($item['venta'], 2) }}
                                    </th>
                                </tr>
                            @endforeach
                            <tr>
                                <th class="padding_lateral_5 th_yura_green" colspan="2">
                                    TOTALES
                                </th>
                                <th class="padding_lateral_5 th_yura_green padding_lateral_5">
                                    {{ $total_tallos }}
                                </th>
                                <th class="padding_lateral_5 th_yura_green padding_lateral_5">
                                    {{ $total_ramos }}
                                </th>
                                <th class="padding_lateral_5 th_yura_green padding_lateral_5">
                                    ${{ round($total_monto, 2) }}
                                </th>
                            </tr>
                        </tbody>
                    </table>
                </td>
                <td style="vertical-align: top;" class="padding_lateral_5">
                    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
                        <tbody>
                            <tr>
                                <th class="text-center th_yura_green">
                                    PIEZAS
                                </th>
                                <th class="text-center th_yura_green padding_lateral_5">
                                    FULL
                                </th>
                            </tr>
                            @php
                                $total_piezas = 0;
                            @endphp
                            @foreach ($resumen_piezas as $item)
                                @php
                                    $total_piezas += $item['cantidad'];
                                @endphp
                                <tr onmouseover="$(this).addClass('bg-yura_dark')"
                                    onmouseleave="$(this).removeClass('bg-yura_dark')" class="">
                                    <th class="text-center" style="border-color: #9d9d9d">
                                        {{ $item['tipo_caja'] }}
                                    </th>
                                    <th class="text-center" style="border-color: #9d9d9d">
                                        {{ $item['cantidad'] }}
                                    </th>
                                </tr>
                            @endforeach
                            <tr>
                                <th class="text-center th_yura_green">
                                    TOTALES
                                </th>
                                <th class="text-center th_yura_green padding_lateral_5">
                                    {{ $total_piezas }}
                                </th>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<script>
    function editar_proyecto(id) {
        datos = {
            id: id
        }
        get_jquery('{{ url('proyectos/editar_proyecto') }}', datos, function(retorno) {
            modal_view('modal_add_proyecto', retorno, '<i class="fa fa-fw fa-plus"></i> Editar Pedido',
                true, false, '{{ isPC() ? '95%' : '' }}',
                function() {});
        })
    }

    function copiar_pedido(pedido) {
        datos = {
            pedido: pedido
        }
        get_jquery('{{ url('proyectos/copiar_pedido') }}', datos, function(retorno) {
            modal_view('modal_copiar_pedido', retorno, '<i class="fa fa-fw fa-calendar"></i> Copiar Pedido',
                true, false, '{{ isPC() ? '50%' : '' }}',
                function() {});
        });
    }

    function delete_pedido(id) {
        mensaje = {
            title: '<i class="fa fa-fw fa-save"></i> Cancelar Pedido',
            mensaje: '<div class="alert alert-warning text-center"><i class="fa fa-fw fa-exclamation-triangle"></i> ¿Está seguro de <b>CANCELAR</b> este pedido?</div>',
        };
        modal_quest('modal_update_especificaciones', mensaje['mensaje'], mensaje['title'], true, false,
            '{{ isPC() ? '35%' : '' }}',
            function() {
                datos = {
                    _token: '{{ csrf_token() }}',
                    id: id
                };
                post_jquery_m('{{ url('proyectos/delete_pedido') }}', datos, function() {
                    listar_reporte();
                });
            });
    }
</script>
