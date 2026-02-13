<table class="table-bordered table-striped" style="width: 100%; border: 1px solid #9d9d9d">
    <tr>
        <th class="text-center th_yura_green">
            Nombre
        </th>
        <th class="text-center th_yura_green">
        </th>
    </tr>

    <tr>
        <td class="text-center" style="border-color: #9d9d9d">
            <input type="text" class="text-center form-control" style="width: 100%" id="new_nombre_e">
        </td>
        <td class="text-center" style="border-color: #9d9d9d">
            <button class="btn btn-yura_primary" onclick="store_presentacion()">
                <i class="fa fa-fw fa-plus"></i>
            </button>
        </td>
    </tr>

    @foreach($empaques as $e)
        <tr class="{{$e->estado == 1 ? '' : 'error'}}">
            <td class="text-center" style="border-color: #9d9d9d">
                <input type="text" class="text-center" style="width: 100%; background-color: #e9ecef"
                       id="edit_nombre_e_{{$e->id_empaque}}"
                       value="{{$e->nombre}}">
            </td>
            <td class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                <div class="btn-group">
                    <button class="btn btn-yura_warning btn-xs" onclick="update_presentacion('{{$e->id_empaque}}')">
                        <i class="fa fa-fw fa-edit"></i>
                    </button>
                    <button class="btn btn-yura_danger btn-xs" onclick="cambiar_estado_presentacion('{{$e->id_empaque}}')">
                        <i class="fa fa-fw fa-{{$e->estado == 1 ? 'unlock' : 'lock'}}"></i>
                    </button>
                </div>
            </td>
        </tr>
    @endforeach
</table>