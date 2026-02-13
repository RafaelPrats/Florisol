<table class="table-striped table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
    <tr>
        <th class="text-center th_yura_green">
            Nombre
        </th>
        <th class="text-center th_yura_green">
            Tipo
        </th>
        <th class="text-center th_yura_green">
            CC x Planta
        </th>
        <th class="text-center th_yura_green">
            Repeticiones
        </th>
        <th class="text-center th_yura_green">
            CC x Repetición
        </th>
        <th class="text-center th_yura_green">
        </th>
    </tr>
    <tr>
        <td class="text-center" style="border-color: #9d9d9d" rowspan="2">
            <input type="text" style="width: 100%" class="form-control text-center" placeholder="Nombre" required
                   id="edit_nombre_{{$m->id_aplicacion_mezcla}}" value="{{$m->nombre}}">
        </td>
        <td class="text-center bg-yura_dark" style="border-color: #9d9d9d">
            SIEMBRAS
        </td>
        <td class="text-center" style="border-color: #9d9d9d">
            <input type="number" style="width: 100%" class="text-center" placeholder="Litros" required
                   id="edit_litro_x_cama_{{$m->id_aplicacion_mezcla}}" value="{{$m->litro_x_cama}}">
        </td>
        <td class="text-center" style="border-color: #9d9d9d">
            <input type="text" style="width: 100%" class="text-center" placeholder="Repeticiones" required
                   id="edit_repeticiones_{{$m->id_aplicacion_mezcla}}" value="{{$m->repeticiones}}"
                   onkeyup="formatear_repeticiones('edit_repeticiones_{{$m->id_aplicacion_mezcla}}')"
                   title="Separar por espacio">
        </td>
        <td class="text-center" style="border-color: #9d9d9d">
            <input type="text" style="width: 100%" class="text-center" placeholder="Litros x Repetición" required
                   id="edit_litros_x_repeticiones_{{$m->id_aplicacion_mezcla}}" value="{{$m->litros_x_repeticiones}}"
                   onkeyup="formatear_repeticiones('edit_litros_x_repeticiones_{{$m->id_aplicacion_mezcla}}')"
                   title="Separar por espacio">
        </td>
        <td class="text-center" style="border-color: #9d9d9d" rowspan="2">
            <div class="btn-group">
                <button type="button" class="btn btn-xs btn-yura_primary" onclick="update_mezcla('{{$m->id_aplicacion_mezcla}}')">
                    <i class="fa fa-fw fa-pencil"></i>
                </button>
                <button type="button" class="btn btn-xs btn-yura_danger"
                        onclick="delete_mezcla('{{$m->id_aplicacion_mezcla}}', '{{$m->id_aplicacion}}')">
                    <i class="fa fa-fw fa-trash"></i>
                </button>
            </div>
        </td>
    </tr>
    <tr>
        <td class="text-center bg-yura_dark" style="border-color: #9d9d9d">
            PODAS
        </td>
        <td class="text-center" style="border-color: #9d9d9d">
            <input type="number" style="width: 100%" class="text-center" placeholder="Litros" required
                   id="edit_litro_x_cama_poda_{{$m->id_aplicacion_mezcla}}" value="{{$m->litro_x_cama_poda}}">
        </td>
        <td class="text-center" style="border-color: #9d9d9d">
            <input type="text" style="width: 100%" class="text-center" placeholder="Repeticiones" required
                   id="edit_repeticiones_poda_{{$m->id_aplicacion_mezcla}}" value="{{$m->repeticiones_poda}}"
                   onkeyup="formatear_repeticiones('edit_repeticiones_poda_{{$m->id_aplicacion_mezcla}}')"
                   title="Separar por espacio">
        </td>
        <td class="text-center" style="border-color: #9d9d9d">
            <input type="text" style="width: 100%" class="text-center" placeholder="Litros x Repetición" required
                   id="edit_litros_x_repeticiones_poda_{{$m->id_aplicacion_mezcla}}" value="{{$m->litros_x_repeticiones_poda}}"
                   onkeyup="formatear_repeticiones('edit_litros_x_repeticiones_poda_{{$m->id_aplicacion_mezcla}}')"
                   title="Separar por espacio">
        </td>
    </tr>
</table>

<legend style="font-size: 1em; margin-top: 10px" class="text-center">Detalles de la <strong>MEZCLA</strong></legend>

