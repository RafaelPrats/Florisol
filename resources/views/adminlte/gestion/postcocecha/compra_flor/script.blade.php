<script>
    $('#vista_actual').val('recepcion');
    $('#planta_filtro').select2();
    $('#variedad_filtro').select2();
    listar_reporte();

    function listar_reporte() {
        datos = {
            fecha: $('#fecha_filtro').val(),
            planta: $('#planta_filtro').val(),
            variedad: $('#variedad_filtro').val(),
        };
        if (datos['fecha'] >= $('#fecha_filtro').prop('min'))
            get_jquery('{{ url('compra_flor/listar_reporte') }}', datos, function(retorno) {
                $('#div_listar_reporte').html(retorno);
            });
    }

    function importar_compras() {
        datos = {}
        get_jquery('{{ url('compra_flor/importar_compras') }}', datos, function(retorno) {
            modal_view('modal_importar_compras', retorno, '<i class="fa fa-fw fa-plus"></i> Importar Compras',
                true, false, '{{ isPC() ? '95%' : '' }}',
                function() {});
        })
    }
</script>
