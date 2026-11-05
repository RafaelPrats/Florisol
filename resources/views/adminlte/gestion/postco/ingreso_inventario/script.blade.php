<script>
    $('#vista_actual').val('ingreso_inventario');
    $('#planta_filtro, #variedad_filtro, #documento_filtro').select2({ width: '100%' });
    listar_reporte();

    function listar_reporte() {
        var datos = {
            planta: $('#planta_filtro').val(),
            variedad: $('#variedad_filtro').val(),
            fecha: $('#fecha_filtro').val(),
            documento: $('#documento_filtro').val(),
            bodega: $('#bodega_filtro').val(),
        };
        get_jquery('{{ url('ingreso_inventario/listar_reporte') }}', datos, function(retorno) {
            $('#div_listado').html(retorno);
        });
    }

    function admin_bodegas() {
        var datos = {};
        get_jquery('{{ url('ingreso_inventario/admin_bodegas') }}', datos, function(retorno) {
            modal_view('modal_admin_bodegas', retorno,
                '<i class="fa fa-cogs"></i> Bodegas por segmento',
                true, false, '{{ isPC() ? '70%' : '100%' }}',
                function() {});
        });
    }

    function modal_documentos_api_pendientes() {
        get_jquery('{{ url('ingreso_inventario/documentos_api_pendientes') }}', {}, function(retorno) {
            modal_view('modal_documentos_api_pendientes', retorno,
                '<i class="fa fa-trash-o"></i> Documentos API pendientes',
                true, false, '{{ isPC() ? '75%' : '100%' }}',
                function() {});
        });
    }

    function eliminar_documento_api_pendiente(id, button) {
        var documento = $(button).closest('tr').children().first().text().trim();
        var eliminaraEncabezado = $(button).text().indexOf('Eliminar documento') >= 0;
        var alcance = eliminaraEncabezado
            ? 'Se eliminará el encabezado junto con todos sus detalles pendientes.'
            : 'Se eliminarán únicamente los detalles pendientes que no tengan ingresos ni movimientos asociados. Los detalles recibidos y el encabezado se conservarán.';
        var texto = '<div class="alert alert-danger text-center">' +
            '<h4>¿Eliminar el documento <strong>' + $('<div>').text(documento).html() + '</strong>?</h4>' +
            '<p>' + alcance + ' Esta acción no se puede deshacer.</p>' +
            '</div>';

        modal_quest('confirmar_eliminar_documento_api', texto, 'Eliminar documento pendiente', true, false, '40%',
            function() {
                post_jquery_m('{{ url('ingreso_inventario/eliminar_documento_api_pendiente') }}', {
                    _token: '{{ csrf_token() }}',
                    id_api_store_cajas: id
                }, function() {
                    $('#documento_filtro option[value="' + id + '"]').remove();
                    if ($('#documento_filtro').val() == id) {
                        $('#documento_filtro').val('').trigger('change');
                    }
                    listar_reporte();
                    get_jquery('{{ url('ingreso_inventario/listar_documentos_api_pendientes') }}', {},
                        function(retorno) {
                            $('#div_documentos_api_pendientes').html(retorno);
                        });
                });
            });
    }
</script>
