<table class="table-bordered table-striped" style="width: 100%; border: 1px solid #9d9d9d" id="table_add_adicional">
    <tr>
        <th class="text-center th_yura_green">
            Módulo
        </th>
        <th class="text-center th_yura_green" style="padding-left: 5px; padding-right: 5px">
            Var.
        </th>
        <th class="text-center th_yura_green" style="padding-left: 5px; padding-right: 5px">
            P/S
        </th>
        <th class="text-center th_yura_green">
            <div style="width: 150px">Inicio</div>
        </th>
        <th class="text-center th_yura_green" style="padding-left: 5px; padding-right: 5px">
            Días
        </th>
        <th class="text-center th_yura_green">
            <div style="width: 150px">Labor</div>
        </th>
        <th class="text-center th_yura_green">
            Fecha
        </th>
        <th class="text-center th_yura_green">
            Repetición
        </th>
        <th class="text-center th_yura_green">
            Plantas
        </th>
        <th class="text-center th_yura_green">
            Horas Día
        </th>
        @foreach($mano_obras as $mo)
            <th class="text-center bg-yura_dark th_detalles">
                <div style="width: 120px">
                    {{$mo->nombre}}
                </div>
                <input type="hidden" class="new_ids_mano_obras" value="{{$mo->id_mano_obra}}">
            </th>
        @endforeach
        <th class="text-center th_yura_green">
            Hombres día
        </th>
        <th class="text-center th_yura_green">
            Horas Necesarias
        </th>
        <th class="text-center th_yura_green" style="width: 80px">
            <div class="btn-group">
                <button type="button" class="btn btn-xs btn-yura_default" onclick="add_tr_adicional()">
                    <i class="fa fa-fw fa-plus"></i>
                </button>
                <button type="button" class="btn btn-xs btn-yura_danger" onclick="del_tr_adicional()">
                    <i class="fa fa-fw fa-times"></i>
                </button>
            </div>
        </th>
    </tr>
    <tr id="tr_adicional_1">
        <th class="text-center" style="border-color: #9d9d9d">
            <select id="new_ciclo_1" style="width: 100%" onchange="seleccionar_modulo(1)">
                <option value="">Seleccione...</option>
                @foreach($ciclos as $c)
                    <option value="{{$c->id_ciclo}}">{{$c->modulo->nombre}}</option>
                @endforeach
            </select>
        </th>
        <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef" id="td_variedad_1">
        </th>
        <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef" id="td_poda_siembra_1">
        </th>
        <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef" id="td_fecha_inicio_1">
        </th>
        <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef" id="td_dias_1">
        </th>
        <td class="text-center" style="border-color: #9d9d9d" onchange="seleccionar_labor(1)">
            <select id="new_labor_1" style="width: 100%" readonly>
            </select>
        </td>
        <td class="text-center" style="border-color: #9d9d9d">
            <input type="date" id="new_fecha_1" style="width: 100%" class="text-center" min="{{$semana->fecha_inicial}}"
                   max="{{$semana->fecha_final}}" readonly value="{{$semana->fecha_inicial}}">
        </td>
        <td class="text-center" style="border-color: #9d9d9d">
            <input type="number" id="new_repeticion_1" style="width: 100%" class="text-center" readonly>
        </td>
        <td class="text-center" style="border-color: #9d9d9d" id="td_plantas_1">
            <input type="number" id="new_plantas_1" style="width: 100%" class="text-center" onclick="calcular_hombres_dia_add(1)"
                   onchange="calcular_hombres_dia_add(1)" onkeyup="calcular_hombres_dia_add(1)" readonly>
        </td>
        <td class="text-center" style="border-color: #9d9d9d">
            <input type="number" id="new_horas_dia_1" style="width: 100%" class="text-center" onclick="calcular_hombres_dia_add(1)"
                   onchange="calcular_hombres_dia_add(1)" onkeyup="calcular_hombres_dia_add(1)" readonly value="0">
        </td>
        @foreach($mano_obras as $mo)
            <td class="text-center" style="border-color: #9d9d9d">
                <input type="number" id="new_dosis_{{$mo->id_mano_obra}}_1" style="width: 100%;"
                       class="text-center new_dosis_1" placeholder="Rend. x plantas" value="0" onclick="calcular_hombres_dia_add(1)"
                       onchange="calcular_hombres_dia_add(1)" onkeyup="calcular_hombres_dia_add(1)">
            </td>
        @endforeach
        <td class="text-center" style="border-color: #9d9d9d">
            <input type="number" id="new_hombres_dia_1" style="width: 100%" class="text-center" onclick="calcular_horas_necesarias_add(1)"
                   onchange="calcular_horas_necesarias_add(1)" onkeyup="calcular_horas_necesarias_add(1)" readonly value="0">
        </td>
        <td class="text-center" style="border-color: #9d9d9d">
            <input type="number" id="new_horas_necesarias_1" style="width: 100%" class="text-center" readonly value="0">
        </td>
    </tr>
