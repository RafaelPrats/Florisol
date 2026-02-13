@extends('layouts.adminlte.master')

@section('titulo')
    Ejecución de Labores
@endsection

@section('script_inicio')
    <script></script>
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Ejecución
            <small class="text-color_yura">de labores</small>
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
                Tipo de Labores
            </div>
            <select name="filtro_tipo_labor" id="filtro_tipo_labor" class="form-control"
                onchange="seleccionar_tipo_labor()">
                <option value="">Seleccione...</option>
                <option value="S">Sanidad</option>
                <option value="C">Cultural</option>
            </select>
            <div class="input-group-addon bg-yura_dark">
                Labores
            </div>
            <select name="filtro_labor" id="filtro_labor" class="form-control">
            </select>
            <div class="input-group-addon bg-yura_dark">
                Sector
            </div>
            <select name="filtro_sector" id="filtro_sector" class="form-control">
                @foreach ($sectores as $s)
                    <option value="{{ $s->id_sector }}">{{ $s->nombre }}</option>
                @endforeach
            </select>
            <div class="input-group-addon bg-yura_dark">
                Semana
            </div>
            <input type="number" name="filtro_semana" id="filtro_semana" class="form-control text-center"
                value="{{ $semana_actual->codigo }}" max="{{ $semana_actual->codigo }}">
            <div class="input-group-btn">
                <button type="button" class="btn btn-yura_primary" onclick="listar_reporte()">
                    <i class="fa fa-fw fa-search"></i>
                </button>
            </div>
        </div>

        <div id="div_listado_ciclos" style="margin-top: 10px"></div>
    </section>
@endsection

@section('script_final')
    @include('adminlte.gestion.campo.ejecucion_labores.script')
@endsection
