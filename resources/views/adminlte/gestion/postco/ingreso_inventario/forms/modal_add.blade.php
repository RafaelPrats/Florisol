<div class="ingreso-manual">
    <div class="ingreso-manual-meta">
        <label class="ingreso-manual-field">
            <span><i class="fa fa-calendar"></i> Fecha de ingreso</span>
            <input type="date" name="new_fecha" id="new_fecha" class="form-control" value="{{ hoy() }}">
        </label>
        @if ($finca != 1)
            <label class="ingreso-manual-field">
                <span><i class="fa fa-file-text-o"></i> Orden de compra</span>
                <select name="new_orden_compra" id="new_orden_compra" class="form-control"
                    onchange="seleccionar_orden_compra()">
                    <option value="">Ingreso sin orden</option>
                    @foreach ($ordenes_compra as $oc)
                        <option value="{{ $oc->id_orden_compra }}">
                            #{{ $oc->id_orden_compra }} {{ $oc->proyecto->cliente->detalle()->nombre }}
                        </option>
                    @endforeach
                </select>
            </label>
        @else
            <input type="hidden" name="new_orden_compra" id="new_orden_compra" value="">
        @endif
        <label class="ingreso-manual-field">
            <span><i class="fa fa-truck"></i> Proveedor</span>
            <select name="new_proveedor" id="new_proveedor" class="form-control" onchange="seleccionar_proveedor()">
                @foreach ($proveedores as $prov)
                    <option value="{{ $prov->id_configuracion_empresa }}">{{ $prov->nombre }}</option>
                @endforeach
            </select>
        </label>
        <label class="ingreso-manual-field">
            <span><i class="fa fa-file-text-o"></i> Factura</span>
            <input type="text" name="new_factura" id="new_factura" class="form-control" placeholder="N° de factura">
        </label>
        <label class="ingreso-manual-field">
            <span><i class="fa fa-tag"></i> Packing</span>
            <input type="text" name="new_packing" id="new_packing" class="form-control"
                value="{{ $last_invoices->packing + 1 }}">
        </label>
    </div>

    <div class="ingreso-manual-lines">
        <div class="ingreso-manual-lines-heading">
            <div>
                <h4><i class="fa fa-list-ul"></i> Detalle de flores</h4>
                <small>Agrega una línea por variedad, longitud y bodega.</small>
            </div>
            <button type="button" class="btn btn-yura_default ingreso-add-line" onclick="add_inventario()">
                <i class="fa fa-plus"></i> Agregar línea
            </button>
        </div>
        <div class="ingreso-manual-table-wrap">
            <table class="ingreso-manual-table" id="table_add_inventario">
                <thead>
                    <tr>
                        <th>Planta</th>
                        <th>Variedad</th>
                        <th style="width: 95px">Longitud</th>
                        <th style="width: 85px">TxR</th>
                        <th style="width: 100px">Ramos</th>
                        <th style="width: 135px">Bodega</th>
                        <th class="text-center" style="width: 55px">Quitar</th>
                    </tr>
                </thead>
                <tbody id="tbody_add_inventario">
                    <tr id="new_tr_1">
                        <td>
                            <select id="new_planta_1" class="form-control new_planta"
                                onchange="seleccionar_planta(1)">
                                <option value="">Seleccione</option>
                            </select>
                        </td>
                        <td>
                            <select id="new_variedad_1" class="form-control new_variedad">
                                <option value="">Seleccione planta</option>
                            </select>
                        </td>
                        <td><input type="number" min="1" class="form-control" id="new_longitud_1" value="60"></td>
                        <td><input type="number" min="1" class="form-control" id="new_tallos_x_ramo_1"></td>
                        <td><input type="number" min="0" class="form-control" id="new_ramos_1"></td>
                        <td>
                            <select id="new_bodega_1" class="form-control">
                                <option value="V">Ventas</option>
                                <option value="P">Producción</option>
                            </select>
                        </td>
                        <td class="text-center"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="ingreso-manual-footer">
        <span><i class="fa fa-info-circle"></i> Revisa los ramos y la bodega antes de guardar el ingreso.</span>
        <button type="button" class="btn btn-yura_primary" onclick="store_inventario()">
            <i class="fa fa-save"></i> Grabar ingreso
        </button>
    </div>
</div>

<input type="hidden" id="finca_selected" value="{{ $finca }}">

