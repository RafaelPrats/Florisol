<div style="overflow-y: scroll; max-height: 500px">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d" id="table_inventario_cosecha">
        <thead>
            <tr class="tr_fija_top_0">
                <th class="text-center th_yura_green padding_lateral_5">
                    Pre-OT
                </th>
                <th class="text-center th_yura_green padding_lateral_5">
                    Pedido
                </th>
                <th class="text-center th_yura_green padding_lateral_5">
                    Receta
                </th>
                <th class="text-center th_yura_green padding_lateral_5">
                    Longitud
                </th>
                <th class="text-center th_yura_green padding_lateral_5">
                    Ramos
                </th>
                <th class="text-center th_yura_green padding_lateral_5">
                    Parcial
                </th>
                <th class="text-center th_yura_green padding_lateral_5">
                    Variedad
                </th>
                <th class="text-center th_yura_green padding_lateral_5">
                    Tallos
                </th>
                <th class="text-center th_yura_green padding_lateral_5">
                    Opciones
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach ($listado as $pos_o => $item)
                @php
                    $detalle_pedido = $item->detalle_import_pedido;
                    $pedido = $detalle_pedido->pedido;
                @endphp
                @foreach ($item->detalles as $pos_d => $det)
                    <tr onmouseover="$(this).addClass('bg-aqua')" onmouseleave="$(this).removeClass('bg-aqua')"
                        class="tr_ot_{{ $item->id_pre_orden_trabajo }}">
                        @if ($pos_d == 0)
                            <th class="text-center padding_lateral_5" style="border-color: #9d9d9d"
                                rowspan="{{ count($item->detalles) }}">
                                #{{ $item->id_pre_orden_trabajo }}
                            </th>
                            <th class="text-center padding_lateral_5" style="border-color: #9d9d9d"
                                rowspan="{{ count($item->detalles) }}">
                                #{{ $pedido->codigo }}
                                <br>
                                {{ $pedido->cliente->detalle()->nombre }}
                                <br>
                                {{ convertDateToText($item->fecha) }}
                            </th>
                            <th class="text-center padding_lateral_5" style="border-color: #9d9d9d"
                                rowspan="{{ count($item->detalles) }}">
                                {{ $detalle_pedido->variedad->nombre }}
                            </th>
                            <th class="text-center padding_lateral_5" style="border-color: #9d9d9d"
                                rowspan="{{ count($item->detalles) }}">
                                {{ $item->longitud }} <sup>cm</sup>
                            </th>
                            <th class="text-center padding_lateral_5" style="border-color: #9d9d9d"
                                rowspan="{{ count($item->detalles) }}">
                                {{ $item->ramos }}
                            </th>
                            <th class="text-center padding_lateral_5" style="border-color: #9d9d9d"
                                rowspan="{{ count($item->detalles) }}">
                                {{ $item->getTotalRamosParcial() }}
                            </th>
                        @endif
                        <th class="text-center padding_lateral_5" style="border-color: #9d9d9d">
                            {{ $det->variedad->nombre }}
                        </th>
                        <th class="text-center padding_lateral_5" style="border-color: #9d9d9d">
                            {{ $det->tallos * $item->ramos }}
                        </th>
                        @if ($pos_d == 0)
                            <th class="text-center padding_lateral_5" style="border-color: #9d9d9d"
                                rowspan="{{ count($item->detalles) }}">
                                @if ($item->estado == 1)
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-xs btn-yura_warning"
                                            style="margin-top: 5px"
                                            onclick="editar_preorden('{{ $item->id_pre_orden_trabajo }}')">
                                            <i class="fa fa-fw fa-edit"></i> Modificar
                                        </button>
                                        <button type="button" class="btn btn-xs btn-yura_primary"
                                            style="margin-top: 5px"
                                            onclick="convertir_a_orden_trabajo('{{ $item->id_pre_orden_trabajo }}')">
                                            <i class="fa fa-fw fa-check"></i> Convertir en OT
                                        </button>
                                        <button type="button" class="btn btn-xs btn-yura_danger"
                                            style="margin-top: 5px"
                                            onclick="eliminar_pre_orden_trabajo('{{ $item->id_pre_orden_trabajo }}')">
                                            <i class="fa fa-fw fa-trash"></i> Eliminar
                                        </button>
                                    </div>
                                    <div class="input-group">
                                        <input type="number" style="width: 100%; color: black" class="text-center"
                                            placeholder="Cantidad" min="1"
                                            id="convertir_parcial_{{ $item->id_pre_orden_trabajo }}" max="10">
                                        <div class="input-group-btn">
                                            <button type="button" class="btn btn-xs btn-yura_dark"
                                                onclick="convertir_parcial('{{ $item->id_pre_orden_trabajo }}')"
                                                style="height: 26px">
                                                <i class="fa fa-fw fa-file"></i> Convertir Parcial
                                            </button>
                                        </div>
                                    </div>
                                @else
                                    <span class="badge btn-yura_dark">
                                        Convertida en la OT #{{ $item->id_orden_trabajo }}
                                    </span>
                                @endif
                            </th>
                        @endif
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>
</div>

