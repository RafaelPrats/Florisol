<table width="100%" style="margin-bottom: 0;">
    <tr>
        <td>
            <div class="input-group">
                <div class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                    <i class="fa fa-fw fa-barcode"></i> Escanear Codigo
                </div>
                <input type="text" id="filtro_codigo_barra" required class="form-control input-yura_default text-center"
                    autofocus style="width: 100% !important;" onchange="escanear_codigo()">
            </div>
        </td>
    </tr>
</table>

<div style="overflow-x: scroll; width: 100%">
    <table style="width:100%; margin-top: 5px">
        <tr>
            <td style="vertical-align: top; padding-right: 5px" id="td_inventarios">
                <div class="panel panel-success" style="margin-bottom: 0px;" id="panel_inventarios">
                    <div class="panel-heading"
                        style="display: flex; justify-content: space-between; align-items: center;">
                        <b> <i class="fa fa-list"></i> LISTADO </b>
                        <div class="btn-group">
                            <button type="button" class="btn btn-xs btn-yura_primary" onclick="store_despachos()">
                                <i class="fa fa-fw fa-save"></i> GUARDAR LISTADO
                            </button>
                        </div>
                    </div>
                    <div class="panel-body" id="body_contenido_listado" style="max-height: 500px">
                        <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
                            <tr>
                                <th class="text-center th_yura_green">
                                    Proveedor
                                </th>
                                <th class="text-center th_yura_green">
                                    Variedad
                                </th>
                                <th class="text-center th_yura_green">
                                    Longitud
                                </th>
                                <th class="text-center th_yura_green">
                                    Ramos
                                </th>
                                <th class="text-center th_yura_green">
                                    TxR
                                </th>
                                <th class="text-center th_yura_green" style="width: 80px" colspan="2">
                                    Faltantes
                                </th>
                            </tr>
                            <tbody id="table_listado"></tbody>
                            <tr>
                                <th class="text-center bg-yura_dark" colspan="2">
                                    Totales
                                </th>
                                <th class="text-center bg-yura_dark">
                                </th>
                                <th class="text-center bg-yura_dark" id="td_total_ramos_listado">
                                </th>
                                <th class="text-center bg-yura_dark" id="td_total_tallos_listado">
                                </th>
                                <th class="text-center bg-yura_dark" style="width: 80px">
                                </th>
                            </tr>
                        </table>
                    </div>
                </div>
            </td>
            <td style="vertical-align: top; padding-left: 5px;" id="td_seleccionados">
                <div class="panel panel-success" style="margin-bottom:0px; min-width: 240px;" id="panel_seleccionados">
                    <div class="panel-heading"
                        style="display: flex; justify-content: space-between; align-items: center;">
                        <b> <i class="fa fa-th"></i> DETALLE DE LA LECTURA</b>
                    </div>
                    <div class="panel-body" style="max-height: 500px; overflow:auto" id="body_escaneado">
                    </div>
                </div>
            </td>
        </tr>
    </table>
</div>

