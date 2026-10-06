@extends('layouts.adminlte.master')

@section('titulo')
    Inventario Histórico
@endsection

@section('contenido')
    <section class="content-header">
        <h1>
            Inventario Histórico
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

    <section class="content inventario-diario">
        <div class="inventory-filter-card">
            <div class="inventory-filter-heading">
                <span class="inventory-filter-icon"><i class="fa fa-line-chart"></i></span>
                <div>
                    <h2>Consulta de inventario</h2>
                    <p>Compara los saldos diarios por planta, variedad y bodega.</p>
                </div>
            </div>

            <div class="inventory-filter-grid">
                <div class="inventory-filter-row">
                    <div class="inventory-filter-field">
                        <label for="planta_filtro">Planta</label>
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-leaf"></i></span>
                            <select name="planta_filtro" id="planta_filtro" class="form-control" style="width: 100%"
                                onchange="select_planta_global($(this).val(), 'variedad_filtro', 'div_filtro_variedad', '<option value=>Todas</option>')">
                                <option value="">Todas las plantas</option>
                                @foreach ($plantas as $p)
                                    <option value="{{ $p->id_planta }}">{{ $p->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="inventory-filter-field" id="div_filtro_variedad">
                        <label for="variedad_filtro">Variedad</label>
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-pagelines"></i></span>
                            <select name="variedad_filtro" id="variedad_filtro" class="form-control" style="width: 100%">
                                <option value="">Todas las variedades</option>
                            </select>
                        </div>
                    </div>
                    <div class="inventory-filter-field">
                        <label for="bodega_filtro">Bodega</label>
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-archive"></i></span>
                            <select name="bodega_filtro" id="bodega_filtro" class="form-control" style="width: 100%"
                                onchange="listar_reporte()">
                                <option value="V">Ventas</option>
                                <option value="P">Producción</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="inventory-filter-row inventory-filter-row-dates">
                    <div class="inventory-filter-field">
                        <label for="desde_filtro">Desde</label>
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                            <input type="date" name="desde_filtro" id="desde_filtro" class="form-control"
                                value="{{ opDiasFecha('-', 7, hoy()) }}" min="2026-09-17">
                        </div>
                    </div>
                    <div class="inventory-filter-field">
                        <label for="hasta_filtro">Hasta</label>
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                            <input type="date" name="hasta_filtro" id="hasta_filtro" class="form-control"
                                value="{{ hoy() }}" min="2026-09-17">
                        </div>
                    </div>
                    <div class="inventory-filter-actions">
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

        <div id="div_listado" class="inventory-results"></div>
    </section>

    <style>
        .inventario-diario {
            --inventory-green: #00B388;
            --inventory-ink: #003729;
            --inventory-muted: #5A7177;
            --inventory-line: #dce9e4;
            --inventory-soft: #f4faf7;
        }

        .inventory-filter-card,
        .inventory-summary-card,
        .inventory-table-card {
            overflow: hidden;
            border: 1px solid var(--inventory-line);
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 5px 18px rgba(0, 55, 41, .07);
        }

        .inventory-filter-card {
            margin-bottom: 16px;
            padding: 18px;
        }

        .inventory-filter-heading {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }

        .inventory-filter-icon {
            display: grid;
            width: 42px;
            height: 42px;
            flex: 0 0 42px;
            place-items: center;
            border-radius: 12px;
            background: rgba(0, 179, 136, .12);
            color: var(--inventory-ink);
            font-size: 18px;
        }

        .inventory-filter-heading h2 {
            margin: 0 0 3px;
            color: var(--inventory-ink);
            font-size: 18px;
            font-weight: 700;
        }

        .inventory-filter-heading p {
            margin: 0;
            color: var(--inventory-muted);
            font-size: 13px;
        }

        .inventory-filter-grid {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .inventory-filter-row {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
            align-items: end;
        }

        .inventory-filter-row-dates {
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) minmax(300px, 2fr);
        }

        .inventory-filter-field label {
            display: block;
            margin-bottom: 6px;
            color: var(--inventory-ink);
            font-size: 13px;
            font-weight: 700;
        }

        .inventory-filter-field .input-group,
        .inventory-filter-field .select2-container {
            width: 100% !important;
        }

        .inventory-filter-field .input-group-addon {
            min-width: 40px;
            border-color: var(--inventory-line);
            background: var(--inventory-soft);
            color: var(--inventory-muted);
        }

        .inventory-filter-field .form-control {
            height: 38px;
            border-color: var(--inventory-line);
            box-shadow: none;
        }

        .inventory-filter-field .form-control:focus {
            border-color: var(--inventory-green);
            box-shadow: 0 0 0 2px rgba(0, 179, 136, .12);
        }

        .inventory-filter-field .select2-selection {
            height: 38px !important;
            border-color: var(--inventory-line) !important;
        }

        .inventory-filter-field .select2-selection__rendered {
            line-height: 36px !important;
        }

        .inventory-filter-field .select2-selection__arrow {
            height: 36px !important;
        }

        .inventory-filter-actions {
            display: flex;
            align-self: end;
            gap: 8px;
        }

        .inventory-filter-actions .btn {
            min-height: 38px;
            flex: 1 1 0;
            border-radius: 7px;
            font-weight: 700;
        }

        .inventory-summary {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 12px;
        }

        .inventory-summary-card {
            display: flex;
            min-height: 78px;
            align-items: center;
            gap: 12px;
            padding: 14px 16px;
        }

        .inventory-summary-icon {
            display: grid;
            width: 42px;
            height: 42px;
            flex: 0 0 42px;
            place-items: center;
            border-radius: 12px;
            background: var(--inventory-soft);
            color: var(--inventory-green);
            font-size: 17px;
        }

        .inventory-summary-value {
            display: block;
            color: var(--inventory-ink);
            font-size: 20px;
            font-weight: 700;
            line-height: 1.1;
        }

        .inventory-summary-label {
            display: block;
            margin-top: 4px;
            color: var(--inventory-muted);
            font-size: 12px;
        }

        .inventory-table-card {
            border-top: 3px solid var(--inventory-green);
        }

        .inventory-table-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 14px 16px;
            border-bottom: 1px solid var(--inventory-line);
        }

        .inventory-table-heading h3 {
            margin: 0;
            color: var(--inventory-ink);
            font-size: 16px;
            font-weight: 700;
        }

        .inventory-table-hint {
            color: var(--inventory-muted);
            font-size: 12px;
        }

        .inventory-table-scroll {
            max-height: min(72vh, 760px);
            overflow: auto;
        }

        .inventory-table {
            width: max-content;
            min-width: 100%;
            margin: 0;
            border-collapse: separate;
            border-spacing: 0;
            color: var(--inventory-ink);
            font-size: 14px;
        }

        .inventory-table th {
            min-width: 118px;
            padding: 9px 12px;
            border-right: 1px solid #315f53;
            border-bottom: 1px solid #315f53;
            text-align: right;
            white-space: nowrap;
        }

        .inventory-table thead th {
            position: sticky;
            top: 0;
            z-index: 5;
            background: var(--inventory-ink);
            color: #fff;
            font-size: 13px;
            font-weight: 700;
        }

        .inventory-table thead th.inventory-plant,
        .inventory-table thead th.inventory-variety {
            z-index: 8;
            text-align: left;
        }

        .inventory-table th.inventory-plant,
        .inventory-table th.inventory-variety {
            position: sticky;
            text-align: left;
        }

        .inventory-table th.inventory-plant {
            left: 0;
            width: 180px;
            min-width: 180px;
        }

        .inventory-table th.inventory-variety {
            left: 180px;
            width: 210px;
            min-width: 210px;
            border-right: 2px solid #a8c9bd;
        }

        .inventory-table tbody th {
            border-right: 1px solid var(--inventory-line);
            border-bottom: 1px solid var(--inventory-line);
            background: #fff;
            font-weight: 500;
        }

        .inventory-table tbody tr:nth-child(even) th {
            background: var(--inventory-soft);
        }

        .inventory-table tbody tr:hover th {
            background: #e4f5ee;
        }

        .inventory-table tbody th.inventory-plant,
        .inventory-table tbody th.inventory-variety {
            z-index: 2;
        }

        .inventory-table tbody th.inventory-variety {
            font-weight: 700;
        }

        .inventory-table .inventory-date-cell {
            border-left: 2px solid #b8d8cc;
            text-align: right;
            font-variant-numeric: tabular-nums;
        }

        .inventory-table .inventory-date-head {
            border-left: 2px solid #00B388;
            text-align: center;
        }

        .inventory-table .inventory-change-up {
            color: #008c69;
        }

        .inventory-table .inventory-change-down {
            color: #c33c54;
        }

        .inventory-empty {
            display: flex;
            min-height: 170px;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 24px;
            color: var(--inventory-muted);
            text-align: center;
            font-size: 15px;
        }

        .inventory-empty i {
            color: var(--inventory-green);
            font-size: 23px;
        }

        @media (max-width: 991px) {
            .inventory-filter-row,
            .inventory-filter-row-dates {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .inventory-filter-actions {
                grid-column: 1 / -1;
            }
        }

        @media (max-width: 600px) {
            .inventory-filter-card {
                padding: 14px;
            }

            .inventory-filter-row,
            .inventory-filter-row-dates {
                grid-template-columns: minmax(0, 1fr);
                gap: 12px;
            }

            .inventory-filter-actions {
                display: grid;
                grid-template-columns: 1fr 1fr;
                grid-column: auto;
            }

            .inventory-summary {
                grid-template-columns: 1fr;
                gap: 8px;
            }

            .inventory-table-heading {
                align-items: flex-start;
                flex-direction: column;
                gap: 5px;
            }
        }
    </style>
@endsection

@section('script_final')
    @include('adminlte.gestion.postco.inventario_diario.script')
@endsection