</table>
<div class="text-center" style="margin-top: 10px">
    <button type="button" class="btn btn-yura_primary" onclick="store_adicional()">
        <i class="fa fa-fw fa-check"></i> Guardar
    </button>
</div>

<script>
    var count_tr = 1;

    function seleccionar_modulo(pos) {
        datos = {
            _token: '{{csrf_token()}}',
            ciclo: $('#new_ciclo_' + pos).val(),
            labor: $('#filtro_labor').val(),
        };
        if (datos['ciclo'] != '') {
            $('#tr_adicional_' + pos).LoadingOverlay('show');
            $.post('{{url('ingreso_labores/seleccionar_modulo')}}', datos, function (retorno) {
                $('#td_variedad_' + pos).html(retorno.variedad);
                $('#td_poda_siembra_' + pos).html(retorno.poda_siembra);
                $('#td_fecha_inicio_' + pos).html(retorno.fecha_inicio);
                $('#td_dias_' + pos).html(retorno.dias_ciclo);
                $('#new_labor_' + pos).html('');
                for (i = 0; i < retorno.aplicaciones.length; i++) {
                    $('#new_labor_' + pos).append('<option value="' + retorno.aplicaciones[i].id_aplicacion + '">' +
                        retorno.aplicaciones[i].nombre + '</option>');
                }
                $('#new_labor_' + pos).prop('readonly', false);
                seleccionar_labor(pos);
            }, 'json').fail(function (retorno) {
                console.log(retorno);
                alerta_errores(retorno.responseText);
            }).always(function () {
                $('#tr_adicional_' + pos).LoadingOverlay('hide');
            });
        }
    }

    function seleccionar_labor(pos) {
        datos = {
            _token: '{{csrf_token()}}',
            ciclo: $('#new_ciclo_' + pos).val(),
            app: $('#new_labor_' + pos).val(),
        };
        if (datos['ciclo'] != '' && datos['app'] != '') {
            $('#tr_adicional_' + pos).LoadingOverlay('show');
            $.post('{{url('ingreso_labores/seleccionar_labor')}}', datos, function (retorno) {
                $('#new_repeticion_' + pos).val(retorno.repeticion);
                $('#new_plantas_' + pos).val(retorno.plantas);
                $('#new_fecha_' + pos).prop('readonly', false);
                $('#new_repeticion_' + pos).prop('readonly', false);
                $('#new_plantas_' + pos).prop('readonly', false);
                $('#new_horas_dia_' + pos).prop('readonly', false);
                $('#new_hombres_dia_' + pos).prop('readonly', false);
            }, 'json').fail(function (retorno) {
                console.log(retorno);
                alerta_errores(retorno.responseText);
            }).always(function () {
                $('#tr_adicional_' + pos).LoadingOverlay('hide');
            });
        }
    }

    function store_adicional() {
        data = [];
        for (i = 1; i <= count_tr; i++) {
            if ($('#new_ciclo_' + i).val() != '') {
                detalles = [];
                ids_mano_obras = $('.new_ids_mano_obras');
                for (y = 0; y < ids_mano_obras.length; y++) {
                    id_mo = ids_mano_obras[y].value;
                    dosis = $('#new_dosis_' + id_mo + '_' + i).val();
                    if (dosis > 0) {
                        detalles.push({
                            mo: id_mo,
                            dosis: dosis,
                        });
                    }
                }
                if (detalles.length > 0)
                    data.push({
                        ciclo: $('#new_ciclo_' + i).val(),
                        aplicacion: $('#new_labor_' + i).val(),
                        fecha: $('#new_fecha_' + i).val(),
                        repeticion: $('#new_repeticion_' + i).val(),
                        plantas: $('#new_plantas_' + i).val(),
                        horas_dia: $('#new_horas_dia_' + i).val(),
                        hombres_dia: $('#new_hombres_dia_' + i).val(),
                        horas_necesarias: $('#new_horas_necesarias_' + i).val(),
                        detalles: detalles,
                    });
            }
        }
        if (data.length > 0) {
            datos = {
                _token: '{{csrf_token()}}',
                data: data,
                labor: $('#filtro_labor').val(),
            };
            post_jquery_m('{{url('ingreso_labores/store_adicional')}}', datos, function () {
                listar_labores();
                cerrar_modals();
            });
        }
    }

    function add_tr_adicional() {
        count_tr++;
        $('#table_add_adicional').append('<tr id="tr_adicional_' + count_tr + '">' +
            '        <th class="text-center" style="border-color: #9d9d9d">' +
            '            <select id="new_ciclo_' + count_tr + '" style="width: 100%" onchange="seleccionar_modulo(' + count_tr + ')">' +
            '                <option value="">Seleccione...</option>' +
            '                @foreach($ciclos as $c)' +
            '                    <option value="{{$c->id_ciclo}}">{{$c->modulo->nombre}}</option>' +
            '                @endforeach' +
            '            </select>' +
            '        </th>' +
            '        <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef" id="td_variedad_' + count_tr + '">' +
            '        </th>' +
            '        <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef" id="td_poda_siembra_' + count_tr + '">' +
            '        </th>' +
            '        <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef" id="td_fecha_inicio_' + count_tr + '">' +
            '        </th>' +
            '        <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef" id="td_dias_' + count_tr + '">' +
            '        </th>' +
            '        <td class="text-center" style="border-color: #9d9d9d" onchange="seleccionar_labor(' + count_tr + ')">' +
            '            <select id="new_labor_' + count_tr + '" style="width: 100%" readonly>' +
            '            </select>' +
            '        </td>' +
            '        <td class="text-center" style="border-color: #9d9d9d">' +
            '            <input type="date" id="new_fecha_' + count_tr + '" style="width: 100%" class="text-center" min="{{$semana->fecha_inicial}}"' +
            '                   max="{{$semana->fecha_final}}" readonly value="{{$semana->fecha_inicial}}">' +
            '        </td>' +
            '        <td class="text-center" style="border-color: #9d9d9d">' +
            '            <input type="number" id="new_repeticion_' + count_tr + '" style="width: 100%" class="text-center" readonly>' +
            '        </td>' +
            '        <td class="text-center" style="border-color: #9d9d9d" id="td_plantas_' + count_tr + '">' +
            '            <input type="number" id="new_plantas_' + count_tr + '" style="width: 100%" class="text-center" ' +
            '                   onchange="calcular_hombres_dia_add(' + count_tr + ')"' +
            '                   onclick="calcular_hombres_dia_add(' + count_tr + ')"' +
            '                   onkeyup="calcular_hombres_dia_add(' + count_tr + ')" readonly>' +
            '        </td>' +
            '        <td class="text-center" style="border-color: #9d9d9d">' +
            '            <input type="number" id="new_horas_dia_' + count_tr + '" style="width: 100%" class="text-center" ' +
            '                   onchange="calcular_hombres_dia_add(' + count_tr + ')"' +
            '                   onclick="calcular_hombres_dia_add(' + count_tr + ')"' +
            '                   onkeyup="calcular_hombres_dia_add(' + count_tr + ')" readonly value="0">' +
            '        </td>' +
            '       @foreach($mano_obras as $mo)' +
            '       <td class="text-center" style="border-color: #9d9d9d">' +
            '           <input type="number" id="new_dosis_{{$mo->id_mano_obra}}_' + count_tr + '" style="width: 100%;" ' +
            '               class="text-center new_dosis_' + count_tr + '" placeholder="Rend. x plantas" value="0" ' +
            '               onchange="calcular_hombres_dia_add(' + count_tr + ')"' +
            '               onclick="calcular_hombres_dia_add(' + count_tr + ')"' +
            '               onkeyup="calcular_hombres_dia_add(' + count_tr + ')">' +
            '       </td>' +
            '       @endforeach' +
            '       <td class="text-center" style="border-color: #9d9d9d">' +
            '            <input type="number" id="new_hombres_dia_' + count_tr + '" style="width: 100%" class="text-center" ' +
            '                   onchange="calcular_horas_necesarias_add(' + count_tr + ')"' +
            '                   onclick="calcular_horas_necesarias_add(' + count_tr + ')"' +
            '                   onkeyup="calcular_horas_necesarias_add(' + count_tr + ')" readonly value="0">' +
            '        </td>' +
            '        <td class="text-center" style="border-color: #9d9d9d">' +
            '            <input type="number" id="new_horas_necesarias_' + count_tr + '" style="width: 100%" class="text-center" readonly ' +
            '                   value="0">' +
            '        </td>' +
            '</tr>');
    }

    function del_tr_adicional() {
        if (count_tr > 1) {
            $('#tr_adicional_' + count_tr).remove();
            count_tr--;
        }
    }

    function calcular_hombres_dia_add(pos) {
        plantas = $('#new_plantas_' + pos).val();
        horas_dia = $('#new_horas_dia_' + pos).val();
        if ($('.new_dosis_' + pos).length > 0 && plantas > 0 && horas_dia > 0) {
            dosis = $('.new_dosis_' + pos)[0].value;
            hombres_dia = dosis > 0 && horas_dia > 0 ? Math.round((plantas / dosis) / horas_dia) : 0;
            $('#new_hombres_dia_' + pos).val(hombres_dia);
            calcular_horas_necesarias_add(pos);
        }
    }

    function calcular_horas_necesarias_add(pos) {
        plantas = $('#new_plantas_' + pos).val();
        hombres = $('#new_hombres_dia_' + pos).val();
        if ($('.new_dosis_' + pos).length > 0 && hombres > 0) {
            dosis = $('.dosis_' + pos)[0].value;
            horas_necesarias = 0;
            if (dosis > 0 && hombres > 0)
                horas_necesarias = Math.round((plantas / dosis) / hombres);
            $('#new_horas_necesarias_' + pos).val(horas_necesarias);
        }
    }
</script>