@foreach ($detalles_receta as $pos => $item)
    <tr id="tr_variedad_seleccionado_{{ $pos + 1 }}">
        <td class="text-center" style="border-color: #9d9d9d">
            {{ $item->item->planta->nombre }}
        </td>
        <td class="text-center" style="border-color: #9d9d9d">
            {{ $item->item->nombre }}
            <input type="hidden" class="cant_variedad_seleccionado" value="{{ $pos + 1 }}">
            <input type="hidden" id="id_variedad_seleccionado_{{ $pos + 1 }}" value="{{ $item->id_item }}">
        </td>
        <td class="text-center" style="border-color: #9d9d9d">
            <input type="number" class="text-center" style="width: 100%"
                id="longitud_variedad_seleccionado_{{ $pos + 1 }}" value="{{ $detalle_pedido->longitud }}">
        </td>
        <td class="text-center" style="border-color: #9d9d9d">
            <input type="number" class="text-center" style="width: 100%"
                id="cantidad_variedad_seleccionado_{{ $pos + 1 }}" value="{{ $item->unidades }}">
        </td>
        <td class="text-center" style="border-color: #9d9d9d">
            <button type="button" class="btn btn-xs btn-yura_danger" title="Quitar"
                onclick="quitar_variedad_seleccionado('{{ $pos + 1 }}')">
                <i class="fa fa-fw fa-trash"></i>
            </button>
        </td>
    </tr>
@endforeach
