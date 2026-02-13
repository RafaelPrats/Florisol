<script>
    $('#vista_actual').val('inventario_cosecha');
    //listar_inventario_cosecha_acumulado('T');

    function listar_inventario_cosecha() {
        datos = {
            proveedor: $('#proveedor_filtro').val(),
            planta: $('#planta_filtro').val(),
            variedad: $('#variedad_filtro').val(),
        };
        get_jquery('{{ url('inventario_cosecha/listar_inventario_cosecha') }}', datos, function(retorno) {
            $('#div_listado').html(retorno);
            estructura_tabla('table_inventario_cosecha');
            $('#table_inventario_cosecha_filter label input').addClass('input-yura_default')
        });
    }

    function listar_inventario_cosecha_acumulado() {
        datos = {
            planta: $('#planta_filtro').val(),
            variedad: $('#variedad_filtro').val(),
            fecha_venta: $('#fecha_venta_filtro').val(),
        };
        get_jquery('{{ url('inventario_cosecha/listar_inventario_cosecha_acumulado') }}', datos, function(retorno) {
            $('#div_listado').html(retorno);
            estructura_tabla('table_inventario_cosecha');
            $('#table_inventario_cosecha_filter label input').addClass('input-yura_default');
        });
    }
</script>
