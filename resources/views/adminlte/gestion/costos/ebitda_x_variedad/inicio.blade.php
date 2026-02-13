@extends('layouts.adminlte.master')

@section('titulo')
    Ebitda x Variedad
@endsection

@section('script_inicio')
    <script></script>
@endsection

@section('css_inicio')
@endsection

@section('contenido')
    <section class="content-header">
        <h1>
            Ebitda x Variedad
            <small class="text-color_yura">Reporte</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="javascript:void(0)" onclick="cargar_url('')" class="text-color_yura"><i class="fa fa-home"></i>
                    Inicio</a></li>
            <li class="text-color_yura">
                {{ $submenu->menu->grupo_menu->nombre }}
            </li>
            <li class="text-color_yura">
                {{ $submenu->menu->nombre }}
            </li>
            <li class="active">
                <a href="javascript:void(0)" onclick="cargar_url('{{ $submenu->url }}')" class="text-color_yura">
                    <i class="fa fa-fw fa-refresh"></i> {!! $submenu->nombre !!}
                </a>
            </li>
        </ol>
    </section>

    <section class="content">
        <div class="input-group">
            <div class="input-group-addon span-input-group-yura-fixed bg-yura_dark" style="background-color: #e9ecef">
                Semana
            </div>
            <input type="number" id="filtro_semana" onkeypress="return isNumber(event)"
                class="form-control text-center input-yura_default" value="{{ $semana->codigo }}">
            <div class="input-group-addon bg-yura_dark" style="background-color: #e9ecef">
                Tipo de Planta
            </div>
            <select id="filtro_tipo_planta" class="form-control input-yura_default" style="width: 100%"
                onchange="seleecionar_tipo_planta($(this).val())">
                <option value="T">Todos</option>
                <option value="P">Perennes</option>
                <option value="N">No Perennes</option>
            </select>
            <div class="input-group-addon bg-yura_dark" style="background-color: #e9ecef">
                Variedad
            </div>
            <select id="filtro_planta" class="form-control input-yura_default" style="width: 100%">
                <option value="T">Todas</option>
                @foreach ($plantas as $p)
                    <option value="{{ $p->id_planta }}" class="option_planta_{{ $p->tipo }} option_planta">
                        {{ $p->nombre }}
                    </option>
                @endforeach
            </select>
            <div class="input-group-btn">
                <button type="button" class="btn btn-yura_primary" onclick="listado_ebitda_x_variedad()">
                    <i class="fa fa-fw fa-search"></i>
                </button>
            </div>
        </div>

        <div id="div_reporte"></div>
    </section>
@endsection

@section('script_final')
    @include('adminlte.gestion.costos.ebitda_x_variedad.script')
@endsection
