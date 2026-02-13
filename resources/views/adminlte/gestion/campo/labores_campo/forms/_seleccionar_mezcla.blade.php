<div class="text-center">
    <nav class="navbar navbar-default">
        <div class="container-fluid">
            <div class="navbar-header">
                <a class="navbar-brand" href="javascript:void(0)" style="font-size: 1em"><strong>Agregar a la MEZCLA</strong></a>
            </div>

            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                <form class="navbar-form navbar-right">
                    <div class="form-group">
                        <select id="new_prod" class="form-control input-yura_default">
                            <option value="">Seleccione el producto</option>
                            @foreach($other_productos as $p)
                                <option value="{{$p->id_producto.'|'.$p->nombre}}">{{$p->nombre}}</option>
                            @endforeach
                        </select>
                        <input type="number" class="form-control text-center input-yura_default" id="new_dosis" placeholder="Dosis">
                        <select id="new_unidad_medida" class="form-control input-yura_default">
                            <option value="">Unidad Medida</option>
                            @foreach($unidad_medidas as $um)
                                <option value="{{$um->id_unidad_medida}}">{{$um->siglas}}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="button" class="btn btn-yura_primary" onclick="add_new_producto()">
                        <i class="fa fa-fw fa-save"></i> Grabar
                    </button>
                </form>
            </div>
        </div>
    </nav>
</div>

