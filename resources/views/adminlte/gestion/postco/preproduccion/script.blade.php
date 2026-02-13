<script>
    $('#vista_actual').val('preproduccion');
    $('#variedad_filtro').select2();
    //listar_reporte();

    function listar_reporte() {
        datos = {
            fecha: $('#fecha_filtro').val(),
            variedad: $('#variedad_filtro').val(),
            desde: $('#desde_filtro').val(),
            hasta: $('#hasta_filtro').val(),
        };
        get_jquery('{{ url('preproduccion/listar_reporte') }}', datos, function(retorno) {
            $('#div_listar_reporte').html(retorno);
        });
    }

    function exportar_reporte() {
        $.LoadingOverlay('show');
        window.open('{{ url('preproduccion/exportar_reporte') }}?desde=' + $('#desde_filtro').val() +
            '&hasta=' + $('#hasta_filtro').val() +
            '&variedad=' + $('#variedad_filtro').val(), '_blank');
        $.LoadingOverlay('hide');
    }
</script>
