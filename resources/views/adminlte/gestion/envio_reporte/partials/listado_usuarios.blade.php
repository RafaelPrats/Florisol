<input type="hidden" id="reporte_seleccionado" value="{{$envio_reporte->id_envio_reporte}}">
<div style="overflow-y: scroll; max-height: 450px; width: 100%">
    <table class="table-striped table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
        <tr>
            <th class="text-center th_yura_green" colspan="3">
                Usuarios
            </th>
        </tr>

        @foreach($usuarios as $u)
            <input type="hidden" class="ids_usuario" value="{{$u->id_usuario}}">
            <tr id="tr_usuario_{{$u->id_usuario}}">
                <th class="text-left" style="border-color: #9d9d9d; padding-left: 10px">
                    {{$u->nombre_completo}}
                </th>
                <th class="text-left" style="border-color: #9d9d9d; padding-left: 10px">
                    {{$u->correo}}
                </th>
                <td class="text-center" style="border-color: #9d9d9d">
                    <input type="checkbox" class="mouse-hand" onchange="seleccionar_usuario('{{$u->id_usuario}}')"
                           id="check_usuario_{{$u->id_usuario}}" {{in_array($u->id_usuario, $usuarios_envio) ? 'checked' : ''}}>
                </td>
            </tr>
        @endforeach
    </table>
</div>

<script>
    function seleccionar_usuario(id) {
        datos = {
            _token: '{{csrf_token()}}',
            usuario: id,
            reporte: $('#reporte_seleccionado').val(),
        };
        $('#tr_usuario_' + id).LoadingOverlay('show');
        $.post('envio_reporte/seleccionar_usuario', datos, function (retorno) {

        }, 'json').fail(function (retorno) {
            console.log(retorno);
            alerta_errores(retorno.responseText);
        }).always(function () {
            $('#tr_usuario_' + id).LoadingOverlay('hide');
        });
    }
</script>