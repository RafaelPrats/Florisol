<div class="api-pending-table-wrap">
    <table class="api-pending-table">
        <thead>
            <tr>
                <th>Documento</th>
                <th>Fecha</th>
                <th>Fincas</th>
                <th class="text-center">Detalles pendientes</th>
                <th class="text-center">Acción</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($documentos as $documento)
                <tr>
                    <td>{{ $documento->documento }}</td>
                    <td>{{ $documento->fecha }}</td>
                    <td>{{ $documento->fincas ?: '—' }}</td>
                    <td class="text-center">{{ number_format($documento->cantidad_pendiente) }}</td>
                    <td class="text-center">
                        @if ($documento->puede_eliminar)
                            <button type="button" class="btn btn-xs btn-yura_danger"
                                title="Eliminar documento pendiente"
                                onclick="eliminar_documento_api_pendiente('{{ $documento->id_api_store_cajas }}', this)">
                                <i class="fa fa-trash" aria-hidden="true"></i>
                                Eliminar
                            </button>
                        @else
                            <button type="button" class="btn btn-xs btn-default" disabled
                                title="El documento ya tiene detalles recibidos, ingresos o movimientos asociados">
                                <i class="fa fa-lock" aria-hidden="true"></i>
                                No disponible
                            </button>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="api-pending-table-empty">
                        <i class="fa fa-check-circle" aria-hidden="true"></i>
                        No hay documentos API pendientes por recibir.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
