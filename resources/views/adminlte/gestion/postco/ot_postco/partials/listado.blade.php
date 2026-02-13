<table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d" id="table_inventario_cosecha">
    <thead>
        <tr class="tr_fija_top_0">
            <th class="text-center th_yura_green padding_lateral_5">
                OT
            </th>
            <th class="text-center th_yura_green padding_lateral_5">
                Cliente
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
            <th class="text-center th_yura_green padding_lateral_5">
                Observacion
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
                $postco = $item->postco;
                $cliente = $item->cliente;
                $estado = $item->getEstado();
                $despachador = $item->despachador;
                $disponible = true;
            @endphp
            @foreach ($item->detalles as $pos_d => $det)
                @php
                    $inventario = getTotalInventarioByVariedad($det->id_item);
                    if ($inventario < $det->unidades * $item->ramos) {
                        $disponible = false;
                    }
                @endphp
                <tr onmouseover="$(this).addClass('bg-aqua')" onmouseleave="$(this).removeClass('bg-aqua')">
                    @if ($pos_d == 0)
                        <th class="text-center padding_lateral_5" style="border-color: #9d9d9d"
                            rowspan="{{ count($item->detalles) }}">
                            #{{ $item->id_ot_postco }}
                        </th>
                        <th class="text-center padding_lateral_5" style="border-color: #9d9d9d"
                            rowspan="{{ count($item->detalles) }}">
                            {{ $cliente->detalle()->nombre }}
                            <br>
                            {{ convertDateToText($postco->fecha) }}
                        </th>
                        <th class="text-center padding_lateral_5" style="border-color: #9d9d9d"
                            rowspan="{{ count($item->detalles) }}">
                            {{ $postco->variedad->nombre }}
                        </th>
                        <th class="text-center padding_lateral_5" style="border-color: #9d9d9d"
                            rowspan="{{ count($item->detalles) }}">
                            {{ $postco->longitud }} <sup>cm</sup>
                        </th>
                        <th class="text-center padding_lateral_5" style="border-color: #9d9d9d"
                            rowspan="{{ count($item->detalles) }}">
                            {{ $item->ramos }}
                        </th>
                        <th class="text-center padding_lateral_5" style="border-color: #9d9d9d"
                            rowspan="{{ count($item->detalles) }}">
                            {{ $item->armados }}
                        </th>
                    @endif
                    <th class="text-center padding_lateral_5" style="border-color: #9d9d9d">
                        {{ $det->item->nombre }}
                    </th>
                    <th class="text-center padding_lateral_5" style="border-color: #9d9d9d">
                        {{ $det->unidades }}
                    </th>
                    <th class="text-center padding_lateral_5 {{ $inventario < $det->tallos ? 'error' : '' }}"
                        style="border-color: #9d9d9d">
                        {{ number_format($inventario) }}
                    </th>
                    @if ($pos_d == 0)
                        <th class="text-center" style="border-color: #9d9d9d" rowspan="{{ count($item->detalles) }}">
                            <select id="despachador_{{ $item->id_ot_postco }}" style="width: 100%; color: black">
                                <option value="">Seleccione...</option>
                                @foreach ($despachadores as $desp)
                                    <option value="{{ $desp->id_despachador }}"
                                        {{ $desp->id_despachador == $item->id_despachador ? 'selected' : '' }}>
                                        {{ $desp->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-xs btn-block btn-yura_dark"
                                onclick="update_despachador('{{ $item->id_ot_postco }}')">
                                <i class="fa fa-fw fa-save"></i> Grabar
                            </button>
                            <button type="button" class="btn btn-xs btn-block btn-yura_warning"
                                onclick="modal_reclamos('{{ $item->id_ot_postco }}')" style="margin-top: 0">
                                <i class="fa fa-fw fa-exchange"></i> Reclamos
                            </button>
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d" rowspan="{{ count($item->detalles) }}">
                            <textarea id="observacion_{{ $item->id_ot_postco }}" rows="3" style="width: 100%; color: black;"
                                placeholder="Observacion...">{{ $item->observacion }}</textarea>
                            <button type="button" class="btn btn-xs btn-block btn-yura_dark"
                                onclick="update_observacion('{{ $item->id_ot_postco }}')">
                                <i class="fa fa-fw fa-save"></i> Grabar
                            </button>
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d" rowspan="{{ count($item->detalles) }}">
                            @if (isset($estado))
                                {!! $estado['html'] !!}
                                @if ($estado['estado'] == 'Pendiente')
                                    <br>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-xs btn-yura_primary"
                                            style="margin-top: 5px"
                                            onclick="despachar_orden_trabajo('{{ $item->id_ot_postco }}')"
                                            id="btn_despachar_{{ $item->id_ot_postco }}">
                                            <i class="fa fa-fw fa-check"></i> Despachar
                                        </button>
                                        <button type="button" class="btn btn-xs btn-yura_primary" disabled
                                            id="btn_sin_flor_{{ $item->id_ot_postco }}" style="margin-top: 5px">
                                            <i class="fa fa-fw fa-ban"></i> Sin Flor
                                        </button>
                                        <button type="button" class="btn btn-xs btn-yura_danger"
                                            style="margin-top: 5px"
                                            onclick="eliminar_orden_trabajo('{{ $item->id_ot_postco }}')">
                                            <i class="fa fa-fw fa-trash"></i> Eliminar
                                        </button>
                                    </div>
                                @endif
                                @if ($item->armados == 0)
                                    <br>
                                    <div class="input-group">
                                        <input type="number" style="width: 100%; color: black" class="text-center"
                                            placeholder="Armar" min="1"
                                            id="ramos_armados_{{ $item->id_ot_postco }}"
                                            max="{{ $item->ramos - $item->armados }}">
                                        <div class="input-group-btn">
                                            <button type="button" class="btn btn-xs btn-yura_dark"
                                                onclick="store_armar('{{ $item->id_ot_postco }}')"
                                                style="height: 26px">
                                                <i class="fa fa-fw fa-save"></i> Grabar
                                            </button>
                                        </div>
                                    </div>
                                @endif
                            @endif
                        </th>
                        <th class="text-center padding_lateral_5" style="border-color: #9d9d9d"
                            rowspan="{{ count($item->detalles) }}">
                            <div class="btn-group">
                                <button type="button" class="btn btn-xs btn-yura_primary" title="Excel"
                                    onclick="exportar_orden_trabajo('{{ $item->id_ot_postco }}')">
                                    <i class="fa fa-fw fa-file-excel-o"></i>
                                </button>
                                <button type="button" class="btn btn-xs btn-yura_default" title="PDF"
                                    onclick="exportar_orden_trabajo_pdf('{{ $item->id_ot_postco }}')">
                                    <i class="fa fa-fw fa-file-pdf-o"></i>
                                </button>
                            </div>
                        </th>
                    @endif
                </tr>
            @endforeach
            @if ($disponible)
                <script>
                    $('#btn_sin_flor_{{ $item->id_ot_postco }}').addClass('hidden')
                </script>
            @else
                <script>
                    $('#btn_despachar_{{ $item->id_ot_postco }}').addClass('hidden')
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
                    post_jquery_m('{{ url('ot_postco/store_armar') }}', datos, function() {
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
                post_jquery_m('{{ url('ot_postco/despachar_orden_trabajo') }}', datos, function() {
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
                post_jquery_m('{{ url('preproduccion/eliminar_orden_trabajo') }}', datos, function() {
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
                    id_ot: id,
                    despachador: $('#despachador_' + id).val(),
                }
                post_jquery_m('{{ url('preproduccion/update_despachador') }}', datos, function() {
                    cerrar_modals();
                });
            })
    }

    function update_observacion(id) {
        datos = {
            _token: '{{ csrf_token() }}',
            id_ot: id,
            observacion: $('#observacion_' + id).val(),
        }
        post_jquery_m('{{ url('ot_postco/update_observacion') }}', datos, function() {
            cerrar_modals();
        });
    }

    function exportar_orden_trabajo(id) {
        $.LoadingOverlay('show');
        window.open('{{ url('preproduccion/exportar_orden_trabajo') }}?id=' + id, '_blank');
        $.LoadingOverlay('hide');
    }

    function exportar_orden_trabajo_pdf(id) {
        $.LoadingOverlay('show');
        window.open('{{ url('ot_postco/exportar_orden_trabajo_pdf') }}?id=' + id, '_blank');
        $.LoadingOverlay('hide');
    }

    function modal_reclamos(id) {
        datos = {
            id: id
        };
        get_jquery('{{ url('ot_postco/modal_reclamos') }}', datos, function(retorno) {
            modal_view('modal_modal_reclamos', retorno, '<i class="fa fa-fw fa-plus"></i> Reclamos',
                true, false, '{{ isPC() ? '90%' : '' }}',
                function() {});
        })
    }
</script>
