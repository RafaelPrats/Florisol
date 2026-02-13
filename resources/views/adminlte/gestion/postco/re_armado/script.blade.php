<script>
    $('#vista_actual').val('re_armado');
    $('#variedad_filtro').select2();
    //listar_reporte();

    function listar_reporte() {
        datos = {
            variedad: $('#variedad_filtro').val(),
            desde: $('#desde_filtro').val(),
            hasta: $('#hasta_filtro').val(),
        };
        get_jquery('{{ url('re_armado/listar_reporte') }}', datos, function(retorno) {
            $('#div_listar_reporte').html(retorno);
        });
    }

    function exportar_reporte() {
        $.LoadingOverlay('show');
        window.open('{{ url('re_armado/exportar_reporte') }}?desde=' + $('#desde_filtro').val() +
            '&hasta=' + $('#hasta_filtro').val() +
            '&variedad=' + $('#variedad_filtro').val(), '_blank');
        $.LoadingOverlay('hide');
    }
</script>
