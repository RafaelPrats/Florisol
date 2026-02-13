<div style="overflow-x: scroll; overflow-y: scroll; max-height: 550px">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
        <tr>
            <th class="th_yura_green padding_lateral_5" style="width: 70px">
                FECHA
            </th>
            <th class="th_yura_green padding_lateral_5" style="width: 200px">
                IMAGEN
            </th>
            <th class="th_yura_green padding_lateral_5">
                PLANTA
                <button type="button" class="btn btn-xs btn-yura_default pull-right" onclick="add_edit_planta()">
                    <i class="fa fa-fw fa-plus"></i>
                </button>
            </th>
            <th class="th_yura_green padding_lateral_5">
                VARIEDAD
            </th>
            <th class="th_yura_green padding_lateral_5" style="width: 70px">
                UNIDADES
            </th>
            <th class="th_yura_green padding_lateral_5" style="width: 70px">
                PRECIO
            </th>
            <th class="th_yura_green padding_lateral_5" style="width: 70px">
                MO
            </th>
            <th class="th_yura_green padding_lateral_5" style="width: 70px">
                LONGITUD
            </th>
            <th class="th_yura_green padding_lateral_5">
                COLOR
                <button type="button" class="btn btn-xs btn-yura_default pull-right" onclick="add_edit_color()">
                    <i class="fa fa-fw fa-plus"></i>
                </button>
            </th>
            <th class="th_yura_green padding_lateral_5">
                SEASON
                <button type="button" class="btn btn-xs btn-yura_default pull-right" onclick="add_edit_season()">
                    <i class="fa fa-fw fa-plus"></i>
                </button>
            </th>
            <th class="th_yura_green padding_lateral_5">
                CLIENTE
                <button type="button" class="btn btn-xs btn-yura_default pull-right" onclick="add_edit_cliente()">
                    <i class="fa fa-fw fa-plus"></i>
                </button>
            </th>
            <th class="th_yura_green padding_lateral_5">
                CAJA
                <button type="button" class="btn btn-xs btn-yura_default pull-right" onclick="add_edit_caja()">
                    <i class="fa fa-fw fa-plus"></i>
                </button>
            </th>
            <th class="th_yura_green padding_lateral_5" style="width: 70px">
                PACKING
            </th>
        </tr>
        <tr>
            <td class="text-center" style="border-color: #9d9d9d; vertical-align: top">
                <input type="date" style="width: 100%; height: 26px;" class="text-center" id="edit_fecha"
                    name="edit_fecha" value="{{ $propuesta->fecha }}">
            </td>
            <td class="text-center" style="border-color: #9d9d9d; vertical-align: top">
                <form id="form_update_propuesta" enctype="multipart/form-data" class="text-center"
                    action="{{ url('propuestas/update_propuesta') }}" method="POST">
                    {!! csrf_field() !!}
                    <input type="file" style="width: 100%; height: 26px;" class="text-center bg-yura_dark"
                        id="edit_imagen" name="edit_imagen" placeholder="Codigo" accept="image/jpeg, image/png">
                    <img id="preview" onclick="abrirGaleria('{{ $propuesta->id_propuesta }}')"
                        class="img_new mouse-hand imagen_listado"
                        src="{{ asset('images/propuesta/' . $propuesta->imagen) }}">
                </form>
            </td>
            <td class="text-center" style="border-color: #9d9d9d; vertical-align: top" id="celda_planta_new">
                @foreach ($detalles as $pos => $det)
                    <select id="edit_planta_{{ $pos + 1 }}" style="width: 100%; height: 26px;"
                        onchange="select_planta_global($(this).val(), 'edit_variedad_{{ $pos + 1 }}', 'edit_variedad_{{ $pos + 1 }}', '<option value=>Seleccione</option>')">
                        <option value="">Seleccione</option>
                        @foreach ($plantas as $pta)
                            <option value="{{ $pta->id_planta }}"
                                {{ $pta->id_planta == $det->variedad->id_planta ? 'selected' : '' }}>
                                {{ $pta->nombre }}
                            </option>
                        @endforeach
                    </select>
                @endforeach
            </td>
            <td class="text-center" style="border-color: #9d9d9d; vertical-align: top" id="celda_variedad_new">
                @foreach ($detalles as $pos => $det)
                    @php
                        $planta = $det->variedad->planta;
                    @endphp
                    <select id="edit_variedad_{{ $pos + 1 }}" style="width: 100%; height: 26px;">
                        <option value="">Seleccione</option>
                        @foreach ($planta->variedades as $var)
                            <option value="{{ $var->id_variedad }}"
                                {{ $var->id_variedad == $det->id_variedad ? 'selected' : '' }}>
                                {{ $var->nombre }}
                            </option>
                        @endforeach
                    </select>
                @endforeach
            </td>
            <td class="text-center" style="border-color: #9d9d9d; vertical-align: top" id="celda_unidades_new">
                @foreach ($detalles as $pos => $det)
                    <input type="number" id="edit_unidades_{{ $pos + 1 }}" style="width: 100%;"
                        class="text-center" min="1" value="{{ $det->unidades }}">
                @endforeach
            </td>
            <td class="text-center" style="border-color: #9d9d9d; vertical-align: top" id="celda_precios_new">
                @foreach ($detalles as $pos => $det)
                    <input type="number" id="edit_precio_{{ $pos + 1 }}" style="width: 100%;" class="text-center"
                        min="0" value="{{ $det->precio }}">
                @endforeach
            </td>
            <td class="text-center" style="border-color: #9d9d9d; vertical-align: top">
                <input type="number" id="edit_mo" min="0" style="width: 100%" class="text-center"
                    value="{{ $propuesta->costo_mano_obra }}">
            </td>
            <td class="text-center" style="border-color: #9d9d9d; vertical-align: top">
                <input type="number" id="edit_longitud" min="0" style="width: 100%" class="text-center"
                    value="{{ $propuesta->longitud }}">
            </td>
            <td class="text-center" style="border-color: #9d9d9d; vertical-align: top" id="celda_color_new">
                @foreach ($propuesta->colores as $pos => $col)
                    <select id="edit_color_select_{{ $pos + 1 }}" style="width: 100%; height: 26px;"
                        ondblclick="cambiar_campo_color('{{ $pos + 1 }}')"
                        title="Doble Click para un NUEVO COLOR" data-activo="1">
                        <option value="">Seleccione</option>
                        @foreach ($colores as $c)
                            <option value="{{ $c }}" {{ $c == $col->nombre ? 'selected' : '' }}>
                                {{ $c }}
                            </option>
                        @endforeach
                    </select>
                    <input type="text" id="edit_color_text_{{ $pos + 1 }}" style="width: 100%;"
                        class="hidden" placeholder="Nuevo Color"
                        ondblclick="cambiar_campo_color('{{ $pos + 1 }}')"
                        title="Doble Click para colores PREDETERMINADOS" data-activo="0">
                @endforeach
            </td>
            <td class="text-center" style="border-color: #9d9d9d; vertical-align: top" id="celda_season_new">
                @foreach ($propuesta->seasons as $pos => $sea)
                    <select id="edit_season_select_{{ $pos + 1 }}" style="width: 100%; height: 26px;"
                        ondblclick="cambiar_campo_season('{{ $pos + 1 }}')"
                        title="Doble Click para una NUEVA SEASON" data-activo="1">
                        <option value="">Seleccione</option>
                        @foreach ($seasons as $c)
                            <option value="{{ $c }}" {{ $c == $sea->nombre ? 'selected' : '' }}>
                                {{ $c }}
                            </option>
                        @endforeach
                    </select>
                    <input type="text" id="edit_season_text_{{ $pos + 1 }}" style="width: 100%;"
                        class="hidden" placeholder="Nueva Season"
                        ondblclick="cambiar_campo_season('{{ $pos + 1 }}')"
                        title="Doble Click para seasons PREDETERMINADAS" data-activo="0">
                @endforeach
            </td>
            <td class="text-center" style="border-color: #9d9d9d; vertical-align: top" id="celda_cliente_new">
                @foreach ($propuesta->clientes as $pos => $sea)
                    <select id="edit_cliente_select_{{ $pos + 1 }}" style="width: 100%; height: 26px;"
                        ondblclick="cambiar_campo_cliente('{{ $pos + 1 }}')"
                        title="Doble Click para un NUEVO CLIENTE" data-activo="1">
                        <option value="">Seleccione</option>
                        @foreach ($clientes as $c)
                            <option value="{{ $c }}" {{ $c == $sea->nombre ? 'selected' : '' }}>
                                {{ $c }}
                            </option>
                        @endforeach
                    </select>
                    <input type="text" id="edit_cliente_text_{{ $pos + 1 }}" style="width: 100%;"
                        class="hidden" placeholder="Nueva cliente"
                        ondblclick="cambiar_campo_cliente('{{ $pos + 1 }}')"
                        title="Doble Click para clientes PREDETERMINADAS" data-activo="0">
                @endforeach
            </td>
            <td class="text-center" style="border-color: #9d9d9d; vertical-align: top" id="celda_caja_new">
                @foreach ($propuesta->cajas as $pos => $sea)
                    <select id="edit_caja_select_{{ $pos + 1 }}" style="width: 100%; height: 26px;"
                        ondblclick="cambiar_campo_caja('{{ $pos + 1 }}')"
                        title="Doble Click para una NUEVA CAJA" data-activo="1">
                        <option value="">Seleccione</option>
                        @foreach ($cajas as $c)
                            <option value="{{ $c }}" {{ $c == $sea->nombre ? 'selected' : '' }}>
                                {{ $c }}
                            </option>
                        @endforeach
                    </select>
                    <input type="text" id="edit_caja_text_{{ $pos + 1 }}" style="width: 100%;"
                        class="hidden" placeholder="Nueva caja"
                        ondblclick="cambiar_campo_caja('{{ $pos + 1 }}')"
                        title="Doble Click para cajas PREDETERMINADAS" data-activo="0">
                @endforeach
            </td>
            <td class="text-center" style="border-color: #9d9d9d; vertical-align: top">
                <input type="number" id="edit_packing" min="0" style="width: 100%" class="text-center"
                    value="{{ $propuesta->packing }}">
            </td>
        </tr>
    </table>
