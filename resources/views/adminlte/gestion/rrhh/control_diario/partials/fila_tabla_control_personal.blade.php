<tr id="row_planta_4" style="">
    <input type="hidden" class="id_personal_detalle" value="">
    @if (!$asignacionMasivaHoras)
        <td style="border-color: #9d9d9d" class="text-center">
            <input type="checkbox" class="check_select_personal" checked>
        </td>
    @endif
    <td style="border-color: #9d9d9d" class="text-center">
        <select class="w-100 text-center" style="height: 22px;" onchange="seleccionar_personal(this)">
            <option value="">Seleccione</option>
            @foreach ($personal as $p)
                <option value="{{$p->id_personal}}"
                    data-identificacion="{{$p->cedula_identidad}}"
                    data-id-personal-detalle="{{$p->id_personal_detalle}}"
                    {{isset($personalEncontrado) ? $personalEncontrado->id_personal == $p->id_personal ? 'selected' : '' : ''}}
                >
                    {{$p->nombre}} {{$p->apellido}}
                </option>
            @endforeach
        </select>
    </td>
    <td style="border-color: #9d9d9d" class="text-center persona_detalle"></td>
    <td style="border-color: #9d9d9d" class="text-center">
        <input type="time" class="w-100 input-date-cd" value="{{$desde}}" >
    </td>
    <td style="border-color: #9d9d9d" class="text-center">
        <input type="time" class="w-100 input-date-ch" value="{{$hasta}}">
    </td>
    <td style="border-color: #9d9d9d" class="text-center">
        <select class="w-100 id_mano_obra" style="height: 22px;">
            <option value="">Seleccione</option>
            @foreach ($manoObra as $mo)
                <option value="{{$mo->id_mano_obra}}" {{isset($personalEncontrado) ? $personalEncontrado->id_mano_obra == $mo->id_mano_obra ? 'selected' : '' : ''}} >{{$mo->nombre}}</option>
            @endforeach
        </select>
    </td>
    <td style="border-color: #9d9d9d" class="text-center">
        <button class="btn btn-xs btn-danger rounded-4" style="border-radius:18px"
                title="Eliminar asistencia" onclick="$(this).parent().parent().remove()">
            <i class="fa fa-trash"></i>
        </button>
    </td>
</tr>
