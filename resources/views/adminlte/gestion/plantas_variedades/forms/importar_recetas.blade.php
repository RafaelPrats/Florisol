<form id="form-importar_recetas" action="{{ url('plantas_variedades/post_importar_recetas') }}" method="POST">
    {!! csrf_field() !!}
    <div class="input-group">
        <span class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
            Archivo
        </span>
        <input type="file" id="file_recetas" name="file_recetas" required class="form-control input-group-addon"
            accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">
        <span class="input-group-btn">
            <button type="button" class="btn btn-yura_primary" onclick="post_importar_recetas()">
                <i class="fa fa-fw fa-check"></i> Importar Archivo
            </button>
            <button type="button" class="btn btn-yura_dark" onclick="descargar_plantilla_recetas()">
                <i class="fa fa-fw fa-download"></i> Descargar Plantilla
            </button>
        </span>
    </div>
</form>

<div style="margin-top: 5px" id="div_importar_recetas"></div>

<script>
    function descargar_plantilla_recetas() {
        $.LoadingOverlay('show');
        window.open('{{ url('plantas_variedades/descargar_plantilla_recetas') }}', '_blank');
        $.LoadingOverlay('hide');
    }

    function post_importar_recetas() {
        if ($('#form-importar_recetas').valid()) {
            $.LoadingOverlay('show');
            formulario = $('#form-importar_recetas');
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
                            cerrar_modals();
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
