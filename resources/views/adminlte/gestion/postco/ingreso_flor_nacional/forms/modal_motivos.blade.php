<table class="table-bordered" style="border: 1px solid #9d9d9d; width: 100%">
    <thead id="thead_table">
        <tr>
            <th class="padding_lateral_5 bg-yura_dark">
                Nombre
            </th>
            <th class="text-center bg-yura_dark" style="width: 90px">
                <button type="button" class="btn btn-xs btn-yura_default" onclick="add_motivo()">
                    <i class="fa fa-fw fa-plus"></i> Nuevo
                </button>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr id="tr_grabar_nuevos_motivos" class="hidden">
            <th class="text-center" colspan="2">
                <button type="button" class="btn btn-xs btn-yura_primary" onclick="store_motivos()">
                    <i class="fa fa-fw fa-save"></i> GRABAR MOTIVOS
                </button>
            </th>
        </tr>
        @foreach ($motivos as $m)
            <tr>
                <th class="text-center" style="border-color: #9d9d9d">
                    <input type="text" value="{{ $m->nombre }}" style="width: 100%"
                        id="nombre_motivo_{{ $m->id_motivo_flor_nacional }}"
                        class="padding_lateral_5 {{ !$m->estado ? 'error' : '' }}">
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    <div class="btn-group">
                        <button type="button" class="btn btn-xs btn-yura_warning"
                            onclick="update_motivo('{{ $m->id_motivo_flor_nacional }}')">
                            <i class="fa fa-fw fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-xs btn-yura_danger"
                            onclick="cambiar_estado_motivo('{{ $m->id_motivo_flor_nacional }}')">
                            <i class="fa fa-fw fa-lock"></i>
                        </button>
                    </div>
                </th>
            </tr>
        @endforeach
    </tbody>
</table>

<script>
    num_motivos = 0;

    function add_motivo() {
        num_motivos++;
        $('#thead_table').append('<tr>' +
            '<th class="text-center" style="border-color: #9d9d9d">' +
            '<input type="text" style="width: 100%; background-color: #dddddd" id="new_motivo_' + num_motivos +
            '" class="padding_lateral_5" placeholder="Nombre del nuevo motivo">' +
            '</th>' +
            '</tr>');
        $('#tr_grabar_nuevos_motivos').removeClass('hidden')
    }

    function store_motivos() {
        data = [];
        for (i = 1; i <= num_motivos; i++) {
            nombre = $('#new_motivo_' + i).val();
            if (nombre != '') {
                data.push(nombre);
            }
        }
        if (data.length > 0) {
            datos = {
                _token: '{{ csrf_token() }}',
                data: JSON.stringify(data),
            }
            post_jquery_m('{{ url('ingreso_flor_nacional/store_motivos') }}', datos, function() {
                cerrar_modals();
                modal_motivos();
            })
        }
    }

    function update_motivo(id) {
        nombre = $('#nombre_motivo_' + id).val();
        if (nombre != '') {
            datos = {
                _token: '{{ csrf_token() }}',
                id: id,
                nombre: nombre,
            }
            post_jquery_m('{{ url('ingreso_flor_nacional/update_motivo') }}', datos, function() {});
        }
    }

    function cambiar_estado_motivo(id) {
        datos = {
            _token: '{{ csrf_token() }}',
            id: id,
        }
        post_jquery_m('{{ url('ingreso_flor_nacional/cambiar_estado_motivo') }}', datos, function() {
            cerrar_modals();
            modal_motivos();
        });
    }
</script>
