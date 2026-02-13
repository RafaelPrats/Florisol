<script>
    var cant_forms = 1;
    set_max_today($('#fecha_search'))
    listar_ingresos_bqt();

    function listar_ingresos_bqt() {
        datos = {
            fecha: $('#fecha_search').val(),
            planta: $('#filtro_predeterminado_planta').val(),
            variedad: $('#filtro_predeterminado_variedad').val(),
            finca: $('#filtro_predeterminado_empresa').val(),
        };
        if (datos['fecha'] != '')
            get_jquery('{{url('ingreso_bouquetera/listar_ingresos_bqt')}}', datos, function (retorno) {
                $('#div_listado_ingreso_bqt').html(retorno);
                estructura_tabla('table_listado_ingresos_bqt', false, false);
                $('#table_listado_ingresos_bqt_filter').remove()
            });
    }

    function add_form_bqt() {
        cant_forms++;
        select_fincas = $('#add_finca_1').html();
        select_plantas = $('#add_planta_1').html();
        $('#table_add_bqt').append('<tr id="tr_ingreso_bqt_' + cant_forms + '">' +
            '        <td class="text-center" style="border-color: #9d9d9d">' +
            '            <select id="add_finca_' + cant_forms + '" style="width: 100%">' +
            select_fincas +
            '            </select>' +
            '        </td>' +
            '        <td class="text-center" style="border-color: #9d9d9d">' +
            '            <select id="add_planta_' + cant_forms + '" style="width: 100%"' +
            '                    onchange="select_planta_add_bqt(' + cant_forms + ')">' +
            select_plantas +
            '            </select>' +
            '        </td>' +
            '        <td class="text-center" style="border-color: #9d9d9d">' +
            '            <select id="add_variedad_' + cant_forms + '" style="width: 100%">' +
            '            </select>' +
            '        </td>' +
            '        <td class="text-center" style="border-color: #9d9d9d">' +
            '            <input type="number" id="add_precio_' + cant_forms + '" style="width: 100%" class="text-center" placeholder="$">' +
            '        </td>' +
            '        <td class="text-center" style="border-color: #9d9d9d">' +
            '            <input type="number" id="add_tallos_' + cant_forms + '" style="width: 100%" class="text-center" placeholder="#">' +
            '        </td>' +
            '        <td class="text-center" style="border-color: #9d9d9d">' +
            '            <input type="number" id="add_exportada_' + cant_forms + '" style="width: 100%" class="text-center" placeholder="#">' +
            '        </td>' +
            '</tr>');
    }

    function delete_form_bqt() {
        for (i = 2; i <= cant_forms; i++) {
            $('#tr_ingreso_bqt_' + i).remove();
        }
        $('#add_finca_1').val('');
        $('#add_planta_1').val('');
        $('#add_tallos_1').val('');
        $('#add_exportada_').val('');
        select_planta_add_bqt(1);
        cant_forms = 1;
    }

    function select_planta_add_bqt(pos) {
        select_planta($('#add_planta_' + pos).val(), 'add_variedad_' + pos, 'add_variedad_' + pos);
    }

    function store_bqt() {
        data = [];
        for (i = 1; i <= cant_forms; i++) {
            if ($('#add_finca_' + i).val() != '' && $('#add_planta_' + i).val() != '' && $('#add_variedad_' + i).val() != '' && $('#add_tallos_' + i).val() > 0) {
                data.push({
                    finca: $('#add_finca_' + i).val(),
                    planta: $('#add_planta_' + i).val(),
                    variedad: $('#add_variedad_' + i).val(),
                    precio: $('#add_precio_' + i).val(),
                    tallos: $('#add_tallos_' + i).val(),
                    exportada: $('#add_exportada_' + i).val(),
                });
            }
        }
        if (data.length > 0) {
            datos = {
                _token: '{{csrf_token()}}',
                fecha: $('#fecha_search').val(),
                data: data,
            };
            post_jquery('{{url('ingreso_bouquetera/store_bqt')}}', datos, function () {
                listar_ingresos_bqt();
                delete_form_bqt();
            });
        }
    }

    function importar_file_bqt() {
        get_jquery('{{url('ingreso_bouquetera/importar_file_bqt')}}', {}, function (retorno) {
            modal_view('modal-view_importar_file_bqt', retorno, '<i class="fa fa-fw fa-upload"></i> Importar archivo de bouquetera', true, false, '90%');
        });
    }

    function mostrar_formulario() {
        $('#div_listado').removeClass('col-md-12');
        $('#div_listado').addClass('col-md-5');
        $('#div_formulario').toggleClass('hidden');
        $('#btn_mostrar_formulario').toggleClass('hidden');
        $('#btn_ocultar_formulario').toggleClass('hidden');
    }

    function ocultar_formulario() {
        $('#div_listado').removeClass('col-md-5');
        $('#div_listado').addClass('col-md-12');
        $('#div_formulario').toggleClass('hidden');
        $('#btn_mostrar_formulario').toggleClass('hidden');
        $('#btn_ocultar_formulario').toggleClass('hidden');
    }

    function delete_registros() {
        modal_quest('modal_quest-delete_registros',
            '<div class="alert alert-warning text-center">¿Desea <strong>ELIMINAR</strong> los registros en el rango de fecha seleccionado?</div>',
            '<i class="fa fa-fw fa-exclamation-triangle"></i> Mensaje de confirmación', true, false, '', function () {
                datos = {
                    _token: '{{csrf_token()}}',
                    desde: $('#del_desde').val(),
                    hasta: $('#del_hasta').val(),
                };
                post_jquery('{{url('ingreso_bouquetera/delete_registros')}}', datos, function () {
                    listar_ingresos_bqt();
                    cerrar_modals();
                });
            });
    }
</script>