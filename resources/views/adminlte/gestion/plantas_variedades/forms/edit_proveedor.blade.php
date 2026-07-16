<form id="form_add_proveedor">
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label for="nombre">Nombre</label>
                <input type="text" id="nombre" name="nombre" class="form-control text-center" required maxlength="250"
                    autocomplete="off" value="{{ $proveedor->nombre }}">
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="ruc">Identificacion</label>
                <input type="text" id="ruc" name="ruc" class="form-control text-sm" maxlength="250"
                    autocomplete="off" value="{{ $proveedor->ruc }}">
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="codigo_pais">Pais</label>
                <select id="codigo_pais" name="codigo_pais" class="form-control">
                    <option value="">Seleccione</option>
                    @foreach ($paises as $p)
                        <option value="{{ $p->codigo }}"
                            {{ $p->codigo == $proveedor->codigo_pais ? 'selected' : '' }}>
                            {{ $p->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="provincia">Provincia</label>
                <input type="text" id="provincia" name="provincia" class="form-control text-sm" maxlength="250"
                    autocomplete="off" value="{{ $proveedor->provincia }}">
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="direccion_matriz">Direccion</label>
                <input type="text" id="direccion_matriz" name="direccion_matriz" class="form-control text-sm"
                    maxlength="250" autocomplete="off" value="{{ $proveedor->direccion_matriz }}">
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="correo">Correo</label>
                <input type="text" id="correo" name="correo" class="form-control text-sm" maxlength="250"
                    autocomplete="off" value="{{ $proveedor->correo }}">
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="telefono">Telefono</label>
                <input type="text" id="telefono" name="telefono" class="form-control text-sm" maxlength="250"
                    autocomplete="off" value="{{ $proveedor->telefono }}">
            </div>
        </div>
    </div>
    <input type="hidden" id="id_proveedor" name="id_proveedor" value="{{ $proveedor->id_configuracion_empresa }}">
</form>
