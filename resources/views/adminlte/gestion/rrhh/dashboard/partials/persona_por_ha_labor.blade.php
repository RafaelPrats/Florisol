<table class="table-striped table-bordered" width="100%" style="border: 1px solid #9d9d9d">
    <thead>
        <tr>
            <th class="text-left background-color_yura" style="border-color: #9d9d9d; color: white">
                LABOR
            </th>
            @foreach ($manoObras[0]->cantidades_x_semana as $sem => $x )
                <th class="text-left" style="border-color: #9d9d9d; background-color: #e9ecef">
                    {{$sem}}    <sup>Pers. x H</sup>
                </th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @php 
            $arrData=[];
            $arrSum=[];
        @endphp
        @foreach ($manoObras as $mo)
            <tr>
                <td class="text-left" style="border-color: #9d9d9d">{{$mo->nombre}} - <b>{{$mo->actividad}}</b></td>
                @foreach ($mo->cantidades_x_semana as $sem => $cs)
                @php $arrData[$sem][] = $cs @endphp
                    <td class="text-left" style="border-color: #9d9d9d; background-color: #e9ecef">
                        {{$cs}}
                    </td>
                @endforeach
            </tr>
        @endforeach
        @php
            $arrSum = array_values(array_map(function($a) { return array_sum($a); }, $arrData));
        @endphp
        <tr>
            <td style="border-color: #9d9d9d"><b>TOTALES:</b></td>
            <td class="text-left" style="border-color: #9d9d9d"><b>{{number_format($arrSum[0],2,'.',',')}}</b></td>
            <td class="text-left" style="border-color: #9d9d9d"><b>{{number_format($arrSum[1],2,'.',',')}}</b></td>
            <td class="text-left" style="border-color: #9d9d9d"><b>{{number_format($arrSum[2],2,'.',',')}}</b></td>
            <td class="text-left" style="border-color: #9d9d9d"><b>{{number_format($arrSum[3],2,'.',',')}}</b></td>
        </tr>
    </tbody>
</table>
