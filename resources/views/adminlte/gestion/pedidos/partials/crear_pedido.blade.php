<div class="row">
    <div class="col-md-3">
        <div class="input-group" style="margin-bottom:10px">
            <div class="input-group-addon bg-yura_dark ">
                <i class="fa fa-calendar"></i> Fecha de entrega 
            </div>
            <input type="date" id="fecha_de_entrega" name="fecha_de_entrega" value="{{now()->toDateString()}}" class="form-control" required>
        </div>        
    </div>
    <div class="col-md-3">
        <div class="input-group" style="margin-bottom:10px">
            <div class="input-group-addon bg-yura_dark ">
                <i class="fa fa-user-circle-o"></i> Cliente 
            </div>
            <select class="form-control" id="id_cliente_pedido" name="id_cliente_pedido" style="background:transparent" required>
                <option disabled selected> Seleccione</option>
                @foreach($clientes as $cliente)
                    <option value="{{$cliente->id_cliente}}"> {{$cliente->nombre}} </option>
                @endforeach
            </select>
            <input type="hidden" id="iva_cliente" name="iva_cliente" value="">
        </div>        
    </div>
    <div class="col-md-3" >
        <div class="input-group" style="margin-bottom:10px">
            <div class="input-group-addon bg-yura_dark ">
                <i class="fa fa-plane"></i> Exportador:
            </div>
            <select class="form-control" id="id_exportador" name="id_exportador" title="Seleccione un empresa para facturar los pedidos">
                @foreach($exportadores as $exp)
                    <option value="{{$exp->id_exportador}}"> {{$exp->nombre}} </option>
                @endforeach
            </select>
        </div>
        
    </div>
