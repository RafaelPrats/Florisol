@if (count($semanas) > 0)
    <div style="overflow-y: scroll; max-height: 450px; overflow-x: scroll">
        <table class="table-bordered table-striped" style="width: 100%; border-radius: 18px 18px 0 0"
            id="table_proy_perennes">
            <tr id="tr_fijo_top_0">
                <th class="text-center th_yura_green" style="border-radius: 18px 0 0 0">
                    <div style="width: 20px">
                        <input type="checkbox" onclick="select_all_checks()" id="check_all_semanas" checked>
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 60px">
                        SEMANA
                    </div>
                </th>
                <th class="text-center" style="width: 80px; background-color: #0b3248; color: white">
                    <div style="width: 100px">
                        Tallos/m<sup>2</sup>/año
                        <input type="number" id="input_total_tallos_m2_anno" style="width: 100%; color: black"
                            class="text-center" onchange="set_tallos_m2_anno()" onkeyup="set_tallos_m2_anno()">
                        <script>
                            $('#area_total').val('{{ round($area, 2) }}')
                        </script>
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 90px">
                        Tallos Proyectados
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 90px">
                        Tallos Proyectados Acum.
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 80px">
                        Tallos Cosechados
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 80px">
                        Tallos Cosechados Acum.
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 100px">
                        % Cumplimiento Semanal
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 100px">
                        % Cumplimiento Acum.
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 80px">
                        Tallos/m<sup>2</sup> Ejecutado
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 80px">
                        Tallos/m<sup>2</sup> Ejec. Sem.
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 80px">
                        Tallos Cosechados Acum. 52 Sem.
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 100px">
                        Tallos/m<sup>2</sup>/año (52 semanas)
                    </div>
                </th>
                <th class="text-center th_yura_green" style="border-radius: 0 18px 0 0; width: 80px">
                    <button type="button" class="btn btn-xs btn-yura_dark" onclick="corregir_proy_sem_perenne()"
                        title="Corregir semanas">
                        <i class="fa fa-fw fa-refresh"></i>
                    </button>
                </th>
            </tr>
            @php
                $total_tallos_m2_anno = 0;
                $total_proyectados = 0;
                $total_cosechados = 0;
                $total_tallos_m2_ejec_acum = 0;
            @endphp
            @foreach ($semanas as $pos => $s)
                <tr id="tr_semana_{{ $s->id_semana }}">
                    <td class="text-center td_yura_default" style="border-color: #9d9d9d">
                        <input type="checkbox" id="check_{{ $s->id_semana }}" class="check_semana" checked>
                    </td>
                    <td class="text-center td_yura_default" style="border-color: #9d9d9d">
                        {{ $s->codigo }}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="number" id="curva_perenne_{{ $s->id_semana }}"
                            class="text-center input_curva_perenne"
                            value="{{ $s->curva_perenne > 0 ? $s->curva_perenne : 0 }}" style="width: 100%"
                            title="Tallos/planta/semana">
                        @php
                            $total_tallos_m2_anno += $s->curva_perenne > 0 ? $s->curva_perenne : 0;
                        @endphp
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        @php
                            $proyectados = isset($s->proyectados) ? $s->proyectados : 0;
                            $total_proyectados += $proyectados;
                        @endphp
                        {{ number_format($proyectados) }}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{ number_format($total_proyectados, 2) }}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        @php
                            $cosechados = isset($s->cosechados) ? $s->cosechados : 0;
                            $total_cosechados += $cosechados;
                        @endphp
                        {{ number_format($cosechados) }}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{ number_format($total_cosechados, 2) }}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{ isset($s->porcentaje_cumplimiento) ? $s->porcentaje_cumplimiento : 0 }}%
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{ isset($s->porcentaje_cumplimiento_acum) ? $s->porcentaje_cumplimiento_acum : 0 }}%
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{ isset($s->tallos_m2_ejecutado) ? $s->tallos_m2_ejecutado : 0 }}
                        @php
                            $total_tallos_m2_ejec_acum += isset($s->tallos_m2_ejecutado) ? $s->tallos_m2_ejecutado : 0;
                        @endphp
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{ $total_tallos_m2_ejec_acum }}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{ isset($s->cosechados_52_sem) ? number_format($s->cosechados_52_sem) : 0 }}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d" title="Tallos acum. / Área / #Sem * 52">
                        {{ $area > 0 ? number_format(($total_cosechados / $area / ($pos + 1)) * 52, 2) : '' }}
                    </td>
                    {{-- <td class="text-center" style="border-color: #9d9d9d" title="T/m2 Eje +(-52 sem) * 1">
                        {{isset($s->sum_ejec_52_sem) ? round($s->sum_ejec_52_sem * 1, 2) : 0}}
                    </td> --}}
                    <td class="text-center" style="border-color: #9d9d9d">
                        <button type="button" class="btn btn-xs btn-yura_primary"
                            onclick="update_semana('{{ $s->id_semana }}')">
                            <i class="fa fa-fw fa-save"></i>
                        </button>
                    </td>
                </tr>
                <input type="hidden" class="ids_semana" value="{{ $s->id_semana }}">
            @endforeach
            <tr>
                <th class="text-center th_yura_green" colspan="2">
                    TOTALES
                </th>
                <th class="text-center th_yura_green">
                    {{ number_format($total_tallos_m2_anno, 2) }}
                    <script>
                        $('#input_total_tallos_m2_anno').val({{ round($total_tallos_m2_anno, 2) }});
                    </script>
                </th>
                <th class="text-center th_yura_green">
                    {{ number_format($total_proyectados, 2) }}
                </th>
                <th class="text-center th_yura_green">
                </th>
                <th class="text-center th_yura_green">
                    {{ number_format($total_cosechados) }}
                </th>
                <th class="text-center th_yura_green">
                </th>
                <th class="text-center th_yura_green">
                    {{ $total_proyectados > 0 ? round(($total_cosechados * 100) / $total_proyectados, 2) : 0 }}%
                </th>
                <th class="text-center th_yura_green">
                </th>
                <th class="text-center th_yura_green">
                    {{ $area > 0 ? round($total_cosechados / $area, 2) : 0 }}
                </th>
                <th class="text-center th_yura_green" colspan="3">
                </th>
                <th class="text-center th_yura_green">
                    <div class="btn-group dropup" style="width: 60px">
                        <button type="button" class="btn btn-xs btn-yura_default"
                            onclick="update_all_semanas('{{ $s->id_semana }}')">
                            <i class="fa fa-fw fa-save"></i>
                        </button>
                        <button type="button" class="btn btn-xs btn-yura_dark dropdown-toggle"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span class="caret"></span>
                            <span class="sr-only">Toggle Dropdown</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-right">
                            @foreach (getAllFincas()->where('id_configuracion_empresa', '!=', getFincaActiva()) as $f)
                                <li>
                                    <a href="javascript:void(0)"
                                        onclick="copiar_a_finca({{ $f->id_configuracion_empresa }})">
                                        <i class="fa fa-fw fa-copy"></i> Copiar para {{ $f->nombre }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </th>
            </tr>
        </table>
    </div>
@else
    <div class="alert alert-info text-center">No se han encontrado resultados que mostrar</div>
@endif

<style>
    #table_proy_perennes tr#tr_fijo_top_0 th {
        position: sticky;
        top: 0;
        z-index: 8;
    }
