@php
    $postco = $ot_postco->postco;
    $cliente = $ot_postco->cliente;
@endphp
<legend class="text-center" style="font-size: 1.3em; margin-bottom: 5px">
    Reclamos de la receta <b>{{ $postco->variedad->nombre }}</b>: #{{ $ot_postco->id_ot_postco }}
</legend>
<table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
    <tr>
        <th class="padding_lateral_5 th_yura_green" style="width: 120px">
            Fecha
        </th>
        <th class="padding_lateral_5 th_yura_green" style="width: 60px">
            Ramos
        </th>
        <th class="text-center th_yura_green" style="width: 200px">
            Motivo
        </th>
        <th class="text-center th_yura_green">
            Link
        </th>
        <th class="text-center th_yura_green" style="width: 90px">
            <button type="button" class="btn btn-xs btn-yura_default" onclick="add_reclamo()">
                <i class="fa fa-fw fa-plus"></i> Nuevo
            </button>
        </th>
    </tr>
    <tr id="tr_new_reclamo" class="hidden">
        <th class="text-center" style="border-color: #9d9d9d">
            <input type="date" id="new_reclamo_fecha" style="width: 100%; background-color: #dddddd"
                value="{{ hoy() }}" class="text-center">
        </th>
        <th class="text-center" style="border-color: #9d9d9d">
            <input type="number" id="new_reclamo_cantidad" style="width: 100%; background-color: #dddddd"
                value="0" class="text-center">
        </th>
        <th class="text-center" style="border-color: #9d9d9d">
            <select id="new_reclamo_id_motivo" style="width: 100%; background-color: #dddddd; height: 26px;">
                @foreach ($motivos_reclamo as $mot)
                    <option value="{{ $mot->id_motivo_reclamo }}">
                        {{ $mot->nombre }}
                    </option>
                @endforeach
            </select>
        </th>
        <th class="text-center" style="border-color: #9d9d9d">
            <input type="text" id="new_reclamo_link" style="width: 100%; background-color: #dddddd"
                placeholder="https://">
        </th>
        <th class="text-center" style="border-color: #9d9d9d">
            <button type="button" class="btn btn-xs btn-yura_primary"
                onclick="store_reclamo('{{ $ot_postco->id_ot_postco }}')">
                <i class="fa fa-fw fa-save"></i>
            </button>
        </th>
    </tr>
    @foreach ($ot_postco->reclamos as $item)
        <tr onmouseover="$(this).css('background-color', 'cyan')" onmouseleave="$(this).css('background-color', '')">
            <th class="text-center" style="border-color: #9d9d9d">
                <input type="date" id="edit_reclamo_fecha_{{ $item->id_ot_reclamo }}" style="width: 100%;"
                    value="{{ $item->fecha }}" class="text-center">
            </th>
            <th class="text-center" style="border-color: #9d9d9d">
                <input type="number" id="edit_reclamo_cantidad_{{ $item->id_ot_reclamo }}" style="width: 100%;"
                    value="{{ $item->cantidad }}" class="text-center">
            </th>
            <th class="text-center" style="border-color: #9d9d9d">
                <select id="edit_reclamo_id_motivo_{{ $item->id_ot_reclamo }}" style="width: 100%;; height: 26px;">
                    @foreach ($motivos_reclamo as $mot)
                        <option value="{{ $mot->id_motivo_reclamo }}"
                            {{ $mot->id_motivo_reclamo == $item->id_motivo_reclamo ? 'selected' : '' }}>
                            {{ $mot->nombre }}
                        </option>
                    @endforeach
                </select>
            </th>
            <th class="text-center" style="border-color: #9d9d9d">
                <input type="text" id="edit_reclamo_link_{{ $item->id_ot_reclamo }}" style="width: 100%;"
                    value="{{ $item->link }}" class="text-center" placeholder="https://">
            </th>
            <th class="text-center" style="border-color: #9d9d9d;">
                <div class="btn-group">
                    <button type="button" class="btn btn-xs btn-yura_default" title="Ver"
                        onclick="window.open('{{ $item->link }}', '_blank')">
                        <i class="fa fa-fw fa-eye"></i>
                    </button>
                    <button type="button" class="btn btn-xs btn-yura_warning" title="Actualizar"
                        onclick="update_reclamo('{{ $item->id_ot_reclamo }}')">
                        <i class="fa fa-fw fa-save"></i>
                    </button>
                    <button type="button" class="btn btn-xs btn-yura_danger" title="Eliminar"
                        onclick="eliminar_reclamo('{{ $item->id_ot_reclamo }}')">
                        <i class="fa fa-fw fa-trash"></i>
                    </button>
                </div>
            </th>
        </tr>
    @endforeach
</table>

<script>
    function add_reclamo() {
        $('#tr_new_reclamo').removeClass('hidden');
    }

    function store_reclamo(id) {
        datos = {
            _token: '{{ csrf_token() }}',
            id: id,
            fecha: $('#new_reclamo_fecha').val(),
            cantidad: $('#new_reclamo_cantidad').val(),
            link: $('#new_reclamo_link').val(),
            id_motivo: $('#new_reclamo_id_motivo').val(),
        }
        post_jquery_m('{{ url('ot_postco/store_reclamo') }}', datos, function() {
            cerrar_modals();
            modal_reclamos(id);
        });
    }

    function update_reclamo(id) {
        datos = {
            _token: '{{ csrf_token() }}',
            id: id,
            fecha: $('#edit_reclamo_fecha_' + id).val(),
            cantidad: $('#edit_reclamo_cantidad_' + id).val(),
            link: $('#edit_reclamo_link_' + id).val(),
            id_motivo: $('#edit_reclamo_id_motivo_' + id).val(),
        }
        post_jquery_m('{{ url('ot_postco/update_reclamo') }}', datos, function() {});
    }

    function eliminar_reclamo(id) {
        mensaje = {
            title: '<i class="fa fa-fw fa-lock"></i> Activar/Desactivar reclamo',
            mensaje: '<div class="alert alert-warning text-center"><i class="fa fa-fw fa-exclamation-triangle"></i> ¿Está seguro de <b>ELIMINAR</b> este reclamo?</div>',
        };
        modal_quest('modal-quest_eliminar_reclamo', mensaje['mensaje'], mensaje['title'], true, false, '50%',
            function() {
                datos = {
                    _token: '{{ csrf_token() }}',
                    id: id,
                }
                post_jquery_m('{{ url('ot_postco/eliminar_reclamo') }}', datos, function() {
                    cerrar_modals();
                    modal_reclamos(id);
                });
            });
    }
</script>
