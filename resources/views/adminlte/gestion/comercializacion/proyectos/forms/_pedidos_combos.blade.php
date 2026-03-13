<div style="overflow-y: scroll; max-height: 550px">
    <table class="table-bordered" style="border: 1px solid #9d9d9d; width: 100%; font-size: 0.9em">
        <tbody>
            <tr class="tr_fija_top_0">
                <th class="text-center th_yura_green" style="min-width: 40px">
                    PIEZAS
                </th>
                <th class="text-center th_yura_green" style="min-width: 160px">
                    BQT
                </th>
                <th class="text-center th_yura_green" style="min-width: 60px">
                    TIPO CAJA
                </th>
                <th class="text-center th_yura_green" style="min-width: 60px">
                    R. X CAJA
                </th>
                <th class="text-center th_yura_green" style="min-width: 60px">
                    TOTAL RAMOS
                </th>
                <th class="text-center th_yura_green" style="min-width: 60px">
                    T. X RAMOS
                </th>
                <th class="text-center th_yura_green" style="min-width: 60px">
                    TOTAL TALLOS
                </th>
                <th class="text-center th_yura_green" style="min-width: 60px">
                    LONGITUD
                </th>
                <th class="text-center th_yura_green" style="min-width: 60px">
                    PRECIO
                    <input type="number" style="width: 100%; color: black" class="text-center" min="0"
                        id="form_combos_input_all_precio_esp" onchange="$('.form_combos_precio').val($(this).val())"
                        onkeyup="$('.form_combos_precio').val($(this).val())">
                </th>
                @foreach ($datos_exportacion as $dat_exp)
                    <th class="text-center bg-yura_dark" style="min-width: 70px">
                        {{ $dat_exp->nombre }}
                        <input type="text" style="width: 100%; color: black" class="text-center" min="0"
                            id="form_combos_input_all_marcacion_{{ $dat_exp->id_dato_exportacion }}"
                            onchange="$('.form_combos_marcacion_{{ $dat_exp->id_dato_exportacion }}').val($(this).val())"
                            onkeyup="$('.form_combos_marcacion_{{ $dat_exp->id_dato_exportacion }}').val($(this).val())">
                    </th>
                @endforeach
                <th class="text-center th_yura_green col_fija_right_0" style="min-width: 40px">
                </th>
            </tr>
        </tbody>
        <tbody id="table_form_combos">
            <tr>
                <td class="text-center" style="border-color: #9d9d9d" id="td_piezas_combos">
                    <input type="number" style="width: 100%" class="text-center" id="form_combos_piezas"
                        onchange="calcular_totales_form_combos()" onkeyup="calcular_totales_form_combos()">
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <select style="width: 100%; height: 24px;" id="form_combos_receta_1"
                        onchange="form_combos_seleccionar_receta(1)">
                        <option value="">Seleccione...</option>
                        @foreach ($recetas as $rec)
                            <option value="{{ $rec->id_variedad }}">
                                {{ $rec->nombre }}
                            </option>
                        @endforeach
                    </select>
                    <input type="hidden" class="form_num_detalle_combos" value="1">
                </td>
                <td class="text-center" style="border-color: #9d9d9d" id="td_cajas_combos">
                    <select style="width: 100%; height: 24px;" id="form_combos_tipo_caja">
                        <option value="FB">FB</option>
                        <option value="HB">HB</option>
                        <option value="QB">QB</option>
                        <option value="EB">EB</option>
                    </select>
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <input type="number" style="width: 100%" class="text-center" id="form_combos_ramos_x_caja_1"
                        onchange="calcular_totales_form_combos()" onkeyup="calcular_totales_form_combos()"
                        value="1">
                </td>
                <td class="text-center" style="border-color: #9d9d9d" id="td_total_ramos_combos">
                    <input type="number" style="width: 100%" class="text-center" id="form_combos_total_ramos"
                        readonly="" disabled="">
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <input type="number" style="width: 100%" class="text-center" id="form_combos_tallos_x_ramos_1"
                        onchange="calcular_totales_form_combos()" onkeyup="calcular_totales_form_combos()">
                </td>
                <td class="text-center" style="border-color: #9d9d9d" id="td_total_tallos_combos">
                    <input type="number" style="width: 100%" class="text-center" id="form_combos_total_tallos"
                        readonly="" disabled="">
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <input type="number" style="width: 100%" class="text-center" id="form_combos_longitud_1">
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <input type="number" style="width: 100%" class="text-center form_combos_precio"
                        id="form_combos_precio_1">
                </td>
                @foreach ($datos_exportacion as $dat_exp)
                    <td class="text-center" style="border-color: #9d9d9d"
                        id="td_form_combos_marcacion_{{ $dat_exp->id_dato_exportacion }}">
                        <input type="text" style="width: 100%; color: black"
                            class="text-center form_combos_marcacion_{{ $dat_exp->id_dato_exportacion }}"
                            id="form_combos_marcacion_{{ $dat_exp->id_dato_exportacion }}">
                    </td>
                @endforeach
                <td class="text-center col_fija_right_0 bg-yura_dark" style="border-color: #9d9d9d">
                    <button type="button" class="btn btn-xs btn-yura_default" onclick="form_add_detalle_combos()">
                        <i class="fa fa-fw fa-plus"></i>
                    </button>
                </td>
            </tr>
        </tbody>
        <tbody>
            <tr class="tr_fija_bottom_0">
                <th class="text-center th_yura_green" colspan="15">
                    <button type="button" class="btn btn-yura_danger" onclick="agregar_combos_pedido()">
                        <i class="fa fa-fw fa-plus"></i> Agregar Productos al Pedido
                    </button>
                </th>
            </tr>
        </tbody>
    </table>
