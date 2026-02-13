<script>
    $('#vista_actual').val('ingreso_proyecciones');

    function listar_ingreso_proyecciones() {
        datos = {
            planta: $('#filtro_predeterminado_planta').val(),
            variedad: $('#filtro_predeterminado_variedad').val(),
            anno: $('#filtro_predeterminado_anno').val(),
        };
        if (datos['variedad'] != 'T') {
            get_jquery('{{url('ingreso_proyecciones/listar_ingreso_proyecciones')}}', datos, function (retorno) {
                $('#div_listado_proyecciones').html(retorno);
            });
        }
    }

    function copiar_semanas() {
        datos = {
            _token: '{{csrf_token()}}',
            planta: $('#filtro_predeterminado_planta').val(),
            variedad: $('#filtro_predeterminado_variedad').val(),
            anno: $('#filtro_predeterminado_anno').val(),
        };
        if (datos['variedad'] != 'T') {
            post_jquery('{{url('ingreso_proyecciones/copiar_semanas')}}', datos, function () {
                listar_ingreso_proyecciones();
                cerrar_modals();
            });
        }
    }

    function generar_semanas() {
        datos = {
            _token: '{{csrf_token()}}',
            planta: $('#filtro_predeterminado_planta').val(),
            variedad: $('#filtro_predeterminado_variedad').val(),
            anno: $('#filtro_predeterminado_anno').val(),
        };
        if (datos['variedad'] != 'T') {
            post_jquery('{{url('ingreso_proyecciones/generar_semanas')}}', datos, function () {
                listar_ingreso_proyecciones();
                cerrar_modals();
            });
        }
    }
</script>