<legend class="text-center" style="margin-bottom: 5px; font-size: 1.3em">
    Confirmaciones del pedido <b>#{{ $proyecto->id_proyecto }}</b>
    <button type="button" class="btn btn-xs btn-yura_default">
        <i class="fa fa-fw fa-file-excel-o"></i> Exportar
    </button>
</legend>
<div style="overflow-y: scroll; overflow-x: scroll; max-height: 650px">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d" id="table_confirmaciones">
        <tr class="tr_fija_top_0">
            <th class="padding_lateral_5 th_yura_green" style="width: 30px" rowspan="2">
                N°
            </th>
            <th class="padding_lateral_5 th_yura_green" rowspan="2">
                Planta
            </th>
            <th class="padding_lateral_5 th_yura_green" rowspan="2">
                Variedad
            </th>
            <th class="padding_lateral_5 th_yura_green" rowspan="2" style="width: 70px">
                Tallos Pedido
            </th>
            <th class="padding_lateral_5 th_yura_green" rowspan="2" style="width: 70px">
                Tallos Finca
            </th>
            <th class="text-center th_yura_green" colspan="{{ count($fincas) }}">
                Confirmaciones
            </th>
            <th class="padding_lateral_5 th_yura_green" rowspan="2" style="width: 70px">
                Confirmados
            </th>
            <th class="padding_lateral_5 th_yura_green" rowspan="2" style="width: 70px">
                Diferencia
            </th>
        </tr>
        <tr class="tr_fija_top_1">
            @php
                $total_pedido = 0;
                $total_margen = 0;
                $total_confirmados = 0;
                $total_diferencia = 0;
                $total_fincas = [];
            @endphp
            @foreach ($fincas as $f)
                <th class="padding_lateral_5 bg-yura_dark" style="width: 90px">
                    {{ $f->nombre }}
                </th>
                @php
                    $total_fincas[] = 0;
                @endphp
            @endforeach
        </tr>
        @foreach ($listado as $pos => $item)
            <tr style="background-color: {{ $pos % 2 == 0 ? '#dddddd' : '' }}"
                class="{{ $item->tipo_variedad == 'CONFIRMACION' ? 'error' : '' }}"
                title="{{ $item->tipo_variedad == 'CONFIRMACION' ? 'Esta variedad ya no se encuentra en el pedido' : '' }}">
                <th class="padding_lateral_5" style="border-color: #9d9d9d">
                    #{{ $pos + 1 }}
                </th>
                <th class="padding_lateral_5" style="border-color: #9d9d9d">
                    {{ $item->pta_nombre }}
                </th>
                <th class="padding_lateral_5" style="border-color: #9d9d9d">
                    {{ $item->var_nombre }}
                </th>
                <th class="padding_lateral_5" style="border-color: #9d9d9d">
                    {{ $item->tallos }}
                </th>
                <th class="padding_lateral_5" style="border-color: #9d9d9d">
                    {{ $item->tallos_margen }}
                </th>
                @php
                    $confirmados = 0;
                @endphp
                @foreach ($fincas as $pos_f => $f)
                    @php
                        $valor = 0;
                        foreach ($item->confirmaciones as $conf) {
                            if ($conf->id_finca_proveedor == $f->id_finca_proveedor) {
                                $valor += $conf->cantidad;
                            }
                        }
                        $confirmados += $valor;
                        $total_fincas[$pos_f] += $valor;
                    @endphp
                    <th class="padding_lateral_5" style="border-color: #9d9d9d">
                        {{ $valor }}
                    </th>
                @endforeach
                <th class="padding_lateral_5" style="border-color: #9d9d9d">
                    {{ $confirmados }}
                </th>
                @php
                    $diferencia = $confirmados - $item->tallos_margen;
                @endphp
                <th class="text-center" style="border-color: #9d9d9d">
                    @if ($item->tipo_variedad == 'CONFIRMACION')
                        +{{ $confirmados }}
                    @else
                        <input type="text" readonly
                            style="width: 100%; background-color: {{ $pos % 2 == 0 ? '#dddddd' : '' }}"
                            class="padding_lateral_5 {{ $diferencia < 0 ? 'input_diferencia' : '' }}"
                            data-id_variedad="{{ $item->id_variedad }}"
                            value="{{ $diferencia > 0 ? '+' . $diferencia : $diferencia }}">
                    @endif
                </th>
            </tr>
            @php
                $total_pedido += $item->tallos;
                $total_confirmados += $confirmados;
                $total_margen += $item->tallos_margen;
                $total_diferencia += $item->tipo_variedad != 'CONFIRMACION' ? $diferencia : 0;
            @endphp
        @endforeach
        <tr>
            <th class="padding_lateral_5 th_yura_green" colspan="3" rowspan="2">
                TOTALES
            </th>
            <th class="padding_lateral_5 th_yura_green" rowspan="2">
                {{ $total_pedido }}
            </th>
            <th class="padding_lateral_5 th_yura_green" rowspan="2">
                {{ $total_margen }}
            </th>
            @foreach ($total_fincas as $val)
                <th class="padding_lateral_5 bg-yura_dark">
                    {{ $val }}
                </th>
            @endforeach
            <th class="padding_lateral_5 th_yura_green" rowspan="2">
                {{ $total_confirmados }}
            </th>
            <th class="padding_lateral_5 th_yura_green">
                {{ $total_diferencia > 0 ? '+' . $total_diferencia : $total_diferencia }}
            </th>
        </tr>
        <tr>
            @foreach ($total_fincas as $pos_f => $val)
                <th class="text-center bg-yura_dark">
                    @if ($fincas[$pos_f]->confirmacion != '' && $fincas[$pos_f]->confirmacion->estado == 'P')
                        <button type="button" class="btn btn-xs btn-block btn-yura_default"
                            onclick="confirmar_finca('{{ $fincas[$pos_f]->id_finca_proveedor }}', '{{ $proyecto->id_proyecto }}')">
                            CONFIRMAR
                        </button>
                    @elseif($fincas[$pos_f]->confirmacion != '' && $fincas[$pos_f]->confirmacion->estado == 'C')
                        <small class="text-sm">
                            CONFIRMADO
                        </small>
                        <br>
                        <button type="button" class="btn btn-xs btn-yura_default" style="font-size: 1.3em">
                            <i class="fa fa-fw fa-file-excel-o"></i>
                            #{{ $fincas[$pos_f]->confirmacion->id_proyecto_confirmacion }}
                        </button>
                    @elseif($fincas[$pos_f]->confirmacion != '' && $fincas[$pos_f]->confirmacion->estado == 'R')
                        <small class="text-sm">
                            RECIBIDO
                        </small>
                        <br>
                        <button type="button" class="btn btn-xs btn-yura_default" style="font-size: 1.3em">
                            <i class="fa fa-fw fa-file-excel-o"></i>
                            #{{ $fincas[$pos_f]->confirmacion->id_proyecto_confirmacion }}
                        </button>
                    @endif
                </th>
            @endforeach
            <th class="text-center th_yura_green">
                @if ($total_diferencia < 0)
                    @if ($orden_compra != '')
                        <button type="button" class="btn btn-xs btn-block btn-yura_default"
                            onclick="modal_orden_compra('{{ $orden_compra->id_orden_compra }}')"
                            title="Ver la Orden de Compra" style="font-size: 1.2em; color: black">
                            <b>Orden #{{ $orden_compra->id_orden_compra }}</b>
                        </button>
                    @else
                        <button type="button" class="btn btn-xs btn-block btn-yura_default"
                            onclick="store_orden_compra('{{ $proyecto->id_proyecto }}')"
                            title="Generar Orden de Compra">
                            COMPRA
                        </button>
                    @endif
                @endif
            </th>
        </tr>
    </table>
