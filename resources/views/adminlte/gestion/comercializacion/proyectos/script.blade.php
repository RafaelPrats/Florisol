<script>
    $('#vista_actual').val('proyectos');
    $('#cliente_filtro').select2();
    listar_reporte();

    function listar_reporte() {
        datos = {
            fecha: $('#fecha_filtro').val(),
            segmento: $('#segmento_filtro').val(),
            tipo: $('#tipo_filtro').val(),
            cliente: $('#cliente_filtro').val(),
        };
        get_jquery('{{ url('proyectos/listar_reporte') }}', datos, function(retorno) {
            $('#div_listar_reporte').html(retorno);
        });
    }

    function add_proyecto() {
        datos = {}
        get_jquery('{{ url('proyectos/add_proyecto') }}', datos, function(retorno) {
            modal_view('modal_add_proyecto', retorno, '<i class="fa fa-fw fa-plus"></i> Nuevo Pedido',
                true, false, '{{ isPC() ? '95%' : '' }}',
                function() {});
        })
    }
</script>
