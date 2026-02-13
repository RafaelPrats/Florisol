<div style="overflow-y: scroll; max-height: 700px">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d" id="table_add_despacho">
        <tr class="tr_fijo_top_0">
            <th class="text-center th_yura_green" style="width: 280px">
                Proveedor
            </th>
            <th class="text-center th_yura_green">
                Variedad
            </th>
            <th class="text-center th_yura_green" style="width: 60px">
                Longitud
            </th>
            <th class="text-center th_yura_green" style="width: 60px">
                Ramos
            </th>
            <th class="text-center th_yura_green" style="width: 60px">
                TxR
            </th>
            <th class="text-center th_yura_green" style="width: 60px">
                <button type="button" class="btn btn-xs btn-yura_dark" onclick="add_row()">
                    <i class="fa fa-fw fa-plus"></i>
                </button>
            </th>
        </tr>
        <tr id="new_row_1">
            <td class="text-center" style="border-color: #9d9d9d">
                <select id="new_proveedor_1" style="width: 100%; height: 26px;" onchange="seleccionar_proveedor(1)">
                    @foreach ($proveedores as $p)
                        <option value="{{ $p->id_proveedor }}">{{ $p->nombre }}</option>
                    @endforeach
                </select>
            </td>
            <td class="text-center" style="border-color: #9d9d9d">
                <select id="new_variedad_1" style="width: 100%; height: 26px;">
                    <option value="">Seleccione un proveedor</option>
                </select>
            </td>
            <td class="text-center" style="border-color: #9d9d9d">
                <select id="new_longitud_1" style="width: 100%; height: 26px;">
                    @foreach ($longitudes as $p)
                        <option value="{{ $p->nombre }}" {{ $p->nombre == 60 ? 'selected' : '' }}>
                            {{ $p->nombre }}cm</option>
                    @endforeach
                </select>
            </td>
            <td class="text-center" style="border-color: #9d9d9d">
                <input type="number" id="new_ramos_1" style="width: 100%" class="text-center">
            </td>
            <td class="text-center" style="border-color: #9d9d9d">
                <input type="number" id="new_tallos_x_ramo_1" style="width: 100%" class="text-center">
            </td>
        </tr>
    </table>
</div>
<div class="text-center" style="margin-top: 5px">
    <button type="button" class="btn btn-yura_primary" onclick="store_despacho()">
        <i class="fa fa-fw fa-save"></i> GRABAR DESPACHO
    </button>
</div>

<script>
    var num_form = 1;
    seleccionar_proveedor(1);

    function seleccionar_proveedor(pos) {
        datos = {
            _token: '{{ csrf_token() }}',
            proveedor: $('#new_proveedor_' + pos).val(),
        }
        $('#new_variedad_' + pos).LoadingOverlay('show');
        $.post('{{ url('despacho_proveedor/seleccionar_proveedor') }}', datos, function(retorno) {
            $('#new_variedad_' + pos).html(retorno.variedades);
        }, 'json').fail(function(retorno) {
            console.log(retorno);
            alerta_errores(retorno.responseText);
        }).always(function() {
            $('#new_variedad_' + pos).LoadingOverlay('hide');
        });
    }

    function add_row() {
        num_form++;
        select_proveedor = $('#new_proveedor_1').html();
        select_longitud = $('#new_longitud_1').html();
        $('#table_add_despacho').append('<tr id="new_row_' + num_form + '">' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<select id="new_proveedor_' + num_form +
            '" style="width: 100%; height: 26px;" onchange="seleccionar_proveedor(' + num_form + ')">' +
            select_proveedor +
            '</select>' +
            '</td>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<select id="new_variedad_' + num_form + '" style="width: 100%; height: 26px;">' +
            '<option value="">Seleccione un proveedor</option>' +
            '</select>' +
            '</td>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<select id="new_longitud_' + num_form + '" style="width: 100%; height: 26px;">' +
            select_longitud +
            '</select>' +
            '</td>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<input type="number" id="new_ramos_' + num_form + '" style="width: 100%" class="text-center">' +
            '</td>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<input type="number" id="new_tallos_x_ramo_' + num_form +
            '" style="width: 100%" class="text-center">' +
            '</td>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<button type="button" class="btn btn-xs btn-yura_danger" onclick="delete_row(' + num_form + ')">' +
            '<i class="fa fa-fw fa-trash"></i>' +
            '</button>' +
            '</td>' +
            '</tr>');
        seleccionar_proveedor(num_form);
    }

    function delete_row(pos) {
        $('#new_row_' + pos).remove();
    }

    function store_despacho() {
        data = [];
        for (i = 1; i <= num_form; i++) {
            if ($('#new_row_' + i).length > 0) {
                proveedor = $('#new_proveedor_' + i).val();
                variedad = $('#new_variedad_' + i).val();
                longitud = $('#new_longitud_' + i).val();
                ramos = $('#new_ramos_' + i).val();
                tallos_x_ramo = $('#new_tallos_x_ramo_' + i).val();
                if (ramos > 0 && tallos_x_ramo > 0) {
                    data.push({
                        proveedor: proveedor,
                        variedad: variedad,
                        longitud: longitud,
                        ramos: ramos,
                        tallos_x_ramo: tallos_x_ramo,
                    });
                }
            }
        }
        datos = {
            _token: '{{ csrf_token() }}',
            fecha: $('#fecha_filtro').val(),
            data: JSON.stringify(data),
        }
        $.post('{{ url('despacho_proveedor/store_despacho') }}', datos, function(retorno) {
            if (retorno.success) {
                cerrar_modals();
                listar_reporte();
                imprimir_all(retorno.ids);
            } else {
                alerta(retorno.mensaje);
            }
        }, 'json').fail(function(retorno) {
            console.log(retorno);
            alerta_errores(retorno.responseText);
        })
    }
</script>
