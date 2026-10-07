@extends('layouts.adminlte.master')

@section('titulo')
    Clientes
@endsection

@section('contenido')
    @include('adminlte.gestion.partials.breadcrumb')

    <section class="content clientes-page">
        <div class="clientes-shell">
            <header class="clientes-heading">
                <div class="clientes-heading-icon"><i class="fa fa-users"></i></div>
                <div class="clientes-heading-copy">
                    <h1>Clientes</h1>
                    <p>Consulta y administra la información de tus clientes.</p>
                </div>
                <div class="clientes-heading-actions">
                    <button type="button" class="btn btn-clientes-primary" onclick="add_cliente()">
                        <i class="fa fa-plus"></i><span>Nuevo cliente</span>
                    </button>
                </div>
            </header>

            <section class="clientes-filter-card" aria-label="Filtros de clientes">
                <div class="clientes-filter-title">
                    <span><i class="fa fa-sliders"></i></span>
                    <div>
                        <strong>Buscar clientes</strong>
                        <small>Filtra por nombre, identificación, correo o dirección.</small>
                    </div>
                </div>
                <div class="clientes-filters">
                    <div class="clientes-filter-field clientes-search-field">
                        <label for="busqueda_clientes">Búsqueda</label>
                        <div class="clientes-input-wrap">
                            <i class="fa fa-search"></i>
                            <input type="search" id="busqueda_clientes" name="busqueda_clientes"
                                class="form-control" placeholder="Nombre, identificación, correo..."
                                autocomplete="off" onkeydown="if (event.key === 'Enter') buscar_listado()">
                        </div>
                    </div>
                    <div class="clientes-filter-field clientes-segment-field">
                        <label for="filtro_segmento">Segmento</label>
                        <select id="filtro_segmento" name="filtro_segmento" class="form-control"
                            onchange="buscar_listado()">
                            <option value="">Todos los segmentos</option>
                            @foreach ($segmentos as $segmento)
                                <option value="{{ $segmento }}">{{ $segmento }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="button" class="btn btn-clientes-primary clientes-search-button"
                        onclick="buscar_listado()">
                        <i class="fa fa-search"></i> Buscar
                    </button>
                </div>
            </section>

            <section class="clientes-results-card">
                <div class="clientes-results-heading">
                    <div>
                        <h2><i class="fa fa-address-book"></i> Directorio de clientes</h2>
                        <p>Selecciona un cliente para consultar o editar su información.</p>
                    </div>
                    <span class="clientes-results-tag"><i class="fa fa-leaf"></i> Florisol</span>
                </div>
                <div id="div_listado_clientes" aria-live="polite">
                    <div class="clientes-loading"><i class="fa fa-spinner fa-spin"></i> Cargando clientes...</div>
                </div>
            </section>
        </div>
    </section>

    <style>
        .clientes-page {
            --clientes-green: #00B388;
            --clientes-ink: #003729;
            --clientes-muted: #5A7177;
            --clientes-line: #dce9e4;
            --clientes-soft: #f4faf7;
            padding-top: 10px;
            color: var(--clientes-ink);
        }

        .clientes-shell {
            max-width: 1500px;
            margin: 0 auto;
        }

        .clientes-heading {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
            padding: 13px 15px;
            border: 1px solid var(--clientes-line);
            border-radius: 10px;
            background: linear-gradient(110deg, #f4faf7, #fff 72%);
        }

        .clientes-heading-icon {
            display: grid;
            width: 43px;
            height: 43px;
            flex: 0 0 43px;
            place-items: center;
            border-radius: 12px;
            background: #e4f5ee;
            color: var(--clientes-green);
            font-size: 18px;
        }

        .clientes-heading-copy {
            min-width: 0;
            flex: 1 1 auto;
        }

        .clientes-heading-copy h1 {
            margin: 0;
            color: var(--clientes-ink);
            font-size: 19px;
            font-weight: 700;
        }

        .clientes-heading-copy p,
        .clientes-results-heading p {
            margin: 3px 0 0;
            color: var(--clientes-muted);
            font-size: 12px;
        }

        .clientes-heading-actions {
            display: flex;
            flex: 0 0 auto;
            gap: 7px;
        }

        .clientes-heading-actions .btn,
        .clientes-search-button {
            min-height: 35px;
            padding: 7px 11px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }

        .clientes-heading-actions .btn i {
            margin-right: 4px;
        }

        .btn-clientes-primary {
            border: 1px solid var(--clientes-green);
            background: var(--clientes-green);
            color: #fff;
        }

        .btn-clientes-primary:hover,
        .btn-clientes-primary:focus {
            border-color: #008f6c;
            background: #008f6c;
            color: #fff;
        }

        .btn-clientes-outline {
            border: 1px solid var(--clientes-line);
            background: #fff;
            color: var(--clientes-ink);
        }

        .btn-clientes-outline:hover,
        .btn-clientes-outline:focus {
            border-color: #a8d9c8;
            background: var(--clientes-soft);
            color: var(--clientes-ink);
        }

        .clientes-filter-card,
        .clientes-results-card {
            overflow: hidden;
            margin-bottom: 12px;
            border: 1px solid var(--clientes-line);
            border-radius: 10px;
            background: #fff;
            box-shadow: 0 3px 12px rgba(0, 55, 41, .04);
        }

        .clientes-filter-card {
            padding: 12px 14px;
        }

        .clientes-filter-title {
            display: flex;
            align-items: center;
            gap: 9px;
            margin-bottom: 10px;
        }

        .clientes-filter-title > span {
            display: grid;
            width: 31px;
            height: 31px;
            place-items: center;
            border-radius: 8px;
            background: #eaf6f0;
            color: var(--clientes-green);
        }

        .clientes-filter-title strong,
        .clientes-filter-title small {
            display: block;
        }

        .clientes-filter-title strong {
            font-size: 13px;
        }

        .clientes-filter-title small {
            margin-top: 2px;
            color: var(--clientes-muted);
            font-size: 11px;
        }

        .clientes-filters {
            display: grid;
            grid-template-columns: minmax(240px, 1.5fr) minmax(180px, 1fr) auto;
            align-items: end;
            gap: 10px;
        }

        .clientes-filter-field label {
            display: block;
            margin-bottom: 4px;
            color: var(--clientes-ink);
            font-size: 11px;
            font-weight: 700;
        }

        .clientes-input-wrap {
            position: relative;
        }

        .clientes-input-wrap > i {
            position: absolute;
            top: 10px;
            left: 10px;
            color: #799088;
            font-size: 12px;
        }

        .clientes-filter-field .form-control {
            height: 35px;
            border-color: var(--clientes-line);
            border-radius: 6px;
            box-shadow: none;
            color: var(--clientes-ink);
            font-size: 12px;
        }

        .clientes-input-wrap .form-control {
            padding-left: 30px;
        }

        .clientes-filter-field .form-control:focus {
            border-color: var(--clientes-green);
            box-shadow: 0 0 0 2px rgba(0, 179, 136, .1);
        }

        .clientes-search-button {
            white-space: nowrap;
        }

        .clientes-results-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 12px 14px;
            border-bottom: 1px solid var(--clientes-line);
        }

        .clientes-results-heading h2 {
            margin: 0;
            color: var(--clientes-ink);
            font-size: 14px;
            font-weight: 700;
        }

        .clientes-results-heading h2 i {
            margin-right: 4px;
            color: var(--clientes-green);
        }

        .clientes-results-tag {
            flex: 0 0 auto;
            padding: 5px 8px;
            border-radius: 20px;
            background: #eaf6f0;
            color: #147452;
            font-size: 10px;
            font-weight: 700;
        }

        .clientes-loading {
            padding: 35px 12px;
            color: var(--clientes-muted);
            text-align: center;
            font-size: 12px;
        }

        .clientes-loading i {
            margin-right: 5px;
            color: var(--clientes-green);
        }

        @media (max-width: 767px) {
            .clientes-heading {
                align-items: flex-start;
                flex-wrap: wrap;
            }

            .clientes-heading-actions {
                width: 100%;
            }

            .clientes-heading-actions .btn {
                flex: 1 1 auto;
            }

            .clientes-filters {
                grid-template-columns: minmax(0, 1fr) minmax(135px, .8fr);
            }

            .clientes-search-button {
                grid-column: 1 / -1;
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            .clientes-heading-actions .btn {
                padding-right: 7px;
                padding-left: 7px;
                font-size: 11px;
            }

            .clientes-heading-actions .btn i {
                margin-right: 2px;
            }

            .clientes-filters {
                grid-template-columns: 1fr;
            }

            .clientes-search-button {
                grid-column: auto;
            }

            .clientes-results-tag {
                display: none;
            }
        }
    </style>
@endsection

@section('script_final')
    @include('adminlte.gestion.postcocecha.clientes.script')
@endsection
