<script>
    $('#vista_actual').val('proy_no_perennes');

    function listar_proyecciones_no_perennes() {
        datos = {
            tipo_planta: $('#tipo_planta').val(),
            planta: $('#filtro_predeterminado_planta_' + $('#tipo_planta').val()).val(),
            variedad: $('#filtro_predeterminado_variedad').val(),
            anno: $('#filtro_predeterminado_anno').val(),
        };
        if (datos['planta'] != '') {
            get_jquery('{{url('proy_normales/listar_proyecciones')}}', datos, function (retorno) {
                $('#div_listado_proyecciones').html(retorno);
            });
        }
    }

    function exportar_reporte_proyecciones() {
        if ($('#filtro_predeterminado_planta_' + $('#tipo_planta').val()).val() != '') {
            $.LoadingOverlay('show');
            window.open('{{url('proy_normales/exportar_reporte_proyecciones')}}?tipo_planta=' + $('#tipo_planta').val() +
                '&planta=' + $('#filtro_predeterminado_planta_' + $('#tipo_planta').val()).val() +
                '&variedad=' + $('#filtro_predeterminado_variedad').val() +
                '&anno=' + $('#filtro_predeterminado_anno').val()
                , '_blank');
            $.LoadingOverlay('hide');
        }
    }
</script>