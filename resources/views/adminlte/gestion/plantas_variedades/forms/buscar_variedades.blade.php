<div style="overflow-y: scroll; overflow-x: scroll; max-height: 500px;">
    <table class="table-striped table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
        <tr class="tr_fija_top_0">
            <th class="text-center th_yura_green" style="width: 60%">
                COLOR
            </th>
            <th class="text-center th_yura_green">
                LONGITUD
            </th>
            <th class="text-center th_yura_green">
                UNIDADES
            </th>
            <th class="text-center th_yura_green">
                PRECIO
            </th>
        </tr>
        @foreach ($listado as $pos => $item)
            <tr id="tr_variedad_{{ $item->id_variedad }}" class="{{ $item->estado == 0 ? 'error' : '' }}">
                <th class="text-center" style="border-color: #9d9d9d">
                    <input type="hidden" class="variedades_listados" value="{{ $item->id_variedad }}">
                    <input type="hidden" id="nombre_planta_{{ $item->id_variedad }}"
                        value="{{ $item->planta->nombre }}">
                    <input type="text" readonly id="nombre_variedad_{{ $item->id_variedad }}" style="width: 100%"
                        class="text-center" value="{{ $item->nombre }}">
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    <input type="number" style="width: 100%" class="text-center" required min="0"
                        id="longitud_{{ $item->id_variedad }}">
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    <input type="number" style="width: 100%" class="text-center" required min="0"
                        id="cantidad_{{ $item->id_variedad }}">
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    <input type="number" style="width: 100%" class="text-center" required min="0"
                        id="precio_{{ $item->id_variedad }}">
                </th>
            </tr>
        @endforeach
    </table>
</div>
