<table width="100%" class="table-responsive table-bordered" style="font-size: 0.8em; border-color: #9d9d9d"
    id="table_content_proveedores">
    <thead>
        <tr class="tr_fija_top_0">
            <th class="text-center th_yura_green">PROVEEDOR</th>
            <th class="text-center th_yura_green">
                <div class="btn-group">
                    <button type="button" class="btn btn-xs btn-yura_default" title="Añadir Planta"
                        onclick="add_proveedor()">
                        <i class="fa fa-fw fa-plus"></i>
                    </button>
                </div>
            </th>
        </tr>
    </thead>
    @if (count($proveedores) > 0)
        @foreach ($proveedores as $p)
            <tr onmouseover="$(this).css('background-color','#add8e6')"
                onmouseleave="$(this).css('background-color','')">
                <td style="border-color: #9d9d9d" class="text-center mouse-hand"
                    onclick="select_proveedor('{{ $p->id_configuracion_empresa }}')">
                    <i class="fa fa-fw fa-check hidden icon_hidden_proveedor"
                        id="icon_proveedor_{{ $p->id_configuracion_empresa }}"></i>
                    {{ $p->nombre }}
                </td>
                <td style="border-color: #9d9d9d" class="text-center">
                    <div class="btn-group">
                        <button class="btn btn-xs btn-yura_default" type="button" title="Editar"
                            onclick="edit_proveedor('{{ $p->id_configuracion_empresa }}')">
                            <i class="fa fa-fw fa-pencil"></i>
                        </button>
                    </div>
                </td>
            </tr>
        @endforeach
    @else
        <tr>
            <td class="text-center" colspan="2" style="border-color: #9d9d9d">
                No hay plantas registradas
            </td>
        </tr>
        </div>
    @endif
</table>
