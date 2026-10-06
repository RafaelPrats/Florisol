@extends('layouts.adminlte.master')

@section('titulo')
    Kardex
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Kardex
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

    <!-- Main content -->
    <section class="content kardex-page">
        <div class="kardex-filter-card">
            <div class="kardex-filter-heading">
                <span class="kardex-icon"><i class="fa fa-sliders"></i></span>
                <div>
                    <h2>Consulta de movimientos</h2>
                    <p>Selecciona la variedad, bodega y periodo que quieres revisar.</p>
                </div>
            </div>
            <div class="kardex-filter-grid">
                <div class="kardex-filter-field">
                    <label for="planta_filtro">Planta</label>
                    <select name="planta_filtro" id="planta_filtro" class="form-control input-yura_default"
                        onchange="select_planta_global($(this).val(), 'variedad_filtro', 'div_filtro_variedad', '<option value=&quot;&quot;>Seleccione una variedad</option>')">
                        <option value="">Seleccione una planta</option>
                        @foreach ($plantas as $p)
                            <option value="{{ $p->id_planta }}">{{ $p->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="kardex-filter-field" id="div_filtro_variedad">
                    <label for="variedad_filtro">Variedad</label>
                    <select name="variedad_filtro" id="variedad_filtro" class="form-control input-yura_default"
                        onchange="listar_reporte()">
                        <option value="">Seleccione una variedad</option>
                    </select>
                </div>
                <div class="kardex-filter-field">
                    <label for="bodega_filtro">Bodega</label>
                    <select name="bodega_filtro" id="bodega_filtro" class="form-control input-yura_default"
                        onchange="listar_reporte()">
                        <option value="V">Ventas</option>
                        <option value="P">Producción</option>
                    </select>
                </div>
                <div class="kardex-filter-field">
                    <label for="desde_filtro">Desde</label>
                    <input type="date" name="desde_filtro" id="desde_filtro" class="form-control input-yura_default"
                        value="{{ opDiasFecha('-', 7, hoy()) }}" min="2026-09-17">
                </div>
                <div class="kardex-filter-field">
                    <label for="hasta_filtro">Hasta</label>
                    <input type="date" name="hasta_filtro" id="hasta_filtro" class="form-control input-yura_default"
                        value="{{ opDiasFecha('-', 0, hoy()) }}" min="2026-09-17">
                </div>
                <div class="kardex-filter-actions">
                    <button type="button" class="btn btn-yura_primary" onclick="listar_reporte()">
                        <i class="fa fa-fw fa-search"></i> Consultar
                    </button>
                    <button type="button" class="btn btn-yura_default" onclick="exportar_reporte()">
                        <i class="fa fa-fw fa-file-excel-o"></i> Exportar
                    </button>
                </div>
            </div>
        </div>

        <div id="div_listado">
            <div class="kardex-empty-state">
                <span class="kardex-empty-icon"><i class="fa fa-search"></i></span>
                <strong>Elige una variedad para consultar su Kardex</strong>
                <span>Los movimientos aparecerán aquí con su saldo acumulado.</span>
            </div>
        </div>
    </section>

    <style>
        .kardex-page {
            color: #003729;
        }

        .kardex-filter-card,
        .kardex-results-card {
            margin-bottom: 12px;
            overflow: hidden;
            border: 1px solid #e1e8e6;
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 2px 7px #0000000a;
        }

        .kardex-filter-card {
            padding: 14px;
            border-top: 4px solid #00B388;
        }

        .kardex-filter-heading,
        .kardex-result-heading,
        .kardex-result-title {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .kardex-filter-heading {
            margin-bottom: 13px;
        }

        .kardex-icon,
        .kardex-empty-icon {
            display: inline-flex;
            width: 36px;
            height: 36px;
            flex: 0 0 36px;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            background: rgba(0, 179, 136, .1);
            color: #00a67d;
            font-size: 15px;
        }

        .kardex-filter-heading h2,
        .kardex-result-heading h3 {
            margin: 0;
            color: #5A7177;
            font-size: 15px;
            font-weight: 700;
        }

        .kardex-filter-heading p,
        .kardex-result-heading p {
            margin: 3px 0 0;
            color: #87969a;
            font-size: 12px;
        }

        .kardex-filter-grid {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr)) auto;
            align-items: end;
            gap: 10px;
        }

        .kardex-filter-field {
            min-width: 0;
        }

        .kardex-filter-field label {
            display: block;
            margin-bottom: 5px;
            color: #5A7177;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .3px;
            text-transform: uppercase;
        }

        .kardex-filter-field .form-control {
            height: 36px;
            font-size: 13px;
        }

        .kardex-filter-field .select2-container {
            width: 100% !important;
        }

        .kardex-filter-field .select2-selection {
            min-height: 36px !important;
            border: 1px solid #707070 !important;
            border-radius: 18px !important;
            background: #F7F8F8 !important;
        }

        .kardex-filter-field .select2-selection__rendered {
            line-height: 34px !important;
            color: #003729 !important;
            font-size: 13px;
        }

        .kardex-filter-actions {
            display: flex;
            gap: 7px;
        }

        .kardex-filter-actions .btn {
            min-height: 36px;
            border-radius: 18px;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
        }

        .kardex-results-card {
            border-top: 3px solid #00B388;
        }

        .kardex-result-heading {
            justify-content: space-between;
            flex-wrap: wrap;
            padding: 11px 14px;
            border-bottom: 1px solid #edf1f0;
        }

        .kardex-result-title {
            min-width: 0;
        }

        .kardex-result-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .kardex-chip {
            padding: 5px 9px;
            border: 1px solid #e1e8e6;
            border-radius: 16px;
            background: #f7faf8;
            color: #5A7177;
            font-size: 11px;
            white-space: nowrap;
        }

        .kardex-chip strong {
            color: #003729;
        }

        .kardex-summary {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 8px;
            padding: 10px 14px;
            background: #f8fbfa;
        }

        .kardex-summary-item {
            min-width: 0;
            padding: 8px 10px;
            border: 1px solid #e7eeeb;
            border-radius: 8px;
            background: #fff;
        }

        .kardex-summary-item span {
            display: block;
            color: #5A7177;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .kardex-summary-item strong {
            display: block;
            margin-top: 3px;
            color: #003729;
            font-size: 15px;
            font-variant-numeric: tabular-nums;
        }

        .kardex-summary-item.kardex-in strong {
            color: #009874;
        }

        .kardex-summary-item.kardex-out strong {
            color: #d01c62;
        }

        .kardex-table-wrap {
            max-height: min(65vh, 680px);
            overflow: auto;
        }

        .kardex-table {
            width: 100%;
            margin: 0;
            table-layout: fixed;
            border-collapse: separate;
            border-spacing: 0;
            color: #003729;
            font-size: 13px;
        }

        .kardex-table th,
        .kardex-table td {
            padding: 8px 9px;
            border-right: 1px solid #e6ece9;
            border-bottom: 1px solid #e6ece9;
            vertical-align: middle;
        }

        .kardex-table th:last-child,
        .kardex-table td:last-child {
            border-right: 0;
        }

        .kardex-table thead th {
            position: sticky;
            top: 0;
            z-index: 3;
            background: #5A7177;
            color: #fff;
            font-size: 11px;
            letter-spacing: .3px;
            text-transform: uppercase;
        }

        .kardex-table thead th.kardex-in-head {
            background: #00B388;
        }

        .kardex-table thead th.kardex-out-head {
            background: #d01c62;
        }

        .kardex-table tbody tr.kardex-day-0 {
            background: #fff;
        }

        .kardex-table tbody tr.kardex-day-1 {
            background: #f0f7f4;
        }

        .kardex-table tbody tr:hover {
            background: rgba(0, 179, 136, .14);
        }

        .kardex-table tbody tr.kardex-date-start td,
        .kardex-table tbody tr.kardex-date-start th {
            border-top: 2px solid rgba(0, 179, 136, .42);
        }

        .kardex-table .kardex-detail {
            overflow-wrap: anywhere;
            color: #5A7177;
        }

        .kardex-table tbody td {
            overflow-wrap: anywhere;
            word-break: normal;
        }

        .kardex-table .kardex-number {
            text-align: right;
            font-variant-numeric: tabular-nums;
        }

        .kardex-table .kardex-balance {
            background: rgba(0, 179, 136, .06);
            color: #003729;
            font-weight: 700;
        }

        .kardex-table .text-muted {
            color: #718287;
            white-space: nowrap;
        }

        .kardex-type {
            display: inline-block;
            min-width: 70px;
            padding: 5px 8px;
            border-radius: 14px;
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            text-align: center;
        }

        .kardex-type-in {
            background: #00B388;
        }

        .kardex-type-out {
            background: #d01c62;
        }

        .kardex-empty-state {
            display: flex;
            min-height: 180px;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 7px;
            padding: 24px 15px;
            border: 1px dashed #cddbd6;
            border-radius: 12px;
            background: #fff;
            color: #5A7177;
            text-align: center;
        }

        .kardex-empty-icon {
            width: 44px;
            height: 44px;
            flex-basis: 44px;
            margin-bottom: 3px;
            font-size: 18px;
        }

        .kardex-empty-state strong {
            color: #003729;
            font-size: 14px;
        }

        .kardex-empty-state span:last-child {
            font-size: 12px;
        }

        @media (max-width: 1199px) {
            .kardex-filter-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .kardex-filter-actions {
                grid-column: 1 / -1;
                justify-content: flex-end;
            }
        }

        @media (max-width: 767px) {
            .kardex-filter-card {
                padding: 11px;
            }

            .kardex-filter-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 8px;
            }

            .kardex-filter-actions {
                grid-column: 1 / -1;
            }

            .kardex-filter-actions .btn {
                flex: 1;
            }

            .kardex-result-heading {
                align-items: flex-start;
                flex-direction: column;
            }

            .kardex-result-meta {
                width: 100%;
            }

            .kardex-summary {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                padding: 8px;
            }

            .kardex-table {
                font-size: 12px;
            }

            .kardex-table th,
            .kardex-table td {
                padding: 7px 6px;
            }
        }

        @media (max-width: 420px) {
            .kardex-filter-grid {
                grid-template-columns: minmax(0, 1fr);
            }

            .kardex-table {
                font-size: 11px;
            }

            .kardex-table th,
            .kardex-table td {
                padding: 6px 4px;
            }

            .kardex-type {
                min-width: 0;
                padding: 4px 5px;
                font-size: 9px;
            }
        }
    </style>
@endsection

@section('script_final')
    @include('adminlte.gestion.postco.kardex.script')
@endsection
