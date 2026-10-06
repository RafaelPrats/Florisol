<form id="form_add_proveedor" class="pv-modal-form">
    <div class="pv-form-intro">
        <i class="fa fa-truck"></i>
        <div><strong>Editar proveedor</strong>Mantén actualizados los datos de contacto y ubicación del proveedor.</div>
    </div>
    <h4 class="pv-form-seccion"><i class="fa fa-fw fa-building text-color_yura"></i> Información general</h4>
    <div class="row">
        <div class="col-sm-12">
            <div class="form-group">
                <label for="nombre">Nombre o razón social</label>
                <input type="text" id="nombre" name="nombre" class="form-control" required maxlength="250"
                    autocomplete="off" value="{{ $proveedor->nombre }}">
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="ruc">Identificación</label>
                <input type="text" id="ruc" name="ruc" class="form-control" maxlength="250"
                    autocomplete="off" value="{{ $proveedor->ruc }}">
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="codigo_pais">País</label>
                <select id="codigo_pais" name="codigo_pais" class="form-control">
                    <option value="">Selecciona un país</option>
                    @foreach ($paises as $p)
                        <option value="{{ $p->codigo }}"
                            {{ $p->codigo == $proveedor->codigo_pais ? 'selected' : '' }}>
                            {{ $p->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="provincia">Provincia</label>
                <input type="text" id="provincia" name="provincia" class="form-control" maxlength="250"
                    autocomplete="off" value="{{ $proveedor->provincia }}">
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="direccion_matriz">Dirección</label>
                <input type="text" id="direccion_matriz" name="direccion_matriz" class="form-control"
                    maxlength="250" autocomplete="off" value="{{ $proveedor->direccion_matriz }}">
            </div>
        </div>
    </div>
    <h4 class="pv-form-seccion"><i class="fa fa-fw fa-address-card-o text-color_yura"></i> Contacto</h4>
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label for="correo">Correo electrónico</label>
                <input type="text" id="correo" name="correo" class="form-control" maxlength="250"
                    autocomplete="off" value="{{ $proveedor->correo }}">
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="telefono">Teléfono</label>
                <input type="text" id="telefono" name="telefono" class="form-control" maxlength="250"
                    autocomplete="off" value="{{ $proveedor->telefono }}">
            </div>
        </div>
    </div>
    <input type="hidden" id="id_proveedor" name="id_proveedor" value="{{ $proveedor->id_configuracion_empresa }}">
</form>
