<script>
    $('#vista_actual').val('proy_perennes');

    function listar_proyecciones_perennes() {
        datos = {
            planta: $('#filtro_predeterminado_planta').val(),
            variedad: $('#filtro_predeterminado_variedad').val(),
            anno: $('#filtro_predeterminado_anno').val(),
        };
        if (datos['variedad'] != 'T') {
            get_jquery('{{ url('proy_perennes/listar_proyecciones') }}', datos, function(retorno) {
                $('#div_listado_proyecciones').html(retorno);
            });
        }
    }

    function copiar_semanas() {
        datos = {
            _token: '{{ csrf_token() }}',
            planta: $('#filtro_predeterminado_planta').val(),
            variedad: $('#filtro_predeterminado_variedad').val(),
            anno: $('#filtro_predeterminado_anno').val(),
        };
        if (datos['variedad'] != 'T') {
            post_jquery('{{ url('proy_perennes/copiar_semanas') }}', datos, function() {
                listar_proyecciones_perennes();
                cerrar_modals();
            });
        }
    }

    function generar_semanas() {
        datos = {
            _token: '{{ csrf_token() }}',
            planta: $('#filtro_predeterminado_planta').val(),
            variedad: $('#filtro_predeterminado_variedad').val(),
            anno: $('#filtro_predeterminado_anno').val(),
        };
        if (datos['variedad'] != 'T') {
            post_jquery('{{ url('ingreso_proyecciones/generar_semanas') }}', datos, function() {
                listar_proyecciones_perennes();
                cerrar_modals();
            });
        }
    }
</script>
