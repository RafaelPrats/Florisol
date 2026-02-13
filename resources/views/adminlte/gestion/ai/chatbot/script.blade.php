<script>
    mostrar_chatbot();

    function mostrar_chatbot() {
        datos = {};
        get_jquery('{{ url('chatbot/cargar_chatbot') }}', datos, function(retorno) {
            $('#div_listado').html(retorno);
        });
    }
</script>
