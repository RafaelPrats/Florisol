<script>
    $('#vista_actual').val('armado_cajas');
    var customLoading = $("<p>", {
        "css": {
            "font-size": "2em",
            "text-align": "center",
            "margin-top": "7px",
            "color": "white",
        },
        "text": "ESPERANDO_LECTURA"
    });
    setTimeout(() => {
        $('#filtro_codigo_barra').focus();
    }, 1500);

    function escanear_codigo(consulta = false, codigo = '') {
        datos = {
            codigo: codigo == '' ? $('#filtro_codigo_barra').val() : codigo,
            consulta: consulta,
        }
        get_jquery('{{ url('correcion_inventario/escanear_codigo') }}', datos, function(retorno) {
            $('#body_escaneado').html(retorno);
            $('#filtro_codigo_barra').val('');
            $('#filtro_codigo_barra').focus();
        }, 'body_escaneado');
    }

    function agregar_a_listado(inv) {
        nombre_variedad = $('#scan_nombre_variedad').val();
        longitud = $('#scan_longitud').val();
        tallos_x_ramo = parseInt($('#scan_tallos_x_ramo').val());
        disponibles = $('#scan_disponibles').val();
        edad = $('#scan_edad').val();
        id_inv = inv;
        existe = $('#new_id_inventario_recepcion_' + id_inv).val();
        if (existe != undefined) {
            ramos = parseInt($('#new_ramos_' + id_inv).val());
            ramos += tallos_x_ramo;
            $('#new_ramos_' + id_inv).val(ramos)
        } else {
            $('#table_caja').append('<tr id="new_tr_' + id_inv + '">' +
                '<td class="text-center" style="border-color: #9d9d9d">' +
                '<a href="javascript:void(0)" onclick="escanear_codigo(' + true + ', ' + id_inv +
                ')" style="color: black">' +
                '<sup><i class="fa fa-fw fa-copy"></i></sup> ' +
                nombre_variedad +
                '</a>' +
                '<input type="hidden" value="' + id_inv + '" class="new_id_inventario_recepcion">' +
                '<input type="hidden" value="' + id_inv + '" id="new_id_inventario_recepcion_' + id_inv + '">' +
                '</td>' +
                '<td class="text-center" style="border-color: #9d9d9d">' +
                longitud + ' <sup>cm</sup>' +
                '</td>' +
                '<td class="text-center" style="border-color: #9d9d9d">' +
                tallos_x_ramo +
                '<input type="hidden" value="' + tallos_x_ramo + '" id="new_tallos_x_ramo_' + id_inv + '">' +
                '</td>' +
                '<td class="text-center" style="border-color: #9d9d9d">' +
                edad + ' <sup>dias</sup>' +
                '</td>' +
                '<td class="text-center" style="border-color: #9d9d9d">' +
                '<input type="number" value="' + tallos_x_ramo +
                '" min="1" style="width: 100%" class="text-center" id="new_ramos_' +
                id_inv +
                '" onchange="calcular_totales()">' +
                '</td>' +
                '<td class="text-center hidden" style="border-color: #9d9d9d; width: 80px">' +
                '<input type="number" disabled value="' + disponibles +
                '" style="width: 100%" class="text-center" id="new_disponibles_' + id_inv + '">' +
                '</td>' +
                '<td class="text-center" style="border-color: #9d9d9d">' +
                '<button type="button" class="btn btn-xs btn-yura_danger" onclick="eliminar_fila_caja(' + id_inv +
                ')">' +
                '<i class="fa fa-fw fa-trash"></i>' +
                '</button>' +
                '</td>' +
                '</tr>');
        }
        calcular_totales();
    }

    var input_scan = document.getElementById("filtro_codigo_barra");
    input_scan.addEventListener("focus", myFocusFunction, true);
    input_scan.addEventListener("blur", myBlurFunction, true);

    function myFocusFunction() {
        $("#filtro_codigo_barra").LoadingOverlay("show", {
            image: "",
            custom: customLoading
        });
    }

    function myBlurFunction() {
        $("#filtro_codigo_barra").LoadingOverlay('hide');
    }

    function eliminar_fila_caja(id) {
        $('#new_tr_' + id).remove();
        calcular_totales();
    }

    function calcular_totales() {
        new_id_inventario_recepcion = $('.new_id_inventario_recepcion');
        total_tallos = 0;
        total_ramos = 0;
        for (i = 0; i < new_id_inventario_recepcion.length; i++) {
            id_inv = new_id_inventario_recepcion[i].value;
            tallos_x_ramo = parseInt($('#new_tallos_x_ramo_' + id_inv).val());
            ramos = parseInt($('#new_ramos_' + id_inv).val());
            disponibles = parseInt($('#new_disponibles_' + id_inv).val());

            total_tallos += tallos_x_ramo * ramos;
            total_ramos += ramos;
        }
        $('#td_total_tallos_caja').html(total_tallos);
        $('#td_total_ramos_caja').html(total_ramos);
    }

    function corregir_inventario(opcion) {
        switch (opcion) {
            case 1:
                url = '{{ url('correcion_inventario/corregir_all_inventario') }}';
                texto = 'TODO EL INVENTARIO';
                clase = 'danger';
                break;
            case 2:
                url = '{{ url('correcion_inventario/corregir_inventario_selected') }}';
                texto = 'SOLO LO SELECCIONADO';
                clase = 'warning';
                break;
        }
        new_id_inventario_recepcion = $('.new_id_inventario_recepcion');
        data = [];
        for (i = 0; i < new_id_inventario_recepcion.length; i++) {
            id_inv = new_id_inventario_recepcion[i].value;
            ramos = parseInt($('#new_ramos_' + id_inv).val());
            disponibles = parseInt($('#new_disponibles_' + id_inv).val());
            data.push({
                id_inv: id_inv,
                ramos: ramos,
                disponibles: disponibles,
            });
        }
        if (data.length > 0) {
            texto =
                "<div class='alert alert-" + clase +
                " text-center' style='font-size: 1.5em'>Esta a punto de <b>SOBREESCRIBIR " +
                texto + "</b> de recepcion</div>";

            modal_quest('modal_corregir_inventario', texto, 'Corregir inventario', true, false, '50%', function() {
                datos = {
                    _token: '{{ csrf_token() }}',
                    nombre: $('#nombre_caja').val(),
                    fecha: $('#fecha_caja').val(),
                    data: JSON.stringify(data),
                }
                post_jquery_m(url, datos, function() {
                    cargar_url('correcion_inventario');
                });
            })
        }
    }
</script>