<div style="overflow-x: scroll; overflow-y: scroll; max-height: 430px">
    <table class="table-striped table-bordered" style="width: 100%; border: 1px solid #9d9d9d" id="table_seleccionar_mezcla">
        <thead>
        <tr id="m-tr">
            <th class="text-center th_yura_green" rowspan="2">
                <input type="checkbox" id="m-check_all" class="mouse-hand" onchange="$('.m-check').prop('checked', $(this).prop('checked'))">
            </th>
            <th class="text-center th_yura_green" rowspan="2">
                <div style="width: 60px">
                    Variedad
                </div>
            </th>
            <th class="text-center th_yura_green" rowspan="2">
                <div style="width: 120px">
                    Sector
                </div>
            </th>
            <th class="text-center th_yura_green" rowspan="2">
                <div style="width: 60px">
                    Módulo
                </div>
            </th>
            <th class="text-center th_yura_green" rowspan="2">
                P/S
            </th>
            <th class="text-center th_yura_green" rowspan="2">
                <div style="width: 120px">
                    Inicio
                </div>
            </th>
            <th class="text-center th_yura_green" rowspan="2">
                <div style="width: 60px">
                    Días
                </div>
            </th>
            <th class="text-center th_yura_green" rowspan="2">
                <div style="width: 120px">
                    Fecha
                </div>
            </th>
            <th class="text-center th_yura_green" rowspan="2">
                <div style="width: 80px">
                    Repetición
                </div>
            </th>
            <th class="text-center th_yura_green" rowspan="2">
                <div style="width: 60px">
                    Densidad/m<sup>2</sup>
                </div>
            </th>
            <th class="text-center th_yura_green" rowspan="2">
                <div style="width: 60px">
                    CC x Planta
                </div>
            </th>
            <th class="text-center th_yura_green" rowspan="2">
                <div style="width: 60px">
                    Ltr.
                </div>
            </th>
            @foreach($productos as $p)
                <th class="text-center bg-yura_dark columna_prod_{{$p->id_producto}}" colspan="2"
                    onmouseover="$('#btn_del_prod_{{$p->id_producto}}').removeClass('hidden')"
                    onmouseleave="$('#btn_del_prod_{{$p->id_producto}}').addClass('hidden')">
                    <a class="pull-right mouse-hand btn-xs btn-yura_danger hidden" href="javascript:void(0)"
                       onclick="delete_columna_prod('{{$p->id_producto}}')"
                       id="btn_del_prod_{{$p->id_producto}}">×</a>
                    <div style="width: 160px;">
                        {{$p->nombre}}
                    </div>
                </th>
            @endforeach
            @foreach($mano_obras as $mo)
                <th class="text-center bg-yura_dark columna_mo_{{$mo->id_mano_obra}}" colspan="2"
                    onmouseover="$('#btn_del_mo_{{$mo->id_mano_obra}}').removeClass('hidden')"
                    onmouseleave="$('#btn_del_mo_{{$mo->id_mano_obra}}').addClass('hidden')">
                    <a class="pull-right mouse-hand btn-xs btn-yura_danger hidden" href="javascript:void(0)"
                       onclick="delete_columna_mo('{{$mo->id_mano_obra}}')"
                       id="btn_del_mo_{{$mo->id_mano_obra}}">×</a>
                    <div style="width: 160px">
                        {{'(MO) '.$mo->nombre}}
                    </div>
                </th>
            @endforeach
        </tr>
        <tr id="m-tr_input_all">
            @foreach($productos as $p)
                <th class="text-center bg-yura_dark columna_prod_{{$p->id_producto}}">
                    <div style="width: 90px" class="text-center">
                        <input type="number" style="width: 100%" onkeyup="input_all_detalle_prod('{{$p->id_producto}}')"
                               onchange="input_all_detalle_prod('{{$p->id_producto}}')"
                               class="bg-yura_dark text-center" id="input_all_productos_{{$p->id_producto}}">
                        <input type="hidden" class="ids_productos" value="{{$p->id_producto}}">
                    </div>
                </th>
                <th class="text-center bg-yura_dark columna_prod_{{$p->id_producto}}">
                    <div style="width: 70px" class="text-center">
                        <select style="width: 100%" class="bg-yura_dark" id="select_all_productos_{{$p->id_producto}}"
                                onchange="select_all_detalle_prod('{{$p->id_producto}}')">
                            <option value="">...</option>
                            @foreach($unidad_medidas as $um)
                                <option value="{{$um->id_unidad_medida}}">{{$um->siglas}}</option>
                            @endforeach
                        </select>
                    </div>
                </th>
            @endforeach
            @foreach($mano_obras as $mo)
                <th class="text-center bg-yura_dark columna_mo_{{$mo->id_mano_obra}}">
                    <div style="width: 90px" class="text-center">
                        <input type="number" style="width: 100%" onkeyup="input_all_detalle_mo('{{$mo->id_mano_obra}}')"
                               onchange="input_all_detalle_mo('{{$mo->id_mano_obra}}')"
                               class="bg-yura_dark text-center" id="input_all_mo_{{$mo->id_mano_obra}}">
                        <input type="hidden" class="ids_mano_obras" value="{{$mo->id_mano_obra}}">
                    </div>
                </th>
                <th class="text-center bg-yura_dark columna_mo_{{$mo->id_mano_obra}}">
                    <div style="width: 70px" class="text-center">
                        <select style="width: 100%" class="bg-yura_dark" id="select_all_mo_{{$mo->id_mano_obra}}"
                                onchange="select_all_detalle_mo('{{$mo->id_mano_obra}}')">
                            <option value="">...</option>
                            @foreach($unidad_medidas as $um)
                                <option value="{{$um->id_unidad_medida}}">{{$um->siglas}}</option>
                            @endforeach
                        </select>
                    </div>
                </th>
            @endforeach
        </tr>
        </thead>
        <tbody>
        @foreach($listado as $i => $item)
            @php
                $modulo = $item['ciclo']->modulo;
                $densidad = $item['fenograma']->densidad_plantas_ini_m2;
                $dias_ciclo = difFechas(hoy(), $item['ciclo']->fecha_inicio)->days;
                $labor = isset($item['data']['app_campo']) ? getAplicacionCampoById($item['data']['app_campo']) : '';
                if($item['ciclo']->poda_siembra == 'S'){
                    $cc_x_planta = $labor != '' ? $labor->cc_x_planta : $mezcla->litro_x_cama;
                    $litros = $labor != '' ? $labor->litro_x_cama : $densidad * 45 * ($cc_x_planta / 1000);
                } else {
                    $cc_x_planta = $labor != '' ? $labor->litro_x_cama : $mezcla->litro_x_cama_poda;
                    $litros = $labor != '' ? $labor->litro_x_cama : $densidad * 45 * ($cc_x_planta / 1000);
                }

                if ($labor == ''){
                    if($item['ciclo']->poda_siembra == 'S')
                        $posicion = array_search($item['data']['repeticion'], explode('-', $mezcla->repeticiones));
                    else
                        $posicion = array_search($item['data']['repeticion'], explode('-', $mezcla->repeticiones_poda));
                    if ($posicion != false){
                        if($item['ciclo']->poda_siembra == 'S'){
                            $cc_x_planta = explode('-', $mezcla->litros_x_repeticiones)[$posicion];
                        } else {
                            $cc_x_planta = explode('-', $mezcla->litros_x_repeticiones_poda)[$posicion];
                        }
                        $litros = $densidad * 45 * ($cc_x_planta / 1000);
                    }
                }
            @endphp
            <tr id="m-tr_{{$i}}">
                <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                    <input type="checkbox" class="mouse-hand m-check" id="m-check_{{$i}}">
                </th>
                <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                    {{$item['ciclo']->variedad->siglas}}
                </th>
                <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                    {{$modulo->sector->nombre}}
                </th>
                <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                    {{$modulo->nombre}}
                </th>
                <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                    {{$item['ciclo']->poda_siembra}}
                </th>
                <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                    {{convertDateToText($item['ciclo']->fecha_inicio)}}
                </th>
                <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                    {{$dias_ciclo}}
                </th>
                <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                    {{getDiaSemanaByFecha($item['data']['fecha']) . ' ' . convertDateToText($item['data']['fecha'])}}
                </th>
                <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                    {{$item['data']['repeticion']}}
                </th>
                <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                    <input type="number" id="m-densidad_{{$i}}" readonly value="{{$densidad}}" style="width: 100%" class="text-center">
                </th>
                <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                    <input type="number" id="m-cc_x_planta_{{$i}}" value="{{$cc_x_planta}}" style="width: 100%" class="text-center" 
                    onchange="calcular_litros('m-densidad_{{$i}}', 'm-cc_x_planta_{{$i}}', 'm-litros_x_cama_{{$i}}')">
                </th>
                <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                    <input type="number" id="m-litros_x_cama_{{$i}}" value="{{$litros}}" style="width: 100%" class="text-center">
                </th>
                <input type="hidden" value="{{$i}}" class="pos_i">
                <input type="hidden" id="m-id_ciclo_{{$i}}" value="{{$item['data']['ciclo']}}">
                <input type="hidden" id="m-id_aplicacion_campo_{{$i}}"
                       value="{{isset($item['data']['app_campo']) ? $item['data']['app_campo'] : ''}}">
                <input type="hidden" id="m-aplicacion_{{$i}}" value="{{$item['data']['aplicacion']}}">
                <input type="hidden" id="m-fecha_{{$i}}" value="{{$item['data']['fecha']}}">
                <input type="hidden" id="m-repeticion_{{$i}}" value="{{$item['data']['repeticion']}}">
                <input type="hidden" id="m-camas_{{$i}}" value="{{$item['data']['camas']}}">
                @foreach($productos as $p)
                    @php
                        $dosis = '';
                        $unidad_medida = '';
                        $factor_conversion = '';
                        $unidad_conversion = '';
                        if ($labor != '' && count($labor->detalles) > 0){
                            $detalle = $labor->getDetalleByProducto($p->id_producto);
                            if ($detalle != ''){
                                $dosis = $detalle->dosis;
                                $unidad_medida = $detalle->id_unidad_medida;
                                $factor_conversion = $detalle->factor_conversion;
                                $unidad_conversion = $detalle->id_unidad_conversion;
                            }
                        } else {
                            foreach($item['detalles'] as $det){
                                if ($det['detalle']->id_producto == $p->id_producto){
                                    if ($det['parametro'] != ''){
                                        $dosis = $det['parametro']->dosis;
                                        $unidad_medida = $det['parametro']->id_unidad_medida;
                                        $factor_conversion = $det['parametro']->factor_conversion;
                                        $unidad_conversion = $det['parametro']->id_unidad_conversion;
                                    }
                                }
                            }
                        }
                    @endphp

                    <td class="text-center columna_prod_{{$p->id_producto}}" style="border-color: #9d9d9d">
                        <input type="number" value="{{$dosis}}"
                               class="text-center input_prod_{{$p->id_producto}}"
                               style="width: 100%" id="m-dosis_prod_{{$i}}_{{$p->id_producto}}">

                        <input type="hidden" value="{{$factor_conversion}}"
                               id="m-factor_conversion_prod_{{$i}}_{{$p->id_producto}}">
                        <input type="hidden" value="{{$unidad_conversion}}"
                               id="m-unidad_conversion_prod_{{$i}}_{{$p->id_producto}}">
                    </td>
                    <td class="text-center columna_prod_{{$p->id_producto}}" style="border-color: #9d9d9d">
                        <select style="width: 100%" id="m-unidad_medida_prod_{{$i}}_{{$p->id_producto}}" class="um_prod_{{$p->id_producto}}">
                            <option value="">...</option>
                            @foreach($unidad_medidas as $um)
                                <option value="{{$um->id_unidad_medida}}" {{$um->id_unidad_medida == $unidad_medida ? 'selected' : ''}}>
                                    {{$um->siglas}}
                                </option>
                            @endforeach
                        </select>
                    </td>
                @endforeach
                @foreach($mano_obras as $mo)
                    @php
                        $dosis = '';
                        $unidad_medida = '';
                        $factor_conversion = '';
                        $unidad_conversion = '';
                        if ($labor != ''){
                            $detalle = $labor->getDetalleByManoObra($mo->id_mano_obra);
                            if ($detalle != ''){
                                $dosis = $detalle->dosis;
                                $unidad_medida = $detalle->id_unidad_medida;
                                $factor_conversion = $detalle->factor_conversion;
                                $unidad_conversion = $detalle->id_unidad_conversion;
                            }
                        } else {
                            foreach($item['detalles'] as $det){
                                if ($det['detalle']->id_mano_obra == $mo->id_mano_obra){
                                    if ($det['parametro'] != ''){
                                        $dosis = $det['parametro']->cantidad_mo;
                                        $unidad_medida = $det['parametro']->id_unidad_medida;
                                        $factor_conversion = $det['parametro']->factor_conversion;
                                        $unidad_conversion = $det['parametro']->id_unidad_conversion;
                                    }
                                }
                            }
                        }
                    @endphp

                    <td class="text-center columna_mo_{{$mo->id_mano_obra}}" style="border-color: #9d9d9d">
                        <input type="number" value="{{$dosis}}"
                               class="text-center input_mo_{{$mo->id_mano_obra}}"
                               style="width: 100%" id="m-dosis_mo_{{$i}}_{{$mo->id_mano_obra}}">

                        <input type="hidden" value="{{$factor_conversion}}"
                               id="m-factor_conversion_mo_{{$i}}_{{$mo->id_mano_obra}}">
                        <input type="hidden" value="{{$unidad_conversion}}"
                               id="m-unidad_conversion_mo_{{$i}}_{{$mo->id_mano_obra}}">
                    </td>
                    <td class="text-center columna_mo_{{$mo->id_mano_obra}}" style="border-color: #9d9d9d">
                        <select style="width: 100%" id="m-unidad_medida_mo_{{$i}}_{{$mo->id_mano_obra}}" class="um_mo_{{$mo->id_mano_obra}}">
                            <option value="">...</option>
                            @foreach($unidad_medidas as $um)
                                <option value="{{$um->id_unidad_medida}}" {{$um->id_unidad_medida == $unidad_medida ? 'selected' : ''}}>
                                    {{$um->siglas}}
                                </option>
                            @endforeach
                        </select>
                    </td>
                @endforeach
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
<div style="margin-top: 10px" class="text-center">
    <button type="button" class="btn btn-yura_primary" onclick="store_mezclas('{{$mezcla->id_aplicacion_mezcla}}')">
        <i class="fa fa-fw fa-save"></i> Aplicar mezclas
    </button>
