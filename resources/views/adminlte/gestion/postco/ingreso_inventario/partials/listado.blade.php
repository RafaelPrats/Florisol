<div class="ingreso-table-card">
    <div class="ingreso-table-heading">
        <div>
            <h3><i class="fa fa-cubes"></i> Inventario por planta y variedad</h3>
            <span>Expande una planta para revisar existencias y recepciones pendientes.</span>
        </div>
        <button type="button" class="btn btn-yura_primary ingreso-add-button" onclick="modal_add()">
            <i class="fa fa-plus"></i> Ingreso manual
        </button>
    </div>
    <div class="ingreso-table-scroll">
    <table class="table-bordered ingreso-list-table" id="table_listado">
        <thead>
            <tr class="ingreso-list-header">
                <th class="padding_lateral_5 th_yura_green">
                    Fecha
                </th>
                <th class="padding_lateral_5 th_yura_green">
                    Variedad
                </th>
                <th class="padding_lateral_5" style="width: 90px; background-color: #dddddd">
                    Ramos Ingresados
                </th>
                <th class="padding_lateral_5 bg-yura_dark" style="width: 90px">
                    TxR
                </th>
                <th class="padding_lateral_5 bg-yura_dark" style="width: 90px">
                    Longitud
                </th>
                <th class="padding_lateral_5" style="width: 90px; background-color: cyan">
                    Pendiente
                </th>
                <th class="padding_lateral_5 bg-yura_dark" style="width: 90px">
                    Tallos Disponibles
                </th>
                <th class="text-center bg-yura_dark" style="width: 90px">
                    Acciones
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach ($listado as $item)
                @php
                    $ramos_pta = 0;
                    $tallos_pta = 0;
                    $pendiente_pta = 0;
                    $existe_pta = false;
                    foreach ($item['variedades'] as $var) {
                        $ramos_pta += $var->ramos;
                        $tallos_pta += $var->disponibles;
                        $getDetalleApiStoreCajasByVariedad = getDetalleApiStoreCajasByVariedad(
                            $var->id_variedad,
                            $var->tallos_x_ramo,
                            $var->longitud,
                            $var->id_empresa,
                            $var->fecha,
                        );
                        $ramos_pendientes = 0;
                        foreach ($getDetalleApiStoreCajasByVariedad as $detApi) {
                            if ($detApi->id_api_store_cajas == $documento || $documento == '') {
                                $ramos_pendientes += $detApi->ramos;
                                $existe_pta = true;
                            }
                        }
                        $pendiente_pta += $ramos_pendientes;
                    }
                @endphp
                @if ($existe_pta || $documento == '')
                    <tr class="ingreso-plant-row mouse-hand"
                        onclick="toggle_planta_ingreso(this, '{{ $item['planta']->id_planta }}')">
                        <th class="padding_lateral_5" colspan="2">
                            <i class="fa fa-fw fa-caret-right ingreso-plant-caret"></i>
                            <i class="fa fa-leaf"></i> {{ $item['planta']->nombre }}
                        </th>
                        <th class="text-center">
                            {{ number_format($ramos_pta) }}
                        </th>
                        <th class="text-center" colspan="2">
                        </th>
                        <th class="text-center {{ $pendiente_pta > 0 ? 'ingreso-pending-cell' : '' }}">
                            @if ($pendiente_pta > 0)
                                {{ number_format($pendiente_pta) }}
                            @endif
                        </th>
                        <th class="text-center">
                            {{ number_format($tallos_pta) }}
                        </th>
                        <th class="padding_lateral_5" colspan="1">
                        </th>
                    </tr>
                    @foreach ($item['variedades'] as $pos_v => $var)
                        @php
                            $ramos_pendientes = 0;
                            $getDetalleApiStoreCajasByVariedad = getDetalleApiStoreCajasByVariedad(
                                $var->id_variedad,
                                $var->tallos_x_ramo,
                                $var->longitud,
                                $var->id_empresa,
                                $var->fecha,
                            );
                            $existe_var = false;
                            foreach ($getDetalleApiStoreCajasByVariedad as $detApi) {
                                if ($detApi->id_api_store_cajas == $documento || $documento == '') {
                                    $ramos_pendientes += $detApi->ramos;
                                    $existe_var = true;
                                }
                            }
                        @endphp
                        @if ($existe_var || ($documento == '' && $var->disponibles > 0))
                            <tr class="ingreso-variety-row tr_planta_{{ $item['planta']->id_planta }} hidden">
                                <th class="padding_lateral_5">
                                    {{ $var->fecha }}
                                </th>
                                <th class="padding_lateral_5 mouse-hand"
                                    onclick="toggle_detalles_ingreso(this, '{{ $var->id_variedad }}_{{ $pos_v }}')">
                                    {{ $var->nombre }}
                                    @if ($ramos_pendientes > 0)
                                        <i class="fa fa-fw fa-caret-right ingreso-detail-caret"></i>
                                    @endif
                                </th>
                                <th>
                                    <input type="number" class="ingreso-number-input text-center"
                                        id="ramos_ingresados_{{ $var->row_key }}"
                                        value="{{ $var->ramos }}" readonly>
                                </th>
                                <th class="padding_lateral_5">
                                    {{ $var->tallos_x_ramo }}
                                </th>
                                <th class="padding_lateral_5">
                                    {{ $var->longitud }}
                                </th>
                                <th>
                                    @if ($ramos_pendientes > 0)
                                        <input type="number" class="ingreso-number-input ingreso-pending-input text-center" readonly
                                            id="ramos_pendiente_{{ $var->row_key }}"
                                            value="{{ $ramos_pendientes }}">
                                    @endif
                                </th>
                                <th>
                                    <input type="number" class="ingreso-number-input text-center"
                                        id="tallos_disponibles_{{ $var->row_key }}"
                                        value="{{ $var->disponibles }}">
                                </th>
                                <th class="text-center">
                                    <div class="btn-group">
                                        {{-- <button type="button" class="btn btn-xs btn-yura_primary"
                                            onclick="update_inventario('{{ $var->id_inventario_recepcion }}')">
                                            <i class="fa fa-fw fa-edit"></i>
                                        </button> --}}
                                        @if ($var->disponibles > 0)
                                            <button type="button" class="btn btn-xs btn-yura_dark ingreso-row-action"
                                                title="Mover inventario"
                                                onclick="mover_inventario('{{ $var->id_inventario_recepcion }}')">
                                                <i class="fa fa-fw fa-exchange"></i>
                                            </button>
                                        @endif
                                        {{-- <button type="button" class="btn btn-xs btn-yura_danger"
                                            title="Eliminar inventario"
                                            onclick="delete_inventario('{{ $var->id_inventario_recepcion }}')">
                                            <i class="fa fa-fw fa-times"></i>
                                        </button> --}}
                                    </div>
                                </th>
                            </tr>
                            @foreach ($getDetalleApiStoreCajasByVariedad as $detApi)
                                @if ($detApi->id_api_store_cajas == $documento || $documento == '')
                                    <tr
                                        class="tr_detApi_{{ $var->id_variedad }}_{{ $pos_v }} tr_all_detApi_{{ $item['planta']->id_planta }} hidden">
                                        <td class="text-right padding_lateral_5 ingreso-document-cell" colspan="4">
                                            <input type="checkbox"
                                                id="check_detApi_{{ $detApi->id_detalle_api_store_cajas }}"
                                                class="ramos_pendiente check_all_ramos_pendiente"
                                                data-id_detalle_api_store_cajas="{{ $detApi->id_detalle_api_store_cajas }}">
                                            {{ $detApi->documento }}
                                        </td>
                                        <th class="text-center text-sm">
                                            Ventas
                                        </th>
                                        <th>
                                            <input type="number" class="ingreso-pending-input text-center"
                                                id="detApi_ramos_pendiente_ventas_{{ $detApi->id_detalle_api_store_cajas }}"
                                                value="{{ $detApi->ramos }}"
                                                onchange="$('#check_detApi_{{ $detApi->id_detalle_api_store_cajas }}').prop('checked', true)">
                                        </th>
                                        <th class="text-center text-sm">
                                            Producción
                                        </th>
                                        <th>
                                            <input type="number" class="ingreso-pending-input text-center"
                                                id="detApi_ramos_pendiente_produccion_{{ $detApi->id_detalle_api_store_cajas }}"
                                                value="0"
                                                onchange="$('#check_detApi_{{ $detApi->id_detalle_api_store_cajas }}').prop('checked', true)">
                                        </th>
                                    </tr>
                                @endif
                            @endforeach
                        @endif
                    @endforeach
                @endif
            @endforeach
        <tr class="ingreso-list-footer">
            <th class="th_yura_green" colspan="2"></th>
            <th class="text-center" colspan="2">
                <label for="check_all" class="mouse-hand">
                    Seleccionar todos
                </label>
                <input type="checkbox" id="check_all"
                    onchange="$('.check_all_ramos_pendiente').prop('checked', $(this).prop('checked'))">
            </th>
            <th>
                <button type="button" class="btn btn-xs btn-yura_dark" onclick="recibir_all_pendientes()">
                    <i class="fa fa-fw fa-check"></i> Recibir todo
                </button>
            </th>
            <th class="th_yura_green" colspan="3"></th>
        </tr>
    </tbody>
    </table>
    </div>
