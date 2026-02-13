<div style="overflow-y: scroll; max-height: 450px">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d; border-radius: 18px 18px 18px 18px"
           id="table_listado_ingresos_bqt">
        <thead>
        <tr id="tr_fija_top_0">
            <th class="text-center th_yura_green" style="border-radius: 18px 0 0 0">
                Finca
            </th>
            <th class="text-center th_yura_green">
                Variedad
            </th>
            <th class="text-center th_yura_green">
                Tipo
            </th>
            <th class="text-center th_yura_green" style="width: 60px">
                Tallos bqt
            </th>
            <th class="text-center th_yura_green" style="width: 60px">
                Tallos Export.
            </th>
            <th class="text-center th_yura_green" style="width: 35px">
                Precio
            </th>
            <th class="text-center th_yura_green" style="border-radius: 0 18px 0 0; width: 60px">
            </th>
        </tr>
        </thead>
        <tbody>
        @php
            $total_tallos = 0;
            $total_tallos_export = 0;
        @endphp
        @foreach($listado as $item)
            @php
                $total_tallos += $item->tallos;
                $total_tallos_export += $item->exportada;
            @endphp
            <tr id="tr_listado_{{$item->id_bouquetera}}">
                <td class="text-center" style="border-color: #9d9d9d">
                    {{$item->id_empresa > 0 ? $item->nombre_finca : 'Comprada'}}
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    {{$item->nombre_planta}}
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    {{$item->nombre_variedad}}
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <input type="number" id="edit_tallos_{{$item->id_bouquetera}}" value="{{$item->tallos}}" style="width: 100%"
                           class="text-center">
                    <span class="hidden" id="span_tallos_{{$item->id_bouquetera}}">{{$item->tallos}}</span>
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <input type="number" id="edit_exportada_{{$item->id_bouquetera}}" value="{{$item->exportada}}" style="width: 100%"
                           class="text-center">
                    <span class="hidden" id="span_exportada_{{$item->id_bouquetera}}">{{$item->exportada}}</span>
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <input type="number" id="edit_precio_{{$item->id_bouquetera}}" value="{{$item->precio}}" style="width: 100%"
                           class="text-center">
                    <span class="hidden" id="span_precio_{{$item->id_bouquetera}}">{{$item->precio}}</span>
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <div class="btn-group">
                        <button type="button" class="btn btn-xs btn-yura_primary" onclick="update_bqt('{{$item->id_bouquetera}}')">
                            <i class="fa fa-fw fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-xs btn-yura_danger" onclick="delete_bqt('{{$item->id_bouquetera}}')">
                            <i class="fa fa-fw fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        @endforeach
        </tbody>
        <tr id="tr_fija_bottom_0">
            <th class="th_yura_green" colspan="3" style="padding: 0 0 0 10px; border-radius: 0 0 0 18px">
                Totales
            </th>
            <th class="text-center th_yura_green">
                {{number_format($total_tallos)}}
            </th>
            <th class="text-center th_yura_green">
                {{number_format($total_tallos_export)}}
            </th>
            <th class="text-center th_yura_green" style="border-radius: 0 0 18px 0" colspan="2">
            </th>
        </tr>
    </table>
</div>

<script>
    function update_bqt(id) {
        datos = {
            _token: '{{csrf_token()}}',
            id: id,
            tallos: $('#edit_tallos_' + id).val(),
            precio: $('#edit_precio_' + id).val(),
            exportada: $('#edit_exportada_' + id).val(),
        };
        $('#tr_listado_' + id).LoadingOverlay('show');
        $.post('{{url('ingreso_bouquetera/update_bqt')}}', datos, function (retorno) {
            if (retorno.success) {
                $('#span_tallos_' + id).html(datos['tallos']);
                $('#span_precio_' + id).html(datos['precio']);
            } else {
                alerta(retorno.mensaje);
            }
        }, 'json').fail(function (retorno) {
            console.log(retorno);
            alerta_errores(retorno.responseText);
        }).always(function () {
            $('#tr_listado_' + id).LoadingOverlay('hide');
        });
    }

    function delete_bqt(id) {
        modal_quest('modal-quest_delete_bqt', '<div class="alert alert-info text-center">¿Desea <strong>ELIMINAR</strong> este registro?</div>',
            '<i class="fa fa-fw fa-exclamation-triangle"></i> Confirmación del sistema', true, false, '50%', function () {
                datos = {
                    _token: '{{csrf_token()}}',
                    id: id,
                };
                $('#tr_listado_' + id).LoadingOverlay('show');
                $.post('{{url('ingreso_bouquetera/delete_bqt')}}', datos, function (retorno) {
                    if (retorno.success) {
                        $('#tr_listado_' + id).remove();
                        cerrar_modals();
                    } else {
                        alerta(retorno.mensaje);
                    }
                }, 'json').fail(function (retorno) {
                    console.log(retorno);
                    alerta_errores(retorno.responseText);
                }).always(function () {
                    $('#tr_listado_' + id).LoadingOverlay('hide');
                });
            });
    }
</script>

<style>
    #table_listado_ingresos_bqt tr#tr_fija_top_0 th {
        position: sticky;
        top: 0;
        z-index: 8;
    }

    #table_listado_ingresos_bqt tr#tr_fija_bottom_0 th {
        position: sticky;
        bottom: 0;
        z-index: 8;
    }
</style>