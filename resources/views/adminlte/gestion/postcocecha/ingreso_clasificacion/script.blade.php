<script>
    $('#vista_actual').val('ingreso_clasificacion');
    //listar_reporte();
    $('#variedad_filtro').select2();

    function listar_reporte() {
        datos = {
            fecha: $('#fecha_filtro').val(),
            variedad: $('#variedad_filtro').val(),
            dias: $('#dias_filtro').val(),
        };
        get_jquery('{{ url('ingreso_clasificacion/listar_reporte') }}', datos, function(retorno) {
            $('#div_listar_reporte').html(retorno);
        });
    }
</script>
