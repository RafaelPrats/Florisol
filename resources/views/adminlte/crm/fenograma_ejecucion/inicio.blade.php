@extends('layouts.adminlte.master')

@section('titulo')
    Reporte - Fenograma de Ejecución
@endsection

@section('script_inicio')
    <script></script>
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Reporte
            <small class="text-color_yura">Fenograma de Ejecución</small>
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
        <table style="width: 100%">
            <tr>
                <td style="padding-right: 5px;">
                    <div class="input-group">
                        <div class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                            Sector
                        </div>
                        <select id="filtro_sector" class="form-control input-yura_default">
                            <option value="T">Todos</option>
                            @foreach ($sectores as $s)
                                <option value="{{ $s->id_sector }}">{{ $s->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </td>
                <td style="padding-right: 5px;">
                    <div class="input-group">
                        <div class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                            <i class="fa fa-fw fa-leaf"></i>
                        </div>
                        <select name="filtro_predeterminado_planta" id="filtro_predeterminado_planta"
                            class="form-control input-yura_default"
                            onchange="select_planta($(this).val(), 'filtro_predeterminado_variedad', 'div_cargar_variedades', '<option value=T selected>Todos</option>')">
                            <option value="">Todas</option>
                            @foreach ($plantas as $p)
                                <option value="{{ $p->id_planta }}">{{ $p->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </td>
                <td style="padding-right: 5px;">
                    <div class="input-group">
                        <div class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                            Tipo
                        </div>
                        <select name="filtro_predeterminado_variedad" id="filtro_predeterminado_variedad"
                            class="form-control input-yura_default" onchange="filtrar_ciclos_fenograma_ejecucion()">
                            <option value="T" selected>Todos</option>
                        </select>
                    </div>
                </td>
                <td style="padding-right: 5px;">
                    <div class="input-group">
                        <div class="input-group-addon bg-yura_dark span-input-group-yura-fixed" id="span_filtro_fecha">
                            Desde
                        </div>
                        <input type="date" class="form-control input-yura_default" id="filtro_predeterminado_fecha"
                            name="filtro_predeterminado_fecha" required value="{{ date('Y-m-d') }}"
                            onchange="filtrar_ciclos_fenograma_ejecucion()">
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <div class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                            P/S
                        </div>
                        <select id="filtro_poda_siembra" class="form-control input-yura_default"
                            onchange="cambiar_filtro_activo($(this).val())">
                            <option value="" selected>Podas y Siembras</option>
                            <option value="P">Podas</option>
                            <option value="S">Siembras</option>
                        </select>
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <div class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                            Estado
                        </div>
                        <select id="filtro_activo" class="form-control input-yura_default"
                            onchange="cambiar_filtro_activo($(this).val())">
                            <option value="">Todos</option>
                            <option value="1" selected>Activos</option>
                            <option value="0">Cerrados</option>
                        </select>
                        <div class="input-group-btn">
                            <button type="button" class="btn btn-yura_dark" onclick="filtrar_ciclos_fenograma_ejecucion()"
                                title="Buscar">
                                <i class="fa fa-fw fa-search"></i>
                            </button>
                            <button type="button" class="btn btn-yura_default hidden" onclick="exportar_reporte()"
                                title="Exportar Excel">
                                <i class="fa fa-fw fa-file-excel-o"></i>
                            </button>
                        </div>
                    </div>
                </td>
            </tr>
            <tr class="hidden" id="tr_filtro_hasta">
                <td colspan="3"></td>
                <td style="padding-top: 5px;" id="td_filtro_hasta">
                    <div class="input-group">
                        <div class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                            Hasta
                        </div>
                        <input type="date" class="form-control input-yura_default" id="filtro_predeterminado_hasta"
                            name="filtro_predeterminado_hasta" required value="{{ hoy() }}">
                    </div>
                </td>
            </tr>
        </table>
        <div class="row" style="margin-top: 10px">
            <div class="col-md-12">
                <div id="div_listado_ciclos"></div>
            </div>
        </div>
    </section>
@endsection

@section('script_final')
    @include('adminlte.crm.fenograma_ejecucion.script')
@endsection