<table class="table-bordered table-striped" style="width: 100%; border: 1px solid #9d9d9d; border-radius: 18px 18px 0 0"
       id="table_detalles_{{$m->id_aplicacion_mezcla}}">
    <tr>
        <th class="text-center th_yura_green" style="border-radius: 18px 0 0 0">
            Insumo
        </th>
        <th class="text-center th_yura_green" style="width: 50%">
            Mano de Obra
        </th>
        <th class="text-center th_yura_green" style="border-radius: 0 18px 0 0; width: 60px">
        </th>
    </tr>
    <tr>
        <td class="text-center" style="border-color: #9d9d9d">
            <select id="new_det_insumo_{{$m->id_aplicacion_mezcla}}" style="width: 100%"
                    onchange="$('#new_det_mo_{{$m->id_aplicacion_mezcla}}').val('')">
                <option value="">Seleccione...</option>
                @foreach($insumos as $i)
                    <option value="{{$i->id_producto}}">{{$i->nombre}}</option>
                @endforeach
            </select>
        </td>
        <td class="text-center" style="border-color: #9d9d9d">
            <select id="new_det_mo_{{$m->id_aplicacion_mezcla}}" style="width: 100%"
                    onchange="$('#new_det_insumo_{{$m->id_aplicacion_mezcla}}').val('')">
                <option value="">Seleccione...</option>
                @foreach($mo as $mdo)
                    <option value="{{$mdo->id_mano_obra}}">{{$mdo->nombre}}</option>
                @endforeach
            </select>
        </td>
        <td class="text-center" style="border-color: #9d9d9d">
            <button type="button" class="btn btn-yura_primary btn-xs" onclick="store_detalle_app('{{$m->id_aplicacion_mezcla}}')">
                <i class="fa fa-fw fa-save"></i>
            </button>
        </td>
    </tr>
    @foreach($m->getModelDetalles() as $det)
        <tr id="tr_det_app_{{$det->id_detalle_aplicacion}}">
            <td class="text-center" style="border-color: #9d9d9d">
                {{$det->id_producto != '' ? $det->producto->nombre : ''}}
            </td>
            <td class="text-center" style="border-color: #9d9d9d">
                {{$det->id_mano_obra != '' ? $det->mano_obra->nombre : ''}}
            </td>
            <td class="text-center" style="border-color: #9d9d9d">
                <div class="btn-group">
                    <button type="button" class="btn btn-yura_dark btn-xs" title="Parametrizar"
                            onclick="parametrizar_det('{{$det->id_detalle_aplicacion}}')">
                        <i class="fa fa-fw fa-sitemap"></i>
                    </button>
                    <button type="button" class="btn btn-yura_danger btn-xs" onclick="delete_det_app('{{$det->id_detalle_aplicacion}}')"
                            title="Eliminar">
                        <i class="fa fa-fw fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    @endforeach
</table>

<script>
    function store_detalle_app(mezcla) {
        datos = {
            _token: '{{csrf_token()}}',
            id_app: $('#id_aplicacion').val(),
            mo: $('#new_det_mo_' + mezcla).val(),
            insumo: $('#new_det_insumo_' + mezcla).val(),
            mezcla: mezcla,
        };
        $.LoadingOverlay('show');
        $.post('{{url('aplicaciones_campo/store_detalle_app')}}', datos, function (retorno) {
            if (retorno.success) {
                $('#table_detalles_' + mezcla).append('<tr id="tr_det_app_' + retorno.model.id_det + '">' +
                    '<td class="text-center" style="border-color: #9d9d9d">' +
                    retorno.model.producto +
                    '</td>' +
                    '<td class="text-center" style="border-color: #9d9d9d">' +
                    retorno.model.mo +
                    '</td>' +
                    '<td class="text-center" style="border-color: #9d9d9d">' +
                    '<div class="btn-group">' +
                    '<button type="button" class="btn btn-yura_dark btn-xs" title="Parametrizar" ' +
                    'onclick="parametrizar_det(' + retorno.model.id_det + ')">' +
                    '<i class="fa fa-fw fa-sitemap"></i>' +
                    '</button>' +
                    '<button type="button" class="btn btn-yura_danger btn-xs" onclick="delete_det_app(' + retorno.model.id_det + ')" ' +
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

    function delete_det_app(id_det) {
        datos = {
            _token: '{{csrf_token()}}',
            id_det: id_det,
        };
        $('#tr_det_app_' + id_det).LoadingOverlay('show');
        $.post('{{url('aplicaciones_campo/delete_det_app')}}', datos, function (retorno) {
            if (retorno.success) {
                $('#tr_det_app_' + id_det).remove();
            } else {
                alerta(retorno.mensaje);
            }
        }, 'json').fail(function (retorno) {
            console.log(retorno);
            alerta_errores(retorno.responseText);
        }).always(function () {
            $('#tr_det_app_' + id_det).LoadingOverlay('hide');
        })
    }

    function parametrizar_det(id_det) {
        datos = {
            id_det: id_det,
            tipo_labor: $('#filtro_tipo').val(),
            planta: $('#filtro_planta').val(),
        };
        get_jquery('{{url('aplicaciones_campo/parametrizar_det')}}', datos, function (retorno) {
            modal_view('modal-view_parametrizar_det', retorno, '<i class="fa fa-fw fa-sitemap"></i>Parametrizar detalle', true, false, '75%');
        });
    }
</script>