<form id="form_add_proveedor">
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label for="nombre">Nombre</label>
                <input type="text" id="nombre" name="nombre" class="form-control text-center" required maxlength="250"
                    autocomplete="off" value="{{ $proveedor->nombre }}">
            </div>
        </div>
    </div>
    <input type="hidden" id="id_proveedor" name="id_proveedor" value="{{ $proveedor->id_configuracion_empresa }}">
</form>
