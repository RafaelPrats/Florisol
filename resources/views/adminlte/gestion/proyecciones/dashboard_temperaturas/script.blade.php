<script>
    $('#vista_actual').val('dashboard_temperaturas');
    listar_graficas_temperaturas();

    function listar_graficas_temperaturas() {
        datos = {
            desde: $('#filtro_desde').val(),
            hasta: $('#filtro_hasta').val(),
        };
        if (datos['desde'] <= datos['hasta']) {
            get_jquery('{{url('dashboard_temperaturas/listar_graficas_temperaturas')}}', datos, function (retorno) {
                $('#div_listado_temperaturas').html(retorno);
            });
        }
    }
</script>