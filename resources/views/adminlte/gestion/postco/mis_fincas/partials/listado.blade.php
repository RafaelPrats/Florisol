<div style="overflow-y: scroll; overflow-x: scroll; max-height: 600px">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d" id="table_listado">
        <thead>
            <tr class="tr_fija_top_0">
                <th class="padding_lateral_5 th_yura_green">
                    Finca
                </th>
                <th class="padding_lateral_5 th_yura_green" style="width: 90px">
                    Margen %
                </th>
                <th class="padding_lateral_5 th_yura_green" style="width: 220px">
                    Telefonos
                    <button type="button" class="btn btn-xs btn-yura_default tr_new hidden"
                        onclick="agregarTelefono()">
                        <i class="fa fa-plus"></i>
                    </button>
                </th>
                <th class="text-center th_yura_green" style="width: 90px">
                    <button type="button" class="btn btn-xs btn-yura_default"
                        onclick="$('.tr_new').removeClass('hidden')">
                        <i class="fa fa-fw fa-plus"></i> Nueva
                    </button>
                </th>
            </tr>
        </thead>
        <tbody>
            <tr class="tr_new hidden">
                <th class="text-center" style="border-color: #9d9d9d">
                    <input type="text" class="padding_lateral_5" style="width: 100%" id="new_nombre">
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    <input type="number" class="padding_lateral_5" style="width: 100%" id="new_margen">
                </th>
                <th class="text-center" style="border-color: #9d9d9d" id="contenedor_telefonos">
                    <div class="telefono_item" style="display: flex;">
                        <input type="text" class="padding_lateral_5 telefono_input" style="width: 100%"
                            placeholder="0991234567" maxlength="10">
                        <button type="button" class="btn btn-xs btn-yura_danger" style=""
                            onclick="$(this).parent().remove();">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    <button type="button" class="btn btn-xs btn-yura_primary" onclick="store_finca()">
                        <i class="fa fa-save"></i> Grabar
                    </button>
                </th>
            </tr>
            @foreach ($listado as $pos => $item)
                <tr style="background-color: {{ $pos % 2 == 0 ? '#dddddd' : '' }}">
                    <th class="text-center" style="border-color: #9d9d9d;">
                        <input type="text" style="width: 100%"
                            class="padding_lateral_5 {{ !$item->estado ? 'error' : '' }}" value="{{ $item->nombre }}"
                            onchange="update_finca('{{ $item->id_finca_proveedor }}')"
                            id="nombre_{{ $item->id_finca_proveedor }}">
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d;">
                        <input type="text" style="width: 100%"
                            class="padding_lateral_5 {{ !$item->estado ? 'error' : '' }}" value="{{ $item->margen }}"
                            onchange="update_finca('{{ $item->id_finca_proveedor }}')"
                            id="margen_{{ $item->id_finca_proveedor }}">
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        @foreach (explode('|', $item->telefonos) as $pos_t => $t)
                            <input type="text" style="width: 100%"
                                class="padding_lateral_5 telefono_{{ $item->id_finca_proveedor }} {{ !$item->estado ? 'error' : '' }}"
                                value="{{ $t }}"
                                onchange="update_finca('{{ $item->id_finca_proveedor }}')">
                        @endforeach
                        @if ($item->estado)
                            <input type="text" style="width: 100%"
                                class="padding_lateral_5 telefono_{{ $item->id_finca_proveedor }}" placeholder="nuevo"
                                onchange="update_finca('{{ $item->id_finca_proveedor }}')">
                        @endif
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        <div class="btn-group">
                            <button type="button" class="btn btn-xs btn-yura_default" title="Asignar plantas"
                                onclick="modal_plantas('{{ $item->id_finca_proveedor }}')">
                                <i class="fa fa-fw fa-leaf"></i>
                            </button>
                            <button type="button" class="btn btn-xs btn-yura_danger"
                                onclick="cambiar_estado('{{ $item->id_finca_proveedor }}')">
                                <i class="fa fa-fw fa-lock"></i>
                            </button>
                        </div>
                    </th>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<script>
    function agregarTelefono() {
        $('#contenedor_telefonos').append(`
        <div class="telefono_item" style="display: flex;">
            <input type="text"
                   class="padding_lateral_5 telefono_input"
                   placeholder="0991234567"
                   maxlength="10" style="width: 100%">
            <button type="button"
                    class="btn btn-xs btn-yura_danger"
                    style=""
                    onclick="$(this).parent().remove();">
                <i class="fa fa-times"></i>
            </button>
        </div>
    `);
    }

    function obtenerTelefonos() {

        let telefonos = [];

        $('.telefono_input').each(function() {

            let telefono = $(this).val().trim();

            if (telefono === '') {
                return;
            }

            telefono = telefono.replace(/\D/g, '');

            // Formato nacional: 09XXXXXXXX
            if (telefono.length === 10 && telefono.substring(0, 2) === '09') {

                telefono = '+593' + telefono.substring(1);

            }

            // Formato internacional: 5939XXXXXXXX
            else if (telefono.length === 12 && telefono.substring(0, 4) === '5939') {

                telefono = '+' + telefono;

            } else {
                alert('El teléfono "' + $(this).val() + '" no es un celular válido de Ecuador.');
                return false;
            }

            // Evitar teléfonos repetidos
            if (!telefonos.includes(telefono)) {
                telefonos.push(telefono);
            }
        });

        return telefonos.join('|');
    }

    function store_finca() {
        if ($('#new_nombre').val() != '') {
            texto =
                '<div class="alert alert-warning text-center"><h3>¿Esta seguro de <b>GRABAR</b> la finca?</h3></div>';

            modal_quest('modal_store_finca', texto, 'Desechar la flor', true, false, '40%',
                function() {
                    datos = {
                        _token: '{{ csrf_token() }}',
                        nombre: $('#new_nombre').val(),
                        margen: $('#new_margen').val(),
                        telefonos: obtenerTelefonos(),
                    }
                    post_jquery_m('{{ url('mis_fincas/store_finca') }}', datos, function() {
                        cerrar_modals();
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    });
                });
        }
    }

    function update_finca(id) {
        telefonos = '';
        for (i = 0; i < $('.telefono_' + id).length; i++) {
            telf = $('.telefono_' + id)[i].value;
            if (telf != '') {
                if (i == 0) {
                    telefonos = telf;
                } else {
                    telefonos += '|' + telf;
                }
            }
        }
        if ($('#nombre_' + id).val() != '' && telefonos != '') {
            datos = {
                _token: '{{ csrf_token() }}',
                id: id,
                nombre: $('#nombre_' + id).val(),
                margen: $('#margen_' + id).val(),
                telefonos: telefonos,
            }
            post_jquery_m('{{ url('mis_fincas/update_finca') }}', datos, function() {
                cerrar_modals();
                setTimeout(() => {
                    location.reload();
                }, 1000);
            });
        } else {
            alert('Faltan datos necesarios');
        }
    }

    function cambiar_estado(id) {
        texto =
            '<div class="alert alert-warning text-center"><h3>¿Esta seguro de <b>ACTIVAR/DESACTIVAR</b> la finca?</h3></div>';

        modal_quest('modal_cambiar_estado', texto, 'Desechar la flor', true, false, '40%',
            function() {
                datos = {
                    _token: '{{ csrf_token() }}',
                    id: id,
                }
                post_jquery_m('{{ url('mis_fincas/cambiar_estado') }}', datos, function() {
                    cerrar_modals();
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                });
            });
    }

    function modal_plantas(id) {
        datos = {
            id: id
        }
        get_jquery('{{ url('mis_fincas/modal_plantas') }}', datos, function(retorno) {
            modal_view('modal_modal_plantas', retorno,
                '<i class="fa fa-fw fa-plus"></i> Asignar Plantas',
                true, false, '{{ isPC() ? '50%' : '' }}',
                function() {});
        })
    }
</script>
