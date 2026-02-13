<script>
    $('#variedad_filtro').select2();
    listar_reporte();
    
    function listar_reporte() {
        datos = {
            variedad: $('#variedad_filtro').val(),
            fecha: $('#fecha_filtro').val(),
        };
        get_jquery('{{ url('orden_trabajo/listar_reporte') }}', datos, function(retorno) {
            $('#div_listado').html(retorno);
            estructura_tabla('table_orden_trabajo');
            $('#table_orden_trabajo_filter label input').addClass('input-yura_default')
        });
    }
</script>
