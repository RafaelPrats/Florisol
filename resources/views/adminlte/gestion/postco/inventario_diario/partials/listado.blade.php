@php
    $variedades_visibles = $listado->filter(function ($item) {
        return $item->total_saldo > 0;
    });
@endphp

<div class="inventory-summary">
    <div class="inventory-summary-card">
        <span class="inventory-summary-icon"><i class="fa fa-calendar"></i></span>
        <div>
            <span class="inventory-summary-value">{{ count($fechas) }}</span>
            <span class="inventory-summary-label">días en el período</span>
        </div>
    </div>
    <div class="inventory-summary-card">
        <span class="inventory-summary-icon"><i class="fa fa-leaf"></i></span>
        <div>
            <span class="inventory-summary-value">{{ $variedades_visibles->count() }}</span>
            <span class="inventory-summary-label">variedades mostradas</span>
        </div>
    </div>
</div>

<div class="inventory-table-card">
    <div class="inventory-table-heading">
        <h3><i class="fa fa-table text-color_yura"></i> Saldos diarios por variedad</h3>
        <span class="inventory-table-hint">
            Selecciona una variedad para abrir su Kardex
            <i class="fa fa-long-arrow-right"></i>
        </span>
    </div>

    @if ($variedades_visibles->isEmpty() || empty($fechas))
        <div class="inventory-empty">
            <i class="fa fa-inbox"></i>
            <span>No hay saldos para mostrar con los filtros y el período seleccionados.</span>
        </div>
    @else
        <div class="inventory-table-scroll">
            <table class="inventory-table" id="table_listado">
                <thead>
                    <tr>
                        <th class="inventory-plant">Planta</th>
                        <th class="inventory-variety">Variedad</th>
                        @foreach ($fechas as $f)
                            <th class="inventory-date-head" title="{{ $f }}">
                                {{ explode(' del ', convertDateToText($f))[0] }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($variedades_visibles as $item)
                        <tr>
                            <th class="inventory-plant">{{ $item->pta_nombre }}</th>
                            <th class="inventory-variety mouse-hand" title="Abrir Kardex de {{ $item->var_nombre }}"
                                onclick="modal_kardex('{{ $item->id_variedad }}')">
                                {{ $item->var_nombre }}
                            </th>
                            @php
                                $anterior = $item->valores[0]['saldo'];
                            @endphp
                            @foreach ($item->valores as $val)
                                @php
                                    $icon = '';
                                    $clase_cambio = '';
                                    $cambio = '';
                                    if ($anterior > $val['saldo']) {
                                        $icon = '<i class="fa fa-caret-down"></i>';
                                        $clase_cambio = 'inventory-change-down';
                                        $cambio = 'El saldo bajó respecto al día anterior';
                                    } elseif ($anterior < $val['saldo']) {
                                        $icon = '<i class="fa fa-caret-up"></i>';
                                        $clase_cambio = 'inventory-change-up';
                                        $cambio = 'El saldo subió respecto al día anterior';
                                    }
                                    $anterior = $val['saldo'];
                                @endphp
                                <th class="inventory-date-cell {{ $clase_cambio }}" title="{{ $cambio }}">
                                    {{ number_format($val['saldo']) }} {!! $icon !!}
                                </th>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<script>
    function modal_kardex(id_var) {
        datos = {
            variedad: id_var,
            bodega: $('#bodega_filtro').val(),
            desde: $('#desde_filtro').val(),
            hasta: $('#hasta_filtro').val(),
        };
        get_jquery('{{ url('kardex/listar_reporte') }}', datos, function(retorno) {
            modal_view('modal_modal_kardex', retorno,
                '<i class="fa fa-fw fa-list-alt"></i> Kardex',
                true, false, '{{ isPC() ? '90%' : '' }}',
                function() {});
        });
    }
</script>
