<script>
    function listar_parametro() {
        datos = {
            tipo: $('#tipo_parametro').val()
        };
        get_jquery('{{url('parametros/listar_parametro')}}', datos, function (retorno) {
            $('#div_contenido_parametro').html(retorno);
        });
    }


    function actualizar_salario(sueldo) {
        //dd($request->all());
        datos = {
            sueldo: $('#sueldo').val()
        };
        get_jquery('{{url('parametros/actualizar_salario')}}', datos, function (retorno) {
          
        });
       
    }
    /* //////////////////  AUSENTISMO ////////////////////// */


    function actualizar_ausentismo(id_ausentismo, estado_ausentismo) {
        mensaje = {
            title: estado_ausentismo == 1 ? '<i class="fa fa-fw fa-trash"></i> Desactivar ausentismo' : '<i class="fa fa-fw fa-unlock"></i> Activar ausentismo',
            mensaje: estado_ausentismo == 1 ? '<div class="alert alert-danger text-center"><i class="fa fa-fw fa-exclamation-triangle"></i> ¿Está seguro de desactivar este ausentismo?</div>' :
                '<div class="alert alert-info text-center"><i class="fa fa-fw fa-exclamation-triangle"></i> ¿Está seguro de activar este ausentismo?</div>',
        };
        modal_quest('modal_actualizar_estado_ausentismo', mensaje['mensaje'], mensaje['title'], true, false, '{{isPC() ? '25%' : ''}}', function () {
            datos = {
                _token: '{{csrf_token()}}',
                id_ausentismo: id_ausentismo
            };
            $.LoadingOverlay('show');
            $.post('{{url('parametros/update_estado')}}', datos, function (retorno) {
                if (retorno.success) {
                    if (retorno.estado) {
                        $('#row_agencia_' + id_ausentismo).removeClass('error');
                        $('#btn_usuarios_' + id_ausentismo).removeClass('btn-danger');
                        $('#boton_cargo_' + id_ausentismo).addClass('btn-success');
                        $('#boton_cargo_' + id_ausentismo).prop('title', 'Desactivar');
                        $('#icon_cargo_' + id_ausentismo).removeClass('fa-unlock');
                        $('#icon_cargo_' + id_ausentismo).addClass('fa-trash');
                    } else {
                        $('#row_cargo_' + id_ausentismo).addClass('error');
                        $('#boton_cargo_' + id_ausentismo).removeClass('btn-success');
                        $('#boton_cargo_' + id_ausentismo).addClass('btn-danger');
                        $('#boton_cargo_' + id_ausentismo).prop('title', 'Activar');
                        $('#icon_cargo_' + id_ausentismo).removeClass('fa-trash');
                        $('#icon_cargo_' + id_ausentismo).addClass('fa-unlock');
                    }
                    cerrar_modals();
                    listar_parametro();
                } else {
                    alerta(retorno.mensaje);
                }
            }, 'json').fail(function (retorno) {
                alerta(retorno.responseText);
                alerta('Ha ocurrido un problema al cambiar el estado del ausentismo');
            }).always(function () {
                $.LoadingOverlay('hide');
            })
        });
    }


     /* //////////////////  BANCO ////////////////////// */


     function actualizar_banco(id_banco, estado_banco) {
        mensaje = {
            title: estado_banco == 1 ? '<i class="fa fa-fw fa-trash"></i> Desactivar banco' : '<i class="fa fa-fw fa-unlock"></i> Activar banco',
            mensaje: estado_banco == 1 ? '<div class="alert alert-danger text-center"><i class="fa fa-fw fa-exclamation-triangle"></i> ¿Está seguro de desactivar este banco?</div>' :
                '<div class="alert alert-info text-center"><i class="fa fa-fw fa-exclamation-triangle"></i> ¿Está seguro de activar este banco?</div>',
        };
        modal_quest('modal_actualizar_estado_banco', mensaje['mensaje'], mensaje['title'], true, false, '{{isPC() ? '25%' : ''}}', function () {
            datos = {
                _token: '{{csrf_token()}}',
                id_banco: id_banco
            };
            $.LoadingOverlay('show');
            $.post('{{url('parametros/update_estado')}}', datos, function (retorno) {
                if (retorno.success) {
                    if (retorno.estado) {
                        $('#row_agencia_' + id_banco).removeClass('error');
                        $('#btn_usuarios_' + id_banco).removeClass('btn-danger');
                        $('#boton_cargo_' + id_banco).addClass('btn-success');
                        $('#boton_cargo_' + id_banco).prop('title', 'Desactivar');
                        $('#icon_cargo_' + id_banco).removeClass('fa-unlock');
                        $('#icon_cargo_' + id_banco).addClass('fa-trash');
                    } else {
                        $('#row_cargo_' + id_banco).addClass('error');
                        $('#boton_cargo_' + id_banco).removeClass('btn-success');
                        $('#boton_cargo_' + id_banco).addClass('btn-danger');
                        $('#boton_cargo_' + id_banco).prop('title', 'Activar');
                        $('#icon_cargo_' + id_banco).removeClass('fa-trash');
                        $('#icon_cargo_' + id_banco).addClass('fa-unlock');
                    }
                    cerrar_modals();
                    listar_parametro();
                } else {
                    alerta(retorno.mensaje);
                }
            }, 'json').fail(function (retorno) {
                alerta(retorno.responseText);
                alerta('Ha ocurrido un problema al cambiar el estado del banco');
            }).always(function () {
                $.LoadingOverlay('hide');
            })
        });
    }



    /* //////////////////  CARGO ////////////////////// */

    function add_carg() {
        cant_nuevos++;
        $('#tabla_cargos').append('<tr>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<input type="text" class="text-center" id="new_cargo_' + cant_nuevos + '" style="width: 100%">' +
            '</td>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<button type="button" class="btn btn-xs btn-yura_primary" onclick="store_cargo(' + cant_nuevos + ')">' +
            '<i class="fa fa-fw fa-save"></i>' +
            '</button>' +
            '</td>' +
            '</tr>');
    }

    function add_cargo(id_cargo) {
        datos = {
            id_cargo : id_cargo
        };
        $.LoadingOverlay('show');
        $.get('{{url('parametros/add_cargo')}}', datos, function (retorno) {
            modal_form('modal_add_cargo', retorno, '<i class="fa fa-pencil" aria-hidden="true"></i> Editar nombre del cargo', true, false, '{{isPC() ? '40%' : ''}}', function () {
                store_empaque(id_cargo);
                cerrar_modals();
                $.LoadingOverlay('hide');
            });
        }).always(function () {
            $.LoadingOverlay('hide');
        });
    }



    function actualizar_cargo(id_cargo, estado_cargo) {
        mensaje = {
            title: estado_cargo == 1 ? '<i class="fa fa-fw fa-trash"></i> Desactivar banco' : '<i class="fa fa-fw fa-unlock"></i> Activar cargo',
            mensaje: estado_cargo == 1 ? '<div class="alert alert-danger text-center"><i class="fa fa-fw fa-exclamation-triangle"></i> ¿Está seguro de desactivar este cargo?</div>' :
                '<div class="alert alert-info text-center"><i class="fa fa-fw fa-exclamation-triangle"></i> ¿Está seguro de activar este banco?</div>',
        };
        modal_quest('modal_actualizar_estado_cargo', mensaje['mensaje'], mensaje['title'], true, false, '{{isPC() ? '25%' : ''}}', function () {
            datos = {
                _token: '{{csrf_token()}}',
                id_cargo: id_cargo
            };
            $.LoadingOverlay('show');
            $.post('{{url('parametros/update_cargo')}}', datos, function (retorno) {
                if (retorno.success) {
                    if (retorno.estado) {
                        $('#row_agencia_' + id_cargo).removeClass('error');
                        $('#btn_usuarios_' + id_cargo).removeClass('btn-danger');
                        $('#boton_cargo_' + id_cargo).addClass('btn-success');
                        $('#boton_cargo_' + id_cargo).prop('title', 'Desactivar');
                        $('#icon_cargo_' + id_cargo).removeClass('fa-unlock');
                        $('#icon_cargo_' + id_cargo).addClass('fa-trash');
                    } else {
                        $('#row_cargo_' + id_cargo).addClass('error');
                        $('#boton_cargo_' + id_cargo).removeClass('btn-success');
                        $('#boton_cargo_' + id_cargo).addClass('btn-danger');
                        $('#boton_cargo_' + id_cargo).prop('title', 'Activar');
                        $('#icon_cargo_' + id_cargo).removeClass('fa-trash');
                        $('#icon_cargo_' + id_cargo).addClass('fa-unlock');
                    }
                    cerrar_modals();
                    listar_parametro();
                } else {
                    alerta(retorno.mensaje);
                }
            }, 'json').fail(function (retorno) {
                alerta(retorno.responseText);
                alerta('Ha ocurrido un problema al cambiar el estado del cargo');
            }).always(function () {
                $.LoadingOverlay('hide');
            })
        });
    }


