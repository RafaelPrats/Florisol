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
                <button type="button" class="btn btn-xs btn-yura_default pull-right" onclick="add_new_planta()">
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
                <button type="button" class="btn btn-xs btn-yura_default pull-right" onclick="add_new_color()">
                    <i class="fa fa-fw fa-plus"></i>
                </button>
            </th>
            <th class="th_yura_green padding_lateral_5">
                SEASON
                <button type="button" class="btn btn-xs btn-yura_default pull-right" onclick="add_new_season()">
                    <i class="fa fa-fw fa-plus"></i>
                </button>
            </th>
            <th class="th_yura_green padding_lateral_5">
                CLIENTE
                <button type="button" class="btn btn-xs btn-yura_default pull-right" onclick="add_new_cliente()">
                    <i class="fa fa-fw fa-plus"></i>
                </button>
            </th>
            <th class="th_yura_green padding_lateral_5">
                CAJA
                <button type="button" class="btn btn-xs btn-yura_default pull-right" onclick="add_new_caja()">
                    <i class="fa fa-fw fa-plus"></i>
                </button>
            </th>
            <th class="th_yura_green padding_lateral_5" style="width: 70px">
                PACKING
            </th>
        </tr>
        <tr>
            <td class="text-center" style="border-color: #9d9d9d; vertical-align: top">
                <input type="date" style="width: 100%; height: 26px;" class="text-center" id="new_fecha"
                    name="new_fecha" value="{{ hoy() }}">
            </td>
            <td class="text-center" style="border-color: #9d9d9d; vertical-align: top">
                <form id="form_store_propuesta" enctype="multipart/form-data" class="text-center"
                    action="{{ url('propuestas/store_propuesta') }}" method="POST">
                    {!! csrf_field() !!}
                    <input type="file" style="width: 100%; height: 26px;" class="text-center bg-yura_dark"
                        id="new_imagen" name="new_imagen" placeholder="Codigo" accept="image/jpeg, image/png">
                    <img id="preview" class="img_new">
                </form>
            </td>
            <td class="text-center" style="border-color: #9d9d9d; vertical-align: top" id="celda_planta_new">
                <select id="new_planta_1" style="width: 100%; height: 26px;"
                    onchange="select_planta_global($(this).val(), 'new_variedad_1', 'new_variedad_1', '<option value=>Seleccione</option>')">
                    <option value="">Seleccione</option>
                    @foreach ($plantas as $pta)
                        <option value="{{ $pta->id_planta }}">
                            {{ $pta->nombre }}
                        </option>
                    @endforeach
                </select>
            </td>
            <td class="text-center" style="border-color: #9d9d9d; vertical-align: top" id="celda_variedad_new">
                <select id="new_variedad_1" style="width: 100%; height: 26px;">
                </select>
            </td>
            <td class="text-center" style="border-color: #9d9d9d; vertical-align: top" id="celda_unidades_new">
                <input type="number" id="new_unidades_1" style="width: 100%;" class="text-center" min="1">
            </td>
            <td class="text-center" style="border-color: #9d9d9d; vertical-align: top" id="celda_precios_new">
                <input type="number" id="new_precio_1" min="0" style="width: 100%" class="text-center">
            </td>
            <td class="text-center" style="border-color: #9d9d9d; vertical-align: top">
                <input type="number" id="new_mo" min="0" style="width: 100%" class="text-center">
            </td>
            <td class="text-center" style="border-color: #9d9d9d; vertical-align: top">
                <input type="number" id="new_longitud" min="0" style="width: 100%" class="text-center">
            </td>
            <td class="text-center" style="border-color: #9d9d9d; vertical-align: top" id="celda_color_new">
                <select id="new_color_select_1" style="width: 100%; height: 26px;"
                    ondblclick="cambiar_campo_color(1)" title="Doble Click para un NUEVO COLOR" data-activo="1">
                    <option value="">Seleccione</option>
                    @foreach ($colores as $c)
                        <option value="{{ $c }}">{{ $c }}</option>
                    @endforeach
                </select>
                <input type="text" id="new_color_text_1" style="width: 100%;" class="hidden"
                    placeholder="Nuevo Color" ondblclick="cambiar_campo_color(1)"
                    title="Doble Click para colores PREDETERMINADOS" data-activo="0">
            </td>
            <td class="text-center" style="border-color: #9d9d9d; vertical-align: top" id="celda_season_new">
                <select id="new_season_select_1" style="width: 100%; height: 26px;"
                    ondblclick="cambiar_campo_season(1)" title="Doble Click para una NUEVA SEASON" data-activo="1">
                    <option value="">Seleccione</option>
                    @foreach ($seasons as $c)
                        <option value="{{ $c }}">{{ $c }}</option>
                    @endforeach
                </select>
                <input type="text" id="new_season_text_1" style="width: 100%;" class="hidden"
                    placeholder="Nueva Season" ondblclick="cambiar_campo_season(1)"
                    title="Doble Click para seasons PREDETERMINADAS" data-activo="0">
            </td>
            <td class="text-center" style="border-color: #9d9d9d; vertical-align: top" id="celda_cliente_new">
                <select id="new_cliente_select_1" style="width: 100%; height: 26px;"
                    ondblclick="cambiar_campo_cliente(1)" title="Doble Click para un NUEVO CLIENTE" data-activo="1">
                    <option value="">Seleccione</option>
                    @foreach ($clientes as $c)
                        <option value="{{ $c }}">{{ $c }}</option>
                    @endforeach
                </select>
                <input type="text" id="new_cliente_text_1" style="width: 100%;" class="hidden"
                    placeholder="Nueva cliente" ondblclick="cambiar_campo_cliente(1)"
                    title="Doble Click para CLIENTES PREDETERMINADAS" data-activo="0">
            </td>
            <td class="text-center" style="border-color: #9d9d9d; vertical-align: top" id="celda_caja_new">
                <select id="new_caja_select_1" style="width: 100%; height: 26px;" ondblclick="cambiar_campo_caja(1)"
                    title="Doble Click para una NUEVA CAJA" data-activo="1">
                    <option value="">Seleccione</option>
                    @foreach ($cajas as $c)
                        <option value="{{ $c }}">{{ $c }}</option>
                    @endforeach
                </select>
                <input type="text" id="new_caja_text_1" style="width: 100%;" class="hidden"
                    placeholder="Nueva caja" ondblclick="cambiar_campo_caja(1)"
                    title="Doble Click para CAJAS PREDETERMINADAS" data-activo="0">
            </td>
            <td class="text-center" style="border-color: #9d9d9d; vertical-align: top">
                <input type="number" id="new_packing" min="0" style="width: 100%" class="text-center">
            </td>
        </tr>
    </table>
