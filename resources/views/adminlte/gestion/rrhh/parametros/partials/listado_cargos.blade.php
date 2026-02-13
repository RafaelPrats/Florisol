<table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d; border-radius: 18px 18px 0 0" id="tabla_cargos">
    <tr>
        <th class="text-center th_yura_green" style="border-color: white; border-radius: 18px 0 0 0">Nombre</th>
        <th class="text-center th_yura_green" style="border-color: white; border-radius: 0 18px 0 0; width: 80px">
            <button type="button" class="btn btn-xs btn-yura_default" onclick="add_carg()">
                <i class="fa fa-fw fa-plus"></i>
            </button>
        </th>
    </tr>

    @foreach($listado as $item)
        <tr class="{{$item->estado == 0 ? 'error' : ''}}">
            <td class="text-center" style="border-color: #9d9d9d">
                <span id="span_nombre_{{$item->id_cargo}}">{{$item->nombre}}</span>
                <input type="text" id="input_nombre_{{$item->id_cargo}}" value="{{$item->nombre}}" class="text-center hidden" style = "width: 100%">
            </td>
            <td class="text-center" style="border-color: #9d9d9d">
                <div class="btn-group">
                    <button type="button" class="btn btn-xs btn-yura_primary" onclick="editar_cargo('{{$item->id_cargo}}')" 
                        id="btn_edit_cargo_{{$item->id_cargo}}">
                        <i class="fa fa-fw fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-xs btn-yura_primary hidden" id="btn_update_cargo_{{$item->id_cargo}}"
                     onclick="update_cargo('{{$item->id_cargo}}')">
                        <i class="fa fa-fw fa-check"></i>
                    </button>
                    <button type="button" class="btn btn-xs btn-yura_danger" title="Desactivar"
                                    onclick="actualizar_cargo('{{$item->id_cargo}}','{{$item->estado}}')"
                                    id="boton_cargo_{{$item->id_cargo}}">
                                <i class="fa fa-fw {{$item->estado == 1 ? 'fa-trash' : 'fa-unlock'}}" style="color: white"
                                   id="icon_cargo_{{$item->id_cargo}}"></i>
                            </button>
                </div>
            </td>
        </tr>
    @endforeach
</table>


<script>
    var cant_nuevos = 0;
  

    function editar_cargo(id){
        $('#span_nombre_' + id).addClass('hidden');
        $('#input_nombre_' + id).removeClass('hidden');
        $('#btn_edit_cargo_'+id).addClass('hidden');
        $('#btn_update_cargo_'+id).removeClass('hidden');
    }

    
    function store_cargo(num_nuevo) {
        datos = {
            _token: '{{csrf_token()}}',
            nombre: $('#new_cargo_' + num_nuevo).val(),
        };
        post_jquery('{{url('parametros/store_cargo')}}', datos, function () {
            listar_parametro();
        });
    }

    function update_cargo(id){
        datos = {
            _token: '{{csrf_token()}}',
            id_cargo: id,
            nombre: $('#input_nombre_' + id).val(),
        };
        post_jquery('{{url('parametros/editar_cargo')}}', datos, function () {
            listar_parametro();
        });
    }
</script>