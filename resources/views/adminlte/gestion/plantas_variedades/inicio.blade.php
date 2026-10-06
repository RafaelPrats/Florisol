@extends('layouts.adminlte.master')

@section('titulo')
    Plantas y variedades
@endsection

@section('css_inicio')
    <style>
        .pv-pagina {
            color: #003729;
        }

        .pv-intro,
        .pv-panel {
            background: #fff;
            border: 1px solid #e1e8e6;
            border-radius: 15px;
            box-shadow: 0 2px 7px #0000000a;
        }

        .pv-intro {
            align-items: center;
            border-left: 5px solid #00B388;
            display: flex;
            justify-content: space-between;
            margin-bottom: 14px;
            padding: 16px 19px;
        }

        .pv-intro h2 {
            color: #5A7177;
            font-size: 19px;
            font-weight: 700;
            margin: 0 0 4px;
        }

        .pv-intro p {
            color: #5A7177;
            margin: 0;
        }

        .pv-intro-icon {
            align-items: center;
            background: #e8f8f2;
            border-radius: 12px;
            color: #00a67d;
            display: flex;
            font-size: 22px;
            height: 48px;
            justify-content: center;
            width: 48px;
        }

        .pv-panel {
            border-top: 4px solid #00B388;
            height: 100%;
            overflow: hidden;
        }

        .pv-panel-proveedores {
            border-top-color: #3c8dbc;
        }

        .pv-panel-variedades {
            border-top-color: #ef6e11;
        }

        .pv-panel-header {
            align-items: center;
            border-bottom: 1px solid #edf1f0;
            display: flex;
            justify-content: space-between;
            min-height: 65px;
            padding: 12px 15px;
        }

        .pv-panel-titulo {
            color: #5A7177;
            font-size: 15px;
            font-weight: 700;
            margin: 0 0 3px;
        }

        .pv-panel-ayuda {
            color: #87969a;
            font-size: 12px;
            margin: 0;
        }

        .pv-panel-icono {
            align-items: center;
            background: #eff8f5;
            border-radius: 9px;
            color: #00a67d;
            display: inline-flex;
            height: 34px;
            justify-content: center;
            margin-right: 8px;
            width: 34px;
        }

        .pv-panel-proveedores .pv-panel-icono {
            background: #eef6fb;
            color: #3c8dbc;
        }

        .pv-panel-variedades .pv-panel-icono {
            background: #fff4e9;
            color: #ef6e11;
        }

        .pv-panel-body {
            max-height: 650px;
            overflow: auto;
            padding: 0 12px 12px;
        }

        .pv-panel-variedades .pv-panel-body {
            padding-top: 0;
        }

        .pv-tabla,
        #div_content_variedades table {
            border: 0 !important;
            font-size: 13px !important;
            margin: 0;
            width: 100%;
        }

        .pv-tabla th,
        #div_content_variedades table th {
            background: #f5f8f7;
            border-color: #e1e8e6 !important;
            color: #5A7177;
            font-size: 11px;
            letter-spacing: .35px;
            padding: 9px 7px !important;
            text-transform: uppercase;
        }

        .pv-sort-button {
            padding: 0;
            border: 0;
            background: transparent;
            color: inherit;
            font: inherit;
            letter-spacing: inherit;
            text-align: inherit;
            text-transform: inherit;
        }

        .pv-sort-button:hover,
        .pv-sort-button:focus {
            color: #00a67d;
            outline: none;
            text-decoration: underline;
        }

        .pv-sort-button i {
            margin-left: 3px;
            opacity: .65;
        }

        .pv-tabla thead th,
        #div_content_variedades table thead th {
            position: sticky;
            top: 0;
            z-index: 2;
        }

        .pv-tabla td,
        #div_content_variedades table td {
            border-color: #edf1f0 !important;
            padding: 9px 7px !important;
            vertical-align: middle !important;
        }

        .pv-tabla tbody tr:not(.error):hover,
        #div_content_variedades table tbody tr:not(.error):hover {
            background: #f0faf6 !important;
        }

        .pv-tabla .pv-fila-seleccionada,
        #table_content_plantas .pv-fila-seleccionada,
        #table_content_proveedores .pv-fila-seleccionada {
            background: #e5f7f0 !important;
            color: #003729;
            font-weight: 600;
        }

        .pv-tabla .pv-nombre,
        #table_content_plantas .pv-nombre,
        #table_content_proveedores .pv-nombre {
            color: #003729;
            font-weight: 600;
            overflow-wrap: anywhere;
        }

        .pv-tabla .pv-estado-inactivo {
            background: #fff7f5 !important;
            color: #b94a48;
        }

        .pv-vacio {
            color: #87969a;
            padding: 24px 10px !important;
        }

        .pv-modal-form {
            color: #003729;
            padding: 4px 3px 2px;
        }

        .pv-form-intro {
            align-items: center;
            background: linear-gradient(115deg, #eaf8f2, #f7fbf9);
            border: 1px solid #deeee6;
            border-left: 4px solid #00B388;
            border-radius: 11px;
            color: #5A7177;
            display: flex;
            gap: 13px;
            margin-bottom: 20px;
            padding: 14px 16px;
        }

        .pv-form-intro i {
            align-items: center;
            background: #00a67d;
            border-radius: 10px;
            color: #fff;
            display: inline-flex;
            flex: 0 0 38px;
            font-size: 17px;
            height: 38px;
            justify-content: center;
        }

        .pv-form-intro strong {
            color: #003729;
            display: block;
            font-size: 14px;
            margin-bottom: 3px;
        }

        .pv-modal-form .pv-form-seccion {
            align-items: center;
            background: #f6faf8;
            border: 1px solid #e7eeeb;
            border-left: 3px solid #00B388;
            border-radius: 9px;
            color: #425a55;
            display: flex;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .035em;
            margin: 18px 0 13px;
            padding: 10px 12px;
            text-transform: uppercase;
        }

        .pv-modal-form .pv-form-seccion:first-of-type {
            margin-top: 0;
        }

        .pv-modal-form .pv-form-seccion i {
            color: #00a67d;
            font-size: 14px;
            margin-right: 8px;
        }

        .pv-modal-form .row {
            margin-right: -9px;
            margin-left: -9px;
        }

        .pv-modal-form .row > [class*="col-"] {
            padding-right: 9px;
            padding-left: 9px;
        }

        .pv-pagina .form-group label,
        .pv-modal-form .form-group label {
            color: #5A7177;
            display: block;
            font-size: 11px;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .pv-pagina .form-control,
        .pv-modal-form .form-control {
            background-color: #fff;
            border-color: #d5e1dc;
            border-radius: 8px;
            box-shadow: none;
            height: 40px;
            padding: 8px 11px;
            transition: border-color .15s ease, box-shadow .15s ease;
        }

        .pv-pagina .form-control:focus,
        .pv-modal-form .form-control:focus {
            border-color: #00B388;
            box-shadow: 0 0 0 3px rgba(0, 179, 136, .11);
        }

        .pv-modal-form .form-group {
            margin-bottom: 14px;
        }

        .pv-modal-form .form-control::placeholder {
            color: #a2aeaa;
        }

        .pv-modal-form .pv-field-hint {
            color: #80908c;
            display: block;
            font-size: 11px;
            line-height: 1.4;
            margin-top: 5px;
        }

        .pv-modal-form .help-block {
            font-size: 11px;
            margin: 5px 0 0;
        }

        .pv-modal-form select.form-control {
            padding-right: 8px;
        }

        .pv-pagina .btn-xs,
        .pv-modal-form .btn {
            border-radius: 6px;
        }

        @media (max-width: 767px) {
            .pv-intro {
                padding: 14px;
            }

            .pv-panel {
                margin-bottom: 12px;
            }

            .pv-panel-body {
                max-height: 460px;
            }
        }
    </style>
@endsection

@section('contenido')
    <section class="content-header">
        <h1>
            Plantas y variedades
            <small class="text-color_yura">módulo de administrador</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="javascript:void(0)" onclick="cargar_url('')" class="text-color_yura"><i class="fa fa-home"></i>
                    Inicio</a></li>
            <li class="text-color_yura">{{ $submenu->menu->grupo_menu->nombre }}</li>
            <li class="text-color_yura">{{ $submenu->menu->nombre }}</li>
            <li class="active">
                <a href="javascript:void(0)" onclick="cargar_url('{{ $submenu->url }}')" class="text-color_yura">
                    <i class="fa fa-fw fa-refresh"></i> {{ $submenu->nombre }}
                </a>
            </li>
        </ol>
    </section>

    <section class="content pv-pagina">
        <div class="pv-intro">
            <div>
                <h2>Catálogo de producción</h2>
                <p>Organiza plantas, variedades y proveedores desde un solo lugar.</p>
            </div>
            <span class="pv-intro-icon hidden-xs"><i class="fa fa-leaf"></i></span>
        </div>

        <div id="div_content_menu_sistema">
            <input type="hidden" id="planta_seleccionada">
            <input type="hidden" id="proveedor_seleccionada">
            <div class="row">
                <div class="col-md-3 col-sm-4" id="div_content_proveedores">
                    <div class="pv-panel pv-panel-proveedores">
                        <div class="pv-panel-header">
                            <div class="media">
                                <div class="media-left">
                                    <span class="pv-panel-icono"><i class="fa fa-truck"></i></span>
                                </div>
                                <div class="media-body">
                                    <h3 class="pv-panel-titulo">Proveedores</h3>
                                    <p class="pv-panel-ayuda">Proveedores de la finca</p>
                                </div>
                            </div>
                        </div>
                        <div class="pv-panel-body">
                            @include('adminlte.gestion.plantas_variedades.partials.listado_proveedores')
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-4" id="div_content_grupos_menu">
                    <div class="pv-panel">
                        <div class="pv-panel-header">
                            <div class="media">
                                <div class="media-left">
                                    <span class="pv-panel-icono"><i class="fa fa-leaf"></i></span>
                                </div>
                                <div class="media-body">
                                    <h3 class="pv-panel-titulo">Plantas</h3>
                                    <p class="pv-panel-ayuda">Especies registradas</p>
                                </div>
                            </div>
                        </div>
                        <div class="pv-panel-body">
                            @include('adminlte.gestion.plantas_variedades.partials.listado_plantas')
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-sm-4" id="div_content_menus">
                    <div class="pv-panel pv-panel-variedades">
                        <div class="pv-panel-header">
                            <div class="media">
                                <div class="media-left">
                                    <span class="pv-panel-icono"><i class="fa fa-pagelines"></i></span>
                                </div>
                                <div class="media-body">
                                    <h3 class="pv-panel-titulo">Variedades</h3>
                                    <p class="pv-panel-ayuda">Catálogo por planta</p>
                                </div>
                            </div>
                        </div>
                        <div class="pv-panel-body">
                            <div id="div_content_variedades">
                                <table class="table-responsive table-bordered pv-tabla" id="table_content_variedades">
                                    <thead>
                                        <tr>
                                            <th class="text-center">
                                                <button type="button" class="pv-sort-button"
                                                    onclick="ordenar_listado_pv(this, 0)">
                                                    Variedad <i class="fa fa-fw fa-sort" aria-hidden="true"></i>
                                                </button>
                                            </th>
                                            <th class="text-center">
                                                @if (count($plantas) > 0)
                                                    <button type="button" class="btn btn-xs btn-yura_primary"
                                                        title="Añadir variedad" onclick="add_variedad()">
                                                        <i class="fa fa-fw fa-plus"></i>
                                                    </button>
                                                @endif
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="text-center pv-vacio" colspan="2">
                                                <i class="fa fa-hand-pointer-o"></i>
                                                <br>El catálogo de variedades aparecerá aquí
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('script_final')
    @include('adminlte.gestion.plantas_variedades.script')
@endsection
