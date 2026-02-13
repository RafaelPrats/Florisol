<script>
    $('#vista_actual').val('aplicaciones_campo');
    var new_app = 0;

    function buscar_listado_aplicaciones() {
        datos = {
            planta: $('#filtro_planta').val(),
            tipo: $('#filtro_tipo').val(),
            poda_siembra: $('#filtro_poda_siembra').val(),
        };
        if (datos['planta'] != '') {
            $('#div_content_aplicaciones').LoadingOverlay('show');
            $.get('{{url('aplicaciones_campo/buscar_listado')}}', datos, function (retorno) {
                $('#div_content_aplicaciones').html(retorno);
                /*estructura_tabla('table_aplicaciones');
                $('#table_aplicaciones_filter>label>input').addClass('input-yura_default');*/
                //$('.dataTables_empty').html('No se han encontrado resultados');
            }).always(function () {
                $('#div_content_aplicaciones').LoadingOverlay('hide');
            });
        }
    }

    function add_aplicacion() {
        new_app++;
        tipo = $('#filtro_tipo').val();
        tr = '<tr id="tr_new_app_' + new_app + '">' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<input type="text" id="new_nombre_' + new_app + '" class="text-center" style="width: 100%" placeholder="Nombre">' +
            '</td>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<input type="number" id="new_dia_ini_' + new_app + '" class="text-center" style="width: 100%" placeholder="Día Ini." ' +
            'onkeyup="calcular_new_semana_ini(' + new_app + ')">' +
            '</td>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<input type="number" id="new_semana_ini_' + new_app + '" class="text-center" style="width: 100%" placeholder="Sem. Ini.">' +
            '</td>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<input type="number" id="new_repeticiones_' + new_app + '" class="text-center" style="width: 100%" placeholder="Repeticiones" min="1">' +
            '</td>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<input type="number" id="new_veces_x_semana_' + new_app + '" class="text-center" style="width: 100%" placeholder="No. veces" min="1">' +
            '</td>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<input type="number" id="new_frecuencia_' + new_app + '" class="text-center" style="width: 100%" placeholder="Frecuencia" min="0">' +
            '</td>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<select id="new_poda_siembra_' + new_app + '" style="width: 100%">' +
            '<option value="T">Podas y Siembras</option>' +
            '<option value="P">Podas</option>' +
            '<option value="S">Siembras</option>' +
            '</select>' +
            '</td>' +
            '<td class="text-center ' + (tipo == "C" ? "hidden" : "") + '" style="border-color: #9d9d9d">' +
            '<input type="number" id="new_litro_x_cama_' + new_app + '" class="text-center" style="width: 100%" placeholder="Lt x Cama" min="1">' +
            '</td>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<select id="new_continua_' + new_app + '" style="width: 100%">' +
            '<option value="0">No</option>' +
            '<option value="1">Sí</option>' +
            '</select>' +
            '</td>' +
            '<td class="text-center" style="border-color: #9d9d9d" colspan="2">' +
            '<select id="new_app_matriz_' + new_app + '" style="width: 100%">' +
            $('#app_matriz_new').html() +
            '</select>' +
            '</td>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<div class="btn-group">' +
            '<button type="button" class="btn btn-yura_primary btn-xs" onclick="store_app(' + new_app + ')">' +
            '<i class="fa fa-fw fa-save"></i>' +
            '</button>' +
            '</div>' +
            '</td>' +
            '</tr>';
        $('#table_aplicaciones_tfoot').append(tr);
        $('#new_nombre_' + new_app).focus();
    }

    function store_app(num_app) {
        datos = {
            _token: '{{csrf_token()}}',
            tipo: $('#filtro_tipo').val(),
            nombre: $('#new_nombre_' + num_app).val(),
            semana_ini: $('#new_semana_ini_' + num_app).val(),
            repeticiones: $('#new_repeticiones_' + num_app).val(),
            veces_x_semana: $('#new_veces_x_semana_' + num_app).val(),
            frecuencia: $('#new_frecuencia_' + num_app).val(),
            continua: $('#new_continua_' + num_app).val(),
            poda_siembra: $('#new_poda_siembra_' + num_app).val(),
            app_matriz: $('#new_app_matriz_' + num_app).val(),
            dia_ini: $('#new_dia_ini_' + num_app).val() != '' ? $('#new_dia_ini_' + num_app).val() : 0,
            litro_x_cama: $('#new_litro_x_cama_' + num_app).val() != '' ? $('#new_litro_x_cama_' + num_app).val() : 0,
            planta: $('#filtro_planta').val(),
        };
        $('#tr_new_app_' + num_app).LoadingOverlay('show');
        $.post('{{url('aplicaciones_campo/store_app')}}', datos, function (retorno) {
            if (retorno.success) {
                buscar_listado_aplicaciones();
            } else {
                alerta(retorno.mensaje);
            }
        }, 'json').fail(function (retorno) {
            console.log(retorno);
            alerta_errores(retorno.responseText);
        }).always(function () {
            $('#tr_new_app_' + num_app).LoadingOverlay('hide');
        })
    }

    function update_app(id_app) {
        datos = {
            _token: '{{csrf_token()}}',
            id_app: id_app,
            nombre: $('#nombre_app_' + id_app).val(),
            tipo: $('#tipo_app_' + id_app).val(),
            semana_ini: $('#semana_ini_app_' + id_app).val(),
            repeticiones: $('#repeticiones_app_' + id_app).val(),
            veces_x_semana: $('#veces_x_semana_app_' + id_app).val(),
            frecuencia: $('#frecuencia_app_' + id_app).val(),
            continua: $('#continua_app_' + id_app).val(),
            poda_siembra: $('#poda_siembra_app_' + id_app).val(),
            app_matriz: $('#app_matriz_' + id_app).val(),
            dia_ini: $('#dia_ini_app_' + id_app).val() != '' ? $('#dia_ini_app_' + id_app).val() : 0,
            litro_x_cama: $('#litro_x_cama_app_' + id_app).val() != '' ? $('#litro_x_cama_app_' + id_app).val() : 0,
        };
        $('#tr_app_' + id_app).LoadingOverlay('show');
        $.post('{{url('aplicaciones_campo/update_app')}}', datos, function (retorno) {
            if (retorno.success) {

            } else {
                alerta(retorno.mensaje);
            }
        }, 'json').fail(function (retorno) {
            console.log(retorno);
            alerta_errores(retorno.responseText);
        }).always(function () {
            $('#tr_app_' + id_app).LoadingOverlay('hide');
        })
    }

    function desactivar_app(id_app, estado) {
        texto = estado == 1 ? 'DESACTIVAR' : 'ACTIVAR';
        modal_quest('modal-quest_desactivar_app',
            '<div class="alert alert-info text-center">¿Desea <strong>' + texto + '</strong> la aplicación?</div>',
            '<i class="fa fa-fw fa-exclamation-triangle"></i> Mensaje de confirmación', true, false, '', function () {
                datos = {
                    _token: '{{csrf_token()}}',
                    id_app: id_app,
                };
                $('#tr_app_' + id_app).LoadingOverlay('show');
                $.post('{{url('aplicaciones_campo/desactivar_app')}}', datos, function (retorno) {
                    if (retorno.success) {
                        buscar_listado_aplicaciones();
                        /*if (retorno.borrar) {
                            $('#tr_app_' + id_app).remove();
                        } else {
                            datos_app = {
                                id_app: id_app,
                            };
                            get_jquery('{{url('aplicaciones_campo/get_row_listado')}}', datos_app, function (retorno_view) {
                                $('#tr_app_' + id_app).html(retorno_view);
                            });
                        }*/
                    } else {
                        alerta(retorno.mensaje);
                    }
                }, 'json').fail(function (retorno) {
                    console.log(retorno);
                    alerta_errores(retorno.responseText);
                }).always(function () {
                    $('#tr_app_' + id_app).LoadingOverlay('hide');
                });
                cerrar_modals();
            });
    }

    function mezclas_app(id_app) {
        datos = {
            id_app: id_app
        };
        get_jquery('{{url('aplicaciones_campo/mezclas_app')}}', datos, function (retorno) {
            modal_view('modal-view_mezclas_app', retorno, '<i class="fa fa-fw fa-list-alt"></i>Meclas de la Aplicación', true, false, '80%');
        });
    }

    function detalles_app(id_app) {
        datos = {
            id_app: id_app
        };
        get_jquery('{{url('aplicaciones_campo/detalles_app')}}', datos, function (retorno) {
            modal_view('modal-view_detalles_app', retorno, '<i class="fa fa-fw fa-list-alt"></i>Detalles de la Aplicación', true, false, '80%');
        });
    }

    function variedades_app(id_app) {
        datos = {
            id_app: id_app
        };
        get_jquery('{{url('aplicaciones_campo/variedades_app')}}', datos, function (retorno) {
            modal_view('modal-view_variedades_app', retorno, '<i class="fa fa-fw fa-list-alt"></i>Variedades de la Aplicación', true, false, '50%');
        });
    }

    function calcular_semana_ini(id_app) {
        dias = $('#dia_ini_app_' + id_app).val();
        semana = parseInt(dias / 7);
        $('#semana_ini_app_' + id_app).val(semana);
    }

    function calcular_new_semana_ini(id_app) {
        dias = $('#new_dia_ini_' + id_app).val();
        semana = parseInt(dias / 7);
        $('#new_semana_ini_' + id_app).val(semana);
    }

    function parametrizar_app(campo, id_app) {
        datos = {
            id_app: id_app,
            campo: campo,
        };
        get_jquery('{{url('aplicaciones_campo/parametrizar_app')}}', datos, function (retorno) {
            modal_view('modal-view_parametrizar_app', retorno, '<i class="fa fa-fw fa-sitemap"></i> Parametrizar aplicación',
                true, false, '60%');
        });
    }
</script>