</div>

<style>
    .ingreso-table-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 11px 14px;
        border-bottom: 1px solid #dce9e4;
    }

    .ingreso-table-heading h3 {
        margin: 0 0 3px;
        color: #003729;
        font-size: 15px;
        font-weight: 700;
    }

    .ingreso-table-heading h3 i {
        margin-right: 5px;
        color: #00B388;
    }

    .ingreso-table-heading span {
        color: #5A7177;
        font-size: 11px;
    }

    .ingreso-table-heading .ingreso-add-button {
        flex: 0 0 auto;
        padding: 7px 10px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 700;
    }

    .ingreso-table-scroll {
        max-height: min(68vh, 680px);
        overflow: auto;
    }

    .ingreso-list-table {
        width: 100%;
        min-width: 720px;
        margin: 0;
        border-collapse: separate;
        border-spacing: 0;
        color: #003729;
        font-size: 13px;
    }

    .ingreso-list-table th,
    .ingreso-list-table td {
        padding: 5px 8px;
        border-right: 1px solid #dce9e4;
        border-bottom: 1px solid #dce9e4;
        vertical-align: middle;
    }

    .ingreso-list-header th {
        position: sticky;
        top: 0;
        z-index: 5;
        padding-top: 7px;
        padding-bottom: 7px;
        background: #003729 !important;
        color: #fff;
        font-size: 12px;
        white-space: nowrap;
    }

    .ingreso-plant-row {
        cursor: pointer;
    }

    .ingreso-plant-row th {
        background: #e8f6f1;
        color: #003729;
        font-weight: 700;
    }

    .ingreso-plant-row:hover th {
        background: #d8efe6;
    }

    .ingreso-plant-caret {
        color: #00B388;
    }

    .ingreso-variety-row th {
        background: #fff;
        font-weight: 500;
    }

    .ingreso-variety-row:nth-child(even) th {
        background: #f6faf8;
    }

    .ingreso-variety-row:hover th {
        background: #eaf6f0;
    }

    .ingreso-number-input,
    .ingreso-pending-input {
        width: 100%;
        min-width: 68px;
        height: 29px;
        padding: 3px 6px;
        border: 1px solid #dce9e4;
        border-radius: 4px;
        background: #f4faf7;
        color: #003729;
        font-size: 13px;
        font-weight: 700;
    }

    .ingreso-number-input[readonly],
    .ingreso-pending-input {
        background: #e8f6f1;
    }

    .ingreso-pending-cell,
    .ingreso-document-cell {
        background: #eff9f5;
    }

    .ingreso-list-table .btn {
        padding: 4px 7px;
        border-radius: 5px;
    }

    .ingreso-list-footer th {
        position: sticky;
        bottom: 0;
        z-index: 2;
        background: #003729;
        color: #fff;
    }

    .ingreso-list-footer label {
        margin: 0 4px 0 0;
    }

    .ingreso-action-confirm {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 12px;
        border: 1px solid #dce9e4;
        border-radius: 8px;
        background: #f4faf7;
        color: #003729;
    }

    .ingreso-action-confirm.is-warning {
        border-color: #f0d89a;
        background: #fff9e9;
    }

    .ingreso-action-confirm-icon {
        display: grid;
        width: 35px;
        height: 35px;
        flex: 0 0 35px;
        place-items: center;
        border-radius: 9px;
        background: #e4f5ee;
        color: #00B388;
        font-size: 15px;
    }

    .ingreso-action-confirm.is-warning .ingreso-action-confirm-icon {
        background: #ffefc5;
        color: #936400;
    }

    .ingreso-action-confirm strong {
        display: block;
        margin-bottom: 3px;
        font-size: 13px;
    }

    .ingreso-action-confirm p {
        margin: 0;
        color: #5A7177;
        font-size: 11px;
        line-height: 1.45;
    }

    @media (max-width: 600px) {
        .ingreso-table-heading {
            align-items: flex-start;
            flex-direction: column;
        }

        .ingreso-table-heading .ingreso-add-button {
            width: 100%;
        }
    }
