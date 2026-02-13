<script>
    listar_distribucion_bqt();

    function listar_distribucion_bqt() {
        datos = {
            desde: $('#desde_search').val(),
            hasta: $('#hasta_search').val(),
            planta: $('#filtro_predeterminado_planta').val(),
        };

        get_jquery('{{url('distribucion_bqt/listar_distribucion_bqt')}}', datos, function (retorno) {
            $('#div_listado_distribucion_bqt').html(retorno);
            estructura_tabla('table_listado_distribucion_bqt', false, false);
            $('#table_listado_distribucion_bqt_filter').remove()
        });
    }
</script>