<script>
    function eliminar_pre_orden_trabajo(id) {
        texto =
            "<div class='alert alert-warning text-center'>¿Esta seguro de <b>ELIMINAR</b> la Pre-OT?</div>";

        modal_quest('modal_eliminar_pre_orden_trabajo', texto, 'Eliminar la Pre-OT', true, false, '40%',
            function() {
                datos = {
                    _token: '{{ csrf_token() }}',
                    id: id,
                }
                post_jquery_m('{{ url('ingreso_clasificacion/eliminar_pre_orden_trabajo') }}', datos, function() {
                    cerrar_modals();
                    if ($('#vista_actual').val() == 'ingreso_clasificacion') {
                        armar_combinacion($('#id_receta_armar').val(),
                            $('#longitud_receta_armar').val(),
                            $('#fecha_receta_armar').val());
                    }
                    if ($('#vista_actual').val() == 'planificacion') {
                        pos = $('#pos_selected').val();
                        fecha = $('#fecha_receta_armar').val();
                        modal_planificacion(pos, '' + fecha + '');
                    }
                    listar_reporte();
                });
            })
    }

    function convertir_a_orden_trabajo(id) {
        texto =
            "<div class='alert alert-warning text-center'>¿Esta seguro de <b>CONVERTIR en OT</b> la Pre-OT?</div>";

        modal_quest('modal_convertir_a_orden_trabajo', texto, 'Eliminar la Pre-OT', true, false, '40%',
            function() {
                datos = {
                    _token: '{{ csrf_token() }}',
                    id: id,
                    fecha: $('#fecha_filtro').val(),
                }
                post_jquery_m('{{ url('ingreso_clasificacion/convertir_a_orden_trabajo') }}', datos, function() {
                    cerrar_modals();
                    if ($('#vista_actual').val() == 'ingreso_clasificacion') {
                        armar_combinacion($('#id_receta_armar').val(),
                            $('#longitud_receta_armar').val(),
                            $('#fecha_receta_armar').val());
                    }
                    if ($('#vista_actual').val() == 'planificacion') {
                        pos = $('#pos_selected').val();
                        fecha = $('#fecha_receta_armar').val();
                        modal_planificacion(pos, '' + fecha + '');
                    }
                    listar_reporte();
                });
            })
    }

    function editar_preorden(id) {
        datos = {
            id: id,
        }
        get_jquery('{{ url('ingreso_clasificacion/editar_preorden') }}', datos, function(retorno) {
            modal_view('modal_editar_preorden', retorno,
                '<i class="fa fa-fw fa-plus"></i> Modificar Pre-OT',
                true, false, '{{ isPC() ? '98%' : '' }}',
                function() {});
        });
    }

    function convertir_parcial(id) {
        texto =
            "<div class='alert alert-warning text-center'>¿Esta seguro de <b>CONVETRIR PARCIALMENTE</b> la pre orden de trabajo?</div>";

        modal_quest('modal_convertir_parcial', texto, 'CONVETRIR PARCIALMENTE la Orden de Trabajo', true, false,
            '40%',
            function() {
                ramos = $('#convertir_parcial_' + id).val();
                if (ramos > 0) {
                    datos = {
                        _token: '{{ csrf_token() }}',
                        id: id,
                        ramos: ramos,
                    }
                    post_jquery_m('{{ url('ingreso_clasificacion/convertir_parcial') }}', datos, function(
                        retorno) {
                        cerrar_modals();
                        if ($('#vista_actual').val() == 'ingreso_clasificacion') {
                            armar_combinacion($('#id_receta_armar').val(),
                                $('#longitud_receta_armar').val(),
                                $('#fecha_receta_armar').val());
                        }
                        if ($('#vista_actual').val() == 'planificacion') {
                            pos = $('#pos_selected').val();
                            fecha = $('#fecha_receta_armar').val();
                            modal_planificacion(pos, '' + fecha + '');
                        }
                    });
                } else {
                    alerta(
                        '<div class="text-center alert alert-warning">La cantidad de ramos a <b>ARMAR</b> (<b>' +
                        armar + '</b>) no puede superar a la cantidad <b>RAMOS PEDIDOS</b>: (<b>' + por_armar +
                        '</b>)</div>');
                }
            })
    }
</script>
