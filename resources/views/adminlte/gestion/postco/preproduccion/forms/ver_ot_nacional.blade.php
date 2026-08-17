<legend class="text-center" style="font-size: 1.3em; margin-bottom: 5px">
    OT de "<b>{{ $caja->cantidad * $detalle->ramos_x_caja }}</b>" ramos de
    "<b>{{ $detalle->longitud_ramo }}cm</b>" en
    la receta
    "<b>{{ $detalle->variedad->nombre }}</b>"
    para "<b>{{ convertDateToText($proyecto->fecha) }}</b>"

    <button type="button" class="btn btn-xs btn-yura_default"
        onclick="exportar_ot_nacional('{{ $detalle->id_detalle_caja_proyecto }}')">
        <i class="fa fa-fw fa-file-excel-o"></i> Exportar
    </button>
</legend>

<div style="overflow-x: scroll">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
        <tr>
            <th class="padding_lateral_5 th_yura_green" rowspan="2">
                Fecha
            </th>
            <th class="text-center th_yura_green" colspan="5">
                Distribucion RECETA ORIGINAL
            </th>
            <th class="text-center th_yura_green" colspan="4">
                VARIEDAD / ESPECIE
            </th>
            <th class="padding_lateral_5 th_yura_green" rowspan="2">
                Total Tallos
            </th>
        </tr>
        <tr>
            <th class="padding_lateral_5 bg-yura_dark">
                PLANTA
            </th>
            <th class="padding_lateral_5 bg-yura_dark">
                VARIEDAD
            </th>
            <th class="padding_lateral_5 bg-yura_dark">
                UNIDADES
            </th>
            <th class="padding_lateral_5 bg-yura_dark">
                TALLOS
            </th>
            <th class="padding_lateral_5 bg-yura_dark">
                TxR
            </th>

            <th class="padding_lateral_5 bg-yura_dark">
                PLANTA
            </th>
            <th class="padding_lateral_5 bg-yura_dark">
                VARIEDAD
            </th>
            <th class="padding_lateral_5 bg-yura_dark">
                LONGITUD
            </th>
            <th class="padding_lateral_5 bg-yura_dark">
                TALLOS
            </th>
        </tr>
        @php
            $getOtNacional = $detalle->getOtNacional();
            $total_row = count($detalle->ot_nacional);
            $tallos_x_ramo = 0;
            $total_tallos = 0;
            foreach ($getOtNacional as $pos) {
                $tallos_x_ramo += $pos['unidades_dist'];
                foreach ($pos['detalles'] as $det) {
                    $total_tallos += $det->tallos;
                }
            }
        @endphp
        @foreach ($getOtNacional as $pos_pos => $pos)
            @foreach ($pos['detalles'] as $pos_det => $det)
                <tr class="tr_pos_{{ $pos['pos'] }} text-sm"
                    onmouseover="$('.tr_pos_{{ $pos['pos'] }}').css('background-color', 'cyan')"
                    onmouseleave="$('.tr_pos_{{ $pos['pos'] }}').css('background-color', '')">
                    @if ($pos_pos == 0 && $pos_det == 0)
                        <th class="padding_lateral_5" style="border-color: #9d9d9d" rowspan="{{ $total_row }}">
                            {{ $pos['fecha'] }}
                        </th>
                    @endif
                    @if ($pos_det == 0)
                        <th class="padding_lateral_5" style="border-color: #9d9d9d"
                            rowspan="{{ count($pos['detalles']) }}">
                            {{ $pos['pta_dist_nombre'] }}
                        </th>
                        <th class="padding_lateral_5" style="border-color: #9d9d9d"
                            rowspan="{{ count($pos['detalles']) }}">
                            {{ $pos['var_dist_nombre'] . ' ' . $pos['longitud_dist'] . 'cm' }}
                        </th>
                        <th class="padding_lateral_5" style="border-color: #9d9d9d"
                            rowspan="{{ count($pos['detalles']) }}">
                            {{ $pos['unidades_dist'] }}
                        </th>
                        <th class="padding_lateral_5" style="border-color: #9d9d9d"
                            rowspan="{{ count($pos['detalles']) }}">
                            {{ $pos['total_tallos_dist'] }}
                        </th>
                    @endif
                    @if ($pos_pos == 0 && $pos_det == 0)
                        <th class="padding_lateral_5" style="border-color: #9d9d9d" rowspan="{{ $total_row }}">
                            {{ $tallos_x_ramo }}
                        </th>
                    @endif
                    <th class="padding_lateral_5" style="border-color: #9d9d9d">
                        {{ $det->pta_nombre }}
                    </th>
                    <th class="padding_lateral_5" style="border-color: #9d9d9d">
                        {{ $det->var_nombre }}
                    </th>
                    <th class="padding_lateral_5" style="border-color: #9d9d9d">
                        {{ $det->longitud }}cm
                    </th>
                    <th class="padding_lateral_5" style="border-color: #9d9d9d">
                        {{ $det->tallos }}
                    </th>
                    @if ($pos_pos == 0 && $pos_det == 0)
                        <th class="padding_lateral_5" style="border-color: #9d9d9d" rowspan="{{ $total_row }}">
                            {{ $total_tallos }}
                        </th>
                    @endif
                </tr>
            @endforeach
        @endforeach
    </table>
</div>
