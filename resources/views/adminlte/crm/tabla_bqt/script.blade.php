<script>
    $("#cliente, #variedad").select2();
    $(".select2-selection").css('height', '34px');
    filtrar_tablas();

    function filtrar_tablas() {
        datos = {
            desde_mensual: parseInt($('#desde_mensual').val()),
            desde_semanal: parseInt($('#desde_semanal').val()),
            hasta_mensual: parseInt($('#hasta_mensual').val()),
            hasta_semanal: parseInt($('#hasta_semanal').val()),
            annos: $('#annos').val(),
            cliente: $('#cliente').val(),
            receta: $('#variedad').val(),
            rango: $('#rango').val(),
            tipo_listado: $('#tipo_listado').val(),
            filtro_criterio: $('#filtro_criterio').val(),
        };
        if (datos['desde_mensual'] <= datos['hasta_mensual'] && datos['desde_semanal'] <= datos['hasta_semanal']) {
            get_jquery('{{ url('tabla_bqt/filtrar_tablas') }}', datos, function(retorno) {
                $('#div_contentido_tablas').html(retorno);
            });
        }
    }

    function exportar_tabla() {
        datos = {
            desde_mensual: parseInt($('#desde_mensual').val()),
            desde_semanal: parseInt($('#desde_semanal').val()),
            hasta_mensual: parseInt($('#hasta_mensual').val()),
            hasta_semanal: parseInt($('#hasta_semanal').val()),
            annos: $('#annos').val(),
            cliente: $('#cliente').val(),
            receta: $('#variedad').val(),
            rango: $('#rango').val(),
            tipo_listado: $('#tipo_listado').val(),
            filtro_criterio: $('#filtro_criterio').val(),
        };
        if (datos['desde_mensual'] <= datos['hasta_mensual'] && datos['desde_semanal'] <= datos['hasta_semanal']) {
            datos = JSON.stringify(datos);
            $.LoadingOverlay('show');
            window.open('{{ url('tabla_bqt/exportar_tabla') }}' + '?datos=' + datos,  '_blank');
            $.LoadingOverlay('hide');
        }
    }

    function select_anno(a) {
        text = $('#annos').val();
        if (text == '')
            $('#annos').val(a);
        else {
            arreglo = $('#annos').val().split(' - ');
            if (arreglo.includes(a)) { // año seleccionado: quitar año de la lista
                pos = arreglo.indexOf(a);
                arreglo.splice(pos, 1);

                $('#annos').val('');

                for (i = 0; i < arreglo.length; i++) {
                    text = $('#annos').val();
                    if (i == 0)
                        $('#annos').val(arreglo[i]);
                    else
                        $('#annos').val(text + ' - ' + arreglo[i]);
                }

                $('#li_anno_' + a).removeClass('bg-aqua-active');
            } else { // año no seleccionado: agregar año a la lista
                $('#annos').val(text + ' - ' + a);
                $('#li_anno_' + a).addClass('bg-aqua-active');
            }
        }
    }

    function select_mes(m, option) {
        text = m.length == 1 ? '0' + m : m;

        $('.li_mes_' + option).removeClass('bg-aqua-active');
        $('#li_mes_' + option + '_' + m).addClass('bg-aqua-active');

        $('#' + option).val(text);
    }
</script>
