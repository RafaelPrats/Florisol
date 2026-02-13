<table class="table-bordered table-striped" style="width: 100%; border: 1px solid #9d9d9d" id="table_add_adicional">
    <tr>
        <th class="text-center th_yura_green">
            Módulo
        </th>
        <th class="text-center th_yura_green" style="padding-left: 5px; padding-right: 5px">
            Var.
        </th>
        <th class="text-center th_yura_green" style="padding-left: 5px; padding-right: 5px">
            Tipo
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
            Camas
        </th>
        <th class="text-center th_yura_green">
            Densidad/m<sup>2</sup>
        </th>
        <th class="text-center th_yura_green">
            CC x Planta
        </th>
        <th class="text-center th_yura_green">
            Litros x cama
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
        <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef" id="td_planta_1">
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
        <td class="text-center" style="border-color: #9d9d9d">
            <input type="number" id="new_camas_1" style="width: 100%" class="text-center" readonly>
        </td>
        <td class="text-center" style="border-color: #9d9d9d">
            <input type="number" id="new_densidad_1" style="width: 100%" class="text-center" readonly>
        </td>
        <td class="text-center" style="border-color: #9d9d9d">
            <input type="number" id="new_cc_x_planta_1" style="width: 100%" class="text-center" readonly 
            onchange="calcular_litros('new_densidad_1', 'new_cc_x_planta_1', 'new_litros_x_cama_1')">
        </td>
        <td class="text-center" style="border-color: #9d9d9d">
            <input type="number" id="new_litros_x_cama_1" style="width: 100%" class="text-center" readonly>
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
                $('#td_planta_' + pos).html(retorno.planta);
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
                $('#new_camas_' + pos).val(retorno.camas);
                $('#new_densidad_' + pos).val(retorno.densidad);
                $('#new_cc_x_planta_' + pos).val(retorno.cc_x_planta);
                $('#new_litros_x_cama_' + pos).val(retorno.litros_x_cama);
                $('#new_fecha_' + pos).prop('readonly', false);
                $('#new_repeticion_' + pos).prop('readonly', false);
                $('#new_camas_' + pos).prop('readonly', false);
                $('#new_densidad_' + pos).prop('readonly', true);
                $('#new_cc_x_planta_' + pos).prop('readonly', false);
                $('#new_litros_x_cama_' + pos).prop('readonly', false);
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
                data.push({
                    ciclo: $('#new_ciclo_' + i).val(),
                    aplicacion: $('#new_labor_' + i).val(),
                    fecha: $('#new_fecha_' + i).val(),
                    repeticion: $('#new_repeticion_' + i).val(),
                    camas: $('#new_camas_' + i).val(),
                    litros_x_cama: $('#new_litros_x_cama_' + i).val(),
                    cc_x_planta: $('#new_cc_x_planta_' + i).val(),
                })
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
            '        <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef" id="td_planta_' + count_tr + '">' +
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
            '        <td class="text-center" style="border-color: #9d9d9d">' +
            '            <input type="number" id="new_camas_' + count_tr + '" style="width: 100%" class="text-center" readonly>' +
            '        </td>' +
            '        <td class="text-center" style="border-color: #9d9d9d">' +
            '            <input type="number" id="new_densidad_' + count_tr + '" style="width: 100%" class="text-center" readonly>' +
            '        </td>' +
            '        <td class="text-center" style="border-color: #9d9d9d">' +
            '            <input type="number" id="new_cc_x_planta_' + count_tr + '" style="width: 100%" class="text-center" readonly>' +
            '        </td>' +
            '        <td class="text-center" style="border-color: #9d9d9d">' +
            '            <input type="number" id="new_litros_x_cama_' + count_tr + '" style="width: 100%" class="text-center" readonly>' +
            '        </td>' +
            '    </tr>');
    }

    function del_tr_adicional() {
        if (count_tr > 1) {
            $('#tr_adicional_' + count_tr).remove();
            count_tr--;
        }
    }
</script>