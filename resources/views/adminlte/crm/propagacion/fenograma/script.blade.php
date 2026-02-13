<script>
    $('#vista_actual').val('fenograma_propag');

    //filtrar_ciclos_fenograma_propag();

    function filtrar_ciclos_fenograma_propag() {
        datos = {
            planta: $('#filtro_predeterminado_planta').val(),
            variedad: $('#filtro_predeterminado_variedad').val(),
            fecha: $('#filtro_predeterminado_fecha').val(),
            tipo: $('#filtro_predeterminado_tipo').val(),
            finca_actual: $('#fincas_propias').val(),
        };
        get_jquery('{{url('fenograma_propag/filtrar_ciclos')}}', datos, function (retorno) {
            $('#div_listado_ciclos').html(retorno);
        });
    }
</script>