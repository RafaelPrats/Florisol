<div style="overflow-x: scroll; overflow-y: scroll; height: 700px">
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
                @endphp
                @foreach ($detalles as $pos_d => $detalle)
                    <tr class="tr_pedido_{{ $proyecto->id_proyecto }}" style="font-size: 0.9em"
                        onmouseover="$('.tr_pedido_{{ $proyecto->id_proyecto }}').css('background-color', 'cyan')"
                        onmouseleave="$('.tr_pedido_{{ $proyecto->id_proyecto }}').css('background-color', '')">
                        @if ($pos_d == 0 && $pos_c == 0)
                            <th class="text-center" style="border-color: #9d9d9d" rowspan="{{ $cant_rows }}">
                                <input type="checkbox" id="check_proyecto_{{ $proyecto->id_proyecto }}"
                                    class="check_proyecto">
                            </th>
                            <th class="text-center" style="border-color: #9d9d9d" rowspan="{{ $cant_rows }}">
                                {{ $proyecto->packing }}
                            </th>
                            <th class="text-center" style="border-color: #9d9d9d" rowspan="{{ $cant_rows }}">
                                {{ $proyecto->cliente->detalle()->nombre }}
                                <br>
                                <small>Ventas: ${{ $monto_proy }}</small>
                                <br>
                                <small>Cajas: ${{ $cajas_proy }}</small>
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
                            {{ $detalle->variedad->nombre }}
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
                                    <button type="button" class="btn btn-yura_default btn-xs dropdown-toggle"
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

<script>
    function editar_proyecto(id) {
        datos = {
            id: id
        }
        get_jquery('{{ url('proyectos/editar_proyecto') }}', datos, function(retorno) {
            modal_view('modal_editar_proyecto', retorno, '<i class="fa fa-fw fa-plus"></i> Editar Pedido',
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
