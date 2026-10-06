<div class="proyecto-form">
    @include('adminlte.gestion.comercializacion.proyectos.forms._estilos_proyecto')

    <section class="proyecto-card">
        <div class="proyecto-card-heading">
            <span class="proyecto-card-icon"><i class="fa fa-fw fa-file-text-o"></i></span>
            <div>
                <h4>Datos del pedido</h4>
                <small>Fecha, cliente y condiciones de entrega</small>
            </div>
        </div>
        <div class="proyecto-card-body">
            <div class="proyecto-fields">
                <div class="proyecto-field form_fecha">
                    <label for="form_fecha">Fecha de entrega</label>
                    <input type="date" id="form_fecha" class="form-control" value="{{ hoy() }}">
                </div>
                <div class="proyecto-field">
                    <label for="form_segmento">Segmento</label>
                    <select id="form_segmento" class="form-control" onchange="seleccionar_segmento()">
                        <option value="">Seleccione</option>
                        @foreach ($segmentos as $seg)
                            <option value="{{ $seg }}">{{ $seg }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="proyecto-field">
                    <label for="form_cliente">Cliente</label>
                    <div class="proyecto-field-inline">
                        <select id="form_cliente" class="form-control" onchange="seleccionar_cliente()"></select>
                        <button type="button" class="btn proyecto-spec-button" onclick="cambiar_especificaciones()"
                            title="Usar especificaciones" id="btn_especificaciones">
                            <i class="fa fa-fw fa-refresh"></i><sup>SI</sup>
                        </button>
                    </div>
                </div>
                <div class="proyecto-field">
                    <label for="form_consignatario">Consignatario</label>
                    <select id="form_consignatario" class="form-control"></select>
                </div>
                <div class="proyecto-field">
                    <label for="form_agencia">Agencia</label>
                    <select id="form_agencia" class="form-control"></select>
                </div>
                <div class="proyecto-field">
                    <label for="form_tipo_pedido">Tipo de pedido</label>
                    <select id="form_tipo_pedido" class="form-control"
                        onchange="$('.form_fecha').toggleClass('hidden')">
                        <option value="OM">OPEN MARKET</option>
                        <option value="SO">STANDING ORDER</option>
                    </select>
                </div>
            </div>

            <div class="proyecto-schedule proyecto-field form_fecha hidden" style="margin: 12px 6px 0">
                <ul class="list-group proyecto-schedule-menu">
                    <li class="list-group-item">OPCIONES DE ENTREGA</li>
                    <a href="javascript:void(0)" class="list-group-item"
                        onclick="cargar_opciones_orden_fija(1)">
                        Día de la semana <i class="fa fa-fw fa-arrow-right pull-right"></i>
                    </a>
                    <a href="javascript:void(0)" class="list-group-item"
                        onclick="cargar_opciones_orden_fija(2)">
                        Día del mes <i class="fa fa-fw fa-arrow-right pull-right"></i>
                    </a>
                    <a href="javascript:void(0)" class="list-group-item"
                        onclick="cargar_opciones_orden_fija(3)">
                        Personalizado <i class="fa fa-fw fa-arrow-right pull-right"></i>
                    </a>
                </ul>
                <div class="proyecto-schedule-options" id="div_opciones_orden_fija"></div>
            </div>
        </div>
    </section>

    <section class="proyecto-card">
        <ul class="nav nav-tabs nav-justified proyecto-tabs">
            <li class="active">
                <a data-toggle="tab" href="#tab-combos">
                    <i class="fa fa-fw fa-gift"></i> Armado de Cajas
                </a>
            </li>
            <li>
                <a data-toggle="tab" href="#tab-contenido_pedido">
                    <i class="fa fa-fw fa-shopping-cart"></i> Contenido del Pedido
                    <sup><span class="badge" id="span_total_piezas_pedido">0 cajas</span></sup>
                </a>
            </li>
        </ul>
        <div class="tab-content proyecto-tab-content">
            <div id="tab-combos" class="tab-pane fade in active">
                @include('adminlte.gestion.comercializacion.proyectos.forms._pedidos_combos')
            </div>
            <div id="tab-contenido_pedido" class="tab-pane fade">
                @include('adminlte.gestion.comercializacion.proyectos.forms._contenido_pedido')
            </div>
        </div>
    </section>

    <div class="proyecto-summary">
        <div class="proyecto-summary-metrics">
            <div class="proyecto-summary-metric">
                <span>Piezas totales</span>
                <strong id="th_total_piezas_pedido">0</strong>
            </div>
            <div class="proyecto-summary-metric">
                <span>Ramos totales</span>
                <strong id="th_total_ramos_pedido">0</strong>
            </div>
            <div class="proyecto-summary-metric">
                <span>Tallos totales</span>
                <strong id="th_total_tallos_pedido">0</strong>
            </div>
            <div class="proyecto-summary-metric proyecto-summary-money">
                <span>Monto total</span>
                <strong id="th_total_monto_pedido">$0</strong>
            </div>
        </div>
        <div class="proyecto-summary-actions">
            <button type="button" class="btn btn-yura_primary" onclick="store_proyecto(0)">
                <i class="fa fa-fw fa-save"></i> Grabar Pedido
            </button>
            <button type="button" class="btn btn-yura_default" onclick="cerrar_modals(); add_proyecto()">
                <i class="fa fa-fw fa-refresh"></i> Reiniciar
                <span class="badge bg-yura_dark" id="span_total_monto_pedido">$0</span>
            </button>
            <button type="button" class="btn btn-yura_warning" onclick="store_proyecto(1)">
                <i class="fa fa-fw fa-gift"></i> Grabar y despachar
            </button>
        </div>
    </div>
</div>

<script>
    setTimeout(() => {
        $("#form_cliente, #form_consignatario, #form_agencia, #form_combos_receta_1")
            .select2({
                dropdownParent: $('#div_modal-modal_add_proyecto')
            });
        $('.select2-container').css('width', '100%');
        $('.select2-selection').css('height', '34px');
    }, 500);
    usar_especificaciones = 1;

    form_cant_detalles = 0;
    cargar_opciones_orden_fija(1);

    function cargar_opciones_orden_fija(opcion) {
        datos = {
            opcion: opcion,
        };
        get_jquery('{{ url('proyectos/cargar_opciones_orden_fija') }}', datos, function(retorno) {
            $('#div_opciones_orden_fija').html(retorno);
        });
    }

    function add_fechas_pedido_fijo_personalizado() {
        form_cant_fechas_orden_fija++;
        $('#td_fechas_pedido_fijo_personalizado').append('<div class="col-md-4" id="div_' +
            form_cant_fechas_orden_fija + '">' +
            '<div class="input-group" style="min-width: 180px">' +
            '<span class="input-group-addon span-input-group-yura-fixed bg-yura_dark">' +
            'Fecha ' + form_cant_fechas_orden_fija +
            '</span>' +
            '<input type="date" id="fecha_desde_pedido_fijo_' + form_cant_fechas_orden_fija +
            '" name="fecha_desde_pedido_fijo_' + form_cant_fechas_orden_fija + '"' +
            'class="form-control text-center input-yura_default" style="width: 100%" required>' +
            '</div>' +
            '</div>');
    }

    function seleccionar_segmento() {
        datos = {
            _token: '{{ csrf_token() }}',
            segmento: $('#form_segmento').val(),
        }
        $.LoadingOverlay('show');
        $.post('{{ url('proyectos/seleccionar_segmento') }}', datos, function(retorno) {
            $('#form_cliente').html(retorno.options_cliente);
        }, 'json').fail(function(retorno) {
            console.log(retorno);
            alerta_errores(retorno.responseText);
        }).always(function() {
            $.LoadingOverlay('hide');
        })
    }

    function seleccionar_cliente() {
        datos = {
            _token: '{{ csrf_token() }}',
            cliente: $('#form_cliente').val(),
            usar_especificaciones: usar_especificaciones
        }
        $('#btn_add_detalle_combo').addClass('hidden');
        $.LoadingOverlay('show');
        $.post('{{ url('proyectos/seleccionar_cliente') }}', datos, function(retorno) {
            $('#form_consignatario').html(retorno.options_consignatario);
            $('#form_agencia').html(retorno.options_agencia);
            $('.select_variedades').html(retorno.options_variedades);
            if (datos['cliente'] != '')
                $('#btn_add_detalle_combo').removeClass('hidden');
        }, 'json').fail(function(retorno) {
            console.log(retorno);
            alerta_errores(retorno.responseText);
        }).always(function() {
            $.LoadingOverlay('hide');
        })
    }

    function delete_contenido_pedido(form_cant) {
        $('.tr_form_ped_' + form_cant).remove();
        calcular_totales_pedido();
    }

    function calcular_totales_pedido() {
        pos_ped_especificaciones = $('.pos_ped_especificaciones');
        total_piezas_pedido = 0;
        total_ramos_pedido = 0;
        total_tallos_pedido = 0;
        total_monto_pedido = 0;
        for (y = 0; y < pos_ped_especificaciones.length; y++) {
            num_pos = pos_ped_especificaciones[y].value;

            piezas = $('#ped_piezas_' + num_pos).val();
            piezas = piezas != '' ? parseInt(piezas) : 0;
            cant_detalles_combo = $('#cant_detalles_combo_' + num_pos);
            if (cant_detalles_combo.length) {
                total_ramos = 0;
                total_tallos = 0;
                precio_caja = 0;
                for (c = 0; c < cant_detalles_combo.val(); c++) {
                    ramos_x_caja = $('#ped_ramos_x_caja_' + num_pos + '_' + c).val();
                    ramos_x_caja = ramos_x_caja != '' ? parseInt(ramos_x_caja) : 0;
                    tallos_x_ramos = $('#ped_tallos_x_ramos_' + num_pos + '_' + c).val();
                    tallos_x_ramos = tallos_x_ramos != '' ? parseInt(tallos_x_ramos) : 0;
                    precio_ped = $('#ped_precio_esp_' + num_pos + '_' + c).val();
                    precio_ped = precio_ped != '' ? parseFloat(precio_ped) : 0;

                    total_ramos += piezas * ramos_x_caja;
                    total_tallos += piezas * ramos_x_caja * tallos_x_ramos;
                    precio_caja += Math.round((piezas * ramos_x_caja * precio_ped) * 100) / 100;
                }
            } else {
                ramos_x_caja = $('#ped_ramos_x_caja_' + num_pos).val();
                ramos_x_caja = ramos_x_caja != '' ? parseInt(ramos_x_caja) : 0;
                tallos_x_ramos = $('#ped_tallos_x_ramos_' + num_pos).val();
                tallos_x_ramos = tallos_x_ramos != '' ? parseInt(tallos_x_ramos) : 0;
                precio_ped = $('#ped_precio_esp_' + num_pos).val();
                precio_ped = precio_ped != '' ? parseFloat(precio_ped) : 0;

                total_ramos = piezas * ramos_x_caja;
                total_tallos = piezas * ramos_x_caja * tallos_x_ramos;
                precio_caja = Math.round((piezas * ramos_x_caja * precio_ped) * 100) / 100;
            }
            $('#ped_total_ramos_' + num_pos).val(total_ramos);
            $('#ped_total_tallos_' + num_pos).val(total_tallos);
            $('#ped_total_precio_caja_' + num_pos).val('$' + precio_caja);

            total_piezas_pedido += piezas;
            total_ramos_pedido += total_ramos;
            total_tallos_pedido += total_tallos;
            total_monto_pedido += precio_caja;
        }
        total_monto_pedido = Math.round(total_monto_pedido * 100) / 100;

        $('#span_total_piezas_pedido').html(total_piezas_pedido + ' cajas');
        $('#span_total_monto_pedido').html('$' + total_monto_pedido);
        $('#th_total_piezas_pedido').html(total_piezas_pedido);
        $('#th_total_ramos_pedido').html(total_ramos_pedido);
        $('#th_total_tallos_pedido').html(total_tallos_pedido);
        $('#th_total_monto_pedido').html('$' + total_monto_pedido);
    }

    function store_proyecto(liquidar) {
        tipo = $('#form_tipo_pedido').val();
        fallos = false;
        // FECHA
        if (tipo == 'OM') {
            fecha = $('#form_fecha').val();
        } else {
            opcion_pedido_fijo = $('#opcion_pedido_fijo').val();
            if (opcion_pedido_fijo == 1) { // dia semana
                dia_semana = $('#dia_semana').val();
                desde = $('#fecha_desde_pedido_fijo').val();
                hasta = $('#fecha_hasta_pedido_fijo').val();
                intervalo = $('#intervalo_pedido_fijo').val();
                renovar = $('#renovar_pedido_fijo').prop('checked');

                $('#dia_semana').removeClass('bg-red');
                $('#fecha_desde_pedido_fijo').removeClass('bg-red');
                $('#fecha_hasta_pedido_fijo').removeClass('bg-red');
                if (dia_semana != '' && desde != '' && hasta != '') {
                    fecha = {
                        opcion_pedido_fijo: opcion_pedido_fijo,
                        dia_semana: dia_semana,
                        desde: desde,
                        hasta: hasta,
                        intervalo: intervalo,
                        renovar: renovar,
                    }
                } else {
                    fallos = true;
                    if (dia_semana == '')
                        $('#dia_semana').addClass('bg-red');
                    if (desde == '')
                        $('#fecha_desde_pedido_fijo').addClass('bg-red');
                    if (hasta == '')
                        $('#fecha_hasta_pedido_fijo').addClass('bg-red');
                }
            } else if (opcion_pedido_fijo == 2) { // dia mes
                dia_mes = $('#dia_mes').val();
                desde = $('#fecha_desde_pedido_fijo').val();
                hasta = $('#fecha_hasta_pedido_fijo').val();

                $('#dia_mes').removeClass('bg-red');
                $('#fecha_desde_pedido_fijo').removeClass('bg-red');
                $('#fecha_hasta_pedido_fijo').removeClass('bg-red');
                if (dia_mes != '' && desde != '' && hasta != '') {
                    fecha = {
                        opcion_pedido_fijo: opcion_pedido_fijo,
                        dia_mes: dia_mes,
                        desde: desde,
                        hasta: hasta,
                    }
                } else {
                    fallos = true;
                    if (dia_mes == '')
                        $('#dia_mes').addClass('bg-red');
                    if (desde == '')
                        $('#fecha_desde_pedido_fijo').addClass('bg-red');
                    if (hasta == '')
                        $('#fecha_hasta_pedido_fijo').addClass('bg-red');
                }
            } else { // fecha personalizada
                fechas = [];
                for (f = 1; f <= form_cant_fechas_orden_fija; f++) {
                    date = $('#fecha_desde_pedido_fijo_' + f).val();
                    $('#fecha_desde_pedido_fijo_' + f).removeClass('bg-red');
                    if (date != '') {
                        fechas.push(
                            date
                        );
                    } else {
                        fallos = true;
                        if (date == '')
                            $('#fecha_desde_pedido_fijo_' + f).addClass('bg-red');
                    }
                }
                fecha = {
                    opcion_pedido_fijo: opcion_pedido_fijo,
                    fechas: fechas,
                };
            }
        }

        segmento = $('#form_segmento').val();
        cliente = $('#form_cliente').val();
        consignatario = $('#form_consignatario').val();
        agencia = $('#form_agencia').val();

        // DETALLES PEDIDO
        pos_ped_especificaciones = $('.pos_ped_especificaciones');
        detalles_pedido = [];
        for (y = 0; y < pos_ped_especificaciones.length; y++) {
            num_pos = pos_ped_especificaciones[y].value;

            piezas = $('#ped_piezas_' + num_pos).val();
            caja = $('#ped_tipo_caja_' + num_pos).val();
            cant_detalles_combo = $('#cant_detalles_combo_' + num_pos);
            detalles_combo = [];
            if (cant_detalles_combo.length) {
                for (c = 0; c < cant_detalles_combo.val(); c++) {
                    receta = $('#ped_receta_' + num_pos + '_' + c).val();
                    longitud = $('#ped_longitud_' + num_pos + '_' + c).val();
                    ramos_x_caja = $('#ped_ramos_x_caja_' + num_pos + '_' + c).val();
                    tallos_x_ramos = $('#ped_tallos_x_ramos_' + num_pos + '_' + c).val();
                    precio_ped = $('#ped_precio_esp_' + num_pos + '_' + c).val();

                    $('#ped_piezas_' + num_pos).removeClass('bg-red');
                    $('#ped_ramos_x_caja_' + num_pos + '_' + c).removeClass('bg-red');
                    $('#ped_tallos_x_ramos_' + num_pos + '_' + c).removeClass('bg-red');
                    $('#ped_precio_esp_' + num_pos + '_' + c).removeClass('bg-red');
                    if (piezas != '' && ramos_x_caja != '' && tallos_x_ramos != '' && precio_ped != '') {
                        detalles_combo.push({
                            receta: receta,
                            longitud: longitud,
                            ramos_x_caja: ramos_x_caja,
                            tallos_x_ramos: tallos_x_ramos,
                            precio_ped: precio_ped,
                        });
                    } else {
                        fallos = true;
                        if (piezas == '')
                            $('#ped_piezas_' + num_pos).addClass('bg-red');
                        if (ramos_x_caja == '')
                            $('#ped_ramos_x_caja_' + num_pos + '_' + c).addClass('bg-red');
                        if (tallos_x_ramos == '')
                            $('#ped_tallos_x_ramos_' + num_pos + '_' + c).addClass('bg-red');
                        if (precio_ped == '')
                            $('#ped_precio_esp_' + num_pos + '_' + c).addClass('bg-red');
                    }
                }

                ids_marcaciones = $('.ids_marcaciones');
                valores_marcaciones = [];
                for (m = 0; m < ids_marcaciones.length; m++) {
                    id_marcacion = ids_marcaciones[m].value;
                    valor_marcacion = $('#ped_marcacion_' + id_marcacion + '_' + num_pos).val();
                    valores_marcaciones.push({
                        id_marcacion: id_marcacion,
                        valor_marcacion: valor_marcacion,
                    });
                }

                detalles_pedido.push({
                    piezas: piezas,
                    caja: caja,
                    valores_marcaciones: valores_marcaciones,
                    detalles_combo: detalles_combo,
                });
            }
        }

        if (detalles_pedido.length > 0)
            if (!fallos) {
                texto = liquidar ? '<b>GRABAR Y DESPACHAR la flor solida</b> del ' : '<b>GRABAR</b> el ';
                clase = liquidar ? 'warning' : 'info';
                mensaje = {
                    title: '<i class="fa fa-fw fa-save"></i> Mensaje de confirmacion',
                    mensaje: '<div class="alert alert-' + clase +
                        ' text-center" style="font-size: 1.3em" id="div_mensaje_confirmacion"><h3><i class="fa fa-fw fa-exclamation-triangle"></i> ¿Está seguro de ' +
                        texto + ' pedido?</h3></div>',
                };
                BootstrapDialog.show({
                    title: mensaje['title'],
                    closable: false,
                    draggable: true,
                    message: $('<div></div>').html(mensaje['mensaje']),
                    onshown: function(modal) {
                        $('#' + modal.getId()).css('overflow-y', 'scroll');
                        $('#' + modal.getId() + '>div').css('width', '{{ isPC() ? '50%' : '' }}');
                        modal.setId('modal_quest_store_proyecto');
                        arreglo_modals_form.push(modal);
                        $('#btn_no_' + 'modal_quest_store_proyecto').addClass('btn-yura_default');
                        $('#btn_continue_' + 'modal_quest_store_proyecto').addClass('btn-yura_primary');
                    },
                    callback: function() {
                        arreglo_modals_form = [];
                    },
                    buttons: [{
                        id: 'btn_no_' + 'modal_quest_store_proyecto',
                        label: 'No',
                        icon: 'fa fa-fw fa-times',
                        action: function(modal) {
                            modal.close();
                        }
                    }, {
                        id: 'btn_continue_' + 'modal_quest_store_proyecto',
                        label: 'Continuar',
                        icon: 'fa fa-fw fa-check',
                        cssClass: 'btn btn-primary',
                        action: function(modal) {
                            $('#div_mensaje_confirmacion').html(
                                '<i class="fa fa-fw fa-search"></i> <b>VALIDANDO</b> el pedido')
                            datos = {
                                _token: '{{ csrf_token() }}',
                                liquidar: liquidar,
                                tipo: tipo,
                                fecha: fecha,
                                cliente: cliente,
                                segmento: segmento,
                                consignatario: consignatario,
                                agencia: agencia,
                                detalles_pedido: JSON.stringify(detalles_pedido),
                            }
                            $.LoadingOverlay('show');
                            $.post('{{ url('proyectos/store_proyecto') }}', datos, function(
                                retorno) {
                                if (retorno.success) {
                                    mini_alerta('success', retorno.mensaje, 5000);
                                    listar_reporte();
                                    cerrar_modals();
                                    //add_proyecto();
                                } else {
                                    modal.close();
                                    alerta(retorno.mensaje);
                                }
                            }, 'json').fail(function(retorno) {
                                modal.close();
                                console.log(retorno);
                                alerta_errores(retorno.responseText);
                                alerta('Ha ocurrido un problema al enviar la información');
                            }).always(function() {
                                $.LoadingOverlay('hide');
                            });
                        }
                    }]
                });
            } else
                alerta('<div class="alert alert-warning text-center">Faltan datos por ingresar en el pedido</div>')
        else
            alerta('<div class="alert alert-warning text-center">El contenido del pedido esta vacio</div>')
    }

    function cambiar_especificaciones() {
        if (usar_especificaciones) {
            usar_especificaciones = 0;
            $('#btn_especificaciones').html('<i class="fa fa-fw fa-refresh"></i> <sup>NO</sup>')
        } else {
            usar_especificaciones = 1;
            $('#btn_especificaciones').html('<i class="fa fa-fw fa-refresh"></i> <sup>SI</sup>')
        }
        seleccionar_cliente();
    }
</script>
