@extends('layouts.adminlte.login.master')

@section('titulo')
    Login
@endsection

@section('script_inicio')
    <script language="JavaScript" type="text/javascript" src="{{ url('js/rsa/jsbn.js') }}"></script>
    <script language="JavaScript" type="text/javascript" src="{{ url('js/rsa/jsbn2.js') }}"></script>
    <script language="JavaScript" type="text/javascript" src="{{ url('js/rsa/prng4.js') }}"></script>
    <script language="JavaScript" type="text/javascript" src="{{ url('js/rsa/rng.js') }}"></script>
    <script language="JavaScript" type="text/javascript" src="{{ url('js/rsa/rsa.js') }}"></script>
    <script language="JavaScript" type="text/javascript" src="{{ url('js/rsa/rsa2.js') }}"></script>
@endsection

@section('contenido')
    <main class="bench-login">
        <section class="bench-login-brand" aria-label="BenchFlow">
            <div class="bench-login-brand-top">
                <span class="bench-login-brand-mark"><i class="fa fa-leaf"></i></span>
                <span>BENCHFLOW</span>
            </div>
            <div class="bench-login-brand-copy">
                <span class="bench-login-eyebrow">GESTIÓN INTELIGENTE</span>
                <h1>Todo florece<br>cuando fluye.</h1>
                <p>Conecta tus equipos, procesos y fincas en un solo lugar.</p>
            </div>
            <div class="bench-login-botanical bench-login-botanical-one"></div>
            <div class="bench-login-botanical bench-login-botanical-two"></div>
            <div class="bench-login-brand-footer">
                <span class="bench-login-live-dot"></span>
                <span>Un espacio para crecer</span>
            </div>
        </section>

        <section class="bench-login-access">
            <div class="bench-login-access-inner">
                <a href="{{ url('') }}" class="bench-login-logo" aria-label="BenchFlow">
                    <img src="{{ url('images/Logo_Bench_Flow_verde_negro.png') }}" alt="BenchFlow">
                </a>
                <div class="bench-login-welcome">
                    <span>BIENVENIDO</span>
                    <h2>Inicia sesión</h2>
                    <p>Ingresa tus credenciales para continuar.</p>
                </div>

                <form action="{{ url('login') }}" method="post" id="form_login">
                    {!! csrf_field() !!}
                    <div class="bench-login-field">
                        <label for="username">Nombre de usuario</label>
                        <div class="bench-login-input">
                            <i class="fa fa-user-o" aria-hidden="true"></i>
                            <input type="text" class="form-control" placeholder="Tu usuario" id="username"
                                name="username" value="{{ old('username') }}" autofocus required autocomplete="off"
                                autocapitalize="none" spellcheck="false">
                        </div>
                    </div>

                    <div class="bench-login-field">
                        <label for="password">Contraseña</label>
                        <div class="bench-login-input">
                            <i class="fa fa-lock" aria-hidden="true"></i>
                            <input type="password" class="form-control" placeholder="Tu contraseña" id="password"
                                name="password" required autocomplete="current-password">
                            <button type="button" class="bench-login-password-toggle" id="toggle_password"
                                aria-label="Mostrar contraseña" aria-pressed="false" onclick="togglePasswordVisibility()">
                                <i class="fa fa-eye" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>

                    <input type="hidden" name="h_clave" id="h_clave" value="">

                    <button type="button" id="btn_login" class="btn bench-login-submit">
                        <span>Entrar a mi cuenta</span><i class="fa fa-arrow-right" aria-hidden="true"></i>
                    </button>
                </form>

                <div class="bench-login-security">
                    <i class="fa fa-shield" aria-hidden="true"></i>
                    <span>Tu acceso está protegido y cifrado.</span>
                </div>
            </div>
        </section>
    </main>

    <style>
        body.login-page {
            display: flex;
            min-height: 100vh;
            align-items: center;
            justify-content: center;
            overflow-x: hidden;
            padding: 24px;
            background:
                radial-gradient(ellipse at 10% 8%, rgba(0, 179, 136, .13), transparent 32%),
                radial-gradient(ellipse at 92% 96%, rgba(0, 55, 41, .1), transparent 36%),
                #f0f5f2;
        }

        .login-box {
            width: min(100%, 1000px);
            margin: 0;
        }

        .bench-login {
            display: grid;
            min-height: 590px;
            overflow: hidden;
            grid-template-columns: minmax(0, .91fr) minmax(0, 1.09fr);
            border: 1px solid rgba(0, 55, 41, .08);
            border-radius: 22px;
            background: #fff;
            box-shadow: 0 24px 70px rgba(0, 55, 41, .14);
        }

        .bench-login-brand {
            position: relative;
            display: flex;
            min-height: 590px;
            overflow: hidden;
            flex-direction: column;
            justify-content: space-between;
            padding: 34px 36px 27px;
            background:
                radial-gradient(circle at 88% 15%, rgba(103, 216, 177, .25), transparent 25%),
                linear-gradient(145deg, #064c3b 0%, #003729 58%, #002b20 100%);
            color: #fff;
        }

        .bench-login-brand::before,
        .bench-login-brand::after {
            position: absolute;
            border: 1px solid rgba(198, 244, 223, .12);
            border-radius: 50%;
            content: "";
            pointer-events: none;
        }

        .bench-login-brand::before {
            top: 37%;
            right: -27%;
            width: 420px;
            height: 420px;
        }

        .bench-login-brand::after {
            top: 42%;
            right: -16%;
            width: 320px;
            height: 320px;
        }

        .bench-login-brand-top,
        .bench-login-brand-copy,
        .bench-login-brand-footer {
            position: relative;
            z-index: 1;
        }

        .bench-login-brand-top {
            display: flex;
            align-items: center;
            gap: 9px;
            color: #e9fff6;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .18em;
        }

        .bench-login-brand-mark {
            display: grid;
            width: 32px;
            height: 32px;
            place-items: center;
            border: 1px solid rgba(222, 255, 239, .28);
            border-radius: 10px;
            background: rgba(255, 255, 255, .09);
            color: #7ce2b8;
            font-size: 15px;
        }

        .bench-login-brand-copy {
            max-width: 360px;
            margin-top: auto;
            margin-bottom: auto;
            padding: 54px 0 40px;
        }

        .bench-login-eyebrow {
            display: inline-block;
            margin-bottom: 14px;
            color: #8ce7c1;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .17em;
        }

        .bench-login-brand-copy h1 {
            margin: 0;
            color: #fff;
            font-size: clamp(34px, 4.2vw, 49px);
            font-weight: 600;
            letter-spacing: -.045em;
            line-height: 1.08;
        }

        .bench-login-brand-copy p {
            max-width: 285px;
            margin: 17px 0 0;
            color: rgba(239, 255, 248, .73);
            font-size: 14px;
            line-height: 1.65;
        }

        .bench-login-botanical {
            position: absolute;
            z-index: 0;
            border: 1px solid rgba(182, 241, 215, .2);
            border-radius: 100% 0 100% 0;
            transform: rotate(-26deg);
            pointer-events: none;
        }

        .bench-login-botanical-one {
            right: 4%;
            bottom: 18%;
            width: 115px;
            height: 194px;
        }

        .bench-login-botanical-two {
            right: 22%;
            bottom: 8%;
            width: 64px;
            height: 112px;
            opacity: .6;
            transform: rotate(24deg);
        }

        .bench-login-brand-footer {
            display: flex;
            align-items: center;
            gap: 8px;
            color: rgba(239, 255, 248, .68);
            font-size: 11px;
        }

        .bench-login-live-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #71dfa9;
            box-shadow: 0 0 0 4px rgba(113, 223, 169, .13);
        }

        .bench-login-access {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 42px clamp(28px, 6vw, 72px);
            background:
                radial-gradient(ellipse at 100% 0%, rgba(0, 179, 136, .055), transparent 32%),
                #fff;
        }

        .bench-login-access-inner {
            width: 100%;
            max-width: 365px;
        }

        .bench-login-logo {
            display: inline-flex;
            align-items: center;
            margin-bottom: 33px;
        }

        .bench-login-logo img {
            display: block;
            width: 190px;
            max-width: 72%;
            height: auto;
        }

        .bench-login-welcome {
            margin-bottom: 24px;
        }

        .bench-login-welcome > span {
            color: #15936e;
            font-size: 9px;
            font-weight: 700;
            letter-spacing: .16em;
        }

        .bench-login-welcome h2 {
            margin: 5px 0 4px;
            color: #003729;
            font-size: 27px;
            font-weight: 700;
            letter-spacing: -.035em;
        }

        .bench-login-welcome p {
            margin: 0;
            color: #758780;
            font-size: 12px;
        }

        .bench-login-field {
            margin-bottom: 16px;
        }

        .bench-login-field label {
            display: block;
            margin-bottom: 6px;
            color: #264a3e;
            font-size: 11px;
            font-weight: 700;
        }

        .bench-login-input {
            position: relative;
        }

        .bench-login-input > i {
            position: absolute;
            z-index: 1;
            top: 13px;
            left: 13px;
            color: #779187;
            font-size: 13px;
        }

        .bench-login-input .form-control {
            height: 43px;
            padding: 9px 42px 9px 38px;
            border: 1px solid #dce9e4;
            border-radius: 8px;
            background: #fbfdfc;
            box-shadow: none;
            color: #003729;
            font-size: 13px;
            transition: border-color .16s ease, box-shadow .16s ease, background .16s ease;
        }

        .bench-login-input .form-control::placeholder {
            color: #a0afa9;
        }

        .bench-login-input .form-control:focus {
            border-color: #00B388;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(0, 179, 136, .11);
        }

        .bench-login-password-toggle {
            position: absolute;
            top: 5px;
            right: 5px;
            display: grid;
            width: 33px;
            height: 33px;
            place-items: center;
            border: 0;
            border-radius: 6px;
            background: transparent;
            color: #789188;
        }

        .bench-login-password-toggle:hover,
        .bench-login-password-toggle:focus {
            outline: 0;
            background: #edf7f2;
            color: #087a5f;
        }

        .bench-login-submit {
            display: flex;
            width: 100%;
            min-height: 44px;
            align-items: center;
            justify-content: space-between;
            padding: 10px 14px 10px 17px;
            border: 1px solid #00a77e;
            border-radius: 8px;
            background: linear-gradient(105deg, #00B388, #009b74);
            box-shadow: 0 7px 16px rgba(0, 153, 114, .18);
            color: #fff;
            font-size: 12px;
            font-weight: 700;
            transition: transform .15s ease, box-shadow .15s ease, background .15s ease;
        }

        .bench-login-submit i {
            display: grid;
            width: 25px;
            height: 25px;
            place-items: center;
            border-radius: 7px;
            background: rgba(255, 255, 255, .17);
            font-size: 11px;
        }

        .bench-login-submit:hover,
        .bench-login-submit:focus {
            transform: translateY(-1px);
            border-color: #008f6c;
            background: linear-gradient(105deg, #00a77e, #008a68);
            box-shadow: 0 9px 19px rgba(0, 153, 114, .24);
            color: #fff;
            outline: 0;
        }

        .bench-login-submit:active {
            transform: translateY(0);
        }

        .bench-login-security {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            margin-top: 22px;
            color: #8a9a93;
            font-size: 10px;
        }

        .bench-login-security i {
            color: #00a77e;
        }

        #form_login label.error {
            display: block;
            margin-top: 5px;
            color: #b64d47;
            font-size: 10px;
            font-weight: 500;
        }

        @media (max-width: 767px) {
            body.login-page {
                padding: 16px;
            }

            .login-box {
                width: min(100%, 520px);
            }

            .bench-login {
                min-height: 0;
                grid-template-columns: 1fr;
                border-radius: 17px;
            }

            .bench-login-brand {
                min-height: 205px;
                padding: 20px 23px 17px;
            }

            .bench-login-brand-copy {
                max-width: 75%;
                padding: 24px 0 20px;
            }

            .bench-login-brand-copy h1 {
                font-size: 30px;
            }

            .bench-login-brand-copy p {
                max-width: 300px;
                margin-top: 8px;
                font-size: 12px;
            }

            .bench-login-eyebrow {
                margin-bottom: 8px;
                font-size: 9px;
            }

            .bench-login-brand-footer {
                display: none;
            }

            .bench-login-botanical-one {
                right: 3%;
                bottom: -36%;
                width: 94px;
                height: 155px;
            }

            .bench-login-botanical-two {
                right: 25%;
                bottom: -43%;
            }

            .bench-login-access {
                padding: 27px clamp(22px, 8vw, 42px) 24px;
            }

            .bench-login-logo {
                margin-bottom: 20px;
            }

            .bench-login-logo img {
                width: 160px;
            }

            .bench-login-welcome {
                margin-bottom: 18px;
            }

            .bench-login-welcome h2 {
                font-size: 24px;
            }
        }

        @media (max-width: 380px) {
            body.login-page {
                padding: 10px;
            }

            .bench-login-brand {
                min-height: 185px;
            }

            .bench-login-brand-copy {
                max-width: 84%;
            }

            .bench-login-brand-copy h1 {
                font-size: 27px;
            }

            .bench-login-access {
                padding-right: 18px;
                padding-left: 18px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .bench-login-input .form-control,
            .bench-login-submit {
                transition: none;
            }
        }
    </style>
@endsection

@section('script_final')
    <script>
        function togglePasswordVisibility() {
            var passwordInput = $('#password');
            var toggle = $('#toggle_password');
            var visible = passwordInput.attr('type') === 'password';

            passwordInput.attr('type', visible ? 'text' : 'password');
            toggle.attr('aria-label', visible ? 'Ocultar contraseña' : 'Mostrar contraseña');
            toggle.attr('aria-pressed', visible ? 'true' : 'false');
            toggle.find('i').toggleClass('fa-eye', !visible).toggleClass('fa-eye-slash', visible);
            passwordInput.trigger('focus');
        }

        $('#form_login').validate();

        $('#btn_login').on('click', function() {
            if ($('#form_login').valid()) {
                $('#username').val($('#username').val().toLowerCase());
                var publickey = "{{ $key }}";
                var rsakey = new RSAKey();
                rsakey.setPublic(publickey, "10001");
                var password = $('#password');
                $('#h_clave').val(rsakey.encrypt(password.val()));
                password.val('');

                $.LoadingOverlay('show');
                var formulario = $('#form_login');
                var formData = new FormData(formulario[0]);
                $.ajax({
                    url: formulario.attr('action'),
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function(retorno) {
                        if (retorno.success) {
                            location.href = '{{ url('') }}';
                        } else {
                            alerta_accion(retorno.mensaje, function() {
                                cargar_url('');
                            });
                        }
                        $.LoadingOverlay('hide');
                    },
                    error: function(retorno) {
                        console.log(retorno);
                        alerta(retorno.responseText);
                        alert('Hubo un problema en la envío de la información');
                        $.LoadingOverlay('hide');
                    }
                });
            }
        });
    </script>
@endsection
