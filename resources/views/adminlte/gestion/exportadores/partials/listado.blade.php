@forelse ($exportadores as $exp)
    <tr class="{{$exp->estado == 1 ? '' : 'error'}}">
        <td class="text-center" style="border-color: #9d9d9d">
            <input type="text" class="text-center" style="width: 100%; background-color: #e9ecef"
                id="edit_nombre_{{$exp->id_exportador}}" value="{{$exp->nombre}}">
        </td>
        <td class="text-center" style="border-color: #9d9d9d">
            <input type="text" class="text-center" style="width: 100%; background-color: #e9ecef"
                id="edit_identificacion_{{$exp->id_exportador}}" value="{{$exp->identificacion}}">
        </td>
        <td class="text-center" style="border-color: #9d9d9d">
            <input type="text" class="text-center" style="width: 100%; background-color: #e9ecef"
                id="edit_codigo_externo_{{$exp->id_exportador}}" value="{{$exp->codigo_externo}}">
        </td>
        <td class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
            <div class="btn-group">
                <button class="btn btn-yura_warning btn-xs" onclick="update_exportador('{{$exp->id_exportador}}')">
                    <i class="fa fa-fw fa-edit"></i>
                </button>
                <button class="btn btn-yura_danger btn-xs" onclick="cambiar_estado_exportador('{{$exp->id_exportador}}')">
                    <i class="fa fa-fw fa-{{$exp->estado == 1 ? 'unlock' : 'lock'}}"></i>
                </button>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td class="text-center alert alert-info" colspan="4">
            <h4>No hay exportadores registrados</h4>
        </td>
    </tr>
@endforelse
    
