<div style="overflow-y: scroll; max-height: 420px">
    <table class="table-bordered table-striped" style="width: 100%">
        @foreach($plantas as $p)
            <tr id="tr_planta_{{$p->id_planta}}" class="mouse-hand" onclick="$('.tr_var_{{$p->id_planta}}').toggleClass('hidden')">
                <th class="text-left th_yura_green" colspan="4" style="padding-left: 5px">
                    {{$p->nombre}}
                </th>
            </tr>
            @foreach($p->variedades_activos as $v)
                <tr class="tr_var_{{$p->id_planta}} hidden" id="tr_variedad_{{$v->id_variedad}}">
                    <td class="text-left" style="padding-left: 5px; border-color: #9d9d9d">
                        {{$v->nombre}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <div class="input-group" style="width: 100%">
                            <div class="input-group-addon bg-yura_dark">
                                Desecho. enraizamiento
                            </div>
                            <input type="number" value="{{$v->desecho_enraizamiento}}" id="desecho_enraizamiento_var_{{$v->id_variedad}}"
                                   style="width: 100%" placeholder="Desecho de enraizamiento">
                        </div>
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <select id="id_contenedor_propag_{{$v->id_variedad}}" style="width: 100%">
                            <option value="">Seleccione una bandeja</option>
                            @foreach($contenedores as $cont)
                                <option value="{{$cont->id_contenedor_propag}}"
                                        {{$v->id_contenedor_propag == $cont->id_contenedor_propag ? 'selected' : ''}}>
                                    {{$cont->nombre}}
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <div class="btn-group">
                            <button type="button" class="btn btn-xs btn-yura_primary" onclick="actualizar_variedad('{{$v->id_variedad}}')">
                                <i class="fa fa-fw fa-save"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            @endforeach
        @endforeach
    </table>
</div>

<script>
    function actualizar_variedad(id_var) {
        datos = {
            _token: '{{csrf_token()}}',
            id_variedad: id_var,
            des_enr: $('desecho_enraizamiento_var_' + id_var).val(),
            cont_propag: $('#id_contenedor_propag_' + id_var).val(),
        };
        $('#tr_variedad_' + id_var).LoadingOverlay('show');
        $.post('{{url('propag_config/actualizar_variedad')}}', datos, function (retorno) {
            if (retorno.success) {
                //cerrar_modals();
            } else {
                alerta(retorno.mensaje);
            }
        }, 'json').fail(function (retorno) {
            console.log(retorno);
            alerta_errores(retorno.responseText);
        }).always(function () {
            $('#tr_variedad_' + id_var).LoadingOverlay('hide');
        });
    }
</script>