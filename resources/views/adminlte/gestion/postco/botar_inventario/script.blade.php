<script>
    $('#vista_actual').val('botar_inventario');
    $('#planta_filtro').select2();
    $('#variedad_filtro').select2();
    listar_reporte();

    function listar_reporte() {
        datos = {
            planta: $('#planta_filtro').val(),
            variedad: $('#variedad_filtro').val(),
            fecha: $('#fecha_filtro').val(),
            bodega: $('#bodega_filtro').val(),
        };
        get_jquery('{{ url('botar_inventario/listar_reporte') }}', datos, function(retorno) {
            $('#div_listado').html(retorno);
        });
    }

    function admin_motivos() {
        datos = {}
        get_jquery('{{ url('botar_inventario/admin_motivos') }}', datos, function(retorno) {
            modal_view('modal_admin_motivos', retorno,
                '<i class="fa fa-fw fa-plus"></i> Administrar Motivos',
                true, false, '{{ isPC() ? '50%' : '' }}',
                function() {});
        })
    }
</script>
