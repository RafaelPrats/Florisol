<table class="table-bordered table-striped" style="width: 100%; border: 1px solid #9d9d9d">
    <tr>
        <th class="text-center th_yura_green">
            Nombre
        </th>
        <th class="text-center th_yura_green">
            Factor de conversión
        </th>
        <th class="text-center th_yura_green">
            Peso
        </th>
        <th class="text-center th_yura_green">
        </th>
    </tr>

    <tr>
        <td class="text-center" style="border-color: #9d9d9d">
            <input type="text" class="text-center form-control" style="width: 100%" id="new_nombre_c">
        </td>
        <td class="text-center" style="border-color: #9d9d9d">
            <input type="number" class="text-center form-control" style="width: 100%" id="new_fa_c">
        </td>
        <td class="text-center" style="border-color: #9d9d9d">
            <input type="number" class="text-center form-control" style="width: 100%" id="new_p_c">
        </td>
        <td class="text-center" style="border-color: #9d9d9d">
            <button class="btn btn-yura_primary" onclick="store_caja()">
                <i class="fa fa-fw fa-plus"></i>
            </button>
        </td>
    </tr>

    @foreach($cajas as $c)
        <tr class="{{$c->estado == 1 ? '' : 'error'}}">
            <td class="text-center" style="border-color: #9d9d9d">
                <input type="text" class="text-center" style="width: 100%; background-color: #e9ecef"
                       id="edit_nombre_c_{{$c->id_caja}}"
                       value="{{$c->nombre}}">
            </td>
            <td class="text-center" style="border-color: #9d9d9d">
                <input type="number" class="text-center" style="width: 100%; background-color: #e9ecef"
                       id="edit_fa_c_{{$c->id_caja}}"
                       value="{{$c->factor_conversion}}">
            </td>
            <td class="text-center" style="border-color: #9d9d9d">
                <input type="number" class="text-center" style="width: 100%; background-color: #e9ecef"
                       id="edit_peso_c_{{$c->id_caja}}"
                       value="{{$c->peso}}">
            </td>
            <td class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                <div class="btn-group">
                    <button class="btn btn-yura_warning btn-xs" onclick="update_caja('{{$c->id_caja}}')">
                        <i class="fa fa-fw fa-edit"></i>
                    </button>
                    <button class="btn btn-yura_danger btn-xs" onclick="cambiar_estado_caja('{{$c->id_caja}}')">
                        <i class="fa fa-fw fa-{{$c->estado == 1 ? 'unlock' : 'lock'}}"></i>
                    </button>
                </div>
            </td>
        </tr>
    @endforeach
</table>