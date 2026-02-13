<table width="100%" class="table-striped table-bordered" style="font-size: 0.9em; border-color: #9d9d9d"
    id="table_content_variedades">
    <thead>
        <tr class="tr_fija_top_0">
            <th class="text-center th_yura_green">Variedad</th>
            <th class="text-center th_yura_green">Codigo</th>
            <th class="text-center th_yura_green">Color</th>
            <th class="text-center th_yura_green hidden">Recetas</th>
            <th class="text-center th_yura_green hidden">Bloqueos</th>
            @if ($proveedor != '')
                <th class="text-center th_yura_green">
                    Asignar Proveedor
                    <br>
                    <button type="button" class="btn btn-xs btn-blobk btn-yura_default"
                        onclick="asignar_all_variedades()">
                        <i class="fa fa-fw fa-check"></i> Asignar Todas
                    </button>
                </th>
            @endif
            <th class="text-center th_yura_green">
                <div class="btn-group">
                    <button type="button" class="btn btn-xs btn-yura_default" title="Añadir Variedad"
                        onclick="add_variedad()">
                        <i class="fa fa-fw fa-plus"></i>
                    </button>
                    <button class="btn btn-xs btn-yura_dark" onclick="importar_recetas()">
                        <i class="fa fa-fw fa-upload"></i>
                    </button>
                </div>
            </th>
        </tr>
    </thead>
    <tbody>
        @foreach ($variedades as $v)
            <tr onmouseover="$(this).css('background-color','#add8e6')"
                onmouseleave="$(this).css('background-color','')" class="{{ $v->estado == 1 ? '' : 'error' }}"
                id="row_variedad_{{ $v->id_variedad }}">
                <td style="border-color: #9d9d9d" class="text-center">
                    {{ $v->nombre }}
                </td>
                <td style="border-color: #9d9d9d" class="text-center">
                    {{ $v->siglas }}
                </td>
                <td style="border-color: #9d9d9d" class="text-center">
                    @if ($v->receta == 1)
                        RECETA
                    @else
                        {{ $v->color }}
                    @endif
                </td>
                <td style="border-color: #9d9d9d" class="text-center hidden">
                    @if ($v->receta == 1)
                        {{-- $v->getCantidadRecetas() }} <i
                            class="fa fa-fw fa-dollar"></i>{{ $v->getTotalPrecioReceta() --}}
                    @endif
                </td>
                <td style="border-color: #9d9d9d" class="text-center hidden">
                    @if ($v->receta == 1)
                        {{--@php
                            $bloqueos = $v->getCantidadRecetasBloqueadas();
                        @endphp
                        {{ $bloqueos > 0 ? $bloqueos : '' }}--}}
                    @endif
                </td>
                @if ($proveedor != '')
                    <td style="border-color: #9d9d9d" class="text-center">
                        <input type="checkbox" id="check_proveedor_{{ $v->id_variedad }}"
                            class="check_proveedor mouse-hand" onchange="asignar_proveedor({{ $v->id_variedad }})"
                            {{ in_array($v->id_variedad, $variedades_del_proveedor) ? 'checked' : '' }}>
                    </td>
                @endif
                <td style="border-color: #9d9d9d" class="text-center">
                    <div class="btn-group">
                        <button type="button" class="btn btn-yura_default btn-xs dropdown-toggle"
                            data-toggle="dropdown">
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
                                    <a href="javascript:void(0)" title="Administrar Receta"
                                        onclick="admin_receta('{{ $v->id_variedad }}')">
                                        <i class="fa fa-fw fa-tree"></i> Administrar Receta
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:void(0)" title="Productos de la Receta"
                                        onclick="productos_receta('{{ $v->id_variedad }}')">
                                        <i class="fa fa-fw fa-gift"></i> Productos de la Receta
                                    </a>
                                </li>
                            @endif
                            {{-- <li>
                                <a href="javascript:void(0)" title="Clasificaciones Unitarias"
                                    onclick="vincular_variedad_unitaria('{{ $v->id_variedad }}')">
                                    <i class="fa fa-fw fa-filter"></i> Clasificaciones Unitarias
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0)" title="Regalías"
                                    onclick="add_regalias('{{ $v->id_variedad }}')">
                                    <i class="fa fa-fw fa-usd"></i> Regalías
                                </a>
                            </li> --}}
                            <li>
                                <a href="javascript:void(0)" title="{{ $v->estado == 1 ? 'Desactivar' : 'Activar' }}"
                                    onclick="cambiar_estado_variedad('{{ $v->id_variedad }}','{{ $v->estado }}')">
                                    <i class="fa fa-fw fa-{{ $v->estado == 1 ? 'trash' : 'unlock' }}"></i>
                                    {{ $v->estado == 1 ? 'Desactivar' : 'Activar' }}
                                </a>
                            </li>
                        </ul>
                    </div>
                    {{-- <button class="btn btn-xs btn-default" type="button" title="Precio"
                            onclick="add_precio('{{$v->id_variedad}}')">
                        <i class="fa fa-usd"></i>
                    </button> --}}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

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
