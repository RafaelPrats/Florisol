<script>
    $('#vista_actual').val('confirmar_proyecto');
    listar_reporte();

    function listar_reporte() {
        datos = {
            id_proyecto: $('#codigo_pedido').val(),
            id_finca: $('#finca_proveedor').val(),
        };
        if (datos['id_proyecto'] != '' && datos['id_finca'] != '') {
            get_jquery('{{ url('confirmar_proyecto/listar_reporte') }}', datos, function(retorno) {
                $('#div_listado').html(retorno);
            });
        } else {
            $('#div_listado').html('');
        }
    }

    function exportar_reporte() {
        $.LoadingOverlay('show');
        window.open('{{ url('confirmar_proyecto/exportar_reporte') }}?planta=' + $("#planta_filtro").val() +
            '&variedad=' + $("#variedad_filtro").val() +
            '&bodega=' + $("#bodega_filtro").val() +
            '&desde=' + $("#desde_filtro").val() +
            '&hasta=' + $("#hasta_filtro").val(), '_blank');
        $.LoadingOverlay('hide');
    }
</script>
