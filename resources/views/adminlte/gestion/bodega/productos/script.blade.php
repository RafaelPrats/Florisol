<script>
    listar_reporte();

    function listar_reporte() {
        datos = {
            busqueda: $('#filtro_busqueda').val(),
            categoria: $('#filtro_categoria').val(),
        };
        get_jquery('{{ url('bodega_productos/listar_reporte') }}', datos, function(retorno) {
            $('#div_listado').html(retorno);
        }, 'div_listado');
    }

    function upload_productos() {
        datos = {}
        get_jquery('{{ url('bodega_productos/upload_productos') }}', datos, function(retorno) {
            modal_view('modal_upload_productos', retorno, '<i class="fa fa-fw fa-plus"></i> Importar Productos',
                true, false, '{{ isPC() ? '75%' : '' }}',
                function() {});
        })
    }
</script>
