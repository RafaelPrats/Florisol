@extends('layouts.adminlte.master')

@section('titulo')
    Reporte de Ingresos
@endsection

@section('contenido')
    <section class="content-header">
        <h1>
            Reporte de Ingresos
            <small class="text-color_yura">módulo de postcosecha</small>
        </h1>

        <ol class="breadcrumb">
            <li><a href="javascript:void(0)" class="text-color_yura" onclick="cargar_url('')"><i class="fa fa-home"></i>
                    Inicio</a></li>
            <li class="text-color_yura">
                {{ $submenu->menu->grupo_menu->nombre }}
            </li>
            <li class="text-color_yura">
                {{ $submenu->menu->nombre }}
            </li>
            <li class="active">
                <a href="javascript:void(0)" class="text-color_yura" onclick="cargar_url('{{ $submenu->url }}')">
                    <i class="fa fa-fw fa-refresh"></i> {{ $submenu->nombre }}
                </a>
            </li>
        </ol>
    </section>

    <section class="content postco-report">
        <div class="report-filter-card">
            <div class="report-filter-heading">
                <div class="report-filter-icon"><i class="fa fa-sliders"></i></div>
                <div>
                    <h2>Filtros del reporte</h2>
                    <p>Selecciona los criterios y consulta o exporta los ingresos.</p>
                </div>
            </div>
            <div class="report-filter-grid">
                <div class="report-filter-row">
                    <div class="report-filter-field">
                        <label for="planta_filtro">Planta</label>
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-leaf"></i></span>
                            <select name="planta_filtro" id="planta_filtro" class="form-control" style="width: 100%"
                                onchange="select_planta_global($(this).val(), 'variedad_filtro', 'div_filtro_variedad', '<option value=>Todas las Variedades</option>')">
                                <option value="">Todas las plantas</option>
                                @foreach ($plantas as $p)
                                    <option value="{{ $p->id_planta }}">{{ $p->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="report-filter-field" id="div_filtro_variedad">
                        <label for="variedad_filtro">Variedad</label>
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-pagelines"></i></span>
                            <select name="variedad_filtro" id="variedad_filtro" class="form-control" style="width: 100%">
                                <option value="">Todas las variedades</option>
                            </select>
                        </div>
                    </div>
                    <div class="report-filter-field">
                        <label for="documento_filtro">Documento</label>
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-file-text-o"></i></span>
                            <input type="text" name="documento_filtro" id="documento_filtro" class="form-control"
                                placeholder="Factura o documento">
                        </div>
                    </div>
                    <div class="report-filter-field">
                        <label for="bodega_filtro">Bodega</label>
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-gift"></i></span>
                            <select name="bodega_filtro" id="bodega_filtro" class="form-control" style="width: 100%">
                                <option value="T">Todas las bodegas</option>
                                <option value="V">Ventas</option>
                                <option value="P">Producción</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="report-filter-row report-filter-row-bottom">
                    <div class="report-filter-field">
                        <label for="desde_filtro">Desde</label>
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                            <input type="date" name="desde_filtro" id="desde_filtro" class="form-control"
                                value="{{ opDiasFecha('-', 7, hoy()) }}" max="{{ hoy() }}">
                        </div>
                    </div>
                    <div class="report-filter-field">
                        <label for="hasta_filtro">Hasta</label>
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                            <input type="date" name="hasta_filtro" id="hasta_filtro" class="form-control"
                                value="{{ hoy() }}" max="{{ hoy() }}">
                        </div>
                    </div>
                    <div class="report-filter-actions">
                        <button type="button" class="btn btn-yura_primary" onclick="listar_reporte()">
                            <i class="fa fa-search"></i> Consultar
                        </button>
                        <button type="button" class="btn btn-yura_default" onclick="exportar_reporte()">
                            <i class="fa fa-file-excel-o"></i> Exportar Excel
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="report-results" id="div_listado"></div>
    </section>

    <style>
        .postco-report {
            --report-green: #00B388;
            --report-ink: #003729;
            --report-muted: #5A7177;
            --report-line: #dce9e4;
            --report-soft: #f4faf7;
        }

        .report-filter-card,
        .report-results .report-tabs-card {
            overflow: hidden;
            border: 1px solid var(--report-line);
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 5px 18px rgba(0, 55, 41, .07);
        }

        .report-filter-card {
            margin-bottom: 18px;
            padding: 18px;
        }

        .report-filter-heading {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }

        .report-filter-icon {
            display: grid;
            width: 42px;
            height: 42px;
            flex: 0 0 42px;
            place-items: center;
            border-radius: 12px;
            background: rgba(0, 179, 136, .12);
            color: var(--report-ink);
            font-size: 18px;
        }

        .report-filter-heading h2 {
            margin: 0 0 3px;
            color: var(--report-ink);
            font-size: 18px;
            font-weight: 700;
        }

        .report-filter-heading p {
            margin: 0;
            color: var(--report-muted);
            font-size: 13px;
        }

        .report-filter-grid {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .report-filter-row {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
            align-items: end;
        }

        .report-filter-row-bottom {
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) minmax(300px, 2fr);
        }

        .report-filter-field label {
            display: block;
            margin-bottom: 6px;
            color: var(--report-ink);
            font-size: 13px;
            font-weight: 700;
        }

        .report-filter-field .input-group {
            width: 100%;
        }

        .report-filter-field .input-group-addon {
            min-width: 40px;
            border-color: var(--report-line);
            background: var(--report-soft);
            color: var(--report-muted);
        }

        .report-filter-field .form-control {
            height: 38px;
            border-color: var(--report-line);
            box-shadow: none;
        }

        .report-filter-field .form-control:focus {
            border-color: var(--report-green);
            box-shadow: 0 0 0 2px rgba(0, 179, 136, .12);
        }

        .report-filter-field .select2-container {
            width: 100% !important;
        }

        .report-filter-field .select2-selection {
            height: 38px !important;
            border-color: var(--report-line) !important;
        }

        .report-filter-field .select2-selection__rendered {
            line-height: 36px !important;
        }

        .report-filter-field .select2-selection__arrow {
            height: 36px !important;
        }

        .report-filter-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-self: end;
        }

        .report-filter-actions .btn {
            min-height: 38px;
            flex: 1 1 auto;
            border-radius: 7px;
            font-weight: 700;
        }

        .report-results .report-tabs-card {
            border-top: 3px solid var(--report-green);
        }

        .report-results .report-tabs {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 8px;
            margin: 0;
            padding: 12px;
            border: 0;
            background: var(--report-soft);
        }

        .report-results .report-tabs::before,
        .report-results .report-tabs::after {
            display: none;
            content: none;
        }

        .report-results .report-tabs > li {
            float: none;
            min-width: 0;
            margin: 0;
            border-top: 0;
        }

        .report-results .report-tabs > li > a {
            display: flex;
            min-height: 46px;
            align-items: center;
            justify-content: center;
            gap: 7px;
            margin: 0;
            padding: 8px 10px;
            border: 1px solid var(--report-line);
            border-radius: 8px;
            background: #fff;
            color: var(--report-muted);
            font-size: 14px;
            font-weight: 700;
            line-height: 1.25;
            text-align: center;
        }

        .report-results .report-tabs > li.active > a,
        .report-results .report-tabs > li.active > a:hover,
        .report-results .report-tabs > li.active > a:focus {
            border-color: var(--report-green);
            border-bottom-color: var(--report-green);
            background: var(--report-ink) !important;
            color: #fff !important;
            box-shadow: 0 3px 10px rgba(0, 55, 41, .15);
        }

        .report-results .report-tabs > li:not(.active) > a:hover,
        .report-results .report-tabs > li:not(.active) > a:focus {
            border-color: rgba(0, 179, 136, .45);
            background: #fff !important;
            color: var(--report-ink) !important;
        }

        .report-results .report-tab-count {
            display: inline-block;
            min-width: 24px;
            padding: 3px 7px;
            border: 1px solid rgba(0, 179, 136, .24);
            border-radius: 12px;
            background: rgba(0, 179, 136, .13);
            color: var(--report-ink);
            text-align: center;
            font-size: 11px;
            line-height: 1.2;
        }

        .report-results .report-tabs > li.active .report-tab-count {
            border-color: rgba(255, 255, 255, .28);
            background: rgba(255, 255, 255, .15);
            color: #fff;
        }

        .report-results .tab-content {
            padding: 12px;
        }

        .report-results .report-empty {
            display: flex;
            min-height: 150px;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 24px;
            border: 1px dashed var(--report-line);
            border-radius: 8px;
            background: var(--report-soft);
            color: var(--report-muted);
            text-align: center;
            font-size: 15px;
        }

        .report-results .report-empty i {
            color: var(--report-green);
            font-size: 22px;
        }

        .report-results .report-table-scroll {
            max-height: min(70vh, 700px);
            overflow: auto !important;
            border: 1px solid var(--report-line);
            border-radius: 8px;
        }

        .report-results table.table-bordered {
            width: 100%;
            margin: 0 !important;
            border: 0 !important;
            border-collapse: separate;
            border-spacing: 0;
            color: var(--report-ink);
            font-size: 14px;
        }

        .report-results table.table-bordered th {
            padding: 8px 9px;
            border-color: var(--report-line) !important;
            vertical-align: middle;
        }

        .report-results table.table-bordered tr:not(.tr_fija_top_0) > th {
            font-weight: 500;
        }

        .report-results table.table-bordered .tr_fija_top_0 th {
            position: sticky;
            top: 0;
            z-index: 10;
            border-color: #315f53 !important;
            background: #003729 !important;
            color: #fff !important;
            font-size: 13px;
            font-weight: 700;
            white-space: nowrap;
        }

        .report-results table.table-bordered tr:not(.tr_fija_top_0):nth-child(even) > th {
            background-color: var(--report-soft);
        }

        .report-results table.table-bordered tr[style*="background-color"] > th {
            background-color: #e4f5ee !important;
        }

        .report-results .tab-pane > .report-table-scroll + .report-table-scroll {
            margin-top: 12px;
        }

        @media (max-width: 991px) {
            .report-filter-row,
            .report-filter-row-bottom {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .report-filter-actions {
                grid-column: 1 / -1;
            }
        }

        @media (max-width: 600px) {
            .report-filter-card {
                padding: 14px;
            }

            .report-filter-row,
            .report-filter-row-bottom {
                grid-template-columns: minmax(0, 1fr);
                gap: 12px;
            }

            .report-filter-actions {
                display: grid;
                grid-template-columns: 1fr 1fr;
                grid-column: auto;
            }

            .report-results .report-tabs {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 6px;
                padding: 9px;
            }

            .report-results .report-tabs > li > a {
                min-height: 44px;
                padding: 7px 5px;
                font-size: 12px;
                flex-wrap: wrap;
            }
        }
    </style>
@endsection

@section('script_final')
    @include('adminlte.gestion.postco.reporte_ingresos.script')
@endsection
