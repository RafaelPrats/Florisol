<table style="width: 100%" class="table-striped table-bordered">
    @foreach($data as $item)
        <tr>
            <th style="background-color: #e9ecef; border-color: #9d9d9d; padding-left: 5px">
                <div style="width: 150px;">
                    {{$item['variedad']->nombre}}
                </div>
            </th>
            @foreach($item['cosechados'] as $pos_c => $cos)
                <td class="text-center" style="border-color: #9d9d9d;">
                    {{number_format($cos)}}
                </td>
                <td class="text-center" style="border-color: #9d9d9d; border-right: 2px solid">
                    {{number_format($item['proyectados'][$pos_c], 2)}}
                </td>
            @endforeach
        </tr>
    @endforeach
</table>