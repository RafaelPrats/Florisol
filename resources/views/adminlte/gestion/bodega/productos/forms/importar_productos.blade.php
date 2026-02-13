<div style="overflow-y: scroll; max-height: 700px">
    <table class="table-bordered" style="border: 1px solid #9d9d9d; width: 100%">
        <tr class="tr_fija_top_0">
            <th class="text-center th_yura_green" style="width: 120px">
                CODIGO
            </th>
            <th class="text-center th_yura_green">
                NOMBRE
            </th>
            <th class="text-center th_yura_green" style="width: 60px">
                UM
            </th>
        </tr>
        @foreach ($listado as $pos => $item)
            <tr class="tr_import_producto {{ $item['model'] == '' ? 'error' : '' }}" data-pos="{{ $pos }}"
                data-id_producto="{{ $item['model'] != '' ? $item['model']->id_producto : '' }}">
                <td class="text-center" style="border-color: #9d9d9d">
                    <input type="text" id="import_codigo_{{ $pos }}" style="width: 100%" class="text-center"
                        value="{{ $item['codigo'] }}">
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <input type="text" id="import_nombre_{{ $pos }}" style="width: 100%" class="text-center"
                        value="{{ $item['nombre'] }}">
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <input type="text" id="import_um_{{ $pos }}" style="width: 100%" class="text-center"
                        value="{{ $item['um'] }}">
                </td>
            </tr>
        @endforeach
    </table>
</div>

<div class="text-center" style="margin-top: 5px">
    <button type="button" class="btn btn-yura_primary" onclick="store_importar_productos()">
        <i class="fa fa-fw fa-save"></i> GRABAR PRODUCTOS
    </button>
</div>

<script>
    function store_importar_productos() {
        data = [];
        $('.tr_import_producto').each(function(td) {
            pos = $(this).data('pos');
            id_prod = $(this).data('id_producto');
            codigo = $('#import_codigo_' + pos).val();
            nombre = $('#import_nombre_' + pos).val();
            um = $('#import_um_' + pos).val();
            data.push({
                id_prod: id_prod,
                codigo: codigo,
                nombre: nombre,
                um: um,
            });
        })
        datos = {
            _token: '{{ csrf_token() }}',
            data: JSON.stringify(data),
        };
        post_jquery_m('{{ url('bodega_productos/store_importar_productos') }}', datos, function() {
            cerrar_modals();
            listar_reporte();
        });
    }
</script>
