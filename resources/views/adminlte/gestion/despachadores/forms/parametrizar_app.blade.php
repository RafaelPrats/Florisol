<legend style="font-size: 1em" class="text-center"><strong>Parametrizar</strong> aplicación: <strong>{{$app->nombre}}</strong></legend>

<input type="hidden" id="id_app" value="{{$app->id_aplicacion}}">

<table class="table-bordered table-striped" style="width: 100%; border: 1px solid #9d9d9d; border-radius: 18px 18px 0 0"
       id="table_parametrizar_aplicacion">
    <tr>
        <th class="text-center th_yura_green" style="border-radius: 18px 0 0 0; width: 20%">
            Campo
        </th>
        <th class="text-center th_yura_green" style="width: 20%">
            Tipo
        </th>
        <th class="text-center th_yura_green">
            Desde
        </th>
        <th class="text-center th_yura_green">
            Hasta
        </th>
        <th class="text-center th_yura_green">
            Cantidad
        </th>
        <th class="text-center th_yura_green" style="border-radius: 0 18px 0 0">
        </th>
    </tr>
    <tr>
        <td class="text-center" style="border-color: #9d9d9d">
            <select id="new_campo_par" style="width: 100%">
                <option value="dia_ini" {{$campo == 'dia_ini' ? 'selected' : ''}}>Día de inicio</option>
                <option value="semana_ini" {{$campo == 'semana_ini' ? 'selected' : ''}}>Semana de inicio</option>
                <option value="repeticiones" {{$campo == 'repeticiones' ? 'selected' : ''}}>Repeticiones</option>
                <option value="veces_x_semana" {{$campo == 'veces_x_semana' ? 'selected' : ''}}>Veces x semana</option>
            </select>
        </td>
        <td class="text-center" style="border-color: #9d9d9d">
            <select id="new_tipo_par" style="width: 100%">
                <option value="E">Estandar</option>
                <option value="T">Temperatura</option>
                <option value="D">Delta Acum. 10 días</option>
                <option value="L">Lluvia Acum. 21 días</option>
                <option value="A">Altura</option>
            </select>
        </td>
        <td class="text-center" style="border-color: #9d9d9d">
            <input type="number" class="text-center" id="new_desde_par" style="width: 100%" placeholder="Desde*">
        </td>
        <td class="text-center" style="border-color: #9d9d9d">
            <input type="number" class="text-center" id="new_hasta_par" style="width: 100%" placeholder="Hasta*">
        </td>
        <td class="text-center" style="border-color: #9d9d9d">
            <input type="number" class="text-center" id="new_valor_par" style="width: 100%" placeholder="Cantidad*">
        </td>
        <td class="text-center" style="border-color: #9d9d9d">
            <button type="button" class="btn btn-yura_primary btn-xs" onclick="store_parametro_app()">
                <i class="fa fa-fw fa-save"></i>
            </button>
        </td>
    </tr>
    @foreach($parametros as $pos_p => $par)
        <tr id="tr_par_app_{{$par->id_parametro_aplicacion}}">
            <td class="text-left" style="border-color: #9d9d9d; padding-left: 5px">
                {{$par->getCampo()}}
            </td>
            <td class="text-left" style="border-color: #9d9d9d; padding-left: 5px">
                {{$par->getTipo()}}
            </td>
            <td class="text-center" style="border-color: #9d9d9d">
                {{$par->desde}}
            </td>
            <td class="text-center" style="border-color: #9d9d9d">
                {{$par->hasta}}
            </td>
            <td class="text-center" style="border-color: #9d9d9d">
                {{$par->valor}}
            </td>
            <td class="text-center" style="border-color: #9d9d9d">
                <button type="button" class="btn btn-yura_danger btn-xs" onclick="delete_par_app('{{$par->id_parametro_aplicacion}}')">
                    <i class="fa fa-fw fa-trash"></i>
                </button>
            </td>
        </tr>
    @endforeach
</table>

<script>
    function store_parametro_app() {
        datos = {
            _token: '{{csrf_token()}}',
            id_app: $('#id_app').val(),
            campo: $('#new_campo_par').val(),
            tipo: $('#new_tipo_par').val(),
            desde: $('#new_desde_par').val() != '' ? $('#new_desde_par').val() : 0,
            hasta: $('#new_hasta_par').val() != '' ? $('#new_hasta_par').val() : 0,
            valor: $('#new_valor_par').val(),
        };
        $.LoadingOverlay('show');
        $.post('{{url('aplicaciones_campo/store_parametro_app')}}', datos, function (retorno) {
            if (retorno.success) {
                $('#table_parametrizar_aplicacion').append('<tr id="tr_par_app_' + retorno.model.id_par + '">' +
                    '<td class="text-left" style="border-color: #9d9d9d; padding-left: 5px">' +
                    retorno.model.campo +
                    '</td>' +
                    '<td class="text-left" style="border-color: #9d9d9d; padding-left: 5px">' +
                    retorno.model.tipo +
                    '</td>' +
                    '<td class="text-center" style="border-color: #9d9d9d">' +
                    retorno.model.desde +
                    '</td>' +
                    '<td class="text-center" style="border-color: #9d9d9d">' +
                    retorno.model.hasta +
                    '</td>' +
                    '<td class="text-center" style="border-color: #9d9d9d">' +
                    retorno.model.valor +
                    '</td>' +
                    '<td class="text-center" style="border-color: #9d9d9d">' +
                    '<div class="btn-group">' +
                    '<button type="button" class="btn btn-yura_danger btn-xs" onclick="delete_par_app(' + retorno.model.id_par + ')" ' +
                    'title="Eliminar">' +
                    '<i class="fa fa-fw fa-trash"></i>' +
                    '</button>' +
                    '</div>' +
                    '</td>' +
                    '</tr>');
            } else {
                alerta(retorno.mensaje);
            }
        }, 'json').fail(function (retorno) {
            console.log(retorno);
            alerta_errores(retorno.responseText);
        }).always(function () {
            $.LoadingOverlay('hide');
        });
    }

    function delete_par_app(id_par) {
        datos = {
            _token: '{{csrf_token()}}',
            id_par: id_par,
        };
        $('#tr_par_app_' + id_par).LoadingOverlay('show');
        $.post('{{url('aplicaciones_campo/delete_par_app')}}', datos, function (retorno) {
            if (retorno.success) {
                $('#tr_par_app_' + id_par).remove();
            } else {
                alerta(retorno.mensaje);
            }
        }, 'json').fail(function (retorno) {
            console.log(retorno);
            alerta_errores(retorno.responseText);
        }).always(function () {
            $('#tr_par_app_' + id_par).LoadingOverlay('hide');
        })
    }
</script>