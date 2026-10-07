<div class="clientes-import">
    <div class="clientes-import-note">
        <span><i class="fa fa-file-excel-o"></i></span>
        <div>
            <strong>Importar clientes desde Excel</strong>
            <small>Selecciona un archivo .xls o .xlsx. Podrás revisar los cambios antes de guardarlos.</small>
        </div>
    </div>
    <form id="form-importar_clientes" action="{{ url('clientes/post_importar_clientes') }}" method="POST">
        {!! csrf_field() !!}
        <div class="clientes-import-controls">
            <div class="clientes-import-file">
                <label for="file_clientes">Archivo Excel</label>
                <input type="file" id="file_clientes" name="file_clientes" required class="form-control"
                    accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">
            </div>
            <button type="button" class="btn btn-clientes-import-submit" onclick="importar_clientes()">
                <i class="fa fa-upload"></i> Cargar y revisar
            </button>
        </div>
    </form>
    <div id="div_importar_clientes"></div>
</div>

<style>
    .clientes-import {
        --clientes-green: #00B388;
        --clientes-ink: #003729;
        --clientes-muted: #5A7177;
        --clientes-line: #dce9e4;
        color: var(--clientes-ink);
    }

    .clientes-import-note {
        display: flex;
        align-items: center;
        gap: 9px;
        margin-bottom: 11px;
        padding: 10px;
        border: 1px solid var(--clientes-line);
        border-radius: 7px;
        background: #f4faf7;
    }

    .clientes-import-note > span {
        display: grid;
        width: 34px;
        height: 34px;
        flex: 0 0 34px;
        place-items: center;
        border-radius: 8px;
        background: #e4f5ee;
        color: var(--clientes-green);
    }

    .clientes-import-note strong,
    .clientes-import-note small {
        display: block;
    }

    .clientes-import-note strong {
        font-size: 12px;
    }

    .clientes-import-note small {
        margin-top: 3px;
        color: var(--clientes-muted);
        font-size: 10px;
    }

    .clientes-import-controls {
        display: flex;
        align-items: end;
        gap: 9px;
    }

    .clientes-import-file {
        min-width: 0;
        flex: 1 1 auto;
    }

    .clientes-import-file label {
        display: block;
        margin-bottom: 4px;
        font-size: 11px;
        font-weight: 700;
    }

    .clientes-import-file .form-control {
        height: auto;
        min-height: 35px;
        border-color: var(--clientes-line);
        box-shadow: none;
        font-size: 11px;
    }

    .btn-clientes-import-submit {
        min-height: 35px;
        padding: 7px 10px;
        border: 1px solid var(--clientes-green);
        border-radius: 5px;
        background: var(--clientes-green);
        color: #fff;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
    }

    .btn-clientes-import-submit:hover,
    .btn-clientes-import-submit:focus {
        border-color: #008f6c;
        background: #008f6c;
        color: #fff;
    }

    @media (max-width: 520px) {
        .clientes-import-controls {
            align-items: stretch;
            flex-direction: column;
        }
    }
</style>
