<div class="report-tabs-card">
    <ul class="nav nav-tabs report-tabs">
        <li class="active">
            <a href="#ordenes_trabajo" data-toggle="tab">
                <span><i class="fa fa-clipboard"></i> Órdenes de trabajo</span>
                <span class="report-tab-count">{{ count($listado_ot) }}</span>
            </a>
        </li>
        <li>
            <a href="#ot_nacional" data-toggle="tab">
                <span><i class="fa fa-globe"></i> OT nacional</span>
                <span class="report-tab-count">{{ count($listado_ot_nacional) }}</span>
            </a>
        </li>
        <li>
            <a href="#flor_solida" data-toggle="tab">
                <span><i class="fa fa-cube"></i> Flor sólida</span>
                <span class="report-tab-count">{{ count($listado_solido) }}</span>
            </a>
        </li>
        <li>
            <a href="#movimientos" data-toggle="tab">
                <span><i class="fa fa-exchange"></i> Movimientos</span>
                <span class="report-tab-count">{{ count($listado_movimientos) }}</span>
            </a>
        </li>
        <li>
            <a href="#flor_baja" data-toggle="tab">
                <span><i class="fa fa-trash-o"></i> Flor baja</span>
                <span class="report-tab-count">{{ count($listado_orden_basura) }}</span>
            </a>
        </li>
        <li>
            <a href="#correcciones" data-toggle="tab">
                <span><i class="fa fa-pencil-square-o"></i> Correcciones</span>
                <span class="report-tab-count">{{ count($listado_corregir) }}</span>
            </a>
        </li>
    </ul>
    <div class="tab-content no-padding">
        <div class="tab-pane active" id="ordenes_trabajo">
            @if (count($listado_ot))
                <div class="report-table-scroll">
                    @include('adminlte/gestion/postco/reporte_salidas/partials/_ordenes_trabajo')
                </div>
            @else
                <div class="report-empty"><i class="fa fa-inbox"></i> No hay órdenes de trabajo para los filtros seleccionados.</div>
            @endif
        </div>
        <div class="tab-pane" id="ot_nacional">
            @if (count($listado_ot_nacional))
                <div class="report-table-scroll">
                    @include('adminlte/gestion/postco/reporte_salidas/partials/_ot_nacional')
                </div>
            @else
                <div class="report-empty"><i class="fa fa-inbox"></i> No hay órdenes nacionales para los filtros seleccionados.</div>
            @endif
        </div>
        <div class="tab-pane" id="flor_solida">
            @if (count($listado_solido))
                <div class="report-table-scroll">
                    @include('adminlte/gestion/postco/reporte_salidas/partials/_flor_solida')
                </div>
            @else
                <div class="report-empty"><i class="fa fa-inbox"></i> No hay salidas de flor sólida para los filtros seleccionados.</div>
            @endif
        </div>
        <div class="tab-pane" id="movimientos">
            @if (count($listado_movimientos))
                <div class="report-table-scroll">
                    @include('adminlte/gestion/postco/reporte_salidas/partials/_movimientos')
                </div>
            @else
                <div class="report-empty"><i class="fa fa-inbox"></i> No hay movimientos para los filtros seleccionados.</div>
            @endif
        </div>
        <div class="tab-pane" id="flor_baja">
            @if (count($listado_orden_basura))
                <div class="report-table-scroll">
                    @include('adminlte/gestion/postco/reporte_salidas/partials/_flor_baja')
                </div>
            @else
                <div class="report-empty"><i class="fa fa-inbox"></i> No hay registros de flor baja para los filtros seleccionados.</div>
            @endif
        </div>
        <div class="tab-pane" id="correcciones">
            @if (count($listado_corregir))
                <div class="report-table-scroll">
                    @include('adminlte/gestion/postco/reporte_salidas/partials/_correcciones')
                </div>
            @else
                <div class="report-empty"><i class="fa fa-inbox"></i> No hay correcciones para los filtros seleccionados.</div>
            @endif
        </div>
    </div>
</div>