/* //////////////////  PROFESION ////////////////////// */

function actualizar_profesion(id_profesion, estado_profesion) {
        mensaje = {
            title: estado_profesion == 1 ? '<i class="fa fa-fw fa-trash"></i> Desactivar profesion' : '<i class="fa fa-fw fa-unlock"></i> Activar profesion',
            mensaje: estado_profesion == 1 ? '<div class="alert alert-danger text-center"><i class="fa fa-fw fa-exclamation-triangle"></i> ¿Está seguro de desactivar este profesion?</div>' :
                '<div class="alert alert-info text-center"><i class="fa fa-fw fa-exclamation-triangle"></i> ¿Está seguro de activar esta profesión?</div>',
        };
        modal_quest('modal_actualizar_estado_profesion', mensaje['mensaje'], mensaje['title'], true, false, '{{isPC() ? '25%' : ''}}', function () {
            datos = {
                _token: '{{csrf_token()}}',
                id_profesion: id_profesion
            };
            $.LoadingOverlay('show');
            $.post('{{url('parametros/update_profesion')}}', datos, function (retorno) {
                if (retorno.success) {
                    if (retorno.estado) {
                        $('#row_agencia_' + id_profesion).removeClass('error');
                        $('#btn_usuarios_' + id_profesion).removeClass('btn-danger');
                        $('#boton_cargo_' + id_profesion).addClass('btn-success');
                        $('#boton_cargo_' + id_profesion).prop('title', 'Desactivar');
                        $('#icon_cargo_' + id_profesion).removeClass('fa-unlock');
                        $('#icon_cargo_' + id_profesion).addClass('fa-trash');
                    } else {
                        $('#row_cargo_' + id_profesion).addClass('error');
                        $('#boton_cargo_' + id_profesion).removeClass('btn-success');
                        $('#boton_cargo_' + id_profesion).addClass('btn-danger');
                        $('#boton_cargo_' + id_profesion).prop('title', 'Activar');
                        $('#icon_cargo_' + id_profesion).removeClass('fa-trash');
                        $('#icon_cargo_' + id_profesion).addClass('fa-unlock');
                    }
                    cerrar_modals();
                    listar_parametro();
                } else {
                    alerta(retorno.mensaje);
                }
            }, 'json').fail(function (retorno) {
                alerta(retorno.responseText);
                alerta('Ha ocurrido un problema al cambiar el estado del profesion');
            }).always(function () {
                $.LoadingOverlay('hide');
            })
        });
    }


     /* //////////////////  TIPO DE ROL ////////////////////// */

     function actualizar_tipo_rol(id_tipo_rol, estado_tipo_rol) {
        mensaje = {
            title: estado_tipo_rol == 1 ? '<i class="fa fa-fw fa-trash"></i> Desactivar tipo_rol' : '<i class="fa fa-fw fa-unlock"></i> Activar tipo_rol',
            mensaje: estado_tipo_rol == 1 ? '<div class="alert alert-danger text-center"><i class="fa fa-fw fa-exclamation-triangle"></i> ¿Está seguro de desactivar este tipo_rol?</div>' :
                '<div class="alert alert-info text-center"><i class="fa fa-fw fa-exclamation-triangle"></i> ¿Está seguro de activar este tipo_rol?</div>',
        };
        modal_quest('modal_actualizar_estado_tipo_rol', mensaje['mensaje'], mensaje['title'], true, false, '{{isPC() ? '25%' : ''}}', function () {
            datos = {
                _token: '{{csrf_token()}}',
                id_tipo_rol: id_tipo_rol
            };
            $.LoadingOverlay('show');
            $.post('{{url('parametros/update_estado_tipo_rol')}}', datos, function (retorno) {
                if (retorno.success) {
                    if (retorno.estado) {
                        $('#row_agencia_' + id_tipo_rol).removeClass('error');
                        $('#btn_usuarios_' + id_tipo_rol).removeClass('btn-danger');
                        $('#boton_cargo_' + id_tipo_rol).addClass('btn-success');
                        $('#boton_cargo_' + id_tipo_rol).prop('title', 'Desactivar');
                        $('#icon_cargo_' + id_tipo_rol).removeClass('fa-unlock');
                        $('#icon_cargo_' + id_tipo_rol).addClass('fa-trash');
                    } else {
                        $('#row_cargo_' + id_tipo_rol).addClass('error');
                        $('#boton_cargo_' + id_tipo_rol).removeClass('btn-success');
                        $('#boton_cargo_' + id_tipo_rol).addClass('btn-danger');
                        $('#boton_cargo_' + id_tipo_rol).prop('title', 'Activar');
                        $('#icon_cargo_' + id_tipo_rol).removeClass('fa-trash');
                        $('#icon_cargo_' + id_tipo_rol).addClass('fa-unlock');
                    }
                    cerrar_modals();
                    listar_parametro();
                } else {
                    alerta(retorno.mensaje);
                }
            }, 'json').fail(function (retorno) {
                alerta(retorno.responseText);
                alerta('Ha ocurrido un problema al cambiar el estado del tipo_rol');
            }).always(function () {
                $.LoadingOverlay('hide');
            })
        });
    }


    /* //////////////////  D E S V I N C U L A C I O N ////////////////////// */

    function add_causa_desvinculacion() {
        cant_nuevos++;
        $('#tabla_causa_desvinculacion').append('<tr>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<input type="text" class="text-center" id="new_causa_desvinculacion_' + cant_nuevos + '" style="width: 100%">' +
            '</td>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<button type="button" class="btn btn-xs btn-yura_primary" onclick="store_causa_desvinculacion(' + cant_nuevos + ')">' +
            '<i class="fa fa-fw fa-save"></i>' +
            '</button>' +
            '</td>' +
            '</tr>');
    }



    function actualizar_causa_desvinculacion(id_causa_desvinculacion, estado_causa_desvinculacion) {
        mensaje = {
            title: estado_causa_desvinculacion == 1 ? '<i class="fa fa-fw fa-trash"></i> Desactivar Causa' : '<i class="fa fa-fw fa-unlock"></i> Activar Causa',
            mensaje: estado_causa_desvinculacion == 1 ? '<div class="alert alert-danger text-center"><i class="fa fa-fw fa-exclamation-triangle"></i> ¿Está seguro de desactivar esta causa?</div>' :
                '<div class="alert alert-info text-center"><i class="fa fa-fw fa-exclamation-triangle"></i> ¿Está seguro de activar ésta causa de desvinculación?</div>',
        };
        modal_quest('modal_actualizar_estado_causa_desvinculacion', mensaje['mensaje'], mensaje['title'], true, false, '{{isPC() ? '25%' : ''}}', function () {
            datos = {
                _token: '{{csrf_token()}}',
                id_causa_desvinculacion: id_causa_desvinculacion
            };
            $.LoadingOverlay('show');
            $.post('{{url('parametros/update_estado_causa')}}', datos, function (retorno) {
                if (retorno.success) {
                    if (retorno.estado) {
                        $('#row_causa_desvinculacion_' + id_causa_desvinculacion).removeClass('error');
                        $('#btn_causa_desvinculacion_' + id_causa_desvinculacion).removeClass('btn-danger');
                        $('#boton_causa_desvinculacion_' + id_causa_desvinculacion).addClass('btn-success');
                        $('#boton_causa_desvinculacion_' + id_causa_desvinculacion).prop('title', 'Desactivar');
                        $('#icon_causa_desvinculacion_' + id_causa_desvinculacion).removeClass('fa-unlock');
                        $('#icon_causa_desvinculacion_' + id_causa_desvinculacion).addClass('fa-trash');
                    } else {
                        $('#row_causa_desvinculacion_' + id_causa_desvinculacion).addClass('error');
                        $('#boton_causa_desvinculacion_' + id_causa_desvinculacion).removeClass('btn-success');
                        $('#boton_causa_desvinculacion_' + id_causa_desvinculacion).addClass('btn-danger');
                        $('#boton_causa_desvinculacion_' + id_causa_desvinculacion).prop('title', 'Activar');
                        $('#icon_causa_desvinculacion_' + id_causa_desvinculacion).removeClass('fa-trash');
                        $('#icon_causa_desvinculacion_' + id_causa_desvinculacion).addClass('fa-unlock');
                    }
                    cerrar_modals();
                    listar_parametro();
                } else {
                    alerta(retorno.mensaje);
                }
            }, 'json').fail(function (retorno) {
                alerta(retorno.responseText);
                alerta('Ha ocurrido un problema al cambiar el estado de la agencia de carga');
            }).always(function () {
                $.LoadingOverlay('hide');
            })
        });
    }

    //////////////////  TIPO_PAGO ////////////////////// */

    function actualizar_tipo_pago(id_tipo_pago, estado_tipo_pago) {
        mensaje = {
            title: estado_tipo_pago == 1 ? '<i class="fa fa-fw fa-trash"></i> Desactivar tipo_pago' : '<i class="fa fa-fw fa-unlock"></i> Activar tipo_pago',
            mensaje: estado_tipo_pago == 1 ? '<div class="alert alert-danger text-center"><i class="fa fa-fw fa-exclamation-triangle"></i> ¿Está seguro de desactivar este tipo_pago?</div>' :
                '<div class="alert alert-info text-center"><i class="fa fa-fw fa-exclamation-triangle"></i> ¿Está seguro de activar este tipo_pago?</div>',
        };
        modal_quest('modal_actualizar_estado_tipo_pago', mensaje['mensaje'], mensaje['title'], true, false, '{{isPC() ? '25%' : ''}}', function () {
            datos = {
                _token: '{{csrf_token()}}',
                id_tipo_pago: id_tipo_pago
            };
            $.LoadingOverlay('show');
            $.post('{{url('parametros/update_tipo_pago')}}', datos, function (retorno) {
                if (retorno.success) {
                    if (retorno.estado) {
                        $('#row_agencia_' + id_tipo_pago).removeClass('error');
                        $('#btn_usuarios_' + id_tipo_pago).removeClass('btn-danger');
                        $('#boton_cargo_' + id_tipo_pago).addClass('btn-success');
                        $('#boton_cargo_' + id_tipo_pago).prop('title', 'Desactivar');
                        $('#icon_cargo_' + id_tipo_pago).removeClass('fa-unlock');
                        $('#icon_cargo_' + id_tipo_pago).addClass('fa-trash');
                    } else {
                        $('#row_cargo_' + id_tipo_pago).addClass('error');
                        $('#boton_cargo_' + id_tipo_pago).removeClass('btn-success');
                        $('#boton_cargo_' + id_tipo_pago).addClass('btn-danger');
                        $('#boton_cargo_' + id_tipo_pago).prop('title', 'Activar');
                        $('#icon_cargo_' + id_tipo_pago).removeClass('fa-trash');
                        $('#icon_cargo_' + id_tipo_pago).addClass('fa-unlock');
                    }
                    cerrar_modals();
                    listar_parametro();
                } else {
                    alerta(retorno.mensaje);
                }
            }, 'json').fail(function (retorno) {
                alerta(retorno.responseText);
                alerta('Ha ocurrido un problema al cambiar el estado del tipo_pago');
            }).always(function () {
                $.LoadingOverlay('hide');
            })
        });
    }

