<script>
    $('#vista_actual').val('reporte_luz');
    listar_reporte_luz();

    function listar_reporte_luz() {
        datos = {
            semana: $('#filtro_semana').val(),
            sector: $('#filtro_sector').val(),
        };
        if (datos['semana'] >= $('#semana_actual').val()) {
            get_jquery('{{ url('reporte_luz/listar_reporte_luz') }}', datos, function(retorno) {
                $('#div_listado_ciclos').html(retorno);
            });
        }
    }

    function listar_row_luz(id) {
        datos = {
            _token: '{{ csrf_token() }}',
            id: id,
        };
        $('#tr_luz_' + id).LoadingOverlay('show');
        $.post('{{ url('reporte_luz/listar_row_luz') }}', datos, function(retorno) {
            $('#td_inicio_luz_' + id).html(retorno.ini_luz);
            $('#td_sem_inicio_luz_' + id).html(retorno.sem_ini_luz);
            $('#td_fin_luz_' + id).html(retorno.fin_luz);
            $('#td_sem_fin_luz_' + id).html(retorno.sem_fin_luz);
            $('#td_horas_luz_' + id).html(retorno.horas_luz);
            $('#td_costo_luz_' + id).html(retorno.costo_luz);
            $('#td_costo_m2_' + id).html(retorno.costo_m2);
        }, 'json').always(function() {
            $('#tr_luz_' + id).LoadingOverlay('hide');
        });
    }

    function exportar_reporte() {
        $.LoadingOverlay('show');
        window.open('{{ url('reporte_luz/exportar_reporte') }}?semana=' + $('#filtro_semana').val() +
            '&sector= ' + $('#filtro_sector').val(), '_blank');
        $.LoadingOverlay('hide');
    }
</script>
