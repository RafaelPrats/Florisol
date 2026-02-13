<legend class="text-center" style="font-size: 1em; margin-bottom: 5px">
    <div class="input-group">
        <span class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
            Flores de: "<strong>{{ $variedad->nombre }}</strong>"
        </span>
        <select id="filtro_numero_receta" class="form-control" style="width: 100%"
            onchange="admin_receta('{{ $variedad->id_variedad }}', $(this).val())">
            @foreach ($numeros_receta as $n)
                <option value="{{ $n }}" {{ $n == $numero_receta ? 'selected' : '' }}>{{ $n }}
                </option>
            @endforeach
        </select>
        <span class="input-group-btn">
            <button type="button" class="btn btn-yura_dark"
                onclick="admin_receta('{{ $variedad->id_variedad }}', $('#filtro_numero_receta').val())">
                <i class="fa fa-fw fa-search"></i> Cargar Receta
            </button>
            <button type="button" class="btn btn-yura_primary"
                onclick="seleccionar_receta_defecto('{{ $variedad->id_variedad }}', $('#filtro_numero_receta').val())">
                <i class="fa fa-fw fa-check"></i> Seleccionar esta receta por defecto
            </button>
            <button type="button" class="btn btn-yura_danger"
                onclick="bloquear_distribucion('{{ $variedad->id_variedad }}', $('#filtro_numero_receta').val(), '{{ $bloqueado }}')">
                <i class="fa fa-fw fa-{{ $bloqueado == 1 ? 'unlock' : 'lock' }}"></i>
                {{ $bloqueado == 1 ? 'Desbloquear' : 'Bloquear' }} esta receta
            </button>
        </span>
    </div>