</div>

<script>
    estructura_tabla('table_seleccionar_mezcla', false, false);
    $('#table_seleccionar_mezcla_filter').remove();

    function store_mezclas(mezcla) {
        array_pos_i = $('.pos_i');
        ids_productos = $('.ids_productos');
        ids_mano_obras = $('.ids_mano_obras');
        data = [];
        for (i = 0; i < array_pos_i.length; i++) {
            pos_i = array_pos_i[i].value;
            productos = [];
            for (y = 0; y < ids_productos.length; y++) {
                prod = ids_productos[y].value;
                productos.push({
                    prod: prod,
                    dosis: $('#m-dosis_prod_' + pos_i + '_' + prod).val(),
                    unidad_medida: $('#m-unidad_medida_prod_' + pos_i + '_' + prod).val(),
                    factor_conversion: $('#m-factor_conversion_prod_' + pos_i + '_' + prod).val(),
                    unidad_conversion: $('#m-unidad_conversion_prod_' + pos_i + '_' + prod).val(),
                });
            }
            mano_obras = [];
            for (y = 0; y < ids_mano_obras.length; y++) {
                mo = ids_mano_obras[y].value;
                mano_obras.push({
                    mo: mo,
                    dosis: $('#m-dosis_mo_' + pos_i + '_' + mo).val(),
                    unidad_medida: $('#m-unidad_medida_mo_' + pos_i + '_' + mo).val(),
                    factor_conversion: $('#m-factor_conversion_mo_' + pos_i + '_' + mo).val(),
                    unidad_conversion: $('#m-unidad_conversion_mo_' + pos_i + '_' + mo).val(),
                });
            }
            data.push({
                ciclo: $('#m-id_ciclo_' + pos_i).val(),
                app_campo: $('#m-id_aplicacion_campo_' + pos_i).val(),
                aplicacion: $('#m-aplicacion_' + pos_i).val(),
                fecha: $('#m-fecha_' + pos_i).val(),
                repeticion: $('#m-repeticion_' + pos_i).val(),
                camas: $('#m-camas_' + pos_i).val(),
                cc_x_planta: $('#m-cc_x_planta_' + pos_i).val(),
                litros_x_cama: $('#m-litros_x_cama_' + pos_i).val(),
                productos: productos,
                mano_obras: mano_obras,
            });
        }
        datos = {
            _token: '{{csrf_token()}}',
            mezcla: mezcla,
            litros_x_cama: $('#mezcla_litros_x_cama_' + mezcla).val(),
            data: data,
        };
        post_jquery_m('{{url('ingreso_labores/store_mezclas')}}', datos, function () {
            listar_labores();
            cerrar_modals();
        });
    }

    function input_all_detalle_prod(id) {
        texto = $('#input_all_productos_' + id).val();
        array_pos_i = $('.pos_i');
        for (i = 0; i < array_pos_i.length; i++) {
            pos_i = array_pos_i[i].value;
            if ($('#m-check_' + pos_i).prop('checked') == true)
                $('#m-dosis_prod_' + pos_i + '_' + id).val(texto);
        }
    }

    function input_all_detalle_mo(id) {
        texto = $('#input_all_mo_' + id).val();
        array_pos_i = $('.pos_i');
        for (i = 0; i < array_pos_i.length; i++) {
            pos_i = array_pos_i[i].value;
            if ($('#m-check_' + pos_i).prop('checked') == true)
                $('#m-dosis_mo_' + pos_i + '_' + id).val(texto);
        }
    }

    function select_all_detalle_prod(id) {
        texto = $('#select_all_productos_' + id).val();
        array_pos_i = $('.pos_i');
        for (i = 0; i < array_pos_i.length; i++) {
            pos_i = array_pos_i[i].value;
            if ($('#m-check_' + pos_i).prop('checked') == true)
                $('#m-unidad_medida_prod_' + pos_i + '_' + id).val(texto);
        }
    }

    function select_all_detalle_mo(id) {
        texto = $('#select_all_mo_' + id).val();
        array_pos_i = $('.pos_i');
        for (i = 0; i < array_pos_i.length; i++) {
            pos_i = array_pos_i[i].value;
            if ($('#m-check_' + pos_i).prop('checked') == true)
                $('#m-unidad_medida_mo_' + pos_i + '_' + id).val(texto);
        }
    }

    function delete_columna_prod(id) {
        $('.columna_prod_' + id).remove();
    }

    function delete_columna_mo(id) {
        $('.columna_mo_' + id).remove();
    }

    function add_new_producto() {
        new_prod = $('#new_prod').val();
        id_prod = new_prod.split('|')[0];
        nombre_prod = new_prod.split('|')[1];
        new_dosis = $('#new_dosis').val();
        new_unidad_medida = $('#new_unidad_medida').val();
        $('#m-tr').append('<th class="text-center bg-yura_dark columna_prod_' + id_prod + '" colspan="2"' +
            ' onmouseover="$(\'#btn_del_prod_' + id_prod + '\').removeClass(\'hidden\')"' +
            ' onmouseleave="$(\'#btn_del_prod_' + id_prod + '\').addClass(\'hidden\')">' +
            '<a class="pull-right mouse-hand btn-xs btn-yura_danger hidden" href="javascript:void(0)"' +
            ' onclick="delete_columna_prod(\'' + id_prod + '\')"' +
            ' id="btn_del_prod_' + id_prod + '">×</a>' +
            '<div style="width: 140px;">' +
            '' + nombre_prod +
            '</div>' +
            '</th>');
        $('#m-tr_input_all').append('<th class="text-center bg-yura_dark columna_prod_' + id_prod + '">' +
            '<input type="number" style="width: 100%" onkeyup="input_all_detalle_prod(\'' + id_prod + '\')"' +
            ' onchange="input_all_detalle_prod(\'' + id_prod + '\')"' +
            ' class="bg-yura_dark text-center" id="input_all_productos_' + id_prod + '" value="' + new_dosis + '">' +
            '<input type="hidden" class="ids_productos" value="' + id_prod + '">' +
            '</th>' +
            '<th class="text-center bg-yura_dark columna_prod_' + id_prod + '">' +
            '<div style="width: 70px" class="text-center">' +
            '<select style="width: 100%" class="bg-yura_dark" id="select_all_productos_' + id_prod + '"' +
            ' onchange="select_all_detalle_prod(\'' + id_prod + '\')">' +
            $("#new_unidad_medida").html() +
            '</select>' +
            '</div>' +
            '</th>');
        $('#select_all_productos_' + id_prod).val(new_unidad_medida);
        array_pos_i = $('.pos_i');
        for (i = 0; i < array_pos_i.length; i++) {
            pos_i = array_pos_i[i].value;
            $('#m-tr_' + pos_i).append('<td class="text-center columna_prod_' + id_prod + '" style="border-color: #9d9d9d">' +
                '<input type="number" value=""' +
                ' class="text-center input_prod_' + id_prod + '"' +
                ' style="width: 100%" id="m-dosis_prod_' + pos_i + '_' + id_prod + '">' +
                '<input type="hidden" value=""' +
                ' id="m-factor_conversion_prod_' + pos_i + '_' + id_prod + '">' +
                '<input type="hidden" value=""' +
                ' id="m-unidad_conversion_prod_' + pos_i + '_' + id_prod + '">' +
                '</td>' +
                '<td class="text-center columna_prod_' + id_prod + '" style="border-color: #9d9d9d">' +
                '<select style="width: 100%" id="m-unidad_medida_prod_' + pos_i + '_' + id_prod + '" class="um_prod_' + id_prod + '">' +
                '<option value="">...</option>' +
                '@foreach($unidad_medidas as $um)' +
                '<option value="{{$um->id_unidad_medida}}">' +
                '{{$um->siglas}}' +
                '</option>' +
                '@endforeach' +
                '</select>' +
                '</td>');
            if ($('#m-check_' + pos_i).prop('checked') == true) {
                $('#m-dosis_prod_' + pos_i + '_' + id_prod).val(new_dosis);
                $('#m-unidad_medida_prod_' + pos_i + '_' + id_prod).val(new_unidad_medida);
            }
        }
    }
</script>