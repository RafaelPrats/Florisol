@extends('layouts.adminlte.master')

@section('titulo')
    Flor de Baja
@endsection

@section('contenido')
    <section class="content-header">
        <h1>
            Flor de Baja
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

    <section class="content baja-report">
        <div class="baja-filter-card">
            <div class="baja-filter-heading">
                <span class="baja-heading-icon"><i class="fa fa-recycle"></i></span>
                <div>
                    <h2>Control de flor de baja</h2>
                    <p>Filtra las existencias o consulta las órdenes registradas para la fecha seleccionada.</p>
                </div>
            </div>

            <div class="baja-filter-grid">
                <div class="baja-filter-field">
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
                <div class="baja-filter-field" id="div_filtro_variedad">
                    <label for="variedad_filtro">Variedad</label>
                    <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-pagelines"></i></span>
                        <select name="variedad_filtro" id="variedad_filtro" class="form-control" style="width: 100%">
                            <option value="">Todas las variedades</option>
                        </select>
                    </div>
                </div>
                <div class="baja-filter-field">
                    <label for="bodega_filtro">Bodega</label>
                    <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-archive"></i></span>
                        <select name="bodega_filtro" id="bodega_filtro" class="form-control" style="width: 100%">
                            <option value="V">Ventas</option>
                            <option value="P">Producción</option>
                        </select>
                    </div>
                </div>
                <div class="baja-filter-field">
                    <label for="fecha_filtro">Fecha de orden</label>
                    <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                        <input type="date" name="fecha_filtro" id="fecha_filtro" class="form-control"
                            value="{{ hoy() }}">
                    </div>
                </div>
            </div>

            <div class="baja-filter-footer">
                <span class="baja-filter-hint"><i class="fa fa-info-circle"></i> Puedes crear una orden de desecho desde
                    el listado de existencias.</span>
                <div class="baja-filter-actions">
                    <button type="button" class="btn btn-yura_default" onclick="admin_motivos()">
                        <i class="fa fa-list-alt"></i> Motivos
                    </button>
                    <button type="button" class="btn btn-yura_dark" onclick="listar_ordenes()">
                        <i class="fa fa-list"></i> Ver órdenes
                    </button>
                    <button type="button" class="btn btn-yura_primary" onclick="listar_reporte()">
                        <i class="fa fa-search"></i> Consultar existencias
                    </button>
                </div>
            </div>
        </div>

        <div id="div_listado" class="baja-results"></div>
    </section>

    <style>
        .baja-report {
            --baja-green: #00B388;
            --baja-ink: #003729;
            --baja-muted: #5A7177;
            --baja-line: #dce9e4;
            --baja-soft: #f4faf7;
        }

        .baja-filter-card,
        .baja-summary-card,
        .baja-table-card {
            overflow: hidden;
            border: 1px solid var(--baja-line);
            border-radius: 11px;
            background: #fff;
            box-shadow: 0 4px 14px rgba(0, 55, 41, .06);
        }

        .baja-filter-card {
            margin-bottom: 15px;
            padding: 16px;
        }

        .baja-filter-heading {
            display: flex;
            align-items: center;
            gap: 11px;
            margin-bottom: 14px;
        }

        .baja-heading-icon {
            display: grid;
            width: 40px;
            height: 40px;
            flex: 0 0 40px;
            place-items: center;
            border-radius: 11px;
            background: rgba(0, 179, 136, .12);
            color: var(--baja-ink);
            font-size: 17px;
        }

        .baja-filter-heading h2 {
            margin: 0 0 3px;
            color: var(--baja-ink);
            font-size: 17px;
            font-weight: 700;
        }

        .baja-filter-heading p {
            margin: 0;
            color: var(--baja-muted);
            font-size: 13px;
        }

        .baja-filter-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
        }

        .baja-filter-field label {
            display: block;
            margin-bottom: 5px;
            color: var(--baja-ink);
            font-size: 13px;
            font-weight: 700;
        }

        .baja-filter-field .input-group,
        .baja-filter-field .select2-container {
            width: 100% !important;
        }

        .baja-filter-field .input-group-addon {
            min-width: 38px;
            padding: 6px 9px;
            border-color: var(--baja-line);
            background: var(--baja-soft);
            color: var(--baja-muted);
        }

        .baja-filter-field .form-control {
            height: 36px;
            border-color: var(--baja-line);
            box-shadow: none;
        }

        .baja-filter-field .form-control:focus {
            border-color: var(--baja-green);
            box-shadow: 0 0 0 2px rgba(0, 179, 136, .1);
        }

        .baja-filter-field .select2-selection {
            height: 36px !important;
            border-color: var(--baja-line) !important;
        }

        .baja-filter-field .select2-selection__rendered {
            line-height: 34px !important;
        }

        .baja-filter-field .select2-selection__arrow {
            height: 34px !important;
        }

        .baja-filter-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-top: 13px;
            padding-top: 12px;
            border-top: 1px solid var(--baja-line);
        }

        .baja-filter-hint {
            color: var(--baja-muted);
            font-size: 12px;
        }

        .baja-filter-hint i {
            margin-right: 4px;
            color: var(--baja-green);
        }

        .baja-filter-actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 7px;
        }

        .baja-filter-actions .btn {
            min-height: 35px;
            padding: 7px 11px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 700;
        }

        .baja-summary {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
            margin-bottom: 10px;
        }

        .baja-summary-card {
            display: flex;
            min-height: 68px;
            align-items: center;
            gap: 10px;
            padding: 11px 13px;
        }

        .baja-summary-icon {
            display: grid;
            width: 36px;
            height: 36px;
            flex: 0 0 36px;
            place-items: center;
            border-radius: 10px;
            background: var(--baja-soft);
            color: var(--baja-green);
        }

        .baja-summary-value {
            display: block;
            color: var(--baja-ink);
            font-size: 18px;
            font-weight: 700;
            line-height: 1.1;
        }

        .baja-summary-label {
            display: block;
            margin-top: 3px;
            color: var(--baja-muted);
            font-size: 12px;
        }

        .baja-table-card {
            border-top: 3px solid var(--baja-green);
        }

        .baja-table-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 11px 14px;
            border-bottom: 1px solid var(--baja-line);
        }

        .baja-table-heading h3 {
            margin: 0;
            color: var(--baja-ink);
            font-size: 15px;
            font-weight: 700;
        }

        .baja-table-note {
            color: var(--baja-muted);
            font-size: 12px;
        }

        .baja-table-scroll {
            max-height: min(68vh, 680px);
            overflow: auto;
        }

        .baja-table {
            width: 100%;
            min-width: 610px;
            margin: 0;
            border-collapse: separate;
            border-spacing: 0;
            color: var(--baja-ink);
            font-size: 14px;
        }

        .baja-table th {
            padding: 6px 9px;
            border-right: 1px solid var(--baja-line);
            border-bottom: 1px solid var(--baja-line);
            vertical-align: middle;
        }

        .baja-table thead th {
            position: sticky;
            top: 0;
            z-index: 3;
            border-color: #315f53;
            background: var(--baja-ink);
            color: #fff;
            font-size: 13px;
            font-weight: 700;
            white-space: nowrap;
        }

        .baja-table tbody th {
            background: #fff;
            font-weight: 500;
        }

        .baja-table tbody tr:nth-child(even):not(.baja-plant-row) th {
            background: var(--baja-soft);
        }

        .baja-table tbody tr:hover:not(.baja-plant-row) th {
            background: #e4f5ee;
        }

        .baja-table .baja-number {
            text-align: right;
            font-variant-numeric: tabular-nums;
        }

        .baja-table .baja-plant-row {
            cursor: pointer;
        }

        .baja-table .baja-plant-row th {
            padding-top: 7px;
            padding-bottom: 7px;
            background: #e8f6f1;
            color: var(--baja-ink);
            font-weight: 700;
        }

        .baja-plant-toggle {
            color: var(--baja-green);
            transition: transform .15s ease;
        }

        .baja-plant-row[aria-expanded="true"] .baja-plant-toggle {
            transform: rotate(90deg);
        }

        .baja-available-input {
            width: 100%;
            min-width: 76px;
            height: 30px;
            padding: 3px 7px;
            border: 1px solid var(--baja-line);
            border-radius: 5px;
            background: var(--baja-soft);
            color: var(--baja-ink);
            text-align: right;
            font-size: 14px;
            font-weight: 700;
        }

        .baja-table .btn {
            padding: 4px 8px;
            font-size: 12px;
        }

        .baja-empty {
            display: flex;
            min-height: 130px;
            align-items: center;
            justify-content: center;
            gap: 9px;
            padding: 20px;
            color: var(--baja-muted);
            text-align: center;
            font-size: 14px;
        }

        .baja-empty i {
            color: var(--baja-green);
            font-size: 21px;
        }

        @media (max-width: 991px) {
            .baja-filter-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .baja-filter-footer {
                align-items: flex-start;
                flex-direction: column;
            }

            .baja-filter-actions {
                justify-content: flex-start;
            }
        }

        @media (max-width: 600px) {
            .baja-filter-card {
                padding: 13px;
            }

            .baja-filter-grid {
                grid-template-columns: minmax(0, 1fr);
                gap: 10px;
            }

            .baja-filter-actions {
                display: grid;
                width: 100%;
                grid-template-columns: 1fr;
            }

            .baja-filter-actions .btn {
                text-align: left;
            }

            .baja-summary {
                grid-template-columns: 1fr;
                gap: 7px;
            }

            .baja-table-heading {
                align-items: flex-start;
                flex-direction: column;
            }
        }
    </style>
@endsection

@section('script_final')
    @include('adminlte.gestion.postco.botar_inventario.script')
@endsection
