<table width="100%" class="table-responsive table-bordered" style="font-size: 0.8em; border-color: #9d9d9d"
    id="table_content_plantas">
    <thead>
        <tr class="tr_fija_top_0">
            <th class="text-center th_yura_green">PLANTA</th>
            <th class="text-center th_yura_green">
                <div class="btn-group">
                    <button type="button" class="btn btn-yura_default btn-xs dropdown-toggle" data-toggle="dropdown">
                        <i class="fa fa-fw fa-gears"></i> Opciones
                    </button>
                    <ul class="dropdown-menu dropdown-menu-right sombra_pequeña" role="menu"
                        style="z-index: 10 !important">
                        <li>
                            <a href="javascript:void(0)" onclick="add_planta()">
                                <i class="fa fa-fw fa-plus"></i> Agregar Planta
                            </a>
                        </li>
                        <li>
                            <a href="javascript:void(0)"
                                onclick="importar_variedades()">
                                <i class="fa fa-fw fa-upload"></i> Importar Variedades
                            </a>
                        </li>
                        <li>
                            <a href="javascript:void(0)"
                                onclick="form_compelto()">
                                <i class="fa fa-fw fa-leaf"></i> Formulario completo
                            </a>
                        </li>
                    </ul>
                </div>
            </th>
        </tr>
    </thead>
    <tbody>
        @if (count($plantas) > 0)
            @foreach ($plantas as $p)
                <tr onmouseover="$(this).css('background-color','#add8e6')"
                    onmouseleave="$(this).css('background-color','')" class="{{ $p->estado == 1 ? '' : 'error' }}"
                    id="row_planta_{{ $p->id_planta }}">
                    <td style="border-color: #9d9d9d" class="text-center mouse-hand"
                        onclick="select_planta('{{ $p->id_planta }}')">
                        <i class="fa fa-fw fa-check hidden icon_hidden_p" id="icon_planta_{{ $p->id_planta }}"></i>
                        {{ $p->nombre }}
                    </td>
                    <td style="border-color: #9d9d9d" class="text-center">
                        <div class="btn-group">
                            <button class="btn btn-xs btn-yura_default" type="button" title="Editar"
                                onclick="edit_planta('{{ $p->id_planta }}')">
                                <i class="fa fa-fw fa-pencil"></i>
                            </button>
                            <button class="btn btn-xs btn-yura_danger" type="button"
                                title="{{ $p->estado == 1 ? 'Desactivar' : 'Activar' }}"
                                onclick="cambiar_estado_planta('{{ $p->id_planta }}','{{ $p->estado }}')">
                                <i class="fa fa-fw fa-{{ $p->estado == 1 ? 'trash' : 'unlock' }}"></i>
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
        @endif
    </tbody>
</table>
