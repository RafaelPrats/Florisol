<script>
    $('#vista_actual').val('sectores_modulos_perennes');

    function listar_ciclos_sect_mod_perennes() {
        datos = {
            variedad: $('#filtro_predeterminado_variedad').val(),
            estado: $('#filtro_activos').val(),
        };
        get_jquery('{{url('sectores_modulos_perennes/listar_ciclos')}}', datos, function (retorno) {
            $('#div_listado_ciclos').html(retorno);
        });
    }
</script>