//////////////////  TIPO_CONTRATO ////////////////////// */

function actualizar_tipo_contrato(id_tipo_contrato, estado_tipo_contrato) {
        mensaje = {
            title: estado_tipo_contrato == 1 ? '<i class="fa fa-fw fa-trash"></i> Desactivar tipo_contrato' : '<i class="fa fa-fw fa-unlock"></i> Activar tipo_contrato',
            mensaje: estado_tipo_contrato == 1 ? '<div class="alert alert-danger text-center"><i class="fa fa-fw fa-exclamation-triangle"></i> ¿Está seguro de desactivar este tipo de contrato?</div>' :
                '<div class="alert alert-info text-center"><i class="fa fa-fw fa-exclamation-triangle"></i> ¿Está seguro de activar este tipo_contrato?</div>',
        };
        modal_quest('modal_actualizar_estado_tipo_contrato', mensaje['mensaje'], mensaje['title'], true, false, '{{isPC() ? '25%' : ''}}', function () {
            datos = {
                _token: '{{csrf_token()}}',
                id_tipo_contrato: id_tipo_contrato
            };
            $.LoadingOverlay('show');
            $.post('{{url('parametros/update_estado_tipo_contrato')}}', datos, function (retorno) {
                if (retorno.success) {
                    if (retorno.estado) {
                        $('#row_agencia_' + id_tipo_contrato).removeClass('error');
                        $('#btn_usuarios_' + id_tipo_contrato).removeClass('btn-danger');
                        $('#boton_cargo_' + id_tipo_contrato).addClass('btn-success');
                        $('#boton_cargo_' + id_tipo_contrato).prop('title', 'Desactivar');
                        $('#icon_cargo_' + id_tipo_contrato).removeClass('fa-unlock');
                        $('#icon_cargo_' + id_tipo_contrato).addClass('fa-trash');
                    } else {
                        $('#row_cargo_' + id_tipo_contrato).addClass('error');
                        $('#boton_cargo_' + id_tipo_contrato).removeClass('btn-success');
                        $('#boton_cargo_' + id_tipo_contrato).addClass('btn-danger');
                        $('#boton_cargo_' + id_tipo_contrato).prop('title', 'Activar');
                        $('#icon_cargo_' + id_tipo_contrato).removeClass('fa-trash');
                        $('#icon_cargo_' + id_tipo_contrato).addClass('fa-unlock');
                    }
                    cerrar_modals();
                    listar_parametro();
                } else {
                    alerta(retorno.mensaje);
                }
            }, 'json').fail(function (retorno) {
                alerta(retorno.responseText);
                alerta('Ha ocurrido un problema al cambiar el estado del tipo_contrato');
            }).always(function () {
                $.LoadingOverlay('hide');
            })
        });
    }


    /////////////////  SUCURSAL ////////////////////// */

    function actualizar_sucursal(id_sucursal, estado_sucursal) {
        mensaje = {
            title: estado_sucursal == 1 ? '<i class="fa fa-fw fa-trash"></i> Desactivar sucursal' : '<i class="fa fa-fw fa-unlock"></i> Activar sucursal',
            mensaje: estado_sucursal == 1 ? '<div class="alert alert-danger text-center"><i class="fa fa-fw fa-exclamation-triangle"></i> ¿Está seguro de desactivar este sucursal?</div>' :
                '<div class="alert alert-info text-center"><i class="fa fa-fw fa-exclamation-triangle"></i> ¿Está seguro de activar este sucursal?</div>',
        };
        modal_quest('modal_actualizar_estado_sucursal', mensaje['mensaje'], mensaje['title'], true, false, '{{isPC() ? '25%' : ''}}', function () {
            datos = {
                _token: '{{csrf_token()}}',
                id_sucursal: id_sucursal
            };
            $.LoadingOverlay('show');
            $.post('{{url('parametros/update_estado')}}', datos, function (retorno) {
                if (retorno.success) {
                    if (retorno.estado) {
                        $('#row_agencia_' + id_sucursal).removeClass('error');
                        $('#btn_usuarios_' + id_sucursal).removeClass('btn-danger');
                        $('#boton_cargo_' + id_sucursal).addClass('btn-success');
                        $('#boton_cargo_' + id_sucursal).prop('title', 'Desactivar');
                        $('#icon_cargo_' + id_sucursal).removeClass('fa-unlock');
                        $('#icon_cargo_' + id_sucursal).addClass('fa-trash');
                    } else {
                        $('#row_cargo_' + id_sucursal).addClass('error');
                        $('#boton_cargo_' + id_sucursal).removeClass('btn-success');
                        $('#boton_cargo_' + id_sucursal).addClass('btn-danger');
                        $('#boton_cargo_' + id_sucursal).prop('title', 'Activar');
                        $('#icon_cargo_' + id_sucursal).removeClass('fa-trash');
                        $('#icon_cargo_' + id_sucursal).addClass('fa-unlock');
                    }
                    cerrar_modals();
                    listar_parametro();
                } else {
                    alerta(retorno.mensaje);
                }
            }, 'json').fail(function (retorno) {
                alerta(retorno.responseText);
                alerta('Ha ocurrido un problema al cambiar el estado del sucursal');
            }).always(function () {
                $.LoadingOverlay('hide');
            })
        });
    }
