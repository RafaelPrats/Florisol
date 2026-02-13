<script>
    $('#vista_actual').val('categorias_producto');
    listar_reporte();

    function listar_reporte() {
        datos = {
            busqueda: $('#filtro_busqueda').val(),
        };
        get_jquery('{{ url('categorias_producto/listar_reporte') }}', datos, function(retorno) {
            $('#div_listado').html(retorno);
        }, 'div_listado');
    }
</script>
