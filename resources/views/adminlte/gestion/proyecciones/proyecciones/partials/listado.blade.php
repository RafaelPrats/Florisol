@if (count($semanas) > 0)
    <div class="text-right" style="margin-bottom: 5px; border: 1px dotted black; padding-right: 5px" id="div_refresh_jobs">
    </div>

    <div style="overflow-y: scroll; max-height: 450px; overflow-x: scroll">
        <table class="table-bordered table-striped" style="width: 100%; border-radius: 18px 18px 0 0">
            <tr id="tr_fijo_top_0">
                <th class="text-center th_yura_green" rowspan="3">
                    <input type="checkbox" id="check_all"
                        onchange="$('.check_semana').prop('checked', $(this).prop('checked'))">
                </th>
                <th class="text-center th_yura_green" rowspan="3">
                    SEMANA
                </th>
                <th class="text-center bg-yura_dark" colspan="9">
                    SIEMBRAS
                </th>
                <th class="text-center th_yura_green" colspan="9">
                    PODAS
                </th>
                <th class="text-center th_yura_green" rowspan="3">

                </th>
            </tr>
            <tr id="tr_fijo_top_1">
                <th class="text-center bg-yura_dark">
                    <div style="width: 100px">
                        Curva
                    </div>
                </th>
                {{-- SIEMBRAS --}}
                <th class="text-center bg-yura_dark">
                    <div style="width: 70px">
                        Tallos Pta.
                    </div>
                </th>
                <th class="text-center bg-yura_dark">
                    <div style="width: 70px">
                        Desecho
                    </div>
                </th>
                <th class="text-center bg-yura_dark">
                    <div style="width: 70px">
                        Inicio Cosecha
                    </div>
                </th>
                <th class="text-center bg-yura_dark">
                    <div style="width: 70px">
                        Ptas. Iniciales
                    </div>
                </th>
                <th class="text-center bg-yura_dark">
                    <div style="width: 70px">
                        Densidad
                    </div>
                </th>
                <th class="text-center bg-yura_dark">
                    <div style="width: 70px">
                        Área
                    </div>
                </th>
                <th class="text-center bg-yura_dark">
                    <div style="width: 70px">
                        % Bqt
                    </div>
                </th>
                <th class="text-center bg-yura_dark">
                    <div style="width: 70px">
                        % Exp
                    </div>
                </th>
                {{-- PODAS --}}
                <th class="text-center th_yura_green">
                    <div style="width: 100px">
                        Curva
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 70px">
                        Tallos Pta.
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 70px">
                        Desecho
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 70px">
                        Inicio Cosecha
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 70px">
                        Ptas. Iniciales
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 70px">
                        Densidad
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 70px">
                        Área
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 70px">
                        % Bqt
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 70px">
                        % Exp
                    </div>
                </th>
            </tr>
            <tr id="tr_fijo_top_2">
                <th class="text-center bg-yura_dark">
                    <input type="text" maxlength="250" style="width: 100%; color: black" class="text-center"
                        id="curva_s" onchange="set_all('curva_s')" onkeyup="set_all('curva_s')"
                        placeholder="20-40-40">
                </th>
                {{-- SIEMBRAS --}}
                <th class="text-center bg-yura_dark">
                    <input type="number" style="width: 100%; color: black" class="text-center" id="tallos_x_pta_s"
                        min="0" onchange="set_all('tallos_x_pta_s')" onkeyup="set_all('tallos_x_pta_s')">
                </th>
                <th class="text-center bg-yura_dark">
                    <input type="number" style="width: 100%; color: black" class="text-center" id="desecho_s"
                        min="0" onchange="set_all('desecho_s')" onkeyup="set_all('desecho_s')">
                </th>
                <th class="text-center bg-yura_dark">
                    <input type="number" style="width: 100%; color: black" class="text-center" id="semana_cosecha_s"
                        min="0" onchange="set_all('semana_cosecha_s')" onkeyup="set_all('semana_cosecha_s')">
                </th>
                <th class="text-center bg-yura_dark">
                    <input type="number" style="width: 100%; color: black" class="text-center" id="plantas_iniciales_s"
                        min="0" onchange="set_all('plantas_iniciales_s')"
                        onkeyup="set_all('plantas_iniciales_s')">
                </th>
                <th class="text-center bg-yura_dark">
                    <input type="number" style="width: 100%; color: black" class="text-center" id="densidad_s"
                        min="0" onchange="set_all('densidad_s')" onkeyup="set_all('densidad_s')">
                </th>
                <th class="text-center bg-yura_dark">
                </th>
                <th class="text-center bg-yura_dark">
                    <input type="number" style="width: 100%; color: black" class="text-center" id="porcent_bqt_s"
                        min="0" onchange="set_all('porcent_bqt_s')" onkeyup="set_all('porcent_bqt_s')">
                </th>
                <th class="text-center bg-yura_dark">
                    <input type="number" style="width: 100%; color: black" class="text-center" id="porcent_exp_s"
                        min="0" onchange="set_all('porcent_exp_s')" onkeyup="set_all('porcent_exp_s')">
                </th>
                {{-- PODAS --}}
                <th class="text-center th_yura_green">
                    <input type="text" maxlength="250" style="width: 100%; color: black" class="text-center"
                        id="curva_p" onchange="set_all('curva_p')" onkeyup="set_all('curva_p')"
                        placeholder="20-40-40">
                </th>
                <th class="text-center th_yura_green">
                    <input type="number" style="width: 100%; color: black" class="text-center" id="tallos_x_pta_p"
                        min="0" onchange="set_all('tallos_x_pta_p')" onkeyup="set_all('tallos_x_pta_p')">
                </th>
                <th class="text-center th_yura_green">
                    <input type="number" style="width: 100%; color: black" class="text-center" id="desecho_p"
                        min="0" onchange="set_all('desecho_p')" onkeyup="set_all('desecho_p')">
                </th>
                <th class="text-center th_yura_green">
                    <input type="number" style="width: 100%; color: black" class="text-center"
                        id="semana_cosecha_p" min="0" onchange="set_all('semana_cosecha_p')"
                        onkeyup="set_all('semana_cosecha_p')">
                </th>
                <th class="text-center th_yura_green">
                    <input type="number" style="width: 100%; color: black" class="text-center"
                        id="plantas_iniciales_p" min="0" onchange="set_all('plantas_iniciales_p')"
                        onkeyup="set_all('plantas_iniciales_p')">
                </th>
                <th class="text-center th_yura_green">
                    <input type="number" style="width: 100%; color: black" class="text-center" id="densidad_p"
                        min="0" onchange="set_all('densidad_p')" onkeyup="set_all('densidad_p')">
                </th>
                <th class="text-center th_yura_green">
                </th>
                <th class="text-center th_yura_green">
                    <input type="number" style="width: 100%; color: black" class="text-center" id="porcent_bqt_p"
                        min="0" onchange="set_all('porcent_bqt_p')" onkeyup="set_all('porcent_bqt_p')">
                </th>
                <th class="text-center th_yura_green">
                    <input type="number" style="width: 100%; color: black" class="text-center" id="porcent_exp_p"
                        min="0" onchange="set_all('porcent_exp_p')" onkeyup="set_all('porcent_exp_p')">
                </th>
            </tr>
            @foreach ($semanas as $pos => $s)
                @php
                    $getSemanaEmpresaS = $s->getSemanaEmpresa($empresa, 'S');
                    $getSemanaEmpresaP = $s->getSemanaEmpresa($empresa, 'P');
                    
                    $ptas_iniciales_S = isset($getSemanaEmpresaS) ? $getSemanaEmpresaS->plantas_iniciales : 0;
                    $densidad_S = isset($getSemanaEmpresaS) ? $getSemanaEmpresaS->densidad : 0;
                    $ptas_iniciales_P = isset($getSemanaEmpresaP) ? $getSemanaEmpresaP->plantas_iniciales : 0;
                    $densidad_P = isset($getSemanaEmpresaP) ? $getSemanaEmpresaP->densidad : 0;
                    
                    $pct_bqt_S = isset($getSemanaEmpresaS) ? $getSemanaEmpresaS->porcent_bqt : 0;
                    $pct_exp_S = isset($getSemanaEmpresaS) ? $getSemanaEmpresaS->porcent_exp : 0;
                    $pct_bqt_P = isset($getSemanaEmpresaP) ? $getSemanaEmpresaP->porcent_bqt : 0;
                    $pct_exp_P = isset($getSemanaEmpresaP) ? $getSemanaEmpresaP->porcent_exp : 0;
                @endphp
                <tr id="tr_semana_{{ $s->id_semana }}">
                    <td class="text-center td_yura_default field_ejecutar_{{ $s->id_semana }}"
                        style="border-color: #9d9d9d; background-color: {{ $s->ejecutado == 1 ? '#cdffc6' : '' }}">
                        <input type="checkbox" class="check_semana" id="check_sem_{{ $s->id_semana }}">
                    </td>
                    <td class="text-center td_yura_default field_ejecutar_{{ $s->id_semana }}"
                        style="border-color: #9d9d9d; background-color: {{ $s->ejecutado == 1 ? '#cdffc6' : '' }}">
                        {{ $s->codigo }}
                    </td>
                    {{-- SIEMBRAS --}}
                    <td class="text-center td_yura_default" style="border-color: #9d9d9d">
                        <input type="text" maxlength="250"
                            style="width: 100%; background-color: {{ $s->ejecutado == 1 ? '#cdffc6' : '' }}"
                            class="text-center field_ejecutar_{{ $s->id_semana }}" id="curva_s_{{ $s->id_semana }}"
                            value="{{ $s->curva }}">
                    </td>
                    <td class="text-center td_yura_default" style="border-color: #9d9d9d">
                        <input type="number"
                            style="width: 100%; background-color: {{ $s->ejecutado == 1 ? '#cdffc6' : '' }}"
                            class="text-center field_ejecutar_{{ $s->id_semana }}" id="tallos_x_pta_s_{{ $s->id_semana }}"
                            value="{{ $s->tallos_planta_siembra }}" min="0">
                    </td>
                    <td class="text-center td_yura_default" style="border-color: #9d9d9d">
                        <input type="number"
                            style="width: 100%; background-color: {{ $s->ejecutado == 1 ? '#cdffc6' : '' }}"
                            class="text-center field_ejecutar_{{ $s->id_semana }}" id="desecho_s_{{ $s->id_semana }}"
                            value="{{ $s->desecho }}" min="0">
                    </td>
                    <td class="text-center td_yura_default" style="border-color: #9d9d9d">
                        <input type="number"
                            style="width: 100%; background-color: {{ $s->ejecutado == 1 ? '#cdffc6' : '' }}"
                            class="text-center field_ejecutar_{{ $s->id_semana }}" id="semana_cosecha_s_{{ $s->id_semana }}"
                            value="{{ $s->semana_siembra }}" min="0">
                    </td>
                    <td class="text-center td_yura_default" style="border-color: #9d9d9d">
                        <input type="number"
                            style="width: 100%; background-color: {{ $s->ejecutado == 1 ? '#cdffc6' : '' }}"
                            class="text-center field_ejecutar_{{ $s->id_semana }}" id="plantas_iniciales_s_{{ $s->id_semana }}"
                            value="{{ $ptas_iniciales_S }}" min="0">
                    </td>
                    <td class="text-center td_yura_default" style="border-color: #9d9d9d">
                        <input type="number"
                            style="width: 100%; background-color: {{ $s->ejecutado == 1 ? '#cdffc6' : '' }}"
                            class="text-center field_ejecutar_{{ $s->id_semana }}" id="densidad_s_{{ $s->id_semana }}"
                            value="{{ $densidad_S }}" min="0">
                    </td>
                    <td class="text-center td_yura_default field_ejecutar_{{ $s->id_semana }}"
                        style="border-color: #9d9d9d; background-color: {{ $s->ejecutado == 1 ? '#cdffc6' : '' }}">
                        {{ $densidad_S > 0 ? round($ptas_iniciales_S / $densidad_S, 2) : 0 }}
                    </td>
                    <td class="text-center td_yura_default" style="border-color: #9d9d9d">
                        <input type="number"
                            style="width: 100%; background-color: {{ $s->ejecutado == 1 ? '#cdffc6' : '' }}"
                            class="text-center field_ejecutar_{{ $s->id_semana }}" id="porcent_bqt_s_{{ $s->id_semana }}"
                            value="{{ $pct_bqt_S }}" min="0">
                    </td>
                    <td class="text-center td_yura_default" style="border-color: #9d9d9d">
                        <input type="number"
                            style="width: 100%; background-color: {{ $s->ejecutado == 1 ? '#cdffc6' : '' }}"
                            class="text-center field_ejecutar_{{ $s->id_semana }}" id="porcent_exp_s_{{ $s->id_semana }}" value="{{ $pct_exp_S }}"
                            min="0">
                    </td>
                    {{-- PODAS --}}
                    <td class="text-center td_yura_default" style="border-color: #9d9d9d">
                        <input type="text" maxlength="250"
                            style="width: 100%; background-color: {{ $s->ejecutado == 1 ? '#cdffc6' : '' }}"
                            class="text-center field_ejecutar_{{ $s->id_semana }}" id="curva_p_{{ $s->id_semana }}"
                            value="{{ $s->curva_poda }}">
                    </td>
                    <td class="text-center td_yura_default" style="border-color: #9d9d9d">
                        <input type="number"
                            style="width: 100%; background-color: {{ $s->ejecutado == 1 ? '#cdffc6' : '' }}"
                            class="text-center field_ejecutar_{{ $s->id_semana }}" id="tallos_x_pta_p_{{ $s->id_semana }}"
                            value="{{ $s->tallos_planta_poda }}" min="0">
                    </td>
                    <td class="text-center td_yura_default" style="border-color: #9d9d9d">
                        <input type="number"
                            style="width: 100%; background-color: {{ $s->ejecutado == 1 ? '#cdffc6' : '' }}"
                            class="text-center field_ejecutar_{{ $s->id_semana }}" id="desecho_p_{{ $s->id_semana }}"
                            value="{{ $s->desecho_poda }}" min="0">
                    </td>
                    <td class="text-center td_yura_default" style="border-color: #9d9d9d">
                        <input type="number"
                            style="width: 100%; background-color: {{ $s->ejecutado == 1 ? '#cdffc6' : '' }}"
                            class="text-center field_ejecutar_{{ $s->id_semana }}" id="semana_cosecha_p_{{ $s->id_semana }}"
                            value="{{ $s->semana_poda }}" min="0">
                    </td>
                    <td class="text-center td_yura_default" style="border-color: #9d9d9d">
                        <input type="number"
                            style="width: 100%; background-color: {{ $s->ejecutado == 1 ? '#cdffc6' : '' }}"
                            class="text-center field_ejecutar_{{ $s->id_semana }}" id="plantas_iniciales_p_{{ $s->id_semana }}"
                            value="{{ $ptas_iniciales_P }}" min="0">
                    </td>
                    <td class="text-center td_yura_default" style="border-color: #9d9d9d">
                        <input type="number"
                            style="width: 100%; background-color: {{ $s->ejecutado == 1 ? '#cdffc6' : '' }}"
                            class="text-center field_ejecutar_{{ $s->id_semana }}" id="densidad_p_{{ $s->id_semana }}"
                            value="{{ $densidad_P }}" min="0">
                    </td>
                    <td class="text-center td_yura_default field_ejecutar_{{ $s->id_semana }}"
                        style="border-color: #9d9d9d; background-color: {{ $s->ejecutado == 1 ? '#cdffc6' : '' }}">
                        {{ $densidad_P > 0 ? round($ptas_iniciales_P / $densidad_P, 2) : 0 }}
                    </td>
                    <td class="text-center td_yura_default" style="border-color: #9d9d9d">
                        <input type="number"
                            style="width: 100%; background-color: {{ $s->ejecutado == 1 ? '#cdffc6' : '' }}"
                            class="text-center field_ejecutar_{{ $s->id_semana }}" id="porcent_bqt_p_{{ $s->id_semana }}"
                            value="{{ $pct_bqt_P }}" min="0">
                    </td>
                    <td class="text-center td_yura_default" style="border-color: #9d9d9d">
                        <input type="number"
                            style="width: 100%; background-color: {{ $s->ejecutado == 1 ? '#cdffc6' : '' }}"
                            class="text-center field_ejecutar_{{ $s->id_semana }}" id="porcent_exp_p_{{ $s->id_semana }}"
                            value="{{ $pct_exp_P }}" min="0">
                    </td>
                    <td class="text-center td_yura_default field_ejecutar_{{ $s->id_semana }}"
                        style="border-color: #9d9d9d; background-color: {{ $s->ejecutado == 1 ? '#cdffc6' : '' }}">
                        <div class="btn-group">
                            <button type="button" class="btn btn-yura_default btn-xs dropdown-toggle"
                                data-toggle="dropdown">
                                <i class="fa fa-fw fa-gears"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-right" role="menu">
                                <li>
                                    <a href="javascript:void(0)" onclick="update_semana('{{ $s->id_semana }}')">
                                        <i class="fa fa-fw fa-pencil"></i> Modificar Semana
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:void(0)" onclick="ejecutar_semana('{{ $s->id_semana }}')">
                                        <i class="fa fa-fw fa-check"></i> Ejecutar Semana
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
                <input type="hidden" class="ids_semana" value="{{ $s->id_semana }}">
            @endforeach
        </table>
    </div>
    <div style="margin-top: 10px" class="text-center">
        <button type="button" class="btn btn-yura_primary" onclick="update_semanas()">
            <i class="fa fa-fw fa-save"></i> Guardar
        </button>
    </div>
