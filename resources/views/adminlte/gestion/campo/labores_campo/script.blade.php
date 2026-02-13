<script>
    function listar_labores() {
        datos = {
            labor: $('#filtro_labor').val(),
            semana: $('#filtro_semana').val(),
            sector: $('#filtro_sector').val(),
        };
        if (datos['labor'] != '') {
            get_jquery('{{ url('ingreso_labores/listar_labores') }}', datos, function(retorno) {
                $('#div_listado_ciclos').html(retorno);
                estructura_tabla('table_labores', false, false);
                $('#table_labores_filter').addClass('hidden');
            });
        }
    }

    function seleccionar_mezcla(mezcla) {
        array_pos = $('.pos');
        data = [];
        for (i = 0; i < array_pos.length; i++) {
            pos = array_pos[i].value;
            if ($('#check_ciclo_' + pos).prop('checked') == true) {
                data.push({
                    ciclo: $('#id_ciclo_' + pos).val(),
                    variedad: $('#id_variedad_' + pos).val(),
                    app_campo: $('#id_aplicacion_campo_' + pos).val(),
                    aplicacion: $('#id_aplicacion_' + pos).val(),
                    fecha: $('#fecha_' + pos).val(),
                    repeticion: $('#repeticion_' + pos).val(),
                    camas: $('#camas_' + pos).val(),
                    litros_x_cama: $('#litros_x_cama_' + pos).val(),
                });
            }
        }
        datos = {
            mezcla: mezcla,
            data: data,
        };
        get_jquery('{{ url('ingreso_labores/seleccionar_mezcla') }}', datos, function(retorno) {
            $('#div_aplicar_mezcla').html(retorno);
        })
    }

    function seleccionar_tipo_labor() {
        datos = {
            _token: '{{ csrf_token() }}',
            tipo: $('#filtro_tipo_labor').val(),
        };
        if (datos['tipo'] != '')
            $.post('{{ url('ingreso_labores/seleccionar_tipo_labor') }}', datos, function(retorno) {
                $('#filtro_labor').html('');
                $('#filtro_labor').append('<option value="">Seleccione...</option>');
                for (i = 0; i < retorno.labores.length; i++) {
                    $('#filtro_labor').append('<option value="' + retorno.labores[i].id_aplicacion_matriz + '">' +
                        retorno.labores[i].nombre + '</option>');
                }
            }, 'json');
    }

    function calcular_hombres_dia(pos) {
        plantas = $('#plantas_' + pos).val();
        horas_dia = $('#horas_dia_' + pos).val();
        if ($('.dosis_' + pos).length > 0 && plantas > 0 && horas_dia > 0) {
            dosis = $('.dosis_' + pos)[0].value;
            hombres_dia = dosis > 0 && horas_dia > 0 ? Math.round((plantas / dosis) / horas_dia) : 0;
            $('#hombres_dia_' + pos).val(hombres_dia);
            $('#span_hombres_dia_' + pos).html(hombres_dia);

            calcular_horas_necesarias(pos);
        }
    }

    function calcular_horas_necesarias(pos) {
        plantas = $('#plantas_' + pos).val();
        hombres = $('#hombres_dia_' + pos).val();
        if ($('.dosis_' + pos).length > 0 && hombres > 0) {
            dosis = $('.dosis_' + pos)[0].value;
            horas_necesarias = Math.round((plantas / dosis) / hombres);
            $('#horas_necesarias_' + pos).val(horas_necesarias);
            $('#span_horas_necesarias_' + pos).html(horas_necesarias);
        }
        calcular_totales_desbrote();
    }

    function calcular_totales_desbrote() {
        total_plantas = 0;
        prom_horas_dia = 0;
        total_horas_necesarias = 0;
        cantidad = 0;
        list_pos = $('.pos');
        for (i = 0; i < list_pos.length; i++) {
            pos = list_pos[i].value;
            if ($('#id_aplicacion_campo_' + pos).val() > 0) {
                total_plantas += parseInt($('#plantas_' + pos).val());
                prom_horas_dia += parseInt($('#horas_dia_' + pos).val());
                total_horas_necesarias += parseInt($('#horas_necesarias_' + pos).val());
                cantidad++;
            }
        }
        $('#th_total_plantas').html(total_plantas);
        $('#th_prom_horas_dia').html(cantidad > 0 ? (Math.round((prom_horas_dia / cantidad) * 100) / 100) : 0);
        $('#th_total_horas_necesarias').html(total_horas_necesarias);
    }

    function calcular_litros(densidad, cc_x_planta, litros) {
        densidad = parseFloat($('#' + densidad).val());
        cc_x_planta = parseFloat($('#' + cc_x_planta).val());
        litros_x_cama = densidad * 45 * (cc_x_planta / 1000);
        $('#' + litros).val(Math.round(litros_x_cama * 100) / 100);
    }
</script>
