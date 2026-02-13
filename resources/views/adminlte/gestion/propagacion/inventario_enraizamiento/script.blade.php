<script>
    $('#vista_actual').val('inventario_enraizamiento');

    listar_inventario_enraizamiento();

    function listar_inventario_enraizamiento() {
        datos = {
            desde: $('#filtro_predeterminado_desde').val(),
            hasta: $('#filtro_predeterminado_hasta').val(),
        };
        get_jquery('{{url('inventario_enraizamiento/listar_inventario_enraizamiento')}}', datos, function (retorno) {
            $('#listado_inventario').html(retorno);
        });
    }
</script>