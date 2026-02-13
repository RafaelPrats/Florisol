<script>
    $('#vista_actual').val('fenograma_no_perennes');
    listar_fenograma_no_perennes();
    
    function listar_fenograma_no_perennes() {
        datos = {
            planta: $('#filtro_predeterminado_planta').val(),
            variedad: $('#filtro_predeterminado_variedad').val(),
            semana: $('#filtro_predeterminado_semana').val(),
        };
        get_jquery('{{url('fenograma_no_perennes/listar_fenograma')}}', datos, function (retorno) {
            $('#div_listado_fenograma').html(retorno);
            estructura_tabla('table_fenograma_perennes');
            $('#table_fenograma_perennes_filter').addClass('hidden');
        });
    }
</script>