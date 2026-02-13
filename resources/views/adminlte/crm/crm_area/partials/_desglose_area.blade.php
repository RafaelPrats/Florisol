@if(count($ciclos) > 0)
    @php
        $total_variedad = [];
    @endphp
    <div class="box-body" style="overflow-x: scroll">
        <table class="table-striped table-bordered" width="100%" style="border: 1px solid #9d9d9d">
            <thead>
            <tr>
                <th class="text-left background-color_yura"
                    style="border-color: #9d9d9d; color: white">
                    Módulo
                </th>
                <th class="text-left background-color_yura"
                    style="border-color: #9d9d9d; color: white">
                    Fecha Inicio
                </th>
                <th class="text-left background-color_yura"
                    style="border-color: #9d9d9d; color: white">
                    Semana Inicio
                </th>
                @foreach($semanas as $pos_sem => $semana)
                    <th class="text-left"
                        style="border-color: #9d9d9d; background-color: #e9ecef">
                        {{$semana->codigo}}
                    </th>
                    @php
                        $total_variedad[] = 0;
                    @endphp
                @endforeach
            </tr>
            </thead>
            <tbody>
            @foreach($ciclos as $ciclo)
                <tr>
                    <td class="text-left" style="border-color: #9d9d9d">
                        {{$ciclo['ciclo']->modulo->nombre}}
                    </td>
                    <td class="text-left" style="border-color: #9d9d9d">
                        {{$ciclo['ciclo']->fecha_inicio}}
                    </td>
                    <td class="text-left" style="border-color: #9d9d9d">
                        {{getSemanaByDate($ciclo['ciclo']->fecha_inicio)->codigo}}
                    </td>
                    @foreach($ciclo['areas'] as $pos_area => $area)
                        <td class="text-left" style="border-color: #9d9d9d; background-color: #e9ecef"
                            title="Final: '{{$ciclo['ciclo']->fecha_fin}}'">
                            {{number_format($area, 2)}}
                        </td>
                        @php
                            $total_variedad[$pos_area] += $area;
                        @endphp
                    @endforeach
                </tr>
            @endforeach
            </tbody>
            <tr>
                <th class="text-left background-color_yura"
                    style="border-color: #9d9d9d; color: white" colspan="3">
                    Total
                </th>
                @foreach($total_variedad as $valor)
                    <th class="text-left"
                        style="border-color: #9d9d9d; background-color: #e9ecef">
                        {{number_format(round($valor / 1, 2), 2)}}  {{--convertir a hectareas--}}
                    </th>
                @endforeach
            </tr>
        </table>
    </div>
@else
    <div class="alert alert-info text-center">
        No se han encontrado resultados que mostrar
    </div>
@endif