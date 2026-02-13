@extends('layouts.adminlte.master')

@section('titulo')
    Control Diario
@endsection

@section('contenido')
    <script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tensorflow-models/blazeface"></script>

    <section class="content-header">
        
        <h1>
            Control Diario
            <small>módulo de rrhh</small>
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
                    <i class="fa fa-fw fa-refresh"></i> {{ $submenu->nombre }}
                </a>
            </li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-3 col-xs-6">
                <div class="form-group input-group">
                    <span class="input-group-addon span-input-group-yura-fixed bg-yura_dark">Área</span>
                    <select name="id_search_area" id="id_search_area" class="form-control input-yura_default"
                            onchange="obtener_atividad_mano_obra('actividad')">
                        <option value="">Seleccione</option>
                        @foreach ($area as $x => $a)
                            <option {{$x==0 ? 'selected': ''}} value="{{ $a->id_area }}">{{ $a->nombre }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-3 col-xs-6">
                <div class="form-group input-group">
                    <span class="input-group-addon span-input-group-yura-fixed bg-yura_dark">Actividad</span>
                    <select name="id_search_actividad" id="id_search_actividad" class="form-control input-yura_default"
                            onchange="obtener_atividad_mano_obra('mano_obra')">
                            <option value="">Todas</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3 col-xs-6">
                <div class="form-group input-group">
                    <span class="input-group-addon span-input-group-yura-fixed bg-yura_dark">Labor</span>
                    <select name="id_search_mano_obra" id="id_search_mano_obra" class="form-control input-yura_default">
                        <option value="">Todas</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3 col-xs-6">
                <div class="form-group input-group">
                    <span class="input-group-addon span-input-group-yura-fixed bg-yura_dark">Fecha</span>
                    <input type="date" class="form-control input-yura_default" value="{{now()->format('Y-m-d')}}"
                            id="fecha_search_control_diario" name="fecha_search_control_diario" >
                    <div class="input-group-btn">
                        <button type="button" class="btn btn-yura_primary" onclick="obtener_control_diario()">
                            <i class="fa fa-fw fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group input-group" style="padding: 0px">

            <input type="text" class="form-control" placeholder="Búsqueda de personal" id="busqueda_personal" name="busqueda_personal">
            <span class="input-group-btn">
                <button class="btn btn-yura_primary" onclick="add_control_personal()"
                        onmouseover="$('#title_btn_add').html('Añadir personal')"
                        onmouseleave="$('#title_btn_add').html('')"
                >
                    <i class="fa fa-fw fa-plus" style="color: #0c0c0c"></i> <em id="title_btn_add"></em>
                </button>
            </span>
        </div>
        <div id="div_control_diario" style="margin-top:5px"></div>
    </section>
@endsection
@section('script_final')
    @include('adminlte.gestion.rrhh.control_diario.script')
@endsection