</style>

<script>
    function update_semana(id) {
        datos = {
            _token: '{{ csrf_token() }}',
            semana: id,
            curva: $('#curva_perenne_' + id).val(),
            area_total: $('#area_total').val(),
        };
        $('#tr_semana_' + id).LoadingOverlay('show');
        $.post('{{ url('proy_perennes/update_semana') }}', datos, function(retorno) {
            if (!retorno.success)
                alerta(retorno.mensaje);
            else
                listar_proyecciones_perennes();
        }, 'json').fail(function(retorno) {
            console.log(retorno);
            alerta_errores(retorno.responseText);
        }).always(function() {
            $('#tr_semana_' + id).LoadingOverlay('hide');
        })
    }

    function update_all_semanas() {
        ids_semana = $('.ids_semana');
        data = [];
        for (i = 0; i < ids_semana.length; i++) {
            id = ids_semana[i].value;
            if ($('#check_' + id).prop('checked') == true)
                data.push({
                    semana: id,
                    curva: $('#curva_perenne_' + id).val()
                });
        }
        if (data.length > 0) {
            datos = {
                _token: '{{ csrf_token() }}',
                data: data,
                area_total: $('#area_total').val(),
            };
            post_jquery('{{ url('proy_perennes/update_all_semanas') }}', datos, function() {
                listar_proyecciones_perennes();
            })
        }
    }

    function copiar_a_finca(finca) {
        ids_semana = $('.ids_semana');
        data = [];
        for (i = 0; i < ids_semana.length; i++) {
            id = ids_semana[i].value;
            if ($('#check_' + id).prop('checked') == true)
                data.push({
                    semana: id,
                    curva: $('#curva_perenne_' + id).val()
                });
        }
        if (data.length > 0) {
            datos = {
                _token: '{{ csrf_token() }}',
                data: data,
                finca: finca,
                area_total: $('#area_total').val(),
                variedad: $('#filtro_predeterminado_variedad').val(),
            };
            post_jquery('{{ url('proy_perennes/copiar_a_finca') }}', datos, function() {
                //listar_proyecciones_perennes();
            })
        }
    }

    function select_all_checks() {
        if ($('#check_all_semanas').prop('checked') == true) {
            $('.check_semana').prop('checked', true);
        } else {
            $('.check_semana').prop('checked', false);
        }
    }

    function corregir_proy_sem_perenne() {
        datos = {
            _token: '{{ csrf_token() }}',
            anno: $('#filtro_predeterminado_anno').val(),
            variedad: $('#filtro_predeterminado_variedad').val(),
        };
        post_jquery('{{ url('proy_perennes/corregir_proy_sem_perenne') }}', datos, function() {
            listar_proyecciones_perennes();
        });
    }

    function set_tallos_m2_anno() {
        valor_anno = $('#input_total_tallos_m2_anno').val();
        valor_sem = valor_anno / 52;
        valor_sem = Math.round(valor_sem * 100) / 100;
        $('.input_curva_perenne').val(valor_sem);
    }
</script>
