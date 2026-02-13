<script>
    buscar_bqt_diaria();

    function buscar_bqt_diaria() {
        datos = {
            variedad: $('#filtro_predeterminado_variedad').val(),
            desde: $('#filtro_predeterminado_desde').val(),
            hasta: $('#filtro_predeterminado_hasta').val(),
            planta: $('#filtro_predeterminado_planta').val(),
        };
        get_jquery('{{url('bqt_diaria/buscar_bqt_diaria')}}', datos, function (retorno) {
            $('#div_listado_bqt_diaria').html(retorno);
            estructura_tabla('table_listado_bqt_diaria')
            $('#table_listado_bqt_diaria_filter label input').addClass('input-yura_default');
        });
    }
</script>