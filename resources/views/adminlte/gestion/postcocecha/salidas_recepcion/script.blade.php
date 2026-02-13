<script>
    $('#planta_filtro, #variedad_filtro').select2();
    listar_reporte();
    
    function listar_reporte() {
        datos = {
            planta: $('#planta_filtro').val(),
            variedad: $('#variedad_filtro').val(),
            desde: $('#desde_filtro').val(),
            hasta: $('#hasta_filtro').val(),
            criterio: $('#criterio_filtro').val(),
        };
        get_jquery('{{ url('salidas_recepcion/listar_reporte') }}', datos, function(retorno) {
            $('#div_listado').html(retorno);
            estructura_tabla('table_listado');
            $('#table_listado_filter label input').addClass('input-yura_default');
        });
    }
</script>
