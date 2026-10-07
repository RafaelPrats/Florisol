@extends('layouts.adminlte.master')

@section('titulo')
    Usuarios
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
    <section class="content-header">
        <h1>
            Usuarios
            <small>módulo de administrador</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="javascript:void(0)" onclick="cargar_url('')"><i class="fa fa-home"></i> Inicio</a></li>
            <li>{{ $submenu->menu->grupo_menu->nombre }}</li>
            <li>{{ $submenu->menu->nombre }}</li>
            <li class="active">
                <a href="javascript:void(0)" onclick="cargar_url('{{ $submenu->url }}')">
                    <i class="fa fa-refresh"></i> {{ $submenu->nombre }}
                </a>
            </li>
        </ol>
    </section>

    <section class="content usuarios-page">
        <div class="usuarios-heading">
            <span class="usuarios-heading-icon"><i class="fa fa-users"></i></span>
            <div class="usuarios-heading-copy">
                <h2>Administración de usuarios</h2>
                <p>Busca cuentas, consulta detalles y configura sus accesos.</p>
            </div>
            <div class="usuarios-heading-actions">
                <button type="button" class="btn btn-usuarios-outline" onclick="exportar_usuarios()">
                    <i class="fa fa-file-excel-o"></i><span>Exportar</span>
                </button>
                <button type="button" class="btn btn-usuarios-primary" onclick="add_usuario()">
                    <i class="fa fa-plus"></i><span>Nuevo usuario</span>
                </button>
            </div>
        </div>

        <section class="usuarios-search-card">
            <div class="usuarios-search-title">
                <i class="fa fa-search"></i>
                <div>
                    <strong>Buscar usuarios</strong>
                    <small>Busca por nombre, correo, usuario o rol.</small>
                </div>
            </div>
            <div class="usuarios-search-controls">
                <div class="usuarios-search-input">
                    <i class="fa fa-search"></i>
                    <input type="search" class="form-control" placeholder="Nombre, correo, usuario o rol..."
                        id="busqueda_usuarios" name="busqueda_usuarios" autocomplete="off"
                        onkeydown="if (event.key === 'Enter') buscar_listado()">
                </div>
                <button type="button" class="btn btn-usuarios-primary usuarios-search-button"
                    onclick="buscar_listado()">
                    <i class="fa fa-search"></i> Buscar
                </button>
            </div>
        </section>

        <section class="usuarios-list-card">
            <div class="usuarios-list-heading">
                <div>
                    <h3><i class="fa fa-address-book"></i> Directorio de usuarios</h3>
                    <p>Administra el estado y los accesos asignados a cada cuenta.</p>
                </div>
                <span><i class="fa fa-shield"></i> Accesos</span>
            </div>
            <div id="div_content_usuarios">
                <div id="div_listado_usuarios" aria-live="polite">
                    <div class="usuarios-loading"><i class="fa fa-spinner fa-spin"></i> Cargando usuarios...</div>
                </div>
            </div>
        </section>
    </section>

    <style>
        .usuarios-page {
            --usuarios-green: #00B388;
            --usuarios-ink: #003729;
            --usuarios-muted: #5A7177;
            --usuarios-line: #dce9e4;
            padding-top: 10px;
            color: var(--usuarios-ink);
        }

        .usuarios-heading {
            display: flex;
            align-items: center;
            gap: 11px;
            margin-bottom: 11px;
            padding: 12px 14px;
            border: 1px solid var(--usuarios-line);
            border-radius: 9px;
            background: linear-gradient(110deg, #f4faf7, #fff 72%);
        }

        .usuarios-heading-icon {
            display: grid;
            width: 41px;
            height: 41px;
            flex: 0 0 41px;
            place-items: center;
            border-radius: 11px;
            background: #e4f5ee;
            color: var(--usuarios-green);
            font-size: 17px;
        }

        .usuarios-heading-copy {
            min-width: 0;
            flex: 1 1 auto;
        }

        .usuarios-heading-copy h2 {
            margin: 0;
            color: var(--usuarios-ink);
            font-size: 17px;
            font-weight: 700;
        }

        .usuarios-heading-copy p,
        .usuarios-list-heading p {
            margin: 3px 0 0;
            color: var(--usuarios-muted);
            font-size: 11px;
        }

        .usuarios-heading-actions {
            display: flex;
            flex: 0 0 auto;
            gap: 7px;
        }

        .usuarios-heading-actions .btn,
        .usuarios-search-button {
            min-height: 35px;
            padding: 7px 11px;
            border-radius: 5px;
            font-size: 11px;
            font-weight: 700;
        }

        .usuarios-heading-actions .btn i {
            margin-right: 4px;
        }

        .btn-usuarios-primary {
            border: 1px solid var(--usuarios-green);
            background: var(--usuarios-green);
            color: #fff;
        }

        .btn-usuarios-primary:hover,
        .btn-usuarios-primary:focus {
            border-color: #008f6c;
            background: #008f6c;
            color: #fff;
        }

        .btn-usuarios-outline {
            border: 1px solid var(--usuarios-line);
            background: #fff;
            color: var(--usuarios-ink);
        }

        .btn-usuarios-outline:hover,
        .btn-usuarios-outline:focus {
            border-color: #a8d9c8;
            background: #f4faf7;
            color: var(--usuarios-ink);
        }

        .usuarios-search-card,
        .usuarios-list-card {
            overflow: hidden;
            margin-bottom: 11px;
            border: 1px solid var(--usuarios-line);
            border-radius: 9px;
            background: #fff;
            box-shadow: 0 3px 12px rgba(0, 55, 41, .04);
        }

        .usuarios-search-card {
            padding: 11px 13px;
        }

        .usuarios-search-title {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
        }

        .usuarios-search-title > i {
            color: var(--usuarios-green);
        }

        .usuarios-search-title strong,
        .usuarios-search-title small {
            display: block;
        }

        .usuarios-search-title strong {
            font-size: 12px;
        }

        .usuarios-search-title small {
            margin-top: 2px;
            color: var(--usuarios-muted);
            font-size: 10px;
        }

        .usuarios-search-controls {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .usuarios-search-input {
            position: relative;
            min-width: 0;
            flex: 1 1 auto;
        }

        .usuarios-search-input > i {
            position: absolute;
            top: 10px;
            left: 10px;
            color: #799088;
            font-size: 12px;
        }

        .usuarios-search-input .form-control {
            height: 35px;
            padding-left: 30px;
            border-color: var(--usuarios-line);
            border-radius: 5px;
            box-shadow: none;
            font-size: 12px;
        }

        .usuarios-search-input .form-control:focus {
            border-color: var(--usuarios-green);
            box-shadow: 0 0 0 2px rgba(0, 179, 136, .1);
        }

        .usuarios-list-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            padding: 10px 13px;
            border-bottom: 1px solid var(--usuarios-line);
        }

        .usuarios-list-heading h3 {
            margin: 0;
            color: var(--usuarios-ink);
            font-size: 13px;
            font-weight: 700;
        }

        .usuarios-list-heading h3 i {
            margin-right: 4px;
            color: var(--usuarios-green);
        }

        .usuarios-list-heading > span {
            padding: 4px 7px;
            border-radius: 15px;
            background: #eaf6f0;
            color: #147452;
            font-size: 10px;
            font-weight: 700;
        }

        .usuarios-loading {
            padding: 30px 12px;
            color: var(--usuarios-muted);
            text-align: center;
            font-size: 11px;
        }

        .usuarios-loading i {
            margin-right: 4px;
            color: var(--usuarios-green);
        }

        @media (max-width: 600px) {
            .usuarios-heading {
                align-items: flex-start;
                flex-wrap: wrap;
            }

            .usuarios-heading-actions {
                width: 100%;
            }

            .usuarios-heading-actions .btn {
                flex: 1 1 auto;
            }

            .usuarios-search-controls {
                align-items: stretch;
                flex-direction: column;
            }

            .usuarios-search-button {
                width: 100%;
            }
        }

        @media (max-width: 380px) {
            .usuarios-heading-actions .btn {
                padding-right: 7px;
                padding-left: 7px;
                font-size: 10px;
            }
        }
    </style>
@endsection

@section('script_final')
    @include('adminlte.gestion.usuarios.script')
@endsection
