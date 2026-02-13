<legend style="font-size: 1em" class="text-center"><strong>Parametrizar</strong> detalle</legend>

<input type="hidden" id="id_det_app" value="{{ $det->id_detalle_aplicacion }}">

<table class="table-bordered table-striped" style="width: 100%; border: 1px solid #9d9d9d; border-radius: 18px 18px 0 0"
    id="table_parametrizar_detalle">
    <tr>
        <th class="text-center th_yura_green" style="border-radius: 18px 0 0 0; width: 20%">
            variedad
        </th>
        <th class="text-center th_yura_green {{ $det->id_mano_obra != '' ? 'hidden' : '' }}">
            Dosis
        </th>
        <th class="text-center th_yura_green {{ $det->id_producto != '' ? 'hidden' : '' }}">
            Rend. x Cama/hr
        </th>
        <th class="text-center th_yura_green" style="width: 80px">
            Unidad Medida
        </th>
        <th class="text-center th_yura_green {{ $det->id_mano_obra != '' ? 'hidden' : '' }}">
            Factor Conversión
        </th>
        <th class="text-center th_yura_green {{ $det->id_mano_obra != '' ? 'hidden' : '' }}">
            Unidad Conversión
        </th>
        <th class="text-center th_yura_green" style="border-radius: 0 18px 0 0">
        </th>
    </tr>
    <tr>
        <td class="text-center" style="border-color: #9d9d9d">
            <select id="new_variedad_par" style="width: 100%">
                <option value="T">Todas</option>
                @foreach ($variedades as $var)
                    <option value="{{ $var->id_variedad }}">{{ $var->nombre }}</option>
                @endforeach
            </select>
        </td>
        <td class="text-center {{ $det->id_mano_obra != '' ? 'hidden' : '' }}" style="border-color: #9d9d9d">
            <input type="number" class="text-center" id="new_dosis_par" style="width: 100%" placeholder="Dosis*"
                min="0">
        </td>
        <td class="text-center {{ $det->id_producto != '' ? 'hidden' : '' }}" style="border-color: #9d9d9d">
            <input type="number" class="text-center" id="new_cantidad_mo_par" style="width: 100%"
                placeholder="Rend. x Cama/hr*" min="0">
        </td>
        <td class="text-center" style="border-color: #9d9d9d">
            <select id="new_unidad_medida_par" style="width: 100%">
                <option value="">Ninguna</option>
                @foreach ($unidades_medida as $u)
                    <option value="{{ $u->id_unidad_medida }}">{{ $u->siglas }}</option>
                @endforeach
            </select>
        </td>
        <td class="text-center {{ $det->id_mano_obra != '' ? 'hidden' : '' }}" style="border-color: #9d9d9d">
            <input type="number" class="text-center" id="new_factor_conversion_par" style="width: 100%"
                placeholder="Conversión" min="0">
        </td>
        <td class="text-center {{ $det->id_mano_obra != '' ? 'hidden' : '' }}" style="border-color: #9d9d9d">
            <select id="new_unidad_conversion_par" style="width: 100%">
                <option value="">Ninguna</option>
                @foreach ($unidades_medida as $u)
                    <option value="{{ $u->id_unidad_medida }}">{{ $u->siglas }}</option>
                @endforeach
            </select>
        </td>
        <td class="text-center" style="border-color: #9d9d9d">
            <button type="button" class="btn btn-yura_primary btn-xs" onclick="store_parametro()">
                <i class="fa fa-fw fa-save"></i>
            </button>
        </td>
    </tr>
    @foreach ($parametros as $pos_p => $par)
        <tr id="tr_par_det_{{ $par->id_parametro_detalle_aplicacion }}">
            <td class="text-left" style="border-color: #9d9d9d; padding-left: 5px">
                {{ $par->variedad->nombre }}
            </td>
            <td class="text-center {{ $det->id_mano_obra != '' ? 'hidden' : '' }}" style="border-color: #9d9d9d">
                {{ $par->dosis }}
            </td>
            <td class="text-center {{ $det->id_producto != '' ? 'hidden' : '' }}" style="border-color: #9d9d9d">
                {{ $par->cantidad_mo }}
            </td>
            <td class="text-center" style="border-color: #9d9d9d">
                {{ $par->unidad_medida != '' ? $par->unidad_medida->siglas : '' }}
            </td>
            <td class="text-center {{ $det->id_mano_obra != '' ? 'hidden' : '' }}" style="border-color: #9d9d9d">
                {{ $par->factor_conversion }}
            </td>
            <td class="text-center {{ $det->id_mano_obra != '' ? 'hidden' : '' }}" style="border-color: #9d9d9d">
                {{ $par->unidad_conversion != '' ? $par->unidad_conversion->siglas : '' }}
            </td>
            <td class="text-center" style="border-color: #9d9d9d">
                <button type="button" class="btn btn-yura_danger btn-xs"
                    onclick="delete_par('{{ $par->id_parametro_detalle_aplicacion }}')">
                    <i class="fa fa-fw fa-trash"></i>
                </button>
            </td>
        </tr>
    @endforeach
