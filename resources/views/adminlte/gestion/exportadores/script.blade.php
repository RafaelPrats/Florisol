<script>

listar_exportadores()

function listar_exportadores() {
    get_jquery('{{url('exportador/listar')}}', {}, function (retorno) {
        $('#div_exportadores').html(retorno);
    },'div_exportadores')
}

function store_exportador() {
    datos = {
        _token: '{{csrf_token()}}',
        nombre: $('#nombre').val(),
        identificacion: $('#identificacion').val(),
        codigo_externo: $('#codigo_externo').val()
    }

    if (datos['nombre'] != '' && datos['identificacion'] != '') {
        post_jquery_m('{{url('exportador/store')}}', datos, function () {
            listar_exportadores()
            $('#nombre,#identificacion,#codigo_externo').val('')
        })
    }
}

function update_exportador(id) {
    datos = {
        _token: '{{csrf_token()}}',
        id,
        nombre: $('#edit_nombre_'+id).val(),
        identificacion: $('#edit_identificacion_'+id).val(),
        codigo_externo: $('#edit_codigo_externo_'+id).val()
    };
    if (datos['nombre'] != '' && datos['identificacion'] != '')
        post_jquery_m('{{url('exportador/update')}}', datos, function () {
            listar_exportadores()
            $('#nombre,#identificacion,#codigo_externo').val('')
        });
}

function cambiar_estado_exportador(id) {
    datos = {
        _token: '{{csrf_token()}}',
        id
    }

    post_jquery_m('{{url('exportador/cambiar_estado')}}', datos, function () {
        listar_exportadores()
    })
}

</script>