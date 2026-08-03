<div class="nav-tabs-custom">
    <ul class="nav nav-pills nav-justified">
        <li class="active">
            <a href="#listado_compras" data-toggle="tab" aria-expanded="false">
                Compras
            </a>
        </li>
        <li class="">
            <a href="#documentos" data-toggle="tab" aria-expanded="true">
                Internos
            </a>
        </li>
        <li class="">
            <a href="#movimientos" data-toggle="tab" aria-expanded="true">
                Movimientos
            </a>
        </li>
    </ul>
    <div class="tab-content no-padding">
        <div class="tab-pane active" id="listado_compras" style="position: relative">
            <div style="overflow-y: scroll; overflow-x: scroll; max-height: 700px;">
                @include('adminlte/gestion/postco/reporte_ingresos/partials/_compras')
            </div>
        </div>
        <div class="tab-pane" id="documentos" style="position: relative">
            <div style="overflow-y: scroll; overflow-x: scroll; max-height: 700px;">
                @include('adminlte/gestion/postco/reporte_ingresos/partials/_documentos')
            </div>
        </div>
        <div class="tab-pane" id="movimientos" style="position: relative">
            <div style="overflow-y: scroll; overflow-x: scroll; max-height: 700px;">
                @include('adminlte/gestion/postco/reporte_ingresos/partials/_movimientos')
            </div>
        </div>
    </div>
</div>
