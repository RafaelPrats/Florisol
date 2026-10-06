<table class="table-responsive table-bordered pv-tabla" id="table_content_plantas">
    <thead>
        <tr id="th_fija_top_plantas">
            <th class="text-left">
                <button type="button" class="pv-sort-button" onclick="ordenar_listado_pv(this, 0)">
                    Planta <i class="fa fa-fw fa-sort" aria-hidden="true"></i>
                </button>
            </th>
            <th class="text-center">
                <div class="btn-group" role="group" aria-label="Acciones de plantas">
                    <button type="button" class="btn btn-xs btn-yura_default" title="Añadir planta"
                        onclick="add_planta()">
                        <i class="fa fa-fw fa-plus"></i>
                    </button>
                    <button type="button" class="btn btn-xs btn-yura_dark" title="Formulario completo"
                        onclick="form_compelto()">
                        <i class="fa fa-fw fa-leaf"></i>
                    </button>
                </div>
            </th>
        </tr>
    </thead>
    <tbody>
        @forelse ($plantas as $p)
            <tr class="{{ $p->estado == 1 ? '' : 'pv-estado-inactivo' }}" id="row_planta_{{ $p->id_planta }}">
                <td class="text-left mouse-hand pv-nombre"
                    onclick="select_planta('{{ $p->id_planta }}')">
                    <i class="fa fa-fw fa-check hidden icon_hidden_p text-color_yura"
                        id="icon_planta_{{ $p->id_planta }}"></i>
                    {{ $p->nombre }}
                </td>
                <td class="text-center">
                    <div class="btn-group">
                        <button class="btn btn-xs btn-yura_default" type="button" title="Editar planta"
                            onclick="edit_planta('{{ $p->id_planta }}')">
                            <i class="fa fa-fw fa-pencil"></i>
                        </button>
                        <button class="btn btn-xs btn-yura_danger" type="button"
                            title="{{ $p->estado == 1 ? 'Desactivar' : 'Activar' }} planta"
                            onclick="cambiar_estado_planta('{{ $p->id_planta }}','{{ $p->estado }}')">
                            <i class="fa fa-fw fa-{{ $p->estado == 1 ? 'trash' : 'unlock' }}"></i>
                        </button>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td class="text-center pv-vacio" colspan="2">
                    <i class="fa fa-leaf"></i><br>No hay plantas registradas
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
