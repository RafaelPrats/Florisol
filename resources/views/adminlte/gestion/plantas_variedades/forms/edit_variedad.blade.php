<form id="form_edit_variedad" class="pv-modal-form">
    <div class="pv-form-intro">
        <i class="fa fa-leaf"></i>
        <div><strong>Editar variedad</strong>Actualiza sus datos de identificación y parámetros de producción.</div>
    </div>
    <h4 class="pv-form-seccion"><i class="fa fa-fw fa-tag text-color_yura"></i> Identidad de la variedad</h4>
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label for="nombre">Nombre de la variedad <span class="text-danger">*</span></label>
                <input type="text" id="nombre" name="nombre" class="form-control" required maxlength="250"
                    autocomplete="off" value="{{ $variedad->nombre }}">
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="id_planta">Planta <span class="text-danger">*</span></label>
                <select name="id_planta" id="id_planta" required class="form-control">
                    <option value="">Selecciona una planta</option>
                    @foreach ($plantas as $p)
                        <option value="{{ $p->id_planta }}"
                            {{ $variedad->id_planta == $p->id_planta ? 'selected' : '' }}>
                            {{ $p->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label for="siglas">Siglas</label>
                <input type="text" id="siglas" name="siglas" class="form-control" maxlength="25"
                    autocomplete="off" value="{{ $variedad->siglas }}">
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group" title="Color para los reportes">
                <label for="color">Color para reportes <span class="text-danger">*</span></label>
                <input type="text" id="color" name="color" class="form-control" required
                    value="{{ $variedad->color }}">
            </div>
        </div>
    </div>
    <h4 class="pv-form-seccion"><i class="fa fa-fw fa-sliders text-color_yura"></i> Producción y clasificación</h4>
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label for="tallos_x_malla">Tallos por malla <span class="text-danger">*</span></label>
                <input type="number" id="tallos_x_malla" name="tallos_x_malla" class="form-control" min="1"
                    required onkeypress="return isNumber(event)" value="{{ $variedad->tallos_x_malla }}">
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="tallos_x_ramo_estandar">Tallos por ramo</label>
                <input type="number" id="tallos_x_ramo_estandar" name="tallos_x_ramo_estandar"
                    class="form-control" min="1" onkeypress="return isNumber(event)"
                    value="{{ $variedad->tallos_x_ramo_estandar }}">
            </div>
        </div>
    </div>
    <h4 class="pv-form-seccion"><i class="fa fa-fw fa-cog text-color_yura"></i> Configuración</h4>
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label for="compra_flor">Compra de flor</label>
                <select name="compra_flor" id="compra_flor" class="form-control">
                    <option value="1" {{ $variedad->compra_flor == 1 ? 'selected' : '' }}>Sí</option>
                    <option value="0" {{ $variedad->compra_flor == 0 ? 'selected' : '' }}>No</option>
                </select>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="receta">Usa receta</label>
                <select name="receta" id="receta" class="form-control">
                    <option value="1" {{ $variedad->receta == 1 ? 'selected' : '' }}>Sí</option>
                    <option value="0" {{ $variedad->receta == 0 ? 'selected' : '' }}>No</option>
                </select>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="codigo_latin">Código Latin</label>
                <input type="text" id="codigo_latin" name="codigo_latin" class="form-control"
                    value="{{ $variedad->codigo_latin }}">
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="codigo_exportacion">Código de exportación</label>
                <input type="text" id="codigo_exportacion" name="codigo_exportacion" class="form-control"
                    value="{{ $variedad->codigo_exportacion }}">
            </div>
        </div>
    </div>
    <input type="hidden" id="id_variedad" name="id_variedad" value="{{ $variedad->id_variedad }}">
</form>
