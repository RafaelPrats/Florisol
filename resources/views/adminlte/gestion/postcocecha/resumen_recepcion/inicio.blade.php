@extends('layouts.adminlte.master')

@section('titulo')
    Resumen Recepcion
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Resumen Recepcion
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
                    <div class="input-group" id="div_filtro_variedad">
                        <div class="input-group-addon bg-yura_dark">
                            Variedad
                        </div>
                        <select name="variedad_filtro" id="variedad_filtro" class="form-control">
                            <option value="">Todas</option>
                            @foreach ($variedades as $p)
                                <option value="{{ $p->id_variedad }}">{{ $p->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <div class="input-group-addon bg-yura_dark">
                            Desde
                        </div>
                        <input type="date" name="desde_filtro" id="desde_filtro" class="form-control"
                            value="{{ $desde }}">
                        <div class="input-group-addon bg-yura_dark">
                            Hasta
                        </div>
                        <input type="date" name="hasta_filtro" id="hasta_filtro" class="form-control"
                            value="{{ $hasta }}">
                        <div class="input-group-btn">
                            <button type="button" class="btn btn-yura_primary" onclick="listar_reporte()">
                                <i class="fa fa-fw fa-search"></i>
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
            top: 21px !important;
            z-index: 8 !important;
        }

        .col_fija_left_0 {
            position: sticky !important;
            left: 0 !important;
            z-index: 8 !important;
        }

        .tr_fija_bottom_0 {
            position: sticky;
            bottom: 0;
            z-index: 9;
        }
    </style>
@endsection

@section('script_final')
    @include('adminlte.gestion.postcocecha.resumen_recepcion.script')
@endsection
