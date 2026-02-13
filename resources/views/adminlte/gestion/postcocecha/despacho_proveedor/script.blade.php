<script>
    $('#vista_actual').val('despacho_proveedor');
    listar_reporte();
    $('#variedad_filtro').select2();

    function listar_reporte() {
        datos = {
            fecha: $('#fecha_filtro').val(),
            variedad: $('#variedad_filtro').val(),
            usuario: $('#usuario_filtro').val(),
        };
        get_jquery('{{ url('despacho_proveedor/listar_reporte') }}', datos, function(retorno) {
            $('#div_listar_reporte').html(retorno);
            estructura_tabla('table_listado');
        });
    }

    function add_despacho() {
        datos = {}
        get_jquery('{{ url('despacho_proveedor/add_despacho') }}', datos, function(retorno) {
            modal_view('modal_add_despacho', retorno, '<i class="fa fa-fw fa-plus"></i> Agregar despachos',
                true, false, '{{ isPC() ? '75%' : '' }}',
                function() {});
        })
    }

    function imprimir_all(list) {
        $.LoadingOverlay('show');
        window.open('{{ url('despacho_proveedor/imprimir_all') }}?data=' + JSON.stringify(list), '_blank');
        $.LoadingOverlay('hide');
    }
</script>
