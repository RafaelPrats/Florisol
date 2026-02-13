@extends('layouts.adminlte.master')

@section('titulo')
    Tabla de Operaciones
@endsection

@section('script_inicio')
    <script></script>
@endsection

@section('css_inicio')
@endsection

@section('contenido')
    <section class="content-header">
        <h1>
            Tabla de Operaciones
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
                Desde
            </div>
            <input type="number" id="desde" onkeypress="return isNumber(event)"
                class="form-control text-center input-yura_default" value="{{ $desde }}">
            <div class="input-group-addon bg-yura_dark" style="background-color: #e9ecef">
                Hasta
            </div>
            <input type="number" id="hasta" onkeypress="return isNumber(event)"
                class="form-control text-center input-yura_default" value="{{ $hasta }}">
            <div class="input-group-addon bg-yura_dark" style="background-color: #e9ecef">
                Variedad
            </div>
            <select id="filtro_planta" class="form-control input-yura_default" style="width: 100%">
                <option value="T">Todas</option>
                @foreach ($plantas as $p)
                    <option value="{{ $p->id_planta }}">{{ $p->nombre }}</option>
                @endforeach
            </select>
            <div class="input-group-btn">
                <button class="btn bg-yura_dark" type="button" onclick="$('#div_menu_reportes').toggleClass('hidden')">
                    Columnas
                    <span class="caret"></span>
                </button>
                <button type="button" class="btn btn-yura_primary" title="OK" onclick="listado_operaciones()">
                    <i class="fa fa-fw fa-search"></i>
                </button>
                <!--<button type="button" class="btn btn-yura_default" title="Exportar"
                        onclick="exportar_listado_operaciones()">
                        <i class="fa fa-fw fa-file-excel-o"></i>
                    </button>-->
            </div>
        </div>
        <div id="div_menu_reportes" class="hidden">
            <ul class="list-group">
                <li class="list-group-item-yura mouse-hand bg-aqua reportes" id="r-area"
                    onclick="select_columna($(this))">Área m<sup>2</sup></li>
                <li class="list-group-item-yura mouse-hand reportes" id="r-area_promedio" onclick="select_columna($(this))">
                    Área Promedio
                </li>
                <li class="mouse-hand bg-yura_dark text-right li_all_tallos_cosechados" style="height: 20px; padding: 2px"
                    onclick="$('.all_tallos_cosechados').addClass('bg-aqua'); $('.li_all_tallos_cosechados').toggleClass('hidden');
                    $('.col-tallos_cosechados').removeClass('hidden')">
                    <i class="fa fa-fw fa-plus"></i> (Tallos Cosechados) <i class="fa fa-fw fa-caret-down"></i>
                </li>
                <li class="mouse-hand bg-yura_dark text-right li_all_tallos_cosechados hidden"
                    style="height: 20px; padding: 2px"
                    onclick="$('.all_tallos_cosechados').removeClass('bg-aqua'); $('.li_all_tallos_cosechados').toggleClass('hidden');
                    $('.col-tallos_cosechados').addClass('hidden')">
                    <i class="fa fa-fw fa-times"></i> (Tallos Cosechados) <i class="fa fa-fw fa-caret-down"></i>
                </li>
                <li class="list-group-item-yura mouse-hand bg-aqua reportes all_tallos_cosechados" id="r-tallos_cosechados"
                    onclick="select_columna($(this))">Tallos Cosechados
                </li>
                <li class="list-group-item-yura mouse-hand reportes all_tallos_cosechados" id="r-tallos_cosechados_acum"
                    onclick="select_columna($(this))">Tallos Cosechados Acumulado
                </li>
                <li class="list-group-item-yura mouse-hand reportes all_tallos_cosechados" id="r-tallos_m2"
                    onclick="select_columna($(this))">Tallos x m2
                </li>
                <li class="list-group-item-yura mouse-hand reportes all_tallos_cosechados" id="r-tallos_m2_52_sem"
                    onclick="select_columna($(this))">Tallos x m2 (52 sem.)
                </li>
                <li class="mouse-hand bg-yura_dark text-right li_all_tallos_producidos" style="height: 20px; padding: 2px"
                    onclick="$('.all_tallos_producidos').addClass('bg-aqua'); $('.li_all_tallos_producidos').toggleClass('hidden');
                    $('.col-tallos_producidos').removeClass('hidden')">
                    <i class="fa fa-fw fa-plus"></i> (Tallos Producidos) <i class="fa fa-fw fa-caret-down"></i>
                </li>
                <li class="mouse-hand bg-yura_dark text-right li_all_tallos_producidos hidden"
                    style="height: 20px; padding: 2px"
                    onclick="$('.all_tallos_producidos').removeClass('bg-aqua'); $('.li_all_tallos_producidos').toggleClass('hidden');
                    $('.col-tallos_producidos').addClass('hidden')">
                    <i class="fa fa-fw fa-times"></i> (Tallos Producidos) <i class="fa fa-fw fa-caret-down"></i>
                </li>
                <li class="list-group-item-yura mouse-hand bg-aqua reportes all_tallos_producidos" id="r-tallos_producidos"
                    onclick="select_columna($(this))">Tallos Producidos
                </li>
                <li class="list-group-item-yura mouse-hand reportes all_tallos_producidos" id="r-tallos_producidos_acum"
                    onclick="select_columna($(this))">Tallos Producidos Acumulado
                </li>
                <li class="list-group-item-yura mouse-hand reportes all_tallos_producidos" id="r-tallos_exportables"
                    onclick="select_columna($(this))">Tallos Exportables
                </li>
                <li class="list-group-item-yura mouse-hand reportes all_tallos_producidos" id="r-tallos_exportables_acum"
                    onclick="select_columna($(this))">Tallos Exportables Acumulado
                </li>
                <li class="list-group-item-yura mouse-hand reportes all_tallos_producidos" id="r-porcent_exportables"
                    onclick="select_columna($(this))">% Exportables
                </li>
                <li class="list-group-item-yura mouse-hand reportes all_tallos_producidos" id="r-tallos_bqt"
                    onclick="select_columna($(this))">Tallos Bqt
                </li>
                <li class="list-group-item-yura mouse-hand reportes all_tallos_producidos" id="r-tallos_bqt_4_sem"
                    onclick="select_columna($(this))">Tallos Bqt (-4 semanas)
                </li>
                <li class="list-group-item-yura mouse-hand reportes all_tallos_producidos" id="r-tallos_bqt_acum"
                    onclick="select_columna($(this))">Tallos Bqt Acumulado
                </li>
                <li class="list-group-item-yura mouse-hand reportes all_tallos_producidos" id="r-porcent_bqt"
                    onclick="select_columna($(this))">% Bqt
                </li>
                <li class="mouse-hand bg-yura_dark text-right li_all_flor_comprada" style="height: 20px; padding: 2px"
                    onclick="$('.all_flor_comprada').addClass('bg-aqua'); $('.li_all_flor_comprada').toggleClass('hidden');
                    $('.col-flor_comprada').removeClass('hidden')">
                    <i class="fa fa-fw fa-plus"></i> (Flor Comprada) <i class="fa fa-fw fa-caret-down"></i>
                </li>
                <li class="mouse-hand bg-yura_dark text-right li_all_flor_comprada hidden"
                    style="height: 20px; padding: 2px"
                    onclick="$('.all_flor_comprada').removeClass('bg-aqua'); $('.li_all_flor_comprada').toggleClass('hidden');
                    $('.col-flor_comprada').addClass('hidden')">
                    <i class="fa fa-fw fa-times"></i> (Flor Comprada) <i class="fa fa-fw fa-caret-down"></i>
                </li>
                <li class="list-group-item-yura mouse-hand bg-aqua reportes all_flor_comprada"
                    id="r-flor_comprada" onclick="select_columna($(this))">Flor Comprada
                </li>
                <li class="list-group-item-yura mouse-hand reportes all_flor_comprada" id="r-flor_comprada_acum"
                    onclick="select_columna($(this))">Flor Comprada Acumulado
                </li>
                <li class="list-group-item-yura mouse-hand reportes all_flor_comprada" id="r-flor_comprada_tallos_exportables"
                    onclick="select_columna($(this))">Compra Exportables
                </li>
                <li class="list-group-item-yura mouse-hand reportes all_flor_comprada" id="r-flor_comprada_tallos_exportables_acum"
                    onclick="select_columna($(this))">Compra Exportables Acumulado
                </li>
                <li class="list-group-item-yura mouse-hand reportes all_flor_comprada" id="r-flor_comprada_porcent_exportables"
                    onclick="select_columna($(this))">% Compra Exportables
                </li>
                <li class="list-group-item-yura mouse-hand reportes all_flor_comprada" id="r-flor_comprada_tallos_bqt"
                    onclick="select_columna($(this))">Compra Bqt
                </li>
                <li class="list-group-item-yura mouse-hand reportes all_flor_comprada" id="r-flor_comprada_tallos_bqt_acum"
                    onclick="select_columna($(this))">Compra Bqt Acumulado
                </li>
                <li class="list-group-item-yura mouse-hand reportes all_flor_comprada" id="r-flor_comprada_porcent_bqt"
                    onclick="select_columna($(this))">% Compra Bqt
                </li>
                <li class="mouse-hand bg-yura_dark text-right li_all_ventas" style="height: 20px; padding: 2px"
                    onclick="$('.all_ventas').addClass('bg-aqua'); $('.li_all_ventas').toggleClass('hidden');
                    $('.col-ventas').removeClass('hidden')">
                    <i class="fa fa-fw fa-plus"></i> (Ventas) <i class="fa fa-fw fa-caret-down"></i>
                </li>
                <li class="mouse-hand bg-yura_dark text-right li_all_ventas hidden" style="height: 20px; padding: 2px"
                    onclick="$('.all_ventas').removeClass('bg-aqua'); $('.li_all_ventas').toggleClass('hidden');
                    $('.col-ventas').addClass('hidden')">
                    <i class="fa fa-fw fa-times"></i> (Ventas) <i class="fa fa-fw fa-caret-down"></i>
                </li>
                <li class="list-group-item-yura mouse-hand bg-aqua reportes all_ventas" id="r-venta_total"
                    onclick="select_columna($(this))">Venta Total
                </li>
                <li class="list-group-item-yura mouse-hand reportes all_ventas" id="r-venta_total_acum"
                    onclick="select_columna($(this))">Venta Total Acumulado
                </li>
                <li class="list-group-item-yura mouse-hand reportes all_ventas" id="r-venta_normal"
                    onclick="select_columna($(this))">Venta Normal
                </li>
                <li class="list-group-item-yura mouse-hand reportes all_ventas" id="r-venta_normal_acum"
                    onclick="select_columna($(this))">Venta Normal Acumulado
                </li>
                <li class="list-group-item-yura mouse-hand reportes all_ventas" id="r-porcent_venta_normal"
                    onclick="select_columna($(this))">% Venta Normal
                </li>
                <li class="list-group-item-yura mouse-hand reportes all_ventas" id="r-venta_bqt"
                    onclick="select_columna($(this))">Venta Bqt
                </li>
                <li class="list-group-item-yura mouse-hand reportes all_ventas" id="r-venta_bqt_4_sem"
                    onclick="select_columna($(this))">Venta Bqt (-4 semanas)
                </li>
                <li class="list-group-item-yura mouse-hand reportes all_ventas" id="r-venta_bqt_acum"
                    onclick="select_columna($(this))">Venta Bqt Acumulado
                </li>
                <li class="list-group-item-yura mouse-hand reportes all_ventas" id="r-porcent_venta_bqt"
                    onclick="select_columna($(this))">% Venta Bqt
                </li>
                <li class="list-group-item-yura mouse-hand reportes all_ventas" id="r-precio_tallo_total"
                    onclick="select_columna($(this))">Precio x Tallo Total
                </li>
                <li class="list-group-item-yura mouse-hand reportes all_ventas" id="r-precio_tallo_normal"
                    onclick="select_columna($(this))">Precio x Tallo Normal
                </li>
                <li class="list-group-item-yura mouse-hand reportes all_ventas" id="r-precio_tallo_bqt"
                    onclick="select_columna($(this))">Precio x Tallo Bqt
                </li>
                <li class="list-group-item-yura mouse-hand reportes all_ventas" id="r-precio_tallo_bqt_4_sem"
                    onclick="select_columna($(this))">Precio x Tallo Bqt (-4 semanas)
                </li>
                <li class="list-group-item-yura mouse-hand reportes all_ventas" id="r-venta_m2"
                    onclick="select_columna($(this))">Venta x m2
                </li>
                <li class="list-group-item-yura mouse-hand reportes all_ventas" id="r-venta_m2_25_sem"
                    onclick="select_columna($(this))">Venta x m2 (52 sem.)
                </li>
                <li class="mouse-hand bg-yura_dark text-right li_all_costos" style="height: 20px; padding: 2px"
                    onclick="$('.all_costos').addClass('bg-aqua'); $('.li_all_costos').toggleClass('hidden');
                    $('.col-costos').removeClass('hidden')">
                    <i class="fa fa-fw fa-plus"></i> (Costos) <i class="fa fa-fw fa-caret-down"></i>
                </li>
                <li class="mouse-hand bg-yura_dark text-right li_all_costos hidden" style="height: 20px; padding: 2px"
                    onclick="$('.all_costos').removeClass('bg-aqua'); $('.li_all_costos').toggleClass('hidden');
                    $('.col-costos').addClass('hidden')">
                    <i class="fa fa-fw fa-times"></i> (Costos) <i class="fa fa-fw fa-caret-down"></i>
                </li>
                <li class="list-group-item-yura mouse-hand bg-aqua reportes all_costos" id="r-costos_total"
                    onclick="select_columna($(this))">Costos Total
                </li>
                <li class="list-group-item-yura mouse-hand reportes all_costos" id="r-costos_total_acum"
                    onclick="select_columna($(this))">Costos Total Acumulado
                </li>
                <li class="list-group-item-yura mouse-hand reportes all_costos" id="r-mo"
                    onclick="select_columna($(this))">MO
                </li>
                <li class="list-group-item-yura mouse-hand reportes all_costos" id="r-mo_acum"
                    onclick="select_columna($(this))">MO Acumulado
                </li>
                <li class="list-group-item-yura mouse-hand reportes all_costos" id="r-porcent_mo"
                    onclick="select_columna($(this))">% MO
                </li>
                <li class="list-group-item-yura mouse-hand reportes all_costos" id="r-insumos"
                    onclick="select_columna($(this))">Insumos
                </li>
                <li class="list-group-item-yura mouse-hand reportes all_costos" id="r-insumos_acum"
                    onclick="select_columna($(this))">Insumos Acumulado
                </li>
                <li class="list-group-item-yura mouse-hand reportes all_costos" id="r-porcent_insumos"
                    onclick="select_columna($(this))">% Insumos
                </li>
                <li class="list-group-item-yura mouse-hand reportes all_costos" id="r-fijos"
                    onclick="select_columna($(this))">Fijos
                </li>
                <li class="list-group-item-yura mouse-hand reportes all_costos" id="r-fijos_acum"
                    onclick="select_columna($(this))">Fijos Acumulado
                </li>
                <li class="list-group-item-yura mouse-hand reportes all_costos" id="r-porcent_fijos"
                    onclick="select_columna($(this))">% Fijos
                </li>
                <li class="list-group-item-yura mouse-hand reportes all_costos" id="r-regalias"
                    onclick="select_columna($(this))">Regalias
                </li>
                <li class="list-group-item-yura mouse-hand reportes all_costos" id="r-regalias_acum"
                    onclick="select_columna($(this))">Regalias Acumulado
                </li>
                <li class="list-group-item-yura mouse-hand reportes all_costos" id="r-porcent_regalias"
                    onclick="select_columna($(this))">% Regalias
                </li>
                <li class="list-group-item-yura mouse-hand reportes all_costos" id="r-compra_flor"
                    onclick="select_columna($(this))">Compra Flor
                </li>
                <li class="list-group-item-yura mouse-hand reportes all_costos" id="r-compra_flor_acum"
                    onclick="select_columna($(this))">Compra Flor Acumulado
                </li>
                <li class="list-group-item-yura mouse-hand reportes all_costos" id="r-porcent_compra_flor"
                    onclick="select_columna($(this))">% Compra Flor
                </li>
                <li class="list-group-item-yura mouse-hand reportes all_costos" id="r-costos_m2"
                    onclick="select_columna($(this))">Costos x m2
                </li>
                <li class="list-group-item-yura mouse-hand reportes all_costos" id="r-costos_m2_52_sem"
                    onclick="select_columna($(this))">Costos x m2 (52 sem)
                </li>
                <li class="mouse-hand bg-yura_dark text-right li_all_ebitda" style="height: 20px; padding: 2px"
                    onclick="$('.all_ebitda').addClass('bg-aqua'); $('.li_all_ebitda').toggleClass('hidden');
                    $('.col-ebitda').removeClass('hidden')">
                    <i class="fa fa-fw fa-plus"></i> (EBITDA) <i class="fa fa-fw fa-caret-down"></i>
                </li>
                <li class="mouse-hand bg-yura_dark text-right li_all_ebitda hidden" style="height: 20px; padding: 2px"
                    onclick="$('.all_ebitda').removeClass('bg-aqua'); $('.li_all_ebitda').toggleClass('hidden');
                    $('.col-ebitda').addClass('hidden')">
                    <i class="fa fa-fw fa-times"></i> (EBITDA) <i class="fa fa-fw fa-caret-down"></i>
                </li>
                <li class="list-group-item-yura mouse-hand bg-aqua reportes all_ebitda" id="r-ebitda"
                    onclick="select_columna($(this))">EBITDA
                </li>
                <li class="list-group-item-yura mouse-hand reportes all_ebitda" id="r-ebitda_acum"
                    onclick="select_columna($(this))">EBITDA Acumulado
                </li>
                <li class="list-group-item-yura mouse-hand reportes all_ebitda" id="r-ebitda_m2"
                    onclick="select_columna($(this))">EBITDA x m2
                </li>
                <li class="list-group-item-yura mouse-hand reportes all_ebitda" id="r-ebitda_m2_52_sem"
                    onclick="select_columna($(this))">EBITDA x m2 (52 sem)
                </li>
            </ul>
        </div>

        <div id="div_reporte"></div>
    </section>
@endsection

@section('script_final')
    @include('adminlte.gestion.costos.tabla_operaciones.script')
@endsection

@section('css_final')
    <style>
        #div_menu_reportes {
            overflow-y: scroll;
            border: 1px solid #9d9d9d;
            position: absolute;
            right: 20px;
            width: 250px;
            max-height: 400px;
            z-index: 99;
            background-color: white;
            margin-top: 5px;
            border-radius: 16px 0 0 16px;
            font-size: 0.9em;
        }

        .reportes {
            height: 30px;
            vertical-align: center;
            horiz-align: center;
        }
    </style>
@endsection
