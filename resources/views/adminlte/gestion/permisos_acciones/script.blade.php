<script>
    $('#vista_actual').val('permisos_acciones');
    buscar_listado_permisos();

    function buscar_listado_permisos() {
        datos = {};
        get_jquery('{{ url('permisos_acciones/buscar_listado_permisos') }}', datos, function(retorno) {
            $('#div_listado').html(retorno);
        });
    }

    function store_permiso() {
        datos = {
            _token: '{{ csrf_token() }}',
            accion: $('#new_accion').val(),
            usuario: $('#new_usuario').val(),
        };
        if (datos['accion'] != '' && datos['usuario'] != '')
            post_jquery_m('{{ url('permisos_acciones/store_permiso') }}', datos, function(retorno) {
                buscar_listado_permisos();
            });
    }

    function update_permiso(id) {
        datos = {
            _token: '{{ csrf_token() }}',
            id: id,
            accion: $('#edit_accion_' + id).val(),
            usuario: $('#edit_usuario_' + id).val(),
        };
        if (datos['accion'] != '' && datos['usuario'] != '')
            post_jquery_m('{{ url('permisos_acciones/update_permiso') }}', datos, function(retorno) {
                //buscar_listado_cosechadores();
            });
    }

    function desactivar_permiso(id, estado) {
        texto = estado == 1 ? 'DESACTIVAR' : 'ACTIVAR';
        modal_quest('modal-quest_desactivar_permiso',
            '<div class="alert alert-info text-center">¿Desea <strong>' + texto + '</strong> el permiso?</div>',
            '<i class="fa fa-fw fa-exclamation-triangle"></i> Mensaje de confirmación', true, false, '',
            function() {
                datos = {
                    _token: '{{ csrf_token() }}',
                    id: id,
                };
                post_jquery_m('{{ url('permisos_acciones/desactivar_permiso') }}', datos, function(retorno) {
                    buscar_listado_permisos();
                    cerrar_modals();
                });
            });
    }
</script>
