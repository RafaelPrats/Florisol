<div class="nav-tabs-custom" style="cursor: move;">
    <ul class="nav nav-pills nav-justified">
        <li class="active"><a href="#area-chart" class="border-radius_18" data-toggle="tab" aria-expanded="false">Área <sup>ha</sup></a></li>
        <li class=""><a href="#ciclo-chart" class="border-radius_18" data-toggle="tab" aria-expanded="true">Ciclo</a></li>
        <li class=""><a href="#tallos-chart" class="border-radius_18" data-toggle="tab" aria-expanded="true">Tallos/m<sup>2</sup></a></li>
    </ul>
    <div class="tab-content no-padding">
        <div class="chart tab-pane active" id="area-chart" style="position: relative">
            <canvas id="chart_area" width="100%" height="40" style="margin-top: 5px"></canvas>
        </div>
        <div class="chart tab-pane" id="ciclo-chart" style="position: relative">
            <canvas id="chart_ciclo" width="100%" height="40" style="margin-top: 5px"></canvas>
        </div>
        <div class="chart tab-pane" id="tallos-chart" style="position: relative">
            <canvas id="chart_tallos" width="100%" height="40" style="margin-top: 5px"></canvas>
        </div>
    </div>
</div>

<script>
    construir_char('Area', 'chart_area');

    construir_char('Ciclo', 'chart_ciclo');
    construir_char('Tallos', 'chart_tallos');

    function construir_char(label, id) {
        labels = [];
        data_list = [];

        @foreach($labels as $pos_l => $label)
        labels.push('{{$label}}');
        if (label == 'Area')
            data_list.push('{{round($data['area'][$pos_l] / 10000, 2)}}');
        if (label == 'Ciclo')
            data_list.push('{{$data['ciclo'][$pos_l]}}');
        if (label == 'Tallos')
            data_list.push('{{$data['tallos'][$pos_l]}}');
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
</script>