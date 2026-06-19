<div style="overflow-y: scroll; overflow-x: scroll; max-height: 700px">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
        <tr class="tr_fija_top_0">
            <th class="padding_lateral_5 th_yura_green" style="width: 30px">
                TIPO
            </th>
            <th class="padding_lateral_5 th_yura_green" style="width: 90px">
                FECHA
            </th>
            <th class="padding_lateral_5 th_yura_green" style="width: 100px">
                PLANTA
            </th>
            <th class="padding_lateral_5 th_yura_green" style="width: 220px">
                VARIEDAD
            </th>
            <th class="padding_lateral_5 th_yura_green" style="width: 60px">
                TALLOS
            </th>
            <th class="padding_lateral_5 th_yura_green" style="width: 60px">
                BASURA
            </th>
            <th class="text-center bg-yura_dark">
                OT
            </th>
            <th class="text-center bg-yura_dark">
                SUELTA
            </th>
        </tr>
        @foreach ($listado as $pos => $item)
            <tr onmouseover="$(this).css('background-color', 'cyan')"
                onmouseleave="$(this).css('background-color', '')">
                <th class="text-center" style="border-color: #9d9d9d">
                    @if ($item->tipo == 'INGRESO')
                        <span class="badge bg-yura_primary" title="Ingreso">
                            <i class="fa fa-fw fa-download"></i>
                        </span>
                    @else
                        <span class="badge btn-yura_danger" title="Salida">
                            <i class="fa fa-fw fa-upload"></i>
                        </span>
                    @endif
                </th>
                <th class="padding_lateral_5" style="border-color: #9d9d9d">
                    {{ $item->fecha }}
                </th>
                <th class="padding_lateral_5" style="border-color: #9d9d9d">
                    {{ $item->pta_nombre }}
                </th>
                <th class="padding_lateral_5" style="border-color: #9d9d9d">
                    {{ $item->var_nombre }}
                </th>
                <th class="padding_lateral_5" style="border-color: #9d9d9d">
                    @if ($item->tipo == 'INGRESO')
                        {{ $item->data->tallos_ingresados }}
                    @else
                        {{ $item->data->cantidad }}
                    @endif
                </th>
                <th class="padding_lateral_5" style="border-color: #9d9d9d">
                    @if ($item->tipo == 'SALIDA')
                        {{ $item->data->basura }}
                    @endif
                </th>
                <th class="text-center text-sm" style="border-color: #9d9d9d">
                    @if ($item->tipo == 'SALIDA' && $item->data->id_orden_trabajo != '')
                        #{{ $item->data->id_orden_trabajo }}
                        {{ $item->data->cli_nombre_ot }}
                        <br>
                        <small>{{ $item->data->segmento_ot }}</small>
                    @endif
                </th>
                <th class="text-center text-sm" style="border-color: #9d9d9d">
                    @if ($item->tipo == 'SALIDA')
                        {{ $item->data->cli_nombre_proy }}
                        <br>
                        <small>{{ $item->data->segmento_proy }}</small>
                    @endif
                </th>
            </tr>
        @endforeach
    </table>
</div>
