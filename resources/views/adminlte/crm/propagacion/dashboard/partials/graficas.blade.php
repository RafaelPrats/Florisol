<div style="background-color: white; padding: 10px; margin-top: 10px" id="div_graficas">
    <table style="width: 100%">
        <tr>
            <td>
                <div class="form-group input-group">
                        <span class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                            <i class="fa fa-fw fa-calendar-check-o"></i> Rango
                        </span>
                    <select name="filtro_predeterminado_rango" id="filtro_predeterminado_rango" class="form-control input-yura_default"
                            onchange="filtrar_predeterminado()">
                        <option value="12">12 Semanas</option>
                        <option value="24">24 Semanas</option>
                        <option value="52">52 Semanas</option>
                    </select>
                </div>
            </td>
            <td style="padding-left: 10px; padding-right: 10px">
                <div class="form-group input-group">
                    <span class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
                        <i class="fa fa-fw fa-leaf"></i> Variedad
                    </span>
                    <select name="filtro_predeterminado_planta" id="filtro_predeterminado_planta" class="form-control input-yura_default"
                            onchange="select_planta($(this).val(), 'filtro_predeterminado_variedad', 'div_cargar_variedades', '<option value=A>Acumulado</option>')">
                        <option value="" selected>Todas las variedades</option>
                        @foreach($plantas as $p)
                            <option value="{{$p->id_planta}}">{{$p->nombre}}</option>
                        @endforeach
                    </select>
                </div>
            </td>
            <td>
                <div class="form-group input-group" id="div_cargar_variedades">
                    <span class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
                        <i class="fa fa-fw fa-leaf"></i> Tipo
                    </span>
                    <select name="filtro_predeterminado_variedad" id="filtro_predeterminado_variedad" class="form-control input-yura_default">
                        <option value="A" selected>Acumulado</option>
                    </select>
                    <span class="input-group-btn">
                        <button type="button" class="btn btn-yura_primary" onclick="listar_graficas()">
                            <i class="fa fa-fw fa-search"></i>
                        </button>
                    </span>
                </div>
            </td>
        </tr>
    </table>

    <div id="div_cargar_graficas"></div>
</div>