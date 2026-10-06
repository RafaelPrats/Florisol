<script>
    $('#vista_actual').val('kardex');
    $('#planta_filtro').select2();
    $('#variedad_filtro').select2();
    listar_reporte();

    function listar_reporte() {
        datos = {
            planta: $('#planta_filtro').val(),
            variedad: $('#variedad_filtro').val(),
            bodega: $('#bodega_filtro').val(),
            desde: $('#desde_filtro').val(),
            hasta: $('#hasta_filtro').val(),
        };
        if (datos['variedad'] == '') {
            $('#div_listado').html(
                '<div class="kardex-empty-state">' +
                '<span class="kardex-empty-icon"><i class="fa fa-search"></i></span>' +
                '<strong>Elige una variedad para consultar su Kardex</strong>' +
                '<span>Los movimientos aparecerán aquí con su saldo acumulado.</span>' +
                '</div>'
            );
            return;
        }

        if (datos['desde'] > datos['hasta']) {
            alert('La fecha inicial no puede ser posterior a la fecha final.');
            return;
        }

        get_jquery('{{ url('kardex/listar_reporte') }}', datos, function(retorno) {
            $('#div_listado').html(retorno);
        });
    }

    function exportar_reporte() {
        if ($('#variedad_filtro').val() == '') {
            alert('Selecciona una variedad antes de exportar el Kardex.');
            return;
        }
        if ($('#desde_filtro').val() > $('#hasta_filtro').val()) {
            alert('La fecha inicial no puede ser posterior a la fecha final.');
            return;
        }
        $.LoadingOverlay('show');
        window.open('{{ url('kardex/exportar_reporte') }}?planta=' + $("#planta_filtro").val() +
            '&variedad=' + $("#variedad_filtro").val() +
            '&bodega=' + $("#bodega_filtro").val() +
            '&desde=' + $("#desde_filtro").val() +
            '&hasta=' + $("#hasta_filtro").val(), '_blank');
        $.LoadingOverlay('hide');
    }
</script>
