<table class="table-responsive table-bordered pv-tabla" id="table_content_proveedores">
    <thead>
        <tr id="th_fija_top_proveedores">
            <th class="text-left">
                <button type="button" class="pv-sort-button" onclick="ordenar_listado_pv(this, 0)">
                    Proveedor <i class="fa fa-fw fa-sort" aria-hidden="true"></i>
                </button>
            </th>
            <th class="text-center">
                <div class="btn-group" role="group" aria-label="Acciones de proveedores">
                    <button type="button" class="btn btn-xs btn-yura_default" title="Añadir proveedor"
                        onclick="add_proveedor()">
                        <i class="fa fa-fw fa-plus"></i>
                    </button>
                </div>
            </th>
        </tr>
    </thead>
    <tbody>
        @forelse ($proveedores as $p)
            <tr id="row_proveedor_{{ $p->id_configuracion_empresa }}">
                <td class="text-left mouse-hand pv-nombre"
                    onclick="select_proveedor('{{ $p->id_configuracion_empresa }}')">
                    <i class="fa fa-fw fa-check hidden icon_hidden_proveedor text-color_yura"
                        id="icon_proveedor_{{ $p->id_configuracion_empresa }}"></i>
                    {{ $p->nombre }}
                </td>
                <td class="text-center">
                    <button class="btn btn-xs btn-yura_default" type="button" title="Editar proveedor"
                        onclick="edit_proveedor('{{ $p->id_configuracion_empresa }}')">
                        <i class="fa fa-fw fa-pencil"></i>
                    </button>
                </td>
            </tr>
        @empty
            <tr>
                <td class="text-center pv-vacio" colspan="2">
                    <i class="fa fa-truck"></i><br>No hay proveedores registrados
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
