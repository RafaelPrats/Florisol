<div class="row">
    <div class="col-md-6" id="div_content_sectores" style="overflow-x: scroll">
        @include('adminlte.gestion.sectores_modulos.partials.listado_sector')
    </div>
    <div class="col-md-6" id="div_content_modulos">
        <table width="100%" class="table-responsive table-bordered" style="font-size: 0.8em; border-color: #9d9d9d"
               id="table_content_modulos">
            <thead>
            <tr>
                <th class="text-center th_yura_default" style="border-color: #9d9d9d">MÓDULO</th>
                <th class="text-center th_yura_default" style="border-color: #9d9d9d; width: 80px">
                    <button type="button" class="btn btn-xs btn-yura_default" title="Añadir Módulo"
                            onclick="add_modulo()">
                        <i class="fa fa-fw fa-plus"></i>
                    </button>
                </th>
            </tr>
            </thead>
        </table>
    </div>
    {{--<div class="col-md-3" id="div_content_lotes">
        <table width="100%" class="table-responsive table-bordered" style="font-size: 0.8em; border-color: #9d9d9d"
               id="table_content_lotes">
            <thead>
            <tr>
                <th class="text-center th_yura_default" style="border-color: #9d9d9d">LOTE</th>
                <th class="text-center th_yura_default" style="border-color: #9d9d9d">
                    <button type="button" class="btn btn-xs btn-yura_default" title="Añadir Lote"
                            onclick="add_lote()">
                        <i class="fa fa-fw fa-plus"></i>
                    </button>
                </th>
            </tr>
            </thead>
        </table>
    </div>--}}
</div>