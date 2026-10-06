@extends('layouts.adminlte.master')

@section('titulo')
    Pedidos
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Pedidos
            <small class="text-color_yura">módulo de comercialiacion</small>
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
        <section class="proyectos-filtros">
            <div class="proyectos-filtros-heading">
                <div class="proyectos-filtros-title">
                    <span class="proyectos-filtros-icon"><i class="fa fa-sliders"></i></span>
                    <div>
                        <h3>Buscar pedidos</h3>
                        <small>Filtra por fecha, segmento, cliente y tipo</small>
                    </div>
                </div>
                <button type="button" class="btn proyectos-btn-nuevo" onclick="add_proyecto()">
                    <i class="fa fa-plus"></i> Nuevo pedido
                </button>
            </div>

            <div class="proyectos-filtros-grid">
                <div class="proyectos-filtro">
                    <label for="desde_filtro"><i class="fa fa-calendar-o"></i> Desde</label>
                    <input type="date" id="desde_filtro" class="form-control" value="{{ hoy() }}">
                </div>
                <div class="proyectos-filtro">
                    <label for="hasta_filtro"><i class="fa fa-calendar-check-o"></i> Hasta</label>
                    <input type="date" id="hasta_filtro" class="form-control" value="{{ hoy() }}">
                </div>
                <div class="proyectos-filtro">
                    <label for="segmento_filtro"><i class="fa fa-tags"></i> Segmento</label>
                    <select id="segmento_filtro" class="form-control">
                        <option value="T">Todos los segmentos</option>
                        @foreach ($segmentos as $seg)
                            <option value="{{ $seg }}">{{ $seg }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="proyectos-filtro proyectos-filtro-cliente">
                    <label for="cliente_filtro"><i class="fa fa-user-o"></i> Cliente</label>
                    <select id="cliente_filtro" class="form-control">
                        <option value="T">Todos los clientes</option>
                        @foreach ($clientes as $cli)
                            <option value="{{ $cli->id_cliente }}">{{ $cli->detalle()->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="proyectos-filtro">
                    <label for="tipo_filtro"><i class="fa fa-cube"></i> Tipo de pedido</label>
                    <select id="tipo_filtro" class="form-control">
                        <option value="T">Todos los tipos</option>
                        <option value="OM">OPEN MARKET</option>
                        <option value="SO">STANDING ORDER</option>
                    </select>
                </div>
            </div>

            <div class="proyectos-filtros-footer">
                <span><i class="fa fa-info-circle"></i> Aplica los criterios para actualizar el listado</span>
                <button type="button" class="btn btn-yura_primary proyectos-btn-buscar" onclick="listar_reporte()">
                    <i class="fa fa-search"></i> Buscar pedidos
                </button>
            </div>
        </section>
        <div class="proyectos-resultados" id="div_listar_reporte"></div>
    </section>

    <style>
        .proyectos-filtros {
            margin-bottom: 12px;
            padding: 15px;
            border: 1px solid #e1ebe5;
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 2px 9px rgba(30, 66, 47, .05);
        }

        .proyectos-filtros-heading,
        .proyectos-filtros-title,
        .proyectos-filtros-footer {
            display: flex;
            align-items: center;
        }

        .proyectos-filtros-heading {
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 15px;
        }

        .proyectos-filtros-title {
            gap: 10px;
        }

        .proyectos-filtros-icon {
            display: inline-flex;
            width: 36px;
            height: 36px;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            background: #eaf6ef;
            color: #176b52;
            font-size: 15px;
        }

        .proyectos-filtros-title h3 {
            margin: 0;
            color: #30483e;
            font-size: 14px;
            font-weight: 700;
        }

        .proyectos-filtros-title small {
            display: block;
            margin-top: 3px;
            color: #788981;
            font-size: 10px;
        }

        .proyectos-btn-nuevo {
            min-height: 36px;
            padding: 8px 12px;
            border: 1px solid #cfe4d7;
            border-radius: 7px;
            background: #f1f8f4;
            color: #176b52;
            font-size: 11px;
            font-weight: 700;
        }

        .proyectos-btn-nuevo:hover,
        .proyectos-btn-nuevo:focus {
            border-color: #176b52;
            background: #176b52;
            color: #fff;
        }

        .proyectos-filtros-grid {
            display: grid;
            grid-template-columns: minmax(130px, 1fr) minmax(130px, 1fr) minmax(170px, 1.15fr) minmax(220px, 1.7fr) minmax(170px, 1.2fr);
            gap: 11px;
        }

        .proyectos-filtro {
            min-width: 0;
        }

        .proyectos-filtro label {
            display: block;
            margin-bottom: 5px;
            color: #61746a;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .35px;
            text-transform: uppercase;
        }

        .proyectos-filtro label i {
            margin-right: 4px;
            color: #75a98c;
        }

        .proyectos-filtro .form-control,
        .proyectos-filtro .select2-container .select2-selection {
            width: 100%;
            height: 37px;
            min-height: 37px;
            border: 1px solid #dce7e1;
            border-radius: 7px;
            background: #fff;
            box-shadow: none;
            color: #30483e;
            font-size: 12px;
        }

        .proyectos-filtro .form-control {
            padding: 7px 9px;
        }

        .proyectos-filtro .form-control:focus {
            border-color: #66b59a;
            box-shadow: 0 0 0 3px rgba(0, 167, 125, .09);
        }

        .proyectos-filtro .select2-container {
            width: 100% !important;
        }

        .proyectos-filtro .select2-selection--single {
            padding: 3px 6px;
        }

        .proyectos-filtro .select2-selection__rendered {
            line-height: 29px;
        }

        .proyectos-filtro .select2-selection__arrow {
            height: 35px;
        }

        .proyectos-filtros-footer {
            justify-content: space-between;
            gap: 10px;
            margin-top: 13px;
            padding-top: 11px;
            border-top: 1px solid #edf2ef;
        }

        .proyectos-filtros-footer > span {
            color: #829087;
            font-size: 10px;
        }

        .proyectos-filtros-footer > span i {
            margin-right: 4px;
            color: #75a98c;
        }

        .proyectos-btn-buscar {
            min-height: 36px;
            padding: 8px 15px;
            border-radius: 7px;
            font-size: 11px;
            font-weight: 700;
        }

        .proyectos-resultados {
            margin-top: 10px;
        }

        @media (max-width: 1100px) {
            .proyectos-filtros-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        @media (max-width: 767px) {
            .proyectos-filtros {
                padding: 12px;
            }

            .proyectos-filtros-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 10px;
            }

            .proyectos-filtros-footer {
                align-items: stretch;
                flex-direction: column;
            }

            .proyectos-btn-buscar {
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            .proyectos-filtros-heading {
                align-items: flex-start;
            }

            .proyectos-filtros-grid {
                grid-template-columns: minmax(0, 1fr);
            }

            .proyectos-btn-nuevo {
                padding: 7px 9px;
                font-size: 10px;
                white-space: nowrap;
            }
        }
    </style>
@endsection

@section('script_final')
    @include('adminlte.gestion.comercializacion.proyectos.script')
@endsection
