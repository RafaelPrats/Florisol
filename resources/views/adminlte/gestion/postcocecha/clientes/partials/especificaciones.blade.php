<div style="overflow-y: scroll; max-height: 550px">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
        <tr class="tr_fija_top_0">
            <th class="text-center th_yura_green">
                Planta
            </th>
            <th class="text-center th_yura_green">
                Variedad
            </th>
            <th class="text-center th_yura_green">
                Tipo Caja
            </th>
            <th class="text-center th_yura_green">
                RxC
            </th>
            <th class="text-center th_yura_green">
                TxR
            </th>
            <th class="text-center th_yura_green">
                Longitud
            </th>
            <th class="text-center th_yura_green">
            </th>
        </tr>
        <tr id="tr_new">
            <th class="" style="border-color: #9d9d9d">
                <select id="new_planta" style="width: 100%; background-color: #eeeeee"
                    onchange="select_planta_global($(this).val(), 'new_variedad', 'div_cargar_variedades', '<option value=>Seleccione</option>')">
                    <option value="">Seleccione</option>
                    @foreach ($plantas as $item)
                        <option value="{{ $item->id_planta }}">{{ $item->nombre }}</option>
                    @endforeach
                </select>
            </th>
            <th class="" style="border-color: #9d9d9d" id="div_cargar_variedades">
                <select id="new_variedad" style="width: 100%; height: 34px; background-color: #eeeeee">
                    <option value="">Seleccione una Planta</option>
                </select>
            </th>
            <th class="" style="border-color: #9d9d9d">
                <select style="width: 100%; height: 34px; background-color: #eeeeee" id="new_tipo_caja">
                    <option value="FB">FB</option>
                    <option value="HB">HB</option>
                    <option value="QB">QB</option>
                    <option value="EB">EB</option>
                </select>
            </th>
            <th class="" style="border-color: #9d9d9d; width: 60px">
                <input type="number" class="text-center" style="width: 100%; height: 34px; background-color: #eeeeee"
                    id="new_ramos_x_caja">
            </th>
            <th class="" style="border-color: #9d9d9d; width: 60px">
                <input type="number" class="text-center" style="width: 100%; height: 34px; background-color: #eeeeee"
                    id="new_tallos_x_ramo">
            </th>
            <th class="" style="border-color: #9d9d9d; width: 90px">
                <input type="text" class="padding_lateral_5"
                    style="width: 100%; height: 34px; background-color: #eeeeee" id="new_longitud">
            </th>
            <th class="text-center" style="border-color: #9d9d9d; width: 90px">
                <button type="button" class="btn btn-yura_primary" onclick="store_especificaciones()">
                    <i class="fa fa-fw fa-save"></i> Nueva
                </button>
            </th>
        </tr>
        @foreach ($listado as $item)
            @php
                $variedad = $item->variedad;
            @endphp
            <tr>
                <td class="text-center" style="border-color: #9d9d9d">
                    <select id="edit_planta_{{ $item->id_especificaciones }}" style="width: 100%; height: 24px;"
                        onchange="select_planta_global($(this).val(), 'edit_variedad_{{ $item->id_especificaciones }}', 'div_cargar_variedades_{{ $item->id_especificaciones }}', '<option value=>Seleccione</option>')">
                        <option value="">Seleccione</option>
                        @foreach ($plantas as $pta)
                            <option value="{{ $pta->id_planta }}"
                                {{ $pta->id_planta == $variedad->id_planta ? 'selected' : '' }}>
                                {{ $pta->nombre }}
                            </option>
                        @endforeach
                    </select>
                </td>
                <td class="text-center" style="border-color: #9d9d9d"
                    id="td_cargar_variedades_{{ $item->id_especificaciones }}">
                    <select id="edit_variedad_{{ $item->id_especificaciones }}" style="width: 100%; height: 24px;">
                        <option value="{{ $item->id_variedad }}">
                            {{ $variedad->nombre }}
                        </option>
                    </select>
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <select style="width: 100%; height: 24px;" id="edit_tipo_caja_{{ $item->id_especificaciones }}">
                        <option value="FB" {{ $item->tipo_caja == 'FB' ? 'selected' : '' }}>FB</option>
                        <option value="HB" {{ $item->tipo_caja == 'HB' ? 'selected' : '' }}>HB</option>
                        <option value="QB" {{ $item->tipo_caja == 'QB' ? 'selected' : '' }}>QB</option>
                        <option value="EB" {{ $item->tipo_caja == 'EB' ? 'selected' : '' }}>EB</option>
                    </select>
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <input type="number" class="text-center" style="width: 100%; height: 24px;"
                        id="edit_ramos_x_caja_{{ $item->id_especificaciones }}" value="{{ $item->ramos_x_caja }}">
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <input type="number" class="text-center" style="width: 100%; height: 24px;"
                        id="edit_tallos_x_ramo_{{ $item->id_especificaciones }}" value="{{ $item->tallos_x_ramo }}">
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <input type="text" class="padding_lateral_5" style="width: 100%; height: 24px;"
                        id="edit_longitud_{{ $item->id_especificaciones }}" value="{{ $item->longitud }}">
                </td>
                <td class="text-center" style="border-color: #9d9d9d; width: 90px">
                    <div class="btn-group">
                        <button type="button" class="btn btn-xs btn-yura_warning"
                            onclick="update_especificaciones({{ $item->id_especificaciones }})">
                            <i class="fa fa-fw fa-save"></i>
                        </button>
                        <button type="button" class="btn btn-xs btn-yura_danger"
                            onclick="delete_especificaciones({{ $item->id_especificaciones }})">
                            <i class="fa fa-fw fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        @endforeach
    </table>
