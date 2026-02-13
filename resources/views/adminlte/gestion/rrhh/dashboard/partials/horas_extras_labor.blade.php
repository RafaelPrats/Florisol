<table class="table-striped table-bordered" width="100%" style="border: 1px solid #9d9d9d">
    <thead>
        <tr>
            <th class="text-left background-color_yura" style="border-color: #9d9d9d; color: white">
                LABOR
            </th>
            @foreach ($manoObras[0]->he_50_semana as $sem => $x )
                <th class="text-left" style="border-color: #9d9d9d; background-color: #fff700">
                    {{$sem}} <sup>HE 50%</sup>
                </th>
            @endforeach
            @foreach ($manoObras[0]->he_100_semana as $sem => $x )
                <th class="text-left" style="border-color: #9d9d9d; background-color: #00e130">
                    {{$sem}} <sup>HE 100%</sup>
                </th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @php 
            $arrData50=[];
            $arrSum50=[];
            $arrData100=[];
            $arrSum100=[];
        @endphp
        @foreach ($manoObras as $mo)
            <tr>
                <td class="text-left" style="border-color: #9d9d9d">{{$mo->nombre}} -  <b>{{$mo->actividad}}</b></td>
                @foreach ($mo->he_50_semana as $sem => $cs)
                    @php $arrData50[$sem][] = $cs @endphp
                    <td class="text-left" style="border-color: #9d9d9d; background-color: #e9ecef">
                        {{$cs}}
                    </td>
                @endforeach
                @foreach ($mo->he_100_semana as $sem => $cs)
                    @php $arrData100[$sem][] = $cs @endphp
                    <td class="text-left" style="border-color: #9d9d9d; background-color: #e9ecef">
                        {{$cs}}
                    </td>
                @endforeach
            </tr>
        @endforeach
        @php
            $arrSum50 = array_values(array_map(function($a) { return array_sum($a); }, $arrData50));
            $arrSum100 = array_values(array_map(function($a) { return array_sum($a); }, $arrData100));
        @endphp
        <tr>
            <td style="border-color: #9d9d9d"><b>TOTALES:</b></td>
            <td class="text-left" style="border-color: #9d9d9d"><b>{{$arrSum50[0]}}</b></td>
            <td class="text-left" style="border-color: #9d9d9d"><b>{{$arrSum50[1]}}</b></td>
            <td class="text-left" style="border-color: #9d9d9d"><b>{{$arrSum50[2]}}</b></td>
            <td class="text-left" style="border-color: #9d9d9d"><b>{{$arrSum50[3]}}</b></td>

            <td class="text-left" style="border-color: #9d9d9d"><b>{{$arrSum100[0]}}</b></td>
            <td class="text-left" style="border-color: #9d9d9d"><b>{{$arrSum100[1]}}</b></td>
            <td class="text-left" style="border-color: #9d9d9d"><b>{{$arrSum100[2]}}</b></td>
            <td class="text-left" style="border-color: #9d9d9d"><b>{{$arrSum100[3]}}</b></td>
        </tr>
    </tbody>
</table>