/* //////////////////  GRUPO_INTERNO ////////////////////// */

function actualizar_grupo_interno(id_grupo_interno, estado_grupo_interno) {
        mensaje = {
            title: estado_grupo_interno == 1 ? '<i class="fa fa-fw fa-trash"></i> Desactivar grupo_interno' : '<i class="fa fa-fw fa-unlock"></i> Activar grupo_interno',
            mensaje: estado_grupo_interno == 1 ? '<div class="alert alert-danger text-center"><i class="fa fa-fw fa-exclamation-triangle"></i> ¿Está seguro de desactivar este grupo_interno?</div>' :
                '<div class="alert alert-info text-center"><i class="fa fa-fw fa-exclamation-triangle"></i> ¿Está seguro de activar este grupo_interno?</div>',
        };
        modal_quest('modal_actualizar_estado_grupo_interno', mensaje['mensaje'], mensaje['title'], true, false, '{{isPC() ? '25%' : ''}}', function () {
            datos = {
                _token: '{{csrf_token()}}',
                id_grupo_interno: id_grupo_interno
            };
            $.LoadingOverlay('show');
            $.post('{{url('parametros/update_estado')}}', datos, function (retorno) {
                if (retorno.success) {
                    if (retorno.estado) {
                        $('#row_agencia_' + id_grupo_interno).removeClass('error');
                        $('#btn_usuarios_' + id_grupo_interno).removeClass('btn-danger');
                        $('#boton_cargo_' + id_grupo_interno).addClass('btn-success');
                        $('#boton_cargo_' + id_grupo_interno).prop('title', 'Desactivar');
                        $('#icon_cargo_' + id_grupo_interno).removeClass('fa-unlock');
                        $('#icon_cargo_' + id_grupo_interno).addClass('fa-trash');
                    } else {
                        $('#row_cargo_' + id_grupo_interno).addClass('error');
                        $('#boton_cargo_' + id_grupo_interno).removeClass('btn-success');
                        $('#boton_cargo_' + id_grupo_interno).addClass('btn-danger');
                        $('#boton_cargo_' + id_grupo_interno).prop('title', 'Activar');
                        $('#icon_cargo_' + id_grupo_interno).removeClass('fa-trash');
                        $('#icon_cargo_' + id_grupo_interno).addClass('fa-unlock');
                    }
                    cerrar_modals();
                    listar_parametro();
                } else {
                    alerta(retorno.mensaje);
                }
            }, 'json').fail(function (retorno) {
                alerta(retorno.responseText);
                alerta('Ha ocurrido un problema al cambiar el estado del grupo_interno');
            }).always(function () {
                $.LoadingOverlay('hide');
            })
        });
    }
