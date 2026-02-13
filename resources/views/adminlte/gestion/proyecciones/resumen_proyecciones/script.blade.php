<script>
    $('#vista_actual').val('resumen_proyecciones');
    listar_resumen_proyecciones();

    function listar_resumen_proyecciones() {
        data = {
            hasta: $("#filtro_predeterminado_hasta").val(),
            desde: $("#filtro_predeterminado_desde").val(),
        };
        get_jquery('{{url('resumen_proyecciones/listar_resumen_total')}}', data, function (retorno) {
            $('#listado_proyecciones_resumen_total').html(retorno);
        });
    }

    function exportar_reporte() {
        $.LoadingOverlay('show');
        window.open('{{url('resumen_proyecciones/exportar_reporte')}}?desde=' + $('#filtro_predeterminado_desde').val() +
            '&hasta=' + $('#filtro_predeterminado_hasta').val()
            , '_blank');
        $.LoadingOverlay('hide');
    }
</script>
