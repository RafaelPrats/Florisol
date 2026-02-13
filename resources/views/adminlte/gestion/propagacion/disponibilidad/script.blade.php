<script>
    $('#vista_actual').val('propag_disponibilidad');

    function listar_disponibilidades() {
        datos = {
            variedad: $('#filtro_predeterminado_variedad').val(),
            desde: $('#filtro_predeterminado_desde').val(),
            hasta: $('#filtro_predeterminado_hasta').val(),
        };
        get_jquery('{{url('propag_disponibilidad/listar_disponibilidades')}}', datos, function (retorno) {
            $('#listado_disponibilidad').html(retorno);
            //estructura_tabla('table_contenedores', false, true);
        });
    }
</script>