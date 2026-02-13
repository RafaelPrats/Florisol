<script>
    $('#vista_actual').val('importar_pedidos');
    listar_reporte();

    function listar_reporte() {
        datos = {
            fecha: $('#filtro_fecha').val(),
            cliente: $('#filtro_cliente').val(),
        }
        get_jquery('{{ url('importar_pedidos/listar_reporte') }}', datos, function(retorno) {
            $('#div_listado').html(retorno);
        });
    }

    function add_pedido() {
        datos = {}
        get_jquery('{{ url('importar_pedidos/add_pedido') }}', datos, function(retorno) {
            modal_view('modal_add_pedido', retorno, '<i class="fa fa-fw fa-plus"></i> Importar Pedidos',
                true, false, '{{ isPC() ? '95%' : '' }}',
                function() {});
        })
    }

    function ver_procesos() {
        datos = {}
        get_jquery('{{ url('importar_pedidos/ver_procesos') }}', datos, function(retorno) {
            modal_view('modal_ver_procesos', retorno, '<i class="fa fa-fw fa-plus"></i> Procesos',
                true, false, '{{ isPC() ? '95%' : '' }}',
                function() {});
        })
    }
</script>
