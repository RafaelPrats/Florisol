<form id="form-importar_compras" action="{{ url('compra_flor/post_importar_compras') }}" method="POST">
    {!! csrf_field() !!}
    <div class="input-group">
        <span class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
            Archivo
        </span>
        <input type="file" id="file_compras" name="file_compras" required class="form-control input-group-addon"
            accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">
        <span class="input-group-btn">
            <button type="button" class="btn btn-yura_primary" onclick="post_importar_compras()">
                <i class="fa fa-fw fa-check"></i> Importar Archivo
            </button>
        </span>
    </div>
</form>

<div style="margin-top: 5px" id="div_importar_compras"></div>

<script>
    function post_importar_compras() {
        if ($('#form-importar_compras').valid()) {
            $.LoadingOverlay('show');
            formulario = $('#form-importar_compras');
            var formData = new FormData(formulario[0]);
            formData.append('fecha', $('#filtro_fecha').val());
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
                            get_jquery('{{ url('compra_flor/get_importar_compras') }}', datos,
                                function(retorno) {
                                    $('#div_importar_compras').html(retorno);
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
