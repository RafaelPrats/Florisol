@foreach ($listado as $pos => $item)
    <tr id="new_tr_{{ $pos + 1 }}">
        <td>
            <select id="new_planta_{{ $pos + 1 }}" class="form-control new_planta"
                onchange="seleccionar_planta({{ $pos + 1 }})">
                <option value="">Seleccione</option>
                @foreach ($plantas as $pta)
                    <option value="{{ $pta->id_planta }}" {{ $pta->id_planta == $item->id_planta ? 'selected' : '' }}>
                        {{ $pta->nombre }}
                    </option>
                @endforeach
            </select>
        </td>
        <td>
            <select id="new_variedad_{{ $pos + 1 }}" class="form-control new_variedad">
                <option value="{{ $item->id_variedad }}">
                    {{ $item->var_nombre }}
                </option>
            </select>
        </td>
        <td>
            <input type="number" min="1" class="form-control" value="60"
                id="new_longitud_{{ $pos + 1 }}">
        </td>
        <td>
            <input type="number" min="1" class="form-control"
                id="new_tallos_x_ramo_{{ $pos + 1 }}" value="1">
        </td>
        <td>
            <input type="number" min="0" class="form-control"
                id="new_ramos_{{ $pos + 1 }}" value="{{ $item->cantidad }}">
        </td>
        <td>
            <select id="new_bodega_{{ $pos + 1 }}" class="form-control">
                <option value="V">Ventas</option>
            </select>
        </td>
        <td></td>
    </tr>
@endforeach
