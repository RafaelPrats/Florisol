<div class="panel box box-danger" style="margin-top: 10px">
    <div class="box-header with-border">
        <h4 class="box-title pull-right">
            <a data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="false"
               class="collapsed color_text-yura_danger">
                <i class="fa fa-fw fa-exclamation-triangle"></i> Eliminar registros <i class="fa fa-fw fa-exclamation-triangle"></i>
            </a>
        </h4>
    </div>
    <div id="collapseOne" class="panel-collapse collapse" aria-expanded="false" style="height: 0px;">
        <div class="box-body">
            <table style="width: 100%">
                <tr>
                    <td>
                        <div class="input-group">
                            <div class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
                                <i class="fa fa-fw fa-calendar"></i> Desde
                            </div>
                            <input type="date" id="del_desde" style="width: 100%" value="{{hoy()}}" required
                                   class="form-control input-yura_default">
                        </div>
                    </td>
                    <td style="padding-left: 5px">
                        <div class="input-group">
                            <div class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
                                <i class="fa fa-fw fa-calendar"></i> Hasta
                            </div>
                            <input type="date" id="del_hasta" style="width: 100%" value="{{hoy()}}" required
                                   class="form-control input-yura_default">
                            <div class="input-group-btn">
                                <button type="button" class="btn btn-yura_danger" onclick="delete_registros()">
                                    <i class="fa fa-fw fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>