</style>

<script>
    function ingreso_confirmacion(titulo, detalle, icono, esAdvertencia) {
        return '<div class="ingreso-action-confirm ' + (esAdvertencia ? 'is-warning' : '') + '">' +
            '<span class="ingreso-action-confirm-icon"><i class="fa ' + icono + '"></i></span>' +
            '<div><strong>' + titulo + '</strong><p>' + detalle + '</p></div></div>';
    }

    function modal_add() {
        datos = {}
        get_jquery('{{ url('ingreso_inventario/modal_add') }}', datos, function(retorno) {
            modal_view('modal_modal_add', retorno,
                '<i class="fa fa-plus"></i> Ingreso manual de inventario',
                true, false, '{{ isPC() ? '95%' : '100%' }}',
                function() {});
        })
    }

    function toggle_planta_ingreso(row, planta) {
        $('.tr_planta_' + planta).toggleClass('hidden');
        $('.tr_all_detApi_' + planta).addClass('hidden');
        $(row).find('.ingreso-plant-caret')
            .toggleClass('fa-caret-right')
            .toggleClass('fa-caret-down');
    }

    function toggle_detalles_ingreso(cell, id) {
        $('.tr_detApi_' + id).toggleClass('hidden');
        $(cell).find('.ingreso-detail-caret')
            .toggleClass('fa-caret-right')
            .toggleClass('fa-caret-down');
    }

    function update_inventario(id) {
        var texto = ingreso_confirmacion('¿Modificar el inventario?',
            'Se actualizarán los ramos ingresados y los tallos disponibles de esta variedad.',
            'fa-pencil', true) +
            '<div class="input-group">' +
            '<div class="input-group-addon span-input-group-yura-fixed bg-yura_dark">' +
            'Codigo de autorizacion' +
            '</div>' +
            '<input type="password" id="codigo_autorizacion" style="width: 100%" class="text-center form-control">' +
            '</div>';

        modal_quest('modal_update_inventario', texto, 'Modificar el inventario', true, false, '40%',
            function() {
                datos = {
                    _token: '{{ csrf_token() }}',
                    id: id,
                    ramos_ingresados: $('#ramos_ingresados_' + id).val(),
                    tallos_disponibles: $('#tallos_disponibles_' + id).val(),
                    codigo: $('#codigo_autorizacion').val(),
                }
                post_jquery_m('{{ url('ingreso_inventario/update_inventario') }}', datos, function() {});
            });
    }

    function delete_inventario(id) {
        var texto = ingreso_confirmacion('¿Eliminar este registro de inventario?',
            'Esta acción no se puede deshacer.', 'fa-trash', true);

        modal_quest('modal_delete_inventario', texto, 'Eliminar inventario', true, false, '40%', function() {
            datos = {
                _token: '{{ csrf_token() }}',
                id: id
            }
            post_jquery_m('{{ url('ingreso_inventario/delete_inventario') }}', datos, function() {
                cerrar_modals();
                listar_reporte();
            });
        })
    }

    function recibir_pendientes(id) {
        var texto = ingreso_confirmacion('¿Recibir los ramos pendientes?',
            'Se registrará la recepción y se actualizarán las existencias de esta variedad.',
            'fa-download', false);

        modal_quest('modal_recibir_pendientes', texto, 'Grabar recetas', true, false, '40%', function() {
            datos = {
                _token: '{{ csrf_token() }}',
                id: id,
                ramos: $('#ramos_pendiente_' + id).val()
            }
            post_jquery_m('{{ url('ingreso_inventario/recibir_pendientes') }}', datos, function() {
                cerrar_modals();
                listar_reporte();
            });
        })
    }

    function recibir_all_pendientes() {
        var texto = ingreso_confirmacion('¿Recibir los documentos seleccionados?',
            'Se registrarán los ramos marcados y se actualizarán las bodegas correspondientes.',
            'fa-check-circle', false);

        modal_quest('modal_recibir_pendientes', texto, 'Recibir ramos pendientes', true, false, '40%', function() {
            data = [];
            ramos_pendiente = $('.ramos_pendiente');
            for (i = 0; i < ramos_pendiente.length; i++) {
                id = ramos_pendiente[i].id;
                id_detApi = $('#' + id).data('id_detalle_api_store_cajas');
                if ($('#check_detApi_' + id_detApi).prop('checked') == true) {
                    data.push({
                        id_detApi: id_detApi,
                        ramos_ventas: $('#detApi_ramos_pendiente_ventas_' + id_detApi).val(),
                        ramos_produccion: $('#detApi_ramos_pendiente_produccion_' + id_detApi).val(),
                    });
                }
            }
            if (data.length > 0) {
                datos = {
                    _token: '{{ csrf_token() }}',
                    id: id,
                    fecha: $('#fecha_filtro').val(),
                    data: JSON.stringify(data)
                }
                post_jquery_m('{{ url('ingreso_inventario/recibir_all_pendientes') }}', datos, function() {
                    cerrar_modals();
                    listar_reporte();
                });
            }
        })
    }

    function botar_inventario(id) {
        var texto = ingreso_confirmacion('¿Dar de baja esta flor?',
            'Se descontarán los tallos indicados del inventario disponible.',
            'fa-exclamation-triangle', true);

        modal_quest('modal_botar_inventario', texto, 'Eliminar inventario', true, false, '40%', function() {
            datos = {
                _token: '{{ csrf_token() }}',
                id: id,
                botar: $('#tallos_botar_' + id).val(),
                fecha: $('#fecha_filtro').val(),
            }
            post_jquery_m('{{ url('ingreso_inventario/botar_inventario') }}', datos, function() {
                cerrar_modals();
                listar_reporte();
            });
        })
    }

    function mover_inventario(id) {
        //alert('Deshabilitado momentanemente');
        datos = {
            id: id,
        }
        get_jquery('{{ url('ingreso_inventario/mover_inventario') }}', datos, function(retorno) {
            modal_view('modal_mover_inventario', retorno,
                '<i class="fa fa-exchange"></i> Mover inventario',
                true, false, '{{ isPC() ? '75%' : '100%' }}',
                function() {});
        })
    }
</script>
