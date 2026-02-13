<script>
    $('#vista_actual').val('cosechadores');
    buscar_listado_cosechadores();

    function buscar_listado_cosechadores() {
        datos = {};
        get_jquery('{{ url('cosechadores/buscar_listado_cosechadores') }}', datos, function(retorno) {
            $('#div_listado').html(retorno);
        });
    }

    function store_cosechador() {
        datos = {
            _token: '{{ csrf_token() }}',
            nombre: $('#new_nombre').val(),
        };
        if (datos['nombre'] != '')
            post_jquery_m('{{ url('cosechadores/store_cosechador') }}', datos, function(retorno) {
                buscar_listado_cosechadores();
            });
    }

    function update_cosechador(id) {
        datos = {
            _token: '{{ csrf_token() }}',
            id: id,
            nombre: $('#edit_nombre_' + id).val(),
        };
        if (datos['nombre'] != '')
            post_jquery_m('{{ url('cosechadores/update_cosechador') }}', datos, function(retorno) {
                //buscar_listado_cosechadores();
            });
    }

    function desactivar_cosechador(id, estado) {
        texto = estado == 1 ? 'DESACTIVAR' : 'ACTIVAR';
        modal_quest('modal-quest_desactivar_cosechador',
            '<div class="alert alert-info text-center">¿Desea <strong>' + texto + '</strong> el cosechador?</div>',
            '<i class="fa fa-fw fa-exclamation-triangle"></i> Mensaje de confirmación', true, false, '',
            function() {
                datos = {
                    _token: '{{ csrf_token() }}',
                    id: id,
                };
                post_jquery_m('{{ url('cosechadores/desactivar_cosechador') }}', datos, function(retorno) {
                    buscar_listado_cosechadores();
                    cerrar_modals();
                });
            });
    }
</script>
