@if ($inventario_recepcion != '')
    @php
        $variedad = $inventario_recepcion->variedad;
    @endphp
    <table style="width: 100%">
        <tr style="padding: 0">
            <th style="text-align: center; padding: 0; font-size: 1em" colspan="3">
                {{ $variedad->nombre }}
            </th>
        </tr>
        <tr style="padding: 0">
            <th style="text-align: center; padding: 0;" colspan="2">
                {!! $barCode->getBarcode($inventario_recepcion->id_desglose_recepcion, $barCode::TYPE_CODE_128, 2) !!}
            </th>
            <th style="text-align: left; font-size: 1em">
                <b>{{ $inventario_recepcion->longitud }}</b><sup>cm</sup>
                <br>
                {{ $inventario_recepcion->tallos_x_malla }}<sup>tallos</sup>
            </th>
        </tr>
        <tr style="padding: 0">
            <th style="text-align: left; font-size: 1em; padding: 0">
                {{ getDias(TP_LETRA)[transformDiaPhp(date('w', strtotime($inventario_recepcion->fecha)))] }}{{ intVal(substr($inventario_recepcion->fecha, 5, 2)) }}.{{ substr($inventario_recepcion->fecha, 8, 2) }}
            </th>
            <th style="text-align: center; font-size: 1em; padding: 0" colspan="2">
                PRODUCT of ECUADOR
            </th>
        </tr>
    </table>

    <input type="hidden" id="id_desglose_recepcion_scan" value="{{ $inventario_recepcion->id_desglose_recepcion }}">
    <input type="hidden" id="scan_nombre_variedad" value="{{ $variedad->nombre }}">
    <input type="hidden" id="scan_longitud" value="{{ $inventario_recepcion->longitud }}">
    <input type="hidden" id="scan_tallos_x_ramo" value="{{ $inventario_recepcion->tallos_x_malla }}">
    <input type="hidden" id="scan_disponibles" value="{{ $inventario_recepcion->disponibles }}">
    <input type="hidden" id="scan_edad" value="{{ difFechas(hoy(), $inventario_recepcion->fecha)->days }}">

    @if ($inventario_recepcion->disponibles > 0)
        <div class="alert alert-success text-center" style="margin-top: 5px">
            <i class="fa fa-fw fa-check"></i> LECTURA EXITOSA
        </div>
    @else
        <div class="alert alert-warning text-center" style="margin-top: 5px">
            <i class="fa fa-fw fa-ban"></i> NO HAY FLOR DISPONIBLE
        </div>
    @endif

    <script>
        agregar_a_listado('{{ $inventario_recepcion->id_desglose_recepcion }}');
    </script>
@else
    <div class="alert alert-danger text-center">
        <i class="fa fa-fw fa-ban"></i> LECTURA FALLIDA
    </div>
@endif
