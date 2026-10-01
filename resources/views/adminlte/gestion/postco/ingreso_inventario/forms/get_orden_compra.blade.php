@foreach ($listado as $pos => $item)
    <tr id="new_tr_{{ $pos + 1 }}">
        <th class="text-center" style="border-color: #9d9d9d">
            <select id="new_planta_{{ $pos + 1 }}" style="width: 100%; height: 26px;" onchange="seleccionar_planta(1)"
                class="new_planta">
                <option value="">Seleccione</option>
                @foreach ($plantas as $pta)
                    <option value="{{ $pta->id_planta }}" {{ $pta->id_planta == $item->id_planta ? 'selected' : '' }}>
                        {{ $pta->nombre }}
                    </option>
                @endforeach
            </select>
        </th>
        <th class="text-center" style="border-color: #9d9d9d">
            <select id="new_variedad_{{ $pos + 1 }}" style="width: 100%; height: 26px;" class="new_variedad">
                <option value="{{ $item->id_variedad }}">
                    {{ $item->var_nombre }}
                </option>
            </select>
        </th>
        <th class="text-center" style="border-color: #9d9d9d">
            <input type="text" style="width: 100%; height: 34px;" class="padding_lateral_5" value="60"
                id="new_longitud_{{ $pos + 1 }}">
        </th>
        <th class="text-center" style="border-color: #9d9d9d">
            <input type="number" style="width: 100%; height: 34px;" class="padding_lateral_5"
                id="new_tallos_x_ramo_{{ $pos + 1 }}" value="1">
        </th>
        <th class="text-center" style="border-color: #9d9d9d">
            <input type="number" style="width: 100%; height: 34px;" class="padding_lateral_5"
                id="new_ramos_{{ $pos + 1 }}" value="{{ $item->cantidad }}">
        </th>
        <th class="text-center" style="border-color: #9d9d9d">
            <select id="new_bodega_{{ $pos + 1 }}" style="width: 100%; height: 34px;">
                <option value="V">Ventas</option>
            </select>
        </th>
        <th class="text-center" style="border-color: #9d9d9d">
        </th>
    </tr>
@endforeach

<script>
    num_row = {{ count($listado) }};
</script>
