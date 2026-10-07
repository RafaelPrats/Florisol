@extends('layouts.adminlte.master')

@section('titulo')
    Confirmar pedidos
@endsection

@section('contenido')
    <section class="content-header">
        <h1>
            Confirmar pedidos
            <small class="text-color_yura">módulo de postcosecha</small>
        </h1>
        <ol class="breadcrumb">
            <li>
                <a href="javascript:void(0)" class="text-color_yura" onclick="cargar_url('')">
                    <i class="fa fa-home"></i> Inicio
                </a>
            </li>
            <li class="text-color_yura">{{ $submenu->menu->grupo_menu->nombre }}</li>
            <li class="text-color_yura">{{ $submenu->menu->nombre }}</li>
            <li class="active">
                <a href="javascript:void(0)" class="text-color_yura"
                    onclick="cargar_url('{{ $submenu->url }}')">
                    <i class="fa fa-refresh"></i> {{ $submenu->nombre }}
                </a>
            </li>
        </ol>
    </section>

    <section class="content confirmar-proyecto-page">
        <div class="confirmar-proyecto-heading">
            <span class="confirmar-proyecto-heading-icon"><i class="fa fa-check-square-o"></i></span>
            <div class="confirmar-proyecto-heading-copy">
                <h2>Confirmación de pedidos</h2>
                <p>Indica el pedido y la finca para revisar y registrar los tallos confirmados.</p>
            </div>
        </div>

        <section class="confirmar-proyecto-filter">
            <div class="confirmar-proyecto-filter-title">
                <i class="fa fa-search"></i>
                <strong>Seleccionar pedido y finca</strong>
            </div>
            <div class="confirmar-proyecto-filter-fields">
                <div class="confirmar-proyecto-field">
                    <label for="codigo_pedido">Código del pedido</label>
                    <div class="confirmar-proyecto-input-wrap">
                        <i class="fa fa-hashtag"></i>
                        <input type="text" name="codigo_pedido" id="codigo_pedido"
                            class="form-control" placeholder="Ingresa el código del pedido"
                            value="{{ $proyecto }}" autocomplete="off"
                            onkeydown="if (event.key === 'Enter') listar_reporte()">
                    </div>
                </div>
                <div class="confirmar-proyecto-field">
                    <label for="finca_proveedor">Finca proveedora</label>
                    <div class="confirmar-proyecto-input-wrap">
                        <i class="fa fa-leaf"></i>
                        <select name="finca_proveedor" id="finca_proveedor" class="form-control"
                            onchange="listar_reporte()">
                            @if (count($fincas) > 1)
                                <option value="">Selecciona una finca</option>
                            @endif
                            @foreach ($fincas as $f)
                                <option value="{{ $f->id_finca_proveedor }}"
                                    {{ request('f') == $f->id_finca_proveedor ? 'selected' : '' }}>
                                    {{ $f->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <button type="button" class="btn btn-confirmar-proyecto-search" onclick="listar_reporte()">
                    <i class="fa fa-search"></i> Consultar pedido
                </button>
            </div>
            <div class="confirmar-proyecto-filter-hint">
                <i class="fa fa-info-circle"></i>
                La finca determina las variedades y cantidades que aparecerán para confirmar.
            </div>
        </section>

        <div id="div_listado" aria-live="polite"></div>
    </section>

    <style>
        .confirmar-proyecto-page {
            --confirm-green: #00B388;
            --confirm-ink: #003729;
            --confirm-muted: #5A7177;
            --confirm-line: #dce9e4;
            padding-top: 10px;
            color: var(--confirm-ink);
        }

        .confirmar-proyecto-heading {
            display: flex;
            align-items: center;
            gap: 11px;
            margin-bottom: 11px;
            padding: 12px 14px;
            border: 1px solid var(--confirm-line);
            border-radius: 9px;
            background: linear-gradient(110deg, #f4faf7, #fff 72%);
        }

        .confirmar-proyecto-heading-icon {
            display: grid;
            width: 40px;
            height: 40px;
            flex: 0 0 40px;
            place-items: center;
            border-radius: 11px;
            background: #e4f5ee;
            color: var(--confirm-green);
            font-size: 17px;
        }

        .confirmar-proyecto-heading-copy {
            min-width: 0;
        }

        .confirmar-proyecto-heading h2 {
            margin: 0;
            color: var(--confirm-ink);
            font-size: 17px;
            font-weight: 700;
        }

        .confirmar-proyecto-heading p {
            margin: 3px 0 0;
            color: var(--confirm-muted);
            font-size: 11px;
        }

        .confirmar-proyecto-filter {
            margin-bottom: 11px;
            padding: 12px 14px 9px;
            border: 1px solid var(--confirm-line);
            border-radius: 9px;
            background: #fff;
            box-shadow: 0 3px 12px rgba(0, 55, 41, .04);
        }

        .confirmar-proyecto-filter-title {
            display: flex;
            align-items: center;
            gap: 7px;
            margin-bottom: 9px;
            font-size: 12px;
        }

        .confirmar-proyecto-filter-title i {
            color: var(--confirm-green);
        }

        .confirmar-proyecto-filter-fields {
            display: grid;
            grid-template-columns: minmax(200px, 1fr) minmax(200px, 1fr) auto;
            align-items: end;
            gap: 10px;
        }

        .confirmar-proyecto-field label {
            display: block;
            margin-bottom: 4px;
            color: var(--confirm-ink);
            font-size: 11px;
            font-weight: 700;
        }

        .confirmar-proyecto-input-wrap {
            position: relative;
        }

        .confirmar-proyecto-input-wrap > i {
            position: absolute;
            z-index: 1;
            top: 10px;
            left: 10px;
            color: #799088;
            font-size: 12px;
        }

        .confirmar-proyecto-input-wrap .form-control {
            height: 36px;
            padding-left: 30px;
            border-color: var(--confirm-line);
            border-radius: 5px;
            box-shadow: none;
            color: var(--confirm-ink);
            font-size: 12px;
        }

        .confirmar-proyecto-input-wrap .form-control:focus {
            border-color: var(--confirm-green);
            box-shadow: 0 0 0 2px rgba(0, 179, 136, .1);
        }

        .btn-confirmar-proyecto-search {
            min-height: 36px;
            padding: 7px 12px;
            border: 1px solid var(--confirm-green);
            border-radius: 5px;
            background: var(--confirm-green);
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
        }

        .btn-confirmar-proyecto-search:hover,
        .btn-confirmar-proyecto-search:focus {
            border-color: #008f6c;
            background: #008f6c;
            color: #fff;
        }

        .confirmar-proyecto-filter-hint {
            margin-top: 8px;
            color: var(--confirm-muted);
            font-size: 10px;
        }

        .confirmar-proyecto-filter-hint i {
            margin-right: 3px;
            color: var(--confirm-green);
        }

        @media (max-width: 680px) {
            .confirmar-proyecto-filter-fields {
                grid-template-columns: 1fr;
            }

            .btn-confirmar-proyecto-search {
                width: 100%;
            }
        }
    </style>
@endsection

@section('script_final')
    @include('adminlte.gestion.postco.confirmar_proyecto.script')
@endsection
