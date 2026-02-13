<form id="form_add_personal">
    <input type="hidden" id="id_personal" value=" {!! isset($dataPersonal->id_personal) ? $dataPersonal->id_personal : '' !!}">
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
                        <option value="{{ $p->id_sucursal }}">{{ $p->nombre }}</option>
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
                        <option value="{{ $p->id_area }}">{{ $p->nombre }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="id_actividad">Actividad</label>
                <select name="id_actividad" id="id_actividad" class="form-control input-yura_default"
                    onchange="seleccionar_actividad()">
                    <option>seleccione</option>
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="id_mano_obra">Mano de Obra</label>
                <select name="id_mano_obra" id="id_mano_obra" class="form-control input-yura_default">
                    <option>seleccione</option>
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="sueldo">Sueldo</label>
                <input type="text" id="sueldo" name="sueldo" class="form-control input-yura_default" required="" maxlength="250" autocomplete="off" value="">
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="tipo_rol">Rol</label>
                <select name="tipo_rol" id="id_tipo_rol" class="form-control input-yura_default">
                    <option>seleccione</option> 
                    @foreach($tipo_rol as $p)
                        <option value="{{$p->id_tipo_rol}}">{{$p->nombre}}</option>
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
                        <option value="{{$p->id_grupo}}">{{$p->nombre}}</option>
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
    <button type="button" class="btn btn-yura_primary" onclick="crear_personal()">
        <i class="fa fa-fw fa-save"></i> Guardar
    </button>
</div>

<script>
    function crear_personal() {
  
        $.LoadingOverlay('show');
        formulario = $('#form_add_personal');
        var formData = new FormData(formulario[0]);

        formData.append('_token', '{{ csrf_token() }}');
        
        //hacemos la petición ajax
        $.ajax({
            url: '{{ url('personal/store_personal') }}',
            type: 'POST',
            data: formData,
            dataType: 'json',
            cache: false,
            contentType: false,
            processData: false,
            success: function (retorno) {
               
                $.LoadingOverlay('hide');
                alerta_accion(retorno.mensaje, function () {
                    $('#tipo').val('cedula_identidad')
                    $('#estado').val('1')
                    $('#busqueda_personal').val(datos['cedula_identidad'])

                    trabajador();
                    //cerrar_modals();
                });
                
            },
            error: function (retorno) {

                $.LoadingOverlay('hide');
                alerta_errores(retorno.responseText)
              
            }

        })
    }

    function seleccionar_discapacidad() {
        if ($('#discapacidad').val() == 'S')
            $('#porcentaje_discapacidad').prop('disabled', false)
        else
            $('#porcentaje_discapacidad').prop('disabled', true)
    }

    function seleccionar_actividad() {
        datos = {
            id_actividad: $('#id_actividad').val(),
        }
        get_jquery('{{ url('personal/seleccionar_actividad') }}', datos, function(retorno) {
            $('#id_mano_obra').html(retorno);
        })
    }
</script>
