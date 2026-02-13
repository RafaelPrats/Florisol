<form id="form_add_cargo">
    <input type="hidden" id="id_cargo" value="">
    <div class="row">
        <div class="{{!isset($cargo->id_cargo) ? 'col-md-6' : 'col-md-12' }}">
            <div class="form-group">
                <label for="nombre_cargo">Nombre cargo</label>
                <input  type="text" id="nombre_cargo" name="nombre_cargo" class="form-control input-yura_default" required maxlength="250" autocomplete="off" value='{{isset($cargo->nombre) ? $cargo->nombre : ""}}'>
                <input class="form-control input-yura_default" type="hidden" value="{{isset($cargo->id_cargo) ? $cargo->id_cargo : ""}}">
            </div>
        </div>
        </div>
    </div>
</form>



