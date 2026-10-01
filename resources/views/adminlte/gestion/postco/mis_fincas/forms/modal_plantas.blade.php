<legend class="text-center" style="margin-bottom: 5px; font-size: 1.3em">
    Plantas de <b>{{ $fincaProveedor->nombre }}</b>
</legend>
<div style="overflow-y: scroll; max-height: 650px">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
        <tr class="tr_fija_top_0">
            <th class="padding_lateral_5 th_yura_green">
                Planta
            </th>
            <th class="padding_lateral_5 th_yura_green" style="width: 60px">
            </th>
        </tr>
        @foreach ($plantas as $pos => $pta)
            <tr style="background-color: {{ $pos % 2 == 0 ? '#dddddd' : '' }}">
                <td class="padding_lateral_5" style="border-color: #9d9d9d">
                    <label for="check_{{ $pta->id_planta }}" class="mouse-hand">
                        {{ $pta->nombre }}
                    </label>
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <input type="checkbox" id="check_{{ $pta->id_planta }}"
                        {{ in_array($pta->id_planta, $mis_plantas) ? 'checked' : '' }}
                        onchange="update_planta_finca('{{ $fincaProveedor->id_finca_proveedor }}', '{{ $pta->id_planta }}')">
                </td>
            </tr>
        @endforeach
    </table>
</div>

<script>
    function update_planta_finca(id_finca, pta) {
        datos = {
            _token: '{{ csrf_token() }}',
            id_finca: id_finca,
            pta: pta
        }
        post_jquery_m('{{ url('mis_fincas/update_planta_finca') }}', datos, function() {});
    }
</script>
