@extends('layouts.adminlte.master')

@section('titulo')
    Inventario de Recepción
@endsection

@section('contenido')
    <section class="content-header">
        <h1>
            Inventario de Recepción
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

    <section class="content inventario-recepcion">
        <div class="reception-filter-card">
            <div class="reception-filter-heading">
                <span class="reception-filter-icon"><i class="fa fa-cubes"></i></span>
                <div>
                    <h2>Existencias por período</h2>
                    <p>Consulta el inventario disponible de los últimos nueve días y el saldo anterior.</p>
                </div>
            </div>

            <div class="reception-filter-grid">
                <div class="reception-filter-row">
                    <div class="reception-filter-field">
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
                    <div class="reception-filter-field" id="div_filtro_variedad">
                        <label for="variedad_filtro">Variedad</label>
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-pagelines"></i></span>
                            <select name="variedad_filtro" id="variedad_filtro" class="form-control" style="width: 100%">
                                <option value="">Todas las variedades</option>
                            </select>
                        </div>
                    </div>
                    <div class="reception-filter-field">
                        <label for="bodega_filtro">Bodega</label>
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-archive"></i></span>
                            <select name="bodega_filtro" id="bodega_filtro" class="form-control" style="width: 100%">
                                <option value="V">Ventas</option>
                                <option value="P">Producción</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="reception-filter-actions">
                    <button type="button" class="btn btn-yura_primary" onclick="listar_reporte()">
                        <i class="fa fa-search"></i> Consultar
                    </button>
                    <button type="button" class="btn btn-yura_default" onclick="exportar_reporte()">
                        <i class="fa fa-file-excel-o"></i> Exportar Excel
                    </button>
                </div>
            </div>
        </div>

        <div id="div_listado" class="reception-results"></div>
    </section>

    <style>
        .inventario-recepcion {
            --reception-green: #00B388;
            --reception-ink: #003729;
            --reception-muted: #5A7177;
            --reception-line: #dce9e4;
            --reception-soft: #f4faf7;
        }

        .reception-filter-card,
        .reception-summary-card,
        .reception-table-card {
            overflow: hidden;
            border: 1px solid var(--reception-line);
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 5px 18px rgba(0, 55, 41, .07);
        }

        .reception-filter-card {
            margin-bottom: 16px;
            padding: 18px;
        }

        .reception-filter-heading {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }

        .reception-filter-icon {
            display: grid;
            width: 42px;
            height: 42px;
            flex: 0 0 42px;
            place-items: center;
            border-radius: 12px;
            background: rgba(0, 179, 136, .12);
            color: var(--reception-ink);
            font-size: 18px;
        }

        .reception-filter-heading h2 {
            margin: 0 0 3px;
            color: var(--reception-ink);
            font-size: 18px;
            font-weight: 700;
        }

        .reception-filter-heading p {
            margin: 0;
            color: var(--reception-muted);
            font-size: 13px;
        }

        .reception-filter-grid {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .reception-filter-row {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
            align-items: end;
        }

        .reception-filter-field label {
            display: block;
            margin-bottom: 6px;
            color: var(--reception-ink);
            font-size: 13px;
            font-weight: 700;
        }

        .reception-filter-field .input-group,
        .reception-filter-field .select2-container {
            width: 100% !important;
        }

        .reception-filter-field .input-group-addon {
            min-width: 40px;
            border-color: var(--reception-line);
            background: var(--reception-soft);
            color: var(--reception-muted);
        }

        .reception-filter-field .form-control {
            height: 38px;
            border-color: var(--reception-line);
            box-shadow: none;
        }

        .reception-filter-field .form-control:focus {
            border-color: var(--reception-green);
            box-shadow: 0 0 0 2px rgba(0, 179, 136, .12);
        }

        .reception-filter-field .select2-selection {
            height: 38px !important;
            border-color: var(--reception-line) !important;
        }

        .reception-filter-field .select2-selection__rendered {
            line-height: 36px !important;
        }

        .reception-filter-field .select2-selection__arrow {
            height: 36px !important;
        }

        .reception-filter-actions {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
        }

        .reception-filter-actions .btn {
            min-height: 38px;
            min-width: 145px;
            border-radius: 7px;
            font-weight: 700;
        }

        .reception-summary {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 12px;
        }

        .reception-summary-card {
            display: flex;
            min-height: 78px;
            align-items: center;
            gap: 12px;
            padding: 14px 16px;
        }

        .reception-summary-icon {
            display: grid;
            width: 42px;
            height: 42px;
            flex: 0 0 42px;
            place-items: center;
            border-radius: 12px;
            background: var(--reception-soft);
            color: var(--reception-green);
            font-size: 17px;
        }

        .reception-summary-value {
            display: block;
            color: var(--reception-ink);
            font-size: 20px;
            font-weight: 700;
            line-height: 1.1;
        }

        .reception-summary-label {
            display: block;
            margin-top: 4px;
            color: var(--reception-muted);
            font-size: 12px;
        }

        .reception-table-card {
            border-top: 3px solid var(--reception-green);
        }

        .reception-table-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 14px 16px;
            border-bottom: 1px solid var(--reception-line);
        }

        .reception-table-heading h3 {
            margin: 0;
            color: var(--reception-ink);
            font-size: 16px;
            font-weight: 700;
        }

        .reception-table-hint {
            color: var(--reception-muted);
            font-size: 12px;
        }

        .reception-table-scroll {
            max-height: min(72vh, 760px);
            overflow: auto;
        }

        .reception-table {
            width: max-content;
            min-width: 100%;
            margin: 0;
            border-collapse: separate;
            border-spacing: 0;
            color: var(--reception-ink);
            font-size: 14px;
        }

        .reception-table th {
            min-width: 104px;
            padding: 9px 12px;
            border-right: 1px solid var(--reception-line);
            border-bottom: 1px solid var(--reception-line);
            text-align: right;
            white-space: nowrap;
        }

        .reception-table thead th {
            position: sticky;
            top: 0;
            z-index: 5;
            border-color: #315f53;
            background: var(--reception-ink);
            color: #fff;
            font-size: 13px;
            font-weight: 700;
        }

        .reception-table thead th.reception-plant,
        .reception-table thead th.reception-variety {
            z-index: 8;
            text-align: left;
        }

        .reception-table th.reception-plant,
        .reception-table th.reception-variety {
            position: sticky;
            text-align: left;
        }

        .reception-table th.reception-plant {
            left: 0;
            width: 180px;
            min-width: 180px;
        }

        .reception-table th.reception-variety {
            left: 180px;
            width: 210px;
            min-width: 210px;
            border-right: 2px solid #a8c9bd;
        }

        .reception-table tbody th {
            background: #fff;
            font-weight: 500;
        }

        .reception-table tbody tr:nth-child(even) th {
            background: var(--reception-soft);
        }

        .reception-table tbody tr:hover th {
            background: #e4f5ee;
        }

        .reception-table tbody th.reception-plant,
        .reception-table tbody th.reception-variety {
            z-index: 2;
        }

        .reception-table .reception-date-cell {
            border-left: 2px solid #b8d8cc;
            font-variant-numeric: tabular-nums;
        }

        .reception-table .reception-date-head {
            border-left: 2px solid var(--reception-green);
            text-align: center;
        }

        .reception-table .reception-total {
            border-left: 2px solid var(--reception-green);
            background: #e8f6f1;
            font-weight: 700;
        }

        .reception-table tfoot th {
            position: sticky;
            bottom: 0;
            z-index: 6;
            border-color: #315f53;
            background: var(--reception-ink);
            color: #fff;
            font-weight: 700;
        }

        .reception-table tfoot th:first-child {
            left: 0;
            z-index: 9;
            text-align: left;
        }

        .reception-empty {
            display: flex;
            min-height: 170px;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 24px;
            color: var(--reception-muted);
            text-align: center;
            font-size: 15px;
        }

        .reception-empty i {
            color: var(--reception-green);
            font-size: 23px;
        }

        @media (max-width: 991px) {
            .reception-filter-row {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .reception-filter-actions {
                justify-content: flex-start;
            }
        }

        @media (max-width: 600px) {
            .reception-filter-card {
                padding: 14px;
            }

            .reception-filter-row {
                grid-template-columns: minmax(0, 1fr);
                gap: 12px;
            }

            .reception-filter-actions {
                display: grid;
                grid-template-columns: 1fr 1fr;
            }

            .reception-filter-actions .btn {
                min-width: 0;
            }

            .reception-summary {
                grid-template-columns: 1fr;
                gap: 8px;
            }

            .reception-table-heading {
                align-items: flex-start;
                flex-direction: column;
                gap: 5px;
            }
        }
    </style>
@endsection

@section('script_final')
    @include('adminlte.gestion.postco.inventario_recepcion.script')
@endsection
