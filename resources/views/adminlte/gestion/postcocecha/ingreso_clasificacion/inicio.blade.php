@extends('layouts.adminlte.master')

@section('titulo')
    Preproduccion
@endsection

@section('script_inicio')
    <script></script>
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Preproduccion
            <small class="text-color_yura">modulo de postcosecha</small>
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
                    <i class="fa fa-fw fa-refresh"></i> {!! $submenu->nombre !!}
                </a>
            </li>
        </ol>
    </section>

    <section class="content">
        <table style="width: 100%">
            <tr>
                <td>
                    <div class="input-group" style="margin-top: 5px;">
                        <div class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
                            <i class="fa fa-fw fa-calendar"></i> Fecha de trabajo
                        </div>
                        <input type="date" id="fecha_filtro" class="form-control text-center"
                            value="{{ hoy() }}" max="{{ hoy() }}">
                    </div>
                </td>
                <td>
                    <div class="input-group" style="margin-top: 5px;">
                        <div class="input-group-addon bg-yura_dark">
                            <i class="fa fa-fw fa-leaf"></i> Receta
                        </div>
                        <select id="variedad_filtro" class="form-control input-yura_default" onchange="listar_reporte()"
                            style="width: 100%">
                            <option value="T">Todas las recetas</option>
                            @foreach ($variedades as $p)
                                <option value="{{ $p->id_variedad }}">{{ $p->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </td>
                <td>
                    <div class="input-group" style="margin-top: 5px;">
                        <div class="input-group-addon bg-yura_dark">
                            <i class="fa fa-fw fa-calendar"></i> Dias
                        </div>
                        <select id="dias_filtro" class="form-control input-yura_default" onchange="listar_reporte()"
                            style="width: 100%">
                            <option value="1" selected>1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                        </select>
                        <div class="input-group-btn">
                            <button class="btn btn-yura_primary" onclick="listar_reporte()">
                                <i class="fa fa-fw fa-search"></i>
                            </button>
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <div style="margin-top: 5px" id="div_listar_reporte"></div>
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

        div.input-group span.select2-selection {
            top: 0px;
            border-radius: 0px;
            height: 34px;
        }
    </style>
@endsection

@section('script_final')
    @include('adminlte.gestion.postcocecha.ingreso_clasificacion.script')
@endsection
