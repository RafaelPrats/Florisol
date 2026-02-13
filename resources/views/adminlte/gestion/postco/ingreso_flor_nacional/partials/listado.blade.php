<div style="overflow-y: scroll; overflow-x: scroll; max-height: 700px">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d" id="table_listado">
        <thead>
            <tr class="tr_fija_top_0">
                <th class="padding_lateral_5 th_yura_green">
                    Fecha
                </th>
                <th class="padding_lateral_5 th_yura_green">
                    Planta
                </th>
                <th class="padding_lateral_5 th_yura_green">
                    Variedad
                </th>
                <th class="padding_lateral_5 th_yura_green">
                    Motivo
                </th>
                <th class="padding_lateral_5 th_yura_green">
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
                <th class="text-center bg-yura_dark" style="width: 70px">
                    <button type="button" class="btn btn-xs btn-yura_default" onclick="modal_nacional()">
                        <i class="fa fa-fw fa-plus"></i> Agregar
                    </button>
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach ($listado as $item)
                @php
                    $variedad = $item->variedad;
                @endphp
                <tr onomuseover="$(this).css('background-color', 'cyan')"
                    onomuseleave="$(this).css('background-color', '')">
                    <th class="text-center" style="border-color: #9d9d9d">
                        <input type="date" id="edit_fecha_{{ $item->id_flor_nacional }}" style="width: 100%"
                            value="{{ $item->fecha }}" class="padding_lateral_5">
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        {{ $variedad->planta->nombre }}
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        {{ $variedad->nombre }}
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        <select id="edit_motivo_{{ $item->id_flor_nacional }}" style="width: 100%; height: 26px;">
                            @foreach ($motivos as $m)
                                <option value="{{ $m->id_motivo_flor_nacional }}"
                                    {{ $m->id_motivo_flor_nacional == $item->id_motivo_flor_nacional ? 'selected' : '' }}>
                                    {{ $m->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        <select id="edit_finca_origen_{{ $item->id_flor_nacional }}" style="width: 100%; height: 26px;">
                            @foreach ($fincas as $f)
                                <option value="{{ $f->id_finca_flor_nacional }}"
                                    {{ $f->id_finca_flor_nacional == $item->id_finca_flor_nacional ? 'selected' : '' }}>
                                    {{ $f->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        <input type="number" id="edit_produccion_{{ $item->id_flor_nacional }}" style="width: 100%"
                            value="{{ $item->produccion }}" class="padding_lateral_5"
                            onchange="calcular_nacional('{{ $item->id_flor_nacional }}')">
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        <input type="number" id="edit_nacional_{{ $item->id_flor_nacional }}" style="width: 100%"
                            value="{{ $item->nacional }}" class="padding_lateral_5"
                            onchange="calcular_nacional('{{ $item->id_flor_nacional }}')">
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        <input type="text" id="edit_porcentaje_{{ $item->id_flor_nacional }}"
                            style="width: 100%; background-color: #dddddd" value="{{ $item->porcentaje }}%"
                            class="padding_lateral_5" readonly>
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        <div class="btn-group">
                            <button type="button" class="btn btn-xs btn-yura_warning"
                                onclick="update_flor_nacional('{{ $item->id_flor_nacional }}')">
                                <i class="fa fa-fw fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-xs btn-yura_danger"
                                onclick="delete_flor_nacional('{{ $item->id_flor_nacional }}')">
                                <i class="fa fa-fw fa-trash"></i>
                            </button>
                        </div>
                    </th>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<script>
    estructura_tabla('table_listado');

    function calcular_nacional(id) {
        produccion = parseInt($('#edit_produccion_' + id).val());
        nacional = parseInt($('#edit_nacional_' + id).val());
        if (produccion > 0 && nacional >= 0) {
            porcentaje = Math.round((nacional / produccion) * 100);
            $('#edit_porcentaje_' + id).val(porcentaje + '%');
        }
    }

    function modal_nacional() {
        datos = {}
        get_jquery('{{ url('ingreso_flor_nacional/modal_nacional') }}', datos, function(retorno) {
            modal_view('modal_modal_nacional', retorno,
                '<i class="fa fa-fw fa-plus"></i> Flor Nacional',
                true, false, '{{ isPC() ? '95%' : '' }}',
                function() {});
        })
    }

    function update_flor_nacional(id) {
        datos = {
            _token: '{{ csrf_token() }}',
            id: id,
            fecha: $('#edit_fecha_' + id).val(),
            motivo: $('#edit_motivo_' + id).val(),
            finca_origen: $('#edit_finca_origen_' + id).val(),
            produccion: $('#edit_produccion_' + id).val(),
            porcentaje: parseInt($('#edit_porcentaje_' + id).val()),
            nacional: $('#edit_nacional_' + id).val(),
        }
        if (datos['fecha'] != '' && datos['motivo'] != '' && datos['finca_origen'] != '' && datos['produccion'] > 0 &&
            datos['porcentaje'] >= 0 && datos['nacional'] >= 0) {
            post_jquery_m('{{ url('ingreso_flor_nacional/update_flor_nacional') }}', datos, function() {});
        }
    }

    function delete_flor_nacional(id) {
        texto =
            "<div class='alert alert-warning text-center'><h3><i class='fa fa-fw fa-exclamation-triangle error'></i>¿Esta seguro de <b>ELIMINAR</b> el registro de flor nacional?</h3></div>";

        modal_quest('modal_delete_flor_nacional', texto, 'Grabar recetas', true, false, '40%', function() {
            datos = {
                _token: '{{ csrf_token() }}',
                id: id
            }
            post_jquery_m('{{ url('ingreso_flor_nacional/delete_flor_nacional') }}', datos, function() {
                cerrar_modals();
                listar_reporte();
            });
        })
    }
</script>