/* //////////////////  GRADO_INSTRUCCION ////////////////////// */

function actualizar_grado_instruccion(id_grado_instruccion, estado_grado_instruccion) {
        mensaje = {
            title: estado_grado_instruccion == 1 ? '<i class="fa fa-fw fa-trash"></i> Desactivar grado_instruccion' : '<i class="fa fa-fw fa-unlock"></i> Activar grado_instruccion',
            mensaje: estado_grado_instruccion == 1 ? '<div class="alert alert-danger text-center"><i class="fa fa-fw fa-exclamation-triangle"></i> ¿Está seguro de desactivar este grado_instruccion?</div>' :
                '<div class="alert alert-info text-center"><i class="fa fa-fw fa-exclamation-triangle"></i> ¿Está seguro de activar este grado_instruccion?</div>',
        };
        modal_quest('modal_actualizar_estado_grado_instruccion', mensaje['mensaje'], mensaje['title'], true, false, '{{isPC() ? '25%' : ''}}', function () {
            datos = {
                _token: '{{csrf_token()}}',
                id_grado_instruccion: id_grado_instruccion
            };
            $.LoadingOverlay('show');
            $.post('{{url('parametros/update_estado')}}', datos, function (retorno) {
                if (retorno.success) {
                    if (retorno.estado) {
                        $('#row_agencia_' + id_grado_instruccion).removeClass('error');
                        $('#btn_usuarios_' + id_grado_instruccion).removeClass('btn-danger');
                        $('#boton_cargo_' + id_grado_instruccion).addClass('btn-success');
                        $('#boton_cargo_' + id_grado_instruccion).prop('title', 'Desactivar');
                        $('#icon_cargo_' + id_grado_instruccion).removeClass('fa-unlock');
                        $('#icon_cargo_' + id_grado_instruccion).addClass('fa-trash');
                    } else {
                        $('#row_cargo_' + id_grado_instruccion).addClass('error');
                        $('#boton_cargo_' + id_grado_instruccion).removeClass('btn-success');
                        $('#boton_cargo_' + id_grado_instruccion).addClass('btn-danger');
                        $('#boton_cargo_' + id_grado_instruccion).prop('title', 'Activar');
                        $('#icon_cargo_' + id_grado_instruccion).removeClass('fa-trash');
                        $('#icon_cargo_' + id_grado_instruccion).addClass('fa-unlock');
                    }
                    cerrar_modals();
                    listar_parametro();
                } else {
                    alerta(retorno.mensaje);
                }
            }, 'json').fail(function (retorno) {
                alerta(retorno.responseText);
                alerta('Ha ocurrido un problema al cambiar el estado del grado_instruccion');
            }).always(function () {
                $.LoadingOverlay('hide');
            })
        });
    }


