<div class="mis-fincas-plantas">
    @php
        $cantidad_plantas_asignadas = 0;
        foreach ($plantas as $pta) {
            if (in_array($pta->id_planta, $mis_plantas)) {
                $cantidad_plantas_asignadas++;
            }
        }
    @endphp

    <div class="mis-fincas-plants-summary">
        <span class="mis-fincas-plants-icon"><i class="fa fa-leaf"></i></span>
        <div class="mis-fincas-plants-title">
            <small>Finca seleccionada</small>
            <strong>{{ $fincaProveedor->nombre }}</strong>
        </div>
        <div class="mis-fincas-plants-counter">
            <strong id="plantas_asignadas_count">{{ $cantidad_plantas_asignadas }}</strong>
            <span>asignadas</span>
        </div>
    </div>

    <div class="mis-fincas-plants-toolbar">
        <label for="buscar_planta_finca"><i class="fa fa-search"></i> Buscar planta</label>
        <input type="search" id="buscar_planta_finca" class="form-control"
            placeholder="Escribe el nombre de una planta...">
        <span class="mis-fincas-plants-total">{{ count($plantas) }} disponibles</span>
    </div>

    <div class="mis-fincas-plants-list">
        @forelse ($plantas as $pta)
            @php
                $asignada = in_array($pta->id_planta, $mis_plantas);
            @endphp
            <label class="mis-fincas-plant-option {{ $asignada ? 'is-assigned' : '' }}"
                data-plant-search="{{ $pta->nombre }}"
                for="check_{{ $pta->id_planta }}">
                <span class="mis-fincas-plant-check">
                    <input type="checkbox" id="check_{{ $pta->id_planta }}"
                        {{ $asignada ? 'checked' : '' }}
                        onchange="update_planta_finca('{{ $fincaProveedor->id_finca_proveedor }}', '{{ $pta->id_planta }}', this)">
                </span>
                <span class="mis-fincas-plant-leaf"><i class="fa fa-leaf"></i></span>
                <span class="mis-fincas-plant-name">{{ $pta->nombre }}</span>
                <span class="mis-fincas-plant-state">
                    {{ $asignada ? 'Asignada' : 'No asignada' }}
                </span>
            </label>
        @empty
            <div class="mis-fincas-plants-empty">
                <i class="fa fa-info-circle"></i>
                No hay plantas activas disponibles para asignar.
            </div>
        @endforelse
        <div class="mis-fincas-plants-empty hidden" id="sin_resultado_planta">
            <i class="fa fa-search"></i> No hay plantas que coincidan con la búsqueda.
        </div>
    </div>

    <div class="mis-fincas-plants-footer">
        <i class="fa fa-info-circle"></i>
        Marca o desmarca una planta para actualizar su asignación a esta finca.
    </div>
</div>

