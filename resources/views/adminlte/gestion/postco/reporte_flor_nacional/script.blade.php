<script>
    $('#vista_actual').val('reporte_flor_nacional');
    listar_reporte();
    var num_grafica = 0;

    function listar_reporte() {
        $('#div_listado').html('');
        datos = {
            motivo: $('#filtro_motivo').val(),
            planta: $('#filtro_planta').val(),
            variedad: $('#filtro_variedad').val(),
            desde: $('#filtro_desde').val(),
            hasta: $('#filtro_hasta').val(),
            finca: $('#filtro_finca').val(),
        };
        get_jquery('{{ url('reporte_flor_nacional/listar_reporte') }}', datos, function(retorno) {
            num_grafica = 0;
            $('#div_listado').html(retorno);
        });
    }

    function exportar_reporte() {
        $.LoadingOverlay('show');
        window.open('{{ url('reporte_flor_nacional/exportar_reporte') }}?motivo=' + $('#filtro_motivo').val() +
            '&desde=' + $('#filtro_desde').val() +
            '&hasta=' + $('#filtro_hasta').val() +
            '&tipo=' + $('#filtro_tipo').val(), '_blank');
        $.LoadingOverlay('hide');
    }

    function getListColores() {
        return [
            '#d01c62',
            '#1000ff',
            '#00b388',
            '#ef6e11',
            '#fff700',
            '#5e5e5e',
            '#ff75f4',
            '#00ffff',
            '#33ff00',
            "#7e0075"
        ];
    }
</script>
