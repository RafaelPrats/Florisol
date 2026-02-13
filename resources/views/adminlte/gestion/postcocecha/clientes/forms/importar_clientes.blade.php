<form id="form-importar_clientes" action="{{ url('clientes/post_importar_clientes') }}" method="POST">
    {!! csrf_field() !!}
    <div class="input-group">
        <span class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
            Archivo
        </span>
        <input type="file" id="file_clientes" name="file_clientes" required class="form-control input-group-addon"
            accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">
        <span class="input-group-btn">
            <button type="button" class="btn btn-yura_primary" onclick="importar_clientes()">
                <i class="fa fa-fw fa-check"></i> Importar Archivo
            </button>
            {{-- 
            <button type="button" class="btn btn-yura_dark" onclick="descargar_plantilla()">
                <i class="fa fa-fw fa-download"></i> Descargar Plantilla
            </button>
             --}}
        </span>
    </div>
</form>

<div style="margin-top: 5px" id="div_importar_clientes"></div>
