<div style="overflow-y: scroll; overflow-x: scroll; max-height: 700px">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d" id="table_listado">
        <thead>
            <tr class="tr_fija_top_0">
                <th class="padding_lateral_5 bg-yura_dark">
                    Planta
                </th>
                <th class="padding_lateral_5 bg-yura_dark">
                    Variedad
                </th>
                <th class="padding_lateral_5 bg-yura_dark" style="width: 250px">
                    Saldo <i class="fa fa-fw fa-caret-right"></i>{{ convertDateToText($fecha) }}
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach ($listado as $pos => $item)
                @if ($item->saldo > 0)
                    <tr onmouseover="$(this).css('background-color', 'cyan')"
                        onmouseleave="$(this).css('background-color', '')">
                        <th class="padding_lateral_5" style="border-color: #9d9d9d">
                            {{ $item->pta_nombre }}
                        </th>
                        <th class="padding_lateral_5" style="border-color: #9d9d9d">
                            {{ $item->var_nombre }}
                        </th>
                        <th class="padding_lateral_5" style="border-color: #9d9d9d">
                            {{ number_format($item->saldo) }}
                        </th>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>
</div>

<script>
    estructura_tabla('table_listado')
</script>
