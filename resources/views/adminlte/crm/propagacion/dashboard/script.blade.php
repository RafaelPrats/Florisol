<script>
    $('#vista_actual').val('dashboard_propagacion');
    listar_graficas();

    function listar_graficas() {
        datos = {
            rango: $('#filtro_predeterminado_rango').val(),
            planta: $('#filtro_predeterminado_planta').val(),
            variedad: $('#filtro_predeterminado_variedad').val(),
        };

        get_jquery('{{url('dashboard_propagacion/listar_graficas')}}', datos, function (retorno) {
            $('#div_cargar_graficas').html(retorno);
        }, 'div_graficas');
    }
</script>