@extends('layouts.adminlte.master')

@section('titulo')
    Inventario Compra de Flor
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Inventario Compra de Flor
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
    <section class="content">
        <table style="width: 100%">
            <tr>
                <td style="width: 15%">
                    <div class="input-group">
                        <select name="proveedor_filtro" id="proveedor_filtro" class="form-control input-yura_default">
                            <option value="">Todos los Proveedores</option>
                            @foreach ($proveedores as $p)
                                <option value="{{ $p->id_configuracion_empresa }}">{{ $p->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <div class="input-group-addon bg-yura_dark">
                            Planta
                        </div>
                        <select name="planta_filtro" id="planta_filtro" class="form-control">
                            <option value="">Todas</option>
                            @foreach ($plantas as $p)
                                <option value="{{ $p->id_planta }}">{{ $p->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <div class="input-group-addon bg-yura_dark">
                            Desde
                        </div>
                        <input type="date" name="fecha_desde_filtro" id="fecha_desde_filtro" class="form-control"
                            value="{{ hoy() }}" min="{{ hoy() }}" readonly>
                        <div class="input-group-addon bg-yura_dark">
                            Hasta
                        </div>
                        <input type="date" name="fecha_hasta_filtro" id="fecha_hasta_filtro" class="form-control"
                            value="{{ hoy() }}" min="{{ hoy() }}">
                        <div class="input-group-btn">
                            <button type="button" class="btn btn-yura_primary" onclick="listar_inventario_compra_flor()">
                                <i class="fa fa-fw fa-search"></i> Buscar
                            </button>
                            <button type="button" class="btn btn-yura_dark dropdown-toggle hidden" data-toggle="dropdown">
                                <i class="fa fa-fw fa-search"></i> Acumulados <i class="fa fa-fw fa-caret-down"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-right sombra_pequeña" role="menu"
                                style="z-index: 10 !important">
                                <li>
                                    <a href="javascript:void(0)" title="Eliminar"
                                        onclick="listar_inventario_compra_flor_acumulado('T')">
                                        Ventas + Inventario
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:void(0)" title="Eliminar"
                                        onclick="listar_inventario_compra_flor_acumulado('V')">
                                        Solo Ventas
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:void(0)" title="Eliminar"
                                        onclick="listar_inventario_compra_flor_acumulado('I')">
                                        Solo Inventario
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:void(0)" title="Eliminar"
                                        onclick="listar_inventario_compra_flor_acumulado('T', 1)">
                                        Solo Necesidades Negativas
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <input type="hidden" id="input_last_variedad" value="">
        <input type="hidden" id="input_last_pos_variedad" value="-1">
        <input type="hidden" id="input_total_pos_variedad" value="">
        <div style="margin-top: 5px; overflow-y: scroll; max-height: 700px" id="div_listado"></div>
        <div class="text-right" style="width: 100%">
            <span id="span_mostrar_mas_acumulado"></span>
        </div>
        <div style="margin-top: 5px; overflow-y: scroll; max-height: 700px" id="div_listado_acumulado">
            <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d" id="table_inventario_cosecha">
                <thead id="thead_acumulado">
                </thead>
                <tbody id="body_acumulado">
                </tbody>
            </table>
        </div>
        <div class="text-center">
            <button type="button" class="btn btn-sm btn-block btn-yura_default hidden" id="btn_mostrar_mas_acumulado"
                onclick="mostrar_mas_acumulado()">
                « Ver más »
            </button>
        </div>
    </section>

    <style>
        .tr_fija_top_1 {
            position: sticky !important;
            top: 21px !important;
            z-index: 8;
        }

        .tr_fija_bottom_0 {
            position: sticky;
            bottom: 0;
            z-index: 9;
        }

        .columna_fija_left_1 {
            position: sticky;
            left: 160px;
            z-index: 8;
        }
    </style>
@endsection

@section('script_final')
    @include('adminlte.gestion.postcocecha.inventario_compra_flor.script')
@endsection
