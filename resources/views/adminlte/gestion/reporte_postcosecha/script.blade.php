<script>
    $('#vista_actual').val('reporte_postcosecha');
    listar_reporte();

    function listar_reporte() {
        datos = {
            planta: $('#filtro_planta').val(),
            variedad: $('#filtro_variedad').val(),
            desde: $('#filtro_desde').val(),
            hasta: $('#filtro_hasta').val(),
        };
        get_jquery('{{ url('reporte_postcosecha/listar_reporte') }}', datos, function(retorno) {
            $('#div_listado').html(retorno);
        });
    }

    function exportar_reporte() {
        $.LoadingOverlay('show');
        window.open('{{ url('reporte_postcosecha/exportar_reporte') }}?planta=' + $('#filtro_planta').val() +
            '&variedad=' + $('#filtro_variedad').val() +
            '&desde=' + $('#filtro_desde').val() +
            '&hasta=' + $('#filtro_hasta').val(), '_blank');
        $.LoadingOverlay('hide');
    }
</script>
