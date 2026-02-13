<script>
    function estructura_tabla() {
        $('#table_content_semanas').DataTable({
            order: [],
            responsive: true,
            paging: false,
            info: false,
            search: false,
            columnDefs: [
                {
//                    targets: [9],
                    searchable: false,
                    orderable: false
                }
            ],
            language: {
                sSearch: "Filtrar en este listado: "
            }
        });
    }

    function select_accion(a) {
        $.LoadingOverlay('show');
        datos = {
            accion: a
        };
        get_jquery('{{url('semanas/get_accion')}}', datos, function (retorno) {
            $('#div_content_semanas').html('');
            $('#div_content_form_accions').html(retorno);
        });
        $.LoadingOverlay('hide');
    }

    function procesar() {
        if ($('#form-accions').valid()) {
            modal_quest('modal_quest_procesar',
                '<div class="alert alert-info text-center">Está a punto de generar las semanas para el año indicado<br>¿Desea continuar?</div>',
                '<i class="fa fa-fw fa-gears"></i> Procesar semanas', true, false, '35%', function () {
                    $.LoadingOverlay('show');
                    datos = {
                        _token: '{{csrf_token()}}',
                        id_variedad: $('#id_variedad').val(),
                        anno: $('#anno').val(),
                        fecha_inicial: $('#fecha_inicial').val(),
                        fecha_final: $('#fecha_final').val(),
                    };
                    post_jquery('{{url('semanas/procesar')}}', datos, function () {
                        cerrar_modals();
                        listar();
                    });
                    $.LoadingOverlay('hide');
                });
        }
    }

    function listar() {
        if ($('#form-accions').valid()) {
            datos = {
                id_variedad: $('#id_variedad').val(),
                anno: $('#anno').val(),
            };
            get_jquery('{{url('semanas/listar_semanas')}}', datos, function (retorno) {
                $('#div_content_semanas').html(retorno);
            });
        }
    }

    function save_semana(id) {
        if ($('#form-semana_curva-' + id).valid() && $('#form-semana_desecho-' + id).valid() &&
            $('#form-semana_tallos_planta_siembra-' + id).valid() && $('#form-semana_tallos_planta_poda-' + id).valid() &&
            $('#form-semana_tallos_ramo_siembra-' + id).valid() && $('#form-semana_tallos_ramo_poda-' + id).valid() &&
            $('#form-semana_poda-' + id).valid() && $('#form-semana_siembra-' + id).valid()) {
            datos = {
                _token: '{{csrf_token()}}',
                id_semana: id,
                tallos_planta_siembra: $('#tallos_planta_siembra_' + id).val(),
                tallos_planta_poda: $('#tallos_planta_poda_' + id).val(),
                tallos_ramo_siembra: $('#tallos_ramo_siembra_' + id).val(),
                tallos_ramo_poda: $('#tallos_ramo_poda_' + id).val(),
                curva: $('#curva_' + id).val(),
                desecho: $('#desecho_' + id).val(),
                semana_poda: $('#semana_poda_' + id).val(),
                semana_siembra: $('#semana_siembra_' + id).val(),
                plantas_iniciales: $('#plantas_iniciales_' + id).val(),
                densidad: $('#densidad_' + id).val(),
                porcent_bqt: $('#porcent_export_' + id).val(),
                porcent_export: $('#porcent_export_' + id).val(),
            };
            modal_quest('modal_quest_update_semana', '<div class="alert alert-info text-center">¿Desea actualizar los datos de la semana?</div>',
                '<i class="fa fa-fw fa-save"></i> Actualizar semana', true, false, '35%', function () {
                    $.LoadingOverlay('show');
                    post_jquery('{{url('semanas/update_semana')}}', datos, function () {
                        cerrar_modals();
                        //listar();
                    });
                    $.LoadingOverlay('hide');
                });
        }
    }

    select_accion(1);

    /* ============================================================== */

    function select_all() {
        list = $('.check_week');
        cants = {
            total: list.length,
            activos: 0,
        };
        for (i = 0; i < cants['total']; i++) {
            if (list[i].checked) {
                cants['activos']++;
            }
        }
        if (cants['activos'] > 0) {
            for (i = 0; i < cants['total']; i++) {
                $('#' + list[i].id).prop('checked', false);
            }
        } else {
            for (i = 0; i < cants['total']; i++) {
                $('#' + list[i].id).prop('checked', true);
            }
        }
    }

    function select_all_options(option) {
        cant = 0;
        for (i = 0; i < $('.check_week').length; i++) {
            if ($('.check_week')[i].checked) {
                cant++;
            }
        }
        if (cant > 0) {
            if (option == 1) {
                igualar_datos(true, true, true, true, true, true, true, true, true, true, true, true, true);  // todos
            }
            if (option == 2) {
                igualar_datos(true, false, false, false, false, false, false, false, false, false, false, false);   // curva
            }
            if (option == 3) {
                igualar_datos(false, true, false, false, false, false, false, false, false, false, false, false);   // desecho
            }
            if (option == 4) {
                igualar_datos(false, false, true, false, false, false, false, false, false, false, false, false);   // semana_poda
            }
            if (option == 5) {
                igualar_datos(false, false, false, true, false, false, false, false, false, false, false, false);   // semana_poda
            }
            if (option == 6) {
                igualar_datos(false, false, false, false, true, false, false, false, false, false, false, false);   // tallos_planta_siembra
            }
            if (option == 7) {
                igualar_datos(false, false, false, false, false, true, false, false, false, false, false, false);   // tallos_planta_poda
            }
            if (option == 8) {
                igualar_datos(false, false, false, false, false, false, true, false, false, false, false, false);   // tallos_ramo_siembra
            }
            if (option == 9) {
                igualar_datos(false, false, false, false, false, false, false, true, false, false, false, false);   // tallos_ramo_poda
            }
            if (option == 11) {
                igualar_datos(false, false, false, false, false, false, false, false, true, false, false, false);   // plantas_iniciales
            }
            if (option == 12) {
                igualar_datos(false, false, false, false, false, false, false, false, false, true, false, false);   // densidad
            }
            if (option == 13) {
                igualar_datos(false, false, false, false, false, false, false, false, false, false, true, false);   // porcent_bqt
            }
            if (option == 14) {
                igualar_datos(false, false, false, false, false, false, false, false, false, false, false, true);   // porcent_export
            }
            if (option == 10) {
                actualizar_proyecciones_by_semanas();   // Actualizar CICLOS (semana de cosecha, tallos x ramo, curva y desecho) según las SEMANAS
            }
            if (option == 15) {
                actualizar_siembras_by_semanas();   // Actualizar SIEMBRAS segun las SEMANAS
            }
        } else {
            alerta('<p class="text-center">Selecciona al menos 1 semanas</p>');
        }
        $('#all_options').val('');
    }

    function igualar_datos(curva, desecho, semana_poda, semana_siembra, tallos_planta_siembra, tallos_planta_poda, tallos_ramo_siembra, tallos_ramo_poda, plantas_iniciales, densidad, porcent_bqt, porcent_export) {
        datos = {
            curva: curva,
            desecho: desecho,
            semana_poda: semana_poda,
            semana_siembra: semana_siembra,
            tallos_planta_siembra: tallos_planta_siembra,
            tallos_planta_poda: tallos_planta_poda,
            tallos_ramo_siembra: tallos_ramo_siembra,
            tallos_ramo_poda: tallos_ramo_poda,
            plantas_iniciales: plantas_iniciales,
            densidad: densidad,
            porcent_bqt: porcent_bqt,
            porcent_export: porcent_export,
        };
        get_jquery('{{url('semanas/igualar_datos')}}', datos, function (retorno) {
            modal_form('modal_igualar_datos', retorno, '<i class="fa fa-fw fa-exchange"></i> Igualar todos los datos', false, true, '75%', function () {
                if ($('#form-igualar_datos').valid()) {
                    arreglo = [];
                    list = $('.check_week');
                    for (i = 0; i < list.length; i++) {
                        if (list[i].checked) {
                            arreglo.push(list[i].id.substr(6));
                        }
                    }
                    datos = {
                        _token: '{{csrf_token()}}',
                        id_planta: $('#filtro_predeterminado_planta').val(),
                        curva: $('#curva').val(),
                        desecho: $('#desecho').val(),
                        semana_siembra: $('#semana_siembra').val(),
                        semana_poda: $('#semana_poda').val(),
                        tallos_planta_siembra: $('#tallos_planta_siembra').val(),
                        tallos_planta_poda: $('#tallos_planta_poda').val(),
                        tallos_ramo_siembra: $('#tallos_ramo_siembra').val(),
                        tallos_ramo_poda: $('#tallos_ramo_poda').val(),
                        plantas_iniciales: $('#plantas_iniciales').val(),
                        densidad: $('#densidad').val(),
                        porcent_bqt: $('#porcent_bqt').val(),
                        porcent_export: $('#porcent_export').val(),
                        ids: arreglo,
                        variedades: $('#check_variedades').prop('checked'),
                    };
                    post_jquery('{{url('semanas/store_igualar_datos')}}', datos, function () {
                        listar();
                        cerrar_modals();
                    });
                }
            });
        });
    }

    function actualizar_proyecciones_by_semanas() {
        checkeados = $('.check_week');
        ids = [];
        for (i = 0; i < checkeados.length; i++) {
            if ($('#' + checkeados[i].id).prop('checked') == true)
                ids.push(checkeados[i].id.split('_')[1])
        }
        datos = {
            _token: '{{csrf_token()}}',
            semanas: ids
        };
        post_jquery('{{url('semanas/actualizar_proyecciones_by_semanas')}}', datos, function () {

        });
    }

    function actualizar_siembras_by_semanas() {
        checkeados = $('.check_week');
        ids = [];
        for (i = 0; i < checkeados.length; i++) {
            if ($('#' + checkeados[i].id).prop('checked') == true)
                ids.push(checkeados[i].id.split('_')[1])
        }
        datos = {
            _token: '{{csrf_token()}}',
            semanas: ids
        };
        post_jquery('{{url('semanas/actualizar_siembras_by_semanas')}}', datos, function () {

        });
    }

    /* =================================================== */
    function update_semanas() {
        ids_semana = $('.ids_semana');
        var data = [];
        for (i = 0; i < ids_semana.length; i++) {
            id = ids_semana[i].value;
            data.push({
                id_semana: id,
                tallos_planta_siembra: $('#tallos_planta_siembra_' + id).val(),
                tallos_planta_poda: $('#tallos_planta_poda_' + id).val(),
                tallos_ramo_siembra: $('#tallos_ramo_siembra_' + id).val(),
                tallos_ramo_poda: $('#tallos_ramo_poda_' + id).val(),
                curva: $('#curva_' + id).val(),
                desecho: $('#desecho_' + id).val(),
                semana_poda: $('#semana_poda_' + id).val(),
                semana_siembra: $('#semana_siembra_' + id).val(),
                plantas_iniciales: $('#plantas_iniciales_' + id).val(),
                densidad: $('#densidad_' + id).val(),
                porcent_bqt: $('#porcent_bqt_' + id).val(),
                porcent_export: $('#porcent_export_' + id).val(),
            });
        }
        datos = {
            _token: '{{csrf_token()}}',
            data: data,
        };
        modal_quest('modal_quest_update_semanas', '<div class="alert alert-info text-center">¿Desea actualizar los datos de las semanas?</div>',
            '<i class="fa fa-fw fa-save"></i> Actualizar semanas', true, false, '35%', function () {
                post_jquery('{{url('semanas/update_semanas')}}', datos, function () {
                    cerrar_modals();
                });
            });
    }
</script>
