<script>
    $('#vista_actual').val('resumen_ebitda');

    buscar_resumen_ebitda();

    function buscar_resumen_ebitda() {
        datos = {
            reporte: $('#filtro_predeterminado_reporte').val(),
            desde: $('#filtro_predeterminado_desde').val(),
            hasta: $('#filtro_predeterminado_hasta').val(),
        };
        get_jquery('{{url('resumen_ebitda/buscar_resumen_ebitda')}}', datos, function (retorno) {
            $('#div_listado_resumen_ebitda').html(retorno);
        });
    }

    /*-------------------------------------------*/
    function refrescar_tallos_cosechados(variedad) {
        datos = {
            variedad: variedad,
            desde: $('#filtro_predeterminado_desde').val(),
            hasta: $('#filtro_predeterminado_hasta').val(),
        };
        id_tr = variedad > 0 ? 'tr_tallos_cosechados_' + variedad : 'tr_tallos_cosechados';
        get_jquery('{{url('resumen_ebitda/refrescar_tallos_cosechados')}}', datos, function (retorno) {
            if (variedad > 0)
                $('#tr_tallos_cosechados_' + variedad).html(retorno);
            else
                $('#tr_tallos_cosechados').html(retorno);
        }, id_tr);
    }

    function refrescar_tallos_vendidos(variedad) {
        datos = {
            variedad: variedad,
            desde: $('#filtro_predeterminado_desde').val(),
            hasta: $('#filtro_predeterminado_hasta').val(),
        };
        id_tr = variedad > 0 ? 'tr_tallos_vendidos_' + variedad : 'tr_tallos_vendidos';
        get_jquery('{{url('resumen_ebitda/refrescar_tallos_vendidos')}}', datos, function (retorno) {
            if (variedad > 0)
                $('#tr_tallos_vendidos_' + variedad).html(retorno);
            else
                $('#tr_tallos_vendidos').html(retorno);
        }, id_tr);
    }

    function refrescar_dinero_ingresado(variedad) {
        datos = {
            variedad: variedad,
            desde: $('#filtro_predeterminado_desde').val(),
            hasta: $('#filtro_predeterminado_hasta').val(),
        };
        id_tr = variedad > 0 ? 'tr_dinero_ingresado_' + variedad : 'tr_dinero_ingresado';
        get_jquery('{{url('resumen_ebitda/refrescar_dinero_ingresado')}}', datos, function (retorno) {
            if (variedad > 0)
                $('#tr_dinero_ingresado_' + variedad).html(retorno);
            else
                $('#tr_dinero_ingresado').html(retorno);
        }, id_tr);
    }

    function refrescar_precio_tallo(variedad) {
        datos = {
            variedad: variedad,
            desde: $('#filtro_predeterminado_desde').val(),
            hasta: $('#filtro_predeterminado_hasta').val(),
        };
        id_tr = variedad > 0 ? 'tr_precio_tallo_' + variedad : 'tr_precio_tallo';
        get_jquery('{{url('resumen_ebitda/refrescar_precio_tallo')}}', datos, function (retorno) {
            if (variedad > 0)
                $('#tr_precio_tallo_' + variedad).html(retorno);
            else
                $('#tr_precio_tallo').html(retorno);
        }, id_tr);
    }

    function refrescar_tallos_m2(variedad) {
        datos = {
            variedad: variedad,
            desde: $('#filtro_predeterminado_desde').val(),
            hasta: $('#filtro_predeterminado_hasta').val(),
        };
        id_tr = variedad > 0 ? 'tr_tallos_m2_' + variedad : 'tr_tallos_m2';
        get_jquery('{{url('resumen_ebitda/refrescar_tallos_m2')}}', datos, function (retorno) {
            if (variedad > 0)
                $('#tr_tallos_m2_' + variedad).html(retorno);
            else
                $('#tr_tallos_m2').html(retorno);
        }, id_tr);
    }

    function refrescar_venta_m2_anno(variedad) {
        datos = {
            variedad: variedad,
            desde: $('#filtro_predeterminado_desde').val(),
            hasta: $('#filtro_predeterminado_hasta').val(),
        };
        id_tr = variedad > 0 ? 'tr_venta_m2_anno_' + variedad : 'tr_venta_m2_anno';
        get_jquery('{{url('resumen_ebitda/refrescar_venta_m2_anno')}}', datos, function (retorno) {
            if (variedad > 0)
                $('#tr_venta_m2_anno_' + variedad).html(retorno);
            else
                $('#tr_venta_m2_anno').html(retorno);
        }, id_tr);
    }
</script>