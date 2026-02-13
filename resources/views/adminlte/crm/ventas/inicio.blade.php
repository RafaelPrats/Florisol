@extends('layouts.adminlte.master')

@section('titulo')
    Dashboard
@endsection

@section('script_inicio')
    <script></script>
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Dashboard
            <small class="text-color_yura">General</small>
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
                    <i class="fa fa-fw fa-refresh"></i> {{ $submenu->nombre }}
                </a>
            </li>
        </ol>
    </section>

    <section class="content">
        <div id="div_indicadores">
            @include('adminlte.crm.ventas.partials.indicadores')
        </div>

        <table style="width: 100%">
            <tr>
                <td style="width: 33%">
                    <div class="input-group">
                        <div class="input-group-btn bg-yura_dark">
                            <button type="button" class="btn dropdown-toggle bg-yura_dark" data-toggle="dropdown"
                                aria-haspopup="true" aria-expanded="false">
                                Mostrar <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu sombra_pequeña">
                                <li>
                                    <a href="javascript:void(0)" class="li_tipo_grafica bg-aqua-active"
                                        onclick="$('#tipo_grafica').val('line'); $('.li_tipo_grafica').removeClass('bg-aqua-active'); $(this).addClass('bg-aqua-active'); listar_graficas();">
                                        Lineal
                                    </a>
                                    <a href="javascript:void(0)" class="li_tipo_grafica"
                                        onclick="$('#tipo_grafica').val('area'); $('.li_tipo_grafica').removeClass('bg-aqua-active'); $(this).addClass('bg-aqua-active'); listar_graficas();">
                                        Area
                                    </a>
                                    <a href="javascript:void(0)" class="li_tipo_grafica"
                                        onclick="$('#tipo_grafica').val('bar'); $('.li_tipo_grafica').removeClass('bg-aqua-active'); $(this).addClass('bg-aqua-active'); listar_graficas();">
                                        Barra
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <input type="hidden" id="tipo_grafica" value="line">
                        <select name="rango" id="rango" class="form-control">
                            <option value="D">Diario</option>
                            <option value="S">Semanal</option>
                            <option value="M">Mensual</option>
                        </select>
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <span class="input-group-addon bg-yura_dark">
                            Desde
                        </span>
                        <input type="date" id="filtro_desde" style="width: 100%" class="form-control text-center"
                            value="{{ opDiasFecha('-', 30, hoy()) }}" required>
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <span class="input-group-addon bg-yura_dark ">
                            Hasta
                        </span>
                        <input type="date" id="filtro_hasta" style="width: 100%"
                            class="form-control text-center input-yura_default" value="{{ opDiasFecha('-', 1, hoy()) }}"
                            required>
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="input-group div_input_group">
                        <span class="input-group-addon bg-yura_dark">
                            Cliente
                        </span>
                        <select name="cliente" id="cliente" class="form-control" style="width: 100%">
                            <option value="T">Todos los Clientes</option>
                            @foreach ($clientes as $c)
                                <option value="{{ $c->id_cliente }}">{{ $c->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <span class="input-group-addon bg-yura_dark">
                            Flor
                        </span>
                        <select name="planta" id="planta" class="form-control" style="width: 100%">
                            <option value="T">Todas las Flores</option>
                            @foreach ($plantas as $p)
                                <option value="{{ $p->id_planta }}">{{ $p->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <div class="input-group-btn bg-yura_dark">
                            <button type="button" class="btn btn-default dropdown-toggle bg-yura_dark"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                Años
                                <span class="caret"></span></button>
                            <ul class="dropdown-menu">
                                @foreach ($annos as $a)
                                    <li>
                                        <a href="javascript:void(0)" onclick="select_anno('{{ $a->anno }}')"
                                            class="li_anno" id="li_anno_{{ $a->anno }}">
                                            {{ $a->anno }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <input type="text" class="form-control text-center input-yura_default" placeholder="Años"
                            id="annos" name="annos" readonly>
                        <div class="input-group-btn">
                            <button type="button" id="btn_filtrar" class="btn btn-yura_dark" onclick="listar_graficas()"
                                title="Buscar">
                                <i class="fa fa-fw fa-search"></i>
                            </button>
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <div class="row" style="margin-top: 10px">
            <div class="col-md-8 border-radius_16" id="div_graficas"></div>
            <div class="col-md-4 bg-yura_dark border-radius_16" id="div_master_ranking">
                <legend class="text-center" style="margin-bottom: 5px">
                    <div class="pull-left">
                        <button type="button" class="btn btn-xs btn-yura_primary dropdown-toggle" data-toggle="dropdown"
                            aria-haspopup="true" aria-expanded="false">
                            Criterio <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu sombra_pequeña" style="top: 28px;">
                            <li>
                                <a href="javascript:void(0)" class="li_criterio_ranking bg-aqua-active"
                                    onclick="$('#criterio_ranking').val('A'); $('.li_criterio_ranking').removeClass('bg-aqua-active'); $(this).addClass('bg-aqua-active'); listar_ranking();">
                                    Armados
                                </a>
                                <a href="javascript:void(0)" class="li_criterio_ranking"
                                    onclick="$('#criterio_ranking').val('C'); $('.li_criterio_ranking').removeClass('bg-aqua-active'); $(this).addClass('bg-aqua-active'); listar_ranking();">
                                    Comprados
                                </a>
                                <a href="javascript:void(0)" class="li_criterio_ranking"
                                    onclick="$('#criterio_ranking').val('D'); $('.li_criterio_ranking').removeClass('bg-aqua-active'); $(this).addClass('bg-aqua-active'); listar_ranking();">
                                    Desechados
                                </a>
                                <a href="javascript:void(0)" class="li_criterio_ranking"
                                    onclick="$('#criterio_ranking').val('R'); $('.li_criterio_ranking').removeClass('bg-aqua-active'); $(this).addClass('bg-aqua-active'); listar_ranking();">
                                    Recibidos
                                </a>
                            </li>
                        </ul>
                    </div>
                    <strong style="color: white">Ranking <sup>-4 semanas</sup></strong>
                    <div class="pull-right">
                        <button type="button" class="btn btn-xs btn-yura_primary dropdown-toggle" data-toggle="dropdown"
                            aria-haspopup="true" aria-expanded="false">
                            Tipo <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu sombra_pequeña" style="top: 28px; right: 13px">
                            <li>
                                <a href="javascript:void(0)" class="li_tipo_ranking bg-aqua-active"
                                    onclick="$('#tipo_ranking').val('F'); $('.li_tipo_ranking').removeClass('bg-aqua-active'); $(this).addClass('bg-aqua-active'); listar_ranking();">
                                    Flores
                                </a>
                                <a href="javascript:void(0)" class="li_tipo_ranking"
                                    onclick="$('#tipo_ranking').val('C'); $('.li_tipo_ranking').removeClass('bg-aqua-active'); $(this).addClass('bg-aqua-active'); listar_ranking();">
                                    Clientes
                                </a>
                            </li>
                        </ul>
                    </div>
                </legend>
                <input type="hidden" id="tipo_ranking" value="F">
                <input type="hidden" id="criterio_ranking" value="A">
                <div id="div_ranking"></div>
            </div>
        </div>
    </section>

    <style>
        div.div_input_group span.select2-selection {
            top: 0px;
            border-radius: 0px;
            height: 34px;
        }
    </style>
@endsection

@section('script_final')
    {{-- JS de Chart.js --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.3/Chart.min.js"></script>

    @include('adminlte.crm.ventas.script')
@endsection
