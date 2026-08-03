<div class="nav-tabs-custom">
    <ul class="nav nav-pills nav-justified">
        <li class="active">
            <a href="#ordenes_trabajo" data-toggle="tab" aria-expanded="false">
                Ordenes de Trabajo
            </a>
        </li>
        <li class="">
            <a href="#flor_solida" data-toggle="tab" aria-expanded="true">
                Flor Solida
            </a>
        </li>
        <li class="">
            <a href="#movimientos" data-toggle="tab" aria-expanded="true">
                Movimientos
            </a>
        </li>
        <li class="">
            <a href="#flor_baja" data-toggle="tab" aria-expanded="true">
                Flor Baja
            </a>
        </li>
    </ul>
    <div class="tab-content no-padding">
        <div class="tab-pane active" id="ordenes_trabajo" style="position: relative">
            <div style="overflow-y: scroll; overflow-x: scroll; max-height: 700px;">
                @include('adminlte/gestion/postco/reporte_salidas/partials/_ordenes_trabajo')
            </div>
        </div>
        <div class="tab-pane" id="flor_solida" style="position: relative">
            <div style="overflow-y: scroll; overflow-x: scroll; max-height: 700px;">
                @include('adminlte/gestion/postco/reporte_salidas/partials/_flor_solida')
            </div>
        </div>
        <div class="tab-pane" id="movimientos" style="position: relative">
            <div style="overflow-y: scroll; overflow-x: scroll; max-height: 700px;">
                @include('adminlte/gestion/postco/reporte_salidas/partials/_movimientos')
            </div>
        </div>
        <div class="tab-pane" id="flor_baja" style="position: relative">
            <div style="overflow-y: scroll; overflow-x: scroll; max-height: 700px;">
                @include('adminlte/gestion/postco/reporte_salidas/partials/_flor_baja')
            </div>
        </div>
    </div>
</div>