@else
    <div class="alert alert-info text-center">No se han encontrado resultados que mostrar</div>
@endif

<input type="hidden" id="id_variedad_reporte" value="{{ $variedad->id_variedad }}">
<style>
    tr#tr_fijo_top_0 th {
        position: sticky;
        top: 0;
        z-index: 8;
    }

    tr#tr_fijo_top_1 th {
        position: sticky;
        top: 21px;
        z-index: 8;
    }

    tr#tr_fijo_top_2 th {
        position: sticky;
        top: 61px;
        z-index: 8;
    }
</style>

<script>
    function update_semana(sem) {
        datos = {
            _token: '{{ csrf_token() }}',
            sem: sem,
            curva_s: $('#curva_s_' + sem).val(),
            tallos_x_pta_s: $('#tallos_x_pta_s_' + sem).val(),
            desecho_s: $('#desecho_s_' + sem).val(),
            semana_cosecha_s: $('#semana_cosecha_s_' + sem).val(),
            plantas_iniciales_s: $('#plantas_iniciales_s_' + sem).val(),
            densidad_s: $('#densidad_s_' + sem).val(),
            porcent_bqt_s: $('#porcent_bqt_s_' + sem).val(),
            porcent_exp_s: $('#porcent_exp_s_' + sem).val(),
            curva_p: $('#curva_p_' + sem).val(),
            tallos_x_pta_p: $('#tallos_x_pta_p_' + sem).val(),
            desecho_p: $('#desecho_p_' + sem).val(),
            semana_cosecha_p: $('#semana_cosecha_p_' + sem).val(),
            plantas_iniciales_p: $('#plantas_iniciales_p_' + sem).val(),
            densidad_p: $('#densidad_p_' + sem).val(),
            porcent_bqt_p: $('#porcent_bqt_p_' + sem).val(),
            porcent_exp_p: $('#porcent_exp_p_' + sem).val(),
        };
        post_jquery('{{ url('ingreso_proyecciones/update_semana') }}', datos, function() {

        });
    }

    function ejecutar_semana(sem) {
        modal_quest('modal-quest_ejecturar_semana',
            '<div class="alert alert-info text-center">¿Desea <strong>EJECUTAR</strong> la semana?</div>',
            '<i class="fa fa-fw fa-"></i>', true, false, '50%',
            function() {
                datos = {
                    _token: '{{ csrf_token() }}',
                    sem: sem,
                };
                post_jquery_m('{{ url('ingreso_proyecciones/ejecutar_semana') }}', datos, function() {
                    $('.field_ejecutar_' + sem).css('background-color', '#cdffc6');
                    update_semana(sem);
                    cerrar_modals();
                }, 'tr_semana_' + sem);
            });
    }

    function set_all(campo) {
        texto = $('#' + campo).val();
        ids_semana = $('.ids_semana');
        for (i = 0; i < ids_semana.length; i++) {
            sem = ids_semana[i].value;
            if ($('#check_sem_' + sem).prop('checked') == true) {
                $('#' + campo + '_' + sem).val(texto);
            }
        }
    }

    function update_semanas() {
        data = [];
        ids_semana = $('.ids_semana');
        for (i = 0; i < ids_semana.length; i++) {
            sem = ids_semana[i].value;
            if ($('#check_sem_' + sem).prop('checked') == true) {
                data.push({
                    sem: sem,
                    curva_s: $('#curva_s_' + sem).val(),
                    tallos_x_pta_s: $('#tallos_x_pta_s_' + sem).val(),
                    desecho_s: $('#desecho_s_' + sem).val(),
                    semana_cosecha_s: $('#semana_cosecha_s_' + sem).val(),
                    plantas_iniciales_s: $('#plantas_iniciales_s_' + sem).val(),
                    densidad_s: $('#densidad_s_' + sem).val(),
                    porcent_bqt_s: $('#porcent_bqt_s_' + sem).val(),
                    porcent_exp_s: $('#porcent_exp_s_' + sem).val(),
                    curva_p: $('#curva_p_' + sem).val(),
                    tallos_x_pta_p: $('#tallos_x_pta_p_' + sem).val(),
                    desecho_p: $('#desecho_p_' + sem).val(),
                    semana_cosecha_p: $('#semana_cosecha_p_' + sem).val(),
                    plantas_iniciales_p: $('#plantas_iniciales_p_' + sem).val(),
                    densidad_p: $('#densidad_p_' + sem).val(),
                    porcent_bqt_p: $('#porcent_bqt_p_' + sem).val(),
                    porcent_exp_p: $('#porcent_exp_p_' + sem).val(),
                });
            }
        }
        if (data.length > 0) {
            datos = {
                _token: '{{ csrf_token() }}',
                data: data,
                variedad: $('#filtro_predeterminado_variedad').val(),
            };
            post_jquery('{{ url('ingreso_proyecciones/update_all_semanas') }}', datos, function() {

            });
        }
    }

    function refresh_jobs() {
        datos = {
            variedad: $('#id_variedad_reporte').val(),
        };
        $.get('{{ url('ingreso_proyecciones/refresh_jobs') }}', datos, function(retorno) {
            $('#div_refresh_jobs').html(retorno);
        });
    }

    setInterval(function() {
        refresh_jobs();
    }, 7000);
</script>
