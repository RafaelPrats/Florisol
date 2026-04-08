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
        <table style="width: 100%">
            <tr>
                <td style="width: 120px">
                    <div class="input-group div_group_filtro" style="margin-top: 5px;">
                        <div class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
                            Desde
                        </div>
                        <input type="date" id="desde_filtro" class="form-control" value="{{ hoy() }}">
                    </div>
                </td>
                <td style="width: 120px">
                    <div class="input-group div_group_filtro" style="margin-top: 5px;">
                        <div class="input-group-addon bg-yura_dark">
                            Hasta
                        </div>
                        <input type="date" id="hasta_filtro" class="form-control" value="{{ hoy() }}">
                    </div>
                </td>
                <td>
                    <div class="input-group div_group_filtro" style="margin-top: 5px;">
                        <div class="input-group-addon bg-yura_dark">
                            Segmento
                        </div>
                        <select id="segmento_filtro" class="form-control" style="width: 100%">
                            <option value="T">Todos</option>
                            @foreach ($segmentos as $seg)
                                <option value="{{ $seg }}">
                                    {{ $seg }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </td>
                <td>
                    <div class="input-group div_group_filtro" style="margin-top: 5px;">
                        <div class="input-group-addon bg-yura_dark">
                            Cliente
                        </div>
                        <select id="cliente_filtro" class="form-control" style="width: 100%">
                            <option value="T">Todos</option>
                            @foreach ($clientes as $cli)
                                <option value="{{ $cli->id_cliente }}">
                                    {{ $cli->detalle()->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </td>
                <td>
                    <div class="input-group div_group_filtro" style="margin-top: 5px;">
                        <div class="input-group-addon bg-yura_dark">
                            Tipo
                        </div>
                        <select id="tipo_filtro" class="form-control" style="width: 100%">
                            <option value="T">Todos</option>
                            <option value="OM">OPEN MARKET</option>
                            <option value="SO">STANDING ORDER</option>
                        </select>
                        <div class="input-group-btn">
                            <button class="btn btn-yura_primary" onclick="listar_reporte()">
                                <i class="fa fa-fw fa-search"></i>
                            </button>
                            <button class="btn btn-yura_default" onclick="add_proyecto()">
                                <i class="fa fa-fw fa-plus"></i>
                            </button>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
        <div style="margin-top: 5px" id="div_listar_reporte"></div>
    </section>

    <style>
        div.div_group_filtro span.select2-selection {
            top: 0px;
            border-radius: 0px;
            height: 34px;
        }
    </style>
@endsection

@section('script_final')
    @include('adminlte.gestion.comercializacion.proyectos.script')
@endsection
