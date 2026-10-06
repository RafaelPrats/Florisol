@if ($modo_admin)
    @foreach ($detalles_receta as $pos => $item)
        <tr id="tr_variedad_seleccionado_{{ $pos + 1 }}">
            <td class="text-center" style="border-color: #9d9d9d">
                {{ $item->item->planta->nombre }}
            </td>
            <td class="text-center" style="border-color: #9d9d9d">
                {{ $item->item->nombre }}
                <input type="hidden" class="cant_variedad_seleccionado" value="{{ $pos + 1 }}">
                <input type="hidden" id="id_variedad_seleccionado_{{ $pos + 1 }}"
                    value="{{ $item->id_item }}">
            </td>
            <td class="text-center" style="border-color: #9d9d9d">
                <input type="number" class="text-center" id="longitud_variedad_seleccionado_{{ $pos + 1 }}"
                    value="{{ $det_caja->longitud_ramo }}">
            </td>
            <td class="text-center" style="border-color: #9d9d9d">
                <input type="number" class="text-center" id="cantidad_variedad_seleccionado_{{ $pos + 1 }}"
                    value="{{ $item->unidades }}">
            </td>
            <td class="text-center" style="border-color: #9d9d9d">
                <button type="button" class="btn btn-xs btn-yura_danger" title="Quitar"
                    onclick="quitar_variedad_seleccionado('{{ $pos + 1 }}')">
                    <i class="fa fa-fw fa-trash"></i>
                </button>
            </td>
        </tr>
    @endforeach
@else
    @php
        $total_tallos = 0;
        foreach ($detalles_receta as $d) {
            $total_tallos += $d->unidades * $ramos_pedido;
        }
    @endphp
    @foreach ($detalles_receta as $pos => $item)
        <tr id="tr_variedad_seleccionado_{{ $pos + 1 }}" class="tr_distribucion" data-pos="{{ $pos + 1 }}"
            data-pta_nombre="{{ $item->item->planta->nombre }}" data-var_nombre="{{ $item->item->nombre }}">
            <th class="text-center mouse-hand tr_distribucion_{{ $pos + 1 }}" style="border-color: #9d9d9d"
                onmouseover="$('.tr_distribucion_{{ $pos + 1 }}').css('background-color', 'rgba(0, 179, 136, .12)')"
                onmouseleave="$('.tr_distribucion_{{ $pos + 1 }}').css('background-color', '')"
                onclick="seleccionar_distribucion('{{ $pos + 1 }}')">
                <i id="icon_distribucion_{{ $pos + 1 }}" class="fa fa-fw fa-check hidden icon_distribucion"></i>
                {{ $pos + 1 }}
            </th>
            <th class="text-center mouse-hand tr_distribucion_{{ $pos + 1 }}" style="border-color: #9d9d9d"
                onmouseover="$('.tr_distribucion_{{ $pos + 1 }}').css('background-color', 'rgba(0, 179, 136, .12)')"
                onmouseleave="$('.tr_distribucion_{{ $pos + 1 }}').css('background-color', '')"
                onclick="seleccionar_distribucion('{{ $pos + 1 }}')">
                {{ $item->item->planta->nombre }}
            </th>
            <th class="text-center mouse-hand tr_distribucion_{{ $pos + 1 }}" style="border-color: #9d9d9d"
                onmouseover="$('.tr_distribucion_{{ $pos + 1 }}').css('background-color', 'rgba(0, 179, 136, .12)')"
                onmouseleave="$('.tr_distribucion_{{ $pos + 1 }}').css('background-color', '')"
                onclick="seleccionar_distribucion('{{ $pos + 1 }}')">
                {{ $item->item->nombre }}
                <input type="hidden" class="cant_variedad_seleccionado" value="{{ $pos + 1 }}">
                <input type="hidden" id="id_variedad_seleccionado_{{ $pos + 1 }}" value="{{ $item->id_item }}">
            </th>
            <th class="text-center mouse-hand tr_distribucion_{{ $pos + 1 }}" style="border-color: #9d9d9d"
                onmouseover="$('.tr_distribucion_{{ $pos + 1 }}').css('background-color', 'rgba(0, 179, 136, .12)')"
                onmouseleave="$('.tr_distribucion_{{ $pos + 1 }}').css('background-color', '')"
                onclick="seleccionar_distribucion('{{ $pos + 1 }}')">
                {{ $det_caja->longitud_ramo }}cm
                <input type="hidden" class="text-center" style="width: 100%"
                    id="longitud_variedad_seleccionado_{{ $pos + 1 }}" value="{{ $det_caja->longitud_ramo }}">
            </th>
            <th class="text-center mouse-hand tr_distribucion_{{ $pos + 1 }}" style="border-color: #9d9d9d"
                onmouseover="$('.tr_distribucion_{{ $pos + 1 }}').css('background-color', 'rgba(0, 179, 136, .12)')"
                onmouseleave="$('.tr_distribucion_{{ $pos + 1 }}').css('background-color', '')"
                onclick="seleccionar_distribucion('{{ $pos + 1 }}')">
                {{ $item->unidades }}
                <input type="hidden" class="text-center" style="width: 100%"
                    id="cantidad_variedad_seleccionado_{{ $pos + 1 }}" value="{{ $item->unidades }}">
            </th>
            <th class="text-center mouse-hand tr_distribucion_{{ $pos + 1 }}" style="border-color: #9d9d9d"
                onmouseover="$('.tr_distribucion_{{ $pos + 1 }}').css('background-color', 'rgba(0, 179, 136, .12)')"
                onmouseleave="$('.tr_distribucion_{{ $pos + 1 }}').css('background-color', '')"
                onclick="seleccionar_distribucion('{{ $pos + 1 }}')">
                {{ $item->unidades * $ramos_pedido }}
                <input type="hidden" readonly id="total_tallos_distribucion_{{ $pos + 1 }}" style="width: 100%"
                    class="text-center" value="{{ $item->unidades * $ramos_pedido }}">
            </th>
            @if ($pos == 0)
                <th class="text-center" style="border-color: #9d9d9d" rowspan="{{ count($detalles_receta) }}">
                    {{ $total_tallos }}
                </th>
            @endif
        </tr>
    @endforeach
@endif
