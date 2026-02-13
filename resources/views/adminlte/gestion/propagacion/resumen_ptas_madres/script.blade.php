<script>
    $('#vista_actual').val('resumen_plantas_madres');
    listar_resumen_ptas_madres();

    function listar_resumen_ptas_madres() {
        datos = {
            reporte: $('#filtro_predeterminado_reporte').val(),
            desde: $('#filtro_predeterminado_desde').val(),
            hasta: $('#filtro_predeterminado_hasta').val(),
        };
        get_jquery('{{url('resumen_plantas_madres/listar_resumen')}}', datos, function (retorno) {
            $('#listado_resumen').html(retorno);
            //estructura_tabla('table_contenedores', false, true);
        });
    }

    function job_update_propag(variedad) {
        datos = {
            _token: '{{csrf_token()}}',
            variedad: variedad,
            desde: $('#filtro_predeterminado_desde').val(),
            hasta: $('#filtro_predeterminado_hasta').val(),
        };
        $('#tr_variedad_' + variedad).LoadingOverlay('show');
        $.post('{{url('resumen_plantas_madres/job_update_propag')}}', datos, function () {
            //notificar()
        }, 'json').fail(function (retorno) {
            console.log(retorno);
            alerta_errores(retorno.responseText);
        }).always(function () {
            $('#tr_variedad_' + variedad).LoadingOverlay('hide');
        });
    }
</script>