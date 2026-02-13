@extends('layouts.adminlte.master')

@section('titulo')
    Flor Nacional
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Flor Nacional
            <small class="text-color_yura">módulo de cosecha</small>
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
                <td>
                    <div class="input-group">
                        <div class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                            <i class="fa fa-fw fa-gift"></i> Planta
                        </div>
                        <select name="planta_filtro" id="planta_filtro" class="form-control" style="width: 100%"
                            onchange="select_planta_global($(this).val(), 'variedad_filtro', 'div_filtro_variedad', '<option value=>Todas las Varidades</option>')">
                            <option value="">Todas las Plantas</option>
                            @foreach ($plantas as $p)
                                <option value="{{ $p->id_planta }}">{{ $p->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </td>
                <td id="div_filtro_variedad">
                    <div class="input-group">
                        <div class="input-group-addon bg-yura_dark">
                            <i class="fa fa-fw fa-gift"></i> Variedad
                        </div>
                        <select name="variedad_filtro" id="variedad_filtro" class="form-control" style="width: 100%">
                            <option value="">Todas las Varidades</option>
                        </select>
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <div class="input-group-addon bg-yura_dark">
                            Motivo
                        </div>
                        <select name="motivo_filtro" id="motivo_filtro" class="form-control" style="width: 100%">
                            <option value="">Todos</option>
                            @foreach ($motivos as $m)
                                <option value="{{ $m->id_motivo_flor_nacional }}">
                                    {{ $m->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <div class="input-group-addon bg-yura_dark">
                            Fecha
                        </div>
                        <input type="date" name="fecha_filtro" id="fecha_filtro" class="form-control"
                            value="{{ hoy() }}" max="{{ hoy() }}">
                        <div class="input-group-btn">
                            <button type="button" class="btn btn-yura_primary" onclick="listar_reporte()">
                                <i class="fa fa-fw fa-search"></i> Buscar
                            </button>
                            <button type="button" class="btn btn-yura_default dropdown-toggle" data-toggle="dropdown"
                                aria-expanded="true">
                                <i class="fa fa-fw fa-cogs"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-right sombra_pequeña" style="background-color: #c8c8c8">
                                <li>
                                    <a href="javascript:void(0)" style="color: black" onclick="modal_motivos()">
                                        <i class="fa fa-fw fa-file-o"></i>
                                        Administrar Motivos
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:void(0)" style="color: black" onclick="modal_fincas()">
                                        <i class="fa fa-fw fa-tree"></i>
                                        Administrar Fincas
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <div style="margin-top: 5px;" id="div_listado"></div>
    </section>

    <style>
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

        .select2-selection {
            height: 34px !important;
        }
    </style>
@endsection

@section('script_final')
    @include('adminlte.gestion.postco.ingreso_flor_nacional.script')
@endsection
