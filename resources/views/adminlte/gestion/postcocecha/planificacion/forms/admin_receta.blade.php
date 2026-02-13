<legend class="text-center" style="font-size: 1em; margin-bottom: 5px">
    <div class="input-group">
        <span class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
            Distribucion de "<b>{{ $detalle_pedido->ramos }}</b>" ramos de "<b>{{ $detalle_pedido->longitud }}cm</b>" en
            la receta
            "<b>{{ $detalle_pedido->variedad->nombre }}</b>"
            para el pedido "<b>{{ $detalle_pedido->pedido->codigo }}</b>"
        </span>
        <select id="select_numero_receta" class="form-control">
            @foreach ($numeros_receta as $n)
                <option value="{{ $n }}">{{ $n }}</option>
            @endforeach
        </select>
        <span class="input-group-btn">
            <button type="button" class="btn btn-yura_primary" onclick="cargar_receta()">
                <i class="fa fa-fw fa-refresh"></i> Cargar esta receta
            </button>
        </span>
    </div>
</legend>
<input type="hidden" id="id_detalle_pedido_seleccionado" value="{{ $detalle_pedido->id_detalle_import_pedido }}">
<input type="hidden" id="ramos_pedido" value="{{ $detalle_pedido->ramos }}">
<input type="hidden" id="longitud_pedido" value="{{ $detalle_pedido->longitud }}">
<table style="width: 100%;">
    <tr>
        <td id="listado_productos" style="vertical-align: top; width: 45%">
            <table style="width: 100%">
                <tr>
                    <td class="text-center padding_lateral_5" style="border-color: #9d9d9d" id="td_cargar_longitudes">
                        <div class="input-group">
                            <span class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                                Plantas
                            </span>
                            <select id="receta_planta_filtro" class="form-control" style="width: 100%"
                                onchange="buscar_variedades()">
                                <option value="">Seleccione una flor</option>
                                @foreach ($plantas as $p)
                                    <option value="{{ $p->id_planta }}">{{ $p->nombre }}</option>
                                @endforeach
                            </select>
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-yura_dark" onclick="buscar_variedades()">
                                    <i class="fa fa-fw fa-search"></i>
                                </button>
                            </span>
                        </div>
                    </td>
                </tr>
            </table>

            <div id="div_listado_variedades" style="margin-top: 5px">
            </div>
        </td>

        <td>
            <button type="button" class="btn btn-block btn-yura_dark" onclick="agregar_variedades()">
                <i class="fa fa-fw fa-arrow-right"></i> Agregar
            </button>
            <button type="button" class="btn btn-block btn-yura_primary" style="margin-top: 0"
                onclick="store_distribucion_receta()">
                <i class="fa fa-fw fa-save"></i> Grabar
            </button>
        </td>

        <td id="listado_seleccionados" style="vertical-align: top; width: 45%">
            <div style="overflow-y: scroll; overflow-x: scroll; max-height: 500px;">
                <table class="table-striped table-bordered" style="width: 100%; border: 1px solid #9d9d9d"
                    id="table_variedades_seleccionados">
                    <tr class="tr_fija_top_0">
                        <th class="text-center th_yura_green" style="width: 25%">
                            FLOR
                        </th>
                        <th class="text-center th_yura_green" style="width: 25%">
                            VARIEDAD
                        </th>
                        <th class="text-center th_yura_green">
                            LONGITUD
                        </th>
                        <th class="text-center th_yura_green">
                            UNIDADES
                        </th>
                        <th class="text-center th_yura_green">
                        </th>
                    </tr>
                    @php
                        $pos = 0;
                    @endphp
                    <tbody id="tbody_variedades_seleccionados">
                        @foreach ($detalle_pedido->detalles_receta as $pos => $item)
                            <tr id="tr_variedad_seleccionado_{{ $pos + 1 }}">
                                <td class="text-center" style="border-color: #9d9d9d">
                                    {{ $item->variedad->planta->nombre }}
                                </td>
                                <td class="text-center" style="border-color: #9d9d9d">
                                    {{ $item->variedad->nombre }}
                                    <input type="hidden" class="cant_variedad_seleccionado"
                                        value="{{ $pos + 1 }}">
                                    <input type="hidden" id="id_variedad_seleccionado_{{ $pos + 1 }}"
                                        value="{{ $item->id_variedad }}">
                                </td>
                                <td class="text-center" style="border-color: #9d9d9d">
                                    <input type="number" class="text-center" style="width: 100%"
                                        id="longitud_variedad_seleccionado_{{ $pos + 1 }}"
                                        value="{{ $item->longitud }}">
                                </td>
                                <td class="text-center" style="border-color: #9d9d9d">
                                    <input type="number" class="text-center" style="width: 100%"
                                        id="cantidad_variedad_seleccionado_{{ $pos + 1 }}"
                                        value="{{ $item->unidades }}">
                                </td>
                                <td class="text-center" style="border-color: #9d9d9d">
                                    <button type="button" class="btn btn-xs btn-yura_danger" title="Quitar"
                                        onclick="quitar_variedad_seleccionado('{{ $pos + 1 }}')">
                                        <i class="fa fa-fw fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </td>
    </tr>
