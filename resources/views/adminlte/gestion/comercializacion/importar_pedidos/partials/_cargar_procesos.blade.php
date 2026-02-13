<div style="overflow-y: scroll; height: max-700px;">
    <table class="table table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
        <tr class="tr_fija_top_0">
            <th class="padding_lateral_5 th_yura_green" style="width: 140px">
                FECHA Y HORA
            </th>
            <th class="padding_lateral_5 th_yura_green" style="width: 110px">
                USUARIO
            </th>
            <th class="padding_lateral_5 th_yura_green" style="width: 120px">
                PROGRESO %
            </th>
            <th class="padding_lateral_5 th_yura_green">
                DESCRIPCION
            </th>
        </tr>
        @foreach ($listado as $p)
            <tr>
                <td class="padding_lateral_5" style="border-color: #9d9d9d">
                    {{ $p->fecha_registro }}
                </td>
                <td class="padding_lateral_5" style="border-color: #9d9d9d; font-size: 1.3em">
                    {{ $p->usuario->username }}
                </td>
                <td class="padding_lateral_5" style="border-color: #9d9d9d; font-size: 1.3em">
                    {{ $p->numero }}/{{ $p->total_proceso }}
                    <b><sup>{{ porcentaje($p->numero, $p->total_proceso, 1) }}%</sup></b>
                </td>
                <td class="padding_lateral_5" style="border-color: #9d9d9d">
                    {!! $p->descripcion !!}
                </td>
            </tr>
        @endforeach
    </table>
</div>
