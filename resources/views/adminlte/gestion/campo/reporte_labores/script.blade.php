<script>
    $('#vista_actual').val('reporte_labores');

    function listar_reporte() {
        datos = {
            labor: $('#filtro_labor').val(),
            semana: $('#filtro_semana').val(),
            sector: $('#filtro_sector').val(),
        };
        if (datos['labor'] != '') {
            get_jquery('{{ url('reporte_labores/listar_reporte') }}', datos, function(retorno) {
                $('#div_listado_ciclos').html(retorno);
                estructura_tabla('table_labores', false, false);
                $('#table_labores_filter').addClass('hidden');
            });
        }
    }

    function seleccionar_tipo_labor() {
        datos = {
            _token: '{{ csrf_token() }}',
            tipo: $('#filtro_tipo_labor').val(),
        };
        if (datos['tipo'] != '')
            $.post('{{ url('ingreso_labores/seleccionar_tipo_labor') }}', datos, function(retorno) {
                $('#filtro_labor').html('');
                $('#filtro_labor').append('<option value="">Seleccione...</option>');
                for (i = 0; i < retorno.labores.length; i++) {
                    $('#filtro_labor').append('<option value="' + retorno.labores[i].id_aplicacion_matriz + '">' +
                        retorno.labores[i].nombre + '</option>');
                }
            }, 'json');
    }
</script>
