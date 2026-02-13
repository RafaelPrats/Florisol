<table style="width: 100%">
    <tr>
        <td class="padding_lateral_5">
            <div class="input-group">
                <div class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
                    Desde
                </div>
                <input type="date" class="form-control input-yura_default" id="filtro_desde" value="{{$desde}}" required>
            </div>
        </td>
        <td class="padding_lateral_5">
            <div class="input-group">
                <div class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
                    Hasta
                </div>
                <input type="date" class="form-control input-yura_default" id="filtro_hasta" value="{{$hasta}}" required>
                <div class="input-group-btn">
                    <button type="button" class="btn btn-yura_dark" onclick="listar_temperaturas()">
                        <i class="fa fa-fw fa-search"></i>
                    </button>
                    <button type="button" class="btn btn-yura_primary" onclick="add_temperatura()">
                        <i class="fa fa-fw fa-plus"></i>
                    </button>
                    <button type="button" class="btn btn-yura_default" title="Exportar" onclick="exportar_reporte_temperatura()">
                        <i class="fa fa-fw fa-file-excel-o"></i>
                    </button>
                </div>
            </div>
        </td>
    </tr>
</table>

@if(count($listado) > 0)
    <table class="table-striped table-bordered" style="border: 1px solid #9d9d9d; width: 100%; border-radius: 18px 18px 0 0"
           id="table_temperaturas">
        <thead>
        <tr id="tr_fijo_0">
            <th class=""
                style="border-color: #9d9d9d; background-color: #e9ecef !important; border-radius: 18px 0 0 0; padding-left: 5px">
                Fecha
            </th>
            <th class="" style="border-color: #9d9d9d; background-color: #e9ecef !important; padding-left: 5px">
                Máxima
            </th>
            <th class="" style="border-color: #9d9d9d; background-color: #e9ecef !important; padding-left: 5px">
                Mínima
            </th>
            <th class="" style="border-color: #9d9d9d; background-color: #e9ecef !important; padding-left: 5px">
                Delta
            </th>
            <th class=""
                style="border-color: #9d9d9d; background-color: #e9ecef !important; border-radius: 0 18px 0 0; padding-left: 5px">
                Lluvia
            </th>
        </tr>
        </thead>
        <tbody>
        @php
            $prom_minima = 0;
            $prom_maxima = 0;
            $prom_delta = 0;
            $total_lluvia = 0;
        @endphp
        @foreach($listado as $item)
            @php
                $prom_minima += $item->minima;
                $prom_maxima += $item->maxima;
                $prom_delta += ($item->maxima - $item->minima);
                $total_lluvia += $item->lluvia;
            @endphp
            <tr onmouseover="$(this).css('background-color','#e5f7f3 !important');"
                onmouseleave="$(this).css('background-color','');">
                <td class="" style="border-color: #9d9d9d; padding-left: 5px">
                    {{$item->fecha}}
                </td>
                <td class="" style="border-color: #9d9d9d; padding-left: 5px">
                    {{$item->maxima}}
                </td>
                <td class="" style="border-color: #9d9d9d; padding-left: 5px">
                    {{$item->minima}}
                </td>
                <td class="" style="border-color: #9d9d9d; padding-left: 5px">
                    {{$item->maxima - $item->minima}}
                </td>
                <td class="" style="border-color: #9d9d9d; padding-left: 5px">
                    {{$item->lluvia}}
                </td>
            </tr>
        @endforeach
        </tbody>
        <tr>
            <th class="th_yura_green">
                TOTALES
            </th>
            <th class="th_yura_green">
                {{round($prom_maxima / count($listado), 2)}}
            </th>
            <th class="th_yura_green">
                {{round($prom_minima / count($listado), 2)}}
            </th>
            <th class="th_yura_green">
                {{round($prom_delta / count($listado), 2)}}
            </th>
            <th class="th_yura_green">
                {{$total_lluvia}}
            </th>
        </tr>
    </table>

    <script>
        estructura_tabla('table_temperaturas', false, false);
        $('#table_temperaturas_wrapper .row:first').hide()
    </script>

    <style>
        #tr_fijo_0 th {
            position: sticky;
            top: 0;
            z-index: 1;
        }
    </style>
@endif