@php
    $total_variedades = 0;
    $total_disponibles = 0;
    foreach ($listado as $grupo) {
        $total_variedades += $grupo['variedades']->count();
        $total_disponibles += $grupo['variedades']->sum('disponibles');
    }
@endphp

<div class="baja-summary">
    <div class="baja-summary-card">
        <span class="baja-summary-icon"><i class="fa fa-leaf"></i></span>
        <span><strong class="baja-summary-value">{{ count($listado) }}</strong><small class="baja-summary-label">Plantas con
                existencias</small></span>
    </div>
    <div class="baja-summary-card">
        <span class="baja-summary-icon"><i class="fa fa-pagelines"></i></span>
        <span><strong class="baja-summary-value">{{ number_format($total_variedades) }}</strong><small
                class="baja-summary-label">Lotes disponibles</small></span>
    </div>
    <div class="baja-summary-card">
        <span class="baja-summary-icon"><i class="fa fa-cubes"></i></span>
        <span><strong class="baja-summary-value">{{ number_format($total_disponibles) }}</strong><small
                class="baja-summary-label">Tallos disponibles</small></span>
    </div>
</div>

<div class="baja-table-card">
    <div class="baja-table-heading">
        <h3><i class="fa fa-box-open"></i> Inventario disponible</h3>
        <span class="baja-table-note"><i class="fa fa-hand-pointer-o"></i> Selecciona una planta para mostrar sus lotes</span>
    </div>

    @if (count($listado))
        <div class="baja-table-scroll">
            <table class="baja-table">
                <thead>
                    <tr>
                        <th style="width: 115px">Fecha</th>
                        <th>Variedad / Planta</th>
                        <th class="text-center" style="width: 72px">TxR</th>
                        <th class="text-center" style="width: 85px">Longitud</th>
                        <th class="text-right" style="width: 155px">Tallos disponibles</th>
                        <th class="text-center" style="width: 128px">
                            <button type="button" class="btn btn-yura_default" onclick="modal_baja()">
                                <i class="fa fa-plus"></i> Crear orden
                            </button>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($listado as $item)
                        @php
                            $planta_id = $item['planta']->id_planta;
                            $tallos_pta = $item['variedades']->sum('disponibles');
                            $variedades = $item['variedades'];
                        @endphp
                        <tr class="baja-plant-row" role="button" tabindex="0" aria-expanded="false"
                            aria-controls="baja_lotes_{{ $planta_id }}"
                            onclick="toggle_lotes_baja(this, '{{ $planta_id }}')"
                            onkeydown="if(event.key === 'Enter' || event.key === ' '){event.preventDefault();toggle_lotes_baja(this, '{{ $planta_id }}')}">
                            <th colspan="2">
                                <i class="fa fa-caret-right baja-plant-toggle"></i>
                                <i class="fa fa-leaf"></i> {{ $item['planta']->nombre }}
                                <span class="text-muted">({{ $variedades->count() }} lotes)</span>
                            </th>
                            <th colspan="2"></th>
                            <th class="baja-number">{{ number_format($tallos_pta) }}</th>
                            <th></th>
                        </tr>
                        @foreach ($variedades as $var)
                            <tr class="baja-lot-row hidden" data-plant="{{ $planta_id }}"
                                id="baja_lote_{{ $var->id_inventario_recepcion }}">
                                <th>{{ $var->fecha }}</th>
                                <th>{{ $var->nombre }}</th>
                                <th class="text-center">{{ $var->tallos_x_ramo }}</th>
                                <th class="text-center">{{ $var->longitud }}</th>
                                <th>
                                    <input type="number" class="baja-available-input"
                                        id="tallos_disponibles_{{ $var->id_inventario_recepcion }}"
                                        value="{{ $var->disponibles }}" readonly aria-label="Tallos disponibles">
                                </th>
                                <th></th>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="baja-empty"><i class="fa fa-inbox"></i> No se encontraron existencias con los filtros seleccionados.</div>
    @endif
</div>

<script>
    function toggle_lotes_baja(row, planta) {
        var expanded = $(row).attr('aria-expanded') === 'true';
        $(row).attr('aria-expanded', expanded ? 'false' : 'true');
        $('.baja-lot-row[data-plant="' + planta + '"]').toggleClass('hidden', expanded);
    }

    function modal_baja() {
        var datos = {
            bodega: $('#bodega_filtro').val()
        };
        get_jquery('{{ url('botar_inventario/modal_baja') }}', datos, function(retorno) {
            modal_view('modal_modal_baja', retorno,
                '<i class="fa fa-recycle"></i> Crear orden de flor de baja',
                true, false, '{{ isPC() ? '94%' : '100%' }}',
                function() {});
        });
    }
</script>
