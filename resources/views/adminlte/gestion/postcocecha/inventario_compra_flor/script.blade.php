<script>
    $('#vista_actual').val('inventario_compra_flor');
    listar_inventario_compra_flor();

    function listar_inventario_compra_flor() {
        datos = {
            proveedor: $('#proveedor_filtro').val(),
            planta: $('#planta_filtro').val(),
            variedad: $('#variedad_filtro').val(),
        };
        get_jquery('{{ url('inventario_compra_flor/listar_inventario_compra_flor') }}', datos, function(retorno) {
            $('#div_listado').html(retorno);
            $('#thead_acumulado').html('');
            $('#body_acumulado').html('');
            estructura_tabla('table_inventario_compra_flor');
            $('#table_inventario_compra_flor_filter label input').addClass('input-yura_default')
        });
    }

    function listar_inventario_compra_flor_acumulado(tipo, negativas = 0) {
        $('#input_last_variedad').val('');
        $('#input_last_pos_variedad').val(-1);
        $('#input_total_pos_variedad').val('');
        datos = {
            planta: $('#planta_filtro').val(),
            variedad: $('#variedad_filtro').val(),
            desde: $('#fecha_desde_filtro').val(),
            hasta: $('#fecha_hasta_filtro').val(),
            tipo: tipo,
            negativas: negativas,
            last_variedad: $('#input_last_variedad').val(),
            last_pos: $('#input_last_pos_variedad').val(),
        };
        get_jquery('{{ url('inventario_compra_flor/get_thead_acumulado') }}', datos, function(
            retorno) {
            $('#div_listado').html('');
            if (datos['last_variedad'] == '') {
                $('#thead_acumulado').html('');
                $('#body_acumulado').html('');
            }
            $('#thead_acumulado').html(retorno);
            get_jquery('{{ url('inventario_compra_flor/listar_inventario_compra_flor_acumulado') }}', datos,
                function(retorno) {
                    $('#body_acumulado').html(retorno);
                });
        });
    }

    function mostrar_mas_acumulado() {
        datos = {
            planta: $('#planta_filtro').val(),
            variedad: $('#variedad_filtro').val(),
            desde: $('#fecha_desde_filtro').val(),
            hasta: $('#fecha_hasta_filtro').val(),
            tipo: $('#input_tipo_acumulado').val(),
            negativas: $('#input_negativas_acumulado').val(),
            last_variedad: $('#input_last_variedad').val(),
            last_pos: $('#input_last_pos_variedad').val(),
        };
        get_jquery('{{ url('inventario_compra_flor/listar_inventario_compra_flor_acumulado') }}', datos,
            function(retorno) {
                $('#body_acumulado').append(retorno);
            });
    }

    function detalle_ventas(variedad, total) {
        datos = {
            _token: '{{ csrf_token() }}',
            variedad: variedad,
            total: total,
            desde: $('#fecha_desde_filtro').val(),
            hasta: $('#fecha_hasta_filtro').val(),
        }
        get_jquery('{{ url('inventario_compra_flor/detalle_ventas') }}', datos, function(retorno) {
            modal_view('modal_detalle_ventas', retorno,
                '<i class="fa fa-fw fa-plus"></i> Detalle de los Pedidos',
                true, false, '{{ isPC() ? '90%' : '' }}',
                function() {});
        });
    }

    function exportar_listado_compra_flor_acumulado(tipo, negativas) {
        $.LoadingOverlay('show');
        window.open('{{ url('inventario_compra_flor/exportar_listado_compra_flor_acumulado') }}?planta=' + $("#planta_filtro").val() +
            '&desde=' + $("#fecha_desde_filtro").val() +
            '&hasta=' + $("#fecha_hasta_filtro").val() +
            '&tipo=' + tipo +
            '&negativas=' + negativas, '_blank');
        $.LoadingOverlay('hide');
    }

    function exportar_archivo_compras() {
        $.LoadingOverlay('show');
        window.open('{{ url('inventario_compra_flor/exportar_archivo_compras') }}?proveedor=' + $(
                "#proveedor_filtro")
            .val() +
            '&planta=' + $("#planta_filtro").val() +
            '&variedad=' + $("#variedad_filtro").val() +
            '&desde=' + $("#fecha_desde_filtro").val() +
            '&hasta=' + $("#fecha_hasta_filtro").val(), '_blank');
        $.LoadingOverlay('hide');
    }

    function refrescar_ventas(variedad, pos_v) {
        datos = {
            _token: '{{ csrf_token() }}',
            variedad: variedad,
            fecha: $('#th_fecha_venta_' + pos_v).attr('data-fecha'),
        }
        $('#celda_ventas_' + variedad + '_' + pos_v).LoadingOverlay('show');
        $.post('{{ url('inventario_compra_flor/refrescar_ventas') }}', datos, function(retorno) {
            if (retorno.success) {
                mini_alerta('success', retorno.mensaje, 5000);
                $('#btn_pedidos_actuales_' + variedad + '_' + pos_v).html(retorno.pedidos_actuales);
            } else
                alerta_errores(retorno.mensaje);
        }, 'json').fail(function(retorno) {
            console.log(retorno);
            alerta_errores(retorno.responseText);
        }).always(function() {
            $('#celda_ventas_' + variedad + '_' + pos_v).LoadingOverlay('hide');
        });
    }

    function refrescar_all_ventas(pos_v) {
        data = [];
        ids_variedad = $('.ids_variedad');
        for (i = 0; i < ids_variedad.length; i++) {
            id_var = ids_variedad[i].value;
            data.push(id_var);
        }
        datos = {
            _token: '{{ csrf_token() }}',
            data: JSON.stringify(data),
            fecha: $('#th_fecha_venta_' + pos_v).attr('data-fecha'),
        }
        $.LoadingOverlay('show');
        $.post('{{ url('inventario_compra_flor/refrescar_all_ventas') }}', datos, function(retorno) {
            if (retorno.success) {
                mini_alerta('success', retorno.mensaje, 5000);
                for (x = 0; x < retorno.pedidos_actuales.length; x++)
                    $('#btn_pedidos_actuales_' + retorno.pedidos_actuales[x].variedad + '_' + pos_v).html(
                        retorno.pedidos_actuales[x].venta);
            } else
                alerta_errores(retorno.mensaje);
        }, 'json').fail(function(retorno) {
            console.log(retorno);
            alerta_errores(retorno.responseText);
        }).always(function() {
            $.LoadingOverlay('hide');
        });
    }
</script>
