<table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d" id="table_add_flor_nacional">
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
        <th class="padding_lateral_5 bg-yura_dark">
            Motivo
        </th>
        <th class="padding_lateral_5 bg-yura_dark">
            Finca Origen
        </th>
        <th class="padding_lateral_5 bg-yura_dark" style="width: 90px">
            Tallos Produccion
        </th>
        <th class="padding_lateral_5 bg-yura_dark" style="width: 90px">
            Tallos Nacional
        </th>
        <th class="padding_lateral_5 bg-yura_dark" style="width: 90px">
            Nacional %
        </th>
        <th class="text-center bg-yura_dark" style="width: 90px">
            <button type="button" class="btn btn-xs btn-yura_default" onclick="add_flor_nacional()">
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
            <select id="new_motivo_1" style="width: 100%; height: 26px;">
                <option value="">Seleccione</option>
                @foreach ($motivos as $m)
                    <option value="{{ $m->id_motivo_flor_nacional }}">{{ $m->nombre }}</option>
                @endforeach
            </select>
        </th>
        <th class="text-center" style="border-color: #9d9d9d">
            <select id="new_finca_origen_1" style="width: 100%; height: 26px;">
                <option value="">Seleccione</option>
                @foreach ($fincas as $f)
                    <option value="{{ $f->id_finca_flor_nacional }}">{{ $f->nombre }}</option>
                @endforeach
            </select>
        </th>
        <th class="text-center" style="border-color: #9d9d9d">
            <input type="number" style="width: 100%;" class="padding_lateral_5" id="new_produccion_1"
                onchange="calcular_nacional(1)" value="0">
        </th>
        <th class="text-center" style="border-color: #9d9d9d">
            <input type="number" style="width: 100%;" class="padding_lateral_5" value="0" id="new_nacional_1"
                onchange="calcular_nacional(1)">
        </th>
        <th class="text-center" style="border-color: #9d9d9d">
            <input type="text" style="width: 100%;  background-color: #dddddd" class="padding_lateral_5"
                id="new_porcentaje_1" placeholder="%" value="0" readonly>
        </th>
        <th class="text-center" style="border-color: #9d9d9d">
        </th>
    </tr>
</table>

<div class="text-center" style="margin-top: 5px">
    <button type="button" class="btn btn-yura_primary" onclick="store_flor_nacional()">
        <i class="fa fa-fw fa-save"></i> GRABAR FLOR NACIONAL
    </button>
</div>

<script>
    num_row = 1;

    function calcular_nacional(row) {
        produccion = parseInt($('#new_produccion_' + row).val());
        nacional = parseInt($('#new_nacional_' + row).val());
        if (produccion > 0 && nacional >= 0) {
            porcentaje = Math.round((nacional / produccion) * 100);
            $('#new_porcentaje_' + row).val(porcentaje + '%');
        }
    }

    function add_flor_nacional() {
        num_row++;
        parametros = [
            "'new_variedad_" + num_row + "'",
            "'<option value = selected>Seleccione</option>'",
        ];
        select_planta = $('#new_planta_1').html();
        select_motivo = $('#new_motivo_1').html();
        select_finca_origen = $('#new_finca_origen_1').html();
        fecha = $('#new_fecha_1').val();
        max_fecha = $('#new_fecha_1').prop('max');
        $('#table_add_flor_nacional').append('<tr id="new_tr_' + num_row + '">' +
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
            '<select id="new_motivo_' + num_row + '" style="width: 100%; height: 26px;">' +
            select_motivo +
            '</select>' +
            '</th>' +
            '<th class="text-center" style="border-color: #9d9d9d">' +
            '<select id="new_finca_origen_' + num_row + '" style="width: 100%; height: 26px;">' +
            select_finca_origen +
            '</select>' +
            '</th>' +
            '<th class="text-center" style="border-color: #9d9d9d">' +
            '<input type="number" style="width: 100%;" class="padding_lateral_5" id="new_produccion_' + num_row +
            '" ' +
            'onchange="calcular_nacional(' + num_row + ')" value="0">' +
            '</th>' +
            '<th class="text-center" style="border-color: #9d9d9d">' +
            '<input type="number" style="width: 100%;" class="padding_lateral_5" ' +
            'value="0" id="new_nacional_' + num_row + '" onchange="calcular_nacional(' + num_row + ')">' +
            '</th>' +
            '<th class="text-center" style="border-color: #9d9d9d">' +
            '<input type="text" style="width: 100%; background-color: #dddddd" class="padding_lateral_5" id="new_porcentaje_' +
            num_row +
            '" placeholder="%" readonly value="0">' +
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

    function store_flor_nacional() {
        data = [];
        for (i = 1; i <= num_row; i++) {
            if ($('#new_tr_' + i).length) {
                fecha = $('#new_fecha_' + i).val();
                variedad = $('#new_variedad_' + i).val();
                motivo = $('#new_motivo_' + i).val();
                finca_origen = $('#new_finca_origen_' + i).val();
                produccion = $('#new_produccion_' + i).val();
                porcentaje = parseInt($('#new_porcentaje_' + i).val());
                nacional = $('#new_nacional_' + i).val();
                if (variedad != '' && motivo != '' && finca_origen != '' && produccion > 0 && porcentaje >= 0 &&
                    nacional >= 0) {
                    data.push({
                        fecha: fecha,
                        variedad: variedad,
                        motivo: motivo,
                        finca_origen: finca_origen,
                        produccion: produccion,
                        porcentaje: porcentaje,
                        nacional: nacional,
                    });
                } else {
                    alert(variedad + ' - ' + motivo + ' - ' + finca_origen + ' - ' + produccion +
                        ' - ' + porcentaje + ' - ' + nacional)
                }
            }
        }
        if (data.length > 0) {
            datos = {
                _token: '{{ csrf_token() }}',
                data: JSON.stringify(data),
            }
            post_jquery_m('{{ url('ingreso_flor_nacional/store_flor_nacional') }}', datos, function() {
                cerrar_modals();
                listar_reporte();
            })
        }
    }
</script>
