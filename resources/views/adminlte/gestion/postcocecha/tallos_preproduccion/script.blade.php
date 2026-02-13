<script>
    $('#vista_actual').val('tallos_preproduccion');
    listar_reporte();
    $('#variedad_filtro').select2();

    function listar_reporte() {
        datos = {
            fecha: $('#fecha_filtro').val(),
            variedad: $('#variedad_filtro').val(),
        };
        get_jquery('{{ url('tallos_preproduccion/listar_reporte') }}', datos, function(retorno) {
            $('#div_listar_reporte').html(retorno);
        });
    }
</script>
