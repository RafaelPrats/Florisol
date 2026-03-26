<table class="table-bordered" style="border: 1px solid #9d9d9d; width: 100%">
    <thead id="thead_table">
        <tr>
            <th class="padding_lateral_5 bg-yura_dark">
                Nombre
            </th>
            <th class="text-center bg-yura_dark" style="width: 90px">
                <button type="button" class="btn btn-xs btn-yura_default" onclick="add_finca()">
                    <i class="fa fa-fw fa-plus"></i> Nuevo
                </button>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr id="tr_grabar_nuevos_fincas" class="hidden">
            <th class="text-center" colspan="2">
                <button type="button" class="btn btn-xs btn-yura_primary" onclick="store_fincas()">
                    <i class="fa fa-fw fa-save"></i> GRABAR FINCAS
                </button>
            </th>
        </tr>
        @foreach ($fincas as $m)
            <tr>
                <th class="text-center" style="border-color: #9d9d9d">
                    <input type="text" value="{{ $m->nombre }}" style="width: 100%"
                        id="nombre_finca_{{ $m->id_finca_flor_nacional }}"
                        class="padding_lateral_5 {{ !$m->estado ? 'error' : '' }}">
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    <div class="btn-group">
                        <button type="button" class="btn btn-xs btn-yura_warning"
                            onclick="update_finca('{{ $m->id_finca_flor_nacional }}')">
                            <i class="fa fa-fw fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-xs btn-yura_danger"
                            onclick="cambiar_estado_finca('{{ $m->id_finca_flor_nacional }}')">
                            <i class="fa fa-fw fa-lock"></i>
                        </button>
                    </div>
                </th>
            </tr>
        @endforeach
    </tbody>
</table>

<script>
    num_fincas = 0;

    function add_finca() {
        num_fincas++;
        $('#thead_table').append('<tr>' +
            '<th class="text-center" style="border-color: #9d9d9d">' +
            '<input type="text" style="width: 100%; background-color: #dddddd" id="new_finca_' + num_fincas +
            '" class="padding_lateral_5" placeholder="Nombre de la nueva finca de origen">' +
            '</th>' +
            '</tr>');
        $('#tr_grabar_nuevos_fincas').removeClass('hidden')
    }

    function store_fincas() {
        data = [];
        for (i = 1; i <= num_fincas; i++) {
            nombre = $('#new_finca_' + i).val();
            if (nombre != '') {
                data.push(nombre);
            }
        }
        if (data.length > 0) {
            datos = {
                _token: '{{ csrf_token() }}',
                data: JSON.stringify(data),
            }
            post_jquery_m('{{ url('ingreso_flor_nacional/store_fincas') }}', datos, function() {
                cerrar_modals();
                modal_fincas();
            })
        }
    }

    function update_finca(id) {
        nombre = $('#nombre_finca_' + id).val();
        if (nombre != '') {
            datos = {
                _token: '{{ csrf_token() }}',
                id: id,
                nombre: nombre,
            }
            post_jquery_m('{{ url('ingreso_flor_nacional/update_finca') }}', datos, function() {});
        }
    }

    function cambiar_estado_finca(id) {
        datos = {
            _token: '{{ csrf_token() }}',
            id: id,
        }
        post_jquery_m('{{ url('ingreso_flor_nacional/cambiar_estado_finca') }}', datos, function() {
            cerrar_modals();
            modal_fincas();
        });
    }
</script>
