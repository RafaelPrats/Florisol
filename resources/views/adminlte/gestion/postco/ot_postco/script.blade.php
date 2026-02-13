<script>
    $('#vista_actual').val('ot_postco');
    $('#variedad_filtro').select2();
    //listar_reporte();

    function listar_reporte() {
        datos = {
            fecha: $('#fecha_filtro').val(),
            variedad: $('#variedad_filtro').val(),
            desde: $('#desde_filtro').val(),
            hasta: $('#hasta_filtro').val(),
        };
        get_jquery('{{ url('ot_postco/listar_reporte') }}', datos, function(retorno) {
            $('#div_listar_reporte').html(retorno);
        });
    }
</script>
