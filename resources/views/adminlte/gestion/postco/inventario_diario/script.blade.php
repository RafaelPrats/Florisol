<script>
    $('#vista_actual').val('inventario_diario');
    $('#planta_filtro').select2();
    $('#variedad_filtro').select2();
    listar_reporte();

    function obtener_filtros_reporte() {
        return {
            planta: $('#planta_filtro').val(),
            variedad: $('#variedad_filtro').val(),
            bodega: $('#bodega_filtro').val(),
            desde: $('#desde_filtro').val(),
            hasta: $('#hasta_filtro').val(),
        };
    }

    function validar_periodo_reporte(filtros) {
        if (!filtros.desde || !filtros.hasta || filtros.desde > filtros.hasta) {
            alert('Selecciona un período válido: la fecha inicial no puede ser posterior a la fecha final.');
            return false;
        }
        return true;
    }

    function listar_reporte() {
        var datos = obtener_filtros_reporte();
        if (!validar_periodo_reporte(datos)) {
            return;
        }
        get_jquery('{{ url('inventario_diario/listar_reporte') }}', datos, function(retorno) {
            $('#div_listado').html(retorno);
        });
    }

    function exportar_reporte() {
        var datos = obtener_filtros_reporte();
        if (!validar_periodo_reporte(datos)) {
            return;
        }
        $.LoadingOverlay('show');
        window.open('{{ url('inventario_diario/exportar_reporte') }}?' + $.param(datos), '_blank');
        $.LoadingOverlay('hide');
    }
</script>
