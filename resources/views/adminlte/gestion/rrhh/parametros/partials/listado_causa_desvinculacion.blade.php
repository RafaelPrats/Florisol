<table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d; border-radius: 18px 18px 0 0" id="tabla_causa_desvinculacion">
    <tr>
        <th class="text-center th_yura_green" style="border-color: white; border-radius: 18px 0 0 0">Nombre</th>
        <th class="text-center th_yura_green" style="border-color: white; border-radius: 0 18px 0 0; width: 80px">
            <button type="button" class="btn btn-xs btn-yura_default" onclick="add_causa_desvinculacion()">
                <i class="fa fa-fw fa-plus"></i>
            </button>
        </th>
    </tr>
    @foreach($listado as $item)
        <tr class="{{$item->estado == 0 ? 'error' : ''}}">
            <td class="text-center" style="border-color: #9d9d9d">
                <span id="span_nombre_{{$item->id_causa_desvinculacion}}">{{$item->nombre}}</span>
                <input type="text" id="input_nombre_{{$item->id_causa_desvinculacion}}" value="{{$item->nombre}}" class="text-center hidden" style = "width: 100%">
            </td>
            <td class="text-center" style="border-color: #9d9d9d">
                <div class="btn-group">
                    <button type="button" class="btn btn-xs btn-yura_primary" onclick="editar_causa_desvinculacion('{{$item->id_causa_desvinculacion}}')" 
                        id="btn_edit_causa_desvinculacion_{{$item->id_causa_desvinculacion}}">
                        <i class="fa fa-fw fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-xs btn-yura_primary hidden" id="btn_update_causa_desvinculacion_{{$item->id_causa_desvinculacion}}"
                     onclick="update_causa_desvinculacion('{{$item->id_causa_desvinculacion}}')">
                        <i class="fa fa-fw fa-check"></i>
                    </button>
                    <button type="button" class="btn btn-xs btn-yura_danger" title="Desactivar"
                                    onclick="actualizar_causa_desvinculacion('{{$item->id_causa_desvinculacion}}','{{$item->estado}}')"
                                    id="boton_cargo_{{$item->id_cargo}}">
                                <i class="fa fa-fw {{$item->estado == 1 ? 'fa-trash' : 'fa-unlock'}}" style="color: white"
                                   id="icon_causa_desvinculacion_{{$item->id_causa_desvinculacion}}"></i>
                            </button>
                </div>
            </td>
        </tr>
    @endforeach

</table>


<script>
    var cant_nuevos = 0;

    function editar_causa_desvinculacion(id){
        $('#span_nombre_' + id).addClass('hidden');
        $('#input_nombre_' + id).removeClass('hidden');
        $('#btn_edit_causa_desvinculacion_'+id).addClass('hidden');
        $('#btn_update_causa_desvinculacion_'+id).removeClass('hidden');
    }

    function add_causa_desvinculacion() {
        cant_nuevos++;
        $('#tabla_causa_desvinculacion').append('<tr>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<input type="text" class="text-center" id="new_causa_desvinculacion_' + cant_nuevos + '" style="width: 100%">' +
            '</td>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<button type="button" class="btn btn-xs btn-yura_primary" onclick="store_causa_desvinculacion(' + cant_nuevos + ')">' +
            '<i class="fa fa-fw fa-save"></i>' +
            '</button>' +
            '</td>' +
            '</tr>');
    }

    function store_causa_desvinculacion(num_nuevo) {
        datos = {
            _token: '{{csrf_token()}}',
            nombre: $('#new_causa_desvinculacion_' + num_nuevo).val(),
        };
        post_jquery('{{url('parametros/store_causa_desvinculacion')}}', datos, function () {
            listar_parametro();
        });
    }

    function update_causa_desvinculacion(id){
        datos = {
            _token: '{{csrf_token()}}',
            id_causa_desvinculacion: id,
            nombre: $('#input_nombre_' + id).val(),
        };
        post_jquery('{{url('parametros/editar_causa_desvinculacion')}}', datos, function () {
            listar_parametro();
        });
    }
</script>
