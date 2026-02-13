<div style="overflow-y: scroll; max-height: 450px">
    <table class="table-bordered table-striped" style="width: 100%; border: 1px solid #9d9d9d">
        <tr>
            <th class="text-center th_yura_green columna_fija_left_0" rowspan="2">
                <div style="width: 200px">
                    Semanas / Bandejas
                </div>
            </th>
            @foreach($semanas as $s)
                <th class="text-center bg-yura_dark" colspan="3">
                    {{$s->codigo}}
                </th>
            @endforeach
        </tr>
        <tr>
            @foreach($semanas as $s)
                <th class="text-center bg-yura_dark" style="padding-left: 5px; padding-right: 5px">
                    Ingresos
                </th>
                <th class="text-center bg-yura_dark" style="padding-left: 5px; padding-right: 5px">
                    En Uso
                </th>
                <th class="text-center bg-yura_dark" style="border-right: 2px solid white; padding-left: 5px; padding-right: 5px">
                    Disponibles
                </th>
            @endforeach
        </tr>
        @foreach($data as $d)
            <tr>
                <th class="text-center columna_fija_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
                    {{$d['contenedor']->nombre}}
                </th>
                @foreach($d['valores'] as $v)
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{number_format($v['ingresos'])}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{number_format($v['usando'])}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d; border-right: 2px solid #9d9d9d">
                        {{number_format($v['disponibles'])}}
                    </td>
                @endforeach
            </tr>
        @endforeach
    </table>
</div>

<style>
    .columna_fija_left_0{
        position: sticky;
        left: 0;
        z-index: 9;
    }
</style>