</div>

<input type="hidden" id="cliente_selected" value="{{ $id_cliente }}">

<script>
    $('#new_planta, #new_variedad').select2({
        dropdownParent: $('#div_modal-modal_view_detalle_cliente')
    });
    $('.select2-container').css('width', '100%');
    $('.select2-selection').css('height', '34px');

    function store_especificaciones() {
        datos = {
            _token: '{{ csrf_token() }}',
            cliente: $('#cliente_selected').val(),
            variedad: $('#new_variedad').val(),
            tipo_caja: $('#new_tipo_caja').val(),
            ramos_x_caja: $('#new_ramos_x_caja').val(),
            tallos_x_ramo: $('#new_tallos_x_ramo').val(),
            longitud: $('#new_longitud').val(),
        }
        if (datos['variedad'] == '' || datos['ramos_x_caja'] == '' || datos['tallos_x_ramo'] == '' || datos[
                'longitud'] == '') {
            alerta('<div class="alert alert-warning text-center">Complete los campos para continuar</div>');
            return false;
        }
        post_jquery_m('{{ url('clientes/store_especificaciones') }}', datos, function() {
            admin_especificaciones(datos['cliente']);
        });
    }

    function update_especificaciones(id) {
        datos = {
            _token: '{{ csrf_token() }}',
            id: id,
            variedad: $('#edit_variedad_' + id).val(),
            tipo_caja: $('#edit_tipo_caja_' + id).val(),
            ramos_x_caja: $('#edit_ramos_x_caja_' + id).val(),
            tallos_x_ramo: $('#edit_tallos_x_ramo_' + id).val(),
            longitud: $('#edit_longitud_' + id).val(),
        }
        if (datos['variedad'] == '' || datos['ramos_x_caja'] == '' || datos['tallos_x_ramo'] == '' || datos[
                'longitud'] == '') {
            alerta('<div class="alert alert-warning text-center">Complete los campos para continuar</div>');
            return false;
        }
        post_jquery_m('{{ url('clientes/update_especificaciones') }}', datos, function() {
        });
    }

    function delete_especificaciones(id) {
        mensaje = {
            title: '<i class="fa fa-fw fa-save"></i> Eliminar especificación',
            mensaje: '<div class="alert alert-warning text-center"><i class="fa fa-fw fa-exclamation-triangle"></i> ¿Está seguro de <b>ELIMINAR</b> esta especificación?</div>',
        };
        modal_quest('modal_delete_especificaciones', mensaje['mensaje'], mensaje['title'], true, false,
            '{{ isPC() ? '35%' : '' }}',
            function() {
                datos = {
                    _token: '{{ csrf_token() }}',
                    id: id
                };
                post_jquery_m('{{ url('clientes/delete_especificaciones') }}', datos, function() {
                    admin_especificaciones(id);
                });
            });
    }
</script>
