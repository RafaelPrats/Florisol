@extends('layouts.adminlte.master')

@section('titulo')
    Corregir Inventario
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Corregir Inventario
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
    <section class="content ci-page">
        <div class="ci-filter-card">
            <div class="ci-filter-heading">
                <span class="ci-icon"><i class="fa fa-sliders"></i></span>
                <div>
                    <h2>Alcance de revisión</h2>
                    <p>Filtra el inventario por planta, variedad y bodega.</p>
                </div>
            </div>
            <div class="ci-filter-grid">
                <div class="ci-filter-field">
                    <label for="planta_filtro">Planta</label>
                    <select name="planta_filtro" id="planta_filtro" class="form-control input-yura_default"
                        onchange="select_planta_global($(this).val(), 'variedad_filtro', 'div_filtro_variedad', '<option value=&quot;&quot;>Todas las variedades</option>')">
                        <option value="">Todas las plantas</option>
                        @foreach ($plantas as $p)
                            <option value="{{ $p->id_planta }}">{{ $p->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="ci-filter-field" id="div_filtro_variedad">
                    <label for="variedad_filtro">Variedad</label>
                    <select name="variedad_filtro" id="variedad_filtro" class="form-control input-yura_default">
                        <option value="">Todas las variedades</option>
                    </select>
                </div>
                <div class="ci-filter-field">
                    <label for="bodega_filtro">Bodega</label>
                    <select name="bodega_filtro" id="bodega_filtro" class="form-control input-yura_default"
                        onchange="listar_reporte()">
                        <option value="V">Ventas</option>
                        <option value="P">Producción</option>
                    </select>
                </div>
                <div class="ci-filter-action">
                    <button type="button" class="btn btn-yura_primary" onclick="listar_reporte()">
                        <i class="fa fa-fw fa-search"></i> Consultar inventario
                    </button>
                </div>
            </div>
        </div>

        <div id="div_listado"></div>
    </section>

    <style>
        .ci-page {
            color: #003729;
        }

        .ci-filter-card,
        .ci-transaction-card,
        .ci-results-card {
            margin-bottom: 12px;
            overflow: hidden;
            border: 1px solid #e1e8e6;
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 2px 7px #0000000a;
        }

        .ci-filter-card {
            padding: 14px;
            border-top: 4px solid #00B388;
        }

        .ci-filter-heading,
        .ci-card-heading {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .ci-filter-heading {
            margin-bottom: 12px;
        }

        .ci-icon {
            display: inline-flex;
            width: 34px;
            height: 34px;
            flex: 0 0 34px;
            align-items: center;
            justify-content: center;
            border-radius: 9px;
            background: rgba(0, 179, 136, .1);
            color: #00a67d;
            font-size: 14px;
        }

        .ci-filter-heading h2,
        .ci-card-heading h3 {
            margin: 0;
            color: #5A7177;
            font-size: 14px;
            font-weight: 700;
        }

        .ci-filter-heading p,
        .ci-card-heading p {
            margin: 3px 0 0;
            color: #87969a;
            font-size: 11px;
        }

        .ci-filter-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr)) auto;
            align-items: end;
            gap: 10px;
        }

        .ci-filter-field {
            min-width: 0;
        }

        .ci-filter-field label,
        .ci-transaction-field label {
            display: block;
            margin-bottom: 5px;
            color: #5A7177;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .3px;
            text-transform: uppercase;
        }

        .ci-filter-field .select2-container {
            width: 100% !important;
        }

        .ci-filter-field .select2-selection {
            min-height: 34px !important;
            border: 1px solid #707070 !important;
            border-radius: 18px !important;
            background: #F7F8F8 !important;
        }

        .ci-filter-field .select2-selection__rendered {
            line-height: 32px !important;
            color: #003729 !important;
        }

        .ci-filter-action .btn,
        .ci-save-button {
            min-height: 35px;
            border-radius: 18px;
            font-size: 11px;
            font-weight: 700;
        }

        .ci-transaction-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 11px 13px;
            border-left: 4px solid #5A7177;
        }

        .ci-transaction-title {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #5A7177;
            font-size: 11px;
            font-weight: 700;
        }

        .ci-transaction-fields {
            display: grid;
            grid-template-columns: minmax(145px, 190px) minmax(105px, 140px);
            gap: 9px;
        }

        .ci-transaction-field {
            min-width: 0;
        }

        .ci-transaction-field label {
            margin-bottom: 3px;
            font-size: 9px;
        }

        .ci-transaction-field .form-control {
            height: 32px;
            border-radius: 17px;
            font-size: 11px;
        }

        .ci-results-card {
            border-top: 3px solid #00B388;
        }

        .ci-card-heading {
            justify-content: space-between;
            padding: 10px 13px;
            border-bottom: 1px solid #edf1f0;
        }

        .ci-result-title {
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 0;
        }

        .ci-result-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            justify-content: flex-end;
        }

        .ci-badge {
            padding: 4px 8px;
            border: 1px solid #e1e8e6;
            border-radius: 15px;
            background: #f7faf8;
            color: #5A7177;
            font-size: 9px;
            font-weight: 700;
            white-space: nowrap;
        }

        .ci-badge-warning {
            border-color: rgba(239, 110, 17, .25);
            background: rgba(239, 110, 17, .08);
            color: #b6530b;
        }

        .ci-table-wrap {
            max-height: min(62vh, 620px);
            overflow: auto;
        }

        .ci-table {
            width: 100%;
            margin: 0;
            table-layout: fixed;
            border-collapse: separate;
            border-spacing: 0;
            color: #003729;
            font-size: 13px;
        }

        .ci-table th,
        .ci-table td {
            padding: 7px 9px;
            border-right: 1px solid #edf1f0;
            border-bottom: 1px solid #edf1f0;
            vertical-align: middle;
        }

        .ci-table th:last-child,
        .ci-table td:last-child {
            border-right: 0;
        }

        .ci-table thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            background: #5A7177;
            color: #fff;
            font-size: 11px;
            letter-spacing: .25px;
            text-transform: uppercase;
        }

        .ci-table thead th:nth-child(5) {
            background: #ef6e11;
        }

        .ci-table tbody tr:nth-child(even) {
            background: #f8fbfa;
        }

        .ci-table tbody tr:hover {
            background: rgba(0, 179, 136, .08);
        }

        .ci-table .ci-name {
            overflow-wrap: anywhere;
            font-weight: 600;
        }

        .ci-table .ci-number {
            text-align: right;
            font-variant-numeric: tabular-nums;
        }

        .ci-table .ci-inventory-alert {
            background: #fff3d4;
            color: #795400;
            font-weight: 700;
        }

        .ci-table .ci-input-cell {
            background: rgba(239, 110, 17, .045);
        }

        .ci-table input.ci-correction-input {
            width: min(100%, 110px);
            height: 30px;
            padding: 3px 8px;
            border: 1px solid #ef6e11;
            border-radius: 16px;
            background: #fffaf4;
            color: #003729;
            font-size: 13px;
            text-align: right;
        }

        .ci-table input.ci-correction-input:focus {
            border-color: #00B388;
            outline: 0;
            box-shadow: 0 0 0 2px rgba(0, 179, 136, .13);
        }

        .ci-status {
            display: inline-block;
            padding: 4px 7px;
            border-radius: 12px;
            background: rgba(0, 179, 136, .1);
            color: #007c60;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
        }

        .ci-status-review {
            background: rgba(208, 28, 98, .09);
            color: #b51c56;
        }

        .ci-sort-button {
            padding: 0;
            border: 0;
            background: transparent;
            color: inherit;
            font: inherit;
            letter-spacing: inherit;
            text-align: inherit;
            text-transform: inherit;
        }

        .ci-sort-button:hover,
        .ci-sort-button:focus {
            color: #fff;
            text-decoration: underline;
            outline: none;
        }

        .ci-sort-button i {
            margin-left: 3px;
            opacity: .7;
        }

        .ci-empty {
            padding: 26px 12px !important;
            color: #87969a;
            text-align: center;
        }

        .ci-results-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 10px 13px;
            background: #f8fbfa;
        }

        .ci-results-footer small {
            color: #5A7177;
            font-size: 10px;
        }

        .ci-save-button {
            padding: 7px 14px;
            box-shadow: 0 3px 9px rgba(0, 179, 136, .17);
        }

        @media (max-width: 991px) {
            .ci-filter-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .ci-filter-action .btn {
                width: 100%;
            }
        }

        @media (max-width: 767px) {
            .ci-filter-card {
                padding: 11px;
            }

            .ci-filter-grid {
                grid-template-columns: minmax(0, 1fr);
                gap: 8px;
            }

            .ci-filter-action {
                padding-top: 2px;
            }

            .ci-transaction-card {
                align-items: stretch;
                flex-direction: column;
            }

            .ci-transaction-fields {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .ci-card-heading {
                align-items: flex-start;
                flex-direction: column;
            }

            .ci-result-badges {
                justify-content: flex-start;
            }

            .ci-table {
                font-size: 12px;
            }

            .ci-table th,
            .ci-table td {
                padding: 6px 5px;
            }

            .ci-table input.ci-correction-input {
                width: 100%;
            }
        }

        @media (max-width: 420px) {
            .ci-table {
                font-size: 11px;
            }

            .ci-table th,
            .ci-table td {
                padding: 5px 3px;
            }

            .ci-results-footer {
                align-items: stretch;
                flex-direction: column;
            }

            .ci-save-button {
                width: 100%;
            }
        }
    </style>
@endsection

@section('script_final')
    @include('adminlte.gestion.postco.corregir_inventario.script')
@endsection