</div>

<script>
    function confirmar_finca(finca, proy) {
        mensaje = {
            title: '<i class="fa fa-fw fa-save"></i> Confirmar Pedido de Flor',
            mensaje: '<div class="alert alert-warning text-center"><i class="fa fa-fw fa-exclamation-triangle"></i> ¿Está seguro de <b>CONFIRMAR</b> este pedido de flor?</div>',
        };
        modal_quest('modal_confirmar_finca', mensaje['mensaje'], mensaje['title'], true, false,
            '{{ isPC() ? '35%' : '' }}',
            function() {
                datos = {
                    _token: '{{ csrf_token() }}',
                    finca: finca,
                    proy: proy,
                };
                post_jquery_m('{{ url('proyectos/confirmar_finca') }}', datos, function() {
                    cerrar_modals();
                    modal_confirmaciones(proy);
                }, '', 10000);
            });
    }

    function store_orden_compra(id) {
        mensaje = {
            title: '<i class="fa fa-fw fa-save"></i> Crear Orden de Compra',
            mensaje: '<div class="alert alert-warning text-center"><i class="fa fa-fw fa-exclamation-triangle"></i> ¿Está seguro de <b>Generar la Orden Compra</b> para este pedido?</div>',
        };
        modal_quest('modal_store_orden_compra', mensaje['mensaje'], mensaje['title'], true, false,
            '{{ isPC() ? '35%' : '' }}',
            function() {
                data = [];
                input_diferencia = $('.input_diferencia');
                for (i = 0; i < input_diferencia.length; i++) {
                    variedad = input_diferencia[i].getAttribute('data-id_variedad');
                    tallos = parseInt(input_diferencia[i].value);
                    data.push({
                        variedad: variedad,
                        tallos: tallos,
                    })
                }
                datos = {
                    _token: '{{ csrf_token() }}',
                    id: id,
                    data: JSON.stringify(data),
                };
                post_jquery_m('{{ url('proyectos/store_orden_compra') }}', datos, function() {
                    cerrar_modals();
                    modal_confirmaciones(id);
                }, '', 10000);
            });
    }

    function modal_orden_compra(id) {
        datos = {
            id: id
        }
        get_jquery('{{ url('proyectos/modal_orden_compra') }}', datos, function(retorno) {
            modal_view('modal_modal_orden_compra', retorno,
                '<i class="fa fa-fw fa-plus"></i> Orden de Compra',
                true, false, '{{ isPC() ? '95%' : '' }}',
                function() {});
        });
    }
</script>
