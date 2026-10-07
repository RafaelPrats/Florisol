<div class="usuarios-edit-wrap">
    <form id="form_edit_usuario">
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label for="nombre_completo">Nombre completo</label>
                <input type="text" id="nombre_completo" name="nombre_completo" class="form-control" required maxlength="250"
                       autocomplete="off" placeholder="Escriba el nombre completo" value="{{$usuario->nombre_completo}}">
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-3">
            <div class="form-group has-feedback">
                <label for="username">Nombre de usuario</label>
                <input type="text" id="username" name="username" class="form-control" required maxlength="250" autocomplete="off"
                       placeholder="Escriba el nombre de usuario" value="{{$usuario->username}}">
                <span class="glyphicon glyphicon-user form-control-feedback"></span>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="id_rol">Rol</label>
                <select name="id_rol" id="id_rol" class="form-control" required>
                    <option value="">Seleccione un rol</option>
                    @foreach($roles as $item)
                        <option value="{{$item->id_rol}}"
                                {{$usuario->id_rol == $item->id_rol ? 'selected' : ''}}>{{$item->nombre}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group has-feedback">
                <label for="correo">Correo</label>
                <input type="email" id="correo" name="correo" class="form-control" required maxlength="250" autocomplete="off"
                       placeholder="info@yura.com" value="{{$usuario->correo}}">
                <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
            </div>
        </div>
    </div>
        <div class="usuarios-edit-actions">
            <span><i class="fa fa-info-circle"></i> Guarda para aplicar cambios de perfil y rol.</span>
            <button type="button" class="btn btn-usuarios-edit-save" onclick="update_usuario()">
                <i class="fa fa-save"></i> Guardar cambios
            </button>
        </div>
    </form>
</div>

<style>
    .usuarios-edit-wrap {
        --usuarios-green: #00B388;
        --usuarios-muted: #5A7177;
        --usuarios-line: #dce9e4;
    }

    .usuarios-edit-wrap .form-group {
        margin-bottom: 9px;
    }

    .usuarios-edit-wrap label {
        margin-bottom: 4px;
        color: #35574b;
        font-size: 11px;
        font-weight: 700;
    }

    .usuarios-edit-wrap .form-control {
        min-height: 35px;
        border-color: var(--usuarios-line);
        border-radius: 5px;
        box-shadow: none;
        font-size: 12px;
    }

    .usuarios-edit-wrap .form-control:focus {
        border-color: var(--usuarios-green);
        box-shadow: 0 0 0 2px rgba(0, 179, 136, .1);
    }

    .usuarios-edit-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-top: 4px;
        padding-top: 8px;
        border-top: 1px solid var(--usuarios-line);
    }

    .usuarios-edit-actions > span {
        color: var(--usuarios-muted);
        font-size: 10px;
    }

    .usuarios-edit-actions > span i {
        margin-right: 3px;
        color: var(--usuarios-green);
    }

    .btn-usuarios-edit-save {
        min-width: 130px;
        min-height: 34px;
        border: 1px solid var(--usuarios-green);
        border-radius: 5px;
        background: var(--usuarios-green);
        color: #fff;
        font-size: 11px;
        font-weight: 700;
    }

    .btn-usuarios-edit-save:hover,
    .btn-usuarios-edit-save:focus {
        border-color: #008f6c;
        background: #008f6c;
        color: #fff;
    }

    @media (max-width: 520px) {
        .usuarios-edit-actions {
            align-items: stretch;
            flex-direction: column;
        }

        .btn-usuarios-edit-save {
            width: 100%;
        }
    }
</style>

<script>
    $('#form_edit_usuario').validate({
        messages: {
            nombre_completo: {
                pattern: 'Formato incorrecto para un nombre'
            },
        }
    });

    function update_usuario() {
        if ($('#form_edit_usuario').valid()) {
            $.LoadingOverlay('show');
            datos = {
                _token: '{{csrf_token()}}',
                id_usuario: '{{$usuario->id_usuario}}',
                nombre_completo: $('#nombre_completo').val(),
                username: $('#username').val(),
                correo: $('#correo').val(),
                id_rol: $('#id_rol').val(),
            };
            $.post('{{url('usuarios/update_usuario')}}', datos, function (retorno) {
                if (retorno.success) {
                    alerta_accion(retorno.mensaje, function () {
                        for (i = 0; i < arreglo_modals_form.length; i++) {
                            arreglo_modals_form[i].close();
                        }
                        arreglo_modals_form = [];
                        buscar_listado();
                    });
                } else {
                    alerta(retorno.mensaje);
                }
            }, 'json').fail(function (retorno) {
                console.log(retorno);
                alerta_errores(retorno.responseText);
                alert('Ha ocurrido un problema al enviar la información');
            }).always(function () {
                $.LoadingOverlay('hide');
            });
        }
    }

</script>