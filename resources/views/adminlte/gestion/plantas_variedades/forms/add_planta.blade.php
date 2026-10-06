<form id="form_add_planta" class="pv-modal-form">
    <div class="pv-form-intro">
        <i class="fa fa-leaf"></i>
        <div><strong>Registrar una planta</strong>Completa los datos del catálogo y la información arancelaria.</div>
    </div>
    <h4 class="pv-form-seccion"><i class="fa fa-fw fa-info-circle text-color_yura"></i> Datos generales</h4>
    <div class="row">
        <div class="col-sm-12">
            <div class="form-group">
                <label for="nombre">Nombre de la planta</label>
                <input type="text" id="nombre" name="nombre" class="form-control" required maxlength="250"
                    autocomplete="off" placeholder="Ej. Rosa">
            </div>
        </div>
    </div>
    <h4 class="pv-form-seccion"><i class="fa fa-fw fa-file-text-o text-color_yura"></i> Identificación comercial</h4>
    <div class="row">
        <div class="col-sm-4">
            <div class="form-group">
                <label for="siglas">Siglas</label>
                <input type="text" id="siglas" name="siglas" class="form-control" required maxlength="10"
                    autocomplete="off" placeholder="Ej. ROS">
                <small class="pv-field-hint">Código corto para identificar la planta.</small>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                <label for="tarifa">HTS (Tarifa) <span class="text-danger">*</span></label>
                <input type="text" id="tarifa" name="tarifa" class="form-control" required maxlength="50"
                    autocomplete="off" placeholder="Código arancelario">
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                <label for="nandina">Nandina <span class="text-danger">*</span></label>
                <input type="text" id="nandina" name="nandina" class="form-control" required maxlength="50"
                    autocomplete="off" placeholder="Código Nandina">
            </div>
        </div>
    </div>
</form>
