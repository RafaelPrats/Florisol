@extends('layouts.adminlte.master')

@section('titulo')
    Preproduccion
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Preproduccion
            <small class="text-color_yura">módulo de postcosecha</small>
        </h1>

        <ol class="breadcrumb">
            <li><a href="javascript:void(0)" onclick="cargar_url('')" class="text-color_yura">
                    <i class="fa fa-home text-color_yura"></i>
                    Inicio</a></li>
            <li class="text-color_yura">
                {{ $submenu->menu->grupo_menu->nombre }}
            </li>
            <li class="text-color_yura">
                {{ $submenu->menu->nombre }}
            </li>

            <li class="active">
                <a href="javascript:void(0)" onclick="cargar_url('{{ $submenu->url }}')" class="text-color_yura">
                    <i class="fa fa-fw fa-refresh text-color_yura"></i> {{ $submenu->nombre }}
                </a>
            </li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        <section class="preproduccion-filtros">
            <div class="preproduccion-filtros-head">
                <div class="preproduccion-filtros-title">
                    <span class="preproduccion-filtros-icon bg-yura_primary"><i class="fa fa-sliders"></i></span>
                    <div>
                        <h3>Control de preproducción</h3>
                        <small>Define el contenido y el periodo que quieres consultar</small>
                    </div>
                </div>
                <span class="preproduccion-filtros-badge bg-yura_dark">
                    <i class="fa fa-calendar-check-o"></i> Planificación
                </span>
            </div>

            <div class="preproduccion-filtros-grid">
                <div class="preproduccion-filtro">
                    <label for="fecha_filtro"><i class="fa fa-calendar"></i> Fecha base</label>
                    <input type="date" id="fecha_filtro" class="form-control input-yura_default" value="{{ hoy() }}"
                        min="2026-09-17">
                </div>
                <div class="preproduccion-filtro">
                    <label for="tipo_filtro"><i class="fa fa-th-large"></i> Vista</label>
                    <select id="tipo_filtro" class="form-control input-yura_default"
                        onchange="listar_reporte()">
                        <option value="R">Recetas</option>
                        <option value="F">Flores</option>
                    </select>
                </div>
                <div class="preproduccion-filtro preproduccion-filtro-receta">
                    <label for="variedad_filtro"><i class="fa fa-leaf"></i> Receta</label>
                    <select id="variedad_filtro" class="form-control input-yura_default"
                        onchange="listar_reporte()">
                        <option value="T">Todas las recetas</option>
                        @foreach ($variedades as $var)
                            <option value="{{ $var->id_variedad }}">{{ $var->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="preproduccion-filtro preproduccion-filtro-cliente">
                    <label for="cliente_filtro"><i class="fa fa-users"></i> Cliente</label>
                    <select id="cliente_filtro" class="form-control input-yura_default"
                        onchange="listar_reporte()">
                        <option value="T">Todos los clientes</option>
                        @foreach ($clientes as $cli)
                            <option value="{{ $cli->id_cliente }}">{{ $cli->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="preproduccion-periodo">
                    <div class="preproduccion-periodo-heading">
                        <i class="fa fa-arrows-h"></i> Rango de planificación
                    </div>
                    <div class="preproduccion-periodo-inputs">
                        <div class="preproduccion-filtro">
                            <label for="desde_filtro">Desde</label>
                            <input type="date" id="desde_filtro" class="form-control input-yura_default"
                                value="{{ hoy() }}">
                        </div>
                        <span class="preproduccion-periodo-separator" aria-hidden="true">
                            <i class="fa fa-long-arrow-right"></i>
                        </span>
                        <div class="preproduccion-filtro">
                            <label for="hasta_filtro">Hasta</label>
                            <input type="date" id="hasta_filtro" class="form-control input-yura_default"
                                value="{{ opDiasFecha('+', 7, hoy()) }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="preproduccion-filtros-footer">
                <span><i class="fa fa-info-circle"></i> La vista y los selectores actualizan el reporte al cambiar.</span>
                <button type="button" class="btn btn-yura_primary preproduccion-buscar"
                    onclick="listar_reporte()">
                    <i class="fa fa-search"></i> Actualizar reporte
                </button>
            </div>
        </section>
        <div class="preproduccion-resultados" id="div_listar_reporte"></div>
    </section>

    <style>
        .preproduccion-filtros {
            margin-bottom: 12px;
            padding: 16px;
            border: 1px solid rgba(0, 179, 136, .2);
            border-radius: 13px;
            background: linear-gradient(135deg, #fff 0%, rgba(0, 179, 136, .035) 100%);
            box-shadow: 0 3px 12px rgba(90, 113, 119, .12);
        }

        .preproduccion-filtros-head,
        .preproduccion-filtros-title,
        .preproduccion-filtros-footer {
            display: flex;
            align-items: center;
        }

        .preproduccion-filtros-head {
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 15px;
        }

        .preproduccion-filtros-title {
            gap: 10px;
        }

        .preproduccion-filtros-icon {
            display: inline-flex;
            width: 38px;
            height: 38px;
            flex: 0 0 38px;
            align-items: center;
            justify-content: center;
            border-radius: 11px;
            color: #fff;
            font-size: 16px;
        }

        .preproduccion-filtros-title h3 {
            margin: 0;
            color: #003729;
            font-size: 15px;
            font-weight: 700;
        }

        .preproduccion-filtros-title small {
            display: block;
            margin-top: 3px;
            color: #5A7177;
            font-size: 11px;
        }

        .preproduccion-filtros-badge {
            padding: 6px 10px;
            border: 1px solid rgba(255, 255, 255, .3);
            border-radius: 20px;
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            white-space: nowrap;
        }

        .preproduccion-filtros-badge i {
            margin-right: 4px;
            color: #fff;
        }

        .preproduccion-filtros-grid {
            display: grid;
            grid-template-columns: minmax(145px, .95fr) minmax(125px, .75fr) minmax(190px, 1.25fr) minmax(190px, 1.25fr) minmax(300px, 1.8fr);
            gap: 12px;
            align-items: end;
        }

        .preproduccion-filtro {
            min-width: 0;
        }

        .preproduccion-filtro label {
            display: block;
            margin-bottom: 6px;
            color: #5A7177;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .4px;
            text-transform: uppercase;
        }

        .preproduccion-filtro label i {
            margin-right: 4px;
            color: #00B388;
        }

        .preproduccion-filtro .form-control,
        .preproduccion-filtro .select2-container .select2-selection {
            width: 100%;
            height: 38px;
            min-height: 38px;
            border: 1px solid #707070;
            border-radius: 18px;
            background-color: #F7F8F8;
            box-shadow: none;
            color: #003729;
            font-size: 12px;
        }

        .preproduccion-filtro .form-control {
            padding: 7px 9px;
        }

        .preproduccion-filtro .form-control:focus {
            border-color: #00B388;
            box-shadow: 0 0 0 3px rgba(0, 179, 136, .12);
        }

        .preproduccion-filtro .select2-container {
            width: 100% !important;
        }

        .preproduccion-filtro .select2-selection--single {
            padding: 4px 7px;
        }

        .preproduccion-filtro .select2-selection__rendered {
            line-height: 28px;
        }

        .preproduccion-filtro .select2-selection__arrow {
            height: 36px;
        }

        .preproduccion-periodo {
            min-width: 0;
            padding: 9px 10px 10px;
            border: 1px solid rgba(0, 179, 136, .2);
            border-radius: 9px;
            background: rgba(0, 179, 136, .045);
        }

        .preproduccion-periodo-heading {
            margin-bottom: 7px;
            color: #5A7177;
            font-size: 9px;
            font-weight: 700;
            letter-spacing: .45px;
            text-transform: uppercase;
        }

        .preproduccion-periodo-heading i {
            margin-right: 4px;
            color: #00B388;
        }

        .preproduccion-periodo-inputs {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 18px minmax(0, 1fr);
            gap: 5px;
            align-items: end;
        }

        .preproduccion-periodo-inputs .preproduccion-filtro label {
            margin-bottom: 4px;
            font-size: 9px;
        }

        .preproduccion-periodo-separator {
            display: flex;
            height: 38px;
            align-items: center;
            justify-content: center;
            color: #5A7177;
        }

        .preproduccion-filtros-footer {
            justify-content: space-between;
            gap: 12px;
            margin-top: 14px;
            padding-top: 12px;
            border-top: 1px solid rgba(90, 113, 119, .18);
        }

        .preproduccion-filtros-footer > span {
            color: #5A7177;
            font-size: 10px;
        }

        .preproduccion-filtros-footer > span i {
            margin-right: 4px;
            color: #00B388;
        }

        .preproduccion-buscar {
            min-height: 37px;
            padding: 8px 15px;
            border-radius: 18px;
            font-size: 11px;
            font-weight: 700;
        }

        .preproduccion-resultados {
            margin-top: 10px;
        }

        .preproduccion-table-wrap {
            width: 100%;
            max-height: 700px;
            overflow: auto;
            border: 1px solid rgba(90, 113, 119, .25);
            border-radius: 10px;
            background: #fff;
            box-shadow: 0 3px 12px rgba(90, 113, 119, .1);
        }

        .preproduccion-table {
            width: 100%;
            min-width: 780px;
            margin: 0;
            border: 0 !important;
            border-collapse: separate;
            border-spacing: 0;
            color: #003729;
            font-size: 11px;
        }

        .preproduccion-table th,
        .preproduccion-table td {
            padding: 7px 8px !important;
            border: 0 !important;
            border-right: 1px solid rgba(90, 113, 119, .16) !important;
            border-bottom: 1px solid rgba(90, 113, 119, .16) !important;
            vertical-align: middle !important;
        }

        .preproduccion-table thead th {
            position: sticky;
            top: 0;
            z-index: 5;
            height: 46px;
            background: #00B388 !important;
            color: #fff !important;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .35px;
            white-space: nowrap;
        }

        .preproduccion-table thead th.bg-yura_dark {
            background: #5A7177 !important;
        }

        .preproduccion-table thead th.th_fechas {
            border-left: 2px solid rgba(255, 255, 255, .72) !important;
            border-right: 1px solid rgba(0, 55, 41, .28) !important;
        }

        .preproduccion-table thead th small {
            display: inline-block;
            margin-top: 3px;
            color: rgba(255, 255, 255, .86);
            font-size: 9px;
            font-weight: 400;
            letter-spacing: 0;
        }

        .preproduccion-table thead th:first-child,
        .preproduccion-table tbody th:first-child,
        .preproduccion-table tfoot th:first-child {
            position: sticky;
            left: 0;
            z-index: 6;
            min-width: 160px;
        }

        .preproduccion-table thead th:first-child {
            z-index: 8;
            background: #00B388 !important;
        }

        .preproduccion-table tbody th:first-child {
            background: #fff;
            color: #003729;
            font-weight: 700;
            text-align: left;
        }

        .preproduccion-table tbody tr {
            transition: background-color .12s ease;
        }

        .preproduccion-table tbody tr:nth-child(even) {
            background: rgba(90, 113, 119, .045);
        }

        .preproduccion-table tbody tr:hover {
            background: rgba(0, 179, 136, .1) !important;
        }

        .preproduccion-table tbody tr:hover th:first-child {
            background: rgba(0, 179, 136, .1);
        }

        .preproduccion-table .preproduccion-cell-date {
            min-width: 112px;
            border-left: 2px solid rgba(90, 113, 119, .32) !important;
            border-right: 1px solid rgba(90, 113, 119, .2) !important;
            text-align: center;
        }

        .preproduccion-table .preproduccion-date-even {
            background: rgba(90, 113, 119, .06);
        }

        .preproduccion-table .preproduccion-cell-date .btn-group {
            display: inline-flex;
            max-width: 100%;
            flex-wrap: wrap;
            justify-content: center;
            gap: 3px;
        }

        .preproduccion-table .preproduccion-cell-date .btn {
            min-width: 30px;
            padding: 4px 7px;
            border-radius: 16px !important;
            font-size: 10px;
            font-weight: 700;
        }

        .preproduccion-table .preproduccion-row-total {
            position: sticky;
            bottom: 0;
            z-index: 7;
        }

        .preproduccion-table tfoot th,
        .preproduccion-table tfoot td {
            background: #00B388 !important;
            color: #fff !important;
            font-weight: 700;
            white-space: nowrap;
        }

        .preproduccion-table tfoot .preproduccion-cell-date {
            border-left-color: rgba(255, 255, 255, .72) !important;
            border-right-color: rgba(0, 55, 41, .28) !important;
        }

        .preproduccion-table tfoot th:first-child {
            z-index: 9;
            background: #00B388 !important;
        }

        .preproduccion-table tfoot .btn-yura_dark {
            background-color: #5A7177 !important;
        }

        .preproduccion-table tfoot .btn-yura_default {
            background-color: #fff !important;
        }

        .preproduccion-table .preproduccion-inventory {
            min-width: 75px;
            background: rgba(90, 113, 119, .06);
            color: #003729;
            font-variant-numeric: tabular-nums;
            font-weight: 700;
            text-align: center;
        }

        .tr_fija_top_0 {
            position: sticky;
            top: 0;
            z-index: 9;
        }

        .tr_fija_top_1 {
            position: sticky !important;
            top: 21px !important;
            z-index: 9 !important;
        }

        .tr_fija_bottom_0 {
            position: sticky;
            bottom: 0;
            z-index: 9;
        }

        @media (max-width: 1200px) {
            .preproduccion-filtros-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .preproduccion-periodo {
                grid-column: span 2;
            }
        }

        @media (max-width: 767px) {
            .preproduccion-filtros {
                padding: 12px;
            }

            .preproduccion-filtros-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 10px;
            }

            .preproduccion-filtro-receta,
            .preproduccion-filtro-cliente {
                grid-column: span 1;
            }

            .preproduccion-periodo {
                grid-column: 1 / -1;
            }

            .preproduccion-filtros-footer {
                align-items: stretch;
                flex-direction: column;
            }

            .preproduccion-buscar {
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            .preproduccion-filtros-head {
                align-items: flex-start;
            }

            .preproduccion-filtros-badge {
                padding: 5px 7px;
                font-size: 9px;
            }

            .preproduccion-filtros-grid {
                grid-template-columns: minmax(0, 1fr);
            }

            .preproduccion-periodo {
                grid-column: auto;
            }

            .preproduccion-table-wrap {
                max-height: 600px;
            }
        }
    </style>
@endsection

@section('script_final')
    {{-- JS de Chart.js --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.3/Chart.min.js"></script>

    @include('adminlte.gestion.postco.preproduccion.script')
@endsection
