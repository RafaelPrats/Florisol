<script>
    $('#vista_actual').val('fenograma_ejecucion');

    filtrar_ciclos_fenograma_ejecucion();

    function filtrar_ciclos_fenograma_ejecucion() {
        datos = {
            variedad: $('#filtro_predeterminado_variedad').val(),
            fecha: $('#filtro_predeterminado_fecha').val(),
            tipo: $('#filtro_predeterminado_tipo').val(),
            planta: $('#filtro_predeterminado_planta').val(),
            activo: $('#filtro_activo').val(),
            hasta: $('#filtro_predeterminado_hasta').val(),
            sector: $('#filtro_sector').val(),
            poda_siembra: $('#filtro_poda_siembra').val(),
        };
        get_jquery('{{ url('fenograma_ejecucion/filtrar_ciclos') }}', datos, function(retorno) {
            $('#div_listado_ciclos').html(retorno);
        });
    }

    function cambiar_filtro_activo(valor) {
        if (valor === '0') {
            $('#tr_filtro_hasta').removeClass('hidden');
        } else {
            $('#tr_filtro_hasta').addClass('hidden');
        }
    }

    function exportar_reporte() {
        $.LoadingOverlay('show');
        window.open('{{ url('fenograma_ejecucion/exportar_reporte') }}?var=' + $('#filtro_predeterminado_variedad')
            .val() +
            '&fecha=' + $('#filtro_predeterminado_fecha').val() +
            '&tipo=' + $('#filtro_predeterminado_tipo').val() +
            '&planta=' + $('#filtro_predeterminado_planta').val() +
            '&activo=' + $('#filtro_activo').val() +
            '&hasta=' + $('#filtro_predeterminado_hasta').val(), '_blank');
        $.LoadingOverlay('hide');
    }
</script>
