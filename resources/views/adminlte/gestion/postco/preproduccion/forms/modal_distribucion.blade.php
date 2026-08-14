<legend class="text-center" style="font-size: 1.3em">
    Distribucion de "<b>{{ $caja->cantidad * $det_caja->ramos_x_caja }}</b>" ramos de
    "<b>{{ $det_caja->longitud_ramo }}cm</b>" en
    la receta
    "<b>{{ $det_caja->variedad->nombre }}</b>"
    para "<b>{{ convertDateToText($proyecto->fecha) }}</b>"
</legend>

<input type="hidden" id="id_detalle_seleccionado" value="{{ $det_caja->id_detalle_caja_proyecto }}">
<input type="hidden" id="ramos_pedido" value="{{ $caja->cantidad * $det_caja->ramos_x_caja }}">
<input type="hidden" id="longitud_pedido" value="{{ $det_caja->longitud_ramo }}">
<input type="hidden" id="postco_fecha" value="{{ $proyecto->fecha }}">
<input type="hidden" id="distribucion_selected">

<table style="width: 100%;">
    <tr>
        <td style="width: 50%; vertical-align: top" rowspan="2">
            <div id="listado_inventario" style="overflow-y: scroll; max-height: 650px">
                <table class="table-bordered text-sm" style="width: 100%; border: 1px solid #9d9d9d"
                    id="listado_inventarios">
                    <thead>
                        <tr class="tr_fija_top_0">
                            <th class="padding_lateral_5 th_yura_green">
                                Planta
                            </th>
                            <th class="padding_lateral_5 th_yura_green">
                                Variedad
                            </th>
                            <th class="padding_lateral_5 th_yura_green">
                                Longitud
                            </th>
                            <th class="padding_lateral_5 th_yura_green" style="width: 60px">
                                Disponibles
                            </th>
                            <th class="padding_lateral_5 th_yura_green">
                                Usar
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($inventarios as $pos_inv => $inv)
                            <tr onmouseover="$(this).css('background-color', 'cyan')"
                                onmouseleave="$(this).css('background-color', '')">
                                <th class="padding_lateral_5" style="border-color: #9d9d9d">
                                    {{ $inv->pta_nombre }}
                                </th>
                                <th class="padding_lateral_5" style="border-color: #9d9d9d">
                                    {{ $inv->var_nombre }}
                                </th>
                                <th class="padding_lateral_5" style="border-color: #9d9d9d">
                                    {{ $inv->longitud }}cm
                                </th>
                                <th class="padding_lateral_5" style="border-color: #9d9d9d">
                                    {{ $inv->disponibles }}
                                    <input type="hidden" id="inventario_{{ $inv->id_variedad }}_{{ $inv->longitud }}"
                                        value="{{ $inv->disponibles }}">
                                </th>
                                <th class="text-center" style="border-color: #9d9d9d">
                                    <input type="text" id="usar_inventario_{{ $pos_inv }}" style="width: 100%"
                                        class="text-center" onchange="seleccionar_inventario($(this))"
                                        max="{{ $inv->disponibles }}" data-id_variedad="{{ $inv->id_variedad }}"
                                        data-pta_nombre="{{ $inv->pta_nombre }}"
                                        data-var_nombre="{{ $inv->var_nombre }}" data-longitud="{{ $inv->longitud }}">
                                </th>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </td>
        <td style="width: 10px" rowspan="2">
        </td>
    </tr>
    <tr>
        <td style="vertical-align: top">
            <div class="input-group">
                <span class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                    Distribucion seleccionada
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
            <table class="table-bordered text-sm" style="width: 100%; border: 1px solid #9d9d9d; margin-top: 5px">
                <tr class="tr_fija_top_0">
                    <th class="text-center th_yura_green" style="width: 30px">
                        N°
                    </th>
                    <th class="text-center th_yura_green">
                        Planta
                    </th>
                    <th class="text-center th_yura_green">
                        Variedad
                    </th>
                    <th class="text-center th_yura_green" style="width: 60px">
                        Longitud
                    </th>
                    <th class="text-center th_yura_green" style="width: 60px">
                        Unidades
                    </th>
                    <th class="text-center th_yura_green" style="width: 60px" colspan="2">
                        Total Tallos
                    </th>
                </tr>
                @php
                    $pos = 0;
                @endphp
                <tbody id="tbody_variedades_seleccionados">
                    @php
                        $total_tallos = 0;
                        $distribuciones = $det_caja->distribuciones;
                        foreach ($distribuciones as $d) {
                            $total_tallos += $d->unidades * $caja->cantidad * $det_caja->ramos_x_caja;
                        }
                    @endphp
                    @foreach ($distribuciones as $pos => $item)
                        <tr id="tr_variedad_seleccionado_{{ $pos + 1 }}" class="tr_distribucion"
                            data-pos="{{ $pos + 1 }}" data-pta_nombre="{{ $item->variedad->planta->nombre }}"
                            data-var_nombre="{{ $item->variedad->nombre }}">
                            <th class="text-center mouse-hand tr_distribucion_{{ $pos + 1 }}"
                                style="border-color: #9d9d9d"
                                onmouseover="$('.tr_distribucion_{{ $pos + 1 }}').css('background-color', 'cyan')"
                                onmouseleave="$('.tr_distribucion_{{ $pos + 1 }}').css('background-color', '')"
                                onclick="seleccionar_distribucion('{{ $pos + 1 }}')">
                                <i id="icon_distribucion_{{ $pos + 1 }}"
                                    class="fa fa-fw fa-check hidden icon_distribucion"></i>
                                {{ $pos + 1 }}
                            </th>
                            <th class="text-center mouse-hand tr_distribucion_{{ $pos + 1 }}"
                                style="border-color: #9d9d9d"
                                onmouseover="$('.tr_distribucion_{{ $pos + 1 }}').css('background-color', 'cyan')"
                                onmouseleave="$('.tr_distribucion_{{ $pos + 1 }}').css('background-color', '')"
                                onclick="seleccionar_distribucion('{{ $pos + 1 }}')">
                                {{ $item->variedad->planta->nombre }}
                            </th>
                            <th class="text-center mouse-hand tr_distribucion_{{ $pos + 1 }}"
                                style="border-color: #9d9d9d"
                                onmouseover="$('.tr_distribucion_{{ $pos + 1 }}').css('background-color', 'cyan')"
                                onmouseleave="$('.tr_distribucion_{{ $pos + 1 }}').css('background-color', '')"
                                onclick="seleccionar_distribucion('{{ $pos + 1 }}')">
                                {{ $item->variedad->nombre }}
                                <input type="hidden" class="cant_variedad_seleccionado"
                                    value="{{ $pos + 1 }}">
                                <input type="hidden" id="id_variedad_seleccionado_{{ $pos + 1 }}"
                                    value="{{ $item->id_variedad }}">
                            </th>
                            <th class="text-center mouse-hand tr_distribucion_{{ $pos + 1 }}"
                                style="border-color: #9d9d9d"
                                onmouseover="$('.tr_distribucion_{{ $pos + 1 }}').css('background-color', 'cyan')"
                                onmouseleave="$('.tr_distribucion_{{ $pos + 1 }}').css('background-color', '')"
                                onclick="seleccionar_distribucion('{{ $pos + 1 }}')">
                                {{ $item->longitud }}cm
                                <input type="hidden" class="text-center" style="width: 100%"
                                    id="longitud_variedad_seleccionado_{{ $pos + 1 }}"
                                    value="{{ $item->longitud }}" readonly>
                            </th>
                            <th class="text-center mouse-hand tr_distribucion_{{ $pos + 1 }}"
                                style="border-color: #9d9d9d"
                                onmouseover="$('.tr_distribucion_{{ $pos + 1 }}').css('background-color', 'cyan')"
                                onmouseleave="$('.tr_distribucion_{{ $pos + 1 }}').css('background-color', '')"
                                onclick="seleccionar_distribucion('{{ $pos + 1 }}')">
                                {{ $item->unidades }}
                                <input type="hidden" class="text-center" style="width: 100%"
                                    id="cantidad_variedad_seleccionado_{{ $pos + 1 }}"
                                    value="{{ $item->unidades }}" readonly>
                            </th>
                            <th class="text-center mouse-hand tr_distribucion_{{ $pos + 1 }}"
                                style="border-color: #9d9d9d"
                                onmouseover="$('.tr_distribucion_{{ $pos + 1 }}').css('background-color', 'cyan')"
                                onmouseleave="$('.tr_distribucion_{{ $pos + 1 }}').css('background-color', '')"
                                onclick="seleccionar_distribucion('{{ $pos + 1 }}')">
                                {{ $item->unidades * $caja->cantidad * $det_caja->ramos_x_caja }}
                                <input type="hidden" readonly id="total_tallos_distribucion_{{ $pos + 1 }}"
                                    style="width: 100%" class="text-center"
                                    value="{{ $item->unidades * $caja->cantidad * $det_caja->ramos_x_caja }}">
                            </th>
                            @if ($pos == 0)
                                <th class="text-center" style="border-color: #9d9d9d"
                                    rowspan="{{ count($distribuciones) }}">
                                    {{ $total_tallos }}
                                </th>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d; margin-top: 15px">
                <tr>
                    <th class="text-center th_yura_green" colspan="6" style="font-size: 1.3em">
                        ORDEN TRABAJO {{ $caja->cantidad * $det_caja->ramos_x_caja }} ramos
                    </th>
                </tr>
                <tr>
                    <th class="padding_lateral_5 bg-yura_dark" style="width: 30px">
                        N°
                    </th>
                    <th class="padding_lateral_5 bg-yura_dark">
                        Planta
                    </th>
                    <th class="padding_lateral_5 bg-yura_dark">
                        Variedad
                    </th>
                    <th class="padding_lateral_5 bg-yura_dark" style="width: 60px">
                        Longitud
                    </th>
                    <th class="padding_lateral_5 bg-yura_dark" style="width: 30px">
                        Tallos
                    </th>
                    <th class="padding_lateral_5 bg-yura_dark" style="width: 30px">
                        Opciones
                    </th>
                </tr>
                <tbody id="tbody_orden_trabajo" class="text-sm"></tbody>
                <tr>
                    <th class="padding_lateral_5 th_yura_green" colspan="4">
                        Total Tallos
                    </th>
                    <th class="text-center">
                        <input type="text" style="width: 100%" class="text-center th_yura_green" readonly
                            id="ot_total_tallos">
                    </th>
                    <th class="text-center th_yura_green">
                    </th>
                </tr>
            </table>

            <div class="text-center" style="margin-top: 10px">
                <button type="button" class="btn btn-yura_primary" onclick="store_ot_nacional()">
                    <i class="fa fa-fw fa-save"></i> GRABAR
                </button>
            </div>
        </td>
    </tr>