<script>
    var customLoading = $("<p>", {
        "css": {
            "font-size": "2em",
            "text-align": "center",
            "margin-top": "7px",
            "color": "white",
        },
        "text": "ESPERANDO_LECTURA"
    });

    var input_scan = document.getElementById("filtro_codigo_barra");
    input_scan.addEventListener("focus", myFocusFunction, true);
    input_scan.addEventListener("blur", myBlurFunction, true);

    setTimeout(() => {
        $('#filtro_codigo_barra').focus();
    }, 500);

    function myFocusFunction() {
        $("#filtro_codigo_barra").LoadingOverlay("show", {
            image: "",
            custom: customLoading
        });
    }

    function myBlurFunction() {
        $("#filtro_codigo_barra").LoadingOverlay('hide');
    }

    function escanear_codigo(consulta = false, codigo = '') {
        datos = {
            codigo: codigo == '' ? $('#filtro_codigo_barra').val() : codigo,
            consulta: consulta,
        }
        get_jquery('{{ url('recepcion/escanear_codigo') }}', datos, function(retorno) {
            $('#body_escaneado').html(retorno);
            $('#filtro_codigo_barra').val('');
            $('#filtro_codigo_barra').focus();
        }, 'body_escaneado');
    }

    function agregar_a_listado(id) {
        nombre_proveedor = $('#scan_nombre_proveedor').val();
        nombre_variedad = $('#scan_nombre_variedad').val();
        longitud = $('#scan_longitud').val();
        tallos_x_ramo = $('#scan_tallos_x_ramo').val();
        disponibles = $('#scan_disponibles').val();
        id_model = id;
        existe = $('#new_ramos_' + id_model).val();
        if (existe != undefined) {
            ramos = parseInt($('#new_ramos_' + id_model).val());
            ramos++;
            if (ramos <= $('#new_ramos_' + id_model).prop('max')) {
                $('#new_ramos_' + id_model).val(ramos)
            } else {
                alerta(
                    '<div class="alert alert-warning text-center">La cantidad <b>INGRESADA</b> supera los ramos <b>FALTANTES</b></div>'
                );
            }
        } else {
            $('#table_listado').append('<tr id="new_tr_' + id_model + '">' +
                '<td class="text-center" style="border-color: #9d9d9d">' +
                '<a href="javascript:void(0)" onclick="escanear_codigo(' + true + ', ' + id_model +
                ')" style="color: black">' +
                '<sup><i class="fa fa-fw fa-eye"></i></sup>' +
                nombre_proveedor +
                '</a>' +
                '</td>' +
                '<td class="text-center" style="border-color: #9d9d9d">' +
                nombre_variedad +
                '</td>' +
                '<td class="text-center" style="border-color: #9d9d9d">' +
                longitud + ' <sup>cm</sup>' +
                '</td>' +
                '<td class="text-center" style="border-color: #9d9d9d">' +
                '<input type="number" value="1" min="1" max="' + disponibles +
                '" style="width: 100%" class="text-center new_id_despacho_proveedor" id="new_ramos_' + id_model +
                '" onchange="calcular_totales_listado()" data-id_model="' + id_model + '">' +
                '</td>' +
                '<td class="text-center" style="border-color: #9d9d9d">' +
                tallos_x_ramo +
                '<input type="hidden" value="' + tallos_x_ramo + '" id="new_tallos_x_ramo_' + id_model + '">' +
                '</td>' +
                '<td class="text-center" style="border-color: #9d9d9d">' +
                '<input type="number" disabled value="' + disponibles +
                '" style="width: 100%" class="text-center" id="new_disponibles_' + id_model + '">' +
                '</td>' +
                '<td class="text-center" style="border-color: #9d9d9d">' +
                '<button type="button" class="btn btn-xs btn-yura_danger" onclick="eliminar_fila_listado(' +
                id_model +
                ')">' +
                '<i class="fa fa-fw fa-trash"></i>' +
                '</button>' +
                '</td>' +
                '</tr>');
        }
        calcular_totales_listado();
    }

    function eliminar_fila_listado(id) {
        $('#new_tr_' + id).remove();
        calcular_totales_listado();
    }

    function calcular_totales_listado() {
        new_id_despacho_proveedor = $('.new_id_despacho_proveedor');
        total_tallos = 0;
        total_ramos = 0;
        for (i = 0; i < new_id_despacho_proveedor.length; i++) {
            id_model = new_id_despacho_proveedor[i].getAttribute('data-id_model');
            tallos_x_ramo = parseInt($('#new_tallos_x_ramo_' + id_model).val());
            ramos = parseInt($('#new_ramos_' + id_model).val());
            disponibles = parseInt($('#new_disponibles_' + id_model).val());
            if (ramos > 0 && ramos <= disponibles) {
                total_tallos += tallos_x_ramo * ramos;
                total_ramos += ramos;
            } else {
                alerta(
                    '<div class="alert alert-warning text-center">La cantidad <b>INGRESADA</b> debe ser menor que los ramos <b>FALTANTES</b></div>'
                );
                $('#new_ramos_' + id_model).val(1);
                calcular_totales_listado()
            }
        }
        new_inventario_bqt = $('.new_inventario_bqt');
        for (i = 0; i < new_inventario_bqt.length; i++) {
            cod = new_inventario_bqt[i].value;
            tallos_x_ramo = parseInt($('#new_tallos_x_ramo_bqt_' + cod).val());
            ramos = parseInt($('#new_ramos_bqt_' + cod).val());
            total_tallos += tallos_x_ramo * ramos;
            total_ramos += ramos;
        }

        $('#td_total_tallos_listado').html(total_tallos);
        $('#td_total_ramos_listado').html(total_ramos);
    }

    function store_despachos() {
        new_id_despacho_proveedor = $('.new_id_despacho_proveedor');
        data = [];
        for (i = 0; i < new_id_despacho_proveedor.length; i++) {
            id_model = new_id_despacho_proveedor[i].getAttribute('data-id_model');
            ramos = $('#new_ramos_' + id_model).val();
            if (ramos > 0) {
                data.push({
                    id_model: id_model,
                    ramos: ramos,
                });
            }
        }
        datos = {
            _token: '{{ csrf_token() }}',
            fecha: $('#fecha_filtro').val(),
            data: JSON.stringify(data),
        }
        post_jquery_m('{{ url('recepcion/store_despachos') }}', datos, function() {
            cerrar_modals();
            listar_reporte();
        });
    }
</script>
