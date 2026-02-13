@extends('layouts.adminlte.master')

@section('titulo')
    Disponibilidad
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Disponibilidad de Flor
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
                            Hasta
                        </div>
                        <input type="date" name="hasta_filtro" id="hasta_filtro" class="form-control"
                            value="{{ hoy() }}" min="{{ hoy() }}">
                        <div class="input-group-btn">
                            <button type="button" class="btn btn-yura_primary btn_listar_reporte"
                                onclick="listar_reporte()">
                                <i class="fa fa-fw fa-search"></i> Buscar
                            </button>
                            <button type="button" class="btn btn-yura_dark" title="Exportar Reporte"
                                onclick="exportar_listado()">
                                <i class="fa fa-fw fa-file-excel-o"></i>
                            </button>
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
    @include('adminlte.gestion.postco.reporte_disponibilidad.script')
@endsection
