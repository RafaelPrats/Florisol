<form id="form_add_planta">
    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label for="nombre">Nombre</label>
                <input type="text" id="nombre" name="nombre" class="form-control" required maxlength="250" autocomplete="off"
                       value="{{$planta->nombre}}">
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="tarifa">HTS (Tarifa)</label>
                <input type="text" id="tarifa" name="tarifa" class="form-control" required maxlength="50" autocomplete="off"
                       value="{{$planta->tarifa}}">
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="nandina">Nandina</label>
                <input type="text" id="nandina" name="nandina" class="form-control" required maxlength="50" autocomplete="off"
                       value="{{$planta->nandina}}">
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="siglas">Siglas</label>
                <input type="text" id="siglas" name="siglas" class="form-control" required maxlength="10" autocomplete="off"
                       value="{{$planta->siglas}}">
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="tipo">Tipo</label>
                <select name="tipo" id="tipo" class="form-control">
                    <option value="N" {{$planta->tipo == 'N' ? 'selected' : ''}}>Normal</option>
                    <option value="P" {{$planta->tipo == 'P' ? 'selected' : ''}}>Perennes</option>
                </select>
            </div>
        </div>
    </div>
    <input type="hidden" id="id_planta" name="id_planta" value="{{$planta->id_planta}}">
</form>
