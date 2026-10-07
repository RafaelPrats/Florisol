<div class="usuarios-security-wrap">
    <form id="form_edit_password">
    <div class="row">
        <div class="col-md-6">
            <label for="password">Contraseña</label>
            <div class="form-group input-group has-feedback" style="width: 100%">
                    <span class="input-group-btn">
                        <button type="button" class="btn text-black" title="Mostrar/ocultar" id="btn_mostrar_ocultar_reg"
                                onclick="mostrar_ocultar_passw()">
                            <i class="fa fa-fw fa-eye"></i>
                        </button>
                    </span>
                <input type="password" name="password" id="password" class="form-control" placeholder="Contraseña" required
                       maxlength="250" pattern="^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,}$">
                <span class="glyphicon glyphicon-lock form-control-feedback"></span>
            </div>
        </div>
        <div class="col-md-6">
            <label for="password_b">Repite la contraseña</label>
            <div class="form-group has-feedback">
                <input type="password" name="password_b" id="password_b" class="form-control" placeholder="Repite la contraseña"
                       required maxlength="250">
                <span class="glyphicon glyphicon-lock form-control-feedback"></span>
            </div>
        </div>
    </div>
    <input type="hidden" name="h_clave" id="h_clave" value="">
        <div class="usuarios-security-actions">
            <span><i class="fa fa-shield"></i> Usa una contraseña robusta para mayor seguridad.</span>
            <button type="button" class="btn btn-usuarios-security-save" onclick="update_password()">
                <i class="fa fa-save"></i> Actualizar contraseña
            </button>
        </div>
    </form>
</div>

<style>
    .usuarios-security-wrap {
        --usuarios-green: #00B388;
        --usuarios-muted: #5A7177;
        --usuarios-line: #dce9e4;
    }

    .usuarios-security-wrap label {
        margin-bottom: 4px;
        color: #35574b;
        font-size: 11px;
        font-weight: 700;
    }

    .usuarios-security-wrap .form-control {
        min-height: 35px;
        border-color: var(--usuarios-line);
        border-radius: 5px;
        box-shadow: none;
        font-size: 12px;
    }

    .usuarios-security-wrap .form-control:focus {
        border-color: var(--usuarios-green);
        box-shadow: 0 0 0 2px rgba(0, 179, 136, .1);
    }

    .usuarios-security-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-top: 4px;
        padding-top: 8px;
        border-top: 1px solid var(--usuarios-line);
    }

    .usuarios-security-actions > span {
        color: var(--usuarios-muted);
        font-size: 10px;
    }

    .usuarios-security-actions > span i {
        margin-right: 3px;
        color: var(--usuarios-green);
    }

    .btn-usuarios-security-save {
        min-width: 150px;
        min-height: 34px;
        border: 1px solid var(--usuarios-green);
        border-radius: 5px;
        background: var(--usuarios-green);
        color: #fff;
        font-size: 11px;
        font-weight: 700;
    }

    .btn-usuarios-security-save:hover,
    .btn-usuarios-security-save:focus {
        border-color: #008f6c;
        background: #008f6c;
        color: #fff;
    }

    @media (max-width: 520px) {
        .usuarios-security-actions {
            align-items: stretch;
            flex-direction: column;
        }

        .btn-usuarios-security-save {
            width: 100%;
        }
    }
</style>

<script>
    $('#form_edit_password').validate({
        messages: {
            password: {
                pattern: 'Al menos un número, una mayúscula y una minúscula. Más de 6 caracteres'
            }
        }
    });

    function update_password() {
        if ($('#form_edit_password').valid()) {
            passw = {
                password: $('#password').val(),
                password_b: $('#password_b').val(),
            };
            if (passw['password'] == passw['password_b']) {
                var password = $('#password');
                var password_b = $('#password_b');
                var h_clave = $('#h_clave');

                var publickey = "{{$key}}";
                var rsakey = new RSAKey();
                rsakey.setPublic(publickey, "10001");

                h_clave.val(rsakey.encrypt(password.val()));
                password.val('');
                password_b.val('');

                datos = {
                    _token: '{{csrf_token()}}',
                    id_usuario: '{{$usuario->id_usuario}}',
                    passw: $('#h_clave').val()
                };
                $.LoadingOverlay('show');
                post_jquery('{{url('usuarios/update_password')}}', datos, function () {
                    for (i = 0; i < arreglo_modals_form.length; i++) {
                        arreglo_modals_form[i].close();
                    }
                    arreglo_modals_form = [];
                });
                $.LoadingOverlay('hide');
            }
            else {
                alerta('<p class="text-center">Las contraseñas deben ser iguales</p>');
            }
        }
    }
</script>