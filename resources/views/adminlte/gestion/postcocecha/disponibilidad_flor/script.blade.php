<script>
    $('#variedad_filtro').select2();

    function listar_reporte() {
        datos = {
            planta: $('#planta_filtro').val(),
            variedad: $('#variedad_filtro').val(),
            hasta: $('#hasta_filtro').val(),
        };
        get_jquery('{{ url('disponibilidad_flor/listar_reporte') }}', datos, function(retorno) {
            $('#div_listado').html(retorno);
        });
    }

    function detalle_ventas(variedad, total) {
        datos = {
            _token: '{{ csrf_token() }}',
            variedad: variedad,
            total: total,
            hasta: $('#hasta_filtro').val(),
        }
        get_jquery('{{ url('disponibilidad_flor/detalle_ventas') }}', datos, function(retorno) {
            modal_view('modal_detalle_ventas', retorno,
                '<i class="fa fa-fw fa-plus"></i> Detalle de los Pedidos',
                true, false, '{{ isPC() ? '90%' : '' }}',
                function() {});
        });
    }

    function exportar_listado(tipo, negativas) {
        $.LoadingOverlay('show');
        window.open('{{ url('disponibilidad_flor/exportar_listado') }}?planta=' + $("#planta_filtro").val() +
            '&variedad=' + $("#variedad_filtro").val() +
            '&hasta=' + $("#hasta_filtro").val(), '_blank');
        $.LoadingOverlay('hide');
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
