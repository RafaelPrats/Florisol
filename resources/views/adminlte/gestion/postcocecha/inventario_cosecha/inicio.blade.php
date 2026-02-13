@extends('layouts.adminlte.master')

@section('titulo')
    Inventario Recepcion
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Inventario Recepcion
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
                        <select name="planta_filtro" id="planta_filtro" class="form-control"
                            onchange="select_planta_global($(this).val(), 'variedad_filtro', 'div_filtro_variedad', '<option value=>Todas</option>')">
                            <option value="">Todas</option>
                            @foreach ($plantas as $p)
                                <option value="{{ $p->id_planta }}">{{ $p->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </td>
                <td>
                    <div class="input-group" id="div_filtro_variedad">
                        <div class="input-group-addon bg-yura_dark">
                            Variedad
                        </div>
                        <select name="variedad_filtro" id="variedad_filtro" class="form-control">
                            <option value="">Todas</option>
                        </select>
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <div class="input-group-addon bg-yura_dark">
                            Ventas
                        </div>
                        <input type="date" name="fecha_venta_filtro" id="fecha_venta_filtro" class="form-control"
                            value="{{ hoy() }}" min="{{ hoy() }}" onchange="listar_inventario_cosecha_acumulado('T')">
                        <div class="input-group-btn">
                            <button type="button" class="btn btn-yura_primary" onclick="listar_inventario_cosecha()">
                                <i class="fa fa-fw fa-search"></i> Detallados
                            </button>
                            <button type="button" class="btn btn-yura_dark" onclick="listar_inventario_cosecha_acumulado()">
                                <i class="fa fa-fw fa-search"></i> Acumulados
                            </button>
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <div style="margin-top: 5px; overflow-y: scroll; max-height: 550px" id="div_listado"></div>
    </section>

    <style>
        .tr_fija_top_0 {
            position: sticky;
            top: 0;
            z-index: 9;
        }

        .tr_fija_top_1 {
            position: sticky !important;
            top: 24px !important;
            z-index: 9 !important;
        }

        .tr_fija_bottom_0 {
            position: sticky;
            bottom: 0;
            z-index: 9;
        }
    </style>
@endsection

@section('script_final')
    @include('adminlte.gestion.postcocecha.inventario_cosecha.script')
@endsection
