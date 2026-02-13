<script>
    $("select#cliente").select2();
    listar_graficas();
    listar_ranking();

    function listar_graficas() {
        datos = {
            tipo_grafica: $('#tipo_grafica').val(),
            rango: $('#rango').val(),
            desde: $('#filtro_desde').val(),
            hasta: $('#filtro_hasta').val(),
            cliente: $('#cliente').val(),
            planta: $('#planta').val(),
            criterio: $('#criterio').val(),
            annos: $('#annos').val(),
        };

        get_jquery('{{ url('crm_ventas/listar_graficas') }}', datos, function(retorno) {
            $('#div_graficas').html(retorno);
        }, 'div_graficas');
    }

    function listar_ranking() {
        datos = {
            tipo_ranking: $('#tipo_ranking').val(),
            criterio_ranking: $('#criterio_ranking').val(),
            desde: $('#filtro_desde').val(),
            hasta: $('#filtro_hasta').val(),
        };

        get_jquery('{{ url('crm_ventas/listar_ranking') }}', datos, function(retorno) {
            $('#div_ranking').html(retorno);
        }, 'div_master_ranking');
    }

    function select_anno(a) {
        text = $('#annos').val();
        if (text == '') {
            $('#annos').val(a);
            $('#li_anno_' + a).addClass('bg-aqua-active');
        } else {
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
</script>
