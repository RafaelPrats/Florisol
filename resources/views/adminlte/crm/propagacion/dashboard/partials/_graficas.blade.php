<div class="nav-tabs-custom" style="cursor: move;">
    <ul class="nav nav-pills nav-justified">
        <li class="active">
            <a href="#esqueje_x_planta-chart" class="border-radius_18" data-toggle="tab" aria-expanded="true">
                Productividad <small>(Esq x Pta)</small>
            </a>
        </li>
        <li class="">
            <a href="#porcentaje_enraizamiento-chart" class="border-radius_18" data-toggle="tab" aria-expanded="false">
                Porcentaje enraizamiento
            </a>
        </li>
        <li class="">
            <a href="#requerimientos-chart" class="border-radius_18" data-toggle="tab" aria-expanded="false">Requerimientos</a>
        </li>
        <li class="">
            <a href="#costo_x_planta-chart" class="border-radius_18" data-toggle="tab" aria-expanded="false">Costo x Planta</a>
        </li>
    </ul>
    <div class="tab-content no-padding">
        <div class="chart tab-pane active" id="esqueje_x_planta-chart" style="position: relative">
            <canvas id="chart_esqueje_x_planta" width="100%" height="25" style="margin-top: 5px"></canvas>
        </div>
        <div class="chart tab-pane" id="porcentaje_enraizamiento-chart" style="position: relative">
            <canvas id="chart_porcentaje_enraizamiento" width="100%" height="20" style="margin-top: 5px"></canvas>
        </div>
        <div class="chart tab-pane" id="requerimientos-chart" style="position: relative">
            <canvas id="chart_requerimientos" width="100%" height="20" style="margin-top: 5px"></canvas>
        </div>
        <div class="chart tab-pane" id="costo_x_planta-chart" style="position: relative">
            <canvas id="chart_costo_x_planta" width="100%" height="20" style="margin-top: 5px"></canvas>
        </div>
    </div>
</div>

<script>
    construir_char('Esqueje x Planta', 'chart_esqueje_x_planta');
    construir_char('Porcentaje enraizamiento', 'chart_porcentaje_enraizamiento');
    construir_char('Requerimientos', 'chart_requerimientos');
    construir_char('Costo x Planta', 'chart_costo_x_planta');

    function construir_char(label, id) {
        labels = [];
        data_list = [];

        if (label == 'Esqueje x Planta') {
            @foreach($resumen_propagacion as $pos => $item)
            labels.push('{{$item->semana}}');
            data_list.push('{{$item->plantas_sembradas > 0 ? round($item->esquejes_cosechados / $item->plantas_sembradas, 2) : 0}}');
            @endforeach
        }

        if (label == 'Porcentaje enraizamiento') {
            @foreach($porcentaje_enraizamiento as $pos => $item)
            labels.push('{{$item->semana}}');
            data_list.push('{{$cant_validos_porc_enr[$pos]->cantidad > 0 ? round(100 - ($item->cantidad / $cant_validos_porc_enr[$pos]->cantidad), 3) : 0}}');
            @endforeach
        }

        if (label == 'Requerimientos') {
            @foreach($resumen_propagacion as $pos => $item)
            labels.push('{{$item->semana}}');
            data_list.push('{{$item->requerimientos}}');
            @endforeach
        }

        if (label == 'Costo x Planta') {
            @foreach($costo_x_planta as $pos => $item)
            labels.push('{{$item->semana}}');
            data_list.push('{{$cant_validos_cost_x_plta[$pos]->cantidad > 0 ? round(($item->cantidad / $cant_validos_cost_x_plta[$pos]->cantidad) * 100, 3) : 0}}');
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
</script>