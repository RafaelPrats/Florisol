<table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d" id="table_add_recepcion">
    <tr>
        <th class="text-center th_yura_green">
            Variedad
        </th>
        <th class="text-center th_yura_green">
            Tipo
        </th>
        <th class="text-center th_yura_green">
            Módulo
        </th>
        <th class="text-center th_yura_green">
            Mallas
        </th>
        <th class="text-center th_yura_green">
            Tallos x Malla
        </th>
        <th class="text-center th_yura_green">
            Cosechador
        </th>
        <th class="text-center th_yura_green">
            <div class="btn-group">
                <button type="button" class="btn btn-xs btn-yura_dark" onclick="add_new_row()">
                    <i class="fa fa-fw fa-plus"></i>
                </button>
            </div>
        </th>
    </tr>
    <tr>
        <td style="border-color: #9d9d9d">
            <select id="new_planta_1" style="width: 100%"
                onchange="select_planta($(this).val(), 'new_variedad_1', 'new_variedad_1', '<option value= selected>Seleccione</option>')">
                @if (count($plantas) > 1)
                    <option value="">Seleccione</option>
                @endif
                @foreach ($plantas as $p)
                    <option value="{{ $p->id_planta }}">{{ $p->nombre }}</option>
                @endforeach
            </select>
            @if (count($plantas) == 1)
                <script>
                    select_planta($('#new_planta_1').val(), 'new_variedad_1', 'new_variedad_1',
                        '<option value= selected>Seleccione</option>');
                </script>
            @endif
        </td>
        <td style="border-color: #9d9d9d">
            <select id="new_variedad_1" style="width: 100%" onchange="select_variedad_recepcion(1)">
                <option value="">Seleccione</option>
            </select>
        </td>
        <td style="border-color: #9d9d9d">
            <select id="new_modulo_1" style="width: 100%">
                <option value="-1">Default</option>
            </select>
        </td>
        <td style="border-color: #9d9d9d">
            <input type="number" id="new_mallas_1" class="text-center" min="1" value="1" required
                style="width: 100%">
        </td>
        <td style="border-color: #9d9d9d">
            <input type="number" id="new_tallos_x_malla_1" class="text-center" min="0" value="0" required
                style="width: 100%">
        </td>
        <td style="border-color: #9d9d9d" colspan="2">
            <select id="new_cosechador_1" style="width: 100%">
                <option value="">Seleccione</option>
                @foreach ($cosechadores as $c)
                    <option value="{{ $c->id_cosechador }}">{{ $c->nombre }}</option>
                @endforeach
            </select>
        </td>
    </tr>
</table>

<div class="text-center" style="margin-top: 5px">
    <button type="button" class="btn btn-yura_primary" onclick="store_recepcion()">
        <i class="fa fa-fw fa-save"></i> Grabar
    </button>
</div>

<script>
    cant_forms = 1;

    function select_variedad_recepcion(pos) {
        datos = {
            _token: '{{ csrf_token() }}',
            variedad: $('#new_variedad_' + pos).val()
        };
        if (datos['variedad'] != '') {
            $.get('{{ url('recepcion/select_variedad_recepcion') }}', datos, function(retorno) {
                $('#new_modulo_' + pos).html(retorno.options_modulos);
                $('#new_tallos_x_malla_' + pos).val(retorno.variedad.tallos_x_malla);
            }, 'json').fail(function(retorno) {
                console.log(retorno);
                alerta_errores(retorno.responseText);
            });
        } else {
            $('#new_modulo_' + pos).html('');
        }
    }

    function add_new_row() {
        cant_forms++;
        select_plantas = $('#new_planta_1').html();
        select_cosechadores = $('#new_cosechador_1').html();
        parametros_select_planta = [
            "'new_variedad_" + cant_forms + "'",
            "'<option value = selected>Seleccione</option>'",
        ]
        $('#table_add_recepcion').append('<tr>' +
            '<td style="border-color: #9d9d9d">' +
            '<select id="new_planta_' + cant_forms + '" style="width: 100%"' +
            'onchange="select_planta($(this).val(), ' +
            parametros_select_planta[0] + ', ' +
            parametros_select_planta[0] + ', ' + parametros_select_planta[1] + ')">' +
            select_plantas +
            '</select>' +
            '</td>' +
            '<td style="border-color: #9d9d9d">' +
            '<select id="new_variedad_' + cant_forms +
            '" style="width: 100%" onchange="select_variedad_recepcion(' + cant_forms + ')">' +
            '<option value="">Seleccione</option>' +
            '</select>' +
            '</td>' +
            '<td style="border-color: #9d9d9d">' +
            '<select id="new_modulo_' + cant_forms + '" style="width: 100%">' +
            '<option value="-1">Default</option>' +
            '</select>' +
            '</td>' +
            '<td style="border-color: #9d9d9d">' +
            '<input type="number" id="new_mallas_' + cant_forms +
            '" class="text-center" min="1" value="1" required' +
            ' style="width: 100%">' +
            '</td>' +
            '<td style="border-color: #9d9d9d">' +
            '<input type="number" id="new_tallos_x_malla_' + cant_forms +
            '" class="text-center" min="0" value="0" required' +
            ' style="width: 100%">' +
            '</td>' +
            '<td style="border-color: #9d9d9d" colspan="2">' +
            '<select id="new_cosechador_' + cant_forms + '" required style="width: 100%">' +
            select_cosechadores +
            '</select>' +
            '</td>' +
            '</tr>');
        select_planta($('#new_planta_' + cant_forms).val(), 'new_variedad_' + cant_forms, 'new_variedad_' + cant_forms,
            '<option value= selected>Seleccione</option>');
    }

    function store_recepcion() {
        data = [];
        for (i = 1; i <= cant_forms; i++) {
            mallas = $('#new_mallas_' + i).val();
            tallos_x_malla = $('#new_tallos_x_malla_' + i).val();
            cosechador = $('#new_cosechador_' + i).val();
            if (mallas > 0 && tallos_x_malla > 0 && cosechador != '') {
                planta = $('#new_planta_' + i).val();
                variedad = $('#new_variedad_' + i).val();
                modulo = $('#new_modulo_' + i).val();
                data.push({
                    planta: planta,
                    variedad: variedad,
                    modulo: modulo,
                    mallas: mallas,
                    tallos_x_malla: tallos_x_malla,
                    cosechador: cosechador,
                });
            }
        }
        if (data.length > 0) {
            datos = {
                _token: '{{ csrf_token() }}',
                fecha: $('#filtro_fecha').val(),
                data: data,
            }
            post_jquery_m('{{ url('recepcion/store_recepcion') }}', datos, function() {
                cerrar_modals();
                buscar_listado_recepcion();
            });
        } else {
            alerta('<div class="text-center alert alert-warning">Faltan datos necesarios</div>')
        }
    }
</script>
