<script>
    $('#cliente_filtro').select2();
    listar_reporte();
    
    function listar_reporte() {
        datos = {
            cliente: $('#cliente_filtro').val(),
            fecha: $('#fecha_filtro').val(),
            despachador: $('#despachador_filtro').val(),
        };
        get_jquery('{{ url('despachos_preproduccion/listar_reporte') }}', datos, function(retorno) {
            $('#div_listado').html(retorno);
            /*estructura_tabla('table_despachos_preproduccion');
            $('#table_despachos_preproduccion_filter label input').addClass('input-yura_default')*/
        });
    }
</script>