</div>
<div class="text-center">
    <button type="submit" class="btn btn-yura_primary" onclick="store_propuesta()">
        <i class="fa fa-fw fa-save"></i> Guardar NUEVA PROPUESTA
    </button>
</div>

<style>
    .img_new {
        width: 200px;
        display: none;
        margin-top: 0px;
        border: 1px solid #9d9d9d;
        border-radius: 16px;
    }
</style>

<script>
    num_var = 1;
    num_color = 1;
    num_season = 1;
    num_cliente = 1;
    num_caja = 1;

    document.getElementById('new_imagen').addEventListener('change', function(e) {
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

    function add_new_planta() {
        num_var++;
        html_planta = $('#new_planta_1').html();
        parametros = [
            "'new_variedad_" + num_var + "'",
            "'<option value = selected>Seleccione</option>'",
        ];
        $('#celda_planta_new').append('<select id="new_planta_' + num_var + '" style="width: 100%; height: 26px;"' +
            'onchange="select_planta_global($(this).val(), ' + parametros[0] + ', ' +
            parametros[0] + ', ' + parametros[1] + ')">' +
            html_planta +
            '</select>');

        html_variedad = $('#new_variedad_1').html();
        $('#celda_variedad_new').append('<select id="new_variedad_' + num_var +
            '" style="width: 100%; height: 26px;"></select>');
        $('#celda_unidades_new').append('<input type="number" id="new_unidades_' + num_var +
            '" style="width: 100%;" class="text-center" min="1">');
        $('#celda_precios_new').append('<input type="number" id="new_precio_' + num_var +
            '" style="width: 100%;" class="text-center" min="0">');
    }

    function add_new_color() {
        num_color++;
        html_select = $('#new_color_select_1').html();
        $('#celda_color_new').append(
            '<select id="new_color_select_' + num_color +
            '" style="width: 100%; height: 26px;" ondblclick="cambiar_campo_color(' + num_color + ')"' +
            'title="Doble Click para un NUEVO COLOR" data-activo="1">' +
            html_select +
            '</select>' +
            '<input type="text" id="new_color_text_' + num_color + '" style="width: 100%;" class="hidden" ' +
            'placeholder="Nuevo Color" ondblclick="cambiar_campo_color(' + num_color + ')" ' +
            'title="Doble Click para colores PREDETERMINADOS" data-activo="0">');
    }

    function add_new_season() {
        num_season++;
        html_select = $('#new_season_select_1').html();
        $('#celda_season_new').append(
            '<select id="new_season_select_' + num_season +
            '" style="width: 100%; height: 26px;" ondblclick="cambiar_campo_season(' + num_season + ')"' +
            'title="Doble Click para una NUEVA SEASON" data-activo="1">' +
            html_select +
            '</select>' +
            '<input type="text" id="new_season_text_' + num_season + '" style="width: 100%;" class="hidden" ' +
            'placeholder="Nueva Season" ondblclick="cambiar_campo_season(' + num_season + ')" ' +
            'title="Doble Click para seasons PREDETERMINADAS" data-activo="0">');
    }

    function add_new_cliente() {
        num_cliente++;
        html_select = $('#new_cliente_select_1').html();
        $('#celda_cliente_new').append(
            '<select id="new_cliente_select_' + num_cliente +
            '" style="width: 100%; height: 26px;" ondblclick="cambiar_campo_cliente(' + num_cliente + ')"' +
            'title="Doble Click para un NUEVO CLIENTE" data-activo="1">' +
            html_select +
            '</select>' +
            '<input type="text" id="new_cliente_text_' + num_cliente + '" style="width: 100%;" class="hidden" ' +
            'placeholder="Nueva cliente" ondblclick="cambiar_campo_cliente(' + num_cliente + ')" ' +
            'title="Doble Click para CLIENTES PREDETERMINADAS" data-activo="0">');
    }

    function add_new_caja() {
        num_caja++;
        html_select = $('#new_caja_select_1').html();
        $('#celda_caja_new').append(
            '<select id="new_caja_select_' + num_caja +
            '" style="width: 100%; height: 26px;" ondblclick="cambiar_campo_caja(' + num_caja + ')"' +
            'title="Doble Click para una NUEVA CAJA" data-activo="1">' +
            html_select +
            '</select>' +
            '<input type="text" id="new_caja_text_' + num_caja + '" style="width: 100%;" class="hidden" ' +
            'placeholder="Nueva caja" ondblclick="cambiar_campo_caja(' + num_caja + ')" ' +
            'title="Doble Click para CAJAS PREDETERMINADAS" data-activo="0">');
    }

    function cambiar_campo_color(i) {
        $('#new_color_select_' + i).toggleClass('hidden');
        $('#new_color_text_' + i).toggleClass('hidden');

        if ($('#new_color_select_' + i).data('activo') == 1)
            $('#new_color_select_' + i).data('activo', 0).attr('data-activo', 0);
        else
            $('#new_color_select_' + i).data('activo', 1).attr('data-activo', 1);

        if ($('#new_color_text_' + i).data('activo') == 1)
            $('#new_color_text_' + i).data('activo', 0).attr('data-activo', 0);
        else
            $('#new_color_text_' + i).data('activo', 1).attr('data-activo', 1);
    }

    function cambiar_campo_season(i) {
        $('#new_season_select_' + i).toggleClass('hidden');
        $('#new_season_text_' + i).toggleClass('hidden');

        if ($('#new_season_select_' + i).data('activo') == 1)
            $('#new_season_select_' + i).data('activo', 0).attr('data-activo', 0);
        else
            $('#new_season_select_' + i).data('activo', 1).attr('data-activo', 1);

        if ($('#new_season_text_' + i).data('activo') == 1)
            $('#new_season_text_' + i).data('activo', 0).attr('data-activo', 0);
        else
            $('#new_season_text_' + i).data('activo', 1).attr('data-activo', 1);
    }

    function cambiar_campo_cliente(i) {
        $('#new_cliente_select_' + i).toggleClass('hidden');
        $('#new_cliente_text_' + i).toggleClass('hidden');

        if ($('#new_cliente_select_' + i).data('activo') == 1)
            $('#new_cliente_select_' + i).data('activo', 0).attr('data-activo', 0);
        else
            $('#new_cliente_select_' + i).data('activo', 1).attr('data-activo', 1);

        if ($('#new_cliente_text_' + i).data('activo') == 1)
            $('#new_cliente_text_' + i).data('activo', 0).attr('data-activo', 0);
        else
            $('#new_cliente_text_' + i).data('activo', 1).attr('data-activo', 1);
    }

    function cambiar_campo_caja(i) {
        $('#new_caja_select_' + i).toggleClass('hidden');
        $('#new_caja_text_' + i).toggleClass('hidden');

        if ($('#new_caja_select_' + i).data('activo') == 1)
            $('#new_caja_select_' + i).data('activo', 0).attr('data-activo', 0);
        else
            $('#new_caja_select_' + i).data('activo', 1).attr('data-activo', 1);

        if ($('#new_caja_text_' + i).data('activo') == 1)
            $('#new_caja_text_' + i).data('activo', 0).attr('data-activo', 0);
        else
            $('#new_caja_text_' + i).data('activo', 1).attr('data-activo', 1);
    }

    function calcular_tallos() {
        tallos = 0;
        for (i = 1; i <= num_var; i++) {
            unidades = parseInt($('#new_unidades_' + i).val());
            tallos += unidades > 0 ? unidades : 0;
        }
        $('#new_total_tallos').val(tallos);
    }

    function store_propuesta() {
        if ($('#form_store_propuesta').valid()) {
            $.LoadingOverlay('show');
            formulario = $('#form_store_propuesta');

            data_variedades = [];
            data_colores = [];
            data_seasons = [];
            data_clientes = [];
            data_cajas = [];
            for (i = 1; i <= num_var; i++) {
                id_variedad = $('#new_variedad_' + i).val();
                unidades = parseInt($('#new_unidades_' + i).val());
                precio = parseFloat($('#new_precio_' + i).val());
                if (id_variedad != '' && unidades > 0) {
                    data_variedades.push({
                        id_variedad: id_variedad,
                        unidades: unidades,
                        precio: precio,
                    });
                }
            }
            for (i = 1; i <= num_color; i++) {
                if ($('#new_color_select_' + i).data('activo') == 1)
                    color = $('#new_color_select_' + i).val();
                else
                    color = $('#new_color_text_' + i).val();

                if (color != '') {
                    data_colores.push(color);
                }
            }
            for (i = 1; i <= num_season; i++) {
                if ($('#new_season_select_' + i).data('activo') == 1)
                    season = $('#new_season_select_' + i).val();
                else
                    season = $('#new_season_text_' + i).val();

                if (season != '') {
                    data_seasons.push(season);
                }
            }
            for (i = 1; i <= num_cliente; i++) {
                if ($('#new_cliente_select_' + i).data('activo') == 1)
                    cliente = $('#new_cliente_select_' + i).val();
                else
                    cliente = $('#new_cliente_text_' + i).val();

                if (cliente != '') {
                    data_clientes.push(cliente);
                }
            }
            for (i = 1; i <= num_caja; i++) {
                if ($('#new_caja_select_' + i).data('activo') == 1)
                    caja = $('#new_caja_select_' + i).val();
                else
                    caja = $('#new_caja_text_' + i).val();

                if (caja != '') {
                    data_cajas.push(caja);
                }
            }

            var formData = new FormData(formulario[0]);
            formData.append('mo', $('#new_mo').val());
            formData.append('longitud', $('#new_longitud').val());
            formData.append('fecha', $('#new_fecha').val());
            formData.append('packing', $('#new_packing').val());
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
                        add_propuesta();
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