<style>
    .ingreso-manual {
        --manual-green: #00B388;
        --manual-ink: #003729;
        --manual-muted: #5A7177;
        --manual-line: #dce9e4;
        color: var(--manual-ink);
        font-size: 13px;
    }

    .ingreso-manual-meta {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 10px;
        padding: 12px;
        border: 1px solid var(--manual-line);
        border-radius: 8px;
        background: #f4faf7;
    }

    .ingreso-manual-field {
        display: block;
        min-width: 0;
        margin: 0;
        font-size: 12px;
        font-weight: 700;
    }

    .ingreso-manual-field span {
        display: block;
        margin-bottom: 5px;
    }

    .ingreso-manual-field span i {
        margin-right: 3px;
        color: var(--manual-green);
    }

    .ingreso-manual .form-control {
        height: 34px;
        border-color: var(--manual-line);
        border-radius: 5px;
        box-shadow: none;
        font-size: 13px;
    }

    .ingreso-manual .form-control:focus {
        border-color: var(--manual-green);
        box-shadow: 0 0 0 2px rgba(0, 179, 136, .1);
    }

    .ingreso-manual-meta .select2-container {
        width: 100% !important;
    }

    .ingreso-manual-meta .select2-selection {
        height: 34px !important;
        border-color: var(--manual-line) !important;
    }

    .ingreso-manual-meta .select2-selection__rendered {
        line-height: 32px !important;
    }

    .ingreso-manual-meta .select2-selection__arrow {
        height: 32px !important;
    }

    .ingreso-manual-lines {
        margin-top: 12px;
        overflow: hidden;
        border: 1px solid var(--manual-line);
        border-radius: 8px;
    }

    .ingreso-manual-lines-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 10px 12px;
        border-bottom: 1px solid var(--manual-line);
    }

    .ingreso-manual-lines-heading h4 {
        margin: 0 0 3px;
        font-size: 14px;
        font-weight: 700;
    }

    .ingreso-manual-lines-heading h4 i {
        color: var(--manual-green);
    }

    .ingreso-manual-lines-heading small {
        color: var(--manual-muted);
        font-size: 11px;
    }

    .ingreso-add-line {
        padding: 6px 9px;
        border-radius: 5px;
        font-size: 12px;
        font-weight: 700;
    }

    .ingreso-manual-table-wrap {
        max-height: min(48vh, 430px);
        overflow: auto;
    }

    .ingreso-manual-table {
        width: 100%;
        min-width: 800px;
        margin: 0;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 12px;
    }

    .ingreso-manual-table th,
    .ingreso-manual-table td {
        padding: 5px 7px;
        border-right: 1px solid #e4eee9;
        border-bottom: 1px solid #e4eee9;
        vertical-align: middle;
    }

    .ingreso-manual-table thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        background: var(--manual-ink);
        color: #fff;
        font-size: 11px;
        white-space: nowrap;
    }

    .ingreso-manual-table tbody tr:nth-child(even) {
        background: #f7fbf9;
    }

    .ingreso-manual-table .select2-container {
        width: 100% !important;
    }

    .ingreso-manual-table .select2-selection {
        height: 34px !important;
        border-color: var(--manual-line) !important;
    }

    .ingreso-manual-table .select2-selection__rendered {
        line-height: 32px !important;
    }

    .ingreso-manual-table .select2-selection__arrow {
        height: 32px !important;
    }

    .ingreso-manual-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-top: 11px;
        padding-top: 10px;
        border-top: 1px solid var(--manual-line);
    }

    .ingreso-manual-footer span {
        color: var(--manual-muted);
        font-size: 11px;
    }

    .ingreso-manual-footer span i {
        color: var(--manual-green);
    }

    .ingreso-manual-footer .btn {
        flex: 0 0 auto;
        padding: 8px 13px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 700;
    }

    @media (max-width: 900px) {
        .ingreso-manual-meta {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (max-width: 600px) {
        .ingreso-manual-meta {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .ingreso-manual-footer {
            align-items: stretch;
            flex-direction: column;
        }

        .ingreso-manual-footer .btn {
            width: 100%;
        }
    }

    @media (max-width: 380px) {
        .ingreso-manual-meta {
            grid-template-columns: 1fr;
        }
    }
</style>

<script>
    inicializar_selects_ingreso();
    seleccionar_proveedor();

    num_row = 1;

    function inicializar_selects_ingreso() {
        $('#tbody_add_inventario .new_planta, #tbody_add_inventario .new_variedad').each(function() {
            if (!$(this).hasClass('select2-hidden-accessible')) {
                $(this).select2({
                    width: '100%',
                    dropdownParent: $('#div_modal-modal_modal_add')
                });
            }
        });
        $('select#new_orden_compra, select#new_proveedor').each(function() {
            if (!$(this).hasClass('select2-hidden-accessible')) {
                $(this).select2({
                    width: '100%',
                    dropdownParent: $('#div_modal-modal_modal_add')
                });
            }
        });
    }

    function add_inventario() {
        num_row++;
        select_planta = $('#new_planta_1').html();
        $('#tbody_add_inventario').append('<tr id="new_tr_' + num_row + '">' +
            '<td>' +
            '<select id="new_planta_' + num_row + '" class="form-control new_planta" ' +
            'onchange="seleccionar_planta(' + num_row + ')">' +
            select_planta +
            '</select>' +
            '</td>' +
            '<td>' +
            '<select id="new_variedad_' + num_row + '" class="form-control new_variedad">' +
            '<option value="">Seleccione</option>' +
            '</select>' +
            '</td>' +
            '<td><input type="number" min="1" class="form-control" id="new_longitud_' +
            num_row +
            '">' +
            '</td>' +
            '<td><input type="number" min="1" class="form-control" id="new_tallos_x_ramo_' +
            num_row +
            '">' +
            '</td>' +
            '<td><input type="number" min="0" class="form-control" id="new_ramos_' + num_row + '">' +
            '</td>' +
            '<td><select id="new_bodega_' + num_row + '" class="form-control">' +
            '<option value="V">Ventas</option>' +
            '<option value="P">Producción</option>' +
            '</select>' +
            '</td>' +
            '<td class="text-center">' +
            '<button type="button" class="btn btn-xs btn-yura_danger" onclick="quitar_row(' + num_row + ')">' +
            '<i class="fa fa-fw fa-times"></i>' +
            '</button>' +
            '</td>' +
            '</tr>');

        inicializar_selects_ingreso();
        $('#new_planta_' + num_row).val('');
        //seleccionar_proveedor();
    }

    function quitar_row(row) {
        $('#new_tr_' + row).remove();
    }

    function store_inventario() {
        data = [];
        for (i = 1; i <= num_row; i++) {
            if ($('#new_tr_' + i).length) {
                variedad = $('#new_variedad_' + i).val();
                longitud = $('#new_longitud_' + i).val();
                tallos_x_ramo = parseInt($('#new_tallos_x_ramo_' + i).val());
                ramos = $('#new_ramos_' + i).val();
                bodega = $('#new_bodega_' + i).val();
                if (longitud != '' && variedad != '' && tallos_x_ramo > 0 && ramos >= 0) {
                    data.push({
                        variedad: variedad,
                        longitud: longitud,
                        tallos_x_ramo: tallos_x_ramo,
                        ramos: ramos,
                        bodega: bodega,
                    });
                }
            }
        }
        if (data.length > 0) {
            datos = {
                _token: '{{ csrf_token() }}',
                id_proveedor: $('#new_proveedor').val(),
                orden_compra: $('#new_orden_compra').val(),
                fecha: $('#new_fecha').val(),
                factura: $('#new_factura').val(),
                packing: $('#new_packing').val(),
                data: JSON.stringify(data),
            }
            post_jquery_m('{{ url('ingreso_inventario/store_inventario') }}', datos, function() {
                cerrar_modals();
                listar_reporte();
            })
        } else {
            alerta_errores("<div class='alert alert-warning text-center'>Agrega al menos una línea completa con variedad, longitud, tallos por ramo y cantidad de ramos.</div>");
        }
    }

    function seleccionar_proveedor() {
        finca = $('#finca_selected').val();
        orden_compra = $('#new_orden_compra').val();
        if (finca == 1 || orden_compra == '') {
            datos = {
                _token: '{{ csrf_token() }}',
                id_proveedor: $('#new_proveedor').val(),
            }
            $.post('{{ url('ingreso_inventario/seleccionar_proveedor') }}', datos, function(retorno) {
                $('.new_planta').html(retorno.plantas);
                $('.new_variedad').html('<option value="">Seleccione</option>');
                $('.new_planta, .new_variedad').trigger('change.select2');
            }, 'json').fail(function(retorno) {
                console.log(retorno);
                alerta_errores(retorno.responseText);
            });
        } else { // GET orden de compra
            datos = {
                orden_compra: $('#new_orden_compra').val(),
                id_proveedor: $('#new_proveedor').val(),
            }
            get_jquery('{{ url('ingreso_inventario/get_orden_compra') }}', datos, function(retorno) {
                $('#tbody_add_inventario').html(retorno);
                num_row = $('#tbody_add_inventario tr[id^="new_tr_"]').length;
                inicializar_selects_ingreso();
            });
        }
    }

    function seleccionar_planta(row) {
        datos = {
            _token: '{{ csrf_token() }}',
            id_proveedor: $('#new_proveedor').val(),
            id_planta: $('#new_planta_' + row).val(),
        }
        $.post('{{ url('ingreso_inventario/seleccionar_planta') }}', datos, function(retorno) {
            $('#new_variedad_' + row).html(retorno.variedades);
            $('#new_variedad_' + row).trigger('change');
        }, 'json').fail(function(retorno) {
            console.log(retorno);
            alerta_errores(retorno.responseText);
        });
    }

    function seleccionar_orden_compra() {
        datos = {
            _token: '{{ csrf_token() }}',
            orden_compra: $('#new_orden_compra').val(),
        }
        $.post('{{ url('ingreso_inventario/seleccionar_orden_compra') }}', datos, function(retorno) {
            $('#new_proveedor').html(retorno.options).trigger('change.select2');
            seleccionar_proveedor();
        }, 'json').fail(function(retorno) {
            console.log(retorno);
            alerta_errores(retorno.responseText);
        });
    }
</script>
