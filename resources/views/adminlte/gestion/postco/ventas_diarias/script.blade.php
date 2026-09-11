<script>
    $('#vista_actual').val('ventas_diarias');
    $('#planta_filtro').select2();
    $('#variedad_filtro').select2();
    listar_reporte();

    function listar_reporte() {
        datos = {
            planta: $('#planta_filtro').val(),
            variedad: $('#variedad_filtro').val(),
            bodega: $('#bodega_filtro').val(),
            desde: $('#desde_filtro').val(),
            hasta: $('#hasta_filtro').val(),
        };
        get_jquery('{{ url('ventas_diarias/listar_reporte') }}', datos, function(retorno) {
            $('#div_listado').html(retorno);
        });
    }
</script>
