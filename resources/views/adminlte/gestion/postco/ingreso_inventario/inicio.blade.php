@extends('layouts.adminlte.master')

@section('titulo')
    Ingreso Inventario
@endsection

@section('contenido')
    <section class="content ingreso-inventario">
        <div class="ingreso-toolbar">
            <div class="ingreso-toolbar__top">
                <div class="ingreso-toolbar__identity">
                    <span class="ingreso-toolbar__icon"><i class="fa fa-cubes" aria-hidden="true"></i></span>
                    <div>
                        <span class="ingreso-eyebrow">MÓDULO DE POSTCOSECHA</span>
                        <h2>Ingreso de inventario</h2>
                    </div>
                </div>
                <ol class="ingreso-breadcrumb" aria-label="Ruta de navegación">
                    <li>
                        <a href="javascript:void(0)" onclick="cargar_url('')">
                            <i class="fa fa-home" aria-hidden="true"></i> Inicio
                        </a>
                    </li>
                    <li>{{ $submenu->menu->grupo_menu->nombre }}</li>
                    <li>{{ $submenu->menu->nombre }}</li>
                    <li class="ingreso-breadcrumb__current">
                        <a href="javascript:void(0)" onclick="cargar_url('{{ $submenu->url }}')">
                            <i class="fa fa-refresh" aria-hidden="true"></i> {{ $submenu->nombre }}
                        </a>
                    </li>
                </ol>
            </div>

            <div class="ingreso-filter-grid">
                <div class="ingreso-filter-field">
                    <label for="planta_filtro">Planta</label>
                    <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-leaf"></i></span>
                        <select name="planta_filtro" id="planta_filtro" class="form-control" style="width: 100%"
                            onchange="select_planta_global($(this).val(), 'variedad_filtro', 'div_filtro_variedad', '<option value=>Todas las variedades</option>')">
                            <option value="">Todas las plantas</option>
                            @foreach ($plantas as $p)
                                <option value="{{ $p->id_planta }}">{{ $p->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="ingreso-filter-field" id="div_filtro_variedad">
                    <label for="variedad_filtro">Variedad</label>
                    <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-pagelines"></i></span>
                        <select name="variedad_filtro" id="variedad_filtro" class="form-control" style="width: 100%">
                            <option value="">Todas las variedades</option>
                        </select>
                    </div>
                </div>
                <div class="ingreso-filter-field">
                    <label for="documento_filtro">Documento pendiente</label>
                    <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-file-text-o"></i></span>
                        <select name="documento_filtro" id="documento_filtro" class="form-control" style="width: 100%">
                            <option value="">Todos los documentos</option>
                            @foreach ($documentos as $d)
                                <option value="{{ $d->id_api_store_cajas }}">{{ $d->documento }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="ingreso-filter-field">
                    <label for="bodega_filtro">Bodega</label>
                    <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-archive"></i></span>
                        <select name="bodega_filtro" id="bodega_filtro" class="form-control" style="width: 100%">
                            <option value="V">Ventas</option>
                            <option value="P">Producción</option>
                        </select>
                    </div>
                </div>
                <div class="ingreso-filter-field">
                    <label for="fecha_filtro">Fecha</label>
                    <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                        <input type="date" name="fecha_filtro" id="fecha_filtro" class="form-control"
                            value="{{ hoy() }}" max="{{ hoy() }}">
                    </div>
                </div>
                <div class="ingreso-filter-actions">
                    <div class="btn-group" role="group" aria-label="Acciones del filtro">
                        <button type="button" class="btn btn-yura_default" title="Bodegas por segmento"
                            aria-label="Bodegas por segmento" onclick="admin_bodegas()">
                            <i class="fa fa-cogs" aria-hidden="true"></i>
                        </button>
                        <button type="button" class="btn btn-yura_primary" title="Consultar inventario"
                            aria-label="Consultar inventario" onclick="listar_reporte()">
                            <i class="fa fa-search" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
            </div>
            <p class="ingreso-filter-hint">
                <i class="fa fa-info-circle" aria-hidden="true"></i>
                Consulta existencias y recepciones pendientes por planta, documento, bodega y fecha.
            </p>
        </div>

        <div id="div_listado" class="ingreso-results"></div>
    </section>

    <style>
        .ingreso-inventario {
            --ingreso-green: #00B388;
            --ingreso-ink: #003729;
            --ingreso-muted: #5A7177;
            --ingreso-line: #dce9e4;
            --ingreso-soft: #f4faf7;
        }

        .ingreso-summary-card,
        .ingreso-table-card {
            overflow: hidden;
            border: 1px solid var(--ingreso-line);
            border-radius: 11px;
            background: #fff;
            box-shadow: 0 4px 14px rgba(0, 55, 41, .06);
        }

        .ingreso-toolbar {
            display: flex;
            flex-direction: column;
            gap: 11px;
            margin: 0 0 12px;
            padding: 10px 14px 12px;
            border: 1px solid #dcebe4;
            border-radius: 12px;
            background: linear-gradient(110deg, #fff, #f0faf5);
            box-shadow: 0 4px 14px rgba(28, 76, 58, .05);
        }

        .ingreso-toolbar__top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .ingreso-toolbar__identity {
            display: flex;
            align-items: center;
            gap: 9px;
        }

        .ingreso-toolbar__icon {
            display: grid;
            width: 32px;
            height: 32px;
            flex: 0 0 32px;
            place-items: center;
            border-radius: 9px;
            background: var(--ingreso-green);
            color: #fff;
            font-size: 14px;
        }

        .ingreso-eyebrow {
            color: #087d60;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .8px;
        }

        .ingreso-toolbar h2 {
            margin: 1px 0 0;
            color: #253f37;
            font-size: 17px;
            font-weight: 700;
        }

        .ingreso-breadcrumb {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 0;
            margin: 0;
            padding: 0;
            list-style: none;
            color: #7b8c86;
            font-size: 12px;
            line-height: 1.5;
        }

        .ingreso-breadcrumb li {
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
        }

        .ingreso-breadcrumb li + li::before {
            padding: 0 7px;
            color: #b4c3bc;
            content: "›";
        }

        .ingreso-breadcrumb a {
            color: #087d60;
            text-decoration: none;
        }

        .ingreso-breadcrumb a:hover {
            color: var(--ingreso-green);
        }

        .ingreso-breadcrumb__current a {
            font-weight: 700;
        }

        .ingreso-breadcrumb i {
            margin-right: 4px;
        }

        .ingreso-filter-grid {
            display: grid;
            width: 100%;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            align-items: end;
            gap: 10px;
        }

        .ingreso-filter-field {
            min-width: 0;
        }

        .ingreso-filter-field label {
            display: block;
            margin-bottom: 5px;
            color: var(--ingreso-ink);
            font-size: 12px;
            font-weight: 700;
        }

        .ingreso-filter-field .input-group,
        .ingreso-filter-field .select2-container {
            width: 100% !important;
            min-width: 0;
            max-width: 100%;
        }

        .ingreso-filter-field .input-group-addon {
            min-width: 36px;
            padding: 6px 8px;
            border-color: var(--ingreso-line);
            background: var(--ingreso-soft);
            color: var(--ingreso-muted);
        }

        .ingreso-filter-field .form-control {
            min-width: 0;
            height: 35px;
            border-color: var(--ingreso-line);
            box-shadow: none;
            font-size: 13px;
        }

        .ingreso-filter-field .form-control:focus {
            border-color: var(--ingreso-green);
            box-shadow: 0 0 0 2px rgba(0, 179, 136, .1);
        }

        .ingreso-filter-field .select2-selection {
            height: 35px !important;
            border-color: var(--ingreso-line) !important;
        }

        .ingreso-filter-field .select2-selection__rendered {
            line-height: 33px !important;
        }

        .ingreso-filter-field .select2-selection__arrow {
            height: 33px !important;
        }

        .ingreso-filter-hint {
            margin: 0;
            color: var(--ingreso-muted);
            font-size: 11px;
        }

        .ingreso-filter-hint i {
            margin-right: 4px;
            color: var(--ingreso-green);
        }

        .ingreso-filter-actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 7px;
            white-space: nowrap;
        }

        .ingreso-filter-actions .btn {
            min-height: 36px;
            padding: 8px 11px;
            border-radius: 0;
            font-size: 13px;
        }

        .ingreso-filter-actions .btn-group > .btn:first-child {
            border-radius: 18px 0 0 18px;
        }

        .ingreso-filter-actions .btn-group > .btn:last-child {
            border-radius: 0 18px 18px 0;
        }

        .ingreso-filter-actions .btn i {
            margin: 0;
        }

        @media (max-width: 700px) {
            .ingreso-toolbar__top {
                align-items: flex-start;
                flex-direction: column;
                gap: 5px;
            }

            .ingreso-breadcrumb {
                justify-content: flex-start;
                padding-left: 41px;
            }

            .ingreso-filter-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 9px;
            }

            .ingreso-filter-actions {
                grid-column: 1 / -1;
                justify-content: flex-end;
            }
        }

        @media (max-width: 480px) {
            .ingreso-toolbar {
                padding: 10px;
            }

            .ingreso-filter-grid {
                grid-template-columns: 1fr;
            }

            .ingreso-filter-actions {
                justify-content: flex-start;
            }
        }
    </style>
@endsection

@section('script_final')
    @include('adminlte.gestion.postco.ingreso_inventario.script')
@endsection
