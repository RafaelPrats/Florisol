<div class="box box-success">
    <div class="box-header with-border">
        <h3 class="box-title">Crear y activar nuevo módulo</h3>
    </div>
    <div class="box-body">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group input-group">
            <span class="span-input-group-yura-fixed input-group-addon bg-yura_dark">
                Sector
            </span>
                    <select id="sector_new" class="form-control input-yura_default">
                        @foreach($sectores as $sec)
                            <option value="{{$sec->id_sector}}">{{$sec->nombre}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group input-group">
            <span class="span-input-group-yura-fixed input-group-addon bg-yura_dark">
                Módulo
            </span>
                    <input type="text" id="modulo_new" class="form-control input-yura_default text-center">
                </div>
            </div>
        </div>
        <table style="width: 100%">
            <tr>
                <th class="text-center th_yura_green" style="">Inicio</th>
                <th class="text-center th_yura_green" style="">Poda/Siembra</th>
                <th class="text-center th_yura_green" style="">Área m<sup>2</sup></th>
                <th class="text-center th_yura_green" style="">Ptas. Iniciales</th>
                <th class="text-center th_yura_green" style="">Ptas. Muertas</th>
                <th class="text-center th_yura_green" style="">Conteo <sup>T/P</sup></th>
            </tr>
            <tr>
                <td class="text-center" style="border-color: #9d9d9d">
                    <input type="date" id="fecha_inicio_new" class="form-control text-center" style="width: 100%" value="{{date('Y-m-d')}}"
                           required>
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <select id="poda_siembra_new" class="form-control" style="width: 100%" readonly="">
                        <option value="P">Poda</option>
                        <option value="S" selected>Siembra</option>
                    </select>
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <input type="number" id="area_new" class="form-control text-center" style="width: 100%" value="0" required>
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <input type="number" id="plantas_iniciales_new" class="form-control text-center" style="width: 100%" value="0" required>
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <input type="number" id="plantas_muertas_new" class="form-control text-center" style="width: 100%" value="0" required readonly>
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <input type="number" id="conteo_new" class="form-control text-center" style="width: 100%" value="0" required readonly>
                </td>
            </tr>
        </table>
        <div class="text-center" style="margin-top: 10px">
            <button type="button" class="btn btn-yura_primary" onclick="store_crear_activar_modulo()">
                <i class="fa fa-fw fa-save"></i> Guardar
            </button>
        </div>
    </div>
</div>

<script>
    function store_crear_activar_modulo() {
        datos = {
            _token: '{{csrf_token()}}',
            variedad: $('#filtro_predeterminado_variedad').val(),
            sector: $('#sector_new').val(),
            modulo: $('#modulo_new').val(),
            fecha_inicio: $('#fecha_inicio_new').val(),
            poda_siembra: $('#poda_siembra_new').val(),
            area: $('#area_new').val(),
            plantas_iniciales: $('#plantas_iniciales_new').val(),
            plantas_muertas: $('#plantas_muertas_new').val(),
            conteo: $('#conteo_new').val(),
        };
        if (datos['variedad'] != '' && datos['modulo'] != '' && datos['area'] != '' && datos['variedad'] != '' && datos['fecha_inicio'] != '' && datos['poda_siembra'] != '')
            modal_quest('modal-quest_store_ciclo', '<div class="alert alert-info text-center">¿Está seguro de <strong>CREAR y EMPEZAR</strong> este ciclo?</div>',
                '<i class="fa fa-fw fa-exclamation-triangle"></i> Confirmar acción', true, false, '{{isPC() ? '35%' : ''}}', function () {
                    post_jquery('{{url('sectores_modulos_perennes/store_crear_activar_modulo')}}', datos, function () {
                        $('#filtro_activos').val(1);
                        listar_ciclos_sect_mod_perennes();
                        cerrar_modals();
                    });
                });
        else
            alerta('<div class="alert alert-warning text-center">Faltan datos necesarios para iniciar un nuevo ciclo</div>');

    }
</script>