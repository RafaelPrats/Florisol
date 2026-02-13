@if(count($listado) > 0)
    <div style="overflow-x: scroll; overflow-y: scroll; max-height: 450px">
        <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d" id="table_fenograma_perennes">
            <thead>
            <tr id="tr_fijo_top_0">
                <th class="text-center th_yura_green" style="border-radius: 18px 0 0 0">
                    <div style="width: 150px">
                        Variedad
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 80px">
                        Área m<sup>2</sup>
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 90px">
                        Ptas Iniciales
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 90px">
                        Densidad/m<sup>2</sup>
                    </div>
                </th>

                <th class="text-center th_yura_green">
                    <div style="width: 80px">
                        Tallos Proy/m<sup>2</sup>/año
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 80px">
                        Tallos Proy/m<sup>2</sup>/sem.
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 90px">
                        Tallos Proy.
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 90px">
                        Tallos Proy. Acum. Año
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 90px">
                        Tallos Proy. Acum. 52
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 80px">
                        Tallos Cos.
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 80px">
                        Tallos Cos. Acum. Año
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 80px">
                        Tallos Cos. Acum. 52
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 100px">
                        % Cump. Sem.
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 100px">
                        % Cump. Acum.
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 80px">
                        Tallos/m<sup>2</sup> Ejec.
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 80px">
                        Tallos/m<sup>2</sup> Ejec. Acum.
                    </div>
                </th>
                <th class="text-center th_yura_green" style="border-radius: 0 18px 0 0">
                    <div style="width: 100px">
                        Tallos/m<sup>2</sup>/año (52 sem.)
                    </div>
                </th>
            </tr>
            </thead>
            <tbody>
            @foreach($listado as $pos => $item)
                <tr style="background-color: {{$pos % 2 == 0 ? '#e9ecef' : ''}}">
                    <th class="text-center" style="border-color: #9d9d9d;">
                        <a href="javascript:void(0)" onclick="seleccionar_planta('{{$item['planta']->id_planta}}')" class="text-black">
                            {{$item['planta']->nombre}}
                        </a>
                    </th>
                    <td class="text-center" style="border-color: #9d9d9d;">
                        {{number_format($item['area'], 2)}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d;">
                        {{number_format($item['plantas_iniciales'])}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d;">
                        {{$item['densidad']}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d;">
                        {{$item['tallos_m2_anno']}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d;">
                        {{$item['tallos_m2_semana']}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d;">
                        {{number_format($item['total_proyectados'], 2)}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d;">
                        {{number_format($item['proy_acum_anual'], 2)}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d;">
                        {{number_format($item['total_proyectados_acum'], 2)}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d;">
                        {{number_format($item['total_cosechados'], 2)}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d;">
                        {{number_format($item['total_cosechados_anno'], 2)}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d;">
                        {{number_format($item['total_cosechados_acum'], 2)}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d;">
                        {{$item['cumplimiento']}}%
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d;">
                        {{$item['cumplimiento_acum']}}%
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d;">
                        {{$item['tallos_m2_ejec']}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d;">
                        {{$item['tallos_m2_ejec_acum']}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d;">
                        {{$item['flor_m2_anno_52']}}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="alert alert-info text-center">No se han encontrado resultados</div>
@endif
<style>
    #table_fenograma_perennes tr#tr_fijo_top_0 th {
        position: sticky;
        top: 0;
        z-index: 8;
    }
</style>

<script>
    function seleccionar_planta(id) {
        $('#filtro_predeterminado_planta_P').val(id);
        $('#filtro_predeterminado_variedad').val('T');
        listar_fenograma_perennes();
    }
</script>