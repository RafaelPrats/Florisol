<form id="form-importar_pedidos" action="{{ url('importar_postco/post_importar_pedidos') }}" method="POST">
    {!! csrf_field() !!}
    <div class="input-group">
        <span class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
            Fecha
        </span>
        <input type="date" id="fecha_pedidos" name="fecha_pedidos" required class="form-control input-group-addon" value="{{hoy()}}" style="background-color: #ef6e11; color: white !important">
        <span class="input-group-addon bg-yura_dark">
            Archivo
        </span>
        <input type="file" id="file_pedidos" name="file_pedidos" required class="form-control input-group-addon"
            accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">
        <span class="input-group-btn">
            <button type="button" class="btn btn-yura_primary" onclick="importar_pedidos()">
                <i class="fa fa-fw fa-check"></i> Importar Archivo
            </button>
            {{-- 
            <button type="button" class="btn btn-yura_dark" onclick="descargar_plantilla()">
                <i class="fa fa-fw fa-download"></i> Descargar Plantilla
            </button>
             --}}
        </span>
    </div>
</form>

<div style="margin-top: 5px" id="div_importar_pedidos"></div>

<script>
    function descargar_plantilla() {
        $.LoadingOverlay('show');
        window.open('{{ url('importar_pedidos/descargar_plantilla') }}', '_blank');
        $.LoadingOverlay('hide');
    }

    function importar_pedidos() {
        if ($('#form-importar_pedidos').valid()) {
            $.LoadingOverlay('show');
            formulario = $('#form-importar_pedidos');
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
                            get_jquery('{{ url('importar_postco/get_importar_pedidos') }}', datos,
                                function(retorno) {
                                    $('#div_importar_pedidos').html(retorno);
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