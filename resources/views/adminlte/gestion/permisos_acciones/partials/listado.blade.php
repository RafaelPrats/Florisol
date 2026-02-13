<div style="overflow-x: scroll; overflow-y: scroll; max-height: 500px">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
        <tr>
            <th class="text-center th_yura_green">
                Accion
            </th>
            <th class="text-center th_yura_green">
                Usuario
            </th>
            <th class="text-center th_yura_green" style="width: 80px">
            </th>
        </tr>
        <tr>
            <td class="text-center" style="border-color: #9d9d9d">
                <select id="new_accion" style="width: 100%" class="text-center bg-yura_dark" required>
                    <option value="">Seleccione</option>
                    <option value="SALIDAS_RECEPCION">SALIDAS RECEPCION</option>
                    <option value="CONFIRMAR_PREPRODUCCION">CONFIRMAR PREPRODUCCION</option>
                </select>
            </td>
            <td class="text-center" style="border-color: #9d9d9d">
                <select id="new_usuario" style="width: 100%" class="text-center bg-yura_dark" required>
                    <option value="">Seleccione</option>
                    @foreach ($usuarios as $item)
                        <option value="{{ $item->id_usuario }}">{{ $item->nombre_completo }}</option>
                    @endforeach
                </select>
            </td>
            <td class="text-center" style="border-color: #9d9d9d">
                <button type="button" class="btn btn-xs btn-yura_primary" onclick="store_permiso()">
                    <i class="fa fa-fw fa-save"></i> Grabar
                </button>
            </td>
        </tr>
        @foreach ($listado as $item)
            <tr>
                <td class="text-center" style="border-color: #9d9d9d">
                    <select id="edit_accion_{{ $item->id_permiso_accion }}" style="width: 100%" class="text-center"
                        required>
                        <option value="SALIDAS_RECEPCION" {{ $item->accion == 'SALIDAS_RECEPCION' ? 'selected' : '' }}>
                            SALIDAS RECEPCION
                        </option>
                        <option value="CONFIRMAR_PREPRODUCCION"
                            {{ $item->accion == 'CONFIRMAR_PREPRODUCCION' ? 'selected' : '' }}>
                            CONFIRMAR PREPRODUCCION
                        </option>
                    </select>
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <select id="edit_usuario_{{ $item->id_permiso_accion }}" style="width: 100%" class="text-center"
                        required>
                        @foreach ($usuarios as $us)
                            <option value="{{ $us->id_usuario }}"
                                {{ $us->id_usuario == $item->id_usuario ? 'selected' : '' }}>
                                {{ $us->nombre_completo }}
                            </option>
                        @endforeach
                    </select>
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <div class="btn-group">
                        <button type="button" class="btn btn-xs btn-yura_primary"
                            onclick="update_permiso('{{ $item->id_permiso_accion }}')">
                            <i class="fa fa-fw fa-edit"></i>
                        </button>
                        <button type="button"
                            class="btn btn-xs btn-yura_{{ $item->estado == 1 ? 'danger' : 'warning' }}"
                            onclick="desactivar_permiso('{{ $item->id_permiso_accion }}','{{ $item->estado }}')"
                            title="{{ $item->estado == 1 ? 'DESACTIVAR' : 'ACTIVAR' }}">
                            <i class="fa fa-fw fa-{{ $item->estado == 1 ? 'lock' : 'unlock' }}"></i>
                        </button>
                    </div>
                </td>
            </tr>
        @endforeach
    </table>
</div>
