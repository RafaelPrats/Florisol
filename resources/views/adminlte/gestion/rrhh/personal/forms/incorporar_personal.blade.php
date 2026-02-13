<form id="form_incorporar_personal">

    <div class="row">
        <div class="col-md-3">
            <div class="form-group text-center">
                <label for="nombre">Nombre</label>
                <input autocomplete="off" disabled type="text" id="nombre" name="nombre" required
                    class="form-control input-yura_default" required maxlength="250" value="{{ $dataPersonal->nombre }}">
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label for="apellido">Apellido</label>
                <input autocomplete="off" disabled type="text" id="apellido" name="apellido"
                    class="form-control input-yura_default" required maxlength="250" required
                    value="{{ $dataPersonal->apellido }}">
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label for="cedula_identidad">Cédula de Identidad</label>
                <input disabled type="number" id="cedula_identidad" name="cedula_identidad" required
                    class="form-control input-yura_default" required maxlength="250" autocomplete="off"
                    value="{{ $dataPersonal->cedula_identidad }}">
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label for="fecha_desvinculacion">F. de Reingreso</label>
                <input type="date" id="fecha_ingreso" name="fecha_ingreso" required
                    class="form-control input-yura_default" value="{{now()->format('Y-m-d')}}">
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="fecha_desvinculacion">Labor</label>
                <select name="id_mano_obra" id="id_mano_obra" class="form-control input-yura_default" required>
                    <option>seleccione</option>
                    @foreach ($manoObra as $mo)
                        <option value="{{ $mo->id_mano_obra }}" {{$detalle->id_mano_obra == $mo->id_mano_obra ? 'selected': ''}}>
                            {{ $mo->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="fecha_desvinculacion">Sueldo</label>
                <input type="number" id="sueldo" name="sueldo" required value="{{$detalle->sueldo}}"
                    class="form-control input-yura_default">
            </div>
        </div>
    </div>
</form>
<div class="text-center">
    <button type="button" class="btn btn-yura_primary" onclick="reincorporar_personal()">
        <i class="fa fa-fw fa-save"></i> Guardar
    </button>
</div>

<input type="hidden" id="id_personal" value="{{ $dataPersonal->id_personal }}">
<input type="hidden" id="id_personal_detalle" value="{{ $detalle->id_personal_detalle }}">
<script>
    function reincorporar_personal() {

        if($("#form_incorporar_personal").valid()){
            datos = {
                _token: '{{ csrf_token() }}',
                id_personal: $('#id_personal').val(),
                nombre: $('#nombre').val(),
                apellido: $('#apellido').val(),
                cedula_identidad: $('#cedula_identidad').val(),
                fecha_nacimiento: $('#fecha_nacimiento').val(),
                id_sexo: $('#id_sexo').val(),
                fecha_ingreso: $('#fecha_ingreso').val(),
                id_grado_instruccion: $('#id_grado_instruccion').val(),
                id_estado_civil: $('#id_estado_civil').val(),
                id_nacionalidad: $('#id_nacionalidad').val(),
                telef: $('#telef').val(),
                cargas_familiares: $('#cargas_familiares').val(),
                id_tipo_contrato: $('#id_tipo_contrato').val(),
                lugar_residencia: $('#lugar_residencia').val(),
                direccion: $('#direccion').val(),
                correo: $('#correo').val(),
                discapacidad: $('#discapacidad').val(),
                porcentaje_discapacidad: $('#porcentaje_discapacidad').val(),
                id_cargo: $('#id_cargo').val(),
                id_tipo_pago: $('#id_tipo_pago').val(),
                sueldo: $('#sueldo').val(),
                id_banco: $('#id_banco').val(),
                id_tipo_cuenta: $('#id_tipo_cuenta').val(),
                numero_cuenta: $('#numero_cuenta').val(),
                id_tipo_rol: $('#id_tipo_rol').val(),
                id_sucursal: $('#id_sucursal').val(),
                id_departamento: $('#id_departamento').val(),
                id_area: $('#id_area').val(),
                id_actividad: $('#id_actividad').val(),
                id_mano_obra: $('#id_mano_obra').val(),
                id_grupo: $('#id_grupo').val(),
                id_grupo_interno: $('#id_grupo_interno').val(),
                id_plantilla: $('#id_plantilla').val(),
                id_relacion_laboral: $('#id_relacion_laboral').val(),
                id_detalle_contrato: $('#id_detalle_contrato').val(),
                id_seguro: $('#id_seguro').val(),
                n_afiliacion: $('#n_afiliacion').val(),

            };
            post_jquery('{{ url('personal/reincorporar_personal') }}', datos, function() {

                cerrar_modals();
                trabajador();

            });
        }   
    }
