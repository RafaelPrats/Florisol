<div class="nav-tabs-custom">
    <ul class="nav nav-pills nav-justified">
        <li class="{{count($mezclas) == 0 ? 'active' : ''}}">
            <a href="#tab-nueva_mezcla" class="border-radius_18" data-toggle="tab" aria-expanded="true">
                <i class="fa fa-fw fa-plus"></i> Nueva
            </a>
        </li>
        @foreach($mezclas as $pos => $m)
            <li class="{{$pos == 0 ? 'active' : ''}}">
                <a href="#tab-mezcla_{{$m->id_aplicacion_mezcla}}" class="border-radius_18" data-toggle="tab" aria-expanded="true">
                    {{$m->nombre}}
                </a>
            </li>
        @endforeach
    </ul>
    <div class="tab-content">
        <div class="chart tab-pane {{count($mezclas) == 0 ? 'active' : ''}}" id="tab-nueva_mezcla" style="position: relative">
            <input type="hidden" id="id_aplicacion" value="{{$app->id_aplicacion_matriz}}">
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
                </tr>
                <tr>
                    <td class="text-center" style="border-color: #9d9d9d" rowspan="2">
                        <input type="text" style="width: 100%" class="form-control text-center" placeholder="Nombre" required id="new_nombre">
                    </td>
                    <td class="text-center bg-yura_dark" style="border-color: #9d9d9d">
                        SIEMBRAS
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="number" style="width: 100%" class="text-center" placeholder="Litros" required
                               id="new_litro_x_cama">
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="text" style="width: 100%" class="text-center" placeholder="Repeticiones" required
                               id="new_repeticiones" onkeyup="formatear_repeticiones('new_repeticiones')" title="Separar por espacio">
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="text" style="width: 100%" class="text-center" placeholder="Litros x Repetición" required
                               id="new_litros_x_repeticiones" onkeyup="formatear_repeticiones('new_litros_x_repeticiones')"
                               title="Separar por espacio">
                    </td>
                </tr>
                <tr>
                    <td class="text-center bg-yura_dark" style="border-color: #9d9d9d">
                        PODAS
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="number" style="width: 100%" class="text-center" placeholder="Litros" required
                               id="new_litro_x_cama_poda">
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="text" style="width: 100%" class="text-center" placeholder="Repeticiones" required
                               id="new_repeticiones_poda" onkeyup="formatear_repeticiones('new_repeticiones_poda')" title="Separar por espacio">
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="text" style="width: 100%" class="text-center" placeholder="Litros x Repetición" required
                               id="new_litros_x_repeticiones_poda" onkeyup="formatear_repeticiones('new_litros_x_repeticiones_poda')"
                               title="Separar por espacio">
                    </td>
                </tr>
            </table>
            <div style="margin-top: 5px" class="text-center">
                <button type="button" class="btn btn-yura_primary" onclick="store_mezcla()">
                    <i class="fa fa-fw fa-save"></i> Guardar
                </button>
            </div>
        </div>
        @foreach($mezclas as $pos => $m)
            <div class="chart tab-pane {{$pos == 0 ? 'active' : ''}}" id="tab-mezcla_{{$m->id_aplicacion_mezcla}}" style="position: relative">
                <input type="hidden" id="id_mezcla_{{$m->id_aplicacion_mezcla}}">
                @include('adminlte.gestion.campo.aplicaciones.forms.detalles')
            </div>
        @endforeach
    </div>
</div>

<script>
    function store_mezcla() {
        datos = {
            _token: '{{csrf_token()}}',
            app: $('#id_aplicacion').val(),
            nombre: $('#new_nombre').val(),
            litro_x_cama: $('#new_litro_x_cama').val(),
            repeticiones: $('#new_repeticiones').val(),
            litros_x_repeticiones: $('#new_litros_x_repeticiones').val(),
            litro_x_cama_poda: $('#new_litro_x_cama_poda').val(),
            repeticiones_poda: $('#new_repeticiones_poda').val(),
            litros_x_repeticiones_poda: $('#new_litros_x_repeticiones_poda').val(),
        };
        if (datos['nombre'] == '')
            alerta('<div class="alert alert-warning text-center">El nombre de la mezcla es obligatorio</div>');
        if (datos['litro_x_cama'] == '')
            alerta('<div class="alert alert-warning text-center">Los litros de la mezcla son obligatorios</div>');
        if (datos['repeticiones'] == '')
            alerta('<div class="alert alert-warning text-center">Las repeticiones de la mezcla son obligatoriss</div>');
        if (datos['litros_x_repeticiones'] == '')
            alerta('<div class="alert alert-warning text-center">Los litros por repeticiones son obligatorios</div>');
        post_jquery_m('{{url('aplicaciones_campo/store_mezcla')}}', datos, function () {
            cerrar_modals();
            mezclas_app(datos['app']);
        });
    }

    function update_mezcla(mezcla) {
        datos = {
            _token: '{{csrf_token()}}',
            mezcla: mezcla,
            nombre: $('#edit_nombre_' + mezcla).val(),
            litro_x_cama: $('#edit_litro_x_cama_' + mezcla).val(),
            repeticiones: $('#edit_repeticiones_' + mezcla).val(),
            litros_x_repeticiones: $('#edit_litros_x_repeticiones_' + mezcla).val(),
            litro_x_cama_poda: $('#edit_litro_x_cama_poda_' + mezcla).val(),
            repeticiones_poda: $('#edit_repeticiones_poda_' + mezcla).val(),
            litros_x_repeticiones_poda: $('#edit_litros_x_repeticiones_poda_' + mezcla).val(),
        };
        if (datos['nombre'] == '')
            alerta('<div class="alert alert-warning text-center">El nombre de la mezcla es obligatorio</div>');
        if (datos['litro_x_cama'] == '')
            alerta('<div class="alert alert-warning text-center">Los litros de la mezcla son obligatorios</div>');
        if (datos['repeticiones'] == '')
            alerta('<div class="alert alert-warning text-center">Las repeticiones de la mezcla son obligatorias</div>');
        if (datos['litros_x_repeticiones'] == '')
            alerta('<div class="alert alert-warning text-center">Los litros por repeticiones son obligatorios</div>');
        post_jquery_m('{{url('aplicaciones_campo/update_mezcla')}}', datos, function () {
            cerrar_modals();
            mezclas_app($('#id_aplicacion').val());
        });
    }

    function delete_mezcla(mezcla, app) {
        modal_quest('modal_quest-delete_mezcla', '<div class="alert alert-info text-center">¿Desea <strong>ELIMINAR</strong> la mezcla?</div>',
            '<i class="fa fa-fw fa-exclamation-triangle"></i> Mensaje de confirmación', true, false, '50%', function () {
                datos = {
                    _token: '{{csrf_token()}}',
                    mezcla: mezcla,
                };
                post_jquery_m('{{url('aplicaciones_campo/delete_mezcla')}}', datos, function () {
                    cerrar_modals();
                    mezclas_app(app);
                });
            });
    }

    function formatear_repeticiones(id) {
        texto = $('#' + id).val();
        $('#' + id).val(texto.replace(' ', '-'));
    }
</script>