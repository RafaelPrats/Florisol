<script>
    $('#vista_actual').val('ingreso_inventario');
    $('#planta_filtro').select2();
    $('#variedad_filtro').select2();
    listar_reporte();

    function listar_reporte() {
        datos = {
            planta: $('#planta_filtro').val(),
            variedad: $('#variedad_filtro').val(),
            fecha: $('#fecha_filtro').val(),
            documento: $('#documento_filtro').val(),
            bodega: $('#bodega_filtro').val(),
        };
        get_jquery('{{ url('ingreso_inventario/listar_reporte') }}', datos, function(retorno) {
            $('#div_listado').html(retorno);
        });
    }

    function admin_bodegas() {
        datos = {}
        get_jquery('{{ url('ingreso_inventario/admin_bodegas') }}', datos, function(retorno) {
            modal_view('modal_admin_bodegas', retorno,
                '<i class="fa fa-fw fa-plus"></i> Administrar Bodegas',
                true, false, '{{ isPC() ? '50%' : '' }}',
                function() {});
        })
    }
</script>
