<form id="form-importar_file_bajas" action="{{ url('reporte_cuarto_frio/importar_file_bajas') }}" method="POST">
    {!! csrf_field() !!}
    <div class="input-group">
        <span class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
            Archivo
        </span>
        <input type="file" id="file_bajas" name="file_bajas" required class="form-control input-group-addon"
            accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel, .csv">
        <span class="input-group-btn">
            <button type="button" class="btn btn-yura_dark" onclick="importar_file_bajas()">
                <i class="fa fa-fw fa-check"></i>
            </button>
        </span>
    </div>
</form>

<div id="div_importar_file_bajas" style="margin-top: 5px"></div>

<script>
    function importar_file_bajas() {
        $.LoadingOverlay('show');
        formulario = $('#form-importar_file_bajas');
        var formData = new FormData(formulario[0]);
        //hacemos la petición ajax
        $.ajax({
            url: formulario.attr('action'),
            type: 'POST',
            data: formData,
            dataType: 'json',
            //necesario para subir archivos via ajax
            cache: false,
            contentType: false,
            processData: false,

            success: function(retorno2) {
                if (retorno2.success) {
                    $.LoadingOverlay('hide');
                    alerta_accion(retorno2.mensaje, function() {
                        //cerrar_modals();
                        datos = {}
                        get_jquery('{{ url('reporte_cuarto_frio/get_importar_file_bajas') }}',
                            datos,
                            function(retorno) {
                                $('#div_importar_file_bajas').html(retorno);
                            });
                    });
                } else {
                    alerta(retorno2.mensaje);
                    $.LoadingOverlay('hide');
                }
            },
            //si ha ocurrido un error
            error: function(retorno2) {
                console.log(retorno2);
                alerta(retorno2.responseText);
                alert('Hubo un problema en el envío de la información');
                $.LoadingOverlay('hide');
            }
        });
    }
</script>
