<script>
    $('#vista_actual').val('pedidos');
    //listar_reporte();

    function listar_reporte() {
        datos = {
            fecha: $('#filtro_fecha').val(),
        }
        get_jquery('{{ url('pedidos/listar_reporte') }}', datos, function(retorno) {
            $('#div_listado').html(retorno);
            estructura_tabla('table_listado');
            $('#table_listado_filter label input').addClass('input-yura_default')
        });
    }

    function add_pedido() {
        datos = {}
        get_jquery('{{ url('pedidos/add_pedido') }}', datos, function(retorno) {
            modal_view('modal_add_pedido', retorno, '<i class="fa fa-fw fa-plus"></i> Formulario Pedido',
                true, false, '{{ isPC() ? '98%' : '' }}',
                function() {});
        })
    }
</script>
