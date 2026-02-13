<script>
    $('#vista_actual').val('importar_postco');
    $('#variedad_filtro').select2();
    listar_reporte();

    function listar_reporte() {
        datos = {
            fecha: $('#fecha_filtro').val(),
            variedad: $('#variedad_filtro').val(),
        };
        get_jquery('{{ url('importar_postco/listar_reporte') }}', datos, function(retorno) {
            $('#div_listar_reporte').html(retorno);
        });
    }

    function modal_importar() {
        datos = {}
        get_jquery('{{ url('importar_postco/modal_importar') }}', datos, function(retorno) {
            modal_view('modal_modal_importar', retorno, '<i class="fa fa-fw fa-plus"></i> Importar Recetas',
                true, false, '{{ isPC() ? '75%' : '' }}',
                function() {});
        })
    }
</script>
