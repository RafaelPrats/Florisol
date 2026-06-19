<script>
    $('#vista_actual').val('movimientos_recepcion');
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
        get_jquery('{{ url('movimientos_recepcion/listar_reporte') }}', datos, function(retorno) {
            $('#div_listado').html(retorno);
        });
    }

    function exportar_reporte() {
        $.LoadingOverlay('show');
        window.open('{{ url('movimientos_recepcion/exportar_reporte') }}?planta=' + $("#planta_filtro").val() +
            '&variedad=' + $("#variedad_filtro").val() +
            '&bodega=' + $("#bodega_filtro").val() +
            '&desde=' + $("#desde_filtro").val() +
            '&hasta=' + $("#hasta_filtro").val(), '_blank');
        $.LoadingOverlay('hide');
    }
</script>
