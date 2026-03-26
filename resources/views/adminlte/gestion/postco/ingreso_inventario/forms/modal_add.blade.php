<table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d" id="table_add_inventario">
    <tr>
        <th class="padding_lateral_5 bg-yura_dark">
            Fecha
        </th>
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
        <th class="text-center bg-yura_dark" style="width: 90px">
            <button type="button" class="btn btn-xs btn-yura_default" onclick="add_inventario()">
                <i class="fa fa-fw fa-plus"></i> Agregar
            </button>
        </th>
    </tr>
    <tr id="new_tr_1">
        <th class="text-center" style="border-color: #9d9d9d">
            <input type="date" style="width: 100%;" class="padding_lateral_5" id="new_fecha_1"
                value="{{ hoy() }}" max="{{ hoy() }}">
        </th>
        <th class="text-center" style="border-color: #9d9d9d">
            <select id="new_planta_1" style="width: 100%; height: 26px;"
                onchange="select_planta_global($(this).val(), 'new_variedad_1', 'new_variedad_1', '<option value=>Seleccione</option>')">
                <option value="">Seleccione</option>
                @foreach ($plantas as $p)
                    <option value="{{ $p->id_planta }}">{{ $p->nombre }}</option>
                @endforeach
            </select>
        </th>
        <th class="text-center" style="border-color: #9d9d9d">
            <select id="new_variedad_1" style="width: 100%; height: 26px;">
                <option value="">Seleccione</option>
            </select>
        </th>
        <th class="text-center" style="border-color: #9d9d9d">
            <input type="text" style="width: 100%;" class="padding_lateral_5" id="new_longitud_1">
        </th>
        <th class="text-center" style="border-color: #9d9d9d">
            <input type="number" style="width: 100%;" class="padding_lateral_5" id="new_tallos_x_ramo_1">
        </th>
        <th class="text-center" style="border-color: #9d9d9d">
            <input type="number" style="width: 100%;" class="padding_lateral_5" id="new_ramos_1">
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
    num_row = 1;

    function add_inventario() {
        num_row++;
        parametros = [
            "'new_variedad_" + num_row + "'",
            "'<option value = selected>Seleccione</option>'",
        ];
        select_planta = $('#new_planta_1').html();
        fecha = $('#new_fecha_1').val();
        max_fecha = $('#new_fecha_1').prop('max');
        $('#table_add_inventario').append('<tr id="new_tr_' + num_row + '">' +
            '<th class="text-center" style="border-color: #9d9d9d">' +
            '<input type="date" style="width: 100%;" class="padding_lateral_5" id="new_fecha_' + num_row + '" ' +
            'value="' + fecha + '" max="' + max_fecha + '">' +
            '</th>' +
            '<th class="text-center" style="border-color: #9d9d9d">' +
            '<select id="new_planta_' + num_row + '" style="width: 100%; height: 26px;" ' +
            'onchange="select_planta_global($(this).val(), ' + parametros[0] + ', ' +
            parametros[0] + ', ' + parametros[1] + ')">' +
            select_planta +
            '</select>' +
            '</th>' +
            '<th class="text-center" style="border-color: #9d9d9d">' +
            '<select id="new_variedad_' + num_row + '" style="width: 100%; height: 26px;">' +
            '<option value="">Seleccione</option>' +
            '</select>' +
            '</th>' +
            '<th class="text-center" style="border-color: #9d9d9d">' +
            '<input type="text" style="width: 100%;" class="padding_lateral_5" id="new_longitud_' + num_row +
            '">' +
            '</th>' +
            '<th class="text-center" style="border-color: #9d9d9d">' +
            '<input type="number" style="width: 100%;" class="padding_lateral_5" id="new_tallos_x_ramo_' + num_row +
            '">' +
            '</th>' +
            '<th class="text-center" style="border-color: #9d9d9d">' +
            '<input type="number" style="width: 100%;" class="padding_lateral_5" ' +
            'id="new_ramos_' + num_row + '">' +
            '</th>' +
            '<th class="text-center" style="border-color: #9d9d9d">' +
            '<button type="button" class="btn btn-xs btn-yura_danger" onclick="quitar_row(' + num_row + ')">' +
            '<i class="fa fa-fw fa-times"></i>' +
            '</button>' +
            '</th>' +
            '</tr>');
    }

    function quitar_row(row) {
        $('#new_tr_' + row).remove();
    }

    function store_inventario() {
        data = [];
        for (i = 1; i <= num_row; i++) {
            if ($('#new_tr_' + i).length) {
                fecha = $('#new_fecha_' + i).val();
                variedad = $('#new_variedad_' + i).val();
                longitud = parseInt($('#new_longitud_' + i).val());
                tallos_x_ramo = parseInt($('#new_tallos_x_ramo_' + i).val());
                ramos = $('#new_ramos_' + i).val();
                if (variedad != '' && tallos_x_ramo > 0 && ramos >= 0) {
                    data.push({
                        fecha: fecha,
                        variedad: variedad,
                        longitud: longitud,
                        tallos_x_ramo: tallos_x_ramo,
                        ramos: ramos,
                    });
                }
            }
        }
        if (data.length > 0) {
            datos = {
                _token: '{{ csrf_token() }}',
                data: JSON.stringify(data),
            }
            post_jquery_m('{{ url('ingreso_inventario/store_inventario') }}', datos, function() {
                cerrar_modals();
                listar_reporte();
            })
        }
    }
</script>
