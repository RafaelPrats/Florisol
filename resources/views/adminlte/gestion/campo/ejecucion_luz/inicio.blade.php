@extends('layouts.adminlte.master')

@section('titulo')
    Ejecución de Luz
@endsection

@section('script_inicio')
    <script></script>
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Ejecución
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
            <select class="form-control" id="filtro_semana">
                @foreach ($semanas as $s)
                    <option value="{{ $s->codigo }}">{{ $s->codigo }}</option>
                @endforeach
            </select>
            <div class="input-group-btn">
                <button type="button" class="btn btn-yura_primary" onclick="listar_ejecucion_luz()">
                    <i class="fa fa-fw fa-search"></i>
                </button>
            </div>
        </div>
        <div id="div_listado_ciclos" style="margin-top: 10px"></div>
    </section>
@endsection

@section('script_final')
    @include('adminlte.gestion.campo.ejecucion_luz.script')
@endsection
