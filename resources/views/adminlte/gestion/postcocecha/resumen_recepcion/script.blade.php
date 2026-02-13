<script>
    $('#vista_actual').val('inventario_cosecha');
    listar_reporte();

    function listar_reporte() {
        datos = {
            planta: $('#planta_filtro').val(),
            variedad: $('#variedad_filtro').val(),
            desde: $('#desde_filtro').val(),
            hasta: $('#hasta_filtro').val(),
        };
        get_jquery('{{ url('resumen_recepcion/listar_reporte') }}', datos, function(retorno) {
            $('#div_listado').html(retorno);
            estructura_tabla('table_resumen_recepcion');
            $('#table_resumen_recepcion_filter label input').addClass('input-yura_default')
        });
    }
</script>
