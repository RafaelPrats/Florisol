<table class="table-striped table-bordered pv-tabla" id="table_content_variedades">
    <thead>
        <tr id="th_fija_top_variedades">
            <th class="text-left">
                <button type="button" class="pv-sort-button" onclick="ordenar_listado_pv(this, 0)">
                    Variedad <i class="fa fa-fw fa-sort" aria-hidden="true"></i>
                </button>
            </th>
            <th class="text-center">
                <button type="button" class="pv-sort-button" onclick="ordenar_listado_pv(this, 1)">
                    Cod. Latin <i class="fa fa-fw fa-sort" aria-hidden="true"></i>
                </button>
            </th>
            <th class="text-center">
                <button type="button" class="pv-sort-button" onclick="ordenar_listado_pv(this, 2)">
                    Cod. Exp. <i class="fa fa-fw fa-sort" aria-hidden="true"></i>
                </button>
            </th>
            <th class="text-center">
                <button type="button" class="pv-sort-button" onclick="ordenar_listado_pv(this, 3)">
                    Color <i class="fa fa-fw fa-sort" aria-hidden="true"></i>
                </button>
            </th>
            @if ($proveedor != '')
                <th class="text-center">
                    Asignar proveedor
                    <br>
                    <button type="button" class="btn btn-xs btn-yura_default"
                        title="Asignar todas las variedades al proveedor seleccionado"
                        onclick="asignar_all_variedades()">
                        <i class="fa fa-fw fa-check"></i> Todas
                    </button>
                </th>
            @endif
            <th class="text-center">
                <button type="button" class="btn btn-xs btn-yura_primary" title="Añadir variedad"
                    onclick="add_variedad()">
                    <i class="fa fa-fw fa-plus"></i>
                </button>
            </th>
        </tr>
    </thead>
    <tbody>
        @forelse ($variedades as $v)
            <tr class="{{ $v->estado == 1 ? '' : 'error' }}" id="row_variedad_{{ $v->id_variedad }}">
                <td class="text-left pv-nombre">{{ $v->nombre }}</td>
                <td class="text-center">{{ $v->codigo_latin ?: '—' }}</td>
                <td class="text-center">{{ $v->codigo_exportacion ?: '—' }}</td>
                <td class="text-center">
                    @if ($v->receta == 1)
                        RECETA
                    @else
                        {{ $v->color ?: '—' }}
                    @endif
                </td>
                @if ($proveedor != '')
                    <td class="text-center">
                        <input type="checkbox" id="check_proveedor_{{ $v->id_variedad }}"
                            class="check_proveedor mouse-hand"
                            aria-label="Asignar {{ $v->nombre }} al proveedor seleccionado"
                            onchange="asignar_proveedor({{ $v->id_variedad }})"
                            {{ in_array($v->id_variedad, $variedades_del_proveedor) ? 'checked' : '' }}>
                    </td>
                @endif
                <td class="text-center">
                    <div class="btn-group">
                        <button type="button" class="btn btn-yura_default btn-xs dropdown-toggle"
                            data-toggle="dropdown" title="Acciones de variedad">
                            <i class="fa fa-fw fa-gears"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-right" role="menu">
                            <li>
                                <a href="javascript:void(0)" title="Editar"
                                    onclick="edit_variedad('{{ $v->id_variedad }}')">
                                    <i class="fa fa-fw fa-pencil"></i> Editar
                                </a>
                            </li>
                            @if ($v->receta == 1)
                                <li>
                                    <a href="javascript:void(0)" title="Administrar receta"
                                        onclick="admin_receta('{{ $v->id_variedad }}')">
                                        <i class="fa fa-fw fa-tree"></i> Administrar receta
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:void(0)" title="Productos de la receta"
                                        onclick="productos_receta('{{ $v->id_variedad }}')">
                                        <i class="fa fa-fw fa-gift"></i> Productos de la receta
                                    </a>
                                </li>
                            @endif
                            <li>
                                <a href="javascript:void(0)"
                                    title="{{ $v->estado == 1 ? 'Desactivar' : 'Activar' }}"
                                    onclick="cambiar_estado_variedad('{{ $v->id_variedad }}','{{ $v->estado }}')">
                                    <i class="fa fa-fw fa-{{ $v->estado == 1 ? 'trash' : 'unlock' }}"></i>
                                    {{ $v->estado == 1 ? 'Desactivar' : 'Activar' }}
                                </a>
                            </li>
                        </ul>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td class="text-center pv-vacio" colspan="{{ $proveedor != '' ? 6 : 5 }}">
                    <i class="fa fa-pagelines"></i><br>No hay variedades registradas para esta planta
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

<style>
    #th_fija_top_variedades th {
        position: sticky;
        top: 0;
        z-index: 2;
    }
</style>

<script>
    function asignar_all_variedades() {
        proveedor = $('#proveedor_seleccionada').val();
        planta = $('#planta_seleccionada').val();
        datos = {
            _token: '{{ csrf_token() }}',
            planta: planta,
            proveedor: proveedor,
        };
        if (planta != '' && proveedor != '')
            post_jquery_m('{{ url('plantas_variedades/asignar_all_variedades') }}', datos, function() {
                select_planta(planta);
            });
    }

    function asignar_proveedor(variedad) {
        proveedor = $('#proveedor_seleccionada').val();
        datos = {
            _token: '{{ csrf_token() }}',
            variedad: variedad,
            id_proveedor: proveedor,
        };
        post_jquery_m('{{ url('plantas_variedades/asignar_proveedor') }}', datos, function() {}, 'row_variedad_' +
            variedad);
    }

    function admin_receta(id_var, numero_receta = null) {
        datos = {
            id_var: id_var,
            numero_receta: numero_receta,
        };
        get_jquery('{{ url('plantas_variedades/admin_receta') }}', datos, function(retorno) {
            cerrar_modals();
            modal_view('modal_admin_receta', retorno, '<i class="fa fa-fw fa-plus"></i> Administrar receta',
                true, false, '{{ isPC() ? '95%' : '' }}');
        });
    }

    function productos_receta(id_var) {
        datos = {
            id_var: id_var,
        };
        get_jquery('{{ url('plantas_variedades/productos_receta') }}', datos, function(retorno) {
            modal_view('modal_productos_receta', retorno,
                '<i class="fa fa-fw fa-plus"></i> Productos de la receta',
                true, false, '{{ isPC() ? '95%' : '' }}');
        });
    }
</script>
