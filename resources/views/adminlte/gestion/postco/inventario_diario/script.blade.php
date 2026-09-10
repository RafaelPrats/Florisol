<script>
    $('#vista_actual').val('inventario_diario');
    $('#planta_filtro').select2();
    $('#variedad_filtro').select2();
    listar_reporte();

    function listar_reporte() {
        datos = {
            planta: $('#planta_filtro').val(),
            variedad: $('#variedad_filtro').val(),
            bodega: $('#bodega_filtro').val(),
            fecha: $('#fecha_filtro').val(),
        };
        get_jquery('{{ url('inventario_diario/listar_reporte') }}', datos, function(retorno) {
            $('#div_listado').html(retorno);
        });
    }
</script>