</div>

<style>
    .col_fija_right_0 {
        position: sticky;
        right: 0;
        z-index: 9;
    }
</style>

<script>
    form_combos_cant_detalles = 1;

    function form_combos_seleccionar_receta(num_combo) {
        datos = {
            _token: '{{ csrf_token() }}',
            receta: $('#form_combos_receta_' + num_combo).val(),
        }
        if (datos['receta'] != '') {
            $.LoadingOverlay('show');
            $.post('{{ url('proyectos/form_combos_seleccionar_receta') }}', datos, function(retorno) {
                $('#form_combos_longitud_' + num_combo).val(retorno.longitud);
                $('#form_combos_tallos_x_ramos_' + num_combo).val(retorno.tallos_x_ramos);
                calcular_totales_form_combos();
            }, 'json').fail(function(retorno) {
                console.log(retorno);
                alerta_errores(retorno.responseText);
            }).always(function() {
                $.LoadingOverlay('hide');
            })
        } else {
            $('#form_combos_longitud_' + num_combo).val('');
            $('#form_combos_tallos_x_ramos_' + num_combo).val('');
        }
    }

    function form_add_detalle_combos() {
        form_combos_cant_detalles++;
        select_receta_combos = $('#form_combos_receta_1').html();

        $('#table_form_combos').append('<tr id="tr_form_combos_' + form_combos_cant_detalles + '">' +
            '<td class="text-center" style="border-color: #9d9d9d" id="td_piezas_combos">' +
            '<select style="width: 100%; height: 24px;" id="form_combos_receta_' + form_combos_cant_detalles +
            '" onchange="form_combos_seleccionar_receta(' + form_combos_cant_detalles + ')">' +
            select_receta_combos +
            '</select>' +
            '<input type="hidden" class="form_num_detalle_combos" value="' + form_combos_cant_detalles + '">' +
            '</td>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<input type="number" style="width: 100%" class="text-center" id="form_combos_ramos_x_caja_' +
            form_combos_cant_detalles +
            '" onchange="calcular_totales_form_combos()" onkeyup="calcular_totales_form_combos()" value="1">' +
            '</td>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<input type="number" style="width: 100%" class="text-center" id="form_combos_tallos_x_ramos_' +
            form_combos_cant_detalles +
            '" onchange="calcular_totales_form_combos()" onkeyup="calcular_totales_form_combos()">' +
            '</td>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<input type="number" style="width: 100%" class="text-center" id="form_combos_longitud_' +
            form_combos_cant_detalles + '">' +
            '</td>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<input type="number" style="width: 100%" class="text-center form_combos_precio"' +
            'id="form_combos_precio_' + form_combos_cant_detalles + '">' +
            '</td>' +
            '<td class="text-center col_fija_right_0" style="border-color: #9d9d9d; background-color: white">' +
            '<button type="button" class="btn btn-xs btn-yura_danger" onclick="form_delete_detalle_combos(' +
            form_combos_cant_detalles + ')">' +
            '<i class="fa fa-fw fa-trash"></i>' +
            '</button>' +
            '</td>' +
            '</tr>');

        $('#td_piezas_combos').prop('rowspan', parseInt($('#td_piezas_combos').prop('rowspan')) + 1);
        $('#td_cajas_combos').prop('rowspan', parseInt($('#td_cajas_combos').prop('rowspan')) + 1);
        $('#td_total_ramos_combos').prop('rowspan', parseInt($('#td_total_ramos_combos').prop('rowspan')) + 1);
        $('#td_total_tallos_combos').prop('rowspan', parseInt($('#td_total_tallos_combos').prop('rowspan')) + 1);

        ids_marcaciones = $('.ids_marcaciones');
        for (m = 0; m < ids_marcaciones.length; m++) {
            id_marcacion = ids_marcaciones[m].value;
            $('#td_form_combos_marcacion_' + id_marcacion).prop('rowspan', parseInt($('#td_form_combos_marcacion_' +
                id_marcacion).prop('rowspan')) + 1);
        }
        calcular_totales_form_combos();
    }

    function form_delete_detalle_combos(pos_combo) {
        $('#tr_form_combos_' + pos_combo).remove();
        $('#td_piezas_combos').prop('rowspan', parseInt($('#td_piezas_combos').prop('rowspan')) - 1);
        $('#td_cajas_combos').prop('rowspan', parseInt($('#td_cajas_combos').prop('rowspan')) - 1);
        $('#td_total_ramos_combos').prop('rowspan', parseInt($('#td_total_ramos_combos').prop('rowspan')) - 1);
        $('#td_total_tallos_combos').prop('rowspan', parseInt($('#td_total_tallos_combos').prop('rowspan')) - 1);

        ids_marcaciones = $('.ids_marcaciones');
        for (m = 0; m < ids_marcaciones.length; m++) {
            id_marcacion = ids_marcaciones[m].value;
            $('#td_form_combos_marcacion_' + id_marcacion).prop('rowspan', parseInt($('#td_form_combos_marcacion_' +
                id_marcacion).prop('rowspan')) - 1);
        }
        calcular_totales_form_combos();
    }

    function calcular_totales_form_combos() {
        piezas = $('#form_combos_piezas').val();
        piezas = piezas != '' ? parseInt(piezas) : 0;
        form_num_detalle_combos = $('.form_num_detalle_combos');
        total_combo_ramos = 0;
        total_combo_tallos = 0;
        for (p = 0; p < form_num_detalle_combos.length; p++) {
            pos_combo = form_num_detalle_combos[p].value;

            ramos_x_caja = $('#form_combos_ramos_x_caja_' + pos_combo).val();
            ramos_x_caja = ramos_x_caja != '' ? parseInt(ramos_x_caja) : 0;
            tallos_x_ramos = $('#form_combos_tallos_x_ramos_' + pos_combo).val();
            tallos_x_ramos = tallos_x_ramos != '' ? parseInt(tallos_x_ramos) : 0;

            total_combo_ramos += piezas * ramos_x_caja;
            total_combo_tallos += piezas * ramos_x_caja * tallos_x_ramos;
        }

        $('#form_combos_total_ramos').val(total_combo_ramos);
        $('#form_combos_total_tallos').val(total_combo_tallos);
    }

    function agregar_combos_pedido() {
        piezas = $('#form_combos_piezas').val();
        caja = $('#form_combos_tipo_caja').val();

        ids_marcaciones = $('.ids_marcaciones');
        celdas_marcaciones = [];
        for (m = 0; m < ids_marcaciones.length; m++) {
            id_marcacion = ids_marcaciones[m].value;
            valor_marcacion = $('#form_combos_marcacion_' + id_marcacion).val();
            celdas_marcaciones.push({
                id_marcacion: id_marcacion,
                valor_marcacion: valor_marcacion,
            });
        }

        $('#form_combos_tipo_caja').removeClass('error');

        form_num_detalle_combos = $('.form_num_detalle_combos');
        detalle_combos = [];
        fallos = false;
        for (p = 0; p < form_num_detalle_combos.length; p++) {
            pos_combo = form_num_detalle_combos[p].value;

            receta = $('#form_combos_receta_' + pos_combo).val();
            longitud = $('#form_combos_longitud_' + pos_combo).val();
            ramos_x_caja = $('#form_combos_ramos_x_caja_' + pos_combo).val();
            tallos_x_ramos = $('#form_combos_tallos_x_ramos_' + pos_combo).val();
            precio = $('#form_combos_precio_' + pos_combo).val();

            $('#form_combos_receta_' + pos_combo).removeClass('error');
            $('#form_combos_longitud_' + pos_combo).removeClass('bg-red');
            $('#form_combos_ramos_x_caja_' + pos_combo).removeClass('bg-red');
            $('#form_combos_tallos_x_ramos_' + pos_combo).removeClass('bg-red');
            $('#form_combos_precio_' + pos_combo).removeClass('bg-red');

            if (receta != '' && caja != '' && longitud != '' && ramos_x_caja != '' && tallos_x_ramos != '' && precio !=
                '') {

                detalle_combos.push({
                    receta: receta,
                    longitud: longitud,
                    ramos_x_caja: ramos_x_caja,
                    tallos_x_ramos: tallos_x_ramos,
                    precio: precio,
                })
            } else {
                fallos = true;
                if (receta == '')
                    $('#form_combos_receta_' + pos_combo).addClass('error');
                if (caja == '')
                    $('#form_combos_tipo_caja').addClass('error');
                if (longitud == '')
                    $('#form_combos_longitud_' + pos_combo).addClass('bg-red');
                if (ramos_x_caja == '')
                    $('#form_combos_ramos_x_caja_' + pos_combo).addClass('bg-red');
                if (tallos_x_ramos == '')
                    $('#form_combos_tallos_x_ramos_' + pos_combo).addClass('bg-red');
                if (precio == '')
                    $('#form_combos_precio_' + pos_combo).addClass('bg-red');
            }
        }
        if (!fallos && piezas > 0) {
            form_cant_detalles++;
            datos = {
                piezas: piezas,
                caja: caja,
                celdas_marcaciones: JSON.stringify(celdas_marcaciones),
                data: JSON.stringify(detalle_combos),
                form_cant_detalles: form_cant_detalles
            }
            get_jquery('{{ url('proyectos/agregar_combos_pedido') }}', datos, function(retorno) {
                $('#tbody_form_contenido_pedido').append(retorno);
                calcular_totales_pedido();
            });
        } else
            alerta('<div class="alert alert-warning text-center">Faltan datos por ingresar en el combo</div>')
    }
</script>
