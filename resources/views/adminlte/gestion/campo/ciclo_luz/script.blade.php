<script>
    $('#vista_actual').val('ciclo_luz');
    select_planta($('#filtro_predeterminado_planta').val(), 'filtro_predeterminado_variedad', 'div_cargar_variedades',
        '<option value="" selected>Seleccione</option>');
    setTimeout(function() {
        //listar_ciclo_luz();
    }, 1000);

    function listar_ciclo_luz() {
        datos = {
            variedad: $('#filtro_predeterminado_variedad').val(),
            poda_siembra: $('#filtro_poda_siembra').val(),
            fecha: $('#filtro_fecha').val(),
        };
        if (datos['variedad'] != '')
            get_jquery('{{ url('ciclo_luz/listar_ciclo_luz') }}', datos, function(retorno) {
                $('#div_listado_ciclos').html(retorno);
            });
    }
</script>
