<div style="overflow-y: scroll; overflow-x: scroll; height: 700px;">
    <table class="table-striped table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
        <tr id="tr_fija_top_0">
            <th class="text-center th_yura_green">
                NOMBRE
            </th>
            <th class="text-center th_yura_green" style="width: 60px">
                <button type="button" class="btn btn-xs btn-yura_default"
                    onclick="$('#tr_new_proveedor').removeClass('hidden'); $('#codigo_new').focus()">
                    <i class="fa fa-fw fa-plus"></i>
                </button>
            </th>
        </tr>
        <tr id="tr_new_proveedor" class="hidden">
            <th class="text-center" style="border-color: #9d9d9d">
                <input type="text" style="width: 100%" class="text-center bg-yura_dark" id="nombre_new"
                    name="nombre_new" placeholder="NOMBRE" required>
            </th>
            <th class="text-center" style="border-color: #9d9d9d" colspan="3">
                <button type="button" class="btn btn-xs btn-yura_primary" onclick="store_proveedor()">
                    <i class="fa fa-fw fa-save"></i>
                </button>
            </th>
        </tr>
        @foreach ($listado as $item)
            <tr id="tr_proveedor_{{ $item->id_proveedor }}">
                <th class="text-center" style="border-color: #9d9d9d; vertical-align: top">
                    <input type="text" style="width: 100%" class="text-center {{ $item->estado == 0 ? 'error' : '' }}" id="nombre_{{ $item->id_proveedor }}"
                        value="{{ $item->nombre }}" required>
                </th>
                <th class="text-center" style="border-color: #9d9d9d; vertical-align: top">
                    <div class="btn-group">
                        <button type="button" class="btn btn-xs btn-yura_warning"
                            onclick="update_proveedor('{{ $item->id_proveedor }}')">
                            <i class="fa fa-fw fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-xs btn-yura_danger"
                            onclick="cambiar_estado_proveedor('{{ $item->id_proveedor }}', '{{ $item->estado }}')">
                            <i class="fa fa-fw fa-{{ $item->estado == 1 ? 'lock' : 'unlock' }}"></i>
                        </button>
                    </div>
                </th>
            </tr>
        @endforeach
    </table>
</div>

<script>
    function store_proveedor() {
        datos = {
            _token: '{{ csrf_token() }}',
            nombre: $('#nombre_new').val()
        }
        post_jquery_m('{{ url('proveedores/store_proveedor') }}', datos, function() {
            listar_reporte();
        }, 'div_listado');
    }

    function update_proveedor(id) {
        datos = {
            _token: '{{ csrf_token() }}',
            id:id,
            nombre: $('#nombre_'+id).val(),
        }
        post_jquery_m('{{ url('proveedores/update_proveedor') }}', datos, function() {
        }, 'div_listado');
    }

    function cambiar_estado_proveedor(p, estado) {
        mensaje = {
            title: estado == 1 ? '<i class="fa fa-fw fa-trash"></i> Desactivar proveedor' :
                '<i class="fa fa-fw fa-unlock"></i> Activar proveedor',
            mensaje: estado == 1 ?
                '<div class="alert alert-danger text-center"><i class="fa fa-fw fa-exclamation-triangle"></i> ¿Está seguro de desactivar este proveedor?</div>' :
                '<div class="alert alert-info text-center"><i class="fa fa-fw fa-exclamation-triangle"></i> ¿Está seguro de activar este proveedor?</div>',
        };
        modal_quest('modal_delete_proveedor', mensaje['mensaje'], mensaje['title'], true, false,
            '{{ isPC() ? '45%' : '' }}',
            function() {
                datos = {
                    _token: '{{ csrf_token() }}',
                    id: p,
                };
                post_jquery_m('{{ url('proveedores/cambiar_estado_proveedor') }}', datos, function() {
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
                    nombre: $('#new_nombre_categoria').val(),
                };
                post_jquery_m('{{ url('bodega_productos/store_categoria') }}', datos, function() {
                    cerrar_modals();
                    listar_reporte();
                });
            });
    }

</script>
