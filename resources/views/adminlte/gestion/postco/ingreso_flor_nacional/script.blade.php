<script>
    $('#variedad_filtro').select2();
    listar_reporte();

    function listar_reporte() {
        datos = {
            planta: $('#planta_filtro').val(),
            variedad: $('#variedad_filtro').val(),
            fecha: $('#fecha_filtro').val(),
            motivo: $('#motivo_filtro').val(),
        };
        get_jquery('{{ url('ingreso_flor_nacional/listar_reporte') }}', datos, function(retorno) {
            $('#div_listado').html(retorno);
        });
    }

    function modal_motivos() {
        datos = {}
        get_jquery('{{ url('ingreso_flor_nacional/modal_motivos') }}', datos, function(retorno) {
            modal_view('modal_modal_motivos', retorno,
                '<i class="fa fa-fw fa-plus"></i> Motivos de Flor Nacional',
                true, false, '{{ isPC() ? '50%' : '' }}',
                function() {});
        })
    }

    function modal_fincas() {
        datos = {}
        get_jquery('{{ url('ingreso_flor_nacional/modal_fincas') }}', datos, function(retorno) {
            modal_view('modal_modal_fincas', retorno,
                '<i class="fa fa-fw fa-plus"></i> Fincas de origen',
                true, false, '{{ isPC() ? '50%' : '' }}',
                function() {});
        })
    }
</script>
