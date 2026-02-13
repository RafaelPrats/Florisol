<div class="chat-container">
    <div class="chat-header">
        <i class="fa fa-fw fa-comments"></i> Asistente virtual

        <button type="button" class="btn btn-xs btn-yura_default dropdown-toggle pull-right" data-toggle="dropdown"
            aria-expanded="true">
            <i class="fa fa-fw fa-bars"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-right sombra_pequeña" id="dropdown_menu_reportes"
            style="background-color: #c8c8c8">
            <li>
                <a class="" href="javascript:void(0)" style="color: black" onclick="cambiar_reporte('v_resumen')">
                    <i class="fa fa-fw fa-tree"></i>
                    Tallos vendidos
                </a>
            </li>
            <li>
                <a class="" href="javascript:void(0)" style="color: black"
                    onclick="cambiar_reporte('v_bouquets')">
                    <i class="fa fa-fw fa-gift"></i>
                    Reporte de Bouquets
                </a>
            </li>
        </ul>
    </div>

    <div class="chat-messages" id="messages">
        <div class="message bot">
            <div class="bubble">
                {!! getReportesChatBot()[$reporte]['bienvenida'] !!}
            </div>
        </div>
    </div>

    <div class="chat-input">
        <input type="text" id="message" placeholder="Escribe tu pregunta..." autocomplete="off">
        <button id="send" class="btn btn-yura_primary" style="border-radius: 0px">
            <i class="fa fa-fw fa-send"></i> Enviar
        </button>
    </div>
</div>

<style>
    #dropdown_menu_reportes {
        top: 50px !important;
        right: 0px !important;
    }

    .chat-container {
        width: 100%;
        min-height: 600px;
        max-height: 700px;
        margin: 40px auto;
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, .15);
        display: flex;
        flex-direction: column;
    }

    .chat-header {
        position: relative;
        background: #00B388;
        color: #fff;
        padding: 15px;
        text-align: center;
        font-weight: bold;
        border-radius: 10px 10px 0 0;
    }

    .chat-messages {
        flex: 1;
        padding: 15px;
        overflow-y: auto;
        background: #f7f9fc;
    }

    .chat-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
        font-size: 13px;
    }

    .chat-table th {
        background: #5a7177;
        color: white;
        border-color: white;
        padding: 5px;
        text-align: center;
    }

    .chat-table td {
        border: 1px solid #9d9d9d;
        padding: 6px;
    }

    .chat-table tr:nth-child(even) {
        background: #f2f2f2;
    }

    .message {
        margin-bottom: 12px;
        display: flex;
    }

    .message.user {
        justify-content: flex-end;
    }

    .message.bot {
        justify-content: flex-start;
    }

    #message {
        color: black;
    }

    .bubble {
        max-width: 75%;
        padding: 10px 14px;
        border-radius: 15px;
        font-size: 14px;
        line-height: 1.4;
    }

    .user .bubble {
        background: #00B388;
        color: #fff;
        border-bottom-right-radius: 3px;
    }

    .bot .bubble {
        background: #e4e7ec;
        color: #333;
        border-bottom-left-radius: 3px;
    }

    .typing {
        font-style: italic;
        opacity: .7;
    }

    .chat-input {
        display: flex;
        border-top: 1px solid #ddd;
    }

    .chat-input input {
        flex: 1;
        border: none;
        padding: 15px;
        font-size: 14px;
        outline: none;
    }

    canvas {
        max-width: 100%;
    }

    .subrayado-hover {
        text-decoration: none;
        font-style: italic;
    }

    .subrayado-hover:hover {
        text-decoration: underline;
    }

    .message_user {
        position: relative;
    }

    .mensaje_user_botones {
        position: absolute;
        bottom: -22px;
        right: 0;
    }

    .mensaje_user_botones i {
        color: #a7a7a7;
        font-size: 1.2em;
    }

    .mensaje_user_botones i:hover {
        transform: scale(1.2);
        color: #00B388;
    }

    .m-1 {
        margin-left: 2px;
        margin-right: 2px;
    }
</style>

