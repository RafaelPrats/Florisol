<div style="overflow: scroll; max-height: 300px">
    <table width="100%" class="table-responsive table-bordered" style="font-size: 0.8em; border-color: #9d9d9d"
           id="table_content_modulos">
        <thead>
        <tr>
            <th class="text-center th_yura_default" style="border-color: #9d9d9d">MÓDULO</th>
            <th class="text-center th_yura_default" style="border-color: #9d9d9d">ÁREA</th>
            <th class="text-center th_yura_default" style="border-color: #9d9d9d; width: 80px">
                <button type="button" class="btn btn-xs btn-yura_default" title="Añadir Módulo" onclick="add_modulo()">
                    <i class="fa fa-fw fa-plus"></i>
                </button>
            </th>
        </tr>
        </thead>
        @if(sizeof($modulos)>0)
            <tbody>
            @foreach($modulos as $item)
                <tr onmouseover="$(this).css('background-color','#add8e6')" onmouseleave="$(this).css('background-color','')"
                    class="{{$item->estado == 1 ? '' : 'error'}}" id="row_modulo_{{$item->id_modulo}}"
                        {{--onclick="select_modulo('{{$item->id_modulo}}')"--}}>
                    <td style="border-color: #9d9d9d" class="text-center mouse-hand">
                        <i class="fa fa-fw fa-check hidden icon_hidden_m" id="icon_modulo_{{$item->id_modulo}}"></i> {{$item->nombre}}
                    </td>
                    <td style="border-color: #9d9d9d" class="text-center mouse-hand">
                        {{number_format($item->area, 2)}}m<sup>2</sup>
                    </td>
                    <td style="border-color: #9d9d9d" class="text-center">
                        <div class="btn-group">
                            <button class="btn btn-xs btn-yura_default" type="button" title="Editar"
                                    onclick="edit_modulo('{{$item->id_modulo}}')">
                                <i class="fa fa-fw fa-pencil"></i>
                            </button>
                            <button class="btn btn-xs btn-yura_danger" type="button" title="{{$item->estado == 1 ? 'Desactivar' : 'Activar'}}"
                                    onclick="cambiar_estado_modulo('{{$item->id_modulo}}','{{$item->estado}}', '{{$item->id_sector}}')">
                                <i class="fa fa-fw fa-{{$item->estado == 1 ? 'trash' : 'unlock'}}"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        @else
            <tr onmouseover="$(this).css('background-color','#add8e6')" onmouseleave="$(this).css('background-color','')">
                <td style="border-color: #9d9d9d" class="text-center" colspan="3">
                    No hay módulos registrados en este sector
                </td>
            </tr>
        @endif
    </table>
</div>