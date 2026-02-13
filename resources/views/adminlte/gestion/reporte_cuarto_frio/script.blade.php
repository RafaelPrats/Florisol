<script>
    $('#vista_actual').val('reporte_cuarto_frio');
    listar_reporte();

    function listar_reporte() {
        datos = {
            planta: $('#filtro_planta').val(),
            variedad: $('#filtro_variedad').val(),
        };
        get_jquery('{{ url('reporte_cuarto_frio/listar_reporte') }}', datos, function(retorno) {
            $('#div_listado').html(retorno);
        });
    }

    function importar_bajas() {
        datos = {}
        get_jquery('{{ url('reporte_cuarto_frio/importar_bajas') }}', datos, function(retorno) {
            modal_view('modal_importar_bajas', retorno, '<i class="fa fa-fw fa-plus"></i> Formulario Bajas',
                true, false, '{{ isPC() ? '75%' : '' }}',
                function() {});
        })
    }

    function exportar_reporte() {
        $.LoadingOverlay('show');
        window.open('{{ url('reporte_cuarto_frio/exportar_reporte') }}?planta=' + $('#filtro_planta').val() +
            '&variedad=' + $('#filtro_variedad').val(), '_blank');
        $.LoadingOverlay('hide');
    }
</script>
