@if(count($ciclos) > 0)
    @php
        $semana_actual = getSemanaByDate(hoy());
        $semana_5_futuro = getSemanaByDate(opDiasFecha('+', 35, hoy()));
    @endphp
    <div id="div_content_fixed">
        <table class="table-striped table-bordered" width="100%" style="border: 1px solid #9d9d9d; border-radius: 18px 18px 0 0;"
               id="table_fenograma_ejecucion">
            <thead>
            <tr style="color: white">
                <th class="fila_fija1" style="border-color: #9d9d9d; padding-left: 5px; border-radius: 18px 0 0 0">
                    Cama
                </th>
                <th class="fila_fija1" style="border-color: #9d9d9d; padding-left: 5px" width="95px">
                    Inicio
                </th>
                <th class="fila_fija1" style="border-color: #9d9d9d; width: 30px; padding-left: 5px">
                    Semana siembra
                </th>
                <th class="fila_fija1" style="border-color: #9d9d9d; width: 30px; padding-left: 5px">
                    Semana actual
                </th>
                <th class="fila_fija1" style="border-color: #9d9d9d; background-color: #00B388; padding-left: 5px">
                    Ptas Iniciales
                </th>
                <th class="fila_fija1" style="border-color: #9d9d9d; padding-left: 5px">
                    Cosecha
                </th>
                <th class="fila_fija1" style="border-color: #9d9d9d; padding-left: 5px">
                    Semana Cosecha
                </th>
                <th class="fila_fija1" style="border-color: #9d9d9d; padding-left: 5px">
                    Esq. x Sem.
                </th>
                <th class="fila_fija1" style="border-color: #9d9d9d; padding-left: 5px">
                    Esq. x Sem. Acum.
                </th>
                <th class="fila_fija1" style="border-color: #9d9d9d; padding-left: 5px">
                    Esq.x Pta.
                </th>
                <th class="fila_fija1" style="border-color: #9d9d9d; background-color: #00B388; padding-left: 5px">
                    Fin Producción
                </th>
            </tr>
            </thead>
            <tbody>
            @php
                $cosechados = 0;
                $ptas_iniciales = 0;
            @endphp
            @foreach($ciclos as $c)
                @php
                    $cosechados += $c->cosecha;
                    $ptas_iniciales += $c->plantas_iniciales;
                @endphp
                <tr>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{$c->cama_nombre}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{$c->fecha_inicio}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{$c->semana_siembra}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{$c->semana_actual}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{$c->plantas_iniciales}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{$c->cosecha}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{$c->semana_cosecha}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{$c->esq_x_sem}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{$c->esq_x_sem_acum}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{$c->esq_x_planta}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d; color: {{$c->fin_produccion <= $semana_actual->codigo ? '#D01C62' : ($c->fin_produccion > $semana_actual->codigo && $c->fin_produccion <= $semana_5_futuro->codigo ? '#EF6E11' : '#00B388')}}">
                        @if($c->fin_produccion != '')
                            {{$c->fin_produccion}}
                        @else
                            <i class="fa fa-fw fa-exclamation-triangle text-red" title="La semana fin, aún no esta programada en el sistema"></i>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
            <tr>
                <th class="text-left th_yura_green" style="border-color: white" colspan="4">
                    Totales
                </th>
                <th class="text-center th_yura_green" style="border-color: white">
                    {{$ptas_iniciales}}
                </th>
                <th class="text-center th_yura_green" style="border-color: white">
                    {{$cosechados}}
                </th>
                <th class="text-center th_yura_green" style="border-color: white" colspan="5">
                </th>
            </tr>
        </table>
    </div>

    <script>
        estructura_tabla('table_fenograma_ejecucion', false, false);
        $('#table_fenograma_ejecucion_filter label').addClass('text-color_yura');
        $('#table_fenograma_ejecucion_filter label input').addClass('input-yura_default');
    </script>

    <style>
        #div_content_fixed {
            overflow-x: scroll;
            overflow-y: scroll;
            width: 100%;
            max-height: 450px;
        }

        #table_fenograma_ejecucion {
            border-spacing: 0 !important;
            border: 1px solid #9d9d9d !important;
        }

        #table_fenograma_ejecucion th, #table_fenograma_ejecucion td {
            border-spacing: 0;
        }

        #table_fenograma_ejecucion thead .fila_fija1 {
            background-color: #00B388 !important;
            border: 1px solid #9d9d9d !important;
            z-index: 9;
            position: sticky;
            top: 0;
        }

        #table_fenograma_ejecucion thead .fila_fija2 {
            background-color: #0b3248 !important;
            border: 1px solid #9d9d9d !important;
            z-index: 9;
            position: sticky;
            top: 0;
        }
    </style>
@else
    <div class="alert alert-info text-center">No se han encontrado coincidencias</div>
@endif