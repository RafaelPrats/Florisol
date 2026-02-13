<div style="overflow-y: scroll; max-height: 450px">
    <table class="table-bordered" style="border: 1px solid #9d9d9d; width: 100%">
        <tr id="tr_fija_top_0">
            <th class="text-center th_yura_green">
                Fila
            </th>
            <th class="text-center th_yura_green">
                Variedad
            </th>
            <th class="text-center th_yura_green">
                Longitud
            </th>
            <th class="text-center th_yura_green">
                Ramos
            </th>
            <th class="text-center th_yura_green">
                Tallos
            </th>
            <th class="text-center th_yura_green">
                Tallos x Ramo
            </th>
        </tr>
        @foreach ($listado as $pos => $item)
            <tr style="background-color: {{ $pos % 2 == 0 ? '#dddddd' : '' }}"
                class="{{ $item['fallos'] ? 'error' : '' }}">
                <td class="text-center" style="border-color: #9d9d9d">
                    {{ $pos + 2 }}
                    <input type="hidden" class="pos_file" value="{{ $pos }}">
                </td>
                <th class="text-center" style="border-color: #9d9d9d">
                    {{ $item['nombre_variedad'] }}
                    <input type="hidden" id="id_variedad_{{ $pos }}"
                        value="{{ !$item['fallos'] ? $item['model_variedad']->id_variedad : '' }}">
                </th>
                <td class="text-center" style="border-color: #9d9d9d">
                    {{ $item['longitud'] }}cm
                    <input type="hidden" id="longitud_{{ $pos }}" value="{{ $item['longitud'] }}">
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    {{ $item['ramos'] }}
                    <input type="hidden" id="ramos_{{ $pos }}" value="{{ $item['ramos'] }}">
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    {{ $item['tallos'] }}
                    <input type="hidden" id="tallos_{{ $pos }}" value="{{ $item['tallos'] }}">
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    {{ round($item['tallos'] / $item['ramos']) }}
                    <input type="hidden" id="tallos_x_ramo_{{ $pos }}"
                        value="{{ round($item['tallos'] / $item['ramos']) }}">
                </td>
            </tr>
        @endforeach
    </table>
</div>

<div class="text-center" style="margin-top: 5px">
    @if ($fallos)
        <button type="button" class="btn btn-yura_danger" disabled>
            <i class="fa fa-fw fa-ban"></i> Solucione los fallos antes de PROCESAR
        </button>
    @else
        <button type="button" class="btn btn-yura_primary" onclick="store_bajas()">
            <i class="fa fa-fw fa-save"></i> PROCESAR
        </button>
    @endif
</div>

<style>
    #tr_fija_top_0 th {
        position: sticky;
        top: 0;
        z-index: 9;
    }
</style>

<script>
    @if ($fallos)
        alerta(
            '<div class="alert alert-warning text-center">Se han encontrado fallos <sup>(color ROJO)</sup> en el archivo. Soluciónelos antes de procesar.</div>'
        );
    @endif

    function store_bajas() {
        pos_file = $('.pos_file');
        data = [];
        for (i = 0; i < pos_file.length; i++) {
            p = pos_file[i].value;
            id_variedad = $('#id_variedad_' + p).val();
            longitud = $('#longitud_' + p).val();
            ramos = $('#ramos_' + p).val();
            tallos = $('#tallos_' + p).val();
            tallos_x_ramo = $('#tallos_x_ramo_' + p).val();
            data.push({
                id_variedad: id_variedad,
                longitud: longitud,
                ramos: ramos,
                tallos: tallos,
                tallos_x_ramo: tallos_x_ramo,
            });
        }
        datos = {
            _token: '{{ csrf_token() }}',
            data: JSON.stringify(data)
        };
        post_jquery_m('{{ url('reporte_cuarto_frio/store_bajas') }}', datos, function() {
            cerrar_modals();
            listar_reporte();
        })
    }
</script>
