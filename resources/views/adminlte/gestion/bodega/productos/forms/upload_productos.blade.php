<form id="form-importar_productos" action="{{ url('bodega_productos/post_importar_productos') }}" method="POST">
    {!! csrf_field() !!}
    <div class="input-group">
        <span class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
            Archivo
        </span>
        <input type="file" id="file_producto" name="file_producto" required class="form-control input-group-addon"
            accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">
        <span class="input-group-btn">
            <button type="button" class="btn btn-yura_primary" onclick="importar_productos()">
                <i class="fa fa-fw fa-check"></i> Importar Archivo
            </button>
        </span>
    </div>
</form>

<div style="margin-top: 5px" id="div_importar_productos"></div>

<script>
    function importar_productos() {
        if ($('#form-importar_productos').valid()) {
            $.LoadingOverlay('show');
            formulario = $('#form-importar_productos');
            var formData = new FormData(formulario[0]);
            //formData.append('fecha', $('#filtro_fecha').val());
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
                            get_jquery('{{ url('bodega_productos/get_importar_productos') }}', datos,
                                function(retorno) {
                                    $('#div_importar_productos').html(retorno);
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
    }
</script>
