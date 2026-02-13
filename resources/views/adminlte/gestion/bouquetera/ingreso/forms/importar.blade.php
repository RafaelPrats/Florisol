<div class="box box-success">
    <div class="box-header">
        <div class="box-title">
            Importar archivo excel de bouquetera
        </div>
    </div>
    <div class="box-body">
        <form id="form-upload_file_bqt" action="{{url('ingreso_bouquetera/upload_file_bqt')}}" method="POST">
            {!! csrf_field() !!}
            <div class="input-group">
                <div class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
                    Archivo
                </div>
                <input type="file" id="file_bqt" name="file_bqt" required class="form-control input-yura_default"
                       accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">
                <div class="input-group-addon bg-yura_dark" title="Guardar tallos bqt">
                    Tallos bqt
                </div>
                <select class="form-control input-yura_default" name="tallos_bqt" id="tallos_bqt">
                    <option value="S">Sí</option>
                    <option value="N">No</option>
                </select>
                <div class="input-group-addon bg-yura_dark" title="Guardar tallos exportables">
                    Tallos export.
                </div>
                <select class="form-control input-yura_default" name="tallos_exportables" id="tallos_exportables">
                    <option value="S">Sí</option>
                    <option value="N">No</option>
                </select>
                <div class="input-group-addon bg-yura_dark" title="Guardar Precio">
                    Precio
                </div>
                <select class="form-control input-yura_default" name="precio" id="precio">
                    <option value="S">Sí</option>
                    <option value="N">No</option>
                </select>
                <div class="input-group-btn">
                    <button type="button" class="btn btn-yura_dark" onclick="descargar_plantilla()">
                        <i class="fa fa-fw fa-download"></i> Descargar plantilla
                    </button>
                    <button type="button" class="btn btn-yura_primary" onclick="upload_file_bqt()">
                        <i class="fa fa-fw fa-upload"></i> Importar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function descargar_plantilla() {
        $.LoadingOverlay('show');
        window.open('{{url('ingreso_bouquetera/descargar_plantilla')}}', '_blank');
        $.LoadingOverlay('hide');
    }

    function upload_file_bqt() {
        $.LoadingOverlay('show');
        formulario = $('#form-upload_file_bqt');
        var formData = new FormData(formulario[0]);
        formData.append('finca_actual', $('#fincas_propias').val());
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

            success: function (retorno2) {
                notificar('Se ha importado un archivo', '{{url('ingreso_bouquetera')}}');
                if (retorno2.success) {
                    $.LoadingOverlay('hide');
                    alerta_accion(retorno2.mensaje, function () {
                        //location.reload();
                    });
                } else {
                    alerta(retorno2.mensaje);
                    $.LoadingOverlay('hide');
                }
            },
            //si ha ocurrido un error
            error: function (retorno2) {
                console.log(retorno2);
                alerta(retorno2.responseText);
                alert('Hubo un problema en el envío de la información');
                $.LoadingOverlay('hide');
            }
        });
    }
</script>