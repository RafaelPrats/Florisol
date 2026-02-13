<table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d; border-radius: 18px 18px 0 0" id="tabla_estructura_organizativas">
    <tr>
        <th class="text-center th_yura_green" style="border-color: white; border-radius: 18px 0 0 0">Nombre</th>
        <th class="text-center th_yura_green" style="border-color: white; border-radius: 0 18px 0 0; width: 80px">
            <button type="button" class="btn btn-xs btn-yura_default" onclick="add_estructura_organizativa()">
                <i class="fa fa-fw fa-plus"></i>
            </button>
        </th>
    </tr>
    @foreach($listado as $item)
        <tr class="{{$item->estado == 0 ? 'error' : ''}}">
            <td class="text-center" style="border-color: #9d9d9d">
                <span id="span_nombre_{{$item->id_estructura_organizativa}}">{{$item->nombre}}</span>
                <input type="text" id="input_nombre_{{$item->id_estructura_organizativa}}" value="{{$item->nombre}}" class="text-center hidden" style = "width: 100%">
            </td>
            <td class="text-center" style="border-color: #9d9d9d">
                <div class="btn-group">
                    <button type="button" class="btn btn-xs btn-yura_primary" onclick="editar_estructura_organizativa('{{$item->id_estructura_organizativa}}')" 
                        id="btn_edit_estructura_organizativa_{{$item->id_estructura_organizativa}}">
                        <i class="fa fa-fw fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-xs btn-yura_primary hidden" id="btn_update_estructura_organizativa_{{$item->id_estructura_organizativa}}"
                     onclick="update_estructura_organizativa('{{$item->id_estructura_organizativa}}')">
                        <i class="fa fa-fw fa-check"></i>
                    </button>
                    <button type="button" class="btn btn-xs btn-yura_danger" title="Desactivar"
                                    onclick="actualizar_estructura_organizativa('{{$item->id_estructura_organizativa}}','{{$item->estado}}')"
                                    id="boton_cargo_{{$item->id_cargo}}">
                                <i class="fa fa-fw {{$item->estado == 1 ? 'fa-trash' : 'fa-unlock'}}" style="color: white"
                                   id="icon_estructura_organizativa_{{$item->id_estructura_organizativa}}"></i>
                            </button>
                </div>
            </td>
        </tr>
    @endforeach
</table>

<script>
    var cant_nuevos = 0;

    function editar_estructura_organizativa(id){
        $('#span_nombre_' + id).addClass('hidden');
        $('#input_nombre_' + id).removeClass('hidden');
        $('#btn_edit_estructura_organizativa_'+id).addClass('hidden');
        $('#btn_update_estructura_organizativa_'+id).removeClass('hidden');
    }

    function add_estructura_organizativa() {
        cant_nuevos++;
        $('#tabla_estructura_organizativas').append('<tr>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<input type="text" class="text-center" id="new_estructura_organizativa_' + cant_nuevos + '" style="width: 100%">' +
            '</td>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<button type="button" class="btn btn-xs btn-yura_primary" onclick="store_estructura_organizativa(' + cant_nuevos + ')">' +
            '<i class="fa fa-fw fa-save"></i>' +
            '</button>' +
            '</td>' +
            '</tr>');
    }

    function store_estructura_organizativa(num_nuevo) {
        datos = {
            _token: '{{csrf_token()}}',
            nombre: $('#new_estructura_organizativa_' + num_nuevo).val(),
        };
        post_jquery('{{url('parametros/store_estructura_organizativa')}}', datos, function () {
            listar_parametro();
        });
    }

    function update_estructura_organizativa(id){
        datos = {
            _token: '{{csrf_token()}}',
            id_estructura_organizativa: id,
            nombre: $('#input_nombre_' + id).val(),
        };
        post_jquery('{{url('parametros/editar_estructura_organizativa')}}', datos, function () {
            listar_parametro();
        });
    }
</script>
