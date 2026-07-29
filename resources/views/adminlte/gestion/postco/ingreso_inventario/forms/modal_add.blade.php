<div class="text-center">
    <div class="input-group">
        <div class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
            Fecha
        </div>
        <input type="date" name="new_fecha" id="new_fecha" class="form-control input-yura_default"
            value="{{ hoy() }}">
        <div class="input-group-addon bg-yura_dark">
            Proveedor
        </div>
        <select name="new_proveedor" id="new_proveedor" class="form-control input-yura_default"
            onchange="seleccionar_proveedor()">
            @foreach ($proveedores as $prov)
                <option value="{{ $prov->id_configuracion_empresa }}">
                    {{ $prov->nombre }}
                </option>
            @endforeach
        </select>
        <div class="input-group-addon bg-yura_dark">
            Factura
        </div>
        <input type="text" name="new_factura" id="new_factura" class="form-control input-yura_default">
        <div class="input-group-addon bg-yura_dark">
            Packing
        </div>
        <input type="text" name="new_packing" id="new_packing" class="form-control input-yura_default"
            value="{{ $last_invoices->packing + 1 }}">
    </div>
</div>

<table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d; margin-top: 5px" id="table_add_inventario">
    <tr>
        <th class="padding_lateral_5 bg-yura_dark">
            Planta
        </th>
        <th class="padding_lateral_5 bg-yura_dark">
            Variedad
        </th>
        <th class="padding_lateral_5 bg-yura_dark" style="width: 60px">
            Longitud
        </th>
        <th class="padding_lateral_5 bg-yura_dark" style="width: 60px">
            TxR
        </th>
        <th class="padding_lateral_5 bg-yura_dark" style="width: 60px">
            Ramos
        </th>
        <th class="padding_lateral_5 bg-yura_dark">
            Bodega
        </th>
        <th class="text-center bg-yura_dark" style="width: 90px">
            <button type="button" class="btn btn-xs btn-yura_default" onclick="add_inventario()">
                <i class="fa fa-fw fa-plus"></i> Agregar
            </button>
        </th>
    </tr>
    <tr id="new_tr_1">
        <th class="text-center" style="border-color: #9d9d9d">
            <select id="new_planta_1" style="width: 100%; height: 26px;" onchange="seleccionar_planta(1)"
                class="new_planta">
                <option value="">Seleccione</option>
            </select>
        </th>
        <th class="text-center" style="border-color: #9d9d9d">
            <select id="new_variedad_1" style="width: 100%; height: 26px;" class="new_variedad">
                <option value="">Seleccione</option>
            </select>
        </th>
        <th class="text-center" style="border-color: #9d9d9d">
            <input type="text" style="width: 100%; height: 34px;" class="padding_lateral_5" id="new_longitud_1">
        </th>
        <th class="text-center" style="border-color: #9d9d9d">
            <input type="number" style="width: 100%; height: 34px;" class="padding_lateral_5" id="new_tallos_x_ramo_1">
        </th>
        <th class="text-center" style="border-color: #9d9d9d">
            <input type="number" style="width: 100%; height: 34px;" class="padding_lateral_5" id="new_ramos_1">
        </th>
        <th class="text-center" style="border-color: #9d9d9d">
            <select id="new_bodega_1" style="width: 100%; height: 34px;">
                <option value="V">Ventas</option>
                <option value="P">Producción</option>
            </select>
        </th>
        <th class="text-center" style="border-color: #9d9d9d">
        </th>
    </tr>
</table>

<div class="text-center" style="margin-top: 5px">
    <button type="button" class="btn btn-yura_primary" onclick="store_inventario()">
        <i class="fa fa-fw fa-save"></i> GRABAR INGRESO
    </button>
</div>

