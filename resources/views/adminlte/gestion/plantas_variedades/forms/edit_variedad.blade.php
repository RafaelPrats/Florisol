<form id="form_edit_variedad">
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label for="nombre">Nombre</label>
                <input type="text" id="nombre" name="nombre" class="form-control" required maxlength="250"
                    autocomplete="off" value="{{ $variedad->nombre }}">
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="id_planta">Planta</label>
                <select name="id_planta" id="id_planta" required class="form-control">
                    <option value="">Seleccione</option>
                    @foreach ($plantas as $p)
                        <option value="{{ $p->id_planta }}"
                            {{ $variedad->id_planta == $p->id_planta ? 'selected' : '' }}>
                            {{ $p->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="siglas">Siglas</label>
                <input type="text" id="siglas" name="siglas" class="form-control" required maxlength="25"
                    autocomplete="off" value="{{ $variedad->siglas }}">
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4" title="Color para los reportes">
            <div class="form-group">
                <label for="color">Color</label>
                <input type="text" id="color" name="color" class="form-control" required
                    value="{{ $variedad->color }}">
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="tallos_x_malla">Tallos por malla</label>
                <input type="number" id="tallos_x_malla" name="tallos_x_malla" class="form-control" min="1"
                    required onkeypress="return isNumber(event)" value="{{ $variedad->tallos_x_malla }}">
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="tallos_x_ramo_estandar">Tallos por ramo</label>
                <input type="number" id="tallos_x_ramo_estandar" name="tallos_x_ramo_estandar" class="form-control"
                    min="1" onkeypress="return isNumber(event)"
                    value="{{ $variedad->tallos_x_ramo_estandar }}">
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="tipo">Compra de Flor</label>
                <select name="compra_flor" id="compra_flor" class="form-control">
                    <option value="1" {{ $variedad->compra_flor == 1 ? 'selected' : '' }}>Sí</option>
                    <option value="0" {{ $variedad->compra_flor == 0 ? 'selected' : '' }}>No</option>
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="tipo">Receta</label>
                <select name="receta" id="receta" class="form-control">
                    <option value="1" {{ $variedad->receta == 1 ? 'selected' : '' }}>Sí</option>
                    <option value="0" {{ $variedad->receta == 0 ? 'selected' : '' }}>No</option>
                </select>
            </div>
        </div>
    </div>

    <input type="hidden" id="id_variedad" name="id_variedad" value="{{ $variedad->id_variedad }}">
</form>
