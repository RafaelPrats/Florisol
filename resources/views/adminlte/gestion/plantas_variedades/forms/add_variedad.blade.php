<form id="form_add_variedad">
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label for="nombre">Nombre</label>
                <input type="text" id="nombre" name="nombre" class="form-control" required maxlength="250"
                    autocomplete="off">
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="id_planta">Planta</label>
                <select name="id_planta" id="id_planta" required class="form-control">
                    <option selected disabled>Seleccione</option>
                    @foreach ($plantas as $p)
                        <option value="{{ $p->id_planta }}">{{ $p->nombre }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="siglas">Siglas</label>
                <input type="text" id="siglas" name="siglas" class="form-control" required maxlength="25"
                    autocomplete="off">
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4" title="Color para los reportes">
            <div class="form-group">
                <label for="color">Color</label>
                <input type="text" id="color" name="color" class="form-control" required>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="tallos_x_malla">Tallos por malla</label>
                <input type="number" id="tallos_x_malla" name="tallos_x_malla" class="form-control" min="1"
                    required onkeypress="return isNumber(event)">
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="tallos_x_ramo_estandar">Tallos por ramo</label>
                <input type="number" id="tallos_x_ramo_estandar" name="tallos_x_ramo_estandar" class="form-control"
                    min="1" onkeypress="return isNumber(event)">
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="tipo">Compra de Flor</label>
                <select name="compra_flor" id="compra_flor" class="form-control">
                    <option value="0">No</option>
                    <option value="1">Sí</option>
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="tipo">Receta</label>
                <select name="receta" id="receta" class="form-control">
                    <option value="0">No</option>
                    <option value="1">Sí</option>
                </select>
            </div>
        </div>
    </div>
</form>
