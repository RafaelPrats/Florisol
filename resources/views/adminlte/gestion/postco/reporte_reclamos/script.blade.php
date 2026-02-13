<script>
    $('#vista_actual').val('reporte_reclamos');
    listar_reporte();
    var num_grafica = 0;

    function listar_reporte() {
        $('#div_listado').html('');
        datos = {
            motivo: $('#filtro_motivo').val(),
            desde: $('#filtro_desde').val(),
            hasta: $('#filtro_hasta').val(),
            tipo: $('#filtro_tipo').val(),
        };
        get_jquery('{{ url('reporte_reclamos/listar_reporte') }}', datos, function(retorno) {
            num_grafica = 0;
            $('#div_listado').html(retorno);
        });
    }

    function exportar_reporte() {
        $.LoadingOverlay('show');
        window.open('{{ url('reporte_reclamos/exportar_reporte') }}?motivo=' + $('#filtro_motivo').val() +
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

    function seleccionar_variedad(id_var) {
        $('#filtro_variedad').val(id_var);
        $('#filtro_tipo').val('M');
        listar_reporte();
    }

    function seleccionar_motivo(id_motivo) {
        $('#filtro_motivo').val(id_motivo);
        $('#filtro_tipo').val('V');
        listar_reporte();
    }
</script>
