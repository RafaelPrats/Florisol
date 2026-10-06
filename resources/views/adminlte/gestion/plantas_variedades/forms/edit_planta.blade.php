<form id="form_add_planta" class="pv-modal-form">
    <div class="pv-form-intro">
        <i class="fa fa-leaf"></i>
        <div><strong>Editar planta</strong>Actualiza la identificación y clasificación de esta planta.</div>
    </div>
    <h4 class="pv-form-seccion"><i class="fa fa-fw fa-info-circle text-color_yura"></i> Datos generales</h4>
    <div class="row">
        <div class="col-sm-12">
            <div class="form-group">
                <label for="nombre">Nombre de la planta</label>
                <input type="text" id="nombre" name="nombre" class="form-control" required maxlength="250"
                    autocomplete="off" value="{{ $planta->nombre }}">
            </div>
        </div>
    </div>
    <h4 class="pv-form-seccion"><i class="fa fa-fw fa-file-text-o text-color_yura"></i> Clasificación y códigos</h4>
    <div class="row">
        <div class="col-sm-4">
            <div class="form-group">
                <label for="siglas">Siglas</label>
                <input type="text" id="siglas" name="siglas" class="form-control" required maxlength="10"
                    autocomplete="off" value="{{ $planta->siglas }}">
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                <label for="tarifa">HTS (Tarifa) <span class="text-danger">*</span></label>
                <input type="text" id="tarifa" name="tarifa" class="form-control" required maxlength="50"
                    autocomplete="off" value="{{ $planta->tarifa }}">
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                <label for="nandina">Nandina <span class="text-danger">*</span></label>
                <input type="text" id="nandina" name="nandina" class="form-control" required maxlength="50"
                    autocomplete="off" value="{{ $planta->nandina }}">
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                <label for="tipo">Tipo de planta</label>
                <select name="tipo" id="tipo" class="form-control">
                    <option value="N" {{ $planta->tipo == 'N' ? 'selected' : '' }}>Normal</option>
                    <option value="P" {{ $planta->tipo == 'P' ? 'selected' : '' }}>Perennes</option>
                </select>
            </div>
        </div>
    </div>
    <input type="hidden" id="id_planta" name="id_planta" value="{{ $planta->id_planta }}">
</form>
