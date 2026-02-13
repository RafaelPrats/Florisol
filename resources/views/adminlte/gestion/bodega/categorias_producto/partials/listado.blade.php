<div style="overflow-y: scroll; overflow-x: scroll; height: 700px;">
    <table class="table-striped table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
        <tr id="tr_fija_top_0">
            <th class="text-center th_yura_green">
                NOMBRE
            </th>
            <th class="text-center th_yura_green" style="width: 60px">
                <button type="button" class="btn btn-xs btn-yura_default"
                    onclick="$('#tr_new_categoria').removeClass('hidden'); $('#codigo_new').focus()">
                    <i class="fa fa-fw fa-plus"></i>
                </button>
            </th>
        </tr>
        <tr id="tr_new_categoria" class="hidden">
            <th class="text-center" style="border-color: #9d9d9d">
                <input type="text" style="width: 100%" class="text-center bg-yura_dark" id="nombre_new"
                    name="nombre_new" placeholder="NOMBRE" required>
            </th>
            <th class="text-center" style="border-color: #9d9d9d" colspan="3">
                <button type="button" class="btn btn-xs btn-yura_primary" onclick="store_categoria()">
                    <i class="fa fa-fw fa-save"></i>
                </button>
            </th>
        </tr>
        @foreach ($listado as $item)
            <tr id="tr_categoria_{{ $item->id_categoria_producto }}">
                <th class="text-center" style="border-color: #9d9d9d; vertical-align: top">
                    <input type="text" style="width: 100%"
                        class="text-center {{ $item->estado == 0 ? 'error' : '' }}"
                        id="nombre_{{ $item->id_categoria_producto }}" value="{{ $item->nombre }}" required>
                </th>
                <th class="text-center" style="border-color: #9d9d9d;">
                    <div class="btn-group">
                        <button type="button" class="btn btn-xs btn-yura_warning"
                            onclick="update_categoria('{{ $item->id_categoria_producto }}')">
                            <i class="fa fa-fw fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-xs btn-yura_danger"
                            onclick="cambiar_estado_categoria('{{ $item->id_categoria_producto }}', '{{ $item->estado }}')">
                            <i class="fa fa-fw fa-{{ $item->estado == 1 ? 'lock' : 'unlock' }}"></i>
                        </button>
                    </div>
                </th>
            </tr>
        @endforeach
    </table>
</div>

<script>
    function update_categoria(id) {
        datos = {
            _token: '{{ csrf_token() }}',
            id: id,
            nombre: $('#nombre_' + id).val(),
        }
        post_jquery_m('{{ url('categorias_producto/update_categoria') }}', datos, function() {}, 'div_listado');
    }

    function cambiar_estado_categoria(p, estado) {
        mensaje = {
            title: estado == 1 ? '<i class="fa fa-fw fa-trash"></i> Desactivar categoria' :
                '<i class="fa fa-fw fa-unlock"></i> Activar categoria',
            mensaje: estado == 1 ?
                '<div class="alert alert-danger text-center"><i class="fa fa-fw fa-exclamation-triangle"></i> ¿Está seguro de desactivar esta categoria?</div>' :
                '<div class="alert alert-info text-center"><i class="fa fa-fw fa-exclamation-triangle"></i> ¿Está seguro de activar esta categoria?</div>',
        };
        modal_quest('modal_delete_categoria', mensaje['mensaje'], mensaje['title'], true, false,
            '{{ isPC() ? '45%' : '' }}',
            function() {
                datos = {
                    _token: '{{ csrf_token() }}',
                    id: p,
                };
                post_jquery_m('{{ url('categorias_producto/cambiar_estado_categoria') }}', datos, function() {
                    cerrar_modals();
                    listar_reporte();
                });
            });
    }

    function store_categoria() {
        mensaje = {
            title: '<i class="fa fa-fw fa-exclamation-triangle"></i> Mensaje de confirmacion',
            mensaje: '<div class="alert alert-info text-center" style="font-size: 16px">¿Está seguro de <b>CREAR</b> una nueva categoria?</div>',
        };
        modal_quest('modal_delete_producto', mensaje['mensaje'], mensaje['title'], true, false,
            '{{ isPC() ? '50%' : '' }}',
            function() {
                datos = {
                    _token: '{{ csrf_token() }}',
                    nombre: $('#nombre_new').val(),
                };
                post_jquery_m('{{ url('categorias_producto/store_categoria') }}', datos, function() {
                    cerrar_modals();
                    listar_reporte();
                });
            });
    }
</script>
