<legend style="margin-bottom: 5px"></legend>

<div class="nav-tabs-custom">
    <ul class="nav nav-pills nav-justified">
        <li class="active li-default" id="li-precio">
            <a href="javascript:void(0)" onclick="mostrar_ocultar_tab('precio')">Precio x Tallo</a>
        </li>
        <li class="li-default" id="li-porcentaje_cumplimiento">
            <a href="javascript:void(0)" onclick="mostrar_ocultar_tab('porcentaje_cumplimiento')">% Cumplimiento</a>
        </li>
        <li class="li-default" id="li-porcentaje_nacional">
            <a href="javascript:void(0)" onclick="mostrar_ocultar_tab('porcentaje_nacional')">% Nacional</a>
        </li>
        <li class="li-default" id="li-tallos_m2">
            <a href="javascript:void(0)" onclick="mostrar_ocultar_tab('tallos_m2')">Tallos x m<sup>2</sup></a>
        </li>
        <li class="li-default" id="li-ciclo">
            <a href="javascript:void(0)" onclick="mostrar_ocultar_tab('ciclo')">Ciclo</a>
        </li>
    </ul>
    <div>
        <div class="default" id="tab-precio">
            <div class="row">
                <div class="col-md-3 text-center">
                    <canvas id="canvas_precio"></canvas>
                    <strong>
                        ${{number_format($precio_x_tallo, 2)}}
                    </strong>
                </div>
                <div class="col-md-9">
                    <canvas id="chart_precio" width="100%" height="25" style="margin-top: 5px"></canvas>
                </div>
            </div>
        </div>
        <div class="default ocultar" id="tab-porcentaje_cumplimiento">
            <div class="row">
                <div class="col-md-3 text-center">
                    <canvas id="canvas_porcentaje_cumplimiento"></canvas>
                    <strong>
                        {{round($porcentaje_cumplimiento, 2)}}%
                    </strong>
                </div>
                <div class="col-md-9">
                    <canvas id="chart_porcentaje_cumplimiento" width="100%" height="25" style="margin-top: 5px"></canvas>
                </div>
            </div>
        </div>
        <div class="default ocultar" id="tab-porcentaje_nacional">
            <canvas id="chart_porcentaje_nacional" width="100%" height="25" style="margin-top: 5px"></canvas>
        </div>
        <div class="default ocultar" id="tab-tallos_m2">
            <div class="row">
                <div class="col-md-3 text-center">
                    <canvas id="canvas_tallos_m2"></canvas>
                    <strong>
                        {{number_format($tallos_m2, 2)}}
                    </strong>
                </div>
                <div class="col-md-9">
                    <canvas id="chart_tallos_m2" width="100%" height="25" style="margin-top: 5px"></canvas>
                </div>
            </div>
        </div>
        <div class="default ocultar" id="tab-ciclo">
            <div class="row">
                <div class="col-md-3 text-center">
                    <canvas id="canvas_ciclo"></canvas>
                    <strong>
                        {{number_format($ciclo, 2)}}
                    </strong>
                </div>
                <div class="col-md-9">
                    <canvas id="chart_ciclo" width="100%" height="25" style="margin-top: 5px"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    var rangos_precio = [];
    @foreach(getIntervalosIndicador('D14-' . $finca) as $r)
    rangos_precio.push({
        desde: parseFloat('{{$r->desde}}'),
        hasta: parseFloat('{{$r->hasta}}'),
        color: '{{$r->color}}',
    });
            @endforeach

    var rangos_porcentaje_cumplimiento = [];
    @foreach(getIntervalosIndicador('D17-' . $finca) as $r)
    rangos_porcentaje_cumplimiento.push({
        desde: parseFloat('{{$r->desde}}'),
        hasta: parseFloat('{{$r->hasta}}'),
        color: '{{$r->color}}',
    });
            @endforeach

    var rangos_tallos_m2 = [];
    @foreach(getIntervalosIndicador('D12-' . $finca) as $r)
    rangos_tallos_m2.push({
        desde: parseFloat('{{$r->desde}}'),
        hasta: parseFloat('{{$r->hasta}}'),
        color: '{{$r->color}}',
    });
            @endforeach

    var rangos_ciclo = [];
    @foreach(getIntervalosIndicador('DA1-' . $finca) as $r)
    rangos_ciclo.push({
        desde: parseFloat('{{$r->desde}}'),
        hasta: parseFloat('{{$r->hasta}}'),
        color: '{{$r->color}}',
    });
    @endforeach

    render_gauge('canvas_precio', '{{round($precio_x_tallo, 2)}}', rangos_precio, true);
    render_gauge('canvas_porcentaje_cumplimiento', '{{round($porcentaje_cumplimiento, 2)}}', rangos_porcentaje_cumplimiento, true);
    render_gauge('canvas_tallos_m2', '{{round($tallos_m2, 2)}}', rangos_tallos_m2, true);
    render_gauge('canvas_ciclo', '{{round($ciclo, 2)}}', rangos_ciclo, true);

    construir_char('Precio', 'chart_precio');
    construir_char('Porcentaje Cumplimiento', 'chart_porcentaje_cumplimiento');
    construir_char('Porcentaje Nacional', 'chart_porcentaje_nacional');
    construir_char('Tallos m2', 'chart_tallos_m2');
    construir_char('Ciclo', 'chart_ciclo');

    function construir_char(label, id) {
        labels = [];
        data_list = [];

        if (label == 'Porcentaje Nacional') {
            @foreach($resumen_semanal as $item)
            labels.push('{{$item->semana}}');
            data_list.push('{{porcentaje($item->nacional, $item->tallos_producidos, 1)}}');
            @endforeach
        } else {
            @foreach($indicadores_4_semanas as $pos_l => $item)
            labels.push('{{$item->semana}}');
            if (label == 'Precio')
                data_list.push('{{$item->precio_x_tallo}}');
            if (label == 'Porcentaje Cumplimiento')
                data_list.push('{{$item->porcentaje_cumplimiento}}');
            if (label == 'Tallos m2')
                data_list.push('{{$item->tallos_m2}}');
            if (label == 'Ciclo')
                data_list.push('{{$item->ciclo}}');
            @endforeach
        }

        datasets = [{
            label: label + ' ',
            data: data_list,
            //backgroundColor: '#8c99ff54',
            borderColor: 'black',
            borderWidth: 2,
            fill: false,
        }];

        ctx = document.getElementById(id).getContext('2d');
        myChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: datasets
            },
            options: {
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: false
                        }
                    }]
                },
                elements: {
                    line: {
                        tension: 0, // disables bezier curves
                    }
                },
                tooltips: {
                    mode: 'point' // nearest, point, index, dataset, x, y
                },
                legend: {
                    display: true,
                    position: 'bottom',
                    fullWidth: false,
                    onClick: function () {
                    },
                    onHover: function () {
                    },
                    reverse: true,
                },
                showLines: true, // for all datasets
                borderCapStyle: 'round',    // "butt" || "round" || "square"
            }
        });
    }

    function mostrar_ocultar_tab(id) {
        $('.li-default').removeClass('active');
        $('#li-' + id).addClass('active');
        $('.default').addClass('hidden');
        $('#tab-' + id).removeClass('hidden');
    }

    setTimeout(function () {
        $('.ocultar').addClass('hidden');
    }, 1)
</script>