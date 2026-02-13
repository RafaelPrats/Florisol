@extends('layouts.adminlte.master')

@section('titulo')
    Luz de ciclos
@endsection

@section('script_inicio')
    <script></script>
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Luz
            <small class="text-color_yura">de ciclos</small>
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
                <i class="fa fa-fw fa-leaf"></i> Variedad
            </div>
            <select name="filtro_predeterminado_planta" id="filtro_predeterminado_planta" class="form-control"
                onchange="select_planta($(this).val(), 'filtro_predeterminado_variedad', 'div_cargar_variedades', '<option value=>Seleccione</option>')">
                <option value="">Seleccione</option>
                @foreach ($plantas as $p)
                    <option value="{{ $p->id_planta }}" {{ $p->siglas == 'GYP' ? 'selected' : '' }}>{{ $p->nombre }}
                    </option>
                @endforeach
            </select>
            <div class="input-group-addon bg-yura_dark" id="div_cargar_variedades">
                <i class="fa fa-fw fa-leaf"></i> Tipo
            </div>
            <select name="filtro_predeterminado_variedad" id="filtro_predeterminado_variedad" class="form-control">
                <option value="" selected>Seleccione</option>
            </select>
            <div class="input-group-addon bg-yura_dark">
                P/S
            </div>
            <select name="filtro_poda_siembra" id="filtro_poda_siembra" class="form-control">
                <option value="T">Podas y Siembras</option>
                <option value="P">Poda</option>
                <option value="S">Siembra</option>
            </select>
            <div class="input-group-addon bg-yura_dark">
                Fecha
            </div>
            <input type="date" value="{{ hoy() }}" class="form-control text-center" id="filtro_fecha"
                max="{{ hoy() }}">
            <div class="input-group-btn">
                <button type="button" class="btn btn-yura_primary" onclick="listar_ciclo_luz()">
                    <i class="fa fa-fw fa-search"></i>
                </button>
            </div>
        </div>
        <div id="div_listado_ciclos"></div>
    </section>
@endsection

@section('script_final')
    @include('adminlte.gestion.campo.ciclo_luz.script')
@endsection
