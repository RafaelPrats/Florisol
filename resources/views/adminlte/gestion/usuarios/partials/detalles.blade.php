<div class="usuario-detalle">
    <div class="row">
        <div class="col-md-3">
            <div class="usuario-profile-card">
                <div class="usuario-profile-body">
                    <a href="javascript:void(0)" onclick="$('#box-edit-image').toggleClass('hidden')">
                        <img class="profile-user-img img-responsive img-circle usuario-profile-photo"
                            id="img_perfil" src="{{ url('storage/imagenes') . '/' . $usuario->imagen_perfil }}"
                            alt="Foto de perfil">
                    </a>
                    <h3 class="usuario-profile-name">{{ $usuario->nombre_completo }}</h3>
                    <p class="usuario-profile-role">{{ $usuario->rol()->nombre }}</p>
                    <small class="usuario-profile-hint">Haz clic en la foto para cambiarla</small>
                </div>
            </div>

            <div class="usuario-profile-card hidden" id="box-edit-image">
                <div class="usuario-profile-body">
                    <form action="{{ url('usuarios/update_image_perfil') }}" method="post" id="form_edit_imagen_perfil">
                        {!! csrf_field() !!}
                        <div class="form-group">
                            <label for="imagen_perfil">Imagen de perfil</label>
                            <input type="file" class="form-control file" id="imagen_perfil" name="imagen_perfil"
                                accept="image/jpeg">
                        </div>
                        <input type="hidden" id="id_usuario" name="id_usuario" value="{{ $usuario->id_usuario }}">
                        <button type="button" class="btn btn-usuario-guardar-imagen" onclick="update_image_perfil()">
                            <i class="fa fa-save"></i> Guardar imagen
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-9">
            <div class="usuario-tabs">
                <ul class="nav nav-tabs">
                    <li class="active tab-detalles"><a href="#datos_usuarios" data-toggle="tab">Detalles</a></li>
                    <li class="tab-detalles"><a href="#seguridad" data-toggle="tab">Seguridad</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="datos_usuarios">
                        @include('adminlte.gestion.usuarios.forms.edit_usuario')
                    </div>
                    <div class="tab-pane" id="seguridad">
                        @include('adminlte.gestion.usuarios.forms.seguridad')
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .usuario-detalle {
        --usuarios-green: #00B388;
        --usuarios-ink: #003729;
        --usuarios-muted: #5A7177;
        --usuarios-line: #dce9e4;
        color: var(--usuarios-ink);
    }

    .usuario-profile-card {
        border: 1px solid var(--usuarios-line);
        border-radius: 9px;
        background: #fff;
    }

    .usuario-profile-body {
        padding: 12px;
        text-align: center;
    }

    .usuario-profile-photo {
        transition: box-shadow .15s ease;
    }

    .usuario-profile-photo:hover {
        box-shadow: 0 0 0 3px rgba(0, 179, 136, .2);
    }

    .usuario-profile-name {
        margin: 9px 0 2px;
        color: var(--usuarios-ink);
        font-size: 17px;
        font-weight: 700;
    }

    .usuario-profile-role {
        margin-bottom: 3px;
        color: var(--usuarios-muted);
        font-size: 12px;
    }

    .usuario-profile-hint {
        color: var(--usuarios-muted);
        font-size: 10px;
    }

    .usuario-profile-body .form-control {
        border-color: var(--usuarios-line);
        box-shadow: none;
        font-size: 12px;
    }

    .btn-usuario-guardar-imagen {
        width: 100%;
        margin-top: 7px;
        border: 1px solid var(--usuarios-green);
        border-radius: 5px;
        background: var(--usuarios-green);
        color: #fff;
        font-size: 12px;
        font-weight: 700;
    }

    .btn-usuario-guardar-imagen:hover,
    .btn-usuario-guardar-imagen:focus {
        border-color: #008f6c;
        background: #008f6c;
        color: #fff;
    }

    .usuario-tabs {
        border: 1px solid var(--usuarios-line);
        border-radius: 9px;
        background: #fff;
    }

    .usuario-tabs .nav-tabs {
        border-bottom: 1px solid var(--usuarios-line);
        background: #f4faf7;
    }

    .usuario-tabs .nav-tabs > li > a {
        border: 0;
        border-radius: 0;
        color: #42675b;
        font-size: 11px;
        font-weight: 700;
    }

    .usuario-tabs .nav-tabs > li.active > a,
    .usuario-tabs .nav-tabs > li.active > a:focus,
    .usuario-tabs .nav-tabs > li.active > a:hover {
        border: 0;
        border-bottom: 2px solid var(--usuarios-green);
        background: #fff;
        color: var(--usuarios-ink);
    }

    .usuario-tabs .tab-content {
        padding: 12px;
    }
</style>

<script>
    inicializa('file');
    $('.fileinput-cancel').hide();

    function update_image_perfil() {
        $.LoadingOverlay('show');
        formulario = $('#form_edit_imagen_perfil');
        var formData = new FormData(formulario[0]);
        $.ajax({
            url: formulario.attr('action'),
            type: 'POST',
            data: formData,
            dataType: 'json',
            cache: false,
            contentType: false,
            processData: false,
            success: function(retorno2) {
                if (retorno2.success) {
                    alerta_accion(retorno2.mensaje, function() {
                        $.LoadingOverlay('show');
                        $.post('{{ url('usuarios/get_usuario_json') }}', {
                            _token: '{{ csrf_token() }}',
                            id_usuario: '{{ $usuario->id_usuario }}'
                        }, function(retorno) {
                            $('#img_perfil').prop('src', '{{ url('storage/imagenes') }}/' + retorno.user['imagen_perfil']);
                            $('#img_perfil_menu_izquierda').prop('src', '{{ url('storage/imagenes') }}/' + retorno.user['imagen_perfil']);
                            $('#img_perfil_menu_superior').prop('src', '{{ url('storage/imagenes') }}/' + retorno.user['imagen_perfil']);
                            $('#img_perfil_menu_superior_2').prop('src', '{{ url('storage/imagenes') }}/' + retorno.user['imagen_perfil']);
                            $('#box-edit-image').toggleClass('hidden');
                        }, 'json').fail(function(retorno) {
                            console.log(retorno);
                            alerta_errores(retorno.responseText);
                            alert('Ha ocurrido un problema al obtener el cambio');
                        }).always(function() {
                            $.LoadingOverlay('hide');
                        });
                    });
                } else {
                    alerta(retorno2.mensaje);
                }
                $.LoadingOverlay('hide');
            },
            error: function(retorno2) {
                console.log(retorno2);
                alerta(retorno2.responseText);
                alert('Hubo un problema en la envío de la información');
                $.LoadingOverlay('hide');
            }
        });
        $.LoadingOverlay('hide');
    }

    $('.tab-detalles').on('mouseover', function() {
        $(this).addClass('sombra_estandar');
    });
    $('.tab-detalles').on('mouseleave', function() {
        $(this).removeClass('sombra_estandar');
    });
</script>
