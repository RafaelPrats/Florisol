@extends('layouts.adminlte.master')

@section('titulo')
    Salidas de Recepcion
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Salidas de Recepcion
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
        <div style="overflow-x: scroll">
            <table style="width: 100%">
                <tr>
                    <td style="width: 20%">
                        <div class="input-group">
                            <div class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                                Planta
                            </div>
                            <select name="planta_filtro" id="planta_filtro" class="form-control" style="width: 100%"
                                onchange="select_planta_global($(this).val(), 'variedad_filtro', 'variedad_filtro', '<option value=T selected>Todas</option>')">
                                <option value="T">Todas</option>
                                @foreach ($plantas as $p)
                                    <option value="{{ $p->id_planta }}">{{ $p->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </td>
                    <td style="width: 20%">
                        <div class="input-group">
                            <div class="input-group-addon bg-yura_dark">
                                Variedad
                            </div>
                            <select name="variedad_filtro" id="variedad_filtro" class="form-control" style="width: 100%">
                                <option value="T">Todas</option>
                            </select>
                        </div>
                    </td>
                    <td style="width: 20%">
                        <div class="input-group">
                            <div class="input-group-addon bg-yura_dark">
                                Desde
                            </div>
                            <input type="date" name="desde_filtro" id="desde_filtro" class="form-control"
                                value="{{ hoy() }}" max="{{ hoy() }}">
                        </div>
                    </td>
                    <td style="width: 20%">
                        <div class="input-group">
                            <div class="input-group-addon bg-yura_dark">
                                Hasta
                            </div>
                            <input type="date" name="hasta_filtro" id="hasta_filtro" class="form-control"
                                value="{{ hoy() }}" max="{{ hoy() }}">
                        </div>
                    </td>
                    <td style="width: 20%">
                        <div class="input-group">
                            <div class="input-group-addon bg-yura_dark">
                                Criterio
                            </div>
                            <select name="criterio_filtro" id="criterio_filtro" class="form-control" style="width: 100%">
                                <option value="S">Salidas</option>
                                <option value="B">Basura</option>
                                <option value="C">Combos</option>
                            </select>
                            <div class="input-group-btn">
                                <button type="button" class="btn btn-yura_primary" onclick="listar_reporte()">
                                    <i class="fa fa-fw fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
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
            top: 24px !important;
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
    @include('adminlte.gestion.postcocecha.salidas_recepcion.script')
@endsection
