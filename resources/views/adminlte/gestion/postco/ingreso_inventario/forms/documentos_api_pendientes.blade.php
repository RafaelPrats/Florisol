<div class="api-pending-modal">
    <div class="api-pending-intro">
        <i class="fa fa-info-circle" aria-hidden="true"></i>
        Se muestran los pendientes de la finca activa. Solo se eliminan detalles en estado P de esa finca sin ingresos ni
        movimientos asociados; los detalles de otras fincas se conservan.
    </div>
    <div id="div_documentos_api_pendientes">
        @include('adminlte.gestion.postco.ingreso_inventario.partials.documentos_api_pendientes')
    </div>
</div>

<style>
    .api-pending-modal {
        color: #003729;
        font-size: 13px;
    }

    .api-pending-intro {
        margin-bottom: 12px;
        padding: 10px 12px;
        border: 1px solid #dce9e4;
        border-radius: 7px;
        background: #f4faf7;
        color: #526b61;
    }

    .api-pending-intro i {
        margin-right: 5px;
        color: #00a87e;
    }

    .api-pending-table-wrap {
        max-height: min(60vh, 520px);
        overflow: auto;
        border: 1px solid #dce9e4;
        border-radius: 7px;
    }

    .api-pending-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .api-pending-table th,
    .api-pending-table td {
        padding: 8px 10px;
        border-bottom: 1px solid #e8efeb;
        text-align: left;
    }

    .api-pending-table th {
        position: sticky;
        top: 0;
        background: #f4faf7;
        color: #003729;
    }

    .api-pending-table .text-center {
        text-align: center;
    }

    .api-pending-table-empty {
        padding: 22px !important;
        color: #71817a;
        text-align: center !important;
    }
</style>
