@if ($model != '')
    <table style="width: 100%; font-size: 1.1em">
        <tr style="padding: 0">
            <th style="text-align: left; padding: 0;">
                {{ $model->variedad->nombre }}
            </th>
            <th style="text-align: left; padding: 0;" rowspan="2">
                {{ $model->tallos_x_ramo }} tallos
                <br>
                {{ $model->longitud }}cm
            </th>
        </tr>
        <tr style="padding: 0">
            <th style="text-align: center; padding: 0;">
                {!! $barCode->getBarcode($model->id_despacho_proveedor, $barCode::TYPE_CODE_128, 2) !!}
            </th>
        </tr>
        <tr style="padding: 0">
            <th style="text-align: center; padding: 0;" colspan="2">
                @if ($model->id_proveedor > 0)
                    {{ $model->proveedor->nombre }}
                @endif
            </th>
        </tr>
        <tr>
            <th class="padding_lateral_5 th_yura_green" style="border-color: white">
                FALTANTES
            </th>
            <th class="padding_lateral_5 th_yura_green" style="border-color: white">
                {{ $model->disponibles }}
            </th>
        </tr>
    </table>

    <input type="hidden" id="id_despacho_proveedor_scan" value="{{ $model->id_despacho_proveedor }}">
    <input type="hidden" id="scan_nombre_proveedor" value="{{ $model->proveedor->nombre }}">
    <input type="hidden" id="scan_nombre_variedad" value="{{ $model->variedad->nombre }}">
    <input type="hidden" id="scan_longitud" value="{{ $model->longitud }}">
    <input type="hidden" id="scan_tallos_x_ramo" value="{{ $model->tallos_x_ramo }}">
    <input type="hidden" id="scan_disponibles" value="{{ $model->disponibles }}">

    @if ($model->disponibles > 0)
        <div class="alert alert-success text-center" style="margin-top: 5px; margin-bottom: 0px">
            <i class="fa fa-fw fa-check"></i> LECTURA EXITOSA
        </div>

        @if ($consulta == 'false')
            <script>
                agregar_a_listado('{{ $model->id_despacho_proveedor }}');
            </script>
        @endif
    @else
        <div class="alert alert-warning text-center" style="margin-top: 5px">
            <i class="fa fa-fw fa-ban"></i> NO HAY FLOR DISPONIBLE
        </div>
    @endif
@else
    <div class="alert alert-info text-center">
        NO SE HA ENCONTRADO LA ETIQUETA
    </div>
@endif