<script>
    setTimeout(() => {
        $("#new_planta_1, #new_variedad_1").select2({
            dropdownParent: $('#div_modal-modal_modal_add')
        });
        $('.select2-container').css('width', '100%');
        $('.select2-selection').css('height', '34px');
    }, 500);
    seleccionar_proveedor();

    num_row = 1;

    function add_inventario() {
        num_row++;
        select_planta = $('#new_planta_1').html();
        $('#table_add_inventario').append('<tr id="new_tr_' + num_row + '">' +
            '<th class="text-center" style="border-color: #9d9d9d">' +
            '<select id="new_planta_' + num_row + '" style="width: 100%; height: 26px;" ' +
            'onchange="seleccionar_planta(' + num_row + ')" class="new_planta">' +
            select_planta +
            '</select>' +
            '</th>' +
            '<th class="text-center" style="border-color: #9d9d9d">' +
            '<select id="new_variedad_' + num_row + '" style="width: 100%; height: 26px;" class="new_variedad">' +
            '<option value="">Seleccione</option>' +
            '</select>' +
            '</th>' +
            '<th class="text-center" style="border-color: #9d9d9d">' +
            '<input type="text" style="width: 100%; height: 34px;" class="padding_lateral_5" id="new_longitud_' +
            num_row +
            '">' +
            '</th>' +
            '<th class="text-center" style="border-color: #9d9d9d">' +
            '<input type="number" style="width: 100%; height: 34px;" class="padding_lateral_5" id="new_tallos_x_ramo_' +
            num_row +
            '">' +
            '</th>' +
            '<th class="text-center" style="border-color: #9d9d9d">' +
            '<input type="number" style="width: 100%; height: 34px;" class="padding_lateral_5" ' +
            'id="new_ramos_' + num_row + '">' +
            '</th>' +
            '<th class="text-center" style="border-color: #9d9d9d">' +
            '<select id="new_bodega_' + num_row + '" style="width: 100%; height: 34px;">' +
            '<option value="V">Ventas</option>' +
            '<option value="P">Producción</option>' +
            '</select>' +
            '</th>' +
            '<th class="text-center" style="border-color: #9d9d9d">' +
            '<button type="button" class="btn btn-xs btn-yura_danger" onclick="quitar_row(' + num_row + ')">' +
            '<i class="fa fa-fw fa-times"></i>' +
            '</button>' +
            '</th>' +
            '</tr>');

        $('#new_planta_' + num_row + ', #new_variedad_' + num_row).select2({
            dropdownParent: $('#div_modal-modal_modal_add')
        });
        $('.select2-container').css('width', '100%');
        $('.select2-selection').css('height', '34px');
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
                        fecha: fecha,
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
                fecha: $('#new_fecha').val(),
                factura: $('#new_factura').val(),
                packing: $('#new_packing').val(),
                data: JSON.stringify(data),
            }
            post_jquery_m('{{ url('ingreso_inventario/store_inventario') }}', datos, function() {
                cerrar_modals();
                listar_reporte();
            })
        }
    }

    function seleccionar_proveedor() {
        datos = {
            _token: '{{ csrf_token() }}',
            id_proveedor: $('#new_proveedor').val(),
        }
        $.post('{{ url('ingreso_inventario/seleccionar_proveedor') }}', datos, function(retorno) {
            $('.new_planta').html(retorno.plantas);
            $('.new_variedad').html('<option value="">Seleccione</option>');
        }, 'json').fail(function(retorno) {
            console.log(retorno);
            alerta_errores(retorno.responseText);
        });
    }

    function seleccionar_planta(row) {
        datos = {
            _token: '{{ csrf_token() }}',
            id_proveedor: $('#new_proveedor_' + row).val(),
            id_planta: $('#new_planta_' + row).val(),
        }
        $.post('{{ url('ingreso_inventario/seleccionar_planta') }}', datos, function(retorno) {
            $('#new_variedad_' + row).html(retorno.variedades);
        }, 'json').fail(function(retorno) {
            console.log(retorno);
            alerta_errores(retorno.responseText);
        });
    }
</script>