</table>

<script>
    cant_variedad_seleccionado = {{ $pos + 1 }};

    function buscar_variedades() {
        datos = {
            planta: $('#receta_planta_filtro').val(),
            ramos_pedido: $('#ramos_pedido').val(),
            longitud_pedido: $('#longitud_pedido').val(),
        };
        get_jquery('{{ url('importar_pedidos/buscar_variedades') }}', datos, function(retorno) {
            $('#div_listado_variedades').html(retorno);
        }, 'div_listado_variedades');
    }

    function agregar_variedades() {
        variedades_listados = $('.variedades_listados');
        for (i = 0; i < variedades_listados.length; i++) {
            id = variedades_listados[i].value;
            if ($('#cantidad_' + id).val() > 0) {
                cant_variedad_seleccionado++;
                nombre = $('#nombre_variedad_' + id).val();
                nombre_planta = $('#nombre_planta_' + id).val();
                longitud = $('#longitud_' + id).val();
                cantidad = $('#cantidad_' + id).val();
                $('#table_variedades_seleccionados').append('<tr id="tr_variedad_seleccionado_' +
                    cant_variedad_seleccionado + '">' +
                    '<td class="text-center" style="border-color: #9d9d9d">' +
                    nombre_planta +
                    '</td>' +
                    '<td class="text-center" style="border-color: #9d9d9d">' +
                    nombre +
                    '<input type="hidden" class="cant_variedad_seleccionado" value="' + cant_variedad_seleccionado +
                    '">' +
                    '<input type="hidden" id="id_variedad_seleccionado_' + cant_variedad_seleccionado +
                    '" value="' + id + '">' +
                    '</td>' +
                    '<td class="text-center" style="border-color: #9d9d9d">' +
                    '<input type="number" class="text-center" style="width: 100%" id="longitud_variedad_seleccionado_' +
                    cant_variedad_seleccionado + '" value="' + longitud + '">' +
                    '</td>' +
                    '<td class="text-center" style="border-color: #9d9d9d">' +
                    '<input type="number" class="text-center" style="width: 100%" id="cantidad_variedad_seleccionado_' +
                    cant_variedad_seleccionado + '" value="' + cantidad + '">' +
                    '</td>' +
                    '<td class="text-center" style="border-color: #9d9d9d">' +
                    '<button type="button" class="btn btn-xs btn-yura_danger" title="Quitar" onclick="quitar_variedad_seleccionado(' +
                    cant_variedad_seleccionado + ')">' +
                    '<i class="fa fa-fw fa-trash"></i>' +
                    '</button>' +
                    '</td>' +
                    '</tr>');
            }
        }
    }

    function quitar_variedad_seleccionado(cant_variedad_seleccionado) {
        $('#tr_variedad_seleccionado_' + cant_variedad_seleccionado).remove();
    }

    function store_distribucion_receta() {
        cant_variedad_seleccionado = $('.cant_variedad_seleccionado');
        data = [];
        id_detalle_pedido = $('#id_detalle_pedido_seleccionado').val();
        for (i = 0; i < cant_variedad_seleccionado.length; i++) {
            pos = cant_variedad_seleccionado[i].value;
            unidades = $('#cantidad_variedad_seleccionado_' + pos).val();
            longitud = $('#longitud_variedad_seleccionado_' + pos).val();
            id_item = $('#id_variedad_seleccionado_' + pos).val();
            if (unidades > 0 && longitud != '')
                data.push({
                    id_item: id_item,
                    longitud: longitud,
                    unidades: unidades,
                })
        }
        if (data.length > 0) {
            datos = {
                _token: '{{ csrf_token() }}',
                id_detalle_pedido: id_detalle_pedido,
                data: JSON.stringify(data)
            };
            post_jquery_m('{{ url('importar_pedidos/store_distribucion_receta') }}', datos, function(retorno) {
                cerrar_modals();
                pos = $('#pos_selected').val();
                fecha = $('#fecha_receta_armar').val();
                modal_planificacion(pos, '' + fecha + '');
            });
        }
    }

    function cargar_receta() {
        id_detalle_pedido = $('#id_detalle_pedido_seleccionado').val();
        datos = {
            id_detalle_pedido: id_detalle_pedido,
            numero_receta: $('#select_numero_receta').val(),
        };
        get_jquery('{{ url('importar_pedidos/cargar_receta') }}', datos, function(retorno) {
            $('#tbody_variedades_seleccionados').html(retorno);
        }, 'tbody_variedades_seleccionados');
    }
</script>
