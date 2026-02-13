<script>
    $('#vista_actual').val('ejecucion_luz');
    listar_ejecucion_luz();

    function listar_ejecucion_luz() {
        datos = {
            semana: $('#filtro_semana').val(),
            sector: $('#filtro_sector').val(),
        };
        get_jquery('{{ url('ejecucion_luz/listar_ejecucion_luz') }}', datos, function(retorno) {
            $('#div_listado_ciclos').html(retorno);
        });
    }
</script>
