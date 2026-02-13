<script>
    $('#variedad_filtro').select2();

    function listar_reporte() {
        datos = {
            planta: $('#planta_filtro').val(),
            variedad: $('#variedad_filtro').val(),
            desde: $('#desde_filtro').val(),
            hasta: $('#hasta_filtro').val(),
        };
        get_jquery('{{ url('ingresos_proveedor/listar_reporte') }}', datos, function(retorno) {
            $('#div_listado').html(retorno);
        });
    }

    function exportar_listado(tipo, negativas) {
        $.LoadingOverlay('show');
        window.open('{{ url('ingresos_proveedor/exportar_listado') }}?planta=' + $("#planta_filtro").val() +
            '&variedad=' + $("#variedad_filtro").val() +
            '&desde=' + $("#desde_filtro").val() +
            '&hasta=' + $("#hasta_filtro").val(), '_blank');
        $.LoadingOverlay('hide');
    }
</script>
