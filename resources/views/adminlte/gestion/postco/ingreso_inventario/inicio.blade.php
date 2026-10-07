@extends('layouts.adminlte.master')

@section('titulo')
    Ingreso Inventario
@endsection

@section('contenido')
    <section class="content-header">
        <h1>
            Ingreso Inventario
            <small class="text-color_yura">módulo de postcosecha</small>
        </h1>

        <ol class="breadcrumb">
            <li><a href="javascript:void(0)" class="text-color_yura" onclick="cargar_url('')"><i class="fa fa-home"></i>
                    Inicio</a></li>
            <li class="text-color_yura">{{ $submenu->menu->grupo_menu->nombre }}</li>
            <li class="text-color_yura">{{ $submenu->menu->nombre }}</li>
            <li class="active">
                <a href="javascript:void(0)" class="text-color_yura" onclick="cargar_url('{{ $submenu->url }}')">
                    <i class="fa fa-fw fa-refresh"></i> {{ $submenu->nombre }}
                </a>
            </li>
        </ol>
    </section>

    <section class="content ingreso-inventario">
        <div class="ingreso-filter-card">
            <div class="ingreso-filter-heading">
                <span class="ingreso-heading-icon"><i class="fa fa-cubes"></i></span>
                <div>
                    <h2>Consulta de inventario recibido</h2>
                    <p>Filtra existencias y recepciones pendientes por planta, documento, bodega y fecha.</p>
                </div>
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
            </div>

            <div class="ingreso-filter-footer">
                <span class="ingreso-filter-hint"><i class="fa fa-info-circle"></i> La consulta inicia mostrando el
                    inventario disponible y pendiente.</span>
                <div class="ingreso-filter-actions">
                    <button type="button" class="btn btn-yura_default" onclick="admin_bodegas()">
                        <i class="fa fa-cogs"></i> Bodegas por segmento
                    </button>
                    <button type="button" class="btn btn-yura_primary" onclick="listar_reporte()">
                        <i class="fa fa-search"></i> Consultar inventario
                    </button>
                </div>
            </div>
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

        .ingreso-filter-card,
        .ingreso-summary-card,
        .ingreso-table-card {
            overflow: hidden;
            border: 1px solid var(--ingreso-line);
            border-radius: 11px;
            background: #fff;
            box-shadow: 0 4px 14px rgba(0, 55, 41, .06);
        }

        .ingreso-filter-card {
            margin-bottom: 14px;
            padding: 15px;
        }

        .ingreso-filter-heading {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 13px;
        }

        .ingreso-heading-icon {
            display: grid;
            width: 39px;
            height: 39px;
            flex: 0 0 39px;
            place-items: center;
            border-radius: 10px;
            background: rgba(0, 179, 136, .12);
            color: var(--ingreso-ink);
            font-size: 16px;
        }

        .ingreso-filter-heading h2 {
            margin: 0 0 3px;
            color: var(--ingreso-ink);
            font-size: 17px;
            font-weight: 700;
        }

        .ingreso-filter-heading p {
            margin: 0;
            color: var(--ingreso-muted);
            font-size: 12px;
        }

        .ingreso-filter-grid {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
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
        }

        .ingreso-filter-field .input-group-addon {
            min-width: 36px;
            padding: 6px 8px;
            border-color: var(--ingreso-line);
            background: var(--ingreso-soft);
            color: var(--ingreso-muted);
        }

        .ingreso-filter-field .form-control {
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

        .ingreso-filter-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-top: 12px;
            padding-top: 11px;
            border-top: 1px solid var(--ingreso-line);
        }

        .ingreso-filter-hint {
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
        }

        .ingreso-filter-actions .btn {
            min-height: 34px;
            padding: 7px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 700;
        }

        @media (max-width: 1100px) {
            .ingreso-filter-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        @media (max-width: 700px) {
            .ingreso-filter-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .ingreso-filter-footer {
                align-items: flex-start;
                flex-direction: column;
            }

            .ingreso-filter-actions {
                justify-content: flex-start;
            }
        }

        @media (max-width: 480px) {
            .ingreso-filter-card {
                padding: 12px;
            }

            .ingreso-filter-grid {
                grid-template-columns: 1fr;
            }

            .ingreso-filter-actions {
                display: grid;
                width: 100%;
                grid-template-columns: 1fr;
            }

            .ingreso-filter-actions .btn {
                text-align: left;
            }
        }
    </style>
@endsection

@section('script_final')
    @include('adminlte.gestion.postco.ingreso_inventario.script')
@endsection
