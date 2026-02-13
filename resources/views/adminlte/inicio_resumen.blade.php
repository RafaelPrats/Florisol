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
        <div style="margin-bottom: 10px; padding-left: 15px">
            @foreach ($data as $sf)
                @php
                    $ids_finca = [];
                    foreach ($sf['sf']->fincas as $f) {
                        array_push($ids_finca, $f->id_configuracion_empresa);
                    }
                @endphp
                <table style="width: 100%; margin-bottom: 10px" align="center">
                    <tr>
                        <td class="text-center">
                            <div style="" class="td-org">
                                <div class="row">
                                    <div class="col-md-12 text-center" style="margin-top: 10px">
                                        <strong>
                                            {{ $sf['sf']->nombre }}
                                        </strong>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <img src="{{ url('images/negocio.png') }}" alt="$" class="icon_td-org"
                                            aria-hidden="true" style="top: -32px !important;">
                                        <ul class="list-unstyled text-center" style="margin-top: 5px">
                                            @php
                                                $ebitda_1_mes = $sf['ventas_1_mes']->valor - $sf['costos_1_mes']->valor;
                                                $ebitda_4_mes = $sf['ventas_4_mes']->valor - $sf['costos_4_mes']->valor;
                                                $ebitda_1_anno = $sf['ventas_1_anno']->valor - $sf['costos_1_anno']->valor;
                                            @endphp
                                            <li>
                                                <strong data-toggle="tooltip" data-html="true" data-placement="left"
                                                    title="<strong>Ventas/m<sup>2</sup>/año: </strong> ${{ $sf['ventas_1_mes']->valor }}<br>
                                                        <strong>Costos/m<sup>2</sup>/año: </strong> ${{ $sf['costos_1_mes']->valor }}">
                                                    $ {{ number_format($ebitda_1_mes, 2) }}
                                                    <sup>(1 mes)</sup>
                                                </strong>
                                            </li>
                                            <li>
                                                <strong data-toggle="tooltip" data-html="true" data-placement="left"
                                                    title="<strong>Ventas/m<sup>2</sup>/año: </strong> ${{ $sf['ventas_4_mes']->valor }}<br>
                                                        <strong>Costos/m<sup>2</sup>/año: </strong> ${{ $sf['costos_4_mes']->valor }}">
                                                    $ {{ number_format($ebitda_4_mes, 2) }}
                                                    <sup>(4 meses)</sup>
                                                </strong>
                                            </li>
                                            <li>
                                                <strong data-toggle="tooltip" data-html="true" data-placement="left"
                                                    title="<strong>Ventas/m<sup>2</sup>/año: </strong> ${{ $sf['ventas_1_anno']->valor }}<br>
                                                        <strong>Costos/m<sup>2</sup>/año: </strong> ${{ $sf['costos_1_anno']->valor }}">
                                                    $ {{ number_format($ebitda_1_anno, 2) }}
                                                    <sup>(1 año)</sup>
                                                </strong>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <table style="width: 100%; margin-top: 10px" align="center">
                                <tr>
                                    @foreach ($fincas_propias as $f)
                                        @if (in_array($f->id_empresa, $ids_finca))
                                            @php
                                                $empresa = $f->empresa;
                                                $ebitda_1_mes = getIndicadorByName('R3-' . $f->id_empresa)->valor;
                                                if ($ebitda_1_mes >= 0) {
                                                    $color_precio_4_sem = '#ef6e11';
                                                    if ($ebitda_1_mes >= $empresa->objetivo_precio * $empresa->objetivo_tallos - $empresa->objetivo_costos_fijos - $empresa->objetivo_costos_variables) {
                                                        $color_precio_4_sem = '#00B388';
                                                    }
                                                } else {
                                                    $color_precio_4_sem = '#d01c62';
                                                }
                                            @endphp
                                            <td class="text-center" style="padding-right: 15px; padding-left: 15px">
                                                <div style="background-color: #c7e7db !important" class="td-org">
                                                    <div class="row">
                                                        <div class="col-md-12 text-center" style="margin-top: 10px">
                                                            <strong>EBITDA / m<sup>2</sup> / año</strong>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <img src="{{ url('images/negocio.png') }}" alt="$"
                                                                class="icon_td-org" aria-hidden="true">
                                                            <ul class="list-unstyled text-center" style="margin-top: 5px">
                                                                <li>
                                                                    <strong>
                                                                        $<span style="color: {{ $color_precio_4_sem }}"
                                                                            id="span_rentabilidad_m2_mensual_{{ $f->id_empresa }}">
                                                                            {{ number_format($ebitda_1_mes, 2) }}
                                                                        </span>
                                                                        <sup>(1 mes)</sup>
                                                                    </strong>
                                                                </li>
                                                                <li>
                                                                    @php
                                                                        $ebitda_13_sem = getIndicadorByName('R1-' . $f->id_empresa)->valor;
                                                                        if ($ebitda_13_sem >= 0) {
                                                                            $color_precio_13_sem = '#ef6e11';
                                                                            if ($ebitda_13_sem >= $empresa->objetivo_precio * $empresa->objetivo_tallos - $empresa->objetivo_costos_fijos - $empresa->objetivo_costos_variables) {
                                                                                $color_precio_13_sem = '#00B388';
                                                                            }
                                                                        } else {
                                                                            $color_precio_13_sem = '#d01c62';
                                                                        }
                                                                    @endphp
                                                                    <strong>
                                                                        $<span style="color: {{ $color_precio_4_sem }}"
                                                                            id="span_rentabilidad_m2_mensual_{{ $f->id_empresa }}">
                                                                            {{ number_format($ebitda_13_sem, 2) }}
                                                                        </span>
                                                                        <sup>(4 meses)</sup>
                                                                    </strong>
                                                                </li>
                                                                <li>
                                                                    @php
                                                                        $ebitda_1_anno = getIndicadorByName('R2-' . $f->id_empresa)->valor;
                                                                        if ($ebitda_1_anno >= 0) {
                                                                            $color_precio_1_anno = '#ef6e11';
                                                                            if ($ebitda_1_anno >= $empresa->objetivo_precio * $empresa->objetivo_tallos - $empresa->objetivo_costos_fijos - $empresa->objetivo_costos_variables) {
                                                                                $color_precio_1_anno = '#00B388';
                                                                            }
                                                                        } else {
                                                                            $color_precio_1_anno = '#d01c62';
                                                                        }
                                                                    @endphp
                                                                    <strong>
                                                                        $<span style="color: {{ $color_precio_1_anno }}"
                                                                            id="span_rentabilidad_m2_mensual_{{ $f->id_empresa }}">
                                                                            {{ number_format($ebitda_1_anno, 2) }}
                                                                        </span>
                                                                        <sup>(1 año)</sup>
                                                                    </strong>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                    <div class="row" style="margin-top: 10px">
                                                        <div class="col-md-12">
                                                            <button type="button"
                                                                class="btn btn-sm btn-block btn-yura_default"
                                                                onclick="select_finca_dashboard('{{ $f->id_empresa }}')">
                                                                {{ $f->empresa->nombre }}
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        @endif
                                    @endforeach
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            @endforeach
        </div>
    </section>
@endsection

@section('script_final')
    {{-- JS de Chart.js --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.3/Chart.min.js"></script>

    <script>
        $('#vista_actual').val('inicio_resumen');

        $(function() {
            $('[data-toggle="tooltip"]').tooltip()
        })

        notificar('Bienvenid@ {{ explode(' ', getUsuario(Session::get('id_usuario'))->nombre_completo)[0] }}',
            '{{ url('') }}',
            function() {}, null, false);

        function select_finca_dashboard(id) {
            $.LoadingOverlay('show');
            location.href = '{{ url('dashboard') }}' + '?f=' + id;
            $.LoadingOverlay('hide');
        }
    </script>
@endsection
