@extends('layouts.adminlte.master')

@section('titulo')
    Dashboard
@endsection

@section('css_inicio')
    <style>
        .nodo_org {
            background-color: #e9ecef !important;
            width: 200px;
            cursor: pointer;
            -webkit-box-shadow: 9px 8px 11px -2px rgba(0, 0, 0, 0.34);
            -moz-box-shadow: 9px 8px 11px -2px rgba(0, 0, 0, 0.34);
            box-shadow: 5px 3px 11px -2px rgba(0, 0, 0, 0.34);
            font-size: 1em;
        }

        .nodo_org_selected {
            background-color: #ccc9c9 !important;
        }
    </style>
@endsection

@section('script_inicio')
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

    <script src="https://bernii.github.io/gauge.js/dist/gauge.min.js"></script>
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Bienvenido
            <small>a <a href="{{ url('') }}" class="text-color_yura">{{ explode('//', url(''))[1] }}</a></small>
        </h1>
        <ol class="breadcrumb">
            <li class="active">
                <a href="javascript:void(0)" onclick="location.reload()" class="text-color_yura">
                    <i class="fa fa-fw fa-refresh text-color_yura"></i> Inicio
                </a>
            </li>
        </ol>
    </section>

    <section class="content">
        @if (count(getUsuario(Session::get('id_usuario'))->rol()->getSubmenusByTipo('C')) > 0)
            <div id="box_cuadros" class="box box-primary hide">
                <small><em>Vista para movil en desarrollo</em></small>
            </div>

            <div id="box_arbol" class="hide" style="margin-bottom: 10px">
                <div id="div_box_body">
                    <table style="width: 100%;" align="center" class="table-borsdered">
                        <tr>
                            @for ($i = 1; $i <= 16; $i++)
                                <td style="vertical-align: inherit; width: 6.25%"></td>
                            @endfor
                        </tr>
                        <tr>
                            <td colspan="6">
                                {{-- <select name="filtro_predeterminado_planta" id="filtro_predeterminado_planta"
                                        class="form-control select-yura_default" style="margin-top: 0; width: 150px; height: 31px;"
                                        onchange="select_planta($(this).val(), 'filtro_variedad', '', '<option value=T id=option_acumulado_var selected>Seleccione</option>')">
                                    <option value="">Todas las variedades</option>
                                    @foreach (getPlantas() as $p)
                                        <option value="{{$p->id_planta}}">{{$p->nombre}}</option>
                                    @endforeach
                                </select>
                                <select name="filtro_variedad" id="filtro_variedad" onchange="select_filtro_variedad()"
                                        class="form-control select-yura_default" style="margin-top: 5px; width: 150px; height: 31px;">
                                    <option value="" id="option_acumulado_var">Acumulado</option>
                                </select> --}}
                            </td>
                            <td colspan="4" class="text-center">
                                <div style="" class="td-org">
                                    <div class="row">
                                        <div class="col-md-12 text-center" style="margin-top: 10px">
                                            <strong>EBITDA / m<sup>2</sup> / año</strong>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <img src="{{ url('images/negocio.png') }}" alt="$" class="icon_td-org"
                                                aria-hidden="true">
                                            <ul class="list-unstyled text-center" style="margin-top: 5px">
                                                <li>
                                                    @php
                                                        if ($rentabilidad_m2_4_semanas >= 0) {
                                                            $color_precio_4_sem = '#ef6e11';
                                                            if ($rentabilidad_m2_4_semanas >= $empresa->objetivo_precio * $empresa->objetivo_tallos - $empresa->objetivo_costos_fijos - $empresa->objetivo_costos_variables) {
                                                                $color_precio_4_sem = '#00B388';
                                                            }
                                                        } else {
                                                            $color_precio_4_sem = '#d01c62';
                                                        }
                                                    @endphp
                                                    <strong style="color: {{ $color_precio_4_sem }}">
                                                        $<span>{{ number_format($rentabilidad_m2_4_semanas, 2) }}</span>
                                                        <sup>(4 semanas)</sup>
                                                    </strong>
                                                </li>
                                                <li>
                                                    @php
                                                        if ($rentabilidad_m2_13_semanas >= 0) {
                                                            $color_precio_13_sem = '#ef6e11';
                                                            if ($rentabilidad_m2_13_semanas >= $empresa->objetivo_precio * $empresa->objetivo_tallos - $empresa->objetivo_costos_fijos - $empresa->objetivo_costos_variables) {
                                                                $color_precio_13_sem = '#00B388';
                                                            }
                                                        } else {
                                                            $color_precio_13_sem = '#d01c62';
                                                        }
                                                    @endphp
                                                    <strong style="color: {{ $color_precio_13_sem }}">
                                                        $<span>{{ number_format($rentabilidad_m2_13_semanas, 2) }}</span>
                                                        <sup>(13 semanas)</sup>
                                                    </strong>
                                                </li>
                                                <li>
                                                    <strong>
                                                        $<span>{{ number_format($rentabilidad_m2_anual, 2) }}</span>
                                                        <sup>(año)</sup>
                                                    </strong>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="row" style="margin-top: 10px">
                                        <div class="col-md-12">
                                            <button type="button" class="btn btn-xs btn-block btn-yura_default"
                                                onclick="mostrar_indicadores_claves(4)">
                                                <strong style="color: black; font-size: 1.1em">
                                                    Objetivo:
                                                    ${{ $empresa->objetivo_precio * $empresa->objetivo_tallos + $empresa->objetivo_flor_comprada - $empresa->objetivo_costos_fijos - $empresa->objetivo_costos_variables }}
                                                </strong>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td colspan="6">
                            </td>
                        </tr>
                        <tr>
                            <td colspan="4"></td>
                            <td colspan="4" style="border-bottom: 1px solid #00B388"></td>
                            <td colspan="4"
                                style="border-left: 1px solid #00B388; height: 15px; border-bottom: 1px solid #00B388"></td>
                            <td colspan="4"></td>
                        </tr>
                        <tr>
                            <td colspan="4"></td>
                            <td colspan="4" style="border-left: 1px solid #00B388; height: 15px"></td>
                            <td colspan="4" style="border-left: 1px solid #00B388; height: 15px"></td>
                            <td colspan="4" style="border-left: 1px solid #00B388"></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td colspan="4" class="text-center">
                                <div style="" class="td-org">
                                    <div class="row">
                                        <div class="col-md-12 text-center" style="margin-top: 10px">
                                            <strong>Ventas / m<sup>2</sup> / año</strong>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <img src="{{ url('images/devaluacion.png') }}" alt="$"
                                                class="icon_td-org" aria-hidden="true">
                                            <ul class="list-unstyled text-center" style="margin-top: 5px">
                                                <li>
                                                    <strong
                                                        style="color: {{ $venta_m2_anno_4_semanas >= $empresa->objetivo_precio * $empresa->objetivo_tallos ? '#00B388' : '#d01c62' }}">
                                                        $<span>{{ round($venta_m2_anno_4_semanas, 2) }}</span>
                                                        <sup>(4 semanas)</sup>
                                                    </strong>
                                                </li>
                                                <li>
                                                    <strong
                                                        style="color: {{ $venta_m2_anno_13_semanas >= $empresa->objetivo_precio * $empresa->objetivo_tallos ? '#00B388' : '#d01c62' }}">
                                                        $<span>{{ $venta_m2_anno_13_semanas }}</span>
                                                        <sup>(13 semanas)</sup>
                                                    </strong>
                                                </li>
                                                <li>
                                                    <strong>
                                                        $<span>{{ $venta_m2_anno_anual }}</span>
                                                        <sup>(año)</sup>
                                                    </strong>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="row" style="margin-top: 10px">
                                        <div class="col-md-12">
                                            <button type="button" class="btn btn-xs btn-block btn-yura_default"
                                                onclick="mostrar_indicadores_claves(0)">
                                                <strong style="color: black; font-size: 1.1em">Objetivo:
                                                    ${{ $empresa->objetivo_precio * $empresa->objetivo_tallos + $empresa->objetivo_flor_comprada }}</strong>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td></td>
                            <td colspan="4" class="text-center">
                                <div style="" class="td-org">
                                    <div class="row">
                                        <div class="col-md-12 text-center" style="margin-top: 10px">
                                            <strong>Venta Comprada</strong>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <img src="{{ url('images/carrito.png') }}" alt="$" class="icon_td-org"
                                                aria-hidden="true">
                                            <ul class="list-unstyled text-center" style="margin-top: 5px">
                                                <li>
                                                    <strong style="color:black">
                                                        <span>
                                                            ${{ number_format($venta_comprada_4_semana, 2) }}
                                                            <sup>(-4 semanas)</sup>
                                                        </span>
                                                    </strong>
                                                </li>
                                                <li>
                                                    <strong style="color:black">
                                                        <span>
                                                            ${{ number_format($venta_comprada_13_semana, 2) }}
                                                            <sup>(-13 semanas)</sup>
                                                        </span>
                                                    </strong>
                                                </li>
                                                <li>
                                                    <strong style="color:black">
                                                        <span>
                                                            ${{ number_format($venta_comprada_anno, 2) }}
                                                            <sup>(año)</sup>
                                                        </span>
                                                    </strong>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="row" style="margin-top: 10px">
                                        <div class="col-md-12">
                                            <button type="button" class="btn btn-xs btn-block btn-yura_default">
                                                <strong style="color: black; font-size: 1.1em">Objetivo:
                                                    ${{ $empresa->objetivo_flor_comprada }}</strong>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td></td>
                            <td colspan="4">
                                <div style="" class="td-org">
                                    <div class="row">
                                        <div class="col-md-12 text-center" style="margin-top: 10px">
                                            <strong>Costos / m<sup>2</sup> / año</strong>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <img src="{{ url('images/costos.png') }}" alt="$" class="icon_td-org"
                                                aria-hidden="true">
                                            <ul class="list-unstyled text-center" style="margin-top: 5px">
                                                <li>
                                                    <strong
                                                        style="color: {{ $costos_m2_4_semanas <= $empresa->objetivo_costos_fijos + $empresa->objetivo_costos_variables ? '#00B388' : '#d01c62' }}">
                                                        $<span>{{ number_format($costos_m2_4_semanas, 2) }}</span>
                                                        <sup>(4 semanas)</sup>
                                                    </strong>
                                                </li>
                                                <li>
                                                    <strong
                                                        style="color: {{ $costos_m2_13_semanas <= $empresa->objetivo_costos_fijos + $empresa->objetivo_costos_variables ? '#00B388' : '#d01c62' }}">
                                                        $<span>{{ number_format($costos_m2_13_semanas, 2) }}</span>
                                                        <sup>(13 semanas)</sup>
                                                    </strong>
                                                </li>
                                                <li>
                                                    <strong>
                                                        $<span>{{ number_format($costos_m2_anual, 2) }}</span>
                                                        <sup>(año)</sup>
                                                    </strong>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="row" style="margin-top: 10px">
                                        <div class="col-md-12">
                                            <button type="button" class="btn btn-xs btn-block btn-yura_default"
                                                onclick="mostrar_indicadores_claves(3)">
                                                <strong style="color: black; font-size: 1.1em">Objetivo:
                                                    ${{ $empresa->objetivo_costos_fijos + $empresa->objetivo_costos_variables }}</strong>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td></td>
                        </tr>
                        <tr>
                            <td colspan="2"></td>
                            <td colspan="1" style="border-bottom: 1px solid #00B388"></td>
                            <td colspan="7" style="border-left: 1px solid #00B388; height: 15px"></td>
                            <td colspan="3" style="border-bottom: 1px solid #00B388; height: 15px"></td>
                            <td colspan="3" style="border-left: 1px solid #00B388; height: 15px"></td>
                        </tr>
                        <tr>
                            <td colspan="2"></td>
                            <td colspan="4"
                                style="border-left: 1px solid #00B388; border-top: 1px solid #00B388; height: 15px"></td>
                            <td colspan="4" style="border-left: 1px solid #00B388; height: 15px"></td>
                            <td colspan="4"
                                style="border-left: 1px solid #00B388; border-top: 1px solid #00B388; height: 15px"></td>
                            <td colspan="2" style="border-left: 1px solid #00B388; height: 15px"></td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-center">
                                <div style="" class="td-org">
                                    <div class="row">
                                        <div class="col-md-12" style="margin-top: 10px">
                                            <ul class="list-unstyled text-center">
                                                <li>
                                                    <strong
                                                        style="color: {{ $precio_x_tallo >= $empresa->objetivo_precio ? '#00B388' : '#d01c62' }}">
                                                        <small>Precio: $</small>
                                                        <span id="span_precio_x_tallo" title="Tallo">
                                                            {{ number_format($precio_x_tallo, 2) }}
                                                        </span>
                                                    </strong>
                                                </li>
                                                <li>
                                                    <strong>
                                                        <small>Precio Normal: $</small>
                                                        <span>
                                                            {{ number_format($precio_x_tallo_normal, 2) }}
                                                        </span>
                                                    </strong>
                                                </li>
                                                <li>
                                                    <strong>
                                                        <small>Precio Bqt: $</small>
                                                        <span>
                                                            {{ number_format($precio_x_tallo_bqt, 2) }}
                                                        </span>
                                                    </strong>
                                                </li>
                                                <li>
                                                    <strong>
                                                        <small>Venta:</small>
                                                        $<span id="span_venta">{{ number_format($venta, 2) }}</span>
                                                    </strong>
                                                </li>
                                                <li>
                                                    <strong>
                                                        <small>Venta Normal:</small>
                                                        {{ $porcent_venta_normal }}%
                                                    </strong>
                                                </li>
                                                <li>
                                                    <strong>
                                                        <small>Venta Bqt:</small>
                                                        {{ $porcent_venta_bqt }}%
                                                    </strong>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="row" style="margin-top: 10px">
                                        <div class="col-md-12">
                                            <button type="button" class="btn btn-xs btn-block btn-yura_default"
                                                onclick="mostrar_indicadores_claves(1)">
                                                <strong style="color: black; font-size: 1.1em">Objetivo Precio:
                                                    ${{ $empresa->objetivo_precio }}</strong>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td></td>
                            <td colspan="3" class="text-center">
                                <div style="" class="td-org">
                                    <div class="row">
                                        <div class="col-md-12" style="margin-top: 10px">
                                            <ul class="list-unstyled text-center">
                                                <li>
                                                    <strong>
                                                        <small>Área:</small>
                                                        <span
                                                            id="span_area_produccion">{{ number_format(round($area_produccion / 10000, 2), 2) }}</span></strong>
                                                </li>
                                                <li>
                                                    <strong
                                                        style="color: {{ $tallos_m2 >= $empresa->objetivo_tallos ? '#00B388' : '#d01c62' }}">
                                                        <small>Tallos/m<sup>2</sup>/52 sem:</small>
                                                        <span id="span_tallos_m2">
                                                            {{ number_format($tallos_m2, 2) }}
                                                        </span>
                                                    </strong>
                                                </li>
                                                <li>
                                                    <strong title="Tallos cosechados">
                                                        <small>T/cosechados:</small>
                                                        <span id="span_tallos_cosechados">
                                                            {{ number_format($tallos_cosechados) }}
                                                        </span>
                                                    </strong>
                                                </li>
                                                <li>
                                                    <strong title="Tallos clasificados"
                                                        onclick="detallar_indicador({{ '"D2"' }})"
                                                        style="color: #333333" class="mouse-hand">
                                                        <small>T/exportables:</small>
                                                        <span
                                                            id="span_tallos">{{ number_format($tallos_exportables) }}</span></strong>
                                                </li>
                                                <li>
                                                    <strong title="Cajas exportadas">
                                                        <small>Tallos Año:</small>
                                                        {{ number_format($tallos_anno) }}
                                                    </strong>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="row" style="margin-top: 10px">
                                        <div class="col-md-12">
                                            <button type="button" class="btn btn-xs btn-block btn-yura_default"
                                                onclick="mostrar_indicadores_claves(5)">
                                                <strong style="color: black; font-size: 1.1em">Objetivo
                                                    Tallos/m<sup>2</sup>: {{ $empresa->objetivo_tallos }}</strong>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td colspan="1"></td>
                            <td colspan="4" class="text-center">
                                <div style="" class="td-org">
                                    <div class="row">
                                        <div class="col-md-12" style="margin-top: 10px">
                                            <ul class="list-unstyled text-center">
                                                <li>
                                                    <strong title="Fijos, Semana: {{ explode(':', $costos_fijos)[0] }}">
                                                        <small>Fijos:</small>
                                                        <span id="span_costos_fijos">
                                                            ${{ number_format(explode(':', $costos_fijos)[1], 2) }}
                                                        </span>
                                                    </strong>
                                                </li>
                                                <li>
                                                    <strong
                                                        title="Regalías, Semana: {{ explode(':', $costos_regalias)[0] }}">
                                                        <small>Regalías:</small>
                                                        <span id="span_costos_regalias">
                                                            ${{ number_format(explode(':', $costos_regalias)[1], 2) }}
                                                        </span>
                                                    </strong>
                                                </li>
                                                <li>
                                                    <strong>
                                                        <small>TOTAL Fijos:</small>
                                                        <span>
                                                            ${{ number_format(explode(':', $costos_fijos)[1] + explode(':', $costos_regalias)[1], 2) }}
                                                        </span>
                                                    </strong>
                                                </li>
                                                <li>
                                                    @php
                                                        $costos_fijos_m2_anno = round(((explode(':', $costos_fijos)[1] + explode(':', $costos_regalias)[1]) / $area_produccion) * 52, 2);
                                                    @endphp
                                                    <strong
                                                        style="color: {{ $costos_fijos_m2_anno <= $empresa->objetivo_costos_fijos ? '#00B388' : '#d01c62' }}">
                                                        <small>Total/m<sup>2</sup>/año</small>
                                                        <span>
                                                            ${{ $costos_fijos_m2_anno }}
                                                        </span>
                                                    </strong>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="row" style="margin-top: 10px">
                                        <div class="col-md-12">
                                            <button type="button" class="btn btn-xs btn-block btn-yura_default"
                                                onclick="mostrar_indicadores_claves(2)">
                                                <strong style="color: black; font-size: 1.1em">
                                                    Objetivo Fijos:
                                                    ${{ $empresa->objetivo_costos_fijos }}
                                                </strong>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td></td>
                            <td colspan="3" class="text-center">
                                <div style="" class="td-org">
                                    <div class="row">
                                        <div class="col-md-12" style="margin-top: 10px">
                                            <ul class="list-unstyled text-center">
                                                <li>
                                                    <strong title="Total">
                                                        <small>Total:</small>
                                                        <span
                                                            id="span_costos_total">${{ number_format(explode(':', $costos_mano_obra)[1] + explode(':', $costos_insumos)[1] + $costos_bqt, 2) }}</span></strong>
                                                </li>
                                                <li>
                                                    <strong
                                                        title="Mano de Obra, Semana: {{ explode(':', $costos_mano_obra)[0] }}">
                                                        <small>MO:</small>
                                                        <span
                                                            id="span_costos_mano_obra">${{ number_format(explode(':', $costos_mano_obra)[1], 2) }}</span></strong>
                                                </li>
                                                <li>
                                                    <strong title="MP, Semana: {{ explode(':', $costos_insumos)[0] }}">
                                                        <small>MP:</small>
                                                        <span
                                                            id="span_costos_insumos">${{ number_format(explode(':', $costos_insumos)[1], 2) }}</span></strong>
                                                </li>

                                                <li>
                                                    <strong>
                                                        <small>Flor Bqt:</small>
                                                        <span
                                                            id="">${{ number_format($compra_flor_bqt, 2) }}</span>
                                                    </strong>
                                                </li>
                                                <li>
                                                    <strong>
                                                        <small>Flor Export.:</small>
                                                        <span
                                                            id="">${{ number_format($compra_flor_export, 2) }}</span>
                                                    </strong>
                                                </li>
                                                <li>
                                                    @php
                                                        $costos_variables_m2_anno = round(((explode(':', $costos_mano_obra)[1] + explode(':', $costos_insumos)[1] + $costos_bqt) / $area_produccion) * 52, 2);
                                                    @endphp
                                                    <strong
                                                        style="color: {{ $costos_variables_m2_anno <= $empresa->objetivo_costos_variables ? '#00B388' : '#d01c62' }}">
                                                        <small>Total/m<sup>2</sup>/año:</small>
                                                        <span id="">${{ $costos_variables_m2_anno }}</span>
                                                    </strong>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="row" style="margin-top: 10px">
                                        <div class="col-md-12">
                                            <button type="button" class="btn btn-xs btn-block btn-yura_default"
                                                onclick="mostrar_indicadores_claves(6)">
                                                <strong style="color: black; font-size: 1.1em">
                                                    Objetivo Variables:
                                                    ${{ $empresa->objetivo_costos_variables }}
                                                </strong>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </table>

                    <div id="div_indicadores_claves" style="margin-top: 10px"></div>
                </div>
            </div>

            <script>
                function render_gauge(canvas, value, rangos, indices = false, time = 250) {
                    var staticLabels = false;
                    if (indices) {
                        staticLabels = {
                            //font: "10px sans-serif",  // Specifies font
                            labels: [rangos[0]['desde'], rangos[1]['desde'], rangos[2]['desde'], rangos[2][
                                'hasta'
                            ]], // Print labels at these values
                            color: "#000000", // Optional: Label text color
                            fractionDigits: 0 // Optional: Numerical precision. 0=round off.
                        };
                    }

                    var opts = {
                        angle: 0, // The span of the gauge arc
                        lineWidth: 0.2, // The line thickness
                        radiusScale: 1, // Relative radius
                        pointer: {
                            length: 0.46, // // Relative to gauge radius
                            strokeWidth: 0.033, // The thickness
                            color: '#000000', // Fill color
                        },
                        limitMax: false, // If false, max value increases automatically if value > maxValue
                        limitMin: true, // If true, the min value of the gauge will be fixed
                        colorStart: '#6F6EA0', // Colors
                        colorStop: '#C0C0DB', // just experiment with them
                        strokeColor: '#EEEEEE', // to see which ones work best for you
                        generateGradient: true,
                        highDpiSupport: true, // High resolution support
                        // renderTicks is Optional
                        renderTicks: {
                            divisions: 4,
                            divWidth: 1,
                            divLength: 0.79,
                            divColor: '#333333',
                            subDivisions: 5,
                            subLength: 0.45,
                            subWidth: 0.4,
                            subColor: '#666666'
                        },
                        staticZones: [{
                                strokeStyle: rangos[0]['color'],
                                min: rangos[0]['desde'],
                                max: rangos[0]['hasta'],
                                height: 0.6
                            }, // Red from 0 to 15
                            {
                                strokeStyle: rangos[1]['color'],
                                min: rangos[1]['desde'],
                                max: rangos[1]['hasta'],
                                height: 1
                            }, // Orange
                            {
                                strokeStyle: rangos[2]['color'],
                                min: rangos[2]['desde'],
                                max: rangos[2]['hasta'],
                                height: 1.2
                            } // Green
                        ],
                        staticLabels: staticLabels,
                    };

                    var target = document.getElementById(canvas); // your canvas element
                    var gauge = new Gauge(target).setOptions(opts); // create sexy gauge!
                    gauge.maxValue = rangos[2]['hasta']; // set max gauge value
                    gauge.setMinValue(rangos[0]['desde']); // Prefer setter over gauge.minValue = 0
                    gauge.animationSpeed = time; // set animation speed (32 is default value)
                    gauge.set(value); // set actual value
                }

                function mostrar_indicadores_claves(view, variedad = '') {
                    var views = [
                        'indicadores_ventas_m2',
                        'indicadores_claves',
                        'indicadores_claves_costos',
                        'indicadores_costos_m2',
                        'indicadores_rentabilidad_m2',
                        'indicadores_datos_importantes',
                        'indicadores_costos_datos_importantes',
                    ];
                    datos = {
                        view: views[view],
                        variedad: variedad
                    };
                    get_jquery('{{ url('mostrar_indicadores_claves') }}', datos, function(retorno) {
                        /*modal_view('modal-view_mostrar_indicadores_claves', retorno,
                            '<i class="fa fa-fw fa-dashboard"></i> Desglose de Indicadores', true, false, '95%');*/
                        $('#div_indicadores_claves').html(retorno);
                        location.href = '#div_indicadores_claves';
                    });
                }

                function count(id) {
                    var $el = $("#" + id),
                        value = $el.html();

                    $({
                        percentage: 0
                    }).stop(true).animate({
                        percentage: value
                    }, {
                        duration: 4000,
                        easing: "easeOutExpo",
                        step: function() {
                            // percentage with 1 decimal;
                            var percentageVal = Math.round(this.percentage * 10) / 10;

                            $el.text(percentageVal);
                        }
                    }).promise().done(function() {
                        // hard set the value after animation is done to be
                        // sure the value is correct
                        $el.text(value);
                    });
                }
            </script>
        @endif
    </section>
    <input type="hidden" id="finca_dashboard_inicial" value="{{ $finca }}">
