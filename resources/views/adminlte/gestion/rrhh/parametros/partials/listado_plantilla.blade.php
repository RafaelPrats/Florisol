<table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d; border-radius: 18px 18px 0 0" id="tabla_plantillas">
    <tr>
        <th class="text-center th_yura_green" style="border-color: white; border-radius: 18px 0 0 0">Nombre</th>
        <th class="text-center th_yura_green" style="border-color: white; border-radius: 0 18px 0 0; width: 80px">
            <button type="button" class="btn btn-xs btn-yura_default" onclick="add_plantilla()">
                <i class="fa fa-fw fa-plus"></i>
            </button>
        </th>
    </tr>
    @foreach($listado as $item)
        <tr class="{{$item->estado == 0 ? 'error' : ''}}">
            <td class="text-center" style="border-color: #9d9d9d">
                <span id="span_nombre_{{$item->id_plantilla}}">{{$item->nombre}}</span>
                <input type="text" id="input_nombre_{{$item->id_plantilla}}" value="{{$item->nombre}}" class="text-center hidden" style = "width: 100%">
            </td>
            <td class="text-center" style="border-color: #9d9d9d">
                <div class="btn-group">
                    <button type="button" class="btn btn-xs btn-yura_primary" onclick="editar_plantilla('{{$item->id_plantilla}}')" 
                        id="btn_edit_plantilla_{{$item->id_plantilla}}">
                        <i class="fa fa-fw fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-xs btn-yura_primary hidden" id="btn_update_plantilla_{{$item->id_plantilla}}"
                     onclick="update_plantilla('{{$item->id_plantilla}}')">
                        <i class="fa fa-fw fa-check"></i>
                    </button>
                    <button type="button" class="btn btn-xs btn-yura_danger" title="Desactivar"
                                    onclick="actualizar_plantilla('{{$item->id_plantilla}}','{{$item->estado}}')"
                                    id="boton_cargo_{{$item->id_cargo}}">
                                <i class="fa fa-fw {{$item->estado == 1 ? 'fa-trash' : 'fa-unlock'}}" style="color: white"
                                   id="icon_plantilla_{{$item->id_plantilla}}"></i>
                            </button>
                </div>
            </td>
        </tr>
    @endforeach
</table>

<script>
    var cant_nuevos = 0;

    function editar_plantilla(id){
        $('#span_nombre_' + id).addClass('hidden');
        $('#input_nombre_' + id).removeClass('hidden');
        $('#btn_edit_plantilla_'+id).addClass('hidden');
        $('#btn_update_plantilla_'+id).removeClass('hidden');
    }

    function add_plantilla() {
        cant_nuevos++;
        $('#tabla_plantillas').append('<tr>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<input type="text" class="text-center" id="new_plantilla_' + cant_nuevos + '" style="width: 100%">' +
            '</td>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<button type="button" class="btn btn-xs btn-yura_primary" onclick="store_plantilla(' + cant_nuevos + ')">' +
            '<i class="fa fa-fw fa-save"></i>' +
            '</button>' +
            '</td>' +
            '</tr>');
    }

    function store_plantilla(num_nuevo) {
        datos = {
            _token: '{{csrf_token()}}',
            nombre: $('#new_plantilla_' + num_nuevo).val(),
        };
        post_jquery('{{url('parametros/store_plantilla')}}', datos, function () {
            listar_parametro();
        });
    }

    function update_plantilla(id){
        datos = {
            _token: '{{csrf_token()}}',
            id_plantilla: id,
            nombre: $('#input_nombre_' + id).val(),
        };
        post_jquery('{{url('parametros/editar_plantilla')}}', datos, function () {
            listar_parametro();
        });
    }
</script>