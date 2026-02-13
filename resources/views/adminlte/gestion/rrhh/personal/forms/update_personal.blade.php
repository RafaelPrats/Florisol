<form id="form_actualiza_personal">
    <div class="row">
        <div class="col-md-3">
            <div class="form-group text-center">
                <label for="nombre">Nombre</label>
                <input type="text" id="nombre" name="nombre" class="form-control input-yura_default" required
                    maxlength="250" autocomplete="off" value='{!! !empty($dataPersonal->nombre) != '' ? $dataPersonal->nombre : '' !!}'>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="apellido">Apellido</label>
                <input type="text" id="apellido" name="apellido" class="form-control input-yura_default" required
                    maxlength="250" autocomplete="off" value='{!! !empty($dataPersonal->apellido) != '' ? $dataPersonal->apellido : '' !!}'>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="cedula_identidad">Cédula de Identidad</label>
                <input type="cedula_identidad" id="cedula_identidad" name="cedula_identidad"
                    class="form-control input-yura_default" required maxlength="250" autocomplete="off"
                    value='{!! !empty($dataPersonal->cedula_identidad) != '' ? $dataPersonal->cedula_identidad : '' !!}'>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="sucursal">Sucursal</label>
                <select name="id_sucursal" id="id_sucursal" class="form-control input-yura_default">
                    <option>seleccione</option>
                    @foreach ($sucursal as $p)
                        <option
                            {{ $p->id_sucursal == $detalle->id_sucursal ? 'selected' : '' }}
                            value="{{ $p->id_sucursal }}">{{ $p->nombre }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="id_area">Área</label>
                <select name="id_area" id="id_area" class="form-control input-yura_default"
                    onchange="seleccionar_area()">
                    <option>seleccione</option>
                    @foreach ($area as $p)
                        <option  {{ $p->id_area == $detalle->id_area ? 'selected' : '' }} value="{{ $p->id_area }}">
                            {{ $p->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="id_actividad">Actividad</label>
                <select name="id_actividad" id="id_actividad" class="form-control input-yura_default"
                    onchange="seleccionar_actividad()">
                    @foreach ($actividad as $p)
                        <option value="{{ $p->id_actividad }}"
                            {{ $p->id_actividad == $detalle->id_actividad ? 'selected' : '' }}>{{ $p->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="id_mano_obra">Mano de Obra</label>
                <select name="id_mano_obra" id="id_mano_obra" class="form-control input-yura_default">
                    @foreach ($mano_obra as $p)
                        <option value="{{ $p->id_mano_obra }}"
                            {{ $p->id_mano_obra == $detalle->id_mano_obra ? 'selected' : '' }}>{{ $p->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="sueldo">Sueldo</label>
                <input type="text" id="sueldo" name="sueldo" class="form-control input-yura_default" required="" maxlength="250" autocomplete="off" value="{{$detalle->sueldo}}">
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                
                <label for="tipo_rol">Rol</label>
                <select name="tipo_rol" id="id_tipo_rol" class="form-control input-yura_default">
                    <option>seleccione</option> 
                    @foreach($tipo_rol as $p)
                        <option value="{{$p->id_tipo_rol}}" {{$p->id_tipo_rol == $detalle->id_tipo_rol ? 'selected' : ''}}>{{$p->nombre}}</option>
                    @endforeach
                </select>   
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="grupo">Agrupación</label>
                <select name="grupo" id="id_grupo" class="form-control input-yura_default">
                    <option>seleccione</option>
                    @foreach($grupo as $p)
                        <option value="{{$p->id_grupo}}" {{$p->id_grupo == $detalle->id_grupo ? 'selected' : ''}}>{{$p->nombre}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <div class="input-group">
                    <span class="input-group-addon" id="basic-addon1">Cargue la foto del personal jpg, png</span>
                    <input type="file" class="form-control" name="file_personal" accept="jpg,png">
                </div>
            </div>
        </div>
    </div>
</form>
<div class="text-center">
    <button type="button" class="btn btn-yura_primary" onclick="actualiza_personal()">
        <i class="fa fa-fw fa-save"></i> Guardar
    </button>
</div>

<script>
    function actualiza_personal() {

        $.LoadingOverlay('show');
        formulario = $('#form_actualiza_personal');
        var formData = new FormData(formulario[0]);

        formData.append('_token', '{{ csrf_token() }}');
        formData.append('id_personal', '{{ $dataPersonal->id_personal }}');
        formData.append('id_personal_detalle', '{{ $detalle->id_personal_detalle }}');
        
        //hacemos la petición ajax
        $.ajax({
            url: '{{ url('personal/actualiza_personal') }}',
            type: 'POST',
            data: formData,
            dataType: 'json',
            cache: false,
            contentType: false,
            processData: false,
            success: function (retorno) {
               
                $.LoadingOverlay('hide');
                alerta_accion(retorno.mensaje)
                cerrar_modals();
                trabajador();
                
            },
            error: function (retorno) {

                $.LoadingOverlay('hide');
                alerta_errores(retorno.responseText)
              
            }

        })
        /*datos = {
            _token: '{{ csrf_token() }}',
            id_personal: $('#id_personal').val(),
            id_personal_detalle: $('#id_personal_detalle').val(),
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
        post_jquery('{{ url('personal/actualiza_personal') }}', datos, function() {

            cerrar_modals();
            trabajador();

        });*/
    }
