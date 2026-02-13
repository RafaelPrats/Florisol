<legend style="margin-bottom: 5px"></legend>

<div class="nav-tabs-custom">
    <ul class="nav nav-pills nav-justified">
        <li class="active"><a href="#tab-area" data-toggle="tab">Área <sup>ha</sup></a></li>
        <li><a href="#tab-venta" data-toggle="tab">Venta</a></li>
        <li><a href="#tab-tallos_cosechados" data-toggle="tab">Tallos cosechados</a></li>
        <li><a href="#tab-tallos_exportables" data-toggle="tab">Tallos exportables</a></li>
        <li><a href="#tab-bajas" data-toggle="tab">Bajas</a></li>
    </ul>
    <div class="tab-content no-padding">
        <div class="chart tab-pane active" id="tab-area">
            <canvas id="chart_area" width="100%" height="25" style="margin-top: 5px"></canvas>
        </div>
        <div class="chart tab-pane active default" id="tab-venta">
            <canvas id="chart_venta" width="100%" height="25" style="margin-top: 5px"></canvas>
        </div>
        <div class="chart tab-pane active default" id="tab-tallos_cosechados">
            <canvas id="chart_tallos_cosechados" width="100%" height="25" style="margin-top: 5px"></canvas>
        </div>
        <div class="chart tab-pane active default" id="tab-tallos_exportables">
            <canvas id="chart_tallos_exportables" width="100%" height="25" style="margin-top: 5px"></canvas>
        </div>
        <div class="chart tab-pane active default" id="tab-bajas">
            <canvas id="chart_bajas" width="100%" height="25" style="margin-top: 5px"></canvas>
        </div>
    </div>
</div>

<script>
    setTimeout(function () {
        $('.default').removeClass('active');
    }, 1);

    construir_char('Área', 'chart_area');
    construir_char('Venta', 'chart_venta');
    construir_char('Tallos cosechados', 'chart_tallos_cosechados');
    construir_char('Tallos exportables', 'chart_tallos_exportables');
    construir_char('Bajas', 'chart_bajas');

    function construir_char(label, id) {
        labels = [];
        data_list = [];

        if (label == 'Área') {
            @foreach($resumen_area as $pos_l => $item)
            labels.push('{{$item['semana']}}');
            data_list.push('{{round($item['area'] / 10000, 2)}}');
            @endforeach
        } else {
            @foreach($resumen_semanal as $pos_l => $item)
            labels.push('{{$item->semana}}');
            if (label == 'Venta')
                data_list.push('{{round($item->venta + $item->venta_bouquetera, 2)}}');
            if (label == 'Tallos cosechados')
                data_list.push('{{$item->tallos_cosechados}}');
            if (label == 'Tallos exportables')
                data_list.push('{{$item->tallos_exportables}}');
            if (label == 'Bajas')
                data_list.push('{{$item->bajas}}');
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