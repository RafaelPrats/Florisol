<div style="overflow-y: scroll; max-height: 700px; overflow-x: scroll;">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
        <thead>
            <tr class="tr_fija_top_0">
                <th class="text-center th_yura_green">
                    Nombre
                </th>
                <th class="text-center th_yura_green" style="width: 30px">
                    <button type="button" class="btn btn-xs btn-yura_default" onclick="add_motivo()">
                        <i class="fa fa-fw fa-plus"></i> Nuevo
                    </button>
                </th>
            </tr>
        </thead>
        <tr id="tr_new_motivo" class="hidden">
            <th class="text-center" style="border-color: #9d9d9d">
                <input type="text" id="new_motivo_nombre" style="width: 100%; background-color: #dddddd"
                    class="text-center">
            </th>
            <th class="text-center" style="border-color: #9d9d9d">
                <button type="button" class="btn btn-xs btn-yura_primary" onclick="store_motivo()">
                    <i class="fa fa-fw fa-save"></i>
                </button>
            </th>
        </tr>
        <tbody>
            @foreach ($listado as $item)
                <tr onmouseover="$(this).css('background-color', 'cyan')"
                    onmouseleave="$(this).css('background-color', '')">
                    <th class="text-center" style="border-color: #9d9d9d;">
                        <input type="text" id="motivo_nombre_{{ $item->id_motivo_reclamo }}"
                            style="width: 100%; font-size: 0.9em"
                            class="text-center {{ $item->estado == 0 ? 'error' : '' }}" value="{{ $item->nombre }}"
                            onclick="select_motivo('{{ $item->id_motivo_reclamo }}')">
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d;">
                        <div class="btn-group">
                            <button type="button" class="btn btn-xs btn-yura_warning" title="Actualizar"
                                onclick="update_motivo('{{ $item->id_motivo_reclamo }}')">
                                <i class="fa fa-fw fa-save"></i>
                            </button>
                            <button type="button" class="btn btn-xs btn-yura_danger" title="Activar/Desactivar"
                                onclick="cambiar_estado_motivo('{{ $item->id_motivo_reclamo }}')">
                                <i class="fa fa-fw fa-lock"></i>
                            </button>
                        </div>
                    </th>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<script type="text/javascript">
    function add_motivo() {
        $('#tr_new_motivo').removeClass('hidden');
    }

    function store_motivo() {
        datos = {
            _token: '{{ csrf_token() }}',
            nombre: $('#new_motivo_nombre').val(),
        }
        post_jquery_m('{{ url('motivos_reclamos/store_motivo') }}', datos, function() {
            listar_reporte();
        });
    }

    function update_motivo(id) {
        datos = {
            _token: '{{ csrf_token() }}',
            id: id,
            nombre: $('#motivo_nombre_' + id).val(),
        }
        post_jquery_m('{{ url('motivos_reclamos/update_motivo') }}', datos, function() {});
    }

    function cambiar_estado_motivo(id) {
        mensaje = {
            title: '<i class="fa fa-fw fa-lock"></i> Activar/Desactivar motivo',
            mensaje: '<div class="alert alert-warning text-center"><i class="fa fa-fw fa-exclamation-triangle"></i> ¿Está seguro de <b>Activar/Desactivar</b> este motivo?</div>',
        };
        modal_quest('modal-quest_cambiar_estado_motivo', mensaje['mensaje'], mensaje['title'], true, false, '50%',
            function() {
                datos = {
                    _token: '{{ csrf_token() }}',
                    id: id,
                }
                post_jquery_m('{{ url('motivos_reclamos/cambiar_estado_motivo') }}', datos, function() {
                    cerrar_modals();
                    listar_reporte();
                });
            });
    }
</script>
