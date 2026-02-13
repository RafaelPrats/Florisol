<table class="table table-stripped table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
    <tr>
        <th class="text-center th_yura_green" style="width: 50%; border-right: 4px solid" colspan="3">
            SIEMBRAS
        </th>
        <th class="text-center th_yura_green" style="width: 50%" colspan="3">
            PODA
        </th>
    </tr>
    <tr>
        <td class="text-center bg-yura_dark" style="width: 16%">
            <strong>{{ count($listado_S) }}</strong> - PLANTAS
        </td>
        <td class="text-center bg-yura_dark" style="width: 16%">
            <strong id="html_cant_tipos_S">...</strong> - TIPOS
        </td>
        <td class="text-center bg-yura_dark" style="width: 16%; border-right: 4px solid">
            <strong id="html_cant_ejecutados_S">...</strong> - EJECUTADOS
        </td>
        <td class="text-center bg-yura_dark" style="width: 16%">
            <strong>{{ count($listado_P) }}</strong> - PLANTAS
        </td>
        <td class="text-center bg-yura_dark" style="width: 16%">
            <strong id="html_cant_tipos_P">...</strong> - TIPOS
        </td>
        <td class="text-center bg-yura_dark" style="width: 16%">
            <strong id="html_cant_ejecutados_P">...</strong> - EJECUTADOS
        </td>
    </tr>
    @php
        $cant_tipos_S = 0;
        $cant_ejecutados_S = 0;
        $cant_tipos_P = 0;
        $cant_ejecutados_P = 0;
    @endphp
    <tr>
        <td class="text-center" id="td_variedades_S" colspan="3">
            <div style="overflow-y: scroll; max-height: 450px">
                <table class="table-stripped table-bordered" style="width: 100%; border: 2px solid black; width: 100%">
                    @foreach ($listado_S as $item)
                        @foreach ($item['variedades'] as $pos_v => $var)
                            @php
                                $cant_tipos_S++;
                                $cant_ejecutados_S += $var->ejecutado == 1 ? 1 : 0;
                            @endphp
                            <tr>
                                @if ($pos_v == 0)
                                    <th class="th_yura_green" rowspan="{{ count($item['variedades']) }}"
                                        style="padding-left: 5px">
                                        {{ $item['planta']->nombre }}
                                    </th>
                                @endif
                                <td class="text-left"
                                    style="border-color: #9d9d9d; padding-left: 5px; background-color: {{ $var->ejecutado == 1 ? '#cdffc6' : '' }}">
                                    {{ $var->nombre }}
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                </table>
            </div>
        </td>
        <td class="text-center" id="td_variedades_P" colspan="3">
            <div style="overflow-y: scroll; max-height: 450px">
                <table class="table-stripped table-bordered" style="width: 100%; border: 2px solid black; width: 100%">
                    @foreach ($listado_P as $item)
                        @foreach ($item['variedades'] as $pos_v => $var)
                            @php
                                $cant_tipos_P++;
                                $cant_ejecutados_P += $var->ejecutado == 1 ? 1 : 0;
                            @endphp
                            <tr>
                                @if ($pos_v == 0)
                                    <th class="th_yura_green" rowspan="{{ count($item['variedades']) }}"
                                        style="padding-left: 5px">
                                        {{ $item['planta']->nombre }}
                                    </th>
                                @endif
                                <td class="text-left"
                                    style="border-color: #9d9d9d; padding-left: 5px; background-color: {{ $var->ejecutado == 1 ? '#cdffc6' : '' }}">
                                    {{ $var->nombre }}
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                </table>
            </div>
        </td>
    </tr>
</table>

<script>
    $('#html_cant_tipos_S').html('{{ $cant_tipos_S }}')
    $('#html_cant_ejecutados_S').html('{{ $cant_ejecutados_S }}')
    $('#html_cant_tipos_P').html('{{ $cant_tipos_P }}')
    $('#html_cant_ejecutados_P').html('{{ $cant_ejecutados_P }}')
</script>
