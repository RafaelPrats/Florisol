<legend style="font-size: 1em" class="text-center">Asigne las variedaes a la labor: <strong>{{$app->nombre}}</strong></legend>

<input type="hidden" id="id_aplicacion" value="{{$app->id_aplicacion}}">

<table class="table-striped table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
    <tr>
        <th class="text-center th_yura_green">
            Nombre
        </th>
        <th class="text-center th_yura_green">

        </th>
    </tr>
    @foreach($plantas as $pos_v => $var)
        <tr id="tr_variedad_{{$var->id_planta}}">
            <td class="text-center" style="border-color: #9d9d9d">
                {{$var->nombre}}
            </td>
            <td class="text-center" style="border-color: #9d9d9d">
                @php
                    $existe = false;
                @endphp
                @foreach($app_variedades as $av)
                    @php
                        if ($av->id_variedad == $var->id_planta)
                            $existe = true;
                    @endphp
                @endforeach
                <input type="checkbox" id="check_var_{{$var->id_planta}}" class="mouse-hand"
                       {{$existe ? 'checked' : ''}} onchange="seleccionar_app_variedad('{{$var->id_planta}}')">
            </td>
        </tr>
    @endforeach
</table>

<script>
    function seleccionar_app_variedad(planta) {
        datos = {
            _token: '{{csrf_token()}}',
            planta: planta,
            app: $('#id_aplicacion').val(),
        };
        $('#tr_variedad_' + planta).LoadingOverlay('show');
        $.post('{{url('aplicaciones_campo/seleccionar_app_variedad')}}', datos, function (retorno) {
            if (!retorno.success)
                alerta(retorno.mensaje);
        }, 'json').fail(function (retorno) {
            console.log(retorno);
            alerta_errores(retorno.responseText);
        }).always(function () {
            $('#tr_variedad_' + planta).LoadingOverlay('hide');
        })
    }
</script>