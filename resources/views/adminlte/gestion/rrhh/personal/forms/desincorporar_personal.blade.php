<form id="form_desincorporar_personal">

    <div class="row">
        <div class="col-md-2">
            <div class="form-group text-center">
                <label for="nombre">Nombre</label>
                <input autocomplete="off" disabled type="text" id="nombre" name="nombre"
                       class="form-control input-yura_default" required maxlength="250"
                       value="{{$dataPersonal->nombre}}">
            </div>
        </div>

        <div class="col-md-2">
            <div class="form-group">
                <label for="apellido">Apellido</label>
                <input autocomplete="off" disabled type="text" id="apellido" name="apellido"
                       class="form-control input-yura_default" required maxlength="250"
                       value="{{$dataPersonal->apellido}}">
            </div>
        </div>

        <div class="col-md-2">
            <div class="form-group">
                <label for="cedula_identidad">Cédula de Identidad</label>
                <input disabled type="number" id="cedula_identidad" name="cedula_identidad"
                       class="form-control input-yura_default" required maxlength="250" autocomplete="off"
                       value="{{$dataPersonal->cedula_identidad}}">
            </div>
        </div>

        <div class="col-md-2">
            <div class="form-group">
                <label for="fecha_desvinculacion">F. de Desvinculación</label>
                <input type="date" id="fecha_desvinculacion" name="fecha_desvinculacion" required
                       class="form-control input-yura_default"
                       value="">
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label for="id_causa_desvinculacion">Causa de Desvinculación</label>
                <select name="id_causa_desvinculacion" id="id_causa_desvinculacion"
                        class="form-control input-yura_default">
                    <option value="">seleccione</option>
                    @foreach($causa_desvinculacion as $p)
                        <option value="{{$p->id_causa_desvinculacion}}">{{$p->nombre}}</option>
                    @endforeach
                </select>
            </div>
        </div>


    </div>

</form>
<div class="text-center">
    <button type="button" class="btn btn-yura_primary" onclick="desincorporar_personal()">
        <i class="fa fa-fw fa-save"></i> Guardar
    </button>
</div>
<input type="hidden" id="id_personal" value="{{$dataPersonal->id_personal}}">
<input type="hidden" id="id_personal_detalle" value="{{$detalle->id_personal_detalle}}">
<script>
    function desincorporar_personal() {
        datos = {
            _token: '{{csrf_token()}}',
            id_personal: $('#id_personal').val(),
            id_personal_detalle: $('#id_personal_detalle').val(),
            fecha_desvinculacion: $('#fecha_desvinculacion').val(),
            id_causa_desvinculacion: $('#id_causa_desvinculacion').val(),

        };
        post_jquery('{{url('personal/desincorporar_persona')}}', datos, function () {
            cerrar_modals();
            trabajador()
        });
    }
</script>