<style>
    .mis-fincas-plantas {
        --plants-green: #00B388;
        --plants-ink: #003729;
        --plants-muted: #5A7177;
        --plants-line: #dce9e4;
        color: var(--plants-ink);
        font-size: 13px;
    }

    .mis-fincas-plants-summary {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 11px 12px;
        border: 1px solid var(--plants-line);
        border-radius: 9px;
        background: #f4faf7;
    }

    .mis-fincas-plants-icon {
        display: grid;
        width: 38px;
        height: 38px;
        flex: 0 0 38px;
        place-items: center;
        border-radius: 10px;
        background: #e4f5ee;
        color: var(--plants-green);
        font-size: 16px;
    }

    .mis-fincas-plants-title {
        display: flex;
        min-width: 0;
        flex: 1 1 auto;
        flex-direction: column;
        gap: 2px;
    }

    .mis-fincas-plants-title small {
        color: var(--plants-muted);
        font-size: 10px;
    }

    .mis-fincas-plants-title strong {
        overflow: hidden;
        font-size: 14px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .mis-fincas-plants-counter {
        flex: 0 0 auto;
        text-align: right;
    }

    .mis-fincas-plants-counter strong,
    .mis-fincas-plants-counter span {
        display: block;
    }

    .mis-fincas-plants-counter strong {
        color: var(--plants-ink);
        font-size: 18px;
        line-height: 1;
    }

    .mis-fincas-plants-counter span {
        margin-top: 3px;
        color: var(--plants-muted);
        font-size: 10px;
    }

    .mis-fincas-plants-toolbar {
        display: grid;
        grid-template-columns: auto minmax(140px, 1fr) auto;
        align-items: center;
        gap: 9px;
        margin: 11px 0 8px;
    }

    .mis-fincas-plants-toolbar label {
        margin: 0;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
    }

    .mis-fincas-plants-toolbar label i {
        color: var(--plants-green);
    }

    .mis-fincas-plants-toolbar .form-control {
        height: 33px;
        border-color: var(--plants-line);
        border-radius: 5px;
        box-shadow: none;
        font-size: 12px;
    }

    .mis-fincas-plants-toolbar .form-control:focus {
        border-color: var(--plants-green);
        box-shadow: 0 0 0 2px rgba(0, 179, 136, .1);
    }

    .mis-fincas-plants-total {
        color: var(--plants-muted);
        font-size: 10px;
        white-space: nowrap;
    }

    .mis-fincas-plants-list {
        display: grid;
        max-height: min(54vh, 450px);
        gap: 5px;
        overflow-y: auto;
        padding: 1px 4px 1px 1px;
    }

    .mis-fincas-plant-option {
        display: flex;
        min-height: 42px;
        align-items: center;
        gap: 9px;
        margin: 0;
        padding: 6px 9px;
        border: 1px solid var(--plants-line);
        border-radius: 7px;
        background: #fff;
        cursor: pointer;
        transition: background .15s ease, border-color .15s ease;
    }

    .mis-fincas-plant-option:hover {
        border-color: #adddcc;
        background: #f7fbf9;
    }

    .mis-fincas-plant-option.is-assigned {
        border-color: #c4e8d8;
        background: #f2faf6;
    }

    .mis-fincas-plant-check {
        display: grid;
        width: 20px;
        flex: 0 0 20px;
        place-items: center;
    }

    .mis-fincas-plant-check input {
        width: 16px;
        height: 16px;
        margin: 0;
        accent-color: var(--plants-green);
        cursor: pointer;
    }

    .mis-fincas-plant-leaf {
        display: grid;
        width: 28px;
        height: 28px;
        flex: 0 0 28px;
        place-items: center;
        border-radius: 8px;
        background: #eaf6f0;
        color: var(--plants-green);
        font-size: 11px;
    }

    .mis-fincas-plant-name {
        min-width: 0;
        flex: 1 1 auto;
        overflow: hidden;
        color: var(--plants-ink);
        font-size: 12px;
        font-weight: 600;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .mis-fincas-plant-state {
        flex: 0 0 auto;
        color: var(--plants-muted);
        font-size: 10px;
    }

    .mis-fincas-plant-option.is-assigned .mis-fincas-plant-state {
        color: #147452;
        font-weight: 700;
    }

    .mis-fincas-plants-empty {
        padding: 20px 12px;
        color: var(--plants-muted);
        text-align: center;
        font-size: 12px;
    }

    .mis-fincas-plants-empty i {
        margin-right: 4px;
        color: var(--plants-green);
    }

    .mis-fincas-plants-footer {
        margin-top: 9px;
        padding-top: 9px;
        border-top: 1px solid var(--plants-line);
        color: var(--plants-muted);
        font-size: 10px;
    }

    .mis-fincas-plants-footer i {
        margin-right: 3px;
        color: var(--plants-green);
    }

    @media (max-width: 520px) {
        .mis-fincas-plants-toolbar {
            grid-template-columns: minmax(0, 1fr) auto;
        }

        .mis-fincas-plants-toolbar label {
            grid-column: 1 / -1;
        }

        .mis-fincas-plant-option {
            gap: 6px;
            padding-right: 7px;
            padding-left: 7px;
        }
    }
</style>

<script>
    function actualizar_conteo_plantas() {
        var asignadas = $('.mis-fincas-plant-option input:checked').length;
        $('#plantas_asignadas_count').text(asignadas);
    }

    function filtrar_plantas_finca() {
        var busqueda = $.trim($('#buscar_planta_finca').val().toLowerCase());
        var visibles = 0;
        $('.mis-fincas-plant-option').each(function() {
            var coincide = $(this).attr('data-plant-search').toLowerCase().indexOf(busqueda) !== -1;
            $(this).toggleClass('hidden', !coincide);
            if (coincide) {
                visibles++;
            }
        });
        $('#sin_resultado_planta').toggleClass('hidden', visibles > 0 || busqueda === '');
    }

    $('#buscar_planta_finca').on('input', filtrar_plantas_finca);

    function update_planta_finca(id_finca, pta, checkbox) {
        var opcion = $(checkbox).closest('.mis-fincas-plant-option');
        var asignada = $(checkbox).prop('checked');
        opcion.toggleClass('is-assigned', asignada);
        opcion.find('.mis-fincas-plant-state').text(asignada ? 'Asignada' : 'No asignada');
        actualizar_conteo_plantas();

        var datos = {
            _token: '{{ csrf_token() }}',
            id_finca: id_finca,
            pta: pta
        };
        post_jquery_m('{{ url('mis_fincas/update_planta_finca') }}', datos, function() {});
    }
</script>
