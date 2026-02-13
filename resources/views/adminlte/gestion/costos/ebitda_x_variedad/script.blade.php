<script>
    $('#vista_actual').val('ebitda_x_variedad');

    listado_ebitda_x_variedad();

    function listado_ebitda_x_variedad() {
        datos = {
            semana: $('#filtro_semana').val(),
            planta: $('#filtro_planta').val(),
            tipo_planta: $('#filtro_tipo_planta').val(),
        };

        get_jquery('{{ url('ebitda_x_variedad/listado_ebitda_x_variedad') }}', datos, function(retorno) {
            $('#div_reporte').html(retorno);
            estructura_tabla('table_operaciones', false, false);
            $('#table_operaciones_filter').remove();
        });
    }

    function seleecionar_tipo_planta(tipo) {
        $('#filtro_planta').val('T');
        $('.option_planta').addClass('hidden');
        $('.option_planta_' + tipo).removeClass('hidden');
    }

    function exportar_listado_operaciones() {
        li_reportes = $('.reportes');
        columnas = [];
        for (i = 0; i < li_reportes.length; i++) {
            id = li_reportes[i].id;
            if ($('#' + id).hasClass('bg-aqua')) {
                columnas.push(id);
            }
        }
        $.LoadingOverlay('show');
        window.open('{{ url('tabla_operaciones/exportar_listado_operaciones') }}?desde=' + $('#desde').val() +
            '&hasta=' + $('#hasta').val() +
            '&columnas=' + columnas, '_blank');
        $.LoadingOverlay('hide');
    }
</script>
