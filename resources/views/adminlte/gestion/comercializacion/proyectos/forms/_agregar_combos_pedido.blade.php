@foreach ($detalles_combo as $pos_d => $det)
    <tr class="tr_form_ped_{{ $form_cant_detalles }}"
        onmouseover="$('.tr_form_ped_{{ $form_cant_detalles }}').addClass('bg-yura_dark')"
        onmouseleave="$('.tr_form_ped_{{ $form_cant_detalles }}').removeClass('bg-yura_dark')">
        @if ($pos_d == 0)
            <td class="text-center" rowspan="{{ count($detalles_combo) }}" style="border-color: #9d9d9d">
                <input type="number" style="width: 100%; color: black" class="text-center"
                    onchange="calcular_totales_pedido()" onkeyup="calcular_totales_pedido()"
                    id="ped_piezas_{{ $form_cant_detalles }}" min="0" value="{{ $piezas }}">
                <input type="hidden" id="ped_tipo_caja_{{ $form_cant_detalles }}" value="{{ $caja }}">
                <input type="hidden" class="pos_ped_especificaciones pos_ped_combo" value="{{ $form_cant_detalles }}">
                <input type="hidden" id="cant_detalles_combo_{{ $form_cant_detalles }}"
                    value="{{ count($detalles_combo) }}">
            </td>
        @endif
        <td class="text-center" style="border-color: #9d9d9d">
            <input type="hidden" id="ped_receta_{{ $form_cant_detalles }}_{{ $pos_d }}"
                value="{{ $det['receta']->id_variedad }}">
            <input type="hidden" id="ped_longitud_{{ $form_cant_detalles }}_{{ $pos_d }}"
                value="{{ $det['longitud'] }}">
            {{ $det['receta']->nombre }}
        </td>
        @if ($pos_d == 0)
            <td class="text-center" style="border-color: #9d9d9d" rowspan="{{ count($detalles_combo) }}">
                {{ $caja }}
            </td>
        @endif
        <td class="text-center" style="border-color: #9d9d9d">
            <input type="number" style="width: 100%; color: black"
                class="text-center ramos_x_caja_combo_{{ $form_cant_detalles }}" onchange="calcular_totales_pedido()"
                onkeyup="calcular_totales_pedido()"
                id="ped_ramos_x_caja_{{ $form_cant_detalles }}_{{ $pos_d }}" min="0"
                value="{{ $det['ramos_x_caja'] }}">
        </td>
        @if ($pos_d == 0)
            <td class="text-center" rowspan="{{ count($detalles_combo) }}" style="border-color: #9d9d9d">
                <input type="number" style="width: 100%; color: black" class="text-center"
                    id="ped_total_ramos_{{ $form_cant_detalles }}" readonly disabled>
            </td>
        @endif
        <td class="text-center" style="border-color: #9d9d9d">
            <input type="number" style="width: 100%; color: black"
                class="text-center tallos_x_ramos_combo_{{ $form_cant_detalles }}" onchange="calcular_totales_pedido()"
                onkeyup="calcular_totales_pedido()"
                id="ped_tallos_x_ramos_{{ $form_cant_detalles }}_{{ $pos_d }}" min="0"
                value="{{ $det['tallos_x_ramos'] }}">
        </td>
        @if ($pos_d == 0)
            <td class="text-center" rowspan="{{ count($detalles_combo) }}" style="border-color: #9d9d9d">
                <input type="number" style="width: 100%; color: black" class="text-center"
                    id="ped_total_tallos_{{ $form_cant_detalles }}" readonly disabled>
            </td>
        @endif
        <td class="text-center" style="border-color: #9d9d9d">
            {{ $det['longitud'] }}cm
        </td>
        <td class="text-center" style="border-color: #9d9d9d">
            <input type="number" style="width: 100%; color: black" class="text-center"
                onchange="calcular_totales_pedido()" onkeyup="calcular_totales_pedido()"
                id="ped_precio_esp_{{ $form_cant_detalles }}_{{ $pos_d }}" min="0"
                value="{{ $det['precio'] }}">
        </td>
        @if ($pos_d == 0)
            <td class="text-center" rowspan="{{ count($detalles_combo) }}" style="border-color: #9d9d9d">
                <input type="text" style="width: 100%; color: black" class="text-center"
                    id="ped_total_precio_caja_{{ $form_cant_detalles }}" readonly disabled>
            </td>
        @endif
        @if ($pos_d == 0)
            @foreach ($celdas_marcaciones as $m)
                <td class="text-center" style="border-color: #9d9d9d" rowspan="{{ count($detalles_combo) }}">
                    <input type="text" style="width: 100%; color: black" class="text-center"
                        value="{{ $m->valor_marcacion }}"
                        id="ped_marcacion_{{ $m->id_marcacion }}_{{ $form_cant_detalles }}">
                </td>
            @endforeach
            <td class="text-center" rowspan="{{ count($detalles_combo) }}" style="border-color: #9d9d9d">
                <button type="button" class="btn btn-xs btn-yura_danger"
                    onclick="delete_contenido_pedido({{ $form_cant_detalles }})" title="Eliminar Pieza">
                    <i class="fa fa-fw fa-trash"></i>
                </button>
            </td>
        @endif
    </tr>
@endforeach
