<script>
    $('#vista_actual').val('bitacora_recepcion');
    $('#planta_filtro').select2();
    $('#variedad_filtro').select2();
    listar_reporte();

    function listar_reporte() {
        datos = {
            planta: $('#planta_filtro').val(),
            variedad: $('#variedad_filtro').val(),
            bodega: $('#bodega_filtro').val(),
            desde: $('#desde_filtro').val(),
            hasta: $('#hasta_filtro').val(),
        };
        if (datos['variedad'] != '') {
            get_jquery('{{ url('bitacora_recepcion/listar_reporte') }}', datos, function(retorno) {
                $('#div_listado').html(retorno);
            });
        }
    }
</script>
