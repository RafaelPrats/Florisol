<script>
    $('#vista_actual').val('ejecucion_no_perennes');
    listar_ejecucion_no_perennes();

    function listar_ejecucion_no_perennes() {
        datos = {
            semana: $('#filtro_semana').val(),
        };
        get_jquery('{{ url('ejecucion_no_perennes/listar_ejecucion_no_perennes') }}', datos, function(retorno) {
            $('#div_listado').html(retorno);
        });
    }
</script>
