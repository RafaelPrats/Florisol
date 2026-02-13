<legend style="margin-bottom: 5px" class="text-center">
    PROCESOS EN SEGUNDO PLANO EJECUTADOS EN EL DIA
</legend>
<div id="div_cargar_procesos"></div>

<script>
    cargar_procesos();
    var funcionInterval = setInterval(() => {
        cargar_procesos();
    }, 1500);

    $('#btn_cerrar_modal_ver_procesos').on('click', function() {
        $('#div_cargar_procesos').html('');
        clearInterval(funcionInterval);
    })

    function cargar_procesos() {
        datos = {}
        $.get('{{ url('importar_pedidos/cargar_procesos') }}', datos, function(retorno) {
            $('#div_cargar_procesos').html(retorno);
        });
    }
</script>
