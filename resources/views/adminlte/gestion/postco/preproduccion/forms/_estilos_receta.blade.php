<style>
    .receta-ui {
        --receta-primary: #00B388;
        --receta-dark: #5A7177;
        --receta-ink: #003729;
        --receta-border: rgba(90, 113, 119, .2);
        color: var(--receta-ink);
        font-size: 12px;
    }

    .receta-ui .receta-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin: 0 0 12px;
        padding: 12px 14px;
        border: 1px solid rgba(0, 179, 136, .24);
        border-radius: 10px;
        background: linear-gradient(115deg, rgba(0, 179, 136, .09), rgba(90, 113, 119, .045));
    }

    .receta-ui legend {
        margin-bottom: 10px;
        padding: 9px 11px;
        border: 1px solid rgba(0, 179, 136, .2);
        border-radius: 8px;
        background: rgba(0, 179, 136, .06);
        color: #003729;
        font-size: 11px !important;
        line-height: 1.5;
    }

    .receta-ui .receta-hero-title {
        display: flex;
        min-width: 0;
        align-items: center;
        gap: 10px;
    }

    .receta-ui .receta-hero-icon {
        display: inline-flex;
        width: 36px;
        height: 36px;
        flex: 0 0 36px;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        background: #00B388;
        color: #fff;
        font-size: 15px;
    }

    .receta-ui .receta-hero h4 {
        margin: 0;
        color: #003729;
        font-size: 14px;
        font-weight: 700;
    }

    .receta-ui .receta-hero small {
        display: block;
        margin-top: 3px;
        color: #5A7177;
        font-size: 10px;
    }

    .receta-ui .receta-hero-metrics {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .receta-ui .receta-metric {
        padding: 6px 9px;
        border: 1px solid rgba(90, 113, 119, .16);
        border-radius: 7px;
        background: #fff;
        color: #5A7177;
        font-size: 9px;
        white-space: nowrap;
    }

    .receta-ui .receta-metric strong {
        color: #003729;
        font-size: 12px;
    }

    .receta-ui .receta-panel {
        min-width: 0;
        overflow: hidden;
        margin-bottom: 10px;
        border: 1px solid var(--receta-border);
        border-radius: 9px;
        background: #fff;
    }

    .receta-ui .receta-panel-heading {
        display: flex;
        min-height: 39px;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        padding: 8px 11px;
        border-bottom: 1px solid rgba(90, 113, 119, .13);
        background: #f7faf8;
        color: #003729;
        font-size: 11px;
        font-weight: 700;
    }

    .receta-ui .receta-panel-heading i {
        margin-right: 5px;
        color: #00B388;
    }

    .receta-ui .receta-panel-body {
        min-width: 0;
        padding: 10px;
    }

    .receta-ui .receta-table-scroll {
        width: 100%;
        max-height: 54vh;
        overflow: auto;
        border: 1px solid var(--receta-border);
        border-radius: 8px;
        background: #fff;
    }

    .receta-ui table.receta-table {
        width: 100%;
        min-width: 650px;
        margin: 0;
        border: 0 !important;
        border-collapse: separate;
        border-spacing: 0;
        color: #003729;
        font-size: 11px;
    }

    .receta-ui table.receta-table th,
    .receta-ui table.receta-table td {
        padding: 7px 8px !important;
        border: 0 !important;
        border-right: 1px solid rgba(90, 113, 119, .15) !important;
        border-bottom: 1px solid rgba(90, 113, 119, .15) !important;
        vertical-align: middle !important;
    }

    .receta-ui table.receta-table thead th {
        position: sticky;
        top: 0;
        z-index: 3;
        background: #00B388 !important;
        color: #fff !important;
        font-size: 9px;
        font-weight: 700;
        letter-spacing: .35px;
        white-space: nowrap;
        text-transform: uppercase;
    }

    .receta-ui table.receta-table thead th.bg-yura_dark {
        background: #5A7177 !important;
    }

    .receta-ui table.receta-table tbody tr:nth-child(even) {
        background: rgba(90, 113, 119, .04);
    }

    .receta-ui table.receta-table tbody tr:hover {
        background: rgba(0, 179, 136, .09) !important;
    }

    .receta-ui table.receta-table tfoot th,
    .receta-ui table.receta-table tfoot td {
        position: sticky;
        bottom: 0;
        z-index: 2;
        background: #00B388 !important;
        color: #fff !important;
        font-weight: 700;
    }

    .receta-ui table.receta-table .form-control,
    .receta-ui table.receta-table input[type="number"],
    .receta-ui table.receta-table input[type="text"],
    .receta-ui table.receta-table select {
        width: 100%;
        min-width: 56px;
        height: 32px;
        padding: 4px 7px;
        border: 1px solid #707070;
        border-radius: 18px;
        background: #F7F8F8;
        color: #003729;
        box-shadow: none;
        font-size: 11px;
    }

    .receta-ui .form-control:focus,
    .receta-ui input[type="number"]:focus,
    .receta-ui input[type="text"]:focus,
    .receta-ui select:focus {
        border-color: #00B388;
        outline: 0;
        box-shadow: 0 0 0 2px rgba(0, 179, 136, .12);
    }

    .receta-ui .receta-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 7px;
        margin-top: 10px;
    }

    .receta-ui .receta-actions .btn {
        min-height: 34px;
        border-radius: 18px;
        font-size: 10px;
        font-weight: 700;
    }

    .receta-ui .receta-toolbar {
        display: flex;
        align-items: end;
        gap: 8px;
        margin-bottom: 10px;
        padding: 10px;
        border: 1px solid rgba(0, 179, 136, .18);
        border-radius: 8px;
        background: rgba(0, 179, 136, .045);
    }

    .receta-ui .receta-toolbar-field {
        min-width: 0;
        flex: 1 1 auto;
    }

    .receta-ui .receta-toolbar-field label {
        display: block;
        margin-bottom: 5px;
        color: #5A7177;
        font-size: 9px;
        font-weight: 700;
        letter-spacing: .3px;
        text-transform: uppercase;
    }

    .receta-ui .receta-toolbar .select2-container {
        width: 100% !important;
    }

    .receta-ui .receta-empty {
        padding: 20px 12px;
        color: #5A7177;
        font-size: 11px;
        text-align: center;
    }

    .receta-ui .receta-layout-table {
        width: 100%;
        border-spacing: 10px 0;
    }

    .receta-ui .receta-editor-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 104px minmax(0, 1fr);
        align-items: start;
        gap: 12px;
        width: 100%;
        min-width: 0;
    }

    .receta-ui .receta-editor-panel {
        min-width: 0;
        overflow: hidden;
        border: 1px solid var(--receta-border);
        border-radius: 9px;
        background: #fff;
    }

    .receta-ui .receta-editor-panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        min-height: 39px;
        padding: 8px 11px;
        border-bottom: 1px solid rgba(90, 113, 119, .13);
        background: #f7faf8;
        color: #003729;
        font-size: 11px;
        font-weight: 700;
    }

    .receta-ui .receta-editor-panel-header i {
        margin-right: 5px;
        color: #00B388;
    }

    .receta-ui .receta-editor-panel-body {
        min-width: 0;
        padding: 10px;
    }

    .receta-ui .receta-editor-actions {
        display: flex;
        flex-direction: column;
        gap: 8px;
        padding-top: 42px;
    }

    .receta-ui .receta-editor-actions .btn {
        min-height: 38px;
        margin: 0;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 700;
        white-space: normal;
    }

    .receta-ui .receta-editor-actions .receta-save-action {
        box-shadow: 0 3px 9px rgba(0, 179, 136, .2);
    }

    .receta-ui .receta-catalog-filter {
        display: flex;
        align-items: end;
        gap: 8px;
        margin-bottom: 10px;
        padding: 10px;
        border: 1px solid rgba(0, 179, 136, .18);
        border-radius: 8px;
        background: rgba(0, 179, 136, .045);
    }

    .receta-ui .receta-catalog-filter-field {
        min-width: 0;
        flex: 1 1 auto;
    }

    .receta-ui .receta-catalog-filter-field label {
        display: block;
        margin-bottom: 5px;
        color: #5A7177;
        font-size: 9px;
        font-weight: 700;
        letter-spacing: .3px;
        text-transform: uppercase;
    }

    .receta-ui .receta-catalog-filter .btn {
        height: 34px;
        min-width: 38px;
        border-radius: 18px;
    }

    .receta-ui .receta-sort-button {
        padding: 0;
        border: 0;
        background: transparent;
        color: inherit;
        font: inherit;
        letter-spacing: inherit;
        text-transform: inherit;
    }

    .receta-ui .receta-sort-button:hover,
    .receta-ui .receta-sort-button:focus {
        color: #fff;
        text-decoration: underline;
        outline: none;
    }

    .receta-ui .receta-sort-button i {
        margin-left: 4px;
        opacity: .75;
    }

    .receta-ui .receta-layout-scroll {
        width: 100%;
        overflow-x: auto;
    }

    .receta-ui .receta-distribucion-layout {
        min-width: 760px;
    }

    .receta-ui .receta-layout-table > tbody > tr > td {
        padding: 0 4px;
        vertical-align: top;
    }

    .receta-ui .receta-layout-table > tbody > tr > td:first-child {
        min-width: 0;
    }

    .receta-ui .receta-operation-column {
        width: 105px;
        min-width: 105px;
        padding-top: 8px !important;
    }

    .receta-ui .receta-operation-column .btn {
        min-height: 36px;
        margin: 0 0 7px;
        border-radius: 18px;
        font-size: 10px;
        font-weight: 700;
        white-space: normal;
    }

    .receta-ui .receta-total-cards {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 8px;
        margin-bottom: 10px;
    }

    .receta-ui .receta-total-card {
        padding: 9px 10px;
        border: 1px solid rgba(90, 113, 119, .17);
        border-radius: 8px;
        background: #fff;
    }

    .receta-ui .receta-total-card span {
        display: block;
        color: #5A7177;
        font-size: 9px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .receta-ui .receta-total-card strong {
        display: block;
        margin-top: 4px;
        color: #003729;
        font-size: 16px;
        font-variant-numeric: tabular-nums;
    }

    .receta-ui .receta-total-card.receta-total-balance {
        border-color: rgba(0, 179, 136, .25);
        background: rgba(0, 179, 136, .07);
    }

    .receta-ui .receta-total-card.receta-total-balance strong {
        color: #009874;
    }

    .receta-ui .receta-danger {
        color: #d01c62 !important;
        font-weight: 700;
    }

    .receta-confirm {
        padding: 4px;
    }

    .receta-confirm .receta-confirm-message {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px;
        border: 1px solid rgba(239, 110, 17, .24);
        border-radius: 8px;
        background: rgba(239, 110, 17, .07);
        color: #5A7177;
        line-height: 1.5;
    }

    .receta-confirm .receta-confirm-message i {
        color: #ef6e11;
        font-size: 19px;
    }

    @media (max-width: 767px) {
        .receta-ui .col-md-offset-5 {
            margin-left: 0;
        }

        .receta-ui .col-md-7 {
            width: 100%;
        }

        .receta-ui .receta-hero {
            align-items: flex-start;
            flex-direction: column;
        }

        .receta-ui .receta-hero-metrics {
            width: 100%;
        }

        .receta-ui .receta-editor-layout {
            grid-template-columns: minmax(0, 1fr);
            gap: 10px;
        }

        .receta-ui .receta-editor-actions {
            order: 2;
            flex-direction: row;
            padding-top: 0;
        }

        .receta-ui #listado_seleccionados {
            order: 3;
        }

        .receta-ui #listado_productos {
            order: 1;
        }
    }

    @media (max-width: 480px) {
        .receta-ui .receta-total-cards {
            grid-template-columns: minmax(0, 1fr);
        }

        .receta-ui .receta-hero-metrics {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .receta-ui .receta-toolbar {
            align-items: stretch;
            flex-direction: column;
        }

        .receta-ui .receta-catalog-filter {
            align-items: stretch;
        }
    }
</style>
