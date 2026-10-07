<script>
    $('#vista_actual').val('botar_inventario');
    $('#planta_filtro').select2({ width: '100%' });
    $('#variedad_filtro').select2({ width: '100%' });
    listar_reporte();

    function listar_reporte() {
        var datos = {
            planta: $('#planta_filtro').val(),
            variedad: $('#variedad_filtro').val(),
            fecha: $('#fecha_filtro').val(),
            bodega: $('#bodega_filtro').val(),
        };
        get_jquery('{{ url('botar_inventario/listar_reporte') }}', datos, function(retorno) {
            $('#div_listado').html(retorno);
        });
    }

    function listar_ordenes() {
        var datos = {
            planta: $('#planta_filtro').val(),
            variedad: $('#variedad_filtro').val(),
            fecha: $('#fecha_filtro').val(),
            bodega: $('#bodega_filtro').val(),
        };
        get_jquery('{{ url('botar_inventario/listar_ordenes') }}', datos, function(retorno) {
            $('#div_listado').html(retorno);
        });
    }

    function admin_motivos() {
        var datos = {};
        get_jquery('{{ url('botar_inventario/admin_motivos') }}', datos, function(retorno) {
            modal_view('modal_admin_motivos', retorno,
                '<i class="fa fa-list-alt"></i> Administrar motivos',
                true, false, '{{ isPC() ? '92%' : '100%' }}',
                function() {});
        });
    }
</script>