/* //////////////////  AGRUPACION ////////////////////// */

function actualizar_agrupacion(id_agrupacion, estado_agrupacion) {
        mensaje = {
            title: estado_agrupacion == 1 ? '<i class="fa fa-fw fa-trash"></i> Desactivar agrupacion' : '<i class="fa fa-fw fa-unlock"></i> Activar agrupacion',
            mensaje: estado_agrupacion == 1 ? '<div class="alert alert-danger text-center"><i class="fa fa-fw fa-exclamation-triangle"></i> ¿Está seguro de desactivar este agrupacion?</div>' :
                '<div class="alert alert-info text-center"><i class="fa fa-fw fa-exclamation-triangle"></i> ¿Está seguro de activar este agrupacion?</div>',
        };
        modal_quest('modal_actualizar_estado_agrupacion', mensaje['mensaje'], mensaje['title'], true, false, '{{isPC() ? '25%' : ''}}', function () {
            datos = {
                _token: '{{csrf_token()}}',
                id_agrupacion: id_agrupacion
            };
            $.LoadingOverlay('show');
            $.post('{{url('parametros/update_estado')}}', datos, function (retorno) {
                if (retorno.success) {
                    if (retorno.estado) {
                        $('#row_agencia_' + id_agrupacion).removeClass('error');
                        $('#btn_usuarios_' + id_agrupacion).removeClass('btn-danger');
                        $('#boton_cargo_' + id_agrupacion).addClass('btn-success');
                        $('#boton_cargo_' + id_agrupacion).prop('title', 'Desactivar');
                        $('#icon_cargo_' + id_agrupacion).removeClass('fa-unlock');
                        $('#icon_cargo_' + id_agrupacion).addClass('fa-trash');
                    } else {
                        $('#row_cargo_' + id_agrupacion).addClass('error');
                        $('#boton_cargo_' + id_agrupacion).removeClass('btn-success');
                        $('#boton_cargo_' + id_agrupacion).addClass('btn-danger');
                        $('#boton_cargo_' + id_agrupacion).prop('title', 'Activar');
                        $('#icon_cargo_' + id_agrupacion).removeClass('fa-trash');
                        $('#icon_cargo_' + id_agrupacion).addClass('fa-unlock');
                    }
                    cerrar_modals();
                    listar_parametro();
                } else {
                    alerta(retorno.mensaje);
                }
            }, 'json').fail(function (retorno) {
                alerta(retorno.responseText);
                alerta('Ha ocurrido un problema al cambiar el estado del agrupacion');
            }).always(function () {
                $.LoadingOverlay('hide');
            })
        });
    }


