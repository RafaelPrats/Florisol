<style>
    .proyecto-form {
        --proyecto-green: #00a77d;
        --proyecto-green-dark: #176b52;
        --proyecto-green-soft: #eef8f3;
        --proyecto-border: #e3ebe7;
        --proyecto-text: #30483e;
        --proyecto-muted: #788981;
        color: var(--proyecto-text);
        padding: 2px;
    }

    .proyecto-form .proyecto-card {
        margin-bottom: 14px;
        overflow: hidden;
        border: 1px solid var(--proyecto-border);
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 2px 8px rgba(30, 66, 47, .05);
    }

    .proyecto-form .proyecto-card-heading {
        display: flex;
        min-height: 56px;
        align-items: center;
        gap: 10px;
        padding: 10px 14px;
        border-bottom: 1px solid #edf2ef;
        background: #fcfdfc;
    }

    .proyecto-form .proyecto-card-icon {
        display: inline-flex;
        width: 34px;
        height: 34px;
        flex: 0 0 34px;
        align-items: center;
        justify-content: center;
        border-radius: 9px;
        background: var(--proyecto-green-soft);
        color: var(--proyecto-green-dark);
        font-size: 15px;
    }

    .proyecto-form .proyecto-card-heading h4 {
        margin: 0;
        color: var(--proyecto-text);
        font-size: 14px;
        font-weight: 700;
    }

    .proyecto-form .proyecto-card-heading small {
        display: block;
        margin-top: 3px;
        color: var(--proyecto-muted);
        font-size: 11px;
    }

    .proyecto-form .proyecto-card-body {
        padding: 14px;
    }

    .proyecto-form .proyecto-fields {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
    }

    .proyecto-form .proyecto-field {
        min-width: 0;
    }

    .proyecto-form .proyecto-field label {
        display: block;
        margin-bottom: 6px;
        color: #607269;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .45px;
        text-transform: uppercase;
    }

    .proyecto-form .proyecto-field .form-control,
    .proyecto-form .proyecto-field .select2-container .select2-selection {
        min-height: 38px;
        border: 1px solid #dce7e1;
        border-radius: 7px;
        background-color: #fff;
        box-shadow: none;
        color: var(--proyecto-text);
    }

    .proyecto-form .proyecto-field select.form-control {
        width: 100%;
    }

    .proyecto-form .proyecto-field .form-control:focus {
        border-color: #66b59a;
        box-shadow: 0 0 0 3px rgba(0, 167, 125, .1);
    }

    .proyecto-form .proyecto-field .select2-container {
        width: 100% !important;
    }

    .proyecto-form .proyecto-field .select2-selection--single {
        height: 38px;
        padding: 4px 7px;
    }

    .proyecto-form .proyecto-field .select2-selection__rendered {
        line-height: 28px;
    }

    .proyecto-form .proyecto-field .select2-selection__arrow {
        height: 36px;
    }

    .proyecto-form .proyecto-field-inline {
        display: flex;
        align-items: end;
        gap: 6px;
    }

    .proyecto-form .proyecto-field-inline .form-control {
        min-width: 0;
        flex: 1 1 auto;
    }

    .proyecto-form .proyecto-spec-button {
        height: 38px;
        flex: 0 0 auto;
        border: 1px solid #d4e8dd;
        border-radius: 7px;
        background: #f1f8f4;
        color: var(--proyecto-green-dark);
    }

    .proyecto-form .proyecto-spec-button:hover,
    .proyecto-form .proyecto-spec-button:focus {
        border-color: var(--proyecto-green);
        background: var(--proyecto-green);
        color: #fff;
    }

    .proyecto-form .proyecto-schedule {
        display: flex;
        align-items: stretch;
        gap: 14px;
    }

    .proyecto-form .proyecto-schedule-menu {
        width: 210px;
        flex: 0 0 210px;
        margin: 0;
        border: 0;
        border-radius: 8px;
        box-shadow: none;
    }

    .proyecto-form .proyecto-schedule-menu .list-group-item {
        border-color: #e7eeea;
        color: #53665c;
        font-size: 12px;
    }

    .proyecto-form .proyecto-schedule-menu .list-group-item:first-child {
        border-radius: 8px 8px 0 0;
        background: var(--proyecto-green-dark);
        color: #fff;
        font-weight: 700;
    }

    .proyecto-form .proyecto-schedule-menu a:hover {
        background: var(--proyecto-green-soft);
        color: var(--proyecto-green-dark);
    }

    .proyecto-form .proyecto-schedule-options {
        min-width: 0;
        flex: 1 1 auto;
        padding: 2px 0;
    }

    .proyecto-form .proyecto-tabs {
        margin: 0;
        padding: 0 10px;
        border-bottom: 1px solid var(--proyecto-border);
        background: #f8fbf9;
    }

    .proyecto-form .proyecto-tabs > li > a {
        margin: 0;
        padding: 13px 16px;
        border: 0;
        border-bottom: 3px solid transparent;
        border-radius: 0;
        color: #728279;
        font-size: 12px;
        font-weight: 700;
    }

    .proyecto-form .proyecto-tabs > li > a:hover,
    .proyecto-form .proyecto-tabs > li > a:focus {
        background: transparent;
        color: var(--proyecto-green-dark);
    }

    .proyecto-form .proyecto-tabs > li.active > a,
    .proyecto-form .proyecto-tabs > li.active > a:hover,
    .proyecto-form .proyecto-tabs > li.active > a:focus {
        border: 0;
        border-bottom: 3px solid var(--proyecto-green);
        background: transparent;
        color: var(--proyecto-green-dark);
    }

    .proyecto-form .proyecto-tabs .badge {
        margin-left: 4px;
        background: #e5f3eb;
        color: var(--proyecto-green-dark);
        font-size: 10px;
    }

    .proyecto-form .proyecto-tab-content {
        padding: 12px;
    }

    .proyecto-form #tab-combos,
    .proyecto-form #tab-contenido_pedido {
        max-height: 540px;
        overflow: auto;
    }

    .proyecto-form #tab-combos > div,
    .proyecto-form #tab-contenido_pedido > div {
        overflow: auto !important;
        max-height: inherit;
        border: 1px solid var(--proyecto-border);
        border-radius: 8px;
    }

    .proyecto-form table.table-bordered,
    .proyecto-form #table_form_contenido_pedido {
        border-color: #e1e9e4 !important;
        font-size: 11px !important;
    }

    .proyecto-form #table_form_contenido_pedido {
        min-width: 1000px;
    }

    .proyecto-form table th,
    .proyecto-form #table_form_contenido_pedido th {
        position: sticky;
        top: 0;
        z-index: 2;
        padding: 8px 6px !important;
        border-color: #dfe9e3 !important;
        background: #edf6f0;
        color: #476454;
        font-size: 10px;
        letter-spacing: .3px;
        white-space: nowrap;
    }

    .proyecto-form table td,
    .proyecto-form #table_form_contenido_pedido td {
        padding: 6px !important;
        border-color: #e8eeea !important;
        vertical-align: middle !important;
    }

    .proyecto-form table input.form-control,
    .proyecto-form table input[type="number"],
    .proyecto-form table input[type="text"],
    .proyecto-form table select,
    .proyecto-form #table_form_contenido_pedido input,
    .proyecto-form #table_form_contenido_pedido select {
        min-width: 58px;
        height: 32px !important;
        padding: 4px 6px;
        border: 1px solid #dce6e0;
        border-radius: 5px;
        background: #fff;
        box-shadow: none;
        font-size: 11px;
    }

    .proyecto-form table input:focus,
    .proyecto-form table select:focus {
        border-color: #66b59a;
        outline: 0;
        box-shadow: 0 0 0 2px rgba(0, 167, 125, .1);
    }

    .proyecto-form .proyecto-summary {
        display: flex;
        align-items: stretch;
        gap: 12px;
        margin: 14px 0 2px;
        padding: 13px;
        border: 1px solid #dceae1;
        border-radius: 11px;
        background: linear-gradient(120deg, #f4faf6, #fff);
    }

    .proyecto-form .proyecto-summary-metrics {
        display: flex;
        flex: 1 1 auto;
        flex-wrap: wrap;
        gap: 8px;
    }

    .proyecto-form .proyecto-summary-metric {
        min-width: 90px;
        flex: 1 1 90px;
        padding: 7px 10px;
        border-right: 1px solid #e1ebe4;
    }

    .proyecto-form .proyecto-summary-metric:last-child {
        border-right: 0;
    }

    .proyecto-form .proyecto-summary-metric span {
        display: block;
        margin-bottom: 4px;
        color: #75877c;
        font-size: 9px;
        font-weight: 700;
        letter-spacing: .4px;
        text-transform: uppercase;
    }

    .proyecto-form .proyecto-summary-metric strong {
        color: #294739;
        font-size: 18px;
        font-weight: 700;
    }

    .proyecto-form .proyecto-summary-metric.proyecto-summary-money strong {
        color: var(--proyecto-green-dark);
        font-size: 20px;
    }

    .proyecto-form .proyecto-summary-actions {
        display: flex;
        flex: 0 0 auto;
        align-items: center;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 7px;
    }

    .proyecto-form .proyecto-summary-actions .btn {
        min-height: 36px;
        border-radius: 7px;
        font-size: 11px;
        font-weight: 700;
    }

    .proyecto-form .proyecto-summary-actions .btn-yura_default {
        border: 1px solid #d8e3dc;
        background: #fff;
    }

    @media (max-width: 767px) {
        .proyecto-form .proyecto-card-body,
        .proyecto-form .proyecto-tab-content {
            padding: 10px;
        }

        .proyecto-form .proyecto-field {
            min-width: 0;
        }

        .proyecto-form .proyecto-fields {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .proyecto-form .proyecto-schedule {
            flex-direction: column;
        }

        .proyecto-form .proyecto-schedule-menu {
            width: 100%;
            flex-basis: auto;
        }

        .proyecto-form .proyecto-tabs {
            display: flex;
            overflow-x: auto;
            white-space: nowrap;
        }

        .proyecto-form .proyecto-summary {
            flex-direction: column;
        }

        .proyecto-form .proyecto-summary-actions {
            justify-content: stretch;
        }

        .proyecto-form .proyecto-summary-actions .btn {
            flex: 1 1 150px;
        }
    }

    @media (max-width: 480px) {
        .proyecto-form .proyecto-field {
            min-width: 0;
        }

        .proyecto-form .proyecto-fields {
            grid-template-columns: minmax(0, 1fr);
        }
    }
</style>
