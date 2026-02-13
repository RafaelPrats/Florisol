<table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d; border-radius: 18px 18px 0 0" id="tabla_profesion">
    <tr>
        <th class="text-center th_yura_green" style="border-color: white; border-radius: 18px 0 0 0">Nombre</th>
        <th class="text-center th_yura_green" style="border-color: white; border-radius: 0 18px 0 0; width: 80px">
            <button type="button" class="btn btn-xs btn-yura_default" onclick="add_profesion()">
                <i class="fa fa-fw fa-plus"></i>
            </button>
        </th>
    </tr>
    @foreach($listado as $item)
        <tr class="{{$item->estado == 0 ? 'error' : ''}}">
            <td class="text-center" style="border-color: #9d9d9d">
                <span id="span_nombre_{{$item->id_profesion}}">{{$item->nombre}}</span>
                <input type="text" id="input_nombre_{{$item->id_profesion}}" value="{{$item->nombre}}" class="text-center hidden" style = "width: 100%">
            </td>
            <td class="text-center" style="border-color: #9d9d9d">
                <div class="btn-group">
                    <button type="button" class="btn btn-xs btn-yura_primary" onclick="editar_profesion('{{$item->id_profesion}}')" 
                        id="btn_edit_profesion_{{$item->id_profesion}}">
                        <i class="fa fa-fw fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-xs btn-yura_primary hidden" id="btn_update_profesion_{{$item->id_profesion}}"
                     onclick="update_profesion('{{$item->id_profesion}}')">
                        <i class="fa fa-fw fa-check"></i>
                    </button>
                    <button type="button" class="btn btn-xs btn-yura_danger" title="Desactivar"
                                    onclick="actualizar_profesion('{{$item->id_profesion}}','{{$item->estado}}')"
                                    id="boton_cargo_{{$item->id_cargo}}">
                                <i class="fa fa-fw {{$item->estado == 1 ? 'fa-trash' : 'fa-unlock'}}" style="color: white"
                                   id="icon_profesion_{{$item->id_profesion}}"></i>
                            </button>
                </div>
            </td>
        </tr>
    @endforeach


</table>

<!--<script>
    var cant_nuevos = 0;

    function add_profesion() {
        cant_nuevos++;
        $('#tabla_profesion').append('<tr>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<input type="text" class="text-center" id="new_profesion_' + cant_nuevos + '" style="width: 100%">' +
            '</td>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<button type="button" class="btn btn-xs btn-yura_primary" onclick="store_profesion(' + cant_nuevos + ')">' +
            '<i class="fa fa-fw fa-save"></i>' +
            '</button>' +
            '</td>' +
            '</tr>');
    }

    function store_profesion(num_nuevo) {
        datos = {
            _token: '{{csrf_token()}}',
            nombre: $('#new_profesion_' + num_nuevo).val(),
        };
        post_jquery('{{url('parametros/store_profesion')}}', datos, function () {
            listar_parametro();
        });
    }
</script>-->

<script>
    var cant_nuevos = 0;

    function editar_profesion(id){
        $('#span_nombre_' + id).addClass('hidden');
        $('#input_nombre_' + id).removeClass('hidden');
        $('#btn_edit_profesion_'+id).addClass('hidden');
        $('#btn_update_profesion_'+id).removeClass('hidden');
    }

    function add_profesion() {
        cant_nuevos++;
        $('#tabla_profesion').append('<tr>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<input type="text" class="text-center" id="new_profesion_' + cant_nuevos + '" style="width: 100%">' +
            '</td>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<button type="button" class="btn btn-xs btn-yura_primary" onclick="store_profesion(' + cant_nuevos + ')">' +
            '<i class="fa fa-fw fa-save"></i>' +
            '</button>' +
            '</td>' +
            '</tr>');
    }

    function store_profesion(num_nuevo) {
        datos = {
            _token: '{{csrf_token()}}',
            nombre: $('#new_profesion_' + num_nuevo).val(),
        };
        post_jquery('{{url('parametros/store_profesion')}}', datos, function () {
            listar_parametro();
        });
    }

    function update_profesion(id){
        datos = {
            _token: '{{csrf_token()}}',
            id_profesion: id,
            nombre: $('#input_nombre_' + id).val(),
        };
        post_jquery('{{url('parametros/editar_profesion')}}', datos, function () {
            listar_parametro();
        });
    }
