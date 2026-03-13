@if ($opcion == 1 || $opcion == 2)
    <div style="overflow-x: scroll">
        <table class="table-bordered" style="width: 100%; border-color: #9d9d9d">
            <thead>
                <tr>
                    <th class="text-center bg-yura_dark">
                        @if ($opcion == 1)
                            DIA SEMANA
                        @else
                            DIA MES
                        @endif
                    </th>
                    <th class="text-center th_yura_green">
                        DESDE
                    </th>
                    <th class="text-center th_yura_green">
                        HASTA
                    </th>
                    @if ($opcion == 1)
                        <th class="text-center th_yura_green">
                            INTERVALO
                        </th>
                        <th class="text-center th_yura_green">
                            RENOVAR
                        </th>
                    @endif
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="border-color: #9d9d9d">
                        @if ($opcion == 1)
                            <input type="hidden" id="opcion_pedido_fijo" value="{{ $opcion }}">
                            <select id="dia_semana" name="dia_semana" class="form-control" required style="width: 100%">
                                <option selected> Seleccione</option>
                                <option value="1">Lunes</option>
                                <option value="2">Martes</option>
                                <option value="3">Miercoles</option>
                                <option value="4">Jueves</option>
                                <option value="5">Viernes</option>
                                <option value="6">Sabado</option>
                                <option value="7">Domingo</option>
                            </select>
                        @elseif($opcion == 2)
                            <input type="hidden" id="opcion_pedido_fijo" value="{{ $opcion }}">
                            <select id="dia_mes" name="dia_mes" class="form-control" required style="width: 100%">
                                <option selected> Seleccione</option>
                                @for ($i = 1; $i < 32; $i++)
                                    <option value="{{ $i }}">{{ $i }} </option>
                                @endfor
                            </select>
                        @endif
                    </td>
                    <td>
                        <input type="date" id="fecha_desde_pedido_fijo" style="width: 100%"
                            onchange="verificar_intervalo_fecha()" name="fecha_desde_pedido_fijo"
                            class="text-center form-control" required>
                    </td>
                    <td>
                        <input type="date" id="fecha_hasta_pedido_fijo" name="fecha_hasta_pedido_fijo"
                            style="width: 100%" class="text-center form-control" required>
                    </td>
                    @if ($opcion == 1)
                        <td>
                            <select id="intervalo_pedido_fijo" style="width: 100%" class="text-center form-control">
                                <option value="1">Semanal</option>
                                <option value="2">Quincenal</option>
                            </select>
                        </td>
                        <td class="text-center">
                            <input type="checkbox" id="renovar_pedido_fijo" checked>
                        </td>
                    @endif
                </tr>
            </tbody>
        </table>
    </div>
@else
    <table class="table table-bordered" style="width: 100%; border-color: #9d9d9d;"
        id="table_content_fechas_pedidos_personalizados">
        <thead>
            <tr>
                <th class="text-center th_yura_green" style="width: 90%">
                    FECHAS PERSONALIZADAS
                </th>
                <th class="text-center th_yura_green">
                    <div class="btn-group">
                        <button type="button" onclick="add_fechas_pedido_fijo_personalizado()" title="Agregar fecha"
                            class="btn btn-yura_dark" id="btn_add_fechas_pedido_fijo_personalizado">
                            <i class="fa fa-plus" aria-hidden="true"></i> Agregar fecha
                        </button>
                    </div>
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="2" style="border-color: #9d9d9d">
                    <input type="hidden" id="opcion_pedido_fijo" value="{{ $opcion }}">
                    <div class="row" id="td_fechas_pedido_fijo_personalizado">
                        <div class="col-md-4" id="div_1">
                            <div class="input-group" style="min-width: 180px">
                                <span class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
                                    Fecha 1
                                </span>
                                <input type="date" id="fecha_desde_pedido_fijo_1" name="fecha_desde_pedido_fijo_1"
                                    class="form-control text-center input-yura_default" style="width: 100%" required>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
@endif

<script>
    form_cant_fechas_orden_fija = 1;

    function verificar_intervalo_fecha() {

        if ($("#fecha_desde_pedido_fijo").val() != '') {
            $("#intervalo").prop('disabled', false)
            $("#fecha_hasta_pedido_fijo").val(moment($("#fecha_desde_pedido_fijo").val()).add(3, 'months').format(
                'YYYY-MM-DD'))
        }

        var fechaDesde = moment($("#fecha_desde_pedido_fijo").val());
        var fechaHasta = moment($("#fecha_hasta_pedido_fijo").val());

        var diferenciaDias = fechaHasta.diff(fechaDesde, 'days')

        var fechaFormateada = $('#fecha_desde_pedido_fijo').val().replace('/-/g', '/');
        let date = new Date(fechaFormateada);

        var p = 0;
        for (var x = 0; x < diferenciaDias + 2; x++) {
            var fechas = (date.getMonth() + 1) + "/" + date.getDate() + "/" + date.getFullYear();
            date.setDate(date.getDate() + 1);
            var d = new Date(fechas);
            if (d.getDay() === parseInt($("#dia_semana").val().trim())) {
                p++
            }
        }
        var arrSemanas = [];
        if (p > 0) {
            for (var i = 0; i < p; i++) {
                var plu = '';
                (i > 0) ? plu = 's': plu;
                arrSemanas.push([(i + 1) + ' Semana' + plu]);
            }
        }
        pushSemanas(1, arrSemanas);

    }
</script>
