<script>
    listar_historico_luz();

    function listar_historico_luz() {
        datos = {
            desde: $('#filtro_desde').val(),
            hasta: $('#filtro_hasta').val(),
        };
        get_jquery('{{url('historico_luz/listar_historico_luz')}}', datos, function (retorno) {
            $('#div_listado_ciclos').html(retorno);
        });
    }
</script>