<script>
    chartInstance = null;

    function renderChart(canvasId, chartData) {
        let canvas = document.getElementById(canvasId);

        if (!canvas) {
            console.error('Canvas no encontrado:', canvasId);
            return;
        }

        let ctx = canvas.getContext('2d');

        new Chart(ctx, {
            type: chartData.type || 'bar',
            data: {
                labels: chartData.labels,
                datasets: [{
                    label: chartData.label,
                    data: chartData.values,
                    backgroundColor: '#00B388',
                    borderColor: '#003025',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            callback: function(value) {
                                return value.toLocaleString('es-ES');
                            }
                        }
                    }]
                }
            }
        });
    }

    function addMessage(text, type) {
        if (type == 'bot')
            $('#messages').append(
                '<div class="message ' + type + '"><div class="bubble">' + text + '</div></div>'
            );
        else {
            parametro_texto = "'" + text + "'";
            botones_mensaje =
                '<div class="mensaje_user_botones"><i onclick="editar_mensaje(' + parametro_texto +
                ')" class="m-1 mouse-hand fa fa-fw fa-edit" title="Editar Pregunta"></i>' +
                '<i onclick="repetir_mensaje(' + parametro_texto +
                ')" class="m-1 mouse-hand fa fa-fw fa-refresh" title="Repetir pregunta"></i></div>';
            $('#messages').append(
                '<div class="message ' + type + ' message_user"><div class="bubble">' + text + '</div>' +
                botones_mensaje + '</div>'
            );
        }
        $('#messages').scrollTop($('#messages')[0].scrollHeight);
    }

    function addTyping() {
        $('#messages').append(
            '<div class="message bot typing" id="typing"><div class="bubble">Escribiendo...</div></div>'
        );
        $('#messages').scrollTop($('#messages')[0].scrollHeight);
    }

    function removeTyping() {
        $('#typing').remove();
    }

    $('#send').click(send_chat);
    $('#message').keypress(function(e) {
        if (e.which === 13) send_chat();
    });

    function send_chat() {
        let msg = $('#message').val().trim();
        if (!msg) return;

        addMessage(msg, 'user');
        $('#message').val('');
        addTyping();

        datos = {
            _token: '{{ csrf_token() }}',
            message: msg
        }

        $.post('{{ url('chatbot/send_chat') }}', datos, function(retorno) {
            removeTyping();
            if (retorno.success) {
                let chartId = 'chart_' + Date.now();
                let tablaId = 'tabla_' + Date.now();
                parametro_ver_tabla = "'" + tablaId + "'";
                texto_adicional =
                    '<br><small class="pull-right"><a javascript:void(0) style="color: black;" class="subrayado-hover mouse-hand" onclick="verTabla(' +
                    parametro_ver_tabla + ')">Ver Tabla</a></small>';
                texto_adicional +=
                    '<div class="hidden" id="data_encode_' + tablaId + '">' + retorno.data_encode +
                    '</div>';
                addMessage(retorno.answer + texto_adicional, 'bot');

                if (retorno.table) {
                    boton_grafica = '';
                    if (retorno.chart) {
                        parametro = "'" + chartId + "'";
                        boton_grafica =
                            '<button type="button" class="export-btn btn-xs btn btn-yura_dark" onclick="dibujarGrafica(' +
                            parametro + ')" style="margin-top: 5px" title="Ver Gráfica">' +
                            '<i class="fa fa-fw fa-bar-chart"></i>' +
                            '</button>';
                    }
                    $('#messages').append(
                        '<div class="message bot hidden" id="message_' + tablaId + '">' +
                        '<div class="bubble text-center">' +
                        retorno.table +
                        '<div class="btn-group">' +
                        '<button type="button" class="export-btn btn-xs btn btn-yura_primary" title="Exportar a Excel" onclick="exportExcel(' +
                        parametro_ver_tabla + ')" style="margin-top: 5px">' +
                        '<i class="fa fa-fw fa-file-excel-o"></i>' +
                        '</button>' +
                        boton_grafica +
                        '</div>' +
                        '</div>' +
                        '</div>'
                    );
                }

                if (retorno.chart) {
                    $('#messages').append(
                        '<div class="message bot" id="message_' + chartId + '">' +
                        '<div class="bubble">' +
                        '<canvas id="' + chartId + '" height="220"></canvas>' +
                        '</div>' +
                        '</div>'
                    );

                    $('#messages').scrollTop($('#messages')[0].scrollHeight);

                    // ⏳ esperar a que el DOM pinte el canvas
                    setTimeout(function() {
                        renderChart(chartId, retorno.chart);
                        setTimeout(function() {
                            $('#message_' + chartId).addClass('hidden');
                        }, 100);
                    }, 100);
                }
                $('#message').focus();
            } else {
                addMessage(retorno.mensaje, 'bot');
            }
        }, 'json').fail(function(retorno) {
            removeTyping();
            addMessage('❌ Error procesando la consulta', 'bot');
            console.log(retorno);
            alerta_errores(retorno.responseText);
        })
    }

    function cambiar_reporte(reporte) {
        datos = {
            _token: '{{ csrf_token() }}',
            reporte: reporte
        }
        $.post('{{ url('chatbot/cambiar_reporte') }}', datos, function(retorno) {
            if (retorno.success) {
                addMessage(retorno.mensaje, 'bot');
            }
        }, 'json').fail(function(retorno) {
            console.log(retorno);
            alerta_errores(retorno.responseText);
        });
    }

    function dibujarGrafica(chartId) {
        $('#message_' + chartId).toggleClass('hidden');
    }

    function verTabla(tablaId) {
        $('#message_' + tablaId).toggleClass('hidden');
    }

    function exportExcel(tablaId) {
        data_encode = $('#data_encode_' + tablaId).html();
        $.LoadingOverlay('show');
        window.open('{{ url('chatbot/export_excel') }}?data=' + data_encode, '_blank');
        $.LoadingOverlay('hide');
    }

    function repetir_mensaje(text) {
        $('#message').val(text);
        send_chat();
    }

    function editar_mensaje(text) {
        $('#message').val(text);
        $('#message').focus();
    }
</script>
