<script>

function listar_pedidos(){
    let datos = {
        id_cliente: $("#id_cliente").val(),
        fecha: $("#fecha_pedidos_search").val()
    };
    get_jquery('/pedidos/crear_pedido', datos, function (retorno) {
        modal_view('modal_add_pedido', retorno, '<i class="fa fa-fw fa-plus"></i> Agregar pedido', true, false, '98%');
    });
}

function crear_pedido(id_pedido){
    let datos = {
        id_pedido
    };
    get_jquery('/pedidos/crear_pedido', datos, function (retorno) {
        modal_view('modal_add_pedido', retorno, '<i class="fa fa-fw fa-plus"></i> Agregar pedido', true, false, '98%');
    });
}

function obetener_inventario(){
    let datos= {
        id_configuracion_empresa: $('#finca_inventario').val()
    }
    
    get_jquery('/pedidos/obtener_inventario_planta', datos, function (retorno) {
        $("#tabla-inventario").html(retorno);
    },'tabla-inventario');
}

function ver_inventario_veriedad(index,id_planta,element){

    $("#icon_planta_"+index).toggleClass('fa-caret-down fa-caret-left');
    
    if($(`#tabla-inv-variedad-${index}`).length){
        $(`#tabla-inv-variedad-${index}`).remove()
        $(element).parent().removeClass('bg-info')
        return
    }

    let datos = { 
        id_planta,
        id_configuracion_empresa: $('#finca_inventario').val()
    }

    get_jquery('/pedidos/obtener_inventario_planta_variedad', datos, function (retorno) {
        $(element).parent().addClass('bg-info').after(`<tr id="tabla-inv-variedad-${index}"> <td colspan='2'>${retorno}</td> </tr>`);
    },'tabla-inventario');

}

function guardar_pedido(){

    let data_pedido = []

    $.each($("tbody#body-table-pedido tr"),function(){

        data_pedido.push({
            id_variedad: $(this).find("td:eq(0)").data('id_variedad'),
            id_clasificacion_ramo: $(this).find("td:eq(1)").data('id_clasificacion_ramo'),
            id_empaque: $(this).find("td:eq(2)").data('id_empaque'),
            tallos_x_ramo: $(this).find("td:eq(3)").html().trim(),
            longitud: $(this).find("td:eq(4)").html(),
            cantidad: $(this).find("td:eq(5)").find('input').val(),
            id_caja: $(this).find("td:eq(6)").find('select').val(),
            ramos_x_caja: $(this).find("td:eq(7)").find('input').val(),
            precio: $(this).find("td:eq(8)").find('input').val(),
            id_agencia_carga: $(this).find("td:eq(9)").find('select').val(),
            id_inventario_frio: $(this).data('id_inventario_frio')
        })

    })
    console.log(data_pedido)

    let data ={
        _token: '{{ csrf_token() }}',
        data_pedido,
        id_pedido: $('#id_pedido').val(),
        id_cliente: $("#id_cliente_pedido").val(),
        id_exportador: $("#id_exportador").val(),
        fecha: $("#fecha_de_entrega").val()
    }

    modal_quest('modal_guardar_pedido', '<div class="alert alert-info text-center"> Esta seguro que desea guardar este pedido?</div>', "<i class='fa fa-floppy-o'></i> Guardar pedido",true, false, '{{isPC() ? '50%' : ''}}', function () {
        $.LoadingOverlay('show')
        
        post_jquery('pedidos/store_pedido', data, function () {
            cerrar_modals()
        })
        $.LoadingOverlay('hide')
    })

}

</script>