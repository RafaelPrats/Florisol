<script>
    $('#vista_actual').val('reporte_enraizamiento');

    listar_reporte_enraizamiento();

    function listar_reporte_enraizamiento() {
        datos = {
            desde: $('#filtro_desde').val(),
            hasta: $('#filtro_hasta').val(),
        };
        get_jquery('{{url('reporte_enraizamiento/listar_reporte_enraizamiento')}}', datos, function (retorno) {
            $('#listado_reporte').html(retorno);
        });
    }
</script>