@extends('layouts.adminlte.master')

@section('titulo')
    Importar Pedidos
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Importar Pedidos
            <small class="text-color_yura">módulo de comercializacion</small>
        </h1>

        <ol class="breadcrumb">
            <li>
                <a href="javascript:void(0)" onclick="cargar_url('')" class="text-color_yura">
                    <i class="fa fa-home text-color_yura"></i>
                    Inicio
                </a>
            </li>
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
        <div id="div_content_recepciones">
            <table width="100%" style="margin-bottom: 0">
                <tr>
                    <td>
                        <div class="input-group">
                            <div class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                                Fecha
                            </div>
                            <input type="date" id="filtro_fecha" name="filtro_fecha" required
                                class="form-control input-yura_default text-center" onchange="listar_reporte()"
                                style="width: 100% !important;" value="{{ hoy() }}">
                            <div class="input-group-addon bg-yura_dark">
                                Cliente
                            </div>
                            <select id="filtro_cliente" class="form-control input-yura_default" style="width: 100%"
                                onchange="listar_reporte()">
                                <option value="">Todos</option>
                                @foreach ($clientes as $c)
                                    <option value="{{ $c->id_cliente }}">{{ $c->nombre }}</option>
                                @endforeach
                            </select>
                            <div class="input-group-btn">
                                <button class="btn btn-primary btn-yura_primary" onclick="listar_reporte()">
                                    <i class="fa fa-fw fa-search"></i>
                                </button>
                                <button class="btn btn-primary btn-yura_dark" onclick="add_pedido()">
                                    <i class="fa fa-fw fa-plus"></i>
                                </button>
                                <button class="btn btn-primary btn-yura_default" onclick="ver_procesos()"
                                    style="color: black" title="Ver procesos en segundo plano">
                                    <i class="fa fa-fw fa-cogs"></i>
                                </button>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
            <div id="div_listado" style="margin-top: 5px"></div>
        </div>
    </section>

    <style>
        .tr_fija_top_0 {
            position: sticky;
            top: 0;
            z-index: 9;
        }

        .tr_fija_bottom_0 {
            position: sticky;
            bottom: 0;
            z-index: 9;
        }
    </style>
@endsection

@section('script_final')
    {{-- JS de Chart.js --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.3/Chart.min.js"></script>

    @include('adminlte.gestion.comercializacion.importar_pedidos.script')
@endsection
