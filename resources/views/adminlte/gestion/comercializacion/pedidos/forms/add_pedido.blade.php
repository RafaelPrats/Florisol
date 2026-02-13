<table width="100%">
    <tr>
        <td>
            <div class="form-group input-group">
                <div class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                    Fecha de Entrega
                </div>
                <input type="date" id="add_fecha" required class="form-control input-yura_default text-center"
                    style="width: 100% !important;" value="{{ hoy() }}">
                <div class="input-group-addon bg-yura_dark">
                    Cliente
                </div>
                <select id="add_cliente" class="form-control input-yura_default" style="width: 100%">
                    <option value="">Seleccione</option>
                    @foreach ($clientes as $c)
                        <option value="{{ $c->id_cliente }}">{{ $c->nombre }}</option>
                    @endforeach
                </select>
                <div class="input-group-addon bg-yura_dark">
                    Finca
                </div>
                <select id="add_finca" class="form-control input-yura_default" style="width: 100%">
                    @foreach ($fincas as $f)
                        <option value="{{ $f->id_configuracion_empresa }}">{{ $f->nombre }}</option>
                    @endforeach
                </select>
            </div>
        </td>
    </tr>
</table>

<table style="width:100%">
    <tr>
        <td style="vertical-align: top; width: 50%; padding-right: 5px" id="td_inventarios">
            <div class="panel panel-success" style="margin-bottom: 0px" id="panel_inventarios">
                <div class="panel-heading" style="display: flex; justify-content: space-between; align-items: center;">
                    <div id="titulo_inventarios">
                        <b> <i class="fa fa-leaf"></i> INVENTARIO DISPONIBLE </b>
                    </div>
                    <div>
                        <div class="btn-group">
                            <button class="btn btn-xs btn-yura_default" onclick="modificar_div_inv('left')">
                                <i class="fa fa-arrow-left"></i>
                            </button>
                            <button class="btn btn-xs btn-yura_primary" onclick="modificar_div_inv('center')">
                                <i class="fa fa-compress"></i>
                            </button>
                            <button class="btn btn-xs btn-yura_default" onclick="modificar_div_inv('right')">
                                <i class="fa fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="panel-body" id="body_inventarios">
                    <div class="input-group div-compress" style="margin-bottom:10px">
                        <div class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                            Empresa
                        </div>
                        <select id="finca_inventario" class="form-control input-yura_default"
                            onchange="buscar_inventario()">
                            <option value="">Todas</option>
                            @foreach ($fincas as $f)
                                <option value="{{ $f->id_configuracion_empresa }}">{{ $f->nombre }}</option>
                            @endforeach
                        </select>
                        <div class="input-group-addon bg-yura_dark">
                            Busqueda
                        </div>
                        <input type="text" id="buscar_inventario" class="form-control text-center input-yura_default"
                            onkeyup="buscar_inventario()">
                    </div>

                    <div id="div_inventario" style="height:300px; overflow:auto">
                    </div>
                </div>
            </div>
        </td>
        <td style="vertical-align: top; padding-left: 5px" id="td_seleccionados">
            <div class="panel panel-success" style="margin-bottom:0px" id="panel_seleccionados">
                <div class="panel-heading" style="display: flex; justify-content: space-between; align-items: center;">
                    <div id="titulo_seleccionados">
                        <b> <i class="fa fa-th"></i> CONTENIDO DEL PEDIDO</b>
                    </div>
                    <div class="btn-group">
                        <button type="button" class="btn btn-yura_default btn-xs dropdown-toggle"
                            data-toggle="dropdown" aria-expanded="false">
                            Acciones <span class="fa fa-caret-down"></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-right sombra_pequeña">
                            <li>
                                <a href="javascript:void(0)" onclick="quitar_detalles()">
                                    <i class="fa fa-fw fa-gift"></i> Unificar detalles
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0)" onclick="quitar_detalles()">
                                    <i class="fa fa-fw fa-trash"></i> Quitar detalles
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="panel-body" style="height: 373px; overflow:auto" id="body_seleccionados">
                    <div id="droppable" style="height: 100%; display:flex; align-items: center; justify-content: center"
                        class="ui-droppable">
                        <div style="color:silver; font-size:16px" id="mensaje-drop">
                            <b>AGREGUE LOS PRODUCTOS AL PEDIDO</b>
                        </div>
                    </div>
                    <div id="div_seleccionados" class="hidden" style="height: 100%; overflow: auto">
                        <table class="table-bordered" style="width: 100%; border:1px solid #9d9d9d"
                            id="table_seleccionados">
                            <tr class="tr_fija_top_0">
                                <th class="text-center th_yura_green">
                                    <input type="checkbox" id="check_all_selec" class="mouse-hand"
                                        onclick="check_all_selec()">
                                </th>
                                <th class="text-center th_yura_green" style="width: 60px">
                                    <div style="width: 60px">
                                        Cajas
                                    </div>
                                </th>
                                <th class="text-center th_yura_green">
                                    <div style="width: 90px">
                                        Finca
                                    </div>
                                </th>
                                <th class="text-center th_yura_green">
                                    <div style="width: 90px">
                                        Origen
                                    </div>
                                </th>
                                <th class="text-center th_yura_green">
                                    <div style="width: 70px">
                                        Planta
                                    </div>
                                </th>
                                <th class="text-center th_yura_green">
                                    <div style="width: 110px">
                                        Variedad
                                    </div>
                                </th>
                                <th class="text-center padding_lateral_5 th_yura_green">
                                    Long.
                                </th>
                                <th class="text-center padding_lateral_5 th_yura_green">
                                    Tallos
                                </th>
                                <th class="text-center padding_lateral_5 th_yura_green">
                                    Ramos
                                </th>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </td>
    </tr>
</table>

<script>
    buscar_inventario();
    cant_seleccionados = 0;

    function buscar_inventario() {
        datos = {
            finca: $('#finca_inventario').val(),
            buscar: $('#buscar_inventario').val(),
        }
        get_jquery('{{ url('pedidos/buscar_inventario') }}', datos, function(retorno) {
            $('#div_inventario').html(retorno);
        }, 'div_inventario');
    }

    function modificar_div_inv(par) {
        if (par == 'left') {
            $('#td_inventarios').css('width', '10%');
            $('#titulo_inventarios').addClass('hidden');
            $('#body_inventarios').addClass('hidden');
            $('#titulo_seleccionados').removeClass('hidden');
            $('#body_seleccionados').removeClass('hidden');
        }
        if (par == 'center') {
            $('#td_inventarios').css('width', '50%');
            $('#titulo_inventarios').removeClass('hidden');
            $('#body_inventarios').removeClass('hidden');
            $('#titulo_seleccionados').removeClass('hidden');
            $('#body_seleccionados').removeClass('hidden');
        }
        if (par == 'right') {
            $('#td_inventarios').css('width', '90%');
            $('#titulo_inventarios').removeClass('hidden');
            $('#body_inventarios').removeClass('hidden');
            $('#titulo_seleccionados').addClass('hidden');
            $('#body_seleccionados').addClass('hidden');
        }
    }

    function check_all_selec() {
        $('.check_selec').prop('checked', $('#check_all_selec').prop('checked'));
    }

    function quitar_detalles() {
        for (i = 1; i <= cant_seleccionados; i++) {
            if ($('#check_selec_' + i).prop('checked') == true) {
                $('#tr_seleccionado_' + i).remove();
            }
        }
    }
</script>
