<script>
    function store_codigos() {
        data = [];
        input_valor = $('.input_valor');
        for (i = 0; i < input_valor.length; i++) {
            id = input_valor[i].id;
            id_codigo = $('#' + id).data('id');
            valor = $('#' + id).val();
            data.push({
                id: id_codigo,
                valor: valor,
            });
        }
        datos = {
            _token: '{{ csrf_token() }}',
            data: JSON.stringify(data),
        };
        post_jquery_m('{{ url('codigo_autorizacion/store_codigos') }}', datos, function(retorno) {

        });
    }
</script>
