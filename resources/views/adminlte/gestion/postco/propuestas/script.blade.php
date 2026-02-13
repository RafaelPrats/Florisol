<script>
    $('#vista_actual').val('propuestas');
    var num_grafica = 0;

    function listar_reporte() {
        $('#div_listado').html('');
        datos = {
            color: $('#filtro_color').val(),
            season: $('#filtro_season').val(),
            planta: $('#filtro_planta').val(),
            variedad: $('#filtro_variedad').val(),
            busqueda: $('#filtro_busqueda').val(),
        };
        get_jquery('{{ url('propuestas/listar_reporte') }}', datos, function(retorno) {
            num_grafica = 0;
            $('#div_listado').html(retorno);
        });
    }

    function exportar_reporte() {
        $.LoadingOverlay('show');
        window.open('{{ url('propuestas/exportar_reporte') }}?motivo=' + $('#filtro_motivo').val() +
            '&desde=' + $('#filtro_desde').val() +
            '&hasta=' + $('#filtro_hasta').val() +
            '&tipo=' + $('#filtro_tipo').val(), '_blank');
        $.LoadingOverlay('hide');
    }

    function add_propuesta() {
        datos = {}
        get_jquery('{{ url('propuestas/add_propuesta') }}', datos, function(retorno) {
            modal_view('modal_add_propuesta', retorno, '<i class="fa fa-fw fa-plus"></i> Agregar Prouesta',
                true, false, '{{ isPC() ? '95%' : '' }}',
                function() {});
        })
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
