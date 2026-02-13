@extends('layouts.adminlte.master')

@section('titulo')
    Listado de OT
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Listado de OT
            <small class="text-color_yura">módulo de postcosecha</small>
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
                <td>
                    <div class="input-group div_group_filtro" style="margin-top: 5px;">
                        <div class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
                            <i class="fa fa-fw fa-calendar"></i> Fecha de trabajo
                        </div>
                        <input type="date" id="fecha_filtro" class="form-control" value="{{ hoy() }}">
                    </div>
                </td>
                <td>
                    <div class="input-group div_group_filtro" style="margin-top: 5px;">
                        <div class="input-group-addon bg-yura_dark">
                            <i class="fa fa-fw fa-leaf"></i> Receta
                        </div>
                        <select id="variedad_filtro" class="form-control" onchange="listar_reporte()" style="width: 100%;">
                            <option value="T">Todas las recetas</option>
                            @foreach ($variedades as $var)
                                <option value="{{ $var->id_variedad }}">{{ $var->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </td>
                <td>
                    <div class="input-group div_group_filtro" style="margin-top: 5px;">
                        <div class="input-group-addon bg-yura_dark">
                            <i class="fa fa-fw fa-calendar"></i> Desde
                        </div>
                        <input type="date" id="desde_filtro" class="form-control" value="{{ hoy() }}">
                    </div>
                </td>
                <td>
                    <div class="input-group div_group_filtro" style="margin-top: 5px;">
                        <div class="input-group-addon bg-yura_dark">
                            <i class="fa fa-fw fa-calendar"></i> Hasta
                        </div>
                        <input type="date" id="hasta_filtro" class="form-control"
                            value="{{ opDiasFecha('+', 7, hoy()) }}">
                        <div class="input-group-btn">
                            <button class="btn btn-yura_primary" onclick="listar_reporte()">
                                <i class="fa fa-fw fa-search"></i>
                            </button>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
        <div style="margin-top: 5px; overflow-y: scroll; overflow-x: scroll; max-height: 700px" id="div_listar_reporte">
        </div>
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
    {{-- JS de Chart.js --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.3/Chart.min.js"></script>

    @include('adminlte.gestion.postco.ot_postco.script')
@endsection
