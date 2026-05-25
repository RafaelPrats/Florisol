<table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
    <tr>
        <th class="padding_lateral_5 th_yura_green">
            Segmento
        </th>
        <th class="text-center th_yura_green" style="width: 150px">
            Bodega
        </th>
    </tr>
    @foreach ($segmentos as $segmento)
        <tr>
            <td class="padding_lateral_5" style="border-color: #9d9d9d">
                {{ $segmento->nombre }}
            </td>
            <td class="text-center" style="border-color: #9d9d9d">
                <select style="width: 100%" onchange="update_bodega('{{ $segmento->id_segmento }}', $(this).val())">
                    <option value="V" {{ $segmento->bodega == 'V' ? 'selected' : '' }}>
                        Ventas
                    </option>
                    <option value="P" {{ $segmento->bodega == 'P' ? 'selected' : '' }}>
                        Producción
                    </option>
                </select>
            </td>
        </tr>
    @endforeach
</table>

<script>
    function update_bodega(id_segmento, bodega) {
        datos = {
            _token: '{{ csrf_token() }}',
            id_segmento: id_segmento,
            bodega: bodega,
        }
        post_jquery_m('{{ url('ingreso_inventario/update_bodega') }}', datos, function() {
            //listar_reporte();
        })
    }
</script>
