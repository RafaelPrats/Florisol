@extends('layouts.adminlte.master')

@section('titulo')
    Reporte de Flor Nacional
@endsection

@section('script_inicio')
    <script></script>
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Reporte de Flor Nacional
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
                            Motivo
                        </span>
                        <select name="filtro_motivo" id="filtro_motivo" class="form-control">
                            <option value="">Todos</option>
                            @foreach ($motivos as $m)
                                <option value="{{ $m->id_motivo_flor_nacional }}">{{ $m->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <span class="input-group-addon bg-yura_dark">
                            Finca
                        </span>
                        <select name="filtro_finca" id="filtro_finca" class="form-control">
                            <option value="">Todas</option>
                            @foreach ($fincas as $f)
                                <option value="{{ $f->id_finca_flor_nacional }}">{{ $f->nombre }}</option>
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
                            Desde
                        </span>
                        <input type="date" class="form-control" id="filtro_desde" name="filtro_desde" required
                            value="{{ $desde }}" max="{{ hoy() }}">
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <span class="input-group-addon bg-yura_dark">
                            Hasta
                        </span>
                        <input type="date" class="form-control" id="filtro_hasta" name="filtro_hasta" required
                            value="{{ $hasta }}" max="{{ hoy() }}">
                        <div class="input-group-btn">
                            <button type="button" class="btn btn-yura_primary" onclick="listar_reporte()">
                                <i class="fa fa-fw fa-search"></i>
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
    </section>
@endsection

@section('script_final')
    {{-- JS de Chart.js --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.3/Chart.min.js"></script>

    @include('adminlte.gestion.postco.reporte_flor_nacional.script')
@endsection
