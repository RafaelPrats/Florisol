<form id="form_add_variedad" class="pv-modal-form">
    <div class="pv-form-intro">
        <i class="fa fa-leaf"></i>
        <div><strong>Registrar variedad</strong>Define su identidad y configura sus parámetros de producción.</div>
    </div>
    <h4 class="pv-form-seccion"><i class="fa fa-fw fa-tag text-color_yura"></i> Identidad de la variedad</h4>
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label for="nombre">Nombre de la variedad <span class="text-danger">*</span></label>
                <input type="text" id="nombre" name="nombre" class="form-control" required maxlength="250"
                    autocomplete="off" placeholder="Nombre de la variedad">
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="id_planta">Planta <span class="text-danger">*</span></label>
                <select name="id_planta" id="id_planta" required class="form-control">
                    <option value="" selected disabled>Selecciona una planta</option>
                    @foreach ($plantas as $p)
                        <option value="{{ $p->id_planta }}">{{ $p->nombre }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label for="siglas">Siglas <span class="text-danger">*</span></label>
                <input type="text" id="siglas" name="siglas" class="form-control" required maxlength="25"
                    autocomplete="off" placeholder="Código corto">
            </div>
        </div>
        <div class="col-sm-6" title="Color para los reportes">
            <div class="form-group">
                <label for="color">Color para reportes <span class="text-danger">*</span></label>
                <input type="text" id="color" name="color" class="form-control" required
                    placeholder="Ej. Blanco">
            </div>
        </div>
    </div>
    <h4 class="pv-form-seccion"><i class="fa fa-fw fa-sliders text-color_yura"></i> Apertura y producción</h4>
    <div class="row">
        <div class="col-sm-3 col-xs-6">
            <div class="form-group">
                <label for="minimo_apertura">Mínimo apertura</label>
                <input type="number" id="minimo_apertura" name="minimo_apertura" class="form-control"
                    min="0" onkeypress="return isNumber(event)">
            </div>
        </div>
        <div class="col-sm-3 col-xs-6">
            <div class="form-group">
                <label for="maximo_apertura">Máximo apertura</label>
                <input type="number" id="maximo_apertura" name="maximo_apertura" class="form-control"
                    min="0" onkeypress="return isNumber(event)">
            </div>
        </div>
        <div class="col-sm-3 col-xs-6">
            <div class="form-group">
                <label for="estandar">Estándar</label>
                <input type="number" id="estandar" name="estandar" class="form-control"
                    min="0" onkeypress="return isNumber(event)">
            </div>
        </div>
        <div class="col-sm-3 col-xs-6">
            <div class="form-group">
                <label for="tipo">Tipo</label>
                <select name="tipo" id="tipo" class="form-control">
                    <option value="P">Peso</option>
                    <option value="L">Longitud</option>
                </select>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="tallos_x_malla">Tallos por malla <span class="text-danger">*</span></label>
                <input type="number" id="tallos_x_malla" name="tallos_x_malla" class="form-control" min="1"
                    required onkeypress="return isNumber(event)" placeholder="Cantidad">
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="tallos_x_ramo_estandar">Tallos por ramo</label>
                <input type="number" id="tallos_x_ramo_estandar" name="tallos_x_ramo_estandar"
                    class="form-control" min="1" onkeypress="return isNumber(event)" placeholder="Cantidad estándar">
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="saldo_inicial">Saldo inicial</label>
                <input type="number" id="saldo_inicial" name="saldo_inicial" class="form-control" min="0"
                    onkeypress="return isNumber(event)" placeholder="Cantidad inicial para proyecciones">
            </div>
        </div>
    </div>
    <h4 class="pv-form-seccion"><i class="fa fa-fw fa-cog text-color_yura"></i> Configuración</h4>
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label for="compra_flor">Compra de flor</label>
                <select name="compra_flor" id="compra_flor" class="form-control">
                    <option value="0">No</option>
                    <option value="1">Sí</option>
                </select>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="receta">Usa receta</label>
                <select name="receta" id="receta" class="form-control">
                    <option value="0">No</option>
                    <option value="1">Sí</option>
                </select>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="codigo_latin">Código Latin</label>
                <input type="text" id="codigo_latin" name="codigo_latin" class="form-control"
                    placeholder="Código botánico">
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="codigo_exportacion">Código de exportación</label>
                <input type="text" id="codigo_exportacion" name="codigo_exportacion" class="form-control"
                    placeholder="Código comercial">
            </div>
        </div>
    </div>
</form>
