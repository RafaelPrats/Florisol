<div style="overflow-x: scroll; max-height: 700px;">
    <table class="table-bordered" style="border: 1px solid #9d9d9d; width: 100%">
        <tr class="tr_fija_top_0">
            <th class="text-center th_yura_green">
                Cliente
            </th>
            <th class="text-center th_yura_green">
                Tallos
            </th>
            <th class="text-center th_yura_green">
                Ramos
            </th>
        </tr>
        @php
            $tallos_totales = 0;
            $ramos_totales = 0;
        @endphp
        @foreach ($listado as $pos => $item)
            <tr title="Cliente: {{ $item['cliente']->nombre }}">
                <th class="padding_lateral_5"
                    style="border-color: #9d9d9d; background-color: {{ $pos % 2 == 0 ? '#dddddd' : '' }}">
                    {{ $item['cliente']->nombre }}
                </th>
                <th style="vertical-align: top">
                    <table class="table-bordered" style="width: 100%;">
                        @php
                            $total_tallos = 0;
                        @endphp
                        @foreach ($item['tallos'] as $r)
                            @php
                                $total_tallos += $r->tallos;
                                $tallos_totales += $r->tallos;
                            @endphp
                            <tr>
                                <th class="padding_lateral_5"
                                    style="border-color: #9d9d9d; width: 80%; background-color: {{ $pos % 2 == 0 ? '#dddddd' : '' }}">
                                    {{ $r->nombre }}
                                </th>
                                <th class="text-center"
                                    style="border-color: #9d9d9d; background-color: {{ $pos % 2 == 0 ? '#dddddd' : '' }}">
                                    {{ number_format($r->tallos) }}
                                </th>
                            </tr>
                        @endforeach
                        <tr>
                            <th class="padding_lateral_5 bg-yura_dark">
                                Total Tallos
                            </th>
                            <th class="text-center bg-yura_dark">
                                {{ number_format($total_tallos) }}
                            </th>
                        </tr>
                    </table>
                </th>
                <th style="vertical-align: top">
                    <table class="table-bordered" style="width: 100%;">
                        @php
                            $total_ramos = 0;
                        @endphp
                        @foreach ($item['ramos'] as $r)
                            @php
                                $total_ramos += $r->ramos;
                                $ramos_totales += $r->ramos;
                            @endphp
                            <tr>
                                <th class="padding_lateral_5"
                                    style="border-color: #9d9d9d; width: 80%; background-color: {{ $pos % 2 == 0 ? '#dddddd' : '' }}">
                                    {{ $r->nombre }}
                                </th>
                                <th class="text-center"
                                    style="border-color: #9d9d9d; background-color: {{ $pos % 2 == 0 ? '#dddddd' : '' }}">
                                    {{ number_format($r->ramos) }}
                                </th>
                            </tr>
                        @endforeach
                        <tr>
                            <th class="padding_lateral_5 bg-yura_dark">
                                Total Ramos
                            </th>
                            <th class="text-center bg-yura_dark">
                                {{ number_format($total_ramos) }}
                            </th>
                        </tr>
                    </table>
                </th>
            </tr>
        @endforeach
        <tr class="tr_fija_bottom_0">
            <th class="text-center th_yura_green">
                TOTALES
            </th>
            <th class="text-center th_yura_green">
                {{ number_format($tallos_totales) }}
            </th>
            <th class="text-center th_yura_green">
                {{ number_format($ramos_totales) }}
            </th>
        </tr>
    </table>
</div>