</table>

<script>
    function store_parametro() {
        datos = {
            _token: '{{ csrf_token() }}',
            id_det: $('#id_det_app').val(),
            variedad: $('#new_variedad_par').val(),
            dosis: $('#new_dosis_par').val() != '' ? $('#new_dosis_par').val() : 0,
            cantidad_mo: $('#new_cantidad_mo_par').val(),
            unidad_medida: $('#new_unidad_medida_par').val(),
            factor_conversion: $('#new_factor_conversion_par').val(),
            unidad_conversion: $('#new_unidad_conversion_par').val(),
            planta: $('#filtro_planta').val(),
        };
        $.LoadingOverlay('show');
        $.post('{{ url('aplicaciones_campo/store_parametro') }}', datos, function(retorno) {
            if (retorno.success) {
                for (i = 0; i < retorno.model.length; i++)
                    $('#table_parametrizar_detalle').append('<tr id="tr_par_det_' + retorno.model[i].id_par +
                        '">' +
                        '<td class="text-left" style="border-color: #9d9d9d; padding-left: 5px">' +
                        retorno.model[i].variedad +
                        '</td>' +
                        '<td class="text-center {{ $det->id_mano_obra != '' ? 'hidden' : '' }}" style="border-color: #9d9d9d">' +
                        retorno.model[i].dosis +
                        '</td>' +
                        '<td class="text-center {{ $det->id_producto != '' ? 'hidden' : '' }}" style="border-color: #9d9d9d">' +
                        retorno.model[i].cantidad_mo +
                        '</td>' +
                        '<td class="text-center" style="border-color: #9d9d9d">' +
                        retorno.model[i].unidad_medida +
                        '</td>' +
                        '<td class="text-center {{ $det->id_mano_obra != '' ? 'hidden' : '' }}" style="border-color: #9d9d9d">' +
                        retorno.model[i].factor_conversion +
                        '</td>' +
                        '<td class="text-center {{ $det->id_mano_obra != '' ? 'hidden' : '' }}" style="border-color: #9d9d9d">' +
                        retorno.model[i].unidad_conversion +
                        '</td>' +
                        '<td class="text-center" style="border-color: #9d9d9d">' +
                        '<div class="btn-group">' +
                        '<button type="button" class="btn btn-yura_danger btn-xs" onclick="delete_par(' +
                        retorno.model[i].id_par + ')" ' +
                        'title="Eliminar">' +
                        '<i class="fa fa-fw fa-trash"></i>' +
                        '</button>' +
                        '</div>' +
                        '</td>' +
                        '</tr>');



            } else {
                alerta(retorno.mensaje);
            }
        }, 'json').fail(function(retorno) {
            console.log(retorno);
            alerta_errores(retorno.responseText);
        }).always(function() {
            $.LoadingOverlay('hide');
        });
    }

    function delete_par(id_par) {
        datos = {
            _token: '{{ csrf_token() }}',
            id_par: id_par,
        };
        $('#tr_par_det_' + id_par).LoadingOverlay('show');
        $.post('{{ url('aplicaciones_campo/delete_par') }}', datos, function(retorno) {
            if (retorno.success) {
                $('#tr_par_det_' + id_par).remove();
            } else {
                alerta(retorno.mensaje);
            }
        }, 'json').fail(function(retorno) {
            console.log(retorno);
            alerta_errores(retorno.responseText);
        }).always(function() {
            $('#tr_par_det_' + id_par).LoadingOverlay('hide');
        })
    }
</script>
