<table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d" id="table_inventario_cosecha">
    <thead>
        <tr class="tr_fija_top_0">
            <th class="text-center th_yura_green padding_lateral_5">
                OT
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
                Armados
            </th>
            <th class="text-center th_yura_green padding_lateral_5">
                Variedad
            </th>
            <th class="text-center th_yura_green padding_lateral_5">
                Tallos
            </th>
            <th class="text-center th_yura_green padding_lateral_5">
                Inventario
            </th>
            <th class="text-center th_yura_green padding_lateral_5">
                Responsable
            </th>
            <th class="text-center th_yura_green padding_lateral_5" style="min-width: 180px">
                Estado
            </th>
            <th class="text-center th_yura_green padding_lateral_5" style="min-width: 70px">
            </th>
        </tr>
    </thead>
    <tbody>
        @foreach ($listado as $pos_o => $item)
            @php
                $detalle_pedido = $item['orden']->detalle_import_pedido;
                $pedido = $detalle_pedido->pedido;
                $estado = $item['orden']->getEstado();
                $despachador = $item['orden']->despachador;
                $disponible = true;
            @endphp
            @foreach ($item['orden']->detalles as $pos_d => $det)
                @php
                    $inventario = getTotalInventarioByVariedad($det->id_variedad);
                    if ($inventario < $det->tallos) {
                        $disponible = false;
                    }
                @endphp
                <tr onmouseover="$(this).addClass('bg-aqua')" onmouseleave="$(this).removeClass('bg-aqua')">
                    @if ($pos_d == 0)
                        <th class="text-center padding_lateral_5" style="border-color: #9d9d9d"
                            rowspan="{{ count($item['orden']->detalles) }}">
                            #{{ $item['orden']->id_orden_trabajo }}
                        </th>
                        <th class="text-center padding_lateral_5" style="border-color: #9d9d9d"
                            rowspan="{{ count($item['orden']->detalles) }}">
                            #{{ $pedido->codigo }}
                            <br>
                            {{ $pedido->cliente->detalle()->nombre }}
                            <br>
                            {{ convertDateToText($pedido->fecha) }}
                        </th>
                        <th class="text-center padding_lateral_5" style="border-color: #9d9d9d"
                            rowspan="{{ count($item['orden']->detalles) }}">
                            {{ $detalle_pedido->variedad->nombre }}
                        </th>
                        <th class="text-center padding_lateral_5" style="border-color: #9d9d9d"
                            rowspan="{{ count($item['orden']->detalles) }}">
                            {{ $item['orden']->longitud }} <sup>cm</sup>
                        </th>
                        <th class="text-center padding_lateral_5" style="border-color: #9d9d9d"
                            rowspan="{{ count($item['orden']->detalles) }}">
                            {{ $item['orden']->ramos }}
                        </th>
                        <th class="text-center padding_lateral_5" style="border-color: #9d9d9d"
                            rowspan="{{ count($item['orden']->detalles) }}">
                            {{ $item['orden']->ramos_armados }}
                        </th>
                    @endif
                    <th class="text-center padding_lateral_5" style="border-color: #9d9d9d">
                        {{ $det->variedad->nombre }}
                    </th>
                    <th class="text-center padding_lateral_5" style="border-color: #9d9d9d">
                        {{ $det->tallos }}
                    </th>
                    <th class="text-center padding_lateral_5 {{ $inventario < $det->tallos ? 'error' : '' }}"
                        style="border-color: #9d9d9d">
                        {{ number_format($inventario) }}
                    </th>
                    @if ($pos_d == 0)
                        <th class="text-center padding_lateral_5" style="border-color: #9d9d9d"
                            rowspan="{{ count($item['orden']->detalles) }}">
                            <select id="despachador_{{ $item['orden']->id_orden_trabajo }}"
                                style="width: 100%; color: black">
                                <option value="">Seleccione...</option>
                                @foreach ($despachadores as $desp)
                                    <option value="{{ $desp->id_despachador }}"
                                        {{ $desp->id_despachador == $item['orden']->id_despachador ? 'selected' : '' }}>
                                        {{ $desp->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-xs btn-block btn-yura_dark"
                                onclick="update_despachador('{{ $item['orden']->id_orden_trabajo }}')">
                                <i class="fa fa-fw fa-save"></i> Grabar
                            </button>
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d"
                            rowspan="{{ count($item['orden']->detalles) }}">
                            @if (isset($estado))
                                {!! $estado['html'] !!}
                                @if ($estado['estado'] == 'Pendiente')
                                    <br>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-xs btn-yura_primary"
                                            style="margin-top: 5px"
                                            onclick="despachar_orden_trabajo('{{ $item['orden']->id_orden_trabajo }}')"
                                            id="btn_despachar_{{ $item['orden']->id_orden_trabajo }}">
                                            <i class="fa fa-fw fa-check"></i> Despachar
                                        </button>
                                        <button type="button" class="btn btn-xs btn-yura_primary" disabled
                                            id="btn_sin_flor_{{ $item['orden']->id_orden_trabajo }}"
                                            style="margin-top: 5px">
                                            <i class="fa fa-fw fa-ban"></i> Sin Flor
                                        </button>
                                        <button type="button" class="btn btn-xs btn-yura_danger"
                                            style="margin-top: 5px"
                                            onclick="eliminar_orden_trabajo('{{ $item['orden']->id_orden_trabajo }}')">
                                            <i class="fa fa-fw fa-trash"></i> Eliminar
                                        </button>
                                    </div>
                                @endif
                                @if ($item['orden']->armado == 0)
                                    <br>
                                    <div class="input-group">
                                        <input type="number" style="width: 100%; color: black" class="text-center"
                                            placeholder="Armar" min="1"
                                            id="ramos_armados_{{ $item['orden']->id_orden_trabajo }}"
                                            max="{{ $item['orden']->ramos - $item['orden']->ramos_armados }}">
                                        <div class="input-group-btn">
                                            <button type="button" class="btn btn-xs btn-yura_dark"
                                                onclick="store_armar('{{ $item['orden']->id_orden_trabajo }}')"
                                                style="height: 26px">
                                                <i class="fa fa-fw fa-save"></i> Grabar
                                            </button>
                                        </div>
                                    </div>
                                @endif
                            @endif
                        </th>
                        <th class="text-center padding_lateral_5" style="border-color: #9d9d9d"
                            rowspan="{{ count($item['orden']->detalles) }}">
                            <div class="btn-group">
                                <button type="button" class="btn btn-xs btn-yura_primary" title="Excel"
                                    onclick="exportar_orden_trabajo('{{ $item['orden']->id_orden_trabajo }}')">
                                    <i class="fa fa-fw fa-file-excel-o"></i>
                                </button>
                                <button type="button" class="btn btn-xs btn-yura_default" title="PDF"
                                    onclick="exportar_orden_trabajo_pdf('{{ $item['orden']->id_orden_trabajo }}')">
                                    <i class="fa fa-fw fa-file-pdf-o"></i>
                                </button>
                            </div>
                        </th>
                    @endif
                </tr>
            @endforeach
            @if ($disponible)
                <script>
                    $('#btn_sin_flor_{{ $item['orden']->id_orden_trabajo }}').addClass('hidden')
                </script>
            @else
                <script>
                    $('#btn_despachar_{{ $item['orden']->id_orden_trabajo }}').addClass('hidden')
                </script>
            @endif
        @endforeach
    </tbody>
</table>

<script>
    function store_armar(id) {
        texto =
            "<div class='alert alert-info text-center' style='font-size: 16px'>¿Esta seguro de <b>ARMAR</b> los ramos?</div>";

        modal_quest('modal_store_armar', texto, 'Despachar la Orden de Trabajo', true, false, '40%',
            function() {
                datos = {
                    _token: '{{ csrf_token() }}',
                    id: id,
                    armar: parseInt($('#ramos_armados_' + id).val()),
                }
                if (datos['armar'] <= parseInt($('#ramos_armados_' + id).prop('max')))
                    post_jquery_m('{{ url('orden_trabajo/store_armar') }}', datos, function() {
                        cerrar_modals();
                        listar_reporte();
                    });
                else
                    alerta(
                        '<div class="alert alert-warning text-center" style="font-size: 16px">La cantidad de ramos a <b>ARMAR</b> excede la cantidad <b>FALTANTE</b></div>'
                    )
            })
    }

    function despachar_orden_trabajo(id) {
        texto =
            "<div class='alert alert-info text-center'>¿Esta seguro de <b>DESPACHAR</b> la orden de trabajo?</div>";

        modal_quest('modal_despachar_orden_trabajo', texto, 'Despachar la Orden de Trabajo', true, false, '40%',
            function() {
                datos = {
                    _token: '{{ csrf_token() }}',
                    id: id,
                }
                post_jquery_m('{{ url('orden_trabajo/despachar_orden_trabajo') }}', datos, function() {
                    cerrar_modals();
                    listar_reporte();
                });
            })
    }

    function eliminar_orden_trabajo(id) {
        texto =
            "<div class='alert alert-warning text-center'>¿Esta seguro de <b>ELIMINAR</b> la orden de trabajo?</div>";

        modal_quest('modal_eliminar_orden_trabajo', texto, 'Eliminar la Orden de Trabajo', true, false, '40%',
            function() {
                datos = {
                    _token: '{{ csrf_token() }}',
                    id: id,
                }
                post_jquery_m('{{ url('orden_trabajo/eliminar_orden_trabajo') }}', datos, function() {
                    cerrar_modals();
                    listar_reporte();
                });
            })
    }

    function update_despachador(id) {
        texto =
            "<div class='alert alert-info text-center'>¿Esta seguro de <b>ASIGNAR</b> el despachador?</div>";

        modal_quest('modal_update_despachador', texto, 'Mensaje de confirmacion', true, false, '40%',
            function() {
                datos = {
                    _token: '{{ csrf_token() }}',
                    id: id,
                    despachador: $('#despachador_' + id).val(),
                }
                post_jquery_m('{{ url('orden_trabajo/update_despachador') }}', datos, function() {
                    cerrar_modals();
                    //listar_reporte();
                });
            })
    }

    function exportar_orden_trabajo(id) {
        $.LoadingOverlay('show');
        window.open('{{ url('ingreso_clasificacion/exportar_orden_trabajo') }}?id=' + id, '_blank');
        $.LoadingOverlay('hide');
    }

    function exportar_orden_trabajo_pdf(id) {
        $.LoadingOverlay('show');
        window.open('{{ url('ingreso_clasificacion/exportar_orden_trabajo_pdf') }}?id=' + id, '_blank');
        $.LoadingOverlay('hide');
    }
</script>