</div>
<div class="row" style="margin-top:20px">
    <div class="col-md-6" id="div-inv">
        <div class="panel panel-success" style="margin-bottom:0px">
            <div class="panel-heading" style="display: flex;justify-content: space-between;align-items: center;">
                <div class="div-compress">
                    <b> <i class="fa fa-leaf"></i> INVENTARIO DISPONIBLE </b>
                </div>
                <div>
                    <button class="btn btn-xs btn-yura_primary" onclick="modificar_div_inv()">
                        <i class="fa fa-compress" id="icon-compress-expand"></i>
                    </button>
                </div>
            </div>
            <div class="panel-body">
                <div class="input-group div-compress" style="margin-bottom:10px">
                    <div class="input-group-addon bg-yura_dark ">
                        Empresa
                    </div>
                    <select id="finca_inventario" class="form-control input-yura_default" onchange="obetener_inventario()">
                        @foreach ($empresas as $emp)
                            <option value="{{$emp->id_configuracion_empresa}}">{{$emp->nombre}}</option>
                        @endforeach
                    </select>
                    <div class="input-group-addon bg-yura_dark">
                        Busqueda
                    </div>
                    <input type="text" id="buscar_inventario" class="form-control text-center" >
                </div>
                {{-- <div class="form-group input-group" style="padding: 0px">
                    <input type="text" class="form-control" placeholder="Búsqueda" id="buscar_inventario" name="buscar_inventario">
                    <span class="input-group-btn">
                        <button class="btn btn-default">
                            <i class="fa fa-fw fa-search" style="color: #0c0c0c"></i> <em id="title_btn_buscar"></em>
                        </button>
                    </span>
                </div> --}}
                <div class="table-responsive " id="tabla-inventario" style="height:280px;overflow:auto">          
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6" id="div-pedido">
        <div class="panel panel-success" style="margin-bottom:0px">
            <div class="panel-heading" style="display: flex;justify-content: space-between;align-items: center;">
                <div>
                    <b> <i class="fa fa-th"></i> ORDENAR PEDIDO</b>
                </div>
                <div>
                    <button class="btn btn-xs btn-success" onclick="guardar_pedido()">
                        <i class="fa fa-floppy-o"></i> GUARDAR PEDIDO
                    </button>
                </div>
            </div>
            <div class="panel-body" style="height:355px;overflow:auto">
                <div id="droppable" style="height: 100%;display:flex;align-items: center;justify-content: center">
                    <div id="tabla_pedido" class="hide">
                        <table class="table-bordered w-100">
                            <thead>
                                <tr>
                                    <th>Tipo</th>
                                    <th>Peso</th>
                                    <th>Present.</th>
                                    <th>TxR</th>
                                    <th>Long.</th>
                                    <th>Cant.</th>
                                    <th style="width:150px">Caja</th>
                                    <th style="width:60px">RxC</th>
                                    <th style="width:60px">Precio</th>
                                    <th style="width:120px">A. carga</th>
                                    <th class="text-center">Opciones</th>
                                </tr>
                            </thead>
                            <tbody id="body-table-pedido"></tbody>
                            </thead>
                        </table>
                    </div>
                    <div style="color:silver;font-size:16px" id="mensaje-drop">
                        <b>SUELTE LOS PRODUCTOS A ORDENAR EN ESTA ZONA</b>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>

    $("#buscar_inventario").on("keyup", function ()  {      
        var value = $("#buscar_inventario").val().toLowerCase();
        $("div#tabla-inventario tbody tr").filter(function () {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        })
    })

    setTimeout(() => { obetener_inventario() }, 500)
    
    $( "#body-table-pedido").sortable()

    $("#droppable").droppable({
      accept: ".especificacion_pedido",
      drop: function( event, ui ) {

        $.get('/pedidos/obtener_data_pedido', {}, function (res) {
          
            const { cajas, agencias_carga } = res
            $("#tabla_pedido").removeClass("hide")
            $("#mensaje-drop").addClass("hide")
            $("#droppable").css({'display':'inherit'})

            let cantidad= $(ui.helper).find('td#td-cantidad').text().trim()

            let select_cajas = ''
            let select_agencias_carga = ''

            cajas.forEach(element => {
                select_cajas+=`<option value="${element.id_caja}">${element.nombre}</option>`
            })

            agencias_carga.forEach(element => {
                select_agencias_carga+=`<option value="${element.id_agencia_carga}">${element.nombre}</option>`
            })

            let contenido_dinamico= `
                <td style="width:80px">
                    <input type="number" value="${cantidad}" placeholder="Cantidad" style="width:80px;height: 21px;text-align:center">
                </td>
                <td style="width:150px">
                    <select style="width:150px"> ${select_cajas} </select>
                </td>
                <td style="width:60px">
                    <input type="number" value="" placeholder="Cantidad" style="width:60px;height: 21px;text-align:center">
                </td>
                <td style="width:60px">
                    <input type="number" value="" placeholder="Cantidad" style="width:60px;height: 21px;text-align:center">
                </td>
                <td style="width:120px">
                    <select style="width:120px"> ${select_agencias_carga} </select>
                </td>
                <td class="text-center" style="width:60px">
                    <button class="btn btn-xs btn-yura_danger" onclick="elimiar_especificacion(this)">
                        <i class="fa fa-trash"></i>
                    </button>
                </td>
            `

            let html = $(ui.helper).removeClass('especificacion_pedido').css('position','inherit').find("td#td-btn-action, td#td-edad, td#td-cantidad").remove().end().append(contenido_dinamico).clone()
            $("#body-table-pedido").append(html)

        })
                    
      }
    })

    function modificar_div_inv(){

        $("#icon-compress-expand").toggleClass('fa-compress fa-expand')
       
        $("#div-inv").toggleClass('col-md-6 col-md-1')
        $("#div-pedido").toggleClass('col-md-6 col-md-11')

        if($("#icon-compress-expand").hasClass('fa-expand')){
            $(".div-compress").addClass('hide')
        }else{
            $(".div-compress").removeClass('hide')
        }
    }

    function elimiar_especificacion(element){

        $(element).parent().parent().remove()
        
        if(!$("#body-table-pedido tr").length){

            $("#mensaje-drop").removeClass("hide")
            $("#tabla_pedido").addClass("hide")
            $("#droppable").css({'display':'flex'})
            
        }

    }
</script>