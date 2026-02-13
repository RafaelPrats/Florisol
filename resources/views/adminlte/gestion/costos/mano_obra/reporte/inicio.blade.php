@extends('layouts.adminlte.master')

@section('titulo')
    Reporte - Mano de Obra
@endsection

@section('script_inicio')
    <script>
        function listar_reporte_mano_obra(area = false, actividad = false) {
            datos = {
                area: area,
                actividad: actividad,
                desde: $('#desde').val(),
                hasta: $('#hasta').val(),
                criterio: $('#criterio').val(),
                finca_actual: $('#fincas_propias').val(),
            };
            $('#actividad_seleccionada').val('');
            $('#area_seleccionada').val('');
            if (area != false) {
                $('.btn_actividad').removeClass('bg-blue');
                $('.btn_area').removeClass('bg-blue');
                $('#btn_area_' + area).addClass('bg-blue');
                $('#area_seleccionada').val(area);
            }
            if (actividad != false) {
                $('.btn_area').removeClass('bg-blue');
                $('.btn_actividad').removeClass('bg-blue');
                $('#btn_actividad_' + actividad).addClass('bg-blue');
                $('#actividad_seleccionada').val(actividad);
            }
            get_jquery('{{url('reporte_mano_obra/listar_reporte')}}', datos, function (retorno) {
                $('#div_content_fixed').html(retorno);
            }, 'div_content_fixed');
        }
    </script>
@endsection

@section('css_inicio')
    <style>

    </style>
@endsection

@section('contenido')
    <section class="content-header">
        <h1>
            Mano de Obra
            <small class="text-color_yura">Reporte</small>
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
        <div class="input-group">
            <div class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
                Desde
            </div>
            <input type="number" id="desde" onkeypress="return isNumber(event)" class="form-control text-center"
                   value="{{$semana_desde->codigo}}">
            <div class="input-group-addon bg-yura_dark">
                Hasta
            </div>
            <input type="number" id="hasta" onkeypress="return isNumber(event)" class="form-control text-center"
                   value="{{$semana_actual->codigo}}">
            <div class="input-group-addon bg-yura_dark">
                Criterio
            </div>
            <select name="criterio" id="criterio" class="form-control">
                <option value="V">Valores</option>
                <option value="C">Cantidades</option>
            </select>

            <div class="input-group-btn">
                <button type="button" class="btn btn-yura_dark" title="Buscar" onclick="listar_reporte_mano_obra()">
                    <i class="fa fa-fw fa-search"></i>
                </button>
                <button type="button" class="btn btn-yura_primary" title="Exportar" onclick="exportar_reporte_mano_obra()">
                    <i class="fa fa-fw fa-file-excel-o"></i>
                </button>
            </div>
        </div>
        <div class="row" style="margin-top: 10px">
            <div class="col-md-3 div_content_fixed">
                @include('adminlte.gestion.costos.mano_obra.reporte.partials.areas_actividades')
            </div>
            <div class="col-md-9 div_content_fixed">
                <div id="div_content_fixed" style="overflow-x: scroll; overflow-y: scroll; max-height: 450px">
                </div>
            </div>
        </div>
    </section>
@endsection

@section('script_final')
    <script>
        $('#vista_actual').val('reporte_mano_obra');

        listar_reporte_mano_obra();

        function exportar_reporte_mano_obra() {
            $.LoadingOverlay('show');
            window.open('{{url('reporte_mano_obra/exportar_reporte_mano_obra')}}?area=' + $('#area_seleccionada').val('') +
                '&actividad=' + $('#actividad_seleccionada').val('') +
                '&criterio=' + $('#criterio').val() +
                '&finca_actual=' + $('#fincas_propias').val() +
                '&desde=' + $('#desde').val() +
                '&hasta=' + $('#hasta').val()
                , '_blank');
            $.LoadingOverlay('hide');
        }
    </script>
@endsection