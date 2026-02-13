<legend style="margin-bottom: 5px"></legend>

<div class="nav-tabs-custom">
    <ul class="nav nav-pills nav-justified">
        <li class="active li-default" id="li-propagacion_x_tallo">
            <a href="javascript:void(0)" onclick="mostrar_ocultar_tab('propagacion_x_tallo')">Propagación x Tallo</a>
        </li>
        <li class="li-default" id="li-cultivo_x_tallo">
            <a href="javascript:void(0)" onclick="mostrar_ocultar_tab('cultivo_x_tallo')">Cultivo x Tallo</a>
        </li>
        <li class="li-default" id="li-postcosecha_x_tallo">
            <a href="javascript:void(0)" onclick="mostrar_ocultar_tab('postcosecha_x_tallo')">Postcosecha x Tallo</a>
        </li>
        <li class="li-default" id="li-total_x_tallo">
            <a href="javascript:void(0)" onclick="mostrar_ocultar_tab('total_x_tallo')">Total x Tallo</a>
        </li>
    </ul>
    <div>
        <div class="default" id="tab-propagacion_x_tallo">
            <div class="row">
                <div class="col-md-3 text-center">
                    <canvas id="canvas_propagacion_x_tallo"></canvas>
                    <strong>
                        ¢{{number_format($costos_propagacion_x_tallo , 2)}}
                    </strong>
                </div>
                <div class="col-md-9">
                    <canvas id="chart_propagacion_x_tallo" width="100%" height="25" style="margin-top: 5px"></canvas>
                </div>
            </div>
        </div>
        <div class="default ocultar" id="tab-cultivo_x_tallo">
            <div class="row">
                <div class="col-md-3 text-center">
                    <canvas id="canvas_cultivo_x_tallo"></canvas>
                    <strong>
                        ¢{{number_format($costos_cultivo_x_tallo, 2)}}
                    </strong>
                </div>
                <div class="col-md-9">
                    <canvas id="chart_cultivo_x_tallo" width="100%" height="25" style="margin-top: 5px"></canvas>
                </div>
            </div>
        </div>
        <div class="default ocultar" id="tab-postcosecha_x_tallo">
            <div class="row">
                <div class="col-md-3 text-center">
                    <canvas id="canvas_postcosecha_x_tallo"></canvas>
                    <strong>
                        ¢{{number_format($costos_postcosecha_x_tallo, 2)}}
                    </strong>
                </div>
                <div class="col-md-9">
                    <canvas id="chart_postcosecha_x_tallo" width="100%" height="25" style="margin-top: 5px"></canvas>
                </div>
            </div>
        </div>
        <div class="default ocultar" id="tab-total_x_tallo">
            <div class="row">
                <div class="col-md-3 text-center">
                    <canvas id="canvas_total_x_tallo"></canvas>
                    <strong>
                        ¢{{number_format($costos_total_x_tallo, 2)}}
                    </strong>
                </div>
                <div class="col-md-9">
                    <canvas id="chart_total_x_tallo" width="100%" height="25" style="margin-top: 5px"></canvas>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    var rangos_propagacion_x_tallo = [];
    @foreach(getIntervalosIndicador('C3-' . $finca) as $r)
    rangos_propagacion_x_tallo.push({
        desde: parseFloat('{{$r->desde}}'),
        hasta: parseFloat('{{$r->hasta}}'),
        color: '{{$r->color}}',
    });
            @endforeach

    var rangos_cultivo_x_tallo = [];
    @foreach(getIntervalosIndicador('C4-' . $finca) as $r)
    rangos_cultivo_x_tallo.push({
        desde: parseFloat('{{$r->desde}}'),
        hasta: parseFloat('{{$r->hasta}}'),
        color: '{{$r->color}}',
    });
            @endforeach

    var rangos_postcosecha_x_tallo = [];
    @foreach(getIntervalosIndicador('C5-' . $finca) as $r)
    rangos_postcosecha_x_tallo.push({
        desde: parseFloat('{{$r->desde}}'),
        hasta: parseFloat('{{$r->hasta}}'),
        color: '{{$r->color}}',
    });
            @endforeach

    var rangos_total_x_tallo = [];
    @foreach(getIntervalosIndicador('C6-' . $finca) as $r)
    rangos_total_x_tallo.push({
        desde: parseFloat('{{$r->desde}}'),
        hasta: parseFloat('{{$r->hasta}}'),
        color: '{{$r->color}}',
    });
    @endforeach

    render_gauge('canvas_propagacion_x_tallo', '{{round(explode('|', $costos_propagacion_x_tallo)[0], 2)}}', rangos_propagacion_x_tallo, true);
    render_gauge('canvas_cultivo_x_tallo', '{{round($costos_cultivo_x_tallo, 2)}}', rangos_cultivo_x_tallo, true);
    render_gauge('canvas_postcosecha_x_tallo', '{{round($costos_postcosecha_x_tallo, 2)}}', rangos_postcosecha_x_tallo, true);
    render_gauge('canvas_total_x_tallo', '{{round($costos_total_x_tallo, 2)}}', rangos_total_x_tallo, true);

    construir_char('Propagación x Tallo', 'chart_propagacion_x_tallo');
    construir_char('Cultivo x Tallo', 'chart_cultivo_x_tallo');
    construir_char('Postcosecha x Tallo', 'chart_postcosecha_x_tallo');
    construir_char('Costo Total x Tallo', 'chart_total_x_tallo');

    function construir_char(label, id) {
        labels = [];
        data_list = [];

        @foreach($indicadores_4_semanas as $pos_l => $item)
        labels.push('{{$item->semana}}');
        if (label == 'Propagación x Tallo')
            data_list.push('{{$item->propagacion_x_tallo}}');
        if (label == 'Cultivo x Tallo')
            data_list.push('{{$item->cultivo_x_tallo}}');
        if (label == 'Postcosecha x Tallo')
            data_list.push('{{$item->postcosecha_x_tallo}}');
        if (label == 'Costo Total x Tallo')
            data_list.push('{{$item->costos_total_x_tallo}}');
        @endforeach

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