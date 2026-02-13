@extends('layouts.adminlte.master')

@section('titulo')
    Propuestas
@endsection

@section('script_inicio')
    <script></script>
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Propuestas
            <small class="text-color_yura">modulo postcosecha</small>
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
        <table style="width: 100%;">
            <tr>
                <td>
                    <div class="input-group">
                        <span class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                            Color
                        </span>
                        <select name="filtro_color" id="filtro_color" class="form-control">
                            <option value="">Todos</option>
                            @foreach ($colores as $m)
                                <option value="{{ $m }}">{{ $m }}</option>
                            @endforeach
                        </select>
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <span class="input-group-addon bg-yura_dark">
                            Season
                        </span>
                        <select name="filtro_season" id="filtro_season" class="form-control">
                            <option value="">Todas</option>
                            @foreach ($seasons as $f)
                                <option value="{{ $f }}">{{ $f }}</option>
                            @endforeach
                        </select>
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <div class="input-group-addon bg-yura_dark">
                            <i class="fa fa-fw fa-leaf"></i>
                        </div>
                        <select name="filtro_planta" id="filtro_planta" class="form-control" style="width: 100%"
                            onchange="select_planta_global($(this).val(), 'filtro_variedad', 'div_filtro_variedad', '<option value=>Todas las Varidades</option>')">
                            <option value="">Todas</option>
                            @foreach ($plantas as $p)
                                <option value="{{ $p->id_planta }}">{{ $p->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </td>
                <td id="div_filtro_variedad">
                    <div class="input-group">
                        <div class="input-group-addon bg-yura_dark">
                            Variedad
                        </div>
                        <select name="filtro_variedad" id="filtro_variedad" class="form-control" style="width: 100%">
                            <option value="">Todas</option>
                        </select>
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <span class="input-group-addon bg-yura_dark">
                            <i class="fa fa-fw fa-search"></i>
                        </span>
                        <input type="text" class="form-control" id="filtro_busqueda" name="filtro_busqueda"
                            placeholder="Busqueda">
                        <div class="input-group-btn">
                            <button type="button" class="btn btn-yura_primary" onclick="listar_reporte()">
                                <i class="fa fa-fw fa-search"></i>
                            </button>
                            <button type="button" class="btn btn-yura_dark" onclick="add_propuesta()">
                                <i class="fa fa-fw fa-plus"></i>
                            </button>
                            {{-- <button type="button" class="btn btn-yura_default" onclick="exportar_reporte()"
                                title="Exportar Excel">
                                <i class="fa fa-fw fa-file-excel-o"></i>
                            </button> --}}
                        </div>
                    </div>
                </td>
            </tr>
        </table>
        <div id="div_listado" style="margin-top: 5px"></div>

        <style>
            .imagen_listado:hover {
                transform: scale(1.05);
            }
        </style>
    </section>
@endsection

@section('script_final')
    {{-- JS de Chart.js --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.3/Chart.min.js"></script>

    @include('adminlte.gestion.postco.propuestas.script')
@endsection