</legend>
<input type="hidden" id="id_variedad_seleccionado" value="{{ $variedad->id_variedad }}">
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
            <input type="text" style="width: 100%" id="new_nombre_receta"
                class="text-center form-control input-yura_default" placeholder="Nueva receta">
            <button type="button" class="btn btn-block btn-yura_dark" onclick="agregar_variedades()">
                <i class="fa fa-fw fa-arrow-right"></i> Agregar
            </button>
            <button type="button" class="btn btn-block btn-yura_primary" style="margin-top: 0"
                onclick="store_agregar_variedades()">
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
                            COLOR
                        </th>
                        <th class="text-center th_yura_green">
                            LONGITUD
                        </th>
                        <th class="text-center th_yura_green">
                            UNIDADES
                        </th>
                        <th class="text-center th_yura_green">
                            PRECIO
                        </th>
                        <th class="text-center th_yura_green">
                        </th>
                    </tr>
                    @php
                        $pos = 0;
                    @endphp
                    @foreach ($detalles as $pos => $item)
                        <tr id="tr_variedad_seleccionado_{{ $pos + 1 }}">
                            <td class="text-center" style="border-color: #9d9d9d">
                                {{ $item->item->planta->nombre }}
                            </td>
                            <td class="text-center" style="border-color: #9d9d9d">
                                {{ $item->item->nombre }}
                                <input type="hidden" class="cant_variedad_seleccionado" value="{{ $pos + 1 }}">
                                <input type="hidden" id="id_variedad_seleccionado_{{ $pos + 1 }}"
                                    value="{{ $item->id_item }}">
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
                                <input type="number" class="text-center" style="width: 100%"
                                    id="precio_variedad_seleccionado_{{ $pos + 1 }}"
                                    value="{{ $item->precio }}">
                            </td>
                            <td class="text-center" style="border-color: #9d9d9d">
                                <button type="button" class="btn btn-xs btn-yura_danger" title="Quitar"
                                    onclick="quitar_variedad_seleccionado('{{ $pos + 1 }}')">
                                    <i class="fa fa-fw fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
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
        };
        get_jquery('{{ url('plantas_variedades/buscar_variedades') }}', datos, function(retorno) {
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
                precio = $('#precio_' + id).val();
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
                    '<input type="number" class="text-center" style="width: 100%" id="precio_variedad_seleccionado_' +
                    cant_variedad_seleccionado + '" value="' + precio + '">' +
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

    function store_agregar_variedades() {
        cant_variedad_seleccionado = $('.cant_variedad_seleccionado');
        data = [];
        for (i = 0; i < cant_variedad_seleccionado.length; i++) {
            pos = cant_variedad_seleccionado[i].value;
            unidades = $('#cantidad_variedad_seleccionado_' + pos).val();
            longitud = $('#longitud_variedad_seleccionado_' + pos).val();
            precio = $('#precio_variedad_seleccionado_' + pos).val();
            id_item = $('#id_variedad_seleccionado_' + pos).val();
            id_var = $('#id_variedad_seleccionado').val();
            if (unidades > 0 && longitud != '')
                data.push({
                    id_item: id_item,
                    longitud: longitud,
                    unidades: unidades,
                    precio: precio,
                })
        }
        if (data.length > 0) {
            datos = {
                _token: '{{ csrf_token() }}',
                new_nombre_receta: $('#new_nombre_receta').val(),
                filtro_numero_receta: $('#filtro_numero_receta').val(),
                id_var: id_var,
                data: JSON.stringify(data)
            };

            if (datos['new_nombre_receta'] != '')
                mensaje = {
                    title: '<i class="fa fa-fw fa-exclamation-triangle"></i> Mensaje de Confirmacion',
                    mensaje: '<div class="alert alert-info text-center">¿Desea <b>CREAR</b> una nueva distribucion?</div>',
                };
            else
                mensaje = {
                    title: '<i class="fa fa-fw fa-exclamation-triangle"></i> Mensaje de Confirmacion',
                    mensaje: '<div class="alert alert-info text-center">¿Desea <b>MODIFICAR</b> la distribucion <b>' +
                        datos['filtro_numero_receta'] + '</b>?</div>',
                };
            modal_quest('modal_store_agregar_variedades', mensaje['mensaje'], mensaje['title'], true, false,
                '{{ isPC() ? '35%' : '' }}',
                function() {
                    post_jquery_m('{{ url('plantas_variedades/store_agregar_variedades') }}', datos, function(
                        retorno) {
                        cerrar_modals();
                        admin_receta(id_var);
                    });
                });
        }
    }

    function seleccionar_receta_defecto(variedad, numero_receta) {
        datos = {
            _token: '{{ csrf_token() }}',
            filtro_numero_receta: $('#filtro_numero_receta').val(),
            variedad: variedad,
        };

        mensaje = {
            title: '<i class="fa fa-fw fa-exclamation-triangle"></i> Mensaje de Confirmacion',
            mensaje: '<div class="alert alert-info text-center">¿Desea <b>SELECCIONAR</b> esta distribucion por defecto?</div>',
        };
        modal_quest('modal_seleccionar_receta_defecto', mensaje['mensaje'], mensaje['title'], true, false,
            '{{ isPC() ? '35%' : '' }}',
            function() {
                post_jquery_m('{{ url('plantas_variedades/seleccionar_receta_defecto') }}', datos, function(
                    retorno) {});
            });
    }

    function bloquear_distribucion(variedad, numero_receta, bloqueado) {
        datos = {
            _token: '{{ csrf_token() }}',
            filtro_numero_receta: $('#filtro_numero_receta').val(),
            variedad: variedad,
            bloqueado: bloqueado,
        };

        texto = bloqueado == 1 ? 'DESBLOQUEAR' : 'BLOQUEAR';
        mensaje = {
            title: '<i class="fa fa-fw fa-exclamation-triangle"></i> Mensaje de Confirmacion',
            mensaje: '<div class="alert alert-info text-center">¿Desea <b>' + texto +
                '</b> esta distribucion?</div>',
        };
        modal_quest('modal_bloquear_distribucion', mensaje['mensaje'], mensaje['title'], true, false,
            '{{ isPC() ? '35%' : '' }}',
            function() {
                post_jquery_m('{{ url('plantas_variedades/bloquear_distribucion') }}', datos, function() {
                    cerrar_modals();
                    admin_receta(variedad);
                });
            });
    }
</script>
