<script>
    $('#vista_actual').val('clasificaciones');
    listar_ramos();
    
    function listar_ramos() {
        get_jquery('{{url('clasificaciones/listar_ramos')}}', {}, function (retorno) {
            $('#tab_ramos').html(retorno);
        });
    }

    function listar_presentaciones() {
        get_jquery('{{url('clasificaciones/listar_presentaciones')}}', {}, function (retorno) {
            $('#tab_presentaciones').html(retorno);
        });
    }

    function listar_cajas() {
        get_jquery('{{url('clasificaciones/listar_presentaciones')}}', {}, function (retorno) {
            $('#tab_presentaciones').html(retorno);
        });
    }

    function store_ramo() {
        datos = {
            _token: '{{csrf_token()}}',
            nombre: $('#new_nombre_r').val(),
            unidad_medida: $('#new_unidad_medida_r').val(),
            estandar: 0,
        };
        if (datos['nombre'] != '') {
            post_jquery_m('{{url('clasificaciones/store_ramo')}}', datos, function () {
                listar_ramos();
            });
        }
    }

    function store_presentacion() {
        datos = {
            _token: '{{csrf_token()}}',
            nombre: $('#new_nombre_e').val(),
        };
        if (datos['nombre'] != '') {
            post_jquery_m('{{url('clasificaciones/store_presentacion')}}', datos, function () {
                listar_presentaciones();
            });
        }
    }

    function update_ramo(id) {
        datos = {
            _token: '{{csrf_token()}}',
            id: id,
            nombre: $('#edit_nombre_r_' + id).val(),
            unidad_medida: $('#edit_unidad_medida_r_' + id).val(),
            estandar: 0,
        };
        if (datos['nombre'] != '')
            post_jquery_m('{{url('clasificaciones/update_ramo')}}', datos, function () {
                listar_ramos();
            });
    }

    function update_presentacion(id) {
        datos = {
            _token: '{{csrf_token()}}',
            id: id,
            nombre: $('#edit_nombre_e_' + id).val(),
        };
        if (datos['nombre'] != '')
            post_jquery_m('{{url('clasificaciones/update_presentacion')}}', datos, function () {
                listar_presentaciones();
            });
    }

    function cambiar_estado_ramo(id) {
        datos = {
            _token: '{{csrf_token()}}',
            id: id,
        };
        post_jquery_m('{{url('clasificaciones/cambiar_estado_ramo')}}', datos, function () {
            listar_ramos();
        });
    }

    function cambiar_estado_presentacion(id) {
        datos = {
            _token: '{{csrf_token()}}',
            id: id,
        };
        post_jquery_m('{{url('clasificaciones/cambiar_estado_presentacion')}}', datos, function () {
            listar_presentaciones();
        });
    }

    function listar_cajas() {
        get_jquery('{{url('clasificaciones/listar_cajas')}}', {}, function (retorno) {
            $('#tab_cajas').html(retorno);
        });
    }

    function store_caja() {
        datos = {
            _token: '{{csrf_token()}}',
            nombre: $('#new_nombre_c').val(),
            factor_conversion: $('#new_fa_c').val(),
            peso: $('#new_p_c').val(),
        };
        if (datos['nombre'] != '' && datos['factor_conversion'] != '') {
            post_jquery_m('{{url('clasificaciones/store_caja')}}', datos, function () {
                listar_cajas()
            });
        }
    }

    function update_caja(id) {
        datos = {
            _token: '{{csrf_token()}}',
            id: id,
            nombre: $('#edit_nombre_c_'+id).val(),
            factor_conversion: $('#edit_fa_c_'+id).val(),
            peso: $('#edit_peso_c_'+id).val(),
        };
        if (datos['nombre'] != '')
            post_jquery_m('{{url('clasificaciones/update_caja')}}', datos, function () {
                listar_cajas()
            });
    }

    function cambiar_estado_caja(id) {
        datos = {
            _token: '{{csrf_token()}}',
            id
        };
        post_jquery_m('{{url('clasificaciones/cambiar_estado_caja')}}', datos, function () {
            listar_cajas()
        });
    }

</script>
