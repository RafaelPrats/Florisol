<div style="overflow-y: scroll; max-height: 450px">
    <table class="table-striped table-bordered" style="width: 100%; border: 1px solid #9d9d9d" id="table_labores_matriz">
        <tr id="tr_top_fija_0">
            <th class="text-center th_yura_green">
                Nombre
            </th>
            <th class="text-center th_yura_green">

            </th>
        </tr>
        <tr>
            <td class="text-center" style="border-color: #9d9d9d">
                <input type="text" id="new_nombre" style="width: 100%" value=""
                       maxlength="250" placeholder="Nombre" class="text-center">
            </td>
        </tr>
        @foreach($listado as $item)
            <tr>
                <td class="text-center" style="border-color: #9d9d9d">
                    <input type="text" id="edit_nombre_{{$item->id_aplicacion_matriz}}" style="width: 100%" value="{{$item->nombre}}"
                           maxlength="250" placeholder="Nombre" class="text-center">
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <div class="btn-group">
                        @if($item->tipo != 'U')
                            <button type="button" class="btn btn-xs btn-yura_primary"
                                    onclick="update_matriz('{{$item->id_aplicacion_matriz}}')">
                                <i class="fa fa-fw fa-pencil"></i>
                            </button>
                            <button type="button" class="btn btn-xs btn-yura_danger"
                                    onclick="delete_matriz('{{$item->id_aplicacion_matriz}}')">
                                <i class="fa fa-fw fa-trash"></i>
                            </button>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    </table>
</div>