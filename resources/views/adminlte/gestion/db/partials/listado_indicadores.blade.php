<table class="table-striped table-bordered table-hover" width="100%" style="border: 2px solid #9d9d9d"
       id="db_tbl_indicadores">
    <thead>
    <tr style="background-color: #e9ecef">
        <th class="text-center" style="border-color: #9d9d9d" width="10%">
            Finca
        </th>
        <th class="text-center" style="border-color: #9d9d9d" width="10%">
            Nombre
        </th>
        <th class="text-center" style="border-color: #9d9d9d">
            Descripción
        </th>
        <th class="text-center" style="border-color: #9d9d9d" width="10%">
            Valor
        </th>
        <th class="text-center" style="border-color: #9d9d9d" width="10%">
            Estado
        </th>
        <th class="text-center" style="border-color: #9d9d9d" width="10%">
            <button type="button" class="btn btn-xs btn-primary" title="Añadir" onclick="add_indicador()"
                    id="btn_add_indicador">
                <i class="fa fa-fw fa-plus"></i>
            </button>
        </th>
    </tr>
    </thead>
    <tbody>
    @foreach($indicadores as $item)
        <tr>
            <td class="text-center" style="border-color: #9d9d9d">
                {{$item->empresa->nombre}}
            </td>
            <td class="text-center" style="border-color: #9d9d9d">
                <input type="text" class="text-center" id="nombre_{{$item->id_indicador}}" style="width: 100%"
                       value="{{$item->nombre}}" max="4">
            </td>
            <td class="text-center" style="border-color: #9d9d9d">
                <input type="text" class="text-center" id="descripcion_{{$item->id_indicador}}" style="width: 100%"
                       value="{{$item->descripcion}}" max="250">
            </td>
            <td class="text-center" style="border-color: #9d9d9d">
                <input type="text" class="text-center" id="valor_{{$item->id_indicador}}" style="width: 100%"
                       value="{{$item->valor}}">
            </td>
            <td class="text-center" style="border-color: #9d9d9d">
                <input type="checkbox" id="estado_{{$item->id_indicador}}" {{$item->estado == 1 ? 'checked' : ''}}>
            </td>
            <td class="text-center" style="border-color: #9d9d9d">
                <button type="button" class="btn btn-xs btn-success" title="Editar"
                        onclick="update_indicador('{{$item->id_indicador}}')">
                    <i class="fa fa-fw fa-save"></i>
                </button>
                <button type="button" class="btn btn-xs btn-primary" title="Ejecutar Comando"
                        onclick="ejecutar_comando('{{$item->id_indicador}}')">
                    <i class="fa fa-fw fa-refresh"></i>
                </button>
                <button type="button" class="btn btn-xs btn-default" title="Copiar para las demás fincas"
                        onclick="copiar_indicador('{{$item->id_indicador}}')">
                    <i class="fa fa-fw fa-copy"></i>
                </button>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>