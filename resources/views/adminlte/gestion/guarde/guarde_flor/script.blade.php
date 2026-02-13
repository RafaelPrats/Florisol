<script>
    $('#vista_actual').val('guarde_flor');
    $('#variedad_filtro').select2();
    listar_reporte();

    function listar_reporte() {
        datos = {
            desde: $('#desde_filtro').val(),
            hasta: $('#hasta_filtro').val(),
            variedad: $('#variedad_filtro').val(),
        };
        get_jquery('{{ url('guarde_flor/listar_reporte') }}', datos, function(retorno) {
            $('#div_listar_reporte').html(retorno);
        });
    }

    function add_guardes() {
        datos = {}
        get_jquery('{{ url('guarde_flor/add_guardes') }}', datos, function(retorno) {
            modal_view('modal_add_guardes', retorno, '<i class="fa fa-fw fa-plus"></i> Importar Compras',
                true, false, '{{ isPC() ? '95%' : '' }}',
                function() {});
        })
    }
</script>
