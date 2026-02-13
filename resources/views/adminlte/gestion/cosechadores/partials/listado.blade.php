<div style="overflow-x: scroll; overflow-y: scroll; max-height: 500px">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
        <tr>
            <th class="text-center th_yura_green">
                Nombre
            </th>
            <th class="text-center th_yura_green">
            </th>
        </tr>
        <tr>
            <td class="text-center" style="border-color: #9d9d9d">
                <input type="text" id="new_nombre" style="width: 100%" class="text-center" required maxlength="250"
                    placeholder="NOMBRE">
            </td>
            <td class="text-center" style="border-color: #9d9d9d">
                <button type="button" class="btn btn-xs btn-yura_primary" onclick="store_cosechador()">
                    <i class="fa fa-fw fa-save"></i> Grabar
                </button>
            </td>
        </tr>
        @foreach ($listado as $item)
            <tr>
                <td class="text-center" style="border-color: #9d9d9d">
                    <input type="text" id="edit_nombre_{{ $item->id_cosechador }}" style="width: 100%"
                        class="text-center {{ $item->estado == 1 ? '' : 'error' }}" required maxlength="250"
                        placeholder="NOMBRE" value="{{ $item->nombre }}">
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <div class="btn-group">
                        <button type="button" class="btn btn-xs btn-yura_primary"
                            onclick="update_cosechador('{{ $item->id_cosechador }}')">
                            <i class="fa fa-fw fa-edit"></i>
                        </button>
                        <button type="button"
                            class="btn btn-xs btn-yura_{{ $item->estado == 1 ? 'danger' : 'warning' }}"
                            onclick="desactivar_cosechador('{{ $item->id_cosechador }}','{{ $item->estado }}')"
                            title="{{ $item->estado == 1 ? 'DESACTIVAR' : 'ACTIVAR' }}">
                            <i class="fa fa-fw fa-{{ $item->estado == 1 ? 'lock' : 'unlock' }}"></i>
                        </button>
                    </div>
                </td>
            </tr>
        @endforeach
    </table>
</div>
