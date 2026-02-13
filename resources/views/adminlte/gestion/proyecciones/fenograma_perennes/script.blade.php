<script>
    $('#vista_actual').val('fenograma_perennes');
    listar_fenograma_perennes();

    function listar_fenograma_perennes() {
        datos = {
            tipo_planta: $('#tipo_planta').val(),
            planta: $('#filtro_predeterminado_planta_' + $('#tipo_planta').val()).val(),
            variedad: $('#filtro_predeterminado_variedad').val(),
            semana: $('#filtro_predeterminado_semana').val(),
        };
        if (datos['tipo_planta'] == 'P')
            get_jquery('{{url('fenograma_perennes/listar_fenograma_perennes')}}', datos, function (retorno) {
                $('#div_listado_fenograma').html(retorno);
                estructura_tabla('table_fenograma_perennes');
                $('#table_fenograma_perennes_filter').addClass('hidden');
            });
        else
            get_jquery('{{url('fenograma_no_perennes/listar_fenograma')}}', datos, function (retorno) {
                $('#div_listado_fenograma').html(retorno);
                estructura_tabla('table_fenograma_perennes');
                $('#table_fenograma_perennes_filter').addClass('hidden');
            });
    }
</script>