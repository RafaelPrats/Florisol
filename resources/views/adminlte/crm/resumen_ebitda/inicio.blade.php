@extends('layouts.adminlte.master')

@section('titulo')
    Resumen EBITDA
@endsection

@section('script_inicio')
    <script>
    </script>
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            EBITDA
            <small class="text-color_yura">x Variedad</small>
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
        <table style="width: 100%;">
            <tr>
                <td style="padding: 5px">
                    <div class="form-group input-group">
                        <div class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                            <i class="fa fa-fw fa-calendar"></i> Desde
                        </div>
                        <input type="number" class="form-control input-yura_default" id="filtro_predeterminado_desde"
                               name="filtro_predeterminado_desde" required value="{{$desde->codigo}}">
                    </div>
                </td>
                <td style="padding: 5px">
                    <div class="form-group input-group">
                        <div class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                            <i class="fa fa-fw fa-calendar"></i> Hasta
                        </div>
                        <input type="number" class="form-control input-yura_default" id="filtro_predeterminado_hasta"
                               name="filtro_predeterminado_hasta" required value="{{$hasta->codigo}}">
                    </div>
                </td>
                <td style="padding: 5px">
                    <div class="form-group input-group" id="div_cargar_variedades">
                        <div class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
                            <i class="fa fa-fw fa-list-alt"></i> Reporte
                        </div>
                        <select name="filtro_predeterminado_reporte" id="filtro_predeterminado_reporte"
                                class="form-control input-yura_default">
                            <option value="1">Área m2</option>
                            <option value="2">Tallos cosechados</option>
                            <option value="3">Tallos producidos</option>
                            <option value="4">Ventas</option>
                            <option value="5">Precio/tallo</option>
                            <option value="6">Tallos/m2/año (52 sem.)</option>
                            <option value="7" selected>Venta/m2</option>
                        </select>
                        <div class="input-group-btn">
                            <button type="button" class="btn btn-yura_primary" onclick="buscar_resumen_ebitda()">
                                <i class="fa fa-fw fa-search"></i>
                            </button>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
        <div class="row">
            <div class="col-md-12">
                <div id="div_listado_resumen_ebitda"></div>
            </div>
        </div>
    </section>
@endsection

@section('script_final')

    @include('adminlte.crm.resumen_ebitda.script')
@endsection