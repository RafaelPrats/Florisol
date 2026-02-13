<script>
    $('#vista_actual').val('planificacion');
    $('#variedad_filtro').select2()
    $('#flor_filtro').select2()
    listar_reporte('T');

    function listar_reporte() {
        datos = {
            desde: $('#desde_filtro').val(),
            hasta: $('#hasta_filtro').val(),
            variedad: $('#variedad_filtro').val(),
            flor: $('#flor_filtro').val(),
        };
        get_jquery('{{ url('planificacion/listar_reporte') }}', datos, function(retorno) {
            $('#div_listado').html(retorno);
            estructura_tabla('table_reporte');
            $('#table_reporte_filter label input').addClass('input-yura_default');
        });
    }
</script>
