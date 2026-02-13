<script>
    $('#vista_actual').val('sectores_modulos');
    listar_sectores_modulos();

    function listar_sectores_modulos() {
        datos = {
            finca_actual: $('#fincas_propias').val(),
        };
        get_jquery('{{ url('sectores_modulos/listar_sectores_modulos') }}', datos, function(retorno) {
            $('#tab_sectores').html(retorno);
        });
    }

    function select_sector(s) {
        datos = {
            id_sector: s
        };
        get_jquery('{{ url('sectores_modulos/select_sector') }}', datos, function(retorno) {
            $('#div_content_modulos').html(retorno);
            $('.row_lote').remove();
            $('.icon_hidden_s').addClass('hidden');
            $('#icon_sector_' + s).removeClass('hidden');
            //estructura_tabla('table_content_modulos', false, true);
        });
    }

    function select_modulo(m) {
        $.LoadingOverlay('show');
        datos = {
            id_modulo: m
        };
        $.get('{{ url('sectores_modulos/select_modulo') }}', datos, function(retorno) {
            $('#div_content_lotes').html(retorno);
            $('.icon_hidden_m').addClass('hidden');
            $('#icon_modulo_' + m).removeClass('hidden');
        }).always(function() {
            $.LoadingOverlay('hide');
        });
        $.LoadingOverlay('hide');
    }

    function listar_modulos_x_sector(s) {
        $.LoadingOverlay('show');
        datos = {
            id_sector: s,
        };
        $.get('{{ url('sectores_modulos/listar_modulos_x_sector') }}', datos, function(retorno) {
            $('#div_input_modulos').html(retorno);
        }).always(function() {
            $.LoadingOverlay('hide');
        });
        $.LoadingOverlay('hide');
    }

    /* ===================================================== */

    function add_sector() {
        $.LoadingOverlay('show');
        $.get('{{ url('sectores_modulos/add_sector') }}', {}, function(retorno) {
            modal_form('modal_add_sector', retorno, '<i class="fa fa-fw fa-plus"></i> Añadir Sector', true,
                false, '{{ isPC() ? '75%' : '' }}',
                function() {
                    store_sector();
                });
        });
        $.LoadingOverlay('hide');
    }

    function store_sector() {
        if ($('#form_add_sector').valid()) {
            datos = {
                _token: '{{ csrf_token() }}',
                nombre: $('#nombre').val().toUpperCase(),
                interno: $('#interno').val(),
                descripcion: $('#descripcion').val(),
                finca_actual: $('#fincas_propias').val(),
            };
            post_jquery('{{ url('sectores_modulos/store_sector') }}', datos, function() {
                cerrar_modals();
                listar_sectores_modulos();
            });
        }
    }

    function add_modulo() {
        datos = {
            finca_actual: $('#fincas_propias').val()
        }
        $.LoadingOverlay('show');
        $.get('{{ url('sectores_modulos/add_modulo') }}', datos, function(retorno) {
            modal_form('modal_add_modulo', retorno, '<i class="fa fa-fw fa-plus"></i> Añadir Módulo', true,
                false, '{{ isPC() ? '75%' : '' }}',
                function() {
                    store_modulo();
                });
        });
        $.LoadingOverlay('hide');
    }

    function store_modulo() {
        if ($('#form_add_modulo').valid()) {
            datos = {
                _token: '{{ csrf_token() }}',
                nombre: $('#nombre').val().toUpperCase(),
                id_sector: $('#id_sector').val(),
                area: $('#area').val(),
                descripcion: $('#descripcion').val(),
            };
            post_jquery('{{ url('sectores_modulos/store_modulo') }}', datos, function() {
                cerrar_modals();
                listar_sectores_modulos();
                select_sector(datos['id_sector']);
            });
        }
    }

    function add_lote() {
        $.LoadingOverlay('show');
        $.get('{{ url('sectores_modulos/add_lote') }}', {}, function(retorno) {
            modal_form('modal_add_lote', retorno, '<i class="fa fa-fw fa-plus"></i> Añadir Lote', true, false,
                '{{ isPC() ? '55%' : '' }}',
                function() {
                    store_lote();
                });
        });
        $.LoadingOverlay('hide');
    }

    function store_lote() {
        if ($('#form_add_lote').valid()) {
            $.LoadingOverlay('show');
            datos = {
                _token: '{{ csrf_token() }}',
                nombre: $('#nombre').val().toUpperCase(),
                id_modulo: $('#id_modulo').val(),
                descripcion: $('#descripcion').val(),
                area: $('#area').val(),
            };
            post_jquery('{{ url('sectores_modulos/store_lote') }}', datos, function() {
                cerrar_modals();
                listar_sectores_modulos();
            });
            $.LoadingOverlay('hide');
        }
    }

    /* ===================================================== */

    function edit_sector(g) {
        $.LoadingOverlay('show');
        datos = {
            id_sector: g
        };
        $.get('{{ url('sectores_modulos/edit_sector') }}', datos, function(retorno) {
            modal_form('modal_edit_sector', retorno, '<i class="fa fa-fw fa-plus"></i> Editar Sector', true,
                false, '{{ isPC() ? '75%' : '' }}',
                function() {
                    update_sector();
                });
        });
        $.LoadingOverlay('hide');
    }

    function update_sector() {
        if ($('#form_edit_sector').valid()) {
            datos = {
                _token: '{{ csrf_token() }}',
                nombre: $('#nombre').val().toUpperCase(),
                id_sector: $('#id_sector').val(),
                interno: $('#interno').val(),
                descripcion: $('#descripcion').val(),
                finca_actual: $('#finca').val(),
            };
            post_jquery('{{ url('sectores_modulos/update_sector') }}', datos, function() {
                cerrar_modals();
                listar_sectores_modulos();
                select_sector(datos['id_sector']);
            });
        }
    }

    function cambiar_estado_sector(s, estado) {
        mensaje = {
            title: estado == 1 ? '<i class="fa fa-fw fa-trash"></i> Desactivar sector' :
                '<i class="fa fa-fw fa-unlock"></i> Activar sector',
            mensaje: estado == 1 ?
                '<div class="alert alert-danger text-center"><i class="fa fa-fw fa-exclamation-triangle"></i> ¿Está seguro de desactivar este sector?</div>' :
                '<div class="alert alert-info text-center"><i class="fa fa-fw fa-exclamation-triangle"></i> ¿Está seguro de activar este sector?</div>',
        };
        modal_quest('modal_delete_sector', mensaje['mensaje'], mensaje['title'], true, false,
            '{{ isPC() ? '25%' : '' }}',
            function() {
                datos = {
                    _token: '{{ csrf_token() }}',
                    id_sector: s,
                    estado: estado == 1 ? 0 : 1,
                };
                post_jquery('{{ url('sectores_modulos/cambiar_estado_sector') }}', datos, function() {
                    cerrar_modals();
                    listar_sectores_modulos();
                    select_sector(s);
                });
            });
    }

    function edit_modulo(m) {
        $.LoadingOverlay('show');
        datos = {
            id_modulo: m,
            finca_actual: $('#fincas_propias').val()
        };
        $.get('{{ url('sectores_modulos/edit_modulo') }}', datos, function(retorno) {
            modal_form('modal_edit_modulo', retorno, '<i class="fa fa-fw fa-plus"></i> Editar módulo', true,
                false, '{{ isPC() ? '75%' : '' }}',
                function() {
                    update_modulo();
                });
        });
        $.LoadingOverlay('hide');
    }

    function update_modulo() {
        if ($('#form_edit_modulo').valid()) {
            datos = {
                _token: '{{ csrf_token() }}',
                nombre: $('#nombre').val().toUpperCase(),
                id_sector: $('#id_sector').val(),
                area: $('#area').val(),
                id_modulo: $('#id_modulo').val(),
                descripcion: $('#descripcion').val(),
            };
            post_jquery('{{ url('sectores_modulos/update_modulo') }}', datos, function() {
                cerrar_modals();
                select_sector(datos['id_sector']);
            });
        }
    }

    function cambiar_estado_modulo(m, estado, sector) {
        mensaje = {
            title: estado == 1 ? '<i class="fa fa-fw fa-trash"></i> Desactivar módulo' :
                '<i class="fa fa-fw fa-unlock"></i> Activar módulo',
            mensaje: estado == 1 ?
                '<div class="alert alert-danger text-center"><i class="fa fa-fw fa-exclamation-triangle"></i> ¿Está seguro de desactivar este módulo?</div>' :
                '<div class="alert alert-info text-center"><i class="fa fa-fw fa-exclamation-triangle"></i> ¿Está seguro de activar este módulo?</div>',
        };
        modal_quest('modal_delete_modulo', mensaje['mensaje'], mensaje['title'], true, false,
            '{{ isPC() ? '25%' : '' }}',
            function() {
                datos = {
                    _token: '{{ csrf_token() }}',
                    id_modulo: m,
                    estado: estado == 1 ? 0 : 1,
                };
                post_jquery('{{ url('sectores_modulos/cambiar_estado_modulo') }}', datos, function() {
                    cerrar_modals();
                    select_sector(sector);
                });
            });
    }

    function edit_lote(l) {
        $.LoadingOverlay('show');
        datos = {
            id_lote: l
        };
        $.get('{{ url('sectores_modulos/edit_lote') }}', datos, function(retorno) {
            modal_form('modal_edit_lote', retorno, '<i class="fa fa-fw fa-plus"></i> Editar lote', true, false,
                '{{ isPC() ? '55%' : '' }}',
                function() {
                    update_lote();
                });
        });
        $.LoadingOverlay('hide');
    }

    function update_lote() {
        if ($('#form_edit_lote').valid()) {
            $.LoadingOverlay('show');
            datos = {
                _token: '{{ csrf_token() }}',
                nombre: $('#nombre').val().toUpperCase(),
                area: $('#area').val(),
                descripcion: $('#descripcion').val(),
                id_lote: $('#id_lote').val(),
                id_modulo: $('#id_modulo').val(),
            };
            post_jquery('{{ url('sectores_modulos/update_lote') }}', datos, function() {
                cerrar_modals();
                listar_sectores_modulos();
            });
            $.LoadingOverlay('hide');
        }
    }

    function cambiar_estado_lote(s, estado) {
        mensaje = {
            title: estado == 1 ? '<i class="fa fa-fw fa-trash"></i> Desactivar lote' :
                '<i class="fa fa-fw fa-unlock"></i> Activar lote',
            mensaje: estado == 1 ?
                '<div class="alert alert-danger text-center"><i class="fa fa-fw fa-exclamation-triangle"></i> ¿Está seguro de desactivar este lote?</div>' :
                '<div class="alert alert-info text-center"><i class="fa fa-fw fa-exclamation-triangle"></i> ¿Está seguro de activar este lote?</div>',
        };
        modal_quest('modal_delete_lote', mensaje['mensaje'], mensaje['title'], true, false,
            '{{ isPC() ? '25%' : '' }}',
            function() {
                datos = {
                    _token: '{{ csrf_token() }}',
                    id_lote: s,
                    estado: estado == 1 ? 0 : 1,
                };
                post_jquery('{{ url('sectores_modulos/cambiar_estado_lote') }}', datos, function() {
                    cerrar_modals();
                    listar_sectores_modulos();
                });
            });
    }

    /* ====================== CICLOS =================== */
    listar_ciclos_sectores_modulos();

    function listar_ciclos_sectores_modulos() {
        datos = {
            variedad: $('#variedad_ciclos').val(),
            tipo: $('#tipo_ciclos').val(),
            finca_actual: $('#fincas_propias').val(),
            sector: $('#filtro_ciclos_sector').val()
        };
        get_jquery('{{ url('sectores_modulos/listar_ciclos') }}', datos, function(retorno) {
            $('#div_ciclos').html(retorno);
            //estructura_tabla('table_listado_ciclos', false, false);
            $('#table_listado_ciclos_length label').addClass('text-color_yura');
            $('#table_listado_ciclos_length label select').addClass('input-yura_white');
            //$('#table_listado_ciclos_length label select').val(50);
            $('#table_listado_ciclos_filter label').addClass('text-color_yura');
            $('#table_listado_ciclos_filter label input').addClass('input-yura_white');
        });
    }

    function crear_activar_modulo() {
        datos = {
            finca_actual: $('#fincas_propias').val(),
        };
        get_jquery('{{ url('sectores_modulos/crear_activar_modulo') }}', {}, function(retorno) {
            modal_view('modal-view', retorno, '<i class="fa fa-fw fa-plus"></i> Activar un módulo nuevo', true,
                false, '75%');
        })
    }

    function ver_ciclos(mod) {
        datos = {
            modulo: mod,
        };
        get_jquery('{{ url('sectores_modulos/ver_ciclos') }}', datos, function(retorno) {
            modal_view('modal-view_ver_ciclos', retorno, '<i class="fa fa-fw fa-refresh"></i> Ciclos', true,
                false, '{{ isPC() ? '95%' : '' }}');
        });
    }

    function terminar_ciclo(ciclo, modal = false, mod) {
        datos = {
            _token: '{{ csrf_token() }}',
            ciclo: ciclo,
            modulo: mod,
            abrir: false,
            fecha_fin: $('#ciclo_fecha_fin_' + ciclo).val(),
        };
        if ($('#ciclo_fecha_fin_' + ciclo) != '') {
            modal_quest('modal-quest_terminar_ciclo',
                '<div class="alert alert-info text-center">¿Está seguro de <strong>TERMINAR</strong> este ciclo?</div>',
                '<i class="fa fa-fw fa-exclamation-triangle"></i> Confirmar acción', true, false,
                '{{ isPC() ? '35%' : '' }}',
                function() {
                    post_jquery('{{ url('sectores_modulos/terminar_ciclo') }}', datos, function() {
                        listar_ciclos_sectores_modulos();
                        cerrar_modals();
                        if (modal)
                            ver_ciclos(mod);
                    });
                });
        } else {
            alerta(
                '<div class="alert alert-warning text-center">Faltan las fechas necesarias para terminar el ciclo</div>'
            );
        }
    }

    function abrir_ciclo(mod, ciclo, modal = false) {
        datos = {
            _token: '{{ csrf_token() }}',
            ciclo: ciclo,
            abrir: true,
        };
        modal_quest('modal-quest_abrir_ciclo',
            '<div class="alert alert-info text-center">¿Está seguro de <strong>ABRIR</strong> este ciclo?</div>',
            '<i class="fa fa-fw fa-exclamation-triangle"></i> Confirmar acción', true, false,
            '{{ isPC() ? '35%' : '' }}',
            function() {
                post_jquery('{{ url('sectores_modulos/abrir_ciclo') }}', datos, function() {
                    listar_ciclos_sectores_modulos();
                    cerrar_modals();
                    if (modal)
                        ver_ciclos(mod);
                });
            });
    }

    function eliminar_ciclo(ciclo) {
        datos = {
            _token: '{{ csrf_token() }}',
            ciclo: ciclo,
        };
        modal_quest('modal-quest_eliminar_ciclo',
            '<div class="alert alert-info text-center">¿Está seguro de <strong>ELIMINAR</strong> este ciclo?</div>',
            '<i class="fa fa-fw fa-exclamation-triangle"></i> Confirmar acción', true, false,
            '{{ isPC() ? '35%' : '' }}',
            function() {
                post_jquery('{{ url('sectores_modulos/eliminar_ciclo') }}', datos, function() {
                    listar_ciclos_sectores_modulos();
                    cerrar_modals();
                    ver_ciclos($('#id_modulo').val());
                });
            });
    }

    function store_ciclo(mod) {
        datos = {
            _token: '{{ csrf_token() }}',
            modulo: mod,
            area: $('#ciclo_area_' + mod).val(),
            variedad: $('#variedad_ciclos').val(),
            fecha_inicio: $('#ciclo_fecha_inicio_' + mod).val(),
            poda_siembra: $('#ciclo_poda_siembra_' + mod).val(),
            fecha_cosecha: $('#ciclo_fecha_cosecha_' + mod).val(),
            fecha_fin: $('#ciclo_fecha_fin_' + mod).val(),
            plantas_iniciales: $('#ciclo_plantas_iniciales_' + mod).val(),
            plantas_muertas: $('#ciclo_plantas_muertas_' + mod).val(),
            conteo: $('#ciclo_conteo_' + mod).val(),
        };

        if (datos['variedad'] != '' && datos['area'] != '' && datos['variedad'] != '' && datos['fecha_inicio'] != '' &&
            datos['poda_siembra'] != '')
            modal_quest('modal-quest_store_ciclo',
                '<div class="alert alert-info text-center">¿Está seguro de <strong>EMPEZAR</strong> este ciclo?</div>',
                '<i class="fa fa-fw fa-exclamation-triangle"></i> Confirmar acción', true, false,
                '{{ isPC() ? '35%' : '' }}',
                function() {
                    post_jquery('{{ url('sectores_modulos/store_ciclo') }}', datos, function() {
                        listar_ciclos_sectores_modulos();
                        cerrar_modals();
                    });
                });
        else
            alerta(
                '<div class="alert alert-warning text-center">Faltan datos necesarios para iniciar un nuevo ciclo</div>'
            );
    }

    function update_ciclo(ciclo) {
        datos = {
            _token: '{{ csrf_token() }}',
            ciclo: ciclo,
            area: $('#ciclo_area_' + ciclo).val(),
            fecha_inicio: $('#ciclo_fecha_inicio_' + ciclo).val(),
            poda_siembra: $('#ciclo_poda_siembra_' + ciclo).val(),
            fecha_cosecha: $('#ciclo_fecha_cosecha_' + ciclo).val(),
            fecha_fin: $('#ciclo_fecha_fin_' + ciclo).val(),
            plantas_iniciales: $('#ciclo_plantas_iniciales_' + ciclo).val(),
            plantas_muertas: $('#ciclo_plantas_muertas_' + ciclo).val(),
            conteo: $('#ciclo_conteo_' + ciclo).val(),
            ancho_cama: $('#ciclo_ancho_cama_' + ciclo).val(),
            ancho_camino: $('#ciclo_ancho_camino_' + ciclo).val(),
        };
        modal_quest('modal-quest_update_ciclo',
            '<div class="alert alert-info text-center">¿Está seguro de <strong>MODIFICAR</strong> este ciclo?</div>',
            '<i class="fa fa-fw fa-exclamation-triangle"></i> Confirmar acción', true, false,
            '{{ isPC() ? '35%' : '' }}',
            function() {
                post_jquery('{{ url('sectores_modulos/update_ciclo') }}', datos, function() {
                    listar_ciclos_sectores_modulos();
                    cerrar_modals();
                });
            });
    }

    function guardar_ciclo(ciclo, mod) {
        datos = {
            _token: '{{ csrf_token() }}',
            ciclo: ciclo,
            activo: $('#activo_ciclo_modal_' + ciclo).val(),
            area: $('#area_ciclo_modal_' + ciclo).val(),
            variedad: $('#variedad_ciclo_modal_' + ciclo).val(),
            fecha_inicio: $('#fecha_inicio_ciclo_modal_' + ciclo).val(),
            poda_siembra: $('#poda_siembra_ciclo_modal_' + ciclo).val(),
            fecha_cosecha: $('#fecha_cosecha_ciclo_modal_' + ciclo).val(),
            fecha_fin: $('#fecha_fin_ciclo_modal_' + ciclo).val(),
            plantas_iniciales: $('#plantas_iniciales_ciclo_modal_' + ciclo).val(),
            plantas_muertas: $('#plantas_muertas_ciclo_modal_' + ciclo).val(),
            conteo: $('#conteo_ciclo_modal_' + ciclo).val(),
            ancho_cama: $('#ancho_cama_ciclo_modal_' + ciclo).val(),
            ancho_camino: $('#ancho_camino_ciclo_modal_' + ciclo).val(),
        };

        if (datos['area'] != '' && datos['variedad'] != '' && datos['fecha_inicio'] != '' && datos['poda_siembra'] !=
            '') {
            if (datos['activo'] == 0 && (datos['fecha_cosecha'] == '' || datos['fecha_fin'] == '')) {
                alerta(
                    '<div class="alert alert-warning text-center">Faltan fechas necesarias para modificar el ciclo</div>'
                );
                return false;
            }
            modal_quest('modal-quest_guardar_ciclo',
                '<div class="alert alert-info text-center">¿Está seguro de <strong>MODIFICAR</strong> este ciclo?</div>',
                '<i class="fa fa-fw fa-exclamation-triangle"></i> Confirmar acción', true, false,
                '{{ isPC() ? '35%' : '' }}',
                function() {
                    post_jquery('{{ url('sectores_modulos/update_ciclo') }}', datos, function() {
                        listar_ciclos_sectores_modulos();
                        ver_ciclos(mod);
                        cerrar_modals();
                    });
                });
        } else
            alerta(
                '<div class="alert alert-warning text-center">Faltan datos necesarios para modificar el ciclo</div>');
    }

    function editar_ciclo(ciclo) {
        $('.elemento_view_' + ciclo).hide();
        $('.elemento_input_' + ciclo).show();
    }

    function ver_cosechas(ciclo) {
        datos = {
            ciclo: ciclo,
        };
        get_jquery('{{ url('sectores_modulos/ver_cosechas') }}', datos, function(retorno) {
            modal_view('modal-view_ver_cosechas', retorno, '<i class="fa fa-fw fa-le"></i> Cosechas', true,
                false, '{{ isPC() ? '95%' : '' }}');
        });
    }

    function nuevos_ciclos() {
        get_jquery('{{ url('sectores_modulos/nuevos_ciclos') }}', {}, function(retorno) {
            $('#tab_new_ciclos').html(retorno);
        });
    }
</script>
