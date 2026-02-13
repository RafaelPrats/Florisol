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
        get_jquery('{{ url('recepcion/listar_reporte') }}', datos, function(retorno) {
            $('#div_listar_reporte').html(retorno);
        });
    }

    function modal_scan() {
        datos = {}
        get_jquery('{{ url('recepcion/modal_scan') }}', datos, function(retorno) {
            modal_view('modal_modal_scan', retorno, '<i class="fa fa-fw fa-plus"></i> Ingreso mediante codigos de barra',
                true, false, '{{ isPC() ? '75%' : '' }}',
                function() {});
        })
    }
</script>
