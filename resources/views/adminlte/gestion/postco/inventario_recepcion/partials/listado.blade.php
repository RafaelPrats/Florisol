<div class="reception-summary">
    <div class="reception-summary-card">
        <span class="reception-summary-icon"><i class="fa fa-leaf"></i></span>
        <div>
            <span class="reception-summary-value">{{ $variedades->count() }}</span>
            <span class="reception-summary-label">variedades con existencia</span>
        </div>
    </div>
    <div class="reception-summary-card">
        <span class="reception-summary-icon"><i class="fa fa-cubes"></i></span>
        <div>
            <span class="reception-summary-value">{{ number_format($total_tallos) }}</span>
            <span class="reception-summary-label">tallos disponibles en el período</span>
        </div>
    </div>
    <div class="reception-summary-card">
        <span class="reception-summary-icon"><i class="fa fa-calendar"></i></span>
        <div>
            <span class="reception-summary-value">10</span>
            <span class="reception-summary-label">días y acumulado anterior</span>
        </div>
    </div>
</div>

<div class="reception-table-card">
    <div class="reception-table-heading">
        <h3><i class="fa fa-table text-color_yura"></i> Inventario disponible por variedad</h3>
        <span class="reception-table-hint">Cada fecha se presenta en una columna; “9+ días” agrupa el período anterior.</span>
    </div>

    @if ($variedades->isEmpty())
        <div class="reception-empty">
            <i class="fa fa-inbox"></i>
            <span>No hay existencias para la planta, variedad y bodega seleccionadas.</span>
        </div>
    @else
        <div class="reception-table-scroll">
            <table class="reception-table" id="table_listado">
                <thead>
                    <tr>
                        <th class="reception-plant">Planta</th>
                        <th class="reception-variety">Variedad</th>
                        @foreach ($periodos as $periodo)
                            <th class="reception-date-head" title="{{ $periodo['title'] }}">
                                {{ $periodo['label'] }}
                            </th>
                        @endforeach
                        <th class="reception-total">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($variedades as $variedad)
                        <tr>
                            <th class="reception-plant">{{ $variedad->pta_nombre }}</th>
                            <th class="reception-variety">{{ $variedad->var_nombre }}</th>
                            @foreach ($variedad->valores as $valor)
                                <th class="reception-date-cell">
                                    {{ $valor > 0 ? number_format($valor) : '' }}
                                </th>
                            @endforeach
                            <th class="reception-total">{{ number_format($variedad->total_tallos) }}</th>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="2">TOTALES</th>
                        @foreach ($totales as $total)
                            <th class="reception-date-cell">{{ number_format($total) }}</th>
                        @endforeach
                        <th>{{ number_format($total_tallos) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif
</div>

<script>
    function mover_inventario(id) {
        var datos = {
            id: id,
        };
        get_jquery('{{ url('ingreso_inventario/mover_inventario') }}', datos, function(retorno) {
            modal_view('modal_mover_inventario', retorno,
                '<i class="fa fa-fw fa-plus"></i> Mover Inventario',
                true, false, '{{ isPC() ? '75%' : '' }}',
                function() {});
        });
    }
</script>
