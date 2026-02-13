<table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d; border-radius: 18px 18px 0 0" id="tabla_grupo_internos">
    <tr>
        <th class="text-center th_yura_green" style="border-color: white; border-radius: 18px 0 0 0">Nombre</th>
        <th class="text-center th_yura_green" style="border-color: white; border-radius: 0 18px 0 0; width: 80px">
            <button type="button" class="btn btn-xs btn-yura_default" onclick="add_grupo_interno()">
                <i class="fa fa-fw fa-plus"></i>
            </button>
        </th>
    </tr>
    @foreach($listado as $item)
        <tr class="{{$item->estado == 0 ? 'error' : ''}}">
            <td class="text-center" style="border-color: #9d9d9d">
                <span id="span_nombre_{{$item->id_grupo_interno}}">{{$item->nombre}}</span>
                <input type="text" id="input_nombre_{{$item->id_grupo_interno}}" value="{{$item->nombre}}" class="text-center hidden" style = "width: 100%">
            </td>
            <td class="text-center" style="border-color: #9d9d9d">
                <div class="btn-group">
                    <button type="button" class="btn btn-xs btn-yura_primary" onclick="editar_grupo_interno('{{$item->id_grupo_interno}}')" 
                        id="btn_edit_grupo_interno_{{$item->id_grupo_interno}}">
                        <i class="fa fa-fw fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-xs btn-yura_primary hidden" id="btn_update_grupo_interno_{{$item->id_grupo_interno}}"
                     onclick="update_grupo_interno('{{$item->id_grupo_interno}}')">
                        <i class="fa fa-fw fa-check"></i>
                    </button>
                    <button type="button" class="btn btn-xs btn-yura_danger" title="Desactivar"
                                    onclick="actualizar_grupo_interno('{{$item->id_grupo_interno}}','{{$item->estado}}')"
                                    id="boton_cargo_{{$item->id_cargo}}">
                                <i class="fa fa-fw {{$item->estado == 1 ? 'fa-trash' : 'fa-unlock'}}" style="color: white"
                                   id="icon_grupo_interno_{{$item->id_grupo_interno}}"></i>
                            </button>
                </div>
            </td>
        </tr>
    @endforeach
</table>

<script>
    var cant_nuevos = 0;

    function editar_grupo_interno(id){
        $('#span_nombre_' + id).addClass('hidden');
        $('#input_nombre_' + id).removeClass('hidden');
        $('#btn_edit_grupo_interno_'+id).addClass('hidden');
        $('#btn_update_grupo_interno_'+id).removeClass('hidden');
    }

    function add_grupo_interno() {
        cant_nuevos++;
        $('#tabla_grupo_internos').append('<tr>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<input type="text" class="text-center" id="new_grupo_interno_' + cant_nuevos + '" style="width: 100%">' +
            '</td>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<button type="button" class="btn btn-xs btn-yura_primary" onclick="store_grupo_interno(' + cant_nuevos + ')">' +
            '<i class="fa fa-fw fa-save"></i>' +
            '</button>' +
            '</td>' +
            '</tr>');
    }

    function store_grupo_interno(num_nuevo) {
        datos = {
            _token: '{{csrf_token()}}',
            nombre: $('#new_grupo_interno_' + num_nuevo).val(),
        };
        post_jquery('{{url('parametros/store_grupo_interno')}}', datos, function () {
            listar_parametro();
        });
    }

    function update_grupo_interno(id){
        datos = {
            _token: '{{csrf_token()}}',
            id_grupo_interno: id,
            nombre: $('#input_nombre_' + id).val(),
        };
        post_jquery('{{url('parametros/editar_grupo_interno')}}', datos, function () {
            listar_parametro();
        });
    }
</script>
