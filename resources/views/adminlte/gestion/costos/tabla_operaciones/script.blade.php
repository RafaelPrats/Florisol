<script>
    $('#vista_actual').val('tabla_operaciones');

    listado_operaciones();

    function listado_operaciones() {
        li_reportes = $('.reportes');
        columnas = [];
        for (i = 0; i < li_reportes.length; i++) {
            id = li_reportes[i].id;
            if ($('#' + id).hasClass('bg-aqua')) {
                columnas.push(id);
            }
        }
        datos = {
            desde: $('#desde').val(),
            hasta: $('#hasta').val(),
            planta: $('#filtro_planta').val(),
            columnas: columnas,
        };

        get_jquery('{{url('tabla_operaciones/listado_operaciones')}}', datos, function (retorno) {
            $('#div_reporte').html(retorno);
            estructura_tabla('table_operaciones', false, false);
            $('#table_operaciones_filter').remove();
        });
    }

    function select_columna(li) {
        id = li.attr('id');
        li.toggleClass('bg-aqua');
        $('.' + id).toggleClass('hidden');
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
        window.open('{{url('tabla_operaciones/exportar_listado_operaciones')}}?desde=' + $('#desde').val() +
            '&hasta=' + $('#hasta').val()+
            '&columnas=' + columnas
            , '_blank');
        $.LoadingOverlay('hide');
    }
</script>