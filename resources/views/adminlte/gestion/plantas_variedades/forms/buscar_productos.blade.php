<div style="overflow-y: scroll; overflow-x: scroll; max-height: 500px;">
    <table class="table-striped table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
        <tr class="tr_fija_top_0">
            <th class="text-center th_yura_green">
                CODIGO
            </th>
            <th class="text-center th_yura_green" style="width: 60%">
                PRODUCTO
            </th>
            <th class="text-center th_yura_green">
                UNIDADES
            </th>
        </tr>
        @foreach ($listado as $pos => $item)
        <tr id="tr_producto_{{ $item->id_producto }}" class="{{ $item->estado == 0 ? 'error' : '' }}">
                <th class="text-center" style="border-color: #9d9d9d">
                    {{ $item->codigo }}
                    <input type="hidden" class="productos_listados" value="{{ $item->id_producto }}">
                    <input type="hidden" id="id_producto_{{ $item->id_producto }}" value="{{ $item->id_producto }}">
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    <input type="text" readonly id="nombre_producto_{{ $item->id_producto }}" style="width: 100%"
                        class="text-center" value="{{ $item->nombre }}">
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    <input type="number" style="width: 100%" class="text-center" required min="0"
                        id="cantidad_producto_{{ $item->id_producto }}">
                </th>
            </tr>
        @endforeach
    </table>
</div>
