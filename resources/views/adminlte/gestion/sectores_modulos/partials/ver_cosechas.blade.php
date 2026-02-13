<div style="overflow-x: scroll">
    <table class="table-striped table-bordered" width="100%" style="border: 1px solid #9d9d9d; border-radius: 18px 18px 0 0"
           id="table_ver_cosechas">
        <thead>
        <tr style="background-color: #00b388; color: white">
            <th class="text-center th_yura_default" style="border-radius: 18px 0 0 0">
                Fecha Cosecha
            </th>
            <th class="text-center th_yura_default" style="border-radius: 0 18px 0 0">
                Tallos Cosechados módulo: {{$modulo->nombre}}
            </th>
        </tr>
        </thead>
        <tbody>
        @php
            $total_cosecha = 0;
        @endphp
        @foreach($cosechas as $cosecha)
            <tr>
                <td class="text-center" style="border-color: #9d9d9d">
                    {{$cosecha->fecha_ingreso}}
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    @php
                        $getTotalTallosByModulo = $cosecha->getTotalTallosByModulo($modulo->id_modulo);
                        $total_cosecha += $getTotalTallosByModulo;
                    @endphp
                    {{number_format($getTotalTallosByModulo)}}
                </td>
            </tr>
        @endforeach
        </tbody>
        <tr>
            <th class="text-center th_yura_green">Total</th>
            <th class="text-center th_yura_green">{{number_format($total_cosecha)}}</th>
        </tr>
    </table>
</div>

<script>
    estructura_tabla('table_ver_cosechas', false, false);
    $('#table_ver_cosechas_filter label').addClass('text-color_yura');
    $('#table_ver_cosechas_filter label input').addClass('input-yura_white');
</script>