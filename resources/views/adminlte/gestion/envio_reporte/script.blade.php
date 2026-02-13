<script>
    function seleccionar_reporte(r) {
        $('.tr_reporte').removeClass('bg-yura_dark');
        $('#tr_reporte_' + r).addClass('bg-yura_dark');
        datos = {
            reporte: r,
            dia: $('#dia_semana_' + r).val(),
            hora: $('#hora_reporte_' + r).val(),
        };
        get_jquery('{{url('envio_reporte/seleccionar_reporte')}}', datos, function (retorno) {
            $('#div_listado_usuarios').html(retorno);
        });
    }
</script>