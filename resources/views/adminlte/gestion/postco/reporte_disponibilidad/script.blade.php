<script>
    $('#variedad_filtro').select2();

    function listar_reporte() {
        datos = {
            planta: $('#planta_filtro').val(),
            variedad: $('#variedad_filtro').val(),
            hasta: $('#hasta_filtro').val(),
        };
        get_jquery('{{ url('reporte_disponibilidad/listar_reporte') }}', datos, function(retorno) {
            $('#div_listado').html(retorno);
        });
    }

    function exportar_listado(tipo, negativas) {
        $.LoadingOverlay('show');
        window.open('{{ url('reporte_disponibilidad/exportar_listado') }}?planta=' + $("#planta_filtro").val() +
            '&variedad=' + $("#variedad_filtro").val() +
            '&hasta=' + $("#hasta_filtro").val(), '_blank');
        $.LoadingOverlay('hide');
    }
</script>
