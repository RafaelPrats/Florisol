<div style="overflow-y: scroll; overflow-x: scroll; height: 500px;">
    <table class="table-striped table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
        <tr class="tr_fija_top_0">
            <th class="text-center th_yura_green">
                CODIGO
            </th>
            <th class="text-center th_yura_green">
                NOMBRE
            </th>
            <th class="text-center th_yura_green">
                UM
            </th>
            <th class="text-center th_yura_green">
                Unidades Fisicas
            </th>
        </tr>
        @foreach ($listado as $item)
            <tr id="tr_producto_{{ $item->id_producto }}" class="{{ $item->estado == 0 ? 'error' : '' }}">
                <th class="padding_lateral_5" style="border-color: #9d9d9d">
                    {{ $item->codigo }}
                </th>
                <th class="padding_lateral_5" style="border-color: #9d9d9d">
                    {{ $item->nombre }}
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    {{ $item->unidad_medida }}
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    {{ $item->disponibles != floor($item->disponibles) ? number_format($item->disponibles, 2) : number_format($item->disponibles) }}
                </th>
            </tr>
        @endforeach
    </table>
</div>