<table style="width: 100%">
    <tr>
        <td style="padding-right: 2px">
            <div class="div_indicadores border-radius_16" style="background-color: #FFFFFF; margin-bottom: 5px">
                <legend class="text-center" style="font-size: 1.1em; margin-bottom: 5px;">Ptas Madres <sup>-4 semanas</sup></legend>
                <p>Productividad
                    <small>(Esq x Pta)</small>
                    <strong class="pull-right">{{$esqueje_x_planta}}</strong>
                </p>
                <legend style="margin-bottom: 5px;"></legend>
                {{--<p class="text-center" style="margin-bottom: 0px">
                    <a href="javascript:void(0)" class="text-center" onclick="desglose_indicador('esqueje_x_planta')">
                        <strong>Ver más <i class="fa fa-fw fa-arrow-circle-right"></i></strong>
                    </a>
                </p>--}}
            </div>
        </td>
        <td style="padding-left: 2px; padding-right: 2px">
            <div class="div_indicadores_md_2 border-radius_16" style="background-color: #30BBBB; margin-bottom: 5px">
                <legend class="text-center" style="font-size: 1.1em; margin-bottom: 5px; color: white">Enraizamiento <sup>-4 semanas</sup>
                </legend>
                <p style="color: white">% Enraizamiento
                    <strong class="pull-right">{{$porcentaje_enraizamiento}}%</strong>
                </p>
                <legend style="margin-bottom: 5px; color: white"></legend>
                {{--<p class="text-center" style="margin-bottom: 0px">
                    <a href="javascript:void(0)" class="text-center" onclick="desglose_indicador('porcentaje_enraizamiento')">
                        <strong>Ver más <i class="fa fa-fw fa-arrow-circle-right"></i></strong>
                    </a>
                </p>--}}
            </div>
        </td>
        <td style="padding-left: 2px; padding-right: 2px">
            <div class="div_indicadores_md_2 border-radius_16" style="background-color: #FFFFFF; margin-bottom: 5px">
                <legend class="text-center" style="font-size: 1.1em; margin-bottom: 5px;">Enraizamiento <sup>-1 semana</sup></legend>
                <p>Requerimientos
                    <strong class="pull-right">{{number_format($requerimientos)}}</strong>
                </p>
                <legend style="margin-bottom: 5px;"></legend>
                {{--<p class="text-center" style="margin-bottom: 0px">
                    <a href="javascript:void(0)" class="text-center" onclick="desglose_indicador('requerimientos')">
                        <strong>Ver más <i class="fa fa-fw fa-arrow-circle-right"></i></strong>
                    </a>
                </p>--}}
            </div>
        </td>
        <td style="padding-left: 2px;">
            <div class="div_indicadores_md_2 border-radius_16" style="background-color: #30BBBB; margin-bottom: 5px">
                <legend class="text-center" style="font-size: 1.1em; margin-bottom: 5px; color: white">Costo Total <sup>-4 semanas</sup></legend>
                <p style="color: white">Costo x Planta
                    <strong class="pull-right">&cent;{{round($costo_x_planta * 100, 2)}}</strong>
                </p>
                <legend style="margin-bottom: 5px; color: white"></legend>
                {{--<p class="text-center" style="margin-bottom: 0px">
                    <a href="javascript:void(0)" class="text-center" style="color: white" onclick="desglose_indicador('costo_x_planta')">
                        <strong>Ver más <i class="fa fa-fw fa-arrow-circle-right"></i></strong>
                    </a>
                </p>--}}
            </div>
        </td>
    </tr>
</table>