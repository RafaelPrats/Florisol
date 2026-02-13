<table class="table-bordered w-100">
    <thead>
        <tr style="background: #18a7ed;color: white;">
            <th style="vertical-align: middle"{{--  class="fixed-column-left" --}}>Tipo</th>
            <th style="vertical-align: middle;text-align:center">Peso</th>
            <th style="vertical-align: middle">Present.</th>
            <th style="vertical-align: middle;text-align:center">T x R</th>
            <th style="vertical-align: middle;text-align:center">Long.</th>
            <th style="vertical-align: middle;text-align:center">Edad</th>
            <th style="vertical-align: middle">Cant.</th>
            <th style="vertical-align: middle" class="fixed-column-right"></th>
        </tr>
    </thead>
    <tbody>
        @forelse ($invFrio as $inv)
            <tr style="font-size: 13px;cursor:pointer" class="especificacion_pedido ui-widget-content" data-id_inventario_frio="{{$inv->id_inventario_frio}}">
                <td style="vertical-align: middle" data-id_variedad="{{$inv->id_variedad}}" {{-- class="fixed-column-left" --}}>
                    <div title="{{$inv->variedad}}" data-toggle="tooltip" data-placement="top">
                        {{$inv->siglas}}
                    </div>
                </td>
                <td style="vertical-align: middle;text-align:center" data-id_clasificacion_ramo="{{$inv->id_clasificacion_ramo}}">
                    {{$inv->peso}}
                </td>
                <td style="vertical-align: middle" data-id_empaque="{{$inv->id_empaque}}">{{$inv->presentacion}}</td>
                <td style="vertical-align: middle;text-align:center">{{$inv->tallos_x_ramo}}</td>
                <td style="vertical-align: middle;text-align:center">{{$inv->longitud}}</td>
                <td style="vertical-align: middle;text-align:center" id="td-edad">{{$inv->edad}}</td>
                <td style="vertical-align: middle;text-align:center" id="td-cantidad">{{$inv->disponibles}}</td>
                <td style="vertical-align: middle;text-align:center" id="td-btn-action" {{-- class="fixed-column-right" --}}>
                    <button class="btn btn-xs btn-yura_primary" onclick="agregar_especificacion(this)">
                        <i class="fa fa-fw  fa-caret-right"></i>
                    </button>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="alert alert-info" style="vertical-align: middle;text-align:center">
                    No existe inventario disponible
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

<script>
    $(function () { $('[data-toggle="tooltip"]').tooltip() })

    $(".especificacion_pedido").draggable({
        helper: 'clone',
        zIndex: 9999
    })

    function agregar_especificacion(element){

        let tr_especificacion = $(element).parent().parent()

        $.get('/pedidos/obtener_data_pedido', {}, function (res) {
          
            const { cajas } = res
            $("#tabla_pedido").removeClass("hide")
            $("#mensaje-drop").addClass("hide")
            $("#droppable").css({'display':'inherit'})

            let cantidad = tr_especificacion.find("td#td-cantidad").text().trim()

            let select_cajas = ''
            let agencias_carga = ''

            cajas.forEach(element => {
                select_cajas+=`<option value="${element.id_caja}">${element.nombre}</option>`
            })

            cajas.forEach(element => {
                agencias_carga+=`<option value="${element.id_caja}">${element.nombre}</option>`
            })

            let contenido_dinamico= `
                <td style="width:80px">
                    <input value="${cantidad}" placeholder="Cantidad" style="width:80px;height: 21px;text-align:center">
                </td>
                <td style="width:150px">
                    <select style="width:150px"> ${select_cajas} </select>
                </td>
                <td style="width:60px">
                    <input value="" placeholder="Cantidad" style="width:60px;height: 21px;text-align:center">
                </td>
                <td style="width:60px">
                    <input value="" placeholder="Cantidad" style="width:60px;height: 21px;text-align:center">
                </td>
                <td style="width:120px">
                    <select style="width:120px"> ${agencias_carga} </select>
                </td>
                <td class="text-center" style="width:60px">
                    <button class="btn btn-xs btn-yura_danger" onclick="elimiar_especificacion(this)">
                        <i class="fa fa-trash"></i>
                    </button>
                </td>
            `
            
            let html = $(`<tr>${tr_especificacion.html()}</tr>`).removeClass('especificacion_pedido').css('position','inherit').find("td#td-btn-action, td#td-edad, td#td-cantidad").remove().end().append(contenido_dinamico)
            console.log(html)
            $("#body-table-pedido").append( html )

        })

    }

</script>

<style>
    .fixed-column-right{
        position: sticky;
        right: 0;
        z-index: 8;
        /* background-color: gray!important; */
    }
    .fixed-column-left{
        position: sticky;
        left: 0;
        z-index: 8;
        /* background-color: gray!important; */
    }
</style>