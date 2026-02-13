<div class="row">
    <div class="col-md-3">
        <div class="div_indicadores border-radius_16" style="background-color: #30BBBB; margin-bottom: 5px">
            <legend class="text-center" style="font-size: 1.1em; margin-bottom: 5px; color: white">
                <strong>Armados <sup>-4 semanas</sup></strong>
            </legend>
            <table style="width: 100%;">
                @php
                    $total_armados = 0;
                @endphp
                @foreach ($indicadores as $item)
                    @php
                        $total_armados += $item->armados;
                    @endphp
                    <tr>
                        <th style="color: white">
                            {{ $item->semana }}
                        </th>
                        <th class="text-right">
                            {{ number_format($item->armados) }}
                        </th>
                    </tr>
                @endforeach
            </table>
            <legend style="margin-bottom: 5px; color: white"></legend>
            <p class="text-center" style="margin-bottom: 0px">
                <a href="javascript:void(0)" class="text-center" style="color: white">
                    <strong>{{ number_format($total_armados) }}</strong>
                </a>
            </p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="div_indicadores border-radius_16" style="background-color: #30BBBB; margin-bottom: 5px">
            <legend class="text-center" style="font-size: 1.1em; margin-bottom: 5px; color: white">
                <strong>Comprados <sup>-4 semanas</sup></strong>
            </legend>
            <table style="width: 100%;">
                @php
                    $total_comprados = 0;
                @endphp
                @foreach ($indicadores as $item)
                    @php
                        $total_comprados += $item->comprados;
                    @endphp
                    <tr>
                        <th style="color: white">
                            {{ $item->semana }}
                        </th>
                        <th class="text-right">
                            {{ number_format($item->comprados) }}
                        </th>
                    </tr>
                @endforeach
            </table>
            <legend style="margin-bottom: 5px; color: white"></legend>
            <p class="text-center" style="margin-bottom: 0px">
                <a href="javascript:void(0)" class="text-center" style="color: white">
                    <strong>{{ number_format($total_comprados) }}</strong>
                </a>
            </p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="div_indicadores border-radius_16" style="background-color: #30BBBB; margin-bottom: 5px">
            <legend class="text-center" style="font-size: 1.1em; margin-bottom: 5px; color: white">
                <strong>Recibidos <sup>-4 semanas</sup></strong>
            </legend>
            <table style="width: 100%;">
                @php
                    $total_recibidos = 0;
                @endphp
                @foreach ($indicadores as $item)
                    @php
                        $total_recibidos += $item->recibidos;
                    @endphp
                    <tr>
                        <th style="color: white">
                            {{ $item->semana }}
                        </th>
                        <th class="text-right">
                            {{ number_format($item->recibidos) }}
                        </th>
                    </tr>
                @endforeach
            </table>
            <legend style="margin-bottom: 5px; color: white"></legend>
            <p class="text-center" style="margin-bottom: 0px">
                <a href="javascript:void(0)" class="text-center" style="color: white">
                    <strong>{{ number_format($total_recibidos) }}</strong>
                </a>
            </p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="div_indicadores border-radius_16" style="background-color: #30BBBB; margin-bottom: 5px">
            <legend class="text-center" style="font-size: 1.1em; margin-bottom: 5px; color: white">
                <strong>Desechados <sup>-4 semanas</sup></strong>
            </legend>
            <table style="width: 100%;">
                @php
                    $total_desechados = 0;
                @endphp
                @foreach ($indicadores as $item)
                    @php
                        $total_desechados += $item->desechados;
                    @endphp
                    <tr>
                        <th style="color: white">
                            {{ $item->semana }}
                        </th>
                        <th class="text-right">
                            {{ number_format($item->desechados) }}
                        </th>
                    </tr>
                @endforeach
            </table>
            <legend style="margin-bottom: 5px; color: white"></legend>
            <p class="text-center" style="margin-bottom: 0px">
                <a href="javascript:void(0)" class="text-center" style="color: white">
                    <strong>{{ number_format($total_desechados) }}</strong>
                </a>
            </p>
        </div>
    </div>
</div>