/* //////////////////  PLANTILLA ////////////////////// */

function actualizar_plantilla(id_plantilla, estado_plantilla) {
        mensaje = {
            title: estado_plantilla == 1 ? '<i class="fa fa-fw fa-trash"></i> Desactivar plantilla' : '<i class="fa fa-fw fa-unlock"></i> Activar plantilla',
            mensaje: estado_plantilla == 1 ? '<div class="alert alert-danger text-center"><i class="fa fa-fw fa-exclamation-triangle"></i> ¿Está seguro de desactivar este plantilla?</div>' :
                '<div class="alert alert-info text-center"><i class="fa fa-fw fa-exclamation-triangle"></i> ¿Está seguro de activar este plantilla?</div>',
        };
        modal_quest('modal_actualizar_estado_plantilla', mensaje['mensaje'], mensaje['title'], true, false, '{{isPC() ? '25%' : ''}}', function () {
            datos = {
                _token: '{{csrf_token()}}',
                id_plantilla: id_plantilla
            };
            $.LoadingOverlay('show');
            $.post('{{url('parametros/update_estado')}}', datos, function (retorno) {
                if (retorno.success) {
                    if (retorno.estado) {
                        $('#row_agencia_' + id_plantilla).removeClass('error');
                        $('#btn_usuarios_' + id_plantilla).removeClass('btn-danger');
                        $('#boton_cargo_' + id_plantilla).addClass('btn-success');
                        $('#boton_cargo_' + id_plantilla).prop('title', 'Desactivar');
                        $('#icon_cargo_' + id_plantilla).removeClass('fa-unlock');
                        $('#icon_cargo_' + id_plantilla).addClass('fa-trash');
                    } else {
                        $('#row_cargo_' + id_plantilla).addClass('error');
                        $('#boton_cargo_' + id_plantilla).removeClass('btn-success');
                        $('#boton_cargo_' + id_plantilla).addClass('btn-danger');
                        $('#boton_cargo_' + id_plantilla).prop('title', 'Activar');
                        $('#icon_cargo_' + id_plantilla).removeClass('fa-trash');
                        $('#icon_cargo_' + id_plantilla).addClass('fa-unlock');
                    }
                    cerrar_modals();
                    listar_parametro();
                } else {
                    alerta(retorno.mensaje);
                }
            }, 'json').fail(function (retorno) {
                alerta(retorno.responseText);
                alerta('Ha ocurrido un problema al cambiar el estado del plantilla');
            }).always(function () {
                $.LoadingOverlay('hide');
            })
        });
    }









</script>
