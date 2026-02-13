@extends('layouts.adminlte.master')

@section('titulo')
    Fenograma de Perennes
@endsection

@section('script_inicio')
    <script>
    </script>
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Fenogramas
        </h1>
        <ol class="breadcrumb">
            <li><a href="javascript:void(0)" class="text-color_yura" onclick="cargar_url('')"><i class="fa fa-home"></i> Inicio</a></li>
            <li class="text-color_yura">
                {{$submenu->menu->grupo_menu->nombre}}
            </li>
            <li class="text-color_yura">
                {{$submenu->menu->nombre}}
            </li>

            <li class="active">
                <a href="javascript:void(0)" class="text-color_yura" onclick="cargar_url('{{$submenu->url}}')">
                    <i class="fa fa-fw fa-refresh"></i> {!! $submenu->nombre !!}
                </a>
            </li>
        </ol>
    </section>

    <section class="content">
        <table style="width: 100%">
            <tr>
                <td>
                    <div class="input-group">
                        <div class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
                            <i class="fa fa-fw fa-calendar"></i> Semana
                        </div>
                        <input type="number" name="filtro_predeterminado_semana" id="filtro_predeterminado_semana"
                               class="form-control input-yura_default text-center" value="{{$semana_pasada->codigo}}">
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <div class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
                            <i class="fa fa-fw fa-tree"></i> Plantas
                        </div>
                        <select name="tipo_planta" id="tipo_planta" class="form-control input-yura_default"
                                onchange="$('.td_filtro_planta').toggleClass('hidden'); $('#td_filtro_planta_'+$(this).val());
                                $('#filtro_predeterminado_variedad').html('<option value=T selected>Seleccione</option>')">
                            <option value="P">Perennes</option>
                            <option value="N">No Perennes</option>
                        </select>
                    </div>
                </td>
                <td class="td_filtro_planta hidden" id="td_filtro_planta_N">
                    <div class="input-group">
                        <div class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
                            <i class="fa fa-fw fa-leaf"></i> Variedad
                        </div>
                        <select name="filtro_predeterminado_planta_N" id="filtro_predeterminado_planta_N" class="form-control input-yura_default"
                                onchange="select_planta($(this).val(), 'filtro_predeterminado_variedad', 'filtro_predeterminado_variedad',
                                '<option value=T selected>Todos los tipos</option>')">
                            <option value="T">Todas las variedades</option>
                            @foreach($plantas as $p)
                                @if($p->tipo == 'N')
                                    <option value="{{$p->id_planta}}">{{$p->nombre}}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </td>
                <td class="td_filtro_planta" id="td_filtro_planta_P">
                    <div class="input-group">
                        <div class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
                            <i class="fa fa-fw fa-leaf"></i> Variedad
                        </div>
                        <select name="filtro_predeterminado_planta_P" id="filtro_predeterminado_planta_P" class="form-control input-yura_default"
                                onchange="select_planta($(this).val(), 'filtro_predeterminado_variedad', 'filtro_predeterminado_variedad',
                            '<option value=T selected>Todos los tipos</option>')">
                            <option value="T">Todas las variedades</option>
                            @foreach($plantas as $p)
                                @if($p->tipo == 'P')
                                    <option value="{{$p->id_planta}}">{{$p->nombre}}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <div class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
                            <i class="fa fa-fw fa-leaf"></i> Tipo
                        </div>
                        <select name="filtro_predeterminado_variedad" id="filtro_predeterminado_variedad"
                                class="form-control input-yura_default">
                            <option value="T" selected>Seleccione una variedad</option>
                        </select>
                        <div class="input-group-btn">
                            <button type="button" class="btn btn-yura_primary" onclick="listar_fenograma_perennes()">
                                <i class="fa fa-fw fa-search"></i>
                            </button>
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <div id="div_listado_fenograma" style="margin-top: 5px"></div>
    </section>
@endsection

@section('script_final')
    @include('adminlte.gestion.proyecciones.fenograma_perennes.script')
@endsection
