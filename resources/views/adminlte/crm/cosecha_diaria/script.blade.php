<script>
    $('#vista_actual').val('cosecha_diaria');
    buscar_cosecha_diaria();

    function buscar_cosecha_diaria() {
        datos = {
            sector: $('#filtro_predeterminado_sector').val(),
            variedad: $('#filtro_predeterminado_variedad').val(),
            desde: $('#filtro_predeterminado_desde').val(),
            hasta: $('#filtro_predeterminado_hasta').val(),
            planta: $('#filtro_predeterminado_planta').val(),
        };
        get_jquery('{{ url('cosecha_diaria/buscar_cosecha_diaria') }}', datos, function(retorno) {
            $('#div_listado_cosecha_diaria').html(retorno);
        });
    }

    function exportar_reporte() {
        $.LoadingOverlay('show');
        window.open('{{ url('cosecha_diaria/exportar_reporte') }}?desde=' + $('#filtro_predeterminado_desde').val() +
            '&sector=' + $('#filtro_predeterminado_sector').val() +
            '&hasta=' + $('#filtro_predeterminado_hasta').val() +
            '&variedad=' + $('#filtro_predeterminado_variedad').val() +
            '&planta=' + $('#filtro_predeterminado_planta').val(), '_blank');
        $.LoadingOverlay('hide');
    }
</script>
