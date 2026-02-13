<table class="table-bordered" style="width: 100%" id="table_requerimientos_{{$item->id_propag_disponibilidad}}">
    @if($item->requerimientos != '')
        @foreach(explode('|', $item->requerimientos) as $req)
            <tr>
                <td class="text-center" style="border-color: #9d9d9d; width: 80px" title="{{explode('+', $req)[3] == 'S' ? 'Siembra' : 'Poda'}}">
                    <span class="input-group-addon {{explode('+', $req)[3] == 'S' ? 'bg-yura_dark' : ''}}" style="height: 20px !important;">
                        {{explode('+', $req)[1]}}
                    </span>
                    <input type="hidden" class="modulo_requerimiento_{{$item->id_propag_disponibilidad}}"
                           value="{{explode('+', $req)[0].'+'.explode('+', $req)[1].'+'.explode('+', $req)[3]}}">
                </td>
                <td class="text-center" style="border-color: #9d9d9d"
                    title="{{explode('+', $req)[3] == 'S' ? 'Plantas iniciales' : 'Plantas muertas'}}">
                    <input type="number" class="form-control text-center cantidad_plantas_requerimiento_{{$item->id_propag_disponibilidad}}"
                           value="{{explode('+', $req)[2]}}" style="height: 28px !important; width: 100% !important;">
                </td>
            </tr>
        @endforeach
    @endif
    @if($item->requerimientos_adicionales != '')
        @foreach(explode('|', $item->requerimientos_adicionales) as $req)
            <tr>
                <td class="text-center" style="border: 2px solid #021165; width: 80px"
                    title="{{explode('+', $req)[3] == 'S' ? 'Siembra' : 'Poda'}}">
                    <span class="input-group-addon {{explode('+', $req)[3] == 'S' ? 'bg-yura_dark' : ''}}" style="height: 20px !important;">
                        {{explode('+', $req)[1]}}
                    </span>
                    <input type="hidden" class="modulo_adicional_{{$item->id_propag_disponibilidad}}"
                           value="{{explode('+', $req)[0].'+'.explode('+', $req)[1].'+'.explode('+', $req)[3]}}">
                </td>
                <td class="text-center" style="border: 2px solid #021165"
                    title="{{explode('+', $req)[3] == 'S' ? 'Plantas iniciales' : 'Plantas muertas'}}">
                    <input type="number" class="form-control text-center cantidad_plantas_adicionales_{{$item->id_propag_disponibilidad}}"
                           value="{{explode('+', $req)[2]}}" style="height: 28px !important; width: 100% !important;" min="0">
                </td>
            </tr>
        @endforeach
    @endif
</table>