</div>
<div class="text-center">
    <button type="submit" class="btn btn-yura_primary" onclick="update_propuesta()">
        <i class="fa fa-fw fa-save"></i> Guardar CAMBIOS
    </button>
</div>
<input type="hidden" id="propuesta_selected" value="{{ $propuesta->id_propuesta }}">

<style>
    .img_new {
        width: 200px;
        margin-top: 0px;
        border: 1px solid #9d9d9d;
        border-radius: 16px;
    }
</style>

<script>
    num_var = {{ count($detalles) }};
    num_color = {{ count($propuesta->colores) }};
    num_season = {{ count($propuesta->seasons) }};
    num_cliente = {{ count($propuesta->clientes) }};
    num_caja = {{ count($propuesta->cajas) }};

    document.getElementById('edit_imagen').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;

        // Validar tipo MIME
        const tiposPermitidos = ['image/jpeg', 'image/png'];
        if (!tiposPermitidos.includes(file.type)) {
            alert('Solo se permiten imágenes JPG o JPEG');
            e.target.value = '';
            document.getElementById('preview').style.display = 'none';
            return;
        }

        const reader = new FileReader();
        reader.onload = function(ev) {
            const img = document.getElementById('preview');
            img.src = ev.target.result;
            img.style.display = 'block';
        };
        reader.readAsDataURL(file);
    });

    function add_edit_planta() {
        num_var++;
        html_planta = $('#edit_planta_1').html();
        parametros = [
            "'edit_variedad_" + num_var + "'",
            "'<option value = selected>Seleccione</option>'",
        ];
        $('#celda_planta_new').append('<select id="edit_planta_' + num_var + '" style="width: 100%; height: 26px;"' +
            'onchange="select_planta_global($(this).val(), ' + parametros[0] + ', ' +
            parametros[0] + ', ' + parametros[1] + ')">' +
            html_planta +
            '</select>');

        html_variedad = $('#edit_variedad_1').html();
        $('#celda_variedad_new').append('<select id="edit_variedad_' + num_var +
            '" style="width: 100%; height: 26px;"></select>');
        $('#celda_unidades_new').append('<input type="number" id="edit_unidades_' + num_var +
            '" style="width: 100%;" class="text-center" min="1">');
        $('#celda_precios_new').append('<input type="number" id="edit_precio_' + num_var +
            '" style="width: 100%;" class="text-center" min="0">');
    }

    function add_edit_color() {
        num_color++;
        html_select = $('#edit_color_select_1').html();
        $('#celda_color_new').append(
            '<select id="edit_color_select_' + num_color +
            '" style="width: 100%; height: 26px;" ondblclick="cambiar_campo_color(' + num_color + ')"' +
            'title="Doble Click para un NUEVO COLOR" data-activo="1">' +
            html_select +
            '</select>' +
            '<input type="text" id="edit_color_text_' + num_color + '" style="width: 100%;" class="hidden" ' +
            'placeholder="Nuevo Color" ondblclick="cambiar_campo_color(' + num_color + ')" ' +
            'title="Doble Click para colores PREDETERMINADOS" data-activo="0">');
    }

    function add_edit_season() {
        num_season++;
        html_select = $('#edit_season_select_1').html();
        $('#celda_season_new').append(
            '<select id="edit_season_select_' + num_season +
            '" style="width: 100%; height: 26px;" ondblclick="cambiar_campo_season(' + num_season + ')"' +
            'title="Doble Click para una NUEVA SEASON" data-activo="1">' +
            html_select +
            '</select>' +
            '<input type="text" id="edit_season_text_' + num_season + '" style="width: 100%;" class="hidden" ' +
            'placeholder="Nueva Season" ondblclick="cambiar_campo_season(' + num_season + ')" ' +
            'title="Doble Click para seasons PREDETERMINADAS" data-activo="0">');
    }

    function add_edit_cliente() {
        num_cliente++;
        html_select = $('#edit_cliente_select_1').html();
        $('#celda_cliente_new').append(
            '<select id="edit_cliente_select_' + num_cliente +
            '" style="width: 100%; height: 26px;" ondblclick="cambiar_campo_cliente(' + num_cliente + ')"' +
            'title="Doble Click para un NUEVO CLIENTE" data-activo="1">' +
            html_select +
            '</select>' +
            '<input type="text" id="edit_cliente_text_' + num_cliente + '" style="width: 100%;" class="hidden" ' +
            'placeholder="Nueva cliente" ondblclick="cambiar_campo_cliente(' + num_cliente + ')" ' +
            'title="Doble Click para clientes PREDETERMINADAS" data-activo="0">');
    }

    function add_edit_caja() {
        num_caja++;
        html_select = $('#edit_caja_select_1').html();
        $('#celda_caja_new').append(
            '<select id="edit_caja_select_' + num_caja +
            '" style="width: 100%; height: 26px;" ondblclick="cambiar_campo_caja(' + num_caja + ')"' +
            'title="Doble Click para una NUEVA CAJA" data-activo="1">' +
            html_select +
            '</select>' +
            '<input type="text" id="edit_caja_text_' + num_caja + '" style="width: 100%;" class="hidden" ' +
            'placeholder="Nueva caja" ondblclick="cambiar_campo_caja(' + num_caja + ')" ' +
            'title="Doble Click para cajas PREDETERMINADAS" data-activo="0">');
    }

    function cambiar_campo_color(i) {
        $('#edit_color_select_' + i).toggleClass('hidden');
        $('#edit_color_text_' + i).toggleClass('hidden');

        if ($('#edit_color_select_' + i).data('activo') == 1)
            $('#edit_color_select_' + i).data('activo', 0).attr('data-activo', 0);
        else
            $('#edit_color_select_' + i).data('activo', 1).attr('data-activo', 1);

        if ($('#edit_color_text_' + i).data('activo') == 1)
            $('#edit_color_text_' + i).data('activo', 0).attr('data-activo', 0);
        else
            $('#edit_color_text_' + i).data('activo', 1).attr('data-activo', 1);
    }

    function cambiar_campo_season(i) {
        $('#edit_season_select_' + i).toggleClass('hidden');
        $('#edit_season_text_' + i).toggleClass('hidden');

        if ($('#edit_season_select_' + i).data('activo') == 1)
            $('#edit_season_select_' + i).data('activo', 0).attr('data-activo', 0);
        else
            $('#edit_season_select_' + i).data('activo', 1).attr('data-activo', 1);

        if ($('#edit_season_text_' + i).data('activo') == 1)
            $('#edit_season_text_' + i).data('activo', 0).attr('data-activo', 0);
        else
            $('#edit_season_text_' + i).data('activo', 1).attr('data-activo', 1);
    }

    function cambiar_campo_cliente(i) {
        $('#edit_cliente_select_' + i).toggleClass('hidden');
        $('#edit_cliente_text_' + i).toggleClass('hidden');

        if ($('#edit_cliente_select_' + i).data('activo') == 1)
            $('#edit_cliente_select_' + i).data('activo', 0).attr('data-activo', 0);
        else
            $('#edit_cliente_select_' + i).data('activo', 1).attr('data-activo', 1);

        if ($('#edit_cliente_text_' + i).data('activo') == 1)
            $('#edit_cliente_text_' + i).data('activo', 0).attr('data-activo', 0);
        else
            $('#edit_cliente_text_' + i).data('activo', 1).attr('data-activo', 1);
    }

    function cambiar_campo_caja(i) {
        $('#edit_caja_select_' + i).toggleClass('hidden');
        $('#edit_caja_text_' + i).toggleClass('hidden');

        if ($('#edit_caja_select_' + i).data('activo') == 1)
            $('#edit_caja_select_' + i).data('activo', 0).attr('data-activo', 0);
        else
            $('#edit_caja_select_' + i).data('activo', 1).attr('data-activo', 1);

        if ($('#edit_caja_text_' + i).data('activo') == 1)
            $('#edit_caja_text_' + i).data('activo', 0).attr('data-activo', 0);
        else
            $('#edit_caja_text_' + i).data('activo', 1).attr('data-activo', 1);
    }

    function calcular_tallos() {
        tallos = 0;
        for (i = 1; i <= num_var; i++) {
            unidades = parseInt($('#edit_unidades_' + i).val());
            tallos += unidades > 0 ? unidades : 0;
        }
        $('#edit_total_tallos').val(tallos);
    }

    function update_propuesta() {
        if ($('#form_update_propuesta').valid()) {
            $.LoadingOverlay('show');
            formulario = $('#form_update_propuesta');

            data_variedades = [];
            data_colores = [];
            data_seasons = [];
            data_clientes = [];
            data_cajas = [];
            for (i = 1; i <= num_var; i++) {
                id_variedad = $('#edit_variedad_' + i).val();
                unidades = parseInt($('#edit_unidades_' + i).val());
                precio = parseFloat($('#edit_precio_' + i).val());
                if (id_variedad != '' && unidades > 0) {
                    data_variedades.push({
                        id_variedad: id_variedad,
                        unidades: unidades,
                        precio: precio,
                    });
                }
            }
            for (i = 1; i <= num_color; i++) {
                if ($('#edit_color_select_' + i).data('activo') == 1)
                    color = $('#edit_color_select_' + i).val();
                else
                    color = $('#edit_color_text_' + i).val();

                if (color != '') {
                    data_colores.push(color);
                }
            }
            for (i = 1; i <= num_season; i++) {
                if ($('#edit_season_select_' + i).data('activo') == 1)
                    season = $('#edit_season_select_' + i).val();
                else
                    season = $('#edit_season_text_' + i).val();

                if (season != '') {
                    data_seasons.push(season);
                }
            }
            for (i = 1; i <= num_cliente; i++) {
                if ($('#edit_cliente_select_' + i).data('activo') == 1)
                    cliente = $('#edit_cliente_select_' + i).val();
                else
                    cliente = $('#edit_cliente_text_' + i).val();

                if (cliente != '') {
                    data_clientes.push(cliente);
                }
            }
            for (i = 1; i <= num_caja; i++) {
                if ($('#edit_caja_select_' + i).data('activo') == 1)
                    caja = $('#edit_caja_select_' + i).val();
                else
                    caja = $('#edit_caja_text_' + i).val();

                if (caja != '') {
                    data_cajas.push(caja);
                }
            }

            var formData = new FormData(formulario[0]);
            formData.append('id_propuesta', $('#propuesta_selected').val());
            formData.append('mo', $('#edit_mo').val());
            formData.append('longitud', $('#edit_longitud').val());
            formData.append('fecha', $('#edit_fecha').val());
            formData.append('packing', $('#edit_packing').val());
            formData.append('data_variedades', JSON.stringify(data_variedades));
            formData.append('data_colores', JSON.stringify(data_colores));
            formData.append('data_seasons', JSON.stringify(data_seasons));
            formData.append('data_clientes', JSON.stringify(data_clientes));
            formData.append('data_cajas', JSON.stringify(data_cajas));

            //hacemos la petición ajax
            $.ajax({
                url: formulario.attr('action'),
                type: 'POST',
                data: formData,
                dataType: 'json',
                //necesario para subir archivos via ajax
                cache: false,
                contentType: false,
                processData: false,

                success: function(retorno2) {
                    if (retorno2.success) {
                        mini_alerta('success', retorno2.mensaje, 5000);
                        cerrar_modals();
                        listar_reporte();
                    } else {
                        alerta(retorno2.mensaje);
                    }
                    $.LoadingOverlay('hide');
                },
                //si ha ocurrido un error
                error: function(retorno2) {
                    console.log(retorno2);
                    alerta(retorno2.responseText);
                    alert('Hubo un problema en la envío de la información');
                    $.LoadingOverlay('hide');
                }
            });
        }
    }
</script>
