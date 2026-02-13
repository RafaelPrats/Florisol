<script>
    $('#vista_actual').val('despachadores');
    buscar_listado_despachadores();

    function buscar_listado_despachadores() {
        datos = {};
        get_jquery('{{ url('despachadores/buscar_listado_despachadores') }}', datos, function(retorno) {
            $('#div_listado').html(retorno);
        });
    }

    function store_despachador() {
        datos = {
            _token: '{{ csrf_token() }}',
            nombre: $('#new_nombre').val(),
        };
        if (datos['nombre'] != '')
            post_jquery_m('{{ url('despachadores/store_despachador') }}', datos, function(retorno) {
                buscar_listado_despachadores();
            });
    }

    function update_despachador(id) {
        datos = {
            _token: '{{ csrf_token() }}',
            id: id,
            nombre: $('#edit_nombre_' + id).val(),
        };
        if (datos['nombre'] != '')
            post_jquery_m('{{ url('despachadores/update_despachador') }}', datos, function(retorno) {
                //buscar_listado_despachadores();
            });
    }

    function desactivar_despachador(id, estado) {
        texto = estado == 1 ? 'DESACTIVAR' : 'ACTIVAR';
        modal_quest('modal-quest_desactivar_despachador',
            '<div class="alert alert-info text-center">¿Desea <strong>' + texto + '</strong> el despachador?</div>',
            '<i class="fa fa-fw fa-exclamation-triangle"></i> Mensaje de confirmación', true, false, '',
            function() {
                datos = {
                    _token: '{{ csrf_token() }}',
                    id: id,
                };
                post_jquery_m('{{ url('despachadores/desactivar_despachador') }}', datos, function(retorno) {
                    buscar_listado_despachadores();
                    cerrar_modals();
                });
            });
    }
</script>
