@extends('layouts.adminlte.master')

@section('titulo')
    Reporte de Luz
@endsection

@section('script_inicio')
    <script></script>
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Reporte
            <small class="text-color_yura">de luz</small>
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
        <div class="input-group">
            <div class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
                Sector
            </div>
            <select class="form-control" id="filtro_sector">
                @foreach ($sectores as $s)
                    <option value="{{ $s->id_sector }}">{{ $s->nombre }}</option>
                @endforeach
            </select>
            <div class="input-group-addon bg-yura_dark">
                Semana
            </div>
            <input type="number" value="{{ $semana_actual->codigo }}" class="form-control text-center" id="filtro_semana"
                min="{{ $semana_actual->codigo }}">
            <input type="hidden" id="semana_actual" value="{{ $semana_actual->codigo }}">
            <div class="input-group-btn">
                <button type="button" class="btn btn-yura_primary" onclick="listar_reporte_luz()">
                    <i class="fa fa-fw fa-search"></i>
                </button>
                <button type="button" class="btn btn-yura_dark" onclick="exportar_reporte()" title="Exportar Excel">
                    <i class="fa fa-fw fa-file-excel-o"></i>
                </button>
            </div>
        </div>
        <div id="div_listado_ciclos" style="margin-top: 10px"></div>
    </section>
@endsection

@section('script_final')
    @include('adminlte.gestion.campo.reporte_luz.script')
@endsection