</table>

<script>
    row_ot = 0;
    estructura_tabla('listado_inventarios');
    verificar_distribucion();

    function seleccionar_distribucion(pos) {
        $('.icon_distribucion').addClass('hidden');
        $('#icon_distribucion_' + pos).removeClass('hidden');
        $('#distribucion_selected').val(pos);
    }

    function cargar_receta() {
        id_detalle = $('#id_detalle_seleccionado').val();
        datos = {
            id_detalle: id_detalle,
            numero_receta: $('#select_numero_receta').val(),
            ramos_pedido: $('#ramos_pedido').val(),
        };
        get_jquery('{{ url('preproduccion/cargar_receta') }}', datos, function(retorno) {
            $('#tbody_variedades_seleccionados').html(retorno);
            verificar_distribucion();
        }, 'tbody_variedades_seleccionados');
    }

    function verificar_distribucion() {
        $('#tbody_orden_trabajo').html('');

        tr_distribucion = $('.tr_distribucion');
        for (i = 0; i < tr_distribucion.length; i++) {
            id = tr_distribucion[i].id;
            pos = $('#' + id).data('pos');
            id_variedad = $('#id_variedad_seleccionado_' + pos).val();
            longitud = $('#longitud_variedad_seleccionado_' + pos).val();
            total_tallos = parseInt($('#total_tallos_distribucion_' + pos).val());
            inventario = 0;
            if ($('#inventario_' + id_variedad + '_' + longitud).length)
                inventario = parseInt($('#inventario_' + id_variedad + '_' + longitud).val());
            if (inventario >= total_tallos) {
                usar = total_tallos;
            } else {
                usar = inventario;
            }
            if (usar > 0) {
                row_ot++;
                pta_nombre = $('#' + id).data('pta_nombre');
                var_nombre = $('#' + id).data('var_nombre');
                $('#tbody_orden_trabajo').append(
                    '<tr id="tr_ot_' + row_ot + '" class="tr_ot tr_ot_' + pos + '"' +
                    'onmouseover="$(this).css(\'background-color\', \'cyan\')" ' +
                    'onmouseleave="$(this).css(\'background-color\', \'\')" data-pos="' + pos +
                    '" data-id_variedad="' + id_variedad + '" data-longitud="' + longitud + '" data-row_ot="' +
                    row_ot + '">' +
                    '<th class="padding_lateral_5" style="border-color: #9d9d9d">' +
                    pos +
                    '</th>' +
                    '<th class="padding_lateral_5" style="border-color: #9d9d9d">' +
                    pta_nombre +
                    '</th>' +
                    '<th class="padding_lateral_5" style="border-color: #9d9d9d">' +
                    var_nombre +
                    '</th>' +
                    '<th class="padding_lateral_5" style="border-color: #9d9d9d">' +
                    longitud + 'cm' +
                    '</th>' +
                    '<th class="text-center" style="border-color: #9d9d9d">' +
                    '<input type="text" class="text-center input_tallos_' + pos +
                    '" style="width: 100%" id="input_tallos_' + row_ot +
                    '" value="' + usar + '">' +
                    '</th>' +
                    '<th class="text-center" style="border-color: #9d9d9d" onclick="delete_row_ot(' + row_ot +
                    ')">' +
                    '<button type="button" class="btn btn-xs btn-yura_danger">' +
                    '<i class="fa fa-fw fa-trash"></i>' +
                    '</button>' +
                    '</th>' +
                    '</tr>'
                );
            }
        }
        calcular_totales();
    }

    function delete_row_ot(num) {
        $('#tr_ot_' + num).remove();
    }

    function seleccionar_inventario(input) {
        inventario = parseInt(input.val());
        if (inventario <= parseInt(input.prop('max')) && inventario > 0) {
            pos = $('#distribucion_selected').val();
            if (pos != '') {
                id_variedad = input.data('id_variedad');
                longitud = input.data('longitud');
                pta_nombre = input.data('pta_nombre');
                var_nombre = input.data('var_nombre');
                row_ot++;
                $('#tbody_orden_trabajo').append(
                    '<tr id="tr_ot_' + row_ot + '" class="tr_ot tr_ot_' + pos + '"' +
                    'onmouseover="$(this).css(\'background-color\', \'cyan\')" ' +
                    'onmouseleave="$(this).css(\'background-color\', \'\')" data-pos="' + pos +
                    '" data-id_variedad="' + id_variedad + '" data-longitud="' + longitud + '" data-row_ot="' +
                    row_ot + '">' +
                    '<th class="padding_lateral_5" style="border-color: #9d9d9d">' +
                    pos +
                    '</th>' +
                    '<th class="padding_lateral_5" style="border-color: #9d9d9d">' +
                    pta_nombre +
                    '</th>' +
                    '<th class="padding_lateral_5" style="border-color: #9d9d9d">' +
                    var_nombre +
                    '</th>' +
                    '<th class="padding_lateral_5" style="border-color: #9d9d9d">' +
                    longitud + 'cm' +
                    '</th>' +
                    '<th class="text-center" style="border-color: #9d9d9d">' +
                    '<input type="text" class="text-center input_tallos_' + pos +
                    '" style="width: 100%" id="input_tallos_' + row_ot +
                    '" value="' + inventario + '">' +
                    '</th>' +
                    '<th class="text-center" style="border-color: #9d9d9d" onclick="delete_row_ot(' + row_ot +
                    ')">' +
                    '<button type="button" class="btn btn-xs btn-yura_danger">' +
                    '<i class="fa fa-fw fa-trash"></i>' +
                    '</button>' +
                    '</th>' +
                    '</tr>'
                );
            } else {
                alert('Primero, debe seleccionar la flor de la distribucion');
            }
        } else {
            alert('La cantidad no es válida');
        }
        input.val('');
        calcular_totales();
    }

    function calcular_totales() {
        total_tallos = 0;
        tr_ot = $('.tr_ot');
        for (i = 0; i < tr_ot.length; i++) {
            id = tr_ot[i].id;
            row = $('#' + id).data('row_ot');
            tallos = parseInt($('#input_tallos_' + row).val());
            total_tallos += tallos;
        }
        $('#ot_total_tallos').val(total_tallos);
    }

    function verificar_totales() {
        fallos = false;
        tr_distribucion = $('.tr_distribucion');
        for (d = 0; d < tr_distribucion.length; d++) {
            id_dist = tr_distribucion[d].id;
            pos = parseInt($('#' + id_dist).data('pos'));
            $('.input_tallos_' + pos).removeClass('bg-yura_warning');
            total_tallos_pos = parseInt($('#total_tallos_distribucion_' + pos).val());
            total_tallos_ot = 0;
            tr_ot = $('.tr_ot_' + pos);
            for (o = 0; o < tr_ot.length; o++) {
                id_tr = tr_ot[o].id;
                row = $('#' + id_tr).data('row_ot');
                tallos = parseInt($('#input_tallos_' + row).val());
                total_tallos_ot += tallos;
            }
            //alert(total_tallos_ot + ' vs ' + total_tallos_pos + ' pos:' + pos);
            if (total_tallos_ot > total_tallos_pos) {
                fallos = true;
                $('.input_tallos_' + pos).addClass('bg-yura_warning');
                alert('Ha superado el numero de tallos totales para la flor N° ' + pos);
            }
        }
        return fallos;
    }

    function store_ot_nacional() {
        if (!verificar_totales()) {
            data = [];
            tr_ot = $('.tr_ot');
            for (i = 0; i < tr_ot.length; i++) {

            }
        }
    }
</script>
