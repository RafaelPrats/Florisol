<script>
    $('#vista_actual').val('ingreso_inventario');
    $('#planta_filtro, #variedad_filtro, #documento_filtro').select2({ width: '100%' });
    listar_reporte();

    function listar_reporte() {
        var datos = {
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
        var datos = {};
        get_jquery('{{ url('ingreso_inventario/admin_bodegas') }}', datos, function(retorno) {
            modal_view('modal_admin_bodegas', retorno,
                '<i class="fa fa-cogs"></i> Bodegas por segmento',
                true, false, '{{ isPC() ? '70%' : '100%' }}',
                function() {});
        });
    }
</script>
