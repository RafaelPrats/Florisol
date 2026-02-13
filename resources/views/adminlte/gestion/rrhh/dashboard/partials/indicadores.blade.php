<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.3/Chart.min.js"></script>
<section class="content">
    <div id="div_indicadores">
        <div class="row">
            <div class="col-md-3">
                <div class="div_indicadores border-radius_16" style="background-color: #30BBBB; margin-bottom: 5px">
                    <legend class="text-center" style="font-size: 1.1em; margin-bottom: 5px; color: white">
                        Personas HA <sup>-4 semanas</sup>
                    </legend>
                    <p style="color: white">Personas
                        <strong class="pull-right">{{$persPorHA}}</strong>
                    </p>
                    <legend style="margin-bottom: 5px; color: white"></legend>
                    <p class="text-center" style="margin-bottom: 0px;margin-top: 26px;">
                        <a href="javascript:void(0)" class="text-center" style="color: white"
                            onclick="desglose_indicador_rrhh('persona_ha')">
                            <strong>Ver más <i class="fa fa-fw fa-arrow-circle-right"></i></strong>
                        </a>
                    </p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="div_indicadores border-radius_16" style="background-color: #30BBBB; margin-bottom: 5px">
                    <div class="text-center" style="font-size: 1.1em; margin-bottom: 5px; color: white">
                        Horas extras <sup>-4 semanas</sup>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <legend class="text-center" style="font-size: 1.1em; margin-bottom: 5px; color: white">
                                50%</legend>
                            <p style="color: white">Cantidad
                                <strong class="pull-right">
                                    {{$horas50}}
                                </strong>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <legend class="text-center" style="font-size: 1.1em; margin-bottom: 5px; color: white">
                                100%</legend>
                            <p style="color: white">Cantidad
                                <strong class="pull-right">
                                    {{$horas100}}
                                </strong>
                            </p>
                        </div>
                        <legend style="margin-bottom: 5px; color: white"></legend>
                        <p class="text-center" style="margin-bottom: 0px">
                            <a href="javascript:void(0)" class="text-center" style="color: white"
                                onclick="desglose_indicador_rrhh('horas_extras')">
                                <strong>Ver más <i class="fa fa-fw fa-arrow-circle-right"></i></strong>
                            </a>
                        </p>
                    </div>
                </div>

            </div>
            <div class="col-md-3">
                <div class="div_indicadores border-radius_16" style="background-color: #30BBBB; margin-bottom: 5px">
                    <legend class="text-center" style="font-size: 1.1em; margin-bottom: 5px; color: white">
                        Costo persona <sup>-4 semanas</sup>
                    </legend>
                    <p style="color: white">Monto
                        <strong class="pull-right">{{$costo_por_persona_4_sem}}</strong>
                    </p>
                    <legend style="margin-bottom: 5px; color: white"></legend>
                    <p class="text-center" style="margin-bottom: 0px;margin-top: 26px;">
                        <a href="javascript:void(0)" class="text-center" style="color: white"
                            onclick="desglose_indicador_rrhh('costo_persona')">
                            <strong>Ver más <i class="fa fa-fw fa-arrow-circle-right"></i></strong>
                        </a>
                    </p>
                </div>
            </div>
        </div>
        <h4 class="box-title">
            <strong>Gráficas</strong>
        </h4>
        <div style="background-color: white; padding: 10px">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group input-group">
                        <span class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                            <i class="fa fa-fw fa-leaf"></i> Labor
                        </span>
                        <select name="filtro_predeterminado_rango" id="filtro_predeterminado_labor"
                            class="form-control input-yura_default">
                            <option value="">Todos</option>
                            @foreach ($manoObra as $mo)
                                <option value="{{$mo->id_mano_obra}}">{{$mo->nombre}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group input-group">
                        <span class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                            <i class="fa fa-fw fa-calendar-check-o"></i> Rango
                        </span>
                        <select name="filtro_predeterminado_rango" id="filtro_predeterminado_rango"
                            class="form-control input-yura_default">
                            <option value="">Seleccione</option>
                            <option value="2">3 Meses</option>
                            <option value="3">6 Meses</option>
                            <option value="4">1 Año</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group input-group">
                        <span class="input-group-btn bg-yura_dark span-input-group-yura-fixed">
                            <button type="button" class="btn btn-sm btn-yura_dark dropdown-toggle bg-gray"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fa fa-calendar-minus-o"></i> Años <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a href="javascript:void(0)" class="li_anno"
                                        id="li_anno_2017">
                                        2017
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:void(0)" class="li_anno"
                                        id="li_anno_2018">
                                        2018
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:void(0)" class="li_anno"
                                        id="li_anno_2019">
                                        2019
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:void(0)" class="li_anno"
                                        id="li_anno_2020">
                                        2020
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:void(0)" class="li_anno"
                                        id="li_anno_2021">
                                        2021
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:void(0)" class="li_anno"
                                        id="li_anno_2022">
                                        2022
                                    </a>
                                </li>
                            </ul>
                        </span>
                        <input type="text" class="form-control input-yura_default" placeholder="Años"
                            id="filtro_predeterminado_annos" name="filtro_predeterminado_annos" readonly="">
                        <span class="input-group-btn">
                            <button type="button" id="btn_filtrar" class="btn btn-yura_primary"
                                onclick="filtrar_graficas_rrhh()" title="Buscar">
                                <i class="fa fa-fw fa-search"></i>
                            </button>
                        </span>
                    </div>
                </div>
            </div>
            <div class="row" id="div_graficas_rrhh"></div>
        </div>
    </div>
</section>
