<script>
    estructura_tabla('table_content_proveedores');
    $('#table_content_proveedores_filter label').addClass('hidden');
    estructura_tabla('table_content_plantas');
    $('#table_content_plantas_filter label').addClass('hidden');

    function select_planta(p) {
        proveedor = $('#proveedor_seleccionada').val();
        $('#planta_seleccionada').val(p);
        datos = {
            id_planta: p,
            id_proveedor: proveedor,
        };
        get_jquery('{{ url('plantas_variedades/select_planta') }}', datos, function(retorno) {
            $('#div_content_menus').html(retorno);
            $('.row_variedad').remove();
            $('.icon_hidden_p').addClass('hidden');
            $('#icon_planta_' + p).removeClass('hidden');
            estructura_tabla('table_content_variedades');
            $('#table_content_variedades_filter label input').addClass('input-yura_default').css('width',
                '40%');
        }, 'div_content_menus');
    }

    function select_proveedor(p) {
        planta = $('#planta_seleccionada').val();
        $('#proveedor_seleccionada').val(p);
        datos = {
            id_planta: planta,
            id_proveedor: p,
        };
        get_jquery('{{ url('plantas_variedades/select_planta') }}', datos, function(retorno) {
            $('#div_content_menus').html(retorno);
            $('.icon_hidden_proveedor').addClass('hidden');
            $('#icon_proveedor_' + p).removeClass('hidden');
            if (planta != '') {
                estructura_tabla('table_content_variedades');
                $('#table_content_variedades_filter label input').addClass('input-yura_default').css('width',
                    '40%');
            }
        }, 'div_content_menus');
    }

    /* ===================================================== */

    function form_compelto() {
        get_jquery('{{ url('plantas_variedades/form_compelto') }}', {}, function(retorno) {
            modal_view('modal_form_compelto', retorno, '<i class="fa fa-fw fa-plus"></i> Formulario completo',
                true, false, '{{ isPC() ? '95%' : '' }}',
                function() {});
        });
    }

    function add_planta() {
        $.LoadingOverlay('show');
        $.get('{{ url('plantas_variedades/add_planta') }}', {}, function(retorno) {
            modal_form('modal_add_planta', retorno, '<i class="fa fa-fw fa-plus"></i> Añadir Planta', true,
                false, '{{ isPC() ? '65%' : '' }}',
                function() {
                    store_planta();
                });
        });
        $.LoadingOverlay('hide');
    }

    function store_planta() {
        if ($('#form_add_planta').valid()) {
            datos = {
                _token: '{{ csrf_token() }}',
                nombre: $('#nombre').val(),
                tarifa: $("#tarifa").val(),
                nandina: $("#nandina").val(),
                siglas: $("#siglas").val(),
            };
            post_jquery_m('{{ url('plantas_variedades/store_planta') }}', datos, function() {
                cerrar_modals();
                location.reload();
            });
        }
    }

    function add_variedad() {
        $.LoadingOverlay('show');
        $.get('{{ url('plantas_variedades/add_variedad') }}', {}, function(retorno) {
            modal_form('modal_add_variedad', retorno, '<i class="fa fa-fw fa-plus"></i> Añadir Variedad', true,
                false, '{{ isPC() ? '75%' : '' }}',
                function() {
                    store_variedad();
                });
        });
        $.LoadingOverlay('hide');
    }

    function store_variedad() {
        if ($('#form_add_variedad').valid()) {
            $.LoadingOverlay('show');
            datos = {
                _token: '{{ csrf_token() }}',
                nombre: $('#nombre').val(),
                id_planta: $('#id_planta').val(),
                unidad_medida: $('#unidad_medida').val(),
                minimo_apertura: $('#minimo_apertura').val(),
                maximo_apertura: $('#maximo_apertura').val(),
                estandar: $('#estandar').val(),
                siglas: $('#siglas').val(),
                color: $('#color').val(),
                tipo: $('#tipo').val(),
                tallos_x_ramo_estandar: $('#tallos_x_ramo_estandar').val(),
                tallos_x_malla: $('#tallos_x_malla').val(),
                saldo_inicial: $('#saldo_inicial').val(),
            };
            post_jquery('{{ url('plantas_variedades/store_variedad') }}', datos, function() {
                cerrar_modals();
                location.reload();
            });
            $.LoadingOverlay('hide');
        }
    }

    function add_proveedor() {
        get_jquery('{{ url('plantas_variedades/add_proveedor') }}', {}, function(retorno) {
            modal_form('modal_add_proveedor', retorno, '<i class="fa fa-fw fa-plus"></i> Añadir Proveedor',
                true, false, '{{ isPC() ? '65%' : '' }}',
                function() {
                    store_proveedor();
                });
        });
    }

    function store_proveedor() {
        if ($('#form_add_proveedor').valid()) {
            datos = {
                _token: '{{ csrf_token() }}',
                nombre: $('#nombre').val(),
            };
            post_jquery_m('{{ url('plantas_variedades/store_proveedor') }}', datos, function() {
                cerrar_modals();
                location.reload();
            });
        }
    }

    function edit_proveedor(p) {
        datos = {
            id_proveedor: p,
        };
        get_jquery('{{ url('plantas_variedades/edit_proveedor') }}', datos, function(retorno) {
            modal_form('modal_edit_proveedor', retorno, '<i class="fa fa-fw fa-plus"></i> Editar Proveedor',
                true, false, '{{ isPC() ? '65%' : '' }}',
                function() {
                    update_proveedor();
                });
        });
    }

    function update_proveedor() {
        if ($('#form_add_proveedor').valid()) {
            datos = {
                _token: '{{ csrf_token() }}',
                nombre: $('#nombre').val(),
                id_proveedor: $('#id_proveedor').val(),
            };
            post_jquery_m('{{ url('plantas_variedades/update_proveedor') }}', datos, function() {
                cerrar_modals();
                location.reload();
            });
        }
    }

    /* ===================================================== */

    function edit_planta(p) {
        $.LoadingOverlay('show');
        datos = {
            id_planta: p,
        };
        $.get('{{ url('plantas_variedades/edit_planta') }}', datos, function(retorno) {
            modal_form('modal_edit_planta', retorno, '<i class="fa fa-fw fa-plus"></i> Editar Planta', true,
                false, '{{ isPC() ? '65%' : '' }}',
                function() {
                    update_planta();
                });
        });
        $.LoadingOverlay('hide');
    }

    function update_planta() {
        if ($('#form_add_planta').valid()) {
            $.LoadingOverlay('show');
            datos = {
                _token: '{{ csrf_token() }}',
                nombre: $('#nombre').val(),
                id_planta: $('#id_planta').val(),
                tarifa: $("#tarifa").val(),
                nandina: $("#nandina").val(),
                siglas: $("#siglas").val(),
                tipo: $("#tipo").val(),
            };
            post_jquery('{{ url('plantas_variedades/update_planta') }}', datos, function() {
                cerrar_modals();
                location.reload();
            });
            $.LoadingOverlay('hide');
        }
    }

    function cambiar_estado_planta(p, estado) {
        mensaje = {
            title: estado == 1 ? '<i class="fa fa-fw fa-trash"></i> Desactivar planta' :
                '<i class="fa fa-fw fa-unlock"></i> Activar planta',
            mensaje: estado == 1 ?
                '<div class="alert alert-danger text-center"><i class="fa fa-fw fa-exclamation-triangle"></i> ¿Está seguro de desactivar esta planta?</div>' :
                '<div class="alert alert-info text-center"><i class="fa fa-fw fa-exclamation-triangle"></i> ¿Está seguro de activar esta planta?</div>',
        };
        modal_quest('modal_delete_planta', mensaje['mensaje'], mensaje['title'], true, false,
            '{{ isPC() ? '25%' : '' }}',
            function() {
                datos = {
                    _token: '{{ csrf_token() }}',
                    id_planta: p,
                    estado: estado == 1 ? 0 : 1,
                };
                post_jquery('{{ url('plantas_variedades/cambiar_estado_planta') }}', datos, function() {
                    cerrar_modals();
                    location.reload();
                });
            });
    }

    function edit_variedad(v) {
        $.LoadingOverlay('show');
        datos = {
            id_variedad: v
        };
        $.get('{{ url('plantas_variedades/edit_variedad') }}', datos, function(retorno) {
            modal_form('modal_edit_variedad', retorno, '<i class="fa fa-fw fa-plus"></i> Editar Variedad', true,
                false, '{{ isPC() ? '75%' : '' }}',
                function() {
                    update_variedad();
                });
        });
        $.LoadingOverlay('hide');
    }

    function update_variedad() {
        if ($('#form_edit_variedad').valid()) {
            $.LoadingOverlay('show');
            datos = {
                _token: '{{ csrf_token() }}',
                nombre: $('#nombre').val(),
                id_planta: $('#id_planta').val(),
                id_variedad: $('#id_variedad').val(),
                siglas: $('#siglas').val(),
                color: $('#color').val(),
                tallos_x_ramo_estandar: $('#tallos_x_ramo_estandar').val(),
                tallos_x_malla: $('#tallos_x_malla').val(),
                compra_flor: $("#compra_flor").val(),
                receta: $("#receta").val(),
            };
            post_jquery('{{ url('plantas_variedades/update_variedad') }}', datos, function() {
                cerrar_modals();
                select_planta(datos['id_planta']);
            });
            $.LoadingOverlay('hide');
        }
    }

    function cambiar_estado_variedad(v, estado) {
        mensaje = {
            title: estado == 1 ? '<i class="fa fa-fw fa-trash"></i> Desactivar variedad' :
                '<i class="fa fa-fw fa-unlock"></i> Activar variedad',
            mensaje: estado == 1 ?
                '<div class="alert alert-danger text-center"><i class="fa fa-fw fa-exclamation-triangle"></i> ¿Está seguro de desactivar esta variedad?</div>' :
                '<div class="alert alert-info text-center"><i class="fa fa-fw fa-exclamation-triangle"></i> ¿Está seguro de activar esta variedad?</div>',
        };
        modal_quest('modal_delete_variedad', mensaje['mensaje'], mensaje['title'], true, false,
            '{{ isPC() ? '25%' : '' }}',
            function() {
                datos = {
                    _token: '{{ csrf_token() }}',
                    id_variedad: v,
                    estado: estado == 1 ? 0 : 1,
                };
                post_jquery('{{ url('plantas_variedades/cambiar_estado_variedad') }}', datos, function() {
                    cerrar_modals();
                    location.reload();
                });
            });
    }

    function add_precio(id_variedad) {

        $.LoadingOverlay('show');
        datos = {
            id_variedad: id_variedad
        };

        $.get('{{ url('plantas_variedades/form_precio_variedad') }}', datos, function(retorno) {
            modal_form('modal_add_precio', retorno, '<i class="fa fa-fw fa-plus"></i> Añadir PrecioController',
                true, false, '{{ isPC() ? '40%' : '' }}',
                function() {
                    store_precio();
                });
            setInterval(function() {
                $("#id_variedad").val(id_variedad);
            }, 1000)
        });
        $.LoadingOverlay('hide');
    }

    function store_precio() {

        if ($('#form_add_precio').valid()) {

            var cant_tr = $("tbody#precios tr").length;
            var arrData = [];
            for (var i = 0; i < cant_tr; i++) {
                arrData.push([$("#precio_" + (parseInt(i) + parseInt(1))).val(), $("#id_clasificacion_por_ramo_" + (
                    parseInt(i) + parseInt(1))).val(), $("#id_precio_" + (parseInt(i) + parseInt(1))).val()]);
            }
            $.LoadingOverlay('show');
            datos = {
                _token: '{{ csrf_token() }}',
                arrData: arrData,
                id_variedad: $("#id_variedad").val()
            };
            post_jquery('{{ url('plantas_variedades/store_precio') }}', datos, function() {
                cerrar_modals();
                location.reload();
            });
            $.LoadingOverlay('hide');
        }
    }

    function add_inptus_precio() {

        $.LoadingOverlay('show');

        var cant_tr = $("tbody#precios tr").length;
        datos = {
            cant_tr: cant_tr
        };
        console.log(datos);
        $.get('{{ url('plantas_variedades/add_inptus_precio_variedad') }}', datos, function(retorno) {
            $("#precios").append(retorno);

        });
        $.LoadingOverlay('hide');
    }

    function delete_inputs(cant) {
        var tr = $("tbody#precios tr#precios_" + cant);
        tr.remove(tr.cant);
    }

    function actualizar_status_precio(id_precio, estado, id_variedad) {
        $.LoadingOverlay('show');
        datos = {
            _token: '{{ csrf_token() }}',
            id_precio: id_precio,
            estado: estado,
        };
        post_jquery('{{ url('plantas_variedades/update_precio') }}', datos, function() {
            cerrar_modals();
            add_precio($("#id_variedad").val());
        });
        $.LoadingOverlay('hide');

    }

    function comprabar(id_input) {
        $("#id_precio_" + id_input).val('');
    }

    function vincular_variedad_unitaria(v) {
        $.LoadingOverlay('show');
        datos = {
            id_variedad: v
        };
        $.get('{{ url('plantas_variedades/vincular_variedad_unitaria') }}', datos, function(retorno) {
            modal_view('modal_vincular_variedad_unitaria', retorno,
                '<i class="fa fa-fw fa-plus"></i> Editar Variedad', true, false, '',
                function() {});
        });
        $.LoadingOverlay('hide');
    }

    function add_regalias(v) {
        $.LoadingOverlay('show');
        datos = {
            id_variedad: v
        };
        $.get('{{ url('plantas_variedades/add_regalias') }}', datos, function(retorno) {
            modal_view('modal_add_regalias', retorno, '<i class="fa fa-fw fa-plus"></i> Regalías', true, false,
                '',
                function() {});
        });
        $.LoadingOverlay('hide');
    }

    function importar_variedades() {
        datos = {}
        get_jquery('{{ url('plantas_variedades/importar_variedades') }}', datos, function(retorno) {
            modal_view('modal_importar_variedades', retorno,
                '<i class="fa fa-fw fa-plus"></i> Importar Variedades',
                true, false, '{{ isPC() ? '90%' : '' }}',
                function() {});
        })
    }

    function importar_recetas() {
        datos = {}
        get_jquery('{{ url('plantas_variedades/importar_recetas') }}', datos, function(retorno) {
            modal_view('modal_importar_recetas', retorno,
                '<i class="fa fa-fw fa-plus"></i> Importar Recetas',
                true, false, '{{ isPC() ? '90%' : '' }}',
                function() {});
        })
    }
</script>
