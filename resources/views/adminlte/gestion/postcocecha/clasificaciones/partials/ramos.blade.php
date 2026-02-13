<table class="table-bordered table-striped" style="width: 100%; border: 1px solid #9d9d9d">
    <tr>
        <th class="text-center th_yura_green">
            Nombre
        </th>
        <th class="text-center th_yura_green">
            Unidad Medida
        </th>
        <th class="text-center th_yura_green">
        </th>
    </tr>

    <tr>
        <td class="text-center" style="border-color: #9d9d9d">
            <input type="text" class="text-center form-control" style="width: 100%" id="new_nombre_r" placeholder="NUEVO">
        </td>
        <td class="text-center" style="border-color: #9d9d9d">
            <select id="new_unidad_medida_r" style="width: 100%" class="form-control">
                @foreach($unidades as $um)
                    <option value="{{$um->id_unidad_medida}}">
                        {{$um->siglas}}
                    </option>
                @endforeach
            </select>
        </td>
        <td class="text-center" style="border-color: #9d9d9d">
            <button class="btn btn-yura_primary" onclick="store_ramo()">
                <i class="fa fa-fw fa-plus"></i>
            </button>
        </td>
    </tr>

    @foreach($ramos as $r)
        <tr class="{{$r->estado == 1 ? '' : 'error'}}">
            <td class="text-center" style="border-color: #9d9d9d">
                <input type="text" class="text-center" style="width: 100%; background-color: #e9ecef"
                       id="edit_nombre_r_{{$r->id_clasificacion_ramo}}"
                       value="{{$r->nombre}}">
            </td>
            <td class="text-center" style="border-color: #9d9d9d">
                <select id="edit_unidad_medida_r_{{$r->id_clasificacion_ramo}}" style="width: 100%; background-color: #e9ecef">
                    @foreach($unidades as $um)
                        <option value="{{$um->id_unidad_medida}}" {{$um->id_unidad_medida == $r->id_unidad_medida ? 'selected' : ''}}>
                            {{$um->siglas}}
                        </option>
                    @endforeach
                </select>
            </td>
            <td class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                <div class="btn-group">
                    <button class="btn btn-yura_warning btn-xs" onclick="update_ramo('{{$r->id_clasificacion_ramo}}')">
                        <i class="fa fa-fw fa-edit"></i>
                    </button>
                    <button class="btn btn-yura_danger btn-xs" onclick="cambiar_estado_ramo('{{$r->id_clasificacion_ramo}}')">
                        <i class="fa fa-fw fa-{{$r->estado == 1 ? 'unlock' : 'lock'}}"></i>
                    </button>
                </div>
            </td>
        </tr>
    @endforeach
</table>