@endsection

@section('script_final')
    {{-- JS de Chart.js --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.3/Chart.min.js"></script>

    <script>
        $('#vista_actual').val('dashboard');

        $(window).ready(function() {
            if ($(document).width() >= 833) { // mostrar arbol
                $('#box_arbol').removeClass('hide');
                $('#box_cuadros').addClass('hide');
            } else { // ocultar arbol
                $('#box_arbol').addClass('hide');
                $('#box_cuadros').removeClass('hide');
            }
        });

        $(window).resize(function() {
            if ($(document).width() >= 833) { // mostrar arbol
                $('#box_arbol').removeClass('hide');
                $('#box_cuadros').addClass('hide');
            } else { // ocultar arbol
                $('#box_arbol').addClass('hide');
                $('#box_cuadros').removeClass('hide');
            }
        });

        function select_filtro_variedad() {
            datos = {
                variedad: $('#filtro_variedad').val()
            };
            if (datos['variedad'] != '')
                get_jquery('{{ url('select_filtro_variedad') }}', datos, function(retorno) {
                    $('#div_box_body').html(retorno);
                });
            else
                location.href = '/';
        }

        function detallar_indicador(ind) {
            datos = {
                ind: ind
            };
            get_jquery('{{ url('detallar_indicador') }}', datos, function(retorno) {
                modal_view('modal-view_detallar_indicador', retorno,
                    '<i class="fa fa-fw fa-table"></i> Detalles del indicador', true, false, '95%')
            });
        }
    </script>
@endsection
